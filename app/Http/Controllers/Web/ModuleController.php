<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreModuleRequest;
use App\Http\Requests\UpdateModuleRequest;
use App\Models\Feature;
use App\Models\Module;
use App\Services\ModuleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function __construct(
        private readonly ModuleService $moduleService
    ) {}

    public function index(Request $request): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $query = Module::with('feature');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('slug', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        if ($request->boolean('trashed')) {
            $query->onlyTrashed();
        }

        $modules = $query->select(['id', 'name', 'created_at', 'feature_id'])->latest()->paginate(20);

        return view('modules.index', compact('modules'));
    }

    public function create(): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $features = Feature::orderBy('name')->pluck('name', 'id');

        return view('modules.create', compact('features'));
    }

    public function store(StoreModuleRequest $request): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);
        Module::create($validated);

        return redirect()->route('modules.index')->with('success', 'Module created successfully.');
    }

    public function show(int $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $module = Module::with('feature', 'notes.user')->findOrFail($id);

        return view('modules.show', compact('module'));
    }

    public function edit(int $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $module = Module::findOrFail($id);
        $features = Feature::orderBy('name')->pluck('name', 'id');

        return view('modules.edit', compact('module', 'features'));
    }

    public function update(UpdateModuleRequest $request, int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $module = Module::findOrFail($id);
        $this->checkOptimisticLock($module, $request);
        $module->update($request->validated());

        return redirect()->route('modules.index')->with('success', 'Module updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $module = Module::findOrFail($id);
        $module->delete();

        return redirect()->route('modules.index')->with('success', 'Module deleted successfully.');
    }
}
