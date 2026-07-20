<?php

namespace App\Http\Controllers\Web;

use App\Enums\DomainStatus;
use App\Http\Controllers\Controller;
use App\Models\Domain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DomainController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
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
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        return view('domains.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:domains,name,NULL,id,deleted_at,NULL',
            'status' => 'required|in:' . DomainStatus::Active->value . ',' . DomainStatus::Suspended->value . ',' . DomainStatus::Expired->value,
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();

        $domain = Domain::create($validated);

        activity()->event('created')->performedOn($domain)->causedBy(Auth::user())
            ->withProperties(['name' => $domain->name])
            ->log('Domain created: '.$domain->name);

        Cache::increment('dashboard:version');

        return to_route('domains.show', $domain)
            ->with('success', 'Domain created successfully.');
    }

    public function show(Domain $domain): View
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $domain->load('creator', 'emailAccounts');

        return view('domains.show', compact('domain'));
    }

    public function edit(Domain $domain): View
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        return view('domains.edit', compact('domain'));
    }

    public function update(Request $request, Domain $domain): RedirectResponse
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $this->checkOptimisticLock($domain, $request);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:domains,name,' . $domain->id . ',id,deleted_at,NULL',
            'status' => 'required|in:' . DomainStatus::Active->value . ',' . DomainStatus::Suspended->value . ',' . DomainStatus::Expired->value,
            'notes' => 'nullable|string',
        ]);

        $domain->update($validated);

        activity()->event('updated')->performedOn($domain)->causedBy(Auth::user())
            ->withProperties(['name' => $domain->name])
            ->log('Domain updated: '.$domain->name);

        Cache::increment('dashboard:version');

        return to_route('domains.show', $domain)
            ->with('success', 'Domain updated successfully.');
    }

    public function destroy(Domain $domain): RedirectResponse
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

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

        Cache::increment('dashboard:version');

        return to_route('domains.index')
            ->with('success', 'Domain deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

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

        Cache::increment('dashboard:version');

        return to_route('domains.index')
            ->with('success', 'Domain restored successfully.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

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

        Cache::increment('dashboard:version');

        return to_route('domains.index')
            ->with('success', 'Domain permanently deleted.');
    }
}
