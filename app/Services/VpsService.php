<?php

namespace App\Services;

use App\Models\Vps;
use Illuminate\Pagination\LengthAwarePaginator;

class VpsService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Vps>
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Vps::with('module.feature', 'user');
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
                    ->orWhere('ip_address', 'like', '%'.$filters['search'].'%')
                    ->orWhere('department', 'like', '%'.$filters['search'].'%')
                    ->orWhere('location', 'like', '%'.$filters['search'].'%');
            });
        }
        if (! empty($filters['accessible_module_ids'])) {
            $query->whereIn('module_id', $filters['accessible_module_ids']);
        }
        $sortBy = in_array($filters['sort_by'] ?? 'expiry_date', ['name', 'plan', 'ip_address', 'os', 'cost', 'status', 'created_at', 'expiry_date']) ? ($filters['sort_by'] ?? 'expiry_date') : 'expiry_date';
        $sortOrder = in_array($filters['sort_order'] ?? 'asc', ['asc', 'desc']) ? ($filters['sort_order'] ?? 'asc') : 'asc';

        return $query->orderBy($sortBy, $sortOrder)->paginate(min($filters['per_page'] ?? 20, 100));
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Vps
    {
        return Vps::create($data)->fresh()->load('module.feature', 'user');
    }

    /** @param array<string, mixed> $data */
    public function update(Vps $entry, array $data): Vps
    {
        $entry->update($data);
        $entry->refresh()->load('module.feature', 'user');

        return $entry;
    }

    public function delete(Vps $entry): void
    {
        $entry->delete();
    }
}
