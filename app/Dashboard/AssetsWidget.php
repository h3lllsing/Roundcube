<?php

namespace App\Dashboard;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;

class AssetsWidget
{
    public const SLUG = 'assets';

    public function cacheTtl(): int
    {
        return 300;
    }

    public function data(User $user, ?array $accessibleIds = null): array
    {
        $isSA = $user->hasRole('super-admin');

        $assetQuery = Asset::query();
        if (!$isSA) {
            if ($accessibleIds !== null && !empty($accessibleIds)) {
                $assetQuery->whereIn('module_id', $accessibleIds);
            } else {
                $assetQuery->where('user_id', $user->id);
            }
        }

        $assetsByStatus = (clone $assetQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalAssets = array_sum($assetsByStatus->toArray());

        $assetIds = (clone $assetQuery)->select('id');

        $recentAssignments = AssetAssignment::with('asset:id,asset_tag', 'assignee:id,name')
            ->whereNull('returned_at')
            ->whereIn('asset_id', $assetIds)
            ->latest('assigned_at')
            ->take(5)
            ->get()
            ->map(fn($a) => [
                'asset_tag' => $a->asset?->asset_tag,
                'assignee' => $a->assignee?->name,
                'assigned_at' => $a->assigned_at?->diffForHumans(),
            ]);

        $recentReturns = AssetAssignment::with('asset:id,asset_tag', 'assignee:id,name')
            ->whereNotNull('returned_at')
            ->whereIn('asset_id', $assetIds)
            ->latest('returned_at')
            ->take(5)
            ->get()
            ->map(fn($a) => [
                'asset_tag' => $a->asset?->asset_tag,
                'assignee' => $a->assignee?->name,
                'returned_at' => $a->returned_at?->diffForHumans(),
            ]);

        $assignmentQuery = AssetAssignment::whereIn('asset_id', $assetIds);
        $assignedToday = (clone $assignmentQuery)->whereDate('assigned_at', today())->count();
        $returnedToday = (clone $assignmentQuery)->whereDate('returned_at', today())->count();

        return [
            'assets' => [
                'assets_by_status' => $assetsByStatus,
                'total_assets' => $totalAssets,
                'recent_assignments' => $recentAssignments,
                'recent_returns' => $recentReturns,
                'assigned_today' => $assignedToday,
                'returned_today' => $returnedToday,
            ],
        ];
    }
}
