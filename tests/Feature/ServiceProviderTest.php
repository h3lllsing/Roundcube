<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ServiceProvider;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceProviderTest extends TestCase
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

    public function test_create(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/service-providers', ['name' => 'ISP Co', 'type' => 'internet'])
            ->assertStatus(201)->assertJsonPath('data.name', 'ISP Co');
    }

    public function test_list(): void
    {
        $u = $this->superAdmin();
        ServiceProvider::factory()->count(2)->create(['user_id' => $u->id]);
        $this->actingAs($u)->getJson('/api/service-providers')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_show(): void
    {
        $u = $this->superAdmin();
        $s = ServiceProvider::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->getJson("/api/service-providers/{$s->id}")->assertOk()->assertJsonPath('data.id', $s->id);
    }

    public function test_update(): void
    {
        $u = $this->superAdmin();
        $s = ServiceProvider::factory()->create(['user_id' => $u->id, 'name' => 'Old']);
        $this->actingAs($u)->putJson("/api/service-providers/{$s->id}", ['name' => 'New', 'type' => 'hosting'])->assertOk()->assertJsonPath('data.name', 'New');
    }

    public function test_delete(): void
    {
        $u = $this->superAdmin();
        $s = ServiceProvider::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->deleteJson("/api/service-providers/{$s->id}")->assertOk();
        $this->assertSoftDeleted($s);
    }

    public function test_validation(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/service-providers', [])->assertStatus(422);
    }

    public function test_search(): void
    {
        $u = $this->superAdmin();
        ServiceProvider::factory()->create(['user_id' => $u->id, 'name' => 'Fast ISP']);
        ServiceProvider::factory()->create(['user_id' => $u->id, 'name' => 'Slow ISP']);
        $this->actingAs($u)->getJson('/api/service-providers?search=Fast')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_auth(): void
    {
        $this->getJson('/api/service-providers')->assertUnauthorized();
    }

    public function test_regular_user_cannot_access_another_users_service_provider(): void
    {
        $owner = User::factory()->create();
        $sp = ServiceProvider::factory()->create(['user_id' => $owner->id]);

        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/service-providers/{$sp->id}")->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/service-providers/{$sp->id}", ['name' => 'hacked', 'type' => 'hosting'])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/service-providers/{$sp->id}")->assertStatus(403);
    }
}
