<?php
$files = [
    'public/User/img/pelaporanicon/kebersihan.png',
    'public/User/img/elemen/event.png',
    'public/User/img/elemen/tugu.png',
    'public/User/img/elemen/slide12.png',
    'public/User/img/elemen/slide11.png'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    // Backup (hanya jika belum ada backup)
    if (!file_exists($file . '.bak')) {
        copy($file, $file . '.bak');
    }

    // Load image
    $img = @imagecreatefrompng($file);
    if (!$img) {
        echo "Failed to load $file\n";
        continue;
    }
    
    $width = imagesx($img);
    $height = imagesy($img);
    
    // Resize if width > 1920
    $maxWidth = 1920;
    if ($width > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = floor($height * ($maxWidth / $width));
        
        $newImg = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency
        imagealphablending($newImg, false);
        imagesavealpha($newImg, true);
        $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagefilledrectangle($newImg, 0, 0, $newWidth, $newHeight, $transparent);
        
        imagecopyresampled($newImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save back as PNG with max compression (level 9)
        imagepng($newImg, $file, 9);
        imagedestroy($newImg);
        echo "Resized & Compressed: $file\n";
    } else {
        // Just re-save to compress
        imagealphablending($img, false);
        imagesavealpha($img, true);
        imagepng($img, $file, 9);
        echo "Compressed only (width <= 1920): $file\n";
    }
    imagedestroy($img);
}
echo "Done.";
