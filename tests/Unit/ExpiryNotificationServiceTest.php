<?php

namespace Tests\Unit;

use App\Models\Domain;
use App\Models\Hosting;
use App\Models\User;
use App\Models\Vps;
use App\Notifications\ExpiringSoon;
use App\Services\ExpiryNotificationService;
use App\Services\WebhookService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ExpiryNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
    }

    public function test_sends_notification_for_expiring_item(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Domain::factory()->create([
            'name' => 'expiring.com',
            'expiry_date' => Carbon::today()->addDays(5)->toDateString(),
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        $webhookService = $this->createMock(WebhookService::class);
        $webhookService->method('fire');

        $service = new ExpiryNotificationService($webhookService);
        $sent = $service->check();

        $this->assertEquals(1, $sent);
        Notification::assertSentTo($user, ExpiringSoon::class);
    }

    public function test_skips_items_with_status_not_active(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Domain::factory()->create([
            'name' => 'expired.com',
            'expiry_date' => Carbon::today()->addDays(5)->toDateString(),
            'status' => 'expired',
            'user_id' => $user->id,
        ]);

        $webhookService = $this->createMock(WebhookService::class);
        $service = new ExpiryNotificationService($webhookService);
        $sent = $service->check();

        $this->assertEquals(0, $sent);
        Notification::assertNothingSent();
    }

    public function test_skips_items_far_in_future(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Domain::factory()->create([
            'name' => 'far.com',
            'expiry_date' => Carbon::today()->addMonths(3)->toDateString(),
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        $webhookService = $this->createMock(WebhookService::class);
        $service = new ExpiryNotificationService($webhookService);
        $sent = $service->check();

        $this->assertEquals(0, $sent);
    }

    public function test_does_not_duplicate_notifications(): void
    {
        $user = User::factory()->create();
        Domain::factory()->create([
            'name' => 'duplicate.com',
            'expiry_date' => Carbon::today()->addDays(5)->toDateString(),
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        $webhookService = $this->createMock(WebhookService::class);
        $service = new ExpiryNotificationService($webhookService);

        $sentFirst = $service->check();
        $sentSecond = $service->check();

        $this->assertEquals(1, $sentFirst);
        $this->assertEquals(0, $sentSecond);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => ExpiringSoon::class,
        ]);
    }

    public function test_notifies_overdue_items(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Domain::factory()->create([
            'name' => 'overdue.com',
            'expiry_date' => Carbon::today()->subDay()->toDateString(),
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        $webhookService = $this->createMock(WebhookService::class);
        $webhookService->method('fire');

        $service = new ExpiryNotificationService($webhookService);
        $sent = $service->check();

        $this->assertEquals(1, $sent);
    }

    public function test_notifies_for_1_day_threshold(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        Hosting::factory()->create(['expiry_date' => Carbon::today()->addDay()->toDateString(), 'status' => 'active', 'user_id' => $user->id]);

        $webhookService = $this->createMock(WebhookService::class);
        $webhookService->method('fire');
        $service = new ExpiryNotificationService($webhookService);
        $this->assertEquals(1, $service->check());
    }

    public function test_notifies_for_14_days_threshold(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        Vps::factory()->create(['expiry_date' => Carbon::today()->addDays(10)->toDateString(), 'status' => 'active', 'user_id' => $user->id]);

        $webhookService = $this->createMock(WebhookService::class);
        $webhookService->method('fire');
        $service = new ExpiryNotificationService($webhookService);
        $this->assertEquals(1, $service->check());
    }

    public function test_notifies_for_30_days_threshold(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        Hosting::factory()->create(['expiry_date' => Carbon::today()->addDays(20)->toDateString(), 'status' => 'active', 'user_id' => $user->id]);

        $webhookService = $this->createMock(WebhookService::class);
        $webhookService->method('fire');
        $service = new ExpiryNotificationService($webhookService);
        $this->assertEquals(1, $service->check());
    }

    public function test_notifies_for_all_model_types(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        \App\Models\ExpiryTracker::factory()->create(['expiry_date' => Carbon::today()->addDays(5)->toDateString(), 'status' => 'active', 'user_id' => $user->id]);
        \App\Models\ServiceProvider::factory()->create(['expiry_date' => Carbon::today()->addDays(5)->toDateString(), 'status' => 'active', 'user_id' => $user->id]);
        \App\Models\DomainEmail::factory()->create(['expiry_date' => Carbon::today()->addDays(5)->toDateString(), 'status' => 'active', 'user_id' => $user->id]);
        \App\Models\OtherService::factory()->create(['expiry_date' => Carbon::today()->addDays(5)->toDateString(), 'status' => 'active', 'user_id' => $user->id]);

        $webhookService = $this->createMock(WebhookService::class);
        $webhookService->method('fire');
        $service = new ExpiryNotificationService($webhookService);
        $this->assertEquals(4, $service->check());
    }


}
