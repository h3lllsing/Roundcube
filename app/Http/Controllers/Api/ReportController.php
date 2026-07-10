<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response as ResponseFacade;
use OpenApi\Attributes as OA;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

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
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);

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
                'summary' => $this->reportService->costsSummary(),
            ]);
        }

        $data = match ($type) {
            'tasks' => $this->reportService->tasksReport($groupBy, $dateFrom, $dateTo, $userId),
            'activity' => $this->reportService->activityReport($groupBy, $dateFrom, $dateTo, $userId),
            'logins' => $this->reportService->loginReport($groupBy, $dateFrom, $dateTo, $userId),
            default => [],
        };

        return $this->success([
            'type' => $type,
            'group_by' => $groupBy,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'periods' => $data,
            'summary' => $this->reportService->summary($type, $dateFrom, $dateTo, $userId),
        ]);
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
    public function users(): JsonResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        return $this->success($this->reportService->getReportUsers());
    }

    public function export(Request $request): Response|JsonResponse
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);

        $type = $request->get('type', 'tasks');
        $dateFrom = $request->get('date_from', now()->subMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());
        $userId = $request->get('user_id');
        $groupBy = in_array($request->get('group_by'), ['day', 'week', 'month']) ? $request->get('group_by') : 'day';

        $validTypes = ['tasks', 'activity', 'logins'];
        if (! in_array($type, $validTypes, true)) {
            return $this->message('Invalid report type', 404);
        }

        $data = match ($type) {
            'tasks' => $this->reportService->tasksReport($groupBy, $dateFrom, $dateTo, $userId),
            'activity' => $this->reportService->activityReport($groupBy, $dateFrom, $dateTo, $userId),
            'logins' => $this->reportService->loginReport($groupBy, $dateFrom, $dateTo, $userId),
        };

        $headers = array_keys($data[0] ?? []);
        $rows = array_map(fn ($row) => array_values((array) $row), $data);

        $csv = $this->toCsv(array_merge([$headers], $rows));

        $filename = $type.'-report-'.now()->format('Y-m-d-His').'.csv';

        return ResponseFacade::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /** @param array<int, array<int, mixed>> $data */
    private function toCsv(array $data): string
    {
        $out = fopen('php://temp', 'r+');
        if ($out === false) {
            return '';
        }
        foreach ($data as $row) {
            $row = array_map(fn ($v) => is_array($v) ? json_encode($v) : $v, (array) $row);
            fputcsv($out, $row);
        }
        rewind($out);
        $content = stream_get_contents($out);
        fclose($out);

        return $content;
    }
}
