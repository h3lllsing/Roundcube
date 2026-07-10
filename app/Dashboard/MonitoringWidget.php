<?php

namespace App\Dashboard;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Voip;
use App\Models\Vps;

class MonitoringWidget
{
    public const SLUG = 'monitoring';

    public function cacheTtl(): int
    {
        return 300;
    }

    /** @return array<string, mixed> */
    public function data(User $user, ?array $accessibleIds = null): array
    {
        $isSA = $user->hasRole('super-admin');

        $models = [
            Domain::class => ['label' => 'Domain', 'nameCol' => 'name', 'route' => 'domains.show'],
            Hosting::class => ['label' => 'Hosting', 'nameCol' => 'name', 'route' => 'hostings.show'],
            Vps::class => ['label' => 'VPS', 'nameCol' => 'name', 'route' => 'vps.show'],
            Voip::class => ['label' => 'VoIP', 'nameCol' => 'name', 'route' => 'voip.show'],
            ServiceProvider::class => ['label' => 'Service Provider', 'nameCol' => 'name', 'route' => 'service-providers.show'],
            DomainEmail::class => ['label' => 'Domain Email', 'nameCol' => 'email', 'route' => 'domain-emails.show'],
            OtherService::class => ['label' => 'Other Service', 'nameCol' => 'name', 'route' => 'other-services.show'],
            ExpiryTracker::class => ['label' => 'Renewal', 'nameCol' => 'name', 'route' => 'expiry-trackers.show'],
        ];

        $totalMonitored = 0;
        $online = 0;
        $offline = 0;
        $unchecked = 0;
        $offlineItems = [];
        $sslExpiringItems = [];
        $cutoff = now()->addDays(30);

        foreach ($models as $modelClass => $cfg) {
            $query = $modelClass::whereNotNull('monitoring_url');
            if (!$isSA && $accessibleIds !== null) {
                $query->whereIn('module_id', $accessibleIds);
            }

            $totalMonitored += (clone $query)->count();
            $online += (clone $query)->where('last_ping_at', '>', now()->subHours(2))->count();
            $offline += (clone $query)->whereNotNull('last_ping_at')->where('last_ping_at', '<=', now()->subHours(2))->count();
            $unchecked += (clone $query)->whereNull('last_ping_at')->count();

            $records = $query->whereNotNull('last_ping_at')->where('last_ping_at', '<=', now()->subHours(2))->orderBy('last_ping_at')->take(5)->get(['id', $cfg['nameCol'], 'monitoring_url', 'last_ping_at']);
            foreach ($records as $record) {
                $offlineItems[] = (object) [
                    'type' => $cfg['label'],
                    'id' => $record->id,
                    'name' => $record->getAttribute($cfg['nameCol']),
                    'last_ping_at' => $record->last_ping_at,
                    'route' => $cfg['route'],
                ];
            }

            $sslQuery = $modelClass::whereNotNull('monitoring_url')->whereNotNull('ssl_expires_at')->where('ssl_expires_at', '<=', $cutoff);
            if (!$isSA && $accessibleIds !== null) {
                $sslQuery->whereIn('module_id', $accessibleIds);
            }
            $sslRecords = $sslQuery->orderBy('ssl_expires_at')->take(5)->get(['id', $cfg['nameCol'], 'monitoring_url', 'ssl_expires_at']);
            foreach ($sslRecords as $record) {
                $sslExpiringItems[] = (object) [
                    'type' => $cfg['label'],
                    'id' => $record->id,
                    'name' => $record->getAttribute($cfg['nameCol']),
                    'ssl_expires_at' => $record->ssl_expires_at,
                    'route' => $cfg['route'],
                ];
            }
        }

        usort($offlineItems, fn ($a, $b) => $a->last_ping_at <=> $b->last_ping_at);
        $offlineItems = array_slice($offlineItems, 0, 5);
        usort($sslExpiringItems, fn ($a, $b) => $a->ssl_expires_at <=> $b->ssl_expires_at);
        $sslExpiringItems = array_slice($sslExpiringItems, 0, 5);

        return [
            'monitoring' => [
                'total_monitored' => $totalMonitored,
                'online' => $online,
                'offline' => $offline,
                'unchecked' => $unchecked,
                'ssl_expiring_30d' => count($sslExpiringItems),
                'offline_items' => $offlineItems,
                'ssl_expiring_items' => $sslExpiringItems,
            ],
        ];
    }
}
