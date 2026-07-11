<?php

namespace Tests\Feature;

use App\Events\MonitorCheckFailed;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\User;
use App\Notifications\MonitorCheckFailed as MonitorCheckFailedNotification;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifyMonitorFailureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
    }

    public function test_notifies_all_admin_users_when_monitor_check_fails(): void
    {
        Notification::fake();

        $admin1 = User::factory()->create();
        $admin1->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $admin2 = User::factory()->create();
        $admin2->assignRole(Role::where('slug', 'admin')->firstOrFail());
        $regular = User::factory()->create();

        $domain = Domain::factory()->create(['monitoring_url' => 'https://example.com']);
        MonitorCheckFailed::dispatch($domain, 'Domain', 'Connection timeout');

        Notification::assertSentTo($admin1, MonitorCheckFailedNotification::class);
        Notification::assertSentTo($admin2, MonitorCheckFailedNotification::class);
        Notification::assertNotSentTo($regular, MonitorCheckFailedNotification::class);
    }

    public function test_notification_contains_error_details(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $domain = Domain::factory()->create(['monitoring_url' => 'https://fail.com', 'name' => 'My Domain']);
        MonitorCheckFailed::dispatch($domain, 'Domain', 'Connection timeout');

        Notification::assertSentTo($admin, MonitorCheckFailedNotification::class, function ($notification) {
            $data = $notification->toArray(new User);

            return $data['type'] === 'monitor_check_failed'
                && $data['resource_type'] === 'Domain'
                && $data['resource_name'] === 'My Domain'
                && $data['error'] === 'Connection timeout';
        });
    }

    public function test_falls_back_to_email_when_name_is_absent(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $email = DomainEmail::factory()->create(['email' => 'test@example.com']);
        MonitorCheckFailed::dispatch($email, 'DomainEmail', 'Error');

        Notification::assertSentTo($admin, MonitorCheckFailedNotification::class, function ($notification) {
            $data = $notification->toArray(new User);

            return $data['resource_name'] === 'test@example.com';
        });
    }

    public function test_notification_stores_item_id(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $domain = Domain::factory()->create(['monitoring_url' => 'https://fail.com', 'name' => 'My Domain']);
        MonitorCheckFailed::dispatch($domain, 'Domain', 'Connection timeout');

        Notification::assertSentTo($admin, MonitorCheckFailedNotification::class, function ($notification) {
            $data = $notification->toArray(new User);

            return isset($data['item_id']) && $data['item_id'] > 0;
        });
    }

    public function test_subject_includes_opsilot_prefix(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $domain = Domain::factory()->create(['monitoring_url' => 'https://fail.com', 'name' => 'My Domain']);
        MonitorCheckFailed::dispatch($domain, 'Domain', 'Connection timeout');

        Notification::assertSentTo($admin, MonitorCheckFailedNotification::class, function ($notification) use ($admin) {
            $mail = $notification->toMail($admin);

            return str_starts_with($mail->subject, '[OpsPilot]');
        });
    }
}
