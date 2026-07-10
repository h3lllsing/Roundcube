<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class SearchController extends Controller
{
    #[OA\Get(
        path: '/search',
        summary: 'Global search across all modules',
        security: [['sanctum' => []]],
        tags: ['Search'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter', in: 'query', schema: new OA\Schema(type: 'string', default: 'all')),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 5)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Search results grouped by type'),
        ]
    )]
    public function index(Request $request, GlobalSearchService $searchService): JsonResponse
    {
        $q = $request->get('q');
        $filter = $request->get('filter', 'all');
        $limit = min((int) $request->get('limit', 5), 20);

        if (! $q || strlen(trim($q)) < 2) {
            return $this->success([]);
        }

        $cacheKey = 'search:'.$request->user()->id.':'.md5(strtolower(trim($q)).'|'.$filter.'|'.$limit);

        $groups = Cache::remember($cacheKey, 60, function () use ($q, $filter, $limit, $request, $searchService) {
            return $searchService->searchForApi($q, $request->user(), $filter, $limit);
        });

        return $this->success($groups);
    }
}
