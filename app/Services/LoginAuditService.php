<?php

namespace App\Services;

use App\Models\LoginAudit;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class LoginAuditService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = LoginAudit::with('user:id,name,email');

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('email', 'like', '%'.$filters['search'].'%')
                    ->orWhere('ip_address', 'like', '%'.$filters['search'].'%');
            });
        }
        if (! empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->select(['id', 'email', 'event', 'ip_address', 'user_agent', 'created_at', 'user_id'])
            ->latest()
            ->paginate($filters['per_page'] ?? 30);
    }

    public function find(int $id): LoginAudit
    {
        return LoginAudit::with('user:id,name,email')->findOrFail($id);
    }

    public function delete(int $id, User $user): void
    {
        $audit = LoginAudit::findOrFail($id);
        $audit->delete();

        activity()->event('deleted')
            ->causedBy($user)
            ->withProperties([
                'login_audit_id' => $id,
                'email' => $audit->email,
                'event' => $audit->event,
            ])
            ->log('Login audit record deleted for: '.$audit->email);
    }
}
