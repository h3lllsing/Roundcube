<?php

namespace App\Services;

use App\Models\Note;
use App\Models\User;
use App\Notifications\NoteAdded;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Database\Eloquent\Model;

class NoteService
{
    /**
     * @param array<string, mixed> $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, Note>
     */
    public function listFor(mixed $notable = null, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
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
            $query->where('content', 'like', '%' . $filters['search'] . '%');
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $allowedSort = ['created_at', 'updated_at', 'content'];
        if (!in_array($sortBy, $allowedSort)) $sortBy = 'created_at';
        if (!in_array($sortOrder, ['asc', 'desc'])) $sortOrder = 'desc';

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

        $note->save();

        $note->load('user');
        $this->notifyNoteAdded($note);

        return $note;
    }

    public function delete(Note $note): void
    {
        $note->delete();
    }

    private function notifyNoteAdded(Note $note): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        if (!$superAdminRole) return;

        $superAdmins = User::whereHas('roles', fn($q) => $q->where('roles.id', $superAdminRole->id))->get();
        foreach ($superAdmins as $admin) {
            if ($admin->id !== $note->user_id) {
                $admin->notify(new NoteAdded($note));
            }
        }
    }
}