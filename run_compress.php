<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

$manager = new ImageManager(new Driver());
$dir = public_path('User/img/pelaporanicon');
$files = glob($dir . '/*.png');

$total_saved = 0;

echo "Mulai kompresi ikon pelaporan...\n";

foreach ($files as $file) {
    if (strpos($file, '.bak') !== false) continue; // Skip backup files

    $original_size = filesize($file);
    
    // Only compress if larger than 200KB to be safe
    if ($original_size > 200 * 1024) {
        $image = $manager->read($file);
        
        // Scale to 300px width max for icons
        if ($image->width() > 300) {
            $image->scale(width: 300);
        }
        
        // Encode and save
        $encoded = $image->encodeByExtension('png', 80);
        file_put_contents($file, (string) $encoded);
        
        $new_size = filesize($file);
        $saved = $original_size - $new_size;
        $total_saved += $saved;
        
        echo basename($file) . ": " . round($original_size/1024, 2) . " KB -> " . round($new_size/1024, 2) . " KB (Hemat " . round($saved/1024, 2) . " KB)\n";
    }
}

echo "\nSelesai! Total hemat: " . round($total_saved/1024/1024, 2) . " MB\n";
