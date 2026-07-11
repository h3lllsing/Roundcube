<?php

namespace App\Http\Controllers\Web;

use App\Helpers\ModuleCache;
use App\Helpers\RbacScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpiryTrackerRequest;
use App\Http\Requests\UpdateExpiryTrackerRequest;
use App\Models\ExpiryTracker;
use App\Services\ExpiryTrackerService;
use App\Services\RenewalNotificationService;
use App\Http\Controllers\Concerns\CleansPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpiryTrackerController extends Controller
{
    use CleansPasswords;

    public function __construct(
        private readonly ExpiryTrackerService $expiryTrackerService
    ) {}

    private function moduleSlug(): string
    {
        return 'expiry-trackers';
    }

    private function userOwnedFilter(): void
    {
        RbacScope::apply(ExpiryTracker::class, 'module');
    }

    public function index(Request $request): View
    {
        $this->userOwnedFilter();

        $filters = $request->only(['status', 'search', 'expiring_soon', 'expired', 'date_from', 'date_to', 'sync_type', 'source_type']);
        $filters['per_page'] = config('app.pagination_per_page');

        $trackers = $this->expiryTrackerService->list($filters);
        $totalCost = $this->expiryTrackerService->totalCost($filters);

        $trackers->loadMorph('trackable', [
            'hosting' => [],
            'vps' => [],
            'voip' => [],
            'other_service' => [],
            'domain' => [],
            'domain_email' => [],
            'service_provider' => [],
        ]);

        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super-admin');
        $slug = $this->moduleSlug();
        $module = ModuleCache::findBySlug($slug);
        $canCreate = $isSuperAdmin || ($module && $user->canOnModule($module, 'create'));
        $canExport = $isSuperAdmin;
        $canBulkDelete = $isSuperAdmin || ($module && $user->canOnModule($module, 'delete'));
        $canBulkRestore = $user->hasRole('super-admin');
        $canBulkForceDelete = $user->hasRole('super-admin');
        $bulkActions = ['update-status'];
        if ($canBulkDelete) $bulkActions[] = 'delete';
        if ($canBulkRestore) $bulkActions[] = 'restore';
        if ($canBulkForceDelete) $bulkActions[] = 'force-delete';

        $sourceTypes = $this->expiryTrackerService->getSourceTypes();

        return view('expiry-trackers.index', compact('trackers', 'totalCost', 'canCreate', 'canExport', 'canBulkDelete', 'canBulkRestore', 'canBulkForceDelete', 'bulkActions', 'sourceTypes'));
    }

    public function create(): View
    {
        $user = Auth::user();
        $module = ModuleCache::findBySlug($this->moduleSlug());
        abort_unless($user->hasRole('super-admin') || ($module && $user->canOnModule($module, 'create')), 403);

        $formData = $this->expiryTrackerService->getFormData();

        return view('expiry-trackers.create', $formData);
    }

    public function store(StoreExpiryTrackerRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $module = ModuleCache::findBySlug($this->moduleSlug());
        abort_unless($user->hasRole('super-admin') || ($module && $user->canOnModule($module, 'create')), 403);

        $validated = $request->validated();
        if ($module) {
            $validated['module_id'] = $module->id;
        }

        if (!empty($validated['notify_days'])) {
            $validated['notify_days_before'] = $validated['notify_days'];
        }
        unset($validated['notify_days']);

        $validated['user_id'] = Auth::id();

        $this->expiryTrackerService->create($validated);

        return redirect()->route('expiry-trackers.index')->with('success', 'Renewal created successfully.');
    }

    public function show(int $id, RenewalNotificationService $service): View
    {
        $this->userOwnedFilter();
        $tracker = ExpiryTracker::with(['module', 'user', 'smtpProfile', 'disabledByUser', 'trackable'])->findOrFail($id);

        $preview = $this->expiryTrackerService->getRecipientPreview($tracker, $service);

        return view('expiry-trackers.show', array_merge(compact('tracker'), $preview));
    }

    public function edit(int $id, RenewalNotificationService $service): View
    {
        $this->userOwnedFilter();
        $tracker = ExpiryTracker::with(['smtpProfile', 'notifications'])->findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($tracker->module && $user->canOnModule($tracker->module, 'update')), 403);

        $formData = $this->expiryTrackerService->getFormData();
        $preview = $this->expiryTrackerService->getRecipientPreview($tracker, $service);

        return view('expiry-trackers.edit', array_merge(compact('tracker'), $formData, $preview));
    }

    public function update(UpdateExpiryTrackerRequest $request, int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $tracker = ExpiryTracker::findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($tracker->module && $user->canOnModule($tracker->module, 'update')), 403);
        $this->checkOptimisticLock($tracker, $request);

        $validated = $request->validated();
        unset($validated['module_id']);

        $this->cleanPasswordField($validated);

        if (!empty($validated['notify_days'])) {
            $validated['notify_days_before'] = $validated['notify_days'];
        }
        unset($validated['notify_days']);

        if (array_key_exists('email_notifications_enabled', $validated)) {
            if (!empty($validated['email_notifications_enabled']) && !$tracker->email_notifications_enabled) {
                $validated['disabled_by'] = null;
                $validated['disabled_at'] = null;
                $validated['disable_reason'] = null;
            } elseif (empty($validated['email_notifications_enabled']) && $tracker->email_notifications_enabled) {
                $validated['disabled_by'] = Auth::id();
                $validated['disabled_at'] = now();
                $validated['disable_reason'] = $validated['disable_reason'] ?? 'Manual';
            }
        }

        $this->expiryTrackerService->update($tracker, $validated);

        return redirect()->route('expiry-trackers.index')->with('success', 'Renewal updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $tracker = ExpiryTracker::findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($tracker->module && $user->canOnModule($tracker->module, 'delete')), 403);

        $this->expiryTrackerService->delete($tracker);

        return redirect()->route('expiry-trackers.index')->with('success', 'Renewal deleted successfully.');
    }

    public function restore($id)
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $this->userOwnedFilter();
        $model = ExpiryTracker::withTrashed()->findOrFail($id);
        $model->restore();

        return redirect()->route('expiry-trackers.index')
            ->with('success', 'Renewal restored successfully.');
    }

    public function forceDelete($id)
    {
        abort_unless(Auth::user()->hasRole('super-admin'), 403);

        $this->userOwnedFilter();
        $model = ExpiryTracker::withTrashed()->findOrFail($id);
        $model->forceDelete();

        return redirect()->route('expiry-trackers.index')
            ->with('success', 'Renewal permanently deleted.');
    }

    public function previewEmail(int $id, RenewalNotificationService $service): JsonResponse
    {
        $this->userOwnedFilter();
        $tracker = ExpiryTracker::with(['user', 'module', 'serviceProvider', 'smtpProfile', 'trackable'])->findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($tracker->module && $user->canOnModule($tracker->module, 'update')), 403);

        $preview = $service->previewEmail($tracker);
        $preview['testRecipient'] = $user->email;

        return response()->json($preview);
    }

    public function testEmail(int $id, RenewalNotificationService $service): RedirectResponse
    {
        $this->userOwnedFilter();
        $tracker = ExpiryTracker::findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($tracker->module && $user->canOnModule($tracker->module, 'update')), 403);

        $service->sendTest($tracker, $user);

        return redirect()->back()->with('success', 'Test email sent to ' . $user->email);
    }

    public function sendReminderNow(int $id, RenewalNotificationService $service): RedirectResponse
    {
        $this->userOwnedFilter();
        $tracker = ExpiryTracker::findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($tracker->module && $user->canOnModule($tracker->module, 'update')), 403);

        $sent = $service->sendNow($tracker);

        return redirect()->back()->with('success', "{$sent} reminder(s) sent.");
    }

    public function renew(int $id): RedirectResponse
    {
        $this->userOwnedFilter();
        $tracker = ExpiryTracker::with('trackable')->findOrFail($id);

        $user = Auth::user();
        abort_unless($user->hasRole('super-admin') || ($tracker->module && $user->canOnModule($tracker->module, 'update')), 403);

        if ($tracker->status === 'cancelled') {
            return redirect()->route('expiry-trackers.index')->with('error', 'Cannot renew a cancelled item.');
        }

        $this->expiryTrackerService->processRenew($tracker, $user);

        return redirect()->route('expiry-trackers.index')->with('success', 'Renewal processed successfully.');
    }

    public function notificationHistory(int $id, Request $request): View
    {
        $this->userOwnedFilter();
        $tracker = ExpiryTracker::findOrFail($id);

        $notifications = $this->expiryTrackerService->getNotificationHistory($id);

        return view('expiry-trackers.notifications', compact('tracker', 'notifications'));
    }
}
