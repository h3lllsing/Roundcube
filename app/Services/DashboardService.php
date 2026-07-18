<?php

namespace App\Services;

use App\Models\Feature;
use App\Models\Module;
use App\Models\User;
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
            $data['total_users'] = User::count();
            $data['suspended_users'] = User::whereNotNull('suspended_at')->count();
        }

        $data['unread_notifications'] = $user->unreadNotifications()->count();
        $data['total_notifications'] = $user->notifications()->count();

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

        return $data;
    }
}
