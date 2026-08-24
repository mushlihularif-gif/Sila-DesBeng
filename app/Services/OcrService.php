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
        try {
            $response = Http::attach(
                'file', file_get_contents($imagePath), 'ktp.jpg'
            )->post('https://api.ocr.space/parse/image', [
                'apikey' => $this->apiKey,
                'language' => 'eng',
                'OCREngine' => '2',
                'isTable' => 'true',
                'isOverlayRequired' => 'false',
                'OCREngine' => '2' // Engine 2 is usually better for numbers/special chars
            ]);

            $result = $response->json();

            if (isset($result['ParsedResults'][0]['ParsedText'])) {
                $rawText = $result['ParsedResults'][0]['ParsedText'];
                return $this->parseKtpText($rawText);
            }

            Log::error('OCR API Error: ' . json_encode($result));
            return [];
        } catch (\Exception $e) {
            Log::error('OCR HTTP Error: ' . $e->getMessage());
            return [];
        }
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
