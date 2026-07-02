<?php
$file = 'D:/laragon/www/SilaDesBeng/public/User/img/logo/logocb.png';
if(!file_exists($file)) die('File not found');

// Load image
$source = imagecreatefrompng($file);

// Get size
$width = imagesx($source);
$height = imagesy($source);

// Max width for a chatbot icon is around 200px
$newWidth = 200;
$newHeight = floor($height * ($newWidth / $width));

// Create new image
$newImage = imagecreatetruecolor($newWidth, $newHeight);

// Preserve transparency
imagealphablending($newImage, false);
imagesavealpha($newImage, true);
$transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);

// Resize
imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

// Save compressed (quality 9 is max compression for PNG)
imagepng($newImage, $file, 9);
imagedestroy($source);
imagedestroy($newImage);

echo "Compression successful. New dimensions: {$newWidth}x{$newHeight}\n";
?>
