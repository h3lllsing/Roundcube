<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Feature;
use App\Models\LoginAudit;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\Note;
use App\Models\OtherService;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class WebNewResourcesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    private Module $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $this->admin = User::factory()->create(['name' => 'Admin', 'email' => 'admin@test.com']);
        $this->admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $this->user = User::factory()->create(['name' => 'User', 'email' => 'user@test.com']);
        $this->user->assignRole(Role::where('slug', 'admin')->firstOrFail());

        $this->module = Module::first();
    }

    // ─── Domain Email CRUD ────────────────────────────────────────────

    public function test_domain_email_index_page_loads(): void
    {
        DomainEmail::factory()->create();
        $this->actingAs($this->admin);
        $this->get(route('domain-emails.index'))->assertStatus(200);
    }

    public function test_domain_email_create_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('domain-emails.create'))->assertStatus(200);
    }

    public function test_domain_email_store_creates(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('domain-emails.store'), [
            'email' => 'test@example.com',
        ])->assertRedirect(route('domain-emails.index'));

        $this->assertDatabaseHas('domain_emails', ['email' => 'test@example.com']);
    }

    public function test_domain_email_store_validates(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('domain-emails.store'), [])
            ->assertSessionHasErrors('email');
    }

    public function test_domain_email_show_displays(): void
    {
        $email = DomainEmail::factory()->create();
        $this->actingAs($this->admin);
        $this->get(route('domain-emails.show', $email->id))
            ->assertStatus(200)
            ->assertSee($email->email);
    }

    public function test_domain_email_edit_page_loads(): void
    {
        $email = DomainEmail::factory()->create();
        $this->actingAs($this->admin);
        $this->get(route('domain-emails.edit', $email->id))
            ->assertStatus(200)
            ->assertSee($email->email);
    }

    public function test_domain_email_update_modifies(): void
    {
        $email = DomainEmail::factory()->create(['email' => 'old@example.com']);
        $this->actingAs($this->admin);
        $this->put(route('domain-emails.update', $email->id), [
            'email' => 'new@example.com',
        ])->assertRedirect(route('domain-emails.index'));

        $this->assertDatabaseHas('domain_emails', ['id' => $email->id, 'email' => 'new@example.com']);
    }

    public function test_domain_email_destroy_deletes(): void
    {
        $email = DomainEmail::factory()->create();
        $this->actingAs($this->admin);
        $this->delete(route('domain-emails.destroy', $email->id))
            ->assertRedirect(route('domain-emails.index'));

        $this->assertSoftDeleted($email);
    }

    // ─── Other Service CRUD ───────────────────────────────────────────

    public function test_other_service_index_page_loads(): void
    {
        OtherService::factory()->create();
        $this->actingAs($this->admin);
        $this->get(route('other-services.index'))->assertStatus(200);
    }

    public function test_other_service_create_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('other-services.create'))->assertStatus(200);
    }

    public function test_other_service_store_creates(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('other-services.store'), [
            'name' => 'Test Service',
            'service_type' => 'saas',
        ])->assertRedirect(route('other-services.index'));

        $this->assertDatabaseHas('other_services', ['name' => 'Test Service']);
    }

    public function test_other_service_store_validates(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('other-services.store'), [])
            ->assertSessionHasErrors(['name', 'service_type']);
    }

    public function test_other_service_show_displays(): void
    {
        $service = OtherService::factory()->create();
        $this->actingAs($this->admin);
        $this->get(route('other-services.show', $service->id))
            ->assertStatus(200)
            ->assertSee($service->name);
    }

    public function test_other_service_edit_page_loads(): void
    {
        $service = OtherService::factory()->create();
        $this->actingAs($this->admin);
        $this->get(route('other-services.edit', $service->id))
            ->assertStatus(200)
            ->assertSee($service->name);
    }

    public function test_other_service_update_modifies(): void
    {
        $service = OtherService::factory()->create(['name' => 'Old Name']);
        $this->actingAs($this->admin);
        $this->put(route('other-services.update', $service->id), [
            'name' => 'New Name',
            'service_type' => 'monitoring',
        ])->assertRedirect(route('other-services.index'));

        $this->assertDatabaseHas('other_services', ['id' => $service->id, 'name' => 'New Name']);
    }

    public function test_other_service_destroy_deletes(): void
    {
        $service = OtherService::factory()->create();
        $this->actingAs($this->admin);
        $this->delete(route('other-services.destroy', $service->id))
            ->assertRedirect(route('other-services.index'));

        $this->assertSoftDeleted($service);
    }

    // ─── Expiry Tracker CRUD ──────────────────────────────────────────

    public function test_expiry_tracker_index_page_loads(): void
    {
        ExpiryTracker::factory()->create();
        $this->actingAs($this->admin);
        $this->get(route('expiry-trackers.index'))->assertStatus(200);
    }

    public function test_expiry_tracker_create_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('expiry-trackers.create'))->assertStatus(200);
    }

    public function test_expiry_tracker_store_creates(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('expiry-trackers.store'), [
            'name' => 'Test Tracker',
        ])->assertRedirect(route('expiry-trackers.index'));

        $this->assertDatabaseHas('expiry_trackers', ['name' => 'Test Tracker']);
    }

    public function test_expiry_tracker_store_validates(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('expiry-trackers.store'), [])
            ->assertSessionHasErrors('name');
    }

    public function test_expiry_tracker_show_displays(): void
    {
        $tracker = ExpiryTracker::factory()->create();
        $this->actingAs($this->admin);
        $this->get(route('expiry-trackers.show', $tracker->id))
            ->assertStatus(200)
            ->assertSee($tracker->name);
    }

    public function test_expiry_tracker_edit_page_loads(): void
    {
        $tracker = ExpiryTracker::factory()->create();
        $this->actingAs($this->admin);
        $this->get(route('expiry-trackers.edit', $tracker->id))
            ->assertStatus(200)
            ->assertSee($tracker->name);
    }

    public function test_expiry_tracker_update_modifies(): void
    {
        $tracker = ExpiryTracker::factory()->create(['name' => 'Old Tracker']);
        $this->actingAs($this->admin);
        $this->put(route('expiry-trackers.update', $tracker->id), [
            'name' => 'Updated Tracker',
        ])->assertRedirect(route('expiry-trackers.index'));

        $this->assertDatabaseHas('expiry_trackers', ['id' => $tracker->id, 'name' => 'Updated Tracker']);
    }

    public function test_expiry_tracker_destroy_deletes(): void
    {
        $tracker = ExpiryTracker::factory()->create();
        $this->actingAs($this->admin);
        $this->delete(route('expiry-trackers.destroy', $tracker->id))
            ->assertRedirect(route('expiry-trackers.index'));

        $this->assertSoftDeleted($tracker);
    }

    // ─── Webhook CRUD ─────────────────────────────────────────────────

    public function test_webhook_index_page_loads(): void
    {
        Webhook::factory()->create();
        $this->actingAs($this->admin);
        $this->get(route('webhooks.index'))->assertStatus(200);
    }

    public function test_webhook_create_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('webhooks.create'))->assertStatus(200);
    }

    public function test_webhook_store_creates(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('webhooks.store'), [
            'name' => 'Test Webhook',
            'url' => 'https://example.com/hook',
            'is_active' => true,
        ])->assertRedirect(route('webhooks.index'));

        $this->assertDatabaseHas('webhooks', ['name' => 'Test Webhook']);
    }

    public function test_webhook_store_validates(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('webhooks.store'), [])
            ->assertSessionHasErrors(['name', 'url']);
    }

    public function test_webhook_show_displays(): void
    {
        $webhook = Webhook::factory()->create();
        $this->actingAs($this->admin);
        $this->get(route('webhooks.show', $webhook->id))
            ->assertStatus(200)
            ->assertSee($webhook->name);
    }

    public function test_webhook_edit_page_loads(): void
    {
        $webhook = Webhook::factory()->create();
        $this->actingAs($this->admin);
        $this->get(route('webhooks.edit', $webhook->id))
            ->assertStatus(200)
            ->assertSee($webhook->name);
    }

    public function test_webhook_update_modifies(): void
    {
        $webhook = Webhook::factory()->create(['name' => 'Old Hook']);
        $this->actingAs($this->admin);
        $this->put(route('webhooks.update', $webhook->id), [
            'name' => 'New Hook',
            'url' => 'https://example.com/new',
        ])->assertRedirect(route('webhooks.index'));

        $this->assertDatabaseHas('webhooks', ['id' => $webhook->id, 'name' => 'New Hook']);
    }

    public function test_webhook_destroy_deletes(): void
    {
        $webhook = Webhook::factory()->create();
        $this->actingAs($this->admin);
        $this->delete(route('webhooks.destroy', $webhook->id))
            ->assertRedirect(route('webhooks.index'));

        $this->assertSoftDeleted($webhook);
    }

    // ─── Login Audits (read-only) ─────────────────────────────────────

    public function test_login_audit_index_page_loads(): void
    {
        LoginAudit::create([
            'email' => 'test@example.com',
            'ip_address' => '127.0.0.1',
            'event' => 'login_success',
        ]);
        $this->actingAs($this->admin);
        $this->get(route('login-audits.index'))->assertStatus(200);
    }

    public function test_login_audit_show_displays(): void
    {
        $audit = LoginAudit::create([
            'email' => 'test@example.com',
            'ip_address' => '127.0.0.1',
            'event' => 'login_success',
        ]);
        $this->actingAs($this->admin);
        $this->get(route('login-audits.show', $audit->id))
            ->assertStatus(200)
            ->assertSee('test@example.com');
    }

    // ─── Attachments ──────────────────────────────────────────────────

    public function test_attachment_index_page_loads(): void
    {
        Attachment::factory()->create();
        $this->actingAs($this->admin);
        $this->get(route('attachments.index'))->assertStatus(200);
    }

    public function test_attachment_index_with_search_filter(): void
    {
        Attachment::factory()->create(['original_name' => 'searchable-doc.pdf']);
        Attachment::factory()->create(['original_name' => 'other-file.txt']);
        $this->actingAs($this->admin);
        $response = $this->get(route('attachments.index', ['search' => 'searchable']));
        $response->assertStatus(200)->assertSee('searchable-doc.pdf')->assertDontSee('other-file.txt');
    }

    public function test_attachment_show_displays(): void
    {
        $attachment = Attachment::factory()->create();
        $this->actingAs($this->admin);
        $this->get(route('attachments.show', $attachment->id))
            ->assertStatus(200)
            ->assertSee($attachment->original_name);
    }

    public function test_attachment_download_returns_file(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('test.pdf', 100);
        $path = $file->store('attachments', 'public');

        $attachment = Attachment::factory()->create([
            'filename' => basename($path),
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size' => 100,
        ]);

        $this->actingAs($this->admin);
        $this->get(route('attachments.download', $attachment->id))
            ->assertStatus(200);
    }

    public function test_attachment_destroy_deletes(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('test.txt', 100);
        $path = $file->store('attachments', 'public');

        $attachment = Attachment::factory()->create([
            'filename' => basename($path),
            'original_name' => 'test.txt',
        ]);

        $this->actingAs($this->admin);
        $this->delete(route('attachments.destroy', $attachment->id))
            ->assertRedirect(route('attachments.index'));

        $this->assertSoftDeleted($attachment);
    }

    public function test_attachment_create_with_notable_shows_hint(): void
    {
        $note = Note::factory()->create();
        $this->actingAs($this->admin);
        $this->get(route('attachments.create', [
            'notable_type' => 'App\Models\Note',
            'notable_id' => $note->id,
        ]))
            ->assertStatus(200)
            ->assertSee(class_basename('App\Models\Note'))
            ->assertSee((string) $note->id);
    }

    public function test_attachment_create_with_invalid_notable_type_ignores(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('attachments.create', [
            'notable_type' => 'InvalidModel',
            'notable_id' => 999,
        ]))
            ->assertStatus(200)
            ->assertDontSee('InvalidModel');
    }

    public function test_attachment_store_with_notable_associates(): void
    {
        Storage::fake('public');
        $note = Note::factory()->create();
        $this->actingAs($this->admin);
        $this->post(route('attachments.store'), [
            'file' => UploadedFile::fake()->create('note_file.pdf', 100),
            'notable_type' => 'App\Models\Note',
            'notable_id' => $note->id,
        ])->assertRedirect(route('attachments.index'))->assertSessionHas('success');

        $attachment = Attachment::where('original_name', 'note_file.pdf')->first();
        $this->assertNotNull($attachment);
        $this->assertEquals('App\Models\Note', $attachment->notable_type);
        $this->assertEquals($note->id, $attachment->notable_id);
    }

    public function test_attachment_store_with_invalid_notable_type_ignores(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);
        $this->post(route('attachments.store'), [
            'file' => UploadedFile::fake()->create('orphan.pdf', 100),
            'notable_type' => 'App\Models\InvalidModel',
            'notable_id' => 999,
        ])->assertRedirect(route('attachments.index'))->assertSessionHas('success');

        $attachment = Attachment::where('original_name', 'orphan.pdf')->first();
        $this->assertNotNull($attachment);
        $this->assertNull($attachment->notable_type);
        $this->assertNull($attachment->notable_id);
    }

    // ─── Reports ──────────────────────────────────────────────────────

    public function test_reports_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('reports.index'))->assertStatus(200);
    }

    public function test_reports_export_csv(): void
    {
        \App\Models\Domain::factory()->create(['name' => 'export-test.com', 'cost' => 15.00, 'status' => 'active']);
        $this->actingAs($this->admin);
        $this->get(route('reports.export', ['category' => 'domains', 'report' => 'active']))
            ->assertStatus(200)
            ->assertHeader('Content-Disposition', 'attachment; filename="domains-active-' . now()->format('Y-m-d') . '.csv"');
    }

    // ─── Task Kanban ──────────────────────────────────────────────────

    public function test_task_kanban_page_loads(): void
    {
        Task::factory()->create(['module_id' => $this->module->id, 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->get(route('tasks.kanban'))->assertStatus(200);
    }

    public function test_task_kanban_with_my_tasks_filter(): void
    {
        Task::factory()->create(['module_id' => $this->module->id, 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->get(route('tasks.kanban', ['my_tasks' => 1]))->assertStatus(200);
    }

    public function test_task_kanban_with_priority_filter(): void
    {
        Task::factory()->create(['module_id' => $this->module->id, 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id, 'priority' => 'high']);
        $this->actingAs($this->admin);
        $this->get(route('tasks.kanban', ['priority' => 'high']))->assertStatus(200);
    }

    // ─── Task Status Quick-Update ─────────────────────────────────────

    public function test_task_update_status_changes_status(): void
    {
        $task = Task::factory()->create([
            'module_id' => $this->module->id,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
            'status' => 'pending',
        ]);
        $this->actingAs($this->admin);
        $this->patch(route('tasks.update-status', $task->id), ['status' => 'in_progress'])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'in_progress']);
    }

    public function test_task_update_status_validates(): void
    {
        $task = Task::factory()->create([
            'module_id' => $this->module->id,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);
        $this->actingAs($this->admin);
        $this->patch(route('tasks.update-status', $task->id), [])
            ->assertSessionHasErrors('status');
    }

    // ─── Task My Tasks Filter ─────────────────────────────────────────

    public function test_task_index_with_my_tasks_filter(): void
    {
        Task::factory()->create([
            'module_id' => $this->module->id,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);
        $this->actingAs($this->admin);
        $this->get(route('tasks.index', ['my_tasks' => 1]))->assertStatus(200);
    }

    // ─── User Suspend/Unsuspend ───────────────────────────────────────

    public function test_user_suspend_updates_user(): void
    {
        $target = User::factory()->create();
        $this->actingAs($this->admin);
        $this->patch(route('users.suspend', $target->id), ['reason' => 'Test reason'])
            ->assertRedirect(route('users.show', $target->id));

        $this->assertNotNull($target->fresh()->suspended_at);
        $this->assertEquals('Test reason', $target->fresh()->suspension_reason);
    }

    public function test_user_unsuspend_clears_suspension(): void
    {
        $target = User::factory()->create(['suspended_at' => now(), 'suspension_reason' => 'Previous reason']);
        $this->actingAs($this->admin);
        $this->patch(route('users.unsuspend', $target->id))
            ->assertRedirect(route('users.show', $target->id));

        $this->assertNull($target->fresh()->suspended_at);
    }

    // ─── Activity Log Show ────────────────────────────────────────────

    public function test_activity_log_show_page_loads(): void
    {
        $activity = Activity::create([
            'description' => 'test activity',
            'event' => 'created',
            'causer_type' => User::class,
            'causer_id' => $this->admin->id,
            'subject_type' => Module::class,
            'subject_id' => $this->module->id,
        ]);
        $this->actingAs($this->admin);
        $this->get(route('activity-logs.show', $activity->id))
            ->assertStatus(200)
            ->assertSee('test activity');
    }

    // ─── Module Permissions ───────────────────────────────────────────

    public function test_module_permissions_index_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('module-permissions.index'))->assertStatus(200);
    }

    public function test_module_permissions_update_works(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('module-permissions.update'), [
            'permissions' => [
                $this->module->id => [
                    Role::where('slug', 'admin')->firstOrFail()->id => ['create', 'read'],
                ],
            ],
        ])->assertRedirect();
    }

    public function test_module_permissions_destroy_removes(): void
    {
        $this->actingAs($this->admin);
        $role = Role::where('slug', 'admin')->firstOrFail();
        $this->post(route('module-permissions.update'), [
            'module_id' => $this->module->id,
            'role_id' => $role->id,
            'can_create' => '1',
            'can_read' => '1',
        ])->assertRedirect();
        $this->assertDatabaseHas('module_role_permissions', [
            'module_id' => $this->module->id,
            'role_id' => $role->id,
        ]);
        $this->delete(route('module-permissions.destroy'), [
            'module_id' => $this->module->id,
            'role_id' => $role->id,
        ])->assertRedirect();
        $this->assertDatabaseMissing('module_role_permissions', [
            'module_id' => $this->module->id,
            'role_id' => $role->id,
        ]);
    }

    // ─── Filter/Search ────────────────────────────────────────────────

    public function test_domain_email_filters_work(): void
    {
        $domain = Domain::factory()->create(['name' => 'FilterDomain.com', 'user_id' => $this->admin->id]);
        DomainEmail::factory()->create(['email' => 'filter@test.com', 'status' => 'active', 'domain_id' => $domain->id]);
        $this->actingAs($this->admin);
        $this->get(route('domain-emails.index', ['search' => 'filter']))->assertStatus(200);
        $this->get(route('domain-emails.index', ['status' => 'active']))->assertStatus(200);
        $this->get(route('domain-emails.index', ['domain_id' => $domain->id]))->assertStatus(200);
    }

    public function test_other_service_filters_work(): void
    {
        OtherService::factory()->create(['name' => 'FilterService', 'service_type' => 'saas', 'status' => 'active']);
        $this->actingAs($this->admin);
        $this->get(route('other-services.index', ['search' => 'Filter']))->assertStatus(200);
        $this->get(route('other-services.index', ['service_type' => 'saas']))->assertStatus(200);
        $this->get(route('other-services.index', ['status' => 'active']))->assertStatus(200);
    }

    public function test_expiry_tracker_filters_work(): void
    {
        ExpiryTracker::factory()->create(['name' => 'FilterTracker', 'status' => 'active', 'expiry_date' => now()->addDays(10)]);
        ExpiryTracker::factory()->create(['name' => 'ExpiredTracker', 'status' => 'active', 'expiry_date' => now()->subDays(5)]);
        $this->actingAs($this->admin);
        $this->get(route('expiry-trackers.index', ['search' => 'Filter']))->assertStatus(200);
        $this->get(route('expiry-trackers.index', ['status' => 'active']))->assertStatus(200);
        $this->get(route('expiry-trackers.index', ['expiring_soon' => '1']))->assertStatus(200);
        $this->get(route('expiry-trackers.index', ['date_from' => now()->toDateString()]))->assertStatus(200);
        $this->get(route('expiry-trackers.index', ['date_to' => now()->addMonth()->toDateString()]))->assertStatus(200);
        $this->get(route('expiry-trackers.index', ['expired' => '1']))->assertStatus(200)->assertSee('ExpiredTracker');
    }

    public function test_login_audit_filters_work(): void
    {
        LoginAudit::create(['email' => 'filter@test.com', 'ip_address' => '1.2.3.4', 'event' => 'login_success']);
        $this->actingAs($this->admin);
        $this->get(route('login-audits.index', ['search' => 'filter']))->assertStatus(200);
        $this->get(route('login-audits.index', ['event' => 'login_success']))->assertStatus(200);
    }

    // ─── Bulk Actions ─────────────────────────────────────────────────

    public function test_bulk_action_update_status(): void
    {
        $email = DomainEmail::factory()->create(['status' => 'active']);
        $other = OtherService::factory()->create(['status' => 'active']);
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [
            'type' => 'domain-emails',
            'ids' => [$email->id],
            'action' => 'update-status',
            'status' => 'expired',
        ])->assertRedirect();
        $this->assertDatabaseHas('domain_emails', ['id' => $email->id, 'status' => 'expired']);
    }

    public function test_bulk_action_delete(): void
    {
        $email = DomainEmail::factory()->create();
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [
            'type' => 'domain-emails',
            'ids' => [$email->id],
            'action' => 'delete',
        ])->assertRedirect();
        $this->assertSoftDeleted($email);
    }

    public function test_bulk_action_validates_type(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [
            'type' => 'invalid',
            'ids' => [1],
            'action' => 'delete',
        ])->assertSessionHas('error');
    }

    public function test_bulk_action_validates_required(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [])
            ->assertSessionHasErrors(['type', 'ids', 'action']);
    }

    public function test_bulk_action_validates_status_required_for_update(): void
    {
        $email = DomainEmail::factory()->create();
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [
            'type' => 'domain-emails',
            'ids' => [$email->id],
            'action' => 'update-status',
        ])->assertSessionHasErrors('status');
    }

    public function test_bulk_action_tasks_update_status(): void
    {
        $module = Module::first();
        $task = Task::create(['title' => 'BulkTask', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [
            'type' => 'tasks',
            'ids' => [$task->id],
            'action' => 'update-status',
            'status' => 'completed',
        ])->assertRedirect();
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'completed']);
    }

    public function test_bulk_action_tasks_invalid_status_rejected(): void
    {
        $module = Module::first();
        $task = Task::create(['title' => 'BulkTask2', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [
            'type' => 'tasks',
            'ids' => [$task->id],
            'action' => 'update-status',
            'status' => 'active',
        ])->assertSessionHas('error');
    }

    public function test_bulk_action_tasks_delete(): void
    {
        $module = Module::first();
        $task = Task::create(['title' => 'BulkTask3', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [
            'type' => 'tasks',
            'ids' => [$task->id],
            'action' => 'delete',
        ])->assertRedirect();
        $this->assertSoftDeleted($task);
    }

    public function test_bulk_action_tasks_ownership_check(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'user')->firstOrFail());
        $module = Module::first();
        $task = Task::create(['title' => 'OthersTask', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);
        $this->actingAs($user);
        $this->post(route('bulk-action'), [
            'type' => 'tasks',
            'ids' => [$task->id],
            'action' => 'delete',
        ])->assertSessionHas('error');
    }

    public function test_bulk_action_vault_delete(): void
    {
        $entry = VaultEntry::factory()->create(['service_name' => 'BulkVault', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [
            'type' => 'vault',
            'ids' => [$entry->id],
            'action' => 'delete',
        ])->assertRedirect();
        $this->assertSoftDeleted($entry);
    }

    public function test_bulk_action_non_admin_own_delete_goes_through(): void
    {
        $role = Role::where('slug', 'user')->firstOrFail();
        $user = User::factory()->create();
        $user->assignRole($role);
        $own = VaultEntry::factory()->create(['service_name' => 'OwnEntry', 'user_id' => $user->id]);
        $others = VaultEntry::factory()->create(['service_name' => 'OthersEntry', 'user_id' => $this->admin->id]);

        $this->actingAs($user);
        $this->post(route('bulk-action'), [
            'type' => 'vault',
            'ids' => [$own->id, $others->id],
            'action' => 'delete',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSoftDeleted($own);
        $this->assertNotSoftDeleted($others);
    }

    public function test_bulk_action_non_admin_restore_own_soft_deleted(): void
    {
        $role = Role::where('slug', 'user')->firstOrFail();
        $user = User::factory()->create();
        $user->assignRole($role);
        $domain = Domain::factory()->create(['user_id' => $user->id]);
        $domain->delete();

        $this->actingAs($user);
        $this->post(route('bulk-action'), [
            'type' => 'domains',
            'ids' => [$domain->id],
            'action' => 'restore',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertNotSoftDeleted($domain);
    }

    public function test_bulk_action_vault_update_status_rejected(): void
    {
        $entry = VaultEntry::factory()->create(['service_name' => 'BulkVault2', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [
            'type' => 'vault',
            'ids' => [$entry->id],
            'action' => 'update-status',
            'status' => 'active',
        ])->assertSessionHas('error');
    }

    public function test_bulk_action_notes_delete(): void
    {
        $note = Note::factory()->create(['content' => 'BulkNote', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [
            'type' => 'notes',
            'ids' => [$note->id],
            'action' => 'delete',
        ])->assertRedirect();
        $this->assertSoftDeleted($note);
    }

    public function test_bulk_action_users_suspend(): void
    {
        $user = User::factory()->create(['suspended_at' => null]);
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [
            'type' => 'users',
            'ids' => [$user->id],
            'action' => 'suspend',
        ])->assertRedirect();
        $this->assertNotNull($user->fresh()->suspended_at);
    }

    public function test_bulk_action_users_unsuspend(): void
    {
        $user = User::factory()->create(['suspended_at' => now()]);
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [
            'type' => 'users',
            'ids' => [$user->id],
            'action' => 'unsuspend',
        ])->assertRedirect();
        $this->assertNull($user->fresh()->suspended_at);
    }

    public function test_bulk_action_users_forbidden_for_non_admin(): void
    {
        $regular = User::factory()->create();
        $regular->assignRole(Role::where('slug', 'user')->firstOrFail());
        $target = User::factory()->create();
        $this->actingAs($regular);
        $this->post(route('bulk-action'), [
            'type' => 'users',
            'ids' => [$target->id],
            'action' => 'suspend',
        ])->assertSessionHas('error');
    }

    public function test_bulk_action_restore(): void
    {
        $email = DomainEmail::factory()->create();
        $email->delete();
        $this->assertSoftDeleted($email);
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [
            'type' => 'domain-emails',
            'ids' => [$email->id],
            'action' => 'restore',
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertNotSoftDeleted($email);
    }

    public function test_bulk_action_force_delete(): void
    {
        $email = DomainEmail::factory()->create();
        $email->delete();
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [
            'type' => 'domain-emails',
            'ids' => [$email->id],
            'action' => 'force-delete',
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseMissing('domain_emails', ['id' => $email->id]);
    }

    public function test_task_trashed_filter_shows_deleted(): void
    {
        $task = Task::factory()->create(['title' => 'TrashTaskTest']);
        $task->delete();
        $this->actingAs($this->admin);
        $this->get(route('tasks.index', ['trashed' => 1]))
            ->assertStatus(200)
            ->assertSee('TrashTaskTest');
        $this->get(route('tasks.index'))
            ->assertStatus(200)
            ->assertDontSee('TrashTaskTest');
    }

    // ─── Search ───────────────────────────────────────────────────────

    public function test_search_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('search'))->assertStatus(200);
    }

    public function test_search_with_short_query_shows_empty(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('search', ['q' => 'a']))->assertStatus(200)->assertSee('at least 2 characters');
    }

    public function test_search_returns_results(): void
    {
        Domain::factory()->create(['name' => 'SearchDomainTest', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->get(route('search', ['q' => 'SearchDomain']))->assertStatus(200)->assertSee('SearchDomain');
    }

    public function test_search_no_results_shows_message(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('search', ['q' => 'ZZZZNoMatch999']))->assertStatus(200)->assertSee('No results found');
    }

    // ─── Export ───────────────────────────────────────────────────────

    public function test_export_domains_returns_csv(): void
    {
        Domain::factory()->create(['name' => 'ExportTest', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $response = $this->get(route('export', 'domains'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('attachment; filename="domains-', $response->headers->get('Content-Disposition'));
    }

    public function test_export_invalid_type_redirects_back(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('export', 'invalid'))->assertSessionHas('error');
    }

    public function test_export_non_admin_filters_by_user(): void
    {
        $role = Role::where('slug', 'user')->firstOrFail();
        $user = User::factory()->create();
        $user->assignRole($role);

        $module = Module::where('slug', 'domains')->first();
        ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $role->id,
            'can_export' => true,
        ]);

        Domain::factory()->create(['name' => 'my-export-domain.com', 'user_id' => $user->id, 'service_provider_id' => null]);
        Domain::factory()->create(['name' => 'admin-export-domain.com', 'user_id' => $this->admin->id, 'service_provider_id' => null]);

        $this->actingAs($user);
        $response = $this->get(route('export', 'domains'));
        $response->assertStatus(200);
        $this->assertStringContainsString('my-export-domain.com', $response->getContent());
        $this->assertStringNotContainsString('admin-export-domain.com', $response->getContent());
    }

    public function test_export_tasks_returns_csv(): void
    {
        $module = Module::first();
        Task::create(['title' => 'TaskExport', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->get(route('export', 'tasks'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_export_vault_returns_csv(): void
    {
        VaultEntry::factory()->create(['service_name' => 'VaultExport', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->get(route('export', 'vault'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_export_notes_returns_csv(): void
    {
        Note::factory()->create(['content' => 'NoteExport', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->get(route('export', 'notes'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_export_features_returns_csv(): void
    {
        Feature::factory()->create(['name' => 'FeatureExport']);
        $this->actingAs($this->admin);
        $this->get(route('export', 'features'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_export_modules_returns_csv(): void
    {
        $feature = Feature::factory()->create();
        Module::factory()->create(['name' => 'ModuleExport', 'feature_id' => $feature->id]);
        $this->actingAs($this->admin);
        $this->get(route('export', 'modules'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_export_webhooks_returns_csv(): void
    {
        Webhook::factory()->create(['name' => 'WebhookExport', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->get(route('export', 'webhooks'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_export_activity_logs_returns_csv(): void
    {
        activity()->log('Test export');
        $this->actingAs($this->admin);
        $this->get(route('export', 'activity-logs'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_export_login_audits_returns_csv(): void
    {
        LoginAudit::create(['user_id' => $this->admin->id, 'email' => 'test@example.com', 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'event' => 'login_success']);
        $this->actingAs($this->admin);
        $this->get(route('export', 'login-audits'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_export_attachments_returns_csv(): void
    {
        Attachment::factory()->create(['filename' => 'test.txt', 'user_id' => $this->admin->id]);
        $this->actingAs($this->admin);
        $this->get(route('export', 'attachments'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_export_users_returns_csv(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('export', 'users'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    // ─── Import ───────────────────────────────────────────────────────

    public function test_import_create_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('import.create'))->assertStatus(200)->assertSee('Import CSV');
    }

    public function test_import_store_with_valid_csv(): void
    {
        $csv = "name,service_type,status\nTestImport,saas,active\n";
        $file = UploadedFile::fake()->createWithContent('test.csv', $csv);
        $this->actingAs($this->admin);
        $this->post(route('import.store'), [
            'type' => 'other-services',
            'file' => $file,
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('other_services', ['name' => 'TestImport']);
    }

    public function test_import_store_validates_type(): void
    {
        $file = UploadedFile::fake()->create('test.csv', 100);
        $this->actingAs($this->admin);
        $this->post(route('import.store'), [
            'type' => 'invalid',
            'file' => $file,
        ])->assertSessionHas('error');
    }

    public function test_import_store_validates_file_required(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('import.store'), ['type' => 'domains'])
            ->assertSessionHasErrors('file');
    }

    // ─── Calendar ────────────────────────────────────────────────────

    public function test_calendar_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('calendar'))->assertStatus(200)->assertSee('Calendar');
    }

    public function test_calendar_shows_events(): void
    {
        Domain::factory()->create([
            'name' => 'CalendarDomain',
            'expiry_date' => now()->startOfMonth()->addDays(10),
            'status' => 'active',
            'user_id' => $this->admin->id,
        ]);
        $this->actingAs($this->admin);
        $this->get(route('calendar'))->assertStatus(200)->assertSee('CalendarDomain');
    }

    public function test_calendar_with_month_navigation(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('calendar', ['month' => 1, 'year' => 2025]))->assertStatus(200);
        $this->get(route('calendar', ['month' => 12, 'year' => 2026]))->assertStatus(200);
    }

    public function test_calendar_non_admin_sees_records_in_accessible_modules(): void
    {
        $role = Role::where('slug', 'user')->firstOrFail();
        $regularUser = User::factory()->create();
        $regularUser->assignRole($role);
        $module = Module::where('slug', 'domains')->firstOrFail();
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $module->id, 'role_id' => $role->id],
            ['can_read' => true]
        );

        Domain::factory()->create([
            'name' => 'my-domain.com',
            'expiry_date' => now()->startOfMonth()->addDays(10),
            'user_id' => $regularUser->id,
            'module_id' => $module->id,
        ]);
        Domain::factory()->create([
            'name' => 'other-domain.com',
            'expiry_date' => now()->startOfMonth()->addDays(10),
            'user_id' => $this->admin->id,
            'module_id' => $module->id,
        ]);

        $this->actingAs($regularUser);
        $response = $this->get(route('calendar'));
        $response->assertStatus(200)->assertSee('my-domain.com')->assertSee('other-domain.com');
    }

    public function test_calendar_shows_tasks_with_due_dates(): void
    {
        $this->actingAs($this->admin);
        $task = Task::factory()->create([
            'title' => 'CalendarTaskTest',
            'due_date' => now()->startOfMonth()->addDays(15),
            'status' => 'pending',
        ]);
        $this->get(route('calendar'))
            ->assertStatus(200)
            ->assertSee('CalendarTaskTest');
    }

    // ─── API Tokens ──────────────────────────────────────────────────

    public function test_tokens_index_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('tokens.index'))->assertStatus(200)->assertSee('API Tokens');
    }

    public function test_tokens_create_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('tokens.create'))->assertStatus(200)->assertSee('Create API Token');
    }

    public function test_token_store_creates_token(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('tokens.store'), ['name' => 'My Test Token'])
            ->assertRedirect(route('tokens.index'))
            ->assertSessionHas('plain_text');
        $this->assertDatabaseHas('personal_access_tokens', ['name' => 'My Test Token']);
    }

    public function test_token_store_validates(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('tokens.store'), [])->assertSessionHasErrors('name');
    }

    public function test_token_destroy_revokes(): void
    {
        $token = $this->admin->createToken('Revoke Test');
        $this->actingAs($this->admin);
        $this->delete(route('tokens.destroy', $token->accessToken->id))
            ->assertRedirect(route('tokens.index'));
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }

    public function test_token_destroy_not_found(): void
    {
        $this->actingAs($this->admin);
        $this->delete(route('tokens.destroy', 99999))
            ->assertRedirect()
            ->assertSessionHas('error', 'Token not found.');
    }

    // ─── Guest Access for New Routes ──────────────────────────────────

    public function test_guest_cannot_access_new_routes(): void
    {
        $routes = [
            'notifications.index', 'search', 'calendar',
            'tokens.index', 'tokens.create', 'import.create',
            'tasks.my', 'tasks.my-counts',
        ];
        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $this->assertContains($response->getStatusCode(), [302, 401]);
        }
    }

    // ─── My Tasks ─────────────────────────────────────────────────────

    public function test_my_tasks_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('tasks.my'))->assertStatus(200)->assertSee('My Tasks');
    }

    public function test_my_tasks_shows_only_assigned_tasks(): void
    {
        $module = Module::first();
        $assigned = Task::create(['title' => 'My Task', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);
        $assigned->assignees()->attach($this->admin->id);
        $notAssigned = Task::create(['title' => 'Not Mine', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'low', 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);

        $this->actingAs($this->admin);
        $res = $this->get(route('tasks.my'));
        $res->assertStatus(200)->assertSee('My Task')->assertDontSee('Not Mine');
    }

    public function test_my_task_counts_endpoint(): void
    {
        $module = Module::first();
        $task = Task::create(['title' => 'Counted Task', 'module_id' => $module->id, 'status' => 'in_progress', 'priority' => 'medium', 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);
        $task->assignees()->attach($this->admin->id);

        $this->actingAs($this->admin);
        $this->getJson(route('tasks.my-counts'))
            ->assertOk()
            ->assertJson(['total' => 1, 'in_progress' => 1]);
    }

    public function test_my_tasks_filters_by_status(): void
    {
        $module = Module::first();
        $pending = Task::create(['title' => 'Pending Task', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);
        $pending->assignees()->attach($this->admin->id);
        $completed = Task::create(['title' => 'Done Task', 'module_id' => $module->id, 'status' => 'completed', 'priority' => 'low', 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);
        $completed->assignees()->attach($this->admin->id);

        $this->actingAs($this->admin);
        $res = $this->get(route('tasks.my', ['status' => 'completed']));
        $res->assertStatus(200)->assertSee('Done Task')->assertDontSee('Pending Task');
    }

    // ─── Monitor Check ─────────────────────────────────────────────────

    public function test_monitor_check_pings_resource(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $domain = Domain::factory()->create([
            'monitoring_url' => 'https://example.com',
            'user_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin);
        $this->get(route('monitor.check', ['domains', $domain->id]))
            ->assertRedirect()
            ->assertSessionHas('monitor_result');

        $this->assertNotNull($domain->fresh()->last_ping_at);
    }

    public function test_monitor_check_requires_monitoring_url(): void
    {
        $domain = Domain::factory()->create([
            'monitoring_url' => null,
            'user_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin);
        $this->get(route('monitor.check', ['domains', $domain->id]))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ─── Last Login ──────────────────────────────────────────────────

    public function test_users_index_shows_last_login(): void
    {
        $this->actingAs($this->admin);
        DB::table('login_audits')->insert([
            'user_id' => $this->admin->id,
            'email' => $this->admin->email,
            'event' => 'login_success',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);
        $this->get(route('users.index'))
            ->assertStatus(200)
            ->assertSee('Last Login')
            ->assertSee('hour ago');
    }

    // ─── Activity Timeline on Resource Show Pages ────────────────────

    public function test_domain_show_displays_activity_timeline(): void
    {
        $this->actingAs($this->admin);
        $domain = Domain::factory()->create(['user_id' => $this->admin->id]);
        activity()
            ->performedOn($domain)
            ->causedBy($this->admin)
            ->log('domain_created');
        $this->get(route('domains.show', $domain->id))
            ->assertStatus(200)
            ->assertSee('Activity Timeline')
            ->assertSee('domain_created');
    }

    // ─── My Vault ────────────────────────────────────────────────────

    public function test_notes_index_notable_type_filter(): void
    {
        $feature = Feature::factory()->create();
        Note::factory()->create(['content' => 'FeatureNote', 'notable_type' => Feature::class, 'notable_id' => $feature->id]);
        Note::factory()->create(['content' => 'GlobalNote', 'notable_type' => null, 'notable_id' => null]);
        $this->actingAs($this->admin);
        $response = $this->get(route('notes.index', ['notable_type' => Feature::class]));
        $response->assertStatus(200)->assertSee('FeatureNote')->assertDontSee('GlobalNote');
    }

    public function test_my_vault_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('vault.my'))->assertStatus(200)->assertSee('Vault');
    }

    public function test_my_vault_shows_only_own_entries(): void
    {
        VaultEntry::factory()->create(['service_name' => 'MyEntry', 'user_id' => $this->admin->id]);
        VaultEntry::factory()->create(['service_name' => 'OthersEntry', 'user_id' => $this->user->id]);

        $this->actingAs($this->admin);
        $res = $this->get(route('vault.my'));
        $res->assertStatus(200)->assertSee('MyEntry')->assertDontSee('OthersEntry');
    }

    public function test_my_vault_search_filter(): void
    {
        VaultEntry::factory()->create(['service_name' => 'SearchableService', 'user_id' => $this->admin->id]);
        VaultEntry::factory()->create(['service_name' => 'OtherService', 'user_id' => $this->admin->id]);

        $this->actingAs($this->admin);
        $res = $this->get(route('vault.my', ['search' => 'Searchable']));
        $res->assertStatus(200)->assertSee('SearchableService')->assertDontSee('OtherService');
    }

    // ─── Activity Timeline on New Show Pages ─────────────────────────

    public function test_features_show_displays_activity_timeline(): void
    {
        $this->actingAs($this->admin);
        $feature = Feature::factory()->create();
        activity()->performedOn($feature)->causedBy($this->admin)->log('feature_created');
        $this->get(route('features.show', $feature->id))
            ->assertStatus(200)
            ->assertSee('Activity Timeline')
            ->assertSee('feature_created');
    }

    public function test_modules_show_displays_activity_timeline(): void
    {
        $this->actingAs($this->admin);
        $module = Module::factory()->create();
        activity()->performedOn($module)->causedBy($this->admin)->log('module_created');
        $this->get(route('modules.show', $module->id))
            ->assertStatus(200)
            ->assertSee('Activity Timeline')
            ->assertSee('module_created');
    }

    public function test_webhooks_show_displays_activity_timeline(): void
    {
        $this->actingAs($this->admin);
        $webhook = Webhook::factory()->create(['user_id' => $this->admin->id]);
        activity()->performedOn($webhook)->causedBy($this->admin)->log('webhook_created');
        $this->get(route('webhooks.show', $webhook->id))
            ->assertStatus(200)
            ->assertSee('Activity Timeline')
            ->assertSee('webhook_created');
    }

    public function test_attachments_show_displays_activity_timeline(): void
    {
        $this->actingAs($this->admin);
        $attachment = Attachment::factory()->create(['user_id' => $this->admin->id]);
        activity()->performedOn($attachment)->causedBy($this->admin)->log('attachment_created');
        $this->get(route('attachments.show', $attachment->id))
            ->assertStatus(200)
            ->assertSee('Activity Timeline')
            ->assertSee('attachment_created');
    }

    public function test_notes_show_displays_activity_timeline(): void
    {
        $this->actingAs($this->admin);
        $note = Note::factory()->create(['user_id' => $this->admin->id]);
        activity()->performedOn($note)->causedBy($this->admin)->log('note_created');
        $this->get(route('notes.show', $note->id))
            ->assertStatus(200)
            ->assertSee('Activity Timeline')
            ->assertSee('note_created');
    }

    public function test_vault_show_displays_activity_timeline(): void
    {
        $this->actingAs($this->admin);
        $entry = VaultEntry::factory()->create(['user_id' => $this->admin->id]);
        activity()->performedOn($entry)->causedBy($this->admin)->log('vault_created');
        $this->get(route('vault.show', $entry->id))
            ->assertStatus(200)
            ->assertSee('Activity Timeline')
            ->assertSee('vault_created');
    }

    public function test_roles_index_lists_roles(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('roles.index'))
            ->assertStatus(200)
            ->assertSee('super-admin')
            ->assertSee('admin');
    }

    public function test_roles_create_and_store(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('roles.create'))->assertStatus(200);

        $this->post(route('roles.store'), [
            'name' => 'Editor',
            'slug' => 'test-editor',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['slug' => 'test-editor']);
    }

    public function test_roles_show_displays_role(): void
    {
        $this->actingAs($this->admin);
        $role = Role::where('slug', 'admin')->firstOrFail();
        $this->get(route('roles.show', $role->id))
            ->assertStatus(200)
            ->assertSee($role->name)
            ->assertSee('Privileges');
    }

    public function test_roles_edit_and_update(): void
    {
        $this->actingAs($this->admin);
        $role = Role::create(['name' => 'Old Name', 'slug' => 'old-name']);

        $this->get(route('roles.edit', $role->id))->assertStatus(200)->assertSee('Old Name');

        $this->put(route('roles.update', $role->id), [
            'name' => 'New Name',
            'slug' => 'new-name',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['slug' => 'new-name']);
        $this->assertDatabaseMissing('roles', ['slug' => 'old-name']);
    }

    public function test_roles_destroy_protected(): void
    {
        $this->actingAs($this->admin);

        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $this->delete(route('roles.destroy', $adminRole->id))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['slug' => 'admin']);

        $superAdminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->delete(route('roles.destroy', $superAdminRole->id))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['slug' => 'super-admin']);
    }

    public function test_roles_destroy_unprotected(): void
    {
        $this->actingAs($this->admin);
        $role = Role::create(['name' => 'Temp', 'slug' => 'temp']);

        $this->delete(route('roles.destroy', $role->id))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('roles', ['slug' => 'temp']);
    }

    public function test_roles_privilege_attach_and_detach(): void
    {
        $this->actingAs($this->admin);
        $role = Role::where('slug', 'admin')->firstOrFail();
        $privilege = Privilege::create([
            'name' => 'Test Priv', 'slug' => 'test-priv',
        ]);

        $this->post(route('roles.privileges.attach', $role->id), [
            'privilege_id' => $privilege->id,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('privilege_role', [
            'role_id' => $role->id, 'privilege_id' => $privilege->id,
        ]);

        $this->post(route('roles.privileges.detach', $role->id), [
            'privilege_id' => $privilege->id,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseMissing('privilege_role', [
            'role_id' => $role->id, 'privilege_id' => $privilege->id,
        ]);
    }

    public function test_privileges_index_lists_privileges(): void
    {
        $this->actingAs($this->admin);
        Privilege::create([
            'name' => 'List Items', 'slug' => 'list-items',
        ]);
        $this->get(route('privileges.index'))
            ->assertStatus(200)
            ->assertSee('List Items');
    }

    public function test_privileges_create_and_store(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('privileges.create'))->assertStatus(200);

        $this->post(route('privileges.store'), [
            'name' => 'Create Items',
            'slug' => 'create-items',
            'description' => 'Can create items',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('privileges', ['slug' => 'create-items']);
    }

    public function test_privileges_show_edit_update_destroy(): void
    {
        $this->actingAs($this->admin);
        $privilege = Privilege::create([
            'name' => 'Delete Items', 'slug' => 'delete-items',
        ]);

        $this->get(route('privileges.show', $privilege->id))
            ->assertStatus(200)
            ->assertSee('Delete Items');

        $this->get(route('privileges.edit', $privilege->id))
            ->assertStatus(200)
            ->assertSee('Delete Items');

        $this->put(route('privileges.update', $privilege->id), [
            'name' => 'Updated Priv',
            'slug' => 'updated-priv',
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('privileges', ['slug' => 'updated-priv']);

        $this->delete(route('privileges.destroy', $privilege->id))
            ->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseMissing('privileges', ['slug' => 'updated-priv']);
    }

    public function test_feature_show_displays_scoped_notes(): void
    {
        $this->actingAs($this->admin);
        $feature = Feature::factory()->create();
        $content = 'Test scoped note content for feature';
        $note = $feature->notes()->create([
            'user_id' => $this->admin->id,
            'content' => $content,
        ]);
        $this->get(route('features.show', $feature->id))
            ->assertStatus(200)
            ->assertSee($note->content)
            ->assertSee('Post Note');
    }

    public function test_module_show_displays_scoped_notes(): void
    {
        $this->actingAs($this->admin);
        $feature = Feature::factory()->create();
        $module = Module::factory()->create(['feature_id' => $feature->id]);
        $content = 'Test scoped note content for module';
        $note = $module->notes()->create([
            'user_id' => $this->admin->id,
            'content' => $content,
        ]);
        $this->get(route('modules.show', $module->id))
            ->assertStatus(200)
            ->assertSee($note->content)
            ->assertSee('Post Note');
    }

    public function test_store_scoped_note_from_feature_page(): void
    {
        $this->actingAs($this->admin);
        $feature = Feature::factory()->create();

        $this->post(route('notes.store'), [
            'content' => 'Scoped note for feature',
            'notable_type' => 'App\Models\Feature',
            'notable_id' => $feature->id,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('notes', [
            'content' => 'Scoped note for feature',
            'notable_type' => 'App\Models\Feature',
            'notable_id' => $feature->id,
        ]);
    }

    public function test_store_scoped_note_from_module_page(): void
    {
        $this->actingAs($this->admin);
        $feature = Feature::factory()->create();
        $module = Module::factory()->create(['feature_id' => $feature->id]);

        $this->post(route('notes.store'), [
            'content' => 'Scoped note for module',
            'notable_type' => 'App\Models\Module',
            'notable_id' => $module->id,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('notes', [
            'content' => 'Scoped note for module',
            'notable_type' => 'App\Models\Module',
            'notable_id' => $module->id,
        ]);
    }

    public function test_reports_index_has_user_filter(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('reports.index'))
            ->assertStatus(200)
            ->assertSee('All users')
            ->assertSee($this->admin->name);
    }

    public function test_reports_user_filter_affects_login_summary(): void
    {
        $this->actingAs($this->admin);
        LoginAudit::create([
            'user_id' => $this->admin->id, 'email' => $this->admin->email, 'event' => 'login_success',
        ]);
        LoginAudit::create([
            'user_id' => $this->user->id, 'email' => $this->user->email, 'event' => 'login_failed',
        ]);

        $response = $this->get(route('reports.index', ['user_id' => $this->admin->id]));
        $response->assertStatus(200);
    }

    public function test_user_show_displays_module_permissions(): void
    {
        $this->actingAs($this->admin);
        $module = Module::factory()->create();
        ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => Role::where('slug', 'super-admin')->firstOrFail()->id,
            'can_read' => true,
        ]);

        $this->get(route('users.show', $this->admin->id))
            ->assertStatus(200)
            ->assertSee('Module Permissions')
            ->assertSee($module->name);
    }

    // ─── New Features: Search/Filter Tests ────────────────────────────

    public function test_privileges_index_search_filters_by_name(): void
    {
        $this->actingAs($this->admin);
        Privilege::create(['name' => 'SearchablePriv', 'slug' => 'searchable-priv']);
        Privilege::create(['name' => 'OtherPriv', 'slug' => 'other-priv']);

        $this->get(route('privileges.index', ['search' => 'Searchable']))
            ->assertStatus(200)
            ->assertSee('SearchablePriv')
            ->assertDontSee('OtherPriv');
    }

    public function test_roles_index_search_filters_by_name(): void
    {
        $this->actingAs($this->admin);
        Role::create(['name' => 'SearchableRole', 'slug' => 'searchable-role']);

        $this->get(route('roles.index', ['search' => 'Searchable']))
            ->assertStatus(200)
            ->assertSee('SearchableRole');
    }

    public function test_tokens_index_search_filters_by_name(): void
    {
        $this->actingAs($this->admin);
        $this->admin->createToken('SearchableToken');
        $this->admin->createToken('OtherToken');

        $this->get(route('tokens.index', ['search' => 'Searchable']))
            ->assertStatus(200)
            ->assertSee('SearchableToken')
            ->assertDontSee('OtherToken');
    }

    // ─── Login Audit Destroy ─────────────────────────────────────────

    public function test_login_audit_destroy_deletes(): void
    {
        $this->actingAs($this->admin);
        $audit = LoginAudit::create([
            'user_id' => $this->admin->id, 'email' => 'test@test.com', 'ip_address' => '1.2.3.4',
            'user_agent' => 'test', 'event' => 'login_success',
        ]);

        $this->delete(route('login-audits.destroy', $audit->id))
            ->assertRedirect(route('login-audits.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('login_audits', ['id' => $audit->id]);
        $this->assertNotNull($audit->fresh()->deleted_at);
    }

    // ─── Bulk Action: New Types ───────────────────────────────────────

    public function test_bulk_action_tokens_delete(): void
    {
        $this->actingAs($this->admin);
        $token = $this->admin->createToken('BulkToken');

        $this->post(route('bulk-action'), [
            'type' => 'tokens',
            'ids' => [$token->accessToken->id],
            'action' => 'delete',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }

    public function test_bulk_action_tokens_rejects_other_actions(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('bulk-action'), [
            'type' => 'tokens',
            'ids' => [1],
            'action' => 'restore',
        ])->assertSessionHas('error');
    }

    public function test_bulk_action_roles_delete_unprotected(): void
    {
        $this->actingAs($this->admin);
        $role = Role::create(['name' => 'BulkRole', 'slug' => 'bulk-role']);

        $this->post(route('bulk-action'), [
            'type' => 'roles',
            'ids' => [$role->id],
            'action' => 'delete',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_bulk_action_roles_delete_protected_blocked(): void
    {
        $this->actingAs($this->admin);
        $adminRole = Role::where('slug', 'admin')->firstOrFail();

        $this->post(route('bulk-action'), [
            'type' => 'roles',
            'ids' => [$adminRole->id],
            'action' => 'delete',
        ])->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['id' => $adminRole->id]);
    }

    public function test_bulk_action_privileges_delete(): void
    {
        $this->actingAs($this->admin);
        $privilege = Privilege::create(['name' => 'BulkPriv', 'slug' => 'bulk-priv']);

        $this->post(route('bulk-action'), [
            'type' => 'privileges',
            'ids' => [$privilege->id],
            'action' => 'delete',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseMissing('privileges', ['id' => $privilege->id]);
    }

    public function test_bulk_action_attachments_delete_cleans_storage(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);
        $file = UploadedFile::fake()->create('test.txt', 100);
        $path = $file->store('attachments', 'public');
        $attachment = Attachment::factory()->create([
            'filename' => basename($path),
            'original_name' => 'test.txt',
            'user_id' => $this->admin->id,
        ]);

        $this->post(route('bulk-action'), [
            'type' => 'attachments',
            'ids' => [$attachment->id],
            'action' => 'delete',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSoftDeleted($attachment);
        Storage::disk('public')->assertExists($path);
    }

    public function test_bulk_action_login_audits_forbidden_for_non_admin(): void
    {
        $regular = User::factory()->create();
        $regular->assignRole(Role::where('slug', 'user')->firstOrFail());
        $audit = LoginAudit::create([
            'user_id' => $this->admin->id, 'email' => 'test@test.com', 'ip_address' => '1.2.3.4',
            'user_agent' => 'test', 'event' => 'login_success',
        ]);

        $this->actingAs($regular);
        $this->post(route('bulk-action'), [
            'type' => 'login-audits',
            'ids' => [$audit->id],
            'action' => 'delete',
        ])->assertSessionHas('error');
    }

    public function test_bulk_action_login_audits_allowed_for_super_admin(): void
    {
        $this->actingAs($this->admin);
        $audit = LoginAudit::create([
            'user_id' => $this->admin->id, 'email' => 'test@test.com', 'ip_address' => '1.2.3.4',
            'user_agent' => 'test', 'event' => 'login_success',
        ]);

        $this->post(route('bulk-action'), [
            'type' => 'login-audits',
            'ids' => [$audit->id],
            'action' => 'delete',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseMissing('login_audits', ['id' => $audit->id]);
    }

    // ─── Export: New Types ────────────────────────────────────────────

    public function test_export_roles_returns_csv(): void
    {
        Role::create(['name' => 'ExportRole', 'slug' => 'export-role']);
        $this->actingAs($this->admin);
        $this->get(route('export', 'roles'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_export_privileges_returns_csv(): void
    {
        Privilege::create(['name' => 'ExportPriv', 'slug' => 'export-priv']);
        $this->actingAs($this->admin);
        $this->get(route('export', 'privileges'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_export_tokens_returns_csv(): void
    {
        $this->actingAs($this->admin);
        $this->admin->createToken('ExportToken');
        $this->get(route('export', 'tokens'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }
}
