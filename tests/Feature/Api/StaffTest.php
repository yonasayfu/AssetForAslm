<?php

use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function createApiStaffUser(array $permissions): User
{
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $user = User::factory()->withoutTwoFactor()->create();
    $user->givePermissionTo($permissions);

    return $user;
}

it('lists staff records', function () {
    $admin = createApiStaffUser(['staff.view']);
    $staff = Staff::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/staff');

    $response->assertOk();
    $response->assertJsonFragment([
        'email' => $staff->email,
    ]);
});

it('creates a staff record', function () {
    $admin = createApiStaffUser(['staff.view', 'staff.create']);

    Sanctum::actingAs($admin);

    $payload = [
        'first_name' => 'Jamie',
        'last_name' => 'Rivera',
        'email' => 'jamie.rivera@example.com',
        'status' => 'active',
    ];

    $response = $this->postJson('/api/v1/staff', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.email', 'jamie.rivera@example.com');

    $this->assertDatabaseHas('staff', [
        'email' => 'jamie.rivera@example.com',
    ]);
});

it('updates a staff record and clears the avatar', function () {
    Storage::fake('public');

    $admin = createApiStaffUser(['staff.view', 'staff.update']);
    $staff = Staff::factory()->create([
        'avatar_path' => 'staff/avatars/original.png',
    ]);

    Storage::disk('public')->put('staff/avatars/original.png', 'avatar-contents');

    Sanctum::actingAs($admin);

    $payload = [
        'first_name' => 'Jordan',
        'last_name' => 'Lee',
        'email' => $staff->email,
        'status' => 'inactive',
        'remove_avatar' => true,
    ];

    $response = $this->putJson("/api/v1/staff/{$staff->id}", $payload);

    $response->assertOk()
        ->assertJsonPath('data.first_name', 'Jordan')
        ->assertJsonPath('data.status', 'inactive')
        ->assertJsonPath('data.avatar_url', null);

    $this->assertDatabaseHas('staff', [
        'id' => $staff->id,
        'first_name' => 'Jordan',
        'avatar_path' => null,
    ]);

    Storage::disk('public')->assertMissing('staff/avatars/original.png');
});

it('deletes a staff record', function () {
    $admin = createApiStaffUser(['staff.view', 'staff.delete']);
    $staff = Staff::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->deleteJson("/api/v1/staff/{$staff->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('staff', [
        'id' => $staff->id,
    ]);
});
