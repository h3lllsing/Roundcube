<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EmailAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $token = $this->generateToken($emailAccount);

        return view('webmail.launch', compact('token'));
    }

    public function openAs(EmailAccount $emailAccount): RedirectResponse
    {
        $this->authorize('view', $emailAccount);

        $token = $this->generateToken($emailAccount);

        return view('webmail.launch', compact('token'));
    }

    public function resolve(Request $request): JsonResponse
    {
        $token = $request->query('t');

        $row = DB::table('webmail_tokens')
            ->where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$row) {
            abort(403, 'Invalid or expired token');
        }

        $updated = DB::table('webmail_tokens')
            ->where('token', $token)
            ->where('used', false)
            ->update(['used' => true]);

        if (!$updated) {
            abort(403, 'Token already used');
        }

        $account = EmailAccount::with('domain')->findOrFail($row->email_account_id);

        if ($account->status !== 'active'
            || $account->domain->status !== 'active'
            || !$account->sync_enabled) {
            abort(403, 'Account not available');
        }

        return response()->json([
            'email' => $account->email,
            'password' => $account->password,
            'imap_host' => $account->imap_host,
            'imap_port' => $account->imap_port,
            'imap_encryption' => $account->imap_encryption,
        ]);
    }

    private function generateToken(EmailAccount $account): string
    {
        $token = bin2hex(random_bytes(32));

        DB::table('webmail_tokens')->insert([
            'token' => $token,
            'email_account_id' => $account->id,
            'created_by' => Auth::id(),
            'expires_at' => now()->addMinutes(5),
        ]);

        return $token;
    }
}
