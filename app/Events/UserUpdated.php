<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\User;

class UserUpdated implements LoggableEvent
{
    public function __construct(
        private readonly User $user,
        private readonly array $oldValues,
        private readonly array $newValues,
    ) {}

    public function getModel(): User
    {
        return $this->user;
    }

    public function getEventName(): string
    {
        return 'updated';
    }

    public function getProperties(): array
    {
        return [
            'old' => $this->oldValues,
            'attributes' => $this->newValues,
        ];
    }
}
