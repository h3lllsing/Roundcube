<?php

namespace Tests\Feature;

use App\Enums\DomainStatus;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('domains.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_index_requires_super_admin(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->get(route('domains.index'))->assertForbidden();
    }

    public function test_super_admin_can_view_domains(): void
    {
        $admin = User::factory()->create(['role' => 'super-admin']);
        Domain::factory()->create(['name' => 'example.com']);

        $response = $this->actingAs($admin)->get(route('domains.index'));
        $response->assertStatus(200);
        $response->assertSee('example.com');
    }

    public function test_domain_has_valid_status_enum(): void
    {
        $domain = Domain::factory()->create(['status' => DomainStatus::Active]);
        $this->assertTrue($domain->status === DomainStatus::Active);
        $this->assertNotNull($domain->status->value);
    }
}
