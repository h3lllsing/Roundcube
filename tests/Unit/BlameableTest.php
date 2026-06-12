<?php

namespace Tests\Unit;

use App\Models\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlameableTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_sets_created_by_and_updated_by_when_authenticated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $feature = Feature::factory()->create();

        $this->assertEquals($user->id, $feature->created_by);
        $this->assertEquals($user->id, $feature->updated_by);
    }

    public function test_creating_does_not_set_when_unauthenticated(): void
    {
        $feature = Feature::factory()->create();

        $this->assertNull($feature->created_by);
        $this->assertNull($feature->updated_by);
    }

    public function test_creating_preserves_explicit_created_by(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $other = User::factory()->create();

        $feature = Feature::factory()->create(['created_by' => $other->id]);

        $this->assertEquals($other->id, $feature->created_by);
    }

    public function test_updating_sets_updated_by_when_authenticated(): void
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create();
        $original = $feature->updated_by;

        $this->actingAs($user);
        $feature->update(['name' => 'Updated']);

        $this->assertEquals($user->id, $feature->fresh()->updated_by);
        $this->assertNotEquals($original, $feature->fresh()->updated_by);
    }

    public function test_updating_does_not_change_updated_by_when_unauthenticated(): void
    {
        $feature = Feature::factory()->create();
        $original = $feature->updated_by;

        $feature->update(['name' => 'Updated']);

        $this->assertEquals($original, $feature->fresh()->updated_by);
    }

    public function test_creator_returns_belongs_to_relation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $feature = Feature::factory()->create();

        $this->assertInstanceOf(User::class, $feature->creator);
        $this->assertEquals($user->id, $feature->creator->id);
    }

    public function test_updater_returns_belongs_to_relation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $feature = Feature::factory()->create();

        $this->assertInstanceOf(User::class, $feature->updater);
        $this->assertEquals($user->id, $feature->updater->id);
    }
}
