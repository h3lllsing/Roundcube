<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::for('import', fn(Request $r) => Limit::none());
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);
    }

    public function test_import_domains_csv(): void
    {
        Storage::fake('local');

        $csv = "name,registrar,registration_date,expiry_date,auto_renew,cost,status,dns_servers,notes\n" .
            "example.com,Namecheap,2024-01-01,2025-01-01,1,15.00,active,ns1.example.com,test domain\n" .
            "test.org,GoDaddy,2024-06-01,2025-06-01,0,12.50,active,ns1.test.org,another domain";

        $file = UploadedFile::fake()->createWithContent('domains.csv', $csv);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/import/domains', [
                'file' => $file,
            ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Imported 2 record(s)')
            ->assertJsonPath('data.count', 2);

        $this->assertDatabaseHas('domains', ['name' => 'example.com', 'registrar' => 'Namecheap']);
        $this->assertDatabaseHas('domains', ['name' => 'test.org', 'registrar' => 'GoDaddy']);
    }

    public function test_import_invalid_type(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->createWithContent('invalid.csv', "name\nvalue");

        $this->actingAs($this->admin)
            ->postJson('/api/import/invalid', ['file' => $file])
            ->assertNotFound()
            ->assertJsonPath('message', 'Invalid import type');
    }

    public function test_import_without_file(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/import/domains', [])
            ->assertJsonValidationErrorFor('file');
    }

    public function test_import_empty_csv(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->createWithContent('empty.csv', "name,registrar\n");

        $this->actingAs($this->admin)
            ->postJson('/api/import/domains', ['file' => $file])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Import failed: CSV has no data rows');
    }

    public function test_import_requires_auth(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->createWithContent('domains.csv', "name\nvalue");

        $this->postJson('/api/import/domains', ['file' => $file])
            ->assertUnauthorized();
    }

    public function test_import_invalid_file_type(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('domains.pdf', 100);

        $this->actingAs($this->admin)
            ->postJson('/api/import/domains', ['file' => $file])
            ->assertJsonValidationErrorFor('file');
    }
}
