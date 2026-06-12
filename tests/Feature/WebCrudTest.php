<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Feature;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\Note;
use App\Models\ServiceProvider;
use App\Models\Task;
use App\Models\User;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use Carbon\Carbon;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private Module $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->seed(\Database\Seeders\FeatureModuleSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $this->module = Module::first();
    }

    // ─── Feature CRUD ───────────────────────────────────────

    public function test_create_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $this->get(route('features.create'))->assertStatus(200);
    }

    public function test_store_creates_feature(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('features.store'), [
            'name' => 'New Feature',
            'slug' => 'new-feature',
            'description' => 'Test description',
            'icon' => 'star',
            'is_active' => true,
        ])->assertRedirect(route('features.index'));

        $this->assertDatabaseHas('features', ['slug' => 'new-feature']);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('features.store'), [])->assertSessionHasErrors(['name']);
    }

    public function test_show_displays_feature(): void
    {
        $this->actingAs($this->superAdmin);
        $feature = Feature::factory()->create();
        $this->get(route('features.show', $feature->id))->assertStatus(200)->assertSee($feature->name);
    }

    public function test_edit_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $feature = Feature::factory()->create();
        $this->get(route('features.edit', $feature->id))->assertStatus(200)->assertSee($feature->name);
    }

    public function test_update_modifies_feature(): void
    {
        $this->actingAs($this->superAdmin);
        $feature = Feature::factory()->create();
        $this->put(route('features.update', $feature->id), [
            'name' => 'Updated',
            'slug' => 'updated-feature',
        ])->assertRedirect(route('features.index'));

        $this->assertDatabaseHas('features', ['id' => $feature->id, 'name' => 'Updated']);
    }

    public function test_destroy_deletes_feature(): void
    {
        $this->actingAs($this->superAdmin);
        $feature = Feature::factory()->create();
        $this->delete(route('features.destroy', $feature->id))->assertRedirect(route('features.index'));

        $this->assertSoftDeleted($feature);
    }

    // ─── Domain CRUD ────────────────────────────────────────

    public function test_domain_create_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $this->get(route('domains.create'))->assertStatus(200);
    }

    public function test_domain_store_creates(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('domains.store'), [
            'name' => 'example.org',
            'status' => 'active',
            'cost' => 15.99,
            'expiry_date' => '2026-12-31',
        ])->assertRedirect(route('domains.index'));

        $this->assertDatabaseHas('domains', ['name' => 'example.org']);
    }

    public function test_domain_store_with_dns_servers(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('domains.store'), [
            'name' => 'example.org',
            'status' => 'active',
            'dns_servers' => 'ns1.example.com, ns2.example.com',
        ])->assertRedirect(route('domains.index'));

        $this->assertDatabaseHas('domains', ['name' => 'example.org']);
    }

    public function test_domain_store_validates_required(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('domains.store'), [])->assertSessionHasErrors(['name']);
    }

    public function test_domain_show_displays(): void
    {
        $this->actingAs($this->superAdmin);
        $domain = Domain::factory()->create();
        $this->get(route('domains.show', $domain->id))->assertStatus(200)->assertSee($domain->name);
    }

    public function test_domain_update_modifies(): void
    {
        $this->actingAs($this->superAdmin);
        $domain = Domain::factory()->create();
        $this->put(route('domains.update', $domain->id), [
            'name' => 'updated.org',
            'status' => 'active',
        ])->assertRedirect(route('domains.index'));

        $this->assertDatabaseHas('domains', ['id' => $domain->id, 'name' => 'updated.org']);
    }

    public function test_domain_update_with_dns_servers(): void
    {
        $this->actingAs($this->superAdmin);
        $domain = Domain::factory()->create();
        $this->put(route('domains.update', $domain->id), [
            'name' => $domain->name,
            'status' => 'active',
            'dns_servers' => 'ns1.updated.com',
        ])->assertRedirect(route('domains.index'));
    }

    public function test_domain_destroy_deletes(): void
    {
        $this->actingAs($this->superAdmin);
        $domain = Domain::factory()->create();
        $this->delete(route('domains.destroy', $domain->id))->assertRedirect(route('domains.index'));
        $this->assertSoftDeleted($domain);
    }

    // ─── Task CRUD ──────────────────────────────────────────

    public function test_task_create_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $this->get(route('tasks.create'))->assertStatus(200);
    }

    public function test_task_store_creates(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('tasks.store'), [
            'title' => 'Test Task',
            'module_id' => $this->module->id,
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
        ])->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', ['title' => 'Test Task']);
    }

    public function test_task_store_validates_required(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('tasks.store'), [])->assertSessionHasErrors(['title']);
    }

    public function test_task_show_displays(): void
    {
        $this->actingAs($this->superAdmin);
        $task = Task::factory()->create();
        $this->get(route('tasks.show', $task->id))->assertStatus(200)->assertSee($task->title);
    }

    public function test_task_edit_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $task = Task::factory()->create();
        $this->get(route('tasks.edit', $task->id))->assertStatus(200)->assertSee($task->title);
    }

    public function test_task_update_modifies(): void
    {
        $this->actingAs($this->superAdmin);
        $task = Task::factory()->pending()->create();
        $this->put(route('tasks.update', $task->id), [
            'title' => 'Updated Task',
            'module_id' => $this->module->id,
            'status' => 'in_progress',
            'priority' => 'high',
        ])->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Updated Task', 'status' => 'in_progress']);
    }

    public function test_task_destroy_deletes(): void
    {
        $this->actingAs($this->superAdmin);
        $task = Task::factory()->create();
        $this->delete(route('tasks.destroy', $task->id))->assertRedirect(route('tasks.index'));
        $this->assertSoftDeleted($task);
    }

    // ─── Guest blocked (smoke) ──────────────────────────────

    // ─── Module CRUD ─────────────────────────────────────────

    public function test_module_create_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $this->get(route('modules.create'))->assertStatus(200);
    }

    public function test_module_store_creates(): void
    {
        $this->actingAs($this->superAdmin);
        $feature = Feature::factory()->create();
        $this->post(route('modules.store'), [
            'feature_id' => $feature->id,
            'name' => 'Test Module',
        ])->assertRedirect(route('modules.index'));

        $this->assertDatabaseHas('modules', ['name' => 'Test Module']);
    }

    public function test_module_store_validates_required(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('modules.store'), [])->assertSessionHasErrors(['feature_id', 'name']);
    }

    public function test_module_show_displays(): void
    {
        $this->actingAs($this->superAdmin);
        $module = Module::factory()->create();
        $this->get(route('modules.show', $module->id))->assertStatus(200)->assertSee($module->name);
    }

    public function test_module_edit_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $module = Module::factory()->create();
        $this->get(route('modules.edit', $module->id))->assertStatus(200)->assertSee($module->name);
    }

    public function test_module_update_modifies(): void
    {
        $this->actingAs($this->superAdmin);
        $module = Module::factory()->create();
        $this->put(route('modules.update', $module->id), [
            'feature_id' => $module->feature_id,
            'name' => 'Updated Module',
        ])->assertRedirect(route('modules.index'));

        $this->assertDatabaseHas('modules', ['id' => $module->id, 'name' => 'Updated Module']);
    }

    public function test_module_destroy_deletes(): void
    {
        $this->actingAs($this->superAdmin);
        $module = Module::factory()->create();
        $this->delete(route('modules.destroy', $module->id))->assertRedirect(route('modules.index'));
        $this->assertSoftDeleted($module);
    }

    // ─── Vault CRUD ──────────────────────────────────────────

    public function test_vault_create_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $this->get(route('vault.create'))->assertStatus(200);
    }

    public function test_vault_store_creates(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('vault.store'), [
            'service_name' => 'My Service',
            'encrypted_password' => 'secret123',
        ])->assertRedirect(route('vault.index'));

        $this->assertDatabaseHas('password_vault', ['service_name' => 'My Service']);
    }

    public function test_vault_store_validates_required(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('vault.store'), [])->assertSessionHasErrors(['service_name']);
    }

    public function test_vault_show_displays(): void
    {
        $this->actingAs($this->superAdmin);
        $vault = VaultEntry::factory()->create();
        $this->get(route('vault.show', $vault->id))->assertStatus(200)->assertSee($vault->service_name);
    }

    public function test_vault_edit_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $vault = VaultEntry::factory()->create();
        $this->get(route('vault.edit', $vault->id))->assertStatus(200)->assertSee($vault->service_name);
    }

    public function test_vault_update_modifies(): void
    {
        $this->actingAs($this->superAdmin);
        $vault = VaultEntry::factory()->create();
        $this->put(route('vault.update', $vault->id), [
            'service_name' => 'Updated Service',
            'encrypted_password' => 'newsecret',
        ])->assertRedirect(route('vault.index'));

        $this->assertDatabaseHas('password_vault', ['id' => $vault->id, 'service_name' => 'Updated Service']);
    }

    public function test_vault_destroy_deletes(): void
    {
        $this->actingAs($this->superAdmin);
        $vault = VaultEntry::factory()->create();
        $this->delete(route('vault.destroy', $vault->id))->assertRedirect(route('vault.index'));
        $this->assertSoftDeleted($vault);
    }

    // ─── Note CRUD ───────────────────────────────────────────

    public function test_note_create_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $this->get(route('notes.create'))->assertStatus(200);
    }

    public function test_note_store_creates(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('notes.store'), [
            'content' => 'Test note content',
        ])->assertRedirect(route('notes.index'));

        $this->assertDatabaseHas('notes', ['content' => 'Test note content']);
    }

    public function test_note_store_validates_required(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('notes.store'), [])->assertSessionHasErrors(['content']);
    }

    public function test_note_show_displays(): void
    {
        $this->actingAs($this->superAdmin);
        $note = Note::factory()->create();
        $this->get(route('notes.show', $note->id))->assertStatus(200)->assertSee($note->content);
    }

    public function test_note_edit_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $note = Note::factory()->create();
        $this->get(route('notes.edit', $note->id))->assertStatus(200)->assertSee($note->content);
    }

    public function test_note_update_modifies(): void
    {
        $this->actingAs($this->superAdmin);
        $note = Note::factory()->create();
        $this->put(route('notes.update', $note->id), [
            'content' => 'Updated content',
        ])->assertRedirect(route('notes.index'));

        $this->assertDatabaseHas('notes', ['id' => $note->id, 'content' => 'Updated content']);
    }

    public function test_note_destroy_deletes(): void
    {
        $this->actingAs($this->superAdmin);
        $note = Note::factory()->create();
        $this->delete(route('notes.destroy', $note->id))->assertRedirect(route('notes.index'));
        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    // ─── Hosting CRUD ────────────────────────────────────────

    public function test_hosting_create_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $this->get(route('hostings.create'))->assertStatus(200);
    }

    public function test_hosting_store_creates(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('hostings.store'), [
            'name' => 'My Hosting',
        ])->assertRedirect(route('hostings.index'));

        $this->assertDatabaseHas('hostings', ['name' => 'My Hosting']);
    }

    public function test_hosting_store_validates_required(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('hostings.store'), [])->assertSessionHasErrors(['name']);
    }

    public function test_hosting_update_modifies(): void
    {
        $this->actingAs($this->superAdmin);
        $hosting = Hosting::factory()->create();
        $this->put(route('hostings.update', $hosting->id), [
            'name' => 'Updated Hosting',
        ])->assertRedirect(route('hostings.index'));

        $this->assertDatabaseHas('hostings', ['id' => $hosting->id, 'name' => 'Updated Hosting']);
    }

    public function test_hosting_destroy_deletes(): void
    {
        $this->actingAs($this->superAdmin);
        $hosting = Hosting::factory()->create();
        $this->delete(route('hostings.destroy', $hosting->id))->assertRedirect(route('hostings.index'));
        $this->assertSoftDeleted($hosting);
    }

    public function test_hosting_show_displays(): void
    {
        $this->actingAs($this->superAdmin);
        $hosting = Hosting::factory()->create();
        $this->get(route('hostings.show', $hosting->id))->assertStatus(200)->assertSee($hosting->name);
    }

    public function test_hosting_edit_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $hosting = Hosting::factory()->create();
        $this->get(route('hostings.edit', $hosting->id))->assertStatus(200)->assertSee($hosting->name);
    }

    // ─── ServiceProvider CRUD ────────────────────────────────

    public function test_serviceprovider_create_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $this->get(route('service-providers.create'))->assertStatus(200);
    }

    public function test_serviceprovider_store_creates(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('service-providers.store'), [
            'name' => 'My Provider',
        ])->assertRedirect(route('service-providers.index'));

        $this->assertDatabaseHas('service_providers', ['name' => 'My Provider']);
    }

    public function test_serviceprovider_store_validates_required(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('service-providers.store'), [])->assertSessionHasErrors(['name']);
    }

    public function test_serviceprovider_update_modifies(): void
    {
        $this->actingAs($this->superAdmin);
        $provider = ServiceProvider::factory()->create();
        $this->put(route('service-providers.update', $provider->id), [
            'name' => 'Updated Provider',
        ])->assertRedirect(route('service-providers.index'));

        $this->assertDatabaseHas('service_providers', ['id' => $provider->id, 'name' => 'Updated Provider']);
    }

    public function test_serviceprovider_destroy_deletes(): void
    {
        $this->actingAs($this->superAdmin);
        $provider = ServiceProvider::factory()->create();
        $this->delete(route('service-providers.destroy', $provider->id))->assertRedirect(route('service-providers.index'));
        $this->assertSoftDeleted($provider);
    }

    public function test_serviceprovider_show_displays(): void
    {
        $this->actingAs($this->superAdmin);
        $provider = ServiceProvider::factory()->create();
        $this->get(route('service-providers.show', $provider->id))->assertStatus(200)->assertSee($provider->name);
    }

    public function test_serviceprovider_edit_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $provider = ServiceProvider::factory()->create();
        $this->get(route('service-providers.edit', $provider->id))->assertStatus(200)->assertSee($provider->name);
    }

    // ─── Voip CRUD ───────────────────────────────────────────

    public function test_voip_create_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $this->get(route('voip.create'))->assertStatus(200);
    }

    public function test_voip_store_creates(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('voip.store'), [
            'name' => 'My Voip',
        ])->assertRedirect(route('voip.index'));

        $this->assertDatabaseHas('voip', ['name' => 'My Voip']);
    }

    public function test_voip_store_validates_required(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('voip.store'), [])->assertSessionHasErrors(['name']);
    }

    public function test_voip_update_modifies(): void
    {
        $this->actingAs($this->superAdmin);
        $voip = Voip::factory()->create();
        $this->put(route('voip.update', $voip->id), [
            'name' => 'Updated Voip',
        ])->assertRedirect(route('voip.index'));

        $this->assertDatabaseHas('voip', ['id' => $voip->id, 'name' => 'Updated Voip']);
    }

    public function test_voip_destroy_deletes(): void
    {
        $this->actingAs($this->superAdmin);
        $voip = Voip::factory()->create();
        $this->delete(route('voip.destroy', $voip->id))->assertRedirect(route('voip.index'));
        $this->assertSoftDeleted($voip);
    }

    public function test_voip_show_displays(): void
    {
        $this->actingAs($this->superAdmin);
        $voip = Voip::factory()->create();
        $this->get(route('voip.show', $voip->id))->assertStatus(200)->assertSee($voip->name);
    }

    public function test_voip_edit_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $voip = Voip::factory()->create();
        $this->get(route('voip.edit', $voip->id))->assertStatus(200)->assertSee($voip->name);
    }

    // ─── Vps CRUD ────────────────────────────────────────────

    public function test_vps_create_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $this->get(route('vps.create'))->assertStatus(200);
    }

    public function test_vps_store_creates(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('vps.store'), [
            'name' => 'My VPS',
        ])->assertRedirect(route('vps.index'));

        $this->assertDatabaseHas('vps', ['name' => 'My VPS']);
    }

    public function test_vps_store_validates_required(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('vps.store'), [])->assertSessionHasErrors(['name']);
    }

    public function test_vps_update_modifies(): void
    {
        $this->actingAs($this->superAdmin);
        $vps = Vps::factory()->create();
        $this->put(route('vps.update', $vps->id), [
            'name' => 'Updated VPS',
        ])->assertRedirect(route('vps.index'));

        $this->assertDatabaseHas('vps', ['id' => $vps->id, 'name' => 'Updated VPS']);
    }

    public function test_vps_destroy_deletes(): void
    {
        $this->actingAs($this->superAdmin);
        $vps = Vps::factory()->create();
        $this->delete(route('vps.destroy', $vps->id))->assertRedirect(route('vps.index'));
        $this->assertSoftDeleted($vps);
    }

    public function test_vps_show_displays(): void
    {
        $this->actingAs($this->superAdmin);
        $vps = Vps::factory()->create();
        $this->get(route('vps.show', $vps->id))->assertStatus(200)->assertSee($vps->name);
    }

    public function test_vps_edit_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $vps = Vps::factory()->create();
        $this->get(route('vps.edit', $vps->id))->assertStatus(200)->assertSee($vps->name);
    }

    public function test_domain_edit_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $domain = Domain::factory()->create();
        $this->get(route('domains.edit', $domain->id))->assertStatus(200)->assertSee($domain->name);
    }

    // ─── User CRUD ───────────────────────────────────────────

    public function test_user_create_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $this->get(route('users.create'))->assertStatus(200);
    }

    public function test_user_store_creates(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['name' => 'Test User', 'email' => 'test@example.com']);
    }

    public function test_user_store_validates_required(): void
    {
        $this->actingAs($this->superAdmin);
        $this->post(route('users.store'), [])->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_user_show_displays(): void
    {
        $this->actingAs($this->superAdmin);
        $user = User::factory()->create();
        $this->get(route('users.show', $user->id))->assertStatus(200)->assertSee($user->name);
    }

    public function test_user_edit_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $user = User::factory()->create();
        $this->get(route('users.edit', $user->id))->assertStatus(200)->assertSee($user->name);
    }

    public function test_user_update_modifies(): void
    {
        $this->actingAs($this->superAdmin);
        $user = User::factory()->create();
        $this->put(route('users.update', $user->id), [
            'name' => 'Updated User',
            'email' => $user->email,
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated User']);
    }

    public function test_user_update_with_password(): void
    {
        $this->actingAs($this->superAdmin);
        $user = User::factory()->create(['password' => bcrypt('oldpassword')]);
        $this->put(route('users.update', $user->id), [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect(route('users.index'));

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_user_update_suspended_at(): void
    {
        $this->actingAs($this->superAdmin);
        $user = User::factory()->create();
        $this->put(route('users.update', $user->id), [
            'name' => $user->name,
            'email' => $user->email,
            'suspended_at' => '2026-06-15',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'suspended_at' => '2026-06-15 00:00:00']);
    }

    public function test_user_destroy_deletes(): void
    {
        $this->actingAs($this->superAdmin);
        $user = User::factory()->create();
        $this->delete(route('users.destroy', $user->id))->assertRedirect(route('users.index'));
        $this->assertSoftDeleted($user);
    }

    public function test_activity_log_index_filters(): void
    {
        $this->actingAs($this->superAdmin);
        activity()->withProperties(['test' => true])->event('created')->log('Test activity entry');
        $this->get(route('activity-logs.index'))->assertStatus(200);
        $this->get(route('activity-logs.index', ['event' => 'created']))->assertStatus(200);
        $this->get(route('activity-logs.index', ['search' => 'Test']))->assertStatus(200);
    }

    // ─── Feature index ───────────────────────────────────────

    public function test_features_index(): void
    {
        $this->actingAs($this->superAdmin);
        $features = Feature::factory(3)->create();

        $response = $this->get(route('features.index'));
        $response->assertStatus(200);
        foreach ($features as $feature) {
            $response->assertSee($feature->name);
        }
    }

    // ─── Task index ──────────────────────────────────────────

    public function test_tasks_index(): void
    {
        $this->actingAs($this->superAdmin);
        $tasks = Task::factory(3)->create();

        $response = $this->get(route('tasks.index'));
        $response->assertStatus(200);
        foreach ($tasks as $task) {
            $response->assertSee($task->title);
        }
    }

    public function test_tasks_index_filters_by_status_and_priority(): void
    {
        $this->actingAs($this->superAdmin);
        Task::factory()->create(['status' => 'pending', 'priority' => 'low']);

        $this->get(route('tasks.index', ['status' => 'pending']))->assertStatus(200);
        $this->get(route('tasks.index', ['priority' => 'low']))->assertStatus(200);
        $this->get(route('tasks.index', ['search' => 'Task']))->assertStatus(200);
    }

    // ─── Module index ────────────────────────────────────────

    public function test_modules_index(): void
    {
        $this->actingAs($this->superAdmin);
        $modules = Module::factory(3)->create();

        $response = $this->get(route('modules.index'));
        $response->assertStatus(200);
        foreach ($modules as $module) {
            $response->assertSee($module->name);
        }
    }

    // ─── Vault index ──────────────────────────────────────────

    public function test_vault_index(): void
    {
        $this->actingAs($this->superAdmin);
        $entries = VaultEntry::factory(3)->create();

        $response = $this->get(route('vault.index'));
        $response->assertStatus(200);
        foreach ($entries as $entry) {
            $response->assertSee($entry->service_name);
        }
    }

    public function test_vault_index_searches(): void
    {
        $this->actingAs($this->superAdmin);
        VaultEntry::factory()->create(['service_name' => 'SecretService']);

        $this->get(route('vault.index', ['search' => 'Secret']))->assertStatus(200);
    }

    // ─── Note index ───────────────────────────────────────────

    public function test_notes_index(): void
    {
        $this->actingAs($this->superAdmin);
        Note::factory(3)->create();

        $this->get(route('notes.index'))->assertStatus(200)->assertSee('Notes');
    }

    public function test_notes_index_searches(): void
    {
        $this->actingAs($this->superAdmin);
        Note::factory()->create(['content' => 'Unique searchable note']);

        $this->get(route('notes.index', ['search' => 'Unique']))->assertStatus(200);
    }

    // ─── User index ───────────────────────────────────────────

    public function test_users_index(): void
    {
        $this->actingAs($this->superAdmin);
        $users = User::factory(3)->create();

        $response = $this->get(route('users.index'));
        $response->assertStatus(200);
        foreach ($users as $user) {
            $response->assertSee($user->name);
        }
    }

    public function test_users_index_searches_by_name(): void
    {
        $this->actingAs($this->superAdmin);
        $user = User::factory()->create(['name' => 'JohnDoe']);

        $this->get(route('users.index', ['search' => 'JohnDoe']))->assertStatus(200)->assertSee($user->name);
    }

    public function test_users_index_searches_by_email(): void
    {
        $this->actingAs($this->superAdmin);
        $user = User::factory()->create(['email' => 'unique@example.com']);

        $this->get(route('users.index', ['search' => 'unique@example.com']))->assertStatus(200)->assertSee($user->email);
    }

    public function test_users_index_filters_by_role(): void
    {
        $this->actingAs($this->superAdmin);

        $this->get(route('users.index', ['role' => 'super-admin']))->assertStatus(200);
    }

    // ─── Index filter/search branches ─────────────────────

    public function test_index_search_by_name(): void
    {
        $this->actingAs($this->superAdmin);
        $entities = [
            'hostings.index' => ['name' => Hosting::factory()->create()->name],
            'domains.index' => ['name' => Domain::factory()->create()->name],
            'service-providers.index' => ['name' => ServiceProvider::factory()->create()->name],
            'voip.index' => ['name' => Voip::factory()->create()->name],
            'vps.index' => ['name' => Vps::factory()->create()->name],
        ];
        foreach ($entities as $route => $params) {
            $this->get(route($route, ['search' => $params['name']]))->assertStatus(200);
        }
    }

    public function test_index_filter_by_status(): void
    {
        $this->actingAs($this->superAdmin);
        Hosting::factory()->create(['status' => 'active']);
        Domain::factory()->create(['status' => 'active']);
        ServiceProvider::factory()->create(['status' => 'active']);
        Voip::factory()->create(['status' => 'active']);
        Vps::factory()->create(['status' => 'active']);

        $routes = ['hostings.index', 'domains.index', 'service-providers.index', 'voip.index', 'vps.index'];
        foreach ($routes as $route) {
            $this->get(route($route, ['status' => 'active']))->assertStatus(200);
        }
    }

    // ─── Guest blocked (smoke) ──────────────────────────────

    public function test_guest_cannot_access_create_pages(): void
    {
        $routes = ['features.create', 'modules.create', 'tasks.create'];
        foreach ($routes as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }
}
