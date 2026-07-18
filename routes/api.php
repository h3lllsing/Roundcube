<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FeatureController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\ModulePermissionController;
use App\Http\Controllers\Api\MonitorController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('health', fn () => response()->json(['status' => 'ok', 'timestamp' => now()->toIso8601String()]));
Route::post('login', [AuthController::class, 'login'])->middleware(['web', 'throttle:5,1']);
Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:5,1');
Route::post('reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1');

Route::middleware(['auth:sanctum', 'suspended', 'throttle:api', 'log.api'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);

    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('api.verification.verify');
    Route::post('email/verification-notification', [AuthController::class, 'resendVerification'])->name('api.verification.send');

    Route::get('dashboard', [DashboardController::class, 'index']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread', [NotificationController::class, 'unread']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('notifications/bulk-delete', [NotificationController::class, 'bulkDelete']);
    Route::post('notifications/bulk-read', [NotificationController::class, 'bulkMarkAsRead']);
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);

    Route::get('my/module-permissions', [ModulePermissionController::class, 'userAllPermissions']);
    Route::get('modules/{module}/my-permissions', [ModulePermissionController::class, 'userPermissions']);

    Route::get('features', [FeatureController::class, 'index']);
    Route::get('features/{feature}', [FeatureController::class, 'show']);
    Route::get('features/{feature}/modules', [ModuleController::class, 'index']);
    Route::get('modules/{module}', [ModuleController::class, 'show']);

    Route::get('monitor/{type}/{id}', [MonitorController::class, 'check']);
});

Route::middleware(['auth:sanctum', 'suspended', 'throttle:api', 'log.api', 'role:super-admin'])->group(function () {
    Route::apiResource('features', FeatureController::class)->except(['index', 'show'])->names('api.features');

    Route::post('features/{feature}/modules', [ModuleController::class, 'store']);
    Route::put('modules/{module}', [ModuleController::class, 'update']);
    Route::delete('modules/{module}', [ModuleController::class, 'destroy']);

    Route::get('modules/{module}/permissions', [ModulePermissionController::class, 'index']);
    Route::post('modules/{module}/permissions', [ModulePermissionController::class, 'store']);
    Route::delete('modules/{module}/permissions/{roleId}', [ModulePermissionController::class, 'destroy']);
    Route::get('users/{user}/module-permissions', [ModulePermissionController::class, 'userAllPermissions']);

    Route::get('users', [UsersController::class, 'index']);
    Route::post('users', [UsersController::class, 'store']);
    Route::get('users/{user}', [UsersController::class, 'show']);
    Route::put('users/{user}', [UsersController::class, 'update']);
    Route::delete('users/{user}', [UsersController::class, 'destroy']);
    Route::patch('users/{user}/suspend', [UsersController::class, 'suspend']);
    Route::patch('users/{user}/unsuspend', [UsersController::class, 'unsuspend']);

});
