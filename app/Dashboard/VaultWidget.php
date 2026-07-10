<?php

namespace App\Dashboard;

use App\Models\User;
use App\Models\VaultEntry;
use Spatie\Activitylog\Models\Activity;

class VaultWidget
{
    public const SLUG = 'vault';

    public function cacheTtl(): int
    {
        return 300;
    }

    public function data(User $user, ?array $accessibleIds = null): array
    {
        $isSA = $user->hasRole('super-admin');

        $totalEntries = $isSA
            ? VaultEntry::count()
            : VaultEntry::where('user_id', $user->id)->count();

        $myEntries = VaultEntry::where('user_id', $user->id)->count();

        $revealQuery = Activity::where('event', 'revealed')->with('causer:id,name');
        if (!$isSA) {
            $revealQuery->where('causer_id', $user->id);
        }

        $recentReveals = (clone $revealQuery)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($a) => [
                'causer' => $a->causer?->name ?? 'System',
                'description' => $a->description,
                'created_at' => $a->created_at?->diffForHumans(),
            ]);

        $totalReveals30d = (clone $revealQuery)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $revealedToday = (clone $revealQuery)
            ->whereDate('created_at', today())
            ->count();

        return [
            'vault' => [
                'total_entries' => $totalEntries,
                'my_entries' => $myEntries,
                'recent_reveals' => $recentReveals,
                'total_reveals_30d' => $totalReveals30d,
                'revealed_today' => $revealedToday,
            ],
        ];
    }
}
