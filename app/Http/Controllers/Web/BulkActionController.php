<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\BulkActionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BulkActionController extends Controller
{
    public function __construct(
        private readonly BulkActionService $bulkActionService
    ) {}

    public function action(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'action' => 'required|string',
            'status' => 'required_if:action,update-status|string',
        ]);

        $result = $this->bulkActionService->execute(
            $validated['type'],
            $validated['action'],
            $validated['ids'],
            Auth::user(),
            $validated['status'] ?? null,
        );

        $type = $result['success'] ? 'success' : 'error';

        return redirect()->back()->with($type, $result['message']);
    }
}
