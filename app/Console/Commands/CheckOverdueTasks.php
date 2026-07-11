<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskOverdue;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOverdueTasks extends Command
{
    protected $signature = 'tasks:check-overdue';
    protected $description = 'Check for overdue tasks and notify assignees';

    public function handle(): int
    {
        $overdue = Task::whereNotNull('due_date')
            ->where('due_date', '<', Carbon::now())
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->with('assignees')
            ->get();

        $count = 0;

        foreach ($overdue as $task) {
            foreach ($task->assignees as $assignee) {
                try {
                    $daysOverdue = (int) Carbon::now()->startOfDay()->diffInDays(Carbon::parse($task->due_date)->startOfDay(), false);
                    $assignee->notify(new TaskOverdue($task, $daysOverdue));
                    $count++;
                } catch (\Throwable $e) {
                    Log::warning("Failed to notify assignee {$assignee->id} for overdue task {$task->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Found {$overdue->count()} overdue tasks, sent {$count} notifications.");

        return 0;
    }
}
