<?php

namespace App\Policies;

use App\Models\Domain;
use App\Models\User;

class DomainPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Domain $domain): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Domain $domain): bool
    {
        return false;
    }

    public function delete(User $user, Domain $domain): bool
    {
        return false;
    }

    public function restore(User $user, Domain $domain): bool
    {
        return false;
    }

    public function forceDelete(User $user, Domain $domain): bool
    {
        return false;
    }

    public function bulkDelete(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
