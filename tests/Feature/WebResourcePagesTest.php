<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\ExpiryTracker;
use App\Models\Feature;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\Note;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Task;
use App\Models\User;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebResourcePagesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->seed(\Database\Seeders\FeatureModuleSeeder::class);

        $this->user = User::factory()->create();
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $this->user->assignRole($adminRole);
    }

    /** @param array<string, mixed> $attributes */
    private function createVaultEntry(array $attributes): VaultEntry
    {
        $entry = new VaultEntry($attributes);
        $entry->encryptPassword('test_password');
        $entry->save();
        return $entry;
    }

    /** @param array<string, mixed> $attributes */
    private function createResource(string $model, array $attributes = []): mixed
    {
        $defaults = [
            'module_id' => Module::first()->id,
            'user_id' => $this->user->id,
            'status' => 'active',
        ];

        return match ($model) {
            Feature::class => Feature::factory()->create($attributes),
            Module::class => Module::factory()->create($attributes + ['feature_id' => Feature::first()->id]),
            Task::class => Task::create($attributes + $defaults + ['title' => 'Test Task', 'priority' => 'medium', 'created_by' => $this->user->id, 'updated_by' => $this->user->id]),
            Domain::class => Domain::create($attributes + $defaults + ['name' => 'example.com', 'registrar' => 'Test']),
            Hosting::class => Hosting::create($attributes + $defaults + ['name' => 'Test Hosting', 'provider' => 'Test']),
            Vps::class => Vps::create($attributes + $defaults + ['name' => 'Test VPS', 'provider' => 'Test']),
            Voip::class => Voip::create($attributes + $defaults + ['name' => 'Test VoIP', 'provider' => 'Test']),
            VaultEntry::class => $this->createVaultEntry($attributes + $defaults + ['service_name' => 'Test Service', 'username' => 'test']),
            Note::class => Note::create($attributes + ['content' => 'Test note', 'user_id' => $this->user->id, 'notable_type' => Module::class, 'notable_id' => Module::first()->id]),
            ServiceProvider::class => ServiceProvider::create($attributes + $defaults + ['name' => 'Test Provider', 'provider' => 'Test', 'type' => 'other']),
            ExpiryTracker::class => ExpiryTracker::create($attributes + $defaults + ['name' => 'Test Tracker', 'expiry_date' => now()->addDays(30)]),
            OtherService::class => OtherService::create($attributes + $defaults + ['name' => 'Test Service', 'service_type' => 'other']),
            default => throw new \InvalidArgumentException("Unknown model: $model"),
        };
    }

    /** @return array<string, array{0: string, 1: class-string|null}> */
    public static function resourceRoutesProvider(): array
    {
        return [
            'features' => ['features', Feature::class],
            'modules' => ['modules', Module::class],
            'tasks' => ['tasks', Task::class],
            'domains' => ['domains', Domain::class],
            'hostings' => ['hostings', Hosting::class],
            'vps' => ['vps', Vps::class],
            'voip' => ['voip', Voip::class],
            'vault' => ['vault', VaultEntry::class],
            'notes' => ['notes', Note::class],
            'service-providers' => ['service-providers', ServiceProvider::class],
            'users' => ['users', null],
        ];
    }

    /** @dataProvider resourceRoutesProvider */
    public function test_resource_page_loads(string $route, ?string $modelClass): void
    {
        if ($modelClass) {
            $this->createResource($modelClass);
        }

        $this->actingAs($this->user);
        $response = $this->get(route("$route.index"));
        $response->assertStatus(200);
    }

    public function test_activity_logs_page_loads(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('activity-logs.index'));
        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_resource_pages(): void
    {
        $routes = ['features', 'modules', 'tasks', 'domains'];
        foreach ($routes as $route) {
            $response = $this->get(route("$route.index"));
            $this->assertContains($response->getStatusCode(), [302, 401]);
        }
    }

    public function test_resource_filters_work(): void
    {
        $this->actingAs($this->user);
        $this->createResource(Task::class);

        $response = $this->get(route('tasks.index', ['status' => 'active']));
        $response->assertStatus(200);

        $response = $this->get(route('tasks.index', ['priority' => 'high']));
        $response->assertStatus(200);

        $response = $this->get(route('tasks.index', ['search' => 'Test']));
        $response->assertStatus(200);
    }

    public function test_user_search_and_role_filter_work(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('users.index', ['search' => 'test']));
        $response->assertStatus(200);

        $response = $this->get(route('users.index', ['role' => 'admin']));
        $response->assertStatus(200);
    }

    public function test_resource_pages_clear_filters(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('tasks.index', ['status' => 'active']));
        $response->assertStatus(200);

        $response = $this->get(route('tasks.index'));
        $response->assertStatus(200);
    }
}
