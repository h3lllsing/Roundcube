<?php

namespace Tests\Unit;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use App\Models\Vps;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Privilege;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private Role $userRole;
    private Role $superAdminRole;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
        $this->userRole = Role::where('slug', 'user')->firstOrFail();
        $this->superAdminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->user = User::factory()->create();
    }

    public function test_fillable_attributes(): void
    {
        $user = User::factory()->make([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret',
        ]);

        $this->assertSame('John Doe', $user->name);
        $this->assertSame('john@example.com', $user->email);
    }

    public function test_password_is_hashed_when_set(): void
    {
        $user = User::factory()->create(['password' => 'plain-text']);

        $this->assertNotSame('plain-text', $user->password);
        $this->assertTrue(password_verify('plain-text', $user->password));
    }

    public function test_hidden_attributes_not_serialized(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret')]);
        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_email_verified_at_is_carbon(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->assertInstanceOf(Carbon::class, $user->email_verified_at);
    }

    public function test_soft_delete(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        $this->assertNull(User::find($userId));
        $this->assertNotNull(User::withTrashed()->find($userId));
        $this->assertTrue(User::withTrashed()->find($userId)->trashed());
    }

    public function test_restore(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();
        User::withTrashed()->find($userId)->restore();

        $this->assertNotNull(User::find($userId));
    }

    public function test_suspended_at_is_carbon(): void
    {
        $user = User::factory()->create(['suspended_at' => now()]);

        $this->assertInstanceOf(Carbon::class, $user->suspended_at);
    }

    public function test_suspended_at_can_be_null(): void
    {
        $user = User::factory()->create(['suspended_at' => null]);

        $this->assertNull($user->suspended_at);
    }

    public function test_password_has_hashed_cast(): void
    {
        $user = User::factory()->create(['password' => 'raw-value']);

        $this->assertTrue(password_verify('raw-value', $user->password));
        $this->assertNotSame('raw-value', $user->password);
    }

    public function test_suspended_at_has_datetime_cast(): void
    {
        $user = User::factory()->create(['suspended_at' => '2026-06-01 12:00:00']);

        $this->assertInstanceOf(Carbon::class, $user->suspended_at);
        $this->assertSame('2026-06-01', $user->suspended_at->format('Y-m-d'));
    }

    public function test_has_role_returns_true_when_assigned(): void
    {
        $this->user->assignRole($this->userRole);

        $this->assertTrue($this->user->hasRole('user'));
    }

    public function test_has_role_returns_false_when_not_assigned(): void
    {
        $this->assertFalse($this->user->hasRole('user'));
    }

    public function test_has_privilege_returns_true_when_role_has_privilege(): void
    {
        $this->user->assignRole($this->superAdminRole);
        $privilege = Privilege::firstOrFail();

        $this->assertTrue($this->user->hasPrivilege($privilege->slug));
    }

    public function test_has_privilege_returns_false_when_role_missing_privilege(): void
    {
        $this->user->assignRole($this->userRole);
        $privilege = Privilege::firstOrFail();

        $this->assertFalse($this->user->hasPrivilege($privilege->slug));
    }

    public function test_can_on_module_returns_true_when_permission_exists(): void
    {
        $this->user->assignRole($this->userRole);
        $module = Module::firstOrFail();
        ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $this->userRole->id,
            'can_read' => true,
        ]);

        $this->assertTrue($this->user->canOnModule($module, 'read'));
    }

    public function test_can_on_module_returns_false_when_permission_missing(): void
    {
        $this->user->assignRole($this->userRole);
        $module = Module::firstOrFail();

        $this->assertFalse($this->user->canOnModule($module, 'read'));
    }

    public function test_suspend_via_force_fill_sets_suspended_at(): void
    {
        $this->user->forceFill(['suspended_at' => now()])->save();

        $this->assertNotNull($this->user->fresh()->suspended_at);
    }

    public function test_unsuspend_via_force_fill_clears_suspended_at(): void
    {
        $this->user->forceFill(['suspended_at' => now()])->save();
        $this->user->forceFill(['suspended_at' => null])->save();

        $this->assertNull($this->user->fresh()->suspended_at);
    }

    public function test_suspended_at_not_in_fillable(): void
    {
        $fillable = (new User)->getFillable();

        $this->assertNotContains('suspended_at', $fillable);
    }

    public function test_role_relationship(): void
    {
        $this->user->assignRole($this->userRole);

        $this->assertTrue($this->user->roles->contains($this->userRole));
    }

    public function test_privilege_relationship(): void
    {
        $this->user->assignRole($this->superAdminRole);

        $this->assertTrue($this->user->privileges()->isNotEmpty());
    }

    public function test_has_many_relationships(): void
    {
        $vps = Vps::factory()->create(['user_id' => $this->user->id]);

        $this->assertTrue($this->user->vps->contains($vps));
    }
}
