<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$req = Illuminate\Http\Request::create('/unit-penyewaan-alat', 'GET', ['region_id' => 5]);
$res = $kernel->handle($req);
echo $res->getStatusCode() . "\n";
echo $res->headers->get('Location') . "\n";
