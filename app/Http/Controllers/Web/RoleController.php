<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService
    ) {}

    public function index(Request $request): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $filters = $request->only(['search']);
        $roles = $this->roleService->list($filters);

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        return view('roles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug',
        ]);

        $this->roleService->create($validated);

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function show(int $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $role = $this->roleService->find($id);
        $allPrivileges = $this->roleService->getAllPrivileges();
        $moduleAccess = $this->roleService->getModuleAccessSummary($id);

        return view('roles.show', compact('role', 'allPrivileges', 'moduleAccess'));
    }

    public function edit(int $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $role = \App\Models\Role::findOrFail($id);

        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $role = \App\Models\Role::findOrFail($id);
        $this->checkOptimisticLock($role, $request);

        $validated = $request->validate([
            'updated_at' => 'required|date',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug,'.$role->id,
        ]);

        $this->roleService->update($role, $validated);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $error = $this->roleService->delete($id);

        if ($error) {
            return redirect()->route('roles.index')->with('error', $error);
        }

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    public function attachPrivilege(Request $request, int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $validated = $request->validate([
            'privilege_id' => 'required|exists:privileges,id',
        ]);

        $this->roleService->attachPrivilege($id, $validated['privilege_id']);

        return redirect()->route('roles.show', $id)->with('success', 'Privilege attached.');
    }

    public function detachPrivilege(Request $request, int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $validated = $request->validate([
            'privilege_id' => 'required|exists:privileges,id',
        ]);

        $this->roleService->detachPrivilege($id, $validated['privilege_id']);

        return redirect()->route('roles.show', $id)->with('success', 'Privilege detached.');
    }
}
