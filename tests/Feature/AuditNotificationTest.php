<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\User;
use App\Models\UserModulePermission;
use App\Notifications\AuditEventNotification;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AuditNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $viewer;
    private User $regular;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $userRole = Role::where('slug', 'user')->firstOrFail();

        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->viewer = User::factory()->create();
        $this->viewer->assignRole($userRole);

        $this->regular = User::factory()->create();
        $this->regular->assignRole($userRole);

        $auditModule = Module::where('slug', 'audit')->firstOrFail();
        UserModulePermission::create([
            'user_id' => $this->viewer->id,
            'module_id' => $auditModule->id,
            'can_read' => true,
        ]);
    }

    public function test_notifies_super_admin_on_critical_action(): void
    {
        Notification::fake();

        activity()->event('soft_delete')
            ->causedBy($this->admin)
            ->performedOn($this->admin)
            ->log('test soft delete');

        Notification::assertSentTo($this->admin, AuditEventNotification::class);
    }

    public function test_notifies_audit_viewer_on_critical_action(): void
    {
        Notification::fake();

        activity()->event('force_delete')
            ->causedBy($this->admin)
            ->performedOn($this->admin)
            ->log('test force delete');

        Notification::assertSentTo($this->viewer, AuditEventNotification::class);
    }

    public function test_does_not_notify_regular_user(): void
    {
        Notification::fake();

        activity()->event('assign')
            ->causedBy($this->admin)
            ->performedOn($this->admin)
            ->log('test assign');

        Notification::assertNotSentTo($this->regular, AuditEventNotification::class);
    }

    public function test_does_not_notify_on_non_critical_action(): void
    {
        Notification::fake();

        activity()->event('created')
            ->causedBy($this->admin)
            ->performedOn($this->admin)
            ->log('test created');

        Notification::assertNotSentTo($this->admin, AuditEventNotification::class);
    }

    public function test_notification_content_has_no_sensitive_data(): void
    {
        Notification::fake();

        activity()->event('soft_delete')
            ->causedBy($this->admin)
            ->performedOn($this->admin)
            ->log('test content');

        Notification::assertSentTo($this->admin, AuditEventNotification::class, function ($notification) {
            $data = $notification->toArray(new User);

            return !isset($data['password'])
                && !isset($data['token'])
                && !isset($data['remember_token'])
                && isset($data['action'])
                && isset($data['causer_name'])
                && isset($data['timestamp']);
        });
    }

    public function test_notification_via_respects_env_config(): void
    {
        $activity = Activity::create([
            'description' => 'test',
            'event' => 'soft_delete',
            'subject_type' => User::class,
            'subject_id' => $this->admin->id,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id,
        ]);

        $notification = new AuditEventNotification($activity);

        putenv('AUDIT_NOTIFY_MAIL=false');
        $channels = $notification->via($this->admin);
        $this->assertNotContains('mail', $channels);
        $this->assertContains('database', $channels);

        putenv('AUDIT_NOTIFY_MAIL=true');
        $channels = $notification->via($this->admin);
        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_notifies_on_all_critical_actions(): void
    {
        $critical = ['assign', 'revoke', 'soft_delete', 'restored', 'force_delete'];

        foreach ($critical as $event) {
            Notification::fake();

            activity()->event($event)
                ->causedBy($this->admin)
                ->performedOn($this->admin)
                ->log("test {$event}");

            Notification::assertSentTo($this->admin, AuditEventNotification::class);
        }
    }
}
