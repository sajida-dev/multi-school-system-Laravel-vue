<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\SetActiveSchoolController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Modules\Schools\Http\Controllers\SchoolsController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'set.active.school', 'team.permission'])->get('/me', function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();

    return response()->json([
        'user' => $user,
        'roles' => $user->getRoleNames(),
        'permissions' => $user->getAllPermissions(),
        'team_id' => getPermissionsTeamId(),
        'active_school_id' => session('active_school_id'),
    ]);
});

Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'set.active.school', 'team.permission'])
    ->name('dashboard');

Route::middleware(['auth', 'set.active.school', 'team.permission'])->prefix('admin')->name('admin.')->group(function () {

    // Simple explicit routes instead of resource routes to avoid model binding conflicts
    Route::get('roles', [RoleController::class, 'index']);
    Route::post('roles', [RoleController::class, 'store']);
    Route::put('roles/{id}', [RoleController::class, 'update']);
    Route::delete('roles/{id}', [RoleController::class, 'destroy']);

    Route::get('permissions', [PermissionController::class, 'index']);
    Route::post('permissions', [PermissionController::class, 'store']);
    Route::put('permissions/{id}', [PermissionController::class, 'update']);
    Route::delete('permissions/{id}', [PermissionController::class, 'destroy']);

    Route::get('users-roles', [UserRoleController::class, 'index']);
    Route::post('users-roles', [UserRoleController::class, 'store']);
    Route::delete('users-roles', [UserRoleController::class, 'destroy']);

    Route::get('roles-list', [UserController::class, 'roles']);

    // Add school switcher route inside auth middleware
    Route::post('set-active-school', SetActiveSchoolController::class);
});

// Add this route for the school switcher
Route::get('/admin/schools', [SchoolsController::class, 'allWithClassesSections']);


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
