<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Satu alamat tersimpan milik warga.
 *
 * Nomor telepon dan detail alamat dienkripsi di tingkat basis data memakai cast
 * yang sama dengan data pribadi di model User — keduanya sama-sama data pribadi
 * warga menurut UU PDP, dan tidak ada alasan alamat diperlakukan lebih longgar
 * daripada NIK yang sudah dienkripsi.
 */
class AlamatWarga extends Model
{
    protected $table = 'alamat_warga';

    protected $fillable = [
        'user_id', 'region_id', 'label', 'nama_penerima', 'no_telepon',
        'detail_alamat', 'rt', 'rw', 'kode_pos', 'patokan',
        'latitude', 'longitude', 'is_utama',
    ];

    protected $casts = [
        'is_utama'      => 'boolean',
        'latitude'      => 'float',
        'longitude'     => 'float',
        'no_telepon'    => \App\Casts\ChaCha20Encrypted::class,
        'detail_alamat' => \App\Casts\ChaCha20Encrypted::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function scopeMilik($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /** Alamat utama warga, atau alamat pertama kalau belum ada yang ditandai. */
    public static function utamaMilik(int $userId): ?self
    {
        return self::milik($userId)->orderByDesc('is_utama')->orderBy('id')->first();
    }

    /**
     * Jadikan alamat ini yang utama, dan lepaskan tanda dari yang lain.
     *
     * Dibungkus transaksi karena keduanya harus berlaku bersamaan: kalau
     * pelepasan tanda berhasil tetapi penandaan gagal, warga kehilangan alamat
     * utamanya sama sekali.
     */
    public function jadikanUtama(): void
    {
        DB::transaction(function () {
            self::milik($this->user_id)->where('id', '!=', $this->id)->update(['is_utama' => false]);
            $this->update(['is_utama' => true]);
        });
    }

    /** Alamat satu baris untuk ditempelkan ke formulir pemesanan. */
    public function satuBaris(): string
    {
        $bagian = array_filter([
            $this->detail_alamat,
            $this->rt ? 'RT ' . $this->rt : null,
            $this->rw ? 'RW ' . $this->rw : null,
            $this->region?->name,
            $this->kode_pos,
        ]);

        return implode(', ', $bagian);
    }

    public function punyaTitik(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
