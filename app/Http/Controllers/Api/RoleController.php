<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Resources\Api\RoleResource;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->ensureCanManageRoles($request);

        $perPage = $this->resolvePerPage($request);
        $search = trim((string) $request->query('search', ''));

        $rolesQuery = Role::query()
            ->with('permissions:id,name')
            ->orderBy('name');

        if ($search !== '') {
            $rolesQuery->where('name', 'like', "%{$search}%");
        }

        $roles = $rolesQuery
            ->paginate($perPage)
            ->withQueryString();

        return RoleResource::collection($roles);
    }

    public function store(RoleStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($data['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLog::record(
            $request->user()?->id,
            $role,
            'created',
            'Role "' . $role->name . '" created',
            [
                'name' => $role->name,
                'permissions' => $data['permissions'] ?? [],
            ]
        );

        $role->load('permissions:id,name');

        return RoleResource::make($role)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, Role $role): RoleResource
    {
        $this->ensureCanManageRoles($request);

        $role->load('permissions:id,name');

        return RoleResource::make($role);
    }

    public function update(RoleUpdateRequest $request, Role $role): RoleResource
    {
        $data = $request->validated();

        $oldName = $role->name;
        $oldPermissions = $role->permissions->pluck('name')->toArray();

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $changes = [];

        if ($oldName !== $role->name) {
            $changes['name'] = ['old' => $oldName, 'new' => $role->name];
        }

        $newPermissions = $role->permissions->pluck('name')->toArray();
        if ($oldPermissions !== $newPermissions) {
            $changes['permissions'] = ['old' => $oldPermissions, 'new' => $newPermissions];
        }

        if (! empty($changes)) {
            ActivityLog::record(
                $request->user()?->id,
                $role,
                'updated',
                'Role "' . $role->name . '" updated',
                $changes
            );
        }

        $role->load('permissions:id,name');

        return RoleResource::make($role);
    }

    public function destroy(Request $request, Role $role): JsonResponse
    {
        $this->ensureCanManageRoles($request);

        if ($role->name === 'Admin') {
            return response()->json([
                'message' => 'The base Admin role cannot be removed.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $roleName = $role->name;
        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLog::record(
            $request->user()?->id,
            $role,
            'deleted',
            'Role "' . $roleName . '" deleted',
            ['name' => $roleName]
        );

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    protected function ensureCanManageRoles(Request $request): void
    {
        abort_unless(
            $request->user()?->can('roles.manage') || $request->user()?->can('users.manage'),
            Response::HTTP_FORBIDDEN
        );
    }

    protected function resolvePerPage(Request $request): int
    {
        $default = 10;
        $allowed = [5, 10, 25, 50, 100];
        $perPage = (int) $request->query('per_page', $default);

        return in_array($perPage, $allowed, true) ? $perPage : $default;
    }
}
