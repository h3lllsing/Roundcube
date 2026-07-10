<?php

namespace App\Dashboard;

use App\Models\ExpiryTracker;
use App\Models\SmtpProfile;
use App\Models\User;

class SmtpWidget
{
    public const SLUG = 'smtp';

    public function cacheTtl(): int
    {
        return 600;
    }

    public function data(User $user, ?array $accessibleIds = null): array
    {
        if (!$user->hasRole('super-admin')) {
            return [];
        }

        $stats = SmtpProfile::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN is_active THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN last_test_status = 'failed' THEN 1 ELSE 0 END) as failed
        ")->first();

        $usageCount = ExpiryTracker::whereNotNull('smtp_profile_id')->count();

        $statuses = SmtpProfile::where('is_active', true)
            ->get(['name', 'last_test_status', 'last_tested_at'])
            ->map(fn($p) => [
                'name' => $p->name,
                'last_test_status' => $p->last_test_status,
                'last_tested_at' => $p->last_tested_at?->diffForHumans(),
            ]);

        return [
            'smtp' => [
                'total_profiles' => (int) ($stats->total ?? 0),
                'active_profiles' => (int) ($stats->active ?? 0),
                'failed_profiles' => (int) ($stats->failed ?? 0),
                'usage_count' => $usageCount,
                'profile_statuses' => $statuses,
            ],
        ];
    }
}
