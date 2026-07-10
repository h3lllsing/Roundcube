<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use App\Models\Vps;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VpsTest extends TestCase
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

    public function test_list_shows_own_vps_for_regular_user(): void
    {
        $owner = User::factory()->create();
        $module = Module::factory()->create();
        $otherModule = Module::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        ModuleRolePermission::create(['module_id' => $module->id, 'role_id' => $userRole->id, 'can_read' => true]);
        $owner->assignRole($userRole);
        Vps::factory()->create(['user_id' => $owner->id, 'name' => 'mine', 'ip_address' => '10.0.0.1', 'module_id' => $module->id]);
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        Vps::factory()->create(['user_id' => $admin->id, 'name' => 'theirs', 'ip_address' => '10.0.0.2', 'module_id' => $otherModule->id]);

        $token = $owner->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/vps');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals('mine', $response->json('data.0.name'));
    }

    public function test_list_with_trashed(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $vps = Vps::factory()->create(['user_id' => $admin->id, 'ip_address' => '10.0.0.3']);
        $vps->delete();

        $this->actingAs($admin)->getJson('/api/vps?with_trashed=true')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_create_with_new_fields(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/vps', [
            'name' => 'Web Server',
            'provider' => 'DigitalOcean',
            'department' => 'Engineering',
            'location' => 'New York',
            'login_ids' => '{"ssh": "root@vps1", "panel": "admin@vps1"}',
            'additional_ips' => '["10.0.0.2", "10.0.0.3"]',
        ])->assertStatus(201)
            ->assertJsonPath('data.department', 'Engineering')
            ->assertJsonPath('data.location', 'New York')
            ->assertJsonPath('data.login_ids.ssh', 'root@vps1')
            ->assertJsonPath('data.additional_ips.0', '10.0.0.2');
    }

    public function test_invalid_login_ids_fails(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/vps', [
            'name' => 'Web Server',
            'provider' => 'DigitalOcean',
            'login_ids' => 'not-json',
        ])->assertStatus(422)->assertJsonValidationErrors('login_ids');
    }

    public function test_invalid_additional_ips_fails(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/vps', [
            'name' => 'Web Server',
            'provider' => 'DigitalOcean',
            'additional_ips' => 'not-json',
        ])->assertStatus(422)->assertJsonValidationErrors('additional_ips');
    }

    public function test_search_by_department(): void
    {
        $u = $this->superAdmin();
        Vps::factory()->create(['user_id' => $u->id, 'name' => 'Web', 'department' => 'Engineering']);
        Vps::factory()->create(['user_id' => $u->id, 'name' => 'DB', 'department' => 'Operations']);
        $this->actingAs($u)->getJson('/api/vps?search=Engineering')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_search_by_location(): void
    {
        $u = $this->superAdmin();
        Vps::factory()->create(['user_id' => $u->id, 'name' => 'Web', 'location' => 'New York']);
        Vps::factory()->create(['user_id' => $u->id, 'name' => 'DB', 'location' => 'London']);
        $this->actingAs($u)->getJson('/api/vps?search=London')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_create_with_password_saves_encrypted(): void
    {
        $u = $this->superAdmin();
        $this->actingAs($u)->postJson('/api/vps', [
            'name' => 'Secure VPS',
            'password' => 'vps_secret!',
        ])->assertStatus(201);

        $vps = Vps::where('name', 'Secure VPS')->first();
        $this->assertNotNull($vps);
        $this->assertNotEquals('vps_secret!', $vps->getRawOriginal('password'));
        $this->assertEquals('vps_secret!', $vps->password);
    }

    public function test_blank_password_update_preserves_existing(): void
    {
        $u = $this->superAdmin();
        $vps = Vps::factory()->create(['user_id' => $u->id, 'password' => 'original_pw']);

        $this->actingAs($u)->putJson("/api/vps/{$vps->id}", [
            'name' => 'Renamed',
        ])->assertOk();

        $this->assertEquals('original_pw', $vps->fresh()->password);
    }

    public function test_web_blank_password_update_preserves_existing(): void
    {
        $u = $this->superAdmin();
        $vps = Vps::factory()->create(['user_id' => $u->id, 'password' => 'web_pw']);

        $this->actingAs($u)->put(route('vps.update', $vps->id), [
            'name' => 'Web Renamed',
        ])->assertSessionHas('success');

        $this->assertEquals('web_pw', $vps->fresh()->password);
    }

    public function test_password_not_visible_in_listing_response(): void
    {
        $u = $this->superAdmin();
        $vps = Vps::factory()->create(['user_id' => $u->id, 'password' => 'invisible_pw']);
        $this->actingAs($u)->getJson('/api/vps')
            ->assertJsonMissingPath('data.0.password');
    }
}
