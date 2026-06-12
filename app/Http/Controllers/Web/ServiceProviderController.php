<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceProviderRequest;
use App\Http\Requests\UpdateServiceProviderRequest;
use App\Models\Module;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ServiceProviderController extends Controller
{
    public function index(Request $request): View
    {
        $query = ServiceProvider::with('module');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $providers = $query->latest()->paginate(20);

        return view('service-providers.index', compact('providers'));
    }

    public function create(): View
    {
        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');
        return view('service-providers.create', compact('modules', 'users'));
    }

    public function store(StoreServiceProviderRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['type'] = $validated['type'] ?? 'other';
        $validated['user_id'] = $validated['user_id'] ?? Auth::id();

        ServiceProvider::create($validated);

        return redirect()->route('service-providers.index')->with('success', 'Service provider created successfully.');
    }

    public function show(int $id): View
    {
        $provider = ServiceProvider::with('module')->findOrFail($id);
        return view('service-providers.show', compact('provider'));
    }

    public function edit(int $id): View
    {
        $provider = ServiceProvider::findOrFail($id);
        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');
        return view('service-providers.edit', compact('provider', 'modules', 'users'));
    }

    public function update(UpdateServiceProviderRequest $request, int $id): RedirectResponse
    {
        $provider = ServiceProvider::findOrFail($id);

        $provider->update($request->validated());

        return redirect()->route('service-providers.index')->with('success', 'Service provider updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $provider = ServiceProvider::findOrFail($id);
        $provider->delete();

        return redirect()->route('service-providers.index')->with('success', 'Service provider deleted successfully.');
    }
}
