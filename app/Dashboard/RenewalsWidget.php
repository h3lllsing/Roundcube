<?php

namespace App\Dashboard;

use App\Models\ExpiryTracker;
use App\Models\ExpiryTrackerNotification;
use App\Models\SmtpProfile;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RenewalsWidget
{
    public const SLUG = 'renewals';

    public function cacheTtl(): int
    {
        return 300;
    }

    public function data(User $user, ?array $accessibleIds = null): array
    {
        $isSA = $user->hasRole('super-admin');

        $trackersQuery = ExpiryTracker::query();
        if (!$isSA && $accessibleIds !== null) {
            $trackersQuery->whereIn('module_id', $accessibleIds);
        }

        $trackersByStatus = (clone $trackersQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalTrackers = array_sum($trackersByStatus->toArray());

        $upcoming = (clone $trackersQuery)
            ->whereIn('status', ['active', 'pending_renewal'])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now())
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->with('module:id,slug')
            ->orderBy('expiry_date')
            ->take(10)
            ->get()
            ->map(fn($t) => [
                'name' => $t->name,
                'expiry_date' => $t->expiry_date->format('M d, Y'),
                'days_left' => now()->startOfDay()->diffInDays($t->expiry_date, false),
                'module_slug' => $t->module?->slug,
                'status' => $t->status,
            ]);

        $notifQuery = ExpiryTrackerNotification::where('created_at', '>=', now()->subDays(30));
        if (!$isSA && $accessibleIds !== null) {
            $notifQuery->whereHas('expiryTracker', fn ($q) => $q->whereIn('module_id', $accessibleIds));
        }
        $notifStats = (clone $notifQuery)
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status='failed' THEN 1 ELSE 0 END) as failed")
            ->first();

        $manualToday = (clone $notifQuery)
            ->where('trigger_source', 'manual')
            ->whereDate('created_at', today())->count();

        $autoToday = (clone $notifQuery)
            ->where('trigger_source', 'cron')
            ->whereDate('created_at', today())->count();

        $failedToday = (clone $notifQuery)
            ->where('status', 'failed')
            ->whereDate('created_at', today())->count();

        $totalSmtpProfiles = $isSA ? SmtpProfile::count() : null;

        $renewalsExpiryDates = (clone $trackersQuery)
            ->whereIn('status', ['active', 'pending_renewal'])
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now()->startOfMonth(), now()->addMonths(6)->endOfMonth()])
            ->pluck('expiry_date');

        $renewalsExpiry = $renewalsExpiryDates
            ->groupBy(fn($d) => $d->format('M'))
            ->map(fn($dates) => $dates->count());

        return [
            'renewals' => [
                'total_trackers' => $totalTrackers,
                'trackers_by_status' => $trackersByStatus,
                'upcoming_renewals' => $upcoming,
                'notifications_sent_30d' => ($notifStats->total ?? 0) - ($notifStats->failed ?? 0),
                'notifications_failed_30d' => (int) ($notifStats->failed ?? 0),
                'manual_sends_today' => $manualToday,
                'automatic_sends_today' => $autoToday,
                'failed_today' => $failedToday,
                'total_smtp_profiles' => $totalSmtpProfiles,
                'renewals_expiry_chart' => $renewalsExpiry,
            ],
        ];
    }
}
