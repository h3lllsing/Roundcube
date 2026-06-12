<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Voip;
use App\Models\Vps;
use App\Notifications\ExpiringSoon;
use App\Services\ExpiryNotificationService;
use Carbon\Carbon;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class ExpiryNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
    }

    private function user(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        return $user;
    }

    public function test_sends_notification_for_item_expiring_within_30_days(): void
    {
        $u = $this->user();
        Domain::factory()->create(['user_id' => $u->id, 'name' => 'example.com', 'expiry_date' => Carbon::today()->addDays(10), 'status' => 'active']);

        $sent = app(ExpiryNotificationService::class)->check();

        $this->assertEquals(1, $sent);
        $this->assertDatabaseHas('notifications', ['notifiable_type' => User::class, 'notifiable_id' => $u->id, 'type' => ExpiringSoon::class]);
    }

    public function test_does_not_send_for_item_expiring_beyond_30_days(): void
    {
        $u = $this->user();
        Domain::factory()->create(['user_id' => $u->id, 'name' => 'example.com', 'expiry_date' => Carbon::today()->addDays(60), 'status' => 'active']);

        $sent = app(ExpiryNotificationService::class)->check();

        $this->assertEquals(0, $sent);
    }

    public function test_sends_overdue_notification(): void
    {
        $u = $this->user();
        Domain::factory()->create(['user_id' => $u->id, 'name' => 'example.com', 'expiry_date' => Carbon::today()->subDays(5), 'status' => 'active']);

        $sent = app(ExpiryNotificationService::class)->check();

        $this->assertEquals(1, $sent);
        $n = DatabaseNotification::where('notifiable_id', $u->id)->first();
        $d = is_array($n->data) ? $n->data : json_decode($n->data, true);
        $this->assertEquals('overdue', $d['threshold']);
        $this->assertEquals(-5, $d['days_remaining']);
    }

    public function test_does_not_send_duplicate_notifications(): void
    {
        $u = $this->user();
        Domain::factory()->create(['user_id' => $u->id, 'name' => 'example.com', 'expiry_date' => Carbon::today()->addDays(10), 'status' => 'active']);

        $service = app(ExpiryNotificationService::class);
        $this->assertEquals(1, $service->check());
        $this->assertEquals(0, $service->check());
        $this->assertEquals(1, DatabaseNotification::where('notifiable_id', $u->id)->count());
    }

    public function test_processes_all_model_types(): void
    {
        $u = $this->user();
        Domain::factory()->create(['user_id' => $u->id, 'name' => 'd', 'expiry_date' => Carbon::today()->addDays(5), 'status' => 'active']);
        Hosting::factory()->create(['user_id' => $u->id, 'name' => 'h', 'expiry_date' => Carbon::today()->addDays(5), 'status' => 'active']);
        Vps::factory()->create(['user_id' => $u->id, 'name' => 'v', 'expiry_date' => Carbon::today()->addDays(5), 'status' => 'active']);
        Voip::factory()->create(['user_id' => $u->id, 'name' => 'o', 'expiry_date' => Carbon::today()->addDays(5), 'status' => 'active']);
        ServiceProvider::factory()->create(['user_id' => $u->id, 'name' => 's', 'expiry_date' => Carbon::today()->addDays(5), 'status' => 'active']);
        DomainEmail::factory()->create(['user_id' => $u->id, 'email' => 'test@example.com', 'expiry_date' => Carbon::today()->addDays(5), 'status' => 'active']);
        OtherService::factory()->create(['user_id' => $u->id, 'name' => 'o2', 'expiry_date' => Carbon::today()->addDays(5), 'status' => 'active']);
        ExpiryTracker::factory()->create(['user_id' => $u->id, 'name' => 'e', 'expiry_date' => Carbon::today()->addDays(5), 'status' => 'active']);

        $sent = app(ExpiryNotificationService::class)->check();

        $this->assertEquals(8, $sent);
        $this->assertEquals(8, DatabaseNotification::where('notifiable_id', $u->id)->count());
    }

    public function test_skips_non_active_items(): void
    {
        $u = $this->user();
        Domain::factory()->create(['user_id' => $u->id, 'name' => 'expired_domain', 'expiry_date' => Carbon::today()->addDays(5), 'status' => 'expired']);

        $sent = app(ExpiryNotificationService::class)->check();

        $this->assertEquals(0, $sent);
    }

    public function test_command_output(): void
    {
        $u = $this->user();
        Domain::factory()->create(['user_id' => $u->id, 'name' => 'example.com', 'expiry_date' => Carbon::today()->addDays(10), 'status' => 'active']);

        $this->artisan('expiry:check')
            ->expectsOutput('Checking for expiring items...')
            ->expectsOutput('Done. 1 expiry notification(s) sent.')
            ->assertSuccessful();
    }

    public function test_notification_stores_correct_data(): void
    {
        $u = $this->user();
        $domain = Domain::factory()->create(['user_id' => $u->id, 'name' => 'example.com', 'expiry_date' => Carbon::today()->addDays(7), 'status' => 'active']);

        app(ExpiryNotificationService::class)->check();

        $n = DatabaseNotification::where('notifiable_id', $u->id)->first();
        $d = is_array($n->data) ? $n->data : json_decode($n->data, true);

        $this->assertEquals('expiring_soon', $d['type']);
        $this->assertEquals(Domain::class, $d['item_type']);
        $this->assertEquals($domain->id, $d['item_id']);
        $this->assertEquals('example.com', $d['name']);
        $this->assertEquals('Domain', $d['entity_type']);
        $this->assertEquals(7, $d['days_remaining']);
        $this->assertEquals('7_days', $d['threshold']);
    }

    public function test_respects_different_thresholds(): void
    {
        $u = $this->user();
        Domain::factory()->create(['user_id' => $u->id, 'name' => 'd1', 'expiry_date' => Carbon::today()->addDays(20), 'status' => 'active']);
        Domain::factory()->create(['user_id' => $u->id, 'name' => 'd2', 'expiry_date' => Carbon::today()->addDays(5), 'status' => 'active']);

        $sent = app(ExpiryNotificationService::class)->check();

        $this->assertEquals(2, $sent);

        $notifications = DatabaseNotification::where('notifiable_id', $u->id)->get();
        $thresholds = $notifications->map(fn($n) => (is_array($n->data) ? $n->data : json_decode($n->data, true))['threshold'])->sort()->values();
        $this->assertEquals(['30_days', '7_days'], $thresholds->toArray());
    }
}
