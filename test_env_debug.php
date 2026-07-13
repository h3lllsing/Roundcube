<?php
$_SERVER['APP_ENV'] = 'testing';
$_ENV['APP_ENV'] = 'testing';
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->bootstrapWith([
    \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
]);
echo 'APP_ENV: ' . $app->environment() . PHP_EOL;
echo 'Config env: ' . $app->make('config')->get('app.env') . PHP_EOL;
echo 'ENV: ' . (getenv('APP_ENV') ?: 'not set') . PHP_EOL;
echo 'SERVER: ' . ($_SERVER['APP_ENV'] ?? 'not set') . PHP_EOL;
