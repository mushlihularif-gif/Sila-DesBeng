<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satu baris buku besar dompet warga.
 *
 * Saldonya tidak disimpan sebagai angka di kolom manapun — dihitung ulang dari
 * baris-baris ini, mengikuti prinsip yang sama dengan SaldoWilayah: satu sumber
 * kebenaran, tidak ada angka tersimpan yang bisa meleset dari riwayatnya.
 */
class SaldoWarga extends Model
{
    protected $table = 'saldo_warga';

    public const REFUND    = 'refund';
    public const BELANJA   = 'belanja';
    public const PENARIKAN = 'penarikan';

    public const SELESAI  = 'selesai';
    public const MENUNGGU = 'pending';
    public const DIPROSES = 'diproses';
    public const DITOLAK  = 'ditolak';

    /** Ambang bawah pencairan, disamakan dengan pencairan wilayah & mitra. */
    public const MINIMAL_PENARIKAN = 20000;

    protected $fillable = [
        'user_id', 'region_id', 'type', 'amount',
        'reference_type', 'reference_id', 'status',
        'nama_bank', 'no_rekening', 'nama_pemilik',
        'catatan', 'diproses_oleh', 'diselesaikan_pada',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'no_rekening' => 'encrypted',
        'diselesaikan_pada' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function scopeBerjalan($query)
    {
        return $query->whereIn('status', [self::MENUNGGU, self::DIPROSES]);
    }

    public function sudahSelesai(): bool
    {
        return in_array($this->status, [self::SELESAI, self::DITOLAK], true);
    }

    public function labelStatus(): string
    {
        return match ($this->status) {
            self::SELESAI  => $this->type === self::PENARIKAN ? 'Sudah dicairkan' : 'Selesai',
            self::MENUNGGU => 'Menunggu diproses',
            self::DIPROSES => 'Sedang ditransfer',
            self::DITOLAK  => 'Tidak bisa diproses',
            default        => $this->status,
        };
    }

    public function labelJenis(): string
    {
        return match ($this->type) {
            self::REFUND    => 'Pengembalian dana',
            self::BELANJA   => 'Dipakai belanja',
            self::PENARIKAN => 'Pencairan ke rekening',
            default         => $this->type,
        };
    }
}
