<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\StoreGMailRequest;
use App\Http\Requests\UpdateGMailRequest;
use App\Models\GMail;
use App\Models\Module;
use App\Helpers\ModuleCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GMailController extends BaseResourceController
{
    protected function modelClass(): string
    {
        return GMail::class;
    }

    protected function moduleSlug(): string
    {
        return 'g-mails';
    }

    protected function viewPrefix(): string
    {
        return 'g-mails';
    }

    protected function indexSelect(): array
    {
        return ['id', 'module_id', 'status', 'user_name', 'pseudo', 'emails_address', 'password', 'department', 'assigned'];
    }

    protected function indexVariable(): string
    {
        return 'gMails';
    }

    protected function recordVariable(): string
    {
        return 'gMail';
    }

    protected function resourceName(): string
    {
        return 'G-Mail';
    }

    protected function applyIndexFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('user_name', 'like', '%'.$request->search.'%')
                  ->orWhere('pseudo', 'like', '%'.$request->search.'%')
                  ->orWhere('emails_address', 'like', '%'.$request->search.'%')
                  ->orWhere('department', 'like', '%'.$request->search.'%');
            });
        }
    }

    public function store(StoreGMailRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = Auth::user();
        $module = ModuleCache::findBySlug($this->moduleSlug());
        if (!$user->hasRole('super-admin')) {
            abort_unless($module && $user->canOnModule($module, 'create'), 403);
        }
        if ($module) {
            $validated['module_id'] = $module->id;
        }
        $validated['user_id'] = Auth::id();
        GMail::create($validated);

        return redirect()->route('g-mails.index')->with('success', 'G-Mail created successfully.');
    }

    public function update(UpdateGMailRequest $request, int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $gMail = GMail::findOrFail($id);
        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($gMail->module && $user->canOnModule($gMail->module, 'update')), 403);
        $this->checkOptimisticLock($gMail, $request);
        $data = $request->validated();
        unset($data['module_id']);
        $gMail->update($data);

        return redirect()->route('g-mails.index')->with('success', 'G-Mail updated successfully.');
    }

    public function show(int $id): View
    {
        $this->userOwnedFilter();
        $gMail = GMail::with(['module', 'user'])->findOrFail($id);
        $vaultModule = ModuleCache::findBySlug('vault');

        return view('g-mails.show', compact('gMail', 'vaultModule'));
    }

    public function getPassword(int $id): JsonResponse
    {
        $user = Auth::user();
        $gMailModule = ModuleCache::findBySlug($this->moduleSlug());
        abort_unless($user->hasRole('super-admin') || ($gMailModule && $user->canOnModule($gMailModule, 'read')), 403);
        $this->userOwnedFilter();
        $gMail = GMail::findOrFail($id);

        $vaultModule = ModuleCache::findBySlug('vault');
        abort_unless($user->hasRole('super-admin') || ($vaultModule && $user->canOnModule($vaultModule, 'reveal')), 403);

        activity()->event('revealed')
            ->performedOn($gMail)
            ->causedBy($user)
            ->withProperties(['type' => 'g_mail_password'])
            ->log('Password revealed for G-Mail: '.$gMail->emails_address);

        return response()->json(['password' => $gMail->password]);
    }
}
