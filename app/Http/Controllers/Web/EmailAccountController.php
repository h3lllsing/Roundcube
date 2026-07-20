<?php

namespace App\Http\Controllers\Web;

use App\Enums\AccountStatus;
use App\Enums\DomainStatus;
use App\Events\EmailAccountCreated;
use App\Events\EmailAccountDeleted;
use App\Events\EmailAccountForceDeleted;
use App\Events\EmailAccountRestored;
use App\Events\EmailAccountUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\AutoDiscoverRequest;
use App\Http\Requests\StoreEmailAccountRequest;
use App\Http\Requests\UpdateEmailAccountRequest;
use App\Models\Domain;
use App\Models\EmailAccount;
use App\Services\CsvExportService;
use App\Services\SmtpAutoDiscover;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class EmailAccountController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmailAccount::class);

        if ($request->boolean('trashed')) {
            $query = EmailAccount::onlyTrashed()->with('domain', 'creator');
        } else {
            $query = EmailAccount::query()->with('domain', 'creator');
        }

        if ($request->filled('search') && strlen($request->search) >= 2) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('domain_id') && is_numeric($request->domain_id)) {
            $query->where('domain_id', $request->domain_id);
        }
        if ($request->filled('status') && in_array($request->status, ['active', 'suspended'], true)) {
            $query->where('status', $request->status);
        }

        $accounts = $query->with('deleter')->latest()->paginate(20);
        $domains = Cache::remember('domains:active', 300, fn () =>
            Domain::where('status', DomainStatus::Active)->orderBy('name')->get(['id', 'name'])
        );

        return view('email-accounts.index', compact('accounts', 'domains'));
    }

    public function create(): View
    {
        $this->authorize('create', EmailAccount::class);

        $domains = Cache::remember('domains:active', 300, fn () =>
            Domain::where('status', DomainStatus::Active)->orderBy('name')->get(['id', 'name'])
        );

        return view('email-accounts.create', compact('domains'));
    }

    public function autoDiscover(AutoDiscoverRequest $request): JsonResponse
    {
        $this->authorize('autoDiscover', EmailAccount::class);
        $email = $request->input('email');

        $result = (new SmtpAutoDiscover)->discoverAll($email);

        if (isset($result['error'])) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    public function store(StoreEmailAccountRequest $request): RedirectResponse
    {
        $this->authorize('create', EmailAccount::class);

        $validated = $request->validated();

        $validated['created_by'] = Auth::id();
        $validated['sync_enabled'] = $request->boolean('sync_enabled');

        $account = EmailAccount::create($validated);

        event(new EmailAccountCreated($account));

        Cache::increment('dashboard:version');

        return to_route('email_accounts.show', $account)
            ->with('success', 'Email account created successfully.');
    }

    public function show(EmailAccount $emailAccount): View
    {
        $this->authorize('view', $emailAccount);

        $emailAccount->load('domain', 'creator', 'assignedUsers');

        return view('email-accounts.show', compact('emailAccount'));
    }

    public function edit(EmailAccount $emailAccount): View
    {
        $this->authorize('update', $emailAccount);

        $domains = Cache::remember('domains:active', 300, fn () =>
            Domain::where('status', DomainStatus::Active)->orderBy('name')->get(['id', 'name'])
        );

        return view('email-accounts.edit', compact('emailAccount', 'domains'));
    }

    public function update(UpdateEmailAccountRequest $request, EmailAccount $emailAccount): RedirectResponse
    {
        $this->authorize('update', $emailAccount);

        $this->checkOptimisticLock($emailAccount, $request);

        $validated = $request->validated();

        if (empty($validated['password'])) {
            unset($validated['password']);
        }
        if (empty($validated['smtp_password'])) {
            $validated['smtp_password'] = null;
        }
        $validated['sync_enabled'] = $request->boolean('sync_enabled');

        $original = $emailAccount->getOriginal();
        $emailAccount->update($validated);

        $changed = $emailAccount->getChanges();
        $dirty = array_diff_key($changed, array_flip(['updated_at']));
        $oldValues = array_intersect_key($original, $dirty);

        event(new EmailAccountUpdated($emailAccount, $oldValues, $dirty));

        Cache::increment('dashboard:version');

        return to_route('email_accounts.show', $emailAccount)
            ->with('success', 'Email account updated successfully.');
    }

    public function destroy(EmailAccount $emailAccount): RedirectResponse
    {
        $this->authorize('delete', $emailAccount);

        $emailAccount->deleted_by = Auth::id();
        $emailAccount->saveQuietly();
        $emailAccount->delete();

        event(new EmailAccountDeleted($emailAccount, Auth::id()));

        Cache::increment('dashboard:version');

        return to_route('email_accounts.index')
            ->with('success', 'Email account deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->authorize('restore', EmailAccount::class);

        $account = EmailAccount::withTrashed()->findOrFail($id);

        $account->restore();
        $account->deleted_by = null;
        $account->saveQuietly();

        event(new EmailAccountRestored($account));

        Cache::increment('dashboard:version');

        return to_route('email_accounts.index')
            ->with('success', 'Email account restored successfully.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $this->authorize('forceDelete', EmailAccount::class);

        $account = EmailAccount::withTrashed()->findOrFail($id);

        $originalId = $account->id;
        $account->forceDelete();

        event(new EmailAccountForceDeleted($account, $originalId));

        Cache::increment('dashboard:version');

        return to_route('email_accounts.index')
            ->with('success', 'Email account permanently deleted.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->authorize('bulkDelete', EmailAccount::class);

        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:email_accounts,id'])['ids'];
        $count = EmailAccount::whereIn('id', $ids)->count();

        EmailAccount::whereIn('id', $ids)->each(function (EmailAccount $account) {
            $account->deleted_by = Auth::id();
            $account->saveQuietly();
            $account->delete();
        });

        Cache::increment('dashboard:version');

        return back()->with('success', "{$count} email accounts deleted.");
    }

    public function export(): StreamedResponse
    {
        $this->authorize('viewAny', EmailAccount::class);

        $accounts = EmailAccount::with('domain')->latest()->get();
        $rows = $accounts->map(fn ($a) => [
            'email' => $a->email,
            'display_name' => $a->display_name,
            'domain' => $a->domain?->name,
            'status' => $a->status->value ?? $a->status,
            'created_at' => $a->created_at?->toDateTimeString(),
        ]);

        return (new CsvExportService)->export($rows, ['email', 'display_name', 'domain', 'status', 'created_at'], 'email-accounts');
    }
}
