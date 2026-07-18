<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\User;
use App\Models\UserModulePermission;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AuditTrailFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $viewerRole = Role::where('slug', 'user')->firstOrFail();

        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->viewer = User::factory()->create();
        $this->viewer->assignRole($viewerRole);

        $auditModule = Module::where('slug', 'audit')->firstOrFail();

        UserModulePermission::create([
            'user_id' => $this->viewer->id,
            'module_id' => $auditModule->id,
            'can_read' => true,
        ]);

        Activity::create(['description' => 'Domain deleted', 'event' => 'soft_delete', 'subject_type' => User::class, 'subject_id' => $this->admin->id, 'causer_type' => User::class, 'causer_id' => $this->admin->id, 'created_at' => now()->subDay()]);
        Activity::create(['description' => 'Domain restored', 'event' => 'restored', 'subject_type' => User::class, 'subject_id' => $this->admin->id, 'causer_type' => User::class, 'causer_id' => $this->admin->id, 'created_at' => now()->subDay()]);
        Activity::create(['description' => 'Account force deleted', 'event' => 'force_delete', 'subject_type' => User::class, 'subject_id' => $this->admin->id, 'causer_type' => User::class, 'causer_id' => $this->admin->id, 'created_at' => now()->subDays(10)]);
    }

    public function test_can_filter_by_action()
    {
        $this->actingAs($this->admin)
            ->get(route('audit.index', ['action' => 'soft_delete']))
            ->assertOk()
            ->assertSee('Domain deleted')
            ->assertDontSee('Domain restored');
    }

    public function test_can_filter_by_resource_type()
    {
        $this->actingAs($this->admin)
            ->get(route('audit.index', ['subject_type' => User::class]))
            ->assertOk()
            ->assertSee('Domain deleted')
            ->assertSee('Domain restored');
    }

    public function test_can_filter_by_causer()
    {
        $this->actingAs($this->admin)
            ->get(route('audit.index', ['causer_id' => $this->admin->id]))
            ->assertOk()
            ->assertSee('Domain deleted');
    }

    public function test_dashboard_shows_audit_stats_for_super_admin()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk();

        $response->assertSee('Soft Deletes');
        $response->assertSee('Restores');
        $response->assertSee('Force Deletes');
    }

    public function test_dashboard_shows_audit_stats_for_audit_viewer()
    {
        $response = $this->actingAs($this->viewer)
            ->get(route('dashboard'))
            ->assertOk();

        $response->assertSee('Soft Deletes');
        $response->assertSee('Restores');
        $response->assertSee('Force Deletes');
    }

    public function test_dashboard_hides_audit_stats_for_regular_user()
    {
        $regular = User::factory()->create();
        $regular->assignRole(Role::where('slug', 'user')->firstOrFail());

        $response = $this->actingAs($regular)
            ->get(route('dashboard'))
            ->assertOk();

        $response->assertDontSee('Soft Deletes');
        $response->assertDontSee('Restores');
        $response->assertDontSee('Force Deletes');
    }

    public function test_audit_stats_only_counts_last_7_days()
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Soft Deletes')
            ->assertSee('Restores')
            ->assertSee('Force Deletes');
    }
}
