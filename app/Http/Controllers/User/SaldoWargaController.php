<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SaldoWarga;
use App\Support\DompetWarga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Dompet warga: saldo hasil pengembalian dana.
 *
 * Saldo ini muncul ketika pesanan yang SUDAH dibayar ditolak atau dibatalkan.
 * Uangnya tidak dikembalikan ke rekening secara otomatis - ia dibukukan sebagai
 * saldo milik warga, lalu warga sendiri yang memilih memakainya untuk pesanan
 * berikutnya atau mencairkannya ke rekening.
 *
 * Sebelum halaman ini ada, pembukuannya sudah berjalan tetapi tidak punya satu
 * pun antarmuka: uang warga tercatat rapi di basis data tanpa ada cara untuk
 * melihat, memakai, apalagi menariknya.
 */
class SaldoWargaController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $saldo = DompetWarga::saldo($user->id);

        // Sebaran saldo, sejajar dengan ringkasan di halaman Keuangan wilayah.
        $rincian = DompetWarga::ringkasan($user->id);

        $riwayat = SaldoWarga::where('user_id', $user->id)
            ->with('region')
            ->latest()
            ->paginate(15);

        // Seluruh pengajuan penarikan, bukan hanya yang berjalan — warga perlu
        // melihat yang sudah cair dan yang dibatalkan juga, seperti daftar
        // pengajuan di sisi admin.
        $pengajuan = SaldoWarga::where('user_id', $user->id)
            ->where('type', SaldoWarga::PENARIKAN)
            ->with('petugas')
            ->latest()
            ->get();

        $rekeningTerakhir = $pengajuan->firstWhere('no_rekening', '!=', null);

        // Buku alamat warga. Sehalaman dengan saldo karena keduanya sama-sama
        // "data saya" — bukan bagian dari alur pemesanan mana pun.
        $alamat = \App\Models\AlamatWarga::milik($user->id)
            ->with('region')
            ->orderByDesc('is_utama')
            ->orderBy('id')
            ->get();

        // Pilihan desa/kelurahan untuk formulir alamat, dibatasi kabupaten ini.
        $desa = \App\Models\Region::whereIn('type', ['desa', 'kelurahan'])
            ->with('parent')
            ->orderBy('name')
            ->get();

        return view('users.saldo.index', compact(
            'saldo', 'rincian', 'riwayat', 'pengajuan', 'rekeningTerakhir',
            'alamat', 'desa'
        ));
    }

    public function tarik(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'amount'       => 'required|numeric|min:' . SaldoWarga::MINIMAL_PENARIKAN,
            'nama_bank'    => 'required|string|max:100',
            'no_rekening'  => 'required|string|max:50',
            'nama_pemilik' => 'required|string|max:255',
            'catatan'      => 'nullable|string|max:500',
        ], [
            'amount.min'   => 'Penarikan minimal Rp ' . number_format(SaldoWarga::MINIMAL_PENARIKAN, 0, ',', '.')
                            . ' — di bawah itu biaya transfer antarbank menggerus nilainya.',
            'nama_bank.required'    => 'Nama bank wajib diisi.',
            'no_rekening.required'  => 'Nomor rekening wajib diisi.',
            'nama_pemilik.required' => 'Nama pemilik rekening wajib diisi.',
        ]);

        // Saldo diperiksa DI DALAM transaksi dan barisnya dikunci, supaya dua
        // pengajuan yang dikirim hampir bersamaan tidak sama-sama lolos dan
        // menarik lebih banyak daripada yang dimiliki.
        try {
            DB::transaction(function () use ($user, $data) {
                SaldoWarga::where('user_id', $user->id)->lockForUpdate()->get();

                $tersedia = DompetWarga::saldo($user->id);

                if ($data['amount'] > $tersedia) {
                    throw new \RuntimeException(
                        'Saldo Anda hanya Rp ' . number_format($tersedia, 0, ',', '.')
                        . ', tidak cukup untuk penarikan sebesar itu.'
                    );
                }

                SaldoWarga::create([
                    'user_id'      => $user->id,
                    'region_id'    => $user->region_id,
                    'type'         => SaldoWarga::PENARIKAN,
                    'amount'       => $data['amount'],
                    'status'       => SaldoWarga::MENUNGGU,
                    'nama_bank'    => $data['nama_bank'],
                    'no_rekening'  => $data['no_rekening'],
                    'nama_pemilik' => $data['nama_pemilik'],
                    'catatan'      => $data['catatan'] ?? null,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success',
            'Pengajuan penarikan Rp ' . number_format((float) $data['amount'], 0, ',', '.')
            . ' terkirim. Saldo Anda ditahan sementara sampai pengajuan diproses petugas.');
    }

    public function batal(SaldoWarga $saldo)
    {
        $user = Auth::user();

        abort_unless($saldo->user_id === $user->id, 403, 'Pengajuan ini bukan milik Anda.');

        // Hanya yang belum disentuh petugas. Begitu berstatus 'diproses',
        // transfernya bisa jadi sedang dikerjakan dan membatalkannya di sini
        // akan membuat pembukuan tidak cocok dengan mutasi bank.
        if ($saldo->status !== SaldoWarga::MENUNGGU) {
            return back()->with('error',
                'Pengajuan ini sudah mulai diproses petugas, jadi tidak bisa dibatalkan sendiri.');
        }

        $saldo->update([
            'status'  => SaldoWarga::DITOLAK,
            'catatan' => trim(($saldo->catatan ? $saldo->catatan . ' — ' : '') . 'Dibatalkan sendiri oleh warga.'),
        ]);

        return back()->with('success', 'Pengajuan penarikan dibatalkan. Saldo Anda kembali tersedia.');
    }
}
