<?php

namespace Tests\Unit;

use App\Http\Resources\ActivityLogResource;
use App\Models\Feature;
use App\Models\Module;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use App\Models\VaultEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_subject_label(): void
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create(['name' => 'My Feature']);
        activity()->performedOn($feature)->causedBy($user)->event('created')->log('test');

        $activity = Activity::where('description', 'test')->first()->load('subject', 'causer');
        $data = (new ActivityLogResource($activity))->resolve();

        $this->assertEquals('My Feature', $data['subject']['label']);
    }

    public function test_module_subject_label(): void
    {
        $user = User::factory()->create();
        $module = Module::factory()->create();
        activity()->performedOn($module)->causedBy($user)->event('created')->log('test');

        $activity = Activity::where('description', 'test')->first()->load('subject', 'causer');
        $data = (new ActivityLogResource($activity))->resolve();

        $this->assertEquals($activity->subject->name, $data['subject']['label']);
    }

    public function test_task_subject_label(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        activity()->performedOn($task)->causedBy($user)->event('created')->log('test');

        $activity = Activity::where('description', 'test')->first()->load('subject', 'causer');
        $data = (new ActivityLogResource($activity))->resolve();

        $this->assertEquals($activity->subject->title, $data['subject']['label']);
    }

    public function test_note_subject_label(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->create();
        activity()->performedOn($note)->causedBy($user)->event('created')->log('test');

        $activity = Activity::where('description', 'test')->first()->load('subject', 'causer');
        $data = (new ActivityLogResource($activity))->resolve();

        $this->assertEquals('Note #'.$note->id, $data['subject']['label']);
    }

    public function test_vault_entry_subject_label(): void
    {
        $user = User::factory()->create();
        $entry = VaultEntry::factory()->create();
        activity()->performedOn($entry)->causedBy($user)->event('created')->log('test');

        $activity = Activity::where('description', 'test')->first()->load('subject', 'causer');
        $data = (new ActivityLogResource($activity))->resolve();

        $this->assertEquals($activity->subject->service_name, $data['subject']['label']);
    }

    public function test_basic_fields(): void
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create(['name' => 'Test Feature']);
        activity()->performedOn($feature)->causedBy($user)->event('created')->log('test');

        $activity = Activity::where('description', 'test')->first()->load('subject', 'causer');
        $data = (new ActivityLogResource($activity))->resolve();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('log_name', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('event', $data);
        $this->assertArrayHasKey('properties', $data);
        $this->assertArrayHasKey('causer', $data);
        $this->assertArrayHasKey('id', $data['causer']);
        $this->assertArrayHasKey('name', $data['causer']);
        $this->assertArrayHasKey('email', $data['causer']);
    }
}
