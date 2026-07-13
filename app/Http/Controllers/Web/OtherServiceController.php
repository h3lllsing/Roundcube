<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\StoreOtherServiceRequest;
use App\Http\Requests\UpdateOtherServiceRequest;
use App\Models\Module;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Services\RenewalSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Concerns\CleansPasswords;
use Illuminate\View\View;

class OtherServiceController extends BaseResourceController
{
    use CleansPasswords;
    protected function modelClass(): string
    {
        return OtherService::class;
    }

    protected function moduleSlug(): string
    {
        return 'other-services';
    }

    protected function viewPrefix(): string
    {
        return 'other-services';
    }

    protected function indexSelect(): array
    {
        return ['id', 'module_id', 'name', 'service_type', 'cost', 'expiry_date', 'status', 'password', 'login_url'];
    }

    protected function indexVariable(): string
    {
        return 'services';
    }

    protected function recordVariable(): string
    {
        return 'service';
    }

    protected function indexWith(): array
    {
        return ['module', 'serviceProvider'];
    }

    protected function showWith(): array
    {
        return ['module', 'serviceProvider', 'user'];
    }

    protected function showExtraData($model): array
    {
        return [
            'vaultModule' => \App\Helpers\ModuleCache::findBySlug('vault'),
            'renewals' => \App\Models\ExpiryTracker::where('trackable_type', OtherService::class)
                ->where('trackable_id', $model->id)
                ->get(),
        ];
    }

    protected function createExtraData(): array
    {
        return [
            'serviceProviders' => ServiceProvider::orderBy('name')->pluck('name', 'id'),
        ];
    }

    protected function applyIndexFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }
    }

    protected function prepareUpdateData(array $validated, Request $request, $model): array
    {
        $this->cleanPasswordField($validated);

        return $validated;
    }

    public function store(StoreOtherServiceRequest $request): RedirectResponse
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
        $validated['user_id'] = Auth::id();
        $otherService = OtherService::create($validated);
        app(RenewalSyncService::class)->sync($otherService);

        return redirect()->route('other-services.index')->with('success', 'Other service created successfully.');
    }

    public function update(UpdateOtherServiceRequest $request, int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $service = OtherService::findOrFail($id);
        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($service->module && $user->canOnModule($service->module, 'update')), 403);
        $this->checkOptimisticLock($service, $request);
        $data = $request->validated();
        unset($data['module_id']);
        $data = $this->prepareUpdateData($data, $request, $service);
        $service->update($data);
        if ($service->wasChanged($this->renewalFields())) {
            app(RenewalSyncService::class)->sync($service);
        }

        return redirect()->route('other-services.index')->with('success', 'Other service updated successfully.');
    }

    public function getPassword(int $id): JsonResponse
    {
        $user = Auth::user();
        $serviceModule = \App\Helpers\ModuleCache::findBySlug($this->moduleSlug());
        abort_unless($user->hasRole('super-admin') || ($serviceModule && $user->canOnModule($serviceModule, 'read')), 403);
        $this->userOwnedFilter();
        $service = OtherService::findOrFail($id);
        $vaultModule = \App\Helpers\ModuleCache::findBySlug('vault');
        abort_unless($user->hasRole('super-admin') || ($vaultModule && $user->canOnModule($vaultModule, 'reveal')), 403);
        activity()->event('revealed')
            ->performedOn($service)
            ->causedBy($user)
            ->withProperties(['type' => 'other_service_password'])
            ->log('Password revealed for Other Service: '.$service->name);

        return response()->json(['password' => $service->password]);
    }

    public function logPasswordCopy(int $id): JsonResponse
    {
        $user = Auth::user();
        $serviceModule = \App\Helpers\ModuleCache::findBySlug($this->moduleSlug());
        abort_unless($user->hasRole('super-admin') || ($serviceModule && $user->canOnModule($serviceModule, 'read')), 403);
        $this->userOwnedFilter();
        $service = OtherService::findOrFail($id);
        $vaultModule = \App\Helpers\ModuleCache::findBySlug('vault');
        abort_unless($user->hasRole('super-admin') || ($vaultModule && $user->canOnModule($vaultModule, 'reveal')), 403);
        activity()->event('copied')
            ->performedOn($service)
            ->causedBy($user)
            ->withProperties(['type' => 'other_service_password'])
            ->log('Password copied for Other Service: '.$service->name);

        return response()->json(['status' => 'logged']);
    }
}
