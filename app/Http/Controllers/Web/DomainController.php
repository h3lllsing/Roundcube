<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\StoreDomainRequest;
use App\Http\Requests\UpdateDomainRequest;
use App\Models\Domain;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\ServiceProvider;
use App\Services\RenewalSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DomainController extends BaseResourceController
{
    protected function modelClass(): string
    {
        return Domain::class;
    }

    protected function moduleSlug(): string
    {
        return 'domains';
    }

    protected function viewPrefix(): string
    {
        return 'domains';
    }

    protected function indexSelect(): array
    {
        return ['id', 'module_id', 'name', 'expiry_date', 'cost', 'status', 'cloudflare_status', 'hosting_id', 'service_provider_id'];
    }

    protected function indexVariable(): string
    {
        return 'domains';
    }

    protected function recordVariable(): string
    {
        return 'domain';
    }

    protected function indexWith(): array
    {
        return ['module', 'hosting', 'serviceProvider'];
    }

    protected function showWith(): array
    {
        return ['module', 'hosting', 'serviceProvider', 'user', 'domainEmails'];
    }

    protected function showExtraData($model): array
    {
        return [
            'renewals' => \App\Models\ExpiryTracker::where('trackable_type', Domain::class)
                ->where('trackable_id', $model->id)
                ->get(),
        ];
    }

    protected function prepareStoreData(array $validated, Request $request): array
    {
        $validated['dns_servers'] = $request->filled('dns_servers') ? array_map('trim', explode(',', $request->dns_servers)) : [];

        return $validated;
    }

    protected function prepareUpdateData(array $validated, Request $request, $model): array
    {
        if ($request->has('dns_servers')) {
            $validated['dns_servers'] = $request->filled('dns_servers') ? array_map('trim', explode(',', $request->dns_servers)) : [];
        }

        return $validated;
    }

    protected function createExtraData(): array
    {
        $user = Auth::user();

        return [
            'hostings' => $user->hasRole('super-admin')
                ? Hosting::orderBy('name')->pluck('name', 'id')
                : Hosting::where('user_id', Auth::id())->orderBy('name')->pluck('name', 'id'),
            'serviceProviders' => ServiceProvider::orderBy('name')->pluck('name', 'id'),
        ];
    }

    public function store(StoreDomainRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = Auth::user();
        $module = $this->resolveModule();
        if (! $user->hasRole('super-admin')) {
            abort_unless($module && $user->canOnModule($module, 'create'), 403);
        }
        if ($module) {
            $validated['module_id'] = $module->id;
        }
        $validated['user_id'] = Auth::id();
        $validated = $this->prepareStoreData($validated, $request);
        $domain = DB::transaction(function () use ($validated) {
            $domain = Domain::create($validated);
            app(RenewalSyncService::class)->sync($domain);
            return $domain;
        });

        return redirect()->route('domains.index')->with('success', 'Domain created successfully.');
    }

    public function update(UpdateDomainRequest $request, int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $domain = Domain::findOrFail($id);
        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($domain->module && $user->canOnModule($domain->module, 'update')), 403);
        $this->checkOptimisticLock($domain, $request);
        $validated = $request->validated();
        unset($validated['module_id']);
        $validated = $this->prepareUpdateData($validated, $request, $domain);
        DB::transaction(function () use ($domain, $validated) {
            $domain->update($validated);
            if ($domain->wasChanged($this->renewalFields())) {
                app(RenewalSyncService::class)->sync($domain);
            }
        });

        return redirect()->route('domains.index')->with('success', 'Domain updated successfully.');
    }
}
