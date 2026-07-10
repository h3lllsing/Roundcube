<?php

namespace App\Services;

use App\Helpers\ModuleCache;
use App\Models\Attachment;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Feature;
use App\Models\GMail;
use App\Models\Hosting;
use App\Models\LoginAudit;
use App\Models\Module;
use App\Models\Note;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Task;
use App\Models\User;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use App\Models\Webhook;
use App\Models\Privilege;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class BulkActionService
{
    /** @var array<string, class-string> */
    private array $types = [
        'attachments' => Attachment::class, 'domains' => Domain::class, 'features' => Feature::class,
        'g-mails' => GMail::class, 'hostings' => Hosting::class, 'modules' => Module::class, 'privileges' => Privilege::class,
        'roles' => Role::class, 'vps' => Vps::class,
        'voip' => Voip::class, 'service-providers' => ServiceProvider::class,
        'domain-emails' => DomainEmail::class, 'other-services' => OtherService::class,
        'expiry-trackers' => ExpiryTracker::class, 'login-audits' => LoginAudit::class, 'tasks' => Task::class,
        'vault' => VaultEntry::class, 'notes' => Note::class, 'users' => User::class,
        'webhooks' => Webhook::class,
    ];

    /** @var array<string, array<int, string>> */
    private array $statusOptions = [
        'g-mails' => ['active', 'inactive', 'suspended'],
        'tasks' => ['pending', 'in_progress', 'completed', 'cancelled'],
    ];

    /** @var array<string, array<int, string>> */
    private array $allowedActions = [
        'attachments' => ['delete'],
        'vault' => ['delete'],
        'notes' => ['delete'],
        'users' => ['suspend', 'unsuspend', 'delete'],
    ];

    /** @var array<string, string> */
    private array $ownerColumns = [
        'tasks' => 'created_by',
        'features' => 'created_by',
        'modules' => 'created_by',
    ];

    /** @var array<string, array{action: string, handler: non-empty-string}> */
    private array $customTypes = [
        'tokens' => ['action' => 'delete', 'handler' => 'handleTokensDelete'],
        'login-audits' => ['action' => 'delete', 'handler' => 'handleLoginAuditsDelete'],
    ];

    /**
     * @param  int[]  $ids
     * @return array{success: bool, message: string, count?: int, status_code?: int}
     */
    public function execute(string $type, string $action, array $ids, User $user, ?string $status = null): array
    {
        if (isset($this->customTypes[$type])) {
            return $this->handleCustomType($type, $action, $ids, $user);
        }

        if (! isset($this->types[$type])) {
            return ['success' => false, 'message' => 'Invalid type', 'status_code' => 404];
        }

        $allowedActionsForType = $this->allowedActions[$type] ?? ['update-status', 'delete', 'restore', 'force-delete'];
        if (! in_array($action, $allowedActionsForType, true)) {
            return ['success' => false, 'message' => 'Invalid action.', 'status_code' => 422];
        }

        $allowedStatuses = $this->statusOptions[$type] ?? ['active', 'expired', 'cancelled', 'suspended'];
        if ($action === 'update-status' && ! in_array($status, $allowedStatuses, true)) {
            return ['success' => false, 'message' => 'Invalid status.', 'status_code' => 422];
        }

        $modelClass = $this->types[$type];

        if ($type === 'roles' && $action === 'delete') {
            $protected = Role::whereIn('slug', ['admin', 'super-admin'])->whereIn('id', $ids)->pluck('id');
            if ($protected->isNotEmpty()) {
                return ['success' => false, 'message' => 'Protected roles cannot be deleted.', 'status_code' => 422];
            }
        }

        if (! $user->hasRole('super-admin')) {
            if ($type === 'users') {
                return ['success' => false, 'message' => 'Forbidden', 'status_code' => 403];
            }
            if (! $this->userHasModulePermission($user, $type, $action)) {
                $ownedIds = $this->filterOwned($modelClass, $type, $action, $ids, $user);
                if ($ownedIds === null) {
                    return ['success' => false, 'message' => 'Forbidden', 'status_code' => 403];
                }
                $ids = $ownedIds;
            }
        }

        return $this->runAction($modelClass, $type, $action, $ids, $user, $status);
    }

    /**
     * @param  int[]  $ids
     * @return array{success: bool, message: string, count?: int}
     */
    private function handleCustomType(string $type, string $action, array $ids, User $user): array
    {
        $custom = $this->customTypes[$type];
        if ($action !== $custom['action']) {
            return ['success' => false, 'message' => 'Invalid action.'];
        }

        if ($custom['handler'] === 'handleTokensDelete') {
            $count = $user->tokens()->whereIn('id', $ids)->delete();

            activity()->event('deleted')
                ->causedBy($user)
                ->withProperties(['type' => 'tokens', 'ids' => $ids, 'count' => $count])
                ->log("Bulk deleted {$count} token(s).");

            return ['success' => true, 'message' => "Deleted {$count} token(s).", 'count' => $count];
        }

        if ($custom['handler'] === 'handleLoginAuditsDelete') {
            if (! $user->hasRole('super-admin')) {
                return ['success' => false, 'message' => 'Forbidden', 'status_code' => 403];
            }
            $count = LoginAudit::whereIn('id', $ids)->delete();

            activity()->event('deleted')
                ->causedBy($user)
                ->withProperties(['type' => 'login-audits', 'ids' => $ids, 'count' => $count])
                ->log("Bulk deleted {$count} login audit record(s).");

            return ['success' => true, 'message' => "Deleted {$count} login audit record(s).", 'count' => $count];
        }

        return ['success' => false, 'message' => 'Invalid action.'];
    }

    /**
     * @param  int[]  $ids
     * @return array{success: bool, message: string, count?: int}
     */
    private function runAction(string $modelClass, string $type, string $action, array $ids, User $user, ?string $status): array
    {
        if ($action === 'delete') {
            if ($this->modelUsesSoftDeletes($modelClass)) {
                $count = DB::transaction(function () use ($modelClass, $ids) {
                    $count = 0;
                    collect($ids)->chunk(100)->each(function ($chunk) use ($modelClass, &$count) {
                        $modelClass::whereIn('id', $chunk->toArray())->each(function ($model) use (&$count) {
                            $model->delete();
                            $count++;
                        });
                    });
                    return $count;
                });
            } else {
                $count = DB::transaction(function () use ($modelClass, $ids) {
                    $count = 0;
                    collect($ids)->chunk(100)->each(function ($chunk) use ($modelClass, &$count) {
                        $count += $modelClass::whereIn('id', $chunk->toArray())->delete();
                    });
                    return $count;
                });
            }

            activity()->event('deleted')
                ->causedBy($user)
                ->withProperties(['type' => $type, 'ids' => $ids, 'count' => $count])
                ->log("Bulk deleted {$count} {$type}.");

            return ['success' => true, 'message' => "Deleted {$count} item(s).", 'count' => $count];
        }

        if ($action === 'restore') {
            $count = $modelClass::withTrashed()->whereIn('id', $ids)->restore();

            activity()->event('restored')
                ->causedBy($user)
                ->withProperties(['type' => $type, 'ids' => $ids, 'count' => $count])
                ->log("Bulk restored {$count} {$type}.");

            return ['success' => true, 'message' => "Restored {$count} item(s).", 'count' => $count];
        }

        if ($action === 'force-delete') {
            $count = DB::transaction(function () use ($modelClass, $ids) {
                $count = 0;
                collect($ids)->chunk(100)->each(function ($chunk) use ($modelClass, &$count) {
                    $count += $modelClass::withTrashed()->whereIn('id', $chunk->toArray())->forceDelete();
                });
                return $count;
            });

            activity()->event('force-deleted')
                ->causedBy($user)
                ->withProperties(['type' => $type, 'ids' => $ids, 'count' => $count])
                ->log("Bulk force-deleted {$count} {$type}.");

            return ['success' => true, 'message' => "Permanently deleted {$count} item(s).", 'count' => $count];
        }

        if ($action === 'update-status') {
            $count = $modelClass::whereIn('id', $ids)->update(['status' => $status]);

            activity()->event('updated')
                ->causedBy($user)
                ->withProperties(['type' => $type, 'ids' => $ids, 'status' => $status, 'count' => $count])
                ->log("Bulk updated status of {$count} {$type} to {$status}.");

            return ['success' => true, 'message' => "Updated {$count} item(s) to {$status}.", 'count' => $count];
        }

        if ($action === 'suspend') {
            $count = $modelClass::whereIn('id', $ids)->update(['suspended_at' => now()]);

            activity()->event('suspended')
                ->causedBy($user)
                ->withProperties(['type' => $type, 'ids' => $ids, 'count' => $count])
                ->log("Bulk suspended {$count} user(s).");

            return ['success' => true, 'message' => "Suspended {$count} user(s).", 'count' => $count];
        }

        if ($action === 'unsuspend') {
            $count = $modelClass::whereIn('id', $ids)->update(['suspended_at' => null]);

            activity()->event('unsuspended')
                ->causedBy($user)
                ->withProperties(['type' => $type, 'ids' => $ids, 'count' => $count])
                ->log("Bulk unsuspended {$count} user(s).");

            return ['success' => true, 'message' => "Unsuspended {$count} user(s).", 'count' => $count];
        }

        return ['success' => false, 'message' => 'Invalid action.'];
    }

    private function modelUsesSoftDeletes(string $modelClass): bool
    {
        return in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass));
    }

    /**
     * @param  int[]  $ids
     * @return int[]|null
     */
    private function filterOwned(string $modelClass, string $type, string $action, array $ids, User $user): ?array
    {
        $ownerColumn = $this->ownerColumns[$type] ?? 'user_id';
        $baseQuery = in_array($action, ['restore', 'force-delete'], true)
            ? $modelClass::withTrashed()
            : $modelClass::query();
        $ownedIds = $baseQuery->whereIn('id', $ids)
            ->where($ownerColumn, $user->id)
            ->pluck('id');
        if ($ownedIds->isEmpty()) {
            return null;
        }

        return $ownedIds->toArray();
    }

    private function userHasModulePermission(User $user, string $type, string $action): bool
    {
        $permission = match ($action) {
            'delete', 'force-delete', 'restore' => 'delete',
            'update-status' => 'update',
            default => null,
        };
        if ($permission === null) {
            return false;
        }

        $module = ModuleCache::findBySlug($type);

        return $module && $user->canOnModule($module, $permission);
    }
}
