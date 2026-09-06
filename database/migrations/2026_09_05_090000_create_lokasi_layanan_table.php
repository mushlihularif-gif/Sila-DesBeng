<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lokasi layanan milik wilayah, dipakai bersama seluruh unit.
 *
 * Sebelumnya "lokasi tersimpan" bukan data tersendiri: tiap form unit
 * mengumpulkannya dengan SELECT DISTINCT lokasi dari tabel produknya sendiri.
 * Akibatnya lokasi hanya "ada" selama masih ada produk yang memakainya, salah
 * ketik melahirkan lokasi baru yang berdiri sendiri, koordinatnya diisi ulang
 * tiap kali, dan gudang yang sama harus diketik ulang di unit gas, sewa alat,
 * dan mobil karena daftarnya tidak saling melihat.
 *
 * Tabel ini menjadikannya data milik WILAYAH: sekali disimpan beserta titik
 * petanya, dipakai semua unit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lokasi_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->string('nama');
            $table->text('alamat')->nullable();
            // 7 angka desimal ~ ketelitian 1 cm, jauh lebih dari cukup dan
            // sama dengan yang dipakai kolom lokasi di tabel unit.
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('catatan')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();

            $table->unique(['region_id', 'nama']);
        });

        // Pindahkan lokasi yang sudah terlanjur diketik di tiap unit supaya
        // admin tidak kehilangan apa yang sudah mereka isi.
        $sumber = ['gas', 'barang', 'mobils', 'fasilitas_umums', 'pasar_produks'];
        $sekarang = now();

        foreach ($sumber as $tabel) {
            if (! Schema::hasTable($tabel) || ! Schema::hasColumn($tabel, 'lokasi')) {
                continue;
            }

            $baris = DB::table($tabel)
                ->select('region_id', 'lokasi', 'latitude', 'longitude')
                ->whereNotNull('region_id')
                ->whereNotNull('lokasi')
                ->where('lokasi', '!=', '')
                ->get();

            foreach ($baris as $b) {
                $nama = trim($b->lokasi);
                if ($nama === '') {
                    continue;
                }

                $ada = DB::table('lokasi_layanan')
                    ->where('region_id', $b->region_id)
                    ->where('nama', $nama)
                    ->first();

                if ($ada) {
                    // Koordinat diisi hanya kalau baris lama belum punya —
                    // baris pertama yang membawa titik peta yang dipakai.
                    if (! $ada->latitude && $b->latitude) {
                        DB::table('lokasi_layanan')->where('id', $ada->id)->update([
                            'latitude'  => $b->latitude,
                            'longitude' => $b->longitude,
                        ]);
                    }
                    continue;
                }

                DB::table('lokasi_layanan')->insert([
                    'region_id'  => $b->region_id,
                    'nama'       => $nama,
                    'latitude'   => $b->latitude,
                    'longitude'  => $b->longitude,
                    'is_aktif'   => true,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lokasi_layanan');
    }
};
