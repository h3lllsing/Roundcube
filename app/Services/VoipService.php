<?php

namespace App\Services;

use App\Models\Voip;
use Illuminate\Pagination\LengthAwarePaginator;

class VoipService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Voip>
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Voip::with('module.feature', 'user');
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
                    ->orWhere('phone_number', 'like', '%'.$filters['search'].'%')
                    ->orWhere('server_ip', 'like', '%'.$filters['search'].'%')
                    ->orWhere('outbound_code', 'like', '%'.$filters['search'].'%');
            });
        }
        if (! empty($filters['accessible_module_ids'])) {
            $query->whereIn('module_id', $filters['accessible_module_ids']);
        }
        $sortBy = in_array($filters['sort_by'] ?? 'expiry_date', ['name', 'type', 'cost', 'status', 'created_at', 'expiry_date', 'direction', 'server_ip', 'number_status']) ? ($filters['sort_by'] ?? 'expiry_date') : 'expiry_date';
        $sortOrder = in_array($filters['sort_order'] ?? 'asc', ['asc', 'desc']) ? ($filters['sort_order'] ?? 'asc') : 'asc';

        return $query->orderBy($sortBy, $sortOrder)->paginate(min($filters['per_page'] ?? 20, 100));
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Voip
    {
        return Voip::create($data)->fresh()->load('module.feature', 'user');
    }

    /** @param array<string, mixed> $data */
    public function update(Voip $entry, array $data): Voip
    {
        $entry->update($data);
        $entry->refresh()->load('module.feature', 'user');

        return $entry;
    }

    public function delete(Voip $entry): void
    {
        $entry->delete();
    }
}
