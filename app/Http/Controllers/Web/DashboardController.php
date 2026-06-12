<?php

namespace App\Http\Controllers\Web;

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
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super-admin');

        if ($isSuperAdmin) {
            $moduleIds = collect();
            $totalFeatures = Feature::count();
            $totalModules = Module::count();
        } else {
            $moduleIds = Module::whereHas('rolePermissions', function ($q) use ($user) {
                $q->whereIn('role_id', $user->roles()->pluck('roles.id'))
                  ->where('can_read', true);
            })->pluck('id');
            $totalFeatures = Feature::whereHas('modules', fn($q) => $q->whereIn('id', $moduleIds))->count();
            $totalModules = $moduleIds->count();
        }

        $taskQuery = Task::query();
        if (!$isSuperAdmin) {
            $taskQuery->where(function ($q) use ($moduleIds, $user) {
                if ($moduleIds->isNotEmpty()) {
                    $q->whereIn('module_id', $moduleIds);
                }
                $q->orWhereHas('assignees', fn($a) => $a->where('user_id', $user->id));
            });
        }
        $totalTasks = (clone $taskQuery)->count();
        $tasksByStatus = (clone $taskQuery)
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalNotes = Note::count();
        $myNotes = Note::where('user_id', $user->id)->count();
        $unreadNotifications = $user->unreadNotifications()->count();
        $totalUsers = $isSuperAdmin ? User::count() : null;

        $serviceModels = [
            'Domains' => Domain::class, 'Hostings' => Hosting::class, 'VPS' => Vps::class,
            'VoIP' => Voip::class, 'Service Providers' => ServiceProvider::class,
            'Domain Emails' => DomainEmail::class, 'Other Services' => OtherService::class,
            'Expiry Trackers' => ExpiryTracker::class,
        ];

        $totalServices = 0;
        $servicesByType = [];
        $expiringSoon = 0;
        $upcomingExpiries = [];
        $today = Carbon::today();
        $thirtyDays = Carbon::today()->addDays(30);

        foreach ($serviceModels as $label => $modelClass) {
            $activeQuery = $modelClass::where('status', 'active');
            if (!$isSuperAdmin) {
                $activeQuery->where('user_id', $user->id);
            }
            $activeCount = (clone $activeQuery)->count();
            $totalServices += $activeCount;
            $servicesByType[$label] = $activeCount;

            $expiring = (clone $activeQuery)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', $today)
                ->whereDate('expiry_date', '<=', $thirtyDays)
                ->count();
            $expiringSoon += $expiring;

            $closest = (clone $activeQuery)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', $today)
                ->orderBy('expiry_date')
                ->take(5)
                ->get(['name', 'expiry_date'])
                ->map(fn($i) => ['name' => $i->name, 'expiry' => Carbon::parse($i->expiry_date)->format('M d')]);
            if ($closest->isNotEmpty()) {
                $upcomingExpiries[$label] = $closest;
            }
        }

        $activityQuery = Activity::with('causer');
        if (!$isSuperAdmin) {
            $activityQuery->where('causer_id', $user->id);
        }
        $recentActivity = $activityQuery->latest()->take(10)->get();

        return view('dashboard.index', compact(
            'totalFeatures', 'totalModules', 'totalTasks', 'tasksByStatus',
            'totalNotes', 'myNotes', 'unreadNotifications', 'totalUsers',
            'totalServices', 'servicesByType', 'expiringSoon', 'upcomingExpiries',
            'recentActivity',
        ));
    }
}
