<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeatureRequest;
use App\Http\Requests\UpdateFeatureRequest;
use App\Models\Feature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeatureController extends Controller
{
    public function index(): View
    {
        $features = Feature::with('modules')->latest()->paginate(20);

        return view('features.index', compact('features'));
    }

    public function create(): View
    {
        return view('features.create');
    }

    public function store(StoreFeatureRequest $request): RedirectResponse
    {
        Feature::create($request->validated());

        return redirect()->route('features.index')->with('success', 'Feature created successfully.');
    }

    public function show(int $id): View
    {
        $feature = Feature::with('modules')->findOrFail($id);

        return view('features.show', compact('feature'));
    }

    public function edit(int $id): View
    {
        $feature = Feature::findOrFail($id);

        return view('features.edit', compact('feature'));
    }

    public function update(UpdateFeatureRequest $request, int $id): RedirectResponse
    {
        $feature = Feature::findOrFail($id);

        $feature->update($request->validated());

        return redirect()->route('features.index')->with('success', 'Feature updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $feature = Feature::findOrFail($id);
        $feature->delete();

        return redirect()->route('features.index')->with('success', 'Feature deleted successfully.');
    }
}
