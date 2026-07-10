<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {}

    #[OA\Get(
        path: '/activity-logs',
        summary: 'List activity logs with filters',
        security: [['sanctum' => []]],
        tags: ['Activity Logs'],
    )]
    public function index(Request $request): ResourceCollection
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);

        $filters = $request->only(['subject_type', 'event', 'causer_id', 'date_from', 'date_to', 'search', 'sort_by', 'sort_order']);
        $filters['per_page'] = min((int) $request->per_page ?: 50, 100);
        $filters['with_subject'] = true;
        $activities = $this->activityLogService->paginate($filters);

        return ActivityLogResource::collection($activities);
    }

    #[OA\Get(
        path: '/activity-logs/{id}',
        summary: 'Get a single activity log entry with subject and causer',
        security: [['sanctum' => []]],
        tags: ['Activity Logs'],
    )]
    public function show(Request $request, Activity $activity): ActivityLogResource
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);

        $activity->loadMissing(['causer', 'subject']);

        return new ActivityLogResource($activity);
    }
}
