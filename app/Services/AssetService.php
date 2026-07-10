<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetAssignment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AssetService
{
    public function list(array $filters = [])
    {
        $query = Asset::with([
            'assignee', 'user', 'module.feature',
        ]);

        if (!empty($filters['with_trashed'])) {
            $query->withTrashed();
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        if (!empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['accessible_module_ids'])) {
            $query->whereIn('module_id', $filters['accessible_module_ids']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('anydesk_id', 'like', "%{$search}%")
                  ->orWhere('premises', 'like', "%{$search}%")
                  ->orWhere('reporting_authority', 'like', "%{$search}%")
                  ->orWhere('assigned_user_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['asset_tag', 'created_at', 'status', 'department']) ? $filters['sort_by'] : 'created_at';
        $sortOrder = ($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = min((int)($filters['per_page'] ?? 20), 100);

        return $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
    }

    public function create(array $data): Asset
    {
        if (empty($data['asset_tag'])) {
            $data['asset_tag'] = Cache::lock('asset_tag_generation', 5)->block(5, function () {
                $prefix = config('assets.tag_prefix', 'AST');
                $last = Asset::withTrashed()->where('asset_tag', 'like', "{$prefix}-%")->orderBy('id', 'desc')->first();
                $nextId = $last ? ((int) substr($last->asset_tag, strlen($prefix) + 1)) + 1 : 1;
                return $prefix . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            });
        }

        return Asset::create($data)->fresh()->load(['assignee', 'user', 'module.feature']);
    }

    public function update(Asset $asset, array $data): Asset
    {
        $asset->update($data);
        return $asset->fresh()->load(['assignee', 'user', 'module.feature']);
    }

    public function delete(Asset $asset): void
    {
        $asset->delete();
    }

    public function assign(Asset $asset, array $data): Asset
    {
        return DB::transaction(function () use ($asset, $data) {
            $asset->update([
                'assigned_to' => $data['assigned_to'],
                'department' => $data['department'] ?? $asset->department,
                'issue_date' => $data['issue_date'] ?? now()->format('Y-m-d'),
                'status' => 'assigned',
                'return_date' => null,
            ]);

            AssetAssignment::create([
                'asset_id' => $asset->id,
                'assigned_to' => $data['assigned_to'],
                'department' => $data['department'] ?? null,
                'assigned_by' => $data['assigned_by'],
                'assigned_at' => now(),
                'expected_return_at' => $data['expected_return_at'] ?? null,
                'assignment_reason' => $data['assignment_reason'] ?? null,
                'note' => $data['note'] ?? null,
            ]);

            return $asset->fresh()->load(['assignee']);
        });
    }

    public function returnAsset(Asset $asset, array $data = []): Asset
    {
        return DB::transaction(function () use ($asset, $data) {
            $activeAssignment = AssetAssignment::where('asset_id', $asset->id)
                ->whereNull('returned_at')
                ->latest('assigned_at')
                ->first();

            if ($activeAssignment) {
                $activeAssignment->update([
                    'returned_at' => now(),
                    'condition_on_return' => $data['condition_on_return'] ?? null,
                    'note' => isset($data['note']) ? ($activeAssignment->note ? $activeAssignment->note . "\n" . $data['note'] : $data['note']) : $activeAssignment->note,
                ]);
            }

            $asset->update([
                'assigned_to' => null,
                'status' => 'available',
                'return_date' => now()->format('Y-m-d'),
            ]);

            return $asset->fresh()->load(['assignee']);
        });
    }
}
