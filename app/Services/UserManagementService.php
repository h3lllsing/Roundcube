<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserManagementService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = User::query();

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if (! empty($filters['with_trashed'])) {
            $query->withTrashed();
        } elseif (! empty($filters['trashed_only'])) {
            $query->onlyTrashed();
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['name', 'email', 'created_at']) ? $filters['sort_by'] : 'created_at';
        $sortOrder = in_array($filters['sort_order'] ?? '', ['asc', 'desc']) ? $filters['sort_order'] : 'desc';

        return $query->with('roles')
            ->orderBy($sortBy, $sortOrder)
            ->paginate(min($filters['per_page'] ?? 20, 100));
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            if (! empty($data['roles'])) {
                $roles = Role::whereIn('id', $data['roles'])->get();
                $user->roles()->sync($roles);
            }

            return $user->loadMissing('roles');
        });
    }

    public function update(User $user, array $data, User $currentUser): User
    {
        return DB::transaction(function () use ($user, $data, $currentUser) {
            if (isset($data['name'])) {
                $user->name = $data['name'];
            }
            if (isset($data['email'])) {
                $user->email = $data['email'];
            }
            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }

            $user->save();

            if (isset($data['roles'])) {
                $superAdminRoleId = Role::where('slug', 'super-admin')->value('id');
                if ($superAdminRoleId && $currentUser->id === $user->id) {
                    $currentRoles = $user->roles()->pluck('roles.id')->toArray();
                    if (in_array($superAdminRoleId, $currentRoles) && ! in_array($superAdminRoleId, $data['roles'])) {
                        abort(403, 'Cannot remove your own Super Admin role.');
                    }
                }
                $roles = Role::whereIn('id', $data['roles'])->get();
                $user->roles()->sync($roles);
            }

            return $user->loadMissing('roles');
        });
    }

    public function delete(User $user): void
    {
        $superAdminRoleId = Role::where('slug', 'super-admin')->value('id');
        if ($superAdminRoleId && $user->roles()->where('roles.id', $superAdminRoleId)->exists()) {
            $superAdminCount = User::whereHas('roles', fn ($q) => $q->where('roles.id', $superAdminRoleId))->count();
            if ($superAdminCount <= 1) {
                abort(403, 'Cannot delete the last Super Admin user.');
            }
        }

        $user->delete();
    }

    public function suspend(User $user, User $actor): void
    {
        DB::transaction(function () use ($user, $actor) {
            $user->forceFill(['suspended_at' => now()])->save();

            activity()->event('suspended')
                ->performedOn($user)
                ->causedBy($actor)
                ->log('User suspended: '.$user->email);
        });
    }

    public function unsuspend(User $user, User $actor): void
    {
        DB::transaction(function () use ($user, $actor) {
            $user->forceFill(['suspended_at' => null])->save();

            activity()->event('unsuspended')
                ->performedOn($user)
                ->causedBy($actor)
                ->log('User unsuspended: '.$user->email);
        });
    }
}
