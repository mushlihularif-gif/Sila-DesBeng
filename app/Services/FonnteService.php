<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected $token;
    protected $baseUrl;

    public function __construct()
    {
        $this->token = config('fonnte.token');
        $this->baseUrl = config('fonnte.base_url', 'https://api.fonnte.com');
    }

    /**
     * Mengirim pesan OTP via WhatsApp menggunakan Fonnte API
     *
     * @param string $target Nomor WhatsApp tujuan (format 08... atau 628...)
     * @param string $otpCode Kode OTP 4 digit
     * @return array
     */
    public function sendOtp($target, $otpCode)
    {
        $message = "*SegiDaerah (Sistem Sinergi Layanan dan Aspirasi Daerah Kecamatan Bengkalis)*\n\n";
        $message .= "Kode OTP Anda adalah: *$otpCode*\n\n";
        $message .= "_Kode ini rahasia. Jangan berikan kepada siapa pun._";

        return $this->sendMessage($target, $message);
    }

    /**
     * Mengirim pesan notifikasi umum via WhatsApp
     *
     * @param string $target Nomor WhatsApp tujuan
     * @param string $message Isi pesan
     * @return array
     */
    public function sendNotification($target, $message)
    {
        return $this->sendMessage($target, $message);
    }

    /**
     * Method inti untuk melakukan HTTP Request ke API Fonnte
     *
     * @param string $target
     * @param string $message
     * @return array
     */
    protected function sendMessage($target, $message)
    {
        // Normalisasi nomor telepon
        $target = $this->normalizePhoneNumber($target);

        // Jika tidak ada token (misal di local yang belum disetting), log saja
        if (empty($this->token)) {
            Log::warning('Fonnte is not configured; WhatsApp message was not sent.');
            return ['status' => false, 'message' => 'Pengiriman WhatsApp belum dikonfigurasi.', 'code' => 'not_configured'];
        }

        try {
            // Batas waktu eksplisit: tanpa ini satu API yang menggantung
            // ikut menyandera proses pendaftaran warga sampai batas bawaan.
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->withHeaders([
                    'Authorization' => $this->token
                ])->post($this->baseUrl . '/send', [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62', // Default kode negara Indonesia
            ]);

            $result = $response->json();

            if (isset($result['status']) && $result['status'] === true) {
                return ['status' => true, 'message' => 'Pesan diterima penyedia untuk dikirim.', 'provider_id' => $result['id'][0] ?? null];
            } else {
                Log::warning('Fonnte rejected a send request.', ['status' => $response->status()]);
                return ['status' => false, 'message' => 'Penyedia belum menerima pesan.', 'code' => $response->status() === 429 ? 'rate_limited' : 'provider_rejected', 'retryable' => $response->status() === 429];
            }

        } catch (\Exception $e) {
            Log::warning('Fonnte request did not return a confirmed result.');
            return ['status' => false, 'message' => 'Hasil pengiriman belum dapat dikonfirmasi.', 'code' => 'unknown'];
        }
    }

    /**
     * Normalisasi nomor telepon agar selalu bisa dibaca Fonnte
     *
     * @param string $number
     * @return string
     */
    protected function normalizePhoneNumber($number)
    {
        // Hapus karakter selain angka
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // Fonnte bisa menerima 08... atau 628... dengan aman
        // Tapi kita biarkan saja sesuai input asal bersih dari karakter
        return $number;
    }
}
