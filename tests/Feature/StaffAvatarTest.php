<?php

use App\Models\Staff;
use App\Models\User;
use App\Support\Storage\StoragePath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('stores an uploaded avatar when creating a staff member', function () {
    Storage::fake('public');

    Permission::firstOrCreate(['name' => 'staff.create']);

    $user = User::factory()->create();
    $user->givePermissionTo('staff.create');

    actingAs($user);

    $response = $this->post('/staff', [
        'first_name' => 'Alex',
        'last_name' => 'Armstrong',
        'email' => 'alex@example.com',
        'phone' => '555-555-5555',
        'job_title' => 'Technician',
        'status' => 'active',
        'hire_date' => now()->toDateString(),
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ]);

    $response->assertRedirect(route('staff.index'));

    $staff = Staff::where('email', 'alex@example.com')->first();

    expect($staff)->not->toBeNull();
    expect($staff->avatar_path)->not->toBeNull();

    Storage::disk('public')->assertExists($staff->avatar_path);
});

it('removes an existing avatar when requested during update', function () {
    Storage::fake('public');

    Permission::firstOrCreate(['name' => 'staff.update']);

    $user = User::factory()->create();
    $user->givePermissionTo('staff.update');

    $existingPath = UploadedFile::fake()
        ->image('existing.jpg')
        ->store(StoragePath::staffAvatars(), 'public');

    $staff = Staff::factory()->create([
        'avatar_path' => $existingPath,
    ]);

    actingAs($user);

    $response = $this->put("/staff/{$staff->id}", [
        'first_name' => $staff->first_name,
        'last_name' => $staff->last_name,
        'email' => $staff->email,
        'phone' => $staff->phone,
        'job_title' => $staff->job_title,
        'status' => $staff->status,
        'hire_date' => optional($staff->hire_date)->toDateString(),
        'user_id' => $staff->user_id,
        'remove_avatar' => true,
    ]);

    $response->assertRedirect(route('staff.index'));

    $staff->refresh();

    expect($staff->avatar_path)->toBeNull();
    Storage::disk('public')->assertMissing($existingPath);
});

