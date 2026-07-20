<?php

namespace App\Policies;

use App\Models\User;

class ActivityLogPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user): bool
    {
        return false;
    }
}
