<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\Domain;

class DomainUpdated implements LoggableEvent
{
    public function __construct(
        private readonly Domain $domain,
        private readonly array $oldValues = [],
        private readonly array $newValues = [],
    ) {}

    public function getModel(): Domain
    {
        return $this->domain;
    }

    public function getEventName(): string
    {
        return 'updated';
    }

    public function getProperties(): array
    {
        if (empty($this->oldValues) && empty($this->newValues)) {
            return ['name' => $this->domain->name];
        }

        return [
            'old' => $this->oldValues,
            'attributes' => $this->newValues,
        ];
    }
}
