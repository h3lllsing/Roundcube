<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BulkActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BulkActionController extends Controller
{
    public function __construct(
        private readonly BulkActionService $bulkActionService
    ) {}

    public function action(Request $request, string $type): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|min:1',
            'action' => 'required|string',
            'status' => 'required_if:action,update-status|string',
        ]);

        $result = $this->bulkActionService->execute(
            $type,
            $validated['action'],
            $validated['ids'],
            $request->user(),
            $validated['status'] ?? null,
        );

        if (! $result['success']) {
            return $this->message($result['message'], $result['status_code'] ?? 422);
        }

        return $this->success(
            ['affected' => $result['count'] ?? 0],
            $result['message'],
        );
    }
}
