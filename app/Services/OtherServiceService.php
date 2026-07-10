<?php

namespace App\Services;

use App\Models\OtherService;
use Illuminate\Pagination\LengthAwarePaginator;

class OtherServiceService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, OtherService>
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = OtherService::with('module.feature', 'user');
        if (! empty($filters['with_trashed'])) {
            $query->withTrashed();
        }
        if (isset($filters['module_id'])) {
            $query->where('module_id', $filters['module_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('service_type', 'like', '%'.$filters['search'].'%');
            });
        }
        if (! empty($filters['accessible_module_ids'])) {
            $query->whereIn('module_id', $filters['accessible_module_ids']);
        }
        $sortBy = in_array($filters['sort_by'] ?? 'expiry_date', ['name', 'service_type', 'cost', 'status', 'created_at', 'expiry_date']) ? ($filters['sort_by'] ?? 'expiry_date') : 'expiry_date';
        $sortOrder = in_array($filters['sort_order'] ?? 'asc', ['asc', 'desc']) ? ($filters['sort_order'] ?? 'asc') : 'asc';

        return $query->orderBy($sortBy, $sortOrder)->paginate(min($filters['per_page'] ?? 20, 100));
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): OtherService
    {
        return OtherService::create($data)->fresh()->load('module.feature', 'user');
    }

    /** @param array<string, mixed> $data */
    public function update(OtherService $entry, array $data): OtherService
    {
        $entry->update($data);
        $entry->refresh()->load('module.feature', 'user');

        return $entry;
    }

    public function delete(OtherService $entry): void
    {
        $entry->delete();
    }
}
