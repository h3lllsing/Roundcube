<?php

namespace App\Services;

use App\Models\Hosting;
use Illuminate\Pagination\LengthAwarePaginator;

class HostingService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Hosting>
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Hosting::with('module.feature', 'user');
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
                    ->orWhere('domain', 'like', '%'.$filters['search'].'%')
                    ->orWhere('domain_ip', 'like', '%'.$filters['search'].'%')
                    ->orWhere('mail_domain_ip', 'like', '%'.$filters['search'].'%')
                    ->orWhere('cpanel_ip', 'like', '%'.$filters['search'].'%');
            });
        }
        if (! empty($filters['accessible_module_ids'])) {
            $query->whereIn('module_id', $filters['accessible_module_ids']);
        }
        $sortBy = in_array($filters['sort_by'] ?? 'expiry_date', ['name', 'plan', 'domain', 'expiry_date', 'cost', 'status', 'created_at']) ? ($filters['sort_by'] ?? 'expiry_date') : 'expiry_date';
        $sortOrder = in_array($filters['sort_order'] ?? 'asc', ['asc', 'desc']) ? ($filters['sort_order'] ?? 'asc') : 'asc';

        return $query->orderBy($sortBy, $sortOrder)->paginate(min($filters['per_page'] ?? 20, 100));
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Hosting
    {
        return Hosting::create($data)->fresh()->load('module.feature', 'user');
    }

    /** @param array<string, mixed> $data */
    public function update(Hosting $entry, array $data): Hosting
    {
        $entry->update($data);
        $entry->refresh()->load('module.feature', 'user');

        return $entry;
    }

    public function delete(Hosting $entry): void
    {
        $entry->delete();
    }
}
