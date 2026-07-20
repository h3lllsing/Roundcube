<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\EmailAccount;

class EmailAccountRestored implements LoggableEvent
{
    public function __construct(private readonly EmailAccount $emailAccount) {}

    public function getModel(): EmailAccount
    {
        return $this->emailAccount;
    }

    public function getEventName(): string
    {
        return 'restore';
    }

    public function getProperties(): array
    {
        return [
            'action' => 'restore',
            'resource_type' => EmailAccount::class,
            'resource_id' => $this->emailAccount->id,
        ];
    }
}
