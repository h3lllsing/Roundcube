<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Webhook;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
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
        $this->seed(TyroSeeder::class);
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
                'events' => ['expiring_soon', 'task.created'],
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

        $this->assertSoftDeleted($webhook);
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

    public function test_non_admin_lists_only_own_webhooks(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        Webhook::factory()->create(['user_id' => $owner->id, 'name' => 'My Hook']);
        Webhook::factory()->create(['user_id' => $other->id, 'name' => 'Their Hook']);

        $token = $owner->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/webhooks');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('My Hook', $response->json('data.0.name'));
    }

    public function test_other_user_cannot_test_webhook(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $webhook = Webhook::factory()->create(['user_id' => $owner->id]);

        $token = $other->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/webhooks/{$webhook->id}/test");

        $response->assertForbidden();
    }

    public function test_web_webhook_index_search(): void
    {
        $user = $this->superAdmin();
        Webhook::factory()->create(['user_id' => $user->id, 'name' => 'SearchableHook']);

        $this->actingAs($user)
            ->get(route('webhooks.index', ['search' => 'Searchable']))
            ->assertStatus(200);
    }

    public function test_web_webhook_test_endpoint(): void
    {
        $user = $this->superAdmin();
        $webhook = Webhook::factory()->create(['user_id' => $user->id, 'events' => ['test']]);
        Http::fake();

        $this->actingAs($user)
            ->post(route('webhooks.test', $webhook->id))
            ->assertSessionHas('success');
    }

    public function test_web_webhook_test_forbidden(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole(Role::where('slug', 'user')->firstOrFail());

        $webhook = Webhook::factory()->create(['user_id' => $owner->id]);

        $other = User::factory()->create();
        $other->assignRole(Role::where('slug', 'user')->firstOrFail());

        $this->actingAs($other)
            ->post(route('webhooks.test', $webhook->id))
            ->assertStatus(403);
    }

    public function test_api_webhook_search_filter(): void
    {
        $user = $this->superAdmin();
        Webhook::factory()->create(['user_id' => $user->id, 'name' => 'ApiSearchable']);
        Webhook::factory()->create(['user_id' => $user->id, 'name' => 'ApiOther']);

        $token = $user->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/webhooks?search=ApiSearchable');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertContains('ApiSearchable', $names);
        $this->assertNotContains('ApiOther', $names);
    }
}
