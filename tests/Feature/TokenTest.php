<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-init')->plainTextToken;
    }

    public function test_index_lists_tokens()
    {
        $this->user->createToken('token-a');
        $this->user->createToken('token-b');

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/tokens');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertContains('token-a', $names);
        $this->assertContains('token-b', $names);
    }

    public function test_store_creates_token()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/tokens', ['name' => 'My New Token']);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'plain_text']])
            ->assertJsonPath('data.name', 'My New Token');
    }

    public function test_store_validation()
    {
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/tokens', [])
            ->assertJsonValidationErrorFor('name');
    }

    public function test_destroy_revokes_token()
    {
        $newToken = $this->user->createToken('revoke-me');
        $tokenId = $newToken->accessToken->id;

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/tokens/{$tokenId}");

        $response->assertOk()
            ->assertJsonPath('message', 'Token revoked successfully');

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }

    public function test_destroy_own_token_only()
    {
        $otherUser = User::factory()->create();
        $otherToken = $otherUser->createToken('other-token');
        $otherTokenId = $otherToken->accessToken->id;

        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/tokens/{$otherTokenId}")
            ->assertStatus(404)
            ->assertJsonPath('message', 'Token not found');

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $otherTokenId]);
    }

    public function test_destroy_nonexistent_returns_404()
    {
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson('/api/tokens/99999')
            ->assertStatus(404);
    }

    public function test_requires_authentication()
    {
        $this->getJson('/api/tokens')->assertUnauthorized();
        $this->postJson('/api/tokens', ['name' => 'x'])->assertUnauthorized();
        $this->deleteJson('/api/tokens/1')->assertUnauthorized();
    }
}
