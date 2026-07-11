<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskOverdue extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
        private readonly int $daysOverdue,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->daysOverdue > 0
            ? "[OpsPilot] Task overdue by {$this->daysOverdue} days — {$this->task->title}"
            : "[OpsPilot] Task overdue — {$this->task->title}";

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . ',');

        $mail->line("A task you are assigned to is overdue.");

        $mail->line("**Task:** {$this->task->title}");
        $mail->line("**Status:** {$this->task->status}");
        $mail->line("**Due Date:** {$this->task->due_date?->format('Y-m-d')}");

        if ($this->daysOverdue > 0) {
            $mail->line("**Days Overdue:** {$this->daysOverdue}");
        }

        $mail->line("**Assigned User:** {$notifiable->name}");

        $mail->line("You received this because you are assigned to this task.");

        return $mail->action('View Task', route('tasks.show', $this->task->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_overdue',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'status' => $this->task->status,
            'due_date' => $this->task->due_date?->toDateString(),
            'days_overdue' => $this->daysOverdue,
        ];
    }
}
