<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\Domain;
use App\Models\EmailAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('email_accounts.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_index_requires_admin(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->get(route('email_accounts.index'))->assertForbidden();
    }

    public function test_admin_can_view_email_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $domain = Domain::factory()->create();
        EmailAccount::factory()->create([
            'domain_id' => $domain->id,
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($admin)->get(route('email_accounts.index'));
        $response->assertStatus(200);
    }

    public function test_email_account_has_valid_status_enum(): void
    {
        $domain = Domain::factory()->create();
        $account = EmailAccount::factory()->create([
            'domain_id' => $domain->id,
            'status' => AccountStatus::Active,
        ]);
        $this->assertTrue($account->status === AccountStatus::Active);
        $this->assertNotNull($account->status->value);
    }
}
