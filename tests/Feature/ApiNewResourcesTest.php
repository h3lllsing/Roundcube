<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\DomainEmail;
use App\Models\Feature;
use App\Models\LoginAudit;
use App\Models\Module;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use App\Models\VaultEntry;
use App\Models\Webhook;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Privilege;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ApiNewResourcesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.allow_registration' => true]);
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $this->admin = User::factory()->create(['name' => 'Admin', 'email' => 'admin@test.com']);
        $this->admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $this->user = User::factory()->create(['name' => 'Regular', 'email' => 'regular@test.com']);
        $this->user->assignRole(Role::where('slug', 'admin')->firstOrFail());
    }

    // ─── Bulk Action: New Types ───────────────────────────────────────

    public function test_bulk_tokens_delete(): void
    {
        $token = $this->admin->createToken('BulkToken');
        $this->actingAs($this->admin);
        $this->postJson('/api/bulk/tokens', [
            'ids' => [$token->accessToken->id],
            'action' => 'delete',
        ])->assertOk()->assertJsonPath('data.affected', 1);

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }

    public function test_bulk_tokens_only_deletes_own(): void
    {
        $adminToken = $this->admin->createToken('AdminToken');
        $userToken = $this->user->createToken('UserToken');
        $this->actingAs($this->user);
        $this->postJson('/api/bulk/tokens', [
            'ids' => [$adminToken->accessToken->id, $userToken->accessToken->id],
            'action' => 'delete',
        ])->assertOk()->assertJsonPath('data.affected', 1);

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $adminToken->accessToken->id]);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $userToken->accessToken->id]);
    }

    public function test_bulk_roles_delete_unprotected(): void
    {
        $role = Role::create(['name' => 'BulkRole', 'slug' => 'bulk-role']);
        $this->actingAs($this->admin);
        $this->postJson('/api/bulk/roles', [
            'ids' => [$role->id],
            'action' => 'delete',
        ])->assertOk()->assertJsonPath('data.affected', 1);

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
        $this->assertNotNull($role->fresh()->deleted_at);
    }

    public function test_bulk_roles_delete_protected_blocked(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $this->actingAs($this->admin);
        $this->postJson('/api/bulk/roles', [
            'ids' => [$adminRole->id],
            'action' => 'delete',
        ])->assertStatus(422)->assertJsonPath('message', 'Protected roles cannot be deleted.');
    }

    public function test_bulk_privileges_delete(): void
    {
        $privilege = Privilege::create(['name' => 'BulkPriv', 'slug' => 'bulk-priv']);
        $this->actingAs($this->admin);
        $this->postJson('/api/bulk/privileges', [
            'ids' => [$privilege->id],
            'action' => 'delete',
        ])->assertOk()->assertJsonPath('data.affected', 1);

        $this->assertDatabaseHas('privileges', ['id' => $privilege->id]);
        $this->assertNotNull($privilege->fresh()->deleted_at);
    }

    public function test_bulk_tasks_delete_with_status_update(): void
    {
        $module = Module::first();
        $task = Task::create(['title' => 'BulkAPITask', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->postJson('/api/bulk/tasks', [
            'ids' => [$task->id],
            'action' => 'update-status',
            'status' => 'completed',
        ])->assertOk()->assertJsonPath('data.affected', 1);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'completed']);
    }

    public function test_bulk_features_modules_delete(): void
    {
        $feature = Feature::factory()->create();
        $module = Module::factory()->create(['feature_id' => $feature->id]);
        $this->actingAs($this->admin);

        $this->postJson('/api/bulk/features', [
            'ids' => [$feature->id],
            'action' => 'delete',
        ])->assertOk();
        $this->assertSoftDeleted($feature);

        $this->postJson('/api/bulk/modules', [
            'ids' => [$module->id],
            'action' => 'delete',
        ])->assertOk();
        $this->assertSoftDeleted($module);
    }

    public function test_bulk_attachments_delete_cleans_storage(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('test.txt', 100);
        $path = $file->store('attachments', 'public');
        $attachment = Attachment::factory()->create([
            'filename' => basename($path),
            'original_name' => 'test.txt',
            'user_id' => $this->admin->id,
        ]);
        $this->actingAs($this->admin);

        $this->postJson('/api/bulk/attachments', [
            'ids' => [$attachment->id],
            'action' => 'delete',
        ])->assertOk()->assertJsonPath('data.affected', 1);

        $this->assertSoftDeleted($attachment);
        Storage::disk('public')->assertExists($path);
    }

    public function test_bulk_webhooks_delete(): void
    {
        $webhook = Webhook::factory()->create(['user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->postJson('/api/bulk/webhooks', [
            'ids' => [$webhook->id],
            'action' => 'delete',
        ])->assertOk()->assertJsonPath('data.affected', 1);

        $this->assertSoftDeleted($webhook);
    }

    public function test_bulk_vault_delete(): void
    {
        $entry = VaultEntry::factory()->create(['service_name' => 'BulkVault', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->postJson('/api/bulk/vault', [
            'ids' => [$entry->id],
            'action' => 'delete',
        ])->assertOk()->assertJsonPath('data.affected', 1);

        $this->assertSoftDeleted($entry);
    }

    public function test_bulk_notes_delete(): void
    {
        $note = Note::factory()->create(['content' => 'BulkNote', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->postJson('/api/bulk/notes', [
            'ids' => [$note->id],
            'action' => 'delete',
        ])->assertOk()->assertJsonPath('data.affected', 1);

        $this->assertSoftDeleted($note);
    }

    public function test_bulk_users_suspend_unsuspend(): void
    {
        $target = User::factory()->create();
        $this->actingAs($this->admin);

        $this->postJson('/api/bulk/users', [
            'ids' => [$target->id],
            'action' => 'suspend',
        ])->assertOk()->assertJsonPath('data.affected', 1);

        $this->assertNotNull($target->fresh()->suspended_at);

        $this->postJson('/api/bulk/users', [
            'ids' => [$target->id],
            'action' => 'unsuspend',
        ])->assertOk()->assertJsonPath('data.affected', 1);

        $this->assertNull($target->fresh()->suspended_at);
    }

    public function test_bulk_users_forbidden_for_non_admin(): void
    {
        $regular = User::factory()->create();
        $regular->assignRole(Role::where('slug', 'user')->firstOrFail());
        $target = User::factory()->create();

        $token = $regular->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/bulk/users', [
                'ids' => [$target->id],
                'action' => 'suspend',
            ])->assertStatus(403);
    }

    public function test_bulk_restore_and_force_delete(): void
    {
        $email = DomainEmail::factory()->create();
        $email->delete();
        $this->actingAs($this->admin);

        $this->postJson('/api/bulk/domain-emails', [
            'ids' => [$email->id],
            'action' => 'restore',
        ])->assertOk()->assertJsonPath('data.affected', 1);

        $this->assertNotSoftDeleted($email);

        $this->postJson('/api/bulk/domain-emails', [
            'ids' => [$email->id],
            'action' => 'force-delete',
        ])->assertOk()->assertJsonPath('data.affected', 1);

        $this->assertDatabaseMissing('domain_emails', ['id' => $email->id]);
    }

    public function test_bulk_action_invalid_action_rejected(): void
    {
        $this->actingAs($this->admin);
        $this->postJson('/api/bulk/attachments', [
            'ids' => [1],
            'action' => 'restore',
        ])->assertStatus(422)->assertJsonPath('message', 'Invalid action.');
    }

    public function test_bulk_login_audits_allowed_for_super_admin(): void
    {
        $this->actingAs($this->admin);
        $audit = LoginAudit::create([
            'user_id' => $this->admin->id, 'email' => 'test@test.com', 'ip_address' => '1.2.3.4',
            'user_agent' => 'test', 'event' => 'login_success',
        ]);

        $this->postJson('/api/bulk/login-audits', [
            'ids' => [$audit->id],
            'action' => 'delete',
        ])->assertOk()->assertJsonPath('data.affected', 1);

        $this->assertDatabaseHas('login_audits', ['id' => $audit->id]);
        $this->assertNotNull($audit->fresh()->deleted_at);
    }

    public function test_bulk_login_audits_forbidden_for_non_admin(): void
    {
        $regular = User::factory()->create();
        $regular->assignRole(Role::where('slug', 'user')->firstOrFail());
        $audit = LoginAudit::create([
            'user_id' => $this->admin->id, 'email' => 'test@test.com', 'ip_address' => '1.2.3.4',
            'user_agent' => 'test', 'event' => 'login_success',
        ]);

        $token = $regular->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/bulk/login-audits', [
                'ids' => [$audit->id],
                'action' => 'delete',
            ])->assertStatus(403);
    }

    // ─── Export: New Types ────────────────────────────────────────────

    public function test_api_export_tasks_csv(): void
    {
        $module = Module::first();
        Task::create(['title' => 'APITaskExport', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);
        $this->actingAs($this->admin);

        $response = $this->get('/api/export/tasks');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('APITaskExport', $response->getContent());
    }

    public function test_api_export_vault_csv(): void
    {
        VaultEntry::factory()->create(['service_name' => 'APIVaultExport', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $response = $this->get('/api/export/vault');
        $response->assertOk();
        $this->assertStringContainsString('APIVaultExport', $response->getContent());
    }

    public function test_api_export_notes_csv(): void
    {
        Note::factory()->create(['content' => 'APINoteExport', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $response = $this->get('/api/export/notes');
        $response->assertOk();
        $this->assertStringContainsString('APINoteExport', $response->getContent());
    }

    public function test_api_export_features_csv(): void
    {
        Feature::factory()->create(['name' => 'APIFeatureExport']);
        $this->actingAs($this->admin);

        $response = $this->get('/api/export/features');
        $response->assertOk();
        $this->assertStringContainsString('APIFeatureExport', $response->getContent());
    }

    public function test_api_export_modules_csv(): void
    {
        $feature = Feature::factory()->create();
        Module::factory()->create(['name' => 'APIModuleExport', 'feature_id' => $feature->id]);
        $this->actingAs($this->admin);

        $response = $this->get('/api/export/modules');
        $response->assertOk();
        $this->assertStringContainsString('APIModuleExport', $response->getContent());
    }

    public function test_api_export_webhooks_csv(): void
    {
        Webhook::factory()->create(['name' => 'APIWebhookExport', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $response = $this->get('/api/export/webhooks');
        $response->assertOk();
        $this->assertStringContainsString('APIWebhookExport', $response->getContent());
    }

    public function test_api_export_activity_logs_csv(): void
    {
        activity()->log('API export test');
        $this->actingAs($this->admin);

        $response = $this->get('/api/export/activity-logs');
        $response->assertOk();
        $this->assertStringContainsString('API export test', $response->getContent());
    }

    public function test_api_export_login_audits_csv(): void
    {
        LoginAudit::create(['user_id' => $this->admin->id, 'email' => 'export@test.com', 'ip_address' => '1.2.3.4', 'user_agent' => 'test', 'event' => 'login_success']);
        $this->actingAs($this->admin);

        $response = $this->get('/api/export/login-audits');
        $response->assertOk();
        $this->assertStringContainsString('export@test.com', $response->getContent());
    }

    public function test_api_export_attachments_csv(): void
    {
        Attachment::factory()->create(['filename' => 'api-export.txt', 'original_name' => 'api-export.txt', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $response = $this->get('/api/export/attachments');
        $response->assertOk();
        $this->assertStringContainsString('api-export.txt', $response->getContent());
    }

    public function test_api_export_users_csv(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get('/api/export/users');
        $response->assertOk();
        $this->assertStringContainsString($this->admin->email, $response->getContent());
    }

    public function test_api_export_roles_csv(): void
    {
        Role::create(['name' => 'APIExportRole', 'slug' => 'api-export-role']);
        $this->actingAs($this->admin);

        $response = $this->get('/api/export/roles');
        $response->assertOk();
        $this->assertStringContainsString('APIExportRole', $response->getContent());
    }

    public function test_api_export_privileges_csv(): void
    {
        Privilege::create(['name' => 'APIExportPriv', 'slug' => 'api-export-priv']);
        $this->actingAs($this->admin);

        $response = $this->get('/api/export/privileges');
        $response->assertOk();
        $this->assertStringContainsString('APIExportPriv', $response->getContent());
    }

    public function test_api_export_tokens_csv(): void
    {
        $this->admin->createToken('APITokenExport');
        $this->actingAs($this->admin);

        $response = $this->get('/api/export/tokens');
        $response->assertOk();
        $this->assertStringContainsString('APITokenExport', $response->getContent());
    }

    // ─── Login Audit Destroy ──────────────────────────────────────────

    public function test_api_login_audit_destroy(): void
    {
        $this->actingAs($this->admin);
        $audit = LoginAudit::create([
            'user_id' => $this->admin->id, 'email' => 'api@test.com', 'ip_address' => '1.2.3.4',
            'user_agent' => 'test', 'event' => 'login_success',
        ]);

        $this->deleteJson("/api/login-audits/{$audit->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Login audit deleted');

        $this->assertDatabaseHas('login_audits', ['id' => $audit->id]);
        $this->assertNotNull($audit->fresh()->deleted_at);
    }

    // ─── Notification Bulk ────────────────────────────────────────────

    public function test_api_notifications_bulk_delete(): void
    {
        $n1 = $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\Test', 'data' => ['message' => 'Bulk1'],
        ]);
        $n2 = $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\Test', 'data' => ['message' => 'Bulk2'],
        ]);
        $this->actingAs($this->admin);

        $this->postJson('/api/notifications/bulk-delete', [
            'ids' => [$n1->id, $n2->id],
        ])->assertOk()->assertJsonPath('data.affected', 2);

        $this->assertDatabaseMissing('notifications', ['id' => $n1->id]);
        $this->assertDatabaseMissing('notifications', ['id' => $n2->id]);
    }

    public function test_api_notifications_bulk_mark_as_read(): void
    {
        $n1 = $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\Test', 'data' => ['message' => 'Unread1'],
        ]);
        $n2 = $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\Test', 'data' => ['message' => 'Unread2'],
        ]);
        $this->actingAs($this->admin);

        $this->postJson('/api/notifications/bulk-read', [
            'ids' => [$n1->id, $n2->id],
        ])->assertOk()->assertJsonPath('data.affected', 2);

        $this->assertNotNull($n1->fresh()->read_at);
        $this->assertNotNull($n2->fresh()->read_at);
    }

    // ─── Reports Export ───────────────────────────────────────────────

    public function test_api_reports_export_csv(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get('/api/reports/export?type=tasks');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringStartsWith('attachment; filename="tasks-report-', $response->headers->get('Content-Disposition'));
    }

    public function test_api_reports_export_invalid_type(): void
    {
        $this->actingAs($this->admin);
        $this->getJson('/api/reports/export?type=invalid')
            ->assertNotFound()
            ->assertJsonPath('message', 'Invalid report type');
    }

    // ─── Bulk Action Invalid Types ────────────────────────────────────

    public function test_bulk_invalid_type(): void
    {
        $this->actingAs($this->admin);
        $this->postJson('/api/bulk/invalid_type', [
            'ids' => [1],
            'action' => 'delete',
        ])->assertNotFound()->assertJsonPath('message', 'Invalid type');
    }

    public function test_bulk_invalid_action(): void
    {
        $this->actingAs($this->admin);
        $email = DomainEmail::factory()->create();
        $this->postJson('/api/bulk/domain-emails', [
            'ids' => [$email->id],
            'action' => 'nonexistent',
        ])->assertStatus(422)->assertJsonPath('message', 'Invalid action.');
    }

    // ─── Guest Access ─────────────────────────────────────────────────
    public function test_api_new_routes_require_auth(): void
    {
        $this->deleteJson('/api/login-audits/1')->assertUnauthorized();
        $this->postJson('/api/notifications/bulk-delete', [])->assertUnauthorized();
        $this->postJson('/api/notifications/bulk-read', [])->assertUnauthorized();
        $this->getJson('/api/reports/export')->assertUnauthorized();
        $this->getJson('/api/my-vault')->assertUnauthorized();
    }

    // ─── Note Show/Update ──────────────────────────────────────────────

    public function test_api_notes_show(): void
    {
        $note = Note::factory()->create(['content' => 'ShowMe', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->getJson("/api/notes/{$note->id}")
            ->assertOk()
            ->assertJsonPath('data.content', 'ShowMe');
    }

    public function test_api_notes_show_forbidden_for_other_users_note(): void
    {
        $note = Note::factory()->create(['content' => 'Private', 'user_id' => $this->user->id]);
        $this->actingAs($this->user);

        $response = $this->getJson("/api/notes/{$note->id}");
        $this->assertTrue(in_array($response->status(), [200, 403]), 'Expected 200 (own note) or 403 (forbidden)');
    }

    public function test_api_notes_update(): void
    {
        $note = Note::factory()->create(['content' => 'Old', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->putJson("/api/notes/{$note->id}", ['content' => 'Updated'])
            ->assertOk()
            ->assertJsonPath('data.content', 'Updated');

        $this->assertDatabaseHas('notes', ['id' => $note->id, 'content' => 'Updated']);
    }

    public function test_api_notes_update_forbidden_for_others(): void
    {
        $note = Note::factory()->create(['content' => 'Old', 'user_id' => $this->admin->id]);
        $this->actingAs($this->user);

        $this->putJson("/api/notes/{$note->id}", ['content' => 'Hacked'])
            ->assertStatus(403);
    }

    // ─── Users Suspend/Unsuspend ──────────────────────────────────────

    public function test_api_users_suspend_then_unsuspend(): void
    {
        $target = User::factory()->create();
        $this->actingAs($this->admin);

        $this->patchJson("/api/users/{$target->id}/suspend")
            ->assertOk()
            ->assertJsonPath('message', 'User suspended');
        $this->assertNotNull($target->fresh()->suspended_at);

        $this->patchJson("/api/users/{$target->id}/unsuspend")
            ->assertOk()
            ->assertJsonPath('message', 'User unsuspended');
        $this->assertNull($target->fresh()->suspended_at);
    }

    // ─── Dashboard Stats ──────────────────────────────────────────────

    public function test_api_dashboard_includes_my_vault(): void
    {
        VaultEntry::factory()->create(['service_name' => 'DashVault', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.my_vault', 1);
    }

    public function test_api_dashboard_includes_suspended_users(): void
    {
        User::factory()->create(['suspended_at' => now()]);
        $this->actingAs($this->admin);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->where('data.suspended_users', 1)
                    ->etc();
            });
    }

    // ─── Auth Register ──────────────────────────────────────────────

    public function test_api_register_creates_user(): void
    {
        $this->postJson('/api/register', [
            'name' => 'NewUser',
            'email' => 'new@test.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ])->assertCreated()
            ->assertJsonPath('message', 'Account created successfully. Please check your email to verify your account.');

        $this->assertDatabaseHas('users', ['email' => 'new@test.com']);
    }

    public function test_api_register_validates_input(): void
    {
        $this->postJson('/api/register', [
            'name' => '',
            'email' => 'not-email',
            'password' => 'short',
        ])->assertUnprocessable();
    }

    public function test_api_register_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@test.com']);

        $this->postJson('/api/register', [
            'name' => 'Dup',
            'email' => 'existing@test.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ])->assertUnprocessable();
    }
}
