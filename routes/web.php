<?php

use App\Http\Controllers\Web\ActivityLogController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DomainController;
use App\Http\Controllers\Web\FeatureController;
use App\Http\Controllers\Web\HostingController;
use App\Http\Controllers\Web\ModuleController;
use App\Http\Controllers\Web\NoteController;
use App\Http\Controllers\Web\ServiceProviderController;
use App\Http\Controllers\Web\TaskController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\VaultController;
use App\Http\Controllers\Web\VoipController;
use App\Http\Controllers\Web\VpsController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/features', [FeatureController::class, 'index'])->name('features.index');
    Route::get('/features/create', [FeatureController::class, 'create'])->name('features.create');
    Route::post('/features', [FeatureController::class, 'store'])->name('features.store');
    Route::get('/features/{id}', [FeatureController::class, 'show'])->name('features.show');
    Route::get('/features/{id}/edit', [FeatureController::class, 'edit'])->name('features.edit');
    Route::put('/features/{id}', [FeatureController::class, 'update'])->name('features.update');
    Route::delete('/features/{id}', [FeatureController::class, 'destroy'])->name('features.destroy');

    Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::get('/modules/create', [ModuleController::class, 'create'])->name('modules.create');
    Route::post('/modules', [ModuleController::class, 'store'])->name('modules.store');
    Route::get('/modules/{id}', [ModuleController::class, 'show'])->name('modules.show');
    Route::get('/modules/{id}/edit', [ModuleController::class, 'edit'])->name('modules.edit');
    Route::put('/modules/{id}', [ModuleController::class, 'update'])->name('modules.update');
    Route::delete('/modules/{id}', [ModuleController::class, 'destroy'])->name('modules.destroy');

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{id}', [TaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{id}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::get('/domains', [DomainController::class, 'index'])->name('domains.index');
    Route::get('/domains/create', [DomainController::class, 'create'])->name('domains.create');
    Route::post('/domains', [DomainController::class, 'store'])->name('domains.store');
    Route::get('/domains/{id}', [DomainController::class, 'show'])->name('domains.show');
    Route::get('/domains/{id}/edit', [DomainController::class, 'edit'])->name('domains.edit');
    Route::put('/domains/{id}', [DomainController::class, 'update'])->name('domains.update');
    Route::delete('/domains/{id}', [DomainController::class, 'destroy'])->name('domains.destroy');

    Route::get('/vps', [VpsController::class, 'index'])->name('vps.index');
    Route::get('/vps/create', [VpsController::class, 'create'])->name('vps.create');
    Route::post('/vps', [VpsController::class, 'store'])->name('vps.store');
    Route::get('/vps/{id}', [VpsController::class, 'show'])->name('vps.show');
    Route::get('/vps/{id}/edit', [VpsController::class, 'edit'])->name('vps.edit');
    Route::put('/vps/{id}', [VpsController::class, 'update'])->name('vps.update');
    Route::delete('/vps/{id}', [VpsController::class, 'destroy'])->name('vps.destroy');

    Route::get('/hostings', [HostingController::class, 'index'])->name('hostings.index');
    Route::get('/hostings/create', [HostingController::class, 'create'])->name('hostings.create');
    Route::post('/hostings', [HostingController::class, 'store'])->name('hostings.store');
    Route::get('/hostings/{id}', [HostingController::class, 'show'])->name('hostings.show');
    Route::get('/hostings/{id}/edit', [HostingController::class, 'edit'])->name('hostings.edit');
    Route::put('/hostings/{id}', [HostingController::class, 'update'])->name('hostings.update');
    Route::delete('/hostings/{id}', [HostingController::class, 'destroy'])->name('hostings.destroy');

    Route::get('/voip', [VoipController::class, 'index'])->name('voip.index');
    Route::get('/voip/create', [VoipController::class, 'create'])->name('voip.create');
    Route::post('/voip', [VoipController::class, 'store'])->name('voip.store');
    Route::get('/voip/{id}', [VoipController::class, 'show'])->name('voip.show');
    Route::get('/voip/{id}/edit', [VoipController::class, 'edit'])->name('voip.edit');
    Route::put('/voip/{id}', [VoipController::class, 'update'])->name('voip.update');
    Route::delete('/voip/{id}', [VoipController::class, 'destroy'])->name('voip.destroy');

    Route::get('/vault', [VaultController::class, 'index'])->name('vault.index');
    Route::get('/vault/create', [VaultController::class, 'create'])->name('vault.create');
    Route::post('/vault', [VaultController::class, 'store'])->name('vault.store');
    Route::get('/vault/{id}', [VaultController::class, 'show'])->name('vault.show');
    Route::get('/vault/{id}/edit', [VaultController::class, 'edit'])->name('vault.edit');
    Route::put('/vault/{id}', [VaultController::class, 'update'])->name('vault.update');
    Route::delete('/vault/{id}', [VaultController::class, 'destroy'])->name('vault.destroy');
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::get('/notes/create', [NoteController::class, 'create'])->name('notes.create');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::get('/notes/{id}', [NoteController::class, 'show'])->name('notes.show');
    Route::get('/notes/{id}/edit', [NoteController::class, 'edit'])->name('notes.edit');
    Route::put('/notes/{id}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{id}', [NoteController::class, 'destroy'])->name('notes.destroy');

    Route::get('/service-providers', [ServiceProviderController::class, 'index'])->name('service-providers.index');
    Route::get('/service-providers/create', [ServiceProviderController::class, 'create'])->name('service-providers.create');
    Route::post('/service-providers', [ServiceProviderController::class, 'store'])->name('service-providers.store');
    Route::get('/service-providers/{id}', [ServiceProviderController::class, 'show'])->name('service-providers.show');
    Route::get('/service-providers/{id}/edit', [ServiceProviderController::class, 'edit'])->name('service-providers.edit');
    Route::put('/service-providers/{id}', [ServiceProviderController::class, 'update'])->name('service-providers.update');
    Route::delete('/service-providers/{id}', [ServiceProviderController::class, 'destroy'])->name('service-providers.destroy');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::redirect('/', '/dashboard');
});
