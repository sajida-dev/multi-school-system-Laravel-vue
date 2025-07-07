<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::resource('roles', App\Http\Controllers\Admin\RoleController::class)->except(['create', 'edit', 'show']);
    Route::resource('permissions', App\Http\Controllers\Admin\PermissionController::class)->except(['create', 'edit', 'show']);
    Route::get('users-roles', [App\Http\Controllers\Admin\UserRoleController::class, 'index']);
    Route::post('users-roles', [App\Http\Controllers\Admin\UserRoleController::class, 'store']);
    Route::delete('users-roles', [App\Http\Controllers\Admin\UserRoleController::class, 'destroy']);
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
    Route::get('/roles', [\App\Http\Controllers\Admin\UserController::class, 'roles']);
    Route::post('/users/{id}/roles', [\App\Http\Controllers\Admin\UserController::class, 'assignRoles']);
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
