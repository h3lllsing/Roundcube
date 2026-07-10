<?php

namespace App\Dashboard;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Voip;
use App\Models\Vps;
use Illuminate\Support\Facades\Cache;

class OperationsWidget
{
    public const SLUG = 'operations';

    public function cacheTtl(): int
    {
        return 300;
    }

    public function data(User $user, ?array $accessibleIds = null): array
    {
        $isSA = $user->hasRole('super-admin');
        $moduleIds = $accessibleIds;

        $serviceModels = [
            'Domains' => ['class' => Domain::class],
            'Hostings' => ['class' => Hosting::class],
            'VPS' => ['class' => Vps::class],
            'VoIP' => ['class' => Voip::class],
            'Service Providers' => ['class' => ServiceProvider::class],
            'Domain Emails' => ['class' => DomainEmail::class],
            'Other Services' => ['class' => OtherService::class],
            'Renewals' => ['class' => ExpiryTracker::class],
        ];

        $totalServices = 0;
        $monthlyCost = 0.0;
        $expiringSoon = 0;
        $expiredCount = 0;
        $servicesByType = [];
        $servicesByTypeChart = [];

        foreach ($serviceModels as $label => $cfg) {
            $query = $cfg['class']::query();
            if (!$isSA && $moduleIds !== null) {
                $query->whereIn('module_id', $moduleIds);
            }

            $stats = (clone $query)->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) AS expired,
                COALESCE(SUM(CASE WHEN status = 'active' AND expiry_date IS NOT NULL THEN cost ELSE 0 END), 0) AS monthly_cost
            ")->first();

            $active = (int) ($stats->active ?? 0);
            $totalServices += $active;
            $monthlyCost += (float) ($stats->monthly_cost ?? 0);
            $expiringSoon += (clone $query)
                ->whereIn('status', ['active', 'pending_renewal'])
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', now())
                ->whereDate('expiry_date', '<=', now()->addDays(30))
                ->count();
            $expiredCount += (int) ($stats->expired ?? 0);
            $servicesByType[$label] = $active;

            if ($active > 0) {
                $servicesByTypeChart[$label] = $active;
            }
        }

        $activeProviders = (clone ServiceProvider::query())
            ->when(!$isSA && $moduleIds !== null, fn($q) => $q->whereIn('module_id', $moduleIds))
            ->where('status', 'active')
            ->count();

        $featuresCounts = $this->getFeatureModuleCounts($user, $isSA, $moduleIds);

        $upcomingExpiries = $this->getUpcomingExpiries($user, $isSA, $moduleIds);

        return [
            'operations' => [
                'total_active_services' => $totalServices,
                'services_by_type' => $servicesByType,
                'services_by_type_chart' => $servicesByTypeChart,
                'total_monthly_cost' => round($monthlyCost, 2),
                'active_providers' => $activeProviders,
                'services_expiring_30d' => $expiringSoon,
                'services_expired' => $expiredCount,
                'total_features' => $featuresCounts['features'],
                'total_modules' => $featuresCounts['modules'],
                'upcoming_expiries' => $upcomingExpiries,
            ],
        ];
    }

    private function getFeatureModuleCounts(User $user, bool $isSA, ?array $moduleIds): array
    {
        if ($isSA) {
            return [
                'features' => \App\Models\Feature::count(),
                'modules' => Module::count(),
            ];
        }

        if ($moduleIds === null || empty($moduleIds)) {
            return ['features' => 0, 'modules' => 0];
        }

        return [
            'features' => \App\Models\Feature::whereHas('modules', fn($q) => $q->whereIn('id', $moduleIds))->count(),
            'modules' => count($moduleIds),
        ];
    }

    private function getUpcomingExpiries(User $user, bool $isSA, ?array $moduleIds): array
    {
        $models = [
            'Domains' => Domain::class,
            'Hostings' => Hosting::class,
            'VPS' => Vps::class,
            'VoIP' => Voip::class,
            'Domain Emails' => DomainEmail::class,
            'Other Services' => OtherService::class,
            'Renewals' => ExpiryTracker::class,
        ];

        $today = now()->startOfDay();
        $expiries = [];

        foreach ($models as $label => $class) {
            $nameCol = $class === DomainEmail::class ? 'email' : 'name';
            $query = $class::whereIn('status', ['active', 'pending_renewal'])
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', $today)
                ->whereDate('expiry_date', '<=', $today->copy()->addDays(30));

            if (!$isSA && $moduleIds !== null) {
                $query->whereIn('module_id', $moduleIds);
            }

            $items = $query->orderBy('expiry_date')->take(5)->get();
            if ($items->isEmpty()) {
                continue;
            }

            $expiries[$label] = $items->map(fn($i) => [
                'name' => $i->getAttribute($nameCol),
                'expiry' => $i->expiry_date->format('M d'),
                'days_left' => $today->diffInDays($i->expiry_date, false),
                'status' => $i->status,
            ]);
        }

        return $expiries;
    }
}
