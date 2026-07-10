<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\ExpiryTracker;
use App\Models\User;
use App\Services\RenewalSyncService;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RenewalCenterUITest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
    }

    public function test_index_page_shows_renewals_title(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $this->actingAs($user)
            ->get(route('expiry-trackers.index'))
            ->assertStatus(200)
            ->assertSee('Renewals')
            ->assertSee('Add Standalone Renewal Item');
    }

    public function test_old_route_still_works(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $this->actingAs($user)
            ->get('/expiry-trackers')
            ->assertStatus(200);
    }

    public function test_linked_renewal_shows_auto_synced_badge(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $domain = Domain::factory()->create([
            'user_id' => $user->id,
            'name' => 'linked-service.com',
            'expiry_date' => '2027-06-15',
            'status' => 'active',
        ]);
        app(RenewalSyncService::class)->sync($domain);

        $this->actingAs($user)
            ->get(route('expiry-trackers.index'))
            ->assertStatus(200)
            ->assertSee('linked-service.com')
            ->assertSee('Auto-synced')
            ->assertSee('Domain');
    }

    public function test_standalone_renewal_shows_standalone_badge(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'name' => 'Manual Item',
            'expiry_date' => '2027-06-15',
            'trackable_type' => null,
            'trackable_id' => null,
        ]);

        $this->actingAs($user)
            ->get(route('expiry-trackers.index'))
            ->assertStatus(200)
            ->assertSee('Manual Item')
            ->assertSee('Standalone');
    }

    public function test_linked_renewal_edit_page_disables_name_and_expiry_date(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $domain = Domain::factory()->create([
            'user_id' => $user->id,
            'name' => 'linked-service.com',
            'expiry_date' => '2027-06-15',
            'status' => 'active',
        ]);
        app(RenewalSyncService::class)->sync($domain);

        $tracker = ExpiryTracker::where('trackable_type', 'domain')
            ->where('trackable_id', $domain->id)
            ->firstOrFail();

        $response = $this->actingAs($user)
            ->get(route('expiry-trackers.edit', $tracker->id))
            ->assertStatus(200)
            ->assertSee('This renewal is linked to a source service');

        $this->assertStringContainsString(
            'disabled',
            $response->getContent(),
            'Name field should be disabled for linked renewals'
        );
    }

    public function test_standalone_renewal_edit_page_keeps_fields_editable(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'name' => 'Manual Item',
            'expiry_date' => '2027-06-15',
            'trackable_type' => null,
            'trackable_id' => null,
        ]);

        $this->actingAs($user)
            ->get(route('expiry-trackers.edit', $tracker->id))
            ->assertStatus(200)
            ->assertSee('Edit Renewal Item')
            ->assertDontSee('This renewal is linked to a source service');
    }

    public function test_linked_renewal_show_page_shows_source_section(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $domain = Domain::factory()->create([
            'user_id' => $user->id,
            'name' => 'linked-service.com',
            'expiry_date' => '2027-06-15',
            'status' => 'active',
        ]);
        app(RenewalSyncService::class)->sync($domain);

        $tracker = ExpiryTracker::where('trackable_type', 'domain')
            ->where('trackable_id', $domain->id)
            ->firstOrFail();

        $this->actingAs($user)
            ->get(route('expiry-trackers.show', $tracker->id))
            ->assertStatus(200)
            ->assertSee('Linked')
            ->assertSee('View Source Service')
            ->assertSee('Domain');
    }

    public function test_notification_config_still_saves_for_linked_renewal(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $domain = Domain::factory()->create([
            'user_id' => $user->id,
            'name' => 'linked-service.com',
            'expiry_date' => '2027-06-15',
            'status' => 'active',
        ]);
        app(RenewalSyncService::class)->sync($domain);

        $tracker = ExpiryTracker::where('trackable_type', 'domain')
            ->where('trackable_id', $domain->id)
            ->firstOrFail();

        $this->actingAs($user)
            ->put(route('expiry-trackers.update', $tracker->id), [
                'email_notifications_enabled' => '1',
                'notify_assigned_user' => '1',
            ])
            ->assertSessionHas('success');

        $tracker->refresh();
        $this->assertTrue((bool)$tracker->email_notifications_enabled);
    }

    public function test_index_page_has_sync_type_filter(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $this->actingAs($user)
            ->get(route('expiry-trackers.index'))
            ->assertStatus(200)
            ->assertSee('All')
            ->assertSee('Linked')
            ->assertSee('Standalone');
    }

    public function test_sync_type_filter_linked(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $domain = Domain::factory()->create([
            'user_id' => $user->id,
            'name' => 'linked-service.com',
            'expiry_date' => '2027-06-15',
            'status' => 'active',
        ]);
        app(RenewalSyncService::class)->sync($domain);

        ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'name' => 'Manual Item',
            'expiry_date' => '2027-06-15',
            'trackable_type' => null,
            'trackable_id' => null,
        ]);

        $this->actingAs($user)
            ->get(route('expiry-trackers.index', ['sync_type' => 'linked']))
            ->assertStatus(200)
            ->assertSee('linked-service.com')
            ->assertDontSee('Manual Item');
    }

    public function test_sync_type_filter_standalone(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $domain = Domain::factory()->create([
            'user_id' => $user->id,
            'name' => 'linked-service.com',
            'expiry_date' => '2027-06-15',
            'status' => 'active',
        ]);
        app(RenewalSyncService::class)->sync($domain);

        ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'name' => 'Manual Item',
            'expiry_date' => '2027-06-15',
            'trackable_type' => null,
            'trackable_id' => null,
        ]);

        $this->actingAs($user)
            ->get(route('expiry-trackers.index', ['sync_type' => 'standalone']))
            ->assertStatus(200)
            ->assertDontSee('linked-service.com')
            ->assertSee('Manual Item');
    }

    public function test_source_type_filter(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $domain = Domain::factory()->create([
            'user_id' => $user->id,
            'name' => 'domain-service.com',
            'expiry_date' => '2027-06-15',
            'status' => 'active',
        ]);
        app(RenewalSyncService::class)->sync($domain);

        $this->actingAs($user)
            ->get(route('expiry-trackers.index', ['source_type' => 'domain']))
            ->assertStatus(200)
            ->assertSee('domain-service.com');
    }

    public function test_create_page_shows_standalone_title(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $this->actingAs($user)
            ->get(route('expiry-trackers.create'))
            ->assertStatus(200)
            ->assertSee('Add Standalone Renewal Item');
    }
}
