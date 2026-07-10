<?php

namespace App\Reports;

use App\Models\Task;

class TaskReports extends ReportProvider
{
    public function slug(): string
    {
        return 'tasks';
    }

    public function label(): string
    {
        return 'Tasks';
    }

    public function description(): string
    {
        return 'Task management reports for pending and overdue items.';
    }

    public function icon(): ?string
    {
        return 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4';
    }

    public function reports(): array
    {
        return [
            'pending' => [
                'slug' => 'pending',
                'label' => 'Pending Tasks',
                'description' => 'All tasks that are not yet completed, ordered by due date.',
                'columns' => ['Title', 'Status', 'Priority', 'Due Date', 'Assignees'],
                'query' => function (array $filters, $user, $accessibleIds) {
                    $query = Task::where('status', '!=', 'completed')
                        ->when(!$user->hasRole('super-admin'), function ($q) use ($accessibleIds, $user) {
                            $q->where(function ($q) use ($accessibleIds, $user) {
                                if ($accessibleIds !== null && !empty($accessibleIds)) {
                                    $q->whereIn('module_id', $accessibleIds);
                                }
                                $q->orWhereHas('assignees', fn($a) => $a->where('user_id', $user->id));
                            });
                        })
                        ->when($filters['search'] ?? null, fn($q, $v) => $q->where('title', 'like', "%{$v}%"))
                        ->orderBy('due_date')
                        ->orderBy('created_at', 'desc');
                    return $query->get(['id', 'title', 'status', 'priority', 'due_date']);
                },
            ],
            'overdue' => [
                'slug' => 'overdue',
                'label' => 'Overdue Tasks',
                'description' => 'Tasks past their due date that have not been completed.',
                'columns' => ['Title', 'Status', 'Priority', 'Due Date', 'Days Overdue'],
                'query' => function (array $filters, $user, $accessibleIds) {
                    $query = Task::where('status', '!=', 'completed')
                        ->whereNotNull('due_date')
                        ->where('due_date', '<', now())
                        ->when(!$user->hasRole('super-admin'), function ($q) use ($accessibleIds, $user) {
                            $q->where(function ($q) use ($accessibleIds, $user) {
                                if ($accessibleIds !== null && !empty($accessibleIds)) {
                                    $q->whereIn('module_id', $accessibleIds);
                                }
                                $q->orWhereHas('assignees', fn($a) => $a->where('user_id', $user->id));
                            });
                        })
                        ->orderBy('due_date')
                        ->get(['id', 'title', 'status', 'priority', 'due_date']);
                },
            ],
        ];
    }
}
