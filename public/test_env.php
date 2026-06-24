<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
echo "<pre>";
echo "DB_CONNECTION env: " . env('DB_CONNECTION') . "\n";
echo "SESSION_CONNECTION env: " . env('SESSION_CONNECTION') . "\n";
echo "session.connection config: " . config('session.connection') . "\n";
echo "database.default config: " . config('database.default') . "\n";
echo "</pre>";
