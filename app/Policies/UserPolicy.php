<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? null : false;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, User $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id && $model->role === 'super-admin') {
            return false;
        }

        return true;
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->role === 'super-admin' && User::where('role', 'super-admin')->count() <= 1) {
            return false;
        }

        return true;
    }

    public function restore(User $user, User $model): bool
    {
        return true;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return true;
    }

    public function suspend(User $user, User $model): bool
    {
        return true;
    }

    public function unsuspend(User $user, User $model): bool
    {
        return true;
    }

    public function bulkDelete(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
