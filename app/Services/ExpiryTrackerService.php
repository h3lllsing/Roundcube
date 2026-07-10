<?php

namespace App\Services;

use App\Models\ExpiryTracker;
use App\Models\ExpiryTrackerNotification;
use App\Models\Module;
use App\Models\ServiceProvider;
use App\Models\SmtpProfile;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ExpiryTrackerService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = $this->buildFilteredQuery($filters);

        $sortBy = $filters['sort_by'] ?? 'expiry_date';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $allowedSort = ['name', 'expiry_date', 'renewal_date', 'cost', 'status', 'created_at', 'updated_at'];
        if (! in_array($sortBy, $allowedSort)) {
            $sortBy = 'expiry_date';
        }
        if (! in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        return $query->with('module.feature', 'serviceProvider', 'user', 'trackable')
            ->orderBy($sortBy, $sortOrder)
            ->paginate(min($filters['per_page'] ?? 20, 100));
    }

    public function totalCost(array $filters = []): float
    {
        return (float) $this->buildFilteredQuery($filters)->sum('cost');
    }

    private function buildFilteredQuery(array $filters)
    {
        $query = ExpiryTracker::query();

        if (! empty($filters['with_trashed'])) {
            $query->withTrashed();
        }

        if (isset($filters['module_id'])) {
            $query->where('module_id', $filters['module_id']);
        }
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (! empty($filters['accessible_module_ids'])) {
            $query->whereIn('module_id', $filters['accessible_module_ids']);
        }
        if (! empty($filters['expiring_soon'])) {
            $query->whereBetween('expiry_date', [now(), now()->addDays(30)]);
        }
        if (! empty($filters['expired'])) {
            $query->where('expiry_date', '<', now())->where('status', '!=', 'expired');
        }
        if (isset($filters['date_from'])) {
            $query->whereDate('expiry_date', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('expiry_date', '<=', $filters['date_to']);
        }
        $syncType = $filters['sync_type'] ?? null;
        if ($syncType === 'linked') {
            $query->whereNotNull('trackable_type');
        } elseif ($syncType === 'standalone') {
            $query->whereNull('trackable_type');
        }
        if (! empty($filters['source_type'])) {
            $query->where('trackable_type', $filters['source_type']);
        }

        return $query;
    }

    public function create(array $data): ExpiryTracker
    {
        $entry = ExpiryTracker::create($data);

        return $entry->fresh()->load('module.feature', 'user');
    }

    public function update(ExpiryTracker $entry, array $data): ExpiryTracker
    {
        $entry->update($data);
        $entry->refresh();
        $entry->load('module.feature', 'user');

        return $entry;
    }

    public function delete(ExpiryTracker $entry): void
    {
        $entry->delete();
    }

    public function getFormData(): array
    {
        return [
            'modules' => Module::orderBy('name')->pluck('name', 'id'),
            'users' => User::orderBy('name')->pluck('name', 'id'),
            'serviceProviders' => ServiceProvider::orderBy('name')->pluck('name', 'id'),
            'smtpProfiles' => SmtpProfile::where('is_active', true)->orderBy('priority')->orderBy('name')->get()
                ->mapWithKeys(fn ($p) => [$p->id => $p->name . ' — ' . $p->sender_name . ' <' . $p->sender_email . '>']),
        ];
    }

    public function getRecipientPreview(ExpiryTracker $tracker, RenewalNotificationService $service): array
    {
        $recipientPreview = [];
        $senderEmail = config('mail.from.address');
        $senderName = config('mail.from.name');
        $userLookup = [];

        if ($tracker->email_notifications_enabled) {
            $recipientPreview = $service->getRecipients($tracker);
            $emails = array_column($recipientPreview, 'email');
            if (!empty($emails)) {
                $userLookup = User::whereIn('email', $emails)->pluck('name', 'email')->toArray();
            }
            $profile = $tracker->smtpProfile;
            if ($profile) {
                $senderEmail = $profile->sender_email;
                $senderName = $profile->sender_name;
            }
        }

        return compact('recipientPreview', 'senderEmail', 'senderName', 'userLookup');
    }

    public function processRenew(ExpiryTracker $tracker, User $user): void
    {
        $period = $tracker->billing_period_months ?? 12;
        $newExpiry = $tracker->expiry_date ? $tracker->expiry_date->copy()->addMonths($period) : now()->addMonths($period);
        $renewalDate = now();

        if ($tracker->trackable) {
            $tracker->trackable->forceFill([
                'expiry_date' => $newExpiry,
                'billing_period_months' => $period,
            ])->save();
        }

        $tracker->update([
            'expiry_date' => $newExpiry,
            'renewal_date' => $renewalDate,
        ]);

        $tracker->notes()->create([
            'content' => "Renewal processed: expiry extended to {$newExpiry->format('Y-m-d')} by {$user->name}",
            'user_id' => $user->id,
        ]);

        activity()
            ->event('renewal_processed')
            ->performedOn($tracker)
            ->causedBy($user)
            ->withProperties([
                'new_expiry_date' => $newExpiry->format('Y-m-d'),
                'renewal_date' => $renewalDate->format('Y-m-d'),
                'trackable_type' => $tracker->trackable_type,
                'trackable_id' => $tracker->trackable_id,
            ])
            ->log('Renewal processed for: '.$tracker->name);
    }

    public function getNotificationHistory(int $trackerId): LengthAwarePaginator
    {
        return ExpiryTrackerNotification::with('smtpProfile')
            ->where('expiry_tracker_id', $trackerId)
            ->orderBy('created_at', 'desc')
            ->paginate(config('app.pagination_per_page'));
    }

    public function getSourceTypes(): array
    {
        return [
            'domain' => 'Domains',
            'hosting' => 'Hosting',
            'vps' => 'VPS',
            'voip' => 'VOIP',
            'domain_email' => 'Domain Emails',
            'other_service' => 'Other Services',
            'service_provider' => 'Service Providers',
        ];
    }
}
