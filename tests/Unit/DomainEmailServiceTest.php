<?php

namespace Tests\Unit;

use App\Models\Domain;
use App\Models\Module;
use App\Models\User;
use App\Services\DomainEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainEmailServiceTest extends TestCase
{
    use RefreshDatabase;

    private DomainEmailService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DomainEmailService::class);
    }

    public function test_create(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $user->id]);

        $entry = $this->service->create([
            'user_id' => $user->id,
            'domain_id' => $domain->id,
            'email' => 'admin@example.com',
            'provider' => 'Google',
            'expiry_date' => '2027-01-01',
        ]);

        $this->assertEquals('admin@example.com', $entry->email);
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $user->id]);
        $entry = $this->service->create([
            'user_id' => $user->id,
            'domain_id' => $domain->id,
            'email' => 'old@example.com',
            'expiry_date' => '2027-01-01',
        ]);

        $updated = $this->service->update($entry, ['email' => 'new@example.com']);

        $this->assertEquals('new@example.com', $updated->email);
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $user->id]);
        $entry = $this->service->create([
            'user_id' => $user->id,
            'domain_id' => $domain->id,
            'email' => 'del@example.com',
            'expiry_date' => '2027-01-01',
        ]);

        $this->service->delete($entry);

        $this->assertSoftDeleted($entry);
    }

    public function test_list_with_trashed(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $user->id]);
        $entry = $this->service->create([
            'user_id' => $user->id,
            'domain_id' => $domain->id,
            'email' => 'del@example.com',
            'expiry_date' => '2027-01-01',
        ]);
        $entry->delete();

        $result = $this->service->list(['with_trashed' => true]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_module_id(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $user->id]);
        $m1 = Module::factory()->create();
        $m2 = Module::factory()->create();

        $this->service->create([
            'user_id' => $user->id, 'domain_id' => $domain->id,
            'email' => 'a@example.com', 'expiry_date' => '2027-01-01', 'module_id' => $m1->id,
        ]);
        $this->service->create([
            'user_id' => $user->id, 'domain_id' => $domain->id,
            'email' => 'b@example.com', 'expiry_date' => '2027-01-01', 'module_id' => $m2->id,
        ]);

        $result = $this->service->list(['module_id' => $m1->id]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_domain_id(): void
    {
        $user = User::factory()->create();
        $d1 = Domain::factory()->create(['user_id' => $user->id]);
        $d2 = Domain::factory()->create(['user_id' => $user->id]);

        $this->service->create([
            'user_id' => $user->id, 'domain_id' => $d1->id,
            'email' => 'a@example.com', 'expiry_date' => '2027-01-01',
        ]);
        $this->service->create([
            'user_id' => $user->id, 'domain_id' => $d2->id,
            'email' => 'b@example.com', 'expiry_date' => '2027-01-01',
        ]);

        $result = $this->service->list(['domain_id' => $d1->id]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_status(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $user->id]);
        $this->service->create([
            'user_id' => $user->id, 'domain_id' => $domain->id,
            'email' => 'a@example.com', 'expiry_date' => '2027-01-01', 'status' => 'active',
        ]);
        $this->service->create([
            'user_id' => $user->id, 'domain_id' => $domain->id,
            'email' => 'b@example.com', 'expiry_date' => '2027-01-01', 'status' => 'expired',
        ]);

        $result = $this->service->list(['status' => 'active']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_search(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $user->id]);
        $this->service->create([
            'user_id' => $user->id, 'domain_id' => $domain->id,
            'email' => 'admin@example.com', 'provider' => 'Google', 'expiry_date' => '2027-01-01',
        ]);
        $this->service->create([
            'user_id' => $user->id, 'domain_id' => $domain->id,
            'email' => 'info@test.org', 'provider' => 'Microsoft', 'expiry_date' => '2027-01-01',
        ]);

        $result = $this->service->list(['search' => 'admin']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_clamps_per_page(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $user->id]);
        for ($i = 0; $i < 110; $i++) {
            $this->service->create([
                'user_id' => $user->id, 'domain_id' => $domain->id,
                'email' => "email{$i}@example.com", 'expiry_date' => '2027-01-01',
            ]);
        }

        $result = $this->service->list(['per_page' => 200]);

        $this->assertEquals(100, $result->perPage());
    }

    public function test_list_filters_by_user_id(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $domain = Domain::factory()->create();

        $this->service->create([
            'user_id' => $u1->id, 'domain_id' => $domain->id,
            'email' => 'u1@example.com', 'expiry_date' => '2027-01-01',
        ]);
        $this->service->create([
            'user_id' => $u2->id, 'domain_id' => $domain->id,
            'email' => 'u2@example.com', 'expiry_date' => '2027-01-01',
        ]);

        $result = $this->service->list(['user_id' => $u1->id]);

        $this->assertCount(1, $result->items());
        $this->assertEquals('u1@example.com', $result->items()[0]->email);
    }

    public function test_list_filters_by_accessible_module_ids(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $user->id]);
        $m1 = Module::factory()->create();
        $m2 = Module::factory()->create();

        $this->service->create([
            'user_id' => $user->id, 'domain_id' => $domain->id,
            'email' => 'a@example.com', 'expiry_date' => '2027-01-01', 'module_id' => $m1->id,
        ]);
        $this->service->create([
            'user_id' => $user->id, 'domain_id' => $domain->id,
            'email' => 'b@example.com', 'expiry_date' => '2027-01-01', 'module_id' => $m2->id,
        ]);

        $result = $this->service->list(['accessible_module_ids' => [$m1->id]]);

        $this->assertCount(1, $result->items());
    }
}
