<?php

namespace App\Providers;

use App\Models\Domain;
use App\Models\EmailAccount;
use App\Models\LoginAudit;
use App\Models\User;
use App\Policies\ActivityLogPolicy;
use App\Policies\DomainPolicy;
use App\Policies\EmailAccountPolicy;
use App\Policies\LoginAuditPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Domain::class => DomainPolicy::class,
        User::class => UserPolicy::class,
        EmailAccount::class => EmailAccountPolicy::class,
        LoginAudit::class => LoginAuditPolicy::class,
        Activity::class => ActivityLogPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('assign-accounts', fn (User $user) => $user->isSuperAdmin());

        Gate::define('manageQueue', fn (User $user) => $user->isSuperAdmin());
    }
}
