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
use App\Services\RenewalSyncService;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RenewalSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
    }

    public function test_migration_added_trackable_columns(): void
    {
        $columns = Schema::getColumnListing('expiry_trackers');

        $this->assertContains('trackable_type', $columns);
        $this->assertContains('trackable_id', $columns);
    }

    public function test_morph_map_uses_aliases_not_class_names(): void
    {
        $domain = Domain::factory()->create(['expiry_date' => '2027-01-01', 'status' => 'active']);

        app(RenewalSyncService::class)->sync($domain);

        $tracker = ExpiryTracker::where('trackable_type', 'domain')->first();

        $this->assertNotNull($tracker);
        $this->assertEquals('domain', $tracker->trackable_type);
        $this->assertNotEquals(Domain::class, $tracker->trackable_type);
    }

    public function test_sync_creates_linked_tracker(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create([
            'user_id' => $user->id,
            'name' => 'example.com',
            'expiry_date' => '2027-06-15',
            'status' => 'active',
        ]);

        $tracker = app(RenewalSyncService::class)->sync($domain);

        $this->assertDatabaseHas('expiry_trackers', [
            'id' => $tracker->id,
            'trackable_type' => 'domain',
            'trackable_id' => $domain->id,
            'name' => 'example.com',
            'user_id' => $user->id,
        ]);
    }

    public function test_sync_sets_default_notification_config_on_first_creation(): void
    {
        $domain = Domain::factory()->create(['expiry_date' => '2027-06-15', 'status' => 'active']);

        $tracker = app(RenewalSyncService::class)->sync($domain);

        $this->assertTrue($tracker->email_notifications_enabled);
        $this->assertEquals([30, 15, 7, 1], $tracker->notify_days_before);
        $this->assertFalse($tracker->notify_on_expiry_day);
        $this->assertTrue($tracker->notify_assigned_user);
        $this->assertFalse($tracker->notify_admins);
    }

    public function test_sync_preserves_existing_notification_config(): void
    {
        $domain = Domain::factory()->create(['expiry_date' => '2027-06-15', 'status' => 'active']);

        $tracker = app(RenewalSyncService::class)->sync($domain);
        $tracker->update([
            'email_notifications_enabled' => false,
            'notify_days_before' => [7, 1],
            'notify_admins' => true,
        ]);

        app(RenewalSyncService::class)->sync($domain);

        $tracker->refresh();
        $this->assertFalse($tracker->email_notifications_enabled);
        $this->assertEquals([7, 1], $tracker->notify_days_before);
        $this->assertTrue($tracker->notify_admins);
    }

    public function test_remove_soft_deletes_linked_tracker(): void
    {
        $domain = Domain::factory()->create(['expiry_date' => '2027-06-15', 'status' => 'active']);
        $tracker = app(RenewalSyncService::class)->sync($domain);

        app(RenewalSyncService::class)->remove($domain);

        $this->assertSoftDeleted($tracker);
    }

    public function test_restore_restores_linked_tracker(): void
    {
        $domain = Domain::factory()->create(['expiry_date' => '2027-06-15', 'status' => 'active']);
        $tracker = app(RenewalSyncService::class)->sync($domain);
        app(RenewalSyncService::class)->remove($domain);

        app(RenewalSyncService::class)->restore($domain);

        $this->assertDatabaseHas('expiry_trackers', [
            'id' => $tracker->id,
            'deleted_at' => null,
        ]);
    }

    public function test_standalone_tracker_still_works(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'name' => 'Standalone SSL Cert',
            'expiry_date' => '2027-06-15',
            'status' => 'active',
        ]);

        $this->assertNull($tracker->trackable_type);
        $this->assertNull($tracker->trackable_id);

        $response = $this->actingAs($user)->getJson("/api/expiry-trackers/{$tracker->id}");

        $response->assertOk();
        $response->assertJsonPath('data.name', 'Standalone SSL Cert');
        $response->assertJsonPath('data.trackable_type', null);
        $response->assertJsonPath('data.trackable_id', null);
    }

    public function test_standalone_tracker_expiry_date_is_cast_to_carbon(): void
    {
        $tracker = ExpiryTracker::factory()->create([
            'expiry_date' => '2027-06-15',
            'trackable_type' => null,
            'trackable_id' => null,
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $tracker->expiry_date);
    }

    public function test_linked_tracker_expiry_date_comes_from_source(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create([
            'user_id' => $user->id,
            'name' => 'source-test.com',
            'expiry_date' => '2027-12-31',
            'status' => 'active',
        ]);

        $tracker = app(RenewalSyncService::class)->sync($domain);

        $this->assertEquals('2027-12-31', $tracker->expiry_date->format('Y-m-d'));

        $domain->update(['expiry_date' => '2028-06-15']);

        $tracker = app(RenewalSyncService::class)->sync($domain);

        $this->assertEquals('2028-06-15', $tracker->expiry_date->format('Y-m-d'));
    }

    public function test_backfill_dry_run_does_not_create_records(): void
    {
        Domain::factory()->create(['expiry_date' => '2027-06-15', 'status' => 'active']);
        Hosting::factory()->create(['expiry_date' => '2027-07-01', 'status' => 'active']);

        $this->assertEquals(0, ExpiryTracker::count());

        $this->artisan('expiry:backfill', ['--dry-run' => true, '--force' => true])
            ->assertSuccessful();

        $this->assertEquals(0, ExpiryTracker::count());
    }

    public function test_backfill_creates_records(): void
    {
        Domain::factory()->create(['expiry_date' => '2027-06-15', 'status' => 'active', 'service_provider_id' => null]);
        Hosting::factory()->create(['expiry_date' => '2027-07-01', 'status' => 'active', 'service_provider_id' => null]);
        Vps::factory()->create(['expiry_date' => '2027-08-01', 'status' => 'active', 'service_provider_id' => null]);

        $this->artisan('expiry:backfill', ['--force' => true])
            ->assertSuccessful();

        $this->assertEquals(3, ExpiryTracker::count());
    }

    public function test_backfill_skips_already_linked(): void
    {
        $domain = Domain::factory()->create(['expiry_date' => '2027-06-15', 'status' => 'active', 'service_provider_id' => null]);
        app(RenewalSyncService::class)->sync($domain);

        $this->artisan('expiry:backfill', ['--force' => true])
            ->assertSuccessful();

        $this->assertEquals(1, ExpiryTracker::count());
    }

    public function test_sync_via_domain_controller_store(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $this->actingAs($user)->post('/domains', [
            'name' => 'controller-sync.com',
            'expiry_date' => '2027-06-15',
            'module_id' => null,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('expiry_trackers', [
            'name' => 'controller-sync.com',
            'trackable_type' => 'domain',
        ]);
    }

    public function test_index_query_does_not_n_plus_one(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $domains = Domain::factory()->count(5)->create([
            'user_id' => $user->id,
            'expiry_date' => '2027-06-15',
            'status' => 'active',
        ]);

        foreach ($domains as $domain) {
            app(RenewalSyncService::class)->sync($domain);
        }

        $queries = 0;
        \DB::listen(function () use (&$queries) { $queries++; });

        $this->actingAs($user)->get('/expiry-trackers');

        $this->assertLessThan(15, $queries, 'Index page should not N+1');
    }

    public function test_expiry_notification_service_no_longer_sends_for_expiry_tracker(): void
    {
        $user = User::factory()->create();
        ExpiryTracker::factory()->create([
            'expiry_date' => now()->addDays(5)->toDateString(),
            'status' => 'active',
            'user_id' => $user->id,
            'service_provider_id' => null,
        ]);

        $webhookService = $this->createMock(\App\Services\WebhookService::class);
        $webhookService->method('fire');

        $service = new \App\Services\ExpiryNotificationService($webhookService);
        $sent = $service->check();

        $this->assertEquals(0, $sent, 'ExpiryNotificationService should not send for ExpiryTracker entries');
    }
}
