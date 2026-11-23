<?php

namespace App\Support;

use App\Models\LokasiLayanan;
use Illuminate\Support\Facades\Schema;

class ServiceLocation
{
    public static function forItem($item): LokasiLayanan
    {
        $name = trim((string) ($item->lokasi ?? ''));
        $itemHasPoint = is_numeric($item->latitude ?? null)
            && is_numeric($item->longitude ?? null)
            && abs((float) $item->latitude) <= 90
            && abs((float) $item->longitude) <= 180;

        if ($item->region_id && Schema::hasTable('lokasi_layanan')) {
            if ($name !== '') {
                $saved = LokasiLayanan::where('region_id', $item->region_id)->where('nama', $name)->first();
                if ($saved) {
                    return $saved;
                }
            }

            // Jika item belum memiliki titik koordinat sendiri (atau nama lokasinya
            // masih teks umum seperti nama desa), pakai titik aktif resmi desa dari
            // tabel lokasi_layanan agar peta lokasi pengambilan tetap tampil.
            if (! $itemHasPoint) {
                $defaultSaved = LokasiLayanan::where('region_id', $item->region_id)
                    ->where('is_aktif', true)
                    ->orderByRaw('CASE WHEN latitude IS NOT NULL AND longitude IS NOT NULL THEN 0 ELSE 1 END')
                    ->orderBy('id')
                    ->first();
                if ($defaultSaved) {
                    return $defaultSaved;
                }
            }
        }

        return new LokasiLayanan([
            'nama' => $name,
            'alamat' => $name,
            'latitude' => $item->latitude,
            'longitude' => $item->longitude,
        ]);
    }

    public static function pickupAddressText($item): string
    {
        $loc = static::forItem($item);
        $parts = array_values(array_unique(array_filter([
            trim((string) $loc->nama),
            trim((string) $loc->alamat),
            trim((string) ($item->region?->name ?? '')),
        ])));

        return 'Ambil di lokasi: ' . ($parts !== [] ? implode(' — ', $parts) : 'Kantor Pengelola Layanan');
    }
}
