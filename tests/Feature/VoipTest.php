<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Voip;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoipTest extends TestCase
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
        $this->actingAs($this->superAdmin())->postJson('/api/voip', ['name' => 'Office Line'])
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
        $this->actingAs($u)->putJson("/api/voip/{$v->id}", ['name' => 'New'])->assertOk()->assertJsonPath('data.name', 'New');
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

    public function test_list_shows_own_voip_for_regular_user(): void
    {
        $owner = User::factory()->create();
        $module = Module::factory()->create();
        $otherModule = Module::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        ModuleRolePermission::create(['module_id' => $module->id, 'role_id' => $userRole->id, 'can_read' => true]);
        $owner->assignRole($userRole);
        Voip::factory()->create(['user_id' => $owner->id, 'name' => 'mine', 'phone_number' => '+1111111111', 'module_id' => $module->id]);
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        Voip::factory()->create(['user_id' => $admin->id, 'name' => 'theirs', 'phone_number' => '+1222222222', 'module_id' => $otherModule->id]);

        $token = $owner->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/voip');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertEquals('mine', $response->json('data.0.name'));
    }

    public function test_list_with_trashed(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $voip = Voip::factory()->create(['user_id' => $admin->id, 'phone_number' => '+1333333333']);
        $voip->delete();

        $this->actingAs($admin)->getJson('/api/voip?with_trashed=true')->assertOk()->assertJsonCount(1, 'data');
    }

    // ─── Extension field mapping tests ────────────────────────

    public function test_web_create_with_extension_saves_extensions_array(): void
    {
        $user = $this->superAdmin();
        $module = Module::factory()->create(['slug' => 'voip']);

        $this->actingAs($user);
        $this->post(route('voip.store'), [
            'name' => 'Test VoIP',
            'extension' => '101',
            'module_id' => $module->id,
        ])->assertSessionHas('success');

        $voip = Voip::where('name', 'Test VoIP')->first();
        $this->assertNotNull($voip);
        $this->assertIsArray($voip->extensions);
        $this->assertCount(1, $voip->extensions);
        $this->assertEquals('101', $voip->extensions[0]);
    }

    public function test_web_create_without_extension_stores_empty_array(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user);
        $this->post(route('voip.store'), [
            'name' => 'No Ext VoIP',
        ])->assertSessionHas('success');

        $voip = Voip::where('name', 'No Ext VoIP')->first();
        $this->assertNotNull($voip);
        $this->assertIsArray($voip->extensions);
        $this->assertCount(0, $voip->extensions);
    }

    public function test_web_update_with_extension_updates_extensions_array(): void
    {
        $user = $this->superAdmin();
        $voip = Voip::factory()->create(['user_id' => $user->id, 'extensions' => ['101']]);

        $this->actingAs($user);
        $this->put(route('voip.update', $voip->id), [
            'name' => $voip->name,
            'extension' => '202',
        ])->assertSessionHas('success');

        $voip->refresh();
        $this->assertCount(1, $voip->extensions);
        $this->assertEquals('202', $voip->extensions[0]);
    }

    public function test_web_update_without_extension_preserves_extensions(): void
    {
        $user = $this->superAdmin();
        $voip = Voip::factory()->create(['user_id' => $user->id, 'extensions' => ['303']]);

        $this->actingAs($user);
        $this->put(route('voip.update', $voip->id), [
            'name' => 'Renamed VoIP',
        ])->assertSessionHas('success');

        $voip->refresh();
        $this->assertCount(1, $voip->extensions);
        $this->assertEquals('303', $voip->extensions[0]);
    }

    public function test_web_update_with_empty_extension_clears_extensions(): void
    {
        $user = $this->superAdmin();
        $voip = Voip::factory()->create(['user_id' => $user->id, 'extensions' => ['404']]);

        $this->actingAs($user);
        $this->put(route('voip.update', $voip->id), [
            'name' => $voip->name,
            'extension' => '',
        ])->assertSessionHas('success');

        $voip->refresh();
        $this->assertIsArray($voip->extensions);
        $this->assertCount(0, $voip->extensions);
    }

    public function test_api_create_with_extension_saves_extensions_array(): void
    {
        $user = $this->superAdmin();
        $module = Module::factory()->create(['slug' => 'voip']);

        $response = $this->actingAs($user)->postJson('/api/voip', [
            'name' => 'API VoIP Ext',
            'extension' => '555',
            'module_id' => $module->id,
        ]);

        $response->assertStatus(201);
        $voip = Voip::where('name', 'API VoIP Ext')->first();
        $this->assertNotNull($voip);
        $this->assertIsArray($voip->extensions);
        $this->assertCount(1, $voip->extensions);
        $this->assertEquals('555', $voip->extensions[0]);
    }

    public function test_api_update_with_extension_updates_extensions_array(): void
    {
        $user = $this->superAdmin();
        $voip = Voip::factory()->create(['user_id' => $user->id, 'extensions' => ['111']]);

        $this->actingAs($user)->putJson("/api/voip/{$voip->id}", [
            'name' => $voip->name,
            'extension' => '666',
        ])->assertOk();

        $voip->refresh();
        $this->assertCount(1, $voip->extensions);
        $this->assertEquals('666', $voip->extensions[0]);
    }

    public function test_api_update_without_extension_preserves_extensions(): void
    {
        $user = $this->superAdmin();
        $voip = Voip::factory()->create(['user_id' => $user->id, 'extensions' => ['777']]);

        $this->actingAs($user)->putJson("/api/voip/{$voip->id}", [
            'name' => 'API Renamed',
        ])->assertOk();

        $voip->refresh();
        $this->assertCount(1, $voip->extensions);
        $this->assertEquals('777', $voip->extensions[0]);
    }

    public function test_web_store_with_extension_does_not_affect_unrelated_fields(): void
    {
        $user = $this->superAdmin();
        $provider = ServiceProvider::factory()->create();
        $module = Module::factory()->create(['slug' => 'voip']);

        // Create with extension
        $this->actingAs($user)->post(route('voip.store'), [
            'name' => 'Unrelated Test',
            'extension' => '888',
            'phone_number' => '+1234567890',
            'service_provider_id' => $provider->id,
            'module_id' => $module->id,
            'direction' => 'both',
        ])->assertSessionHas('success');

        $voip = Voip::where('name', 'Unrelated Test')->first();
        $this->assertNotNull($voip);
        $this->assertEquals('+1234567890', $voip->phone_number);
        $this->assertEquals('both', $voip->direction);
        $this->assertEquals($provider->id, $voip->service_provider_id);
        $this->assertEquals($module->id, $voip->module_id);
        $this->assertEquals(['888'], $voip->extensions);
    }

    public function test_web_update_with_extension_does_not_affect_unrelated_fields(): void
    {
        $user = $this->superAdmin();
        $voip = Voip::factory()->create(['user_id' => $user->id, 'extensions' => ['999'], 'phone_number' => '+1111111111']);

        $this->actingAs($user)->put(route('voip.update', $voip->id), [
            'name' => $voip->name,
            'extension' => '000',
        ])->assertSessionHas('success');

        $voip->refresh();
        $this->assertEquals(['000'], $voip->extensions);
        $this->assertEquals('+1111111111', $voip->phone_number);
    }
}
