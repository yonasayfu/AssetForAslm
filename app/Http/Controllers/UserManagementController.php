<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Models\Staff;
use App\Models\User;
use App\Support\Exports\ExportConfig;
use App\Support\Exports\HandlesDataExport;
use App\Support\Users\SyncsStaffAssignment;
use Illuminate\Database\Eloquent\Builder;
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
    use HandlesDataExport;
    use SyncsStaffAssignment;
    public function index(Request $request): Response
    {
        $this->ensureCanManageUsers();

        $search = trim((string) $request->query('search', ''));
        $allowedPerPage = [5, 10, 25, 50, 100];
        $perPage = $request->integer('per_page', 5);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 5;
        }
        $sort = $this->resolveSort($request);
        $direction = $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        $query = User::query()->with([
            'roles:id,name',
            'permissions:id,name',
            'staff:id,first_name,last_name,email,status,user_id',
            'approver:id,name',
        ]);

        $this->applySearch($query, $search);
        $this->applySorting($query, $request);

        $users = $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_status' => $user->account_status,
                    'account_type' => $user->account_type,
                    'approved_at' => optional($user->approved_at)->toIso8601String(),
                    'approved_by' => $user->approver?->name,
                    'is_pending' => $user->account_status === User::STATUS_PENDING,
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
        $pendingCount = User::where('account_status', User::STATUS_PENDING)->count();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'stats' => [
                [
                    'label' => 'Total Users',
                    'value' => $users->total(),
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
                [
                    'label' => 'Pending Approval',
                    'value' => $pendingCount,
                    'tone' => 'primary',
                ],
            ],
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'can' => [
                'create' => $request->user()->can('users.manage'),
                'edit' => $request->user()->can('users.manage'),
                'delete' => $request->user()->can('users.manage'),
                'impersonate' => $request->user()->can('users.impersonate'),
            ],
            'breadcrumbs' => [
                ['title' => 'Users', 'href' => route('users.index')],
            ],
            'print' => (bool) $request->boolean('print'),
        ]);
    }

    public function export(Request $request)
    {
        $this->ensureCanManageUsers();

        return $this->handleExport($request, User::class, ExportConfig::users(), [
            'label' => 'User Roster',
            'type' => 'users',
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

    public function show(Request $request, User $user): Response
    {
        $this->ensureCanManageUsers();

        $user->load([
            'roles:id,name',
            'permissions:id,name',
            'staff:id,first_name,last_name,status,user_id',
            'approver:id,name',
        ]);

        $activity = $user->activityLogs()
            ->with('causer')
            ->latest()
            ->take(20)
            ->get();

        return Inertia::render('Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'account_status' => $user->account_status,
                'account_type' => $user->account_type,
                'approved_at' => optional($user->approved_at)->toIso8601String(),
                'approved_by' => $user->approver?->name,
                'roles' => $user->roles->pluck('name')->values(),
                'permissions' => $user->getAllPermissions()->pluck('name')->values(),
                'has_two_factor' => ! is_null($user->two_factor_secret),
                'staff' => $user->staff ? [
                    'id' => $user->staff->id,
                    'full_name' => $user->staff->full_name,
                    'status' => $user->staff->status,
                ] : null,
                'created_at' => optional($user->created_at)->toDateTimeString(),
                'updated_at' => optional($user->updated_at)->toDateTimeString(),
            ],
            'activity' => ActivityLogResource::collection($activity),
            'breadcrumbs' => [
                ['title' => 'Users', 'href' => route('users.index')],
                ['title' => $user->name, 'href' => route('users.show', $user)],
            ],
            'print' => (bool) $request->boolean('print'),
        ]);
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        $this->ensureCanManageUsers();

        DB::transaction(function () use ($request) {
            $data = $request->validated();
            $status = $data['account_status'];
            $accountType = $data['account_type'];
            $isActive = $status === User::STATUS_ACTIVE;

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
                'account_status' => $status,
                'account_type' => $accountType,
                'approved_at' => $isActive ? now() : null,
                'approved_by' => $isActive ? $request->user()->id : null,
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

        $user->load(['roles:id,name', 'permissions:id,name', 'staff:id', 'approver:id,name']);

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
                'account_status' => $user->account_status,
                'account_type' => $user->account_type,
                'approved_at' => optional($user->approved_at)->toIso8601String(),
                'approved_by' => $user->approver?->name,
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
        $oldStatus = $user->account_status;
        $oldType = $user->account_type;
        $oldApprovedAt = $user->approved_at;

        $newRoles = $oldRoles;
        $newPermissions = $oldPermissions;
        $newStatus = $oldStatus;
        $newType = $oldType;
        $newApprovedAt = $oldApprovedAt;

        DB::transaction(function () use ($request, $user, &$newRoles, &$newPermissions, &$newStatus, &$newType, &$newApprovedAt) {
            $data = $request->validated();
            $status = $data['account_status'];
            $accountType = $data['account_type'];

            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'account_status' => $status,
                'account_type' => $accountType,
            ];

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            if ($user->account_status !== $status) {
                if ($status === User::STATUS_ACTIVE) {
                    $payload['approved_at'] = now();
                    $payload['approved_by'] = $request->user()->id;
                } else {
                    $payload['approved_at'] = null;
                    $payload['approved_by'] = null;
                }
            }

            $user->update($payload);

            $user->syncRoles($data['roles'] ?? []);
            $user->syncPermissions($data['permissions'] ?? []);

            $this->syncStaffAssignment($user, $data['staff_id'] ?? null);

            $user->refresh()->load('roles');
            $newRoles = $user->roles->pluck('name')->sort()->values()->toArray();
            $newPermissions = $user->getAllPermissions()->pluck('name')->sort()->values()->toArray();
            $newStatus = $user->account_status;
            $newType = $user->account_type;
            $newApprovedAt = $user->approved_at;
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

        if ($oldStatus !== $newStatus || $oldType !== $newType) {
            ActivityLog::record(
                auth()->id(),
                $user->fresh(),
                'user.account_status.updated',
                'Account status updated',
                [
                    'before' => [
                        'status' => $oldStatus,
                        'type' => $oldType,
                        'approved_at' => optional($oldApprovedAt)->toDateTimeString(),
                    ],
                    'after' => [
                        'status' => $newStatus,
                        'type' => $newType,
                        'approved_at' => optional($newApprovedAt)->toDateTimeString(),
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

    protected function applySearch(Builder $query, ?string $search): void
    {
        $term = trim((string) $search);

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($term) {
            $builder
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    protected function applySorting(Builder $query, Request $request): void
    {
        $sort = $this->resolveSort($request);
        $direction = $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if ($sort) {
            $query->orderBy($sort, $direction);

            return;
        }

        $query->orderBy('name');
    }

    protected function resolveSort(Request $request): ?string
    {
        $sort = $request->query('sort');
        $sortable = ['name', 'email', 'created_at'];

        return in_array($sort, $sortable, true) ? $sort : null;
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

    protected function ensureCanManageUsers(): void
    {
        abort_unless(auth()->user()?->can('users.manage'), 403);
    }
}
