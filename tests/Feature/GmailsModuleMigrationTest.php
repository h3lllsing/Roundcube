<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\Role;
use App\Helpers\ModuleCache;
use Database\Seeders\FeatureModuleSeeder;
use Database\Seeders\RolePermissionSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GmailsModuleMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FeatureModuleSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(TyroSeeder::class);
    }

    private function runUp(): void
    {
        $feature = Feature::where('slug', 'infrastructure')->first();
        if ($feature) {
            $existing = Module::withTrashed()->where('slug', 'g-mails')->first();
            if ($existing && $existing->trashed()) {
                $existing->restore();
            } elseif (!$existing) {
                Module::create([
                    'feature_id' => $feature->id,
                    'name' => 'G-Mails',
                    'slug' => 'g-mails',
                ]);
            }
            ModuleCache::flush('g-mails');
            Cache::forget('modules_all_by_slug');
            Cache::increment('perms_generation');
        }
    }

    private function removeGmailsModule(): void
    {
        Module::where('slug', 'g-mails')->forceDelete();
        ModuleCache::flush('g-mails');
        Cache::forget('modules_all_by_slug');
    }

    /** @test */
    public function migration_creates_gmails_when_missing(): void
    {
        $this->removeGmailsModule();
        $this->assertNull(Module::where('slug', 'g-mails')->first());

        $this->runUp();

        $module = Module::where('slug', 'g-mails')->first();
        $this->assertNotNull($module);
        $this->assertSame('G-Mails', $module->name);
        $this->assertSame('g-mails', $module->slug);

        $feature = Feature::where('slug', 'infrastructure')->first();
        $this->assertSame($feature->id, $module->feature_id);
    }

    /** @test */
    public function migration_is_idempotent(): void
    {
        $this->removeGmailsModule();
        $this->runUp();
        $firstId = Module::where('slug', 'g-mails')->first()->id;

        $this->runUp();

        $module = Module::where('slug', 'g-mails')->first();
        $this->assertSame($firstId, $module->id);
        $this->assertSame('G-Mails', $module->name);

        $count = Module::withTrashed()->where('slug', 'g-mails')->count();
        $this->assertSame(1, $count);
    }

    /** @test */
    public function existing_gmails_module_is_preserved(): void
    {
        $module = Module::where('slug', 'g-mails')->first();
        $originalId = $module->id;

        $this->runUp();

        $module->refresh();
        $this->assertSame($originalId, $module->id);
        $this->assertSame('G-Mails', $module->name);
    }

    /** @test */
    public function normal_roles_not_granted_access(): void
    {
        $this->removeGmailsModule();
        $this->runUp();

        $module = Module::where('slug', 'g-mails')->first();
        $nonSuperRoles = Role::whereNotIn('slug', ['super-admin', '*'])->get();

        foreach ($nonSuperRoles as $role) {
            $perm = ModuleRolePermission::where('module_id', $module->id)
                ->where('role_id', $role->id)
                ->first();
            $this->assertNull($perm, "Role '{$role->slug}' should not have ModuleRolePermission for g-mails");
        }
    }

    /** @test */
    public function cache_generation_invalidated(): void
    {
        $this->removeGmailsModule();
        $genBefore = Cache::get('perms_generation', 0);

        $this->runUp();

        $genAfter = Cache::get('perms_generation', 0);
        $this->assertGreaterThan($genBefore, $genAfter);
    }

    /** @test */
    public function gmails_appears_in_module_list(): void
    {
        $this->removeGmailsModule();

        $beforeSlugs = Module::with('feature')->orderBy('name')->get()->pluck('slug')->toArray();
        $this->assertNotContains('g-mails', $beforeSlugs);

        $this->runUp();

        $afterSlugs = Module::with('feature')->orderBy('name')->get()->pluck('slug')->toArray();
        $this->assertContains('g-mails', $afterSlugs);
    }

    /** @test */
    public function no_duplicate_module_created(): void
    {
        $this->removeGmailsModule();
        $this->runUp();
        $this->runUp();
        $this->runUp();

        $count = Module::withTrashed()->where('slug', 'g-mails')->count();
        $this->assertSame(1, $count);
    }

    /** @test */
    public function module_accessible_via_module_cache_after_migration(): void
    {
        $this->removeGmailsModule();
        Cache::forget('modules_all_by_slug');

        $this->runUp();

        $allBySlug = ModuleCache::allBySlug();
        $this->assertArrayHasKey('g-mails', $allBySlug);
        $this->assertSame('G-Mails', $allBySlug['g-mails']->name);
    }

    /** @test */
    public function rollback_does_not_delete_module(): void
    {
        $this->removeGmailsModule();
        $this->runUp();
        $this->assertNotNull(Module::where('slug', 'g-mails')->first());

        Cache::forget('modules_all_by_slug');
        Cache::increment('perms_generation');

        $this->assertNotNull(Module::where('slug', 'g-mails')->first());
    }

    /** @test */
    public function soft_deleted_module_is_restored(): void
    {
        $module = Module::where('slug', 'g-mails')->first();
        $module->delete();
        $this->assertTrue($module->trashed());

        $this->runUp();

        $module->refresh();
        $this->assertFalse($module->trashed());
    }
}
