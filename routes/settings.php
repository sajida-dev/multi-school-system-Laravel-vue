<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');

    Route::get('settings/roles-permissions', function () {
        return Inertia::render('settings/RolesPermissions');
    })->name('roles.settings');

    // User Management routes (admin only) - Using UserManagementController
    Route::middleware('role:admin')->group(function () {
        Route::get('settings/users', [UserManagementController::class, 'index'])->name('settings.users');
        Route::post('settings/user-management/assign-role', [UserManagementController::class, 'assignRole'])->name('settings.user-management.assign-role');
        Route::delete('settings/user-management/remove-role', [UserManagementController::class, 'removeRole'])->name('settings.user-management.remove-role');
    });
});
