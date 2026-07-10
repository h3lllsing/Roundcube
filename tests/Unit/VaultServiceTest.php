<?php

namespace Tests\Unit;

use App\Models\Module;
use App\Models\User;
use App\Models\VaultEntry;
use App\Services\VaultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VaultServiceTest extends TestCase
{
    use RefreshDatabase;

    private VaultService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(VaultService::class);
    }

    public function test_create_encrypts_password(): void
    {
        $user = User::factory()->create();

        $entry = $this->service->create([
            'user_id' => $user->id,
            'service_name' => 'Test Service',
            'service_url' => 'https://test.com',
            'username' => 'testuser',
            'password' => 'my-secret-password',
        ]);

        $this->assertInstanceOf(VaultEntry::class, $entry);
        $this->assertNotEquals('my-secret-password', $entry->getAttribute('encrypted_password'));
        $this->assertEquals('my-secret-password', $entry->decryptPassword());
    }

    public function test_reveal_returns_plaintext(): void
    {
        $user = User::factory()->create();

        $entry = $this->service->create([
            'user_id' => $user->id,
            'service_name' => 'Reveal Test',
            'password' => 'super-secret-123',
        ]);

        $plain = $this->service->reveal($entry);

        $this->assertEquals('super-secret-123', $plain);
    }

    public function test_reveal_logs_activity_with_causer(): void
    {
        $user = User::factory()->create();

        $entry = $this->service->create([
            'user_id' => $user->id,
            'service_name' => 'Logged Reveal',
            'password' => 'secret',
        ]);

        $plain = $this->service->reveal($entry, $user);

        $this->assertEquals('secret', $plain);

        $this->assertDatabaseHas('activity_log', [
            'event' => 'revealed',
            'causer_id' => $user->id,
        ]);
    }

    public function test_update_with_new_password(): void
    {
        $user = User::factory()->create();

        $entry = $this->service->create([
            'user_id' => $user->id,
            'service_name' => 'Update Test',
            'password' => 'old-password',
        ]);

        $updated = $this->service->update($entry, [
            'service_name' => 'Updated Service',
            'password' => 'new-password',
        ]);

        $this->assertEquals('Updated Service', $updated->service_name);
        $this->assertEquals('new-password', $updated->decryptPassword());
    }

    public function test_update_without_changing_password(): void
    {
        $user = User::factory()->create();

        $entry = $this->service->create([
            'user_id' => $user->id,
            'service_name' => 'No Pass Change',
            'password' => 'keep-password',
        ]);

        $updated = $this->service->update($entry, [
            'service_name' => 'Renamed',
        ]);

        $this->assertEquals('Renamed', $updated->service_name);
        $this->assertEquals('keep-password', $updated->decryptPassword());
    }

    public function test_delete_soft_deletes(): void
    {
        $user = User::factory()->create();
        $entry = $this->service->create([
            'user_id' => $user->id,
            'service_name' => 'To Delete',
            'password' => 'delete-me',
        ]);

        $this->service->delete($entry);

        $this->assertSoftDeleted($entry);
    }

    public function test_list_filters_by_user_id(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->service->create(['user_id' => $user1->id, 'service_name' => 'User1 Entry', 'password' => 'p']);
        $this->service->create(['user_id' => $user2->id, 'service_name' => 'User2 Entry', 'password' => 'p']);

        $result = $this->service->list(['user_id' => $user1->id]);

        $this->assertCount(1, $result->items());
        $this->assertEquals('User1 Entry', $result->items()[0]->service_name);
    }

    public function test_list_filters_by_module_id(): void
    {
        $user = User::factory()->create();
        $module1 = Module::factory()->create();
        $module2 = Module::factory()->create();
        $this->service->create(['user_id' => $user->id, 'service_name' => 'A', 'password' => 'p', 'module_id' => $module1->id]);
        $this->service->create(['user_id' => $user->id, 'service_name' => 'B', 'password' => 'p', 'module_id' => $module2->id]);

        $result = $this->service->list(['module_id' => $module1->id]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_searches_by_service_name(): void
    {
        $user = User::factory()->create();
        $this->service->create(['user_id' => $user->id, 'service_name' => 'GitHub', 'password' => 'p']);
        $this->service->create(['user_id' => $user->id, 'service_name' => 'GitLab', 'password' => 'p']);

        $result = $this->service->list(['search' => 'GitHub']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_accessible_module_ids(): void
    {
        $user = User::factory()->create();
        $module1 = Module::factory()->create();
        $module2 = Module::factory()->create();
        $this->service->create(['user_id' => $user->id, 'service_name' => 'A', 'password' => 'p', 'module_id' => $module1->id]);
        $this->service->create(['user_id' => $user->id, 'service_name' => 'B', 'password' => 'p', 'module_id' => $module2->id]);

        $result = $this->service->list(['accessible_module_ids' => [$module1->id]]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_with_trashed(): void
    {
        $user = User::factory()->create();
        $entry = $this->service->create(['user_id' => $user->id, 'service_name' => 'Deleted', 'password' => 'p']);
        $entry->delete();

        $result = $this->service->list(['with_trashed' => true]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_invalid_sort_falls_back_to_service_name(): void
    {
        $user = User::factory()->create();
        $this->service->create(['user_id' => $user->id, 'service_name' => 'Z', 'password' => 'p']);
        $this->service->create(['user_id' => $user->id, 'service_name' => 'A', 'password' => 'p']);

        $result = $this->service->list(['sort_by' => 'invalid']);

        $this->assertCount(2, $result->items());
        $this->assertEquals('A', $result->items()[0]->service_name);
    }

    public function test_list_clamps_per_page_to_max(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 110; $i++) {
            $this->service->create(['user_id' => $user->id, 'service_name' => "Entry $i", 'password' => 'p']);
        }

        $result = $this->service->list(['per_page' => 200]);

        $this->assertEquals(100, $result->perPage());
    }

    public function test_list_filters_by_accessible_module_ids_as_collection(): void
    {
        $user = User::factory()->create();
        $module1 = Module::factory()->create();
        $module2 = Module::factory()->create();
        $this->service->create(['user_id' => $user->id, 'service_name' => 'A', 'password' => 'p', 'module_id' => $module1->id]);
        $this->service->create(['user_id' => $user->id, 'service_name' => 'B', 'password' => 'p', 'module_id' => $module2->id]);

        $result = $this->service->list(['accessible_module_ids' => collect([$module1->id])]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_invalid_sort_order_falls_back_to_asc(): void
    {
        $user = User::factory()->create();
        $this->service->create(['user_id' => $user->id, 'service_name' => 'A', 'password' => 'p']);

        $result = $this->service->list(['sort_order' => 'invalid']);

        $this->assertCount(1, $result->items());
    }
}
