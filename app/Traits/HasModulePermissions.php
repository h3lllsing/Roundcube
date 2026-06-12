<?php

namespace App\Traits;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\VaultEntry;

trait HasModulePermissions
{
    public function canOnModule(Module $module, string $action): bool
    {
        $column = 'can_' . $action;
        return ModuleRolePermission::whereIn('role_id', $this->roles()->pluck('roles.id'))
            ->where('module_id', $module->id)
            ->where($column, true)
            ->exists();
    }

    /** @return array{can_create: bool, can_read: bool, can_update: bool, can_delete: bool, can_approve: bool, can_export: bool}|null */
    public function getModulePermissions(Module $module): ?array
    {
        $perm = ModuleRolePermission::whereIn('role_id', $this->roles()->pluck('roles.id'))
            ->where('module_id', $module->id)
            ->first();

        if (!$perm) return null;

        return [
            'can_create' => $perm->can_create,
            'can_read' => $perm->can_read,
            'can_update' => $perm->can_update,
            'can_delete' => $perm->can_delete,
            'can_approve' => $perm->can_approve,
            'can_export' => $perm->can_export,
        ];
    }

    /** @return array<int, array{can_create: bool, can_read: bool, can_update: bool, can_delete: bool, can_approve: bool, can_export: bool}> */
    public function getAllModulePermissions(): array
    {
        $roleIds = $this->roles()->pluck('roles.id');
        $perms = ModuleRolePermission::whereIn('role_id', $roleIds)->get()->groupBy('module_id');
        $result = [];

        foreach ($perms as $moduleId => $modulePerms) {
            $merged = [
                'can_create' => false,
                'can_read' => false,
                'can_update' => false,
                'can_delete' => false,
                'can_approve' => false,
                'can_export' => false,
            ];
            foreach ($modulePerms as $p) {
                foreach ($merged as $key => &$val) {
                    if ($p->$key) $val = true;
                }
            }
            $result[$moduleId] = $merged;
        }

        return $result;
    }

    /** @return array<int, int> */
    public function getAccessibleModuleIds(string $action): array
    {
        return Module::whereHas('rolePermissions', function ($q) use ($action) {
            $q->whereIn('role_id', $this->roles()->pluck('roles.id'))
              ->where('can_' . $action, true);
        })->pluck('id')->toArray();
    }

    public function canAccessVault(VaultEntry $vault): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }
        if ($vault->user_id === $this->id) {
            return true;
        }
        if ($vault->module_id) {
            $hasAccess = Module::whereHas('rolePermissions', fn($q) =>
                $q->whereIn('role_id', $this->roles()->pluck('roles.id'))->where('can_read', true)
            )->where('id', $vault->module_id)->exists();
            if ($hasAccess) {
                return true;
            }
        }
        return false;
    }

    public function isVaultOwner(VaultEntry $vault): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }
        return $vault->user_id === $this->id;
    }
}
