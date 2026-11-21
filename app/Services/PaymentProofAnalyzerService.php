<?php

namespace App\Services;

use App\Models\WalletTransaction;
use App\Support\PaymentVerificationGuard;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaymentProofAnalyzerService
{
    /**
     * Pindai bukti transfer/e-wallet yang disimpan di disk public, lalu simpan
     * hasilnya ke WalletTransaction sebagai alat bantu cek nominal untuk Admin/Staf.
     * Tidak pernah otomatis mengesahkan pembayaran (manusia tetap verifikator final).
     */
    public function analyzeAndStore(
        string $referenceType,
        int $referenceId,
        ?string $relativePath,
        float $expectedSubtotal,
        float $alreadyPaid = 0.0,
        bool $forceInTests = false
    ): array {
        if (! $forceInTests && app()->runningUnitTests()) {
            return $this->evaluateAmount(null, $expectedSubtotal, $alreadyPaid, null, 'skipped');
        }

        if (! $relativePath || ! Storage::disk('public')->exists($relativePath)) {
            return $this->evaluateAmount(null, $expectedSubtotal, $alreadyPaid, null, 'unreadable');
        }

        $result = $this->analyzeStoragePath($relativePath, $expectedSubtotal, $alreadyPaid);
        WalletTransaction::simpanHasilPindaiBukti($referenceType, $referenceId, $result);

        return $result;
    }

    /**
     * Analisis gambar bukti pembayaran dari disk public.
     */
    public function analyzeStoragePath(
        string $relativePath,
        float $expectedSubtotal,
        float $alreadyPaid = 0.0
    ): array {
        $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $this->evaluateAmount(
                null,
                $expectedSubtotal,
                $alreadyPaid,
                null,
                'unreadable',
                'Bukti berformat PDF/non-gambar. Periksa nominal pada dokumen secara manual sebelum konfirmasi.'
            );
        }

        $absolutePath = Storage::disk('public')->path($relativePath);
        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            return $this->evaluateAmount(null, $expectedSubtotal, $alreadyPaid, null, 'unreadable');
        }

        $targetAmount = max(0.0, round($expectedSubtotal - $alreadyPaid, 2));

        // 1. Prioritaskan Gemini Vision (paling akurat membaca screenshot m-banking/e-wallet Indonesia)
        $geminiResult = $this->extractWithGemini($absolutePath, $ext);
        if ($geminiResult !== null && ($geminiResult['detected_amount'] ?? null) !== null) {
            return $this->evaluateAmount(
                (float) $geminiResult['detected_amount'],
                $expectedSubtotal,
                $alreadyPaid,
                $geminiResult['bank'] ?? null
            );
        }

        // 2. Fallback ke OCR.space + parser angka Rupiah
        $ocrText = $this->extractRawTextWithOcrSpace($absolutePath, $ext);
        if ($ocrText !== null && trim($ocrText) !== '') {
            $parsed = self::parseIndonesianAmount($ocrText, $targetAmount);
            if ($parsed !== null) {
                return $this->evaluateAmount($parsed, $expectedSubtotal, $alreadyPaid, null);
            }
        }

        return $this->evaluateAmount(
            null,
            $expectedSubtotal,
            $alreadyPaid,
            $geminiResult['bank'] ?? null,
            'unreadable',
            'Nominal pada foto bukti belum terbaca otomatis. Periksa nominal di foto bukti dan mutasi rekening secara manual sebelum konfirmasi.'
        );
    }

    /**
     * Evaluasi kecocokan nominal terdeteksi terhadap subtotal tagihan (dan sisa tagihan bila sudah ada cicilan/kurang bayar).
     */
    public function evaluateAmount(
        ?float $detectedAmount,
        float $expectedSubtotal,
        float $alreadyPaid = 0.0,
        ?string $bank = null,
        ?string $overrideStatus = null,
        ?string $customMessage = null
    ): array {
        $expectedSubtotal = round(max(0.0, $expectedSubtotal), 2);
        $alreadyPaid = round(max(0.0, $alreadyPaid), 2);
        $remainingTarget = round(max(0.0, $expectedSubtotal - $alreadyPaid), 2);
        $nowIso = now()->toIso8601String();

        if ($detectedAmount === null || $overrideStatus === 'unreadable' || $overrideStatus === 'skipped') {
            return [
                'status' => $overrideStatus ?? 'unreadable',
                'detected_amount' => null,
                'expected_amount' => $expectedSubtotal,
                'remaining_target' => $remainingTarget,
                'shortfall' => 0.0,
                'excess' => 0.0,
                'bank' => $bank,
                'message' => $customMessage ?? 'Periksa nominal pada foto bukti transfer secara manual sebelum mengonfirmasi uang diterima.',
                'scanned_at' => $nowIso,
            ];
        }

        $detected = round($detectedAmount, 2);

        // Cocok jika sesuai sisa tagihan (kasus pelunasan) ATAU sesuai subtotal penuh
        $matchesRemaining = abs($detected - $remainingTarget) < 1.0;
        $matchesFull = abs($detected - $expectedSubtotal) < 1.0;

        if ($matchesRemaining || $matchesFull) {
            $msg = sprintf(
                'Nominal terbaca pada bukti: Rp %s — sesuai dengan %s.',
                number_format($detected, 0, ',', '.'),
                ($alreadyPaid > 0 && $matchesRemaining) ? 'sisa tagihan pelunasan' : 'subtotal tagihan'
            );

            return [
                'status' => 'matched',
                'detected_amount' => $detected,
                'expected_amount' => $expectedSubtotal,
                'remaining_target' => $remainingTarget,
                'shortfall' => 0.0,
                'excess' => 0.0,
                'bank' => $bank,
                'message' => $msg,
                'scanned_at' => $nowIso,
            ];
        }

        $shortfall = max(0.0, round($remainingTarget - $detected, 2));
        $excess = max(0.0, round($detected - $remainingTarget, 2));

        if ($shortfall > 0) {
            $msg = sprintf(
                'PERINGATAN KURANG BAYAR: Bukti terbaca Rp %s, kurang Rp %s dari %s (Rp %s)! Cek mutasi rekening sebelum konfirmasi.',
                number_format($detected, 0, ',', '.'),
                number_format($shortfall, 0, ',', '.'),
                $alreadyPaid > 0 ? 'sisa tagihan' : 'subtotal tagihan',
                number_format($remainingTarget, 0, ',', '.')
            );
        } else {
            $msg = sprintf(
                'PERHATIAN SELISIH: Bukti terbaca Rp %s, lebih Rp %s dari tagihan (Rp %s). Periksa kembali foto bukti dan mutasi rekening.',
                number_format($detected, 0, ',', '.'),
                number_format($excess, 0, ',', '.'),
                number_format($remainingTarget, 0, ',', '.')
            );
        }

        return [
            'status' => 'mismatch',
            'detected_amount' => $detected,
            'expected_amount' => $expectedSubtotal,
            'remaining_target' => $remainingTarget,
            'shortfall' => $shortfall,
            'excess' => $excess,
            'bank' => $bank,
            'message' => $msg,
            'scanned_at' => $nowIso,
        ];
    }

    /**
     * Ekstrak nominal transfer dari teks mentah OCR struk bank/e-wallet Indonesia.
     * Menangani format: "Rp 20.000", "Rp200.000,00", "IDR 200,000.00", "Nominal Transfer: 20.000", dll.
     */
    public static function parseIndonesianAmount(string $rawText, ?float $expectedAmount = null): ?float
    {
        $lines = preg_split('/\r\n|\r|\n/', $rawText) ?: [];
        $priorityCandidates = [];
        $currencyCandidates = [];
        $keywords = '/\b(total(?:\s+bayar|\s+transfer|\s+transaksi|\s+pembayaran)?|nominal(?:\s+transfer)?|jumlah(?:\s+transfer|\s+bayar)?|amount|nilai\s+transfer)\b/i';
        $ignoreKeywords = '/\b(biaya\s+admin|admin\s+fee|fee|saldo|balance|poin|cashback|diskon|no\.?\s*rek|rekening|ref|referensi)\b/i';

        foreach ($lines as $idx => $line) {
            $lineTrim = trim($line);
            if ($lineTrim === '') {
                continue;
            }

            $isIgnored = preg_match($ignoreKeywords, $lineTrim) === 1;
            $hasKeyword = preg_match($keywords, $lineTrim) === 1;

            // Gabungkan dengan baris berikutnya jika baris saat ini hanya label (misal "Nominal Transfer\nRp 20.000")
            $searchText = $lineTrim;
            if ($hasKeyword && isset($lines[$idx + 1])) {
                $searchText .= ' ' . trim($lines[$idx + 1]);
            }

            // 1. Cari format eksplisit dengan Rp / IDR
            if (preg_match_all('/(?:rp\.?|idr)\s*([0-9]{1,3}(?:[.,][0-9]{3})+(?:[.,][0-9]{1,2})?|[0-9]{4,9}(?:[.,][0-9]{1,2})?)/i', $searchText, $matches)) {
                foreach ($matches[1] as $rawNum) {
                    $val = PaymentVerificationGuard::parseAmount($rawNum);
                    if ($val !== null && $val >= 1000 && $val <= 1000000000) {
                        if ($hasKeyword && ! $isIgnored) {
                            $priorityCandidates[] = $val;
                        } elseif (! $isIgnored) {
                            $currencyCandidates[] = $val;
                        }
                    }
                }
            }

            // 2. Jika ada keyword nominal/total tanpa prefix Rp (misal "Nominal : 20.000")
            if ($hasKeyword && ! $isIgnored && preg_match_all('/(?:[:=]\s*|\s)([0-9]{1,3}(?:[.,][0-9]{3})+(?:[.,][0-9]{1,2})?)(?!\d)/', $searchText, $matches)) {
                foreach ($matches[1] as $rawNum) {
                    $val = PaymentVerificationGuard::parseAmount($rawNum);
                    if ($val !== null && $val >= 1000 && $val <= 1000000000) {
                        $priorityCandidates[] = $val;
                    }
                }
            }
        }

        $pool = ! empty($priorityCandidates) ? $priorityCandidates : $currencyCandidates;
        if (empty($pool)) {
            return null;
        }

        // Jika ada kandidat yang persis sama dengan tagihan, kembalikan langsung.
        if ($expectedAmount !== null) {
            foreach ($pool as $val) {
                if (abs($val - $expectedAmount) < 1.0) {
                    return $val;
                }
            }
        }

        // Prioritaskan kandidat dari baris Total/Nominal terbesar (biasanya total transfer utama, bukan biaya admin Rp 2.500)
        return max($pool);
    }

    protected function extractWithGemini(string $absolutePath, string $ext): ?array
    {
        $apiKey = config('services.gemini.api_key');
        if (empty($apiKey)) {
            return null;
        }

        try {
            $mime = match ($ext) {
                'png' => 'image/png',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
            $imageBase64 = base64_encode((string) file_get_contents($absolutePath));
            $model = config('services.gemini.model', 'gemini-2.5-flash');
            $prompt = 'Anda adalah pemeriksa bukti transfer/pembayaran Indonesia (BRI, BCA, Mandiri, BNI, BSI, QRIS, DANA, OVO, GoPay, ShopeePay, struk ATM). '
                . 'Baca nominal uang yang benar-benar ditransfer/dibayar (BUKAN biaya admin dan BUKAN nomor rekening/referensi). '
                . 'Kembalikan HANYA JSON valid tanpa markdown: '
                . '{"is_transfer_proof": true/false, "detected_amount": angka_numerik_tanpa_titik_atau_null, "bank": "nama bank/e-wallet atau null"}. '
                . 'Contoh jika Rp 20.000 maka detected_amount = 20000. Jika Rp 200.000,00 maka detected_amount = 200000. Jika tidak terbaca, detected_amount = null.';

            $response = Http::timeout(15)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                [
                    'contents' => [[
                        'parts' => [
                            ['text' => $prompt],
                            ['inline_data' => ['mime_type' => $mime, 'data' => $imageBase64]],
                        ],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.0,
                        'response_mime_type' => 'application/json',
                    ],
                ]
            );

            if (! $response->successful()) {
                return null;
            }

            $text = $response->json('candidates.0.content.parts.0.text', '{}');
            $clean = trim(str_replace(['```json', '```'], '', (string) $text));
            $decoded = json_decode($clean, true);
            if (! is_array($decoded)) {
                return null;
            }

            $amount = isset($decoded['detected_amount']) && $decoded['detected_amount'] !== null
                ? PaymentVerificationGuard::parseAmount($decoded['detected_amount'])
                : null;

            return [
                'is_transfer_proof' => (bool) ($decoded['is_transfer_proof'] ?? false),
                'detected_amount' => ($amount !== null && $amount > 0) ? $amount : null,
                'bank' => is_string($decoded['bank'] ?? null) ? trim($decoded['bank']) : null,
            ];
        } catch (\Throwable $e) {
            Log::warning('PaymentProofAnalyzer Gemini scan skipped: ' . $e->getMessage());

            return null;
        }
    }

    protected function extractRawTextWithOcrSpace(string $absolutePath, string $ext): ?string
    {
        $apiKey = config('services.ocr_space.api_key');
        if (empty($apiKey) || $apiKey === 'helloworld') {
            return null;
        }

        try {
            $response = Http::attach(
                'file',
                (string) file_get_contents($absolutePath),
                'proof.' . $ext
            )->timeout(15)->post('https://api.ocr.space/parse/image', [
                'apikey' => $apiKey,
                'language' => 'eng',
                'OCREngine' => '2',
                'isTable' => 'true',
                'isOverlayRequired' => 'false',
            ]);

            if (! $response->successful()) {
                return null;
            }

            $text = $response->json('ParsedResults.0.ParsedText');

            return is_string($text) ? $text : null;
        } catch (\Throwable $e) {
            Log::warning('PaymentProofAnalyzer OCR.space scan skipped: ' . $e->getMessage());

            return null;
        }
    }
}
