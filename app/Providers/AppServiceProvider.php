<?php

namespace App\Providers;

use App\Models\EmailAccount;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        try {
            $account = EmailAccount::where('sync_enabled', true)
                ->where('status', \App\Enums\AccountStatus::Active)
                ->first();

            if ($account && $account->smtp_host) {
                config([
                    'mail.mailer' => 'smtp',
                    'mail.mailers.smtp.host' => $account->smtp_host,
                    'mail.mailers.smtp.port' => $account->smtp_port ?? 587,
                    'mail.mailers.smtp.username' => $account->smtp_username ?: $account->email,
                    'mail.mailers.smtp.password' => $account->smtp_password ?: $account->password,
                    'mail.mailers.smtp.encryption' => $account->smtp_encryption ?? 'tls',
                    'mail.from.address' => $account->email,
                    'mail.from.name' => config('app.name'),
                ]);
            }
        } catch (\Throwable $e) {
            // DB not ready yet (migrations pending, etc.)
        }
    }
}
