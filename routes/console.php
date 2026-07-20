<?php

use App\Models\LoginAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Schedule::command('sanctum:prune-expired')->daily();

Schedule::command('activitylog:clean')->daily();

Schedule::call(function () {
    DB::table('webmail_tokens')->where('expires_at', '<', now())->delete();
})->hourly()->name('webmail-tokens:clean')->onOneServer();

Schedule::call(function () {
    $threshold = config('auth.login_threshold_attempts', 5);
    $window = config('auth.login_threshold_minutes', 15);

    LoginAudit::where('event', \App\Enums\LoginEvent::LoginFailed)
        ->where('created_at', '>=', now()->subMinutes($window))
        ->selectRaw('email, COUNT(*) as attempts')
        ->groupBy('email')
        ->having('attempts', '>=', $threshold)
        ->get()
        ->each(function ($row) {
            logger()->warning("Repeated failed logins detected for {$row->email}: {$row->attempts} attempts in the last window.");
        });
})->everyFiveMinutes()->name('login-threshold:check')->onOneServer();

Schedule::call(function () {
    LoginAudit::where('created_at', '<', now()->subYear())->delete();
})->daily()->name('login-audits:clean')->onOneServer();

// Run queue worker: processes all pending jobs, stops when empty (max 4 min)
Schedule::command('queue:work --stop-when-empty --max-time=240 --sleep=3')
    ->everyMinute()
    ->name('queue:process')
    ->withoutOverlapping()
    ->runInBackground();

// Dispatch IMAP sync jobs for all enabled accounts
Schedule::command('email-sync:dispatch')
    ->everyTenMinutes()
    ->name('email-sync:dispatch')
    ->withoutOverlapping();
