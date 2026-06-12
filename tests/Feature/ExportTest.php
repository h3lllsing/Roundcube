<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
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

    public function test_export_domains_csv(): void
    {
        Domain::factory()->create(['name' => 'example.com', 'registrar' => 'Namecheap']);

        $response = $this->actingAs($this->admin)
            ->get('/api/export/domains');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringStartsWith('attachment; filename="domains-', $response->headers->get('Content-Disposition'));
    }

    public function test_export_invalid_type(): void
    {
        $this->actingAs($this->admin)
            ->getJson('/api/export/invalid')
            ->assertNotFound()
            ->assertJsonPath('message', 'Invalid export type');
    }

    public function test_export_requires_auth(): void
    {
        $this->getJson('/api/export/domains')->assertUnauthorized();
    }

    public function test_non_admin_only_exports_own_records(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Domain::factory()->create(['name' => 'my-domain.com', 'user_id' => $user->id]);
        Domain::factory()->create(['name' => 'other-domain.com', 'user_id' => $otherUser->id]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/export/domains');

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('my-domain.com', $content);
        $this->assertStringNotContainsString('other-domain.com', $content);
    }
}
