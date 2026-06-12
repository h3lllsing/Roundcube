<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkActionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);
    }

    public function test_bulk_update_status(): void
    {
        $d1 = Domain::factory()->create(['status' => 'active']);
        $d2 = Domain::factory()->create(['status' => 'active']);

        $this->actingAs($this->admin)
            ->postJson('/api/bulk/domains', [
                'ids' => [$d1->id, $d2->id],
                'action' => 'update-status',
                'status' => 'expired',
            ])
            ->assertOk()
            ->assertJsonPath('data.affected', 2);

        $this->assertEquals('expired', $d1->fresh()->status);
        $this->assertEquals('expired', $d2->fresh()->status);
    }

    public function test_bulk_delete(): void
    {
        $d1 = Domain::factory()->create();
        $d2 = Domain::factory()->create();

        $this->actingAs($this->admin)
            ->postJson('/api/bulk/domains', [
                'ids' => [$d1->id, $d2->id],
                'action' => 'delete',
            ])
            ->assertOk()
            ->assertJsonPath('data.affected', 2);

        $this->assertSoftDeleted($d1);
        $this->assertSoftDeleted($d2);
    }

    public function test_bulk_invalid_type(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/bulk/invalid', ['ids' => [1], 'action' => 'delete'])
            ->assertNotFound()
            ->assertJsonPath('message', 'Invalid type');
    }

    public function test_bulk_validation(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/bulk/domains', ['ids' => [], 'action' => 'delete'])
            ->assertJsonValidationErrorFor('ids');
    }

    public function test_bulk_requires_auth(): void
    {
        $this->postJson('/api/bulk/domains', ['ids' => [1], 'action' => 'delete'])->assertUnauthorized();
    }

    public function test_non_admin_cannot_bulk_delete_others_records(): void
    {
        $otherUser = User::factory()->create();
        $othersDomain = Domain::factory()->create(['user_id' => $otherUser->id]);
        $nonAdmin = User::factory()->create();
        $ownDomain = Domain::factory()->create(['user_id' => $nonAdmin->id]);
        $token = $nonAdmin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/bulk/domains', [
                'ids' => [$othersDomain->id, $ownDomain->id],
                'action' => 'delete',
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.affected', 1);

        $this->assertNotSoftDeleted($othersDomain);
        $this->assertSoftDeleted($ownDomain);
    }

    public function test_non_admin_cannot_bulk_update_others_records(): void
    {
        $otherUser = User::factory()->create();
        $othersDomain = Domain::factory()->create(['status' => 'active', 'user_id' => $otherUser->id]);

        $nonAdmin = User::factory()->create();
        $token = $nonAdmin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/bulk/domains', [
                'ids' => [$othersDomain->id],
                'action' => 'update-status',
                'status' => 'expired',
            ]);

        $response->assertStatus(403);
        $this->assertEquals('active', $othersDomain->fresh()->status);
    }

    public function test_non_admin_gets_403_if_no_owned_ids(): void
    {
        $otherUser = User::factory()->create();
        $d1 = Domain::factory()->create(['user_id' => $otherUser->id]);
        $d2 = Domain::factory()->create(['user_id' => $otherUser->id]);

        $nonAdmin = User::factory()->create();
        $token = $nonAdmin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/bulk/domains', [
                'ids' => [$d1->id, $d2->id],
                'action' => 'delete',
            ]);

        $response->assertStatus(403);
    }
}
