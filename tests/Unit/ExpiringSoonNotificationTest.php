<?php

namespace Tests\Unit;

use App\Models\User;
use App\Notifications\ExpiringSoon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpiringSoonNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_to_mail_expires_today(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $notification = new ExpiringSoon(
            itemType: 'App\Models\Domain',
            itemId: 1,
            name: 'example.com',
            entityType: 'Domain',
            expiryDate: now()->toDateString(),
            threshold: '1_day',
            daysRemaining: 0,
        );

        $mail = $notification->toMail($user);

        $this->assertStringContainsString('expires today', $mail->render());
    }

    public function test_to_mail_overdue(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $notification = new ExpiringSoon(
            itemType: 'App\Models\Domain',
            itemId: 1,
            name: 'overdue.com',
            entityType: 'Domain',
            expiryDate: now()->subDays(3)->toDateString(),
            threshold: 'overdue',
            daysRemaining: -3,
        );

        $mail = $notification->toMail($user);

        $content = $mail->render();
        $this->assertStringContainsString('Overdue', $mail->subject);
        $this->assertStringContainsString('3 day(s) ago', $content);
    }

    public function test_to_mail_expiring_soon(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $notification = new ExpiringSoon(
            itemType: 'App\Models\Domain',
            itemId: 1,
            name: 'soon.com',
            entityType: 'Domain',
            expiryDate: now()->addDays(5)->toDateString(),
            threshold: '7_days',
            daysRemaining: 5,
        );

        $mail = $notification->toMail($user);

        $content = $mail->render();
        $this->assertStringContainsString('Expiring Soon', $mail->subject);
        $this->assertStringContainsString('5 day(s)', $content);
    }

    public function test_via_returns_database_and_mail_channels(): void
    {
        $notification = new ExpiringSoon('type', 1, 'name', 'Type', '2026-01-01', '30_days', 15);
        $this->assertEquals(['database', 'mail'], $notification->via(new User));
    }

    public function test_to_array_returns_expected_data(): void
    {
        $notification = new ExpiringSoon('App\Models\Domain', 42, 'example.com', 'Domain', '2026-06-15', '7_days', 3);

        $result = $notification->toArray(new User);

        $this->assertSame('expiring_soon', $result['type']);
        $this->assertSame('App\Models\Domain', $result['item_type']);
        $this->assertSame(42, $result['item_id']);
        $this->assertSame('example.com', $result['name']);
        $this->assertSame('Domain', $result['entity_type']);
        $this->assertSame('2026-06-15', $result['expiry_date']);
        $this->assertSame('7_days', $result['threshold']);
        $this->assertSame(3, $result['days_remaining']);
    }
}
