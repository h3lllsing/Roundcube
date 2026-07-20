<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LoginAudit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $query = User::query()->select(['id', 'name', 'email', 'role', 'suspended_at', 'created_at'])->addSelect([
            'last_login_at' => LoginAudit::whereColumn('user_id', 'users.id')
                ->where('event', 'login_success')
                ->latest()
                ->take(1)
                ->select('created_at'),
        ]);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('status')) {
            if ($request->status === 'suspended') {
                $query->whereNotNull('suspended_at');
            } elseif ($request->status === 'active') {
                $query->whereNull('suspended_at');
            }
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->latest()->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|in:user,admin',
            'status' => 'nullable|in:active,suspended',
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->input('role', 'user'),
                'password' => Hash::make($request->password),
            ]);

            if ($request->input('status') === 'suspended') {
                $user->suspended_at = now();
                $user->save();
            }

            activity()->event('created')
                ->performedOn($user)
                ->causedBy(Auth::user())
                ->withProperties(['role' => $user->role])
                ->log('User created: '.$user->email);

            return $user;
        });

        return redirect()->route('users.show', $user->id)->with('success', 'User created successfully.');
    }

    public function show(int $id): View
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $user = User::findOrFail($id);

        $lastLogin = LoginAudit::where('user_id', $user->id)
            ->where('event', 'login_success')
            ->latest()
            ->first();

        return view('users.show', compact('user', 'lastLogin'));
    }

    public function edit(int $id): View
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $user = User::findOrFail($id);

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $user = User::findOrFail($id);

        $this->checkOptimisticLock($user, $request);
        $validated = $request->validate([
            'updated_at' => 'required|date',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'nullable|in:user,admin',
            'suspended_at' => 'nullable|date',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $currentUser = Auth::user();

        DB::transaction(function () use ($user, $validated, $request, $currentUser) {
            if (array_key_exists('suspended_at', $validated)) {
                $suspendedAt = $validated['suspended_at'];
                unset($validated['suspended_at']);
                $user->forceFill(['suspended_at' => $suspendedAt])->save();
            }

            if (isset($validated['role'])) {
                if ($currentUser->id === $user->id && $user->role === 'super-admin' && $validated['role'] !== 'super-admin') {
                    abort(403, 'Cannot remove your own Super Admin role.');
                }
            }

            $original = $user->getOriginal();
            $user->update($validated);

            $changed = $user->getChanges();
            $dirty = array_diff_key($changed, array_flip(['updated_at']));
            $oldValues = array_intersect_key($original, $dirty);

            activity()->event('updated')
                ->performedOn($user)
                ->causedBy(Auth::user())
                ->withProperties([
                    'old' => $oldValues,
                    'attributes' => $dirty,
                ])
                ->log('User updated: '.$user->email);
        });

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function suspend(Request $request, int $id): RedirectResponse
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $request->validate(['reason' => 'nullable|string|max:1000']);

        $user = User::findOrFail($id);
        DB::transaction(function () use ($user, $request) {
            $user->forceFill([
                'suspended_at' => now(),
                'suspension_reason' => $request->input('reason'),
            ])->save();

            activity()->event('suspended')
                ->performedOn($user)
                ->causedBy(Auth::user())
                ->withProperties(['reason' => $request->input('reason')])
                ->log('User suspended: '.$user->email);
        });

        return redirect()->route('users.show', $user->id)->with('success', 'User suspended successfully.');
    }

    public function unsuspend(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $user = User::findOrFail($id);
        DB::transaction(function () use ($user) {
            $user->forceFill([
                'suspended_at' => null,
                'suspension_reason' => null,
            ])->save();

            activity()->event('unsuspended')
                ->performedOn($user)
                ->causedBy(Auth::user())
                ->log('User unsuspended: '.$user->email);
        });

        return redirect()->route('users.show', $user->id)->with('success', 'User unsuspended successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $user = User::findOrFail($id);

        if ($user->role === 'super-admin') {
            $superAdminCount = User::where('role', 'super-admin')->count();
            if ($superAdminCount <= 1) {
                abort(403, 'Cannot delete the last Super Admin user.');
            }
        }

        $userEmail = $user->email;
        $userName = $user->name;

        DB::transaction(function () use ($user, $userEmail, $userName) {
            $user->delete();

            activity()->event('deleted')
                ->performedOn($user)
                ->causedBy(Auth::user())
                ->withProperties([
                    'email' => $userEmail,
                    'name' => $userName,
                ])
                ->log('User deleted: '.$userEmail);
        });

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        activity()->event('restored')
            ->performedOn($user)
            ->causedBy(Auth::user())
            ->withProperties(['email' => $user->email, 'name' => $user->name])
            ->log('User restored: '.$user->email);

        return redirect()->route('users.index')->with('success', 'User restored successfully.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $user = User::withTrashed()->findOrFail($id);
        $userEmail = $user->email;
        $userName = $user->name;
        $user->forceDelete();

        activity()->event('permanently_deleted')
            ->performedOn($user)
            ->causedBy(Auth::user())
            ->withProperties(['email' => $userEmail, 'name' => $userName])
            ->log('User permanently deleted: '.$userEmail);

        return redirect()->route('users.index')->with('success', 'User permanently deleted.');
    }
}
