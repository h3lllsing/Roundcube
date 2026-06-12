<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TaskService;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->seed(\Database\Seeders\FeatureModuleSeeder::class);
    }

    public function test_dashboard_as_super_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [
                'total_features', 'total_modules', 'tasks_by_status', 'total_tasks',
                'my_tasks_total', 'my_pending_tasks', 'total_notes', 'my_notes',
                'unread_notifications', 'total_notifications', 'recent_activity',
                'total_users',
            ]]);
    }

    public function test_dashboard_as_regular_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonMissingPath('data.total_users');
    }

    public function test_dashboard_reflects_task_changes()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/dashboard');
        $beforeTasks = $response->json('data.total_tasks');

        $module = \App\Models\Module::first();
        app(TaskService::class)->create([
            'title' => 'Dashboard Test', 'module_id' => $module->id,
            'status' => 'pending', 'priority' => 'medium',
            'created_by' => $user->id, 'updated_by' => $user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/dashboard');
        $afterTasks = $response->json('data.total_tasks');

        $this->assertEquals($beforeTasks + 1, $afterTasks);
    }

    public function test_dashboard_requires_authentication()
    {
        $response = $this->getJson('/api/dashboard');
        $response->assertStatus(401);
    }

    public function test_dashboard_counts_match_database()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/dashboard');

        $expectedFeatures = \App\Models\Feature::count();
        $expectedModules = \App\Models\Module::count();
        $expectedTasks = \App\Models\Task::count();
        $expectedUsers = \App\Models\User::count();

        $response->assertStatus(200);
        $this->assertEquals($expectedFeatures, $response->json('data.total_features'));
        $this->assertEquals($expectedModules, $response->json('data.total_modules'));
        $this->assertEquals($expectedTasks, $response->json('data.total_tasks'));
        $this->assertEquals($expectedUsers, $response->json('data.total_users'));
    }
}
