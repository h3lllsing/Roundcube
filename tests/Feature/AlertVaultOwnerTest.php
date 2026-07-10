<?php

namespace Tests\Feature;

use App\Events\VaultPasswordRevealed;
use App\Models\User;
use App\Models\VaultEntry;
use App\Notifications\VaultPasswordRevealed as VaultPasswordRevealedNotification;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AlertVaultOwnerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
    }

    public function test_notifies_owner_when_another_user_reveals_password(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $causer = User::factory()->create();
        $entry = VaultEntry::factory()->create(['user_id' => $owner->id, 'service_name' => 'AWS Console']);

        VaultPasswordRevealed::dispatch($entry, $causer);

        Notification::assertSentTo($owner, VaultPasswordRevealedNotification::class);
    }

    public function test_does_not_notify_when_owner_is_causer(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $entry = VaultEntry::factory()->create(['user_id' => $owner->id]);

        VaultPasswordRevealed::dispatch($entry, $owner);

        Notification::assertNothingSent();
    }

    public function test_does_not_notify_when_entry_has_no_owner(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $causer = User::factory()->create();
        $entry = VaultEntry::factory()->create(['user_id' => $owner->id]);
        $owner->delete();
        $entry->unsetRelation('user');

        VaultPasswordRevealed::dispatch($entry, $causer);

        Notification::assertNothingSent();
    }

    public function test_notification_contains_service_name_and_revealed_by(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $causer = User::factory()->create(['name' => 'John Doe']);
        $entry = VaultEntry::factory()->create(['user_id' => $owner->id, 'service_name' => 'GitHub Token']);

        VaultPasswordRevealed::dispatch($entry, $causer);

        Notification::assertSentTo($owner, VaultPasswordRevealedNotification::class, function ($notification) {
            $data = $notification->toArray(new User);

            return $data['type'] === 'vault_password_revealed'
                && $data['service'] === 'GitHub Token'
                && $data['revealed_by'] === 'John Doe';
        });
    }

    public function test_uses_system_when_causer_is_null(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $entry = VaultEntry::factory()->create(['user_id' => $owner->id, 'service_name' => 'API Key']);

        VaultPasswordRevealed::dispatch($entry, null);

        Notification::assertSentTo($owner, VaultPasswordRevealedNotification::class, function ($notification) {
            $data = $notification->toArray(new User);

            return $data['revealed_by'] === 'System';
        });
    }
}
