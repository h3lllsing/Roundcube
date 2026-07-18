<?php

use App\Http\Controllers\Web\ActivityLogController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BulkActionController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DomainController;
use App\Http\Controllers\Web\EmailAccountController;
use App\Http\Controllers\Web\EmailAssignmentController;
use App\Http\Controllers\Web\FeatureController;
use App\Http\Controllers\Web\LoginAuditController;
use App\Http\Controllers\Web\ModuleController;
use App\Http\Controllers\Web\ModulePermissionController;
use App\Http\Controllers\Web\MonitorController;
use App\Http\Controllers\Web\MonitoringOverviewController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\PrivilegeController;
use App\Http\Controllers\Web\RoleController;
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
    Route::get('/my-permissions', [AuthController::class, 'myPermissions'])->name('my-permissions');

    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])->name('verification.send');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/features', [FeatureController::class, 'index'])->name('features.index');
    Route::get('/features/{id}', [FeatureController::class, 'show'])->name('features.show')->whereNumber('id');

    Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::get('/modules/{id}', [ModuleController::class, 'show'])->name('modules.show')->whereNumber('id');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/bulk-delete', [NotificationController::class, 'bulkDelete'])->name('notifications.bulk-delete');
    Route::post('/notifications/bulk-read', [NotificationController::class, 'bulkMarkAsRead'])->name('notifications.bulk-read');

    Route::get('/monitor/{type}/{id}', [MonitorController::class, 'check'])->name('monitor.check');
    Route::get('/monitoring', [MonitoringOverviewController::class, 'index'])->name('monitoring.index');

    Route::resource('domains', DomainController::class);
    Route::post('domains/{id}/restore', [DomainController::class, 'restore'])->name('domains.restore')->whereNumber('id');
    Route::delete('domains/{id}/force-delete', [DomainController::class, 'forceDelete'])->name('domains.force-delete')->whereNumber('id');

    Route::resource('email_accounts', EmailAccountController::class);
    Route::post('email-accounts/{id}/restore', [EmailAccountController::class, 'restore'])->name('email-accounts.restore')->whereNumber('id');
    Route::delete('email-accounts/{id}/force-delete', [EmailAccountController::class, 'forceDelete'])->name('email-accounts.force-delete')->whereNumber('id');
    Route::post('email_accounts/{email_account}/assign', [EmailAssignmentController::class, 'store'])->name('email_accounts.assign');
    Route::delete('email_accounts/{email_account}/assign/{user}', [EmailAssignmentController::class, 'destroy'])->name('email_accounts.assign.revoke');

    Route::get('webmail', [WebmailController::class, 'index'])->name('webmail.index');
    Route::get('webmail/open/{email_account}', [WebmailController::class, 'redirect'])->name('webmail.open');
    Route::get('webmail/open-as/{email_account}', [WebmailController::class, 'openAs'])->name('webmail.open_as');
    Route::get('webmail/resolve', [WebmailController::class, 'resolve'])->name('webmail.resolve');

    Route::get('/', fn() => redirect()->route('dashboard'));
});

Route::middleware(['auth', 'suspended', 'role:super-admin'])->group(function () {
    Route::get('/features/create', [FeatureController::class, 'create'])->name('features.create');
    Route::post('/features', [FeatureController::class, 'store'])->name('features.store');
    Route::get('/features/{id}/edit', [FeatureController::class, 'edit'])->name('features.edit');
    Route::put('/features/{id}', [FeatureController::class, 'update'])->name('features.update');
    Route::delete('/features/{id}', [FeatureController::class, 'destroy'])->name('features.destroy');

    Route::get('/modules/create', [ModuleController::class, 'create'])->name('modules.create');
    Route::post('/modules', [ModuleController::class, 'store'])->name('modules.store');
    Route::get('/modules/{id}/edit', [ModuleController::class, 'edit'])->name('modules.edit');
    Route::put('/modules/{id}', [ModuleController::class, 'update'])->name('modules.update');
    Route::delete('/modules/{id}', [ModuleController::class, 'destroy'])->name('modules.destroy');

    Route::post('/bulk-action', BulkActionController::class)->name('bulk-action');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show'])->name('activity-logs.show');

    Route::get('/module-permissions', [ModulePermissionController::class, 'index'])->name('module-permissions.index');
    Route::post('/module-permissions', [ModulePermissionController::class, 'update'])->name('module-permissions.update');
    Route::delete('/module-permissions', [ModulePermissionController::class, 'destroy'])->name('module-permissions.destroy');

    Route::get('/login-audits', [LoginAuditController::class, 'index'])->name('login-audits.index');
    Route::get('/login-audits/{id}', [LoginAuditController::class, 'show'])->name('login-audits.show');
    Route::delete('/login-audits/{id}', [LoginAuditController::class, 'destroy'])->name('login-audits.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::get('/users/{id}/permissions', [UserController::class, 'editPermissions'])->name('users.permissions.edit');
    Route::put('/users/{id}/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions.update');
    Route::patch('/users/{id}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    Route::patch('/users/{id}/unsuspend', [UserController::class, 'unsuspend'])->name('users.unsuspend');
    Route::get('/users/{id}/clone', [UserController::class, 'cloneForm'])->name('users.clone');
    Route::post('/users/{id}/clone', [UserController::class, 'cloneStore'])->name('users.clone.store');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');

    // Route::view('/design-system', 'design-system')->name('design-system');

    Route::prefix('admin')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
        Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::post('/roles/{id}/privileges/attach', [RoleController::class, 'attachPrivilege'])->name('roles.privileges.attach');
        Route::post('/roles/{id}/privileges/detach', [RoleController::class, 'detachPrivilege'])->name('roles.privileges.detach');

        Route::get('/privileges', [PrivilegeController::class, 'index'])->name('privileges.index');
        Route::get('/privileges/create', [PrivilegeController::class, 'create'])->name('privileges.create');
        Route::post('/privileges', [PrivilegeController::class, 'store'])->name('privileges.store');
        Route::get('/privileges/{id}', [PrivilegeController::class, 'show'])->name('privileges.show');
        Route::get('/privileges/{id}/edit', [PrivilegeController::class, 'edit'])->name('privileges.edit');
        Route::put('/privileges/{id}', [PrivilegeController::class, 'update'])->name('privileges.update');
        Route::delete('/privileges/{id}', [PrivilegeController::class, 'destroy'])->name('privileges.destroy');
    });
});
