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
        $this->middleware(fn ($req, $next) => Auth::user()->isSuperAdmin() ? $next($req) : abort(403));
    }

    public function index(Request $request): View
    {
        if ($request->boolean('trashed')) {
            $query = Domain::onlyTrashed();
        } else {
            $query = Domain::query();
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $domains = $query->with('creator', 'deleter')->latest()->paginate(20);

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

        activity()->event('created')->performedOn($domain)->causedBy(Auth::user())
            ->withProperties(['name' => $domain->name])
            ->log('Domain created: '.$domain->name);

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

        activity()->event('updated')->performedOn($domain)->causedBy(Auth::user())
            ->withProperties(['name' => $domain->name])
            ->log('Domain updated: '.$domain->name);

        return to_route('domains.show', $domain)
            ->with('success', 'Domain updated successfully.');
    }

    public function destroy(Domain $domain): RedirectResponse
    {
        $domain->deleted_by = Auth::id();
        $domain->saveQuietly();
        $domain->delete();

        activity()
            ->event('soft_delete')
            ->causedBy(Auth::user())
            ->performedOn($domain)
            ->withProperties([
                'action' => 'soft_delete',
                'resource_type' => Domain::class,
                'resource_id' => $domain->id,
                'deleted_by' => Auth::id(),
                'from_route' => url()->current(),
            ])
            ->log('soft deleted');

        return to_route('domains.index')
            ->with('success', 'Domain deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        $domain = Domain::withTrashed()->findOrFail($id);

        $domain->restore();
        $domain->deleted_by = null;
        $domain->saveQuietly();

        activity()
            ->event('restore')
            ->causedBy(Auth::user())
            ->performedOn($domain)
            ->withProperties([
                'action' => 'restore',
                'resource_type' => Domain::class,
                'resource_id' => $domain->id,
            ])
            ->log('restored');

        return to_route('domains.index')
            ->with('success', 'Domain restored successfully.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $domain = Domain::withTrashed()->findOrFail($id);

        $domain->forceDelete();

        activity()
            ->event('force_delete')
            ->causedBy(Auth::user())
            ->performedOn($domain)
            ->withProperties([
                'action' => 'force_delete',
                'resource_type' => Domain::class,
                'resource_id' => $id,
            ])
            ->log('force deleted');

        return to_route('domains.index')
            ->with('success', 'Domain permanently deleted.');
    }
}
