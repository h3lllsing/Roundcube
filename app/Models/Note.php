<?php

namespace App\Models;

use App\Traits\HasAttachments;
use Database\Factories\NoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Note extends Model
{
    /** @use HasFactory<NoteFactory> */
    use HasAttachments, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'content',
        'user_id',
        'notable_type',
        'notable_id',
        'is_pinned',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Note #{$this->id} {$eventName}");
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, $this> $relation */
        $relation = $this->belongsTo(User::class);

        return $relation;
    }

    /** @return MorphTo<Model, $this> */
    public function notable(): MorphTo
    {
        return $this->morphTo();
    }
}
