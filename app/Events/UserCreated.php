<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\User;

class UserCreated implements LoggableEvent
{
    public function __construct(private readonly User $user) {}

    public function getModel(): User
    {
        return $this->user;
    }

    public function getEventName(): string
    {
        return 'created';
    }

    public function getProperties(): array
    {
        return ['role' => $this->user->role];
    }
}
