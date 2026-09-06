<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PenarikanSaldo;
use App\Models\Region;
use App\Models\WalletTransaction;
use App\Support\SaldoWilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Halaman Keuangan wilayah: saldo Midtrans dan pencairannya.
 *
 * Dipisah dari Pengaturan → Pembayaran Wilayah dengan sengaja. Yang di
 * Pengaturan sifatnya konfigurasi sekali-atur (nomor rekening, sakelar
 * pembayaran otomatis); yang di sini pekerjaan berulang: melihat uang masuk
 * dan mencairkannya. Dua kebiasaan pemakaian yang berbeda, jadi dua halaman.
 */
class KeuanganController extends Controller
{
    /**
     * Uang wilayah bukan urusan operasional unit. Kepala desa/camat yang
     * bertanggung jawab atasnya, jadi staf unit tidak boleh mengajukan
     * pencairan meski route-nya kebetulan lolos 'role:admin' (peran semu itu
     * ikut meloloskan staff - lihat CheckRole::handle).
     */
    private function pastikanAdminWilayah(): void
    {
        abort_unless(
            in_array(auth()->user()?->role, ['admin', 'admin_kecamatan', 'admin_desa', 'super_admin'], true),
            403,
            'Halaman keuangan wilayah khusus admin wilayah.'
        );
    }

    public function index()
    {
        $this->pastikanAdminWilayah();

        $region = Region::find(auth()->user()->region_id);

        if (! $region) {
            return redirect()->route('admin.dashboard')->with('error', 'Anda tidak terhubung dengan wilayah mana pun.');
        }

        $saldo = SaldoWilayah::ringkasan($region->id);
        $totalPemasukan = SaldoWilayah::totalPemasukan($region->id);
        $pesananSelesai = SaldoWilayah::jumlahPesananSelesai($region->id);

        $penarikanBerjalan = PenarikanSaldo::where('region_id', $region->id)->berjalan()->first();

        $riwayatPenarikan = PenarikanSaldo::where('region_id', $region->id)
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'penarikan');

