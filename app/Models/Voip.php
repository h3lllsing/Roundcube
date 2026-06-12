<?php

namespace App\Models;

use App\Traits\HasAttachments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Voip extends Model
{
    /** @use HasFactory<\Database\Factories\VoipFactory> */
    use HasFactory, SoftDeletes, LogsActivity, HasAttachments;

    protected $table = 'voip';

    protected $fillable = ['user_id', 'module_id', 'name', 'provider', 'phone_number', 'type', 'username', 'cost', 'start_date', 'expiry_date', 'status', 'notes'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expiry_date' => 'date',
            'cost' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    /** @return BelongsTo<Module, $this> */
    public function module(): BelongsTo { return $this->belongsTo(Module::class); }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}
