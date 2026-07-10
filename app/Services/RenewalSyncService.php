<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Voip;
use App\Models\Vps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RenewalSyncService
{
    private function moduleSlugFor(Model $service): string
    {
        return match ($service::class) {
            Hosting::class => 'hostings',
            Vps::class => 'vps',
            Voip::class => 'voip',
            Domain::class => 'domains',
            DomainEmail::class => 'domain-emails',
            ServiceProvider::class => 'service-providers',
            OtherService::class => 'other-services',
            default => $service->getTable(),
        };
    }
    public function sync(Model $service, int $retries = 3): ?ExpiryTracker
    {
        if (! $service->expiry_date) {
            $this->remove($service);
            return null;
        }

        $attempt = 0;
        while (true) {
            try {
                return DB::transaction(function () use ($service) {
                $tracker = ExpiryTracker::where([
                    'trackable_type' => $service->getMorphClass(),
                    'trackable_id'   => $service->id,
                ])->lockForUpdate()->first();

                if (! $tracker) {
                    $tracker = new ExpiryTracker;
                    $tracker->trackable_type = $service->getMorphClass();
                    $tracker->trackable_id = $service->id;
                }

                $period = $service->billing_period_months ?? 12;

                $tracker->fill([
                    'user_id'             => $service->user_id,
                    'module_id'           => $service->module_id ?? \App\Helpers\ModuleCache::idBySlug($this->moduleSlugFor($service)),
                    'service_provider_id' => $service->service_provider_id ?? null,
                    'name'                => $service->name ?? $service->email ?? class_basename($service).' #'.$service->id,
                    'expiry_date'         => $service->expiry_date,
                    'cost'                => $service->cost ?? null,
                    'renewal_date'        => $service->renewal_date ?? null,
                    'billing_period_months' => $period,
                ]);

                if (! $tracker->exists) {
                    $tracker->fill([
                        'email_notifications_enabled' => true,
                        'notify_days_before'          => $this->defaultNotifyDays($period),
                        'notify_on_expiry_day'        => false,
                        'notify_assigned_user'        => true,
                        'notify_admins'               => false,
                        'notify_custom_emails'        => null,
                    ]);
                }

                $tracker->save();

                return $tracker;
            });
            } catch (\Throwable $e) {
                $attempt++;
                if ($attempt >= $retries) {
                    Log::error('RenewalSyncService::sync failed after retries exhausted', [
                        'model' => get_class($service),
                        'model_id' => $service->id,
                        'attempts' => $attempt,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }
                Log::warning('RenewalSyncService::sync retrying', [
                    'model' => get_class($service),
                    'model_id' => $service->id,
                    'attempt' => $attempt,
                    'max_retries' => $retries,
                ]);
                usleep(200_000 * $attempt);
            }
        }
    }

    /** @param class-string[] $modelClasses */
    public function detectInconsistencies(array $modelClasses = []): array
    {
        $models = $modelClasses ?: [
            Domain::class, Hosting::class, Vps::class, Voip::class,
            DomainEmail::class, OtherService::class, ServiceProvider::class,
        ];

        $inconsistencies = [];

        foreach ($models as $modelClass) {
            $modelClass::whereNotNull('expiry_date')
                ->whereNull('deleted_at')
                ->chunk(100, function ($records) use ($modelClass, &$inconsistencies) {
                    foreach ($records as $record) {
                        $tracker = ExpiryTracker::where([
                            'trackable_type' => $record->getMorphClass(),
                            'trackable_id'   => $record->id,
                        ])->first();

                        if (! $tracker) {
                            $inconsistencies[] = [
                                'type' => 'missing',
                                'model' => class_basename($modelClass),
                                'model_id' => $record->id,
                                'entity_expiry' => $record->expiry_date,
                                'tracker_expiry' => null,
                            ];
                        } elseif ((string) $tracker->expiry_date !== (string) $record->expiry_date) {
                            $inconsistencies[] = [
                                'type' => 'mismatch',
                                'model' => class_basename($modelClass),
                                'model_id' => $record->id,
                                'entity_expiry' => $record->expiry_date,
                                'tracker_expiry' => $tracker->expiry_date,
                            ];
                        }
                    }
                });
        }

        return $inconsistencies;
    }

    public function remove(Model $service): void
    {
        ExpiryTracker::where([
            'trackable_type' => $service->getMorphClass(),
            'trackable_id'   => $service->id,
        ])->delete();
    }

    public function restore(Model $service): void
    {
        ExpiryTracker::withTrashed()->where([
            'trackable_type' => $service->getMorphClass(),
            'trackable_id'   => $service->id,
        ])->restore();
    }

    public function defaultNotifyDays(int $billingPeriodMonths): array
    {
        return match (true) {
            $billingPeriodMonths <= 1  => [7, 3, 1],
            $billingPeriodMonths <= 3  => [14, 7, 3, 1],
            $billingPeriodMonths <= 6  => [21, 14, 7, 1],
            $billingPeriodMonths <= 12 => [30, 15, 7, 1],
            default                    => [60, 30, 15, 7, 1],
        };
    }
}
