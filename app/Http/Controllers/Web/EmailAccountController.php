<?php

namespace App\Http\Controllers\Web;

use App\Enums\AccountStatus;
use App\Enums\DomainStatus;
use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\EmailAccount;
use App\Services\SmtpAutoDiscover;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class EmailAccountController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        if ($request->boolean('trashed')) {
            $query = EmailAccount::onlyTrashed()->with('domain', 'creator');
        } else {
            $query = EmailAccount::query()->with('domain', 'creator');
        }

        if (!Auth::user()->isAdmin()) {
            $query->whereHas('assignedUsers', fn ($q) => $q->where('user_id', Auth::id()));
        }

        if ($request->filled('search')) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $accounts = $query->with('deleter')->latest()->paginate(20);
        $domains = Domain::where('status', DomainStatus::Active)->orderBy('name')->get(['id', 'name']);

        return view('email-accounts.index', compact('accounts', 'domains'));
    }

    public function create(): View
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $domains = Domain::where('status', DomainStatus::Active)->orderBy('name')->get(['id', 'name']);

        return view('email-accounts.create', compact('domains'));
    }

    public function autoDiscover(Request $request): JsonResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $request->validate(['email' => 'required|email']);
        $email = $request->input('email');

        $result = (new SmtpAutoDiscover)->discoverAll($email);

        if (isset($result['error'])) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $validated = $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'email' => 'required|email|max:255|unique:email_accounts,email,NULL,id,deleted_at,NULL',
            'password' => 'required|string',
            'imap_host' => 'required|string|max:255',
            'imap_port' => 'required|integer|min:1|max:65535',
            'imap_encryption' => 'required|in:ssl,tls,none',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|in:ssl,tls,none',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string',
            'status' => 'required|in:' . AccountStatus::Active->value . ',' . AccountStatus::Suspended->value,
            'sync_enabled' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['sync_enabled'] = $request->boolean('sync_enabled');

        $account = EmailAccount::create($validated);

        activity()->event('created')->performedOn($account)->causedBy(Auth::user())
            ->withProperties(['email' => $account->email])
            ->log('Email account created: '.$account->email);

        Cache::increment('dashboard:version');

        return to_route('email_accounts.show', $account)
            ->with('success', 'Email account created successfully.');
    }

    public function show(EmailAccount $emailAccount): View
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $emailAccount->load('domain', 'creator', 'assignedUsers');

        return view('email-accounts.show', compact('emailAccount'));
    }

    public function edit(EmailAccount $emailAccount): View
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $domains = Domain::where('status', DomainStatus::Active)->orderBy('name')->get(['id', 'name']);

        return view('email-accounts.edit', compact('emailAccount', 'domains'));
    }

    public function update(Request $request, EmailAccount $emailAccount): RedirectResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $this->checkOptimisticLock($emailAccount, $request);

        $validated = $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'email' => 'required|email|max:255|unique:email_accounts,email,' . $emailAccount->id . ',id,deleted_at,NULL',
            'password' => 'nullable|string',
            'imap_host' => 'required|string|max:255',
            'imap_port' => 'required|integer|min:1|max:65535',
            'imap_encryption' => 'required|in:ssl,tls,none',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|in:ssl,tls,none',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string',
            'status' => 'required|in:' . AccountStatus::Active->value . ',' . AccountStatus::Suspended->value,
            'sync_enabled' => 'boolean',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }
        if (empty($validated['smtp_password'])) {
            $validated['smtp_password'] = null;
        }
        $validated['sync_enabled'] = $request->boolean('sync_enabled');

        $emailAccount->update($validated);

        activity()->event('updated')->performedOn($emailAccount)->causedBy(Auth::user())
            ->withProperties(['email' => $emailAccount->email])
            ->log('Email account updated: '.$emailAccount->email);

        Cache::increment('dashboard:version');

        return to_route('email_accounts.show', $emailAccount)
            ->with('success', 'Email account updated successfully.');
    }

    public function destroy(EmailAccount $emailAccount): RedirectResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $emailAccount->deleted_by = Auth::id();
        $emailAccount->saveQuietly();
        $emailAccount->delete();

        activity()
            ->event('soft_delete')
            ->causedBy(Auth::user())
            ->performedOn($emailAccount)
            ->withProperties([
                'action' => 'soft_delete',
                'resource_type' => EmailAccount::class,
                'resource_id' => $emailAccount->id,
                'deleted_by' => Auth::id(),
                'from_route' => url()->current(),
            ])
            ->log('soft deleted');

        Cache::increment('dashboard:version');

        return to_route('email_accounts.index')
            ->with('success', 'Email account deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $account = EmailAccount::withTrashed()->findOrFail($id);

        $account->restore();
        $account->deleted_by = null;
        $account->saveQuietly();

        activity()
            ->event('restore')
            ->causedBy(Auth::user())
            ->performedOn($account)
            ->withProperties([
                'action' => 'restore',
                'resource_type' => EmailAccount::class,
                'resource_id' => $account->id,
            ])
            ->log('restored');

        Cache::increment('dashboard:version');

        return to_route('email_accounts.index')
            ->with('success', 'Email account restored successfully.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $account = EmailAccount::withTrashed()->findOrFail($id);

        $account->forceDelete();

        Cache::increment('dashboard:version');

        activity()
            ->event('force_delete')
            ->causedBy(Auth::user())
            ->performedOn($account)
            ->withProperties([
                'action' => 'force_delete',
                'resource_type' => EmailAccount::class,
                'resource_id' => $id,
            ])
            ->log('force deleted');

        return to_route('email_accounts.index')
            ->with('success', 'Email account permanently deleted.');
    }
}
