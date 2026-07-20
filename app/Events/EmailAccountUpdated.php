<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\EmailAccount;

class EmailAccountUpdated implements LoggableEvent
{
    public function __construct(
        private readonly EmailAccount $emailAccount,
        private readonly array $oldValues = [],
        private readonly array $newValues = [],
    ) {}

    public function getModel(): EmailAccount
    {
        return $this->emailAccount;
    }

    public function getEventName(): string
    {
        return 'updated';
    }

    public function getProperties(): array
    {
        if (empty($this->oldValues) && empty($this->newValues)) {
            return ['email' => $this->emailAccount->email];
        }

        return [
            'old' => $this->oldValues,
            'attributes' => $this->newValues,
        ];
    }
}
