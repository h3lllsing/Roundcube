<?php

namespace Tests\Feature;

use App\Models\Hosting;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HostingTest extends TestCase
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
        $this->actingAs($this->superAdmin())->postJson('/api/hostings', ['name' => 'My Site'])
            ->assertStatus(201)->assertJsonPath('data.name', 'My Site');
    }

    public function test_list(): void
    {
        $u = $this->superAdmin();
        Hosting::factory()->count(2)->create(['user_id' => $u->id]);
        $this->actingAs($u)->getJson('/api/hostings')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_show(): void
    {
        $u = $this->superAdmin();
        $h = Hosting::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->getJson("/api/hostings/{$h->id}")->assertOk()->assertJsonPath('data.id', $h->id);
    }

    public function test_update(): void
    {
        $u = $this->superAdmin();
        $h = Hosting::factory()->create(['user_id' => $u->id, 'name' => 'Old']);
        $this->actingAs($u)->putJson("/api/hostings/{$h->id}", ['name' => 'New'])->assertOk()->assertJsonPath('data.name', 'New');
    }

    public function test_delete(): void
    {
        $u = $this->superAdmin();
        $h = Hosting::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->deleteJson("/api/hostings/{$h->id}")->assertOk();
        $this->assertSoftDeleted($h);
    }

    public function test_validation(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/hostings', [])->assertStatus(422);
    }

    public function test_filter_by_status(): void
    {
        $u = $this->superAdmin();
        Hosting::factory()->create(['user_id' => $u->id, 'status' => 'active']);
        Hosting::factory()->create(['user_id' => $u->id, 'status' => 'expired']);
        $this->actingAs($u)->getJson('/api/hostings?status=expired')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_auth(): void
    {
        $this->getJson('/api/hostings')->assertUnauthorized();
    }

    public function test_regular_user_cannot_access_another_users_hosting(): void
    {
        $owner = User::factory()->create();
        $hosting = Hosting::factory()->create(['user_id' => $owner->id]);

        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/hostings/{$hosting->id}")->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/hostings/{$hosting->id}", ['name' => 'hacked'])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/hostings/{$hosting->id}")->assertStatus(403);
    }
}
