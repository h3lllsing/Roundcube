<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\Domain;

class DomainDeleted implements LoggableEvent
{
    public function __construct(
        private readonly Domain $domain,
        private readonly int $deletedBy,
    ) {}

    public function getModel(): Domain
    {
        return $this->domain;
    }

    public function getEventName(): string
    {
        return 'soft_delete';
    }

    public function getProperties(): array
    {
        return [
            'action' => 'soft_delete',
            'resource_type' => Domain::class,
            'resource_id' => $this->domain->id,
            'deleted_by' => $this->deletedBy,
        ];
    }
}
