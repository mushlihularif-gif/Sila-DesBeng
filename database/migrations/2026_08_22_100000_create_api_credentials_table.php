<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel kredensial pihak ketiga: satu KATEGORI = satu BARIS.
 *
 * Menggantikan kolom-kolom API key yang sebelumnya menumpang di tabel
 * `system_settings`. Data lama otomatis dipindahkan di bawah, jadi tidak ada
 * kredensial yang hilang saat migrate dijalankan di server yang sudah terisi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_credentials', function (Blueprint $table) {
            $table->id();
            // Unik: inilah yang menjamin data tidak bisa bertumpuk per kategori.
            $table->string('category')->unique();
            // Blob JSON terenkripsi AES-256, isinya bebas mengikuti config/api_providers.php.
            $table->text('credentials')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });

        $this->pindahkanDataLama();
    }

    public function down(): void
    {
        Schema::dropIfExists('api_credentials');
    }

    /**
     * Salin kredensial dari kolom lama di system_settings ke tabel baru.
     */
    private function pindahkanDataLama(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        $lama = DB::table('system_settings')->orderBy('id')->first();

        if (! $lama) {
            return;
        }

        $ambil = function (string $kolom) use ($lama) {
            if (! Schema::hasColumn('system_settings', $kolom)) {
                return null;
            }

            $nilai = $lama->{$kolom} ?? null;

            if ($nilai === null || $nilai === '') {
                return null;
            }

            // Kolom-kolom ini tersimpan lewat cast 'encrypted' milik model lama,
            // yang memakai mode tanpa serialisasi -> harus decryptString, bukan decrypt().
            try {
                return Crypt::decryptString($nilai);
            } catch (\Throwable $e) {
                // Nilai ternyata tidak terenkripsi (mis. diisi manual lewat SQL).
                return $nilai;
            }
        };

        $mentah = function (string $kolom) use ($lama) {
            if (! Schema::hasColumn('system_settings', $kolom)) {
                return null;
            }

            $nilai = $lama->{$kolom} ?? null;

            return ($nilai === null || $nilai === '') ? null : $nilai;
        };

        $kategori = [
            'google_oauth' => array_filter([
                'client_id'     => $mentah('google_client_id'),
                'client_secret' => $ambil('google_client_secret'),
            ], fn ($v) => $v !== null),

            'google_maps' => array_filter([
                'api_key' => $ambil('google_maps_api_key'),
            ], fn ($v) => $v !== null),

            'midtrans' => array_filter([
                'merchant_id'   => $mentah('midtrans_merchant_id'),
                'server_key'    => $ambil('gateway_secret_key'),
                'client_key'    => $ambil('gateway_public_key'),
                'is_production' => Schema::hasColumn('system_settings', 'gateway_is_production')
                    ? (bool) ($lama->gateway_is_production ?? false)
                    : null,
            ], fn ($v) => $v !== null),
        ];

        $sekarang = now();

        foreach ($kategori as $nama => $isi) {
            // Hanya buat baris kalau memang ada kredensial yang tersimpan.
            if (empty($isi) || array_keys($isi) === ['is_production']) {
                continue;
            }

            DB::table('api_credentials')->updateOrInsert(
                ['category' => $nama],
                [
                    // WAJIB encryptString (serialize = false): itulah format yang
                    // dipakai cast 'encrypted:array' di model ApiCredential.
                    // Memakai encrypt() biasa akan menghasilkan nilai yang tidak
                    // bisa dibaca kembali oleh model.
                    'credentials' => Crypt::encryptString(json_encode($isi)),
                    'is_active'   => true,
                    'updated_by'  => 'migrasi otomatis',
                    'created_at'  => $sekarang,
                    'updated_at'  => $sekarang,
                ]
            );
        }
    }
};
