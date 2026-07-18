<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use App\Models\UserModulePermission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserPermissionService
{
    public function getSuperAdminRoleId(): ?int
    {
        return Cache::remember('super_admin_role_id', 86400, fn () =>
            Role::where('slug', 'super-admin')->value('id')
        );
    }

    public function getModulesWithFeatures()
    {
        return Module::with('feature')->orderBy('name')->get();
    }

    public function preventSuperAdminAssignment(Request $request): void
    {
        $superAdminRoleId = $this->getSuperAdminRoleId();
        if ($superAdminRoleId && $request->has('roles')) {
            $roles = $request->input('roles', []);
            if (in_array($superAdminRoleId, $roles)) {
                abort(403, 'Cannot assign Super Admin role through this form.');
            }
        }
    }

    public function saveUserModulePermissions(User $user, ?array $permissions): void
    {
        if ($permissions === null) {
            return;
        }

        $permissionColumns = config('permissions.keys');
        $isSuperAdmin = $user->hasRole('super-admin');

        DB::transaction(function () use ($user, $permissions, $permissionColumns, $isSuperAdmin) {
            UserModulePermission::where('user_id', $user->id)
                ->lockForUpdate()
                ->get();

            $upsertData = [];
            $deleteModuleIds = [];

            foreach ($permissions as $moduleId => $perms) {
                $data = ['user_id' => $user->id, 'module_id' => $moduleId];
                $hasNonNull = false;

                foreach ($permissionColumns as $col) {
                    $val = $perms[$col] ?? null;
                    if ($val === '1' || $val === 1 || $val === true) {
                        $data[$col] = true;
                        $hasNonNull = true;
                    } elseif ($val === '0' || $val === 0 || $val === false) {
                        $data[$col] = false;
                        $hasNonNull = true;
                    } else {
                        $data[$col] = null;
                    }
                }

                if (! $isSuperAdmin) {
                    $data['can_delete'] = false;
                }

                if ($hasNonNull) {
                    $upsertData[] = $data;
                } else {
                    $deleteModuleIds[] = (int) $moduleId;
                }
            }

            if ($deleteModuleIds !== []) {
                UserModulePermission::where('user_id', $user->id)
                    ->whereIn('module_id', $deleteModuleIds)
                    ->delete();
            }

            if ($upsertData !== []) {
                DB::table('user_module_permissions')->upsert($upsertData, ['user_id', 'module_id']);
            }

            $incomingModuleIds = array_keys($permissions);
            if ($incomingModuleIds === []) {
                UserModulePermission::where('user_id', $user->id)->delete();
            } else {
                UserModulePermission::where('user_id', $user->id)
                    ->whereNotIn('module_id', $incomingModuleIds)
                    ->delete();
            }

            Cache::increment('perms_generation');
        });

        $enabledPerms = array_filter($permissions, fn ($p) => is_array($p) && collect($p)->contains(fn ($v) => $v === '1' || $v === 1 || $v === true));

        activity()->event('updated')
            ->performedOn($user)
            ->causedBy(Auth::user())
            ->withProperties([
                'type' => 'permission_overrides',
                'modules_updated' => array_keys($enabledPerms),
            ])
            ->log('Permission overrides updated for user: '.$user->email);
    }

    public function clonePermissions(User $source, User $target): void
    {
        $overrides = UserModulePermission::where('user_id', $source->id)->get();
        foreach ($overrides as $override) {
            UserModulePermission::create([
                'user_id' => $target->id,
                'module_id' => $override->module_id,
                'can_create' => $override->can_create,
                'can_read' => $override->can_read,
                'can_update' => $override->can_update,
                'can_delete' => $override->can_delete,
                'can_approve' => $override->can_approve,
                'can_export' => $override->can_export,
                'can_reveal' => $override->can_reveal,
                'can_import' => $override->can_import,
            ]);
        }
    }

    /** @return array{access: string, manage: string, import: string, export: string} */
    public function mapDbRowToControls(?UserModulePermission $row, Module $module): array
    {
        if ($row === null) {
            return ['access' => 'inherit', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'inherit'];
        }

        $controls = [];

        // Access
        $cr = $row->can_read;
        $crv = $row->can_reveal;
        if ($cr === null && $crv === null) {
            $controls['access'] = 'inherit';
        } elseif ($cr === true && $crv === true) {
            $controls['access'] = 'allow';
        } elseif ($cr === false && $crv === false) {
            $controls['access'] = 'deny';
        } else {
            $controls['access'] = ($cr === true) ? 'allow' : 'deny';
        }

        // Manage
        $cc = $row->can_create;
        $cu = $row->can_update;
        if ($cc === null && $cu === null) {
            $controls['manage'] = 'inherit';
        } elseif ($cc === true && $cu === true) {
            $controls['manage'] = 'allow';
        } elseif ($cc === false && $cu === false) {
            $controls['manage'] = 'deny';
        } else {
            $controls['manage'] = ($cc === true) ? 'allow' : 'deny';
        }

        // Import
        $ci = $row->can_import;
        $controls['import'] = ($ci === null) ? 'inherit' : (($ci === true) ? 'allow' : 'deny');

        // Export
        $ce = $row->can_export;
        $controls['export'] = ($ce === null) ? 'inherit' : (($ce === true) ? 'allow' : 'deny');

        return $controls;
    }

    /** @param array<string, string> $controlValues */
    public function controlsToDbRow(array $controlValues, Module $module, bool $isSuperAdmin): array
    {
        $data = [
            'can_read' => null,
            'can_reveal' => null,
            'can_create' => null,
            'can_update' => null,
            'can_import' => null,
            'can_export' => null,
            'can_delete' => null,
            'can_approve' => null,
        ];

        $access = $controlValues['access'] ?? 'inherit';
        $manage = $controlValues['manage'] ?? 'inherit';
        $import = $controlValues['import'] ?? 'inherit';
        $export = $controlValues['export'] ?? 'inherit';

        // Full Access convenience
        if (!empty($controlValues['full_access'])) {
            $access = 'allow';
            $manage = 'allow';
            $import = $module->isImportSupported() ? 'allow' : 'inherit';
            $export = $module->isExportSupported() ? 'allow' : 'inherit';
        }

        // Access
        if ($access === 'allow') {
            $data['can_read'] = true;
            $data['can_reveal'] = true;
        } elseif ($access === 'deny') {
            $data['can_read'] = false;
            $data['can_reveal'] = false;
        }

        // Manage
        if ($manage === 'allow') {
            $data['can_create'] = true;
            $data['can_update'] = true;
        } elseif ($manage === 'deny') {
            $data['can_create'] = false;
            $data['can_update'] = false;
        }

        // Import
        if ($import === 'allow' && $module->isImportSupported()) {
            $data['can_import'] = true;
        } elseif ($import === 'deny' && $module->isImportSupported()) {
            $data['can_import'] = false;
        }

        // Export
        if ($export === 'allow' && $module->isExportSupported()) {
            $data['can_export'] = true;
        } elseif ($export === 'deny' && $module->isExportSupported()) {
            $data['can_export'] = false;
        }

        if (! $isSuperAdmin) {
            $data['can_delete'] = false;
        }

        return $data;
    }

    /** @param array<int, array<string, string>> $controls moduleId => [access, manage, import, export, full_access] */
    public function saveUserControls(User $user, array $controls): void
    {
        $isSuperAdmin = $user->hasRole('super-admin');

        DB::transaction(function () use ($user, $controls, $isSuperAdmin) {
            UserModulePermission::where('user_id', $user->id)
                ->lockForUpdate()
                ->get();

            $existingRows = UserModulePermission::where('user_id', $user->id)
                ->get()
                ->keyBy('module_id');

            $upsertData = [];
            $deleteModuleIds = [];

            foreach ($controls as $moduleId => $controlValues) {
                $module = Module::find($moduleId);
                if (!$module) {
                    continue;
                }

                $data = $this->controlsToDbRow($controlValues, $module, $isSuperAdmin);
                $data['user_id'] = $user->id;
                $data['module_id'] = (int) $moduleId;

                // Preserve existing hidden columns for all users
                $existing = $existingRows->get((int) $moduleId);
                if ($existing) {
                    $data['can_approve'] = $existing->can_approve;
                    if ($isSuperAdmin) {
                        $data['can_delete'] = $existing->can_delete;
                    }
                }

                // Preserve legacy can_read/can_reveal mismatch unless Access intentionally changed
                if ($existing) {
                    $accessUnchanged = (($controlValues['_access_unchanged'] ?? '1') === '1');
                    $cr = $existing->can_read;
                    $crv = $existing->can_reveal;
                    $accessMismatch = !($cr === null && $crv === null) && !($cr === true && $crv === true) && !($cr === false && $crv === false);
                    if ($accessUnchanged && $accessMismatch) {
                        $data['can_read'] = $cr;
                        $data['can_reveal'] = $crv;
                    }

                    $manageUnchanged = (($controlValues['_manage_unchanged'] ?? '1') === '1');
                    $cc = $existing->can_create;
                    $cu = $existing->can_update;
                    $manageMismatch = !($cc === null && $cu === null) && !($cc === true && $cu === true) && !($cc === false && $cu === false);
                    if ($manageUnchanged && $manageMismatch) {
                        $data['can_create'] = $cc;
                        $data['can_update'] = $cu;
                    }
                }

                // Delete row only when no meaningful override remains
                $uiColumns = ['can_read', 'can_reveal', 'can_create', 'can_update', 'can_import', 'can_export'];
                $allUiNull = true;
                foreach ($uiColumns as $col) {
                    if ($data[$col] !== null) {
                        $allUiNull = false;
                        break;
                    }
                }

                $hasHiddenOverride = $data['can_approve'] !== null;
                $hasDeleteOverride = $isSuperAdmin && $data['can_delete'] !== null;

                if ($allUiNull && !$hasHiddenOverride && !$hasDeleteOverride) {
                    $deleteModuleIds[] = (int) $moduleId;
                } else {
                    $upsertData[] = $data;
                }
            }

            if ($deleteModuleIds !== []) {
                UserModulePermission::where('user_id', $user->id)
                    ->whereIn('module_id', $deleteModuleIds)
                    ->delete();
            }

            if ($upsertData !== []) {
                DB::table('user_module_permissions')->upsert($upsertData, ['user_id', 'module_id']);
            }

            $incomingModuleIds = array_keys($controls);
            if ($incomingModuleIds === []) {
                UserModulePermission::where('user_id', $user->id)->delete();
            } else {
                UserModulePermission::where('user_id', $user->id)
                    ->whereNotIn('module_id', $incomingModuleIds)
                    ->delete();
            }

            Cache::increment('perms_generation');
        });

        $changedModules = array_filter($controls, fn ($c) => is_array($c));
        activity()->event('updated')
            ->performedOn($user)
            ->causedBy(Auth::user())
            ->withProperties([
                'type' => 'permission_overrides',
                'modules_updated' => array_keys($changedModules),
            ])
            ->log('Permission overrides updated for user: '.$user->email);
    }

    public function buildRoleSummaries($roles): array
    {
        $rolePermissions = ModuleRolePermission::whereIn('role_id', $roles->pluck('id'))
            ->get()
            ->groupBy('role_id');

        $roleSummaries = [];
        foreach ($roles as $role) {
            $perms = $rolePermissions->get($role->id, collect());

            $roleSummaries[$role->id] = [
                'template_name' => null,
                'modules_count' => $perms->count(),
                'permissions' => [
                    'can_read' => $perms->contains('can_read', true),
                    'can_create' => $perms->contains('can_create', true),
                    'can_update' => $perms->contains('can_update', true),
                    'can_delete' => $perms->contains('can_delete', true),
                    'can_approve' => $perms->contains('can_approve', true),
                    'can_export' => $perms->contains('can_export', true),
                    'can_reveal' => $perms->contains('can_reveal', true),
                    'can_import' => $perms->contains('can_import', true),
                ],
            ];
        }

        return $roleSummaries;
    }

    public function getUserOverrideMap(int $userId): array
    {
        return UserModulePermission::where('user_id', $userId)
            ->get()
            ->keyBy('module_id')
            ->map(fn ($p) => [
                'can_create' => $p->can_create,
                'can_read' => $p->can_read,
                'can_update' => $p->can_update,
                'can_delete' => $p->can_delete,
                'can_export' => $p->can_export,
                'can_reveal' => $p->can_reveal,
                'can_import' => $p->can_import,
            ])
            ->toArray();
    }
}
