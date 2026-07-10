<?php

namespace Tests\Feature;

use App\Models\User;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Privilege;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivilegeTest extends TestCase
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
        Privilege::create(['name' => 'CustomPrivilege', 'slug' => 'custom-privilege']);
        $this->actingAs($this->admin);
        $this->get(route('privileges.index'))->assertStatus(200)->assertSee('CustomPrivilege');
    }

    public function test_index_search_filter(): void
    {
        Privilege::create(['name' => 'SearchablePriv', 'slug' => 'searchable-priv']);
        $this->actingAs($this->admin);
        $response = $this->get(route('privileges.index', ['search' => 'Searchable']));
        $response->assertStatus(200)->assertSee('SearchablePriv');
    }

    public function test_create_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('privileges.create'))->assertStatus(200)->assertSee('Create');
    }

    public function test_store_creates_privilege(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('privileges.store'), [
            'name' => 'CustomEdit',
            'slug' => 'custom-edit',
            'description' => 'Custom edit privilege',
        ])->assertRedirect(route('privileges.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('privileges', ['slug' => 'custom-edit']);
    }

    public function test_store_validates(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('privileges.store'), [])
            ->assertSessionHasErrors(['name', 'slug']);
    }

    public function test_show_displays_privilege(): void
    {
        $privilege = Privilege::create(['name' => 'ViewablePriv', 'slug' => 'viewable-priv']);
        $this->actingAs($this->admin);
        $this->get(route('privileges.show', $privilege->id))
            ->assertStatus(200)
            ->assertSee('ViewablePriv');
    }

    public function test_edit_page_loads(): void
    {
        $privilege = Privilege::create(['name' => 'EditablePriv', 'slug' => 'editable-priv']);
        $this->actingAs($this->admin);
        $this->get(route('privileges.edit', $privilege->id))
            ->assertStatus(200)
            ->assertSee($privilege->name);
    }

    public function test_update_modifies_privilege(): void
    {
        $privilege = Privilege::create(['name' => 'OldName', 'slug' => 'old-name']);
        $this->actingAs($this->admin);
        $this->put(route('privileges.update', $privilege->id), [
            'name' => 'NewName',
            'slug' => $privilege->slug,
        ])->assertRedirect(route('privileges.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('privileges', ['id' => $privilege->id, 'name' => 'NewName']);
    }

    public function test_destroy_deletes_privilege(): void
    {
        $privilege = Privilege::create(['name' => 'DeletablePriv', 'slug' => 'deletable-priv']);
        $this->actingAs($this->admin);
        $this->delete(route('privileges.destroy', $privilege->id))
            ->assertRedirect(route('privileges.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('privileges', ['id' => $privilege->id]);
        $this->assertNotNull($privilege->fresh()->deleted_at);
    }

    public function test_guest_cannot_access_privilege_pages(): void
    {
        $this->get(route('privileges.index'))->assertRedirect(route('login'));
    }
}
