<?php

namespace Tests\Unit;

use App\Notifications\VaultPasswordRevealed;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VaultPasswordRevealedNotificationTest extends TestCase
{
    #[Test]
    public function via_returns_database_channel(): void
    {
        $notification = new VaultPasswordRevealed('AWS Console', 'admin@example.com');

        $this->assertSame(['database'], $notification->via(new \stdClass));
    }

    #[Test]
    public function to_array_returns_correct_structure(): void
    {
        $notification = new VaultPasswordRevealed('AWS Console', 'admin@example.com');

        $result = $notification->toArray(new \stdClass);

        $this->assertSame('vault_password_revealed', $result['type']);
        $this->assertSame('AWS Console', $result['service']);
        $this->assertSame('admin@example.com', $result['revealed_by']);
    }
}
