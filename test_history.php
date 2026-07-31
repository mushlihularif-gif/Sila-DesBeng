<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::first(); // Just get any user for testing

if ($user) {
    $reports = App\Models\Laporan::where('user_id', $user->id)->get();
    $gasOrders = App\Models\GasOrder::where('user_id', $user->id)->with('gas')->get();
    $rentals = App\Models\RentalBooking::where('user_id', $user->id)->with('barang')->get();
    $mobil = App\Models\MobilBooking::where('user_id', $user->id)->with('mobil')->get();
    $fasilitas = App\Models\FasilitasUmumBooking::where('user_id', $user->id)->with('fasilitasUmum')->get();

    echo "Reports: " . count($reports) . "\n";
    echo "Gas: " . count($gasOrders) . "\n";
    echo "Rentals: " . count($rentals) . "\n";
    echo "Mobil: " . count($mobil) . "\n";
    echo "Fasilitas: " . count($fasilitas) . "\n";
} else {
    echo "No user found.";
}
