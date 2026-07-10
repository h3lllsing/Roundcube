<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\LoginAudit;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Task;
use App\Models\User;
use App\Models\Voip;
use App\Models\Vps;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class ReportService
{
    public function tasksReport(string $groupBy, string $dateFrom, string $dateTo, ?int $userId): array
    {
        $query = Task::query();
        if ($userId) {
            $query->whereHas('assignees', fn ($q) => $q->where('user_id', $userId));
        }

        $raw = $this->periodRaw('created_at', $groupBy);

        $created = (clone $query)
            ->selectRaw("{$raw} as period, COUNT(*) as count")
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->groupBy(DB::raw($raw))->orderBy('period')
            ->pluck('count', 'period');

        $completed = (clone $query)
            ->selectRaw("{$raw} as period, COUNT(*) as count")
            ->whereDate('updated_at', '>=', $dateFrom)
            ->whereDate('updated_at', '<=', $dateTo)
            ->where('status', 'completed')
            ->groupBy(DB::raw($raw))->orderBy('period')
            ->pluck('count', 'period');

        $periods = collect($created->keys()->merge($completed->keys())->unique()->sort()->values());

        return $periods->map(fn ($p) => [
            'period' => $p,
            'created' => (int) ($created[$p] ?? 0),
            'completed' => (int) ($completed[$p] ?? 0),
        ])->toArray();
    }

    public function activityReport(string $groupBy, string $dateFrom, string $dateTo, ?int $userId): array
    {
        $query = Activity::query();
        if ($userId) {
            $query->where('causer_id', $userId);
        }

        $raw = $this->periodRaw('created_at', $groupBy);

        $events = (clone $query)
            ->selectRaw("{$raw} as period, event, COUNT(*) as count")
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->groupBy(DB::raw($raw), 'event')->orderBy('period')
            ->get();

        $periods = $events->pluck('period')->unique()->sort()->values();

        return $periods->map(fn ($p) => [
            'period' => $p,
            'events' => $events->where('period', $p)->map(fn ($e) => [
                'event' => $e->event,
                'count' => (int) $e->getAttribute('count'),
            ])->values()->toArray(),
        ])->toArray();
    }

    public function loginReport(string $groupBy, string $dateFrom, string $dateTo, ?int $userId): array
    {
        $query = LoginAudit::query();
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $raw = $this->periodRaw('created_at', $groupBy);

        $events = (clone $query)
            ->selectRaw("{$raw} as period, event, COUNT(*) as count")
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->groupBy(DB::raw($raw), 'event')->orderBy('period')
            ->get();

        $periods = $events->pluck('period')->unique()->sort()->values();

        return $periods->map(fn ($p) => [
            'period' => $p,
            'events' => $events->where('period', $p)->map(fn ($e) => [
                'event' => $e->event,
                'count' => (int) $e->getAttribute('count'),
            ])->values()->toArray(),
        ])->toArray();
    }

    public function summary(string $type, string $dateFrom, string $dateTo, ?int $userId): array
    {
        return match ($type) {
            'tasks' => [
                'total_created' => Task::whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
                    ->when($userId, fn ($q) => $q->whereHas('assignees', fn ($a) => $a->where('user_id', $userId)))
                    ->count(),
                'total_completed' => Task::whereDate('updated_at', '>=', $dateFrom)->whereDate('updated_at', '<=', $dateTo)
                    ->where('status', 'completed')
                    ->when($userId, fn ($q) => $q->whereHas('assignees', fn ($a) => $a->where('user_id', $userId)))
                    ->count(),
            ],
            'activity' => [
                'total' => Activity::whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
                    ->when($userId, fn ($q) => $q->where('causer_id', $userId))
                    ->count(),
            ],
            'logins' => [
                'total' => LoginAudit::whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
                    ->when($userId, fn ($q) => $q->where('user_id', $userId))
                    ->count(),
                'successful' => LoginAudit::whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
                    ->where('event', 'login_success')
                    ->when($userId, fn ($q) => $q->where('user_id', $userId))
                    ->count(),
                'failed' => LoginAudit::whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
                    ->where('event', 'login_failed')
                    ->when($userId, fn ($q) => $q->where('user_id', $userId))
                    ->count(),
            ],
            default => [],
        };
    }

    public function costsSummary(): array
    {
        $serviceModels = [
            'domains' => Domain::class, 'hostings' => Hosting::class, 'vps' => Vps::class,
            'voip' => Voip::class, 'service_providers' => ServiceProvider::class,
            'domain_emails' => DomainEmail::class, 'other_services' => OtherService::class,
            'expiry_trackers' => ExpiryTracker::class,
        ];

        $byType = [];
        $byStatus = ['active' => 0, 'expired' => 0, 'suspended' => 0, 'cancelled' => 0];
        $top10 = [];
        $typeLabels = [
            'domains' => 'Domains', 'hostings' => 'Hostings', 'vps' => 'VPS',
            'voip' => 'VoIP', 'service_providers' => 'Service Providers',
            'domain_emails' => 'Domain Emails', 'other_services' => 'Other Services',
            'expiry_trackers' => 'Renewals',
        ];

        foreach ($serviceModels as $key => $modelClass) {
            $rows = $modelClass::whereNotNull('cost')
                ->selectRaw('status, SUM(cost) as total, COUNT(*) as count')
                ->groupBy('status')
                ->get();

            $typeTotal = 0;
            $typeCount = 0;
            foreach ($rows as $r) {
                $cost = (float) $r->getAttribute('total');
                $typeTotal += $cost;
                $typeCount += (int) $r->getAttribute('count');
                if (isset($byStatus[$r->getAttribute('status')])) {
                    $byStatus[$r->getAttribute('status')] += $cost;
                }
            }
            $byType[] = [
                'type' => $key,
                'label' => $typeLabels[$key],
                'total_cost' => round($typeTotal, 2),
                'count' => $typeCount,
            ];

            $modelClass::whereNotNull('cost')
                ->orderByDesc('cost')
                ->limit(10)
                ->each(function ($item) use (&$top10, $key, $typeLabels) {
                    $top10[] = [
                        'type' => $key,
                        'type_label' => $typeLabels[$key],
                        'name' => $item->name ?? $item->email ?? 'Unnamed',
                        'cost' => (float) $item->cost,
                        'status' => $item->status,
                    ];
                }, 100);
        }

        usort($top10, fn ($a, $b) => $b['cost'] <=> $a['cost']);
        $top10 = array_slice($top10, 0, 10);
        $total = round(array_sum(array_column($byType, 'total_cost')), 2);

        return [
            'total_monthly' => $total,
            'by_type' => $byType,
            'top_10' => $top10,
            'by_status' => $byStatus,
        ];
    }

    public function getReportUsers(): array
    {
        return User::select('id', 'name', 'email')->orderBy('name')->get()->toArray();
    }

    public function allCategories(): array
    {
        return [
            'domains' => ['label' => 'Domains', 'description' => 'Manage and review all registered domains, their statuses, and costs.', 'icon' => 'M21 21H3V3h18v18zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z', 'report_count' => 4],
            'hosting' => ['label' => 'Hosting', 'description' => 'View hosting accounts, track expirations and resource usage.', 'icon' => 'M5 12h14M12 5l7 7-7 7', 'report_count' => 4],
            'vps' => ['label' => 'VPS', 'description' => 'Monitor virtual private servers, costs, and renewal dates.', 'icon' => 'M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z', 'report_count' => 4],
            'voip' => ['label' => 'VoIP', 'description' => 'Track VoIP services, providers, and monthly costs.', 'icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 'report_count' => 4],
            'service-providers' => ['label' => 'Service Providers', 'description' => 'Directory of all service providers and vendor contacts.', 'icon' => 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2', 'report_count' => 2],
            'domain-emails' => ['label' => 'Domain Emails', 'description' => 'Email accounts tied to domains, with status and expiry tracking.', 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'report_count' => 4],
            'other-services' => ['label' => 'Other Services', 'description' => 'Miscellaneous services and subscriptions not covered elsewhere.', 'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4', 'report_count' => 2],
            'renewals' => ['label' => 'Renewals', 'description' => 'Consolidated view of all upcoming and overdue renewals.', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'report_count' => 4],
            'assets' => ['label' => 'Assets', 'description' => 'Hardware and software asset inventory with assignment tracking.', 'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7', 'report_count' => 4],
            'tasks' => ['label' => 'Tasks', 'description' => 'Task completion rates, overdue items, and productivity metrics.', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'report_count' => 5],
            'users' => ['label' => 'Users', 'description' => 'User account statuses, roles, and activity summaries.', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197', 'report_count' => 3],
            'activity-logs' => ['label' => 'Activity Logs', 'description' => 'Audit trail of all system activities and user actions.', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'report_count' => 1],
            'login-audits' => ['label' => 'Login Audits', 'description' => 'Login attempt history with success and failure tracking.', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'report_count' => 1],
        ];
    }

    /** @return array{service_models: array, type_labels: array, service_providers: array} */
    private function reportConfig(): array
    {
        return [
            'service_models' => [
                'domains' => Domain::class, 'hostings' => Hosting::class, 'vps' => Vps::class,
                'voip' => Voip::class, 'service_providers' => ServiceProvider::class,
                'domain_emails' => DomainEmail::class, 'other_services' => OtherService::class,
            ],
            'type_labels' => [
                'domains' => 'Domains', 'hostings' => 'Hosting', 'vps' => 'VPS',
                'voip' => 'VoIP', 'service_providers' => 'Service Providers',
                'domain_emails' => 'Domain Emails', 'other_services' => 'Other Services',
            ],
        ];
    }

    public function totalMonthlyCost(array $filters = []): float
    {
        $config = $this->reportConfig();
        $status = $filters['cost_status'] ?? null;
        $total = 0;

        foreach ($config['service_models'] as $modelClass) {
            $query = $modelClass::whereNotNull('cost');
            if ($status) {
                $query->where('status', $status);
            }
            $total += (float) $query->sum('cost');
        }

        return round($total, 2);
    }

    public function costByType(array $filters = []): array
    {
        $config = $this->reportConfig();
        $status = $filters['cost_status'] ?? null;
        $result = [];

        foreach ($config['service_models'] as $key => $modelClass) {
            $query = $modelClass::whereNotNull('cost');
            if ($status) {
                $query->where('status', $status);
            }
            $result[$key] = [
                'total' => round((float) (clone $query)->sum('cost'), 2),
                'count' => (clone $query)->count(),
            ];
        }

        return $result;
    }

    public function topCosts(array $filters = []): array
    {
        $config = $this->reportConfig();
        $status = $filters['cost_status'] ?? null;
        $top = [];

        foreach ($config['service_models'] as $key => $modelClass) {
            $query = $modelClass::whereNotNull('cost')->orderByDesc('cost');
            if ($status) {
                $query->where('status', $status);
            }
            $query->limit(5)->each(function ($item) use (&$top, $key) {
                $top[] = [
                    'type' => $key,
                    'name' => $item->name ?? $item->email ?? 'Unnamed',
                    'cost' => (float) $item->cost,
                ];
            });
        }

        usort($top, fn ($a, $b) => $b['cost'] <=> $a['cost']);

        return array_slice($top, 0, 5);
    }

    public function taskSummary(array $filters = []): array
    {
        $query = Task::query();
        if (isset($filters['user_id'])) {
            $query->whereHas('assignees', fn ($q) => $q->where('user_id', $filters['user_id']));
        }

        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];
    }

    public function loginSummary(array $filters = []): array
    {
        $query = LoginAudit::query();
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return [
            'total' => (clone $query)->count(),
            'successful' => (clone $query)->where('event', 'login_success')->count(),
            'failed' => (clone $query)->where('event', 'login_failed')->count(),
        ];
    }

    private function allReportDefs(): array
    {
        return [
            'domains' => [
                'provider' => ['slug' => 'domains', 'label' => 'Domains', 'icon' => 'M21 21H3V3h18v18zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z', 'description' => 'Manage and review all registered domains, their statuses, and costs.'],
                'reports' => [
                    'active' => ['slug' => 'active', 'label' => 'Active Domains', 'description' => 'All currently active domains with their registration and cost details.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date', 'Auto Renew', 'Provider']],
                    'expiring' => ['slug' => 'expiring', 'label' => 'Domains Expiring', 'description' => 'Domains expiring within the next 30 days.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date', 'Days Left', 'Auto Renew']],
                    'expired' => ['slug' => 'expired', 'label' => 'Expired Domains', 'description' => 'Domains that have passed their expiry date.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date', 'Auto Renew']],
                    'all' => ['slug' => 'all', 'label' => 'All Domains', 'description' => 'Complete list of all domains across all statuses.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date', 'Auto Renew', 'Provider']],
                ],
            ],
            'hosting' => [
                'provider' => ['slug' => 'hosting', 'label' => 'Hosting', 'icon' => 'M5 12h14M12 5l7 7-7 7', 'description' => 'View hosting accounts, track expirations and resource usage.'],
                'reports' => [
                    'active' => ['slug' => 'active', 'label' => 'Active Hosting', 'description' => 'Currently active hosting accounts.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date', 'Provider']],
                    'expiring' => ['slug' => 'expiring', 'label' => 'Hosting Expiring', 'description' => 'Hosting accounts expiring within 30 days.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date', 'Days Left']],
                    'expired' => ['slug' => 'expired', 'label' => 'Expired Hosting', 'description' => 'Hosting accounts past their expiry date.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date']],
                    'all' => ['slug' => 'all', 'label' => 'All Hosting', 'description' => 'Complete list of all hosting accounts.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date', 'Provider']],
                ],
            ],
            'vps' => [
                'provider' => ['slug' => 'vps', 'label' => 'VPS', 'icon' => 'M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z', 'description' => 'Monitor virtual private servers, costs, and renewal dates.'],
                'reports' => [
                    'active' => ['slug' => 'active', 'label' => 'Active VPS', 'description' => 'Currently active VPS servers.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date', 'IP Address', 'Provider']],
                    'expiring' => ['slug' => 'expiring', 'label' => 'VPS Expiring', 'description' => 'VPS servers expiring within 30 days.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date', 'Days Left']],
                    'expired' => ['slug' => 'expired', 'label' => 'Expired VPS', 'description' => 'VPS servers past their expiry date.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date']],
                    'all' => ['slug' => 'all', 'label' => 'All VPS', 'description' => 'Complete list of all VPS servers.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date', 'IP Address', 'Provider']],
                ],
            ],
            'voip' => [
                'provider' => ['slug' => 'voip', 'label' => 'VoIP', 'icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 'description' => 'Track VoIP services, providers, and monthly costs.'],
                'reports' => [
                    'active' => ['slug' => 'active', 'label' => 'Active VoIP', 'description' => 'Currently active VoIP services.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date', 'Provider']],
                    'expiring' => ['slug' => 'expiring', 'label' => 'VoIP Expiring', 'description' => 'VoIP services expiring within 30 days.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date', 'Days Left']],
                    'expired' => ['slug' => 'expired', 'label' => 'Expired VoIP', 'description' => 'VoIP services past their expiry date.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date']],
                    'all' => ['slug' => 'all', 'label' => 'All VoIP', 'description' => 'Complete list of all VoIP services.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date', 'Provider']],
                ],
            ],
            'service-providers' => [
                'provider' => ['slug' => 'service-providers', 'label' => 'Service Providers', 'icon' => 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2', 'description' => 'Directory of all service providers and vendor contacts.'],
                'reports' => [
                    'active' => ['slug' => 'active', 'label' => 'Active Providers', 'description' => 'Currently active service providers.', 'columns' => ['Name', 'Status', 'Contact Email', 'Website']],
                    'all' => ['slug' => 'all', 'label' => 'All Providers', 'description' => 'Complete list of all service providers.', 'columns' => ['Name', 'Status', 'Contact Email', 'Website']],
                ],
            ],
            'domain-emails' => [
                'provider' => ['slug' => 'domain-emails', 'label' => 'Domain Emails', 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'description' => 'Email accounts tied to domains, with status and expiry tracking.'],
                'reports' => [
                    'active' => ['slug' => 'active', 'label' => 'Active Emails', 'description' => 'Currently active domain email accounts.', 'columns' => ['Email', 'Status', 'Cost', 'Expiry Date', 'Domain']],
                    'expiring' => ['slug' => 'expiring', 'label' => 'Emails Expiring', 'description' => 'Email accounts expiring within 30 days.', 'columns' => ['Email', 'Status', 'Cost', 'Expiry Date', 'Days Left']],
                    'expired' => ['slug' => 'expired', 'label' => 'Expired Emails', 'description' => 'Email accounts past their expiry date.', 'columns' => ['Email', 'Status', 'Cost', 'Expiry Date']],
                    'all' => ['slug' => 'all', 'label' => 'All Emails', 'description' => 'Complete list of all domain email accounts.', 'columns' => ['Email', 'Status', 'Cost', 'Expiry Date', 'Domain']],
                ],
            ],
            'other-services' => [
                'provider' => ['slug' => 'other-services', 'label' => 'Other Services', 'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4', 'description' => 'Miscellaneous services and subscriptions not covered elsewhere.'],
                'reports' => [
                    'active' => ['slug' => 'active', 'label' => 'Active Services', 'description' => 'Currently active miscellaneous services.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date', 'Provider']],
                    'all' => ['slug' => 'all', 'label' => 'All Services', 'description' => 'Complete list of all miscellaneous services.', 'columns' => ['Name', 'Status', 'Cost', 'Expiry Date', 'Provider']],
                ],
            ],
            'renewals' => [
                'provider' => ['slug' => 'renewals', 'label' => 'Renewals', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'description' => 'Consolidated view of all upcoming and overdue renewals.'],
                'reports' => [
                    'today' => ['slug' => 'today', 'label' => 'Renewals Today', 'description' => 'Items expiring or due for renewal today.', 'columns' => ['Name', 'Type', 'Status', 'Cost', 'Expiry Date', 'Days Left']],
                    'this-week' => ['slug' => 'this-week', 'label' => 'Renewals This Week', 'description' => 'Items expiring within the next 7 days.', 'columns' => ['Name', 'Type', 'Status', 'Cost', 'Expiry Date', 'Days Left']],
                    'this-month' => ['slug' => 'this-month', 'label' => 'Renewals This Month', 'description' => 'Items expiring within the next 30 days.', 'columns' => ['Name', 'Type', 'Status', 'Cost', 'Expiry Date', 'Days Left']],
                    'overdue' => ['slug' => 'overdue', 'label' => 'Overdue Renewals', 'description' => 'Items past their expiry date requiring immediate attention.', 'columns' => ['Name', 'Type', 'Status', 'Cost', 'Expiry Date', 'Days Left']],
                ],
            ],
            'assets' => [
                'provider' => ['slug' => 'assets', 'label' => 'Assets', 'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7', 'description' => 'Hardware and software asset inventory with assignment tracking.'],
                'reports' => [
                    'assigned' => ['slug' => 'assigned', 'label' => 'Assigned Assets', 'description' => 'Assets currently assigned to users or departments.', 'columns' => ['Asset Tag', 'Name', 'Status', 'Assigned To', 'Department']],
                    'available' => ['slug' => 'available', 'label' => 'Available Assets', 'description' => 'Unassigned assets ready for deployment.', 'columns' => ['Asset Tag', 'Name', 'Status', 'Department']],
                    'retired' => ['slug' => 'retired', 'label' => 'Retired Assets', 'description' => 'Decommissioned or retired assets.', 'columns' => ['Asset Tag', 'Name', 'Status', 'Department']],
                    'all' => ['slug' => 'all', 'label' => 'All Assets', 'description' => 'Complete asset inventory.', 'columns' => ['Asset Tag', 'Name', 'Status', 'Assigned To', 'Department']],
                ],
            ],
            'tasks' => [
                'provider' => ['slug' => 'tasks', 'label' => 'Tasks', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'description' => 'Task completion rates, overdue items, and productivity metrics.'],
                'reports' => [
                    'pending' => ['slug' => 'pending', 'label' => 'Pending Tasks', 'description' => 'Tasks not yet started.', 'columns' => ['Title', 'Status', 'Priority', 'Due Date', 'Assignees']],
                    'in-progress' => ['slug' => 'in-progress', 'label' => 'In Progress Tasks', 'description' => 'Tasks currently being worked on.', 'columns' => ['Title', 'Status', 'Priority', 'Due Date', 'Assignees']],
                    'completed' => ['slug' => 'completed', 'label' => 'Completed Tasks', 'description' => 'Successfully completed tasks.', 'columns' => ['Title', 'Status', 'Priority', 'Due Date', 'Completed At']],
                    'overdue' => ['slug' => 'overdue', 'label' => 'Overdue Tasks', 'description' => 'Tasks past their due date.', 'columns' => ['Title', 'Status', 'Priority', 'Due Date', 'Days Overdue']],
                    'all' => ['slug' => 'all', 'label' => 'All Tasks', 'description' => 'Complete list of all tasks.', 'columns' => ['Title', 'Status', 'Priority', 'Due Date', 'Assignees']],
                ],
            ],
            'users' => [
                'provider' => ['slug' => 'users', 'label' => 'Users', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197', 'description' => 'User account statuses, roles, and activity summaries.'],
                'reports' => [
                    'active' => ['slug' => 'active', 'label' => 'Active Users', 'description' => 'Users with active accounts.', 'columns' => ['Name', 'Email', 'Role', 'Status']],
                    'suspended' => ['slug' => 'suspended', 'label' => 'Suspended Users', 'description' => 'Users with suspended accounts.', 'columns' => ['Name', 'Email', 'Role', 'Status']],
                    'all' => ['slug' => 'all', 'label' => 'All Users', 'description' => 'Complete list of all registered users.', 'columns' => ['Name', 'Email', 'Role', 'Status']],
                ],
            ],
            'activity-logs' => [
                'provider' => ['slug' => 'activity-logs', 'label' => 'Activity Logs', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'description' => 'Audit trail of all system activities and user actions.'],
                'reports' => [
                    'all' => ['slug' => 'all', 'label' => 'All Activity', 'description' => 'Complete system activity log.', 'columns' => ['Description', 'Event', 'Causer', 'Date']],
                ],
            ],
            'login-audits' => [
                'provider' => ['slug' => 'login-audits', 'label' => 'Login Audits', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'description' => 'Login attempt history with success and failure tracking.'],
                'reports' => [
                    'all' => ['slug' => 'all', 'label' => 'All Logins', 'description' => 'Complete login audit trail.', 'columns' => ['User', 'Event', 'IP Address', 'Date']],
                ],
            ],
        ];
    }

    public function categoryReports(string $category): ?array
    {
        $defs = $this->allReportDefs();

        if (!isset($defs[$category])) {
            return null;
        }

        return $defs[$category];
    }

    public function run(string $category, string $report, array $filters = []): ?array
    {
        $defs = $this->allReportDefs();

        if (!isset($defs[$category]['reports'][$report])) {
            return null;
        }

        $reportDef = $defs[$category]['reports'][$report];
        $providerDef = $defs[$category]['provider'];

        $results = $this->queryReport($category, $report, $filters);

        return [
            'provider' => new class ($providerDef) {
                public function __construct(private array $def) {}
                public function slug(): string { return $this->def['slug']; }
                public function label(): string { return $this->def['label']; }
                public function icon(): string { return $this->def['icon']; }
                public function description(): string { return $this->def['description']; }
            },
            'label' => $reportDef['label'],
            'description' => $reportDef['description'],
            'report' => $reportDef,
            'results' => $results,
            'columns' => $reportDef['columns'],
        ];
    }

    private function queryReport(string $category, string $report, array $filters = []): iterable
    {
        $search = $filters['search'] ?? null;

        return match ($category) {
            'domains' => $this->queryDomainReports($report, $search),
            'hosting' => $this->queryModelReports(Hosting::class, $report, $search),
            'vps' => $this->queryModelReports(Vps::class, $report, $search),
            'voip' => $this->queryModelReports(Voip::class, $report, $search),
            'service-providers' => $this->queryProviderReports($report, $search),
            'domain-emails' => $this->queryDomainEmailReports($report, $search),
            'other-services' => $this->queryModelReports(OtherService::class, $report, $search),
            'renewals' => $this->queryRenewalReports($report, $search),
            'assets' => $this->queryAssetReports($report, $search),
            'tasks' => $this->queryTaskReports($report, $search),
            'users' => $this->queryUserReports($report, $search),
            'activity-logs' => $this->queryActivityReports($search),
            'login-audits' => $this->queryLoginAuditReports($search),
            default => [],
        };
    }

    private function queryDomainReports(string $report, ?string $search): iterable
    {
        $query = Domain::query()->with('serviceProvider');
        $query = match ($report) {
            'active' => $query->where('status', 'active'),
            'expiring' => $query->whereDate('expiry_date', '>=', now())
                ->whereDate('expiry_date', '<=', now()->addDays(30)),
            'expired' => $query->whereDate('expiry_date', '<', now()),
            default => $query,
        };

        return $query
            ->when($search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('status', 'like', "%{$s}%");
            }))
            ->orderBy('name')
            ->get();
    }

    private function queryModelReports(string $model, string $report, ?string $search): iterable
    {
        $query = $model::query();
        $query = match ($report) {
            'active' => $query->where('status', 'active'),
            'expiring' => $query->whereDate('expiry_date', '>=', now())
                ->whereDate('expiry_date', '<=', now()->addDays(30)),
            'expired' => $query->whereDate('expiry_date', '<', now()),
            default => $query,
        };

        return $query
            ->when($search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('status', 'like', "%{$s}%");
            }))
            ->orderBy('name')
            ->get();
    }

    private function queryProviderReports(string $report, ?string $search): iterable
    {
        $query = ServiceProvider::query();

        if ($report === 'active') {
            $query->where('status', 'active');
        }

        return $query
            ->when($search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('contact_email', 'like', "%{$s}%");
            }))
            ->orderBy('name')
            ->get();
    }

    private function queryDomainEmailReports(string $report, ?string $search): iterable
    {
        $query = DomainEmail::query()->with('domain');
        $query = match ($report) {
            'active' => $query->where('status', 'active'),
            'expiring' => $query->whereDate('expiry_date', '>=', now())
                ->whereDate('expiry_date', '<=', now()->addDays(30)),
            'expired' => $query->whereDate('expiry_date', '<', now()),
            default => $query,
        };

        return $query
            ->when($search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('email', 'like', "%{$s}%")
                  ->orWhere('status', 'like', "%{$s}%");
            }))
            ->orderBy('email')
            ->get();
    }

    private function queryRenewalReports(string $report, ?string $search): iterable
    {
        $query = ExpiryTracker::query();
        $query = match ($report) {
            'today' => $query->whereDate('expiry_date', today()),
            'this-week' => $query->whereDate('expiry_date', '>=', now())
                ->whereDate('expiry_date', '<=', now()->addDays(7)),
            'this-month' => $query->whereDate('expiry_date', '>=', now())
                ->whereDate('expiry_date', '<=', now()->addDays(30)),
            'overdue' => $query->whereDate('expiry_date', '<', now()),
            default => $query,
        };

        return $query
            ->when($search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('status', 'like', "%{$s}%");
            }))
            ->orderBy('expiry_date')
            ->get();
    }

    private function queryAssetReports(string $report, ?string $search): iterable
    {
        $query = \App\Models\Asset::query();
        $query = match ($report) {
            'assigned' => $query->where('status', 'assigned'),
            'available' => $query->where('status', 'available'),
            'retired' => $query->where('status', 'retired'),
            default => $query,
        };

        return $query
            ->when($search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('asset_tag', 'like', "%{$s}%")
                  ->orWhere('status', 'like', "%{$s}%");
            }))
            ->orderBy('asset_tag')
            ->get();
    }

    private function queryTaskReports(string $report, ?string $search): iterable
    {
        $query = Task::query();
        $query = match ($report) {
            'pending' => $query->where('status', 'pending'),
            'in-progress' => $query->where('status', 'in_progress'),
            'completed' => $query->where('status', 'completed'),
            'overdue' => $query->whereDate('due_date', '<', now())->whereNotIn('status', ['completed', 'cancelled']),
            default => $query,
        };

        return $query
            ->when($search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('status', 'like', "%{$s}%");
            }))
            ->orderBy('due_date')
            ->get();
    }

    private function queryUserReports(string $report, ?string $search): iterable
    {
        $query = User::query();
        $query = match ($report) {
            'active' => $query->whereNull('suspended_at'),
            'suspended' => $query->whereNotNull('suspended_at'),
            default => $query,
        };

        return $query
            ->when($search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            }))
            ->orderBy('name')
            ->get();
    }

    private function queryActivityReports(?string $search): iterable
    {
        $query = Activity::query()->with('causer');

        return $query
            ->when($search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('event', 'like', "%{$s}%");
            }))
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();
    }

    private function queryLoginAuditReports(?string $search): iterable
    {
        $query = LoginAudit::query()->with('user');

        return $query
            ->when($search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('event', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%");
            }))
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();
    }

    public function exportCsv(string $category, string $report, array $filters = []): ?string
    {
        $data = $this->run($category, $report, $filters);

        if (!$data) {
            return null;
        }

        $rows = [];
        $rows[] = $data['columns'];

        foreach ($data['results'] as $row) {
            $csvRow = [];
            foreach ($data['columns'] as $col) {
                $key = str_replace(' ', '_', strtolower($col));
                $val = $row instanceof \Illuminate\Database\Eloquent\Model
                    ? $row->getAttribute($key)
                    : ($row->$key ?? ($row[$key] ?? ''));
                $csvRow[] = is_array($val) || $val instanceof \Carbon\Carbon ? (string) $val : $val;
            }
            $rows[] = $csvRow;
        }

        $out = fopen('php://temp', 'r+');
        if ($out === false) {
            return '';
        }
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        rewind($out);
        $content = stream_get_contents($out);
        fclose($out);

        return $content;
    }

    public function buildCsv(): string
    {
        $total = $this->totalMonthlyCost();
        $byType = $this->costByType();
        $taskSummary = $this->taskSummary();
        $loginSummary = $this->loginSummary();

        $rows = [];
        $rows[] = ['Report', 'Value'];
        $rows[] = ['Total Monthly Cost', number_format($total, 2)];
        $rows[] = [];

        $rows[] = ['Cost by Type', 'Total', 'Count'];
        foreach ($byType as $type => $data) {
            $rows[] = [$type, number_format($data['total'], 2), $data['count']];
        }
        $rows[] = [];

        $rows[] = ['Task Summary', 'Count'];
        foreach ($taskSummary as $key => $val) {
            $rows[] = [ucfirst(str_replace('_', ' ', $key)), $val];
        }
        $rows[] = [];

        $rows[] = ['Login Summary', 'Count'];
        foreach ($loginSummary as $key => $val) {
            $rows[] = [ucfirst($key), $val];
        }

        $out = fopen('php://temp', 'r+');
        if ($out === false) {
            return '';
        }
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        rewind($out);
        $content = stream_get_contents($out);
        fclose($out);

        return $content;
    }

    private function periodRaw(string $column, string $groupBy): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return match ($groupBy) {
                'week' => "strftime('%Y-W%W', {$column})",
                'month' => "strftime('%Y-%m', {$column})",
                default => "DATE({$column})",
            };
        }

        return match ($groupBy) {
            'week' => "DATE_FORMAT({$column}, '%x-W%v')",
            'month' => "DATE_FORMAT({$column}, '%Y-%m')",
            default => "DATE({$column})",
        };
    }
}
