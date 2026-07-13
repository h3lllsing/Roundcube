<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\StoreHostingRequest;
use App\Http\Requests\UpdateHostingRequest;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\ServiceProvider;
use App\Services\RenewalSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Concerns\CleansPasswords;
use Illuminate\View\View;

class HostingController extends BaseResourceController
{
    use CleansPasswords;
    protected function modelClass(): string
    {
        return Hosting::class;
    }

    protected function moduleSlug(): string
    {
        return 'hostings';
    }

    protected function viewPrefix(): string
    {
        return 'hostings';
    }

    protected function indexSelect(): array
    {
        return ['id', 'module_id', 'name', 'username', 'cpanel_url', 'expiry_date', 'status', 'domain_ip', 'mail_domain_ip', 'cpanel_ip', 'service_provider_id'];
    }

    protected function indexVariable(): string
    {
        return 'hostings';
    }

    protected function recordVariable(): string
    {
        return 'hosting';
    }

    protected function indexWith(): array
    {
        return ['module', 'serviceProvider'];
    }

    protected function showWith(): array
    {
        return ['module', 'user', 'domains', 'domains.domainEmails', 'serviceProvider'];
    }

    protected function showExtraData($model): array
    {
        return [
            'vaultModule' => \App\Helpers\ModuleCache::findBySlug('vault'),
            'renewals' => \App\Models\ExpiryTracker::where('trackable_type', Hosting::class)
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

    protected function prepareUpdateData(array $validated, Request $request, $model): array
    {
        $this->cleanPasswordField($validated);

        return $validated;
    }

    public function store(StoreHostingRequest $request): RedirectResponse
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
        $hosting = Hosting::create($validated);
        app(RenewalSyncService::class)->sync($hosting);

        return redirect()->route('hostings.index')->with('success', 'Hosting created successfully.');
    }

    public function update(UpdateHostingRequest $request, int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $hosting = Hosting::findOrFail($id);
        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($hosting->module && $user->canOnModule($hosting->module, 'update')), 403);
        $this->checkOptimisticLock($hosting, $request);
        $data = $request->validated();
        unset($data['module_id']);
        $data = $this->prepareUpdateData($data, $request, $hosting);
        $hosting->update($data);
        if ($hosting->wasChanged($this->renewalFields())) {
            app(RenewalSyncService::class)->sync($hosting);
        }

        return redirect()->route('hostings.index')->with('success', 'Hosting updated successfully.');
    }

    public function getPassword(int $id): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $hostingModule = \App\Helpers\ModuleCache::findBySlug($this->moduleSlug());
        abort_unless($user->hasRole('super-admin') || ($hostingModule && $user->canOnModule($hostingModule, 'read')), 403);
        $this->userOwnedFilter();
        $hosting = Hosting::findOrFail($id);
        $vaultModule = \App\Helpers\ModuleCache::findBySlug('vault');
        abort_unless($user->hasRole('super-admin') || ($vaultModule && $user->canOnModule($vaultModule, 'reveal')), 403);
        activity()->event('revealed')
            ->performedOn($hosting)
            ->causedBy($user)
            ->withProperties(['type' => 'hosting_password'])
            ->log('Password revealed for Hosting: '.$hosting->name);

        return response()->json(['password' => $hosting->password]);
    }

    public function logPasswordCopy(int $id): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $hostingModule = \App\Helpers\ModuleCache::findBySlug($this->moduleSlug());
        abort_unless($user->hasRole('super-admin') || ($hostingModule && $user->canOnModule($hostingModule, 'read')), 403);
        $this->userOwnedFilter();
        $hosting = Hosting::findOrFail($id);
        $vaultModule = \App\Helpers\ModuleCache::findBySlug('vault');
        abort_unless($user->hasRole('super-admin') || ($vaultModule && $user->canOnModule($vaultModule, 'reveal')), 403);
        activity()->event('copied')
            ->performedOn($hosting)
            ->causedBy($user)
            ->withProperties(['type' => 'hosting_password'])
            ->log('Password copied for Hosting: '.$hosting->name);

        return response()->json(['status' => 'logged']);
    }
}
