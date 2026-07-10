<?php

namespace App\Reports;

use App\Models\Hosting;

class HostingReports extends ReportProvider
{
    public function slug(): string
    {
        return 'hosting';
    }

    public function label(): string
    {
        return 'Hosting';
    }

    public function description(): string
    {
        return 'Hosting account reports showing active and expiring accounts.';
    }

    public function icon(): ?string
    {
        return 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2';
    }

    public function reports(): array
    {
        return [
            'active' => [
                'slug' => 'active',
                'label' => 'Active Hosting',
                'description' => 'All active hosting accounts with plans and costs.',
                'columns' => ['Name', 'Plan', 'Domain', 'Expiry Date', 'Cost'],
                'query' => function (array $filters, $user, $accessibleIds) {
                    return Hosting::where('status', 'active')
                        ->when(!$user->hasRole('super-admin') && $accessibleIds !== null, fn($q) => $q->whereIn('module_id', $accessibleIds))
                        ->when($filters['search'] ?? null, fn($q, $v) => $q->where(function ($q) use ($v) {
                            $q->where('name', 'like', "%{$v}%")->orWhere('domain', 'like', "%{$v}%");
                        }))
                        ->orderBy('expiry_date')
                        ->get(['id', 'name', 'plan', 'domain', 'expiry_date', 'cost', 'status']);
                },
            ],
            'expiring' => [
                'slug' => 'expiring',
                'label' => 'Hosting Expiring (30 days)',
                'description' => 'Hosting accounts expiring within the next 30 days.',
                'columns' => ['Name', 'Plan', 'Expiry Date', 'Days Left', 'Cost'],
                'query' => function (array $filters, $user, $accessibleIds) {
                    return Hosting::whereIn('status', ['active', 'pending_renewal'])
                        ->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '>=', now())
                        ->whereDate('expiry_date', '<=', now()->addDays(30))
                        ->when(!$user->hasRole('super-admin') && $accessibleIds !== null, fn($q) => $q->whereIn('module_id', $accessibleIds))
                        ->orderBy('expiry_date')
                        ->get(['id', 'name', 'plan', 'expiry_date', 'cost', 'status']);
                },
            ],
        ];
    }
}
