<?php

namespace App\Models;

use App\Traits\HasAttachments;
use Database\Factories\VoipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Note;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\HasAutoNotes;

class Voip extends Model
{
    /** @use HasFactory<VoipFactory> */
    use HasAttachments, HasFactory, LogsActivity, SoftDeletes, HasAutoNotes;

    protected $table = 'voip';

    protected $fillable = ['module_id', 'user_id', 'service_provider_id', 'name', 'extensions', 'phone_number', 'type', 'direction', 'username', 'password', 'extension_password', 'dashboard_url', 'server_ip', 'billing_period_months', 'cost', 'start_date', 'expiry_date', 'status', 'number_status', 'outbound_code', 'team_details', 'description', 'monitoring_url', 'last_ping_at'];

    protected $hidden = ['password', 'extension_password'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expiry_date' => 'date',
            'billing_period_months' => 'integer',
            'cost' => 'decimal:2',
            'password' => 'encrypted',
            'extension_password' => 'encrypted',
            'extensions' => 'array',
            'last_ping_at' => 'datetime',
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
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs()->dontLogIfAttributesChangedOnly(['password', 'extension_password']);
    }

    /** @return MorphMany<Note, $this> */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }
}
