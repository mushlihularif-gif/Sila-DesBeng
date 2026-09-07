<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AlamatWarga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Buku alamat warga — simpan sekali, pakai berulang saat memesan.
 */
class AlamatWargaController extends Controller
{
    /** Batas wajar sekaligus penjaga: buku alamat bukan tempat menumpuk data. */
    private const MAKS_ALAMAT = 10;

    public function store(Request $request)
    {
        $user = Auth::user();

        if (AlamatWarga::milik($user->id)->count() >= self::MAKS_ALAMAT) {
            return back()->with('error',
                'Anda sudah menyimpan ' . self::MAKS_ALAMAT . ' alamat. Hapus salah satunya dulu.');
        }

        $data = $this->validasi($request);
        $data['user_id'] = $user->id;

        // Alamat pertama otomatis jadi utama — tanpa ini warga bisa punya
        // beberapa alamat tanpa satu pun yang terpilih saat memesan.
        $pertama = AlamatWarga::milik($user->id)->count() === 0;
        $data['is_utama'] = $pertama || $request->boolean('is_utama');

        $alamat = AlamatWarga::create($data);

        if ($data['is_utama']) {
            $alamat->jadikanUtama();
        }

        return back()->with('success', 'Alamat berhasil disimpan.');
    }

    public function update(Request $request, AlamatWarga $alamat)
    {
        $this->pastikanMilikSaya($alamat);

        $data = $this->validasi($request);
        $alamat->update($data);

        if ($request->boolean('is_utama')) {
            $alamat->jadikanUtama();
        }

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    public function utama(AlamatWarga $alamat)
    {
        $this->pastikanMilikSaya($alamat);
        $alamat->jadikanUtama();

        return back()->with('success', 'Alamat utama diperbarui.');
    }

    public function destroy(AlamatWarga $alamat)
    {
        $this->pastikanMilikSaya($alamat);

        $adalahUtama = $alamat->is_utama;
        $userId = $alamat->user_id;
        $alamat->delete();

        // Kalau yang dihapus adalah alamat utama, alamat tersisa yang paling
        // lama otomatis menggantikannya — supaya warga tidak berakhir tanpa
        // alamat utama tanpa menyadarinya.
        if ($adalahUtama) {
            $pengganti = AlamatWarga::milik($userId)->orderBy('id')->first();
            $pengganti?->jadikanUtama();
        }

        return back()->with('success', 'Alamat dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'label'         => 'nullable|string|max:50',
            'nama_penerima' => 'required|string|max:255',
            'no_telepon'    => 'required|string|max:20',
            'region_id'     => 'nullable|exists:regions,id',
            'detail_alamat' => 'required|string|max:1000',
            'rt'            => 'nullable|string|max:10',
            'rw'            => 'nullable|string|max:10',
            'kode_pos'      => 'nullable|string|max:10',
            'patokan'       => 'nullable|string|max:255',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
        ], [
            'nama_penerima.required' => 'Nama penerima wajib diisi.',
            'no_telepon.required'    => 'Nomor telepon wajib diisi.',
            'detail_alamat.required' => 'Detail alamat wajib diisi.',
        ]);
    }

    private function pastikanMilikSaya(AlamatWarga $alamat): void
    {
        abort_unless($alamat->user_id === Auth::id(), 403, 'Alamat ini bukan milik Anda.');
    }
}
