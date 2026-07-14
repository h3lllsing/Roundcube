<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\RoleTemplate;
use App\Models\User;
use App\Models\UserModulePermission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

    public function buildRoleSummaries($roles): array
    {
        $rolePermissions = ModuleRolePermission::whereIn('role_id', $roles->pluck('id'))
            ->get()
            ->groupBy('role_id');

        $templates = RoleTemplate::all()->keyBy('slug');

        $roleSummaries = [];
        foreach ($roles as $role) {
            $perms = $rolePermissions->get($role->id, collect());
            $template = $templates->get($role->slug);

            $roleSummaries[$role->id] = [
                'template_name' => $template?->name,
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
