<?php

namespace Tests\Unit;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Feature;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Task;
use App\Models\Voip;
use App\Models\Vps;
use App\Models\Webhook;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ModelCastingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
    }

    public function test_task_due_date_cast(): void
    {
        $task = Task::factory()->create(['due_date' => '2026-12-31 23:59:59']);
        $this->assertInstanceOf(Carbon::class, $task->due_date);
    }

    public function test_domain_date_casts(): void
    {
        $domain = Domain::factory()->create([
            'registration_date' => '2026-01-15',
            'expiry_date' => '2026-12-31',
        ]);
        $this->assertInstanceOf(Carbon::class, $domain->registration_date);
        $this->assertInstanceOf(Carbon::class, $domain->expiry_date);
    }

    public function test_domain_boolean_cast(): void
    {
        $domain = Domain::factory()->create(['auto_renew' => true]);
        $this->assertIsBool($domain->auto_renew);
        $this->assertTrue($domain->auto_renew);
    }

    public function test_domain_decimal_cast(): void
    {
        $domain = Domain::factory()->create(['cost' => 99.99]);
        $this->assertSame('99.99', $domain->cost);
    }

    public function test_domain_array_cast(): void
    {
        $dns = ['ns1.example.com', 'ns2.example.com'];
        $domain = Domain::factory()->create(['dns_servers' => $dns]);
        $this->assertIsArray($domain->dns_servers);
        $this->assertSame($dns, $domain->dns_servers);
    }

    public function test_hosting_date_casts(): void
    {
        $hosting = Hosting::factory()->create([
            'start_date' => '2026-01-15',
            'expiry_date' => '2026-12-31',
        ]);
        $this->assertInstanceOf(Carbon::class, $hosting->start_date);
        $this->assertInstanceOf(Carbon::class, $hosting->expiry_date);
    }

    public function test_vps_integer_casts(): void
    {
        $vps = Vps::factory()->create([
            'ram_mb' => 4096,
            'disk_gb' => 80,
            'cpu_cores' => 4,
        ]);
        $this->assertIsInt($vps->ram_mb);
        $this->assertIsInt($vps->disk_gb);
        $this->assertIsInt($vps->cpu_cores);
    }

    public function test_vps_date_casts(): void
    {
        $vps = Vps::factory()->create([
            'start_date' => '2026-01-15',
            'expiry_date' => '2026-12-31',
        ]);
        $this->assertInstanceOf(Carbon::class, $vps->start_date);
        $this->assertInstanceOf(Carbon::class, $vps->expiry_date);
    }

    public function test_voip_date_casts(): void
    {
        $voip = Voip::factory()->create([
            'start_date' => '2026-01-15',
            'expiry_date' => '2026-12-31',
        ]);
        $this->assertInstanceOf(Carbon::class, $voip->start_date);
        $this->assertInstanceOf(Carbon::class, $voip->expiry_date);
    }

    public function test_service_provider_date_casts(): void
    {
        $sp = ServiceProvider::factory()->create([
            'start_date' => '2026-01-15',
            'expiry_date' => '2026-12-31',
        ]);
        $this->assertInstanceOf(Carbon::class, $sp->start_date);
        $this->assertInstanceOf(Carbon::class, $sp->expiry_date);
    }

    public function test_domain_email_date_cast(): void
    {
        $de = DomainEmail::factory()->create(['expiry_date' => '2026-12-31']);
        $this->assertInstanceOf(Carbon::class, $de->expiry_date);
    }

    public function test_domain_email_integer_cast(): void
    {
        $de = DomainEmail::factory()->create(['storage_mb' => 10240]);
        $this->assertIsInt($de->storage_mb);
    }

    public function test_other_service_date_casts(): void
    {
        $os = OtherService::factory()->create([
            'start_date' => '2026-01-15',
            'expiry_date' => '2026-12-31',
        ]);
        $this->assertInstanceOf(Carbon::class, $os->start_date);
        $this->assertInstanceOf(Carbon::class, $os->expiry_date);
    }

    public function test_expiry_tracker_date_casts(): void
    {
        $et = ExpiryTracker::factory()->create([
            'expiry_date' => '2026-12-31',
            'renewal_date' => '2026-11-30',
        ]);
        $this->assertInstanceOf(Carbon::class, $et->expiry_date);
        $this->assertInstanceOf(Carbon::class, $et->renewal_date);
    }

    public function test_feature_boolean_cast(): void
    {
        $feature = Feature::factory()->create(['is_active' => false]);
        $this->assertIsBool($feature->is_active);
        $this->assertFalse($feature->is_active);
    }

    public function test_module_boolean_cast(): void
    {
        $module = Module::factory()->create(['is_active' => true]);
        $this->assertIsBool($module->is_active);
        $this->assertTrue($module->is_active);
    }

    public function test_webhook_casts(): void
    {
        $webhook = Webhook::factory()->create([
            'events' => ['task.created', 'domain.expired'],
            'is_active' => true,
            'last_fired_at' => now(),
        ]);
        $this->assertIsArray($webhook->events);
        $this->assertIsBool($webhook->is_active);
        $this->assertInstanceOf(Carbon::class, $webhook->last_fired_at);
    }

    public function test_module_role_permission_boolean_casts(): void
    {
        $module = Module::factory()->create();
        $role = Role::where('slug', 'admin')->firstOrFail();
        $mrp = ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $role->id,
            'can_create' => true,
            'can_read' => true,
            'can_update' => false,
            'can_delete' => false,
            'can_approve' => true,
            'can_export' => false,
        ]);
        $this->assertIsBool($mrp->can_create);
        $this->assertIsBool($mrp->can_read);
        $this->assertIsBool($mrp->can_update);
        $this->assertIsBool($mrp->can_delete);
        $this->assertIsBool($mrp->can_export);
    }

    public function test_feature_active_modules_relationship(): void
    {
        $feature = Feature::factory()->create();
        Module::factory()->count(3)->create(['feature_id' => $feature->id, 'is_active' => true]);
        Module::factory()->create(['feature_id' => $feature->id, 'is_active' => false]);

        $this->assertCount(3, $feature->activeModules);
        $this->assertCount(4, $feature->modules);
    }
}
