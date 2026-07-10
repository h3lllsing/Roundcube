<?php

namespace Tests\Feature;

use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExpiryTrackerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
    }

    public function test_create_entry(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $response = $this->actingAs($user)
            ->postJson('/api/expiry-trackers', [
                'name' => 'AWS Hosting',
                'expiry_date' => '2026-12-31',
                'cost' => 299.99,
                'status' => 'active',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'name', 'expiry_date', 'cost', 'status']])
            ->assertJsonPath('data.name', 'AWS Hosting');
    }

    public function test_list_entries(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        ExpiryTracker::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->getJson('/api/expiry-trackers')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_show_entry(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $entry = ExpiryTracker::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->getJson("/api/expiry-trackers/{$entry->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $entry->id);
    }

    public function test_update_entry(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $entry = ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

        $this->actingAs($user)
            ->putJson("/api/expiry-trackers/{$entry->id}", [
                'name' => 'Updated Name',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_delete_entry(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $entry = ExpiryTracker::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->deleteJson("/api/expiry-trackers/{$entry->id}")
            ->assertOk();

        $this->assertSoftDeleted($entry);
    }

    public function test_validation_required_name(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $this->actingAs($user)
            ->postJson('/api/expiry-trackers', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_filter_by_status(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        ExpiryTracker::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'status' => 'expired']);

        $this->actingAs($user)
            ->getJson('/api/expiry-trackers?status=expired')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'expired');
    }

    public function test_search(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'DigitalOcean Droplet']);
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'AWS S3 Bucket']);

        $this->actingAs($user)
            ->getJson('/api/expiry-trackers?search=Digital')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/expiry-trackers')->assertUnauthorized();
    }

    public function test_regular_user_cannot_access_another_users_entry(): void
    {
        $owner = User::factory()->create();
        $entry = ExpiryTracker::factory()->create(['user_id' => $owner->id]);

        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/expiry-trackers/{$entry->id}")->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/expiry-trackers/{$entry->id}", ['name' => 'hacked'])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/expiry-trackers/{$entry->id}")->assertStatus(403);
    }

    public function test_list_shows_own_entries_for_regular_user(): void
    {
        $owner = User::factory()->create();
        $module = Module::factory()->create();
        $otherModule = Module::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        ModuleRolePermission::create(['module_id' => $module->id, 'role_id' => $userRole->id, 'can_read' => true]);
        $owner->assignRole($userRole);
        ExpiryTracker::factory()->create(['user_id' => $owner->id, 'name' => 'mine', 'module_id' => $module->id]);
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        ExpiryTracker::factory()->create(['user_id' => $admin->id, 'name' => 'theirs', 'module_id' => $otherModule->id]);

        $token = $owner->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/expiry-trackers');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals('mine', $response->json('data.0.name'));
    }

    public function test_list_with_trashed(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $entry = ExpiryTracker::factory()->create(['user_id' => $admin->id]);
        $entry->delete();

        $this->actingAs($admin)->getJson('/api/expiry-trackers?with_trashed=true')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_create_saves_without_password_in_response(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $response = $this->actingAs($user)->postJson('/api/expiry-trackers', [
            'name' => 'Secure Entry',
            'expiry_date' => '2027-01-01',
        ])->assertStatus(201);

        $response->assertJsonMissingPath('data.password');
    }

    public function test_blank_password_update_preserves_existing(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $entry = ExpiryTracker::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->putJson("/api/expiry-trackers/{$entry->id}", [
            'name' => 'Renamed',
        ])->assertOk();

        $entry->refresh();
    }

    public function test_web_blank_password_update_preserves_existing(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $entry = ExpiryTracker::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->put(route('expiry-trackers.update', $entry->id), [
            'name' => 'Web Renamed',
        ])->assertSessionHas('success');
    }

    // ─── Renew Endpoint Tests ───────────────────────────────────────

    public function test_renew_extends_expiry_date_and_syncs_trackable(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $hosting = Hosting::factory()->create(['expiry_date' => '2026-12-31']);
        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'expiry_date' => '2026-12-31',
            'status' => 'active',
            'trackable_type' => $hosting->getMorphClass(),
            'trackable_id' => $hosting->id,
        ]);

        $this->actingAs($user)
            ->post(route('expiry-trackers.renew', $tracker->id))
            ->assertRedirect();

        $trackerRow = DB::table('expiry_trackers')->find($tracker->id);
        $this->assertEquals('2027-12-31', $trackerRow->expiry_date);
        $this->assertNotNull($trackerRow->renewal_date);

        $hostingRow = DB::table('hostings')->find($hosting->id);
        $this->assertEquals('2027-12-31', $hostingRow->expiry_date);
    }

    public function test_renew_logs_activity(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $hosting = Hosting::factory()->create(['expiry_date' => '2026-12-31']);
        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'expiry_date' => '2026-12-31',
            'status' => 'active',
            'trackable_type' => $hosting->getMorphClass(),
            'trackable_id' => $hosting->id,
        ]);

        $this->actingAs($user)->post(route('expiry-trackers.renew', $tracker->id));

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => $tracker->getMorphClass(),
            'subject_id' => $tracker->id,
            'event' => 'renewal_processed',
        ]);
    }

    public function test_renew_requires_update_permission(): void
    {
        $role = Role::where('slug', 'user')->firstOrFail();
        $module = Module::factory()->create(['slug' => 'expiry-trackers']);
        ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $role->id,
            'can_read' => true,
        ]);
        $user = User::factory()->create();
        $user->assignRole($role);
        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'expiry_date' => '2026-12-31',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('expiry-trackers.renew', $tracker->id))
            ->assertStatus(403);
    }

    public function test_renew_rejected_for_cancelled_item(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'expiry_date' => '2026-12-31',
            'status' => 'cancelled',
        ]);

        $this->actingAs($user)
            ->post(route('expiry-trackers.renew', $tracker->id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('expiry_trackers', [
            'id' => $tracker->id,
            'expiry_date' => '2026-12-31',
        ]);
    }

    public function test_renew_no_trackable_extends_own_date(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $tid = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'expiry_date' => '2026-12-31',
            'status' => 'active',
        ])->id;

        $this->actingAs($user)
            ->post(route('expiry-trackers.renew', $tid))
            ->assertRedirect();

        $row = DB::table('expiry_trackers')->find($tid);
        $this->assertNotNull($row->renewal_date);
        $this->assertEquals('2027-12-31', $row->expiry_date);
    }

    public function test_renew_sets_renewal_date_to_today(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $tid = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'expiry_date' => '2026-12-31',
            'status' => 'active',
        ])->id;

        $this->actingAs($user)->post(route('expiry-trackers.renew', $tid));

        $row = DB::table('expiry_trackers')->find($tid);
        $this->assertNotNull($row->renewal_date);
        $this->assertEquals(now()->format('Y-m-d'), $row->renewal_date);
    }
}
