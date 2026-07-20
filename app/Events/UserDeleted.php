<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\User;

class UserDeleted implements LoggableEvent
{
    public function __construct(
        private readonly User $user,
        private readonly string $email,
        private readonly string $name,
    ) {}

    public function getModel(): User
    {
        return $this->user;
    }

    public function getEventName(): string
    {
        return 'deleted';
    }

    public function getProperties(): array
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
        ];
    }
}
