<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

class MonitorCheckFailed
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Model $item,
        public string $type,
        public string $error,
        public ?int $itemId = null,
    ) {}
}
