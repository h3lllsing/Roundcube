<?php

namespace App\Reports;

use App\Models\Vps;

class VpsReports extends ReportProvider
{
    public function slug(): string
    {
        return 'vps';
    }

    public function label(): string
    {
        return 'VPS Accounts';
    }

    public function description(): string
    {
        return 'VPS reports showing active servers and resource allocation.';
    }

    public function icon(): ?string
    {
        return 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01';
    }

    public function reports(): array
    {
        return [
            'active' => [
                'slug' => 'active',
                'label' => 'Active VPS',
                'description' => 'All active VPS accounts with specs and costs.',
                'columns' => ['Name', 'IP Address', 'OS', 'RAM', 'Disk', 'Cost'],
                'query' => function (array $filters, $user, $accessibleIds) {
                    return Vps::where('status', 'active')
                        ->when(!$user->hasRole('super-admin') && $accessibleIds !== null, fn($q) => $q->whereIn('module_id', $accessibleIds))
                        ->when($filters['search'] ?? null, fn($q, $v) => $q->where(function ($q) use ($v) {
                            $q->where('name', 'like', "%{$v}%")->orWhere('ip_address', 'like', "%{$v}%");
                        }))
                        ->orderBy('name')
                        ->get(['id', 'name', 'ip_address', 'os', 'ram_mb', 'disk_gb', 'cost', 'status']);
                },
            ],
        ];
    }
}
