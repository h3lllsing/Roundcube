<?php

namespace App\Services;

use App\Jobs\SendWebhookJob;
use App\Models\Webhook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class WebhookService
{
    public function fire(string $event, Model $resource): void
    {
        $webhooks = Webhook::where('user_id', $resource->user_id)
            ->where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();

        foreach ($webhooks as $webhook) {
            SendWebhookJob::dispatch($webhook, $event, $resource->toArray());
        }
    }

    public function fireOne(Webhook $webhook, string $event, array $payload): void
    {
        SendWebhookJob::dispatch($webhook, $event, $payload);
    }
}
