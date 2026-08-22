<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Kredensial layanan pihak ketiga (Google OAuth, Google Maps, Midtrans, dst).
 *
 * ATURAN UTAMA: satu kategori = tepat satu baris.
 * Kolom `category` unik dan semua penyimpanan lewat put() memakai
 * updateOrCreate, jadi menekan "Terapkan" akan MENIMPA data lama —
 * tidak pernah menumpuk baris baru.
 *
 * Daftar kategori & field-nya didefinisikan di config/api_providers.php,
 * bukan di skema tabel, supaya menambah provider baru tidak butuh migration.
 */
class ApiCredential extends Model
{
    protected $fillable = [
        'category',
        'credentials',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        // Seluruh isi kredensial dienkripsi AES-256 sebagai satu blob JSON.
        'credentials' => 'encrypted:array',
        'is_active'   => 'boolean',
    ];

    /**
     * Cache per-request supaya satu request tidak query tabel ini berulang kali.
     */
    protected static ?Collection $memo = null;

    protected static function booted(): void
    {
        $forget = fn () => static::$memo = null;

        static::saved($forget);
        static::deleted($forget);
    }

    // =====================================================
    // Pembacaan
    // =====================================================

    /**
     * Semua kredensial, di-keyBy kategori. Dibaca sekali per request.
     */
    public static function allCached(): Collection
    {
        if (static::$memo !== null) {
            return static::$memo;
        }

        return static::$memo = static::query()->get()->keyBy('category');
    }

    /**
     * Isi kredensial satu kategori sebagai array. Kembalikan [] kalau belum diisi
     * atau kategorinya sedang dinonaktifkan.
     */
    public static function for(string $category): array
    {
        $row = static::allCached()->get($category);

        if (! $row || ! $row->is_active) {
            return [];
        }

        return $row->credentials ?? [];
    }

    /**
     * Satu nilai field dari satu kategori, mis. value('google_oauth', 'client_id').
     */
    public static function value(string $category, string $field, mixed $default = null): mixed
    {
        $value = static::for($category)[$field] ?? null;

        // String kosong dianggap belum diisi supaya fallback ke .env tetap jalan.
        return ($value === null || $value === '') ? $default : $value;
    }

    // =====================================================
    // Penulisan
    // =====================================================

    /**
     * Simpan (timpa) kredensial satu kategori. Dijamin hanya menghasilkan
     * satu baris per kategori karena memakai updateOrCreate pada kolom unik.
     */
    public static function put(string $category, array $credentials, ?string $updatedBy = null): self
    {
        return static::updateOrCreate(
            ['category' => $category],
            [
                'credentials' => $credentials,
                'is_active'   => true,
                'updated_by'  => $updatedBy,
            ]
        );
    }

    /**
     * Hapus kredensial satu kategori sehingga sistem kembali memakai nilai .env.
     */
    public static function forget(string $category): void
    {
        static::query()->where('category', $category)->delete();

        static::$memo = null;
    }

    // =====================================================
    // Penerapan ke config() saat runtime
    // =====================================================

    /**
     * Timpa config('services.*') dengan nilai dari database, mengikuti pemetaan
     * kunci 'config' di config/api_providers.php.
     *
     * Field yang kosong dilewati, jadi nilai .env tetap berlaku sebagai fallback.
     * Dibungkus penjaga tabel + try/catch supaya `artisan migrate` pada instalasi
     * baru (tabel belum ada) tidak ikut gagal.
     */
    public static function applyToConfig(): void
    {
        try {
            if (! Schema::hasTable('api_credentials')) {
                return;
            }

            $stored = static::allCached();

            if ($stored->isEmpty()) {
                return;
            }

            foreach (config('api_providers', []) as $category => $provider) {
                $values = static::for($category);

                if (empty($values)) {
                    continue;
                }

                foreach ($provider['fields'] ?? [] as $field => $definition) {
                    $configKey = $definition['config'] ?? null;

                    if (! $configKey || ! array_key_exists($field, $values)) {
                        continue;
                    }

                    $value = $values[$field];

                    if (($definition['type'] ?? 'text') === 'boolean') {
                        config([$configKey => (bool) $value]);
                        continue;
                    }

                    if ($value !== null && $value !== '') {
                        config([$configKey => $value]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Diam-diam fallback ke .env kalau DB belum siap (mis. saat instalasi awal).
        }
    }
}
