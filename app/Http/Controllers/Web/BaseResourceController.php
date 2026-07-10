<?php

namespace App\Http\Controllers\Web;

use App\Helpers\ModuleCache;
use App\Helpers\RbacScope;
use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use App\Services\RenewalSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

abstract class BaseResourceController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function moduleSlug(): string;

    abstract protected function viewPrefix(): string;

    abstract protected function indexSelect(): array;

    abstract protected function indexVariable(): string;

    abstract protected function recordVariable(): string;

    protected function indexWith(): array
    {
        return ['module'];
    }

    protected function showWith(): array
    {
        return $this->indexWith();
    }

    protected function applyIndexFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }
    }

    protected function prepareStoreData(array $validated, Request $request): array
    {
        return $validated;
    }

    protected function prepareUpdateData(array $validated, Request $request, $model): array
    {
        return $validated;
    }

    protected function createExtraData(): array
    {
        return [];
    }

    protected function editExtraData($model): array
    {
        return $this->createExtraData();
    }

    protected function showExtraData($model): array
    {
        return [];
    }

    protected function resourceName(): string
    {
        return str_replace(['-', '_'], ' ', $this->moduleSlug());
    }

    protected function renewalFields(): array
    {
        return ['expiry_date', 'name', 'service_provider_id', 'user_id', 'module_id'];
    }

    protected function userOwnedFilter(): void
    {
        $modelClass = $this->modelClass();
        RbacScope::apply($modelClass, 'module');
    }

    protected function resolveModule(): ?Module
    {
        return ModuleCache::findBySlug($this->moduleSlug());
    }

    public function index(Request $request): View
    {
        $this->userOwnedFilter();
        $modelClass = $this->modelClass();
        $query = $modelClass::with($this->indexWith());

        $this->applyIndexFilters($query, $request);

        $records = $query->select($this->indexSelect())->latest()->paginate(20);

        $user = Auth::user();
        $module = $this->resolveModule();
        $isSuperAdmin = $user->hasRole('super-admin');
        $canCreate = $isSuperAdmin || ($module && $user->canOnModule($module, 'create'));
        $canExport = $isSuperAdmin;
        $canBulkDelete = $isSuperAdmin || ($module && $user->canOnModule($module, 'delete'));
        $canBulkRestore = $isSuperAdmin;
        $canBulkForceDelete = $isSuperAdmin;
        $bulkActions = ['update-status'];
        if ($canBulkDelete) {
            $bulkActions[] = 'delete';
        }
        if ($canBulkRestore) {
            $bulkActions[] = 'restore';
        }
        if ($canBulkForceDelete) {
            $bulkActions[] = 'force-delete';
        }

        return view($this->viewPrefix().'.index', array_merge(
            compact('canCreate', 'canExport', 'canBulkDelete', 'canBulkRestore', 'canBulkForceDelete', 'bulkActions'),
            ['vaultModule' => \App\Helpers\ModuleCache::findBySlug('vault')],
            [$this->indexVariable() => $records]
        ));
    }

    public function create(): View
    {
        $user = Auth::user();
        if (! $user->hasRole('super-admin')) {
            $module = $this->resolveModule();
            abort_unless($module && $user->canOnModule($module, 'create'), 403);
        }

        return view($this->viewPrefix().'.create', array_merge(
            $this->createFormData(),
            $this->createExtraData()
        ));
    }

    public function show(int $id): View
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super-admin');
        $module = ModuleCache::findBySlug($this->moduleSlug());

        abort_unless($isSuperAdmin || ($module && $user->canOnModule($module, 'read')), 403);

        $this->userOwnedFilter();
        $modelClass = $this->modelClass();
        $record = $modelClass::with($this->showWith())->findOrFail($id);

        return view($this->viewPrefix().'.show', array_merge(
            [$this->recordVariable() => $record],
            $this->showExtraData($record)
        ));
    }

    public function edit(int $id): View
    {
        $this->userOwnedFilter();
        $modelClass = $this->modelClass();
        $record = $modelClass::findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($record->module && $user->canOnModule($record->module, 'update')), 403);

        return view($this->viewPrefix().'.edit', array_merge(
            [$this->recordVariable() => $record],
            $this->createFormData(),
            $this->editExtraData($record)
        ));
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        $this->userOwnedFilter();
        $modelClass = $this->modelClass();
        $record = $modelClass::findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($record->module && $user->canOnModule($record->module, 'delete')), 403);

        DB::transaction(function () use ($record) {
            $record->delete();
            app(RenewalSyncService::class)->remove($record);
        });

        return redirect()->route($this->viewPrefix().'.index')->with('success', ucfirst($this->resourceName()).' deleted successfully.');
    }

    public function restore($id)
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $this->userOwnedFilter();
        $modelClass = $this->modelClass();
        $model = $modelClass::withTrashed()->findOrFail($id);
        DB::transaction(function () use ($model) {
            $model->restore();
            app(RenewalSyncService::class)->restore($model);
        });

        return redirect()->route($this->viewPrefix().'.index')
            ->with('success', __(ucfirst($this->resourceName()).' restored successfully.'));
    }

    public function forceDelete($id)
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $this->userOwnedFilter();
        $modelClass = $this->modelClass();
        $model = $modelClass::withTrashed()->findOrFail($id);
        $model->forceDelete();

        return redirect()->route($this->viewPrefix().'.index')
            ->with('success', __(ucfirst($this->resourceName()).' permanently deleted.'));
    }

    protected function createFormData(): array
    {
        return [
            'modules' => Module::orderBy('name')->pluck('name', 'id'),
            'users' => User::orderBy('name')->pluck('name', 'id'),
        ];
    }
}
