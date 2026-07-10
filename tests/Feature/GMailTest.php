<?php

namespace Tests\Feature;

use App\Models\GMail;
use App\Models\Module;
use App\Models\User;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GMailTest extends TestCase
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
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        return $user;
    }

    public function test_create(): void
    {
        $this->actingAs($this->superAdmin())
            ->postJson('/api/g-mails', ['user_name' => 'testuser', 'emails_address' => 'test@example.com'])
            ->assertStatus(201)
            ->assertJsonPath('data.user_name', 'testuser');
    }

    public function test_list(): void
    {
        $u = $this->superAdmin();
        GMail::factory()->count(2)->create(['user_id' => $u->id]);
        $this->actingAs($u)->getJson('/api/g-mails')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_show(): void
    {
        $u = $this->superAdmin();
        $g = GMail::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->getJson("/api/g-mails/{$g->id}")->assertOk()->assertJsonPath('data.id', $g->id);
    }

    public function test_update(): void
    {
        $u = $this->superAdmin();
        $g = GMail::factory()->create(['user_id' => $u->id, 'user_name' => 'old']);
        $this->actingAs($u)->putJson("/api/g-mails/{$g->id}", ['user_name' => 'new'])->assertOk()->assertJsonPath('data.user_name', 'new');
    }

    public function test_delete(): void
    {
        $u = $this->superAdmin();
        $g = GMail::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->deleteJson("/api/g-mails/{$g->id}")->assertOk()->assertJsonPath('message', 'G-Mail deleted');
        $this->assertSoftDeleted($g);
    }

    public function test_web_index(): void
    {
        $u = $this->superAdmin();
        GMail::factory()->count(3)->create(['user_id' => $u->id]);
        $this->actingAs($u)->get(route('g-mails.index'))->assertOk();
    }

    public function test_web_create(): void
    {
        $this->actingAs($this->superAdmin())->get(route('g-mails.create'))->assertOk();
    }

    public function test_web_store(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('g-mails.store'), ['user_name' => 'webuser', 'emails_address' => 'web@example.com'])
            ->assertRedirect(route('g-mails.index'));
        $this->assertDatabaseHas('g_mails', ['user_name' => 'webuser']);
    }

    public function test_web_show(): void
    {
        $u = $this->superAdmin();
        $g = GMail::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->get(route('g-mails.show', $g->id))->assertOk();
    }

    public function test_web_edit(): void
    {
        $u = $this->superAdmin();
        $g = GMail::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->get(route('g-mails.edit', $g->id))->assertOk();
    }

    public function test_web_update(): void
    {
        $u = $this->superAdmin();
        $g = GMail::factory()->create(['user_id' => $u->id, 'user_name' => 'old']);
        $this->actingAs($u)
            ->put(route('g-mails.update', $g->id), ['user_name' => 'updated'])
            ->assertRedirect(route('g-mails.index'));
        $this->assertDatabaseHas('g_mails', ['user_name' => 'updated']);
    }

    public function test_web_delete(): void
    {
        $u = $this->superAdmin();
        $g = GMail::factory()->create(['user_id' => $u->id]);
        $this->actingAs($u)->delete(route('g-mails.destroy', $g->id))->assertRedirect(route('g-mails.index'));
        $this->assertSoftDeleted($g);
    }

    public function test_export_g_mails(): void
    {
        $u = $this->superAdmin();
        GMail::factory()->count(2)->create(['user_id' => $u->id]);
        $this->actingAs($u)->get(route('export', 'g-mails'))->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
