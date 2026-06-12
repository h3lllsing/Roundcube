<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
    }

    public function test_send_reset_link_valid_email()
    {
        Notification::fake();

        User::factory()->create(['email' => 'user@example.com']);

        $response = $this->postJson('/api/forgot-password', [
            'email' => 'user@example.com',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message']);
    }

    public function test_send_reset_link_invalid_email()
    {
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422);
    }

    public function test_send_reset_link_validation()
    {
        $this->postJson('/api/forgot-password', [])
            ->assertJsonValidationErrorFor('email');
    }

    public function test_reset_password_with_valid_token()
    {
        $user = User::factory()->create(['email' => 'resetme@example.com']);

        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'resetme@example.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Your password has been reset.');

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_reset_password_with_invalid_token()
    {
        User::factory()->create(['email' => 'fail@example.com']);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'fail@example.com',
            'token' => 'invalid-token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422);
    }

    public function test_reset_password_validation()
    {
        $this->postJson('/api/reset-password', [])
            ->assertJsonValidationErrors(['email', 'token', 'password']);
    }
}
