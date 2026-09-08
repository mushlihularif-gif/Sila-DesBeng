<?php

namespace App\Console\Commands;

use App\Models\AdminNotification;
use App\Models\PenarikanSaldo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Pengingat pengajuan pencairan yang didiamkan Diskominfotik.
 *
 * Uang yang tertahan itu milik wilayah, dan sistem hanya mengizinkan satu
 * pengajuan berjalan — jadi selama satu pengajuan didiamkan, wilayahnya tidak
 * bisa mencairkan apa pun. Perintah ini membuat kelalaian itu bersuara, bukan
 * menunggu ditemukan sendiri.
 *
 * Sengaja TIDAK membatalkan apa pun secara otomatis: membatalkan tidak membuat
 * uangnya cair, hanya memutar ulang prosesnya, dan justru menyembunyikan
 * kelalaiannya karena antreannya jadi bersih sendiri.
 */
class IngatkanPenarikanSaldo extends Command
{
    protected $signature = 'penarikan:ingatkan';

    protected $description = 'Kirim pengingat ke Diskominfotik untuk pengajuan pencairan saldo yang melewati batas waktu';

    public function handle(): int
    {
        $batas = now()->subDays(PenarikanSaldo::BATAS_HARI_PROSES);

        $terlambat = PenarikanSaldo::with('region')
            ->where('status', PenarikanSaldo::MENUNGGU)
            ->where('diajukan_pada', '<=', $batas)
            ->get();

        if ($terlambat->isEmpty()) {
            $this->info('Tidak ada pengajuan pencairan yang terlambat.');
            return self::SUCCESS;
        }

        $dikirim = 0;

        foreach ($terlambat as $penarikan) {
            // Jangan menumpuk pengingat tiap jam untuk pengajuan yang sama —
            // satu per hari sudah cukup terdengar tanpa membanjiri lonceng.
            $sudahDiingatkanHariIni = AdminNotification::where('type', 'penarikan_saldo_terlambat')
                ->where('reference_id', $penarikan->id)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($sudahDiingatkanHariIni) {
                continue;
            }

            AdminNotification::create([
                'type' => 'penarikan_saldo_terlambat',
                'reference_id' => $penarikan->id,
                'region_id' => null, // NULL = tampil di lonceng super_admin/admin
                'title' => 'Pencairan Terlambat Diproses',
                'message' => ($penarikan->region->name ?? 'Sebuah wilayah') . ' menunggu pencairan Rp '
                    . number_format($penarikan->jumlah, 0, ',', '.') . ' sudah '
                    . $penarikan->umurHari() . ' hari. Selama belum diproses, wilayah itu '
                    . 'tidak bisa mencairkan apa pun.',
                'is_read' => false,
            ]);

            $dikirim++;
        }

        Log::info('Pengingat pencairan saldo dikirim', [
            'terlambat' => $terlambat->count(),
            'pengingat_baru' => $dikirim,
        ]);

        $this->info("Pengajuan terlambat: {$terlambat->count()}, pengingat baru dikirim: {$dikirim}.");

        return self::SUCCESS;
    }
}
