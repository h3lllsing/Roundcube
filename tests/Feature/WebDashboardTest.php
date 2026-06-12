<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Feature;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->seed(\Database\Seeders\FeatureModuleSeeder::class);

        $this->user = User::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $this->user->assignRole($userRole);

        $this->superAdmin = User::factory()->create();
        $superAdminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->superAdmin->assignRole($superAdminRole);
    }

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_user_can_login(): void
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_login_fails_with_wrong_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $this->actingAs($this->user);
        $response = $this->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_dashboard_loads_for_authenticated_user(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_dashboard_loads_for_super_admin(): void
    {
        $this->actingAs($this->superAdmin);
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_root_redirects_to_dashboard_when_authenticated(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/');
        $response->assertRedirect('/dashboard');
    }
}
