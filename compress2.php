<?php
$files = [
    'Berita.png',
    'Pengumuman1.png'
];

foreach ($files as $file) {
    $source = __DIR__ . '/public/Admin/img/kabardaerah/' . $file;
    $destination = __DIR__ . '/public/Admin/img/kabardaerah/' . str_replace('.png', '_compressed.png', $file);

    if (file_exists($source)) {
        $img = imagecreatefrompng($source);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        // PNG compression level 0 (no compression) to 9 (maximum compression)
        imagepng($img, $destination, 9);
        imagedestroy($img);

        // Overwrite the original with compressed
        rename($destination, $source);

        echo "Compressed: " . $file . "\n";
    } else {
        echo "File not found: " . $source . "\n";
    }
}
?>
