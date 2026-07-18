<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EmailAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class WebmailController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user->isSuperAdmin() || $user->hasPermission('emails.manage')) {
            $accounts = EmailAccount::with('domain')
                ->where('status', 'active')
                ->orderBy('email')
                ->get();
        } else {
            $accounts = $user->assignedEmailAccounts()
                ->with('domain')
                ->where('status', 'active')
                ->orderBy('email')
                ->get();
        }

        return view('webmail.index', compact('accounts'));
    }

    public function redirect(EmailAccount $emailAccount): RedirectResponse
    {
        $user = Auth::user();

        $canAccess = $user->isSuperAdmin()
            || $user->hasPermission('emails.manage')
            || $emailAccount->assignedUsers()->where('user_id', $user->id)->exists();

        abort_unless($canAccess, 403);

        $signedUrl = URL::temporarySignedRoute(
            'webmail.auth',
            now()->addMinutes(5),
            ['email_account_id' => $emailAccount->id]
        );

        return redirect()->away($signedUrl);
    }

    public function openAs(EmailAccount $emailAccount): RedirectResponse
    {
        $this->authorize('view', $emailAccount);

        $signedUrl = URL::temporarySignedRoute(
            'webmail.auth',
            now()->addMinutes(5),
            ['email_account_id' => $emailAccount->id]
        );

        return redirect()->away($signedUrl);
    }

    public function auth(Request $request): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);

        $account = EmailAccount::findOrFail($request->email_account_id);

        $account->load('domain');

        $config = [
            'email' => $account->email,
            'password' => $account->password,
            'imap_host' => $account->imap_host,
            'imap_port' => $account->imap_port,
            'imap_encryption' => $account->imap_encryption,
            'smtp_host' => $account->smtp_host,
            'smtp_port' => $account->smtp_port,
            'smtp_encryption' => $account->smtp_encryption,
            'smtp_username' => $account->smtp_username,
            'smtp_password' => $account->smtp_password,
        ];

        $webmailUrl = url('/webmail/') . '?' . http_build_query([
            'auto_login' => '1',
            'email' => $config['email'],
            'imap_host' => $config['imap_host'],
            'imap_port' => $config['imap_port'],
            'imap_encryption' => $config['imap_encryption'],
            'smtp_host' => $config['smtp_host'] ?? $config['imap_host'],
            'smtp_port' => $config['smtp_port'] ?? 587,
            'smtp_encryption' => $config['smtp_encryption'] ?? 'tls',
            'smtp_username' => $config['smtp_username'] ?? $config['email'],
            'token' => encrypt(json_encode([
                'email' => $config['email'],
                'password' => $config['password'],
                'imap_host' => $config['imap_host'],
                'imap_port' => $config['imap_port'],
                'imap_encryption' => $config['imap_encryption'],
                'smtp_host' => $config['smtp_host'],
                'smtp_port' => $config['smtp_port'],
                'smtp_encryption' => $config['smtp_encryption'],
                'smtp_username' => $config['smtp_username'],
                'smtp_password' => $config['smtp_password'],
            ])),
        ]);

        return redirect()->away($webmailUrl);
    }
}
