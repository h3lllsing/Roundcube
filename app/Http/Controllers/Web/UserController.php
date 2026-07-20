<?php

namespace App\Http\Controllers\Web;

use App\Enums\LoginEvent;
use App\Events\UserCreated;
use App\Events\UserDeleted;
use App\Events\UserForceDeleted;
use App\Events\UserRestored;
use App\Events\UserSuspended;
use App\Events\UserUnsuspended;
use App\Events\UserUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\SuspendUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\LoginAudit;
use App\Models\User;
use App\Services\CsvExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()->select(['id', 'name', 'email', 'role', 'suspended_at', 'created_at'])->addSelect([
            'last_login_at' => LoginAudit::whereColumn('user_id', 'users.id')
                ->where('event', LoginEvent::LoginSuccess)
                ->latest()
                ->take(1)
                ->select('created_at'),
        ]);

        if ($request->filled('search') && strlen($request->search) >= 2) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('role') && in_array($request->role, ['user', 'admin', 'super-admin'], true)) {
            $query->where('role', $request->role);
        }
        if ($request->filled('status')) {
            if ($request->status === 'suspended') {
                $query->whereNotNull('suspended_at');
            } elseif ($request->status === 'active') {
                $query->whereNull('suspended_at');
            }
        }
        if ($request->filled('date_from') && \Illuminate\Support\Facades\Validator::make(['date_from' => $request->date_from], ['date_from' => 'date'])->passes()) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to') && \Illuminate\Support\Facades\Validator::make(['date_to' => $request->date_to], ['date_to' => 'date'])->passes()) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->latest()->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $request->validated();

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->input('role', 'user'),
                'password' => Hash::make($request->password),
                'password_changed_at' => now(),
            ]);

            if ($request->input('status') === 'suspended') {
                $user->suspended_at = now();
                $user->save();
            }

            event(new UserCreated($user));

            return $user;
        });

        Cache::increment('dashboard:version');

        return redirect()->route('users.show', $user->id)->with('success', 'User created successfully.');
    }

    public function show(int $id): View
    {
        $this->authorize('view', User::class);

        $user = User::findOrFail($id);

        $lastLogin = LoginAudit::where('user_id', $user->id)
            ->where('event', LoginEvent::LoginSuccess)
            ->latest()
            ->first();

        return view('users.show', compact('user', 'lastLogin'));
    }

    public function edit(int $id): View
    {
        $this->authorize('update', User::class);

        $user = User::findOrFail($id);

        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $this->checkOptimisticLock($user, $request);
        $validated = $request->validated();

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

            $original = $user->getOriginal();
            $user->update($validated);

            $changed = $user->getChanges();
            $dirty = array_diff_key($changed, array_flip(['updated_at']));
            $oldValues = array_intersect_key($original, $dirty);

            event(new UserUpdated($user, $oldValues, $dirty));
        });

        Cache::increment('dashboard:version');

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function suspend(SuspendUserRequest $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('suspend', $user);

        DB::transaction(function () use ($user, $request) {
            $user->forceFill([
                'suspended_at' => now(),
                'suspension_reason' => $request->input('reason'),
            ])->save();

            event(new UserSuspended($user, $request->input('reason')));
        });

        Cache::increment('dashboard:version');

        return redirect()->route('users.show', $user->id)->with('success', 'User suspended successfully.');
    }

    public function unsuspend(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('unsuspend', $user);

        DB::transaction(function () use ($user) {
            $user->forceFill([
                'suspended_at' => null,
                'suspension_reason' => null,
            ])->save();

            event(new UserUnsuspended($user));
        });

        Cache::increment('dashboard:version');

        return redirect()->route('users.show', $user->id)->with('success', 'User unsuspended successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);

        $userEmail = $user->email;
        $userName = $user->name;

        DB::transaction(function () use ($user, $userEmail, $userName) {
            $user->deleted_by = Auth::id();
            $user->saveQuietly();
            $user->delete();

            event(new UserDeleted($user, $userEmail, $userName));
        });

        Cache::increment('dashboard:version');

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->authorize('restore', User::class);

        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        event(new UserRestored($user, $user->email, $user->name));

        Cache::increment('dashboard:version');

        return redirect()->route('users.index')->with('success', 'User restored successfully.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $this->authorize('forceDelete', User::class);

        $user = User::withTrashed()->findOrFail($id);

        if ($user->assignedEmailAccounts()->count() > 0) {
            return redirect()->route('users.index')
                ->with('error', 'Cannot delete user with email account assignments. Revoke assignments first.');
        }

        $userEmail = $user->email;
        $userName = $user->name;
        $user->forceDelete();

        event(new UserForceDeleted($user, $userEmail, $userName));

        Cache::increment('dashboard:version');

        return redirect()->route('users.index')->with('success', 'User permanently deleted.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->authorize('bulkDelete', User::class);

        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:users,id'])['ids'];
        $count = count($ids);

        DB::transaction(function () use ($ids) {
            User::whereIn('id', $ids)->each(function (User $user) {
                $user->deleted_by = Auth::id();
                $user->saveQuietly();
                $user->delete();
            });
        });

        Cache::increment('dashboard:version');

        return back()->with('success', "{$count} users deleted.");
    }

    public function export(): StreamedResponse
    {
        $this->authorize('viewAny', User::class);

        $users = User::latest()->get();
        $rows = $users->map(fn ($u) => [
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'suspended' => $u->isSuspended() ? 'Yes' : 'No',
            'created_at' => $u->created_at?->toDateTimeString(),
        ]);

        return (new CsvExportService)->export($rows, ['name', 'email', 'role', 'suspended', 'created_at'], 'users');
    }
}
