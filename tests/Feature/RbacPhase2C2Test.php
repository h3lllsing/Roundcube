<?php

namespace Tests\Feature;

use App\Models\Domain;
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

class RbacPhase2C2Test extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Module $domainsModule;

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

        $this->domainsModule = Module::where('slug', 'domains')->firstOrFail();
        $this->hostingsModule = Module::where('slug', 'hostings')->firstOrFail();
        $this->vpsModule = Module::where('slug', 'vps')->firstOrFail();
        $this->vaultModule = Module::where('slug', 'vault')->firstOrFail();
        $this->deniedModule = Module::where('slug', 'other-services')->firstOrFail();

        // Admin: can_update=true, can_delete=true on domains, hostings, vps, vault
        foreach ([$this->domainsModule, $this->hostingsModule, $this->vpsModule, $this->vaultModule] as $m) {
            ModuleRolePermission::updateOrCreate(
                ['module_id' => $m->id, 'role_id' => $this->adminRole->id],
                ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => false, 'can_export' => true, 'can_reveal' => true]
            );
        }
        // Admin: can_read but no can_update/can_delete on denied-module
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->deniedModule->id, 'role_id' => $this->adminRole->id],
            ['can_create' => false, 'can_read' => true, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false, 'can_reveal' => false]
        );

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superRole);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($this->adminRole);
    }

    // ─── SUPER ADMIN — SEES ALL ─────────────────────────────────────

    public function test_super_admin_sees_edit_and_delete_on_index(): void
    {
        $domain = Domain::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->superAdmin->id]);

        $response = $this->actingAs($this->superAdmin)->get(route('domains.index'));
        $response->assertOk();
        $response->assertSee('Edit');
        $response->assertSeeHtml('action="' . route('domains.destroy', $domain->id) . '"');
    }

    public function test_super_admin_sees_edit_and_delete_on_show(): void
    {
        $domain = Domain::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->superAdmin->id]);

        $response = $this->actingAs($this->superAdmin)->get(route('domains.show', $domain->id));
        $response->assertOk();
        $response->assertSee('Edit');
        $response->assertSeeHtml('action="' . route('domains.destroy', $domain->id) . '"');
    }

    // ─── EDIT BUTTON ON INDEX ───────────────────────────────────────

    public function test_edit_button_visible_on_index_when_can_update_true(): void
    {
        Domain::factory()->create(['module_id' => $this->domainsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('domains.index'));
        $response->assertOk();
        $response->assertSee('Edit');
    }

    public function test_edit_button_hidden_on_index_when_can_update_false(): void
    {
        OtherService::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('other-services.index'));
        $response->assertOk();
        $response->assertDontSee('Edit');
    }

    // ─── DELETE BUTTON ON INDEX ─────────────────────────────────────

    public function test_delete_button_visible_on_index_when_can_delete_true(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('hostings.index'));
        $response->assertOk();
        $response->assertSeeHtml('action="' . route('hostings.destroy', $hosting->id) . '"');
    }

    public function test_delete_button_hidden_on_index_when_can_delete_false(): void
    {
        $service = OtherService::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('other-services.index'));
        $response->assertOk();
        $response->assertDontSeeHtml('action="' . route('other-services.destroy', $service->id) . '"');
    }

    // ─── EDIT BUTTON ON SHOW ────────────────────────────────────────

    public function test_edit_button_visible_on_show_when_can_update_true(): void
    {
        $vps = Vps::factory()->create(['module_id' => $this->vpsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('vps.show', $vps->id));
        $response->assertOk();
        $response->assertSee('Edit');
    }

    public function test_edit_button_hidden_on_show_when_can_update_false(): void
    {
        $service = OtherService::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('other-services.show', $service->id));
        $response->assertOk();
        $response->assertDontSee('Edit');
    }

    // ─── DELETE BUTTON ON SHOW ──────────────────────────────────────

    public function test_delete_button_visible_on_show_when_can_delete_true(): void
    {
        $entry = VaultEntry::factory()->create(['module_id' => $this->vaultModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('vault.show', $entry->id));
        $response->assertOk();
        $response->assertSeeHtml('action="' . route('vault.destroy', $entry->id) . '"');
    }

    public function test_delete_button_hidden_on_show_when_can_delete_false(): void
    {
        $service = OtherService::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('other-services.show', $service->id));
        $response->assertOk();
        $response->assertDontSeeHtml('action="' . route('other-services.destroy', $service->id) . '"');
    }

    // ─── USER OVERRIDES ─────────────────────────────────────────────

    public function test_override_true_shows_edit_and_delete_when_role_denies(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->deniedModule->id,
            'can_update' => true, 'can_delete' => true,
        ]);
        $service = OtherService::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('other-services.show', $service->id));
        $response->assertOk();
        $response->assertSee('Edit');
        $response->assertSeeHtml('action="' . route('other-services.destroy', $service->id) . '"');
    }

    public function test_override_false_hides_edit_and_delete_when_role_allows(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->domainsModule->id,
            'can_update' => false, 'can_delete' => false,
        ]);
        $domain = Domain::factory()->create(['module_id' => $this->domainsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('domains.show', $domain->id));
        $response->assertOk();
        $response->assertDontSee('Edit');
        $response->assertDontSeeHtml('action="' . route('domains.destroy', $domain->id) . '"');
    }

    public function test_override_on_index_shows_hides_buttons(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->deniedModule->id,
            'can_update' => true, 'can_delete' => true,
        ]);
        $service = OtherService::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('other-services.index'));
        $response->assertOk();
        $response->assertSee('Edit');
        $response->assertSeeHtml('action="' . route('other-services.destroy', $service->id) . '"');
    }

    // ─── SERVER-SIDE GUARD STILL ENFORCES ───────────────────────────

    public function test_server_side_update_guard_still_returns_403(): void
    {
        $service = OtherService::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('other-services.edit', $service->id));
        $response->assertForbidden();
    }

    public function test_server_side_delete_guard_still_returns_403(): void
    {
        $service = OtherService::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete(route('other-services.destroy', $service->id));
        $response->assertForbidden();
    }

    // ─── PER-MODULE COVERAGE ────────────────────────────────────────

    public function test_domains_index_shows_edit_delete_for_allowed_module(): void
    {
        $domain = Domain::factory()->create(['module_id' => $this->domainsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('domains.index'));
        $response->assertOk();
        $response->assertSee('Edit');
        $response->assertSeeHtml('action="' . route('domains.destroy', $domain->id) . '"');
    }

    public function test_hostings_index_shows_edit_delete_for_allowed_module(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('hostings.index'));
        $response->assertOk();
        $response->assertSee('Edit');
        $response->assertSeeHtml('action="' . route('hostings.destroy', $hosting->id) . '"');
    }

    public function test_vault_show_shows_edit_delete_for_allowed_module(): void
    {
        $entry = VaultEntry::factory()->create(['module_id' => $this->vaultModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('vault.show', $entry->id));
        $response->assertOk();
        $response->assertSee('Edit');
        $response->assertSeeHtml('action="' . route('vault.destroy', $entry->id) . '"');
    }
}
