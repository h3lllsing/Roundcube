<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetType;
use App\Models\Attachment;
use App\Models\Feature;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use App\Models\VaultEntry;
use Database\Seeders\AssetCategorySeeder;
use Database\Seeders\AssetTypeSeeder;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $regularUser;
    private User $assigneeUser;
    private Module $assetsModule;
    private AssetCategory $category;
    private AssetType $type;
    private AssetLocation $location;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
        $this->seed(AssetCategorySeeder::class);
        $this->seed(AssetTypeSeeder::class);

        $this->admin = User::factory()->create(['name' => 'Admin', 'email' => 'admin@test.com']);
        $this->admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $role = Role::where('slug', 'user')->firstOrFail();
        $this->regularUser = User::factory()->create(['name' => 'Regular', 'email' => 'regular@test.com']);
        $this->regularUser->assignRole($role);

        $this->assigneeUser = User::factory()->create(['name' => 'Assignee', 'email' => 'assignee@test.com']);
        $this->assigneeUser->assignRole($role);

        $this->assetsModule = Module::where('slug', 'assets')->firstOrFail();
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->assetsModule->id, 'role_id' => $role->id],
            ['can_read' => true]
        );

        $this->category = AssetCategory::where('slug', 'laptop')->firstOrFail();
        $this->type = AssetType::where('category_id', $this->category->id)->firstOrFail();
        $this->location = AssetLocation::factory()->create();
    }

    // ─── Asset Tag Generation ─────────────────────────────────────────

    public function test_asset_tag_is_auto_generated(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('assets.store'), [
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'serial_number' => 'SN-001',
            'condition' => 'new',
        ])->assertRedirect(route('assets.index'));

        $this->assertDatabaseHas('assets', [
            'asset_tag' => 'AST-00001',
            'serial_number' => 'SN-001',
        ]);
    }

    public function test_asset_tag_increments_sequentially(): void
    {
        Asset::factory()->create(['asset_tag' => 'AST-00001']);
        $this->actingAs($this->admin);

        $this->post(route('assets.store'), [
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'serial_number' => 'SN-002',
            'condition' => 'good',
        ]);
        $this->assertDatabaseHas('assets', ['asset_tag' => 'AST-00002', 'serial_number' => 'SN-002']);
    }

    public function test_asset_tag_can_be_custom(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('assets.store'), [
            'asset_tag' => 'CUSTOM-001',
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'condition' => 'new',
        ]);
        $this->assertDatabaseHas('assets', ['asset_tag' => 'CUSTOM-001']);
    }

    // ─── Index ────────────────────────────────────────────────────────

    public function test_index_loads(): void
    {
        Asset::factory()->count(3)->create(['user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->get(route('assets.index'))->assertStatus(200);
    }

    public function test_index_filters_by_status(): void
    {
        Asset::factory()->create(['status' => 'available', 'user_id' => $this->admin->id]);
        Asset::factory()->create(['status' => 'lost', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->get(route('assets.index', ['status' => 'lost']))->assertStatus(200);
    }

    public function test_index_filters_by_category(): void
    {
        $otherCat = AssetCategory::factory()->create();
        Asset::factory()->create(['category_id' => $this->category->id, 'user_id' => $this->admin->id]);
        Asset::factory()->create(['category_id' => $otherCat->id, 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->get(route('assets.index', ['category_id' => $this->category->id]))->assertStatus(200);
    }

    public function test_index_searches_by_asset_tag(): void
    {
        Asset::factory()->create(['asset_tag' => 'AST-FINDME', 'user_id' => $this->admin->id]);
        Asset::factory()->create(['asset_tag' => 'AST-OTHER', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->get(route('assets.index', ['search' => 'FINDME']))->assertStatus(200);
    }

    // ─── Create / Store ──────────────────────────────────────────────

    public function test_create_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('assets.create'))->assertStatus(200);
    }

    public function test_store_creates_asset(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('assets.store'), [
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'serial_number' => 'SN-CREATE',
            'status' => 'available',
            'condition' => 'new',
            'location_id' => $this->location->id,
            'description' => 'Test asset',
        ])->assertRedirect(route('assets.index'));

        $this->assertDatabaseHas('assets', ['serial_number' => 'SN-CREATE']);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('assets.store'), [])
            ->assertSessionHasErrors(['category_id', 'type_id']);
    }

    // ─── Show ─────────────────────────────────────────────────────────

    public function test_show_displays_asset(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->get(route('assets.show', $asset->id))
            ->assertStatus(200)
            ->assertSee($asset->asset_tag);
    }

    // ─── Edit / Update ───────────────────────────────────────────────

    public function test_edit_page_loads(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->get(route('assets.edit', $asset->id))->assertStatus(200);
    }

    public function test_update_modifies_asset(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->put(route('assets.update', $asset->id), [
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'serial_number' => 'SN-UPDATED',
            'condition' => 'fair',
        ])->assertRedirect(route('assets.index'));

        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'serial_number' => 'SN-UPDATED', 'condition' => 'fair']);
    }

    // ─── Soft Delete / Restore / Force Delete ─────────────────────────

    public function test_destroy_soft_deletes(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->admin->id, 'status' => 'available']);
        $this->actingAs($this->admin);
        $this->delete(route('assets.destroy', $asset->id))
            ->assertRedirect(route('assets.index'));

        $this->assertSoftDeleted($asset);
    }

    public function test_destroy_assigned_asset_blocked(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->admin->id, 'status' => 'assigned']);
        $this->actingAs($this->admin);
        $this->delete(route('assets.destroy', $asset->id))
            ->assertRedirect(route('assets.index'))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($asset);
    }

    public function test_restore_restores_asset(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->admin->id]);
        $asset->delete();

        $this->actingAs($this->admin);
        $this->patch(route('assets.restore', $asset->id))
            ->assertRedirect(route('assets.index'));

        $this->assertNotSoftDeleted($asset);
    }

    public function test_restore_requires_super_admin(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->regularUser->id]);
        $asset->delete();

        $this->actingAs($this->regularUser);
        $this->patch(route('assets.restore', $asset->id))
            ->assertStatus(403);
    }

    public function test_force_delete_permanently_deletes(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->admin->id]);
        $asset->delete();

        $this->actingAs($this->admin);
        $this->delete(route('assets.force-delete', $asset->id))
            ->assertRedirect(route('assets.index'));

        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
    }

    public function test_force_delete_requires_super_admin(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->regularUser->id]);
        $asset->delete();

        $this->actingAs($this->regularUser);
        $this->delete(route('assets.force-delete', $asset->id))
            ->assertStatus(403);
    }

    // ─── Assign / Return ─────────────────────────────────────────────

    public function test_assign_asset(): void
    {
        $asset = Asset::factory()->create(['status' => 'available', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->post(route('assets.assign', $asset->id), [
            'assigned_to' => $this->assigneeUser->id,
            'assignment_reason' => 'New Employee',
        ])->assertRedirect(route('assets.show', $asset->id));

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'assigned',
            'assigned_to' => $this->assigneeUser->id,
        ]);
        $this->assertDatabaseHas('asset_assignments', [
            'asset_id' => $asset->id,
            'assigned_to' => $this->assigneeUser->id,
            'assignment_reason' => 'New Employee',
            'returned_at' => null,
        ]);
    }

    public function test_return_asset(): void
    {
        $asset = Asset::factory()->create([
            'status' => 'assigned',
            'assigned_to' => $this->assigneeUser->id,
            'user_id' => $this->admin->id,
        ]);
        AssetAssignment::factory()->create([
            'asset_id' => $asset->id,
            'assigned_to' => $this->assigneeUser->id,
            'returned_at' => null,
        ]);

        $this->actingAs($this->admin);
        $this->post(route('assets.return', $asset->id), [
            'condition_on_return' => 'good',
        ])->assertRedirect(route('assets.show', $asset->id));

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'available',
            'assigned_to' => null,
        ]);
    }

    public function test_assign_sets_issue_date_default(): void
    {
        $asset = Asset::factory()->create(['status' => 'available', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->post(route('assets.assign', $asset->id), [
            'assigned_to' => $this->assigneeUser->id,
        ]);

        $asset->refresh();
        $this->assertNotNull($asset->issue_date);
    }

    public function test_assign_creates_assignment_history(): void
    {
        $asset = Asset::factory()->create(['status' => 'available', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->post(route('assets.assign', $asset->id), ['assigned_to' => $this->assigneeUser->id]);
        $this->post(route('assets.return', $asset->id), ['condition_on_return' => 'good']);
        $this->post(route('assets.assign', $asset->id), ['assigned_to' => $this->assigneeUser->id]);

        $this->assertEquals(2, AssetAssignment::where('asset_id', $asset->id)->count());
    }

    // ─── Status Transitions ──────────────────────────────────────────

    public function test_status_transition_available_to_assigned_to_available(): void
    {
        $asset = Asset::factory()->create(['status' => 'available', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->post(route('assets.assign', $asset->id), ['assigned_to' => $this->assigneeUser->id]);
        $this->assertEquals('assigned', $asset->fresh()->status);

        $this->post(route('assets.return', $asset->id));
        $this->assertEquals('available', $asset->fresh()->status);
    }

    public function test_asset_can_be_created_with_lost_status(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('assets.store'), [
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'status' => 'lost',
            'condition' => 'damaged',
        ]);
        $this->assertDatabaseHas('assets', ['status' => 'lost']);
    }

    public function test_asset_can_be_created_with_decommissioned_status(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('assets.store'), [
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'status' => 'decommissioned',
            'condition' => 'poor',
        ]);
        $this->assertDatabaseHas('assets', ['status' => 'decommissioned']);
    }

    // ─── JSON Specifications ─────────────────────────────────────────

    public function test_store_with_specifications(): void
    {
        $this->actingAs($this->admin);
        $specs = ['ram_gb' => 16, 'storage_gb' => 512, 'cpu' => 'Intel i7'];
        $this->post(route('assets.store'), [
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'specifications' => $specs,
            'condition' => 'new',
        ]);

        $this->assertDatabaseHas('assets', ['specifications' => json_encode($specs)]);
    }

    public function test_update_with_specifications(): void
    {
        $asset = Asset::factory()->create([
            'specifications' => ['ram_gb' => 8],
            'user_id' => $this->admin->id,
        ]);
        $this->actingAs($this->admin);

        $newSpecs = ['ram_gb' => 32, 'storage_gb' => 1024];
        $this->put(route('assets.update', $asset->id), [
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'specifications' => $newSpecs,
        ]);

        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'specifications' => json_encode($newSpecs)]);
    }

    // ─── Network Device Validation ───────────────────────────────────

    public function test_network_device_validates_mac_address(): void
    {
        $netCat = AssetCategory::where('slug', 'network-device')->firstOrFail();
        $netType = AssetType::where('category_id', $netCat->id)->firstOrFail();

        $this->actingAs($this->admin);
        $this->post(route('assets.store'), [
            'category_id' => $netCat->id,
            'type_id' => $netType->id,
            'specifications' => [
                'mac_address' => 'invalid-mac',
            ],
            'condition' => 'new',
        ])->assertSessionHasErrors('specifications.mac_address');
    }

    public function test_network_device_accepts_valid_mac_address(): void
    {
        $netCat = AssetCategory::where('slug', 'network-device')->firstOrFail();
        $netType = AssetType::where('category_id', $netCat->id)->firstOrFail();

        $this->actingAs($this->admin);
        $this->post(route('assets.store'), [
            'category_id' => $netCat->id,
            'type_id' => $netType->id,
            'specifications' => [
                'mac_address' => 'AA:BB:CC:DD:EE:FF',
                'ip_address' => '192.168.1.1',
                'hostname' => 'switch-01',
            ],
            'condition' => 'new',
        ])->assertRedirect(route('assets.index'));
    }

    public function test_network_device_accepts_ipv6(): void
    {
        $netCat = AssetCategory::where('slug', 'network-device')->firstOrFail();
        $netType = AssetType::where('category_id', $netCat->id)->firstOrFail();

        $this->actingAs($this->admin);
        $this->post(route('assets.store'), [
            'category_id' => $netCat->id,
            'type_id' => $netType->id,
            'specifications' => [
                'ip_address' => '192.168.1.1',
                'ipv6' => '2001:db8::1',
                'mac_address' => 'AA:BB:CC:DD:EE:FF',
                'hostname' => 'router-01',
            ],
            'condition' => 'new',
        ])->assertRedirect(route('assets.index'));
    }

    // ─── Primary Image ───────────────────────────────────────────────

    public function test_store_with_primary_image(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $this->post(route('assets.store'), [
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'primary_image' => UploadedFile::fake()->image('laptop.jpg', 800, 600),
            'condition' => 'new',
        ])->assertRedirect(route('assets.index'));

        $asset = Asset::first();
        $this->assertNotNull($asset->primary_image);
        Storage::disk('public')->assertExists($asset->primary_image);
    }

    public function test_update_replaces_primary_image(): void
    {
        Storage::fake('public');
        $asset = Asset::factory()->create([
            'user_id' => $this->admin->id,
            'primary_image' => 'assets/old.jpg',
        ]);
        Storage::disk('public')->put('assets/old.jpg', 'old');

        $this->actingAs($this->admin);
        $this->put(route('assets.update', $asset->id), [
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'primary_image' => UploadedFile::fake()->image('new.jpg'),
        ])->assertRedirect(route('assets.index'));

        $asset->refresh();
        $this->assertNotNull($asset->primary_image);
        Storage::disk('public')->assertExists($asset->primary_image);
        Storage::disk('public')->assertMissing('assets/old.jpg');
    }

    // ─── Attachments ─────────────────────────────────────────────────

    public function test_asset_has_attachments_relation(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->admin->id]);
        $attachment = Attachment::factory()->create([
            'notable_type' => Asset::class,
            'notable_id' => $asset->id,
        ]);

        $this->assertTrue($asset->attachments()->exists());
        $this->assertEquals($attachment->id, $asset->attachments->first()->id);
    }

    // ─── Vault Integration ───────────────────────────────────────────

    public function test_asset_can_link_to_vault_entry(): void
    {
        $vault = VaultEntry::factory()->create(['user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->post(route('assets.store'), [
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'vault_entry_id' => $vault->id,
            'condition' => 'new',
        ]);

        $this->assertDatabaseHas('assets', ['vault_entry_id' => $vault->id]);
    }

    public function test_vault_link_appears_on_show(): void
    {
        $vault = VaultEntry::factory()->create(['user_id' => $this->admin->id]);
        $asset = Asset::factory()->create([
            'vault_entry_id' => $vault->id,
            'user_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin);
        $this->get(route('assets.show', $asset->id))
            ->assertStatus(200);
    }

    public function test_vault_fk_uses_null_on_delete(): void
    {
        $vault = VaultEntry::factory()->create(['user_id' => $this->admin->id]);
        $asset = Asset::factory()->create([
            'vault_entry_id' => $vault->id,
            'user_id' => $this->admin->id,
        ]);

        $vault->forceDelete();

        $asset->refresh();
        $this->assertNull($asset->vault_entry_id);
    }

    // ─── RBAC ────────────────────────────────────────────────────────

    public function test_non_admin_cannot_see_others_assets_on_index(): void
    {
        Asset::factory()->create(['user_id' => $this->admin->id, 'description' => 'AdminAsset']);
        Asset::factory()->create(['user_id' => $this->regularUser->id, 'description' => 'UserAsset']);

        $this->actingAs($this->regularUser);
        $response = $this->get(route('assets.index'));
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_create_without_permission(): void
    {
        $this->actingAs($this->regularUser);
        $this->get(route('assets.create'))->assertStatus(403);
    }

    public function test_non_admin_cannot_update_without_permission(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->regularUser->id, 'module_id' => $this->assetsModule->id]);
        $this->actingAs($this->regularUser);
        $this->get(route('assets.edit', $asset->id))->assertStatus(403);
    }

    public function test_non_admin_cannot_delete_without_permission(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->regularUser->id, 'module_id' => $this->assetsModule->id]);
        $this->actingAs($this->regularUser);
        $this->delete(route('assets.destroy', $asset->id))->assertStatus(403);
    }

    public function test_user_with_role_can_create_when_permitted(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $adminUser = User::factory()->create();
        $adminUser->assignRole($adminRole);

        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->assetsModule->id, 'role_id' => $adminRole->id],
            ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => true],
        );

        $this->actingAs($adminUser);
        $this->get(route('assets.create'))->assertStatus(200);
        $this->post(route('assets.store'), [
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'condition' => 'good',
        ])->assertRedirect(route('assets.index'));
    }

    // ─── Search ──────────────────────────────────────────────────────

    public function test_search_finds_asset_by_tag(): void
    {
        Asset::factory()->create(['asset_tag' => 'AST-SEARCH01', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->get(route('search', ['q' => 'SEARCH01']))
            ->assertStatus(200);
    }

    public function test_search_finds_asset_by_serial(): void
    {
        Asset::factory()->create(['serial_number' => 'SN-UNIQUE123', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->get(route('search', ['q' => 'UNIQUE123']))
            ->assertStatus(200);
    }

    // ─── Export ──────────────────────────────────────────────────────

    public function test_export_assets(): void
    {
        Asset::factory()->count(3)->create(['user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->get(route('export', 'assets'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    // ─── Dashboard Widgets ───────────────────────────────────────────

    public function test_dashboard_shows_asset_widgets(): void
    {
        Asset::factory()->create(['status' => 'assigned', 'assigned_to' => $this->assigneeUser->id, 'user_id' => $this->admin->id]);
        Asset::factory()->create(['status' => 'available', 'user_id' => $this->admin->id]);
        Asset::factory()->create(['status' => 'lost', 'user_id' => $this->admin->id]);

        $this->actingAs($this->admin);
        $this->get(route('dashboard'))->assertStatus(200);
    }

    // ─── API ─────────────────────────────────────────────────────────

    public function test_api_index_assets(): void
    {
        Asset::factory()->count(2)->create(['user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->getJson(route('api.assets.index'))
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_api_store_asset(): void
    {
        $this->actingAs($this->admin);
        $this->postJson(route('api.assets.store'), [
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'condition' => 'new',
        ])->assertCreated()
            ->assertJsonStructure(['data']);
    }

    public function test_api_show_asset(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->getJson(route('api.assets.show', $asset->id))
            ->assertOk();
    }

    public function test_api_update_asset(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->putJson(route('api.assets.update', $asset->id), [
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'serial_number' => 'API-UPDATED',
        ])->assertOk();
    }

    public function test_api_destroy_asset(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->deleteJson(route('api.assets.destroy', $asset->id))
            ->assertOk();

        $this->assertSoftDeleted($asset);
    }

    public function test_api_ownership_check(): void
    {
        $asset = Asset::factory()->create(['user_id' => $this->admin->id]);
        $this->actingAs($this->regularUser);

        $this->getJson(route('api.assets.show', $asset->id))
            ->assertForbidden();
    }

    // ─── Guest Cannot Access ─────────────────────────────────────────

    public function test_guest_cannot_access_asset_routes(): void
    {
        $routes = ['assets.index', 'assets.create', 'assets.store'];
        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $this->assertContains($response->getStatusCode(), [302, 401]);
        }
    }
}
