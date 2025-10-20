<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $this->ensureCanManageUsers();

        $search = trim((string) $request->query('search', ''));
        $perPage = $request->integer('per_page', 10);

        $users = User::query()
            ->with([
                'roles:id,name',
                'permissions:id,name',
                'staff:id,first_name,last_name,email,status,user_id',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->values(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->values(),
                    'has_two_factor' => ! is_null($user->two_factor_secret),
                    'staff' => $user->staff ? [
                        'id' => $user->staff->id,
                        'full_name' => $user->staff->full_name,
                        'status' => $user->staff->status,
                    ] : null,
                ];
            });

        $staffLinkedCount = Staff::whereNotNull('user_id')->count();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'stats' => [
                [
                    'label' => 'Total Users',
                    'value' => $users->count(),
                    'tone' => 'primary',
                ],
                [
                    'label' => 'Staff Linked',
                    'value' => $staffLinkedCount,
                    'tone' => 'success',
                ],
                [
                    'label' => 'Roles',
                    'value' => Role::count(),
                    'tone' => 'muted',
                ],
            ],
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
            'can' => [
                'create' => $request->user()->can('users.manage'),
                'edit' => $request->user()->can('users.manage'),
                'delete' => $request->user()->can('users.manage'),
            ],
            'breadcrumbs' => [
                ['title' => 'Users', 'href' => route('users.index')],
            ],
        ]);
    }

    public function create(): Response
    {
        $this->ensureCanManageUsers();

        return Inertia::render('Users/Create', [
            'roles' => $this->availableRoles(),
            'permissions' => $this->availablePermissions(),
            'staff' => $this->availableStaff(),
            'breadcrumbs' => [
                ['title' => 'Users', 'href' => route('users.index')],
                ['title' => 'Create', 'href' => route('users.create')],
            ],
        ]);
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        $this->ensureCanManageUsers();

        DB::transaction(function () use ($request) {
            $data = $request->validated();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
            ]);

            $user->syncRoles($data['roles'] ?? []);
            $user->syncPermissions($data['permissions'] ?? []);

            $this->syncStaffAssignment($user, $data['staff_id'] ?? null);
        });

        return redirect()
            ->route('users.index')
            ->with('bannerStyle', 'success')
            ->with('banner', 'User created successfully.');
    }

    public function edit(User $user): Response
    {
        $this->ensureCanManageUsers();

        $user->load(['roles:id,name', 'permissions:id,name', 'staff:id']);

        $activity = $user->activityLogs()
            ->with('causer')
            ->latest()
            ->take(20)
            ->get();

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->values(),
                'permissions' => $user->getAllPermissions()->pluck('name')->values(),
                'staff_id' => $user->staff?->id,
            ],
            'roles' => $this->availableRoles(),
            'permissions' => $this->availablePermissions(),
            'staff' => $this->availableStaff($user),
            'activity' => ActivityLogResource::collection($activity),
            'breadcrumbs' => [
                ['title' => 'Users', 'href' => route('users.index')],
                ['title' => $user->name, 'href' => route('users.edit', $user)],
            ],
        ]);
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $this->ensureCanManageUsers();

        $oldRoles = $user->roles->pluck('name')->sort()->values()->toArray();
        $oldPermissions = $user->getAllPermissions()->pluck('name')->sort()->values()->toArray();

        $newRoles = $oldRoles;
        $newPermissions = $oldPermissions;

        DB::transaction(function () use ($request, $user, &$newRoles, &$newPermissions) {
            $data = $request->validated();

            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);

            $user->syncRoles($data['roles'] ?? []);
            $user->syncPermissions($data['permissions'] ?? []);

            $this->syncStaffAssignment($user, $data['staff_id'] ?? null);

            $user->refresh()->load('roles');
            $newRoles = $user->roles->pluck('name')->sort()->values()->toArray();
            $newPermissions = $user->getAllPermissions()->pluck('name')->sort()->values()->toArray();
        });

        if ($oldRoles !== $newRoles || $oldPermissions !== $newPermissions) {
            ActivityLog::record(
                auth()->id(),
                $user->fresh(),
                'roles.updated',
                'Roles or permissions updated',
                [
                    'before' => [
                        'roles' => $oldRoles,
                        'permissions' => $oldPermissions,
                    ],
                    'after' => [
                        'roles' => $newRoles,
                        'permissions' => $newPermissions,
                    ],
                ]
            );
        }

        return redirect()
            ->route('users.index')
            ->with('bannerStyle', 'success')
            ->with('banner', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureCanManageUsers();

        if ($request->user()->is($user)) {
            return back()
                ->with('bannerStyle', 'warning')
                ->with('banner', 'You cannot delete your own account.');
        }

        DB::transaction(function () use ($user) {
            $this->syncStaffAssignment($user, null);
            $user->delete();
        });

        return redirect()
            ->route('users.index')
            ->with('bannerStyle', 'info')
            ->with('banner', 'User removed.');
    }

    protected function availableRoles(): array
    {
        return Role::orderBy('name')
            ->get()
            ->map(fn (Role $role) => $role->name)
            ->values()
            ->toArray();
    }

    protected function availablePermissions(): array
    {
        return Permission::orderBy('name')
            ->get()
            ->map(fn (Permission $permission) => $permission->name)
            ->values()
            ->toArray();
    }

    protected function availableStaff(?User $user = null): array
    {
        return Staff::orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function (Staff $staff) use ($user) {
                return [
                    'id' => $staff->id,
                    'label' => $staff->full_name,
                    'status' => $staff->status,
                    'linked_user_id' => $staff->user_id,
                    'linked_to_current_user' => $user ? $staff->user_id === $user->id : false,
                ];
            })
            ->values()
            ->toArray();
    }

    protected function syncStaffAssignment(User $user, ?int $staffId): void
    {
        $previousStaff = $user->staff;
        $previousStaffId = $previousStaff?->id;

        if ($previousStaff && ($staffId === null || $previousStaffId !== $staffId)) {
            $previousStaff->user()->dissociate();
            $previousStaff->save();
        }

        $currentStaff = null;

        if ($staffId) {
            if ($previousStaffId === $staffId) {
                $currentStaff = $previousStaff;
            } else {
                $currentStaff = Staff::find($staffId);

                if ($currentStaff) {
                    $originalUserId = $currentStaff->user_id;

                    if ($originalUserId && $originalUserId !== $user->id) {
                        $currentStaff->user()->dissociate();
                        $currentStaff->save();
                    }

                    $currentStaff->user()->associate($user);
                    $currentStaff->save();

                    ActivityLog::record(
                        auth()->id(),
                        $currentStaff,
                        'user_link.updated',
                        'User link updated',
                        [
                            'before' => ['user_id' => $originalUserId],
                            'after' => ['user_id' => $user->id],
                        ]
                    );
                }
            }
        }

        if ($previousStaff && $previousStaffId !== $staffId) {
            ActivityLog::record(
                auth()->id(),
                $previousStaff,
                'user_link.updated',
                'User link removed',
                [
                    'before' => ['user_id' => $user->id],
                    'after' => ['user_id' => null],
                ]
            );
        }

        $user->unsetRelation('staff');

        if ($staffId && $staffId === $previousStaffId) {
            $user->setRelation('staff', $previousStaff);
        } elseif ($currentStaff) {
            $user->setRelation('staff', $currentStaff);
        }

        $currentStaffId = $user->staff?->id;

        if ($previousStaffId !== $currentStaffId) {
            ActivityLog::record(
                auth()->id(),
                $user,
                'staff_link.updated',
                'Linked staff profile updated',
                [
                    'before' => ['staff_id' => $previousStaffId],
                    'after' => ['staff_id' => $currentStaffId],
                ]
            );
        }
    }

    protected function ensureCanManageUsers(): void
    {
        abort_unless(auth()->user()?->can('users.manage'), 403);
    }
}
