<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\RoleTemplate;
use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RoleTemplateService
{
    private array $permissionColumns;

    public function __construct()
    {
        $this->permissionColumns = RoleTemplate::$permissionColumns;
    }

    public function getViewData(string $id): array
    {
        $template = RoleTemplate::findOrFail($id);
        $modules = Module::with('feature')->orderBy('name')->get();
        $allRoles = Role::orderBy('name')->get();

        return compact('template', 'modules', 'allRoles');
    }

    public function computeDiff(Role $role, Collection $modules, array $permissionsJson): array
    {
        $existingPerms = ModuleRolePermission::where('role_id', $role->id)
            ->get()
            ->keyBy('module_id');

        $added = [];
        $changed = [];
        $unchanged = [];

        foreach ($permissionsJson as $slug => $perms) {
            $module = $modules->get($slug);
            if (! $module) {
                continue;
            }

            $existing = $existingPerms->get($module->id);

            $templateValues = array_intersect_key($perms, array_flip($this->permissionColumns));
            $templateValues = array_merge(array_fill_keys($this->permissionColumns, false), $templateValues);
            $templateValues = array_map(fn ($v) => (bool) $v, $templateValues);

            if (! $existing) {
                $added[] = ['module' => $module, 'template_values' => $templateValues];
            } else {
                $existingValues = [];
                foreach ($this->permissionColumns as $col) {
                    $existingValues[$col] = (bool) $existing->$col;
                }

                if ($existingValues === $templateValues) {
                    $unchanged[] = ['module' => $module, 'current_values' => $existingValues];
                } else {
                    $changed[] = [
                        'module' => $module,
                        'current_values' => $existingValues,
                        'template_values' => $templateValues,
                    ];
                }
            }
        }

        return compact('added', 'changed', 'unchanged');
    }

    public function apply(RoleTemplate $template, Role $role, array $permissionsJson, Collection $modules, bool $confirmedDangerous): array
    {
        if ($template->is_dangerous && ! $confirmedDangerous) {
            return ['error' => 'You must confirm that this template grants extensive permissions.'];
        }

        if ($template->slug === 'super-admin' && $role->slug !== 'super-admin' && ! $confirmedDangerous) {
            return ['error' => 'You must confirm applying the Super Admin template to a non-super-admin role.'];
        }

        $addedCount = 0;
        $changedCount = 0;
        $unchangedCount = 0;

        DB::transaction(function () use ($permissionsJson, $modules, $role, &$addedCount, &$changedCount, &$unchangedCount) {
            foreach ($permissionsJson as $slug => $perms) {
                $module = $modules->get($slug);
                if (! $module) {
                    continue;
                }

                $data = [];
                foreach ($this->permissionColumns as $col) {
                    $data[$col] = (bool) ($perms[$col] ?? false);
                }

                $existing = ModuleRolePermission::where('module_id', $module->id)
                    ->where('role_id', $role->id)
                    ->first();

                if ($existing) {
                    $same = true;
                    foreach ($this->permissionColumns as $col) {
                        if ((bool) $existing->$col !== $data[$col]) {
                            $same = false;
                            break;
                        }
                    }
                    if ($same) {
                        $unchangedCount++;
                        continue;
                    }
                    $existing->update($data);
                    $changedCount++;
                } else {
                    $data['module_id'] = $module->id;
                    $data['role_id'] = $role->id;
                    ModuleRolePermission::create($data);
                    $addedCount++;
                }
            }
        });

        activity()
            ->causedBy(Auth::user())
            ->performedOn($role)
            ->event('template_applied')
            ->withProperties([
                'template' => ['id' => $template->id, 'name' => $template->name, 'slug' => $template->slug],
                'role' => ['id' => $role->id, 'name' => $role->name, 'slug' => $role->slug],
                'changed_count' => $changedCount,
                'added_count' => $addedCount,
                'unchanged_count' => $unchangedCount,
            ])
            ->log("Template '{$template->name}' applied to role '{$role->name}'");

        Cache::increment('perms_generation');

        return [
            'success' => true,
            'template' => $template,
            'role' => $role,
            'addedCount' => $addedCount,
            'changedCount' => $changedCount,
            'unchangedCount' => $unchangedCount,
        ];
    }
}
