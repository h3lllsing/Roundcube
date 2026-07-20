<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Models\EmailAccount;
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

    public function computeDashboardData(User $user, ?int $version = null): array
    {
        $version ??= Cache::get('dashboard:version', 0);
        $isSuperAdmin = $user->isSuperAdmin();

        $data = [];

        if ($isSuperAdmin) {
            $data['total_users'] = Cache::remember('dashboard:total_users:v'.$version, 300, fn () => User::count());
        }

        $data['unread_notifications'] = $user->unreadNotifications()->count();
        $data['total_notifications'] = $user->notifications()->count();

        if ($isSuperAdmin) {
            $data['failed_imap_accounts'] = app(EmailStatService::class)->failedAccountsCountLast24h();
            $data['total_email_accounts'] = Cache::remember('dashboard:total_email_accounts:v'.$version, 300, fn () => EmailAccount::count());
        }

        // Email accounts for all users
        if ($isSuperAdmin) {
            $data['assigned_accounts'] = EmailAccount::with('domain')
                ->where('status', AccountStatus::Active)
                ->orderBy('email')
                ->get();
            $data['total_assigned'] = $data['assigned_accounts']->count();
            $data['active_domains'] = $data['assigned_accounts']
                ->pluck('domain')
                ->unique('id')
                ->values();
        } else {
            $accounts = $user->assignedEmailAccounts()
                ->with('domain')
                ->where('status', AccountStatus::Active)
                ->orderBy('email')
                ->get();
            $data['assigned_accounts'] = $accounts;
            $data['total_assigned'] = $accounts->count();
            $data['active_domains'] = $accounts
                ->pluck('domain')
                ->unique('id')
                ->values();
        }

        if ($isSuperAdmin) {
            $data['audit_actions'] = Cache::remember('dashboard:audit_actions:v'.$version, 300, function () {
                $lastWeek = now()->subDays(7);
                return Activity::selectRaw('event, count(*) as c')
                    ->where('created_at', '>=', $lastWeek)
                    ->whereIn('event', ['soft_delete', 'force_delete', 'restored'])
                    ->groupBy('event')
                    ->pluck('c', 'event');
            });
        }

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
