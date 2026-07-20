<?php

namespace App\Events;

use App\Contracts\LoggableEvent;
use App\Models\Domain;

class DomainCreated implements LoggableEvent
{
    public function __construct(private readonly Domain $domain) {}

    public function getModel(): Domain
    {
        return $this->domain;
    }

    public function getEventName(): string
    {
        return 'created';
    }

    public function getProperties(): array
    {
        return ['name' => $this->domain->name];
    }
}
