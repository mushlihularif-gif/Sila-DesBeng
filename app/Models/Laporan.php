<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Laporan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama',
        'kategori',
        'lokasi',
        'rw',
        'rt',
        'rw_number',
        'rt_number',
        'deskripsi',
        'bukti',
        'status',
        'escalation_level',
        'rt_handler_id',
        'rw_handler_id',
        'catatan_rt',
        'catatan_rw',
        'escalated_to_rw_at',
        'catatan_admin',
        'admin_id',
        'region_id',          // ✅ Ditambahkan: relasi ke wilayah pelapor
        'tujuan_laporan',     // ✅ Ditambahkan: target awal laporan (rt/rw/desa)
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'escalated_to_rw_at' => 'datetime',
        // Defense in Depth: ChaCha20-Poly1305 Database-Level Encryption (PII Pelapor)
        'nama' => \App\Casts\ChaCha20Encrypted::class,
        'lokasi' => \App\Casts\ChaCha20Encrypted::class,
    ];

    // ===================================
    // KONFIGURASI SLA ESKALASI OTOMATIS
    // (Proportional Response Time)
    // ===================================

    /**
     * Batas waktu SLA (dalam jam) per tingkat eskalasi.
     * Jika laporan tidak direspons dalam waktu ini, otomatis naik tingkat.
     */
    public const SLA_HOURS = [
        'rt'        => 24,  // RT → RW: 24 jam (1 hari)
        'rw'        => 24,  // RW → Desa: 24 jam (1 hari)
        'desa'      => 48,  // Desa → Kecamatan: 48 jam (2 hari)
        'kecamatan' => 72,  // Kecamatan → Kabupaten: 72 jam (3 hari)
        'kabupaten' => 999, // Puncak hierarki (tidak naik lagi, tapi bisa berstatus Overdue)
    ];

    /**
     * Urutan hierarki eskalasi.
     */
    public const ESCALATION_HIERARCHY = ['rt', 'rw', 'desa', 'kecamatan', 'kabupaten'];

    // ===================================
    // RELASI
    // ===================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /** Admin RT yang menangani laporan ini */
    public function rtHandler()
    {
        return $this->belongsTo(User::class, 'rt_handler_id');
    }

    /** Admin RW yang menangani laporan ini */
    public function rwHandler()
    {
        return $this->belongsTo(User::class, 'rw_handler_id');
    }

    /** Wilayah asal pelapor */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    // ===================================
    // HELPER METHODS — MATRIKS ESKALASI
    // ===================================

    /**
     * Cek apakah laporan ini sudah melewati batas SLA di tingkat saat ini.
     */
    public function isOverdue(): bool
    {
        $level = $this->escalation_level ?? 'rt';
        $slaHours = self::SLA_HOURS[$level] ?? 24;

        // Timestamp referensi: waktu eskalasi terakhir, atau waktu pembuatan
        $referenceTime = $this->escalated_to_rw_at ?? $this->created_at;

        return $referenceTime && $referenceTime->diffInHours(now()) >= $slaHours;
    }

    /**
     * Ambil level eskalasi berikutnya berdasarkan hierarki.
     *
     * @return string|null Null jika sudah di level tertinggi
     */
    public function getNextEscalationLevel(): ?string
    {
        $currentLevel = $this->escalation_level ?? 'rt';
        $hierarchy = self::ESCALATION_HIERARCHY;
        $currentIndex = array_search($currentLevel, $hierarchy);

        if ($currentIndex === false || $currentIndex >= count($hierarchy) - 1) {
            return null; // Sudah di level tertinggi (admin)
        }

        return $hierarchy[$currentIndex + 1];
    }

    /**
     * Cek apakah laporan ini masih bisa di-eskalasi ke tingkat yang lebih tinggi.
     */
    public function canBeEscalated(): bool
    {
        // Hanya laporan Pending atau Proses yang bisa di-eskalasi
        if (!in_array($this->status, ['Pending', 'Proses'])) {
            return false;
        }

        return $this->getNextEscalationLevel() !== null;
    }

    /**
     * Eskalasi laporan ke tingkat berikutnya.
     *
     * @param  int|null $handlerId ID admin yang melakukan eskalasi manual (null = otomatis)
     * @param  string|null $catatan Catatan eskalasi
     * @return bool
     */
    public function escalateTo(?int $handlerId = null, ?string $catatan = null): bool
    {
        $nextLevel = $this->getNextEscalationLevel();

        if ($nextLevel === null) {
            return false;
        }

        $currentLevel = $this->escalation_level ?? 'rt';

        // Simpan catatan handler di field yang sesuai
        if ($currentLevel === 'rt' && $catatan) {
            $this->catatan_rt = $catatan;
            $this->rt_handler_id = $handlerId;
        } elseif ($currentLevel === 'rw' && $catatan) {
            $this->catatan_rw = $catatan;
            $this->rw_handler_id = $handlerId;
        }

        // Naikkan level eskalasi
        $this->escalation_level = $nextLevel;
        $this->escalated_to_rw_at = now();
        $this->status = 'Dilanjutkan'; // Status khusus eskalasi

        return $this->save();
    }

    /**
     * Cek apakah laporan bisa dihapus
     *
     * Syarat:
     * 1. Status masih "Pending" (belum disentuh RW/Admin)
     * 2. Belum lebih dari 24 jam sejak dibuat
     */
    public function canBeDeletedBy(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        if ($this->user_id !== $userId) {
            return false;
        }

        if (strtolower($this->status) !== 'pending') {
            return false;
        }

        if ($this->created_at->diffInHours(now()) >= 24) {
            return false;
        }

        return true;
    }
}