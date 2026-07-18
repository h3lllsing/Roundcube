<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DomainController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Domain::class, 'domain');
    }

    public function index(Request $request): View
    {
        $query = Domain::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $domains = $query->with('creator')->latest()->paginate(20);

        return view('domains.index', compact('domains'));
    }

    public function create(): View
    {
        return view('domains.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:domains,name',
            'status' => 'required|in:active,suspended,expired',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();

        $domain = Domain::create($validated);

        return to_route('domains.show', $domain)
            ->with('success', 'Domain created successfully.');
    }

    public function show(Domain $domain): View
    {
        $domain->load('creator', 'emailAccounts');

        return view('domains.show', compact('domain'));
    }

    public function edit(Domain $domain): View
    {
        return view('domains.edit', compact('domain'));
    }

    public function update(Request $request, Domain $domain): RedirectResponse
    {
        $this->checkOptimisticLock($domain, $request);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:domains,name,' . $domain->id,
            'status' => 'required|in:active,suspended,expired',
            'notes' => 'nullable|string',
        ]);

        $domain->update($validated);

        return to_route('domains.show', $domain)
            ->with('success', 'Domain updated successfully.');
    }

    public function destroy(Domain $domain): RedirectResponse
    {
        $domain->delete();

        return to_route('domains.index')
            ->with('success', 'Domain deleted successfully.');
    }
}
