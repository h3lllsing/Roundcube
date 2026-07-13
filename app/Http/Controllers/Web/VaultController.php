<?php

namespace App\Http\Controllers\Web;

use App\Helpers\ModuleCache;
use App\Helpers\RbacScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVaultRequest;
use App\Http\Requests\UpdateVaultRequest;
use App\Models\Module;
use App\Models\User;
use App\Models\VaultEntry;
use App\Services\VaultService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VaultController extends Controller
{
    private function moduleSlug(): string
    {
        return 'vault';
    }

    private function userOwnedFilter(): void
    {
        RbacScope::apply(VaultEntry::class, 'module');
    }

    public function index(Request $request): View
    {
        $user = Auth::user();
        $module = ModuleCache::findBySlug($this->moduleSlug());
        abort_unless($user->hasRole('super-admin') || ($module && $user->canOnModule($module, 'read')), 403);

        $this->userOwnedFilter();

        return $this->renderVaultIndex($request);
    }

    public function myVault(Request $request): View
    {
        $request->attributes->set('_my_vault', true);

        return $this->renderVaultIndex($request);
    }

    private function renderVaultIndex(Request $request): View
    {
        $query = VaultEntry::with('module');
        if ($request->attributes->get('_my_vault')) {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('search')) {
            $query->where('service_name', 'like', '%'.$request->search.'%');
        }

        $entries = $query->select(['id', 'module_id', 'service_name', 'service_url', 'username', 'created_at'])->latest()->paginate(20);

        $user = Auth::user();
        $module = ModuleCache::findBySlug($this->moduleSlug());
        $isSuperAdmin = $user->hasRole('super-admin');
        $canCreate = $isSuperAdmin || ($module && $user->canOnModule($module, 'create'));
        $canExport = $isSuperAdmin;
        $canBulkDelete = $isSuperAdmin || ($module && $user->canOnModule($module, 'delete'));
        $canBulkRestore = $isSuperAdmin;
        $canBulkForceDelete = $isSuperAdmin;
        $bulkActions = [];
        if ($canBulkDelete) $bulkActions[] = 'delete';
        if ($canBulkRestore) $bulkActions[] = 'restore';
        if ($canBulkForceDelete) $bulkActions[] = 'force-delete';

        return view('vault.index', compact('entries', 'canCreate', 'canExport', 'canBulkDelete', 'canBulkRestore', 'canBulkForceDelete', 'bulkActions'));
    }

    public function create(): View
    {
        $user = Auth::user();
        if (! $user->hasRole('super-admin')) {
            $module = ModuleCache::findBySlug($this->moduleSlug());
            abort_unless($module && $user->canOnModule($module, 'create'), 403);
        }

        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('vault.create', compact('modules', 'users'));
    }

    public function store(StoreVaultRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        $password = $request->filled('encrypted_password')
            ? $request->encrypted_password
            : ($request->filled('password') ? $request->password : '');

        unset($validated['encrypted_password'], $validated['password']);

        $user = Auth::user();
        $module = ModuleCache::findBySlug($this->moduleSlug());
        if (! $user->hasRole('super-admin')) {
            abort_unless($module && $user->canOnModule($module, 'create'), 403);
        }
        if ($module) {
            $validated['module_id'] = $module->id;
        }

        $entry = DB::transaction(function () use ($validated, $password) {
            $entry = new VaultEntry;
            $entry->fill($validated);
            $entry->encryptPassword($password ?: '');
            $entry->save();
            return $entry;
        });

        return redirect()->route('vault.index')->with('success', 'Vault entry created successfully.');
    }

    public function show(int $id): View
    {
        $this->userOwnedFilter();
        $entry = VaultEntry::with('module')->findOrFail($id);

        return view('vault.show', compact('entry'));
    }

    public function edit(int $id): View
    {
        $this->userOwnedFilter();
        $entry = VaultEntry::findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($entry->module && $user->canOnModule($entry->module, 'update')), 403);

        $modules = Module::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('vault.edit', compact('entry', 'modules', 'users'));
    }

    public function update(UpdateVaultRequest $request, int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $entry = VaultEntry::findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($entry->module && $user->canOnModule($entry->module, 'update')), 403);
        $this->checkOptimisticLock($entry, $request);

        $validated = $request->validated();
        unset($validated['module_id']);

        $password = $request->filled('encrypted_password') ? $request->encrypted_password : ($request->filled('password') ? $request->password : null);
        unset($validated['encrypted_password'], $validated['password']);

        DB::transaction(function () use ($entry, $validated, $password) {
            $entry->update($validated);
            if ($password) {
                $entry->encryptPassword($password);
                $entry->save();
            }
        });

        return redirect()->route('vault.index')->with('success', 'Vault entry updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $entry = VaultEntry::findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($entry->module && $user->canOnModule($entry->module, 'delete')), 403);

        $entry->delete();

        return redirect()->route('vault.index')->with('success', 'Vault entry deleted successfully.');
    }

    public function restore($id)
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $this->userOwnedFilter();
        $model = VaultEntry::withTrashed()->findOrFail($id);
        $model->restore();

        return redirect()->route('vault.index')
            ->with('success', __('Vault entry restored successfully.'));
    }

    public function forceDelete($id)
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $this->userOwnedFilter();
        $model = VaultEntry::withTrashed()->findOrFail($id);
        $model->forceDelete();

        return redirect()->route('vault.index')
            ->with('success', __('Vault entry permanently deleted.'));
    }

    public function reveal(int $id, VaultService $vaultService): RedirectResponse
    {
        $this->userOwnedFilter();
        $entry = VaultEntry::with('module', 'user')->findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($entry->module && $user->canOnModule($entry->module, 'reveal')), 403);

        $password = $vaultService->reveal($entry, $user);

        return redirect()->route('vault.show', $entry->id)
            ->with('revealed_password', $password);
    }
}
