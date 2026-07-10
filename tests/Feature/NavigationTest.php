<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
    }

    public function test_super_admin_sees_administration()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Administration');
    }

    public function test_admin_does_not_see_administration()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'admin')->firstOrFail());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Administration');
    }

    public function test_user_role_does_not_see_administration()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'user')->firstOrFail());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Administration');
    }

    public function test_customer_does_not_see_administration()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'customer')->firstOrFail());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Administration');
    }

    public function test_new_labels_appear_for_super_admin()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Service Providers');
        $response->assertSee('Hosting');
        $response->assertSee('Domain Emails');
        $response->assertSee('VPS Accounts');
        $response->assertSee('Renewals');
        $response->assertSee('My Credentials');
        $response->assertSee('Shared Credentials');
        $response->assertSee('Task Management');
        $response->assertSee('My Access');
        $response->assertSee('Knowledge Base');
        $response->assertSee('Activity Logs');
        $response->assertSee('API Access');
    }

    public function test_old_labels_do_not_appear_for_super_admin()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('>Expiry Trackers<');
        $response->assertDontSee('>API Tokens<');
        $response->assertDontSee('>Import CSV<');
    }

    public function test_notes_hidden_from_sidebar_but_accessible_via_command_palette()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('>Notes<');
        $response->assertSee("label:'Notes'", false);
    }

    public function test_credentials_group_appears_when_vault_access_exists()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Credentials');
    }

    public function test_knowledge_base_appears_under_account()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Knowledge Base');
    }

    public function test_reports_group_appears_for_super_admin()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Reports');
    }

    public function test_reports_group_hidden_for_admin()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('slug', 'admin')->firstOrFail());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Reports');
    }
}
