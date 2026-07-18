<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\EmailAccount;
use App\Models\User;
use App\Services\EmailStatService;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class EmailStatMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Domain $domain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TyroSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->domain = Domain::create(['name' => 'example.com']);
    }

    public function test_failed_accounts_count_returns_zero_when_no_failures(): void
    {
        $count = app(EmailStatService::class)->failedAccountsCountLast24h();
        $this->assertEquals(0, $count);
    }

    public function test_failed_accounts_count_returns_correct_count(): void
    {
        $account = EmailAccount::create([
            'domain_id' => $this->domain->id,
            'email' => 'test@example.com',
            'password' => 'secret',
            'imap_host' => 'imap.example.com',
            'imap_port' => 993,
            'status' => 'active',
            'sync_enabled' => true,
        ]);

        activity()->event('imap_fetch_failed')
            ->performedOn($account)
            ->log("IMAP fetch failed for test@example.com: Connection timeout");

        $count = app(EmailStatService::class)->failedAccountsCountLast24h();
        $this->assertEquals(1, $count);
    }

    public function test_dashboard_shows_imap_health_card(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk();

        $response->assertSee('IMAP Health');
    }

    public function test_failed_accounts_count_filters_by_last_24h(): void
    {
        $account = EmailAccount::create([
            'domain_id' => $this->domain->id,
            'email' => 'old@example.com',
            'password' => 'secret',
            'imap_host' => 'imap.example.com',
            'imap_port' => 993,
            'status' => 'active',
            'sync_enabled' => true,
        ]);

        Activity::create([
            'description' => 'IMAP fetch failed for old@example.com',
            'event' => 'imap_fetch_failed',
            'subject_type' => EmailAccount::class,
            'subject_id' => $account->id,
            'causer_type' => null,
            'causer_id' => null,
            'created_at' => now()->subDays(2),
        ]);

        $count = app(EmailStatService::class)->failedAccountsCountLast24h();
        $this->assertEquals(0, $count);
    }

    public function test_failed_accounts_distinct_by_account(): void
    {
        $account = EmailAccount::create([
            'domain_id' => $this->domain->id,
            'email' => 'dup@example.com',
            'password' => 'secret',
            'imap_host' => 'imap.example.com',
            'imap_port' => 993,
            'status' => 'active',
            'sync_enabled' => true,
        ]);

        activity()->event('imap_fetch_failed')
            ->performedOn($account)
            ->log('Failure 1');
        activity()->event('imap_fetch_failed')
            ->performedOn($account)
            ->log('Failure 2');

        $count = app(EmailStatService::class)->failedAccountsCountLast24h();
        $this->assertEquals(1, $count);
    }
}
