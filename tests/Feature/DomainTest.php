<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        return $user;
    }

    public function test_create_domain(): void
    {
        $this->actingAs($this->superAdmin())
            ->postJson('/api/domains', ['name' => 'example.com', 'registrar' => 'GoDaddy', 'expiry_date' => '2027-06-01', 'cost' => 12.99])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'example.com');
    }

    public function test_list_domains(): void
    {
        $user = $this->superAdmin();
        Domain::factory()->count(3)->create(['user_id' => $user->id]);
        $this->actingAs($user)->getJson('/api/domains')->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_show_domain(): void
    {
        $user = $this->superAdmin();
        $d = Domain::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user)->getJson("/api/domains/{$d->id}")->assertOk()->assertJsonPath('data.id', $d->id);
    }

    public function test_update_domain(): void
    {
        $user = $this->superAdmin();
        $d = Domain::factory()->create(['user_id' => $user->id, 'name' => 'old.com']);
        $this->actingAs($user)->putJson("/api/domains/{$d->id}", ['name' => 'new.com'])->assertOk()->assertJsonPath('data.name', 'new.com');
    }

    public function test_delete_domain(): void
    {
        $user = $this->superAdmin();
        $d = Domain::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user)->deleteJson("/api/domains/{$d->id}")->assertOk();
        $this->assertSoftDeleted($d);
    }

    public function test_validation(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/domains', [])->assertStatus(422)->assertJsonValidationErrors('name');
    }

    public function test_filter_by_status(): void
    {
        $user = $this->superAdmin();
        Domain::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        Domain::factory()->create(['user_id' => $user->id, 'status' => 'expired']);
        $this->actingAs($user)->getJson('/api/domains?status=expired')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_search(): void
    {
        $user = $this->superAdmin();
        Domain::factory()->create(['user_id' => $user->id, 'name' => 'google.com', 'registrar' => 'Namecheap']);
        Domain::factory()->create(['user_id' => $user->id, 'name' => 'aws.com', 'registrar' => 'Route53']);
        $this->actingAs($user)->getJson('/api/domains?search=google')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/domains')->assertUnauthorized();
    }

    public function test_regular_user_cannot_access_another_users_domain(): void
    {
        $owner = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $owner->id]);

        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/domains/{$domain->id}")
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/domains/{$domain->id}", ['name' => 'hacked.com'])
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/domains/{$domain->id}")
            ->assertStatus(403);
    }
}
