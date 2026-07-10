<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BulkActionController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AssetController as ApiAssetController;
use App\Http\Controllers\Api\DomainController;
use App\Http\Controllers\Api\DomainEmailController;
use App\Http\Controllers\Api\ExpiryTrackerController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\FeatureController;
use App\Http\Controllers\Api\GMailController;
use App\Http\Controllers\Api\HostingController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\LoginAuditController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\ModulePermissionController;
use App\Http\Controllers\Api\MonitorController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OtherServiceController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\ServiceProviderController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\VaultController;
use App\Http\Controllers\Api\VoipController;
use App\Http\Controllers\Api\VpsController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('health', fn () => response()->json(['status' => 'ok', 'timestamp' => now()->toIso8601String()]));
Route::post('login', [AuthController::class, 'login'])->middleware(['web', 'throttle:5,1']);
Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:5,1');
Route::post('reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1');

// Authenticated
Route::middleware(['auth:sanctum', 'suspended', 'throttle:api', 'log.api'])->group(function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Profile
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);

    // Email verification
    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('api.verification.verify');
    Route::post('email/verification-notification', [AuthController::class, 'resendVerification'])->name('api.verification.send');

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Global search
    Route::get('search', [SearchController::class, 'index'])->middleware('throttle:search');

    // CSV export
    Route::get('export/{type}', [ExportController::class, 'export'])->middleware('throttle:export');

    // Bulk actions
    Route::post('bulk/{type}', [BulkActionController::class, 'action'])->middleware('throttle:bulk');

    // CSV Import
    Route::post('import/{type}', [ImportController::class, 'store'])->middleware('throttle:import');

    // Attachments
    Route::get('attachments', [AttachmentController::class, 'index']);
    Route::post('attachments', [AttachmentController::class, 'store']);
    Route::get('attachments/{attachment}', [AttachmentController::class, 'show']);
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download']);
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy']);

    // Calendar
    Route::get('calendar', [CalendarController::class, 'index']);

    // API Tokens
    Route::get('tokens', [TokenController::class, 'index']);
    Route::post('tokens', [TokenController::class, 'store']);
    Route::delete('tokens/{id}', [TokenController::class, 'destroy']);

    // Tasks — permission-based (controller checks can_read/can_create/etc per module)
    Route::get('tasks', [TaskController::class, 'index']);
    Route::post('tasks', [TaskController::class, 'store']);
    Route::get('tasks/kanban', [TaskController::class, 'kanban']);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->whereNumber('task');
    Route::get('tasks/{task}', [TaskController::class, 'show'])->whereNumber('task');
    Route::put('tasks/{task}', [TaskController::class, 'update'])->whereNumber('task');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->whereNumber('task');

    // My tasks
    Route::get('my/tasks', [TaskController::class, 'myTasks']);
    Route::get('my/tasks/counts', [TaskController::class, 'myTaskCounts']);

    // Notes — any authenticated user can create/read notes
    Route::post('features/{feature}/notes', [NoteController::class, 'storeForFeature']);
    Route::get('features/{feature}/notes', [NoteController::class, 'featureNotes']);
    Route::post('modules/{module}/notes', [NoteController::class, 'storeForModule']);
    Route::get('modules/{module}/notes', [NoteController::class, 'moduleNotes']);
    Route::post('notes', [NoteController::class, 'storeGlobal']);
    Route::get('notes', [NoteController::class, 'globalNotes']);
    Route::get('notes/{note}', [NoteController::class, 'show']);
    Route::put('notes/{note}', [NoteController::class, 'update']);
    Route::delete('notes/{note}', [NoteController::class, 'destroy']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread', [NotificationController::class, 'unread']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('notifications/bulk-delete', [NotificationController::class, 'bulkDelete']);
    Route::post('notifications/bulk-read', [NotificationController::class, 'bulkMarkAsRead']);
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);

    // My permissions
    Route::get('my/module-permissions', [ModulePermissionController::class, 'userAllPermissions']);
    Route::get('modules/{module}/my-permissions', [ModulePermissionController::class, 'userPermissions']);

    // Features & Modules — list/show for all authenticated users
    Route::get('features', [FeatureController::class, 'index']);
    Route::get('features/{feature}', [FeatureController::class, 'show']);
    Route::get('features/{feature}/modules', [ModuleController::class, 'index']);
    Route::get('modules/{module}', [ModuleController::class, 'show']);

    // Password Vault
    Route::get('my-vault', [VaultController::class, 'myVault']);
    Route::apiResource('vault', VaultController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->names('api.vault');
    Route::post('vault/{vault}/reveal', [VaultController::class, 'reveal'])->middleware('throttle:10,1');

    // Expiry Tracker
    Route::apiResource('expiry-trackers', ExpiryTrackerController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->names('api.expiry-trackers');

    // Assets
    Route::apiResource('assets', ApiAssetController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->names('api.assets');

    // G-Mails
    Route::apiResource('g-mails', GMailController::class)->parameters(['g-mails' => 'gMail'])->only(['index', 'store', 'show', 'update', 'destroy'])->names('api.g-mails');

    // Domains
    Route::apiResource('domains', DomainController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->names('api.domains');

    // Hostings
    Route::apiResource('hostings', HostingController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->names('api.hostings');

    // VPS
    Route::apiResource('vps', VpsController::class)->parameters(['vps' => 'vps'])->only(['index', 'store', 'show', 'update', 'destroy'])->names('api.vps');

    // VoIP
    Route::apiResource('voip', VoipController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->names('api.voip');

    // Service Providers
    Route::apiResource('service-providers', ServiceProviderController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->names('api.service-providers');

    // Domain Emails
    Route::apiResource('domain-emails', DomainEmailController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->names('api.domain-emails');

    // Other Services
    Route::apiResource('other-services', OtherServiceController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->names('api.other-services');

    // Monitoring
    Route::get('monitor/{type}/{id}', [MonitorController::class, 'check']);

    // Webhooks
    Route::apiResource('webhooks', WebhookController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->names('api.webhooks');
    Route::post('webhooks/{webhook}/test', [WebhookController::class, 'test']);
});

// Super-admin only
Route::middleware(['auth:sanctum', 'suspended', 'throttle:api', 'log.api', 'role:super-admin'])->group(function () {
    // Features (CRUD)
    Route::apiResource('features', FeatureController::class)->except(['index', 'show'])->names('api.features');

    // Modules (CRUD — store under feature, update/destroy single)
    Route::post('features/{feature}/modules', [ModuleController::class, 'store']);
    Route::put('modules/{module}', [ModuleController::class, 'update']);
    Route::delete('modules/{module}', [ModuleController::class, 'destroy']);

    // Module permissions (admin)
    Route::get('modules/{module}/permissions', [ModulePermissionController::class, 'index']);
    Route::post('modules/{module}/permissions', [ModulePermissionController::class, 'store']);
    Route::delete('modules/{module}/permissions/{roleId}', [ModulePermissionController::class, 'destroy']);
    Route::get('users/{user}/module-permissions', [ModulePermissionController::class, 'userAllPermissions']);

    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index']);
    Route::get('activity-logs/{activity}', [ActivityLogController::class, 'show']);

    // Users
    Route::get('users', [UsersController::class, 'index']);
    Route::post('users', [UsersController::class, 'store']);
    Route::get('users/{user}', [UsersController::class, 'show']);
    Route::put('users/{user}', [UsersController::class, 'update']);
    Route::delete('users/{user}', [UsersController::class, 'destroy']);
    Route::patch('users/{user}/suspend', [UsersController::class, 'suspend']);
    Route::patch('users/{user}/unsuspend', [UsersController::class, 'unsuspend']);

    // Login Audits
    Route::get('login-audits', [LoginAuditController::class, 'index']);
    Route::get('login-audits/{loginAudit}', [LoginAuditController::class, 'show']);
    Route::delete('login-audits/{loginAudit}', [LoginAuditController::class, 'destroy']);

    // Reports
    Route::get('reports', [ReportController::class, 'index']);
    Route::get('reports/users', [ReportController::class, 'users']);
    Route::get('reports/export', [ReportController::class, 'export'])->middleware('throttle:export');

});
