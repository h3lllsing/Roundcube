<?php

namespace App\Http\Controllers\Web;

use App\Enums\DomainStatus;
use App\Events\DomainCreated;
use App\Events\DomainDeleted;
use App\Events\DomainForceDeleted;
use App\Events\DomainRestored;
use App\Events\DomainUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDomainRequest;
use App\Http\Requests\UpdateDomainRequest;
use App\Models\Domain;
use App\Services\CsvExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class DomainController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Domain::class);
        if ($request->boolean('trashed')) {
            $query = Domain::onlyTrashed();
        } else {
            $query = Domain::query();
        }

        if ($request->filled('search') && strlen($request->search) >= 2) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status') && in_array($request->status, ['active', 'suspended', 'expired'], true)) {
            $query->where('status', $request->status);
        }

        $domains = $query->with('creator', 'deleter')->latest()->paginate(20);

        return view('domains.index', compact('domains'));
    }

    public function create(): View
    {
        $this->authorize('create', Domain::class);

        return view('domains.create');
    }

    public function store(StoreDomainRequest $request): RedirectResponse
    {
        $this->authorize('create', Domain::class);

        $validated = $request->validated();

        $validated['created_by'] = Auth::id();

        $domain = Domain::create($validated);

        event(new DomainCreated($domain));

        Cache::increment('dashboard:version');

        return to_route('domains.show', $domain)
            ->with('success', 'Domain created successfully.');
    }

    public function show(Domain $domain): View
    {
        $this->authorize('view', $domain);

        $domain->load('creator');
        $emailAccounts = $domain->emailAccounts()->latest()->paginate(20);

        return view('domains.show', compact('domain', 'emailAccounts'));
    }

    public function edit(Domain $domain): View
    {
        $this->authorize('update', $domain);

        return view('domains.edit', compact('domain'));
    }

    public function update(UpdateDomainRequest $request, Domain $domain): RedirectResponse
    {
        $this->authorize('update', $domain);

        $this->checkOptimisticLock($domain, $request);

        $validated = $request->validated();

        $original = $domain->getOriginal();
        $domain->update($validated);

        $changed = $domain->getChanges();
        $dirty = array_diff_key($changed, array_flip(['updated_at']));
        $oldValues = array_intersect_key($original, $dirty);

        event(new DomainUpdated($domain, $oldValues, $dirty));

        Cache::increment('dashboard:version');

        return to_route('domains.show', $domain)
            ->with('success', 'Domain updated successfully.');
    }

    public function destroy(Domain $domain): RedirectResponse
    {
        $this->authorize('delete', $domain);

        $domain->deleted_by = Auth::id();
        $domain->saveQuietly();
        $domain->delete();

        event(new DomainDeleted($domain, Auth::id()));

        Cache::increment('dashboard:version');

        return to_route('domains.index')
            ->with('success', 'Domain deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->authorize('restore', Domain::class);

        $domain = Domain::withTrashed()->findOrFail($id);

        $domain->restore();
        $domain->deleted_by = null;
        $domain->saveQuietly();

        event(new DomainRestored($domain));

        Cache::increment('dashboard:version');

        return to_route('domains.index')
            ->with('success', 'Domain restored successfully.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $this->authorize('forceDelete', Domain::class);

        $domain = Domain::withTrashed()->findOrFail($id);

        if ($domain->emailAccounts()->count() > 0) {
            return to_route('domains.index')
                ->with('error', 'Cannot force-delete domain with existing email accounts. Delete them first.');
        }

        $originalId = $domain->id;
        $domain->forceDelete();

        event(new DomainForceDeleted($domain, $originalId));

        Cache::increment('dashboard:version');

        return to_route('domains.index')
            ->with('success', 'Domain permanently deleted.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->authorize('bulkDelete', Domain::class);

        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:domains,id'])['ids'];
        $count = Domain::whereIn('id', $ids)->count();

        Domain::whereIn('id', $ids)->each(function (Domain $domain) {
            $domain->deleted_by = Auth::id();
            $domain->saveQuietly();
            $domain->delete();
        });

        Cache::increment('dashboard:version');

        return back()->with('success', "{$count} domains deleted.");
    }

    public function export(): StreamedResponse
    {
        $this->authorize('viewAny', Domain::class);

        $domains = Domain::withCount('emailAccounts')->latest()->get();
        $rows = $domains->map(fn ($d) => [
            'name' => $d->name,
            'status' => $d->status->value ?? $d->status,
            'email_accounts_count' => $d->email_accounts_count,
            'created_at' => $d->created_at?->toDateTimeString(),
        ]);

        return (new CsvExportService)->export($rows, ['name', 'status', 'email_accounts_count', 'created_at'], 'domains');
    }
}
