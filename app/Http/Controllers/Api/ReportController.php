<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Spatie\Activitylog\Models\Activity;

class ReportController extends Controller
{
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

    #[OA\Get(
        path: '/reports',
        summary: 'Get aggregated reports (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'type', in: 'query', schema: new OA\Schema(type: 'string', enum: ['tasks', 'activity', 'logins', 'costs'], default: 'tasks')),
            new OA\Parameter(name: 'group_by', in: 'query', schema: new OA\Schema(type: 'string', enum: ['day', 'week', 'month'], default: 'day')),
            new OA\Parameter(name: 'date_from', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'user_id', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Report data', content: new OA\JsonContent(ref: '#/components/schemas/ReportData')),
        ]
    )]
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $type = $request->get('type', 'tasks');
        $groupBy = in_array($request->get('group_by'), ['day', 'week', 'month']) ? $request->get('group_by') : 'day';
        $dateFrom = $request->get('date_from', now()->subMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());
        $userId = $request->get('user_id');

        if ($type === 'costs') {
            return $this->success([
                'type' => 'costs',
                'group_by' => null,
                'date_from' => null,
                'date_to' => null,
                'periods' => [],
                'summary' => $this->costsSummary(),
            ]);
        }

        $data = match ($type) {
            'tasks' => $this->tasksReport($groupBy, $dateFrom, $dateTo, $userId),
            'activity' => $this->activityReport($groupBy, $dateFrom, $dateTo, $userId),
            'logins' => $this->loginReport($groupBy, $dateFrom, $dateTo, $userId),
            default => [],
        };

        return $this->success([
            'type' => $type,
            'group_by' => $groupBy,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'periods' => $data,
            'summary' => $this->summary($type, $dateFrom, $dateTo, $userId),
        ]);
    }

    /** @return array<int, array{period: string, created: int, completed: int}> */
    private function tasksReport(string $groupBy, string $dateFrom, string $dateTo, ?int $userId): array
    {
        $query = Task::query();
        if ($userId) {
            $query->whereHas('assignees', fn($q) => $q->where('user_id', $userId));
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
        return $periods->map(fn($p) => [
            'period' => $p,
            'created' => (int) ($created[$p] ?? 0),
            'completed' => (int) ($completed[$p] ?? 0),
        ])->toArray();
    }

    /** @return array<int, array{period: string, events: array<int, array{event: string, count: int}>}> */
    private function activityReport(string $groupBy, string $dateFrom, string $dateTo, ?int $userId): array
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
        return $periods->map(fn($p) => [
            'period' => $p,
            'events' => $events->where('period', $p)->map(fn($e) => [
                'event' => $e->event,
                'count' => (int) $e->getAttribute('count'),
            ])->values()->toArray(),
        ])->toArray();
    }

    /** @return array<int, array{period: string, events: array<int, array{event: string, count: int}>}> */
    private function loginReport(string $groupBy, string $dateFrom, string $dateTo, ?int $userId): array
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
        return $periods->map(fn($p) => [
            'period' => $p,
            'events' => $events->where('period', $p)->map(fn($e) => [
                'event' => $e->event,
                'count' => (int) $e->getAttribute('count'),
            ])->values()->toArray(),
        ])->toArray();
    }

    /** @return array<string, mixed> */
    private function summary(string $type, string $dateFrom, string $dateTo, ?int $userId): array
    {
        return match ($type) {
            'tasks' => [
                'total_created' => Task::whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
                    ->when($userId, fn($q) => $q->whereHas('assignees', fn($a) => $a->where('user_id', $userId)))
                    ->count(),
                'total_completed' => Task::whereDate('updated_at', '>=', $dateFrom)->whereDate('updated_at', '<=', $dateTo)
                    ->where('status', 'completed')
                    ->when($userId, fn($q) => $q->whereHas('assignees', fn($a) => $a->where('user_id', $userId)))
                    ->count(),
            ],
            'activity' => [
                'total' => Activity::whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
                    ->when($userId, fn($q) => $q->where('causer_id', $userId))
                    ->count(),
            ],
            'logins' => [
                'total' => LoginAudit::whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
                    ->when($userId, fn($q) => $q->where('user_id', $userId))
                    ->count(),
                'successful' => LoginAudit::whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
                    ->where('event', 'login_success')
                    ->when($userId, fn($q) => $q->where('user_id', $userId))
                    ->count(),
                'failed' => LoginAudit::whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
                    ->where('event', 'login_failed')
                    ->when($userId, fn($q) => $q->where('user_id', $userId))
                    ->count(),
            ],
            default => [],
        };
    }

    /** @return array{total_monthly: float, by_type: array<int, array{type: string, label: string, total_cost: float, count: int}>, top_10: array<int, array{type: string, type_label: string, name: string, cost: float, status: string}>, by_status: array<string, float|int>} */
    private function costsSummary(): array
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
            'expiry_trackers' => 'Expiry Trackers',
        ];

        foreach ($serviceModels as $key => $modelClass) {
            $rows = $modelClass::whereNotNull('cost')
                ->selectRaw('status, SUM(cost) as total, COUNT(*) as count')
                ->groupBy('status')
                ->get();

            $typeTotal = 0;
            foreach ($rows as $r) {
                $cost = (float) $r->getAttribute('total');
                $typeTotal += $cost;
                if (isset($byStatus[$r->getAttribute('status')])) {
                    $byStatus[$r->getAttribute('status')] += $cost;
                }
            }
            $byType[] = [
                'type' => $key,
                'label' => $typeLabels[$key],
                'total_cost' => round($typeTotal, 2),
                'count' => $modelClass::whereNotNull('cost')->count(),
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

        usort($top10, fn($a, $b) => $b['cost'] <=> $a['cost']);
        $top10 = array_slice($top10, 0, 10);
        $total = round(array_sum(array_column($byType, 'total_cost')), 2);

        return [
            'total_monthly' => $total,
            'by_type' => $byType,
            'top_10' => $top10,
            'by_status' => $byStatus,
        ];
    }

    #[OA\Get(
        path: '/reports/users',
        summary: 'List users for report filter (super-admin only)',
        security: [['sanctum' => []]],
        tags: ['Reports'],
        responses: [
            new OA\Response(response: 200, description: 'List of users (id/name/email)'),
        ]
    )]
    public function users(): \Illuminate\Http\JsonResponse
    {
        return $this->success(User::select('id', 'name', 'email')->orderBy('name')->get());
    }
}
