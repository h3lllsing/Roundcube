<?php

namespace App\Http\Controllers\Web;

use App\Helpers\ModuleCache;
use App\Helpers\RbacScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDomainEmailRequest;
use App\Http\Requests\UpdateDomainEmailRequest;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\Module;
use App\Models\ServiceProvider;
use App\Services\RenewalSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Concerns\CleansPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DomainEmailController extends Controller
{
    use CleansPasswords;
    private function moduleSlug(): string
    {
        return 'domain-emails';
    }

    private function userOwnedFilter(): void
    {
        RbacScope::apply(DomainEmail::class, 'module');
    }

    public function index(Request $request): View
    {
        $user = Auth::user();
        $module = ModuleCache::findBySlug($this->moduleSlug());
        abort_unless($user->hasRole('super-admin') || ($module && $user->canOnModule($module, 'read')), 403);

        $this->userOwnedFilter();
        $query = DomainEmail::with(['domain', 'module', 'serviceProvider']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', '%'.$search.'%')
                  ->orWhereHas('domain', fn ($d) => $d->where('name', 'like', '%'.$search.'%'));
            });
        }

        $emails = $query->select(['id', 'module_id', 'email', 'domain_id', 'status', 'service_provider_id', 'password'])->latest()->paginate(20);

        $isSuperAdmin = $user->hasRole('super-admin');
        $canCreate = $isSuperAdmin || ($module && $user->canOnModule($module, 'create'));
        $canExport = $isSuperAdmin || ($module && $user->canOnModule($module, 'export'));

        $domains = Domain::orderBy('name')->pluck('name', 'id');
        $vaultModule = \App\Helpers\ModuleCache::findBySlug('vault');

        return view('domain-emails.index', compact('emails', 'canCreate', 'canExport', 'domains', 'vaultModule'));
    }

    public function create(): View
    {
        $user = Auth::user();
        if (! $user->hasRole('super-admin')) {
            $module = ModuleCache::findBySlug($this->moduleSlug());
            abort_unless($module && $user->canOnModule($module, 'create'), 403);
        }

        $domains = Domain::orderBy('name')->pluck('name', 'id');
        $serviceProviders = ServiceProvider::orderBy('name')->pluck('name', 'id');

        return view('domain-emails.create', compact('domains', 'serviceProviders'));
    }

    public function store(StoreDomainEmailRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = Auth::user();
        $module = ModuleCache::findBySlug($this->moduleSlug());
        if (! $user->hasRole('super-admin')) {
            abort_unless($module && $user->canOnModule($module, 'create'), 403);
        }
        if ($module) {
            $validated['module_id'] = $module->id;
        }

        $validated['user_id'] = Auth::id();
        $domainEmail = DomainEmail::create($validated);

        app(RenewalSyncService::class)->sync($domainEmail);

        return redirect()->route('domain-emails.index')->with('success', 'Email credential created successfully.');
    }

    public function show(int $id): View
    {
        $this->userOwnedFilter();
        $email = DomainEmail::with(['domain', 'module', 'user'])->findOrFail($id);
        $vaultModule = \App\Helpers\ModuleCache::findBySlug('vault');

        return view('domain-emails.show', compact('email', 'vaultModule'));
    }

    public function edit(int $id): View
    {
        $this->userOwnedFilter();
        $email = DomainEmail::with('module')->findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($email->module && $user->canOnModule($email->module, 'update')), 403);

        $domains = Domain::orderBy('name')->pluck('name', 'id');
        $serviceProviders = ServiceProvider::orderBy('name')->pluck('name', 'id');

        return view('domain-emails.edit', compact('email', 'domains', 'serviceProviders'));
    }

    public function update(UpdateDomainEmailRequest $request, int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $email = DomainEmail::findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($email->module && $user->canOnModule($email->module, 'update')), 403);
        $this->checkOptimisticLock($email, $request);

        $data = $request->validated();
        unset($data['module_id']);
        $this->cleanPasswordField($data);

        $email->update($data);

        if ($email->wasChanged(['expiry_date', 'email', 'service_provider_id', 'user_id', 'module_id'])) {
            app(RenewalSyncService::class)->sync($email);
        }

        return redirect()->route('domain-emails.index')->with('success', 'Email credential updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $email = DomainEmail::with('module')->findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin'), 403);

        $email->delete();

        app(RenewalSyncService::class)->remove($email);

        return redirect()->route('domain-emails.index')->with('success', 'Email credential deleted successfully.');
    }

    public function getPassword(int $id): JsonResponse
    {
        $user = Auth::user();
        $emailModule = \App\Helpers\ModuleCache::findBySlug($this->moduleSlug());
        abort_unless($user->hasRole('super-admin') || ($emailModule && $user->canOnModule($emailModule, 'read')), 403);
        $this->userOwnedFilter();
        $email = DomainEmail::findOrFail($id);

        abort_unless($user->canRevealCredentialsFor($emailModule), 403);

        activity()->event('revealed')
            ->performedOn($email)
            ->causedBy($user)
            ->withProperties(['type' => 'domain_email_password'])
            ->log('Password revealed for Domain Email: '.$email->email);

        return response()->json(['password' => $email->password]);
    }

    public function restore($id)
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $this->userOwnedFilter();
        $model = DomainEmail::withTrashed()->findOrFail($id);
        $model->restore();

        app(RenewalSyncService::class)->restore($model);

        return redirect()->route('domain-emails.index')
            ->with('success', __('Domain Email restored successfully.'));
    }

    public function forceDelete($id)
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $this->userOwnedFilter();
        $model = DomainEmail::withTrashed()->findOrFail($id);
        $model->forceDelete();

        return redirect()->route('domain-emails.index')
            ->with('success', __('Domain Email permanently deleted.'));
    }
}
