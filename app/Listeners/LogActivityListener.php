<?php

namespace App\Listeners;

use App\Contracts\LoggableEvent;
use Illuminate\Support\Facades\Auth;

class LogActivityListener
{
    public function handle(LoggableEvent $event): void
    {
        $model = $event->getModel();

        activity()
            ->event($event->getEventName())
            ->performedOn($model)
            ->causedBy(Auth::user())
            ->withProperties($event->getProperties())
            ->log($event->getEventName() . ': ' . ($model->getAttribute('email') ?? $model->getAttribute('name') ?? $model->getAttribute('id')));
    }
}
