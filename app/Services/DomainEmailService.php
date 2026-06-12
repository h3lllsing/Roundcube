<?php

namespace App\Services;

use App\Models\DomainEmail;

class DomainEmailService
{
    /**
     * @param array<string, mixed> $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, DomainEmail>
     */
    public function list(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = DomainEmail::with('domain', 'module.feature', 'user');
        if (!empty($filters['with_trashed'])) $query->withTrashed();
        if (isset($filters['module_id'])) $query->where('module_id', $filters['module_id']);
        if (isset($filters['domain_id'])) $query->where('domain_id', $filters['domain_id']);
        if (isset($filters['status'])) $query->where('status', $filters['status']);
        if (isset($filters['user_id'])) $query->where('user_id', $filters['user_id']);
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('provider', 'like', '%' . $filters['search'] . '%');
            });
        }
        if (!empty($filters['accessible_module_ids'])) $query->whereIn('module_id', $filters['accessible_module_ids']);
        $sortBy = in_array($filters['sort_by'] ?? 'expiry_date', ['email','provider','cost','status','created_at','expiry_date']) ? ($filters['sort_by'] ?? 'expiry_date') : 'expiry_date';
        $sortOrder = in_array($filters['sort_order'] ?? 'asc', ['asc','desc']) ? ($filters['sort_order'] ?? 'asc') : 'asc';
        return $query->orderBy($sortBy, $sortOrder)->paginate(min($filters['per_page'] ?? 20, 100));
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): DomainEmail
    {
        return DomainEmail::create($data)->fresh()->load('domain', 'module.feature', 'user');
    }

    /** @param array<string, mixed> $data */
    public function update(DomainEmail $entry, array $data): DomainEmail
    {
        $entry->update($data);
        $entry->refresh()->load('domain', 'module.feature', 'user');
        return $entry;
    }

    public function delete(DomainEmail $entry): void { $entry->delete(); }
}