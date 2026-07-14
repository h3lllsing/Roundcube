<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;

class ModulePermissionService
{
    /** @return array<int, array{id: int, role_id: int, role_name: string|null, can_create: bool, can_read: bool, can_update: bool, can_delete: bool, can_approve: bool, can_export: bool, can_reveal: bool, can_import: bool}> */
    public function getForModule(Module $module): array
    {
        return ModuleRolePermission::where('module_id', $module->id)
            ->with('role')
            ->get()
            ->map(function (ModuleRolePermission $perm) {
                $roleName = $perm->role ? (string) $perm->role->getAttribute('name') : null;

                $result = [
                    'id' => (int) $perm->id,
                    'role_id' => (int) $perm->role_id,
                    'role_name' => $roleName,
                ];
                foreach (config('permissions.keys') as $key) {
                    $result[$key] = (bool) $perm->$key;
                }
                return $result;
            })
            ->all();
    }

    /** @param array<string, bool> $permissions */
    public function setForRole(Module $module, int $roleId, array $permissions): ModuleRolePermission
    {
        $role = Role::find($roleId);
        $isSuperAdmin = $role && $role->slug === 'super-admin';

        $data = [];
        foreach (config('permissions.keys') as $key) {
            $data[$key] = $permissions[$key] ?? false;
        }
        if (! $isSuperAdmin) {
            $data['can_delete'] = false;
        }

        $result = ModuleRolePermission::updateOrCreate(
            ['module_id' => $module->id, 'role_id' => $roleId],
            $data
        );

        Cache::increment('perms_generation');

        return $result;
    }

    public function removeForRole(Module $module, int $roleId): void
    {
        ModuleRolePermission::where('module_id', $module->id)
            ->where('role_id', $roleId)
            ->delete();

        Cache::increment('perms_generation');
    }

    /** @return array{can_create: bool, can_read: bool, can_update: bool, can_delete: bool, can_approve: bool, can_export: bool, can_reveal: bool, can_import: bool}|null */
    public function getUserPermissionsForModule(Module $module, mixed $user): ?array
    {
        $roleIds = $user->roles()->pluck('roles.id');
        $perms = ModuleRolePermission::where('module_id', $module->id)
            ->whereIn('role_id', $roleIds)
            ->get();

        if ($perms->isEmpty()) {
            return null;
        }

        $merged = array_fill_keys(config('permissions.keys'), false);

        foreach ($perms as $p) {
            foreach ($merged as $key => &$val) {
                if ($p->$key) {
                    $val = true;
                }
            }
        }

        return $merged;
    }
}
