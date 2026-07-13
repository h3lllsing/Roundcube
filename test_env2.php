<?php
$_SERVER['APP_ENV'] = 'testing';
$_ENV['APP_ENV'] = 'testing';
putenv('APP_ENV=testing');
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo 'getenv: ' . (getenv('APP_ENV') ?: 'not set') . PHP_EOL;
echo 'environments() === production: ' . ($app->environment() === 'production' ? 'YES' : ($app->environment() ?: 'EMPTY')) . PHP_EOL;
echo 'config(app.env): ' . config('app.env') . PHP_EOL;
