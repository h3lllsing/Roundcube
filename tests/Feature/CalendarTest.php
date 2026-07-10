<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\ExpiryTracker;
use App\Models\Module;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);
        $this->user = User::factory()->create();
    }

    private function withinCurrentMonth(int $daysFromNow = 3): string
    {
        $date = Carbon::now()->addDays($daysFromNow);
        if ($date->month !== Carbon::now()->month) {
            $date = Carbon::now()->endOfMonth();
        }

        return $date->toDateString();
    }

    public function test_returns_events_for_user()
    {
        Domain::factory()->create([
            'name' => 'test-domain.com',
            'expiry_date' => $this->withinCurrentMonth(),
            'user_id' => $this->user->id,
        ]);

        $token = $this->user->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/calendar');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['month', 'year', 'start', 'end', 'events']]);
        $this->assertCount(1, $response->json('data.events'));
        $this->assertEquals('test-domain.com', $response->json('data.events.0.name'));
    }

    public function test_non_admin_only_sees_own_events()
    {
        $date = $this->withinCurrentMonth(4);
        Domain::factory()->create([
            'name' => 'my-domain.com',
            'expiry_date' => $date,
            'user_id' => $this->user->id,
            'service_provider_id' => null,
        ]);
        Domain::factory()->create([
            'name' => 'admin-domain.com',
            'expiry_date' => $date,
            'user_id' => $this->admin->id,
            'service_provider_id' => null,
        ]);

        $token = $this->user->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/calendar');

        $response->assertOk();
        $this->assertCount(1, $response->json('data.events'));
        $this->assertEquals('my-domain.com', $response->json('data.events.0.name'));
    }

    public function test_super_admin_sees_all_events()
    {
        $date = $this->withinCurrentMonth(5);
        Domain::factory()->create([
            'name' => 'user-domain.com',
            'expiry_date' => $date,
            'user_id' => $this->user->id,
            'service_provider_id' => null,
        ]);
        Domain::factory()->create([
            'name' => 'admin-domain.com',
            'expiry_date' => $date,
            'user_id' => $this->admin->id,
            'service_provider_id' => null,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/calendar');

        $response->assertOk();
        $this->assertCount(2, $response->json('data.events'));
    }

    public function test_filters_by_month_and_year()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/calendar');
        $month = $response->json('data.month');
        $year = $response->json('data.year');

        Domain::factory()->create([
            'name' => 'filtered-domain.com',
            'expiry_date' => $this->withinCurrentMonth(),
            'user_id' => $this->admin->id,
            'service_provider_id' => null,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/calendar?month={$month}&year={$year}");

        $response->assertOk();
        $this->assertCount(1, $response->json('data.events'));
        $this->assertEquals('filtered-domain.com', $response->json('data.events.0.name'));
    }

    public function test_events_sorted_by_date()
    {
        $now = Carbon::now();
        $earlyDate = $now->copy()->startOfMonth()->addDays(5)->toDateString();
        $lateDate = $now->copy()->startOfMonth()->addDays(15)->toDateString();

        Domain::factory()->create([
            'name' => 'later-domain.com',
            'expiry_date' => $lateDate,
            'user_id' => $this->admin->id,
            'service_provider_id' => null,
        ]);
        Domain::factory()->create([
            'name' => 'earlier-domain.com',
            'expiry_date' => $earlyDate,
            'user_id' => $this->admin->id,
            'service_provider_id' => null,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/calendar');

        $response->assertOk();
        $events = $response->json('data.events');
        $this->assertCount(2, $events);
        $sorted = collect($events)->sortBy('expiry_date')->values();
        $this->assertEquals('earlier-domain.com', $sorted[0]['name']);
    }

    public function test_requires_authentication()
    {
        $this->getJson('/api/calendar')->assertUnauthorized();
    }

    public function test_includes_multiple_service_types()
    {
        $date = $this->withinCurrentMonth();
        Domain::factory()->create([
            'name' => 'domain-event',
            'expiry_date' => $date,
            'user_id' => $this->admin->id,
            'service_provider_id' => null,
        ]);
        ExpiryTracker::factory()->create([
            'name' => 'tracker-event',
            'expiry_date' => $date,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/calendar');

        $response->assertOk();
        $types = collect($response->json('data.events'))->pluck('type')->unique();
        $this->assertContains('domains', $types);
        $this->assertContains('expiry-trackers', $types);
    }

    public function test_includes_tasks_with_due_dates()
    {
        $module = Module::factory()->create();
        $task = Task::create([
            'title' => 'Calendar Task',
            'due_date' => $this->withinCurrentMonth(),
            'priority' => 'high',
            'status' => 'active',
            'module_id' => $module->id,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/calendar');

        $response->assertOk();
        $tasks = collect($response->json('data.events'))->where('type', 'tasks');
        $this->assertCount(1, $tasks);
        $this->assertEquals('Calendar Task', $tasks->first()['name']);
    }

    public function test_non_admin_sees_own_tasks_in_calendar()
    {
        $module = Module::factory()->create();
        $task = Task::create([
            'title' => 'My Task',
            'due_date' => $this->withinCurrentMonth(),
            'priority' => 'medium',
            'status' => 'active',
            'module_id' => $module->id,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
        $task->assignees()->attach($this->user->id);

        $token = $this->user->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/calendar');

        $response->assertOk();
        $tasks = collect($response->json('data.events'))->where('type', 'tasks');
        $this->assertCount(1, $tasks);
    }

    public function test_non_admin_does_not_see_others_tasks()
    {
        $module = Module::factory()->create();
        $task = Task::create([
            'title' => 'Admin Task',
            'due_date' => $this->withinCurrentMonth(),
            'priority' => 'low',
            'status' => 'active',
            'module_id' => $module->id,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);
        $task->assignees()->attach($this->admin->id);

        $token = $this->user->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/calendar');

        $response->assertOk();
        $tasks = collect($response->json('data.events'))->where('type', 'tasks');
        $this->assertCount(0, $tasks);
    }
}
