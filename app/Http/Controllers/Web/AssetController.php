<?php

namespace App\Http\Controllers\Web;

use App\Helpers\RbacScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\Module;
use App\Models\VaultEntry;
use App\Services\AssetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AssetController extends Controller
{
    private function moduleSlug(): string
    {
        return 'assets';
    }

    private function userOwnedFilter(): void
    {
        RbacScope::apply(Asset::class, 'module');
    }

    private function denyIfNotSuperAdminOrCanCreate(Module $module): void
    {
        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || $user->canOnModule($module, 'create'), 403);
    }

    private function denyIfNotSuperAdminOrCanUpdate(Asset $record): void
    {
        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($record->module && $user->canOnModule($record->module, 'update')), 403);
    }

    private function denyIfNotSuperAdminOrCanDelete(Asset $record): void
    {
        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($record->module && $user->canOnModule($record->module, 'delete')), 403);
    }

    private function getLookupData(): array
    {
        $user = Auth::user();
        return [
            'modules' => Module::orderBy('name')->pluck('name', 'id'),
            'vaultEntries' => $user->hasRole('super-admin')
                ? VaultEntry::orderBy('service_name')->pluck('service_name', 'id')
                : VaultEntry::where('user_id', $user->id)->orderBy('service_name')->pluck('service_name', 'id'),
        ];
    }

    public function index(Request $request): View
    {
        $this->userOwnedFilter();
        $query = Asset::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $assets = $query->select(['id', 'asset_tag', 'brand', 'model', 'assigned_user_name', 'status', 'premises', 'anydesk_id', 'module_id'])->with('module')->latest()->paginate(20);

        $user = Auth::user();
        $module = \App\Helpers\ModuleCache::findBySlug($this->moduleSlug());
        $isSuperAdmin = $user->hasRole('super-admin');
        $canCreate = $isSuperAdmin || ($module && $user->canOnModule($module, 'create'));
        $canExport = $isSuperAdmin;
        $canBulkDelete = $isSuperAdmin || ($module && $user->canOnModule($module, 'delete'));
        $canBulkRestore = $isSuperAdmin;
        $canBulkForceDelete = $isSuperAdmin;
        $bulkActions = ['update-status'];
        if ($canBulkDelete) $bulkActions[] = 'delete';
        if ($canBulkRestore) $bulkActions[] = 'restore';
        if ($canBulkForceDelete) $bulkActions[] = 'force-delete';

        return view('assets.index', compact('assets', 'canCreate', 'canExport', 'canBulkDelete', 'canBulkRestore', 'canBulkForceDelete', 'bulkActions'));
    }

    public function create(): View
    {
        $user = Auth::user();
        if (! $user->hasRole('super-admin')) {
            $module = \App\Helpers\ModuleCache::findBySlug($this->moduleSlug());
            abort_unless($module && $user->canOnModule($module, 'create'), 403);
        }

        return view('assets.create', $this->getLookupData());
    }

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = Auth::user();
        $module = \App\Helpers\ModuleCache::findBySlug($this->moduleSlug());
        if (! $user->hasRole('super-admin')) {
            abort_unless($module && $user->canOnModule($module, 'create'), 403);
        }
        if ($module) {
            $validated['module_id'] = $module->id;
        }

        if ($request->hasFile('primary_image')) {
            $validated['primary_image'] = $request->file('primary_image')->store('assets', 'public');
        }

        $validated['user_id'] = Auth::id();

        app(AssetService::class)->create($validated);

        return redirect()->route('assets.index')->with('success', 'Asset created successfully.');
    }

    public function show(int $id): View
    {
        $this->userOwnedFilter();
        $asset = Asset::with(['user', 'module.feature', 'vaultEntry', 'assignments' => function ($q) {
            $q->with(['assignee', 'assigner'])->latest('assigned_at');
        }])->withCount('attachments')->findOrFail($id);

        $user = Auth::user();
        $canAccessVault = $asset->vault_entry_id && $user->canAccessVault($asset->vaultEntry);

        return view('assets.show', compact('asset', 'canAccessVault'));
    }

    public function edit(int $id): View
    {
        $this->userOwnedFilter();
        $asset = Asset::findOrFail($id);

        $this->denyIfNotSuperAdminOrCanUpdate($asset);

        return view('assets.edit', array_merge(['asset' => $asset], $this->getLookupData()));
    }

    public function update(UpdateAssetRequest $request, int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $asset = Asset::findOrFail($id);

        $this->denyIfNotSuperAdminOrCanUpdate($asset);
        $this->checkOptimisticLock($asset, $request);

        $validated = $request->validated();
        unset($validated['module_id']);

        if ($request->hasFile('primary_image')) {
            if ($asset->primary_image) {
                Storage::disk('public')->delete($asset->primary_image);
            }
            $validated['primary_image'] = $request->file('primary_image')->store('assets', 'public');
        }

        $asset->update($validated);

        return redirect()->route('assets.index')->with('success', 'Asset updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $asset = Asset::findOrFail($id);

        $this->denyIfNotSuperAdminOrCanDelete($asset);

        if ($asset->status === 'assigned') {
            return redirect()->route('assets.index')
                ->with('error', 'Cannot delete asset "'.$asset->asset_tag.'" — it is currently assigned to a user. Return it first.');
        }

        app(AssetService::class)->delete($asset);

        return redirect()->route('assets.index')->with('success', 'Asset deleted successfully.');
    }

    public function restore($id)
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $this->userOwnedFilter();
        $model = Asset::withTrashed()->findOrFail($id);
        $model->restore();

        return redirect()->route('assets.index')
            ->with('success', 'Asset restored successfully.');
    }

    public function forceDelete($id)
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $this->userOwnedFilter();
        $model = Asset::withTrashed()->findOrFail($id);
        $model->forceDelete();

        return redirect()->route('assets.index')
            ->with('success', 'Asset permanently deleted.');
    }

    public function assign(Request $request, int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $asset = Asset::findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($asset->module && $user->canOnModule($asset->module, 'update')), 403);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'department' => 'nullable|string|max:191',
            'issue_date' => 'nullable|date',
            'expected_return_at' => 'nullable|date',
            'assignment_reason' => 'nullable|string|in:New Employee,Replacement,Temporary,Loan,Other',
            'note' => 'nullable|string|max:1000',
        ]);
        $validated['assigned_by'] = Auth::id();

        app(AssetService::class)->assign($asset, $validated);

        return redirect()->route('assets.show', $asset->id)->with('success', 'Asset assigned successfully.');
    }

    public function returnAsset(Request $request, int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $asset = Asset::findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($asset->module && $user->canOnModule($asset->module, 'update')), 403);

        $validated = $request->validate([
            'condition_on_return' => 'nullable|string|in:new,good,fair,poor,damaged',
            'note' => 'nullable|string|max:1000',
        ]);

        app(AssetService::class)->returnAsset($asset, $validated);

        return redirect()->route('assets.show', $asset->id)->with('success', 'Asset returned successfully.');
    }
}
