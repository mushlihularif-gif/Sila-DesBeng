<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satu pengajuan pencairan saldo wilayah ke rekening banknya sendiri.
 *
 * Saldonya sendiri tidak disimpan di sini — ia dihitung dari wallet_transactions
 * lewat App\Support\SaldoWilayah, supaya tidak ada dua angka yang bisa berbeda.
 * Baris di tabel ini hanya menandai permohonannya dan hasil keputusannya.
 */
class PenarikanSaldo extends Model
{
    protected $table = 'penarikan_saldo';

    public const MENUNGGU = 'pending';
    public const DIPROSES = 'diproses';
    public const SELESAI  = 'selesai';

    /**
     * Diskominfotik TIDAK BISA memproses pengajuan ini - rekening salah, nama
     * tidak cocok, dana Midtrans belum settle, dsb.
     *
     * Nilai kolomnya masih 'ditolak' (warisan), tapi maknanya bukan menolak
     * hak: uang itu milik wilayah, Diskominfotik cuma penampung karena Midtrans
     * hanya mengizinkan satu rekening pencairan per akun. Ini melaporkan
     * kendala teknis, dan saldonya kembali utuh.
     */
    public const GAGAL_PROSES = 'ditolak';

    /** Ditarik kembali oleh wilayahnya sendiri selagi belum diproses. */
    public const DIBATALKAN = 'dibatalkan';

    /**
     * Ambang bawah satu pengajuan. Mengikuti kebiasaan transfer antarbank
     * (BI-FAST/SKN/RTGS umumnya sudah bisa dari Rp 20.000), supaya batas ini
     * tidak terasa lebih ketat daripada transfer bank biasa.
     */
    public const MINIMAL_PENARIKAN = 20000;

    /**
     * Batas wajar Diskominfotik memproses satu pengajuan (hari).
     * Lewat ini, pengajuan disorot sebagai terlambat di panel mereka dan
     * pengingat otomatis dikirim ulang.
     */
    public const BATAS_HARI_PROSES = 3;

    protected $fillable = [
        'region_id',
        'diajukan_oleh',
        'jumlah',
        'metode',
        'nama_bank',
        'no_rekening',
        'nama_pemilik',
        'status',
        'catatan_admin',
        'diproses_oleh',
        'diajukan_pada',
        'diselesaikan_pada',
        'payout_id',
        'payout_status',
    ];

    protected $casts = [
        'jumlah'            => 'decimal:2',
        // Nomor rekening ikut aturan kerahasiaan yang sama dengan data pribadi
        // lain di aplikasi ini: terenkripsi di database, terbaca hanya lewat model.
        'no_rekening'       => 'encrypted',
        'diajukan_pada'     => 'datetime',
        'diselesaikan_pada' => 'datetime',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    /** Masih memotong saldo: menunggu keputusan atau sedang ditransfer. */
    public function scopeBerjalan($query)
    {
        return $query->whereIn('status', [self::MENUNGGU, self::DIPROSES]);
    }

    /**
     * Dipertahankan sebagai scope kosong.
     *
     * Dulu tabel ini menampung dua aliran: pengajuan wilayah ke Diskominfotik,
     * dan pencairan mitra dari wilayah. Sejak pembagian hasil ke mitra
     * dibatalkan, hanya aliran pertama yang tersisa — jadi tidak ada lagi yang
     * perlu disaring. Dibiarkan ada supaya belasan pemanggil ->keKominfo() di
     * perhitungan saldo tidak perlu diubah serentak, dan maknanya tetap terbaca.
     */
    public function scopeKeKominfo($query)
    {
        return $query;
    }

    public function sudahSelesai(): bool
    {
        return in_array($this->status, [self::SELESAI, self::GAGAL_PROSES, self::DIBATALKAN], true);
    }

    /**
     * Boleh ditarik kembali oleh wilayahnya sendiri?
     *
     * Hanya selagi masih MENUNGGU. Begitu statusnya DIPROSES, uangnya mungkin
     * sudah dalam perjalanan di m-banking petugas — membatalkan di titik itu
     * berisiko transfer ganda.
     */
    public function bisaDibatalkanWilayah(): bool
    {
        return $this->status === self::MENUNGGU;
    }

    /** Sudah lewat batas wajar Diskominfotik memprosesnya? */
    public function terlambat(): bool
    {
        return $this->status === self::MENUNGGU
            && $this->diajukan_pada
            && $this->diajukan_pada->diffInDays(now()) >= self::BATAS_HARI_PROSES;
    }

    public function umurHari(): int
    {
        return $this->diajukan_pada ? (int) $this->diajukan_pada->diffInDays(now()) : 0;
    }

    public function labelStatus(): string
    {
        return match ($this->status) {
            self::MENUNGGU     => 'Menunggu diproses',
            self::DIPROSES     => 'Sedang ditransfer',
            self::SELESAI      => 'Selesai',
            self::GAGAL_PROSES => 'Tidak bisa diproses',
            self::DIBATALKAN   => 'Dibatalkan wilayah',
            default            => $this->status,
        };
    }
}
