<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PrivilegeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrivilegeController extends Controller
{
    public function __construct(
        private readonly PrivilegeService $privilegeService
    ) {}

    public function index(Request $request): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $filters = $request->only(['search']);
        $privileges = $this->privilegeService->list($filters);

        return view('privileges.index', compact('privileges'));
    }

    public function create(): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        return view('privileges.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:privileges,slug',
            'description' => 'nullable|string|max:1000',
        ]);

        $this->privilegeService->create($validated);

        return redirect()->route('privileges.index')->with('success', 'Privilege created successfully.');
    }

    public function show(int $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $privilege = $this->privilegeService->find($id);

        return view('privileges.show', compact('privilege'));
    }

    public function edit(int $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $privilege = \App\Models\Privilege::findOrFail($id);

        return view('privileges.edit', compact('privilege'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $privilege = \App\Models\Privilege::findOrFail($id);
        $this->checkOptimisticLock($privilege, $request);

        $validated = $request->validate([
            'updated_at' => 'required|date',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:privileges,slug,'.$privilege->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $this->privilegeService->update($privilege, $validated);

        return redirect()->route('privileges.index')->with('success', 'Privilege updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $error = $this->privilegeService->delete($id);

        if ($error) {
            return redirect()->route('privileges.index')->with('error', $error);
        }

        return redirect()->route('privileges.index')->with('success', 'Privilege deleted successfully.');
    }
}
