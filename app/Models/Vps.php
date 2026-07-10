<?php

namespace App\Models;

use App\Traits\HasAttachments;
use Database\Factories\VpsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Note;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\HasAutoNotes;

class Vps extends Model
{
    /** @use HasFactory<VpsFactory> */
    use HasAttachments, HasFactory, LogsActivity, SoftDeletes, HasAutoNotes;

    protected $table = 'vps';

    protected $fillable = ['module_id', 'user_id', 'service_provider_id', 'name', 'plan', 'ip_address', 'password', 'os', 'ram_mb', 'disk_gb', 'cpu_cores', 'department', 'location', 'login_ids', 'additional_ips', 'billing_period_months', 'cost', 'start_date', 'expiry_date', 'status', 'description', 'monitoring_url', 'last_ping_at'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'ram_mb' => 'integer',
            'disk_gb' => 'integer',
            'cpu_cores' => 'integer',
            'start_date' => 'date',
            'expiry_date' => 'date',
            'billing_period_months' => 'integer',
            'cost' => 'decimal:2',
            'password' => 'encrypted',
            'last_ping_at' => 'datetime',
            'login_ids' => 'array',
            'additional_ips' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Module, $this> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /** @return BelongsTo<ServiceProvider, $this> */
    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs()->dontLogIfAttributesChangedOnly(['password']);
    }

    /** @return MorphMany<Note, $this> */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }
}
