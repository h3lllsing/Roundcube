<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\EmailAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmailAccountController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(EmailAccount::class, 'email_account');
    }

    public function index(Request $request): View
    {
        $query = EmailAccount::query()->with('domain', 'creator');

        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasPermission('emails.manage')) {
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

        $accounts = $query->latest()->paginate(20);
        $domains = Domain::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('email-accounts.index', compact('accounts', 'domains'));
    }

    public function create(): View
    {
        $domains = Domain::orderBy('name')->get(['id', 'name']);

        return view('email-accounts.create', compact('domains'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'email' => 'required|email|max:255|unique:email_accounts,email',
            'password' => 'required|string',
            'imap_host' => 'required|string|max:255',
            'imap_port' => 'required|integer|min:1|max:65535',
            'imap_encryption' => 'required|in:ssl,tls,none',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|in:ssl,tls,none',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string',
            'status' => 'required|in:active,suspended',
        ]);

        $validated['created_by'] = Auth::id();

        $account = EmailAccount::create($validated);

        return to_route('email-accounts.show', $account)
            ->with('success', 'Email account created successfully.');
    }

    public function show(EmailAccount $emailAccount): View
    {
        $emailAccount->load('domain', 'creator', 'assignedUsers');

        return view('email-accounts.show', compact('emailAccount'));
    }

    public function edit(EmailAccount $emailAccount): View
    {
        $domains = Domain::orderBy('name')->get(['id', 'name']);

        return view('email-accounts.edit', compact('emailAccount', 'domains'));
    }

    public function update(Request $request, EmailAccount $emailAccount): RedirectResponse
    {
        $this->checkOptimisticLock($emailAccount, $request);

        $validated = $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'email' => 'required|email|max:255|unique:email_accounts,email,' . $emailAccount->id,
            'password' => 'nullable|string',
            'imap_host' => 'required|string|max:255',
            'imap_port' => 'required|integer|min:1|max:65535',
            'imap_encryption' => 'required|in:ssl,tls,none',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|in:ssl,tls,none',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string',
            'status' => 'required|in:active,suspended',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }
        if (empty($validated['smtp_password'])) {
            $validated['smtp_password'] = null;
        }

        $emailAccount->update($validated);

        return to_route('email-accounts.show', $emailAccount)
            ->with('success', 'Email account updated successfully.');
    }

    public function destroy(EmailAccount $emailAccount): RedirectResponse
    {
        $emailAccount->delete();

        return to_route('email-accounts.index')
            ->with('success', 'Email account deleted successfully.');
    }
}
