<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\Note;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use App\Models\Webhook;
use Database\Seeders\FeatureModuleSeeder;
use Database\Seeders\RolePermissionSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SecurityFixesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $customerRole = Role::where('slug', 'customer')->firstOrFail();
        $this->customer = User::factory()->create();
        $this->customer->assignRole($customerRole);
    }

    // ─── P5a: Import authorization ─────────────────────────────────

    public function test_api_import_requires_super_admin(): void
    {
        $csv = "name\nTestDomain";
        $file = UploadedFile::fake()->createWithContent('domains.csv', $csv);

        $this->actingAs($this->customer)
            ->postJson('/api/import/domains', ['file' => $file])
            ->assertForbidden();
    }

    public function test_api_import_allows_super_admin(): void
    {
        $csv = "name\nAdminDomain";
        $file = UploadedFile::fake()->createWithContent('domains.csv', $csv);

        $this->actingAs($this->admin)
            ->postJson('/api/import/domains', ['file' => $file])
            ->assertCreated()
            ->assertJsonPath('data.count', 1);

        $this->assertDatabaseHas('domains', ['name' => 'AdminDomain']);
    }

    // ─── P5b: Export permission check ──────────────────────────────

    public function test_api_export_requires_export_permission(): void
    {
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $noExportUser = User::factory()->create();
        $noExportUser->assignRole($userRole);

        $this->actingAs($noExportUser)
            ->getJson('/api/export/domains')
            ->assertForbidden();
    }

    public function test_api_export_allows_with_export_permission(): void
    {
        $this->actingAs($this->customer)
            ->getJson('/api/export/domains')
            ->assertOk();
    }

    // ─── P5c: Dashboard notes don't leak ──────────────────────────

    public function test_web_dashboard_total_notes_scoped_for_non_admin(): void
    {
        Note::factory()->count(3)->create(['user_id' => $this->admin->id]);
        Note::factory()->count(2)->create(['user_id' => $this->customer->id]);

        $response = $this->actingAs($this->customer)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('2'); // myNotes = 2
        $response->assertSee('2'); // totalNotes should also be 2 for non-admin
    }

    public function test_api_dashboard_total_notes_scoped_for_non_admin(): void
    {
        Note::factory()->count(3)->create(['user_id' => $this->admin->id]);
        Note::factory()->count(2)->create(['user_id' => $this->customer->id]);

        $response = $this->actingAs($this->customer)->getJson('/api/dashboard');

        $response->assertOk();
        $this->assertEquals(2, $response->json('data.total_notes'));
        $this->assertEquals(2, $response->json('data.my_notes'));
    }

    public function test_web_dashboard_total_notes_unscoped_for_super_admin(): void
    {
        Note::factory()->count(3)->create(['user_id' => $this->admin->id]);
        Note::factory()->count(2)->create(['user_id' => $this->customer->id]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('5'); // totalNotes = 5 for admin
    }

    public function test_api_dashboard_total_notes_unscoped_for_super_admin(): void
    {
        Note::factory()->count(3)->create(['user_id' => $this->admin->id]);
        Note::factory()->count(2)->create(['user_id' => $this->customer->id]);

        $response = $this->actingAs($this->admin)->getJson('/api/dashboard');

        $response->assertOk();
        $this->assertEquals(5, $response->json('data.total_notes'));
    }

    // ─── P5d: Password reveal activity logging ─────────────────────

    public function test_vps_password_reveal_logs_activity(): void
    {
        $vps = Vps::factory()->create(['user_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get(route('vps.password', $vps->id));

        $this->assertDatabaseHas('activity_log', [
            'event' => 'revealed',
            'subject_id' => $vps->id,
            'subject_type' => $vps->getMorphClass(),
            'causer_id' => $this->admin->id,
        ]);
    }

    public function test_hosting_password_reveal_logs_activity(): void
    {
        $hosting = Hosting::factory()->create(['user_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get(route('hostings.password', $hosting->id));

        $this->assertDatabaseHas('activity_log', [
            'event' => 'revealed',
            'subject_id' => $hosting->id,
            'subject_type' => $hosting->getMorphClass(),
            'causer_id' => $this->admin->id,
        ]);
    }

    public function test_service_provider_password_reveal_logs_activity(): void
    {
        $provider = ServiceProvider::factory()->create(['user_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get(route('service-providers.password', $provider->id));

        $this->assertDatabaseHas('activity_log', [
            'event' => 'revealed',
            'subject_id' => $provider->id,
            'subject_type' => $provider->getMorphClass(),
            'causer_id' => $this->admin->id,
        ]);
    }

    public function test_domain_email_password_reveal_logs_activity(): void
    {
        $email = DomainEmail::factory()->create(['user_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get(route('domain-emails.password', $email->id));

        $this->assertDatabaseHas('activity_log', [
            'event' => 'revealed',
            'subject_id' => $email->id,
            'subject_type' => $email->getMorphClass(),
            'causer_id' => $this->admin->id,
        ]);
    }

    public function test_other_service_password_reveal_logs_activity(): void
    {
        $service = OtherService::factory()->create(['user_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get(route('other-services.password', $service->id));

        $this->assertDatabaseHas('activity_log', [
            'event' => 'revealed',
            'subject_id' => $service->id,
            'subject_type' => $service->getMorphClass(),
            'causer_id' => $this->admin->id,
        ]);
    }

    public function test_voip_password_reveal_logs_activity(): void
    {
        $voip = Voip::factory()->create(['user_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get(route('voip.password', $voip->id));

        $this->assertDatabaseHas('activity_log', [
            'event' => 'revealed',
            'subject_id' => $voip->id,
            'subject_type' => $voip->getMorphClass(),
            'causer_id' => $this->admin->id,
        ]);
    }

    public function test_voip_extension_password_reveal_logs_activity(): void
    {
        $voip = Voip::factory()->create(['user_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get(route('voip.extension-password', $voip->id));

        $this->assertDatabaseHas('activity_log', [
            'event' => 'revealed',
            'subject_id' => $voip->id,
            'subject_type' => $voip->getMorphClass(),
            'causer_id' => $this->admin->id,
        ]);
    }

    // ─── P5e: Suspend/unsuspend activity logging ───────────────────

    public function test_web_user_suspend_logs_activity(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->admin)
            ->patch(route('users.suspend', $target->id));

        $this->assertDatabaseHas('activity_log', [
            'event' => 'suspended',
            'subject_id' => $target->id,
            'subject_type' => User::class,
            'causer_id' => $this->admin->id,
        ]);
    }

    public function test_web_user_unsuspend_logs_activity(): void
    {
        $target = User::factory()->create(['suspended_at' => now()]);

        $this->actingAs($this->admin)
            ->patch(route('users.unsuspend', $target->id));

        $this->assertDatabaseHas('activity_log', [
            'event' => 'unsuspended',
            'subject_id' => $target->id,
            'subject_type' => User::class,
            'causer_id' => $this->admin->id,
        ]);
    }

    public function test_api_user_suspend_logs_activity(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->admin)
            ->patchJson('/api/users/'.$target->id.'/suspend');

        $this->assertDatabaseHas('activity_log', [
            'event' => 'suspended',
            'subject_id' => $target->id,
            'subject_type' => User::class,
            'causer_id' => $this->admin->id,
        ]);
    }

    public function test_api_user_unsuspend_logs_activity(): void
    {
        $target = User::factory()->create(['suspended_at' => now()]);

        $this->actingAs($this->admin)
            ->patchJson('/api/users/'.$target->id.'/unsuspend');

        $this->assertDatabaseHas('activity_log', [
            'event' => 'unsuspended',
            'subject_id' => $target->id,
            'subject_type' => User::class,
            'causer_id' => $this->admin->id,
        ]);
    }

    // ─── Web import also super-admin only ─────────────────────────

    public function test_web_import_requires_super_admin(): void
    {
        $csv = "name\nTestDomain";
        $file = UploadedFile::fake()->createWithContent('domains.csv', $csv);

        $this->actingAs($this->customer)
            ->post(route('import.store'), ['type' => 'domains', 'file' => $file])
            ->assertForbidden();
    }
}
