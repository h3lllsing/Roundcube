<?php

namespace App\Listeners;

use App\Models\Module;
use App\Models\User;
use App\Models\UserModulePermission;
use App\Notifications\AuditEventNotification;
use Spatie\Activitylog\Models\Activity;

class AuditEventListener
{
    private const CRITICAL_ACTIONS = ['assign', 'revoke', 'soft_delete', 'restored', 'force_delete'];

    public function created(Activity $activity): void
    {
        if (!in_array($activity->event, self::CRITICAL_ACTIONS, true)) {
            return;
        }

        $recipients = collect();

        $superAdmins = User::whereHas('roles', fn ($q) => $q->where('slug', 'super-admin'))->get();
        $recipients = $recipients->concat($superAdmins);

        $auditModule = Module::where('slug', 'audit')->value('id');
        if ($auditModule) {
            $viewerIds = UserModulePermission::where('module_id', $auditModule)
                ->where('can_read', true)
                ->pluck('user_id');

            $viewers = User::whereIn('id', $viewerIds)->get();
            $recipients = $recipients->concat($viewers);
        }

        $recipients = $recipients->unique('id');

        foreach ($recipients as $user) {
            $user->notify(new AuditEventNotification($activity));
        }
    }
}
