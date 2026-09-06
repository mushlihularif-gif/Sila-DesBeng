<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrService
{
    // Using free OCR.space API key
    protected $apiKey;

    public function __construct()
    {
        // config(), bukan env(): supaya key dari panel Super Admin ikut terpakai
        // (dan tetap benar setelah `artisan config:cache` di server).
        $this->apiKey = config('services.ocr_space.api_key', 'helloworld');
    } 
    /**
     * Extract KTP Data using OCR.Space API
     *
     * @param string $imagePath Absolute path or URL of the KTP image
     * @return array
     */
    public function extractKtpData($imagePath)
    {
        $apiKey = config('services.ocr_space.api_key', 'helloworld');

        try {
            $response = Http::attach(
                'file', file_get_contents($imagePath), 'ktp.jpg'
            )->timeout(30)->post('https://api.ocr.space/parse/image', [
                'apikey' => $apiKey,
                'language' => 'eng',
                'OCREngine' => '2',
                'isTable' => 'true',
                'isOverlayRequired' => 'false',
                'OCREngine' => '2' // Engine 2 is best for ID numbers and structured cards
            ]);

            $result = $response->json();

            if (isset($result['ParsedResults'][0]['ParsedText'])) {
                $rawText = $result['ParsedResults'][0]['ParsedText'];

                // Penguraian dipisahkan dari panggilan HTTP. Sebelumnya keduanya
                // satu try/catch, sehingga kesalahan di parser dicatat sebagai
                // "OCR.space HTTP Error" - jejak yang menyesatkan justru saat
                // masalahnya ada di kode sendiri, bukan di layanan luar.
                try {
                    $parsed = $this->parseKtpText($rawText);
                } catch (\Throwable $e) {
                    Log::error('OCR: penguraian teks KTP gagal (bug parser, bukan layanan luar): ' . $e->getMessage());
                    $parsed = [];
                }

                // If NIK successfully parsed, return it
                if (!empty($parsed['nik'])) {
                    return $parsed;
                }

                Log::warning('OCR.space membaca teks tetapi NIK tidak ditemukan. Mencoba Gemini...', [
                    'panjang_teks' => strlen($rawText),
                ]);

                return $this->extractUsingGemini($imagePath);
            }

            Log::warning('OCR.space tidak mengembalikan teks sama sekali. Mencoba Gemini...', [
                'error' => $result['ErrorMessage'] ?? null,
            ]);

            return $this->extractUsingGemini($imagePath);
        } catch (\Exception $e) {
            Log::error('OCR.space gagal dihubungi: ' . $e->getMessage() . '. Beralih ke Gemini...');
            return $this->extractUsingGemini($imagePath);
        }
    }

    /**
     * Fallback OCR using Google Gemini Flash Vision
     */
    protected function extractUsingGemini($imagePath)
    {
        $geminiApiKey = config('services.gemini.api_key');
        if (empty($geminiApiKey)) {
            return [];
        }

        try {
            $imageContent = base64_encode(file_get_contents($imagePath));
            $prompt = 'Ekstrak data dari foto KTP Indonesia ini dan kembalikan HANYA dalam format JSON murni tanpa markdown/backticks: {"nik": "16 digit angka", "name": "nama lengkap", "address": "alamat jalan", "rt": "nomor rt misal 001", "rw": "nomor rw misal 002", "desa": "nama kelurahan/desa", "kecamatan": "nama kecamatan", "gender": "laki-laki/perempuan"}';

            // config(), bukan env(): setelah `artisan config:cache` di server,
            // env() mengembalikan null sehingga model jatuh ke gemini-1.5-flash
            // yang sudah dipensiunkan Google - endpoint-nya membalas 404 dan
            // jalur cadangan ini ikut mati.
            $model = config('services.gemini.model', 'gemini-2.5-flash');
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$geminiApiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => 'image/jpeg',
                                    'data' => $imageContent
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'response_mime_type' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                $cleanText = trim(str_replace(['```json', '```'], '', $text));
                $data = json_decode($cleanText, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        } catch (\Exception $e) {
            Log::error('Gemini OCR Fallback Error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Parse raw text to extract KTP fields
     */
        
    private function cleanPrefix($str) {
        $cleaned = preg_replace('/^.*?[:;=]\s*/', '', $str);
        
        $prev = "";
        while ($prev !== $cleaned) {
            $prev = $cleaned;
            $cleaned = preg_replace('/^(?:Nama|Nema|Name|Nam|Alamat|Alamet|Alam|4lamat|Tempat|Tgl|Lahir|Kel|Desa|Kecamatan|Kec|RT|RW|al|Pekerjaan|Agama|Kewarganegaraan|Status|Perkawinan|Gol|Darah)\b\s*/i', '', $cleaned);
            $cleaned = ltrim($cleaned, " ,/\\:-=");
        }
        
        return trim($cleaned);
    }

    protected function parseKtpText($rawText)
    {
        $data = [
            'nik' => null,
            'name' => null,
            'address' => null,
            'rt' => null,
            'rw' => null,
            'kecamatan' => null,
            'desa' => null,
            'gender' => null,
        ];

        // Clean up empty lines
        $lines = array_values(array_filter(array_map('trim', explode("
", $rawText))));
        
        // Find NIK
        foreach ($lines as $index => $line) {
            if (preg_match('/([0-9]{16})/', $line, $matches)) {
                $data['nik'] = $matches[1];
                
                // Name is usually the next line
                if (isset($lines[$index + 1])) {
                    $namaLine = $this->cleanPrefix($lines[$index + 1]);
                    if (strlen($namaLine) > 2) {
                        $data['name'] = $namaLine;
                    }
                }
                break;
            }
        }
        
        $fullText = strtoupper($rawText);
        
        // Extract Gender
        if (preg_match('/(?:KELAMIN|LAKI|PEREMPUAN).*?(LAKI[-\s]*LAKI|PEREMPUAN)/i', $fullText, $m)) {
            $data['gender'] = strpos(strtoupper($m[1]), 'PEREMPUAN') !== false ? 'perempuan' : 'laki-laki';
        }
        
        // Extract RT / RW using regex anywhere in the text
        //
        // Kelas karakternya dulu ditulis [\/\\]. Di dalam string berkutip
        // tunggal, '\\' menjadi SATU backslash, sehingga polanya sampai ke PCRE
        // sebagai [\/\] - tanda kurung siku penutupnya ikut ter-escape, kelasnya
        // tidak pernah tertutup, dan preg_match() gagal dikompilasi:
        //
        //   preg_match(): Compilation failed: unmatched closing parenthesis
        //
        // ErrorException-nya tertangkap catch(\Exception) di extractKtpData(),
        // dicatat sebagai "OCR.space HTTP Error" yang menyesatkan, lalu jatuh ke
        // Gemini. Akibatnya SETIAP unggahan KTP ditolak "bukan e-KTP yang valid"
        // sebagus apa pun fotonya. Sekarang pemisahnya memakai delimiter ~ agar
        // garis miring tidak perlu di-escape sama sekali.
        if (preg_match('~RT[/\\\\]?RW\s*[:;=]?\s*([0-9]{1,3})\s*[/\\\\]\s*([0-9]{1,3})~i', $fullText, $m)) {
            $data['rt'] = str_pad($m[1], 3, '0', STR_PAD_LEFT);
            $data['rw'] = str_pad($m[2], 3, '0', STR_PAD_LEFT);
        } elseif (preg_match('~([0-9]{1,3})\s*[/\\\\]\s*([0-9]{1,3})~', $fullText, $m)) {
            $data['rt'] = str_pad($m[1], 3, '0', STR_PAD_LEFT);
            $data['rw'] = str_pad($m[2], 3, '0', STR_PAD_LEFT);
        }

        // Extract Desa/Kelurahan
        //
        // Labelnya dibatasi \b: pola lama (?:KEL|DESA) ikut cocok di tengah kata
        // "JENIS KELAMIN", lalu [:;=] menyambar titik dua baris itu, sehingga
        // desa terbaca "LAKI". Nilai itu kemudian dipakai
        // KycReviewController::approve() untuk menentukan wilayah warga.
        //
        // Tangkapannya juga dibatasi satu baris ([^\r\n]+): kelas [A-Z0-9\s]
        // memuat \s yang mencakup baris baru, jadi bisa melahap beberapa baris
        // sekaligus.
        if (preg_match('~\b(?:KEL(?:URAHAN)?|DESA)\b[^:\r\n]*[:;=]\s*([^\r\n]+)~i', $fullText, $m)) {
            $data['desa'] = trim(preg_replace('~\bKEC\b.*$~i', '', $m[1]));
        }

        // Extract Kecamatan
        if (preg_match('~\bKECAMATAN\b[^:\r\n]*[:;=]\s*([^\r\n]+)~i', $fullText, $m)) {
            $data['kecamatan'] = trim(preg_replace('~\b(?:AGAMA|STATUS)\b.*$~i', '', $m[1]));
        }

        // Extract Alamat
        if (preg_match('~\bALAMAT\b[^:\r\n]*[:;=]\s*([^\r\n]+)~i', $fullText, $m)) {
            $alamat = trim($m[1]);
            // Remove RT/RW from alamat if it captured too much
            $alamat = preg_replace('~\b(?:RT|RW)\b.*$~i', '', $alamat);
            if (strlen($alamat) > 2) {
                $data['address'] = trim($alamat);
            }
        }

        return $data;
    }
}
