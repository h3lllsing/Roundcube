<?php

namespace App\Policies;

use App\Models\Domain;
use App\Models\User;

class DomainPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Domain $domain): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Domain $domain): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Domain $domain): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Domain $domain): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Domain $domain): bool
    {
        return $user->isSuperAdmin();
    }
}
