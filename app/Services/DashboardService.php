<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Feature;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\Note;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Task;
use App\Models\User;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

class DashboardService
{
    public function compute(User $user): array
    {
        $version = Cache::get('dashboard:version', 0);
        $cacheKey = 'dashboard:'.$user->id.':v'.$version;
        $data = Cache::remember($cacheKey, 300, fn () => $this->computeDashboardData($user));

        return $data;
    }

    public function computeDashboardData(User $user): array
    {
        $isSuperAdmin = $user->hasRole('super-admin');

        $data = [];

        if ($isSuperAdmin) {
            $data['total_features'] = Feature::count();
            $data['total_modules'] = Module::count();
            $accessibleModuleIds = collect();
        } else {
            $accessibleModuleIds = Module::whereHas('rolePermissions', function ($q) use ($user) {
                $q->whereIn('role_id', $user->roles()->pluck('roles.id'))
                    ->where('can_read', true);
            })->pluck('id');
            $data['total_features'] = Feature::whereHas('modules', fn ($q) => $q->whereIn('id', $accessibleModuleIds))->count();
            $data['total_modules'] = $accessibleModuleIds->count();
            $data['accessible_module_ids'] = $accessibleModuleIds;
        }

        $taskQuery = Task::query();
        if (! $isSuperAdmin) {
            $moduleIds = $accessibleModuleIds;
            $taskQuery->where(function ($q) use ($moduleIds, $user) {
                if ($moduleIds->isNotEmpty()) {
                    $q->whereIn('module_id', $moduleIds);
                }
                $q->orWhereHas('assignees', fn ($a) => $a->where('user_id', $user->id));
            });
        }
        $data['tasks_by_status'] = (clone $taskQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        $data['total_tasks'] = array_sum($data['tasks_by_status']->toArray());

        $data['my_tasks_total'] = Task::whereHas('assignees', fn ($q) => $q->where('user_id', $user->id))->count();
        $data['my_pending_tasks'] = Task::whereHas('assignees', fn ($q) => $q->where('user_id', $user->id))
            ->where('status', '!=', 'completed')
            ->count();

        $data['total_notes'] = $isSuperAdmin ? Note::count() : Note::where('user_id', $user->id)->count();
        $data['my_notes'] = Note::where('user_id', $user->id)->count();

        $data['unread_notifications'] = $user->unreadNotifications()->count();
        $data['total_notifications'] = $user->notifications()->count();
        $data['my_vault'] = VaultEntry::where('user_id', $user->id)->count();

        $activityQuery = Activity::with('causer');
        if (! $isSuperAdmin) {
            $activityQuery->where('causer_id', $user->id);
        }
        $data['recent_activity'] = $activityQuery->latest()
            ->take(10)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'description' => $a->description,
                'event' => $a->event,
                'causer_name' => $a->causer?->name,
                'created_at' => $a->created_at,
            ]);

        $vaultQuery = Activity::where('event', 'revealed')->with('causer');
        if (! $isSuperAdmin) {
            $vaultQuery->where('causer_id', $user->id);
        }
        $data['recent_vault_reveals'] = $vaultQuery->latest()
            ->take(5)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'description' => $a->description,
                'event' => $a->event,
                'causer_name' => $a->causer?->name,
                'created_at' => $a->created_at,
            ]);

        $serviceModels = [
            'domains' => Domain::class, 'hostings' => Hosting::class, 'vps' => Vps::class,
            'voip' => Voip::class, 'service_providers' => ServiceProvider::class,
            'domain_emails' => DomainEmail::class, 'other_services' => OtherService::class,
            'expiry_trackers' => ExpiryTracker::class,
        ];

        $totalServices = 0;
        $expiringSoon = 0;
        $expiredCount = 0;
        $monthlyCost = 0;
        $servicesByType = [];
        $today = Carbon::today();
        $thirtyDays = Carbon::today()->addDays(30);

        foreach ($serviceModels as $key => $modelClass) {
            $activeQuery = $modelClass::where('status', 'active');
            $expiredQuery = $modelClass::where('status', 'expired');
            if (! $isSuperAdmin) {
                if ($accessibleModuleIds->isNotEmpty()) {
                    $activeQuery->whereIn('module_id', $accessibleModuleIds);
                    $expiredQuery->whereIn('module_id', $accessibleModuleIds);
                } else {
                    $activeQuery->whereRaw('1 = 0');
                    $expiredQuery->whereRaw('1 = 0');
                }
            }

            $stats = (clone $activeQuery)
                ->selectRaw('COALESCE(COUNT(*), 0) as active_count, COALESCE(SUM(CASE WHEN expiry_date BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as expiring_count, COALESCE(SUM(CASE WHEN expiry_date BETWEEN ? AND ? THEN cost ELSE 0 END), 0) as expiring_cost')
                ->addBinding([$today, $thirtyDays, $today, $thirtyDays], 'select')
                ->first();

            $expiredCount += (clone $expiredQuery)->count();
            $totalServices += $stats->active_count ?? 0;
            $servicesByType[$key] = $stats->active_count ?? 0;
            $expiringSoon += $stats->expiring_count ?? 0;
            $monthlyCost += $stats->expiring_cost ?? 0;
        }

        $data['total_services'] = $totalServices;
        $data['services_expiring_soon'] = $expiringSoon;
        $data['services_expired'] = $expiredCount;
        $data['total_monthly_cost'] = round($monthlyCost, 2);
        $data['services_by_type'] = $servicesByType;

        $upcoming = [];
        $typeLabels = [
            'domains' => 'Domain', 'hostings' => 'Hosting', 'vps' => 'VPS',
            'voip' => 'VoIP', 'service_providers' => 'Service Provider',
            'domain_emails' => 'Domain Email', 'other_services' => 'Other Service',
            'expiry_trackers' => 'Renewal',
        ];
        foreach ($serviceModels as $key => $modelClass) {
            $expiryQuery = $modelClass::where('status', '!=', 'expired')
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', $today);
            if (! $isSuperAdmin) {
                if ($accessibleModuleIds->isNotEmpty()) {
                    $expiryQuery->whereIn('module_id', $accessibleModuleIds);
                } else {
                    $expiryQuery->whereRaw('1 = 0');
                }
            }
            $expiryQuery->orderBy('expiry_date')
                ->take(15)
                ->get()
                ->each(function ($item) use (&$upcoming, $key, $typeLabels, $today) {
                    $upcoming[] = [
                        'type' => $key,
                        'type_label' => $typeLabels[$key],
                        'name' => $item->getAttribute('name') ?? $item->getAttribute('email') ?? 'Unnamed',
                        'expiry_date' => $item->getAttribute('expiry_date'),
                        'days_left' => $today->diffInDays(Carbon::parse($item->getAttribute('expiry_date')), false),
                        'status' => $item->getAttribute('status'),
                    ];
                });
        }
        usort($upcoming, fn ($a, $b) => $a['days_left'] <=> $b['days_left']);
        $data['upcoming_expiries'] = array_slice($upcoming, 0, 15);

        if ($isSuperAdmin) {
            $data['total_users'] = User::count();
            $data['suspended_users'] = User::whereNotNull('suspended_at')->count();
        }

        return $data;
    }
}
