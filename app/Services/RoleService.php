<?php

namespace App\Services;

use App\Models\Module;
use App\Models\Privilege;
use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class RoleService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Role::withCount('privileges', 'users');

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('slug', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->select(['id', 'name', 'slug'])->latest()->paginate($filters['per_page'] ?? 20);
    }

    public function create(array $data): Role
    {
        $role = Role::create($data);

        activity()->event('created')
            ->performedOn($role)
            ->causedBy(Auth::user())
            ->withProperties([
                'name' => $role->name,
                'slug' => $role->slug,
            ])
            ->log('Role created: '.$role->name);

        return $role;
    }

    public function find(int $id): Role
    {
        return Role::with('privileges', 'users')->findOrFail($id);
    }

    public function getAllPrivileges(): \Illuminate\Database\Eloquent\Collection
    {
        return Privilege::orderBy('name')->get(['id', 'name', 'slug']);
    }

    public function update(Role $role, array $data): Role
    {
        $original = $role->getOriginal();
        $role->update($data);

        $changed = $role->getChanges();
        $dirty = array_diff_key($changed, array_flip(['updated_at']));
        $oldValues = array_intersect_key($original, $dirty);

        activity()->event('updated')
            ->performedOn($role)
            ->causedBy(Auth::user())
            ->withProperties([
                'old' => $oldValues,
                'attributes' => $dirty,
            ])
            ->log('Role updated: '.$role->name);

        return $role;
    }

    public function delete(int $id): ?string
    {
        $role = Role::findOrFail($id);

        if (in_array($role->slug, ['admin', 'super-admin'])) {
            return 'Protected roles cannot be deleted.';
        }

        if ($role->users()->count() > 0) {
            return 'Cannot delete role "'.$role->name.'" — it is assigned to '.$role->users()->count().' user(s). Reassign them first.';
        }

        $roleName = $role->name;
        $slug = $role->slug;
        $role->delete();

        activity()->event('deleted')
            ->causedBy(Auth::user())
            ->withProperties([
                'name' => $roleName,
                'slug' => $slug,
            ])
            ->log('Role deleted: '.$roleName);

        return null;
    }

    public function attachPrivilege(int $roleId, int $privilegeId): ?string
    {
        $role = Role::findOrFail($roleId);
        $privilege = Privilege::findOrFail($privilegeId);
        $role->attachPrivilege($privilege);

        activity()->event('updated')
            ->performedOn($role)
            ->causedBy(Auth::user())
            ->withProperties([
                'privilege_id' => $privilege->id,
                'privilege_name' => $privilege->name,
                'action' => 'attached',
            ])
            ->log('Privilege "'.$privilege->name.'" attached to role: '.$role->name);

        return null;
    }

    public function detachPrivilege(int $roleId, int $privilegeId): ?string
    {
        $role = Role::findOrFail($roleId);
        $privilege = Privilege::findOrFail($privilegeId);
        $role->detachPrivilege($privilege);

        activity()->event('updated')
            ->performedOn($role)
            ->causedBy(Auth::user())
            ->withProperties([
                'privilege_id' => $privilege->id,
                'privilege_name' => $privilege->name,
                'action' => 'detached',
            ])
            ->log('Privilege "'.$privilege->name.'" detached from role: '.$role->name);

        return null;
    }

    public function getModuleAccessSummary(int $roleId): array
    {
        $modules = Module::with(['feature', 'rolePermissions' => function ($q) use ($roleId) {
            $q->where('role_id', $roleId);
        }])->orderBy('name')->get();

        $sensitiveSlugs = config('permissions.sensitive_modules', []);
        $sensitivePermKeys = config('permissions.sensitive_permissions', []);

        $accessibleCount = 0;
        $noAccessCount = 0;
        $sensitiveGranted = [];

        foreach ($modules as $module) {
            $rp = $module->rolePermissions->first();
            if ($rp && $rp->can_read) {
                $accessibleCount++;
                if (in_array($module->slug, $sensitiveSlugs)) {
                    foreach ($sensitivePermKeys as $key) {
                        if ($rp->$key) {
                            $sensitiveGranted[] = [
                                'module' => $module->name,
                                'permission' => $key,
                            ];
                        }
                    }
                }
            } else {
                $noAccessCount++;
            }
        }

        return [
            'total_modules' => $modules->count(),
            'accessible_modules' => $accessibleCount,
            'no_access_modules' => $noAccessCount,
            'sensitive_count' => count($sensitiveGranted),
            'sensitive_granted' => $sensitiveGranted,
        ];
    }
}
