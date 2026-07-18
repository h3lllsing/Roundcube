<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('expiry:check')->dailyAt('08:00');

Schedule::command('monitor:check')->hourly();

Schedule::command('sanctum:prune-expired')->daily();

Schedule::command('tasks:check-overdue')->dailyAt('09:00');

Schedule::command('renewals:send-email-reminders')->dailyAt('02:00');

Schedule::command('activitylog:clean')->daily();

Schedule::call(function () {
    \App\Models\LoginAudit::where('created_at', '<', now()->subYear())->delete();
})->daily()->name('login-audits:clean')->onOneServer();

Schedule::call(fn () => app(\App\Services\EmailStatService::class)->batchFetch())
    ->name('email-stats:batch-fetch')
    ->everyTenMinutes()
    ->withoutOverlapping();
