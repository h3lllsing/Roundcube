<?php

namespace App\Models;

use App\Traits\HasAttachments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DomainEmail extends Model
{
    /** @use HasFactory<\Database\Factories\DomainEmailFactory> */
    use HasFactory, SoftDeletes, LogsActivity, HasAttachments;

    protected $table = 'domain_emails';

    protected $fillable = ['user_id', 'module_id', 'domain_id', 'email', 'provider', 'storage_mb', 'cost', 'expiry_date', 'status', 'notes'];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'storage_mb' => 'integer',
            'cost' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    /** @return BelongsTo<Module, $this> */
    public function module(): BelongsTo { return $this->belongsTo(Module::class); }
    /** @return BelongsTo<Domain, $this> */
    public function domain(): BelongsTo { return $this->belongsTo(Domain::class); }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}
