<?php

namespace Tests\Feature;

use App\Models\Hosting;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HostingTest extends TestCase
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

    public function test_list_shows_own_hostings_for_regular_user(): void
    {
        $owner = User::factory()->create();
        $module = Module::factory()->create();
        $otherModule = Module::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        ModuleRolePermission::create(['module_id' => $module->id, 'role_id' => $userRole->id, 'can_read' => true]);
        $owner->assignRole($userRole);
        Hosting::factory()->create(['user_id' => $owner->id, 'name' => 'mine', 'module_id' => $module->id]);
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        Hosting::factory()->create(['user_id' => $admin->id, 'name' => 'theirs', 'module_id' => $otherModule->id]);

        $token = $owner->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/hostings');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals('mine', $response->json('data.0.name'));
    }

    public function test_list_with_trashed(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $hosting = Hosting::factory()->create(['user_id' => $admin->id]);
        $hosting->delete();

        $this->actingAs($admin)->getJson('/api/hostings?with_trashed=true')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_create_with_ip_fields(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/hostings', [
            'name' => 'My Site',
            'domain_ip' => '192.168.1.1',
            'mail_domain_ip' => '192.168.1.2',
            'cpanel_ip' => '192.168.1.3',
        ])->assertStatus(201)
            ->assertJsonPath('data.domain_ip', '192.168.1.1')
            ->assertJsonPath('data.mail_domain_ip', '192.168.1.2')
            ->assertJsonPath('data.cpanel_ip', '192.168.1.3');
    }

    public function test_search_by_domain_ip(): void
    {
        $u = $this->superAdmin();
        Hosting::factory()->create(['user_id' => $u->id, 'name' => 'Site A', 'domain_ip' => '10.0.0.1']);
        Hosting::factory()->create(['user_id' => $u->id, 'name' => 'Site B', 'domain_ip' => '10.0.0.2']);
        $this->actingAs($u)->getJson('/api/hostings?search=10.0.0.1')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_create_with_password_saves_encrypted(): void
    {
        $u = $this->superAdmin();
        $this->actingAs($u)->postJson('/api/hostings', [
            'name' => 'Secure Hosting',
            'password' => 'hosting_secret!',
        ])->assertStatus(201);

        $h = Hosting::where('name', 'Secure Hosting')->first();
        $this->assertNotNull($h);
        $this->assertNotEquals('hosting_secret!', $h->getRawOriginal('password'));
        $this->assertEquals('hosting_secret!', $h->password);
    }

    public function test_blank_password_update_preserves_existing(): void
    {
        $u = $this->superAdmin();
        $h = Hosting::factory()->create(['user_id' => $u->id, 'password' => 'original_pw']);

        $this->actingAs($u)->putJson("/api/hostings/{$h->id}", [
            'name' => 'Renamed Hosting',
        ])->assertOk();

        $this->assertEquals('original_pw', $h->fresh()->password);
    }

    public function test_password_not_visible_in_listing_response(): void
    {
        $u = $this->superAdmin();
        $h = Hosting::factory()->create(['user_id' => $u->id, 'password' => 'invisible_pw']);
        $this->actingAs($u)->getJson('/api/hostings')
            ->assertJsonMissingPath('data.0.password');
    }
}
