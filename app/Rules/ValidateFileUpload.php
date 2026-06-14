<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidateFileUpload implements ValidationRule
{
    /**
     * Ekstensi file yang diblokir (berbahaya).
     *
     * @var array<int, string>
     */
    protected array $blacklistedExtensions = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phar', 'phps',
        'cgi', 'pl', 'asp', 'aspx', 'jsp', 'sh', 'bash',
        'exe', 'bat', 'cmd', 'com',
        'htaccess', 'env',
    ];

    /**
     * Tipe MIME yang diizinkan.
     *
     * @var array<int, string>
     */
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
    ];

    /**
     * Run the validation rule.
     *
     * Memvalidasi file upload dengan memeriksa:
     * 1. Ekstensi file tidak ada di daftar hitam
     * 2. Tipe MIME file sesuai dengan yang diizinkan
     * 3. Tidak ada double extension yang mencurigakan
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail('The :attribute must be a valid uploaded file.');
            return;
        }

        $originalName = $value->getClientOriginalName();
        $extension = strtolower($value->getClientOriginalExtension());

        // Cek 1: Ekstensi file tidak boleh ada di daftar hitam
        if (in_array($extension, $this->blacklistedExtensions, true)) {
            $fail('The :attribute has a forbidden file extension (:extension).')->translate([
                'extension' => $extension,
            ]);
            return;
        }

        // Cek 2: Deteksi double extension (misal: file.php.jpg)
        $nameParts = explode('.', $originalName);
        if (count($nameParts) > 2) {
            // Periksa setiap bagian nama file untuk ekstensi berbahaya
            foreach (array_slice($nameParts, 1, -1) as $part) {
                if (in_array(strtolower($part), $this->blacklistedExtensions, true)) {
                    $fail('The :attribute contains a suspicious double extension.');
                    return;
                }
            }
        }

        // Cek 3: Validasi MIME type yang sebenarnya (bukan dari client)
        $mimeType = $value->getMimeType();
        if (! in_array($mimeType, $this->allowedMimeTypes, true)) {
            $fail('The :attribute has an invalid file type. Allowed types: JPEG, PNG, GIF, WebP, PDF.');
            return;
        }

        // Cek 4: Deteksi Malware/PHP Code injection di dalam file
        // Membaca isi file untuk mencari tag PHP atau signature executable
        $fileContent = file_get_contents($value->getRealPath());
        if ($fileContent !== false) {
            $suspiciousPatterns = [
                '<?php',
                '<?=',
                '<script language="php">',
                'eval(',
                'base64_decode(',
                'system(',
                'shell_exec(',
                'exec(',
            ];

            foreach ($suspiciousPatterns as $pattern) {
                if (stripos($fileContent, $pattern) !== false) {
                    $fail('The :attribute contains suspicious/malicious content (malware signature detected).');
                    return;
                }
            }
        }
    }
}
