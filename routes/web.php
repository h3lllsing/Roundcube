<?php

use App\Http\Controllers\Web\ActivityLogController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DomainController;
use App\Http\Controllers\Web\EmailAccountController;
use App\Http\Controllers\Web\EmailAssignmentController;
use App\Http\Controllers\Web\LoginAuditController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\WebmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');

    Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:5,1');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update')->middleware('throttle:5,1');
});

Route::middleware(['auth', 'suspended'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])->name('verification.send');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index')->middleware('throttle:search');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/bulk-delete', [NotificationController::class, 'bulkDelete'])->name('notifications.bulk-delete')->middleware('throttle:bulk');
    Route::post('/notifications/bulk-read', [NotificationController::class, 'bulkMarkAsRead'])->name('notifications.bulk-read')->middleware('throttle:bulk');


    Route::get('domains', [DomainController::class, 'index'])->name('domains.index')->middleware('throttle:search');
    Route::get('domains/create', [DomainController::class, 'create'])->name('domains.create');
    Route::post('domains', [DomainController::class, 'store'])->name('domains.store')->middleware('throttle:import');
    Route::get('domains/{domain}', [DomainController::class, 'show'])->name('domains.show');
    Route::get('domains/{domain}/edit', [DomainController::class, 'edit'])->name('domains.edit');
    Route::put('domains/{domain}', [DomainController::class, 'update'])->name('domains.update');
    Route::delete('domains/{domain}', [DomainController::class, 'destroy'])->name('domains.destroy');
    Route::post('domains/{id}/restore', [DomainController::class, 'restore'])->name('domains.restore')->whereNumber('id');
    Route::delete('domains/{id}/force-delete', [DomainController::class, 'forceDelete'])->name('domains.force-delete')->whereNumber('id');

    Route::get('email_accounts', [EmailAccountController::class, 'index'])->name('email_accounts.index')->middleware('throttle:search');
    Route::get('email_accounts/create', [EmailAccountController::class, 'create'])->name('email_accounts.create');
    Route::post('email_accounts', [EmailAccountController::class, 'store'])->name('email_accounts.store')->middleware('throttle:import');
    Route::get('email_accounts/{email_account}', [EmailAccountController::class, 'show'])->name('email_accounts.show');
    Route::get('email_accounts/{email_account}/edit', [EmailAccountController::class, 'edit'])->name('email_accounts.edit');
    Route::put('email_accounts/{email_account}', [EmailAccountController::class, 'update'])->name('email_accounts.update');
    Route::delete('email_accounts/{email_account}', [EmailAccountController::class, 'destroy'])->name('email_accounts.destroy');
    Route::post('email-accounts/{id}/restore', [EmailAccountController::class, 'restore'])->name('email-accounts.restore')->whereNumber('id');
    Route::delete('email-accounts/{id}/force-delete', [EmailAccountController::class, 'forceDelete'])->name('email-accounts.force-delete')->whereNumber('id');
    Route::post('email_accounts/{email_account}/assign', [EmailAssignmentController::class, 'store'])->name('email_accounts.assign');
    Route::delete('email_accounts/{email_account}/assign/{user}', [EmailAssignmentController::class, 'destroy'])->name('email_accounts.assign.revoke');
    Route::get('email-accounts/auto-discover', [EmailAccountController::class, 'autoDiscover'])->name('email-accounts.auto-discover');

    Route::get('web-mail', [WebmailController::class, 'index'])->name('webmail.index');
    Route::get('web-mail/open/{email_account}', [WebmailController::class, 'redirect'])->name('webmail.open');
    Route::get('web-mail/open-as/{email_account}', [WebmailController::class, 'openAs'])->name('webmail.open_as');

    Route::get('/', fn() => redirect()->route('dashboard'));
});

// Public route - no auth required, validated by token
Route::middleware('web')->group(function () {
    Route::get('webmail-auth/resolve', [WebmailController::class, 'resolve'])->name('webmail.resolve');
});

Route::middleware(['auth', 'suspended'])->group(function () {

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index')->middleware('throttle:search');
    Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show'])->name('activity-logs.show');

    Route::get('/login-audits', [LoginAuditController::class, 'index'])->name('login-audits.index')->middleware('throttle:search');
    Route::get('/login-audits/{id}', [LoginAuditController::class, 'show'])->name('login-audits.show');
    Route::delete('/login-audits/{id}', [LoginAuditController::class, 'destroy'])->name('login-audits.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('throttle:search');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('throttle:import');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');

    Route::patch('/users/{id}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    Route::patch('/users/{id}/unsuspend', [UserController::class, 'unsuspend'])->name('users.unsuspend');

    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');

});
