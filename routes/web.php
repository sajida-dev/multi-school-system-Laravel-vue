<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserRoleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->prefix('admin')->group(function () {
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
});

// Add this route for the school switcher
Route::get('/admin/schools', [\Modules\Schools\Http\Controllers\SchoolsController::class, 'allWithClassesSections']);


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
