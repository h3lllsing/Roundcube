<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EmailAccount;
use App\Models\User;
use App\Notifications\NewEmailArrived;
use Illuminate\Http\Request;

class NewMailNotificationController extends Controller
{
    public function store(Request $request)
    {
        $token = $request->header('X-API-Token') ?? $request->input('token');
        $expected = config('app.notification_api_token');
        if ($expected && $token !== $expected) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'email' => 'required|email',
            'subject' => 'nullable|string|max:500',
            'from' => 'nullable|string|max:500',
            'account_id' => 'nullable|integer',
        ]);

        $accountId = $validated['account_id'] ?? null;
        if (!$accountId) {
            $account = EmailAccount::where('email', $validated['email'])->first();
            if (!$account) {
                return response()->json(['error' => 'Account not found'], 404);
            }
            $accountId = $account->id;
        }

        $account = EmailAccount::find($accountId);
        if (!$account) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        $notification = new NewEmailArrived(
            email: $account->email,
            subject: $validated['subject'] ?? '(no subject)',
            from: $validated['from'] ?? 'Unknown',
            accountId: $account->id,
        );

        $users = User::whereIn('id', $account->users()->pluck('users.id'))->get();

        foreach ($users as $user) {
            $user->notify($notification);
        }

        return response()->json(['success' => true, 'notified_users' => $users->count()]);
    }
}
