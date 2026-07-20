<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface LoggableEvent
{
    public function getModel(): Model;

    public function getEventName(): string;

    public function getProperties(): array;
}
