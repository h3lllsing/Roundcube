<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

class ExpiryWarningTriggered
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Model $item,
        public string $type,
        public User $user,
        public int $daysRemaining,
    ) {}
}
