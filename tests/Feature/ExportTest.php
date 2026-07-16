<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Module;
use App\Models\User;
use App\Models\UserModulePermission;
use App\Support\DataTypeConfig;
use Database\Seeders\FeatureModuleSeeder;
use Database\Seeders\RolePermissionSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
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
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);
    }

    public function test_export_domains_csv(): void
    {
        Domain::factory()->create(['name' => 'example.com', 'service_provider_id' => \App\Models\ServiceProvider::factory()]);

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

    public function test_export_forbidden_for_user_without_export_perm(): void
    {
        $role = \HasinHayder\Tyro\Models\Role::where('slug', 'user')->firstOrFail();
        $user = \App\Models\User::factory()->create();
        $user->assignRole($role);
        $user->load('roles');

        $this->actingAs($user)
            ->getJson('/api/export/domains')
            ->assertForbidden();
    }

    public function test_export_requires_auth(): void
    {
        $this->getJson('/api/export/domains')->assertUnauthorized();
    }

    public function test_non_admin_without_export_perm_is_forbidden(): void
    {
        $user = User::factory()->create();

        $role = Role::where('slug', 'user')->firstOrFail();
        $user->assignRole($role);
        $user->load('roles');

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/export/domains');

        $response->assertForbidden();
    }

    public function test_non_admin_with_export_perm_can_export(): void
    {
        $user = User::factory()->create();

        $role = Role::where('slug', 'customer')->firstOrFail();
        $user->assignRole($role);
        $user->load('roles');

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/export/domains');

        $response->assertOk();
    }

    /** @return array<string, array{0: string}> */
    public static function exportTypeProvider(): array
    {
        $types = array_keys(DataTypeConfig::exportTypes());
        return array_combine($types, array_map(fn ($t) => [$t], $types));
    }

    /** @dataProvider exportTypeProvider */
    public function test_each_export_type_returns_csv(string $type): void
    {
        $response = $this->actingAs($this->admin)
            ->get("/api/export/{$type}");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringStartsWith("attachment; filename=\"{$type}-", $response->headers->get('Content-Disposition'));
    }

    // ─── CSV Injection ────────────────────────────────────────────

    public function test_export_csv_injection_calculation(): void
    {
        Domain::factory()->create(['name' => '=1+1']);

        $response = $this->actingAs($this->admin)
            ->get('/api/export/domains');

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString("\t=1+1", $content);
    }

    public function test_export_csv_injection_command(): void
    {
        Domain::factory()->create(['name' => '@command']);

        $response = $this->actingAs($this->admin)
            ->get('/api/export/domains');

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString("\t@command", $content);
    }

    public function test_export_normal_text_not_modified(): void
    {
        Domain::factory()->create(['name' => 'NormalName']);

        $response = $this->actingAs($this->admin)
            ->get('/api/export/domains');

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('NormalName', $content);
        $this->assertStringNotContainsString("\tNormalName", $content);
    }
}
