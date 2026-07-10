<?php

namespace Tests\Feature;

use App\Events\ExpiryWarningTriggered;
use App\Models\Domain;
use App\Models\User;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class LogExpiryWarningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
    }

    public function test_logs_activity_when_expiry_warning_triggered(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $domain = Domain::factory()->create(['name' => 'example.com', 'user_id' => $user->id]);

        ExpiryWarningTriggered::dispatch($domain, 'Domain', $user, 7);

        $activity = Activity::where('event', 'expiry_warning')->first();
        $this->assertNotNull($activity);
        $this->assertEquals('expiry_warning_sent', $activity->description);
        $this->assertEquals($user->id, $activity->causer_id);
        $this->assertEquals('domain', $activity->subject_type);
        $this->assertEquals($domain->id, $activity->subject_id);
    }

    public function test_stores_days_remaining_in_properties(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $domain = Domain::factory()->create(['name' => 'example.com', 'user_id' => $user->id]);

        ExpiryWarningTriggered::dispatch($domain, 'Domain', $user, 30);

        $activity = Activity::where('event', 'expiry_warning')->first();
        $props = $activity->properties->toArray();
        $this->assertEquals('Domain', $props['type']);
        $this->assertEquals(30, $props['days_remaining']);
    }
}
