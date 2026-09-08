<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $fillable = [
        'type',
        'reference_id',
        'region_id',
        'title',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Batasi notifikasi pada yang berhak dilihat satu pengguna.
     *
     * Aturannya ditaruh di sini, bukan di tiap pemanggil, karena sebelumnya
     * bilah lonceng di layout dan halaman "Lihat Semua" punya versi sendiri:
     * yang satu menyaring wilayah, yang satu tidak menyaring sama sekali —
     * sehingga admin desa bisa membaca notifikasi desa lain lewat halaman itu.
     *
     * - Akun platform (Diskominfotik & staf platform) hanya melihat notifikasi
     *   tanpa wilayah, yaitu urusan tingkat platform. Pesanan dan laporan milik
     *   desa bukan wewenangnya, dan jumlahnya menenggelamkan yang penting.
     * - Admin wilayah melihat wilayahnya sendiri beserta seluruh turunannya
     *   (camat ikut melihat desa di bawahnya), ditambah notifikasi tanpa wilayah.
     * - Pengguna tanpa wilayah hanya melihat notifikasi tanpa wilayah.
     */
    public function scopeUntukPengguna($query, ?User $pengguna)
    {
        if (! $pengguna || $pengguna->bolehAksesPlatform()) {
            return $query->whereNull('region_id');
        }

        $regionId = $pengguna->region_id;

        if (! $regionId) {
            return $query->whereNull('region_id');
        }

        $wilayah = Region::getDescendantIds($regionId);
        $wilayah[] = $regionId;

        return $query->where(function ($q) use ($wilayah) {
            $q->whereIn('region_id', $wilayah)->orWhereNull('region_id');
        });
    }
}
