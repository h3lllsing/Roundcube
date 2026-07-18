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

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $viewer;
    private User $regular;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();

        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $viewerRole = Role::where('slug', 'user')->firstOrFail();
        $this->viewer = User::factory()->create();
        $this->viewer->assignRole($viewerRole);

        $this->regular = User::factory()->create();
        $this->regular->assignRole($viewerRole);

        $auditModule = Module::where('slug', 'audit')->firstOrFail();

        UserModulePermission::create([
            'user_id' => $this->viewer->id,
            'module_id' => $auditModule->id,
            'can_read' => true,
        ]);

        // Seed a few activity log entries
        Activity::create([
            'description' => 'test entry 1',
            'event' => 'created',
            'subject_type' => User::class,
            'subject_id' => $this->admin->id,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id,
        ]);

        Activity::create([
            'description' => 'test entry 2',
            'event' => 'updated',
            'subject_type' => User::class,
            'subject_id' => $this->admin->id,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id,
        ]);
    }

    public function test_super_admin_can_view_audit_trail()
    {
        $this->actingAs($this->admin)
            ->get(route('audit.index'))
            ->assertOk()
            ->assertSee('test entry 1')
            ->assertSee('test entry 2');
    }

    public function test_user_with_audit_view_permission_can_view()
    {
        $this->actingAs($this->viewer)
            ->get(route('audit.index'))
            ->assertOk()
            ->assertSee('test entry 1');
    }

    public function test_user_without_permission_cannot_view()
    {
        $this->actingAs($this->regular)
            ->get(route('audit.index'))
            ->assertForbidden();
    }

    public function test_audit_trail_paginates()
    {
        Activity::truncate();
        $now = now();
        foreach (range(1, 55) as $i) {
            Activity::create([
                'description' => sprintf('entry %03d', $i),
                'event' => 'created',
                'subject_type' => User::class,
                'subject_id' => $this->admin->id,
                'causer_type' => User::class,
                'causer_id' => $this->admin->id,
                'created_at' => $now->copy()->addSeconds($i),
                'updated_at' => $now->copy()->addSeconds($i),
            ]);
        }

        $this->actingAs($this->admin)
            ->get(route('audit.index'))
            ->assertOk()
            ->assertSee('entry 055')
            ->assertDontSee('entry 001');
    }

    public function test_audit_trail_filters_by_event()
    {
        $this->actingAs($this->admin)
            ->get(route('audit.index', ['event' => 'updated']))
            ->assertOk()
            ->assertSee('test entry 2')
            ->assertDontSee('test entry 1');
    }
}
