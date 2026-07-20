<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\EmailAccount;
use App\Models\User;

class EmailAccountRevoked implements LoggableEvent
{
    public function __construct(
        private readonly EmailAccount $emailAccount,
        private readonly User $user,
    ) {}

    public function getModel(): EmailAccount
    {
        return $this->emailAccount;
    }

    public function getEventName(): string
    {
        return 'revoke';
    }

    public function getProperties(): array
    {
        return [
            'user_id' => $this->user->id,
            'user_email' => $this->user->email,
            'action' => 'revoke',
        ];
    }
}
