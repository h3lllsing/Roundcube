<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    #[OA\Get(
        path: '/dashboard',
        summary: 'Get centralized dashboard stats for the current user',
        security: [['sanctum' => []]],
        tags: ['Dashboard'],
        responses: [
            new OA\Response(response: 200, description: 'Dashboard stats', content: new OA\JsonContent(ref: '#/components/schemas/DashboardData')),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->dashboardService->compute($request->user());
        } catch (\Throwable $e) {
            Log::channel('api')->warning('API Dashboard cache failed, computing fresh data', ['error' => $e->getMessage()]);
            $data = $this->dashboardService->computeDashboardData($request->user());
        }

        return $this->success($data);
    }
}
