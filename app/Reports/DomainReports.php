<?php

namespace App\Reports;

use App\Models\Domain;

class DomainReports extends ReportProvider
{
    public function slug(): string
    {
        return 'domains';
    }

    public function label(): string
    {
        return 'Domains';
    }

    public function description(): string
    {
        return 'Domain name reports including active, expiring, and expired domains.';
    }

    public function icon(): ?string
    {
        return 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9';
    }

    public function reports(): array
    {
        return [
            'active' => [
                'slug' => 'active',
                'label' => 'Active Domains',
                'description' => 'All active domain names with their expiry dates and monthly costs.',
                'columns' => ['Name', 'Expiry Date', 'Cost', 'Auto Renew', 'Provider'],
                'query' => function (array $filters, $user, $accessibleIds) {
                    return Domain::where('status', 'active')
                        ->when(!$user->hasRole('super-admin') && $accessibleIds !== null, fn($q) => $q->whereIn('module_id', $accessibleIds))
                        ->when($filters['search'] ?? null, fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
                        ->orderBy('expiry_date')
                        ->get(['id', 'name', 'expiry_date', 'cost', 'auto_renew', 'status']);
                },
            ],
            'expiring' => [
                'slug' => 'expiring',
                'label' => 'Domains Expiring (30 days)',
                'description' => 'Domains expiring within the next 30 days that need attention.',
                'columns' => ['Name', 'Expiry Date', 'Days Left', 'Cost', 'Provider'],
                'query' => function (array $filters, $user, $accessibleIds) {
                    return Domain::whereIn('status', ['active', 'pending_renewal'])
                        ->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '>=', now())
                        ->whereDate('expiry_date', '<=', now()->addDays(30))
                        ->when(!$user->hasRole('super-admin') && $accessibleIds !== null, fn($q) => $q->whereIn('module_id', $accessibleIds))
                        ->orderBy('expiry_date')
                        ->get(['id', 'name', 'expiry_date', 'cost', 'status']);
                },
            ],
            'expired' => [
                'slug' => 'expired',
                'label' => 'Expired Domains',
                'description' => 'Domains that have passed their expiry date.',
                'columns' => ['Name', 'Expiry Date', 'Days Overdue', 'Cost', 'Provider'],
                'query' => function (array $filters, $user, $accessibleIds) {
                    return Domain::where('status', 'expired')
                        ->whereNotNull('expiry_date')
                        ->when(!$user->hasRole('super-admin') && $accessibleIds !== null, fn($q) => $q->whereIn('module_id', $accessibleIds))
                        ->orderByDesc('expiry_date')
                        ->get(['id', 'name', 'expiry_date', 'cost', 'status']);
                },
            ],
        ];
    }
}
