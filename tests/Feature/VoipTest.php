<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voip;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoipTest extends TestCase
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
        $this->actingAs($this->superAdmin())->postJson('/api/voip', ['name' => 'Office Line', 'provider' => 'Twilio'])
            ->assertStatus(201)->assertJsonPath('data.name', 'Office Line');
    }

    public function test_list(): void
    {
        $u = $this->superAdmin();
        Voip::factory()->count(2)->create(['user_id' => $u->id]);
        $this->actingAs($u)->getJson('/api/voip')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_show(): void
    {
        $u = $this->superAdmin();
        $v = Voip::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->getJson("/api/voip/{$v->id}")->assertOk()->assertJsonPath('data.id', $v->id);
    }

    public function test_update(): void
    {
        $u = $this->superAdmin();
        $v = Voip::factory()->create(['user_id' => $u->id, 'name' => 'Old']);
        $this->actingAs($u)->putJson("/api/voip/{$v->id}", ['name' => 'New', 'phone_number' => '+1234567890'])->assertOk()->assertJsonPath('data.name', 'New');
    }

    public function test_delete(): void
    {
        $u = $this->superAdmin();
        $v = Voip::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->deleteJson("/api/voip/{$v->id}")->assertOk();
        $this->assertSoftDeleted($v);
    }

    public function test_validation(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/voip', [])->assertStatus(422);
    }

    public function test_search(): void
    {
        $u = $this->superAdmin();
        Voip::factory()->create(['user_id' => $u->id, 'name' => 'Main Line']);
        Voip::factory()->create(['user_id' => $u->id, 'name' => 'Fax Line']);
        $this->actingAs($u)->getJson('/api/voip?search=Main')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_auth(): void
    {
        $this->getJson('/api/voip')->assertUnauthorized();
    }

    public function test_regular_user_cannot_access_another_users_voip(): void
    {
        $owner = User::factory()->create();
        $voip = Voip::factory()->create(['user_id' => $owner->id]);

        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/voip/{$voip->id}")->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/voip/{$voip->id}", ['name' => 'hacked', 'phone_number' => '+1111111111'])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/voip/{$voip->id}")->assertStatus(403);
    }
}
