<?php

namespace App\Services;

use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class TaskService
{
    /**
     * @param array<string, mixed> $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, Task>
     */
    public function list(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Task::with(['module.feature', 'assignees', 'creator']);

        if (!empty($filters['with_trashed'])) {
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
            $query->whereHas('assignees', fn($q) => $q->where('user_id', $filters['assigned_to']));
        }
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }
        if (isset($filters['module_ids']) || isset($filters['my_assignee_id'])) {
            $query->where(function ($q) use ($filters) {
                if (!empty($filters['module_ids'])) {
                    $q->whereIn('module_id', $filters['module_ids']);
                }
                if (!empty($filters['my_assignee_id'])) {
                    $q->orWhereHas('assignees', fn($a) => $a->where('user_id', $filters['my_assignee_id']));
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
        if (!in_array($sortBy, $allowedSort)) $sortBy = 'created_at';
        if (!in_array($sortOrder, ['asc', 'desc'])) $sortOrder = 'desc';

        return $query->orderBy($sortBy, $sortOrder)->paginate(min($filters['per_page'] ?? 20, 100));
    }

    public function find(int $id): Task
    {
        return Task::with(['module.feature', 'assignees', 'creator'])->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Task
    {
        Cache::increment('dashboard:version');
        $assigneeIds = $data['assignee_ids'] ?? [];
        unset($data['assignee_ids']);

        $task = Task::create($data);

        if (!empty($assigneeIds)) {
            $task->assignees()->attach($assigneeIds, ['assigned_at' => now()]);
            $task->load('assignees', 'creator');
        }

        TaskCreated::dispatch($task, $assigneeIds);

        return $task->load('assignees');
    }

    /** @param array<string, mixed> $data */
    public function update(Task $task, array $data): Task
    {
        Cache::increment('dashboard:version');
        $oldStatus = $task->getOriginal('status');
        $assigneeIds = $data['assignee_ids'] ?? null;
        unset($data['assignee_ids']);

        $task->update($data);

        if ($assigneeIds !== null) {
            $task->assignees()->sync($assigneeIds);
            $task->load('assignees', 'creator');
            TaskUpdated::dispatch($task, $oldStatus, $assigneeIds);
        } elseif ($oldStatus !== $task->status) {
            TaskUpdated::dispatch($task, $oldStatus, $task->assignees->pluck('id')->toArray());
        }

        return $task->fresh()->load(['module.feature', 'assignees', 'creator', 'updater']);
    }

    public function delete(Task $task): void
    {
        $task->delete();
        Cache::increment('dashboard:version');
    }
}