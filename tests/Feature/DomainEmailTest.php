<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DomainEmail;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainEmailTest extends TestCase
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
        $this->actingAs($this->superAdmin())->postJson('/api/domain-emails', ['email' => 'admin@example.com', 'provider' => 'Google Workspace'])
            ->assertStatus(201)->assertJsonPath('data.email', 'admin@example.com');
    }

    public function test_list(): void
    {
        $u = $this->superAdmin();
        DomainEmail::factory()->count(2)->create(['user_id' => $u->id]);
        $this->actingAs($u)->getJson('/api/domain-emails')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_show(): void
    {
        $u = $this->superAdmin();
        $d = DomainEmail::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->getJson("/api/domain-emails/{$d->id}")->assertOk()->assertJsonPath('data.id', $d->id);
    }

    public function test_update(): void
    {
        $u = $this->superAdmin();
        $d = DomainEmail::factory()->create(['user_id' => $u->id, 'email' => 'old@example.com']);
        $this->actingAs($u)->putJson("/api/domain-emails/{$d->id}", ['email' => 'new@example.com'])->assertOk()->assertJsonPath('data.email', 'new@example.com');
    }

    public function test_delete(): void
    {
        $u = $this->superAdmin();
        $d = DomainEmail::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->deleteJson("/api/domain-emails/{$d->id}")->assertOk();
        $this->assertSoftDeleted($d);
    }

    public function test_validation(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/domain-emails', [])->assertStatus(422);
    }

    public function test_search(): void
    {
        $u = $this->superAdmin();
        DomainEmail::factory()->create(['user_id' => $u->id, 'email' => 'admin@example.com']);
        DomainEmail::factory()->create(['user_id' => $u->id, 'email' => 'info@example.com']);
        $this->actingAs($u)->getJson('/api/domain-emails?search=admin')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_auth(): void
    {
        $this->getJson('/api/domain-emails')->assertUnauthorized();
    }

    public function test_regular_user_cannot_access_another_users_domain_email(): void
    {
        $owner = User::factory()->create();
        $de = DomainEmail::factory()->create(['user_id' => $owner->id]);

        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/domain-emails/{$de->id}")->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/domain-emails/{$de->id}", ['email' => 'hacked@evil.com'])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/domain-emails/{$de->id}")->assertStatus(403);
    }
}
