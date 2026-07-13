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

    public function test_domain_emails_unauthorized_gets_403(): void
    {
        $response = $this->actingAs($this->user)->get(route('domain-emails.index'));
        $response->assertStatus(403);
    }

    public function test_domain_emails_authorized_can_access(): void
    {
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $module = Module::where('slug', 'domain-emails')->firstOrFail();
        ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $userRole->id,
            'can_read' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('domain-emails.index'));
        $response->assertStatus(200);
    }

    public function test_expiry_trackers_unauthorized_gets_403(): void
    {
        $response = $this->actingAs($this->user)->get(route('expiry-trackers.index'));
        $response->assertStatus(403);
    }

    public function test_expiry_trackers_authorized_can_access(): void
    {
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $module = Module::where('slug', 'expiry-trackers')->firstOrFail();
        ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $userRole->id,
            'can_read' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('expiry-trackers.index'));
        $response->assertStatus(200);
    }

    public function test_assets_unauthorized_gets_403(): void
    {
        $response = $this->actingAs($this->user)->get(route('assets.index'));
        $response->assertStatus(403);
    }

    public function test_assets_authorized_can_access(): void
    {
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $module = Module::where('slug', 'assets')->firstOrFail();
        ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $userRole->id,
            'can_read' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('assets.index'));
        $response->assertStatus(200);
    }

    public function test_vault_shared_unauthorized_gets_403(): void
    {
        $response = $this->actingAs($this->user)->get(route('vault.index'));
        $response->assertStatus(403);
    }

    public function test_vault_shared_authorized_can_access(): void
    {
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $module = Module::where('slug', 'vault')->firstOrFail();
        ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $userRole->id,
            'can_read' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('vault.index'));
        $response->assertStatus(200);
    }

    public function test_my_vault_accessible_without_read_permission(): void
    {
        $response = $this->actingAs($this->user)->get(route('vault.my'));
        $response->assertStatus(200);
    }
}
