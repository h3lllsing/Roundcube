<?php

namespace App\Models;

use App\Traits\HasAttachments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Domain extends Model
{
    /** @use HasFactory<\Database\Factories\DomainFactory> */
    use HasFactory, SoftDeletes, LogsActivity, HasAttachments;

    protected $fillable = [
        'user_id', 'module_id', 'name', 'registrar', 'registration_date', 'expiry_date',
        'auto_renew', 'cost', 'status', 'dns_servers', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'auto_renew' => 'boolean',
            'registration_date' => 'date',
            'expiry_date' => 'date',
            'cost' => 'decimal:2',
            'dns_servers' => 'array',
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
