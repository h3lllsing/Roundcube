<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\StoreVoipRequest;
use App\Http\Requests\UpdateVoipRequest;
use App\Models\Module;
use App\Models\ServiceProvider;
use App\Models\Voip;
use App\Services\RenewalSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Concerns\CleansPasswords;
use Illuminate\View\View;

class VoipController extends BaseResourceController
{
    use CleansPasswords;
    protected function modelClass(): string
    {
        return Voip::class;
    }

    protected function moduleSlug(): string
    {
        return 'voip';
    }

    protected function viewPrefix(): string
    {
        return 'voip';
    }

    protected function indexSelect(): array
    {
        return ['id', 'module_id', 'extensions', 'name', 'phone_number', 'server_ip', 'direction', 'number_status', 'outbound_code', 'cost', 'team_details', 'service_provider_id'];
    }

    protected function indexVariable(): string
    {
        return 'voipList';
    }

    protected function recordVariable(): string
    {
        return 'voip';
    }

    protected function indexWith(): array
    {
        return ['module', 'serviceProvider'];
    }

    protected function showWith(): array
    {
        return ['module', 'user', 'serviceProvider'];
    }

    protected function showExtraData($model): array
    {
        return [
            'vaultModule' => \App\Helpers\ModuleCache::findBySlug('vault'),
            'renewals' => \App\Models\ExpiryTracker::where('trackable_type', Voip::class)
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
        $validated['extensions'] = ! empty($validated['extension']) ? [$validated['extension']] : [];
        unset($validated['extension']);

        return $validated;
    }

    protected function prepareUpdateData(array $validated, Request $request, $model): array
    {
        $this->cleanPasswordField($validated);
        $this->cleanPasswordField($validated, 'extension_password');
        if (array_key_exists('extension', $validated)) {
            $validated['extensions'] = ! empty($validated['extension']) ? [$validated['extension']] : [];
            unset($validated['extension']);
        }

        return $validated;
    }

    public function store(StoreVoipRequest $request): RedirectResponse
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
        $voip = Voip::create($validated);
        app(RenewalSyncService::class)->sync($voip);

        return redirect()->route('voip.index')->with('success', 'VoIP created successfully.');
    }

    public function update(UpdateVoipRequest $request, int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $voip = Voip::findOrFail($id);
        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($voip->module && $user->canOnModule($voip->module, 'update')), 403);
        $this->checkOptimisticLock($voip, $request);
        $data = $request->validated();
        unset($data['module_id']);
        $data = $this->prepareUpdateData($data, $request, $voip);
        $voip->update($data);
        if ($voip->wasChanged($this->renewalFields())) {
            app(RenewalSyncService::class)->sync($voip);
        }

        return redirect()->route('voip.index')->with('success', 'VoIP updated successfully.');
    }

    public function getPassword(int $id): JsonResponse
    {
        $this->userOwnedFilter();
        $voip = Voip::findOrFail($id);
        $user = Auth::user();
        $vaultModule = \App\Helpers\ModuleCache::findBySlug('vault');
        abort_unless($user->hasRole('super-admin') || ($vaultModule && $user->canOnModule($vaultModule, 'reveal')), 403);
        activity()->event('revealed')
            ->performedOn($voip)
            ->causedBy($user)
            ->withProperties(['type' => 'voip_password'])
            ->log('Password revealed for VoIP: '.$voip->name);

        return response()->json(['password' => $voip->password]);
    }

    public function getExtensionPassword(int $id): JsonResponse
    {
        $this->userOwnedFilter();
        $voip = Voip::findOrFail($id);
        $user = Auth::user();
        $vaultModule = \App\Helpers\ModuleCache::findBySlug('vault');
        abort_unless($user->hasRole('super-admin') || ($vaultModule && $user->canOnModule($vaultModule, 'reveal')), 403);
        activity()->event('revealed')
            ->performedOn($voip)
            ->causedBy($user)
            ->withProperties(['type' => 'voip_extension_password'])
            ->log('Extension password revealed for VoIP: '.$voip->name);

        return response()->json(['extension_password' => $voip->extension_password]);
    }

    public function logPasswordCopy(int $id): JsonResponse
    {
        $this->userOwnedFilter();
        $voip = Voip::findOrFail($id);
        $user = Auth::user();
        $vaultModule = \App\Helpers\ModuleCache::findBySlug('vault');
        abort_unless($user->hasRole('super-admin') || ($vaultModule && $user->canOnModule($vaultModule, 'reveal')), 403);
        activity()->event('copied')
            ->performedOn($voip)
            ->causedBy($user)
            ->withProperties(['type' => 'voip_password'])
            ->log('Password copied for VoIP: '.$voip->name);

        return response()->json(['status' => 'logged']);
    }

    public function logExtensionPasswordCopy(int $id): JsonResponse
    {
        $this->userOwnedFilter();
        $voip = Voip::findOrFail($id);
        $user = Auth::user();
        $vaultModule = \App\Helpers\ModuleCache::findBySlug('vault');
        abort_unless($user->hasRole('super-admin') || ($vaultModule && $user->canOnModule($vaultModule, 'reveal')), 403);
        activity()->event('copied')
            ->performedOn($voip)
            ->causedBy($user)
            ->withProperties(['type' => 'voip_extension_password'])
            ->log('Extension password copied for VoIP: '.$voip->name);

        return response()->json(['status' => 'logged']);
    }
}
