<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\StoreVpsRequest;
use App\Http\Requests\UpdateVpsRequest;
use App\Models\Module;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Vps;
use App\Services\RenewalSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Concerns\CleansPasswords;
use Illuminate\View\View;

class VpsController extends BaseResourceController
{
    use CleansPasswords;
    protected function modelClass(): string
    {
        return Vps::class;
    }

    protected function moduleSlug(): string
    {
        return 'vps';
    }

    protected function viewPrefix(): string
    {
        return 'vps';
    }

    protected function indexSelect(): array
    {
        return ['id', 'module_id', 'name', 'ip_address', 'plan', 'cost', 'expiry_date', 'status', 'department', 'location', 'user_id', 'service_provider_id', 'password'];
    }

    protected function indexVariable(): string
    {
        return 'vpsList';
    }

    protected function recordVariable(): string
    {
        return 'vps';
    }

    protected function indexWith(): array
    {
        return ['module', 'serviceProvider', 'user'];
    }

    protected function showWith(): array
    {
        return ['module', 'serviceProvider', 'user'];
    }

    protected function showExtraData($model): array
    {
        return [
            'vaultModule' => \App\Helpers\ModuleCache::findBySlug('vault'),
            'renewals' => \App\Models\ExpiryTracker::where('trackable_type', Vps::class)
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

    protected function prepareStoreData(array $validated, Request $request): array
    {
        foreach (['login_ids', 'additional_ips'] as $field) {
            if (isset($validated[$field]) && is_string($validated[$field])) {
                $validated[$field] = json_decode($validated[$field], true);
            }
        }

        return $validated;
    }

    protected function prepareUpdateData(array $validated, Request $request, $model): array
    {
        $this->cleanPasswordField($validated);
        foreach (['login_ids', 'additional_ips'] as $field) {
            if (isset($validated[$field]) && is_string($validated[$field])) {
                $validated[$field] = json_decode($validated[$field], true);
            }
        }

        return $validated;
    }

    public function store(StoreVpsRequest $request): RedirectResponse
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
        $vps = Vps::create($validated);
        app(RenewalSyncService::class)->sync($vps);

        return redirect()->route('vps.index')->with('success', 'VPS created successfully.');
    }

    public function update(UpdateVpsRequest $request, int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $vps = Vps::findOrFail($id);
        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($vps->module && $user->canOnModule($vps->module, 'update')), 403);
        $this->checkOptimisticLock($vps, $request);
        $data = $request->validated();
        unset($data['module_id']);
        $data = $this->prepareUpdateData($data, $request, $vps);
        $vps->update($data);
        if ($vps->wasChanged($this->renewalFields())) {
            app(RenewalSyncService::class)->sync($vps);
        }

        return redirect()->route('vps.index')->with('success', 'VPS updated successfully.');
    }

    public function getPassword(int $id): JsonResponse
    {
        $user = Auth::user();
        $vpsModule = \App\Helpers\ModuleCache::findBySlug($this->moduleSlug());
        abort_unless($user->hasRole('super-admin') || ($vpsModule && $user->canOnModule($vpsModule, 'read')), 403);
        $this->userOwnedFilter();
        $vps = Vps::findOrFail($id);
        abort_unless($user->canRevealCredentialsFor($vpsModule), 403);
        activity()->event('revealed')
            ->performedOn($vps)
            ->causedBy($user)
            ->withProperties(['type' => 'vps_password'])
            ->log('Password revealed for VPS: '.$vps->name);

        return response()->json(['password' => $vps->password]);
    }

    public function logPasswordCopy(int $id): JsonResponse
    {
        $user = Auth::user();
        $vpsModule = \App\Helpers\ModuleCache::findBySlug($this->moduleSlug());
        abort_unless($user->hasRole('super-admin') || ($vpsModule && $user->canOnModule($vpsModule, 'read')), 403);
        $this->userOwnedFilter();
        $vps = Vps::findOrFail($id);
        abort_unless($user->canRevealCredentialsFor($vpsModule), 403);
        activity()->event('copied')
            ->performedOn($vps)
            ->causedBy($user)
            ->withProperties(['type' => 'vps_password'])
            ->log('Password copied for VPS: '.$vps->name);

        return response()->json(['status' => 'logged']);
    }
}
