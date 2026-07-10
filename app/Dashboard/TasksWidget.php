<?php

namespace App\Dashboard;

use App\Models\Task;
use App\Models\User;

class TasksWidget
{
    public const SLUG = 'tasks';

    public function cacheTtl(): int
    {
        return 120;
    }

    public function data(User $user, ?array $accessibleIds = null): array
    {
        $isSA = $user->hasRole('super-admin');

        $taskQuery = Task::query();
        if (!$isSA) {
            $taskQuery->where(function ($q) use ($accessibleIds, $user) {
                if ($accessibleIds !== null && !empty($accessibleIds)) {
                    $q->whereIn('module_id', $accessibleIds);
                }
                $q->orWhereHas('assignees', fn($a) => $a->where('user_id', $user->id));
            });
        }

        $tasksByStatus = (clone $taskQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalTasks = array_sum($tasksByStatus->toArray());

        $overdueTasks = (clone $taskQuery)
            ->where('due_date', '<', now())
            ->where('status', '!=', 'completed')
            ->count();

        $dueThisWeek = (clone $taskQuery)
            ->whereBetween('due_date', [now()->startOfDay(), now()->addWeek()->endOfDay()])
            ->count();

        $myPending = Task::whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
            ->where('status', '!=', 'completed')
            ->count();

        $myTotal = Task::whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
            ->count();

        return [
            'tasks' => [
                'tasks_by_status' => $tasksByStatus,
                'total_tasks' => $totalTasks,
                'overdue_tasks' => $overdueTasks,
                'due_this_week' => $dueThisWeek,
                'my_pending' => $myPending,
                'my_total' => $myTotal,
            ],
        ];
    }
}
