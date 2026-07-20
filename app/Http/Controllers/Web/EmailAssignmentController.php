<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EmailAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailAssignmentController extends Controller
{
    public function store(Request $request, EmailAccount $emailAccount): RedirectResponse
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'can_send' => 'boolean',
            'can_receive' => 'boolean',
        ]);

        $emailAccount->assignedUsers()->syncWithoutDetaching([
            $validated['user_id'] => [
                'can_send' => $validated['can_send'] ?? true,
                'can_receive' => $validated['can_receive'] ?? true,
                'assigned_by' => Auth::id(),
            ],
        ]);

        $assignedUser = User::find($validated['user_id']);

        activity()->event('assign')
            ->performedOn($emailAccount)
            ->causedBy(Auth::user())
            ->withProperties([
                'user_id' => $validated['user_id'],
                'user_email' => $assignedUser?->email,
                'can_send' => $validated['can_send'] ?? true,
                'can_receive' => $validated['can_receive'] ?? true,
                'action' => 'assign',
            ])
            ->log("Email account {$emailAccount->email} assigned to {$assignedUser?->email}");

        return back()->with('success', 'Email account assigned successfully.');
    }

    public function destroy(EmailAccount $emailAccount, User $user): RedirectResponse
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $emailAccount->assignedUsers()->detach($user);

        activity()->event('revoke')
            ->performedOn($emailAccount)
            ->causedBy(Auth::user())
            ->withProperties([
                'user_id' => $user->id,
                'user_email' => $user->email,
                'action' => 'revoke',
            ])
            ->log("Email account {$emailAccount->email} unassigned from {$user->email}");

        return back()->with('success', 'Assignment revoked successfully.');
    }
}
