<?php

namespace Tests\Unit;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\Hosting;
use App\Models\LoginAudit;
use App\Models\Task;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ReportService::class);
    }

    public function test_total_monthly_cost(): void
    {
        Domain::factory()->create(['cost' => 100, 'status' => 'active', 'service_provider_id' => null]);
        Hosting::factory()->create(['cost' => 50, 'status' => 'active', 'service_provider_id' => null]);

        $total = $this->service->totalMonthlyCost();

        $this->assertEquals(150.0, $total);
    }

    public function test_total_monthly_cost_with_status_filter(): void
    {
        Domain::factory()->create(['cost' => 100, 'status' => 'active', 'service_provider_id' => null]);
        Domain::factory()->create(['cost' => 50, 'status' => 'expired', 'service_provider_id' => null]);

        $total = $this->service->totalMonthlyCost(['cost_status' => 'active']);

        $this->assertEquals(100.0, $total);
    }

    public function test_total_monthly_cost_no_data(): void
    {
        $total = $this->service->totalMonthlyCost();

        $this->assertEquals(0.0, $total);
    }

    public function test_cost_by_type(): void
    {
        Domain::factory()->create(['cost' => 100, 'status' => 'active', 'service_provider_id' => null]);
        Hosting::factory()->count(2)->create(['cost' => 50, 'status' => 'active', 'service_provider_id' => null]);

        $result = $this->service->costByType();

        $this->assertArrayHasKey('domains', $result);
        $this->assertArrayHasKey('hostings', $result);
        $this->assertEquals(100.0, $result['domains']['total']);
        $this->assertEquals(1, $result['domains']['count']);
        $this->assertEquals(100.0, $result['hostings']['total']);
        $this->assertEquals(2, $result['hostings']['count']);
    }

    public function test_cost_by_type_with_filters(): void
    {
        Domain::factory()->create(['cost' => 100, 'status' => 'active', 'service_provider_id' => null]);
        Domain::factory()->create(['cost' => 75, 'status' => 'expired', 'service_provider_id' => null]);

        $result = $this->service->costByType(['cost_status' => 'active']);

        $this->assertEquals(100.0, $result['domains']['total']);
        $this->assertEquals(1, $result['domains']['count']);
    }

    public function test_top_costs_returns_top_10(): void
    {
        foreach (range(1, 15) as $i) {
            Domain::factory()->create(['name' => "Domain {$i}", 'cost' => $i * 10, 'status' => 'active', 'service_provider_id' => null]);
        }

        $result = $this->service->topCosts();

        $this->assertCount(5, $result);
        $this->assertEquals(150.0, $result[0]['cost']);
    }

    public function test_top_costs_with_filters(): void
    {
        Domain::factory()->create(['name' => 'Active', 'cost' => 100, 'status' => 'active', 'service_provider_id' => null]);
        Domain::factory()->create(['name' => 'Expired', 'cost' => 200, 'status' => 'expired', 'service_provider_id' => null]);

        $result = $this->service->topCosts(['cost_status' => 'expired']);

        $this->assertCount(1, $result);
        $this->assertEquals('Expired', $result[0]['name']);
    }

    public function test_task_summary(): void
    {
        Task::factory()->create(['status' => 'pending']);
        Task::factory()->create(['status' => 'in_progress']);
        Task::factory()->create(['status' => 'completed']);
        Task::factory()->create(['status' => 'cancelled']);

        $result = $this->service->taskSummary();

        $this->assertEquals(4, $result['total']);
        $this->assertEquals(1, $result['pending']);
        $this->assertEquals(1, $result['in_progress']);
        $this->assertEquals(1, $result['completed']);
        $this->assertEquals(1, $result['cancelled']);
    }

    public function test_task_summary_with_user_filter(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['status' => 'pending']);
        $task->assignees()->attach($user->id);

        Task::factory()->create(['status' => 'completed']);

        $result = $this->service->taskSummary(['user_id' => $user->id]);

        $this->assertEquals(1, $result['total']);
    }

    public function test_login_summary(): void
    {
        LoginAudit::factory()->create(['event' => 'login_success']);
        LoginAudit::factory()->create(['event' => 'login_success']);
        LoginAudit::factory()->create(['event' => 'login_failed']);

        $result = $this->service->loginSummary();

        $this->assertEquals(3, $result['total']);
        $this->assertEquals(2, $result['successful']);
        $this->assertEquals(1, $result['failed']);
    }

    public function test_login_summary_with_filters(): void
    {
        $user = User::factory()->create();
        LoginAudit::factory()->for($user)->create(['event' => 'login']);
        LoginAudit::factory()->create(['event' => 'login_failed']);

        $result = $this->service->loginSummary(['user_id' => $user->id]);

        $this->assertEquals(1, $result['total']);
    }

    public function test_build_csv_returns_valid_csv(): void
    {
        Domain::factory()->create(['cost' => 100, 'name' => 'Test Domain', 'status' => 'active', 'service_provider_id' => null]);
        Task::factory()->create(['status' => 'pending']);

        $csv = $this->service->buildCsv();

        $this->assertStringContainsString('Total Monthly Cost', $csv);
        $this->assertStringContainsString('Cost by Type', $csv);
        $this->assertStringContainsString('domains', $csv);
        $this->assertStringContainsString('Task Summary', $csv);
        $this->assertStringContainsString('Total', $csv);
    }

    public function test_build_csv_no_data(): void
    {
        $csv = $this->service->buildCsv();

        $this->assertStringContainsString('Total Monthly Cost', $csv);
        $this->assertStringContainsString('0.00', $csv);
    }
}
