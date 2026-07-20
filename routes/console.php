<?php

use App\Models\LoginAudit;
use Illuminate\Support\Facades\Schedule;

Schedule::command('sanctum:prune-expired')->daily();

Schedule::command('activitylog:clean')->daily();

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
