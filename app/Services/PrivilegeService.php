<?php

namespace App\Services;

use App\Models\Privilege;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class PrivilegeService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Privilege::withCount('roles');

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('slug', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->select(['id', 'name', 'slug'])->latest()->paginate($filters['per_page'] ?? 20);
    }

    public function create(array $data): Privilege
    {
        $privilege = Privilege::create($data);

        activity()->event('created')
            ->causedBy(Auth::user())
            ->withProperties([
                'name' => $privilege->name,
                'slug' => $privilege->slug,
            ])
            ->log('Privilege created: '.$privilege->name);

        return $privilege;
    }

    public function find(int $id): Privilege
    {
        return Privilege::with('roles')->findOrFail($id);
    }

    public function update(Privilege $privilege, array $data): Privilege
    {
        $original = $privilege->getOriginal();
        $privilege->update($data);

        $changed = $privilege->getChanges();
        $dirty = array_diff_key($changed, array_flip(['updated_at']));
        $oldValues = array_intersect_key($original, $dirty);

        activity()->event('updated')
            ->performedOn($privilege)
            ->causedBy(Auth::user())
            ->withProperties([
                'old' => $oldValues,
                'attributes' => $dirty,
            ])
            ->log('Privilege updated: '.$privilege->name);

        return $privilege;
    }

    public function delete(int $id): ?string
    {
        $privilege = Privilege::withCount('roles')->findOrFail($id);

        if ($privilege->roles_count > 0) {
            return 'Cannot delete privilege "'.$privilege->name.'" — it is assigned to '.$privilege->roles_count.' role(s). Remove assignments first.';
        }

        $name = $privilege->name;
        $slug = $privilege->slug;
        $privilege->delete();

        activity()->event('deleted')
            ->causedBy(Auth::user())
            ->withProperties([
                'name' => $name,
                'slug' => $slug,
            ])
            ->log('Privilege deleted: '.$name);

        return null;
    }
}
