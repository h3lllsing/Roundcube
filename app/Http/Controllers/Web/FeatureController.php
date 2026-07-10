<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeatureRequest;
use App\Http\Requests\UpdateFeatureRequest;
use App\Services\FeatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeatureController extends Controller
{
    public function __construct(
        private readonly FeatureService $featureService
    ) {}

    public function index(Request $request): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $filters = [];
        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }
        if ($request->filled('status')) {
            $filters['is_active'] = $request->status === 'active';
        }
        if ($request->boolean('trashed')) {
            $filters['with_trashed'] = true;
        }

        $features = $this->featureService->list($filters);

        return view('features.index', compact('features'));
    }

    public function create(): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        return view('features.create');
    }

    public function store(StoreFeatureRequest $request): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $this->featureService->create($request->validated());

        return redirect()->route('features.index')->with('success', 'Feature created successfully.');
    }

    public function show(int $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $feature = \App\Models\Feature::with('modules', 'notes.user')->findOrFail($id);

        return view('features.show', compact('feature'));
    }

    public function edit(int $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $feature = $this->featureService->find($id);

        return view('features.edit', compact('feature'));
    }

    public function update(UpdateFeatureRequest $request, int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $feature = \App\Models\Feature::findOrFail($id);
        $this->checkOptimisticLock($feature, $request);
        $this->featureService->update($feature, $request->validated());

        return redirect()->route('features.index')->with('success', 'Feature updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $feature = \App\Models\Feature::findOrFail($id);
        $this->featureService->delete($feature);

        return redirect()->route('features.index')->with('success', 'Feature deleted successfully.');
    }
}
