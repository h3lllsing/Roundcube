<?php

namespace Tests\Feature;

use App\Models\DomainEmail;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\UserModulePermission;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacPhase2B3Test extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $userRole;

    private Module $hostingsModule;

    private Module $vpsModule;

    private Module $voipModule;

    private Module $serviceProvidersModule;

    private Module $domainEmailsModule;

    private Module $otherServicesModule;

    private Module $vaultModule;

    private Module $deniedModule;

    private User $superAdmin;

    private User $admin;

    private User $normalUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->adminRole = Role::where('slug', 'admin')->firstOrFail();
        $this->userRole = Role::where('slug', 'user')->firstOrFail();

        $this->hostingsModule = Module::where('slug', 'hostings')->firstOrFail();
        $this->vpsModule = Module::where('slug', 'vps')->firstOrFail();
        $this->voipModule = Module::where('slug', 'voip')->firstOrFail();
        $this->serviceProvidersModule = Module::where('slug', 'service-providers')->firstOrFail();
        $this->domainEmailsModule = Module::where('slug', 'domain-emails')->firstOrFail();
        $this->otherServicesModule = Module::where('slug', 'other-services')->firstOrFail();
        $this->vaultModule = Module::where('slug', 'vault')->firstOrFail();
        $this->deniedModule = Module::where('slug', 'tasks')->firstOrFail();

        // Admin: can_reveal + can_read on all reveal-enabled modules
        foreach ([$this->hostingsModule, $this->vpsModule, $this->voipModule, $this->serviceProvidersModule, $this->domainEmailsModule, $this->otherServicesModule, $this->vaultModule] as $m) {
            ModuleRolePermission::updateOrCreate(
                ['module_id' => $m->id, 'role_id' => $this->adminRole->id],
                ['can_read' => true, 'can_create' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false, 'can_reveal' => true]
            );
        }
        // Admin: can_read but no can_reveal on denied-module
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->deniedModule->id, 'role_id' => $this->adminRole->id],
            ['can_read' => true, 'can_create' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false, 'can_reveal' => false]
        );

        // User role: can_reveal=false + can_read on all
        foreach ([$this->hostingsModule, $this->vpsModule, $this->voipModule, $this->serviceProvidersModule, $this->domainEmailsModule, $this->otherServicesModule, $this->vaultModule, $this->deniedModule] as $m) {
            ModuleRolePermission::updateOrCreate(
                ['module_id' => $m->id, 'role_id' => $this->userRole->id],
                ['can_read' => true, 'can_create' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false, 'can_reveal' => false]
            );
        }

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superRole);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($this->adminRole);

        $this->normalUser = User::factory()->create();
        $this->normalUser->assignRole($this->userRole);
    }

    // ─── SUPER-ADMIN BYPASS ──────────────────────────────────────────

    public function test_super_admin_can_reveal_hosting_password(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->superAdmin->id]);

        $response = $this->actingAs($this->superAdmin)->get(route('hostings.password', $hosting->id));
        $response->assertOk();
        $response->assertJson(['password' => $hosting->password]);
    }

    // ─── ADMIN WITH CAN_REVEAL ───────────────────────────────────────

    public function test_admin_with_can_reveal_can_reveal_hosting_password(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('hostings.password', $hosting->id));
        $response->assertOk();
        $response->assertJson(['password' => $hosting->password]);
    }

    public function test_admin_with_can_reveal_can_reveal_vps_password(): void
    {
        $vps = Vps::factory()->create(['module_id' => $this->vpsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('vps.password', $vps->id));
        $response->assertOk();
        $response->assertJson(['password' => $vps->password]);
    }

    public function test_admin_with_can_reveal_can_reveal_voip_password(): void
    {
        $voip = Voip::factory()->create(['module_id' => $this->voipModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('voip.password', $voip->id));
        $response->assertOk();
        $response->assertJson(['password' => $voip->password]);
    }

    public function test_admin_with_can_reveal_can_reveal_voip_extension_password(): void
    {
        $voip = Voip::factory()->create(['module_id' => $this->voipModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('voip.extension-password', $voip->id));
        $response->assertOk();
        $response->assertJson(['extension_password' => $voip->extension_password]);
    }

    public function test_admin_with_can_reveal_can_reveal_service_provider_password(): void
    {
        $provider = ServiceProvider::factory()->create(['module_id' => $this->serviceProvidersModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('service-providers.password', $provider->id));
        $response->assertOk();
        $response->assertJson(['password' => $provider->password]);
    }

    public function test_admin_with_can_reveal_can_reveal_domain_email_password(): void
    {
        $email = DomainEmail::factory()->create(['module_id' => $this->domainEmailsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('domain-emails.password', $email->id));
        $response->assertOk();
        $response->assertJson(['password' => $email->password]);
    }

    public function test_admin_with_can_reveal_can_reveal_other_service_password(): void
    {
        $service = OtherService::factory()->create(['module_id' => $this->otherServicesModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('other-services.password', $service->id));
        $response->assertOk();
        $response->assertJson(['password' => $service->password]);
    }

    public function test_admin_with_can_reveal_can_reveal_vault(): void
    {
        $entry = VaultEntry::factory()->create(['module_id' => $this->vaultModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->post(route('vault.reveal', $entry->id));
        $response->assertRedirect();
        $response->assertSessionHas('revealed_password');
    }

    // ─── WITHOUT CAN_REVEAL ──────────────────────────────────────────

    public function test_admin_without_can_reveal_denied_hosting_password(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->vaultModule->id,
            'can_reveal' => false,
        ]);
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('hostings.password', $hosting->id));
        $response->assertForbidden();
    }

    public function test_user_without_can_reveal_denied(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->normalUser->id]);

        $response = $this->actingAs($this->normalUser)->get(route('hostings.password', $hosting->id));
        $response->assertForbidden();
    }

    // ─── USER OVERRIDE ───────────────────────────────────────────────

    public function test_override_true_grants_reveal_when_role_denies(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->deniedModule->id,
            'can_reveal' => true,
        ]);
        $hosting = Hosting::factory()->create(['module_id' => $this->deniedModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('hostings.password', $hosting->id));
        $response->assertOk();
    }

    public function test_override_false_denies_reveal_when_role_allows(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->vaultModule->id,
            'can_reveal' => false,
        ]);
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('hostings.password', $hosting->id));
        $response->assertForbidden();
    }

    // ─── AUDIT LOGGING ───────────────────────────────────────────────

    public function test_successful_reveal_logs_activity(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->admin->id]);

        $this->actingAs($this->admin)->get(route('hostings.password', $hosting->id));

        $this->assertDatabaseHas('activity_log', [
            'event' => 'revealed',
            'subject_id' => $hosting->id,
            'subject_type' => $hosting->getMorphClass(),
            'causer_id' => $this->admin->id,
        ]);
    }

    public function test_denied_reveal_does_not_log_activity(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->vaultModule->id,
            'can_reveal' => false,
        ]);
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('hostings.password', $hosting->id));
        $response->assertForbidden();

        $this->assertDatabaseMissing('activity_log', [
            'event' => 'revealed',
            'subject_id' => $hosting->id,
            'subject_type' => $hosting->getMorphClass(),
        ]);
    }

    // ─── NO MODULE (deny unless super-admin) ─────────────────────────

    public function test_reveal_without_module_denied_for_non_super_admin(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => null, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('hostings.password', $hosting->id));
        $response->assertNotFound();
    }

    public function test_reveal_without_module_allowed_for_super_admin(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => null, 'user_id' => $this->superAdmin->id]);

        $response = $this->actingAs($this->superAdmin)->get(route('hostings.password', $hosting->id));
        $response->assertOk();
    }
}
