<?php

namespace App\Http\Controllers\Web;

use App\Events\EmailAccountAssigned;
use App\Events\EmailAccountRevoked;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssignmentRequest;
use App\Models\EmailAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EmailAssignmentController extends Controller
{
    public function store(StoreAssignmentRequest $request, EmailAccount $emailAccount): RedirectResponse
    {
        Gate::authorize('assign-accounts');

        $validated = $request->validated();

        $emailAccount->assignedUsers()->syncWithoutDetaching([
            $validated['user_id'] => [
                'can_send' => $validated['can_send'] ?? true,
                'can_receive' => $validated['can_receive'] ?? true,
                'assigned_by' => Auth::id(),
            ],
        ]);

        $assignedUser = User::find($validated['user_id']);

        event(new EmailAccountAssigned(
            $emailAccount,
            $assignedUser,
            $validated['can_send'] ?? true,
            $validated['can_receive'] ?? true,
        ));

        return back()->with('success', 'Email account assigned successfully.');
    }

    public function destroy(EmailAccount $emailAccount, User $user): RedirectResponse
    {
        Gate::authorize('assign-accounts');

        $emailAccount->assignedUsers()->detach($user);

        event(new EmailAccountRevoked($emailAccount, $user));

        return back()->with('success', 'Assignment revoked successfully.');
    }
}
