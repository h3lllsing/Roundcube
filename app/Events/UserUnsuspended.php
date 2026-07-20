<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\User;

class UserUnsuspended implements LoggableEvent
{
    public function __construct(private readonly User $user) {}

    public function getModel(): User
    {
        return $this->user;
    }

    public function getEventName(): string
    {
        return 'unsuspended';
    }

    public function getProperties(): array
    {
        return [];
    }
}
