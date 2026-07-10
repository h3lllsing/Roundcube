<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\User;
use App\Models\UserModulePermission;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);
    }

    public function test_index_lists_users()
    {
        User::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/users');

        $response->assertOk();
        $this->assertCount(5, $response->json('data')); // 3 created + 1 setup admin + 1 seeder admin
    }

    public function test_index_search_by_name()
    {
        User::factory()->create(['name' => 'Alice Wonderland']);
        User::factory()->create(['name' => 'Bob Builder']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/users?search=Alice');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Alice Wonderland', $response->json('data.0.name'));
    }

    public function test_index_search_by_email()
    {
        User::factory()->create(['email' => 'findme@example.com']);
        User::factory()->create(['email' => 'other@example.com']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/users?search=findme');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('findme@example.com', $response->json('data.0.email'));
    }

    public function test_store_creates_user()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'Pass12345',
                'password_confirmation' => 'Pass12345',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New User');

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    public function test_store_validation()
    {
        $this->actingAs($this->admin)
            ->postJson('/api/users', ['name' => '', 'email' => 'not-email'])
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_show_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_show_missing_user_returns_404()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/users/99999')
            ->assertStatus(404);
    }

    public function test_update_user()
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $this->actingAs($this->admin)
            ->putJson("/api/users/{$user->id}", [
                'name' => 'New Name',
                'email' => 'newemail@example.com',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name');

        $this->assertEquals('New Name', $user->fresh()->name);
        $this->assertEquals('newemail@example.com', $user->fresh()->email);
    }

    public function test_update_with_password()
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->putJson("/api/users/{$user->id}", [
                'name' => 'Updated',
                'email' => $user->email,
                'password' => 'NewPass123',
                'password_confirmation' => 'NewPass123',
            ])
            ->assertOk();
    }

    public function test_destroy_soft_deletes_user()
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->deleteJson("/api/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('message', 'User deleted');

        $this->assertSoftDeleted($user);
    }

    public function test_suspend_user()
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->patchJson("/api/users/{$user->id}/suspend")
            ->assertOk()
            ->assertJsonPath('message', 'User suspended');

        $this->assertNotNull($user->fresh()->suspended_at);
    }

    public function test_unsuspend_user()
    {
        $user = User::factory()->create(['suspended_at' => now()]);

        $this->actingAs($this->admin)
            ->patchJson("/api/users/{$user->id}/unsuspend")
            ->assertOk()
            ->assertJsonPath('message', 'User unsuspended');

        $this->assertNull($user->fresh()->suspended_at);
    }

    public function test_requires_super_admin_role()
    {
        $regularUser = User::factory()->create();
        $token = $regularUser->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/users')
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/users', ['name' => 'x', 'email' => 'x@x.com', 'password' => 'Pass12345'])
            ->assertStatus(403);

        $targetUser = User::factory()->create();
        $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/users/{$targetUser->id}")
            ->assertStatus(403);
    }

    public function test_store_creates_user_with_roles()
    {
        $role = Role::where('slug', 'super-admin')->firstOrFail();

        $response = $this->actingAs($this->admin)
            ->postJson('/api/users', [
                'name' => 'Role User',
                'email' => 'roleuser@example.com',
                'password' => 'Pass12345',
                'password_confirmation' => 'Pass12345',
                'roles' => [$role->id],
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'roleuser@example.com']);
    }

    public function test_update_user_roles()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();

        $this->actingAs($this->admin)
            ->putJson("/api/users/{$user->id}", [
                'name' => 'Updated',
                'email' => $user->email,
                'roles' => [$role->id],
            ])
            ->assertOk();

        $this->assertTrue($user->fresh()->hasRole('super-admin'));
    }

    public function test_requires_authentication()
    {
        $this->getJson('/api/users')->assertUnauthorized();
    }

    public function test_index_with_trashed()
    {
        $user = User::factory()->create();
        $user->delete();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/users?with_trashed=true');

        $response->assertOk();
        $total = $response->json('total');
        $this->assertGreaterThanOrEqual(2, $total); // admin + soft-deleted user
    }

    public function test_index_trashed_only()
    {
        User::factory()->create();
        $deleted = User::factory()->create();
        $deleted->delete();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/users?trashed_only=true');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($deleted->id, $response->json('data.0.id'));
    }

    public function test_web_user_index_filters(): void
    {
        User::factory()->create(['name' => 'SuspendedUser', 'suspended_at' => now()]);

        $this->actingAs($this->admin);
        $this->get(route('users.index', ['status' => 'active']))->assertStatus(200);
        $this->get(route('users.index', ['status' => 'suspended']))->assertStatus(200);
        $this->get(route('users.index', ['date_from' => now()->subWeek()->format('Y-m-d')]))->assertStatus(200);
        $this->get(route('users.index', ['date_to' => now()->addDay()->format('Y-m-d')]))->assertStatus(200);
    }

    public function test_web_user_create_with_roles(): void
    {
        $role = Role::where('slug', 'user')->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name' => 'Web User',
                'email' => 'webuser@test.com',
                'password' => 'Pass12345',
                'password_confirmation' => 'Pass12345',
                'roles' => [$role->id],
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['email' => 'webuser@test.com']);
    }

    public function test_web_user_update_detach_roles(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'user')->firstOrFail();
        $user->assignRole($role);

        $this->actingAs($this->admin)
            ->put(route('users.update', $user->id), [
                'name' => 'Updated',
                'email' => $user->email,
                'roles' => [],
            ])
            ->assertSessionHas('success');

        $this->assertFalse($user->fresh()->hasRole('user'));
    }

    public function test_web_user_update_with_roles(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'user')->firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('users.update', $user->id), [
                'name' => 'Updated With Role',
                'email' => $user->email,
                'roles' => [$role->id],
            ])
            ->assertSessionHas('success');

        $this->assertTrue($user->fresh()->hasRole('user'));
    }

    public function test_update_name_only_without_password(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);
        $originalHash = $user->password;

        $this->actingAs($this->admin)
            ->putJson("/api/users/{$user->id}", [
                'name' => 'Only Name Changed',
                'email' => $user->email,
            ])
            ->assertOk();

        $fresh = $user->fresh();
        $this->assertEquals('Only Name Changed', $fresh->name);
        $this->assertEquals($originalHash, $fresh->password);
    }

    public function test_update_email_only_without_password(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);
        $originalHash = $user->password;

        $this->actingAs($this->admin)
            ->putJson("/api/users/{$user->id}", [
                'name' => $user->name,
                'email' => 'newemail@example.com',
            ])
            ->assertOk();

        $fresh = $user->fresh();
        $this->assertEquals('newemail@example.com', $fresh->email);
        $this->assertEquals($originalHash, $fresh->password);
    }

    public function test_update_roles_only_without_password(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'user')->firstOrFail();
        $originalHash = $user->password;

        $this->actingAs($this->admin)
            ->putJson("/api/users/{$user->id}", [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => [$role->id],
            ])
            ->assertOk();

        $fresh = $user->fresh();
        $this->assertTrue($fresh->hasRole('user'));
        $this->assertEquals($originalHash, $fresh->password);
    }

    public function test_update_suspend_user_without_password(): void
    {
        $user = User::factory()->create(['suspended_at' => null]);
        $originalHash = $user->password;

        $this->actingAs($this->admin)
            ->put(route('users.update', $user->id), [
                'name' => $user->name,
                'email' => $user->email,
                'suspended_at' => now()->format('Y-m-d'),
            ])
            ->assertSessionHas('success');

        $fresh = $user->fresh();
        $this->assertNotNull($fresh->suspended_at);
        $this->assertEquals($originalHash, $fresh->password);
    }

    public function test_blank_password_does_not_change_existing_hash(): void
    {
        $user = User::factory()->create();
        $originalHash = $user->password;

        $this->actingAs($this->admin)
            ->putJson("/api/users/{$user->id}", [
                'name' => $user->name,
                'email' => $user->email,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertOk();

        $this->assertEquals($originalHash, $user->fresh()->password);
    }

    public function test_password_updates_only_when_provided_and_confirmed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->putJson("/api/users/{$user->id}", [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'NewPass123!',
                'password_confirmation' => 'NewPass123!',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('NewPass123!', $user->fresh()->password));
    }

    public function test_password_confirmation_required_when_password_provided(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->putJson("/api/users/{$user->id}", [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'NewPass123!',
            ])
            ->assertJsonValidationErrors(['password']);
    }

    public function test_basic_update_does_not_change_permission_overrides(): void
    {
        $this->seed(FeatureModuleSeeder::class);
        $user = User::factory()->create();
        $module = Module::first();
        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'can_read' => false,
        ]);
        $this->assertDatabaseHas('user_module_permissions', [
            'user_id' => $user->id,
            'module_id' => $module->id,
            'can_read' => false,
        ]);

        $this->actingAs($this->admin)
            ->putJson("/api/users/{$user->id}", [
                'name' => 'Override Test',
                'email' => $user->email,
            ])
            ->assertOk();

        $this->assertDatabaseHas('user_module_permissions', [
            'user_id' => $user->id,
            'module_id' => $module->id,
            'can_read' => false,
        ]);
    }

    public function test_non_authorized_users_cannot_edit_users(): void
    {
        $user = User::factory()->create();
        $regularUser = User::factory()->create();
        $token = $regularUser->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/users/{$user->id}", [
                'name' => 'Hacked Name',
                'email' => $user->email,
            ])
            ->assertStatus(403);
    }
}
