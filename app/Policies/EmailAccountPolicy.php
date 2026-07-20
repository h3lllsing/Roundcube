<?php

namespace App\Policies;

use App\Models\EmailAccount;
use App\Models\User;

class EmailAccountPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, EmailAccount $emailAccount): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, EmailAccount $emailAccount): bool
    {
        return false;
    }

    public function delete(User $user, EmailAccount $emailAccount): bool
    {
        return false;
    }

    public function restore(User $user, EmailAccount $emailAccount): bool
    {
        return false;
    }

    public function forceDelete(User $user, EmailAccount $emailAccount): bool
    {
        return false;
    }

    public function autoDiscover(User $user): bool
    {
        return false;
    }

    public function bulkDelete(User $user): bool
    {
        return $user->isAdmin();
    }
}
