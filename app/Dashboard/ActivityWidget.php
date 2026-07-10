<?php

namespace App\Dashboard;

use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class ActivityWidget
{
    public const SLUG = 'activity';

    public function cacheTtl(): int
    {
        return 60;
    }

    public function data(User $user, ?array $accessibleIds = null): array
    {
        $isSA = $user->hasRole('super-admin');

        $query = Activity::with('causer:id,name');
        if (!$isSA) {
            $query->where('causer_id', $user->id);
        }

        $activities = $query->latest()->take(10)->get()->map(fn($a) => [
            'id' => $a->id,
            'description' => $a->description,
            'event' => $a->event,
            'causer_name' => $a->causer?->name ?? 'System',
            'subject_type' => class_basename($a->subject_type),
            'subject_id' => $a->subject_id,
            'created_at' => $a->created_at?->diffForHumans(),
        ]);

        $routeMap = [
            'Feature' => 'features.show',
            'Module' => 'modules.show',
            'Task' => 'tasks.show',
            'Note' => 'notes.show',
            'VaultEntry' => 'vault.show',
            'User' => 'users.show',
            'Domain' => 'domains.show',
            'Hosting' => 'hostings.show',
            'Vps' => 'vps.show',
            'Voip' => 'voip.show',
            'ServiceProvider' => 'service-providers.show',
            'DomainEmail' => 'domain-emails.show',
            'OtherService' => 'other-services.show',
            'ExpiryTracker' => 'expiry-trackers.show',
            'Asset' => 'assets.show',
            'Attachment' => '#',
            'Webhook' => '#',
        ];

        return [
            'activity' => [
                'activities' => $activities,
                'route_map' => $routeMap,
            ],
        ];
    }
}
