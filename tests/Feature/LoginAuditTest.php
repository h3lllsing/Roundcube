<?php

namespace Tests\Feature;

use App\Models\LoginAudit;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);
    }

    public function test_index_lists_audits()
    {
        LoginAudit::create(['user_id' => $this->admin->id, 'email' => 'admin@test.com', 'ip_address' => '127.0.0.1', 'event' => 'login_success']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/login-audits');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_index_search_by_email()
    {
        LoginAudit::create(['user_id' => $this->admin->id, 'email' => 'findable@test.com', 'ip_address' => '1.2.3.4', 'event' => 'login_success']);
        LoginAudit::create(['user_id' => $this->admin->id, 'email' => 'other@test.com', 'ip_address' => '5.6.7.8', 'event' => 'login_failed']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/login-audits?search=findable');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('findable@test.com', $response->json('data.0.email'));
    }

    public function test_index_filter_by_event()
    {
        LoginAudit::create(['user_id' => $this->admin->id, 'email' => 'a@test.com', 'ip_address' => '1.2.3.4', 'event' => 'login_success']);
        LoginAudit::create(['user_id' => $this->admin->id, 'email' => 'b@test.com', 'ip_address' => '5.6.7.8', 'event' => 'login_failed']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/login-audits?event=login_failed');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('login_failed', $response->json('data.0.event'));
    }

    public function test_index_filter_by_user_id()
    {
        $otherUser = User::factory()->create();
        LoginAudit::create(['user_id' => $this->admin->id, 'email' => 'a@test.com', 'ip_address' => '1.2.3.4', 'event' => 'login_success']);
        LoginAudit::create(['user_id' => $otherUser->id, 'email' => 'b@test.com', 'ip_address' => '5.6.7.8', 'event' => 'login_success']);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/login-audits?user_id={$otherUser->id}");

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($otherUser->id, $response->json('data.0.user_id'));
    }

    public function test_index_filter_by_date_range()
    {
        LoginAudit::create(['user_id' => $this->admin->id, 'email' => 'a@test.com', 'ip_address' => '1.2.3.4', 'event' => 'login_success', 'created_at' => now()->subDays(10)]);
        LoginAudit::create(['user_id' => $this->admin->id, 'email' => 'b@test.com', 'ip_address' => '5.6.7.8', 'event' => 'login_success', 'created_at' => now()]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/login-audits?date_from=' . now()->subDays(5)->toDateString() . '&date_to=' . now()->addDay()->toDateString());

        $response->assertOk();
        $this->assertGreaterThanOrEqual(1, $response->json('total'));
    }

    public function test_show_audit()
    {
        $audit = LoginAudit::create(['user_id' => $this->admin->id, 'email' => 'a@test.com', 'ip_address' => '1.2.3.4', 'event' => 'login_success']);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/login-audits/{$audit->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $audit->id);
    }

    public function test_show_missing_returns_404()
    {
        $this->actingAs($this->admin)
            ->getJson('/api/login-audits/99999')
            ->assertStatus(404);
    }

    public function test_requires_super_admin_role()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/login-audits')
            ->assertStatus(403);
    }

    public function test_requires_authentication()
    {
        $this->getJson('/api/login-audits')->assertUnauthorized();
    }
}
