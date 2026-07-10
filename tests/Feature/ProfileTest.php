<?php

namespace Tests\Feature;

use App\Models\User;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    public function test_show_profile()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/profile');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'roles', 'permissions']])
            ->assertJsonPath('data.id', $this->user->id);
    }

    public function test_update_profile_name_and_email()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson('/api/profile', [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.email', 'updated@example.com');

        $this->assertEquals('Updated Name', $this->user->fresh()->name);
    }

    public function test_update_profile_password()
    {
        $this->user->password = Hash::make('CurrentPass1');
        $this->user->save();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson('/api/profile', [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'current_password' => 'CurrentPass1',
                'password' => 'NewPass123',
                'password_confirmation' => 'NewPass123',
            ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('NewPass123', $this->user->fresh()->password));
    }

    public function test_update_validation()
    {
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson('/api/profile', [
                'name' => '',
                'email' => 'not-email',
            ])
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_requires_authentication()
    {
        $this->getJson('/api/profile')->assertUnauthorized();
        $this->putJson('/api/profile', [])->assertUnauthorized();
    }
}
