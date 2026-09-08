<?php

/*
 * Pengganti public/index.php untuk hosting yang Document Root-nya
 * tidak boleh keluar dari public_html.
 *
 * Bedanya dengan public/index.php hanya pada tiga path require:
 * di sini memakai path absolut ke folder aplikasi, karena berkas ini
 * berada di document root subdomain, terpisah dari kode aplikasinya.
 *
 * Berkas ini disalin ke document root oleh .cpanel.yml setiap deploy,
 * jadi tidak ada lagi tambal-menambal manual.
 *
 * Kalau path aplikasinya berubah, ubah satu baris $APP di bawah ini.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$APP = '/home/inon1796/repositories/Sila-DesBeng';

if (php_sapi_name() === 'cli-server') {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $APP.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $APP.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $APP.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
