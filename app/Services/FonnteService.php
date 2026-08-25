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
        $message = "*SiladesBeng (Sistem Layanan Desa)*\n\n";
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
            Log::warning("Fonnte Token is empty. Simulated WhatsApp message to $target: $message");
            return ['status' => true, 'message' => 'Simulated'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token
            ])->post($this->baseUrl . '/send', [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62', // Default kode negara Indonesia
            ]);

            $result = $response->json();

            if (isset($result['status']) && $result['status'] === true) {
                return ['status' => true, 'message' => 'Pesan terkirim'];
            } else {
                Log::error('Fonnte API Error: ' . json_encode($result));
                return ['status' => false, 'message' => $result['reason'] ?? 'Gagal mengirim pesan'];
            }

        } catch (\Exception $e) {
            Log::error('Fonnte HTTP Error: ' . $e->getMessage());
            return ['status' => false, 'message' => 'Terjadi kesalahan koneksi'];
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
