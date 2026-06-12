<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Module;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

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
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $filters = $request->only(['status', 'priority', 'module_id', 'assigned_to', 'search', 'per_page', 'sort_by', 'sort_order', 'date_from', 'date_to']);

        if ($request->user()->hasRole('super-admin') && $request->boolean('with_trashed')) {
            $filters['with_trashed'] = true;
        }

        if (!$request->user()->hasRole('super-admin')) {
            $accessibleModules = $request->user()->getAccessibleModuleIds('read');
            $filters['module_ids'] = $accessibleModules;
            $filters['my_assignee_id'] = $request->user()->id;
        }

        $tasks = $this->taskService->list($filters);
        return response()->json($tasks);
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
    public function myTasks(Request $request): \Illuminate\Http\JsonResponse
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
    public function myTaskCounts(Request $request): \Illuminate\Http\JsonResponse
    {
        $userId = $request->user()->id;
        $counts = Task::whereHas('assignees', fn($q) => $q->where('user_id', $userId))
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status');

        return $this->success([
            'total' => array_sum($counts->toArray()),
            'pending' => (int) ($counts['pending'] ?? 0),
            'in_progress' => (int) ($counts['in_progress'] ?? 0),
            'completed' => (int) ($counts['completed'] ?? 0),
            'cancelled' => (int) ($counts['cancelled'] ?? 0),
        ]);
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
    public function store(StoreTaskRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();

        if (!$request->user()->hasRole('super-admin')) {
            $moduleId = $validated['module_id'] ?? null;
            $module = $moduleId ? \App\Models\Module::find((int) $moduleId) : null;
            if (!$moduleId || !$module || !$request->user()->canOnModule($module, 'create')) {
                return $this->message('Forbidden: you lack can_create on this module', 403);
            }
        }

        $validated['created_by'] = $request->user()->id;
        $validated['updated_by'] = $request->user()->id;
        $task = $this->taskService->create($validated);
        return $this->created($task, 'Task created');
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
    public function show(Task $task, Request $request): \Illuminate\Http\JsonResponse
    {
        if (!$request->user()->hasRole('super-admin')) {
            $user = $request->user();
            $module = $task->module;
            if (!$module) {
                return $this->message('Task has no module', 403);
            }
            $isAssignee = $task->assignees()->where('user_id', $user->id)->exists();
            if (!($isAssignee || $user->canOnModule($module, 'read'))) {
                return $this->message('Forbidden: you lack can_read on this module', 403);
            }
        }

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
    public function update(UpdateTaskRequest $request, Task $task): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();

        if (!$request->user()->hasRole('super-admin')) {
            $user = $request->user();
            $module = $task->module;
            if (!$module) {
                return $this->message('Task has no module', 403);
            }
            $isAssignee = $task->assignees()->where('user_id', $user->id)->exists();
            if (!($isAssignee || $user->canOnModule($module, 'update'))) {
                return $this->message('Forbidden: you lack can_update on this module', 403);
            }
        }

        $validated['updated_by'] = $request->user()->id;
        $task = $this->taskService->update($task, $validated);
        return $this->success($task, 'Task updated');
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
    public function kanban(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $query = Task::with('assignees');
        if (!$user->hasRole('super-admin')) {
            $moduleIds = Module::whereHas('rolePermissions', fn($q) => $q->whereIn('role_id', $user->roles()->pluck('roles.id'))->where('can_read', true))->pluck('id');
            $query->where(function ($q) use ($moduleIds, $user) {
                if ($moduleIds->isNotEmpty()) $q->whereIn('module_id', $moduleIds);
                $q->orWhereHas('assignees', fn($a) => $a->where('user_id', $user->id));
            });
        }
        $tasks = $query->latest()->get();
        $grouped = $tasks->groupBy(fn($t) => $t->status ?? 'pending');
        /** @phpstan-ignore-next-line */
        return $this->success($grouped->map(fn($items, $status) => [
            'status' => $status,
            'count' => $items->count(),
            'tasks' => $items->map(function ($t) {
                /** @phpstan-ignore-next-line */
                return [
                    'id' => $t->id,
                    'title' => $t->title,
                    'description' => $t->description,
                    'status' => $t->status,
                    'priority' => $t->priority,
                    'due_date' => $t->due_date,
                    'module_id' => $t->module_id,
                    'assignees' => $t->assignees->map(fn($a) => ['id' => $a->id, 'name' => $a->name]),
                    'created_at' => $t->created_at,
                ];
            }),
        ])->values());
    }

    #[OA\Patch(
        path: '/tasks/{id}/status',
        summary: 'Quick-update task status (for Kanban drag)',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Status updated')]
    )]
    public function updateStatus(Request $request, Task $task): \Illuminate\Http\JsonResponse
    {
        $request->validate(['status' => 'required|string|in:pending,in_progress,completed,cancelled']);
        $user = $request->user();
        if (!$user->hasRole('super-admin')) {
            $module = $task->module;
            $isAssignee = $task->assignees()->where('user_id', $user->id)->exists();
            if (!($isAssignee || ($module && $user->canOnModule($module, 'update')))) {
                return $this->message('Forbidden', 403);
            }
        }
        $task->update(['status' => $request->status, 'updated_by' => $user->id]);
        return $this->message('Status updated');
    }

    public function destroy(Task $task, Request $request): \Illuminate\Http\JsonResponse
    {
        if (!$request->user()->hasRole('super-admin')) {
            $module = $task->module;
            if (!$module || !$request->user()->canOnModule($module, 'delete')) {
                return $this->message('Forbidden: you lack can_delete on this module', 403);
            }
        }

        $this->taskService->delete($task);
        return $this->message('Task deleted');
    }


}
