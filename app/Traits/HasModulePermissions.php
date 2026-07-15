<?php

namespace App\Traits;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\UserModulePermission;
use App\Models\VaultEntry;
use Illuminate\Support\Facades\Cache;

trait HasModulePermissions
{
    private ?array $cachedRoleIds = null;

    private function getRoleIds(): array
    {
        if ($this->cachedRoleIds === null) {
            $this->cachedRoleIds = $this->roles()->pluck('roles.id')->toArray();
        }
        return $this->cachedRoleIds;
    }

    private function permissionCacheKey(): string
    {
        $gen = Cache::get('perms_generation', 0);
        return 'user_perms_'.$this->id.'_v'.$gen;
    }

    /** @deprecated Use Cache::increment('perms_generation') instead. Will be removed in a future cleanup. */
    public function clearPermissionCache(): void
    {
        $gen = Cache::get('perms_generation', 0);
        Cache::forget('user_perms_'.$this->id.'_v'.$gen);
    }

    private function getAllModulePermissionsCached(): array
    {
        return Cache::remember($this->permissionCacheKey(), 60, function () {
            return $this->getAllModulePermissionsFromDb();
        });
    }

    public function canOnModule(Module $module, string $action): bool
    {
        $column = 'can_'.$action;

        $userOverride = UserModulePermission::where('user_id', $this->id)
            ->where('module_id', $module->id)
            ->first();

        if ($userOverride && $userOverride->$column !== null) {
            return $userOverride->$column;
        }

        $hasRolePermission = ModuleRolePermission::whereIn('role_id', $this->getRoleIds())
            ->where('module_id', $module->id)
            ->where($column, true)
            ->exists();

        if ($hasRolePermission) {
            return true;
        }

        if ($action === 'reveal') {
            if ($userOverride && $userOverride->can_reveal !== null && ! $userOverride->can_reveal) {
                return false;
            }

            if ($this->canOnModule($module, 'read')) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, array<string, bool>> */
    public function getAllModulePermissions(): array
    {
        return $this->getAllModulePermissionsCached();
    }

    /** @return array<string, array<string, bool>> */
    private function getAllModulePermissionsFromDb(): array
    {
        $roleIds = $this->getRoleIds();
        $perms = ModuleRolePermission::whereIn('role_id', $roleIds)->get()->groupBy('module_id');
        $allModuleIds = $perms->keys()->toArray();

        $keys = config('permissions.keys');
        $result = [];

        foreach ($perms as $moduleId => $modulePerms) {
            $merged = array_fill_keys($keys, false);
            foreach ($modulePerms as $p) {
                foreach ($keys as $key) {
                    if ($p->$key) {
                        $merged[$key] = true;
                    }
                }
            }
            $result[$moduleId] = $merged;
        }

        $userOverrides = UserModulePermission::where('user_id', $this->id)
            ->get();

        foreach ($userOverrides as $override) {
            $moduleId = $override->module_id;
            if (! isset($result[$moduleId])) {
                $result[$moduleId] = array_fill_keys($keys, false);
            }
            foreach ($keys as $key) {
                if ($override->$key !== null) {
                    $result[$moduleId][$key] = $override->$key;
                }
            }
        }

        return $result;
    }

    /** @return array<int, int> */
    public function getAccessibleModuleIds(string $action): array
    {
        return once(function () use ($action) {
            $column = 'can_'.$action;
            $allPerms = $this->getAllModulePermissionsCached();

            $ids = [];
            foreach ($allPerms as $moduleId => $perms) {
                if (! empty($perms[$column])) {
                    $ids[] = (int) $moduleId;
                }
            }

            return $ids;
        });
    }

    /** @return array<string, array{role: bool|null, user_override: bool|null, effective: bool, source: string}> */
    public function getEffectiveModulePermissions(Module $module): array
    {
        $keys = config('permissions.keys');
        $roleIds = $this->getRoleIds();

        $rolePerm = ModuleRolePermission::whereIn('role_id', $roleIds)
            ->where('module_id', $module->id)
            ->first();

        $userOverride = UserModulePermission::where('user_id', $this->id)
            ->where('module_id', $module->id)
            ->first();

        $result = [];
        foreach ($keys as $key) {
            $roleVal = $rolePerm ? $rolePerm->$key : null;
            $overrideVal = $userOverride ? $userOverride->$key : null;

            if ($overrideVal !== null) {
                $result[$key] = [
                    'role' => $roleVal,
                    'user_override' => $overrideVal,
                    'effective' => $overrideVal,
                    'source' => $overrideVal ? 'User Override' : 'User Override',
                ];
            } elseif ($roleVal !== null) {
                $result[$key] = [
                    'role' => $roleVal,
                    'user_override' => null,
                    'effective' => $roleVal,
                    'source' => 'Role',
                ];
            } else {
                $result[$key] = [
                    'role' => null,
                    'user_override' => null,
                    'effective' => false,
                    'source' => 'None',
                ];
            }
        }

        return $result;
    }

    /** Centralized credential reveal gate. Used by all controllers and Blade views.
     *
     * - Super Admin always passes.
     * - Null module (orphaned record or personal vault entry) → denied for non-SA.
     * - Delegates to canOnModule(module, 'reveal'):
     *   1. User override with can_reveal=true/false takes priority
     *   2. Multiple roles are OR-merged
     *   3. Auto-grant: if user has can_read (role or override) and no explicit can_reveal=false override → reveal granted
     * - NO owner bypass (unlike canAccessVault which grants vault entry access to its owner).
     *   Vault entry owners without can_reveal on the associated module cannot reveal their own password.
     *
     * For vault entries linked to a resource module (module_id set), this checks
     * that module's permissions. For standalone/personal vault entries (module_id null),
     * only super-admin can reveal. */
    public function canRevealCredentialsFor(?Module $module): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }
        if (!$module) {
            return false;
        }
        return $this->canOnModule($module, 'reveal');
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
            $allPerms = $this->getAllModulePermissionsCached();
            if (isset($allPerms[$vault->module_id]['can_read']) && $allPerms[$vault->module_id]['can_read']) {
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
