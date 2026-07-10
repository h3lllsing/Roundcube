<?php

namespace Tests\Feature;

use App\Models\Hosting;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\OtherService;
use App\Models\User;
use App\Models\UserModulePermission;
use App\Models\VaultEntry;
use App\Models\Vps;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacPhase2C3Test extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Module $hostingsModule;

    private Module $vpsModule;

    private Module $vaultModule;

    private Module $deniedModule;

    private User $superAdmin;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->adminRole = Role::where('slug', 'admin')->firstOrFail();

        $this->hostingsModule = Module::where('slug', 'hostings')->firstOrFail();
        $this->vpsModule = Module::where('slug', 'vps')->firstOrFail();
        $this->vaultModule = Module::where('slug', 'vault')->firstOrFail();
        $this->deniedModule = Module::where('slug', 'other-services')->firstOrFail();

        foreach ([$this->hostingsModule, $this->vpsModule, $this->vaultModule] as $m) {
            ModuleRolePermission::updateOrCreate(
                ['module_id' => $m->id, 'role_id' => $this->adminRole->id],
                ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => false, 'can_export' => true, 'can_reveal' => true]
            );
        }
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->deniedModule->id, 'role_id' => $this->adminRole->id],
            ['can_create' => false, 'can_read' => true, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false, 'can_reveal' => false]
        );

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superRole);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($this->adminRole);
    }

    public function test_super_admin_sees_copy_button_on_show(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->superAdmin->id]);

        $response = $this->actingAs($this->superAdmin)->get(route('hostings.show', $hosting->id));
        $response->assertOk();
        $response->assertSee('Copy password');
    }

    public function test_super_admin_sees_show_button_on_show(): void
    {
        $vps = Vps::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->superAdmin->id]);

        $response = $this->actingAs($this->superAdmin)->get(route('vps.show', $vps->id));
        $response->assertOk();
        $response->assertSee('Toggle password visibility');
    }

    public function test_copy_button_visible_on_show_when_can_reveal_true(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('hostings.show', $hosting->id));
        $response->assertOk();
        $response->assertSee('Copy password');
    }

    public function test_copy_button_hidden_on_index_when_can_reveal_false(): void
    {
        OtherService::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('other-services.index'));
        $response->assertOk();
        $response->assertDontSee('Copy password');
    }

    public function test_show_button_visible_on_show_when_can_reveal_true(): void
    {
        $vps = Vps::factory()->create(['module_id' => $this->vpsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('vps.show', $vps->id));
        $response->assertOk();
        $response->assertSee('Toggle password visibility');
    }

    public function test_show_button_hidden_on_show_when_can_reveal_false(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->vaultModule->id,
            'can_reveal' => false,
        ]);
        $service = OtherService::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('other-services.show', $service->id));
        $response->assertOk();
        $response->assertDontSeeHtml('>Show<');
    }

    public function test_vault_reveal_form_visible_when_can_reveal_true(): void
    {
        $entry = VaultEntry::factory()->create(['module_id' => $this->vaultModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('vault.show', $entry->id));
        $response->assertOk();
        $response->assertSee('reveal-form');
    }

    public function test_vault_reveal_form_hidden_when_can_reveal_false(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->vaultModule->id,
            'can_reveal' => false,
        ]);
        $entry = VaultEntry::factory()->create(['module_id' => $this->vaultModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('vault.show', $entry->id));
        $response->assertOk();
        $response->assertDontSee('reveal-form');
    }

    public function test_override_true_shows_reveal_buttons(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->deniedModule->id,
            'can_reveal' => true,
        ]);
        $service = OtherService::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->admin->id, 'password' => 'secret123']);

        $response = $this->actingAs($this->admin)->get(route('other-services.show', $service->id));
        $response->assertOk();
        $response->assertSeeHtml('>Show<');
    }

    public function test_override_false_hides_reveal_buttons(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->vaultModule->id,
            'can_reveal' => false,
        ]);
        $vps = Vps::factory()->create(['module_id' => $this->vpsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('vps.show', $vps->id));
        $response->assertOk();
        $response->assertDontSee('Toggle password visibility');
    }

    public function test_override_on_show_shows_copy_button_when_can_reveal_true(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->deniedModule->id,
            'can_reveal' => true,
        ]);
        $service = OtherService::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->admin->id, 'password' => 'secret123']);

        $response = $this->actingAs($this->admin)->get(route('other-services.show', $service->id));
        $response->assertOk();
        $response->assertSee('Copy password');
    }

    public function test_server_side_reveal_guard_still_returns_403(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->vaultModule->id,
            'can_reveal' => false,
        ]);
        $service = OtherService::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->getJson(route('other-services.password', $service->id));
        $response->assertForbidden();
    }

    public function test_hostings_show_copy_button_visible_with_allowed_module(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('hostings.show', $hosting->id));
        $response->assertOk();
        $response->assertSee('Copy password');
    }

    public function test_vault_show_reveal_form_visible_with_allowed_module(): void
    {
        $entry = VaultEntry::factory()->create(['module_id' => $this->vaultModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('vault.show', $entry->id));
        $response->assertOk();
        $response->assertSee('reveal-form');
    }
}
