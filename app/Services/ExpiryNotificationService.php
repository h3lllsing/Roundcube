<?php

namespace App\Services;

use App\Events\ExpiryWarningTriggered;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Voip;
use App\Models\Vps;
use App\Notifications\ExpiringSoon;
use App\Services\WebhookService;
use Illuminate\Notifications\DatabaseNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ExpiryNotificationService
{
    public function __construct(
        private readonly WebhookService $webhookService
    ) {}

    /** @var array<class-string, string> */
    private array $models = [
        ExpiryTracker::class => 'Expiry Tracker',
        Domain::class => 'Domain',
        Hosting::class => 'Hosting',
        Vps::class => 'VPS',
        Voip::class => 'VoIP',
        ServiceProvider::class => 'Service Provider',
        DomainEmail::class => 'Domain Email',
        OtherService::class => 'Other Service',
    ];

    public function check(): int
    {
        $sent = 0;

        foreach ($this->models as $modelClass => $label) {
            $sent += $this->checkModel($modelClass, $label);
        }

        return $sent;
    }

    private function checkModel(string $modelClass, string $label): int
    {
        $sent = 0;

        $items = $modelClass::with('user')
            ->whereNotNull('expiry_date')
            ->where('status', 'active')
            ->get();

        foreach ($items as $item) {
            $days = (int) Carbon::today()->startOfDay()->diffInDays(Carbon::parse($item->expiry_date)->startOfDay(), false);

            $threshold = $this->getThresholdCategory($days);
            if ($threshold === null) {
                continue;
            }

            if ($this->alreadyNotified($item->user_id, $modelClass, $item->id, $threshold)) {
                continue;
            }

            $user = $item->user;
            if (!$user) {
                continue;
            }

            $name = $item->name ?? $item->email ?? 'Unnamed';

            $user->notify(new ExpiringSoon(
                itemType: $modelClass,
                itemId: $item->id,
                name: $name,
                entityType: $label,
                expiryDate: $item->expiry_date,
                threshold: $threshold,
                daysRemaining: $days,
            ));

            $this->webhookService->fire('expiring_soon', [
                'item_type' => $modelClass,
                'item_id' => $item->id,
                'name' => $name,
                'entity_type' => $label,
                'expiry_date' => $item->expiry_date,
                'days_remaining' => $days,
                'threshold' => $threshold,
                'user_email' => $user->email,
            ]);

            ExpiryWarningTriggered::dispatch($item, $label, $user, $days);

            $sent++;
        }

        return $sent;
    }

    private function getThresholdCategory(int $days): ?string
    {
        return match (true) {
            $days < 0 => 'overdue',
            $days <= 1 => '1_day',
            $days <= 3 => '3_days',
            $days <= 7 => '7_days',
            $days <= 14 => '14_days',
            $days <= 30 => '30_days',
            default => null,
        };
    }

    private function alreadyNotified(int $userId, string $itemType, int $itemId, string $threshold): bool
    {
        return DatabaseNotification::where('type', ExpiringSoon::class)
            ->where('notifiable_id', $userId)
            ->whereJsonContains('data->item_type', $itemType)
            ->whereJsonContains('data->item_id', $itemId)
            ->whereJsonContains('data->threshold', $threshold)
            ->exists();
    }
}
