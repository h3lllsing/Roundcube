<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $filters = $request->only(['date_from', 'date_to', 'cost_status', 'user_id']);
        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('reports.index', [
            'categories' => $this->reportService->allCategories(),
            'totalMonthly' => $this->reportService->totalMonthlyCost($filters),
            'costByType' => $this->reportService->costByType($filters),
            'topCosts' => $this->reportService->topCosts($filters),
            'taskSummary' => $this->reportService->taskSummary($filters),
            'loginSummary' => $this->reportService->loginSummary($filters),
            'users' => $users,
        ]);
    }

    public function category(string $category): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $data = $this->reportService->categoryReports($category);
        if (!$data) {
            abort(404);
        }

        return view('reports.category', $data);
    }

    public function show(Request $request, string $category, string $report): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $filters = $request->only(['search', 'date_from', 'date_to', 'status', 'user_id']);
        $data = $this->reportService->run($category, $report, $filters);

        if (!$data) {
            abort(404);
        }

        return view('reports.show', $data);
    }

    public function export(Request $request, string $category, string $report): Response
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $filters = $request->only(['search', 'date_from', 'date_to', 'status', 'user_id']);
        $csv = $this->reportService->exportCsv($category, $report, $filters);

        if ($csv === null) {
            abort(404);
        }

        $filename = "{$category}-{$report}-" . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
