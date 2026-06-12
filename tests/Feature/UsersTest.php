<?php

namespace Tests\Feature;

use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
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
                'password' => 'password123',
                'password_confirmation' => 'password123',
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
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
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
            ->patchJson("/api/users/{$user->id}/suspend")
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
            ->postJson('/api/users', ['name' => 'x', 'email' => 'x@x.com', 'password' => 'password123'])
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson('/api/users/1')
            ->assertStatus(403);
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
}
