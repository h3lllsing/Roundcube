<?php

namespace App\Jobs;

use App\Models\Webhook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebhookJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $timeout = 30;

    public function __construct(
        private readonly Webhook $webhook,
        private readonly string $event,
        private readonly array $payload,
    ) {}

    public function backoff(): array
    {
        return [5, 15, 60, 300, 900];
    }

    public function handle(): void
    {
        $body = [
            'event' => $this->event,
            'payload' => $this->payload,
            'sent_at' => now()->toIso8601String(),
        ];

        $signature = hash_hmac('sha256', json_encode($body), $this->webhook->secret ?? '');

        $response = Http::timeout(10)
            ->withHeaders([
                'X-Webhook-Signature' => $signature,
                'Content-Type' => 'application/json',
            ])
            ->post($this->webhook->url, $body);

        if (! $response->successful()) {
            throw new \RuntimeException("Webhook {$this->webhook->id} returned status {$response->status()}");
        }

        $this->webhook->update(['last_fired_at' => now()]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::warning("SendWebhookJob failed for webhook {$this->webhook->id}: {$exception->getMessage()}");
    }
}
