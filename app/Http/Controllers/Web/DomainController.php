<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDomainRequest;
use App\Http\Requests\UpdateDomainRequest;
use App\Models\Domain;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DomainController extends Controller
{
    public function index(Request $request): View
    {
        $query = Domain::with('module');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $domains = $query->latest()->paginate(20);

        return view('domains.index', compact('domains'));
    }

    public function create(): View
    {
        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');
        return view('domains.create', compact('modules', 'users'));
    }

    public function store(StoreDomainRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $validated['user_id'] ?? Auth::id();
        $validated['dns_servers'] = $request->filled('dns_servers') ? array_map('trim', explode(',', $request->dns_servers)) : [];

        Domain::create($validated);

        return redirect()->route('domains.index')->with('success', 'Domain created successfully.');
    }

    public function show(int $id): View
    {
        $domain = Domain::with('module')->findOrFail($id);
        return view('domains.show', compact('domain'));
    }

    public function edit(int $id): View
    {
        $domain = Domain::findOrFail($id);
        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');
        return view('domains.edit', compact('domain', 'modules', 'users'));
    }

    public function update(UpdateDomainRequest $request, int $id): RedirectResponse
    {
        $domain = Domain::findOrFail($id);

        $validated = $request->validated();
        $validated['dns_servers'] = $request->filled('dns_servers') ? array_map('trim', explode(',', $request->dns_servers)) : [];

        $domain->update($validated);

        return redirect()->route('domains.index')->with('success', 'Domain updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $domain = Domain::findOrFail($id);
        $domain->delete();

        return redirect()->route('domains.index')->with('success', 'Domain deleted successfully.');
    }
}
