<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vps;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VpsTest extends TestCase
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
        $this->actingAs($this->superAdmin())->postJson('/api/vps', ['name' => 'Web Server', 'provider' => 'DigitalOcean'])
            ->assertStatus(201)->assertJsonPath('data.name', 'Web Server');
    }

    public function test_list(): void
    {
        $u = $this->superAdmin();
        Vps::factory()->count(2)->create(['user_id' => $u->id]);
        $this->actingAs($u)->getJson('/api/vps')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_show(): void
    {
        $u = $this->superAdmin();
        $v = Vps::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->getJson("/api/vps/{$v->id}")->assertOk()->assertJsonPath('data.id', $v->id);
    }

    public function test_update(): void
    {
        $u = $this->superAdmin();
        $v = Vps::factory()->create(['user_id' => $u->id, 'name' => 'Old']);
        $this->actingAs($u)->putJson("/api/vps/{$v->id}", ['name' => 'New', 'ip_address' => '10.0.0.1'])->assertOk()->assertJsonPath('data.name', 'New');
    }

    public function test_delete(): void
    {
        $u = $this->superAdmin();
        $v = Vps::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->deleteJson("/api/vps/{$v->id}")->assertOk();
        $this->assertSoftDeleted($v);
    }

    public function test_validation(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/vps', [])->assertStatus(422);
    }

    public function test_search(): void
    {
        $u = $this->superAdmin();
        Vps::factory()->create(['user_id' => $u->id, 'name' => 'Web Server']);
        Vps::factory()->create(['user_id' => $u->id, 'name' => 'DB Server']);
        $this->actingAs($u)->getJson('/api/vps?search=Web')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_auth(): void
    {
        $this->getJson('/api/vps')->assertUnauthorized();
    }

    public function test_regular_user_cannot_access_another_users_vps(): void
    {
        $owner = User::factory()->create();
        $vps = Vps::factory()->create(['user_id' => $owner->id]);

        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/vps/{$vps->id}")->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/vps/{$vps->id}", ['name' => 'hacked', 'ip_address' => '10.0.0.1'])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/vps/{$vps->id}")->assertStatus(403);
    }
}
