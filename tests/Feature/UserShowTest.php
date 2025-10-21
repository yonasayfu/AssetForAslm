<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('renders the user show page', function () {
    Permission::firstOrCreate(['name' => 'users.manage']);

    $admin = User::factory()->create();
    $admin->givePermissionTo('users.manage');

    $subject = User::factory()->create();

    actingAs($admin)
        ->get("/users/{$subject->id}")
        ->assertOk();
});

it('supports print mode on user show page', function () {
    Permission::firstOrCreate(['name' => 'users.manage']);

    $admin = User::factory()->create();
    $admin->givePermissionTo('users.manage');

    $subject = User::factory()->create();

    actingAs($admin)
        ->get("/users/{$subject->id}?print=1")
        ->assertOk();
});
