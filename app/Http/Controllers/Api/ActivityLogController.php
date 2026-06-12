<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    #[OA\Get(
        path: '/activity-logs',
        summary: 'List activity logs with filters',
        security: [['sanctum' => []]],
        tags: ['Activity Logs'],
        parameters: [
            new OA\Parameter(name: 'subject_type', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'e.g. App\\Models\\Feature'),
            new OA\Parameter(name: 'event', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['created', 'updated', 'deleted', 'restored'])),
            new OA\Parameter(name: 'causer_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date_from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['created_at', 'updated_at', 'event', 'description'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 50)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of activity logs', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ActivityLogData')),
            ])),
        ]
    )]
    public function index(Request $request): \Illuminate\Http\Resources\Json\ResourceCollection
    {
        if (!$request->user()->hasRole('super-admin')) {
            abort(403, 'Forbidden');
        }
        $query = Activity::query()->with(['causer', 'subject']);

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $allowedSort = ['created_at', 'updated_at', 'event', 'description'];
        if (!in_array($sortBy, $allowedSort)) $sortBy = 'created_at';
        if (!in_array($sortOrder, ['asc', 'desc'])) $sortOrder = 'desc';

        $query->orderBy($sortBy, $sortOrder);

        $perPage = min((int) $request->per_page ?: 50, 100);

        return ActivityLogResource::collection($query->paginate($perPage));
    }

    #[OA\Get(
        path: '/activity-logs/{id}',
        summary: 'Get a single activity log entry with subject and causer',
        security: [['sanctum' => []]],
        tags: ['Activity Logs'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Activity log details', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/ActivityLogData'),
            ])),
        ]
    )]
    public function show(Request $request, Activity $activity): ActivityLogResource
    {
        if (!$request->user()->hasRole('super-admin')) {
            abort(403, 'Forbidden');
        }
        $activity->loadMissing(['causer', 'subject']);
        return new ActivityLogResource($activity);
    }
}
