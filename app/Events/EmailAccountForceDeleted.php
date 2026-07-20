<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\EmailAccount;

class EmailAccountForceDeleted implements LoggableEvent
{
    public function __construct(
        private readonly EmailAccount $emailAccount,
        private readonly int $id,
    ) {}

    public function getModel(): EmailAccount
    {
        return $this->emailAccount;
    }

    public function getEventName(): string
    {
        return 'force_delete';
    }

    public function getProperties(): array
    {
        return [
            'action' => 'force_delete',
            'resource_type' => EmailAccount::class,
            'resource_id' => $this->id,
        ];
    }
}
