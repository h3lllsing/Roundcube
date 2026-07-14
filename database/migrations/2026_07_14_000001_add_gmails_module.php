<?php

use App\Models\Feature;
use App\Models\Module;
use App\Models\UserModulePermission;
use App\Helpers\ModuleCache;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    public function up(): void
    {
        $feature = Feature::where('slug', 'infrastructure')->first();

        if (! $feature) {
            return;
        }

        $existing = Module::withTrashed()->where('slug', 'g-mails')->first();

        if ($existing && $existing->trashed()) {
            $existing->restore();
            $module = $existing;
        } elseif ($existing) {
            $module = $existing;
        } else {
            $module = Module::create([
                'feature_id' => $feature->id,
                'name' => 'G-Mails',
                'slug' => 'g-mails',
            ]);
        }

        ModuleCache::flush('g-mails');
        Cache::forget('modules_all_by_slug');
        Cache::increment('perms_generation');
    }

    public function down(): void
    {
        Cache::forget('modules_all_by_slug');
        Cache::increment('perms_generation');
    }
};
