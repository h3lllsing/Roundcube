<?php

namespace Tests\Feature;

use App\Models\DomainEmail;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainEmailTest extends TestCase
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

    public function test_create(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/domain-emails', ['email' => 'admin@example.com'])
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

    public function test_list_shows_own_emails_for_regular_user(): void
    {
        $owner = User::factory()->create();
        $module = Module::factory()->create();
        $otherModule = Module::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        ModuleRolePermission::create(['module_id' => $module->id, 'role_id' => $userRole->id, 'can_read' => true]);
        $owner->assignRole($userRole);
        DomainEmail::factory()->create(['user_id' => $owner->id, 'email' => 'mine@example.com', 'module_id' => $module->id]);
        $admin = $this->superAdmin();
        DomainEmail::factory()->create(['user_id' => $admin->id, 'email' => 'theirs@example.com', 'module_id' => $otherModule->id]);

        $token = $owner->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/domain-emails');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals('mine@example.com', $response->json('data.0.email'));
    }

    public function test_list_with_trashed(): void
    {
        $admin = $this->superAdmin();
        $de = DomainEmail::factory()->create(['user_id' => $admin->id]);
        $de->delete();

        $this->actingAs($admin)->getJson('/api/domain-emails?with_trashed=true')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_create_with_password_saves_encrypted(): void
    {
        $u = $this->superAdmin();
        $this->actingAs($u)->postJson('/api/domain-emails', [
            'email' => 'user@example.com',
            'password' => 'email_secret!',
        ])->assertStatus(201);

        $de = DomainEmail::where('email', 'user@example.com')->first();
        $this->assertNotNull($de);
        $this->assertNotEquals('email_secret!', $de->getRawOriginal('password'));
        $this->assertEquals('email_secret!', $de->password);
    }

    public function test_blank_password_update_preserves_existing(): void
    {
        $u = $this->superAdmin();
        $de = DomainEmail::factory()->create(['user_id' => $u->id, 'password' => 'original_pw']);

        $this->actingAs($u)->putJson("/api/domain-emails/{$de->id}", [
            'email' => 'updated@example.com',
        ])->assertOk();

        $this->assertEquals('original_pw', $de->fresh()->password);
    }

    public function test_password_not_visible_in_listing_response(): void
    {
        $u = $this->superAdmin();
        $de = DomainEmail::factory()->create(['user_id' => $u->id, 'password' => 'invisible_pw']);
        $this->actingAs($u)->getJson('/api/domain-emails')
            ->assertJsonMissingPath('data.0.password');
    }
}
