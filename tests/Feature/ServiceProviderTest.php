<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\ServiceProvider;
use App\Models\User;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceProviderTest extends TestCase
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

    public function test_list_shows_own_providers_for_regular_user(): void
    {
        $owner = User::factory()->create();
        $module = Module::factory()->create();
        $otherModule = Module::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        ModuleRolePermission::create(['module_id' => $module->id, 'role_id' => $userRole->id, 'can_read' => true]);
        $owner->assignRole($userRole);
        ServiceProvider::factory()->create(['user_id' => $owner->id, 'name' => 'mine', 'type' => 'hosting', 'module_id' => $module->id]);
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        ServiceProvider::factory()->create(['user_id' => $admin->id, 'name' => 'theirs', 'type' => 'email', 'module_id' => $otherModule->id]);

        $token = $owner->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/service-providers');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals('mine', $response->json('data.0.name'));
    }

    public function test_list_with_trashed(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $sp = ServiceProvider::factory()->create(['user_id' => $admin->id, 'type' => 'hosting']);
        $sp->delete();

        $this->actingAs($admin)->getJson('/api/service-providers?with_trashed=true')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_create_with_email(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/service-providers', [
            'name' => 'ISP Co',
            'type' => 'internet',
            'email' => 'support@ispco.com',
        ])->assertStatus(201)->assertJsonPath('data.email', 'support@ispco.com');
    }

    public function test_invalid_email_fails(): void
    {
        $this->actingAs($this->superAdmin())->postJson('/api/service-providers', [
            'name' => 'ISP Co',
            'type' => 'internet',
            'email' => 'not-an-email',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_search_by_email(): void
    {
        $u = $this->superAdmin();
        ServiceProvider::factory()->create(['user_id' => $u->id, 'name' => 'ISP A', 'type' => 'internet', 'email' => 'a@isp.com']);
        ServiceProvider::factory()->create(['user_id' => $u->id, 'name' => 'ISP B', 'type' => 'internet', 'email' => 'b@isp.com']);
        $this->actingAs($u)->getJson('/api/service-providers?search=a@isp.com')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_create_with_password_saves_encrypted(): void
    {
        $u = $this->superAdmin();
        $this->actingAs($u)->postJson('/api/service-providers', [
            'name' => 'Secure ISP',
            'type' => 'internet',
            'password' => 's3cret!',
        ])->assertStatus(201);

        $sp = ServiceProvider::where('name', 'Secure ISP')->first();
        $this->assertNotNull($sp);
        $this->assertNotEquals('s3cret!', $sp->getRawOriginal('password'));
        $this->assertEquals('s3cret!', $sp->password);
    }

    public function test_blank_password_update_preserves_existing(): void
    {
        $u = $this->superAdmin();
        $sp = ServiceProvider::factory()->create(['user_id' => $u->id, 'password' => 'original_pw']);

        $this->actingAs($u)->putJson("/api/service-providers/{$sp->id}", [
            'name' => 'Renamed',
        ])->assertOk();

        $this->assertEquals('original_pw', $sp->fresh()->password);
    }

    public function test_web_blank_password_update_preserves_existing(): void
    {
        $u = $this->superAdmin();
        $sp = ServiceProvider::factory()->create(['user_id' => $u->id, 'password' => 'web_pw']);

        $this->actingAs($u)->put(route('service-providers.update', $sp->id), [
            'name' => 'Web Renamed',
        ])->assertSessionHas('success');

        $this->assertEquals('web_pw', $sp->fresh()->password);
    }

    public function test_password_not_visible_in_listing_response(): void
    {
        $u = $this->superAdmin();
        $sp = ServiceProvider::factory()->create(['user_id' => $u->id, 'password' => 'invisible_pw']);
        $this->actingAs($u)->getJson('/api/service-providers')
            ->assertJsonMissingPath('data.0.password');
    }
}
