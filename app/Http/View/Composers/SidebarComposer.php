<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;

class SidebarComposer
{
    public function compose(View $view): void
    {
        $view->with('showMonitoring', false);
    }
}
