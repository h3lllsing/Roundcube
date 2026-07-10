<?php

namespace Tests\Unit;

use App\Models\Attachment;
use App\Models\Domain;
use App\Models\LoginAudit;
use App\Models\User;
use App\Models\VaultEntry;
use App\Services\BulkActionService;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkActionServiceTest extends TestCase
{
    use RefreshDatabase;

    private BulkActionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->service = app(BulkActionService::class);
    }

    public function test_invalid_type(): void
    {
        $user = User::factory()->create();
        $result = $this->service->execute('nonexistent', 'delete', [1], $user);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid type', $result['message']);
        $this->assertEquals(404, $result['status_code']);
    }

    public function test_invalid_action(): void
    {
        $user = User::factory()->create();
        $result = $this->service->execute('domains', 'nonsense', [1], $user);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid action.', $result['message']);
        $this->assertEquals(422, $result['status_code']);
    }

    public function test_update_status_invalid_status(): void
    {
        $user = User::factory()->create();
        $result = $this->service->execute('tasks', 'update-status', [1], $user, 'invalid_status');

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid status.', $result['message']);
        $this->assertEquals(422, $result['status_code']);
    }

    public function test_protected_roles_cannot_be_deleted(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $result = $this->service->execute('roles', 'delete', [$role->id], $admin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Protected roles cannot be deleted.', $result['message']);
        $this->assertEquals(422, $result['status_code']);
    }

    public function test_non_admin_cannot_bulk_users(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();
        $result = $this->service->execute('users', 'delete', [$target->id], $user);

        $this->assertFalse($result['success']);
        $this->assertEquals('Forbidden', $result['message']);
        $this->assertEquals(403, $result['status_code']);
    }

    public function test_non_admin_cannot_delete_others_records(): void
    {
        $otherUser = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $otherUser->id]);

        $nonAdmin = User::factory()->create();
        $result = $this->service->execute('domains', 'delete', [$domain->id], $nonAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Forbidden', $result['message']);
        $this->assertEquals(403, $result['status_code']);
    }

    public function test_non_admin_can_delete_own_records(): void
    {
        $nonAdmin = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $nonAdmin->id]);

        $result = $this->service->execute('domains', 'delete', [$domain->id], $nonAdmin);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['count']);
        $this->assertSoftDeleted($domain);
    }

    public function test_super_admin_can_delete_any(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $otherUser = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $otherUser->id]);

        $result = $this->service->execute('domains', 'delete', [$domain->id], $admin);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['count']);
        $this->assertSoftDeleted($domain);
    }

    public function test_restore_action(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $domain = Domain::factory()->create();
        $domain->delete();

        $result = $this->service->execute('domains', 'restore', [$domain->id], $admin);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['count']);
        $this->assertNotSoftDeleted($domain);
    }

    public function test_force_delete_action(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $domain = Domain::factory()->create();
        $domain->delete();

        $result = $this->service->execute('domains', 'force-delete', [$domain->id], $admin);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['count']);
        $this->assertModelMissing($domain);
    }

    public function test_update_status_action(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $domain = Domain::factory()->create(['status' => 'active']);

        $result = $this->service->execute('domains', 'update-status', [$domain->id], $admin, 'expired');

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['count']);
        $this->assertEquals('expired', $domain->fresh()->status);
    }

    public function test_suspend_unsuspend_users(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $target = User::factory()->create();

        $result = $this->service->execute('users', 'suspend', [$target->id], $admin);
        $this->assertTrue($result['success']);
        $this->assertNotNull($target->fresh()->suspended_at);

        $result = $this->service->execute('users', 'unsuspend', [$target->id], $admin);
        $this->assertTrue($result['success']);
        $this->assertNull($target->fresh()->suspended_at);
    }

    public function test_custom_type_tokens_delete(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        $tokenId = $token->accessToken->id;

        $result = $this->service->execute('tokens', 'delete', [$tokenId], $user);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['count']);
    }

    public function test_custom_type_tokens_cannot_delete_others(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherToken = $otherUser->createToken('other-token');
        $otherTokenId = $otherToken->accessToken->id;

        $result = $this->service->execute('tokens', 'delete', [$otherTokenId], $user);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['count']);
    }

    public function test_custom_type_login_audits_requires_super_admin(): void
    {
        $nonAdmin = User::factory()->create();
        $audit = LoginAudit::create(['user_id' => $nonAdmin->id, 'email' => 'test@test.com', 'event' => 'login']);

        $result = $this->service->execute('login-audits', 'delete', [$audit->id], $nonAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Forbidden', $result['message']);
        $this->assertEquals(403, $result['status_code']);
    }

    public function test_custom_type_login_audits_super_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $audit = LoginAudit::create(['user_id' => $admin->id, 'email' => 'test@test.com', 'event' => 'login']);

        $result = $this->service->execute('login-audits', 'delete', [$audit->id], $admin);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['count']);
    }

    public function test_custom_type_wrong_action(): void
    {
        $user = User::factory()->create();
        $result = $this->service->execute('tokens', 'restore', [1], $user);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid action.', $result['message']);
    }

    public function test_attachments_delete_removes_file(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $attachment = Attachment::factory()->create();

        $result = $this->service->execute('attachments', 'delete', [$attachment->id], $admin);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['count']);
        $this->assertSoftDeleted($attachment);
    }

    public function test_non_admin_owned_filter_with_created_by(): void
    {
        $nonAdmin = User::factory()->create();
        $task = Domain::factory()->create(['user_id' => $nonAdmin->id]);

        $result = $this->service->execute('domains', 'delete', [$task->id], $nonAdmin);

        $this->assertTrue($result['success']);
        $this->assertSoftDeleted($task);
    }

    public function test_empty_selection_returns_zero_count(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $result = $this->service->execute('domains', 'delete', [], $admin);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['count']);
    }

    public function test_non_admin_can_restore_own_soft_deleted_record(): void
    {
        $nonAdmin = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $nonAdmin->id]);
        $domain->delete();

        $result = $this->service->execute('domains', 'restore', [$domain->id], $nonAdmin);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['count']);
        $this->assertNotSoftDeleted($domain);
    }

    public function test_non_admin_cannot_restore_others_record(): void
    {
        $nonAdmin = User::factory()->create();
        $otherUser = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $otherUser->id]);
        $domain->delete();

        $result = $this->service->execute('domains', 'restore', [$domain->id], $nonAdmin);

        $this->assertFalse($result['success']);
        $this->assertEquals('Forbidden', $result['message']);
    }

    public function test_super_admin_can_force_delete_any(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $domain = Domain::factory()->create();
        $domain->delete();

        $result = $this->service->execute('domains', 'force-delete', [$domain->id], $admin);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['count']);
        $this->assertModelMissing($domain);
    }
}
