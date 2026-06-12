<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Models\User;
use App\Notifications\TaskAssigned;

class SendTaskAssignedNotification
{
    public function handle(TaskCreated|TaskUpdated $event): void
    {
        $users = User::whereIn('id', $event->assigneeIds)->get();
        foreach ($users as $user) {
            $user->notify(new TaskAssigned($event->task));
        }
    }
}
