<?php

namespace Tests\Feature;

use App\Models\Webhook;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        return $user;
    }

    public function test_create_webhook(): void
    {
        $user = $this->superAdmin();

        $response = $this->actingAs($user)
            ->postJson('/api/webhooks', [
                'name' => 'My Webhook',
                'url' => 'https://hooks.example.com/notify',
                'events' => ['expiring_soon', 'task_assigned'],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'name', 'url', 'events']])
            ->assertJsonPath('data.name', 'My Webhook');
    }

    public function test_list_webhooks(): void
    {
        $user = $this->superAdmin();
        Webhook::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->getJson('/api/webhooks')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_show_webhook(): void
    {
        $user = $this->superAdmin();
        $webhook = Webhook::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->getJson("/api/webhooks/{$webhook->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $webhook->id);
    }

    public function test_update_webhook(): void
    {
        $user = $this->superAdmin();
        $webhook = Webhook::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

        $this->actingAs($user)
            ->putJson("/api/webhooks/{$webhook->id}", ['name' => 'Updated Name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_delete_webhook(): void
    {
        $user = $this->superAdmin();
        $webhook = Webhook::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->deleteJson("/api/webhooks/{$webhook->id}")
            ->assertOk();

        $this->assertModelMissing($webhook);
    }

    public function test_other_user_cannot_modify(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole(Role::where('slug', 'user')->firstOrFail());

        $other = User::factory()->create();
        $other->assignRole(Role::where('slug', 'user')->firstOrFail());

        $webhook = Webhook::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
            ->getJson("/api/webhooks/{$webhook->id}")
            ->assertForbidden();

        $this->actingAs($other)
            ->putJson("/api/webhooks/{$webhook->id}", ['name' => 'hacked'])
            ->assertForbidden();

        $this->actingAs($other)
            ->deleteJson("/api/webhooks/{$webhook->id}")
            ->assertForbidden();
    }

    public function test_test_endpoint(): void
    {
        $user = $this->superAdmin();
        $webhook = Webhook::factory()->create(['user_id' => $user->id, 'events' => ['test']]);

        Http::fake();

        $this->actingAs($user)
            ->postJson("/api/webhooks/{$webhook->id}/test")
            ->assertOk()
            ->assertJsonPath('message', 'Test webhook fired');

        Http::assertSent(function ($request) use ($webhook) {
            return $request->url() === $webhook->url
                && $request['event'] === 'test'
                && isset($request['payload']['event'])
                && $request['payload']['message'] === 'Test webhook';
        });
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/webhooks')->assertUnauthorized();
    }
}
