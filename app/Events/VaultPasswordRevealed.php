<?php

namespace App\Events;

use App\Models\User;
use App\Models\VaultEntry;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class VaultPasswordRevealed
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public VaultEntry $entry,
        public ?User $causer,
    ) {}
}
