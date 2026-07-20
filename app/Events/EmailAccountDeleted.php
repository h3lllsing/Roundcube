<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\EmailAccount;

class EmailAccountDeleted implements LoggableEvent
{
    public function __construct(
        private readonly EmailAccount $emailAccount,
        private readonly int $deletedBy,
    ) {}

    public function getModel(): EmailAccount
    {
        return $this->emailAccount;
    }

    public function getEventName(): string
    {
        return 'soft_delete';
    }

    public function getProperties(): array
    {
        return [
            'action' => 'soft_delete',
            'resource_type' => EmailAccount::class,
            'resource_id' => $this->emailAccount->id,
            'deleted_by' => $this->deletedBy,
        ];
    }
}
