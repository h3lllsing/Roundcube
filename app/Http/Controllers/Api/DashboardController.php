<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
use App\Models\Voip;
use App\Models\Vps;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    #[OA\Get(
        path: '/dashboard',
        summary: 'Get centralized dashboard stats for the current user',
        security: [['sanctum' => []]],
        tags: ['Dashboard'],
        responses: [
            new OA\Response(response: 200, description: 'Dashboard stats', content: new OA\JsonContent(ref: '#/components/schemas/DashboardData')),
        ]
    )]
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $version = Cache::get('dashboard:version', 0);
        $cacheKey = 'dashboard:' . $user->id . ':v' . $version;
        $data = Cache::remember($cacheKey, 300, function () use ($user) {
        $isSuperAdmin = $user->hasRole('super-admin');

        $data = [];

        // Features & Modules
        if ($isSuperAdmin) {
            $data['total_features'] = Feature::count();
            $data['total_modules'] = Module::count();
        } else {
            $accessibleModuleIds = Module::whereHas('rolePermissions', function ($q) use ($user) {
                $q->whereIn('role_id', $user->roles()->pluck('roles.id'))
                  ->where('can_read', true);
            })->pluck('id');
            $data['total_features'] = Feature::whereHas('modules', fn($q) => $q->whereIn('id', $accessibleModuleIds))->count();
            $data['total_modules'] = $accessibleModuleIds->count();
            $data['accessible_module_ids'] = $accessibleModuleIds;
        }

        // Tasks
        $taskQuery = Task::query();
        if (!$isSuperAdmin) {
            $moduleIds = $data['accessible_module_ids'];
            $taskQuery->where(function ($q) use ($moduleIds, $user) {
                if ($moduleIds->isNotEmpty()) {
                    $q->whereIn('module_id', $moduleIds);
                }
                $q->orWhereHas('assignees', fn($a) => $a->where('user_id', $user->id));
            });
        }
        $data['tasks_by_status'] = (clone $taskQuery)
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status');
        $data['total_tasks'] = array_sum($data['tasks_by_status']->toArray());

        // My tasks (assigned to me)
        $data['my_tasks_total'] = Task::whereHas('assignees', fn($q) => $q->where('user_id', $user->id))->count();
        $data['my_pending_tasks'] = Task::whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
            ->where('status', '!=', 'completed')
            ->count();

        // Notes
        $data['total_notes'] = Note::count();
        $data['my_notes'] = Note::where('user_id', $user->id)->count();

        // Notifications
        $data['unread_notifications'] = $user->unreadNotifications()->count();
        $data['total_notifications'] = $user->notifications()->count();

        // Recent activity (last 10)
        $activityQuery = Activity::with('causer');
        if (!$isSuperAdmin) {
            $activityQuery->where('causer_id', $user->id);
        }
        $data['recent_activity'] = $activityQuery->latest()
            ->take(10)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'description' => $a->description,
                'event' => $a->event,
                'causer_name' => $a->causer?->getAttribute('name'),
                'created_at' => $a->created_at,
            ]);

        // Recent vault reveals (last 5)
        $vaultQuery = Activity::where('event', 'revealed')->with('causer');
        if (!$isSuperAdmin) {
            $vaultQuery->where('causer_id', $user->id);
        }
        $data['recent_vault_reveals'] = $vaultQuery->latest()
            ->take(5)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'description' => $a->description,
                'causer_name' => $a->causer?->getAttribute('name'),
                'created_at' => $a->created_at,
            ]);

        // Service aggregation (all modules with expiry_date)
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
            if (!$isSuperAdmin) {
                $activeQuery->where('user_id', $user->id);
                $expiredQuery = $modelClass::where('status', 'expired')->where('user_id', $user->id);
            } else {
                $expiredQuery = $modelClass::where('status', 'expired');
            }

            $stats = (clone $activeQuery)
                ->selectRaw('COUNT(*) as total, COALESCE(SUM(cost), 0) as total_cost')
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', $today)
                ->whereDate('expiry_date', '<=', $thirtyDays)
                ->first();

            $activeCount = (clone $activeQuery)->count();
            $expiredCount += $expiredQuery->count();

            $totalServices += $activeCount;
            $servicesByType[$key] = $activeCount;
            $expiringSoon += $stats->total ?? 0;
            $monthlyCost += $stats->total_cost ?? 0;
        }

        $data['total_services'] = $totalServices;
        $data['services_expiring_soon'] = $expiringSoon;
        $data['services_expired'] = $expiredCount;
        $data['total_monthly_cost'] = round($monthlyCost, 2);
        $data['services_by_type'] = $servicesByType;

        // Upcoming expiries timeline (next 15 items across all modules, sorted by date)
        $upcoming = [];
        $typeLabels = [
            'domains' => 'Domain', 'hostings' => 'Hosting', 'vps' => 'VPS',
            'voip' => 'VoIP', 'service_providers' => 'Service Provider',
            'domain_emails' => 'Domain Email', 'other_services' => 'Other Service',
            'expiry_trackers' => 'Expiry Tracker',
        ];
        foreach ($serviceModels as $key => $modelClass) {
            $typeLabel = $typeLabels[$key];
            $expiryQuery = $modelClass::where('status', '!=', 'expired')
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', $today);
            if (!$isSuperAdmin) {
                $expiryQuery->where('user_id', $user->id);
            }
            $expiryQuery->orderBy('expiry_date')
                ->take(15)
                ->get()
                ->each(function ($item) use (&$upcoming, $key, $typeLabel, $today) {
                    $upcoming[] = [
                        'type' => $key,
                        'type_label' => $typeLabel,
                        'name' => $item->getAttribute('name') ?? $item->getAttribute('email') ?? 'Unnamed',
                        'expiry_date' => $item->getAttribute('expiry_date'),
                        'days_left' => $today->diffInDays(Carbon::parse($item->getAttribute('expiry_date')), false),
                        'status' => $item->getAttribute('status'),
                    ];
                });
        }
        usort($upcoming, fn($a, $b) => $a['days_left'] <=> $b['days_left']);
        $data['upcoming_expiries'] = array_slice($upcoming, 0, 15);

        // Users (super-admin only)
        if ($isSuperAdmin) {
            $data['total_users'] = User::count();
        }

            return $data;
        });
        return $this->success($data);
    }
}
