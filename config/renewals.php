<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Notification Days Before Expiry
    |--------------------------------------------------------------------------
    |
    | Array of days before expiry on which renewal notifications are sent.
    | Each value must match validation rules in StoreExpiryTrackerRequest
    | and UpdateExpiryTrackerRequest (currently: 1, 7, 15, 30).
    |
    */

    'notify_days_before' => [30, 15, 7, 1],

];
