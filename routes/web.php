<?php

use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\RoleManagementController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('global-search', GlobalSearchController::class)->name('global-search');
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('staff', [StaffController::class, 'index'])
        ->name('staff.index')
        ->middleware('permission:staff.view');

    Route::get('staff/create', [StaffController::class, 'create'])
        ->name('staff.create')
        ->middleware('permission:staff.create');

    Route::post('staff', [StaffController::class, 'store'])
        ->name('staff.store')
        ->middleware('permission:staff.create');

    Route::get('staff/{staff}/edit', [StaffController::class, 'edit'])
        ->name('staff.edit')
        ->middleware('permission:staff.update');

    Route::put('staff/{staff}', [StaffController::class, 'update'])
        ->name('staff.update')
        ->middleware('permission:staff.update');

    Route::delete('staff/{staff}', [StaffController::class, 'destroy'])
        ->name('staff.destroy')
        ->middleware('permission:staff.delete');

    Route::middleware('permission:users.manage')->group(function () {
        Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('permission:roles.manage|users.manage')->group(function () {
        Route::resource('roles', RoleManagementController::class)->only([
            'index',
            'create',
            'store',
            'edit',
            'update',
            'destroy',
        ]);
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';


