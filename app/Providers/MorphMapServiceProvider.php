<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class MorphMapServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Relation::morphMap([
            'feature'          => 'App\Models\Feature',
            'module'           => 'App\Models\Module',
            'domain'           => 'App\Models\Domain',
            'hosting'          => 'App\Models\Hosting',
            'vps'              => 'App\Models\Vps',
            'voip'             => 'App\Models\Voip',
            'domain_email'     => 'App\Models\DomainEmail',
            'other_service'    => 'App\Models\OtherService',
            'service_provider' => 'App\Models\ServiceProvider',
        ]);
    }
}
