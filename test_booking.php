<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email','dikiw7063@gmail.com')->first();
Auth::login($user);
$request = new Illuminate\Http\Request([
    'gas_id' => 3,
    'buyer_name' => 'Test',
    'buyer_address' => 'Test',
    'quantity' => 1,
    'payment_method' => 'midtrans'
]);
$controller = app()->make('App\Http\Controllers\User\GasBookingController');
$response = $controller->store($request);
echo "RESPONSE_START\n";
echo $response->getContent();
echo "\nRESPONSE_END\n";
