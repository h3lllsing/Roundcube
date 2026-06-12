<?php

namespace App\Models;

use App\Traits\Blameable;
use App\Traits\HasAttachments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory, SoftDeletes, Blameable, LogsActivity, HasAttachments;

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
            ->setDescriptionForEvent(fn(string $eventName) => "Task {$this->title} {$eventName}");
    }

    /** @return BelongsTo<\App\Models\Module, $this> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /** @return BelongsToMany<\App\Models\User, $this> */
    public function assignees(): BelongsToMany
    {
        /** @var BelongsToMany<\App\Models\User, $this> $relation */
        /** @phpstan-ignore-next-line  */
        $relation = $this->belongsToMany(config('tyro.models.user', 'App\Models\User'), 'task_user')
            ->withPivot('assigned_at')
            ->withTimestamps();
        return $relation;
    }

    /** @return BelongsTo<\App\Models\User, $this> */
    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        /** @var \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this> $relation */
        /** @phpstan-ignore-next-line  */
        $relation = $this->belongsTo(config('tyro.models.user', 'App\Models\User'), 'created_by');
        return $relation;
    }

    /** @return BelongsTo<\App\Models\User, $this> */
    public function updater(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        /** @var \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this> $relation */
        /** @phpstan-ignore-next-line  */
        $relation = $this->belongsTo(config('tyro.models.user', 'App\Models\User'), 'updated_by');
        return $relation;
    }
}
