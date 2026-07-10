<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\VaultEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VaultEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_encrypt_and_decrypt_password(): void
    {
        $user = User::factory()->create();
        $entry = new VaultEntry(['user_id' => $user->id, 'service_name' => 'Test']);
        $entry->encryptPassword('my-secret-password');
        $entry->save();

        $this->assertEquals('my-secret-password', $entry->fresh()->decryptPassword());
    }

    public function test_password_masked_attribute_masks_password(): void
    {
        $user = User::factory()->create();
        $entry = new VaultEntry(['user_id' => $user->id, 'service_name' => 'Test']);
        $entry->encryptPassword('short');
        $entry->save();

        $masked = $entry->fresh()->password_masked;
        $this->assertStringContainsString('•', $masked);
    }

    public function test_password_masked_returns_fallback_on_exception(): void
    {
        $user = User::factory()->create();
        $entry = new VaultEntry(['user_id' => $user->id, 'service_name' => 'Test']);
        $entry->encrypted_password = 'invalid-base64-data';
        $entry->save();

        $this->assertEquals('••••••', $entry->fresh()->password_masked);
    }

    public function test_decrypt_empty_password_throws_exception(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Encrypted password is empty');

        $entry = new VaultEntry;
        $entry->decryptPassword();
    }
}