        // Riwayat pemasukan: dari mana saja saldo ini terkumpul, supaya angka
        // besar di kartu atas bisa ditelusuri per pesanan.
        $riwayatPemasukan = WalletTransaction::where('region_id', $region->id)
            ->where('source', 'gateway')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'pemasukan');

        $info = $region->payment_info ?? [];
        $tujuan = [
            'bank' => [
                'tersedia' => filled($info['bank_name'] ?? null) && filled($info['account_number'] ?? null),
                'nama'     => $info['bank_name'] ?? null,
                'nomor'    => $info['account_number'] ?? null,
                'pemilik'  => $info['account_name'] ?? null,
            ],
            'ewallet' => [
                'tersedia' => filled($info['ewallet_name'] ?? null) && filled($info['ewallet_number'] ?? null),
                'nama'     => $info['ewallet_name'] ?? null,
                'nomor'    => $info['ewallet_number'] ?? null,
                'pemilik'  => $info['ewallet_account_name'] ?? null,
            ],
        ];

        $minimalPenarikan = PenarikanSaldo::MINIMAL_PENARIKAN;

        return view('admin.keuangan.index', compact(
            'region', 'saldo', 'totalPemasukan', 'pesananSelesai',
            'penarikanBerjalan', 'riwayatPenarikan', 'riwayatPemasukan',
            'tujuan', 'minimalPenarikan'
        ));
    }

    /**
     * Ajukan pencairan saldo ke rekening bank atau e-wallet wilayah.
     *
     * Saldo tidak dipotong ke kolom manapun — SaldoWilayah menghitung ulang
     * dari wallet_transactions dikurangi seluruh pengajuan yang masih berjalan
     * atau sudah selesai, jadi baris 'pending' yang dibuat di sini otomatis
     * ikut mengurangi saldo yang tampak tersedia berikutnya.
     */
    public function tarikSaldo(Request $request)
    {
        $this->pastikanAdminWilayah();

        $user = auth()->user();
        $region = Region::find($user->region_id);

        if (! $region) {
            return redirect()->back()->with('error', 'Wilayah tidak ditemukan.');
        }

        $minimal = PenarikanSaldo::MINIMAL_PENARIKAN;
        $saldoTersedia = SaldoWilayah::tersedia($region->id);

        $validated = $request->validate([
            'metode' => ['required', 'in:bank,ewallet'],
            'jumlah' => ['required', 'numeric', "min:{$minimal}", 'max:' . max($minimal, $saldoTersedia)],
        ], [
            'metode.required' => 'Pilih dulu tujuan pencairannya.',
            'jumlah.min' => 'Jumlah minimal Rp ' . number_format($minimal, 0, ',', '.') . ' per pengajuan.',
            'jumlah.max' => 'Jumlah melebihi saldo yang tersedia untuk dicairkan.',
        ]);

        if (PenarikanSaldo::where('region_id', $region->id)->berjalan()->exists()) {
            return redirect()->back()->with('error',
                'Masih ada pengajuan penarikan yang belum diselesaikan Diskominfotik. Tunggu itu selesai dulu.');
        }

        $info = $region->payment_info ?? [];

        if ($validated['metode'] === 'bank') {
            $namaTujuan = $info['bank_name'] ?? null;
            $nomorTujuan = $info['account_number'] ?? null;
            $pemilikTujuan = $info['account_name'] ?? null;
            $labelKurang = 'Rekening bank wilayah belum lengkap.';
        } else {
            $namaTujuan = $info['ewallet_name'] ?? null;
            $nomorTujuan = $info['ewallet_number'] ?? null;
            $pemilikTujuan = $info['ewallet_account_name'] ?? null;
            $labelKurang = 'Data e-wallet wilayah belum lengkap.';
        }

        if (empty($namaTujuan) || empty($nomorTujuan) || empty($pemilikTujuan)) {
            return redirect()->back()->with('error',
                $labelKurang . ' Lengkapi dulu di Pengaturan → Pembayaran Wilayah.');
        }

        if ($validated['jumlah'] > $saldoTersedia) {
            return redirect()->back()->with('error', 'Jumlah melebihi saldo yang tersedia untuk dicairkan.')->withInput();
        }

        // Tujuan disalin apa adanya saat pengajuan dibuat, bukan dibaca ulang
        // saat dicairkan — kalau admin mengganti rekeningnya di tengah antrean,
        // uang tetap mengalir ke tujuan yang tercantum saat pengajuan disetujui.
        $penarikan = PenarikanSaldo::create([
            'region_id' => $region->id,
            'diajukan_oleh' => $user->id,
            'jumlah' => $validated['jumlah'],
            'metode' => $validated['metode'],
            'nama_bank' => $namaTujuan,
            'no_rekening' => $nomorTujuan,
            'nama_pemilik' => $pemilikTujuan,
            'status' => PenarikanSaldo::MENUNGGU,
            'diajukan_pada' => now(),
        ]);

        // Beri tahu Diskominfotik lewat lonceng navbar mereka. region_id sengaja
        // NULL: lonceng menampilkan notifikasi ber-region_id kosong untuk
        // super_admin/admin, dan yang ber-region_id untuk admin wilayah.
        \App\Models\AdminNotification::create([
            'type' => 'penarikan_saldo',
            'reference_id' => $penarikan->id,
            'region_id' => null,
            'title' => 'Pengajuan Penarikan Saldo',
            'message' => $region->name . ' mengajukan pencairan Rp '
                . number_format($validated['jumlah'], 0, ',', '.') . ' ke '
                . ($validated['metode'] === 'ewallet' ? 'e-wallet ' : 'rekening ') . $namaTujuan . '.',
            'is_read' => false,
        ]);

        Log::info('Pengajuan penarikan saldo dibuat', [
            'penarikan_id' => $penarikan->id,
            'region_id' => $region->id,
            'jumlah' => $validated['jumlah'],
            'metode' => $validated['metode'],
            'oleh' => $user->email,
        ]);

        return redirect()->back()->with('success',
            'Pengajuan penarikan sebesar Rp ' . number_format($validated['jumlah'], 0, ',', '.')
            . ' terkirim ke Diskominfotik. Anda akan diberi tahu setelah diproses.');
    }

    /**
     * Wilayah menarik kembali pengajuannya sendiri.
     *
     * Ini bukan fitur pemanis. Sistem hanya mengizinkan satu pengajuan berjalan
     * dalam satu waktu, jadi tanpa jalan keluar ini sebuah pengajuan yang
     * didiamkan Diskominfotik akan MENGUNCI wilayahnya: tidak bisa mencairkan
     * apa pun, bahkan tidak bisa memperbaiki pengajuan yang salah rekening.
     * Uangnya milik mereka — kendali untuk menarik diri harus ada di tangan
     * mereka juga.
     */
    public function batalkanPenarikan(PenarikanSaldo $penarikan)
    {
        $this->pastikanAdminWilayah();

        $user = auth()->user();

        // Wilayah lain tidak boleh menyentuh pengajuan ini, meski tahu id-nya.
        abort_unless($penarikan->region_id === $user->region_id, 404, 'Pengajuan tidak ditemukan di wilayah Anda.');

        // Begitu Diskominfotik mulai memproses, uangnya mungkin sudah dalam
        // perjalanan di m-banking petugas. Membatalkan di titik itu berisiko
        // transfer ganda, jadi hanya yang masih menunggu yang boleh ditarik.
        if (! $penarikan->bisaDibatalkanWilayah()) {
            return redirect()->back()->with('error',
                'Pengajuan ini sudah mulai diproses Diskominfotik, jadi tidak bisa dibatalkan sendiri. '
                . 'Hubungi Diskominfotik bila ada kekeliruan.');
        }

        $penarikan->update([
            'status' => PenarikanSaldo::DIBATALKAN,
            'catatan_admin' => 'Dibatalkan sendiri oleh admin wilayah.',
            'diselesaikan_pada' => now(),
        ]);

        // Kabari Diskominfotik supaya antrean mereka tidak menyisakan hantu.
        \App\Models\AdminNotification::create([
            'type' => 'penarikan_saldo',
            'reference_id' => $penarikan->id,
            'region_id' => null,
            'title' => 'Pengajuan Penarikan Dibatalkan',
            'message' => ($penarikan->region->name ?? 'Wilayah') . ' membatalkan pengajuan pencairan Rp '
                . number_format($penarikan->jumlah, 0, ',', '.') . '.',
            'is_read' => false,
        ]);

        Log::info('Penarikan saldo dibatalkan wilayah', [
            'penarikan_id' => $penarikan->id,
            'region_id' => $penarikan->region_id,
            'jumlah' => $penarikan->jumlah,
            'oleh' => $user->email,
        ]);

        return redirect()->back()->with('success',
            'Pengajuan dibatalkan. Saldo Rp ' . number_format($penarikan->jumlah, 0, ',', '.')
            . ' kembali tersedia dan bisa diajukan lagi.');
    }
}
