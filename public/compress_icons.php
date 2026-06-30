<?php
$dir = __DIR__.'/User/img/pelaporanicon';
$files = glob($dir . '/*.png');
$total_saved = 0;

echo "Mulai kompresi ikon pelaporan...\n";

foreach ($files as $file) {
    if (strpos($file, '.bak') !== false) continue; // Skip backup files
    $original_size = filesize($file);
    
    // Only compress if larger than 200KB
    if ($original_size > 200 * 1024) {
        $img = imagecreatefrompng($file);
        $width = imagesx($img);
        $height = imagesy($img);
        
        $newWidth = $width;
        $newHeight = $height;
        
        if ($width > 300) {
            $newWidth = 300;
            $newHeight = intval($height * (300 / $width));
            $newImg = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($newImg, false);
            imagesavealpha($newImg, true);
            imagecopyresampled($newImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($img);
            $img = $newImg;
        }
        
        imagepng($img, $file, 8); // Compression level 8 (0-9)
        imagedestroy($img);
        
        $new_size = filesize($file);
        $saved = $original_size - $new_size;
        $total_saved += $saved;
        echo basename($file) . ": " . round($original_size/1024, 2) . " KB -> " . round($new_size/1024, 2) . " KB (Hemat " . round($saved/1024, 2) . " KB)\n";
    }
}

echo "\nSelesai! Total hemat: " . round($total_saved/1024/1024, 2) . " MB\n";
