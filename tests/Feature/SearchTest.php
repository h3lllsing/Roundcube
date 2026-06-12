<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Note;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->user = User::factory()->create();
    }

    public function test_super_admin_sees_all_records(): void
    {
        Domain::factory()->create(['name' => 'alpha-example.com', 'user_id' => $this->admin->id]);
        Domain::factory()->create(['name' => 'alpha-other.com', 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=alpha');

        $response->assertOk();
        $this->assertArrayHasKey('domains', $response->json('data'));
        $this->assertCount(2, $response->json('data.domains.items'));
    }

    public function test_non_admin_only_sees_own_records(): void
    {
        Domain::factory()->create(['name' => 'beta-mine.com', 'user_id' => $this->user->id]);
        Domain::factory()->create(['name' => 'beta-theirs.com', 'user_id' => $this->admin->id]);

        $token = $this->user->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/search?q=beta');

        $response->assertOk();
        $this->assertArrayHasKey('domains', $response->json('data'));
        $this->assertCount(1, $response->json('data.domains.items'));
        $this->assertEquals('beta-mine.com', $response->json('data.domains.items.0.title'));
    }

    public function test_notes_scoped_to_own_records_for_non_admin(): void
    {
        Note::create(['content' => 'gamma secret note', 'user_id' => $this->user->id]);
        Note::create(['content' => 'gamma admin note', 'user_id' => $this->admin->id]);

        $token = $this->user->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/search?q=gamma');

        $response->assertOk();
        $this->assertArrayHasKey('notes', $response->json('data'));
        $this->assertCount(1, $response->json('data.notes.items'));
        $this->assertEquals('gamma secret note', $response->json('data.notes.items.0.title'));
    }

    public function test_requires_minimum_two_characters(): void
    {
        $this->actingAs($this->admin)
            ->getJson('/api/search?q=a')
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/search?q=test')->assertUnauthorized();
    }

    public function test_limit_parameter_respected(): void
    {
        Domain::factory()->create(['name' => 'eta-one', 'user_id' => $this->user->id]);
        Domain::factory()->create(['name' => 'eta-two', 'user_id' => $this->user->id]);
        Domain::factory()->create(['name' => 'eta-three', 'user_id' => $this->user->id]);

        $token = $this->user->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/search?q=eta&limit=2');

        $response->assertOk();
        $this->assertArrayHasKey('domains', $response->json('data'));
        $this->assertCount(2, $response->json('data.domains.items'));
    }

    public function test_super_admin_sees_all_users_notes(): void
    {
        Note::create(['content' => 'theta-secret', 'user_id' => $this->user->id]);
        Note::create(['content' => 'theta-admin', 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=theta');

        $response->assertOk();
        $this->assertCount(2, $response->json('data.notes.items'));
    }
}
