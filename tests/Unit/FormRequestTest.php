<?php

namespace Tests\Unit;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Requests\StoreDomainRequest;
use App\Http\Requests\StoreFeatureRequest;
use App\Http\Requests\StoreHostingRequest;
use App\Http\Requests\StoreModulePermissionRequest;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\StoreVaultRequest;
use App\Http\Requests\StoreVpsRequest;
use App\Http\Requests\UpdateDomainRequest;
use App\Http\Requests\UpdateFeatureRequest;
use App\Http\Requests\UpdateHostingRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdateVaultRequest;
use App\Models\Feature;
use App\Models\User;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FormRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
    }

    public function test_login_request_validates_email_required(): void
    {
        $rules = (new LoginRequest)->rules();
        $v = Validator::make(['email' => '', 'password' => 'secret'], $rules);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('email', $v->errors()->toArray());
    }

    public function test_login_request_validates_password_required(): void
    {
        $rules = (new LoginRequest)->rules();
        $v = Validator::make(['email' => 'a@b.com', 'password' => ''], $rules);
        $this->assertTrue($v->fails());
    }

    public function test_login_request_passes_with_valid_data(): void
    {
        $rules = (new LoginRequest)->rules();
        $v = Validator::make(['email' => 'a@b.com', 'password' => 'secret'], $rules);
        $this->assertTrue($v->passes());
    }

    public function test_store_user_request_requires_name_email_password(): void
    {
        $rules = (new StoreUserRequest)->rules();
        $v = Validator::make([], $rules);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('name', $v->errors()->toArray());
        $this->assertArrayHasKey('email', $v->errors()->toArray());
        $this->assertArrayHasKey('password', $v->errors()->toArray());
    }

    public function test_store_user_request_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $rules = (new StoreUserRequest)->rules();
        $v = Validator::make([
            'name' => 'Test',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $rules);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('email', $v->errors()->toArray());
    }

    public function test_store_user_request_passes_with_valid_data(): void
    {
        $rules = (new StoreUserRequest)->rules();
        $v = Validator::make([
            'name' => 'Test User',
            'email' => 'fresh@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ], $rules);
        $this->assertTrue($v->passes());
    }

    public function test_store_user_request_requires_password_min_8(): void
    {
        $rules = (new StoreUserRequest)->rules();
        $v = Validator::make([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ], $rules);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('password', $v->errors()->toArray());
    }

    public function test_store_user_request_requires_password_confirmed(): void
    {
        $rules = (new StoreUserRequest)->rules();
        $v = Validator::make([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ], $rules);
        $this->assertTrue($v->fails());
    }

    public function test_store_user_request_validates_role_ids_exist(): void
    {
        $rules = (new StoreUserRequest)->rules();
        $v = Validator::make([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [99999],
        ], $rules);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('roles.0', $v->errors()->toArray());
    }

    public function test_update_user_request_email_unique_except_self(): void
    {
        $user = User::factory()->create(['email' => 'self@example.com']);

        $route = $this->createMock(Route::class);
        $route->method('parameter')->with('user')->willReturn($user);

        $request = new UpdateUserRequest;
        $request->setRouteResolver(fn () => $route);

        $rules = $request->rules();

        $v = Validator::make([
            'name' => 'Updated',
            'email' => 'self@example.com',
        ], $rules);
        $this->assertTrue($v->passes());
    }

    public function test_update_profile_request_requires_current_password_with_new_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('currentpass')]);
        $this->actingAs($user);

        $req = new UpdateProfileRequest;
        $req->setUserResolver(fn () => $user);
        $rules = $req->rules();

        $v = Validator::make([
            'name' => 'Test',
            'email' => $user->email,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ], $rules);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('current_password', $v->errors()->toArray());
    }

    public function test_update_profile_request_passes_with_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('currentpass')]);
        $this->actingAs($user);

        $req = new UpdateProfileRequest;
        $req->setUserResolver(fn () => $user);
        $rules = $req->rules();

        $v = Validator::make([
            'name' => 'Test',
            'email' => $user->email,
            'password' => 'Newpassword123',
            'password_confirmation' => 'Newpassword123',
            'current_password' => 'currentpass',
        ], $rules);
        $this->assertTrue($v->passes());
    }

    public function test_store_attachment_request_requires_file(): void
    {
        $rules = (new StoreAttachmentRequest)->rules();
        $v = Validator::make([], $rules);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('file', $v->errors()->toArray());
    }

    public function test_store_attachment_request_validates_notable_type(): void
    {
        $rules = (new StoreAttachmentRequest)->rules();
        $v = Validator::make([
            'notable_type' => 'Invalid\Model',
            'notable_id' => 1,
        ], $rules);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('notable_type', $v->errors()->toArray());
    }

    public function test_store_attachment_request_valid_notable_type_passes(): void
    {
        $rules = (new StoreAttachmentRequest)->rules();
        $v = Validator::make([
            'file' => UploadedFile::fake()->create('test.pdf', 100),
            'notable_type' => 'App\Models\Domain',
            'notable_id' => 1,
        ], $rules);
        $this->assertTrue($v->passes());
    }

    public function test_store_module_permission_request_requires_role_id(): void
    {
        $rules = (new StoreModulePermissionRequest)->rules();
        $v = Validator::make([], $rules);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('role_id', $v->errors()->toArray());
    }

    public function test_store_module_permission_request_validates_role_exists(): void
    {
        $rules = (new StoreModulePermissionRequest)->rules();
        $v = Validator::make(['role_id' => 99999], $rules);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('role_id', $v->errors()->toArray());
    }

    public function test_store_module_permission_request_passes_with_valid_role(): void
    {
        $role = Role::where('slug', 'user')->firstOrFail();
        $rules = (new StoreModulePermissionRequest)->rules();
        $v = Validator::make([
            'role_id' => $role->id,
            'can_read' => true,
        ], $rules);
        $this->assertTrue($v->passes());
    }

    public function test_store_vault_request_requires_service_name_and_password(): void
    {
        $rules = (new StoreVaultRequest)->rules();
        $v = Validator::make([], $rules);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('service_name', $v->errors()->toArray());
        $this->assertArrayHasKey('password', $v->errors()->toArray());
    }

    // ─── StoreFeatureRequest ────────────────────────────

    public function test_store_feature_request_requires_name(): void
    {
        $rules = (new StoreFeatureRequest)->rules();
        $this->assertTrue(Validator::make([], $rules)->fails());
        $this->assertTrue(Validator::make(['name' => 'Test', 'slug' => 'test'], $rules)->passes());
    }

    // ─── UpdateFeatureRequest ───────────────────────────

    public function test_update_feature_request_passes_with_valid_data(): void
    {
        $feature = Feature::factory()->create();
        $route = $this->createMock(Route::class);
        $route->method('parameter')->with('feature')->willReturn($feature);

        $request = new UpdateFeatureRequest;
        $request->setRouteResolver(fn () => $route);

        $this->assertTrue(Validator::make(['name' => 'Updated'], $request->rules())->passes());
    }

    // ─── StoreTaskRequest ───────────────────────────────

    public function test_store_task_request_requires_title(): void
    {
        $rules = (new StoreTaskRequest)->rules();
        $v = Validator::make([], $rules);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('title', $v->errors()->toArray());
    }

    public function test_store_task_request_validates_status_and_priority(): void
    {
        $rules = (new StoreTaskRequest)->rules();
        $v = Validator::make(['title' => 'Test', 'status' => 'invalid', 'priority' => 'invalid'], $rules);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('status', $v->errors()->toArray());
        $this->assertArrayHasKey('priority', $v->errors()->toArray());
    }

    // ─── UpdateTaskRequest ──────────────────────────────

    public function test_update_task_request_passes_with_valid_data(): void
    {
        $rules = (new UpdateTaskRequest)->rules();
        $this->assertTrue(Validator::make(['title' => 'Updated', 'status' => 'completed'], $rules)->passes());
    }

    // ─── StoreNoteRequest ───────────────────────────────

    public function test_store_note_request_requires_content(): void
    {
        $rules = (new StoreNoteRequest)->rules();
        $v = Validator::make([], $rules);
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('content', $v->errors()->toArray());
    }

    public function test_store_note_request_passes_with_content(): void
    {
        $rules = (new StoreNoteRequest)->rules();
        $this->assertTrue(Validator::make(['content' => 'Hello'], $rules)->passes());
    }

    // ─── UpdateNoteRequest ──────────────────────────────

    public function test_update_note_request_requires_content(): void
    {
        $rules = (new UpdateNoteRequest)->rules();
        $this->assertTrue(Validator::make([], $rules)->fails());
    }

    // ─── StoreHostingRequest ────────────────────────────

    public function test_store_hosting_request_requires_name(): void
    {
        $rules = (new StoreHostingRequest)->rules();
        $this->assertTrue(Validator::make([], $rules)->fails());
        $this->assertArrayHasKey('name', Validator::make([], $rules)->errors()->toArray());
    }

    public function test_store_hosting_request_validates_status(): void
    {
        $rules = (new StoreHostingRequest)->rules();
        $this->assertTrue(Validator::make(['name' => 'Test', 'status' => 'bogus'], $rules)->fails());
    }

    // ─── UpdateHostingRequest ───────────────────────────

    public function test_update_hosting_request_validates_expiry_after_start(): void
    {
        $rules = (new UpdateHostingRequest)->rules();
        $v = Validator::make([
            'name' => 'Test',
            'start_date' => '2026-06-20',
            'expiry_date' => '2026-06-10',
        ], $rules);
        $this->assertTrue($v->fails());
    }

    // ─── StoreDomainRequest ─────────────────────────────

    public function test_store_domain_request_requires_name(): void
    {
        $rules = (new StoreDomainRequest)->rules();
        $this->assertTrue(Validator::make([], $rules)->fails());
        $this->assertArrayHasKey('name', Validator::make([], $rules)->errors()->toArray());
    }

    // ─── UpdateDomainRequest ────────────────────────────

    public function test_update_domain_request_validates_status(): void
    {
        $rules = (new UpdateDomainRequest)->rules();
        $this->assertTrue(Validator::make(['name' => 'Test', 'status' => 'invalid'], $rules)->fails());
    }

    // ─── StoreVpsRequest ────────────────────────────────

    public function test_store_vps_request_requires_name(): void
    {
        $rules = (new StoreVpsRequest)->rules();
        $this->assertTrue(Validator::make([], $rules)->fails());
        $this->assertArrayHasKey('name', Validator::make([], $rules)->errors()->toArray());
    }

    public function test_store_vps_request_validates_numeric_fields(): void
    {
        $rules = (new StoreVpsRequest)->rules();
        $v = Validator::make(['name' => 'Test', 'ram_mb' => -1, 'disk_gb' => -1, 'cpu_cores' => -1, 'cost' => -1], $rules);
        $this->assertTrue($v->fails());
    }

    // ─── UpdateVaultRequest ─────────────────────────────

    public function test_update_vault_request_passes_without_service_name(): void
    {
        $rules = (new UpdateVaultRequest)->rules();
        $this->assertTrue(Validator::make([], $rules)->passes());
    }
}
