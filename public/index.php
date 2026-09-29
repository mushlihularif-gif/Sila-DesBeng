<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (php_sapi_name() === 'cli-server') {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
}

// Deteksi letak folder aplikasi (lokal vs hosting cPanel)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $APP = __DIR__ . '/..';
} elseif (file_exists('/home/inon1796/repositories/Sila-DesBeng/vendor/autoload.php')) {
    $APP = '/home/inon1796/repositories/Sila-DesBeng';
} else {
    $APP = __DIR__ . '/..';
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $APP . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $APP . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $APP . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
