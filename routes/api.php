<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Future-proofing: API endpoints for external integrations
| (mobile apps, third-party services, etc.)
|
| Authentication: Sanctum token-based
|
*/

// Public
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login'])->middleware('throttle:5,1');

// Authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (\Illuminate\Http\Request $r) => $r->user());

    Route::get('/domains', [App\Http\Controllers\Api\DomainController::class, 'index']);
    Route::get('/domains/{domain}', [App\Http\Controllers\Api\DomainController::class, 'show']);

    Route::get('/email-accounts', [App\Http\Controllers\Api\EmailAccountController::class, 'index']);
    Route::get('/email-accounts/{email_account}', [App\Http\Controllers\Api\EmailAccountController::class, 'show']);

    Route::get('/dashboard/stats', [App\Http\Controllers\Api\DashboardController::class, 'stats']);
});
