<?php

namespace App\Notifications;

use App\Models\Note;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NoteAdded extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Note $note
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'note_added',
            'note_id' => $this->note->id,
            'content' => str($this->note->content)->limit(120)->toString(),
            'added_by_name' => $this->note->user?->name,
            'added_by_id' => $this->note->user_id,
            'notable_type' => $this->note->notable_type,
            'notable_id' => $this->note->notable_id,
        ];
    }
}
