<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class TaskUpdated
{
    use Dispatchable, InteractsWithSockets;

    /** @param array<int, int> $assigneeIds */
    public function __construct(
        public Task $task,
        public ?string $oldStatus,
        public array $assigneeIds,
    ) {}
}
