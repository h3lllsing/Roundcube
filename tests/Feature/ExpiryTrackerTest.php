<?php

namespace Tests\Feature;

use App\Models\ExpiryTracker;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpiryTrackerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
    }

    public function test_create_entry(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $response = $this->actingAs($user)
            ->postJson('/api/expiry-trackers', [
                'name' => 'AWS Hosting',
                'provider' => 'Amazon',
                'expiry_date' => '2026-12-31',
                'cost' => 299.99,
                'status' => 'active',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'name', 'provider', 'expiry_date', 'cost', 'status']])
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
}
