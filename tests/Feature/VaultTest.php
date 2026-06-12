<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use App\Models\VaultEntry;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class VaultTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->seed(\Database\Seeders\FeatureModuleSeeder::class);
    }

    public function test_create_vault_entry()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/vault', [
                'service_name' => 'My Secret App',
                'password' => 'supersecret',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'service_name', 'password_masked']])
            ->assertJsonMissingPath('data.password');
    }

    public function test_vault_validation()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/vault', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['service_name', 'password']);
    }

    public function test_show_vault_entry()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $entry = new VaultEntry();
        $entry->user_id = $user->id;
        $entry->service_name = 'Test App';
        $entry->username = 'admin';
        $entry->encryptPassword('secret123');
        $entry->save();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/vault/{$entry->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'service_name', 'password_masked']]);
    }

    public function test_reveal_password()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $entry = new VaultEntry();
        $entry->user_id = $user->id;
        $entry->service_name = 'Reveal Test';
        $entry->encryptPassword('my-plain-password');
        $entry->save();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/vault/{$entry->id}/reveal");

        $response->assertStatus(200)
            ->assertJsonPath('data.password', 'my-plain-password');
    }

    public function test_other_user_blocked_from_vault()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $entry = new VaultEntry();
        $entry->user_id = $owner->id;
        $entry->service_name = 'Secret';
        $entry->encryptPassword('hidden');
        $entry->save();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/vault/{$entry->id}");

        $response->assertStatus(403);
    }

    public function test_delete_vault_entry()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $entry = new VaultEntry();
        $entry->user_id = $user->id;
        $entry->service_name = 'Delete Test';
        $entry->encryptPassword('delete-me');
        $entry->save();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/vault/{$entry->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Vault entry deleted']);
    }

    public function test_reveal_creates_audit_log()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $entry = new VaultEntry();
        $entry->user_id = $user->id;
        $entry->service_name = 'Audit Test';
        $entry->encryptPassword('reveal-me');
        $entry->save();

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/vault/{$entry->id}/reveal");

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => VaultEntry::class,
            'subject_id' => $entry->id,
            'causer_id' => $user->id,
            'event' => 'revealed',
            'description' => 'vault_entry_revealed',
        ]);
    }

    public function test_vault_sort_by_service_name()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $entryA = new VaultEntry();
        $entryA->user_id = $user->id;
        $entryA->service_name = 'Z Service';
        $entryA->encryptPassword('p1');
        $entryA->save();

        $entryB = new VaultEntry();
        $entryB->user_id = $user->id;
        $entryB->service_name = 'A Service';
        $entryB->encryptPassword('p2');
        $entryB->save();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/vault?sort_by=service_name&sort_order=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $names = array_column($data, 'service_name');
        $sorted = $names;
        sort($sorted);
        $this->assertSame($sorted, $names);
    }

    public function test_vault_with_trashed_as_super_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $entry = new VaultEntry();
        $entry->user_id = $user->id;
        $entry->service_name = 'Trash Me';
        $entry->encryptPassword('delete-me');
        $entry->save();
        $entryId = $entry->id;
        $entry->delete();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/vault?with_trashed=1');

        $response->assertStatus(200);
        $this->assertStringContainsString('Trash Me', $response->getContent());
    }

    public function test_update_vault_entry()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $entry = new VaultEntry();
        $entry->user_id = $user->id;
        $entry->service_name = 'Original Name';
        $entry->encryptPassword('secret');
        $entry->save();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/vault/{$entry->id}", [
                'service_name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'service_name']]);
        $this->assertDatabaseHas('password_vault', ['id' => $entry->id, 'service_name' => 'Updated Name']);
    }

    public function test_non_owner_cannot_reveal_vault()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $entry = new VaultEntry();
        $entry->user_id = $owner->id;
        $entry->service_name = 'Secret';
        $entry->encryptPassword('hidden');
        $entry->save();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/vault/{$entry->id}/reveal");

        $response->assertStatus(403);
    }

    public function test_create_vault_with_module()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $module = Module::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/vault', [
                'service_name' => 'Module Vault',
                'password' => 'secret',
                'module_id' => $module->id,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('password_vault', [
            'service_name' => 'Module Vault',
            'module_id' => $module->id,
        ]);
    }

    public function test_non_owner_with_module_permission_can_view_vault()
    {
        $owner = User::factory()->create();
        $module = Module::first();

        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $adminRole->id,
            'can_read' => true,
        ]);

        $viewer = User::factory()->create();
        $viewer->assignRole($adminRole);
        $token = $viewer->createToken('test')->plainTextToken;

        $entry = new VaultEntry();
        $entry->user_id = $owner->id;
        $entry->service_name = 'Shared Vault';
        $entry->module_id = $module->id;
        $entry->encryptPassword('shared-secret');
        $entry->save();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/vault/{$entry->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'service_name', 'password_masked']]);
    }

    public function test_show_nonexistent_vault_returns_404()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/vault/99999');

        $response->assertStatus(404);
    }

    public function test_delete_nonexistent_vault_returns_404()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson('/api/vault/99999');

        $response->assertStatus(404);
    }

    public function test_update_nonexistent_vault_returns_404()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/vault/99999', ['service_name' => 'Ghost']);

        $response->assertStatus(404);
    }

    public function test_update_vault_changes_password_masked()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $entry = new VaultEntry();
        $entry->user_id = $user->id;
        $entry->service_name = 'Password Change Test';
        $entry->encryptPassword('old-password');
        $entry->save();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/vault/{$entry->id}", [
                'password' => 'new-password',
            ]);

        $response->assertStatus(200);
        $reveal = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/vault/{$entry->id}/reveal");
        $reveal->assertJsonPath('data.password', 'new-password');
    }
}
