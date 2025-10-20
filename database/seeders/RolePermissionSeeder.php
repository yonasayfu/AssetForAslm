<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect([
            'staff.view',
            'staff.create',
            'staff.update',
            'staff.delete',
            'users.manage',
            'roles.manage',
        ])->map(function (string $name) {
            return Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
            );
        });

        $roles = [
            'Admin' => $permissions->pluck('name')->all(),
            'Manager' => [
                'staff.view',
                'staff.create',
                'staff.update',
            ],
            'Technician' => [
                'staff.view',
            ],
            'Staff' => [
                'staff.view',
            ],
            'Auditor' => [
                'staff.view',
            ],
            'ReadOnly' => [
                'staff.view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
            );

            $role->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
