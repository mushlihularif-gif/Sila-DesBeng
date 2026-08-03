<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$content = file_get_contents(storage_path('app/private_data/kyc/ktp/ktp_6a6dd2498f3fa.enc'));
$decrypted = \App\Services\FileEncryptionService::decrypt($content);
if ($decrypted === null) {
    echo "Decryption returned null\n";
} else {
    echo "Length: " . strlen($decrypted) . "\n";
    $img = @imagecreatefromstring($decrypted);
    var_dump($img !== false);
}
