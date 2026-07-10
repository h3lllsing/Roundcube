<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

class ActivityLogService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Activity::with('causer');

        if (! empty($filters['subject_type'])) {
            $query->where('subject_type', $filters['subject_type']);
        }
        if (! empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }
        if (! empty($filters['search'])) {
            $query->where('description', 'like', '%'.$filters['search'].'%');
        }
        if (! empty($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['with_subject'])) {
            $query->with('subject');
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $allowedSort = ['created_at', 'updated_at', 'event', 'description'];
        if (! in_array($sortBy, $allowedSort)) {
            $sortBy = 'created_at';
        }
        if (! in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $select = $filters['select'] ?? ['id', 'event', 'description', 'subject_type', 'subject_id', 'created_at', 'causer_id'];

        return $query->select($select)->orderBy($sortBy, $sortOrder)->paginate($filters['per_page'] ?? 30);
    }

    public function find(int $id): Activity
    {
        return Activity::with('causer')->findOrFail($id);
    }

    public function getUsers(): array
    {
        return User::orderBy('name')->pluck('name', 'id')->toArray();
    }
}
