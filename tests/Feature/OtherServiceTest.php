<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\OtherService;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtherServiceTest extends TestCase
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
        $this->actingAs($this->superAdmin())->postJson('/api/other-services', ['name' => 'Sentry Monitoring', 'service_type' => 'monitoring'])
            ->assertStatus(201)->assertJsonPath('data.name', 'Sentry Monitoring');
    }

    public function test_list(): void
    {
        $u = $this->superAdmin();
        OtherService::factory()->count(2)->create(['user_id' => $u->id]);
        $this->actingAs($u)->getJson('/api/other-services')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_show(): void
    {
        $u = $this->superAdmin();
        $o = OtherService::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->getJson("/api/other-services/{$o->id}")->assertOk()->assertJsonPath('data.id', $o->id);
    }

    public function test_update(): void
    {
        $u = $this->superAdmin();
        $o = OtherService::factory()->create(['user_id' => $u->id, 'name' => 'Old']);
        $this->actingAs($u)->putJson("/api/other-services/{$o->id}", ['name' => 'New', 'service_type' => 'analytics'])->assertOk()->assertJsonPath('data.name', 'New');
    }

    public function test_delete(): void
    {
        $u = $this->superAdmin();
        $o = OtherService::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->deleteJson("/api/other-services/{$o->id}")->assertOk();
        $this->assertSoftDeleted($o);
    }

    public function test_validation(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/other-services', [])->assertStatus(422);
    }

    public function test_search(): void
    {
        $u = $this->superAdmin();
        OtherService::factory()->create(['user_id' => $u->id, 'name' => 'Sentry']);
        OtherService::factory()->create(['user_id' => $u->id, 'name' => 'Datadog']);
        $this->actingAs($u)->getJson('/api/other-services?search=Sentry')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_auth(): void
    {
        $this->getJson('/api/other-services')->assertUnauthorized();
    }

    public function test_regular_user_cannot_access_another_users_other_service(): void
    {
        $owner = User::factory()->create();
        $os = OtherService::factory()->create(['user_id' => $owner->id]);

        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/other-services/{$os->id}")->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/other-services/{$os->id}", ['name' => 'hacked', 'service_type' => 'monitoring'])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/other-services/{$os->id}")->assertStatus(403);
    }
}
