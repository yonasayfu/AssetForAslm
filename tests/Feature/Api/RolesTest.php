<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function createApiRoleManager(): User
{
    Permission::firstOrCreate(['name' => 'roles.manage', 'guard_name' => 'web']);

    $user = User::factory()->withoutTwoFactor()->create();
    $user->givePermissionTo('roles.manage');

    return $user;
}

it('lists roles', function () {
    $admin = createApiRoleManager();
    Role::create(['name' => 'Quality']);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/roles');

    $response->assertOk();
    $response->assertJsonFragment([
        'name' => 'Quality',
    ]);
});

it('creates a role with permissions', function () {
    $admin = createApiRoleManager();
    $permission = Permission::firstOrCreate(['name' => 'staff.view', 'guard_name' => 'web']);

    Sanctum::actingAs($admin);

    $payload = [
        'name' => 'API Role',
        'permissions' => [$permission->name],
    ];

    $response = $this->postJson('/api/v1/roles', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'API Role')
        ->assertJsonPath('data.permissions.0', $permission->name);

    $this->assertDatabaseHas('roles', [
        'name' => 'API Role',
    ]);
});

it('updates a role', function () {
    $admin = createApiRoleManager();
    $role = Role::create(['name' => 'Support']);
    $permissionA = Permission::firstOrCreate(['name' => 'staff.view', 'guard_name' => 'web']);
    $permissionB = Permission::firstOrCreate(['name' => 'staff.update', 'guard_name' => 'web']);

    Sanctum::actingAs($admin);

    $payload = [
        'name' => 'Support Updated',
        'permissions' => [$permissionA->name, $permissionB->name],
    ];

    $response = $this->putJson("/api/v1/roles/{$role->id}", $payload);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Support Updated')
        ->assertJsonPath('data.permissions', $payload['permissions']);

    expect($role->fresh()->permissions->pluck('name')->sort()->values()->all())
        ->toEqualCanonicalizing($payload['permissions']);
});

it('prevents deleting the base admin role', function () {
    $admin = createApiRoleManager();
    $role = Role::create(['name' => 'Admin']);

    Sanctum::actingAs($admin);

    $response = $this->deleteJson("/api/v1/roles/{$role->id}");

    $response->assertStatus(422)
        ->assertJsonPath('message', 'The base Admin role cannot be removed.');
});

it('deletes a role', function () {
    $admin = createApiRoleManager();
    $role = Role::create(['name' => 'Seasonal']);

    Sanctum::actingAs($admin);

    $response = $this->deleteJson("/api/v1/roles/{$role->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('roles', [
        'id' => $role->id,
    ]);
});
