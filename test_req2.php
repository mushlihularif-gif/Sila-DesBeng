<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = Illuminate\Http\Request::create('/unit-penyewaan-alat?region_id=5', 'GET');
$res = $kernel->handle($req);

echo "Status Code: " . $res->getStatusCode() . "\n";
echo "Location: " . $res->headers->get('Location') . "\n";
echo "Session: \n";
print_r(session()->all());
