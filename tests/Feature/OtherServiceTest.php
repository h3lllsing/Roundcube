<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\OtherService;
use App\Models\User;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtherServiceTest extends TestCase
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

    public function test_list_shows_own_services_for_regular_user(): void
    {
        $owner = User::factory()->create();
        $module = Module::factory()->create();
        $otherModule = Module::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        ModuleRolePermission::create(['module_id' => $module->id, 'role_id' => $userRole->id, 'can_read' => true]);
        $owner->assignRole($userRole);
        OtherService::factory()->create(['user_id' => $owner->id, 'name' => 'mine', 'service_type' => 'monitoring', 'module_id' => $module->id]);
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        OtherService::factory()->create(['user_id' => $admin->id, 'name' => 'theirs', 'service_type' => 'dns', 'module_id' => $otherModule->id]);

        $token = $owner->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/other-services');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals('mine', $response->json('data.0.name'));
    }

    public function test_list_with_trashed(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $os = OtherService::factory()->create(['user_id' => $admin->id, 'service_type' => 'monitoring']);
        $os->delete();

        $this->actingAs($admin)->getJson('/api/other-services?with_trashed=true')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_create_with_password_saves_encrypted(): void
    {
        $u = $this->superAdmin();
        $this->actingAs($u)->postJson('/api/other-services', [
            'name' => 'Secure Service',
            'service_type' => 'monitoring',
            'password' => 'other_secret!',
        ])->assertStatus(201);

        $os = OtherService::where('name', 'Secure Service')->first();
        $this->assertNotNull($os);
        $this->assertNotEquals('other_secret!', $os->getRawOriginal('password'));
        $this->assertEquals('other_secret!', $os->password);
    }

    public function test_blank_password_update_preserves_existing(): void
    {
        $u = $this->superAdmin();
        $os = OtherService::factory()->create(['user_id' => $u->id, 'password' => 'original_pw']);

        $this->actingAs($u)->putJson("/api/other-services/{$os->id}", [
            'name' => 'Renamed',
        ])->assertOk();

        $this->assertEquals('original_pw', $os->fresh()->password);
    }

    public function test_password_not_visible_in_listing_response(): void
    {
        $u = $this->superAdmin();
        $os = OtherService::factory()->create(['user_id' => $u->id, 'password' => 'invisible_pw']);
        $this->actingAs($u)->getJson('/api/other-services')
            ->assertJsonMissingPath('data.0.password');
    }
}
