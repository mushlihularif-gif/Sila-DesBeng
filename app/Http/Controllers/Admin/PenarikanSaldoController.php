<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PenarikanSaldo;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Sisi Diskominfotik: menyetujui atau menolak pengajuan penarikan saldo yang
 * dikirim admin daerah. Bagian mengajukan ada di RegionSettingController,
 * karena itu bagian dari halaman Pembayaran Wilayah milik admin daerah.
 *
 * Kenapa dua controller: yang mengajukan dan yang menyetujui adalah dua peran
 * berbeda dengan penjagaan izin berbeda (staf daerah vs staf platform), jadi
 * memisahkannya mengikuti pola RegionSettingController vs SuperAdminSettingController
 * yang sudah dipakai di seluruh modul pembayaran ini.
 */
class PenarikanSaldoController extends Controller
{
    /**
     * Kabari admin wilayah lewat lonceng navbar mereka.
     *
     * Memakai AdminNotification ber-region_id (bukan NULL): lonceng menyaring
     * notifikasi ber-region_id kosong untuk super_admin/admin, dan yang
     * ber-region_id untuk admin wilayah — lihat resources/views/admin/layouts/admin.blade.php.
     */
    private function beriTahuWilayah(PenarikanSaldo $penarikan, string $judul, string $pesan): void
    {
        \App\Models\AdminNotification::create([
            'type' => 'penarikan_saldo',
            'reference_id' => $penarikan->id,
            'region_id' => $penarikan->region_id,
            'title' => $judul,
            'message' => $pesan,
            'is_read' => false,
        ]);
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'berjalan');

        $query = PenarikanSaldo::with(['region', 'pengaju', 'petugas'])
            ->orderByDesc('created_at');

        if ($status === 'berjalan') {
            $query->berjalan();
        } elseif (in_array($status, [PenarikanSaldo::SELESAI, PenarikanSaldo::GAGAL_PROSES, PenarikanSaldo::DIBATALKAN], true)) {
            $query->where('status', $status);
        }

        $penarikan = $query->paginate(20)->withQueryString();

        // Pengajuan yang terlambat dihitung terpisah supaya kelalaian memproses
        // terlihat sebagai angka, bukan tenggelam di antrean.
        $batasTerlambat = now()->subDays(PenarikanSaldo::BATAS_HARI_PROSES);

        $ringkasan = [
            'menunggu' => PenarikanSaldo::where('status', PenarikanSaldo::MENUNGGU)->count(),
            'total_menunggu' => (float) PenarikanSaldo::where('status', PenarikanSaldo::MENUNGGU)->sum('jumlah'),
            'terlambat' => PenarikanSaldo::where('status', PenarikanSaldo::MENUNGGU)
                ->where('diajukan_pada', '<=', $batasTerlambat)
                ->count(),
            'batas_hari' => PenarikanSaldo::BATAS_HARI_PROSES,
        ];

        // Posisi dana yang benar-benar dipegang Diskominfotik, berikut rincian
        // siapa memilikinya. Tanpa ini mereka menyetujui pencairan tanpa pernah
        // melihat berapa total yang ada di tangan.
        $dana = \App\Support\SaldoWilayah::ringkasanPlatform();
        $rincianWilayah = \App\Support\SaldoWilayah::rincianPerWilayah();

        return view('admin.super_sistem.penarikan_saldo', compact(
            'penarikan', 'status', 'ringkasan', 'dana', 'rincianWilayah'
        ));
    }

    public function approve(Request $request, PenarikanSaldo $penarikan)
    {
        $request->validate([
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        abort_if($penarikan->sudahSelesai(), 422, 'Pengajuan ini sudah diproses sebelumnya.');

        DB::transaction(function () use ($request, $penarikan) {
            $penarikan->update([
                'status' => PenarikanSaldo::SELESAI,
                'catatan_admin' => $request->input('catatan_admin'),
                'diproses_oleh' => auth()->id(),
                'diselesaikan_pada' => now(),
            ]);

            $this->beriTahuWilayah(
                $penarikan,
                'Penarikan Saldo Berhasil',
                'Pencairan Rp ' . number_format($penarikan->jumlah, 0, ',', '.') . ' ke '
                    . $penarikan->nama_bank . ' sudah ditransfer Diskominfotik.'
                    . ($request->filled('catatan_admin') ? ' Catatan: ' . $request->input('catatan_admin') : '')
            );
        });

        Log::info('Penarikan saldo disetujui', [
            'penarikan_id' => $penarikan->id,
            'region_id' => $penarikan->region_id,
            'jumlah' => $penarikan->jumlah,
            'oleh' => auth()->user()->email,
        ]);

        return redirect()->back()->with('success',
            'Penarikan sebesar Rp ' . number_format($penarikan->jumlah, 0, ',', '.') . ' ditandai selesai. '
            . 'Pastikan transfer manual ke rekening wilayah sudah benar-benar dikirim.');
    }

    /**
     * Tandai pengajuan TIDAK BISA DIPROSES — bukan menolak haknya.
     *
     * Uang itu milik wilayah; Diskominfotik memegangnya semata karena Midtrans
     * hanya mengizinkan satu rekening pencairan per akun. Jadi yang dilaporkan
     * di sini adalah kendala teknis (rekening salah ketik, nama tidak cocok
     * dengan data bank, dana Midtrans belum settle, pengajuan dobel), dan
     * saldonya kembali utuh ke wilayah.
     */
    public function reject(Request $request, PenarikanSaldo $penarikan)
    {
        $request->validate([
            'catatan_admin' => 'required|string|max:500',
        ], [
            'catatan_admin.required' => 'Sebutkan kendalanya, supaya wilayah tahu apa yang perlu diperbaiki.',
        ]);

        abort_if($penarikan->sudahSelesai(), 422, 'Pengajuan ini sudah diproses sebelumnya.');

        // Saldo TIDAK dipulihkan lewat transaksi terpisah — SaldoWilayah
        // menghitung ulang dari wallet_transactions setiap saat, dan yang
        // mengurangi saldo hanyalah pengajuan yang masih berjalan atau selesai.
        // Begitu statusnya berubah, baris ini berhenti ikut dihitung dengan
        // sendirinya.
        $penarikan->update([
            'status' => PenarikanSaldo::GAGAL_PROSES,
            'catatan_admin' => $request->input('catatan_admin'),
            'diproses_oleh' => auth()->id(),
            'diselesaikan_pada' => now(),
        ]);

        $this->beriTahuWilayah(
            $penarikan,
            'Penarikan Tidak Bisa Diproses',
            'Pencairan Rp ' . number_format($penarikan->jumlah, 0, ',', '.') . ' terkendala: '
                . $request->input('catatan_admin') . '. Saldo Anda kembali utuh dan bisa diajukan lagi.'
        );

        Log::info('Penarikan saldo tidak bisa diproses', [
            'penarikan_id' => $penarikan->id,
            'region_id' => $penarikan->region_id,
            'jumlah' => $penarikan->jumlah,
            'kendala' => $request->input('catatan_admin'),
            'oleh' => auth()->user()->email,
        ]);

        return redirect()->back()->with('success',
            'Ditandai tidak bisa diproses. Saldo wilayah kembali utuh dan mereka bisa mengajukan ulang.');
    }
}
