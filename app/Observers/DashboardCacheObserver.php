<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class DashboardCacheObserver
{
    public function created(): void
    {
        Cache::increment('dashboard:version');
    }

    public function updated(): void
    {
        Cache::increment('dashboard:version');
    }

    public function deleted(): void
    {
        Cache::increment('dashboard:version');
    }
}
