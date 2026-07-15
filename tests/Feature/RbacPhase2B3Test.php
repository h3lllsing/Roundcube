<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\DomainEmail;
use App\Models\GMail;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\OtherService;
use App\Models\UserModulePermission;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use Database\Seeders\FeatureModuleSeeder;
use Illuminate\Support\Facades\DB;
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
            'user_id' => $this->admin->id, 'module_id' => $this->hostingsModule->id,
            'can_reveal' => false,
        ]);
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('hostings.password', $hosting->id));
        $response->assertForbidden();
    }

    public function test_user_without_can_reveal_denied(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id, 'module_id' => $this->hostingsModule->id,
            'can_reveal' => false,
        ]);
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->normalUser->id]);

        $response = $this->actingAs($this->normalUser)->get(route('hostings.password', $hosting->id));
        $response->assertForbidden();
    }

    // ─── USER OVERRIDE ───────────────────────────────────────────────

    public function test_override_true_grants_reveal_when_role_denies(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->hostingsModule->id,
            'can_reveal' => true, 'can_read' => true,
        ]);
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('hostings.password', $hosting->id));
        $response->assertOk();
    }

    public function test_override_false_denies_reveal_when_role_allows(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->hostingsModule->id,
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
            'user_id' => $this->admin->id, 'module_id' => $this->hostingsModule->id,
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

    // ─── RESOURCE READ REQUIRED ───────────────────────────────────

    private function makeUserWithVaultRevealButNoHostingsRead(): User
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->vaultModule->id,
            'can_reveal' => true,
        ]);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->hostingsModule->id,
            'can_read' => false,
        ]);

        return $user;
    }

    public function test_reveal_denied_without_resource_read_even_with_reveal(): void
    {
        $user = $this->makeUserWithVaultRevealButNoHostingsRead();
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('hostings.password', $hosting->id));
        $response->assertForbidden();
    }

    public function test_reveal_denied_without_resource_read_on_vps(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->vaultModule->id,
            'can_reveal' => true,
        ]);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->vpsModule->id,
            'can_read' => false,
        ]);
        $vps = Vps::factory()->create(['module_id' => $this->vpsModule->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('vps.password', $vps->id));
        $response->assertForbidden();
    }

    public function test_reveal_denied_without_resource_read_on_voip(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->vaultModule->id,
            'can_reveal' => true,
        ]);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->voipModule->id,
            'can_read' => false,
        ]);
        $voip = Voip::factory()->create(['module_id' => $this->voipModule->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('voip.password', $voip->id));
        $response->assertForbidden();
    }

    public function test_reveal_denied_without_resource_read_on_service_provider(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->vaultModule->id,
            'can_reveal' => true,
        ]);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->serviceProvidersModule->id,
            'can_read' => false,
        ]);
        $provider = ServiceProvider::factory()->create(['module_id' => $this->serviceProvidersModule->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('service-providers.password', $provider->id));
        $response->assertForbidden();
    }

    public function test_reveal_denied_without_resource_read_on_domain_email(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->vaultModule->id,
            'can_reveal' => true,
        ]);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->domainEmailsModule->id,
            'can_read' => false,
        ]);
        $email = DomainEmail::factory()->create(['module_id' => $this->domainEmailsModule->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('domain-emails.password', $email->id));
        $response->assertForbidden();
    }

    public function test_reveal_denied_without_resource_read_on_other_service(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->vaultModule->id,
            'can_reveal' => true,
        ]);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->otherServicesModule->id,
            'can_read' => false,
        ]);
        $service = OtherService::factory()->create(['module_id' => $this->otherServicesModule->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('other-services.password', $service->id));
        $response->assertForbidden();
    }

    public function test_copy_denied_without_resource_read(): void
    {
        $user = $this->makeUserWithVaultRevealButNoHostingsRead();
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('hostings.password.copy', $hosting->id));
        $response->assertForbidden();
    }

    public function test_copy_denied_without_resource_read_on_voip(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->vaultModule->id,
            'can_reveal' => true,
        ]);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->voipModule->id,
            'can_read' => false,
        ]);
        $voip = Voip::factory()->create(['module_id' => $this->voipModule->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('voip.password.copy', $voip->id));
        $response->assertForbidden();
    }

    // ─── RESOURCE READ + REVEAL ALLOWED ───────────────────────────

    public function test_reveal_allowed_with_resource_read_and_reveal(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('hostings.password', $hosting->id));
        $response->assertOk();
        $response->assertJson(['password' => $hosting->password]);
    }

    public function test_copy_allowed_with_resource_read_and_reveal(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->post(route('hostings.password.copy', $hosting->id));
        $response->assertOk();
        $response->assertJson(['status' => 'logged']);
    }

    // ─── NO MODULE (deny unless super-admin) ─────────────────────────

    public function test_reveal_without_module_allowed_for_super_admin(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => null, 'user_id' => $this->superAdmin->id]);

        $response = $this->actingAs($this->superAdmin)->get(route('hostings.password', $hosting->id));
        $response->assertOk();
    }

    // ─── UI VISIBILITY (index action menu) ─────────────────────────

    public function test_service_provider_access_shows_password_action_in_index(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->serviceProvidersModule->id,
            'can_read' => true,
        ]);
        $provider = ServiceProvider::factory()->create([
            'module_id' => $this->serviceProvidersModule->id, 'user_id' => $user->id,
            'password' => 'secret',
        ]);

        $response = $this->actingAs($user)->get(route('service-providers.index'));
        $response->assertOk();
        $response->assertSee('Copy Password');
    }

    public function test_hosting_access_shows_password_action_in_index(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->hostingsModule->id,
            'can_read' => true,
        ]);
        $hosting = Hosting::factory()->create([
            'module_id' => $this->hostingsModule->id, 'user_id' => $user->id,
            'password' => 'secret',
        ]);

        $response = $this->actingAs($user)->get(route('hostings.index'));
        $response->assertOk();
        $response->assertSee('Copy Password');
    }

    public function test_explicit_can_reveal_false_hides_password_action(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->serviceProvidersModule->id,
            'can_read' => true, 'can_reveal' => false,
        ]);
        $provider = ServiceProvider::factory()->create([
            'module_id' => $this->serviceProvidersModule->id, 'user_id' => $user->id,
            'password' => 'secret',
        ]);

        $response = $this->actingAs($user)->get(route('service-providers.index'));
        $response->assertOk();
        $response->assertDontSee('Copy Password');
    }

    public function test_no_access_hides_password_action(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->serviceProvidersModule->id,
            'can_read' => false,
        ]);

        $response = $this->actingAs($user)->get(route('service-providers.index'));
        $response->assertForbidden();
    }

    public function test_super_admin_sees_password_action_in_index(): void
    {
        $provider = ServiceProvider::factory()->create([
            'module_id' => $this->serviceProvidersModule->id, 'user_id' => $this->superAdmin->id,
            'password' => 'secret',
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('service-providers.index'));
        $response->assertOk();
        $response->assertSee('Copy Password');
    }

    // ─── canRevealCredentialsFor() DIRECT UNIT TESTS ─────────────────

    public function test_can_reveal_credentials_super_admin_with_module(): void
    {
        $this->assertTrue($this->superAdmin->canRevealCredentialsFor($this->hostingsModule));
    }

    public function test_can_reveal_credentials_super_admin_without_module(): void
    {
        $this->assertTrue($this->superAdmin->canRevealCredentialsFor(null));
    }

    public function test_can_reveal_credentials_null_module_denies_non_super_admin(): void
    {
        $this->assertFalse($this->admin->canRevealCredentialsFor(null));
        $this->assertFalse($this->normalUser->canRevealCredentialsFor(null));
    }

    public function test_can_reveal_credentials_user_with_role_reveal(): void
    {
        $this->assertTrue($this->admin->canRevealCredentialsFor($this->hostingsModule));
    }

    public function test_can_reveal_credentials_auto_grant_from_read_activates_even_with_role_reveal_false(): void
    {
        $this->assertTrue($this->normalUser->canRevealCredentialsFor($this->hostingsModule));
    }

    public function test_can_reveal_credentials_denied_without_read_and_without_reveal(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->hostingsModule->id, 'role_id' => $this->userRole->id],
            ['can_read' => false, 'can_reveal' => false]
        );

        $this->assertFalse($user->canRevealCredentialsFor($this->hostingsModule));
    }

    public function test_can_reveal_credentials_explicit_override_false_denies(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->hostingsModule->id,
            'can_reveal' => false,
        ]);

        $this->assertFalse($this->admin->canRevealCredentialsFor($this->hostingsModule));
    }

    public function test_can_reveal_credentials_explicit_override_true_grants_even_when_role_denies(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        UserModulePermission::create([
            'user_id' => $user->id, 'module_id' => $this->hostingsModule->id,
            'can_reveal' => true, 'can_read' => true,
        ]);

        $this->assertTrue($user->canRevealCredentialsFor($this->hostingsModule));
    }

    public function test_can_reveal_credentials_user_with_no_roles(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->canRevealCredentialsFor($this->hostingsModule));
    }

    public function test_can_reveal_credentials_multiple_roles_or_merge(): void
    {
        $allowRole = \HasinHayder\Tyro\Models\Role::create(['slug' => 'allow-reveal', 'name' => 'Allow Reveal']);
        ModuleRolePermission::create([
            'module_id' => $this->hostingsModule->id, 'role_id' => $allowRole->id,
            'can_reveal' => true, 'can_read' => true,
        ]);
        $denyRole = \HasinHayder\Tyro\Models\Role::create(['slug' => 'deny-reveal', 'name' => 'Deny Reveal']);
        ModuleRolePermission::create([
            'module_id' => $this->hostingsModule->id, 'role_id' => $denyRole->id,
            'can_reveal' => false,
        ]);

        $user = User::factory()->create();
        $user->assignRole($allowRole);
        $user->assignRole($denyRole);

        $this->assertTrue($user->canRevealCredentialsFor($this->hostingsModule));
    }

    public function test_can_reveal_credentials_no_read_no_reveal_denies(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        $noAccessModule = Module::factory()->create(['slug' => 'no-access', 'name' => 'No Access']);

        $this->assertFalse($user->canRevealCredentialsFor($noAccessModule));
    }

    public function test_vault_entry_without_module_reveal_denies_non_super_admin(): void
    {
        $entry = VaultEntry::factory()->create([
            'module_id' => null,
            'user_id' => $this->normalUser->id,
        ]);

        $this->assertFalse($this->normalUser->canRevealCredentialsFor($entry->module));
        $this->assertTrue($this->superAdmin->canRevealCredentialsFor($entry->module));
    }

    public function test_vault_entry_with_module_reveal_grants_with_permission(): void
    {
        $entry = VaultEntry::factory()->create([
            'module_id' => $this->vaultModule->id,
            'user_id' => $this->admin->id,
        ]);

        $this->assertTrue($this->admin->canRevealCredentialsFor($entry->module));
    }

    public function test_vault_entry_owner_cannot_reveal_without_read_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->hostingsModule->id, 'role_id' => $this->userRole->id],
            ['can_read' => false, 'can_reveal' => false]
        );
        $entry = VaultEntry::factory()->create([
            'module_id' => $this->hostingsModule->id,
            'user_id' => $user->id,
        ]);

        $this->assertFalse($user->canRevealCredentialsFor($entry->module));
    }

    // ─── 6 SCENARIO VERIFICATION (precedence rules) ────────────────

    private function makeScenarioBase(): User
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);
        return $user;
    }

    public function test_scenario_A_role_read_true_no_user_override(): void
    {
        $user = $this->makeScenarioBase();
        $this->assertTrue($user->canRevealCredentialsFor($this->hostingsModule));
    }

    public function test_scenario_B_role_read_true_user_reveal_false(): void
    {
        $user = $this->makeScenarioBase();
        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->hostingsModule->id,
            'can_read' => null,
            'can_reveal' => false,
        ]);
        $this->assertFalse($user->canRevealCredentialsFor($this->hostingsModule));
    }

    public function test_scenario_C_role_read_true_user_read_false_reveal_null(): void
    {
        $user = $this->makeScenarioBase();
        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->hostingsModule->id,
            'can_read' => false,
            'can_reveal' => null,
        ]);
        $this->assertFalse($user->canRevealCredentialsFor($this->hostingsModule));
    }

    public function test_scenario_D_direct_user_read_true_no_role(): void
    {
        $user = User::factory()->create();
        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->hostingsModule->id,
            'can_read' => true,
            'can_reveal' => null,
        ]);
        $this->assertTrue($user->canRevealCredentialsFor($this->hostingsModule));
    }

    public function test_scenario_E_multi_role_or_read_no_user_override(): void
    {
        $allowRole = \HasinHayder\Tyro\Models\Role::create(['slug' => 'read-only', 'name' => 'Read Only']);
        ModuleRolePermission::create([
            'module_id' => $this->hostingsModule->id,
            'role_id' => $allowRole->id,
            'can_read' => true,
            'can_reveal' => false,
        ]);
        $denyRole = \HasinHayder\Tyro\Models\Role::create(['slug' => 'no-access', 'name' => 'No Access']);
        ModuleRolePermission::create([
            'module_id' => $this->hostingsModule->id,
            'role_id' => $denyRole->id,
            'can_read' => false,
        ]);
        $user = User::factory()->create();
        $user->assignRole($allowRole);
        $user->assignRole($denyRole);

        $this->assertTrue($user->canRevealCredentialsFor($this->hostingsModule));
    }

    public function test_scenario_F_super_admin_always_allowed(): void
    {
        $this->assertTrue($this->superAdmin->canRevealCredentialsFor($this->deniedModule));
        $this->assertTrue($this->superAdmin->canRevealCredentialsFor(null));
    }

    // ─── READ OVERRIDE PRECEDENCE ───────────────────────────────────

    public function test_user_read_false_override_beats_role_read_true(): void
    {
        $user = $this->makeScenarioBase();
        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->hostingsModule->id,
            'can_read' => false,
            'can_reveal' => null,
        ]);

        $readResult = $user->canOnModule($this->hostingsModule, 'read');
        $revealResult = $user->canRevealCredentialsFor($this->hostingsModule);

        $this->assertFalse($readResult, 'User override can_read=false must deny read');
        $this->assertFalse($revealResult, 'Without effective read, reveal must be denied');
    }

    public function test_user_read_true_override_beats_role_read_false(): void
    {
        $user = $this->makeScenarioBase();
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->hostingsModule->id, 'role_id' => $this->userRole->id],
            ['can_read' => false, 'can_reveal' => false]
        );
        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->hostingsModule->id,
            'can_read' => true,
            'can_reveal' => null,
        ]);

        $this->assertTrue($user->canRevealCredentialsFor($this->hostingsModule));
    }

    // ─── DATA MIGRATION TESTS ──────────────────────────────────────────

    public function test_migration_backfills_null_gmail_module_id(): void
    {
        $gmailModule = Module::where('slug', 'g-mails')->firstOrFail();
        // Create a G-Mail record with null module_id
        $gmail = \App\Models\GMail::factory()->create(['module_id' => null, 'user_id' => $this->superAdmin->id]);
        $this->assertNull($gmail->fresh()->module_id);

        $migration = require __DIR__ . '/../../database/migrations/2026_07_16_000001_backfill_null_module_ids.php';
        $migration->up();

        $this->assertEquals($gmailModule->id, $gmail->fresh()->module_id);
    }

    public function test_migration_is_idempotent(): void
    {
        $gmailModule = Module::where('slug', 'g-mails')->firstOrFail();
        $gmail = \App\Models\GMail::factory()->create(['module_id' => null, 'user_id' => $this->superAdmin->id]);
        $hosting = Hosting::factory()->create(['module_id' => null, 'user_id' => $this->superAdmin->id]);

        $migration = require __DIR__ . '/../../database/migrations/2026_07_16_000001_backfill_null_module_ids.php';
        $migration->up();

        $firstModuleId = $gmail->fresh()->module_id;
        $firstHostingModuleId = $hosting->fresh()->module_id;

        // Run a second time
        $migration->up();

        $this->assertEquals($gmailModule->id, $gmail->fresh()->module_id);
        $this->assertEquals($firstModuleId, $gmail->fresh()->module_id);
        $this->assertEquals($firstHostingModuleId, $hosting->fresh()->module_id);
    }

    public function test_migration_repairs_wrong_module_id(): void
    {
        $gmailModule = Module::where('slug', 'g-mails')->firstOrFail();
        $otherModule = Module::where('slug', 'tasks')->firstOrFail();
        $gmail = GMail::factory()->create([
            'module_id' => $otherModule->id,
            'user_id' => $this->superAdmin->id,
        ]);
        $this->assertEquals($otherModule->id, $gmail->fresh()->module_id);

        $migration = require __DIR__ . '/../../database/migrations/2026_07_16_000001_backfill_null_module_ids.php';
        $migration->up();

        $this->assertEquals($gmailModule->id, $gmail->fresh()->module_id);
    }

    public function test_migration_correct_module_id_unchanged(): void
    {
        $gmailModule = Module::where('slug', 'g-mails')->firstOrFail();
        $hostingsModule = Module::where('slug', 'hostings')->firstOrFail();
        $gmail = GMail::factory()->create([
            'module_id' => $gmailModule->id,
            'user_id' => $this->superAdmin->id,
        ]);
        $hosting = Hosting::factory()->create([
            'module_id' => $hostingsModule->id,
            'user_id' => $this->superAdmin->id,
        ]);

        $migration = require __DIR__ . '/../../database/migrations/2026_07_16_000001_backfill_null_module_ids.php';
        $migration->up();

        $this->assertEquals($gmailModule->id, $gmail->fresh()->module_id);
        $this->assertEquals($hostingsModule->id, $hosting->fresh()->module_id);
    }

    public function test_migration_unrelated_fields_unchanged(): void
    {
        $gmailModule = Module::where('slug', 'g-mails')->firstOrFail();
        $otherModule = Module::where('slug', 'tasks')->firstOrFail();
        $gmail = GMail::factory()->create([
            'module_id' => $otherModule->id,
            'user_id' => $this->superAdmin->id,
            'status' => 'active',
            'user_name' => 'testuser',
            'password' => 'secret123',
        ]);
        $original = $gmail->fresh();
        $originalStatus = $original->status;
        $originalUserName = $original->user_name;
        $originalUserId = $original->user_id;

        $migration = require __DIR__ . '/../../database/migrations/2026_07_16_000001_backfill_null_module_ids.php';
        $migration->up();

        $updated = $gmail->fresh();
        $this->assertEquals($gmailModule->id, $updated->module_id);
        $this->assertEquals($originalStatus, $updated->status);
        $this->assertEquals($originalUserName, $updated->user_name);
        $this->assertEquals($originalUserId, $updated->user_id);
    }

    public function test_migration_corrects_wrong_service_provider_module_id(): void
    {
        $hostingsModule = Module::where('slug', 'hostings')->firstOrFail();
        $spModule = Module::where('slug', 'service-providers')->firstOrFail();
        $sp = ServiceProvider::factory()->create([
            'module_id' => $hostingsModule->id,
            'user_id' => $this->superAdmin->id,
        ]);
        $this->assertEquals($hostingsModule->id, $sp->fresh()->module_id);

        $migration = require __DIR__ . '/../../database/migrations/2026_07_16_000001_backfill_null_module_ids.php';
        $migration->up();

        $this->assertEquals($spModule->id, $sp->fresh()->module_id);
    }

    public function test_migration_backfills_all_resource_tables(): void
    {
        $entries = [
            GMail::class          => 'g_mails',
            Hosting::class        => 'hostings',
            Vps::class            => 'vps',
            Voip::class           => 'voip',
            DomainEmail::class    => 'domain_emails',
            ServiceProvider::class => 'service_providers',
            OtherService::class   => 'other_services',
            Asset::class          => 'assets',
        ];

        foreach ($entries as $model => $table) {
            $model::factory()->create(['module_id' => null, 'user_id' => $this->superAdmin->id]);
            $this->assertNotNull(DB::table($table)->whereNull('module_id')->first(), "Table {$table} should have a null module_id before migration");
        }

        $migration = require __DIR__ . '/../../database/migrations/2026_07_16_000001_backfill_null_module_ids.php';
        $migration->up();

        foreach ($entries as $model => $table) {
            $this->assertNull(DB::table($table)->whereNull('module_id')->first(), "Table {$table} still has null module_id after migration");
        }
    }
}
