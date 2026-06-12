<?php

namespace App\Models;

use App\Traits\HasAttachments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Vps extends Model
{
    /** @use HasFactory<\Database\Factories\VpsFactory> */
    use HasFactory, SoftDeletes, LogsActivity, HasAttachments;

    protected $table = 'vps';

    protected $fillable = ['user_id', 'module_id', 'name', 'provider', 'plan', 'ip_address', 'os', 'ram_mb', 'disk_gb', 'cpu_cores', 'cost', 'start_date', 'expiry_date', 'status', 'notes'];

    protected function casts(): array
    {
        return [
            'ram_mb' => 'integer',
            'disk_gb' => 'integer',
            'cpu_cores' => 'integer',
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
