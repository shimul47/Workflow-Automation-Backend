<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SuperAdminController;

Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

// Auth routes (public)
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('api.register');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('api.login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('api.password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('api.password.store');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('api.logout');

    // SuperAdmin Dashboard
    Route::prefix('admin')->group(function () {
        Route::get('/stats', [SuperAdminController::class, 'stats']);
        Route::get('/menus', [SuperAdminController::class, 'getMenus']);
        Route::get('/is-superadmin', [SuperAdminController::class, 'isSuperAdmin']);
    });

    // Permissions
    Route::apiResource('permissions', PermissionController::class);

    // Roles
    Route::apiResource('roles', RoleController::class);
    Route::post('roles/{id}/assign-permissions', [RoleController::class, 'assignPermissions']);

    // Menus
    Route::apiResource('menus', MenuController::class);
    Route::get('menus-all/all/all', [MenuController::class, 'all']);
    Route::get('my-menus', [MenuController::class, 'myMenus']);
    Route::post('menus/{id}/assign-roles', [MenuController::class, 'assignRoles']);
    Route::post('menus/assign-to-role', [MenuController::class, 'assignToRole']);

    // Users
    Route::apiResource('users', UserController::class);
    Route::post('users/{id}/assign-roles', [UserController::class, 'assignRoles']);
    Route::get('users/{id}/permissions', [UserController::class, 'getPermissions']);
});
