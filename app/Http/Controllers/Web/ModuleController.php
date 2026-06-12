<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreModuleRequest;
use App\Http\Requests\UpdateModuleRequest;
use App\Models\Feature;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function index(): View
    {
        $modules = Module::with('feature')->latest()->paginate(20);

        return view('modules.index', compact('modules'));
    }

    public function create(): View
    {
        $features = Feature::orderBy('name')->pluck('name', 'id');

        return view('modules.create', compact('features'));
    }

    public function store(StoreModuleRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);
        Module::create($validated);

        return redirect()->route('modules.index')->with('success', 'Module created successfully.');
    }

    public function show(int $id): View
    {
        $module = Module::with('feature')->findOrFail($id);

        return view('modules.show', compact('module'));
    }

    public function edit(int $id): View
    {
        $module = Module::findOrFail($id);
        $features = Feature::orderBy('name')->pluck('name', 'id');

        return view('modules.edit', compact('module', 'features'));
    }

    public function update(UpdateModuleRequest $request, int $id): RedirectResponse
    {
        $module = Module::findOrFail($id);

        $module->update($request->validated());

        return redirect()->route('modules.index')->with('success', 'Module updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $module = Module::findOrFail($id);
        $module->delete();

        return redirect()->route('modules.index')->with('success', 'Module deleted successfully.');
    }
}
