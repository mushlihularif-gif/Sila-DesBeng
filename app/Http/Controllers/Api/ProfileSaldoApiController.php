<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaldoWarga;
use App\Support\DompetWarga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileSaldoApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $saldo = DompetWarga::saldo($user->id);
        $rincian = DompetWarga::ringkasan($user->id);

        $riwayat = SaldoWarga::where('user_id', $user->id)
            ->with('region')
            ->latest()
            ->paginate(15);

        $pengajuan = SaldoWarga::where('user_id', $user->id)
            ->where('type', SaldoWarga::PENARIKAN)
            ->with('petugas')
            ->latest()
            ->get();

        $rekeningTerakhir = $pengajuan->firstWhere('no_rekening', '!=', null);

        return response()->json([
            'status' => 'success',
            'data' => [
                'saldo' => $saldo,
                'rincian' => $rincian,
                'riwayat' => $riwayat,
                'pengajuan' => $pengajuan,
                'rekening_terakhir' => $rekeningTerakhir ? [
                    'nama_bank' => $rekeningTerakhir->nama_bank,
                    'no_rekening' => $rekeningTerakhir->no_rekening,
                    'nama_pemilik' => $rekeningTerakhir->nama_pemilik,
                ] : null,
            ]
        ]);
    }

    public function tarik(Request $request)
    {
        $user = $request->user();

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

        try {
            $penarikan = DB::transaction(function () use ($user, $data) {
                // Lock baris saldo untuk mencegah double penarikan bersamaan
                SaldoWarga::where('user_id', $user->id)->lockForUpdate()->get();

                $tersedia = DompetWarga::saldo($user->id);

                if ($data['amount'] > $tersedia) {
                    throw new \RuntimeException(
                        'Saldo Anda hanya Rp ' . number_format($tersedia, 0, ',', '.')
                        . ', tidak cukup untuk penarikan sebesar itu.'
                    );
                }

                return SaldoWarga::create([
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

            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan penarikan terkirim. Saldo Anda ditahan sementara sampai pengajuan diproses petugas.',
                'data' => $penarikan
            ]);
            
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function batal(Request $request, $id)
    {
        $user = $request->user();
        $saldo = SaldoWarga::where('id', $id)->where('user_id', $user->id)->first();

        if (!$saldo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengajuan tidak ditemukan atau bukan milik Anda.'
            ], 404);
        }

        if ($saldo->status !== SaldoWarga::MENUNGGU) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengajuan ini sudah mulai diproses petugas, jadi tidak bisa dibatalkan sendiri.'
            ], 400);
        }

        $saldo->update([
            'status'  => SaldoWarga::DITOLAK,
            'catatan' => trim(($saldo->catatan ? $saldo->catatan . ' — ' : '') . 'Dibatalkan sendiri oleh warga via aplikasi.'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan penarikan dibatalkan. Saldo Anda kembali tersedia.',
            'data' => $saldo
        ]);
    }
}
