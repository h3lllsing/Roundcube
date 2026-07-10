<?php

namespace Tests\Feature;

use App\Mail\ExpiryTrackerReminder;
use App\Models\ExpiryTracker;
use App\Models\Module;
use App\Models\ServiceProvider;
use App\Models\User;
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

    public function test_mailable_subject_for_future_expiry(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);

        $envelope = $mailable->envelope();
        $this->assertStringContainsString('MyDomain.com', $envelope->subject);
        $this->assertStringContainsString('expires in 15 days', $envelope->subject);
    }

    public function test_mailable_subject_for_today(): void
    {
        $this->tracker->expiry_date = Carbon::today();
        $mailable = new ExpiryTrackerReminder($this->tracker, 0, $this->admin->email);

        $envelope = $mailable->envelope();
        $this->assertStringContainsString('expires today', $envelope->subject);
    }

    public function test_mailable_subject_for_overdue(): void
    {
        $this->tracker->expiry_date = Carbon::today()->subDays(3);
        $mailable = new ExpiryTrackerReminder($this->tracker, -3, $this->admin->email);

        $envelope = $mailable->envelope();
        $this->assertStringContainsString('expired 3 days ago', $envelope->subject);
    }

    public function test_mailable_renders_title_in_body(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $html = $mailable->render();

        $this->assertStringContainsString('MyDomain.com', $html);
    }

    public function test_mailable_renders_days_left(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $html = $mailable->render();

        $this->assertStringContainsString('15', $html);
    }

    public function test_mailable_renders_portal_link(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $html = $mailable->render();

        $this->assertStringContainsString(route('expiry-trackers.show', $this->tracker->id), $html);
    }

    public function test_mailable_renders_cost_and_provider(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $html = $mailable->render();

        $this->assertStringContainsString('$99.99', $html);
        $this->assertStringContainsString('TestProvider', $html);
    }

    public function test_mailable_preview_returns_html(): void
    {
        $mailable = new ExpiryTrackerReminder($this->tracker, 15, $this->admin->email);
        $preview = $mailable->renderPreview();

        $this->assertIsString($preview);
        $this->assertStringContainsString('MyDomain.com', $preview);
        $this->assertStringContainsString('Renewal Reminder', $preview);
    }
}
