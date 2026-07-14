<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    private function authorizeTaskCreate(?int $moduleId): void
    {
        $user = request()->user();
        if (! $user || $user->hasRole('super-admin')) { return; }
        $module = $moduleId ? \App\Models\Module::find($moduleId) : null;
        abort_unless($module && $user->canOnModule($module, 'create'), 403);
    }

    private function authorizeTaskView(Task $task): void
    {
        $user = request()->user();
        if (! $user || $user->hasRole('super-admin')) { return; }
        $task->loadMissing('module');
        $module = $task->module;
        if (! $module) { abort(403, 'Forbidden'); }
        $isAssignee = $task->assignees()->where('user_id', $user->id)->exists();
        abort_unless($isAssignee || $user->canOnModule($module, 'read'), 403);
    }

    private function authorizeTaskUpdate(Task $task): void
    {
        $user = request()->user();
        if (! $user || $user->hasRole('super-admin')) { return; }
        $task->loadMissing('module');
        $module = $task->module;
        $isAssignee = $task->assignees()->where('user_id', $user->id)->exists();
        abort_unless($isAssignee || ($module && $user->canOnModule($module, 'update')), 403);
    }

    private function authorizeTaskDelete(Task $task): void
    {
        $user = request()->user();
        abort_unless($user && $user->hasRole('super-admin'), 403);
    }

    #[OA\Get(
        path: '/tasks',
        summary: 'List tasks — filtered by user permissions',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'in_progress', 'completed', 'cancelled'])),
            new OA\Parameter(name: 'priority', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['low', 'medium', 'high', 'urgent'])),
            new OA\Parameter(name: 'module_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'assigned_to', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['created_at', 'updated_at', 'title', 'status', 'priority', 'due_date'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'date_from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of tasks', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/TaskData')),
            ])),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = $this->taskService->buildIndexFiltersForUser(
            $request->user(),
            $request->only(['status', 'priority', 'module_id', 'assigned_to', 'search', 'per_page', 'sort_by', 'sort_order', 'date_from', 'date_to'])
        );

        if ($request->user()->hasRole('super-admin') && $request->boolean('with_trashed')) {
            $filters['with_trashed'] = true;
        }

        return response()->json($this->taskService->list($filters));
    }

    #[OA\Get(
        path: '/my/tasks',
        summary: 'List tasks assigned to the current user',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'in_progress', 'completed', 'cancelled'])),
            new OA\Parameter(name: 'priority', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['low', 'medium', 'high', 'urgent'])),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['created_at', 'updated_at', 'title', 'status', 'priority', 'due_date'])),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'date_from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of my tasks', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/TaskData')),
            ])),
        ]
    )]
    public function myTasks(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'priority', 'search', 'per_page', 'sort_by', 'sort_order', 'date_from', 'date_to']);
        $filters['assigned_to'] = $request->user()->id;

        $tasks = $this->taskService->list($filters);

        return response()->json($tasks);
    }

    #[OA\Get(
        path: '/my/tasks/counts',
        summary: 'Get task counts by status for the current user',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        responses: [
            new OA\Response(response: 200, description: 'Task counts', content: new OA\JsonContent(ref: '#/components/schemas/TaskCounts')),
        ]
    )]
    public function myTaskCounts(Request $request): JsonResponse
    {
        return $this->success($this->taskService->getUserTaskCounts($request->user()->id));
    }

    #[OA\Post(
        path: '/tasks',
        summary: 'Create a task (requires can_create on the module)',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
                new OA\Property(property: 'status', type: 'string', default: 'pending', enum: ['pending', 'in_progress', 'completed', 'cancelled']),
                new OA\Property(property: 'priority', type: 'string', default: 'medium', enum: ['low', 'medium', 'high', 'urgent']),
                new OA\Property(property: 'due_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'assignee_ids', type: 'array', items: new OA\Items(type: 'integer'), nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Task created', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/TaskData'),
            ])),
            new OA\Response(response: 403, description: 'Permission denied'),
        ]
    )]
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $this->authorizeTaskCreate(isset($validated['module_id']) ? (int) $validated['module_id'] : null);

        $validated['created_by'] = $request->user()->id;
        $validated['updated_by'] = $request->user()->id;

        return $this->created($this->taskService->create($validated), 'Task created');
    }

    #[OA\Get(
        path: '/tasks/{id}',
        summary: 'Get a single task',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Task details', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', ref: '#/components/schemas/TaskData'),
            ])),
            new OA\Response(response: 403, description: 'Permission denied'),
        ]
    )]
    public function show(Task $task, Request $request): JsonResponse
    {
        $this->authorizeTaskView($task);

        $task->load(['module.feature', 'assignees', 'creator', 'updater']);

        return $this->success($task);
    }

    #[OA\Put(
        path: '/tasks/{id}',
        summary: 'Update a task',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'module_id', type: 'integer', nullable: true),
                new OA\Property(property: 'status', type: 'string', enum: ['pending', 'in_progress', 'completed', 'cancelled']),
                new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high', 'urgent']),
                new OA\Property(property: 'due_date', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'assignee_ids', type: 'array', items: new OA\Items(type: 'integer'), nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Task updated', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/TaskData'),
            ])),
        ]
    )]
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $validated = $request->validated();

    $this->authorizeTaskUpdate($task);
    $this->checkOptimisticLock($task, $request);

    $validated['updated_by'] = $request->user()->id;

        return $this->success($this->taskService->update($task, $validated), 'Task updated');
    }

    #[OA\Delete(
        path: '/tasks/{id}',
        summary: 'Delete a task',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Task deleted', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    #[OA\Get(
        path: '/tasks/kanban',
        summary: 'List tasks grouped by status for Kanban view',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        responses: [new OA\Response(response: 200, description: 'Tasks grouped by status')]
    )]
    public function kanban(Request $request): JsonResponse
    {
        return $this->success($this->taskService->getKanbanForUser($request->user()));
    }

    #[OA\Patch(
        path: '/tasks/{id}/status',
        summary: 'Quick-update task status (for Kanban drag)',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Status updated')]
    )]
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $request->validate(['status' => 'required|string|in:pending,in_progress,completed,cancelled']);

        $this->authorizeTaskUpdate($task);

        $task->update(['status' => $request->status, 'updated_by' => $request->user()->id]);

        return $this->message('Status updated');
    }

    public function destroy(Task $task, Request $request): JsonResponse
    {
        $this->authorizeTaskDelete($task);

        $this->taskService->delete($task);

        return $this->message('Task deleted');
    }
}
