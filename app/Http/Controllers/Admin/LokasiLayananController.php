<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LokasiLayanan;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Kelola titik layanan milik wilayah — gudang, kantor desa, pangkalan gas.
 *
 * Satu daftar untuk semua unit. Sebelum ada ini, "lokasi tersimpan" hanya hasil
 * SELECT DISTINCT dari tabel produk tiap unit, sehingga tidak bisa dikelola:
 * tidak bisa diganti nama, tidak bisa diberi titik peta sekali untuk seterusnya,
 * dan hilang begitu produk terakhir yang memakainya dihapus.
 */
class LokasiLayananController extends Controller
{
    /** Unit mana saja yang menyimpan nama lokasi, untuk menghitung pemakaian. */
    private const TABEL_UNIT = [
        'gas'             => 'Penjualan Gas',
        'barang'          => 'Penyewaan Alat',
        'mobils'          => 'Penyewaan Mobil',
        'fasilitas_umums' => 'Fasilitas Umum',
        'pasar_produks'   => 'Pasar Daerah',
    ];

    private function pastikanAdminWilayah(): void
    {
        abort_unless(
            in_array(auth()->user()?->role, ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa', 'staff'], true),
            403,
            'Halaman lokasi layanan hanya untuk pengelola wilayah.'
        );
    }

    private function wilayahSaya(): ?int
    {
        return auth()->user()->region_id;
    }

    public function index()
    {
        $this->pastikanAdminWilayah();

        $regionId = $this->wilayahSaya();

        if (! $regionId) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Akun Anda belum terhubung dengan wilayah mana pun.');
        }

        $lokasi = LokasiLayanan::milikWilayah($regionId)->orderBy('nama')->get();

        // Berapa produk yang memakai tiap lokasi. Dipakai untuk memperingatkan
        // sebelum menghapus, dan menjelaskan mengapa penghapusan ditolak.
        foreach ($lokasi as $l) {
            $l->jumlah_pakai = $this->hitungPemakaian($l);
        }

        return view('admin.lokasi_layanan.index', [
            'lokasi'  => $lokasi,
            'wilayah' => Region::find($regionId),
        ]);
    }

    public function store(Request $request)
    {
        $this->pastikanAdminWilayah();
        $regionId = $this->wilayahSaya();

        $data = $this->validasi($request, $regionId);
        $data['region_id'] = $regionId;

        LokasiLayanan::create($data);

        return back()->with('success', 'Lokasi "' . $data['nama'] . '" berhasil ditambahkan.');
    }

    public function update(Request $request, LokasiLayanan $lokasiLayanan)
    {
        $this->pastikanAdminWilayah();
        $this->pastikanMilikSaya($lokasiLayanan);

        $namaLama = $lokasiLayanan->nama;
        $data = $this->validasi($request, $this->wilayahSaya(), $lokasiLayanan->id);

        $lokasiLayanan->update($data);

        // Nama lokasi ikut tersimpan sebagai teks di baris produk, jadi kalau
        // namanya diganti, baris-baris itu harus ikut disesuaikan — kalau tidak,
        // produk lama menunjuk lokasi yang namanya sudah tidak ada.
        if ($namaLama !== $data['nama']) {
            $ikut = $this->gantiNamaDiUnit($lokasiLayanan->region_id, $namaLama, $data['nama']);

            return back()->with('success', 'Lokasi diperbarui'
                . ($ikut ? ", dan {$ikut} produk yang memakainya ikut disesuaikan." : '.'));
        }

        return back()->with('success', 'Lokasi "' . $lokasiLayanan->nama . '" berhasil diperbarui.');
    }

    public function destroy(LokasiLayanan $lokasiLayanan)
    {
        $this->pastikanAdminWilayah();
        $this->pastikanMilikSaya($lokasiLayanan);

        $dipakai = $this->hitungPemakaian($lokasiLayanan);

        if ($dipakai > 0) {
            return back()->with('error', 'Lokasi "' . $lokasiLayanan->nama . '" masih dipakai '
                . $dipakai . ' produk, jadi tidak dihapus. Pindahkan produknya lebih dulu, '
                . 'atau nonaktifkan lokasi ini agar tidak muncul lagi di formulir baru.');
        }

        $nama = $lokasiLayanan->nama;
        $lokasiLayanan->delete();

        return back()->with('success', 'Lokasi "' . $nama . '" berhasil dihapus.');
    }

    /**
     * Simpan lokasi dari dalam formulir produk (tanpa pindah halaman).
     * Dipanggil lewat fetch(), membalas JSON.
     */
    public function simpanCepat(Request $request)
    {
        $this->pastikanAdminWilayah();
        $regionId = $this->wilayahSaya();

        $data = $this->validasi($request, $regionId);
        $data['region_id'] = $regionId;

        $lokasi = LokasiLayanan::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi "' . $lokasi->nama . '" disimpan dan kini tersedia di semua unit.',
            'lokasi'  => [
                'id'        => $lokasi->id,
                'nama'      => $lokasi->nama,
                'alamat'    => $lokasi->alamat,
                'latitude'  => $lokasi->latitude,
                'longitude' => $lokasi->longitude,
            ],
        ]);
    }

    private function validasi(Request $request, ?int $regionId, ?int $abaikanId = null): array
    {
        return $request->validate([
            'nama' => [
                'required', 'string', 'max:255',
                // Unik per wilayah, bukan global: nama "Kantor Desa" wajar ada
                // di banyak desa sekaligus.
                Rule::unique('lokasi_layanan', 'nama')
                    ->where(fn ($q) => $q->where('region_id', $regionId))
                    ->ignore($abaikanId),
            ],
            'alamat'    => 'nullable|string|max:1000',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'catatan'   => 'nullable|string|max:1000',
            'is_aktif'  => 'nullable|boolean',
        ], [
            'nama.required' => 'Nama lokasi wajib diisi.',
            'nama.unique'   => 'Wilayah Anda sudah punya lokasi dengan nama itu.',
            'latitude.between'  => 'Latitude harus antara -90 dan 90.',
            'longitude.between' => 'Longitude harus antara -180 dan 180.',
        ]);
    }

    private function pastikanMilikSaya(LokasiLayanan $lokasi): void
    {
        abort_unless(
            $lokasi->region_id === $this->wilayahSaya() || auth()->user()->role === 'super_admin',
            403,
            'Lokasi ini milik wilayah lain.'
        );
    }

    private function hitungPemakaian(LokasiLayanan $lokasi): int
    {
        $jumlah = 0;

        foreach (array_keys(self::TABEL_UNIT) as $tabel) {
            if (! \Illuminate\Support\Facades\Schema::hasTable($tabel)) {
                continue;
            }

            $jumlah += DB::table($tabel)
                ->where('region_id', $lokasi->region_id)
                ->where('lokasi', $lokasi->nama)
                ->count();
        }

        return $jumlah;
    }

    private function gantiNamaDiUnit(int $regionId, string $lama, string $baru): int
    {
        $ikut = 0;

        foreach (array_keys(self::TABEL_UNIT) as $tabel) {
            if (! \Illuminate\Support\Facades\Schema::hasTable($tabel)) {
                continue;
            }

            $ikut += DB::table($tabel)
                ->where('region_id', $regionId)
                ->where('lokasi', $lama)
                ->update(['lokasi' => $baru]);
        }

        return $ikut;
    }
}
