<?php

namespace App\Policies;

use App\Models\Domain;
use App\Models\User;

class DomainPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('domains.manage');
    }

    public function view(User $user, Domain $domain): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('domains.manage');
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('domains.manage');
    }

    public function update(User $user, Domain $domain): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('domains.manage');
    }

    public function delete(User $user, Domain $domain): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('domains.manage');
    }

    public function restore(User $user, Domain $domain): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('domains.manage');
    }

    public function forceDelete(User $user, Domain $domain): bool
    {
        return $user->isSuperAdmin();
    }
}
