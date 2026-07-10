<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\User;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MonitorTest extends TestCase
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

    public function test_ping_valid_url(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $domain = Domain::factory()->create(['monitoring_url' => 'https://example.com']);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/monitor/domains/{$domain->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['url', 'ping', 'ssl', 'checked_at']]);

        $this->assertNotNull($domain->fresh()->last_ping_at);
    }

    public function test_check_with_no_monitoring_url(): void
    {
        $domain = Domain::factory()->create(['monitoring_url' => null]);

        $this->actingAs($this->admin)
            ->getJson("/api/monitor/domains/{$domain->id}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'No monitoring URL configured');
    }

    public function test_invalid_type(): void
    {
        $this->actingAs($this->admin)
            ->getJson('/api/monitor/invalid/1')
            ->assertNotFound()
            ->assertJsonPath('message', 'Invalid type');
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/monitor/domains/1')->assertUnauthorized();
    }

    public function test_non_admin_cannot_ping_others_domain(): void
    {
        $otherUser = User::factory()->create();
        $domain = Domain::factory()->create([
            'monitoring_url' => 'https://other.com',
            'user_id' => $otherUser->id,
        ]);

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/monitor/domains/{$domain->id}")
            ->assertStatus(403);
    }

    public function test_check_nonexistent_returns_404(): void
    {
        $this->actingAs($this->admin)
            ->getJson('/api/monitor/domains/99999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Not found');
    }

    public function test_web_monitor_invalid_type(): void
    {
        $this->actingAs($this->admin)
            ->get(route('monitor.check', ['type' => 'invalid', 'id' => 1]))
            ->assertSessionHas('error', 'Invalid type.');
    }

    public function test_web_monitor_resource_not_found(): void
    {
        $this->actingAs($this->admin)
            ->get(route('monitor.check', ['type' => 'domains', 'id' => 99999]))
            ->assertSessionHas('error', 'Resource not found.');
    }

    public function test_monitor_check_query_count_is_reasonable(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $domain = Domain::factory()->create(['monitoring_url' => 'https://example.com', 'user_id' => $this->admin->id]);

        DB::enableQueryLog();

        $this->actingAs($this->admin)
            ->getJson("/api/monitor/domains/{$domain->id}")
            ->assertOk();

        $queries = count(DB::getQueryLog());

        DB::disableQueryLog();

        $this->assertLessThanOrEqual(10, $queries, "Monitor check made {$queries} queries, expected ≤10");
    }
}
