<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleRolePermission;

class ModulePermissionService
{
    /** @return array<int, array{id: int, role_id: int, role_name: string|null, can_create: bool, can_read: bool, can_update: bool, can_delete: bool, can_approve: bool, can_export: bool}> */
    public function getForModule(Module $module): array
    {
        return ModuleRolePermission::where('module_id', $module->id)
            ->with('role')
            ->get()
            ->map(function (ModuleRolePermission $perm) {
                $roleName = $perm->role ? (string) $perm->role->getAttribute('name') : null;
                return [
                    'id' => (int) $perm->id,
                    'role_id' => (int) $perm->role_id,
                    'role_name' => $roleName,
                    'can_create' => (bool) $perm->can_create,
                    'can_read' => (bool) $perm->can_read,
                    'can_update' => (bool) $perm->can_update,
                    'can_delete' => (bool) $perm->can_delete,
                    'can_approve' => (bool) $perm->can_approve,
                    'can_export' => (bool) $perm->can_export,
                ];
            })
            ->all();
    }

    /** @param array<string, bool> $permissions */
    public function setForRole(Module $module, int $roleId, array $permissions): ModuleRolePermission
    {
        $data = [
            'can_create' => $permissions['can_create'] ?? false,
            'can_read' => $permissions['can_read'] ?? false,
            'can_update' => $permissions['can_update'] ?? false,
            'can_delete' => $permissions['can_delete'] ?? false,
            'can_approve' => $permissions['can_approve'] ?? false,
            'can_export' => $permissions['can_export'] ?? false,
        ];

        return ModuleRolePermission::updateOrCreate(
            ['module_id' => $module->id, 'role_id' => $roleId],
            $data
        );
    }

    public function removeForRole(Module $module, int $roleId): void
    {
        ModuleRolePermission::where('module_id', $module->id)
            ->where('role_id', $roleId)
            ->delete();
    }

    /** @return array{can_create: bool, can_read: bool, can_update: bool, can_delete: bool, can_approve: bool, can_export: bool}|null */
    public function getUserPermissionsForModule(Module $module, mixed $user): ?array
    {
        $roleIds = $user->roles()->pluck('roles.id');
        $perms = ModuleRolePermission::where('module_id', $module->id)
            ->whereIn('role_id', $roleIds)
            ->get();

        if ($perms->isEmpty()) return null;

        $merged = [
            'can_create' => false,
            'can_read' => false,
            'can_update' => false,
            'can_delete' => false,
            'can_approve' => false,
            'can_export' => false,
        ];

        foreach ($perms as $p) {
            foreach ($merged as $key => &$val) {
                if ($p->$key) $val = true;
            }
        }

        return $merged;
    }
}
