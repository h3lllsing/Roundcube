<?php

namespace App\Services;

use App\Models\Note;
use App\Models\User;
use App\Notifications\NoteAdded;
use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class NoteService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Note>
     */
    public function listFor(mixed $notable = null, array $filters = []): LengthAwarePaginator
    {
        if ($notable) {
            $query = $notable->notes();
        } else {
            $query = Note::whereNull('notable_type');
            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }
        }

        if (isset($filters['search'])) {
            $query->where('content', 'like', '%'.$filters['search'].'%');
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $allowedSort = ['created_at', 'updated_at', 'content'];
        if (! in_array($sortBy, $allowedSort)) {
            $sortBy = 'created_at';
        }
        if (! in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        return $query->with('user')->orderBy($sortBy, $sortOrder)
            ->paginate(min($filters['per_page'] ?? 50, 100));
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, mixed $notable = null): Note
    {
        $note = new Note(['content' => $data['content'], 'user_id' => $data['user_id']]);

        if ($notable) {
            $note->notable()->associate($notable);
        }

        DB::transaction(function () use ($note) {
            $note->save();
            $this->notifyNoteAdded($note);
        });

        $note->load('user');

        return $note;
    }

    /** @param array<string, mixed> $data */
    public function update(Note $note, array $data): Note
    {
        if (isset($data['content'])) {
            $note->content = $data['content'];
        }
        $note->save();
        $note->load('user');

        return $note;
    }

    public function delete(Note $note): void
    {
        $note->delete();
    }

    public function applyUserScope(User $user): void
    {
        if ($user->hasRole('super-admin')) {
            return;
        }
        $accessibleIds = $user->getAccessibleModuleIds('read');
        Note::addGlobalScope('moduleNoteScope', function ($q) use ($accessibleIds, $user) {
            $q->where(function ($sub) use ($accessibleIds, $user) {
                $sub->where('notable_type', 'App\Models\Module')
                    ->whereIn('notable_id', $accessibleIds);
                $sub->orWhere('user_id', $user->id);
            });
        });
    }

    public function listAll(array $filters = []): LengthAwarePaginator
    {
        $query = Note::with('user');

        if (! empty($filters['search'])) {
            $query->where('content', 'like', '%'.$filters['search'].'%');
        }
        if (! empty($filters['notable_type'])) {
            $query->where('notable_type', $filters['notable_type']);
        }

        return $query->select(['id', 'content', 'notable_type', 'notable_id', 'created_at', 'user_id'])
            ->latest()
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getDistinctTypes(): array
    {
        return Note::whereNotNull('notable_type')
            ->distinct()
            ->pluck('notable_type')
            ->toArray();
    }

    public function togglePin(Note $note): Note
    {
        $note->update(['is_pinned' => ! $note->is_pinned]);
        return $note;
    }

    private function notifyNoteAdded(Note $note): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        if (! $superAdminRole) {
            return;
        }

        $superAdmins = User::whereHas('roles', fn ($q) => $q->where('roles.id', $superAdminRole->id))->get();
        foreach ($superAdmins as $admin) {
            if ($admin->id !== $note->user_id) {
                $admin->notify(new NoteAdded($note));
            }
        }
    }
}
