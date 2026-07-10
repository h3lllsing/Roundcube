<?php

namespace Tests\Feature;

use App\Services\ReportService;
use App\Models\Asset;
use App\Models\Domain;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\LoginAudit;
use App\Models\Task;
use App\Models\User;
use App\Models\Vps;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);
    }

    public function test_report_tasks(): void
    {
        Task::factory()->count(3)->create(['created_at' => now()]);
        Task::factory()->count(2)->create(['created_at' => now()->subDays(2), 'status' => 'completed']);

        $this->actingAs($this->admin)
            ->getJson('/api/reports?type=tasks&group_by=day')
            ->assertOk()
            ->assertJsonStructure(['data' => ['type', 'group_by', 'periods', 'summary']])
            ->assertJsonPath('data.type', 'tasks');
    }

    public function test_report_activity(): void
    {
        $activity = new Activity;
        $activity->log_name = 'default';
        $activity->description = 'test';
        $activity->event = 'created';
        $activity->causer_id = $this->admin->id;
        $activity->causer_type = User::class;
        $activity->created_at = now();
        $activity->save();

        $this->actingAs($this->admin)
            ->getJson('/api/reports?type=activity&group_by=week')
            ->assertOk()
            ->assertJsonPath('data.type', 'activity');
    }

    public function test_report_logins(): void
    {
        LoginAudit::create([
            'user_id' => $this->admin->id,
            'email' => $this->admin->email,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'event' => 'login_success',
            'created_at' => now(),
        ]);
        LoginAudit::create([
            'user_id' => $this->admin->id,
            'email' => $this->admin->email,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'event' => 'login_failed',
            'created_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->getJson('/api/reports?type=logins&group_by=month')
            ->assertOk()
            ->assertJsonPath('data.type', 'logins')
            ->assertJsonStructure(['data' => ['summary' => ['total', 'successful', 'failed']]]);
    }

    public function test_report_costs(): void
    {
        Domain::factory()->create(['cost' => 99.99, 'status' => 'active', 'service_provider_id' => null]);
        Domain::factory()->create(['cost' => 49.99, 'status' => 'expired', 'service_provider_id' => null]);
        Hosting::factory()->create(['cost' => 199.99, 'status' => 'active', 'service_provider_id' => null]);

        $this->actingAs($this->admin)
            ->getJson('/api/reports?type=costs')
            ->assertOk()
            ->assertJsonPath('data.type', 'costs')
            ->assertJsonStructure(['data' => ['summary' => ['total_monthly', 'by_type', 'top_10', 'by_status']]])
            ->assertJsonPath('data.summary.total_monthly', 349.97);
    }

    public function test_report_costs_empty(): void
    {
        $this->actingAs($this->admin)
            ->getJson('/api/reports?type=costs')
            ->assertOk()
            ->assertJsonPath('data.summary.total_monthly', 0)
            ->assertJsonPath('data.summary.top_10', []);
    }

    public function test_report_invalid_type(): void
    {
        $this->actingAs($this->admin)
            ->getJson('/api/reports?type=invalid')
            ->assertOk()
            ->assertJsonPath('data.periods', []);
    }

    public function test_report_users(): void
    {
        User::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->getJson('/api/reports/users')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name', 'email']]]);
    }

    public function test_report_forbidden_for_regular_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/reports')
            ->assertForbidden();
    }

    public function test_report_requires_authentication(): void
    {
        $this->getJson('/api/reports')->assertUnauthorized();
    }

    public function test_period_raw_mysql_branch(): void
    {
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->method('getDriverName')->willReturn('mysql');
        DB::shouldReceive('connection')->andReturn($connectionMock);

        $service = new ReportService;
        $method = new \ReflectionMethod($service, 'periodRaw');
        $method->setAccessible(true);

        $week = $method->invoke($service, 'created_at', 'week');
        $this->assertStringContainsString('DATE_FORMAT', $week);
        $this->assertStringContainsString('%x-W%v', $week);

        $month = $method->invoke($service, 'created_at', 'month');
        $this->assertStringContainsString('DATE_FORMAT', $month);
        $this->assertStringContainsString('%Y-%m', $month);

        $day = $method->invoke($service, 'created_at', 'day');
        $this->assertStringContainsString('DATE(', $day);
        $this->assertStringNotContainsString('DATE_FORMAT', $day);
    }

    public function test_report_tasks_with_user_id_filter(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['created_at' => now()]);
        $task->assignees()->attach($user->id);

        $this->actingAs($this->admin)
            ->getJson('/api/reports?type=tasks&user_id='.$user->id)
            ->assertOk()
            ->assertJsonPath('data.type', 'tasks');
    }

    public function test_report_activity_with_user_id_filter(): void
    {
        $activity = new Activity;
        $activity->log_name = 'default';
        $activity->description = 'filtered activity';
        $activity->event = 'created';
        $activity->causer_id = $this->admin->id;
        $activity->causer_type = User::class;
        $activity->save();

        $this->actingAs($this->admin)
            ->getJson('/api/reports?type=activity&user_id='.$this->admin->id)
            ->assertOk()
            ->assertJsonPath('data.type', 'activity');
    }

    public function test_report_logins_with_user_id_filter(): void
    {
        LoginAudit::create([
            'user_id' => $this->admin->id,
            'email' => $this->admin->email,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'event' => 'login_success',
            'created_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->getJson('/api/reports?type=logins&user_id='.$this->admin->id)
            ->assertOk()
            ->assertJsonPath('data.type', 'logins');
    }

    public function test_web_report_page_loads(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reports.index'))
            ->assertStatus(200);
    }

    public function test_web_report_filters(): void
    {
        Domain::factory()->create(['cost' => 99.99, 'status' => 'active']);
        LoginAudit::create([
            'user_id' => $this->admin->id, 'email' => $this->admin->email,
            'ip_address' => '127.0.0.1', 'event' => 'login_success', 'created_at' => now(),
        ]);

        $today = now()->format('Y-m-d');
        $this->actingAs($this->admin);
        $this->get(route('reports.index', ['cost_status' => 'active']))->assertStatus(200);
        $this->get(route('reports.index', ['user_id' => $this->admin->id]))->assertStatus(200);
        $this->get(route('reports.index', ['date_from' => $today, 'date_to' => $today]))->assertStatus(200);
    }

    public function test_web_report_export_with_data(): void
    {
        Domain::factory()->create(['name' => 'export-test.com', 'cost' => 49.99, 'status' => 'active']);
        Task::factory()->create(['created_at' => now()]);

        $this->actingAs($this->admin)
            ->get(route('reports.export', ['category' => 'domains', 'report' => 'active']))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_api_report_export_activity(): void
    {
        $activity = new Activity;
        $activity->log_name = 'default';
        $activity->description = 'export activity';
        $activity->event = 'created';
        $activity->causer_id = $this->admin->id;
        $activity->causer_type = User::class;
        $activity->save();

        $this->actingAs($this->admin)
            ->get('/api/reports/export?type=activity&group_by=day')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_api_report_export_logins(): void
    {
        LoginAudit::create([
            'user_id' => $this->admin->id, 'email' => $this->admin->email,
            'ip_address' => '127.0.0.1', 'user_agent' => 'test',
            'event' => 'login_success', 'created_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get('/api/reports/export?type=logins&group_by=month')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_api_report_export_tasks(): void
    {
        Task::factory()->create(['created_at' => now()]);

        $this->actingAs($this->admin)
            ->get('/api/reports/export?type=tasks&group_by=day')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_api_report_export_invalid_type(): void
    {
        $this->actingAs($this->admin)
            ->get('/api/reports/export?type=invalid')
            ->assertNotFound();
    }

    public function test_web_reports_category_listing(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reports.category', 'domains'))
            ->assertStatus(200)
            ->assertSee('Active Domains')
            ->assertSee('Expired Domains')
            ->assertSee('Domains Expiring');
    }

    public function test_web_reports_show_active_domains(): void
    {
        Domain::factory()->create([
            'name' => 'example-report.com',
            'status' => 'active',
            'cost' => 19.99,
            'expiry_date' => now()->addDays(60),
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.show', ['category' => 'domains', 'report' => 'active']))
            ->assertStatus(200)
            ->assertSee('example-report.com')
            ->assertSee('$19.99');
    }

    public function test_web_reports_show_expiring_domains(): void
    {
        Domain::factory()->create([
            'name' => 'expiring-soon.com',
            'status' => 'active',
            'expiry_date' => now()->addDays(5),
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.show', ['category' => 'domains', 'report' => 'expiring']))
            ->assertStatus(200)
            ->assertSee('expiring-soon.com');
    }

    public function test_web_reports_show_expired_domains(): void
    {
        Domain::factory()->create([
            'name' => 'already-expired.com',
            'status' => 'expired',
            'expiry_date' => now()->subDays(10),
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.show', ['category' => 'domains', 'report' => 'expired']))
            ->assertStatus(200)
            ->assertSee('already-expired.com');
    }

    public function test_web_reports_active_hosting(): void
    {
        Hosting::factory()->create([
            'name' => 'My Hosting Plan',
            'status' => 'active',
            'cost' => 29.99,
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.show', ['category' => 'hosting', 'report' => 'active']))
            ->assertStatus(200)
            ->assertSee('My Hosting Plan');
    }

    public function test_web_reports_home_content(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reports.index'))
            ->assertStatus(200)
            ->assertSee('Enterprise reporting center')
            ->assertSee('Domains')
            ->assertSee('Hosting')
            ->assertSee('Assets')
            ->assertSee('Tasks');
    }

    public function test_web_reports_missing_category_returns_404(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reports.category', 'nonexistent'))
            ->assertStatus(404);
    }

    public function test_web_reports_missing_report_returns_404(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reports.show', ['category' => 'domains', 'report' => 'nonexistent']))
            ->assertStatus(404);
    }

    public function test_web_reports_requires_super_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertForbidden();
    }

    public function test_web_reports_export_csv(): void
    {
        Domain::factory()->create([
            'name' => 'csv-export-test.com',
            'status' => 'active',
            'cost' => 15.00,
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.export', ['category' => 'domains', 'report' => 'active']))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="domains-active-' . now()->format('Y-m-d') . '.csv"');
    }

    public function test_web_reports_vps_active(): void
    {
        Vps::factory()->create([
            'name' => 'Test VPS Server',
            'status' => 'active',
            'cost' => 49.99,
            'ip_address' => '10.0.0.1',
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.show', ['category' => 'vps', 'report' => 'active']))
            ->assertStatus(200)
            ->assertSee('Test VPS Server')
            ->assertSee('10.0.0.1');
    }

    public function test_web_reports_renewal_today(): void
    {
        $tracker = ExpiryTracker::factory()->create([
            'name' => 'SSL Certificate Renewal',
            'status' => 'active',
            'expiry_date' => today(),
            'cost' => 99.00,
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.show', ['category' => 'renewals', 'report' => 'today']))
            ->assertStatus(200)
            ->assertSee('SSL Certificate Renewal');
    }

    public function test_web_reports_assets_assigned(): void
    {
        Asset::factory()->create([
            'asset_tag' => 'TAG-001',
            'status' => 'assigned',
            'department' => 'Engineering',
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.show', ['category' => 'assets', 'report' => 'assigned']))
            ->assertStatus(200)
            ->assertSee('TAG-001');
    }

    public function test_web_reports_tasks_pending(): void
    {
        Task::factory()->create([
            'title' => 'Review quarterly budget',
            'status' => 'pending',
            'due_date' => now()->addDays(7),
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.show', ['category' => 'tasks', 'report' => 'pending']))
            ->assertStatus(200)
            ->assertSee('Review quarterly budget');
    }

    public function test_web_reports_users_active(): void
    {
        $user = User::factory()->create([
            'name' => 'Active User Person',
        ]);

        $this->actingAs($this->admin)
            ->get(route('reports.show', ['category' => 'users', 'report' => 'active']))
            ->assertStatus(200)
            ->assertSee('Active User Person');
    }

    public function test_web_reports_requires_auth(): void
    {
        $this->get(route('reports.index'))->assertRedirectToRoute('login');
    }
}
