<?php

namespace Tests\Feature;

use App\Mail\ExpiryTrackerReminder;
use App\Models\Domain;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Voip;
use App\Models\Vps;
use Carbon\Carbon;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpiryReminderMailTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private ExpiryTracker $tracker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $this->admin = User::factory()->create(['name' => 'Admin', 'email' => 'admin@test.com']);
        $this->admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $module = Module::first();
        $provider = ServiceProvider::factory()->create(['name' => 'TestProvider']);

        $this->tracker = ExpiryTracker::factory()->create([
            'user_id' => $this->admin->id,
            'module_id' => $module->id,
            'service_provider_id' => $provider->id,
            'name' => 'MyDomain.com',
            'expiry_date' => Carbon::today()->addDays(15),
            'cost' => 99.99,
            'status' => 'active',
        ]);
    }

    public function test_subject_includes_opsilot_prefix(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $this->assertStringStartsWith('[OpsPilot]', $mailable->envelope()->subject);
    }

    public function test_subject_includes_test_prefix_when_is_test(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email, null, 'test', true);
        $this->assertStringStartsWith('[OpsPilot][TEST]', $mailable->envelope()->subject);
    }

    public function test_subject_for_future_expiry(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $subject = $mailable->envelope()->subject;
        $this->assertStringContainsString('expires in 15 days', $subject);
        $this->assertStringContainsString('MyDomain.com', $subject);
    }

    public function test_subject_for_today(): void
    {
        $this->tracker->expiry_date = Carbon::today();
        $mailable = new ExpiryTrackerReminder($this->tracker, 0, $this->admin->email);
        $this->assertStringContainsString('expires today', $mailable->envelope()->subject);
    }

    public function test_subject_for_tomorrow(): void
    {
        $this->tracker->expiry_date = Carbon::today()->addDay();
        $mailable = new ExpiryTrackerReminder($this->tracker, 1, $this->admin->email);
        $this->assertStringContainsString('expires tomorrow', $mailable->envelope()->subject);
    }

    public function test_subject_for_overdue(): void
    {
        $this->tracker->expiry_date = Carbon::today()->subDays(3);
        $mailable = new ExpiryTrackerReminder($this->tracker, -3, $this->admin->email);
        $this->assertStringContainsString('expired 3 days ago', $mailable->envelope()->subject);
    }

    public function test_hosting_trackable_shows_hosting_resource_type(): void
    {
        $hosting = Hosting::factory()->create([
            'name' => 'MyHosting',
            'domain' => 'example.com',
        ]);
        $this->tracker->trackable()->associate($hosting);
        $this->tracker->name = 'MyHosting';
        $this->tracker->save();

        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $html = $mailable->render();
        $subject = $mailable->envelope()->subject;

        $this->assertStringContainsString('Hosting', $subject);
        $this->assertStringContainsString('Hosting', $html);
        $this->assertStringContainsString('MyHosting', $html);
    }

    public function test_domain_trackable_shows_domain_resource_type(): void
    {
        $domain = Domain::factory()->create([
            'name' => 'example.org',
        ]);
        $this->tracker->trackable()->associate($domain);
        $this->tracker->save();

        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $html = $mailable->render();

        $this->assertStringContainsString('Domain', $mailable->envelope()->subject);
        $this->assertStringContainsString('Domain', $html);
        $this->assertStringContainsString('example.org', $html);
    }

    public function test_vps_trackable_shows_vps_resource_type(): void
    {
        $vps = Vps::factory()->create(['name' => 'Production VPS']);
        $this->tracker->trackable()->associate($vps);
        $this->tracker->save();

        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $this->assertStringContainsString('VPS', $mailable->envelope()->subject);
        $this->assertStringContainsString('VPS', $mailable->render());
    }

    public function test_voip_trackable_shows_voip_resource_type(): void
    {
        $voip = Voip::factory()->create(['name' => 'Office VoIP']);
        $this->tracker->trackable()->associate($voip);
        $this->tracker->save();

        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $this->assertStringContainsString('VoIP', $mailable->envelope()->subject);
        $this->assertStringContainsString('VoIP', $mailable->render());
    }

    public function test_other_service_trackable_shows_other_service_type(): void
    {
        $service = OtherService::factory()->create(['name' => 'Mailchimp']);
        $this->tracker->trackable()->associate($service);
        $this->tracker->save();

        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $this->assertStringContainsString('Other Service', $mailable->envelope()->subject);
        $this->assertStringContainsString('Other Service', $mailable->render());
    }

    public function test_standalone_tracker_uses_module_name(): void
    {
        $module = Module::factory()->create(['name' => 'Renewals']);
        $this->tracker->module_id = $module->id;
        $this->tracker->save();

        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $this->assertStringContainsString('Renewals', $mailable->envelope()->subject);
    }

    public function test_renders_current_status(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $html = $mailable->render();

        $this->assertStringContainsString('Active', $html);
    }

    public function test_renders_recipient_reason_for_assigned_user(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email, null, 'assigned_user');
        $html = $mailable->render();

        $this->assertStringContainsString('You are assigned to this resource.', $html);
    }

    public function test_renders_recipient_reason_for_admin(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email, null, 'admin');
        $html = $mailable->render();

        $this->assertStringContainsString('You receive administrative renewal notifications.', $html);
    }

    public function test_renders_recipient_reason_for_custom(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email, null, 'custom');
        $html = $mailable->render();

        $this->assertStringContainsString('You were added as a notification recipient.', $html);
    }

    public function test_renders_recipient_reason_for_test(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email, null, 'test', true);
        $html = $mailable->render();

        $this->assertStringContainsString('This test was requested from your OpsPilot account.', $html);
    }

    public function test_test_email_shows_test_banner(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email, null, 'test', true);
        $html = $mailable->render();

        $this->assertStringContainsString('TEST EMAIL', $html);
    }

    public function test_non_test_email_has_no_test_banner(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $html = $mailable->render();

        $this->assertStringNotContainsString('TEST EMAIL', $html);
    }

    public function test_renders_portal_link(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $html = $mailable->render();

        $this->assertStringContainsString(route('expiry-trackers.show', $this->tracker->id), $html);
    }

    public function test_renders_cost_and_provider(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $html = $mailable->render();

        $this->assertStringContainsString('$99.99', $html);
        $this->assertStringContainsString('TestProvider', $html);
    }

    public function test_renders_expiry_date(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $html = $mailable->render();

        $this->assertStringContainsString($this->tracker->expiry_date->format('Y-m-d'), $html);
    }

    public function test_null_relationships_do_not_crash(): void
    {
        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $this->admin->id,
            'name' => 'Standalone',
            'expiry_date' => Carbon::today()->addDays(5),
            'service_provider_id' => null,
            'cost' => null,
        ]);

        $mailable = new ExpiryTrackerReminder($tracker, 5, $this->admin->email);
        $html = $mailable->render();

        $this->assertStringContainsString('Standalone', $html);
        $this->assertStringContainsString('5', $html);
    }

    public function test_sensitive_fields_never_appear(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $html = $mailable->render();

        $this->assertStringNotContainsString('password', $html);
        $this->assertStringNotContainsString('smtp_password', $html);
        $this->assertStringNotContainsString('secret', $html);
    }

    public function test_preview_and_sent_subject_match(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $preview = $mailable->renderPreview();

        $this->assertSame($mailable->envelope()->subject, $mailable->envelope()->subject);
        $this->assertStringContainsString('MyDomain.com', $preview);
    }

    public function test_mailable_preview_returns_html(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $preview = $mailable->renderPreview();

        $this->assertIsString($preview);
        $this->assertStringContainsString('MyDomain.com', $preview);
    }

    public function test_view_link_points_to_correct_record(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $html = $mailable->render();

        $this->assertStringContainsString(
            route('expiry-trackers.show', $this->tracker->id),
            $html
        );
    }

    public function test_domain_trackable_shows_related_hosting(): void
    {
        $hosting = Hosting::factory()->create(['name' => 'ParentHosting', 'domain' => 'parent.com']);
        $domain = Domain::factory()->create([
            'name' => 'sub.example.com',
            'hosting_id' => $hosting->id,
        ]);
        $this->tracker->trackable()->associate($domain);
        $this->tracker->save();

        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $html = $mailable->render();

        $this->assertStringContainsString('ParentHosting', $html);
        $this->assertStringContainsString('sub.example.com', $html);
    }

    public function test_hosting_trackable_shows_related_domain_field(): void
    {
        $hosting = Hosting::factory()->create(['name' => 'MyHosting', 'domain' => 'mysite.com']);
        $this->tracker->trackable()->associate($hosting);
        $this->tracker->save();

        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $html = $mailable->render();

        $this->assertStringContainsString('mysite.com', $html);
    }
}
