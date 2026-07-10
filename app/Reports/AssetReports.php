<?php

namespace App\Reports;

use App\Models\Asset;

class AssetReports extends ReportProvider
{
    public function slug(): string
    {
        return 'assets';
    }

    public function label(): string
    {
        return 'Assets';
    }

    public function description(): string
    {
        return 'Asset inventory reports including assignments and department breakdowns.';
    }

    public function icon(): ?string
    {
        return 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4';
    }

    public function reports(): array
    {
        return [
            'assigned' => [
                'slug' => 'assigned',
                'label' => 'Assigned Assets',
                'description' => 'All currently assigned assets with assignee information.',
                'columns' => ['Asset Tag', 'Serial', 'Department', 'Assignee', 'Issue Date'],
                'query' => function (array $filters, $user, $accessibleIds) {
                    return Asset::where('status', 'assigned')
                        ->when(!$user->hasRole('super-admin') && $accessibleIds !== null, fn($q) => $q->whereIn('module_id', $accessibleIds))
                        ->when($filters['search'] ?? null, fn($q, $v) => $q->where(function ($q) use ($v) {
                            $q->where('asset_tag', 'like', "%{$v}%")->orWhere('serial_number', 'like', "%{$v}%");
                        }))
                        ->orderBy('asset_tag')
                        ->get(['id', 'asset_tag', 'serial_number', 'department', 'assigned_to', 'issue_date', 'status']);
                },
            ],
            'available' => [
                'slug' => 'available',
                'label' => 'Available Assets',
                'description' => 'Assets currently available for assignment.',
                'columns' => ['Asset Tag', 'Serial', 'Department', 'Condition'],
                'query' => function (array $filters, $user, $accessibleIds) {
                    return Asset::where('status', 'available')
                        ->when(!$user->hasRole('super-admin') && $accessibleIds !== null, fn($q) => $q->whereIn('module_id', $accessibleIds))
                        ->orderBy('asset_tag')
                        ->get(['id', 'asset_tag', 'serial_number', 'department', 'condition', 'status']);
                },
            ],
            'by-department' => [
                'slug' => 'by-department',
                'label' => 'Assets By Department',
                'description' => 'Assets grouped and counted by department.',
                'columns' => ['Department', 'Total', 'Assigned', 'Available', 'Retired'],
                'query' => function (array $filters, $user, $accessibleIds) {
                    $query = Asset::query()
                        ->selectRaw("COALESCE(department, 'Unassigned') as department, COUNT(*) as total, SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned, SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available, SUM(CASE WHEN status = 'retired' THEN 1 ELSE 0 END) as retired")
                        ->groupBy('department')
                        ->orderBy('total', 'desc');
                    if (!$user->hasRole('super-admin') && $accessibleIds !== null) {
                        $query->whereIn('module_id', $accessibleIds);
                    }
                    return $query->get();
                },
            ],
        ];
    }
}
