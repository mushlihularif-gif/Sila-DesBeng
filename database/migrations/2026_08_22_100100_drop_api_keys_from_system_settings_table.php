<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Buang kolom kredensial dari system_settings setelah dipindahkan ke
 * tabel api_credentials oleh migration sebelumnya.
 *
 * Tujuannya menghilangkan sumber data ganda: sejak sekarang kredensial HANYA
 * hidup di api_credentials, sedangkan system_settings kembali murni berisi
 * pengaturan operasional (lokasi, rekening, e-wallet, tampilan kartu, fee).
 *
 * `gateway_provider` dan `platform_fee_percentage` sengaja DIPERTAHANKAN
 * karena keduanya pengaturan bisnis, bukan kredensial.
 */
return new class extends Migration
{
    private array $kolom = [
        'google_client_id',
        'google_client_secret',
        'google_maps_api_key',
        'midtrans_merchant_id',
        'gateway_secret_key',
        'gateway_public_key',
        'gateway_is_production',
    ];

    public function up(): void
    {
        $adaSekarang = array_values(array_filter(
            $this->kolom,
            fn ($kolom) => Schema::hasColumn('system_settings', $kolom)
        ));

        if (empty($adaSekarang)) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table) use ($adaSekarang) {
            $table->dropColumn($adaSekarang);
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('system_settings', 'gateway_secret_key')) {
                $table->text('gateway_secret_key')->nullable()->after('gateway_provider');
            }
            if (! Schema::hasColumn('system_settings', 'gateway_public_key')) {
                $table->text('gateway_public_key')->nullable()->after('gateway_secret_key');
            }
            if (! Schema::hasColumn('system_settings', 'gateway_is_production')) {
                $table->boolean('gateway_is_production')->default(false)->after('gateway_public_key');
            }
            if (! Schema::hasColumn('system_settings', 'google_client_id')) {
                $table->string('google_client_id')->nullable()->after('platform_fee_percentage');
            }
            if (! Schema::hasColumn('system_settings', 'google_client_secret')) {
                $table->text('google_client_secret')->nullable()->after('google_client_id');
            }
            if (! Schema::hasColumn('system_settings', 'google_maps_api_key')) {
                $table->text('google_maps_api_key')->nullable()->after('google_client_secret');
            }
            if (! Schema::hasColumn('system_settings', 'midtrans_merchant_id')) {
                $table->string('midtrans_merchant_id')->nullable()->after('google_maps_api_key');
            }
        });
    }
};
