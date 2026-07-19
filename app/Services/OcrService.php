<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrService
{
    // Using free OCR.space API key
    protected $apiKey = 'helloworld'; 

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
                'language' => 'ind',
                'isOverlayRequired' => false,
                'OCREngine' => 2 // Engine 2 is usually better for numbers/special chars
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

        $lines = explode("\n", $rawText);
        
        foreach ($lines as $line) {
            $line = trim($line);
            $upperLine = strtoupper($line);

            // NIK
            if (preg_match('/(?:NIK|N!K|N1K)\s*[:=]?\s*([0-9]{16})/i', $line, $matches)) {
                $data['nik'] = $matches[1];
            } elseif (preg_match('/([0-9]{16})/', $line, $matches) && !$data['nik']) {
                $data['nik'] = $matches[1];
            }

            // Nama
            if (preg_match('/Nama\s*[:=]?\s*(.+)/i', $line, $matches)) {
                $data['name'] = trim($matches[1]);
            }

            // Jenis Kelamin
            if (preg_match('/LAKI-LAKI|LAKI - LAKI/i', $line)) {
                $data['gender'] = 'laki-laki';
            } elseif (preg_match('/PEREMPUAN/i', $line)) {
                $data['gender'] = 'perempuan';
            }

            // RT/RW
            if (preg_match('/RT[\/|\\\]RW\s*[:=]?\s*([0-9]{1,3})\s*[\/|\\\]\s*([0-9]{1,3})/i', $line, $matches)) {
                $data['rt'] = $matches[1];
                $data['rw'] = $matches[2];
            }

            // Kel/Desa
            if (preg_match('/Kel[\/|\\\]Desa\s*[:=]?\s*(.+)/i', $line, $matches)) {
                $data['desa'] = trim($matches[1]);
            }

            // Kecamatan
            if (preg_match('/Kecamatan\s*[:=]?\s*(.+)/i', $line, $matches)) {
                $data['kecamatan'] = trim($matches[1]);
            }

            // Alamat
            if (preg_match('/Alamat\s*[:=]?\s*(.+)/i', $line, $matches)) {
                $data['address'] = trim($matches[1]);
            }
        }

        return $data;
    }
}
