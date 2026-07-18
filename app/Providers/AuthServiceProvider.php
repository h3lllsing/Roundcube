<?php

namespace App\Providers;

use App\Models\Domain;
use App\Models\EmailAccount;
use App\Policies\DomainPolicy;
use App\Policies\EmailAccountPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Domain::class => DomainPolicy::class,
        EmailAccount::class => EmailAccountPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
