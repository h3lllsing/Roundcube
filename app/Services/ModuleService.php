<?php

namespace App\Services;

use App\Models\Feature;
use App\Models\Module;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ModuleService
{
    /**
     * @param array<string, mixed> $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, Module>
     */
    public function listForFeature(Feature $feature, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $feature->modules()->with('feature');

        if (!empty($filters['with_trashed'])) {
            $query->withTrashed();
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        $sortBy = $filters['sort_by'] ?? 'name';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $allowedSort = ['name', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSort)) $sortBy = 'name';
        if (!in_array($sortOrder, ['asc', 'desc'])) $sortOrder = 'asc';

        return $query->orderBy($sortBy, $sortOrder)->paginate(min($filters['per_page'] ?? 20, 100));
    }

    public function find(int $id): Module
    {
        return Module::with('feature', 'rolePermissions.role')->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(Feature $feature, array $data): Module
    {
        $data['feature_id'] = $feature->id;
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $module = Module::create($data);
        Cache::increment('features:version');
        return $module;
    }

    /** @param array<string, mixed> $data */
    public function update(Module $module, array $data): Module
    {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $module->update($data);
        Cache::increment('features:version');
        return $module->fresh();
    }

    public function delete(Module $module): void
    {
        $module->delete();
        Cache::increment('features:version');
    }
}