<?php

use App\Models\DataExport;
use App\Models\Staff;
use App\Models\User;
use App\Support\Storage\StoragePath;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('records export metadata when downloading staff CSV', function () {
    Storage::fake('local');

    Permission::firstOrCreate(['name' => 'staff.view']);

    $user = User::factory()->create();
    $user->givePermissionTo('staff.view');

    Staff::factory()->count(3)->create();

    actingAs($user);

    $response = $this->get('/staff/export');

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $export = DataExport::first();

    expect($export)->not->toBeNull();
    expect($export->user_id)->toEqual($user->id);
    Storage::disk('local')->assertExists($export->file_path);
});

it('allows owners to download and delete exports only for their entries', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $path = StoragePath::exports().'/sample.csv';
    Storage::disk('local')->put($path, 'id,name'.PHP_EOL.'1,Example');

    $export = DataExport::create([
        'user_id' => $user->id,
        'name' => 'Staff Export',
        'type' => 'staff',
        'format' => 'csv',
        'status' => DataExport::STATUS_COMPLETED,
        'file_path' => $path,
        'record_count' => 1,
        'completed_at' => now(),
    ]);

    // Owner can download.
    actingAs($user);
    $this->get("/exports/{$export->uuid}")
        ->assertOk();

    // Other users are blocked.
    actingAs($otherUser);
    $this->get("/exports/{$export->uuid}")
        ->assertForbidden();

    // Owner can delete.
    actingAs($user);
    $this->delete("/exports/{$export->uuid}")
        ->assertRedirect(route('exports.index'));

    expect(DataExport::find($export->id))->toBeNull();
    Storage::disk('local')->assertMissing($path);
});

