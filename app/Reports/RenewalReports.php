<?php

namespace App\Reports;

use App\Models\ExpiryTracker;

class RenewalReports extends ReportProvider
{
    public function slug(): string
    {
        return 'renewals';
    }

    public function label(): string
    {
        return 'Renewals';
    }

    public function description(): string
    {
        return 'Renewal tracker reports for managing upcoming and overdue renewals.';
    }

    public function icon(): ?string
    {
        return 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z';
    }

    public function reports(): array
    {
        return [
            'today' => [
                'slug' => 'today',
                'label' => 'Due Today',
                'description' => 'Renewals that are due today.',
                'columns' => ['Name', 'Expiry Date', 'Cost', 'Status', 'Provider'],
                'query' => function (array $filters, $user, $accessibleIds) {
                    return ExpiryTracker::whereIn('status', ['active', 'pending_renewal'])
                        ->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', today())
                        ->when(!$user->hasRole('super-admin') && $accessibleIds !== null, fn($q) => $q->whereIn('module_id', $accessibleIds))
                        ->orderBy('name')
                        ->get(['id', 'name', 'expiry_date', 'cost', 'status']);
                },
            ],
            'next-30' => [
                'slug' => 'next-30',
                'label' => 'Due Next 30 Days',
                'description' => 'Renewals due within the next 30 days.',
                'columns' => ['Name', 'Expiry Date', 'Days Left', 'Cost', 'Status'],
                'query' => function (array $filters, $user, $accessibleIds) {
                    return ExpiryTracker::whereIn('status', ['active', 'pending_renewal'])
                        ->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '>=', today()->addDay())
                        ->whereDate('expiry_date', '<=', today()->addDays(30))
                        ->when(!$user->hasRole('super-admin') && $accessibleIds !== null, fn($q) => $q->whereIn('module_id', $accessibleIds))
                        ->orderBy('expiry_date')
                        ->get(['id', 'name', 'expiry_date', 'cost', 'status']);
                },
            ],
            'overdue' => [
                'slug' => 'overdue',
                'label' => 'Overdue Renewals',
                'description' => 'Renewals that have passed their expiry date.',
                'columns' => ['Name', 'Expiry Date', 'Days Overdue', 'Cost', 'Status'],
                'query' => function (array $filters, $user, $accessibleIds) {
                    return ExpiryTracker::whereIn('status', ['active', 'pending_renewal'])
                        ->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '<', today())
                        ->when(!$user->hasRole('super-admin') && $accessibleIds !== null, fn($q) => $q->whereIn('module_id', $accessibleIds))
                        ->orderBy('expiry_date')
                        ->get(['id', 'name', 'expiry_date', 'cost', 'status']);
                },
            ],
        ];
    }
}
