<?php

namespace App\Services;

use App\Models\ExpiryTracker;

class ExpiryTrackerService
{
    /**
     * @param array<string, mixed> $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, ExpiryTracker>
     */
    public function list(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = ExpiryTracker::with('module.feature', 'user');

        if (!empty($filters['with_trashed'])) {
            $query->withTrashed();
        }

        if (isset($filters['module_id'])) {
            $query->where('module_id', $filters['module_id']);
        }
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('provider', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('notes', 'like', '%' . $filters['search'] . '%');
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['accessible_module_ids'])) {
            $query->whereIn('module_id', $filters['accessible_module_ids']);
        }
        if (isset($filters['expiring_soon'])) {
            $query->whereDate('expiry_date', '>=', now())
                  ->whereDate('expiry_date', '<=', now()->addDays(30));
        }
        if (isset($filters['expired'])) {
            $query->whereDate('expiry_date', '<', now());
        }
        if (isset($filters['date_from'])) {
            $query->whereDate('expiry_date', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('expiry_date', '<=', $filters['date_to']);
        }

        $sortBy = $filters['sort_by'] ?? 'expiry_date';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $allowedSort = ['name', 'provider', 'expiry_date', 'renewal_date', 'cost', 'status', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSort)) $sortBy = 'expiry_date';
        if (!in_array($sortOrder, ['asc', 'desc'])) $sortOrder = 'asc';

        return $query->orderBy($sortBy, $sortOrder)->paginate(min($filters['per_page'] ?? 20, 100));
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): ExpiryTracker
    {
        $entry = ExpiryTracker::create($data);
        return $entry->fresh()->load('module.feature', 'user');
    }

    /** @param array<string, mixed> $data */
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
}