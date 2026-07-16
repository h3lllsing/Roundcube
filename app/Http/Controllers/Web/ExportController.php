<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    public function __construct(
        private readonly ExportService $exportService
    ) {}

    public function export(Request $request, string $type): Response|RedirectResponse
    {
        if (! $this->exportService->isValidType($type)) {
            return redirect()->back()->with('error', 'Invalid export type.');
        }

        if (! $this->exportService->canExport(Auth::user(), $type)) {
            return redirect()->back()->with('error', 'Forbidden.');
        }

        $result = $this->exportService->export(Auth::user(), $type);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return response()->make($result['csv'], 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$result['filename'].'"',
        ]);
    }
}
