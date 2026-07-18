<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\EmailAccount;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeleteRestoreTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $emailAccount;
    private $domain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TyroSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();

        $this->admin = \App\Models\User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->user = \App\Models\User::factory()->create();

        $this->domain = Domain::create([
            'name' => 'softdelete-test.com',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $this->emailAccount = EmailAccount::create([
            'domain_id' => $this->domain->id,
            'email' => 'softdelete-test@softdelete-test.com',
            'password' => 'testpass123',
            'imap_host' => 'imap.test.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_soft_delete_and_restore_email_account()
    {
        $id = $this->emailAccount->id;

        $this->actingAs($this->admin)
            ->deleteJson("/email_accounts/{$id}")
            ->assertRedirect();

        $this->assertSoftDeleted($this->emailAccount);

        $trashed = EmailAccount::withTrashed()->find($id);
        $this->assertNotNull($trashed->deleted_by);
        $this->assertEquals($this->admin->id, $trashed->deleted_by);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => EmailAccount::class,
            'subject_id' => $id,
            'event' => 'soft_delete',
        ]);

        $this->actingAs($this->admin)
            ->postJson("/email-accounts/{$id}/restore")
            ->assertRedirect();

        $this->assertNotSoftDeleted($this->emailAccount);

        $restored = EmailAccount::find($id);
        $this->assertNull($restored->deleted_by);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => EmailAccount::class,
            'subject_id' => $id,
            'event' => 'restore',
        ]);
    }

    public function test_admin_can_force_delete_email_account()
    {
        $id = $this->emailAccount->id;

        $this->actingAs($this->admin)
            ->deleteJson("/email_accounts/{$id}")
            ->assertRedirect();

        $this->assertSoftDeleted($this->emailAccount);

        $this->actingAs($this->admin)
            ->deleteJson("/email-accounts/{$id}/force-delete")
            ->assertRedirect();

        $this->assertModelMissing($this->emailAccount);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => EmailAccount::class,
            'subject_id' => $id,
            'event' => 'force_delete',
        ]);
    }

    public function test_user_cannot_restore()
    {
        $id = $this->emailAccount->id;

        $this->actingAs($this->admin)
            ->deleteJson("/email_accounts/{$id}")
            ->assertRedirect();

        $this->actingAs($this->user)
            ->postJson("/email-accounts/{$id}/restore")
            ->assertForbidden();
    }

    public function test_user_cannot_force_delete()
    {
        $id = $this->emailAccount->id;

        $this->actingAs($this->admin)
            ->deleteJson("/email_accounts/{$id}")
            ->assertRedirect();

        $this->actingAs($this->user)
            ->deleteJson("/email-accounts/{$id}/force-delete")
            ->assertForbidden();
    }

    public function test_admin_can_soft_delete_and_restore_domain()
    {
        $id = $this->domain->id;

        $this->actingAs($this->admin)
            ->deleteJson("/domains/{$id}")
            ->assertRedirect();

        $this->assertSoftDeleted($this->domain);

        $trashed = Domain::withTrashed()->find($id);
        $this->assertNotNull($trashed->deleted_by);
        $this->assertEquals($this->admin->id, $trashed->deleted_by);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Domain::class,
            'subject_id' => $id,
            'event' => 'soft_delete',
        ]);

        $this->actingAs($this->admin)
            ->postJson("/domains/{$id}/restore")
            ->assertRedirect();

        $this->assertNotSoftDeleted($this->domain);

        $restored = Domain::find($id);
        $this->assertNull($restored->deleted_by);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Domain::class,
            'subject_id' => $id,
            'event' => 'restore',
        ]);
    }

    public function test_admin_can_force_delete_domain()
    {
        $id = $this->domain->id;

        $this->actingAs($this->admin)
            ->deleteJson("/domains/{$id}")
            ->assertRedirect();

        $this->assertSoftDeleted($this->domain);

        $this->actingAs($this->admin)
            ->deleteJson("/domains/{$id}/force-delete")
            ->assertRedirect();

        $this->assertModelMissing($this->domain);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Domain::class,
            'subject_id' => $id,
            'event' => 'force_delete',
        ]);
    }
}
