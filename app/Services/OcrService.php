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
        $this->apiKey = env('OCR_SPACE_API_KEY', 'helloworld');
    } 
    /**
     * Extract KTP Data using OCR.Space API
     *
     * @param string $imagePath Absolute path or URL of the KTP image
     * @return array
     */
    public function extractKtpData($imagePath)
    {
        $apiKey = env('OCR_SPACE_API_KEY', 'helloworld');

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
                $parsed = $this->parseKtpText($rawText);
                
                // If NIK successfully parsed, return it
                if (!empty($parsed['nik'])) {
                    return $parsed;
                }
            }

            Log::warning('OCR.space returned empty/unparsed NIK. Trying Gemini AI fallback...');
            return $this->extractUsingGemini($imagePath);
        } catch (\Exception $e) {
            Log::error('OCR.space HTTP Error: ' . $e->getMessage() . '. Falling back to Gemini AI...');
            return $this->extractUsingGemini($imagePath);
        }
    }

    /**
     * Fallback OCR using Google Gemini Flash Vision
     */
    protected function extractUsingGemini($imagePath)
    {
        $geminiApiKey = env('GEMINI_API_KEY');
        if (empty($geminiApiKey)) {
            return [];
        }

        try {
            $imageContent = base64_encode(file_get_contents($imagePath));
            $prompt = 'Ekstrak data dari foto KTP Indonesia ini dan kembalikan HANYA dalam format JSON murni tanpa markdown/backticks: {"nik": "16 digit angka", "name": "nama lengkap", "address": "alamat jalan", "rt": "nomor rt misal 001", "rw": "nomor rw misal 002", "desa": "nama kelurahan/desa", "kecamatan": "nama kecamatan", "gender": "laki-laki/perempuan"}';

            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiApiKey}", [
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
        if (preg_match('/RT[\/\\]?RW\s*[:;=]?\s*([0-9]{1,3})\s*[\/\\]\s*([0-9]{1,3})/i', $fullText, $m)) {
            $data['rt'] = str_pad($m[1], 3, '0', STR_PAD_LEFT);
            $data['rw'] = str_pad($m[2], 3, '0', STR_PAD_LEFT);
        } elseif (preg_match('/([0-9]{1,3})\s*[\/\\]\s*([0-9]{1,3})/', $fullText, $m)) {
            $data['rt'] = str_pad($m[1], 3, '0', STR_PAD_LEFT);
            $data['rw'] = str_pad($m[2], 3, '0', STR_PAD_LEFT);
        }

        // Extract Desa/Kelurahan
        if (preg_match('/(?:KEL|DESA).*?[:;=]\s*([A-Z0-9\s]+)/i', $fullText, $m)) {
            $data['desa'] = trim(preg_replace('/[Kk][Ee][Cc].*$/i', '', $m[1]));
        }

        // Extract Kecamatan
        if (preg_match('/KECAMATAN.*?[:;=]\s*([A-Z0-9\s]+)/i', $fullText, $m)) {
            $data['kecamatan'] = trim(preg_replace('/(?:AGAMA|STATUS).*$/i', '', $m[1]));
        }

        // Extract Alamat
        if (preg_match('/ALAMAT.*?[:;=]\s*([A-Z0-9\.\-\s]+)/i', $fullText, $m)) {
            $alamat = trim($m[1]);
            // Remove RT/RW from alamat if it captured too much
            $alamat = preg_replace('/(?:RT|RW).*$/i', '', $alamat);
            if (strlen($alamat) > 2) {
                $data['address'] = trim($alamat);
            }
        }

        return $data;
    }
}
