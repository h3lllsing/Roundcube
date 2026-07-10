<?php

namespace Tests\Unit;

use App\Models\Attachment;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Feature;
use App\Models\Hosting;
use App\Models\LoginAudit;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\Note;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Task;
use App\Models\User;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use App\Models\Webhook;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
    }

    public function test_task_belongs_to_module(): void
    {
        $module = Module::factory()->create();
        $task = Task::factory()->create(['module_id' => $module->id]);

        $this->assertInstanceOf(Module::class, $task->module);
        $this->assertTrue($task->module->is($module));
    }

    public function test_task_belongs_to_many_assignees(): void
    {
        $task = Task::factory()->create();
        $users = User::factory()->count(2)->create();
        $task->assignees()->attach($users->pluck('id'));

        $this->assertCount(2, $task->assignees);
        $this->assertInstanceOf(User::class, $task->assignees->first());
    }

    public function test_domain_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $domain->user);
        $this->assertTrue($domain->user->is($user));
    }

    public function test_domain_belongs_to_module(): void
    {
        $module = Module::factory()->create();
        $domain = Domain::factory()->create(['module_id' => $module->id]);

        $this->assertInstanceOf(Module::class, $domain->module);
        $this->assertTrue($domain->module->is($module));
    }

    public function test_hosting_belongs_to_user_and_module(): void
    {
        $user = User::factory()->create();
        $module = Module::factory()->create();
        $hosting = Hosting::factory()->create(['user_id' => $user->id, 'module_id' => $module->id]);

        $this->assertInstanceOf(User::class, $hosting->user);
        $this->assertInstanceOf(Module::class, $hosting->module);
    }

    public function test_feature_has_many_modules(): void
    {
        $feature = Feature::factory()->create();
        Module::factory()->count(3)->create(['feature_id' => $feature->id]);

        $this->assertCount(3, $feature->modules);
        $this->assertInstanceOf(Module::class, $feature->modules->first());
    }

    public function test_feature_has_many_active_modules(): void
    {
        $feature = Feature::factory()->create();
        Module::factory()->create(['feature_id' => $feature->id, 'is_active' => true]);
        Module::factory()->create(['feature_id' => $feature->id, 'is_active' => false]);

        $this->assertCount(1, $feature->activeModules);
    }

    public function test_feature_and_module_morph_many_notes(): void
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create();
        $module = Module::factory()->create();

        $feature->notes()->create(['user_id' => $user->id, 'content' => 'Feature note']);
        $module->notes()->create(['user_id' => $user->id, 'content' => 'Module note']);

        $this->assertCount(1, $feature->notes);
        $this->assertCount(1, $module->notes);
        $this->assertInstanceOf(Note::class, $feature->notes->first());
        $this->assertInstanceOf(Note::class, $module->notes->first());
    }

    public function test_note_belongs_to_notable_polymorphic(): void
    {
        $feature = Feature::factory()->create();
        $note = Note::factory()->create([
            'notable_type' => Feature::class,
            'notable_id' => $feature->id,
        ]);

        $this->assertInstanceOf(Feature::class, $note->notable);
    }

    public function test_attachment_morph_to_notable(): void
    {
        $feature = Feature::factory()->create();
        $attachment = Attachment::factory()->create([
            'notable_type' => Feature::class,
            'notable_id' => $feature->id,
        ]);

        $this->assertInstanceOf(Feature::class, $attachment->notable);
    }

    public function test_module_role_permissions_belongs_to_module(): void
    {
        $module = Module::factory()->create();
        $role = Role::where('slug', 'admin')->firstOrFail();
        $mrp = ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $role->id,
        ]);

        $this->assertInstanceOf(Module::class, $mrp->module);
        $this->assertTrue($mrp->module->is($module));
    }

    public function test_vps_belongs_to_user_and_module(): void
    {
        $user = User::factory()->create();
        $module = Module::factory()->create();
        $vps = Vps::factory()->create(['user_id' => $user->id, 'module_id' => $module->id]);

        $this->assertInstanceOf(User::class, $vps->user);
        $this->assertInstanceOf(Module::class, $vps->module);
    }

    public function test_voip_belongs_to_user_and_module(): void
    {
        $user = User::factory()->create();
        $module = Module::factory()->create();
        $voip = Voip::factory()->create(['user_id' => $user->id, 'module_id' => $module->id]);

        $this->assertInstanceOf(User::class, $voip->user);
        $this->assertInstanceOf(Module::class, $voip->module);
    }

    public function test_service_provider_belongs_to_user_and_module(): void
    {
        $user = User::factory()->create();
        $module = Module::factory()->create();
        $sp = ServiceProvider::factory()->create(['user_id' => $user->id, 'module_id' => $module->id]);

        $this->assertInstanceOf(User::class, $sp->user);
        $this->assertInstanceOf(Module::class, $sp->module);
    }

    public function test_domain_email_belongs_to_domain(): void
    {
        $domain = Domain::factory()->create();
        $de = DomainEmail::factory()->create(['domain_id' => $domain->id]);

        $this->assertInstanceOf(Domain::class, $de->domain);
        $this->assertTrue($de->domain->is($domain));
    }

    public function test_other_service_belongs_to_user_and_module(): void
    {
        $user = User::factory()->create();
        $module = Module::factory()->create();
        $os = OtherService::factory()->create(['user_id' => $user->id, 'module_id' => $module->id]);

        $this->assertInstanceOf(User::class, $os->user);
        $this->assertInstanceOf(Module::class, $os->module);
    }

    public function test_expiry_tracker_belongs_to_user_and_module(): void
    {
        $user = User::factory()->create();
        $module = Module::factory()->create();
        $et = ExpiryTracker::factory()->create(['user_id' => $user->id, 'module_id' => $module->id]);

        $this->assertInstanceOf(User::class, $et->user);
        $this->assertInstanceOf(Module::class, $et->module);
    }

    public function test_webhook_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $webhook = Webhook::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $webhook->user);
        $this->assertTrue($webhook->user->is($user));
    }

    public function test_vault_entry_belongs_to_user_and_module(): void
    {
        $user = User::factory()->create();
        $module = Module::factory()->create();
        $entry = VaultEntry::factory()->create(['user_id' => $user->id, 'module_id' => $module->id]);

        $this->assertInstanceOf(User::class, $entry->user);
        $this->assertInstanceOf(Module::class, $entry->module);
    }

    public function test_login_audit_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $audit = LoginAudit::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
            'event' => 'login',
        ]);

        $this->assertInstanceOf(User::class, $audit->user);
        $this->assertTrue($audit->user->is($user));
    }

    public function test_note_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $note->user);
    }

    public function test_attachment_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $attachment = Attachment::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $attachment->user);
    }
}
