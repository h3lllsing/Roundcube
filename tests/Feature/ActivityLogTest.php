<?php

namespace Tests\Feature;

use App\Models\User;
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
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->seed(\Database\Seeders\FeatureModuleSeeder::class);
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
        $feature = \App\Models\Feature::first();

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
        $feature = \App\Models\Feature::first();

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
        $feature = \App\Models\Feature::first();

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
        $feature = \App\Models\Feature::first();

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
        $feature = \App\Models\Feature::first();

        activity()->performedOn($feature)->causedBy($user)->event('created')->log('structure_test');
        $activity = Activity::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/activity-logs/{$activity->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertArrayHasKey('subject', $data);
        $this->assertArrayHasKey('causer', $data);
        $this->assertArrayHasKey('properties', $data);
        $this->assertEquals('App\Models\Feature', $data['subject_type']);
        $this->assertEquals($feature->id, $data['subject_id']);
        $this->assertNull($data['causer']);
    }

    public function test_activity_log_subject_label_for_feature()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = \App\Models\Feature::first();

        activity()->performedOn($feature)->causedBy($user)->event('created')->log('label_test');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/activity-logs?search=label_test');

        $response->assertStatus(200);
        $activity = $response->json('data')[0];
        $this->assertEquals($feature->name, $activity['subject']['label']);
    }
}
