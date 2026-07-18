<?php

namespace App\Policies;

use App\Models\EmailAccount;
use App\Models\User;

class EmailAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('emails.manage');
    }

    public function view(User $user, EmailAccount $account): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->hasPermission('emails.manage')) {
            return true;
        }

        return $account->assignedUsers()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('emails.manage');
    }

    public function update(User $user, EmailAccount $account): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('emails.manage');
    }

    public function delete(User $user, EmailAccount $account): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('emails.manage');
    }

    public function restore(User $user, EmailAccount $account): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('emails.manage');
    }

    public function forceDelete(User $user, EmailAccount $account): bool
    {
        return $user->isSuperAdmin();
    }
}
