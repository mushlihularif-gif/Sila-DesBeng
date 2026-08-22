<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan operasional platform (lokasi, rekening, e-wallet, tampilan kartu,
 * fee platform). Tabel ini bersifat SINGLETON: hanya boleh berisi satu baris,
 * dijaga oleh event creating() di bawah. Ambil datanya lewat instance().
 *
 * Kredensial API pihak ketiga TIDAK lagi disimpan di sini — pindah ke
 * model ApiCredential (satu kategori = satu baris, lihat config/api_providers.php).
 */
class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_name',
        'latitude',
        'longitude',
        'address',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'ewallet_name',
        'ewallet_number',
        'ewallet_account_holder',
        'payment_methods',
        'card_background_image',
        'card_background_type',
        'card_gradient_style',
        'cash_payment_description',
        'whatsapp_number',
        'office_address',
        'operating_hours',
        'gateway_provider',
        'platform_fee_percentage',
    ];

    protected $casts = [
        'payment_methods' => 'array',
        'bank_account_number' => 'encrypted',
        'bank_account_holder' => 'encrypted',
        'ewallet_number' => 'encrypted',
        'ewallet_account_holder' => 'encrypted',
        'platform_fee_percentage' => 'decimal:2',
    ];

    /**
     * Cache per-request untuk baris tunggal.
     */
    protected static ?self $memo = null;

    protected static function booted(): void
    {
        // Penjaga singleton: tolak pembuatan baris kedua supaya data tidak bertumpuk.
        static::creating(function () {
            if (static::query()->exists()) {
                throw new \RuntimeException(
                    'system_settings hanya boleh berisi satu baris. Gunakan SystemSetting::instance() lalu save().'
                );
            }
        });

        static::saved(fn () => static::$memo = null);
        static::deleted(fn () => static::$memo = null);
    }

    /**
     * Baris tunggal pengaturan sistem. Dibuat otomatis kalau tabel masih kosong,
     * sehingga pemanggil tidak perlu lagi menangani null.
     */
    public static function instance(): self
    {
        if (static::$memo !== null) {
            return static::$memo;
        }

        $baris = static::query()->orderBy('id')->first();

        if (! $baris) {
            $baris = static::query()->create([]);
        }

        return static::$memo = $baris;
    }

    /**
     * Terapkan kredensial Midtrans ke \Midtrans\Config.
     *
     * Nilainya diambil dari config('services.midtrans.*') yang sudah lebih dulu
     * ditimpa oleh ApiCredential::applyToConfig() di AppServiceProvider — jadi
     * urutannya: panel Super Admin (DB, terenkripsi) > .env sebagai fallback.
     */
    public static function applyMidtransConfig(): void
    {
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = (bool) config('services.midtrans.is_production');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }
}
