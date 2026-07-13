<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\User;
use App\Models\VaultEntry;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VaultRouteConflictTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private ?VaultEntry $entry = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([TyroSeeder::class, FeatureModuleSeeder::class]);

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'vault-test@example.com',
        ]);
        $superRole = Role::where('slug', 'super-admin')->first();
        $this->superAdmin->roles()->attach($superRole);

        $vaultModule = Module::where('slug', 'vault')->orWhere('name', 'like', '%vault%')->first();
        if ($vaultModule) {
            $this->entry = VaultEntry::factory()->create([
                'module_id' => $vaultModule->id,
                'user_id' => $this->superAdmin->id,
            ]);
        }
    }

    public function test_my_vault_canonical_route_returns_200()
    {
        $response = $this->actingAs($this->superAdmin)->get('/my-vault');
        $response->assertStatus(200);
    }

    public function test_vault_slash_my_returns_404_not_crashes()
    {
        $response = $this->actingAs($this->superAdmin)->get('/vault/my');
        $response->assertStatus(404);
    }

    public function test_vault_with_numeric_id_returns_200()
    {
        if (!$this->entry) {
            $this->markTestSkipped('No vault module found for VaultEntry FK');
        }
        $response = $this->actingAs($this->superAdmin)->get("/vault/{$this->entry->id}");
        $response->assertStatus(200);
    }

    public function test_vault_create_still_works()
    {
        $response = $this->actingAs($this->superAdmin)->get('/vault/create');
        $response->assertStatus(200);
    }

    public function test_vault_index_still_works()
    {
        $response = $this->actingAs($this->superAdmin)->get('/vault');
        $response->assertStatus(200);
    }

    public function test_vault_slash_my_does_not_match_show_route()
    {
        $this->assertNotNull(Module::where('slug', 'vault')->first());
        $response = $this->actingAs($this->superAdmin)->get('/vault/my');
        $response->assertStatus(404);
        $response->assertSee('404');
    }
}
