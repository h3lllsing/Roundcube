<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        return $user;
    }

    public function test_create_domain(): void
    {
        $provider = \App\Models\ServiceProvider::factory()->create();
        $this->actingAs($this->superAdmin())
            ->postJson('/api/domains', ['name' => 'example.com', 'service_provider_id' => $provider->id, 'expiry_date' => '2027-06-01', 'cost' => 12.99])
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
        Domain::factory()->create(['user_id' => $user->id, 'name' => 'google.com', 'service_provider_id' => \App\Models\ServiceProvider::factory()]);
        Domain::factory()->create(['user_id' => $user->id, 'name' => 'aws.com', 'service_provider_id' => \App\Models\ServiceProvider::factory()]);
        $this->actingAs($user)->getJson('/api/domains?search=google')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_list_shows_own_domains_for_regular_user(): void
    {
        $owner = User::factory()->create();
        $module = Module::factory()->create();
        $otherModule = Module::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        ModuleRolePermission::create(['module_id' => $module->id, 'role_id' => $userRole->id, 'can_read' => true]);
        $owner->assignRole($userRole);
        Domain::factory()->create(['user_id' => $owner->id, 'name' => 'mine.com', 'module_id' => $module->id]);
        $admin = $this->superAdmin();
        Domain::factory()->create(['user_id' => $admin->id, 'name' => 'theirs.com', 'module_id' => $otherModule->id]);

        $token = $owner->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/domains');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('mine.com', $response->json('data.0.name'));
    }

    public function test_list_with_trashed(): void
    {
        $user = $this->superAdmin();
        $domain = Domain::factory()->create(['user_id' => $user->id]);
        $domain->delete();

        $this->actingAs($user)
            ->getJson('/api/domains?with_trashed=true')
            ->assertOk()
            ->assertJsonCount(1, 'data');
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

    public function test_create_with_cloudflare_status(): void
    {
        $provider = \App\Models\ServiceProvider::factory()->create();
        $this->actingAs($this->superAdmin())
            ->postJson('/api/domains', [
                'name' => 'example.com', 'service_provider_id' => $provider->id,
                'expiry_date' => '2027-06-01', 'cost' => 12.99,
                'cloudflare_status' => 'enabled',
            ])->assertStatus(201)->assertJsonPath('data.cloudflare_status', 'enabled');
    }

    public function test_invalid_cloudflare_status_fails(): void
    {
        $provider = \App\Models\ServiceProvider::factory()->create();
        $this->actingAs($this->superAdmin())
            ->postJson('/api/domains', [
                'name' => 'example.com', 'service_provider_id' => $provider->id,
                'expiry_date' => '2027-06-01', 'cost' => 12.99,
                'cloudflare_status' => 'invalid_status',
            ])->assertStatus(422)->assertJsonValidationErrors('cloudflare_status');
    }

    public function test_search_by_cloudflare_status(): void
    {
        $user = $this->superAdmin();
        Domain::factory()->create(['user_id' => $user->id, 'name' => 'site1.com', 'cloudflare_status' => 'enabled']);
        Domain::factory()->create(['user_id' => $user->id, 'name' => 'site2.com', 'cloudflare_status' => 'disabled']);
        $this->actingAs($user)->getJson('/api/domains?search=enabled')->assertOk()->assertJsonCount(1, 'data');
    }
}
