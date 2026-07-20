<?php

namespace App\Policies;

use App\Models\EmailAccount;
use App\Models\User;

class EmailAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, EmailAccount $emailAccount): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, EmailAccount $emailAccount): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, EmailAccount $emailAccount): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, EmailAccount $emailAccount): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, EmailAccount $emailAccount): bool
    {
        return $user->isSuperAdmin();
    }
}
