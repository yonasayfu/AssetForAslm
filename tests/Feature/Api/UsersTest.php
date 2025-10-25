<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function createApiUserManager(): User
{
    Permission::firstOrCreate(['name' => 'users.manage', 'guard_name' => 'web']);

    $user = User::factory()->withoutTwoFactor()->create();
    $user->givePermissionTo('users.manage');

    return $user;
}

it('lists users for managers', function () {
    $admin = createApiUserManager();
    $other = User::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/users');

    $response->assertOk();
    $response->assertJsonFragment([
        'email' => $other->email,
    ]);
});

it('creates a user via the api', function () {
    $admin = createApiUserManager();

    Sanctum::actingAs($admin);

    $payload = [
        'name' => 'API Test User',
        'email' => 'apitest@example.com',
        'password' => 'super-secret',
        'password_confirmation' => 'super-secret',
        'account_status' => User::STATUS_ACTIVE,
        'account_type' => User::TYPE_INTERNAL,
    ];

    $response = $this->postJson('/api/v1/users', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.email', 'apitest@example.com')
        ->assertJsonPath('data.account_status', User::STATUS_ACTIVE);

    $this->assertDatabaseHas('users', [
        'email' => 'apitest@example.com',
        'account_status' => User::STATUS_ACTIVE,
        'approved_by' => $admin->id,
    ]);
});

it('updates a user via the api', function () {
    $admin = createApiUserManager();
    $subject = User::factory()->create([
        'account_status' => User::STATUS_PENDING,
        'account_type' => User::TYPE_EXTERNAL,
    ]);

    Sanctum::actingAs($admin);

    $payload = [
        'name' => 'Updated Name',
        'email' => $subject->email,
        'password' => '',
        'password_confirmation' => '',
        'account_status' => User::STATUS_SUSPENDED,
        'account_type' => User::TYPE_INTERNAL,
    ];

    $response = $this->putJson("/api/v1/users/{$subject->id}", $payload);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.account_status', User::STATUS_SUSPENDED);

    $this->assertDatabaseHas('users', [
        'id' => $subject->id,
        'name' => 'Updated Name',
        'account_status' => User::STATUS_SUSPENDED,
        'approved_at' => null,
    ]);
});

it('prevents deleting the current user account', function () {
    $admin = createApiUserManager();

    Sanctum::actingAs($admin);

    $response = $this->deleteJson("/api/v1/users/{$admin->id}");

    $response->assertStatus(422);
    $response->assertJsonPath('message', 'You cannot delete your own account.');
});

it('deletes a user via the api', function () {
    $admin = createApiUserManager();
    $subject = User::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->deleteJson("/api/v1/users/{$subject->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('users', [
        'id' => $subject->id,
    ]);
});
