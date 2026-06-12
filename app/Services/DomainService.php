<?php

namespace App\Services;

use App\Models\Domain;

class DomainService
{
    /**
     * @param array<string, mixed> $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, Domain>
     */
    public function list(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Domain::with('module.feature', 'user');

        if (!empty($filters['with_trashed'])) $query->withTrashed();
        if (isset($filters['module_id'])) $query->where('module_id', $filters['module_id']);
        if (isset($filters['status'])) $query->where('status', $filters['status']);
        if (isset($filters['user_id'])) $query->where('user_id', $filters['user_id']);
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('registrar', 'like', '%' . $filters['search'] . '%');
            });
        }
        if (!empty($filters['accessible_module_ids'])) $query->whereIn('module_id', $filters['accessible_module_ids']);

        $sortBy = $filters['sort_by'] ?? 'expiry_date';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $allowedSort = ['name', 'registrar', 'expiry_date', 'cost', 'status', 'created_at'];
        if (!in_array($sortBy, $allowedSort)) $sortBy = 'expiry_date';
        if (!in_array($sortOrder, ['asc', 'desc'])) $sortOrder = 'asc';

        return $query->orderBy($sortBy, $sortOrder)->paginate(min($filters['per_page'] ?? 20, 100));
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Domain
    {
        return Domain::create($data)->fresh()->load('module.feature', 'user');
    }

    /** @param array<string, mixed> $data */
    public function update(Domain $entry, array $data): Domain
    {
        $entry->update($data);
        $entry->refresh()->load('module.feature', 'user');
        return $entry;
    }

    public function delete(Domain $entry): void { $entry->delete(); }
}