<?php

namespace App\Models;

use App\Traits\HasAttachments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Note extends Model
{
    /** @use HasFactory<\Database\Factories\NoteFactory> */
    use HasFactory, LogsActivity, HasAttachments;

    protected $fillable = [
        'content',
        'user_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Note #{$this->id} {$eventName}");
    }

    /** @return BelongsTo<\App\Models\User, $this> */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<\App\Models\User, $this> $relation */
        /** @phpstan-ignore-next-line  */
        $relation = $this->belongsTo(config('tyro.models.user', 'App\Models\User'));
        return $relation;
    }

    /** @return MorphTo<\Illuminate\Database\Eloquent\Model, $this> */
    public function notable(): MorphTo
    {
        return $this->morphTo();
    }
}
