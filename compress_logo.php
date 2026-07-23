<?php
$srcPath = __DIR__ . '/public/User/img/logo/logosdfooter.png';
$destPath = __DIR__ . '/public/User/img/logo/logosdfooter.webp';

if (!file_exists($srcPath)) {
    echo "Source file not found: $srcPath\n";
    exit(1);
}

// Check if GD extension is loaded
if (!extension_loaded('gd')) {
    echo "GD extension is not loaded!\n";
    exit(1);
}

$image = imagecreatefrompng($srcPath);
if (!$image) {
    echo "Failed to read PNG image.\n";
    exit(1);
}

// Preserve transparency
imagepalettetotruecolor($image);
imagealphablending($image, true);
imagesavealpha($image, true);

// Convert to WebP with 85% quality (excellent balance of size and quality)
$result = imagewebp($image, $destPath, 85);
imagedestroy($image);

if ($result) {
    $origSize = filesize($srcPath);
    $newSize = filesize($destPath);
    echo "Successfully converted to WEBP!\n";
    echo "Original size: " . number_format($origSize / 1024, 2) . " KB\n";
    echo "New size: " . number_format($newSize / 1024, 2) . " KB\n";
} else {
    echo "Failed to save WEBP image.\n";
    exit(1);
}
