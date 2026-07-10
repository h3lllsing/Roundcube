<?php

namespace App\Services;

use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Models\Module;
use App\Models\Task;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function __construct(
        private readonly WebhookService $webhookService
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Task>
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Task::with(['module.feature', 'assignees', 'creator']);

        if (! empty($filters['with_trashed'])) {
            $query->withTrashed();
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (isset($filters['module_id'])) {
            $query->where('module_id', $filters['module_id']);
        }
        if (isset($filters['assigned_to'])) {
            $query->whereHas('assignees', fn ($q) => $q->where('user_id', $filters['assigned_to']));
        }
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }
        if (isset($filters['module_ids']) || isset($filters['my_assignee_id'])) {
            $query->where(function ($q) use ($filters) {
                if (! empty($filters['module_ids'])) {
                    $q->whereIn('module_id', $filters['module_ids']);
                }
                if (! empty($filters['my_assignee_id'])) {
                    $q->orWhereHas('assignees', fn ($a) => $a->where('user_id', $filters['my_assignee_id']));
                }
            });
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $allowedSort = ['created_at', 'updated_at', 'title', 'status', 'priority', 'due_date'];
        if (! in_array($sortBy, $allowedSort)) {
            $sortBy = 'created_at';
        }
        if (! in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        return $query->orderBy($sortBy, $sortOrder)->paginate(min($filters['per_page'] ?? 20, 100));
    }

    public function find(int $id): Task
    {
        return Task::with(['module.feature', 'assignees', 'creator'])->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Task
    {
        $assigneeIds = $data['assignee_ids'] ?? [];
        unset($data['assignee_ids']);

        $task = DB::transaction(function () use ($data, $assigneeIds) {
            $task = Task::create($data);
            if (! empty($assigneeIds)) {
                $task->assignees()->attach($assigneeIds, ['assigned_at' => now()]);
                $task->load('assignees', 'creator');
            }
            return $task;
        });

        TaskCreated::dispatch($task, $assigneeIds);
        $this->webhookService->fire('task.created', $task);

        return $task->load('assignees');
    }

    /** @param array<string, mixed> $data */
    public function update(Task $task, array $data): Task
    {
        $oldStatus = $task->getOriginal('status');
        $assigneeIds = $data['assignee_ids'] ?? null;
        unset($data['assignee_ids']);

        DB::transaction(function () use ($task, $data, $assigneeIds) {
            $task->update($data);
            if ($assigneeIds !== null) {
                $task->assignees()->sync($assigneeIds);
                $task->load('assignees', 'creator');
            }
        });

        if ($assigneeIds !== null) {
            TaskUpdated::dispatch($task, $oldStatus, $assigneeIds);
        } elseif ($oldStatus !== $task->status) {
            TaskUpdated::dispatch($task, $oldStatus, $task->assignees->pluck('id')->toArray());
        }
        $this->webhookService->fire('task.updated', $task);

        return $task->fresh()->load(['module.feature', 'assignees', 'creator', 'updater']);
    }

    public function delete(Task $task): void
    {
        $task->delete();
        Cache::increment('dashboard:version');
    }

    public function getUserTaskCounts(int $userId): array
    {
        $counts = Task::whereHas('assignees', fn ($q) => $q->where('user_id', $userId))
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'total' => (int) array_sum($counts->toArray()),
            'pending' => (int) ($counts['pending'] ?? 0),
            'in_progress' => (int) ($counts['in_progress'] ?? 0),
            'completed' => (int) ($counts['completed'] ?? 0),
            'cancelled' => (int) ($counts['cancelled'] ?? 0),
        ];
    }

    public function getKanbanForUser(?User $user): array
    {
        $query = Task::with('assignees');
        if ($user && !$user->hasRole('super-admin')) {
            $moduleIds = Module::whereHas('rolePermissions', fn ($q) => $q->whereIn('role_id', $user->roles()->pluck('roles.id'))->where('can_read', true))->pluck('id');
            $query->where(function ($q) use ($moduleIds, $user) {
                if ($moduleIds->isNotEmpty()) {
                    $q->whereIn('module_id', $moduleIds);
                }
                $q->orWhereHas('assignees', fn ($a) => $a->where('user_id', $user->id));
            });
        }
        $tasks = $query->latest()->limit(500)->get();
        $grouped = $tasks->groupBy(fn ($t) => $t->status ?? 'pending');

        return $grouped->map(fn ($items, $status) => [
            'status' => $status,
            'count' => $items->count(),
            'tasks' => $items->map(fn ($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'description' => $t->description,
                'status' => $t->status,
                'priority' => $t->priority,
                'due_date' => $t->due_date,
                'module_id' => $t->module_id,
                'assignees' => $t->assignees->map(fn ($a) => ['id' => $a->id, 'name' => $a->name]),
                'created_at' => $t->created_at,
            ]),
        ])->values()->toArray();
    }

    public function buildIndexFiltersForUser(?User $user, array $requestFilters): array
    {
        $filters = $requestFilters;
        if ($user && !$user->hasRole('super-admin')) {
            $filters['module_ids'] = $user->getAccessibleModuleIds('read');
            $filters['my_assignee_id'] = $user->id;
        }
        return $filters;
    }
}
