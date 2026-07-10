<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\User;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
    }

    public function test_list_activity_logs_as_super_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/activity-logs');

        $response->assertStatus(200);
    }

    public function test_activity_logs_forbidden_for_non_super_admin()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/activity-logs');

        $response->assertStatus(403);
    }

    public function test_activity_log_filter_by_event()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        activity()->performedOn($feature)->causedBy($user)->event('created')->log('test_created');
        activity()->performedOn($feature)->causedBy($user)->event('updated')->log('test_updated');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/activity-logs?event=updated');

        $response->assertStatus(200);
        $this->assertStringContainsString('test_updated', $response->getContent());
    }

    public function test_activity_log_show()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        activity()->performedOn($feature)->causedBy($user)->event('created')->log('show_test');
        $activity = Activity::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/activity-logs/{$activity->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'description', 'event', 'causer', 'subject']]);
    }

    public function test_activity_log_search()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        activity()->performedOn($feature)->causedBy($user)->event('created')->log('unique_search_term');
        activity()->performedOn($feature)->causedBy($user)->event('created')->log('other_term');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/activity-logs?search=unique_search');

        $response->assertStatus(200);
        $this->assertStringContainsString('unique_search_term', $response->getContent());
        $this->assertStringNotContainsString('other_term', $response->getContent());
    }

    public function test_activity_log_sort_by_event()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        activity()->performedOn($feature)->causedBy($user)->event('created')->log('sort_created');
        activity()->performedOn($feature)->causedBy($user)->event('updated')->log('sort_updated');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/activity-logs?sort_by=event&sort_order=asc');

        $response->assertStatus(200);
    }

    public function test_show_nonexistent_activity_log_returns_404()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/activity-logs/99999');

        $response->assertStatus(404);
    }

    public function test_activity_log_structure_includes_subject()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        $activity = activity()->performedOn($feature)->causedBy($user)->event('created')->log('structure_test');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/activity-logs/{$activity->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertArrayHasKey('subject', $data);
        $this->assertArrayHasKey('causer', $data);
        $this->assertArrayHasKey('properties', $data);
        $this->assertEquals($feature->getMorphClass(), $data['subject_type']);
        $this->assertEquals($feature->id, $data['subject_id']);
        $this->assertNotNull($data['causer']);
        $this->assertEquals($user->id, $data['causer']['id']);
        $this->assertEquals($user->name, $data['causer']['name']);
        $this->assertEquals($user->email, $data['causer']['email']);
    }

    public function test_activity_log_subject_label_for_feature()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        activity()->performedOn($feature)->causedBy($user)->event('created')->log('label_test');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/activity-logs?search=label_test');

        $response->assertStatus(200);
        $activity = $response->json('data')[0];
        $this->assertEquals($feature->name, $activity['subject']['label']);
    }

    public function test_activity_log_filter_by_subject_type()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        activity()->performedOn($feature)->causedBy($user)->event('created')->log('subject_type_test');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/activity-logs?subject_type='.$feature->getMorphClass());

        $response->assertStatus(200);
        $this->assertStringContainsString('subject_type_test', $response->getContent());
    }

    public function test_activity_log_filter_by_causer_id()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        activity()->performedOn($feature)->causedBy($user)->event('created')->log('causer_filter_test');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/activity-logs?causer_id='.$user->id);

        $response->assertStatus(200);
        $this->assertStringContainsString('causer_filter_test', $response->getContent());
    }

    public function test_activity_log_filter_by_date_range()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        activity()->performedOn($feature)->causedBy($user)->event('created')->log('date_range_test');

        $today = now()->format('Y-m-d');
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/activity-logs?date_from=$today&date_to=$today&per_page=100");

        $response->assertStatus(200);
        $this->assertStringContainsString('date_range_test', $response->getContent());
    }

    public function test_activity_log_invalid_sort_by_falls_back()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/activity-logs?sort_by=invalid_field');

        $response->assertStatus(200);
    }

    public function test_activity_log_invalid_sort_order_falls_back()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/activity-logs?sort_order=invalid');

        $response->assertStatus(200);
    }

    public function test_activity_log_show_forbidden_for_non_super_admin()
    {
        $user = User::factory()->create();
        $log = activity()->performedOn($user)->causedBy($user)->event('created')->log('test_log');
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/activity-logs/{$log->id}");

        $response->assertStatus(403);
    }

    public function test_web_activity_log_filters(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $feature = Feature::first();

        activity()->performedOn($feature)->causedBy($user)->event('created')->log('web_filter_test');

        $today = now()->format('Y-m-d');
        $this->actingAs($user);
        $this->get(route('activity-logs.index', ['causer_id' => $user->id]))->assertStatus(200);
        $this->get(route('activity-logs.index', ['date_from' => $today]))->assertStatus(200);
        $this->get(route('activity-logs.index', ['date_to' => $today]))->assertStatus(200);
    }
}
