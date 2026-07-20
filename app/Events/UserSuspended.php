<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\User;

class UserSuspended implements LoggableEvent
{
    public function __construct(
        private readonly User $user,
        private readonly ?string $reason,
    ) {}

    public function getModel(): User
    {
        return $this->user;
    }

    public function getEventName(): string
    {
        return 'suspended';
    }

    public function getProperties(): array
    {
        return ['reason' => $this->reason];
    }
}
