<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Support\Users\SyncsStaffAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    use SyncsStaffAssignment;

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->ensureCanManageUsers($request);

        $perPage = $this->resolvePerPage($request);
        $search = trim((string) $request->query('search', ''));
        $sort = $this->resolveSort($request);
        $direction = $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        $query = User::query()->with([
            'roles:id,name',
            'permissions:id,name',
            'staff:id,first_name,last_name,email,status,user_id,job_title,phone,hire_date,avatar_path',
            'approver:id,name',
        ]);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($sort) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name');
        }

        $users = $query
            ->paginate($perPage)
            ->withQueryString();

        return UserResource::collection($users);
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request) {
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

            return $user;
        });

        $user->load([
            'roles:id,name',
            'permissions:id,name',
            'staff:id,first_name,last_name,email,status,user_id,job_title,phone,hire_date,avatar_path',
            'approver:id,name',
        ]);

        return UserResource::make($user)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, User $user): UserResource
    {
        $this->ensureCanManageUsers($request);

        $user->load([
            'roles:id,name',
            'permissions:id,name',
            'staff:id,first_name,last_name,email,status,user_id,job_title,phone,hire_date,avatar_path',
            'approver:id,name',
        ]);

        return UserResource::make($user);
    }

    public function update(UserUpdateRequest $request, User $user): UserResource
    {
        $oldStatus = $user->account_status;

        DB::transaction(function () use ($request, $user, $oldStatus) {
            $data = $request->validated();
            $status = $data['account_status'];

            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'account_status' => $status,
                'account_type' => $data['account_type'],
            ];

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            if ($oldStatus !== $status) {
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
        });

        $user->refresh()->load([
            'roles:id,name',
            'permissions:id,name',
            'staff:id,first_name,last_name,email,status,user_id,job_title,phone,hire_date,avatar_path',
            'approver:id,name',
        ]);

        return UserResource::make($user);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->ensureCanManageUsers($request);

        if ($request->user()->is($user)) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::transaction(function () use ($user) {
            $this->syncStaffAssignment($user, null);
            $user->delete();
        });

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    protected function ensureCanManageUsers(Request $request): void
    {
        abort_unless($request->user()?->can('users.manage'), Response::HTTP_FORBIDDEN);
    }

    protected function resolvePerPage(Request $request): int
    {
        $allowed = [5, 10, 25, 50, 100];
        $perPage = (int) $request->query('per_page', 10);

        return in_array($perPage, $allowed, true) ? $perPage : 10;
    }

    protected function resolveSort(Request $request): ?string
    {
        $sortable = ['name', 'email', 'created_at'];
        $sort = $request->query('sort');

        return in_array($sort, $sortable, true) ? $sort : null;
    }
}
