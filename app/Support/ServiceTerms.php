<?php

namespace App\Support;

use App\Models\Region;

class ServiceTerms
{
    public const RENTAL_LIABILITY = "1. Penyewa wajib menjaga barang sewaan dengan baik.\n2. Jika terjadi KERUSAKAN atau KEHILANGAN barang selama masa penyewaan, maka SEPENUHNYA menjadi tanggung jawab PENGGUNA (penyewa) untuk mengganti rugi atau memperbaiki alat tersebut sesuai dengan nilai barang.\n3. Keterlambatan pengembalian dapat dikenakan denda sesuai ketentuan yang berlaku.";

    public const RENTAL_NO_LIABILITY = "1. Penyewa wajib menjaga barang sewaan dengan baik.\n2. Jika terjadi kerusakan atau kehilangan barang selama masa penyewaan yang diakibatkan oleh faktor ketidaksengajaan/bencana, maka TIDAK DITANGGUNG oleh pengguna (penyewa) karena telah didukung oleh dana operasional.\n3. Namun pengguna tetap diwajibkan melaporkan kejadian tersebut secara transparan.";

    public const FACILITY_LIABILITY = "1. Penyewa wajib menjaga fasilitas umum dengan baik.\n2. Jika terjadi KERUSAKAN fasilitas selama masa peminjaman/penyewaan, maka SEPENUHNYA menjadi tanggung jawab PENGGUNA (penyewa) untuk mengganti rugi atau memperbaiki fasilitas tersebut sesuai dengan kerusakan.\n3. Fasilitas harus dikembalikan dalam keadaan bersih dan rapi.";

    public const FACILITY_NO_LIABILITY = "1. Penyewa wajib menjaga fasilitas umum dengan baik.\n2. Jika terjadi kerusakan fasilitas selama masa peminjaman/penyewaan yang diakibatkan oleh faktor ketidaksengajaan/bencana, maka TIDAK DITANGGUNG oleh pengguna karena telah didukung oleh dana operasional.\n3. Namun pengguna tetap diwajibkan melaporkan kejadian tersebut secara transparan dan menjaga kebersihan.";

    public static function forRegion(?Region $region, string $service): string
    {
        $key = $service === 'rental' ? 'sop_penyewaan' : 'sop_fasilitas';
        $info = $region?->payment_info ?? [];
        $active = ($info[$key.'_active'] ?? 'ditanggung') === 'tidak_ditanggung' ? 'tidak_ditanggung' : 'ditanggung';
        $default = $service === 'rental'
            ? ($active === 'ditanggung' ? self::RENTAL_LIABILITY : self::RENTAL_NO_LIABILITY)
            : ($active === 'ditanggung' ? self::FACILITY_LIABILITY : self::FACILITY_NO_LIABILITY);

        return filled($info[$key.'_'.$active] ?? null) ? $info[$key.'_'.$active] : $default;
    }
}
