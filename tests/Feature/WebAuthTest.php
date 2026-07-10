<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\User;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAuthTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.allow_registration' => true]);
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $this->admin = User::factory()->create(['name' => 'Admin', 'email' => 'admin@test.com']);
        $this->admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
    }

    public function test_register_page_loads(): void
    {
        $this->get(route('register'))->assertStatus(200);
    }

    public function test_register_creates_user(): void
    {
        $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'Pass12345',
            'password_confirmation' => 'Pass12345',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', ['email' => 'newuser@test.com']);
    }

    public function test_register_validates(): void
    {
        $this->post(route('register'), [])
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_forgot_password_page_loads(): void
    {
        $this->get(route('password.request'))->assertStatus(200);
    }

    public function test_forgot_password_sends_reset_link(): void
    {
        $this->post(route('password.email'), ['email' => $this->admin->email])
            ->assertSessionHas('success');
    }

    public function test_forgot_password_validates(): void
    {
        $this->post(route('password.email'), [])
            ->assertSessionHasErrors('email');
    }

    public function test_profile_page_loads(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('profile'))->assertStatus(200)->assertSee($this->admin->name);
    }

    public function test_profile_update_changes_name(): void
    {
        $this->actingAs($this->admin);
        $this->put(route('profile.update'), [
            'name' => 'Updated Admin',
            'email' => $this->admin->email,
        ])->assertRedirect(route('profile'));

        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'name' => 'Updated Admin']);
    }

    public function test_profile_update_validates(): void
    {
        $this->actingAs($this->admin);
        $this->put(route('profile.update'), ['name' => '', 'email' => ''])
            ->assertSessionHasErrors(['name', 'email']);
    }

    public function test_guest_cannot_access_new_resource_pages(): void
    {
        $routes = ['domain-emails.index', 'other-services.index', 'expiry-trackers.index', 'webhooks.index', 'login-audits.index', 'attachments.index', 'reports.index', 'module-permissions.index'];
        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $this->assertContains($response->getStatusCode(), [302, 401]);
        }
    }

    public function test_guest_cannot_access_profile(): void
    {
        $this->get(route('profile'))->assertRedirect(route('login'));
    }
}
