<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Services\ModulePermissionService;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ModulePermissionController extends Controller
{
    public function __construct(
        private readonly ModulePermissionService $permissionService
    ) {}

    public function index(Request $request): View
    {
        $roleId = $request->integer('role_id');
        $focusedRole = null;

        $modules = Module::with(['feature', 'rolePermissions.role'])
            ->orderBy('name')
            ->get();

        if ($roleId) {
            $focusedRole = Role::findOrFail($roleId);
            if (in_array($focusedRole->slug, ['super-admin', '*'])) {
                abort(404);
            }
            $roles = collect([$focusedRole]);
        } else {
            $roles = Role::whereNotIn('slug', ['*'])->orderBy('name')->get();
        }

        return view('module-permissions.index', compact('modules', 'roles', 'focusedRole'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'updated_at' => 'required|date',
            'module_id' => 'required|exists:modules,id',
            'role_id' => 'required|exists:roles,id',
            'access' => 'nullable|boolean',
            'manage' => 'nullable|boolean',
            'import' => 'nullable|boolean',
            'export' => 'nullable|boolean',
            'full_access' => 'nullable|boolean',
        ]);

        $module = Module::findOrFail($validated['module_id']);
        /** @var Module $module */
        $this->checkOptimisticLock($module, $request);

        $controls = [
            'access' => $request->boolean('access'),
            'manage' => $request->boolean('manage'),
            'import' => $request->boolean('import'),
            'export' => $request->boolean('export'),
            'full_access' => $request->boolean('full_access'),
        ];
        $normalized = $this->permissionService->normalizeControls($controls, $module);
        $this->permissionService->setForRole($module, $validated['role_id'], $normalized);

        $role = \App\Models\Role::find($validated['role_id']);

        activity()->event('updated')
            ->causedBy(Auth::user())
            ->withProperties([
                'module' => $module->name,
                'role' => $role?->getAttribute('name'),
                'permissions' => array_keys(array_filter($controls, fn ($v) => $v)),
            ])
            ->log('Module permissions updated for role: '.($role?->getAttribute('name') ?? $validated['role_id']).' on module: '.$module->name);

        return redirect()->route('module-permissions.index', ['role_id' => $validated['role_id']])
            ->with('success', 'Permissions updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $module = Module::findOrFail($validated['module_id']);
        /** @var Module $module */
        $this->permissionService->removeForRole($module, $validated['role_id']);

        $role = \App\Models\Role::find($validated['role_id']);

        activity()->event('deleted')
            ->causedBy(Auth::user())
            ->withProperties([
                'module' => $module->name,
                'role' => $role?->getAttribute('name'),
            ])
            ->log('Module permissions removed for role: '.($role?->getAttribute('name') ?? $validated['role_id']).' on module: '.$module->name);

        return redirect()->route('module-permissions.index', ['role_id' => $validated['role_id']])
            ->with('success', 'Permissions removed successfully.');
    }
}
