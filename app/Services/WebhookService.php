<?php

namespace App\Services;

use App\Models\Webhook;
use Illuminate\Support\Facades\Http;

class WebhookService
{
    /** @param array<string, mixed> $payload */
    public function fire(string $event, array $payload): void
    {
        $webhooks = Webhook::where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();

        foreach ($webhooks as $webhook) {
            try {
                Http::timeout(10)->post($webhook->url, [
                    'event' => $event,
                    'payload' => $payload,
                    'sent_at' => now()->toIso8601String(),
                ]);
                $webhook->update(['last_fired_at' => now()]);
            } catch (\Exception $e) {
                // Log failure silently
                logger()->error('Webhook failed', ['webhook_id' => $webhook->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
