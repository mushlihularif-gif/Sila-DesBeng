<?php
$files_to_process = [
    'resources/views/admin/unit/mobil/create.blade.php',
    'resources/views/admin/unit/mobil/edit.blade.php',
    'resources/views/admin/unit/penyewaan/create.blade.php',
    'resources/views/admin/unit/penyewaan/edit.blade.php',
    'resources/views/admin/unit/penjualan_gas/create.blade.php',
    'resources/views/admin/unit/penjualan_gas/edit.blade.php'
];

$css_replacements = [
    [
        'pattern' => '/\/\* Modern Card Styling \*\/[\s\S]*?\.modern-card {[\s\S]*?transition: all 0\.3s ease;\s*}\s*\.modern-card:hover {[\s\S]*?\}/',
        'replacement' => ':root {
            --primary-color: #3b82f6; /* Smooth Blue */
            --primary-dark: #2563eb;
            --primary-light: #eff6ff;
            --primary-soft: #e0f2fe;
            --border-color: #e2e8f0;
            --bg-soft: #f8fafc;
        }

        /* Card Styling */
        .modern-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
            background: #ffffff;
        }

        .modern-card:hover {
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
        }'
    ],
    [
        'pattern' => '/\/\* Form Sections \*\/[\s\S]*?\.form-section {[\s\S]*?padding: 20px;[\s\S]*?background: #f8f9fa;[\s\S]*?border-radius: 10px;[\s\S]*?border-left: 4px solid #0d6efd;[\s\S]*?\}/',
        'replacement' => '/* Form Sections */
        .form-section {
            padding: 24px;
            background: var(--bg-soft);
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            border-top: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-section:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            border-left-width: 6px;
        }'
    ],
    [
        'pattern' => '/\.modern-input {[\s\S]*?border: 1\.5px solid #e0e6ed;[\s\S]*?border-radius: 8px;[\s\S]*?padding: 10px 14px;[\s\S]*?font-size: 14px;[\s\S]*?transition: all 0\.3s ease;[\s\S]*?background: white;[\s\S]*?\}[\s\S]*?\.modern-input:focus {[\s\S]*?border-color: #0d6efd;[\s\S]*?box-shadow: 0 0 0 3px rgba\(13, 110, 253, 0\.1\);[\s\S]*?background: white;[\s\S]*?\}/',
        'replacement' => '.modern-input {
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #ffffff;
            color: #334155;
        }

        .modern-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            background: #ffffff;
            outline: none;
        }'
    ],
    [
        'pattern' => '/\/\* Modern Buttons \*\/[\s\S]*?\.modern-btn-primary {[\s\S]*?background: linear-gradient.*?0d6efd.*?;[\s\S]*?border: none;[\s\S]*?border-radius: 8px;[\s\S]*?padding: 10px 24px;[\s\S]*?font-weight: 500;[\s\S]*?transition: all 0\.3s ease;[\s\S]*?box-shadow: 0 2px 8px rgba\(13, 110, 253, 0\.2\);[\s\S]*?\}[\s\S]*?\.modern-btn-primary:hover {[\s\S]*?transform: translateY\(-2px\);[\s\S]*?box-shadow: 0 4px 12px rgba\(13, 110, 253, 0\.3\);[\s\S]*?background: linear-gradient.*?0a58ca.*?;[\s\S]*?\}/',
        'replacement' => '/* Modern Buttons */
        .modern-btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 12px 28px;
            font-weight: 600;
            color: #ffffff;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .modern-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35);
            background: var(--primary-dark);
            color: #ffffff;
        }'
    ],
    [
        'pattern' => '/\/\* Upload Box \*\/[\s\S]*?\.upload-box {[\s\S]*?border: 2px dashed #cbd5e0;[\s\S]*?border-radius: 10px;[\s\S]*?padding: 20px;[\s\S]*?text-align: center;[\s\S]*?cursor: pointer;[\s\S]*?transition: all 0\.3s ease;[\s\S]*?background: #f8f9fa;[\s\S]*?min-height: 200px;[\s\S]*?display: flex;[\s\S]*?align-items: center;[\s\S]*?justify-content: center;[\s\S]*?position: relative;[\s\S]*?\}[\s\S]*?\.upload-box:hover {[\s\S]*?border-color: #0d6efd;[\s\S]*?background: #e7f1ff;[\s\S]*?\}/',
        'replacement' => '/* Upload Box */
        .upload-box {
            border: 2px dashed var(--border-color);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #ffffff;
            aspect-ratio: 4/3;
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .upload-box:hover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }'
    ],
    [
        'pattern' => '/\.upload-placeholder i {[\s\S]*?color: #cbd5e0;[\s\S]*?transition: all 0\.3s ease;[\s\S]*?\}[\s\S]*?\.upload-box:hover \.upload-placeholder i {[\s\S]*?color: #0d6efd;[\s\S]*?transform: translateY\(-5px\);[\s\S]*?\}/',
        'replacement' => '.upload-placeholder i {
            color: #94a3b8;
            transition: all 0.3s ease;
        }

        .upload-box:hover .upload-placeholder i {
            color: var(--primary-color);
            transform: translateY(-5px) scale(1.05);
        }'
    ],
    [
        'pattern' => '/\.preview-image {[\s\S]*?width: 100%;[\s\S]*?height: 180px;[\s\S]*?object-fit: cover;[\s\S]*?border-radius: 8px;[\s\S]*?\}/',
        'replacement' => '.preview-image {
            width: 100%;
            height: 100%;
            aspect-ratio: 4/3;
            object-fit: cover;
            border-radius: 8px;
        }'
    ]
];

foreach ($files_to_process as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // 1. Update CSS
    foreach ($css_replacements as $rep) {
        $content = preg_replace($rep['pattern'], $rep['replacement'], $content);
    }
    
    // 2. Move foto section
    // Section pattern:
    // <!-- Section: Foto [anything] --> ... </div> </div> </div> </div>
    // Note: since the divs can be deeply nested, we use a more robust regex for the block or just string manipulation.
    $foto_pattern = '/(<!-- Section: Foto.*?-->\s*<div class="form-section mb-4">\s*<h6 class="section-title mb-3">\s*<i class=\'bx bx-image.*?\s*<div class="row g-3">[\s\S]*?<!-- Foto Utama -->[\s\S]*?<\/div>\s*<\/div>\s*<\/div>\s*<\/div>)/i';
    
    if (preg_match($foto_pattern, $content, $matches)) {
        $foto_block = $matches[1];
        
        // Remove it from original position
        $content = str_replace($foto_block, "", $content);
        
        // Find insert position right after @csrf or @method('PUT')
        $csrf_pos = strpos($content, '@csrf');
        if ($csrf_pos !== false) {
            $insert_pos = $csrf_pos + 5;
            $content = substr($content, 0, $insert_pos) . "\n\n" . $foto_block . "\n" . substr($content, $insert_pos);
        } else {
            $method_pos = strpos($content, "@method('PUT')");
            if ($method_pos !== false) {
                $insert_pos = $method_pos + 14;
                $content = substr($content, 0, $insert_pos) . "\n\n" . $foto_block . "\n" . substr($content, $insert_pos);
            }
        }
    }
    
    file_put_contents($file, $content);
    echo "Updated " . $file . "<br>";
}
echo "Done";
?>
