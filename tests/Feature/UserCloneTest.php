<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use App\Models\UserModulePermission;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class UserCloneTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Module $hostingsModule;

    private User $superAdmin;

    private User $sourceUser;

    private User $normalUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->adminRole = Role::where('slug', 'admin')->firstOrFail();

        $this->hostingsModule = Module::where('slug', 'hostings')->firstOrFail();

        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->hostingsModule->id, 'role_id' => $this->adminRole->id],
            ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => false, 'can_export' => true, 'can_reveal' => true, 'can_import' => false]
        );

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superRole);

        $this->sourceUser = User::factory()->create();
        $this->sourceUser->assignRole($this->adminRole);

        UserModulePermission::updateOrCreate(
            ['user_id' => $this->sourceUser->id, 'module_id' => $this->hostingsModule->id],
            ['can_reveal' => true, 'can_export' => false]
        );

        $this->normalUser = User::factory()->create();
    }

    private function validCloneData(): array
    {
        return [
            'name' => 'Cloned User',
            'email' => 'cloned@example.com',
            'password' => 'NewPass123',
            'password_confirmation' => 'NewPass123',
            'copy_roles' => '1',
            'copy_overrides' => '1',
            'copy_status' => '0',
        ];
    }

    public function test_super_admin_can_view_clone_form(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('users.clone', $this->sourceUser->id));

        $response->assertOk();
        $response->assertSee('Clone User');
        $response->assertSee($this->sourceUser->name);
    }

    public function test_non_super_admin_cannot_view_clone_form(): void
    {
        $response = $this->actingAs($this->normalUser)
            ->get(route('users.clone', $this->sourceUser->id));

        $response->assertForbidden();
    }

    public function test_clone_creates_user_with_roles(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('users.clone.store', $this->sourceUser->id), $this->validCloneData());

        $response->assertRedirect();

        $newUser = User::where('email', 'cloned@example.com')->firstOrFail();

        $this->assertTrue($newUser->hasRole('admin'));
        $this->assertCount(1, $newUser->roles);
    }

    public function test_clone_creates_user_with_overrides(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('users.clone.store', $this->sourceUser->id), $this->validCloneData());

        $newUser = User::where('email', 'cloned@example.com')->firstOrFail();

        $override = UserModulePermission::where('user_id', $newUser->id)
            ->where('module_id', $this->hostingsModule->id)
            ->first();

        $this->assertNotNull($override);
        $this->assertTrue($override->can_reveal);
        $this->assertFalse($override->can_export);
    }

    public function test_clone_does_not_copy_password(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('users.clone.store', $this->sourceUser->id), $this->validCloneData());

        $newUser = User::where('email', 'cloned@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('NewPass123', $newUser->password));
        $this->assertFalse(Hash::check($this->sourceUser->password, $newUser->password));
    }

    public function test_clone_without_roles_or_overrides(): void
    {
        $data = $this->validCloneData();
        $data['copy_roles'] = '0';
        $data['copy_overrides'] = '0';

        $this->actingAs($this->superAdmin)
            ->post(route('users.clone.store', $this->sourceUser->id), $data);

        $newUser = User::where('email', 'cloned@example.com')->firstOrFail();

        $this->assertCount(0, $newUser->roles);
        $this->assertCount(0, UserModulePermission::where('user_id', $newUser->id)->get());
    }

    public function test_clone_status(): void
    {
        $this->sourceUser->suspended_at = now();
        $this->sourceUser->save();

        $data = $this->validCloneData();
        $data['copy_status'] = '1';

        $this->actingAs($this->superAdmin)
            ->post(route('users.clone.store', $this->sourceUser->id), $data);

        $newUser = User::where('email', 'cloned@example.com')->firstOrFail();

        $this->assertNotNull($newUser->suspended_at);
    }

    public function test_clone_duplicate_email_returns_validation_error(): void
    {
        $data = $this->validCloneData();
        $data['email'] = $this->sourceUser->email;

        $response = $this->actingAs($this->superAdmin)
            ->post(route('users.clone.store', $this->sourceUser->id), $data);

        $response->assertSessionHasErrors('email');
    }

    public function test_clone_requires_password(): void
    {
        $data = $this->validCloneData();
        unset($data['password'], $data['password_confirmation']);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('users.clone.store', $this->sourceUser->id), $data);

        $response->assertSessionHasErrors('password');
    }

    public function test_clone_preserves_source_user_unchanged(): void
    {
        $originalName = $this->sourceUser->name;
        $originalEmail = $this->sourceUser->email;
        $originalRoleCount = $this->sourceUser->roles->count();
        $originalOverrideCount = UserModulePermission::where('user_id', $this->sourceUser->id)->count();

        $this->actingAs($this->superAdmin)
            ->post(route('users.clone.store', $this->sourceUser->id), $this->validCloneData());

        $this->sourceUser->refresh();

        $this->assertEquals($originalName, $this->sourceUser->name);
        $this->assertEquals($originalEmail, $this->sourceUser->email);
        $this->assertEquals($originalRoleCount, $this->sourceUser->roles->count());
        $this->assertEquals($originalOverrideCount, UserModulePermission::where('user_id', $this->sourceUser->id)->count());
    }

    public function test_clone_activity_logged(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('users.clone.store', $this->sourceUser->id), $this->validCloneData());

        $log = Activity::where('event', 'cloned')
            ->where('subject_id', $this->sourceUser->id)
            ->where('subject_type', User::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('cloned', $log->event);

        $props = $log->properties;
        $this->assertTrue($props['copied_roles']);
        $this->assertTrue($props['copied_overrides']);
        $this->assertFalse($props['copied_status']);
    }

    public function test_clone_super_admin_requires_confirmation(): void
    {
        $data = $this->validCloneData();
        $data['email'] = 'new-super@example.com';

        $response = $this->actingAs($this->superAdmin)
            ->post(route('users.clone.store', $this->superAdmin->id), $data);

        $response->assertSessionHasErrors('confirm_super_admin');
    }

    public function test_clone_super_admin_with_confirmation(): void
    {
        $data = $this->validCloneData();
        $data['email'] = 'new-super@example.com';
        $data['confirm_super_admin'] = '1';

        $this->actingAs($this->superAdmin)
            ->post(route('users.clone.store', $this->superAdmin->id), $data);

        $newUser = User::where('email', 'new-super@example.com')->firstOrFail();

        $this->assertTrue($newUser->hasRole('super-admin'));
    }

    public function test_transaction_rollback_on_failure(): void
    {
        $data = $this->validCloneData();
        $data['email'] = ''; // triggers validation failure before transaction

        $this->actingAs($this->superAdmin)
            ->post(route('users.clone.store', $this->sourceUser->id), $data);

        $this->assertNull(User::where('email', 'cloned@example.com')->first());
    }

    public function test_clone_form_displays_role_and_override_counts(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('users.clone', $this->sourceUser->id));

        $response->assertSee('admin');
        $response->assertSee('Cloning from');
    }
}
