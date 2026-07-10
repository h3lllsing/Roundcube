<?php

namespace App\Services;

use App\Models\Feature;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FeatureService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Feature>
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Feature::query();

        if (! empty($filters['with_trashed'])) {
            $query->withTrashed();
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['accessible_module_ids'])) {
            $query->whereHas('modules', fn ($q) => $q->whereIn('id', $filters['accessible_module_ids']));
        }

        $sortBy = $filters['sort_by'] ?? 'name';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $allowedSort = ['name', 'created_at', 'updated_at'];
        if (! in_array($sortBy, $allowedSort)) {
            $sortBy = 'name';
        }
        if (! in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        return $query->withCount('modules')->with('creator')->orderBy($sortBy, $sortOrder)->paginate(min($filters['per_page'] ?? 20, 100));
    }

    public function find(int $id): Feature
    {
        return Feature::with('modules')->findOrFail($id);
    }

    public function findBySlug(string $slug): Feature
    {
        return Feature::where('slug', $slug)->with('modules')->firstOrFail();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Feature
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $feature = Feature::create($data);
        Cache::increment('features:version');

        return $feature;
    }

    /** @param array<string, mixed> $data */
    public function update(Feature $feature, array $data): Feature
    {
        if (isset($data['name']) && ! isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $feature->update($data);
        Cache::increment('features:version');

        return $feature->fresh();
    }

    public function delete(Feature $feature): void
    {
        $feature->delete();
        Cache::increment('features:version');
    }
}
