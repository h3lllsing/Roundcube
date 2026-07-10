<?php

namespace App\Models;

use App\Traits\Blameable;
use App\Traits\HasAttachments;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use Blameable, HasAttachments, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'module_id',
        'status',
        'priority',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Task {$this->title} {$eventName}");
    }

    /** @return BelongsTo<Module, $this> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function assignees(): BelongsToMany
    {
        /** @var BelongsToMany<User, $this> $relation */
        $relation = $this->belongsToMany(User::class, 'task_user')
            ->withPivot('assigned_at')
            ->withTimestamps();

        return $relation;
    }
}
