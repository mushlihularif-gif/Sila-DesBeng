<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'region_id',
        'type',
        'source',
        'amount',
        'reference_type',
        'reference_id',
        'status',
        'verified_by',
        'proof_path',
        'notes', 'refund_status', 'refund_proof', 'refunded_at', 'refunded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2', 'refunded_at' => 'datetime',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Catat satu pemasukan pesanan ke ledger wilayah.
     *
     * Sebelumnya tiap controller pemesanan menulis blok ini sendiri-sendiri
     * secara manual, dan hanya GasBookingController yang benar-benar
     * melakukannya — Sewa Alat, Sewa Mobil, Fasilitas Umum, dan Pasar Daerah
     * tidak pernah tercatat di ledger sama sekali, sehingga saldo wilayah
     * selalu meleset dari uang yang sungguh-sungguh diterima.
     *
     * Pembayaran yang masih perlu verifikasi ditahan sampai pesanan dikonfirmasi
     * selesai/diterima — lihat status transition di RequestController.
     * Transfer manual & tunai langsung "masuk" karena uangnya tidak pernah
     * singgah di Diskominfotik: transfer manual langsung ke rekening wilayah,
     * tunai diterima langsung oleh petugas.
     */
    public static function catatPemasukan(
        int $regionId,
        string $referenceType,
        int $referenceId,
        float $amount,
        string $paymentMethod,
        ?string $proofPath = null,
    ): \Illuminate\Support\Collection {
        $lewatGateway = ! in_array($paymentMethod, ['tunai', 'transfer', 'transfer_manual', 'ewallet'], true);

        $dasar = [
            'type' => $lewatGateway ? 'ditahan' : 'masuk',
            'source' => $lewatGateway ? 'gateway' : 'manual',
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'status' => 'pending',
            'proof_path' => $proofPath,
        ];

        // SATU baris, seluruhnya milik wilayah.
        //
        // Rancangan sebelumnya memecah pemasukan menjadi porsi wilayah dan porsi
        // mitra pengelola unit. Rancangan itu dibatalkan: seluruh pemasukan tetap
        // satu kas daerah, dan pembayaran kepada pihak ketiga diurus bendahara
        // desa lewat transfer manual di luar sistem. Nilai kembaliannya tetap
        // Collection supaya pemanggil yang sudah ada tidak perlu diubah.
        return collect([
            self::create($dasar + ['region_id' => $regionId, 'amount' => $amount]),
        ]);
    }

    /**
     * Tandai pembayaran MANUAL satu pesanan sebagai sudah diterima petugas.
     *
     * Hanya menyentuh baris `source = 'manual'` (tunai, transfer ke rekening
     * wilayah, e-wallet) — uang yang penerimaannya memang cuma bisa dipastikan
     * oleh manusia.
     *
     * Pembayaran melalui gateway tidak termasuk jalur yang digunakan aplikasi.
     * Fungsi ini hanya mengubah catatan pembayaran manual setelah petugas
     * memeriksa transaksi.
     *
     * Dipanggil dari SEMUA jalur penyelesaian pesanan, supaya hasilnya tidak
     * bergantung pada tombol mana yang kebetulan ditekan admin.
     */
    public static function tandaiPembayaranManualDiterima(
        string $referenceType,
        int $referenceId,
        ?int $verifiedBy = null,
    ): void {
        self::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('source', 'manual')
            ->where('status', 'pending')
            ->update(['status' => 'verified', 'verified_by' => $verifiedBy]);
    }

    /**
     * Batalkan pemasukan satu pesanan, dan kembalikan uangnya ke dompet warga
     * bila memang sudah terbayar.
     *
     * Dipakai oleh SEMUA jalur pembatalan (ditolak admin, dibatalkan lewat
     * verifikasi bukti, kedaluwarsa/gagal dari webhook) supaya aturannya satu.
     * Sebelum ini tiap jalur hanya menandai baris 'rejected' sendiri-sendiri —
     * uangnya hilang dari saldo wilayah tanpa pernah menjadi milik siapa pun.
     *
     * URUTAN PENTING: refund dihitung dari baris yang masih 'verified', jadi
     * harus dikerjakan SEBELUM barisnya ditandai batal.
     */
    public static function batalkanDanRefund(string $referenceType, int $referenceId, string $alasan): void
    {
        \DB::transaction(function () use ($referenceType, $referenceId, $alasan) {
            $rows = self::where('reference_type', $referenceType)->where('reference_id', $referenceId)
                ->orderBy('id')->lockForUpdate()->get();
            $verified = $rows->where('status', 'verified');
            if ($verified->isNotEmpty()) {
                [$userId, $regionId] = self::pemilikPesanan($referenceType, $referenceId);
                abort_unless($userId && $regionId && $verified->every(fn ($r) => (int)$r->region_id === (int)$regionId),
                    409, 'Pemilik atau wilayah pembayaran perlu diperiksa sebelum pembatalan.');
                if ($verified->contains('source', 'gateway')) {
                    \App\Support\DompetWarga::kembalikanUntukPesanan($referenceType, $referenceId, $userId, $regionId, $alasan);
                }
                foreach ($verified->where('source', 'manual') as $entry) {
                    $entry->refund_status = 'pending';
                    $entry->save();
                }
                if ($verified->contains('source', 'manual')) {
                    Notification::create(['user_id'=>$userId, 'type'=>'status_berubah', 'title'=>'Pengembalian Dana Menunggu Pengelola',
                        'message'=>'Pesanan #'.$referenceId.' dibatalkan. Pembayaran manual yang sudah diterima perlu dikembalikan oleh pengelola desa. Pembatalan ini belum berarti uang sudah ditransfer.',
                        'is_read'=>false]);
                }
            }
            self::whereKey($rows->modelKeys())->whereIn('status', ['pending', 'verified'])
                ->update(['status'=>'rejected', 'notes'=>$alasan]);
        });
    }

    /**
     * Pemesan dan wilayah asal satu pesanan.
     *
     * @return array{0: int|null, 1: int|null} [user_id, region_id]
     */
    public static function pemilikPesanan(string $referenceType, int $referenceId): array
    {
        $pesanan = match ($referenceType) {
            'gas'       => GasOrder::withTrashed()->with('gas')->find($referenceId),
            'rental'    => RentalBooking::withTrashed()->with('barang')->find($referenceId),
            'mobil'     => MobilBooking::withTrashed()->with('mobil')->find($referenceId),
            'fasilitas' => FasilitasUmumBooking::withTrashed()->find($referenceId),
            'pasar'     => PasarOrder::withTrashed()->find($referenceId),
            default     => null,
        };

        if (! $pesanan) {
            return [null, null];
        }

        // Wilayah tidak selalu tersimpan di pesanannya sendiri — untuk gas,
        // sewa alat, dan mobil ia menempel di barang yang disewa/dibeli.
        $regionId = match ($referenceType) {
            'gas'    => $pesanan->gas->region_id ?? null,
            'rental' => $pesanan->barang->region_id ?? null,
            'mobil'  => $pesanan->mobil->region_id ?? null,
            default  => $pesanan->region_id ?? null,
        };

        return [$pesanan->user_id ?? null, $regionId];
    }
}
