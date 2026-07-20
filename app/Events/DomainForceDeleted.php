<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\Domain;

class DomainForceDeleted implements LoggableEvent
{
    public function __construct(
        private readonly Domain $domain,
        private readonly int $id,
    ) {}

    public function getModel(): Domain
    {
        return $this->domain;
    }

    public function getEventName(): string
    {
        return 'force_delete';
    }

    public function getProperties(): array
    {
        return [
            'action' => 'force_delete',
            'resource_type' => Domain::class,
            'resource_id' => $this->id,
        ];
    }
}
