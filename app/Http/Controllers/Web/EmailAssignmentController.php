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
        $this->authorize('update', $emailAccount);

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

        return back()->with('success', 'Email account assigned successfully.');
    }

    public function destroy(EmailAccount $emailAccount, User $user): RedirectResponse
    {
        $this->authorize('update', $emailAccount);

        $emailAccount->assignedUsers()->detach($user);

        return back()->with('success', 'Assignment revoked successfully.');
    }
}
