<?php

namespace Tests\Feature\Dashboard;

use App\Dashboard\ActivityWidget;
use App\Dashboard\AssetsWidget;
use App\Dashboard\OperationsWidget;
use App\Dashboard\QuickActionsWidget;
use App\Dashboard\RenewalsWidget;
use App\Dashboard\ServerHealthWidget;
use App\Dashboard\SmtpWidget;
use App\Dashboard\TasksWidget;
use App\Dashboard\VaultWidget;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Domain;
use App\Models\ExpiryTracker;
use App\Models\ExpiryTrackerNotification;
use App\Models\Task;
use App\Models\User;
use App\Models\VaultEntry;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::where('slug', 'admin')->firstOrFail());

        $this->user = User::factory()->create();
        $this->user->assignRole(Role::where('slug', 'user')->firstOrFail());

        Cache::forget('dashboard:version');
    }

    /** @test */
    public function dashboard_loads_for_super_admin()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Operations Summary');
    }

    /** @test */
    public function dashboard_loads_for_admin()
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);
    }

    /** @test */
    public function dashboard_loads_for_regular_user()
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $response->assertStatus(200);
    }

    /** @test */
    public function guest_redirected_to_login()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function super_admin_sees_server_health()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $response->assertSee('Server Health');
        $response->assertSee(PHP_VERSION);
    }

    /** @test */
    public function admin_does_not_see_server_health()
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertDontSee('Server Health');
    }

    /** @test */
    public function super_admin_sees_smtp_section()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $response->assertSee('SMTP Profiles');
    }

    /** @test */
    public function admin_does_not_see_smtp_section()
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertDontSee('SMTP Profiles');
    }

    /** @test */
    public function super_admin_sees_quick_actions_with_system_links()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $response->assertSee('Quick Actions');
        $response->assertSee('+ Feature');
        $response->assertSee('+ Module');
        $response->assertSee('+ User');
    }

    /** @test */
    public function read_only_user_sees_no_quick_actions()
    {
        $ro = User::factory()->create();
        $ro->assignRole(Role::where('slug', 'user')->firstOrFail());
        $response = $this->actingAs($ro)->get(route('dashboard'));
        $response->assertSee('Quick Actions');
        $response->assertDontSee('+ Feature');
        $response->assertDontSee('+ Module');
        $response->assertDontSee('+ User');
    }

    /** @test */
    public function operations_widget_returns_correct_data()
    {
        Domain::factory()->create(['status' => 'active', 'cost' => 10.00, 'expiry_date' => now()->addDays(5), 'user_id' => $this->superAdmin->id]);

        $widget = new OperationsWidget();
        $data = $widget->data($this->superAdmin);

        $this->assertArrayHasKey('operations', $data);
        $this->assertGreaterThan(0, $data['operations']['total_active_services']);
        $this->assertArrayHasKey('total_monthly_cost', $data['operations']);
    }

    /** @test */
    public function renewals_widget_returns_daily_metrics()
    {
        $tracker = ExpiryTracker::factory()->create(['user_id' => $this->superAdmin->id]);

        ExpiryTrackerNotification::factory()->create([
            'expiry_tracker_id' => $tracker->id,
            'trigger_source' => 'manual',
            'status' => 'sent',
            'created_at' => now(),
        ]);
        ExpiryTrackerNotification::factory()->create([
            'expiry_tracker_id' => $tracker->id,
            'trigger_source' => 'cron',
            'status' => 'sent',
            'created_at' => now(),
        ]);
        ExpiryTrackerNotification::factory()->create([
            'expiry_tracker_id' => $tracker->id,
            'trigger_source' => 'cron',
            'status' => 'failed',
            'created_at' => now(),
        ]);

        $widget = new RenewalsWidget();
        $data = $widget->data($this->superAdmin);

        $this->assertEquals(1, $data['renewals']['manual_sends_today']);
        $this->assertEquals(2, $data['renewals']['automatic_sends_today']);
        $this->assertEquals(1, $data['renewals']['failed_today']);
    }

    /** @test */
    public function assets_widget_returns_daily_metrics()
    {
        $asset = Asset::factory()->create(['status' => 'assigned', 'user_id' => $this->superAdmin->id]);
        AssetAssignment::factory()->create([
            'asset_id' => $asset->id,
            'assigned_at' => now(),
            'returned_at' => null,
        ]);
        AssetAssignment::factory()->create([
            'asset_id' => $asset->id,
            'assigned_at' => now()->subDays(2),
            'returned_at' => now(),
        ]);

        $widget = new AssetsWidget();
        $data = $widget->data($this->superAdmin);

        $this->assertEquals(1, $data['assets']['assigned_today']);
        $this->assertEquals(1, $data['assets']['returned_today']);
        $this->assertArrayHasKey('assets_by_status', $data['assets']);
    }

    /** @test */
    public function tasks_widget_returns_correct_counts()
    {
        Task::factory()->count(3)->create(['status' => 'pending']);
        Task::factory()->count(2)->create(['status' => 'completed']);

        $widget = new TasksWidget();
        $data = $widget->data($this->superAdmin);

        $this->assertEquals(5, $data['tasks']['total_tasks']);
        $this->assertArrayHasKey('tasks_by_status', $data['tasks']);
    }

    /** @test */
    public function vault_widget_returns_revealed_today()
    {
        VaultEntry::factory()->create(['user_id' => $this->superAdmin->id]);

        $widget = new VaultWidget();
        $data = $widget->data($this->superAdmin);

        $this->assertGreaterThan(0, $data['vault']['total_entries']);
        $this->assertArrayHasKey('revealed_today', $data['vault']);
    }

    /** @test */
    public function smtp_widget_empty_for_non_super_admin()
    {
        $widget = new SmtpWidget();
        $data = $widget->data($this->admin);

        $this->assertEquals([], $data);
    }

    /** @test */
    public function server_health_widget_empty_for_non_super_admin()
    {
        $widget = new ServerHealthWidget();
        $data = $widget->data($this->admin);

        $this->assertEquals([], $data);
    }

    /** @test */
    public function server_health_widget_returns_php_version()
    {
        $widget = new ServerHealthWidget();
        $data = $widget->data($this->superAdmin);

        $this->assertEquals(PHP_VERSION, $data['server_health']['php_version']);
        $this->assertArrayHasKey('laravel_version', $data['server_health']);
        $this->assertArrayHasKey('cache_driver', $data['server_health']);
        $this->assertArrayHasKey('session_driver', $data['server_health']);
        $this->assertArrayHasKey('queue_driver', $data['server_health']);
        $this->assertArrayHasKey('disk_free', $data['server_health']);
        $this->assertArrayHasKey('disk_total', $data['server_health']);
        $this->assertArrayHasKey('disk_used_pct', $data['server_health']);
        $this->assertArrayHasKey('mail_status', $data['server_health']);
        $this->assertArrayHasKey('scheduler_last_run', $data['server_health']);
    }

    /** @test */
    public function quick_actions_widget_gates_system_actions()
    {
        $widget = new QuickActionsWidget();
        $saData = $widget->data($this->superAdmin);
        $userData = $widget->data($this->user);

        $this->assertTrue($saData['quick_actions']['can_manage_system']);
        $this->assertFalse($userData['quick_actions']['can_manage_system']);
    }

    /** @test */
    public function activity_widget_returns_activities()
    {
        activity()->by($this->superAdmin)->log('Test activity entry');

        $widget = new ActivityWidget();
        $data = $widget->data($this->superAdmin);

        $this->assertArrayHasKey('activity', $data);
        $this->assertGreaterThan(0, $data['activity']['activities']->count());
    }

    /** @test */
    public function cache_key_includes_user_id_and_version()
    {
        Cache::put('dashboard:version', 7);

        $this->actingAs($this->superAdmin)->get(route('dashboard'));

        $this->assertTrue(Cache::has("dashboard:w:operations:{$this->superAdmin->id}:v7"));
        $this->assertTrue(Cache::has("dashboard:w:renewals:{$this->superAdmin->id}:v7"));
    }

    /** @test */
    public function operations_widget_has_services_by_type_chart()
    {
        Domain::factory()->create(['status' => 'active', 'cost' => 15.00, 'user_id' => $this->superAdmin->id]);

        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $response->assertSee('servicesTypeChart');
    }

    /** @test */
    public function tasks_widget_shows_doughnut_chart()
    {
        Task::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $response->assertSee('tasksStatusChart');
    }

    /** @test */
    public function assets_widget_shows_status_chart()
    {
        Asset::factory()->create(['status' => 'available', 'user_id' => $this->superAdmin->id]);

        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $response->assertSee('assetsStatusChart');
    }

    /** @test */
    public function renewals_widget_shows_expiry_forecast_chart()
    {
        ExpiryTracker::factory()->create([
            'expiry_date' => now()->addMonth(),
            'status' => 'active',
            'user_id' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $response->assertSee('renewalsExpiryChart');
    }
}
