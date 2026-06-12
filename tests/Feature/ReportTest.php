<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\LoginAudit;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Task;
use App\Models\User;
use App\Models\Voip;
use App\Models\Vps;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
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
        $activity = new Activity();
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
        Domain::factory()->create(['cost' => 99.99, 'status' => 'active']);
        Domain::factory()->create(['cost' => 49.99, 'status' => 'expired']);
        Hosting::factory()->create(['cost' => 199.99, 'status' => 'active']);

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
}
