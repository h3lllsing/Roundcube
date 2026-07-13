<?php
// Simulate what PHPUnit does with <env name="APP_ENV" value="testing"/>
putenv('APP_ENV=testing');
$_SERVER['APP_ENV'] = 'testing';
$_ENV['APP_ENV'] = 'testing';

require __DIR__ . '/vendor/autoload.php';

// Check what the repository sees
$repo = \Illuminate\Support\Env::getRepository();
echo 'Repo APP_ENV: ' . var_export($repo->get('APP_ENV'), true) . PHP_EOL;

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'After bootstrap:' . PHP_EOL;
echo 'getenv: ' . (getenv('APP_ENV') ?: 'not set') . PHP_EOL;
echo 'environment(): ' . ($app->environment() ?: 'EMPTY/ERROR') . PHP_EOL;
echo 'config(app.env): ' . $app->make('config')->get('app.env') . PHP_EOL;
echo 'isProduction: ' . ($app->environment('production') ? 'YES' : 'NO') . PHP_EOL;
echo 'isTesting: ' . ($app->environment('testing') ? 'YES' : 'NO') . PHP_EOL;
