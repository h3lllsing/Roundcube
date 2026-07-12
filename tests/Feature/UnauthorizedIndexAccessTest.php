<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnauthorizedIndexAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Module $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $this->user = User::factory()->create();
        $this->user->assignRole($userRole);
        $this->module = Module::where('slug', 'hostings')->firstOrFail();
    }

    public function test_user_without_read_permission_gets_403_on_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('hostings.index'));
        $response->assertStatus(403);
    }

    public function test_user_with_read_permission_can_access_index(): void
    {
        $userRole = Role::where('slug', 'user')->firstOrFail();
        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $userRole->id,
            'can_read' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('hostings.index'));
        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_index(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $response = $this->actingAs($superAdmin)->get(route('hostings.index'));
        $response->assertStatus(200);
    }
}
