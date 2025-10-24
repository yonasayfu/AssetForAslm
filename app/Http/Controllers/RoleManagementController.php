<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $roles = Role::with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(function (Role $role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name')->values()->toArray(),
                ];
            })
            ->values()
            ->toArray();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permissions' => Permission::orderBy('name')->pluck('name')->values()->toArray(),
            'breadcrumbs' => [
                ['title' => 'Roles', 'href' => route('roles.index')],
            ],
            'can' => [
                'create' => $request->user()->can('roles.manage') || $request->user()->can('users.manage'),
                'edit' => $request->user()->can('roles.manage') || $request->user()->can('users.manage'),
                'delete' => $request->user()->can('roles.manage') || $request->user()->can('users.manage'),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->ensureCanManageRoles();

        return Inertia::render('Roles/Create', [
            'permissions' => Permission::orderBy('name')->pluck('name')->values()->toArray(),
            'breadcrumbs' => [
                ['title' => 'Roles', 'href' => route('roles.index')],
                ['title' => 'Create role', 'href' => route('roles.create')],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureCanManageRoles();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLog::record(
            auth()->id(),
            $role,
            'created',
            'Role "' . $role->name . '" created',
            ['name' => $role->name, 'permissions' => $data['permissions'] ?? []]
        );

        return redirect()
            ->route('roles.index')
            ->with('bannerStyle', 'success')
            ->with('banner', 'Role created successfully.');
    }

    public function edit(Role $role): Response
    {
        $this->ensureCanManageRoles();

        $role->load('permissions:id,name');

        return Inertia::render('Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values()->toArray(),
            ],
            'permissions' => Permission::orderBy('name')->pluck('name')->values()->toArray(),
            'breadcrumbs' => [
                ['title' => 'Roles', 'href' => route('roles.index')],
                ['title' => $role->name, 'href' => route('roles.edit', $role)],
            ],
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->ensureCanManageRoles();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

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

        if (!empty($changes)) {
            ActivityLog::record(
                auth()->id(),
                $role,
                'updated',
                'Role "' . $role->name . '" updated',
                $changes
            );
        }

        return redirect()
            ->route('roles.index')
            ->with('bannerStyle', 'success')
            ->with('banner', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->ensureCanManageRoles();

        if ($role->name === 'Admin') {
            return back()
                ->with('bannerStyle', 'warning')
                ->with('banner', 'The base Admin role cannot be removed.');
        }

        $roleName = $role->name;
        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLog::record(
            auth()->id(),
            $role,
            'deleted',
            'Role "' . $roleName . '" deleted',
            ['name' => $roleName]
        );

        return redirect()
            ->route('roles.index')
            ->with('bannerStyle', 'info')
            ->with('banner', 'Role removed.');
    }

    protected function ensureCanManageRoles(): void
    {
        abort_unless(auth()->user()?->can('roles.manage') || auth()->user()?->can('users.manage'), 403);
    }
}
