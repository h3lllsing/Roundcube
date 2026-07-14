<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Privilege;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
    }

    public function test_index_page_loads(): void
    {
        Role::create(['name' => 'CustomRole', 'slug' => 'custom-role']);
        $this->actingAs($this->admin);
        $this->get(route('roles.index'))->assertStatus(200)->assertSee('CustomRole');
    }

    public function test_index_search_filter(): void
    {
        Role::create(['name' => 'SearchableRole', 'slug' => 'searchable-role']);
        $this->actingAs($this->admin);
        $response = $this->get(route('roles.index', ['search' => 'Searchable']));
        $response->assertStatus(200)->assertSee('SearchableRole');
    }

    public function test_create_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('roles.create'))->assertStatus(200)->assertSee('Create');
    }

    public function test_store_creates_role(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('roles.store'), [
            'name' => 'NewEditor',
            'slug' => 'new-editor',
        ])->assertRedirect(route('roles.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['slug' => 'new-editor']);
    }

    public function test_store_validates(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('roles.store'), [])
            ->assertSessionHasErrors(['name', 'slug']);
    }

    public function test_show_displays_role(): void
    {
        $role = Role::create(['name' => 'ViewableRole', 'slug' => 'viewable-role']);
        $this->actingAs($this->admin);
        $this->get(route('roles.show', $role->id))
            ->assertStatus(200)
            ->assertSee('ViewableRole');
    }

    public function test_edit_page_loads(): void
    {
        $role = Role::create(['name' => 'EditableRole', 'slug' => 'editable-role']);
        $this->actingAs($this->admin);
        $this->get(route('roles.edit', $role->id))
            ->assertStatus(200)
            ->assertSee($role->name);
    }

    public function test_update_modifies_role(): void
    {
        $role = Role::create(['name' => 'OldName', 'slug' => 'old-name']);
        $this->actingAs($this->admin);
        $this->put(route('roles.update', $role->id), [
            'name' => 'NewName',
            'slug' => $role->slug,
        ])->assertRedirect(route('roles.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'NewName']);
    }

    public function test_destroy_protected_role_blocked(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $this->actingAs($this->admin);
        $this->delete(route('roles.destroy', $adminRole->id))
            ->assertRedirect(route('roles.index'))
            ->assertSessionHas('error', 'Protected roles cannot be deleted.');

        $this->assertDatabaseHas('roles', ['id' => $adminRole->id]);
    }

    public function test_destroy_unprotected_role_succeeds(): void
    {
        $role = Role::create(['name' => 'DeletableRole', 'slug' => 'deletable-role']);
        $this->actingAs($this->admin);
        $this->delete(route('roles.destroy', $role->id))
            ->assertRedirect(route('roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
        $this->assertNotNull($role->fresh()->deleted_at);
    }

    public function test_attach_privilege(): void
    {
        $role = Role::create(['name' => 'TestRole', 'slug' => 'test-role']);
        $privilege = Privilege::create(['name' => 'TestPriv', 'slug' => 'test-priv']);
        $this->actingAs($this->admin);
        $this->post(route('roles.privileges.attach', $role->id), [
            'privilege_id' => $privilege->id,
        ])->assertRedirect(route('roles.show', $role->id))->assertSessionHas('success');

        $this->assertDatabaseHas('privilege_role', [
            'role_id' => $role->id,
            'privilege_id' => $privilege->id,
        ]);
    }

    public function test_detach_privilege(): void
    {
        $role = Role::create(['name' => 'TestRole2', 'slug' => 'test-role-2']);
        $privilege = Privilege::create(['name' => 'TestPriv2', 'slug' => 'test-priv-2']);
        $role->attachPrivilege($privilege);
        $this->actingAs($this->admin);
        $this->post(route('roles.privileges.detach', $role->id), [
            'privilege_id' => $privilege->id,
        ])->assertRedirect(route('roles.show', $role->id))->assertSessionHas('success');

        $this->assertDatabaseMissing('privilege_role', [
            'role_id' => $role->id,
            'privilege_id' => $privilege->id,
        ]);
    }

    public function test_guest_cannot_access_role_pages(): void
    {
        $this->get(route('roles.index'))->assertRedirect(route('login'));
        $this->get(route('roles.create'))->assertRedirect(route('login'));
    }

    public function test_show_displays_configure_permissions_link(): void
    {
        $this->seed(FeatureModuleSeeder::class);
        $role = Role::create(['name' => 'TestRole', 'slug' => 'test-role']);
        $this->actingAs($this->admin);
        $response = $this->get(route('roles.show', $role->id));
        $response->assertStatus(200);
        $response->assertSee('Configure Permissions');
        $response->assertSee(route('module-permissions.index', ['role_id' => $role->id]));
    }

    public function test_show_displays_module_access_summary(): void
    {
        $this->seed(FeatureModuleSeeder::class);
        $role = Role::create(['name' => 'SummaryRole', 'slug' => 'summary-role']);
        $module = Module::firstOrFail();
        ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $role->id,
            'can_read' => true,
        ]);
        $totalModules = Module::count();

        $this->actingAs($this->admin);
        $response = $this->get(route('roles.show', $role->id));
        $response->assertStatus(200);
        $response->assertSee('Module Access Summary');
        $response->assertSee((string) $totalModules);
        $response->assertSee('With Access');
        $response->assertSee('No Access');
        $response->assertSee('Sensitive Granted');
    }
}
