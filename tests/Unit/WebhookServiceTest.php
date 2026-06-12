<?php

namespace Tests\Unit;

use App\Models\Webhook;
use App\Models\User;
use App\Services\WebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
        Http::fake();

        User::factory()->create();
        Webhook::factory()->create([
            'url' => 'https://hook.example.com',
            'events' => ['test.event'],
            'is_active' => true,
        ]);

        $this->service->fire('test.event', ['key' => 'value']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://hook.example.com'
                && $request->method() === 'POST';
        });
    }

    public function test_skips_inactive_webhooks(): void
    {
        Http::fake();

        User::factory()->create();
        Webhook::factory()->create([
            'url' => 'https://inactive.example.com',
            'events' => ['test.event'],
            'is_active' => false,
        ]);

        $this->service->fire('test.event', []);

        Http::assertNothingSent();
    }

    public function test_skips_webhooks_not_listening_for_event(): void
    {
        Http::fake();

        User::factory()->create();
        Webhook::factory()->create([
            'url' => 'https://wrong-event.example.com',
            'events' => ['other.event'],
            'is_active' => true,
        ]);

        $this->service->fire('test.event', []);

        Http::assertNothingSent();
    }

    public function test_fires_to_multiple_webhooks(): void
    {
        Http::fake();

        User::factory()->create();
        Webhook::factory()->create([
            'url' => 'https://hook1.example.com',
            'events' => ['shared.event'],
            'is_active' => true,
        ]);
        Webhook::factory()->create([
            'url' => 'https://hook2.example.com',
            'events' => ['shared.event'],
            'is_active' => true,
        ]);

        $this->service->fire('shared.event', []);

        Http::assertSentCount(2);
    }

    public function test_handles_http_failure_gracefully(): void
    {
        Http::fake(['*' => Http::response(null, 500)]);

        User::factory()->create();
        $webhook = Webhook::factory()->create([
            'url' => 'https://failing.example.com',
            'events' => ['test.event'],
            'is_active' => true,
        ]);

        $this->service->fire('test.event', []);

        Http::assertSentCount(1);
        $this->assertNotNull($webhook->fresh()->last_fired_at);
    }

    public function test_handles_exception_gracefully(): void
    {
        Http::fake(['*' => function () {
            throw new \Exception('Connection timeout');
        }]);

        User::factory()->create();
        $webhook = Webhook::factory()->create([
            'url' => 'https://exception.example.com',
            'events' => ['test.event'],
            'is_active' => true,
        ]);

        $this->service->fire('test.event', []);

        $this->assertNull($webhook->fresh()->last_fired_at);
    }

    public function test_includes_payload_structure(): void
    {
        Http::fake();

        User::factory()->create();
        Webhook::factory()->create([
            'url' => 'https://payload.example.com',
            'events' => ['ping'],
            'is_active' => true,
        ]);

        $this->service->fire('ping', ['data' => 42]);

        Http::assertSent(function ($request) {
            $body = $request->data();
            return isset($body['event'], $body['payload'], $body['sent_at'])
                && $body['event'] === 'ping'
                && $body['payload'] === ['data' => 42];
        });
    }

    public function test_updates_last_fired_at_on_success(): void
    {
        Http::fake();

        User::factory()->create();
        $webhook = Webhook::factory()->create([
            'url' => 'https://success.example.com',
            'events' => ['ok'],
            'is_active' => true,
        ]);

        $this->assertNull($webhook->last_fired_at);

        $this->service->fire('ok', []);

        $this->assertNotNull($webhook->fresh()->last_fired_at);
    }
}
