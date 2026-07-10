<?php

namespace App\View\Components;

use App\Models\Module;
use Illuminate\View\Component;

class PermissionCheck extends Component
{
    public function __construct(
        public ?Module $module = null,
        public string $action = 'update',
    ) {}

    public function render()
    {
        return view('components.permission-check');
    }
}
