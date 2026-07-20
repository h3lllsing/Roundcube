<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\EmailAccount;

class EmailAccountCreated implements LoggableEvent
{
    public function __construct(private readonly EmailAccount $emailAccount) {}

    public function getModel(): EmailAccount
    {
        return $this->emailAccount;
    }

    public function getEventName(): string
    {
        return 'created';
    }

    public function getProperties(): array
    {
        return ['email' => $this->emailAccount->email];
    }
}
