<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\Module;
use App\Models\User;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
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
        RateLimiter::for('import', fn (Request $r) => Limit::none());
        $this->seed(TyroSeeder::class);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);
    }

    public function test_import_domains_csv(): void
    {
        Storage::fake('local');

        $provider1 = \App\Models\ServiceProvider::factory()->create();
        $provider2 = \App\Models\ServiceProvider::factory()->create();

        $csv = "name,service_provider_id,registration_date,expiry_date,auto_renew,cost,status,dns_servers,notes\n".
            "example.com,{$provider1->id},2024-01-01,2025-01-01,1,15.00,active,ns1.example.com,test domain\n".
            "test.org,{$provider2->id},2024-06-01,2025-06-01,0,12.50,active,ns1.test.org,another domain";

        $file = UploadedFile::fake()->createWithContent('domains.csv', $csv);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/import/domains', [
                'file' => $file,
            ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Imported 2 record(s)')
            ->assertJsonPath('data.count', 2);

        $this->assertDatabaseHas('domains', ['name' => 'example.com', 'service_provider_id' => $provider1->id]);
        $this->assertDatabaseHas('domains', ['name' => 'test.org', 'service_provider_id' => $provider2->id]);
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

        $file = UploadedFile::fake()->createWithContent('empty.csv', "name,service_provider_id\n");

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

    public function test_import_skips_empty_rows(): void
    {
        Storage::fake('local');

        $provider1 = \App\Models\ServiceProvider::factory()->create();
        $provider2 = \App\Models\ServiceProvider::factory()->create();

        $csv = "name,service_provider_id,registration_date,expiry_date,status\n".
            "example.com,{$provider1->id},2024-01-01,2025-01-01,active\n".
            "\n".
            "test.org,{$provider2->id},2024-06-01,2025-06-01,active";

        $file = UploadedFile::fake()->createWithContent('domains.csv', $csv);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/import/domains', ['file' => $file]);

        $response->assertCreated()
            ->assertJsonPath('data.count', 2);
    }

    public function test_import_empty_file_returns_unprocessable(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->createWithContent('empty.csv', '');

        $this->actingAs($this->admin)
            ->postJson('/api/import/domains', ['file' => $file])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Import failed: empty or invalid CSV');
    }

    public function test_import_handles_db_exception(): void
    {
        Storage::fake('local');

        $provider1 = \App\Models\ServiceProvider::factory()->create();
        $provider2 = \App\Models\ServiceProvider::factory()->create();

        $csv = "service_provider_id,status\n{$provider1->id},active\n{$provider2->id},expired";

        $file = UploadedFile::fake()->createWithContent('domains.csv', $csv);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/import/domains', ['file' => $file]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Import failed. Check your CSV format and try again.');
    }

    // ─── New Import Types ───────────────────────────────────────────

    public function test_import_tasks_csv(): void
    {
        $module = Module::factory()->create();
        $csv = "title,description,status,priority,due_date,module_id\n".
            "ImportTask,desc,pending,high,2025-12-31,{$module->id}";
        $file = UploadedFile::fake()->createWithContent('tasks.csv', $csv);

        $this->actingAs($this->admin)
            ->postJson('/api/import/tasks', ['file' => $file])
            ->assertCreated()
            ->assertJsonPath('data.count', 1);

        $this->assertDatabaseHas('tasks', ['title' => 'ImportTask']);
    }

    public function test_import_notes_csv(): void
    {
        $csv = "content\nImported note content";
        $file = UploadedFile::fake()->createWithContent('notes.csv', $csv);

        $this->actingAs($this->admin)
            ->postJson('/api/import/notes', ['file' => $file])
            ->assertCreated()
            ->assertJsonPath('data.count', 1);

        $this->assertDatabaseHas('notes', ['content' => 'Imported note content']);
    }

    public function test_import_features_csv(): void
    {
        $csv = "name,slug,description\nImportFeature,import-feature,Test feature";
        $file = UploadedFile::fake()->createWithContent('features.csv', $csv);

        $this->actingAs($this->admin)
            ->postJson('/api/import/features', ['file' => $file])
            ->assertCreated()
            ->assertJsonPath('data.count', 1);

        $this->assertDatabaseHas('features', ['slug' => 'import-feature']);
    }

    public function test_import_modules_csv(): void
    {
        $feature = Feature::factory()->create();
        $csv = "name,slug,feature_id\nImportModule,import-module,{$feature->id}";
        $file = UploadedFile::fake()->createWithContent('modules.csv', $csv);

        $this->actingAs($this->admin)
            ->postJson('/api/import/modules', ['file' => $file])
            ->assertCreated()
            ->assertJsonPath('data.count', 1);

        $this->assertDatabaseHas('modules', ['slug' => 'import-module']);
    }

    public function test_import_vault_csv(): void
    {
        $csv = "service_name,service_url,username,encrypted_password\nImportVault,https://example.com,admin,supersecret";
        $file = UploadedFile::fake()->createWithContent('vault.csv', $csv);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/import/vault', ['file' => $file]);

        $response->assertCreated()
            ->assertJsonPath('data.count', 1);

        $this->assertDatabaseHas('password_vault', ['service_name' => 'ImportVault']);
    }

    public function test_import_webhooks_csv(): void
    {
        $csv = "name,url,is_active\nImportWebhook,https://hook.example.com,1";
        $file = UploadedFile::fake()->createWithContent('webhooks.csv', $csv);

        $this->actingAs($this->admin)
            ->postJson('/api/import/webhooks', ['file' => $file])
            ->assertCreated()
            ->assertJsonPath('data.count', 1);

        $this->assertDatabaseHas('webhooks', ['name' => 'ImportWebhook']);
    }

    public function test_import_users_csv(): void
    {
        $csv = "name,email,password\nImportUser,import@test.com,secret123";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csv);

        $this->actingAs($this->admin)
            ->postJson('/api/import/users', ['file' => $file])
            ->assertCreated()
            ->assertJsonPath('data.count', 1);

        $this->assertDatabaseHas('users', ['email' => 'import@test.com']);
    }

    public function test_import_roles_csv(): void
    {
        $csv = "name,slug\nImportRole,import-role";
        $file = UploadedFile::fake()->createWithContent('roles.csv', $csv);

        $this->actingAs($this->admin)
            ->postJson('/api/import/roles', ['file' => $file])
            ->assertCreated()
            ->assertJsonPath('data.count', 1);

        $this->assertDatabaseHas('roles', ['slug' => 'import-role']);
    }

    public function test_import_privileges_csv(): void
    {
        $csv = "name,slug,description\nImportPriv,import-priv,Test privilege";
        $file = UploadedFile::fake()->createWithContent('privileges.csv', $csv);

        $this->actingAs($this->admin)
            ->postJson('/api/import/privileges', ['file' => $file])
            ->assertCreated()
            ->assertJsonPath('data.count', 1);

        $this->assertDatabaseHas('privileges', ['slug' => 'import-priv']);
    }

    // ─── Web Import ─────────────────────────────────────────────

    public function test_web_import_page_loads(): void
    {
        $this->actingAs($this->admin)
            ->get(route('import.create'))
            ->assertStatus(200);
    }

    public function test_web_import_domains_csv(): void
    {
        $provider = \App\Models\ServiceProvider::factory()->create();
        $csv = "name,service_provider_id,status\nimport-web.test,{$provider->id},active";
        $file = UploadedFile::fake()->createWithContent('domains.csv', $csv);

        $this->actingAs($this->admin)
            ->post(route('import.store'), ['type' => 'domains', 'file' => $file])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('domains', ['name' => 'import-web.test']);
    }

    public function test_web_import_invalid_type(): void
    {
        $file = UploadedFile::fake()->createWithContent('test.csv', "name\nvalue");

        $this->actingAs($this->admin)
            ->post(route('import.store'), ['type' => 'invalid', 'file' => $file])
            ->assertSessionHas('error');
    }

    public function test_web_import_empty_csv(): void
    {
        $file = UploadedFile::fake()->createWithContent('empty.csv', "name,service_provider_id\n");

        $this->actingAs($this->admin)
            ->post(route('import.store'), ['type' => 'domains', 'file' => $file])
            ->assertSessionHas('error', 'Import failed: CSV has no data rows.');
    }

    public function test_web_import_no_headers_csv(): void
    {
        $file = UploadedFile::fake()->createWithContent('noheaders.csv', '');

        $this->actingAs($this->admin)
            ->post(route('import.store'), ['type' => 'domains', 'file' => $file])
            ->assertSessionHas('error', 'Import failed: empty or invalid CSV.');
    }

    public function test_web_import_users_csv(): void
    {
        $csv = "name,email,password\nWebImportUser,webimport@test.com,secret123";
        $file = UploadedFile::fake()->createWithContent('users.csv', $csv);

        $this->actingAs($this->admin)
            ->post(route('import.store'), ['type' => 'users', 'file' => $file])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['email' => 'webimport@test.com']);
    }

    public function test_web_import_type_without_user_id(): void
    {
        $csv = "name,slug\nImportRoleWeb,import-role-web";
        $file = UploadedFile::fake()->createWithContent('roles.csv', $csv);

        $this->actingAs($this->admin)
            ->post(route('import.store'), ['type' => 'roles', 'file' => $file])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['slug' => 'import-role-web']);
    }

    public function test_web_import_skips_empty_rows_inside_csv(): void
    {
        $provider1 = \App\Models\ServiceProvider::factory()->create();
        $provider2 = \App\Models\ServiceProvider::factory()->create();

        $csv = "name,service_provider_id,status\n".
            "first.com,{$provider1->id},active\n".
            "\n".
            "second.com,{$provider2->id},active";
        $file = UploadedFile::fake()->createWithContent('domains.csv', $csv);

        $this->actingAs($this->admin)
            ->post(route('import.store'), ['type' => 'domains', 'file' => $file])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('domains', ['name' => 'second.com']);
    }

    public function test_web_import_db_exception_caught(): void
    {
        $csv = "name,feature_id\nOrphanModule,99999";
        $file = UploadedFile::fake()->createWithContent('modules.csv', $csv);

        $this->actingAs($this->admin)
            ->post(route('import.store'), ['type' => 'modules', 'file' => $file])
            ->assertSessionHas('error', 'Import failed. Check your CSV format and try again.');
    }

    public function test_non_admin_cannot_import(): void
    {
        $user = User::factory()->create();
        $csv = "name\nTestDomain";
        $file = UploadedFile::fake()->createWithContent('domains.csv', $csv);

        $this->actingAs($user)
            ->postJson('/api/import/domains', ['file' => $file])
            ->assertForbidden();
    }

    // ─── CSV Injection ────────────────────────────────────────────

    public function test_import_sanitizes_calculation_injection(): void
    {
        $csv = "name\n=1+1";
        $file = UploadedFile::fake()->createWithContent('hostings.csv', $csv);

        $this->actingAs($this->admin)
            ->postJson('/api/import/hostings', ['file' => $file])
            ->assertCreated();

        $this->assertDatabaseHas('hostings', ['name' => '1+1']);
    }

    public function test_import_sanitizes_command_injection(): void
    {
        $csv = "name\n@cmd";
        $file = UploadedFile::fake()->createWithContent('hostings.csv', $csv);

        $this->actingAs($this->admin)
            ->postJson('/api/import/hostings', ['file' => $file])
            ->assertCreated();

        $this->assertDatabaseHas('hostings', ['name' => 'cmd']);
    }

    public function test_import_does_not_modify_normal_text(): void
    {
        $csv = "name\nNormalName";
        $file = UploadedFile::fake()->createWithContent('hostings.csv', $csv);

        $this->actingAs($this->admin)
            ->postJson('/api/import/hostings', ['file' => $file])
            ->assertCreated();

        $this->assertDatabaseHas('hostings', ['name' => 'NormalName']);
    }
}
