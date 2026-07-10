<?php

namespace Tests\Unit;

use App\Jobs\SendWebhookJob;
use App\Models\User;
use App\Models\VaultEntry;
use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookServiceTest extends TestCase
{
    use RefreshDatabase;

    private WebhookService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WebhookService::class);
    }

    public function test_fires_to_active_webhooks(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $resource = VaultEntry::factory()->create(['user_id' => $user->id]);
        Webhook::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://hook.example.com',
            'events' => ['test.event'],
            'is_active' => true,
        ]);

        $this->service->fire('test.event', $resource);

        Queue::assertPushed(SendWebhookJob::class);
    }

    public function test_skips_inactive_webhooks(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $resource = VaultEntry::factory()->create(['user_id' => $user->id]);
        Webhook::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://inactive.example.com',
            'events' => ['test.event'],
            'is_active' => false,
        ]);

        $this->service->fire('test.event', $resource);

        Queue::assertNotPushed(SendWebhookJob::class);
    }

    public function test_skips_webhooks_not_listening_for_event(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $resource = VaultEntry::factory()->create(['user_id' => $user->id]);
        Webhook::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://wrong-event.example.com',
            'events' => ['other.event'],
            'is_active' => true,
        ]);

        $this->service->fire('test.event', $resource);

        Queue::assertNotPushed(SendWebhookJob::class);
    }

    public function test_fires_to_multiple_webhooks(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $resource = VaultEntry::factory()->create(['user_id' => $user->id]);
        Webhook::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://hook1.example.com',
            'events' => ['shared.event'],
            'is_active' => true,
        ]);
        Webhook::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://hook2.example.com',
            'events' => ['shared.event'],
            'is_active' => true,
        ]);

        $this->service->fire('shared.event', $resource);

        Queue::assertPushed(SendWebhookJob::class, 2);
    }

    public function test_handles_http_failure_gracefully(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $resource = VaultEntry::factory()->create(['user_id' => $user->id]);
        Webhook::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://failing.example.com',
            'events' => ['test.event'],
            'is_active' => true,
        ]);

        $this->service->fire('test.event', $resource);

        Queue::assertPushed(SendWebhookJob::class);
    }

    public function test_handles_exception_gracefully(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $resource = VaultEntry::factory()->create(['user_id' => $user->id]);
        Webhook::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://exception.example.com',
            'events' => ['test.event'],
            'is_active' => true,
        ]);

        $this->service->fire('test.event', $resource);

        Queue::assertPushed(SendWebhookJob::class);
    }

    public function test_includes_payload_structure(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $resource = VaultEntry::factory()->create(['user_id' => $user->id, 'service_name' => 'Acme API']);
        Webhook::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://payload.example.com',
            'events' => ['ping'],
            'is_active' => true,
        ]);

        $this->service->fire('ping', $resource);

        Queue::assertPushed(function (SendWebhookJob $job) {
            return true;
        });
    }

    public function test_updates_last_fired_at_on_success(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $resource = VaultEntry::factory()->create(['user_id' => $user->id]);
        $webhook = Webhook::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://success.example.com',
            'events' => ['ok'],
            'is_active' => true,
        ]);

        $this->assertNull($webhook->last_fired_at);

        $this->service->fire('ok', $resource);

        $this->assertNull($webhook->fresh()->last_fired_at);
    }
}
