<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Voip;
use App\Models\Vps;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MonitorCheckCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
    }

    public function test_checks_all_model_types_with_monitoring_url(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $domain = Domain::factory()->create(['monitoring_url' => 'https://example.com', 'user_id' => $user->id]);
        $hosting = Hosting::factory()->create(['monitoring_url' => 'https://hosting.com', 'user_id' => $user->id]);
        $vps = Vps::factory()->create(['monitoring_url' => 'https://vps.com', 'user_id' => $user->id]);
        $voip = Voip::factory()->create(['monitoring_url' => 'https://voip.com', 'user_id' => $user->id]);
        $provider = ServiceProvider::factory()->create(['monitoring_url' => 'https://provider.com', 'user_id' => $user->id]);
        $email = DomainEmail::factory()->create(['monitoring_url' => 'https://email.com', 'user_id' => $user->id]);
        $other = OtherService::factory()->create(['monitoring_url' => 'https://other.com', 'user_id' => $user->id]);
        $tracker = ExpiryTracker::factory()->create(['monitoring_url' => 'https://tracker.com', 'user_id' => $user->id]);

        $this->artisan('monitor:check')
            ->expectsOutputToContain('Checking monitored services')
            ->expectsOutputToContain('8/8 services responded successfully')
            ->assertSuccessful();

        $this->assertNotNull($domain->fresh()->last_ping_at);
        $this->assertNotNull($hosting->fresh()->last_ping_at);
        $this->assertNotNull($vps->fresh()->last_ping_at);
        $this->assertNotNull($voip->fresh()->last_ping_at);
        $this->assertNotNull($provider->fresh()->last_ping_at);
        $this->assertNotNull($email->fresh()->last_ping_at);
        $this->assertNotNull($other->fresh()->last_ping_at);
        $this->assertNotNull($tracker->fresh()->last_ping_at);
    }

    public function test_skips_models_without_monitoring_url(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        Domain::factory()->create(['monitoring_url' => null, 'user_id' => $user->id]);
        Hosting::factory()->create(['monitoring_url' => null, 'user_id' => $user->id]);

        $this->artisan('monitor:check')
            ->expectsOutputToContain('0/0 services responded successfully')
            ->assertSuccessful();
    }

    public function test_reports_failed_pings(): void
    {
        Http::fake(['*' => Http::response(null, 500)]);
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        Domain::factory()->create(['monitoring_url' => 'https://fail.com', 'user_id' => $user->id]);
        Hosting::factory()->create(['monitoring_url' => 'https://fail2.com', 'user_id' => $user->id]);

        $this->artisan('monitor:check')
            ->expectsOutputToContain('0/2 services responded successfully')
            ->assertSuccessful();
    }

    public function test_handles_http_exception(): void
    {
        Http::fake(['*' => fn () => throw new \Exception('Connection refused')]);
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        Domain::factory()->create(['monitoring_url' => 'https://timeout.com', 'user_id' => $user->id]);

        $this->artisan('monitor:check')
            ->expectsOutputToContain('0/1 services responded successfully')
            ->assertSuccessful();
    }
}
