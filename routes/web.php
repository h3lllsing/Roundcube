<?php

use App\Http\Controllers\Web\ActivityLogController;
use App\Http\Controllers\Web\AssetController;
use App\Http\Controllers\Web\AttachmentController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BulkActionController;
use App\Http\Controllers\Web\CalendarController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DomainController;
use App\Http\Controllers\Web\MonitoringOverviewController;
use App\Http\Controllers\Web\DomainEmailController;
use App\Http\Controllers\Web\ExpiryTrackerController;
use App\Http\Controllers\Web\ExportController;
use App\Http\Controllers\Web\FeatureController;
use App\Http\Controllers\Web\GMailController;
use App\Http\Controllers\Web\HostingController;
use App\Http\Controllers\Web\ImportController;
use App\Http\Controllers\Web\LoginAuditController;
use App\Http\Controllers\Web\ModuleController;
use App\Http\Controllers\Web\ModulePermissionController;
use App\Http\Controllers\Web\MonitorController;
use App\Http\Controllers\Web\NoteController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\OtherServiceController;
use App\Http\Controllers\Web\PrivilegeController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\RoleTemplateController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\ServiceProviderController;
use App\Http\Controllers\Web\SmtpProfileController;
use App\Http\Controllers\Web\TaskController;
use App\Http\Controllers\Web\TokenController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\VaultController;
use App\Http\Controllers\Web\VoipController;
use App\Http\Controllers\Web\VpsController;
use App\Http\Controllers\Web\WebhookController;
use App\Http\Controllers\HelpController;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

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

    Route::get('/my-tasks', [TaskController::class, 'myTasks'])->name('tasks.my');
    Route::get('/my/tasks/counts', [TaskController::class, 'myTaskCounts'])->name('tasks.my-counts');

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{id}', [TaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{id}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('/tasks/{id}/restore', [TaskController::class, 'restore'])->name('tasks.restore');

    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
    Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
    Route::get('/assets/{id}', [AssetController::class, 'show'])->name('assets.show');
    Route::get('/assets/{id}/edit', [AssetController::class, 'edit'])->name('assets.edit');
    Route::put('/assets/{id}', [AssetController::class, 'update'])->name('assets.update');
    Route::delete('/assets/{id}', [AssetController::class, 'destroy'])->name('assets.destroy');
    Route::patch('/assets/{id}/restore', [AssetController::class, 'restore'])->name('assets.restore');
    Route::delete('/assets/{id}/force-delete', [AssetController::class, 'forceDelete'])->name('assets.force-delete');
    Route::post('/assets/{id}/assign', [AssetController::class, 'assign'])->name('assets.assign');
    Route::post('/assets/{id}/return', [AssetController::class, 'returnAsset'])->name('assets.return');

    Route::get('/domains', [DomainController::class, 'index'])->name('domains.index');
    Route::get('/domains/create', [DomainController::class, 'create'])->name('domains.create');
    Route::post('/domains', [DomainController::class, 'store'])->name('domains.store');
    Route::get('/domains/{id}', [DomainController::class, 'show'])->name('domains.show');
    Route::get('/domains/{id}/edit', [DomainController::class, 'edit'])->name('domains.edit');
    Route::put('/domains/{id}', [DomainController::class, 'update'])->name('domains.update');
    Route::delete('/domains/{id}', [DomainController::class, 'destroy'])->name('domains.destroy');
    Route::patch('/domains/{id}/restore', [DomainController::class, 'restore'])->name('domains.restore');
    Route::delete('/domains/{id}/force-delete', [DomainController::class, 'forceDelete'])->name('domains.force-delete');

    Route::get('/vps', [VpsController::class, 'index'])->name('vps.index');
    Route::get('/vps/create', [VpsController::class, 'create'])->name('vps.create');
    Route::post('/vps', [VpsController::class, 'store'])->name('vps.store');
    Route::get('/vps/{id}', [VpsController::class, 'show'])->name('vps.show');
    Route::get('/vps/{id}/edit', [VpsController::class, 'edit'])->name('vps.edit');
    Route::put('/vps/{id}', [VpsController::class, 'update'])->name('vps.update');
    Route::delete('/vps/{id}', [VpsController::class, 'destroy'])->name('vps.destroy');
    Route::patch('/vps/{id}/restore', [VpsController::class, 'restore'])->name('vps.restore');
    Route::delete('/vps/{id}/force-delete', [VpsController::class, 'forceDelete'])->name('vps.force-delete');
    Route::get('/vps/{id}/password', [VpsController::class, 'getPassword'])->name('vps.password')->middleware('throttle:10,1');
    Route::post('/vps/{id}/password/copy', [VpsController::class, 'logPasswordCopy'])->name('vps.password.copy')->middleware('throttle:10,1');

    Route::get('/monitor/{type}/{id}', [MonitorController::class, 'check'])->name('monitor.check');
    Route::get('/monitoring', [MonitoringOverviewController::class, 'index'])->name('monitoring.index');

    Route::get('/hostings', [HostingController::class, 'index'])->name('hostings.index');
    Route::get('/hostings/create', [HostingController::class, 'create'])->name('hostings.create');
    Route::post('/hostings', [HostingController::class, 'store'])->name('hostings.store');
    Route::get('/hostings/{id}', [HostingController::class, 'show'])->name('hostings.show');
    Route::get('/hostings/{id}/edit', [HostingController::class, 'edit'])->name('hostings.edit');
    Route::put('/hostings/{id}', [HostingController::class, 'update'])->name('hostings.update');
    Route::delete('/hostings/{id}', [HostingController::class, 'destroy'])->name('hostings.destroy');
    Route::patch('/hostings/{id}/restore', [HostingController::class, 'restore'])->name('hostings.restore');
    Route::delete('/hostings/{id}/force-delete', [HostingController::class, 'forceDelete'])->name('hostings.force-delete');
    Route::get('/hostings/{id}/password', [HostingController::class, 'getPassword'])->name('hostings.password')->middleware('throttle:10,1');
    Route::post('/hostings/{id}/password/copy', [HostingController::class, 'logPasswordCopy'])->name('hostings.password.copy')->middleware('throttle:10,1');

    Route::get('/g-mails', [GMailController::class, 'index'])->name('g-mails.index');
    Route::get('/g-mails/create', [GMailController::class, 'create'])->name('g-mails.create');
    Route::post('/g-mails', [GMailController::class, 'store'])->name('g-mails.store');
    Route::get('/g-mails/{id}', [GMailController::class, 'show'])->name('g-mails.show');
    Route::get('/g-mails/{id}/edit', [GMailController::class, 'edit'])->name('g-mails.edit');
    Route::put('/g-mails/{id}', [GMailController::class, 'update'])->name('g-mails.update');
    Route::delete('/g-mails/{id}', [GMailController::class, 'destroy'])->name('g-mails.destroy');
    Route::patch('/g-mails/{id}/restore', [GMailController::class, 'restore'])->name('g-mails.restore');
    Route::delete('/g-mails/{id}/force-delete', [GMailController::class, 'forceDelete'])->name('g-mails.force-delete');
    Route::get('/g-mails/{id}/password', [GMailController::class, 'getPassword'])->name('g-mails.password')->middleware('throttle:10,1');

    Route::get('/voip', [VoipController::class, 'index'])->name('voip.index');
    Route::get('/voip/create', [VoipController::class, 'create'])->name('voip.create');
    Route::post('/voip', [VoipController::class, 'store'])->name('voip.store');
    Route::get('/voip/{id}', [VoipController::class, 'show'])->name('voip.show');
    Route::get('/voip/{id}/edit', [VoipController::class, 'edit'])->name('voip.edit');
    Route::put('/voip/{id}', [VoipController::class, 'update'])->name('voip.update');
    Route::delete('/voip/{id}', [VoipController::class, 'destroy'])->name('voip.destroy');
    Route::patch('/voip/{id}/restore', [VoipController::class, 'restore'])->name('voip.restore');
    Route::delete('/voip/{id}/force-delete', [VoipController::class, 'forceDelete'])->name('voip.force-delete');
    Route::get('/voip/{id}/password', [VoipController::class, 'getPassword'])->name('voip.password')->middleware('throttle:10,1');
    Route::post('/voip/{id}/password/copy', [VoipController::class, 'logPasswordCopy'])->name('voip.password.copy')->middleware('throttle:10,1');
    Route::get('/voip/{id}/extension-password', [VoipController::class, 'getExtensionPassword'])->name('voip.extension-password')->middleware('throttle:10,1');
    Route::post('/voip/{id}/extension-password/copy', [VoipController::class, 'logExtensionPasswordCopy'])->name('voip.extension-password.copy')->middleware('throttle:10,1');

    Route::get('/vault', [VaultController::class, 'index'])->name('vault.index');
    Route::get('/my-vault', [VaultController::class, 'myVault'])->name('vault.my');
    Route::get('/vault/create', [VaultController::class, 'create'])->name('vault.create');
    Route::post('/vault', [VaultController::class, 'store'])->name('vault.store');
    Route::get('/vault/{id}', [VaultController::class, 'show'])->name('vault.show')->where('id', '[0-9]+');
    Route::get('/vault/{id}/edit', [VaultController::class, 'edit'])->name('vault.edit')->where('id', '[0-9]+');
    Route::put('/vault/{id}', [VaultController::class, 'update'])->name('vault.update')->where('id', '[0-9]+');
    Route::delete('/vault/{id}', [VaultController::class, 'destroy'])->name('vault.destroy')->where('id', '[0-9]+');
    Route::patch('/vault/{id}/restore', [VaultController::class, 'restore'])->name('vault.restore')->where('id', '[0-9]+');
    Route::delete('/vault/{id}/force-delete', [VaultController::class, 'forceDelete'])->name('vault.force-delete')->where('id', '[0-9]+');
    Route::post('/vault/{id}/reveal', [VaultController::class, 'reveal'])->name('vault.reveal')->where('id', '[0-9]+')->middleware('throttle:10,1');
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::get('/notes/create', [NoteController::class, 'create'])->name('notes.create');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::get('/notes/{id}', [NoteController::class, 'show'])->name('notes.show');
    Route::get('/notes/{id}/edit', [NoteController::class, 'edit'])->name('notes.edit');
    Route::put('/notes/{id}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{id}', [NoteController::class, 'destroy'])->name('notes.destroy');
    Route::patch('/notes/{id}/restore', [NoteController::class, 'restore'])->name('notes.restore');
    Route::patch('/notes/{id}/pin', [NoteController::class, 'togglePin'])->name('notes.pin');
    Route::delete('/notes/{id}/force-delete', [NoteController::class, 'forceDelete'])->name('notes.force-delete');

    Route::get('/service-providers', [ServiceProviderController::class, 'index'])->name('service-providers.index');
    Route::get('/service-providers/create', [ServiceProviderController::class, 'create'])->name('service-providers.create');
    Route::post('/service-providers', [ServiceProviderController::class, 'store'])->name('service-providers.store');
    Route::get('/service-providers/{id}', [ServiceProviderController::class, 'show'])->name('service-providers.show');
    Route::get('/service-providers/{id}/edit', [ServiceProviderController::class, 'edit'])->name('service-providers.edit');
    Route::put('/service-providers/{id}', [ServiceProviderController::class, 'update'])->name('service-providers.update');
    Route::delete('/service-providers/{id}', [ServiceProviderController::class, 'destroy'])->name('service-providers.destroy');
    Route::patch('/service-providers/{id}/restore', [ServiceProviderController::class, 'restore'])->name('service-providers.restore');
    Route::delete('/service-providers/{id}/force-delete', [ServiceProviderController::class, 'forceDelete'])->name('service-providers.force-delete');
    Route::get('/service-providers/{id}/password', [ServiceProviderController::class, 'getPassword'])->name('service-providers.password')->middleware('throttle:10,1');

    Route::resource('domain-emails', DomainEmailController::class);
    Route::get('/domain-emails/{id}/password', [DomainEmailController::class, 'getPassword'])->name('domain-emails.password')->middleware('throttle:10,1');
    Route::patch('/domain-emails/{id}/restore', [DomainEmailController::class, 'restore'])->name('domain-emails.restore');
    Route::delete('/domain-emails/{id}/force-delete', [DomainEmailController::class, 'forceDelete'])->name('domain-emails.force-delete');
    Route::resource('other-services', OtherServiceController::class);
    Route::get('/other-services/{id}/password', [OtherServiceController::class, 'getPassword'])->name('other-services.password')->middleware('throttle:10,1');
    Route::post('/other-services/{id}/password/copy', [OtherServiceController::class, 'logPasswordCopy'])->name('other-services.password.copy')->middleware('throttle:10,1');
    Route::patch('/other-services/{id}/restore', [OtherServiceController::class, 'restore'])->name('other-services.restore');
    Route::delete('/other-services/{id}/force-delete', [OtherServiceController::class, 'forceDelete'])->name('other-services.force-delete');
    Route::resource('expiry-trackers', ExpiryTrackerController::class);
    Route::patch('/expiry-trackers/{id}/restore', [ExpiryTrackerController::class, 'restore'])->name('expiry-trackers.restore');
    Route::delete('/expiry-trackers/{id}/force-delete', [ExpiryTrackerController::class, 'forceDelete'])->name('expiry-trackers.force-delete');
    Route::get('/expiry-trackers/{expiry_tracker}/preview-email', [ExpiryTrackerController::class, 'previewEmail'])->name('expiry-trackers.preview-email');
    Route::post('/expiry-trackers/{expiry_tracker}/test-email', [ExpiryTrackerController::class, 'testEmail'])->name('expiry-trackers.test-email')->middleware('throttle:10,1');
    Route::post('/expiry-trackers/{expiry_tracker}/send-reminder', [ExpiryTrackerController::class, 'sendReminderNow'])->name('expiry-trackers.send-reminder')->middleware('throttle:10,1');
    Route::get('/expiry-trackers/{expiry_tracker}/notifications', [ExpiryTrackerController::class, 'notificationHistory'])->name('expiry-trackers.notification-history');
    Route::post('/expiry-trackers/{expiry_tracker}/renew', [ExpiryTrackerController::class, 'renew'])->name('expiry-trackers.renew')->middleware('throttle:10,1');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/bulk-delete', [NotificationController::class, 'bulkDelete'])->name('notifications.bulk-delete');
    Route::post('/notifications/bulk-read', [NotificationController::class, 'bulkMarkAsRead'])->name('notifications.bulk-read');

    Route::post('/bulk-action', [BulkActionController::class, 'action'])->name('bulk-action')->middleware('throttle:bulk');

    Route::get('/guide', [HelpController::class, 'index'])->name('guide');
    Route::get('/help/search', [HelpController::class, 'search'])->name('help.search');
    Route::get('/help/module/{module}', [HelpController::class, 'moduleHelp'])->name('help.module');
    Route::get('/help/{slug}', [HelpController::class, 'show'])->name('help.show');

    Route::get('/search', [SearchController::class, 'index'])->name('search')->middleware('throttle:search');

    Route::get('/export/{type}', [ExportController::class, 'export'])->name('export')->middleware('throttle:export');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');

    Route::get('/attachments', [AttachmentController::class, 'index'])->name('attachments.index');
    Route::get('/attachments/create', [AttachmentController::class, 'create'])->name('attachments.create');
    Route::post('/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::get('/attachments/{id}', [AttachmentController::class, 'show'])->name('attachments.show');
    Route::get('/attachments/{id}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{id}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    Route::delete('/attachments/{id}/force-delete', [AttachmentController::class, 'forceDelete'])->name('attachments.force-delete');

    Route::get('/tokens', [TokenController::class, 'index'])->name('tokens.index');
    Route::get('/tokens/create', [TokenController::class, 'create'])->name('tokens.create');
    Route::post('/tokens', [TokenController::class, 'store'])->name('tokens.store');
    Route::delete('/tokens/{id}', [TokenController::class, 'destroy'])->name('tokens.destroy');

    Route::get('/', fn() => redirect()->route('dashboard'));
});

