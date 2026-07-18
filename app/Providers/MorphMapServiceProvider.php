<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class MorphMapServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Relation::morphMap([
            'feature' => 'App\Models\Feature',
            'module'  => 'App\Models\Module',
        ]);
    }
}
