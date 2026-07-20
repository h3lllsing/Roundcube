<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\Domain;

class DomainRestored implements LoggableEvent
{
    public function __construct(private readonly Domain $domain) {}

    public function getModel(): Domain
    {
        return $this->domain;
    }

    public function getEventName(): string
    {
        return 'restore';
    }

    public function getProperties(): array
    {
        return [
            'action' => 'restore',
            'resource_type' => Domain::class,
            'resource_id' => $this->domain->id,
        ];
    }
}
