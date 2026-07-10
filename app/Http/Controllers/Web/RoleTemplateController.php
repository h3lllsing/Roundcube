<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\RoleTemplate;
use App\Services\RoleTemplateService;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleTemplateController extends Controller
{
    public function __construct(
        private readonly RoleTemplateService $roleTemplateService
    ) {
    }

    public function index(): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $templates = RoleTemplate::orderBy('name')->get();

        return view('role-templates.index', compact('templates'));
    }

    public function show(string $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $viewData = $this->roleTemplateService->getViewData($id);

        return view('role-templates.show', $viewData);
    }

    public function apply(Request $request, string $id): RedirectResponse|View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $template = RoleTemplate::findOrFail($id);
        $validated = $request->validate(['role_id' => 'required|integer|exists:roles,id']);
        $role = Role::findOrFail($validated['role_id']);

        $permissionsJson = $template->permissions_json;
        $moduleSlugs = array_keys($permissionsJson);
        $modules = Module::whereIn('slug', $moduleSlugs)->get()->keyBy('slug');

        if ($request->boolean('confirmed')) {
            abort_unless($request->isMethod('post'), 405);

            $result = $this->roleTemplateService->apply($template, $role, $permissionsJson, $modules, $request->boolean('confirm_dangerous'));

            if (isset($result['error'])) {
                return back()->withErrors(['confirm_dangerous' => $result['error']]);
            }

            $totalAffected = $result['addedCount'] + $result['changedCount'];
            return redirect()->route('role-templates.show', $template->id)
                ->with('success', "Template '{$template->name}' applied to role '{$role->name}'. {$totalAffected} module(s) affected ({$result['addedCount']} added, {$result['changedCount']} overwritten, {$result['unchangedCount']} unchanged).");
        }

        $diff = $this->roleTemplateService->computeDiff($role, $modules, $permissionsJson);

        return view('role-templates.apply', compact('template', 'role', 'diff', 'modules'));
    }
}