// Super-admin only routes
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

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show'])->name('activity-logs.show');

    Route::get('/module-permissions', [ModulePermissionController::class, 'index'])->name('module-permissions.index');
    Route::post('/module-permissions', [ModulePermissionController::class, 'update'])->name('module-permissions.update');
    Route::delete('/module-permissions', [ModulePermissionController::class, 'destroy'])->name('module-permissions.destroy');

    Route::resource('webhooks', WebhookController::class);
    Route::post('webhooks/{id}/test', [WebhookController::class, 'test'])->name('webhooks.test')->middleware('throttle:10,1');

    Route::get('/login-audits', [LoginAuditController::class, 'index'])->name('login-audits.index');
    Route::get('/login-audits/{id}', [LoginAuditController::class, 'show'])->name('login-audits.show');
    Route::delete('/login-audits/{id}', [LoginAuditController::class, 'destroy'])->name('login-audits.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{category}/{report}/export', [ReportController::class, 'export'])->name('reports.export')->middleware('throttle:export');
    Route::get('/reports/{category}/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{category}', [ReportController::class, 'category'])->name('reports.category');

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

    Route::get('/import', [ImportController::class, 'create'])->name('import.create');
    Route::post('/import', [ImportController::class, 'store'])->name('import.store');

    Route::view('/design-system', 'design-system')->name('design-system');

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

        Route::get('/role-templates', [RoleTemplateController::class, 'index'])->name('role-templates.index');
        Route::get('/role-templates/{id}', [RoleTemplateController::class, 'show'])->name('role-templates.show');
        Route::match(['GET', 'POST'], '/role-templates/{id}/apply', [RoleTemplateController::class, 'apply'])->name('role-templates.apply');

        Route::get('/privileges', [PrivilegeController::class, 'index'])->name('privileges.index');
        Route::get('/privileges/create', [PrivilegeController::class, 'create'])->name('privileges.create');
        Route::post('/privileges', [PrivilegeController::class, 'store'])->name('privileges.store');
        Route::get('/privileges/{id}', [PrivilegeController::class, 'show'])->name('privileges.show');
        Route::get('/privileges/{id}/edit', [PrivilegeController::class, 'edit'])->name('privileges.edit');
        Route::put('/privileges/{id}', [PrivilegeController::class, 'update'])->name('privileges.update');
        Route::delete('/privileges/{id}', [PrivilegeController::class, 'destroy'])->name('privileges.destroy');

        Route::get('/smtp-profiles', [SmtpProfileController::class, 'index'])->name('smtp-profiles.index');
        Route::get('/smtp-profiles/auto-discover', [SmtpProfileController::class, 'autoDiscover'])->name('smtp-profiles.auto-discover');
        Route::get('/smtp-profiles/create', [SmtpProfileController::class, 'create'])->name('smtp-profiles.create');
        Route::post('/smtp-profiles', [SmtpProfileController::class, 'store'])->name('smtp-profiles.store');
        Route::get('/smtp-profiles/{smtp_profile}', [SmtpProfileController::class, 'show'])->name('smtp-profiles.show');
        Route::get('/smtp-profiles/{smtp_profile}/edit', [SmtpProfileController::class, 'edit'])->name('smtp-profiles.edit');
        Route::put('/smtp-profiles/{smtp_profile}', [SmtpProfileController::class, 'update'])->name('smtp-profiles.update');
        Route::delete('/smtp-profiles/{smtp_profile}', [SmtpProfileController::class, 'destroy'])->name('smtp-profiles.destroy');
        Route::post('/smtp-profiles/{smtp_profile}/test', [SmtpProfileController::class, 'test'])->name('smtp-profiles.test');
        Route::patch('/smtp-profiles/{smtp_profile}/set-default', [SmtpProfileController::class, 'setDefault'])->name('smtp-profiles.set-default');
        Route::patch('/smtp-profiles/{smtp_profile}/toggle-active', [SmtpProfileController::class, 'toggleActive'])->name('smtp-profiles.toggle-active');
        Route::post('/smtp-profiles/{smtp_profile}/duplicate', [SmtpProfileController::class, 'duplicate'])->name('smtp-profiles.duplicate');
    });
});
