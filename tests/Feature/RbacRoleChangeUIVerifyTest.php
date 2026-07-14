<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\User;
use App\Models\UserModulePermission;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacRoleChangeUIVerifyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Role $userRole;
    private Module $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->userRole = Role::where('slug', 'user')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->roles()->sync([$superRole->id]);
        $this->module = Module::firstOrFail();
    }

    /** @test */
    public function edit_page_shows_warning_banner_when_overrides_exist(): void
    {
        $user = User::factory()->create();
        $user->roles()->sync([$this->userRole->id]);
        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->module->id,
            'can_read' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('users.edit', $user->id));

        $response->assertStatus(200);
        $response->assertSee('1 custom permission override');
        $response->assertSee('confirm_role_change');
        $response->assertSee('I understand that existing user-specific permission overrides will remain active');
        $response->assertSee('baseline');
        $response->assertSee('remain in effect');
    }

    /** @test */
    public function edit_page_shows_no_warning_when_no_overrides(): void
    {
        $user = User::factory()->create();
        $user->roles()->sync([$this->userRole->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('users.edit', $user->id));

        $response->assertStatus(200);
        $response->assertDontSee('custom permission override');
        $response->assertDontSee('confirm_role_change');
    }

    /** @test */
    public function edit_page_shows_override_count_and_warning_text(): void
    {
        $user = User::factory()->create();
        $user->roles()->sync([$this->userRole->id]);
        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->module->id,
            'can_read' => true,
        ]);
        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => Module::skip(1)->firstOrFail()->id,
            'can_create' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('users.edit', $user->id));

        $response->assertStatus(200);
        $response->assertSee('2 custom permission override');
        $response->assertSee('not deleted automatically');
        $response->assertSee('Role permissions = baseline');
        $response->assertSee('User overrides = exceptions');
    }

    /** @test */
    public function homepage_loads_after_confirmed_role_change(): void
    {
        $user = User::factory()->create();
        $user->roles()->sync([$this->userRole->id]);
        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->module->id,
            'can_read' => true,
        ]);

        $adminRole = Role::where('slug', 'admin')->firstOrFail();

        $response = $this->actingAs($this->admin)
            ->put(route('users.update', $user->id), [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => [$adminRole->id],
                'confirm_role_change' => '1',
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ]);

        $response->assertSessionHas('success');

        $override = UserModulePermission::where('user_id', $user->id)
            ->where('module_id', $this->module->id)
            ->first();
        $this->assertNotNull($override);
        $this->assertTrue((bool) $override->can_read);
    }

    /** @test */
    public function warning_html_has_correct_structure(): void
    {
        $user = User::factory()->create();
        $user->roles()->sync([$this->userRole->id]);
        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->module->id,
            'can_read' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('users.edit', $user->id));

        $response->assertStatus(200);
        $response->assertSee('bg-amber-50');
        $response->assertSee('dark:bg-amber-900/20');
        $response->assertSee('text-amber-700');
        $response->assertSee('dark:text-amber-300');
        $response->assertSee('confirm_role_change');
    }

    /** @test */
    public function error_message_shown_when_confirmation_missing(): void
    {
        $user = User::factory()->create();
        $user->roles()->sync([$this->userRole->id]);
        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->module->id,
            'can_read' => true,
        ]);

        $adminRole = Role::where('slug', 'admin')->firstOrFail();

        $response = $this->actingAs($this->admin)
            ->from(route('users.edit', $user->id))
            ->put(route('users.update', $user->id), [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => [$adminRole->id],
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ]);

        $response->assertSessionHasErrors('confirm_role_change');
        $response->assertRedirect(route('users.edit', $user->id));

        $this->assertTrue($user->fresh()->roles()->where('roles.id', $adminRole->id)->doesntExist());
    }
}
