<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('expiry:check')->dailyAt('08:00');

Schedule::command('monitor:check')->hourly();

Schedule::command('sanctum:prune-expired')->daily();
