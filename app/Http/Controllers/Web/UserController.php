<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LoginAudit;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\Task;
use App\Models\User;
use App\Models\UserModulePermission;
use App\Services\UserPermissionService;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserPermissionService $permissionService
    ) {}

    public function index(Request $request): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $query = User::query()->select(['id', 'name', 'email', 'suspended_at', 'created_at'])->addSelect([
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
            $query->whereHas('roles', fn ($q) => $q->where('slug', $request->role));
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

        $users = $query->with('roles')->latest()->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $roles = Role::whereNotIn('slug', ['*', 'super-admin'])->orderBy('name')->get();
        $modules = $this->permissionService->getModulesWithFeatures();
        $userOverrides = [];

        $cloneUsers = User::orderBy('name')->get();

        $roleSummaries = $this->permissionService->buildRoleSummaries($roles);

        return view('users.create', compact('roles', 'modules', 'userOverrides', 'cloneUsers', 'roleSummaries'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $this->permissionService->preventSuperAdminAssignment($request);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'nullable|in:active,suspended',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'permissions' => ['nullable', 'array', function ($attr, $value, $fail) {
                $moduleIds = array_keys($value);
                $validIds = Module::whereIn('id', $moduleIds)->pluck('id')->all();
                $invalid = array_diff($moduleIds, $validIds);
                if ($invalid) {
                    $fail('Invalid module IDs: '.implode(', ', $invalid));
                }
            }],
            'clone_user_id' => 'nullable|integer|exists:users,id',
            'copy_roles' => 'nullable|boolean',
            'copy_overrides' => 'nullable|boolean',
            'copy_status' => 'nullable|boolean',
            'clone_role_handling' => 'nullable|in:use_cloned,replace,merge',
        ]);

        $superAdminRoleId = $this->permissionService->getSuperAdminRoleId();

        $user = DB::transaction(function () use ($request, $superAdminRoleId) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            if ($request->input('status') === 'suspended') {
                $user->suspended_at = now();
                $user->save();
            }

            $finalRoles = [];

            if ($request->filled('clone_user_id')) {
                $sourceUser = User::findOrFail($request->input('clone_user_id'));

                if ($request->boolean('copy_roles')) {
                    $clonedRoleIds = $sourceUser->roles()->pluck('roles.id')->toArray();
                    $clonedRoleIds = array_filter($clonedRoleIds, fn($id) => (int) $id !== (int) $superAdminRoleId);
                    $finalRoles = $clonedRoleIds;
                }

                if ($request->boolean('copy_overrides')) {
                    $this->permissionService->clonePermissions($sourceUser, $user);
                }

                if ($request->boolean('copy_status') && $sourceUser->suspended_at) {
                    $user->suspended_at = $sourceUser->suspended_at;
                    $user->save();
                }

                $roleHandling = $request->input('clone_role_handling', 'use_cloned');
                $selectedRoles = $request->input('roles', []);

                if ($roleHandling === 'replace') {
                    $finalRoles = $selectedRoles;
                } elseif ($roleHandling === 'merge') {
                    $finalRoles = array_unique(array_merge($finalRoles, $selectedRoles));
                }
            } else {
                $finalRoles = $request->input('roles', []);
            }

            if (!empty($finalRoles)) {
                $user->roles()->sync($finalRoles);
            }

            $this->permissionService->saveUserModulePermissions($user, $request->input('permissions'));

            activity()->event('created')
                ->performedOn($user)
                ->causedBy(Auth::user())
                ->withProperties([
                    'roles' => $user->roles->pluck('slug')->toArray(),
                    'clone_source_id' => $request->input('clone_user_id'),
                ])
                ->log('User created: '.$user->email);

            return $user;
        });

        return redirect()->route('users.show', $user->id)->with('success', 'User created successfully.');
    }

    public function show(int $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $user = User::findOrFail($id);
        $modules = Module::with('feature')->orderBy('name')->get();
        $moduleIds = $modules->pluck('id');
        $roleIds = $user->roles()->pluck('roles.id')->toArray();

        $rolePerms = ModuleRolePermission::whereIn('role_id', $roleIds)
            ->whereIn('module_id', $moduleIds)
            ->get()
            ->groupBy('module_id');

        $userOverrides = UserModulePermission::where('user_id', $user->id)
            ->whereIn('module_id', $moduleIds)
            ->get()
            ->keyBy('module_id');

        $permKeys = config('permissions.keys');

        $modulePermissions = $modules->map(function ($module) use ($user, $permKeys, $rolePerms, $userOverrides) {
            $rolePermRecords = $rolePerms->get($module->id, collect());
            $userOverride = $userOverrides->get($module->id);

            $effective = [];
            foreach ($permKeys as $key) {
                $roleVal = null;
                foreach ($rolePermRecords as $rp) {
                    if ($rp->$key !== null) {
                        $roleVal = $rp->$key;
                        break;
                    }
                }
                $overrideVal = $userOverride ? $userOverride->$key : null;

                if ($overrideVal !== null) {
                    $effective[$key] = [
                        'role' => $roleVal,
                        'user_override' => $overrideVal,
                        'effective' => $overrideVal,
                        'source' => 'User Override',
                    ];
                } elseif ($roleVal !== null) {
                    $effective[$key] = [
                        'role' => $roleVal,
                        'user_override' => null,
                        'effective' => $roleVal,
                        'source' => 'Role',
                    ];
                } else {
                    $effective[$key] = [
                        'role' => null,
                        'user_override' => null,
                        'effective' => false,
                        'source' => 'None',
                    ];
                }
            }

            return (object) [
                'module_name' => $module->name,
                'feature' => $module->feature->name ?? null,
                'permissions' => $effective,
            ];
        });

        $inspectedIsSuperAdmin = $user->hasRole('super-admin');
        $permsCollection = collect($modulePermissions);

        $summary = [
            'roles_count' => $user->roles->count(),
            'accessible_modules' => $permsCollection->filter(fn ($mp) => $mp->permissions['can_read']['effective'])->count(),
            'denied_modules' => $permsCollection->filter(fn ($mp) => !$mp->permissions['can_read']['effective'])->count(),
            'overrides_count' => $permsCollection->sum(fn ($mp) => collect($mp->permissions)->filter(fn ($p) => $p['user_override'] !== null)->count()),
            'allowed_permissions' => $permsCollection->sum(fn ($mp) => collect($mp->permissions)->filter(fn ($p) => $p['effective'])->count()),
            'denied_permissions' => $permsCollection->sum(fn ($mp) => collect($mp->permissions)->filter(fn ($p) => !$p['effective'])->count()),
        ];

        $lastLogin = LoginAudit::where('user_id', $user->id)
            ->where('event', 'login_success')
            ->latest()
            ->first();

        $offboardingChecklist = [
            'suspended_at' => $user->suspended_at,
            'vault_entries_count' => $user->vaultEntries()->count(),
            'assigned_tasks_count' => Task::whereHas('assignees', fn($q) => $q->where('user_id', $user->id))->count(),
            'assigned_assets_count' => $user->assignedAssets()->count(),
            'activities_30d_count' => $user->activities()->where('created_at', '>=', now()->subDays(30))->count(),
            'can_suspend' => !$user->suspended_at && Auth::user()->hasRole('super-admin'),
            'can_unsuspend' => (bool)$user->suspended_at && Auth::user()->hasRole('super-admin'),
        ];

        return view('users.show', compact('user', 'modulePermissions', 'summary', 'inspectedIsSuperAdmin', 'lastLogin', 'offboardingChecklist'));
    }

    public function edit(int $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $user = User::findOrFail($id);
        $roles = Role::whereNotIn('slug', ['*', 'super-admin'])->orderBy('name')->get();
        $overrideCount = UserModulePermission::where('user_id', $user->id)->count();

        return view('users.edit', compact('user', 'roles', 'overrideCount'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);
        $this->permissionService->preventSuperAdminAssignment($request);

        $user = User::findOrFail($id);

        $this->checkOptimisticLock($user, $request);
        $validated = $request->validate([
            'updated_at' => 'required|date',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'suspended_at' => 'nullable|date',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($request->has('roles')) {
            $currentRoleIds = $user->roles()->pluck('roles.id')->toArray();
            if ($currentRoleIds !== $request->roles) {
                $overrideCount = UserModulePermission::where('user_id', $user->id)->count();
                if ($overrideCount > 0 && !$request->boolean('confirm_role_change')) {
                    return back()->withErrors(['confirm_role_change' => 'Please confirm the role change. This user has ' . $overrideCount . ' active permission override(s).'])->withInput();
                }
            }
        }

        $currentUser = Auth::user();
        $superAdminRoleId = $this->permissionService->getSuperAdminRoleId();

        DB::transaction(function () use ($user, $validated, $request, $currentUser, $superAdminRoleId) {
            if (array_key_exists('suspended_at', $validated)) {
                $suspendedAt = $validated['suspended_at'];
                unset($validated['suspended_at']);
                $user->forceFill(['suspended_at' => $suspendedAt])->save();
            }
        $original = $user->getOriginal();

        $user->update($validated);

        if ($request->has('roles')) {
            $newRoles = $request->roles;

            // Prevent self-demotion: cannot remove own super-admin role
            if ($currentUser->id === $user->id && $superAdminRoleId) {
                $currentRoles = $user->roles()->pluck('roles.id')->toArray();
                if (in_array($superAdminRoleId, $currentRoles) && ! in_array($superAdminRoleId, $newRoles)) {
                    abort(403, 'Cannot remove your own Super Admin role.');
                }
            }

            $user->roles()->sync($newRoles);
        }

        $changed = $user->getChanges();
        $dirty = array_diff_key($changed, array_flip(['updated_at']));
        $oldValues = array_intersect_key($original, $dirty);

        activity()->event('updated')
            ->performedOn($user)
            ->causedBy(Auth::user())
            ->withProperties([
                'old' => $oldValues,
                'attributes' => $dirty,
                'roles' => $request->has('roles') ? $user->roles->pluck('slug')->toArray() : null,
            ])
            ->log('User updated: '.$user->email);
    });

    return redirect()->route('users.index')->with('success', 'User updated successfully.');
}

    public function editPermissions(int $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $user = User::findOrFail($id);
        $modules = $this->permissionService->getModulesWithFeatures();
        $userOverrides = $this->permissionService->getUserOverrideMap($user->id);

        $categories = $modules->pluck('feature.name')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return view('users.permissions', compact('user', 'modules', 'userOverrides', 'categories'));
    }

    public function updatePermissions(Request $request, int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $user = User::findOrFail($id);

        $allowedKeys = config('permissions.keys');

        $request->validate([
            'permissions' => ['nullable', 'array', function ($attr, $value, $fail) use ($allowedKeys) {
                $moduleIds = array_keys($value);
                $validIds = Module::whereIn('id', $moduleIds)->pluck('id')->all();
                $invalid = array_diff($moduleIds, $validIds);
                if ($invalid) {
                    $fail('Invalid module IDs: '.implode(', ', $invalid));
                }

                foreach ($value as $moduleId => $perms) {
                    $extraKeys = array_diff(array_keys($perms), $allowedKeys);
                    if ($extraKeys) {
                        $fail("Invalid permission keys for module {$moduleId}: ".implode(', ', $extraKeys));
                    }
                }
            }],
        ]);

        $this->permissionService->saveUserModulePermissions($user, $request->input('permissions'));

        return redirect()->route('users.edit', $user->id)->with('success', 'Permission overrides updated successfully.');
    }

    public function suspend(Request $request, int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

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
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

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

    public function cloneForm(int $id): View
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $sourceUser = User::findOrFail($id);
        $roles = Role::whereNotIn('slug', ['*', 'super-admin'])->orderBy('name')->get();
        $modules = $this->permissionService->getModulesWithFeatures();
        $sourceRoles = $sourceUser->roles;
        $overrideCount = UserModulePermission::where('user_id', $sourceUser->id)->count();

        return view('users.clone', compact('sourceUser', 'roles', 'modules', 'sourceRoles', 'overrideCount'));
    }

    public function cloneStore(Request $request, int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $sourceUser = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'copy_roles' => 'boolean',
            'copy_overrides' => 'boolean',
            'copy_status' => 'boolean',
        ]);

        $hasSuperAdminRole = $sourceUser->hasRole('super-admin');
        if ($hasSuperAdminRole && !$request->boolean('confirm_super_admin')) {
            return back()->withErrors(['confirm_super_admin' => 'Please confirm cloning the Super Admin role.'])->withInput();
        }

        $validated['password'] = Hash::make($validated['password']);

        $newUser = DB::transaction(function () use ($validated, $request, $sourceUser) {
            $newUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            if ($request->boolean('copy_status')) {
                $newUser->forceFill(['suspended_at' => $sourceUser->suspended_at])->save();
            }

            if ($request->boolean('copy_roles')) {
                $roleIds = $sourceUser->roles()->pluck('roles.id')->toArray();
                $newUser->roles()->sync($roleIds);
            }

            if ($request->boolean('copy_overrides')) {
                $this->permissionService->clonePermissions($sourceUser, $newUser);
            }

            activity()->event('cloned')
                ->performedOn($sourceUser)
                ->causedBy(Auth::user())
                ->withProperties([
                    'target_user_id' => $newUser->id,
                    'target_user_name' => $newUser->name,
                    'target_user_email' => $newUser->email,
                    'copied_roles' => $request->boolean('copy_roles'),
                    'copied_overrides' => $request->boolean('copy_overrides'),
                    'copied_status' => $request->boolean('copy_status'),
                ])
                ->log('User cloned: '.$sourceUser->email.' -> '.$newUser->email);

            return $newUser;
        });

        return redirect()->route('users.show', $newUser->id)
            ->with('success', 'User cloned from '.$sourceUser->name.' successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $user = User::findOrFail($id);

        $superAdminRoleId = $this->permissionService->getSuperAdminRoleId();
        if ($superAdminRoleId && $user->roles()->where('roles.id', $superAdminRoleId)->exists()) {
            $superAdminCount = User::whereHas('roles', fn ($q) => $q->where('roles.id', $superAdminRoleId))->count();
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
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

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
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

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
