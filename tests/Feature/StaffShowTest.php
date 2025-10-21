<?php

use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('renders the staff show page', function () {
    Permission::firstOrCreate(['name' => 'staff.view']);

    $user = User::factory()->create();
    $user->givePermissionTo('staff.view');

    $staff = Staff::factory()->create();

    actingAs($user)
        ->get("/staff/{$staff->id}")
        ->assertOk();
});

it('supports print mode on staff show page', function () {
    Permission::firstOrCreate(['name' => 'staff.view']);

    $user = User::factory()->create();
    $user->givePermissionTo('staff.view');

    $staff = Staff::factory()->create();

    actingAs($user)
        ->get("/staff/{$staff->id}?print=1")
        ->assertOk();
});
