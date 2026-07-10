<?php

namespace App\Helpers;

use App\Models\Module;
use Illuminate\Support\Facades\Cache;

class ModuleCache
{
    public static function findBySlug(string $slug): ?Module
    {
        $id = Cache::remember("module_slug:{$slug}", 86400, fn () =>
            Module::where('slug', $slug)->value('id')
        );

        return $id ? Module::find($id) : null;
    }

    public static function idBySlug(string $slug): ?int
    {
        return Cache::remember("module_slug:{$slug}", 86400, fn () =>
            Module::where('slug', $slug)->value('id')
        );
    }

    public static function flush(string $slug): void
    {
        Cache::forget("module_slug:{$slug}");
    }

    public static function allBySlug(): array
    {
        return Cache::remember('modules_all_by_slug', 86400, fn () =>
            Module::all()->keyBy('slug')->all()
        );
    }
}
