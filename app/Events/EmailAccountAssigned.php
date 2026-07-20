<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\EmailAccount;
use App\Models\User;

class EmailAccountAssigned implements LoggableEvent
{
    public function __construct(
        private readonly EmailAccount $emailAccount,
        private readonly User $assignedUser,
        private readonly bool $canSend,
        private readonly bool $canReceive,
    ) {}

    public function getModel(): EmailAccount
    {
        return $this->emailAccount;
    }

    public function getEventName(): string
    {
        return 'assign';
    }

    public function getProperties(): array
    {
        return [
            'user_id' => $this->assignedUser->id,
            'user_email' => $this->assignedUser->email,
            'can_send' => $this->canSend,
            'can_receive' => $this->canReceive,
            'action' => 'assign',
        ];
    }
}
