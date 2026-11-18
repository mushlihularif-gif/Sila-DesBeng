<?php

namespace App\Support;

use App\Models\LokasiLayanan;
use Illuminate\Support\Facades\Schema;

class ServiceLocation
{
    public static function forItem($item): LokasiLayanan
    {
        $name = trim((string) ($item->lokasi ?? ''));
        if ($name !== '' && $item->region_id && Schema::hasTable('lokasi_layanan')) {
            $saved = LokasiLayanan::where('region_id', $item->region_id)->where('nama', $name)->first();
            if ($saved) {
                return $saved;
            }
        }

        return new LokasiLayanan([
            'nama' => $name,
            'alamat' => $name,
            'latitude' => $item->latitude,
            'longitude' => $item->longitude,
        ]);
    }
}
