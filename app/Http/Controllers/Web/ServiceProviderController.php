<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\StoreServiceProviderRequest;
use App\Http\Requests\UpdateServiceProviderRequest;
use App\Models\Module;
use App\Models\ServiceProvider;
use App\Services\RenewalSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Concerns\CleansPasswords;
use Illuminate\View\View;

class ServiceProviderController extends BaseResourceController
{
    use CleansPasswords;
    protected function modelClass(): string
    {
        return ServiceProvider::class;
    }

    protected function moduleSlug(): string
    {
        return 'service-providers';
    }

    protected function viewPrefix(): string
    {
        return 'service-providers';
    }

    protected function indexSelect(): array
    {
        return ['id', 'module_id', 'name', 'type', 'provider', 'email', 'website', 'login_id', 'password', 'status', 'description', 'expiry_date'];
    }

    protected function indexVariable(): string
    {
        return 'providers';
    }

    protected function recordVariable(): string
    {
        return 'provider';
    }

    protected function indexWith(): array
    {
        return ['module'];
    }

    protected function showWith(): array
    {
        return ['module', 'user'];
    }

    protected function showExtraData($model): array
    {
        $model->load(['hostings', 'domains', 'vps', 'voip', 'otherServices']);
        $model->loadCount(['hostings', 'domains', 'vps', 'voip', 'domainEmails', 'otherServices', 'expiryTrackers']);

        return [
            'vaultModule' => \App\Helpers\ModuleCache::findBySlug('vault'),
        ];
    }

    protected function prepareStoreData(array $validated, Request $request): array
    {
        $validated['type'] = $validated['type'] ?? 'other';

        return $validated;
    }

    protected function prepareUpdateData(array $validated, Request $request, $model): array
    {
        $this->cleanPasswordField($validated);

        return $validated;
    }

    protected function renewalFields(): array
    {
        return ['expiry_date', 'name', 'user_id', 'module_id'];
    }

    public function store(StoreServiceProviderRequest $request): RedirectResponse
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
        $validated = $this->prepareStoreData($validated, $request);
        $provider = ServiceProvider::create($validated);
        app(RenewalSyncService::class)->sync($provider);

        return redirect()->route('service-providers.index')->with('success', 'Service provider created successfully.');
    }

    public function update(UpdateServiceProviderRequest $request, int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $provider = ServiceProvider::findOrFail($id);
        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($provider->module && $user->canOnModule($provider->module, 'update')), 403);
        $this->checkOptimisticLock($provider, $request);
        $data = $request->validated();
        unset($data['module_id']);
        $data = $this->prepareUpdateData($data, $request, $provider);
        $provider->update($data);
        if ($provider->wasChanged($this->renewalFields())) {
            app(RenewalSyncService::class)->sync($provider);
        }

        return redirect()->route('service-providers.index')->with('success', 'Service provider updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $provider = ServiceProvider::withCount([
            'hostings', 'domains', 'vps', 'voip', 'domainEmails', 'otherServices', 'expiryTrackers',
        ])->findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($provider->module && $user->canOnModule($provider->module, 'delete')), 403);

        $dependentCount = $provider->hostings_count
            + $provider->domains_count
            + $provider->vps_count
            + $provider->voip_count
            + $provider->domain_emails_count
            + $provider->other_services_count
            + $provider->expiry_trackers_count;

        if ($dependentCount > 0) {
            return redirect()->route('service-providers.index')
                ->with('error', 'Cannot delete "'.$provider->name.'" — it has '.$dependentCount.' dependent service(s). Reassign them first.');
        }

        $provider->delete();
        app(RenewalSyncService::class)->remove($provider);

        return redirect()->route('service-providers.index')->with('success', 'Service provider deleted successfully.');
    }

    public function getPassword(int $id): JsonResponse
    {
        $user = Auth::user();
        $providerModule = \App\Helpers\ModuleCache::findBySlug($this->moduleSlug());
        abort_unless($user->hasRole('super-admin') || ($providerModule && $user->canOnModule($providerModule, 'read')), 403);
        $this->userOwnedFilter();
        $provider = ServiceProvider::findOrFail($id);
        $vaultModule = \App\Helpers\ModuleCache::findBySlug('vault');
        abort_unless($user->hasRole('super-admin') || ($vaultModule && $user->canOnModule($vaultModule, 'reveal')), 403);
        activity()->event('revealed')
            ->performedOn($provider)
            ->causedBy($user)
            ->withProperties(['type' => 'service_provider_password'])
            ->log('Password revealed for Service Provider: '.$provider->name);

        return response()->json(['password' => $provider->password]);
    }
}
