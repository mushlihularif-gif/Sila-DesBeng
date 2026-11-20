<?php

namespace App\Console\Commands;

use App\Models\Region;
use App\Models\User;
use App\Support\BengkalisScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/** Read-only deployment checks: aggregate output only, no credentials or identity data. */
class AuditSegidaerah extends Command
{
    protected $signature = 'segidaerah:audit';
    protected $description = 'Memeriksa role, batas wilayah, lokasi, enkripsi dan antrean pembersihan tanpa mengubah data';

    public function handle(): int
    {
        $failed = false;
        $check = function (string $label, bool $ok, string $detail) use (&$failed) {
            $this->line(($ok ? 'OK' : 'PERIKSA').' | '.$label.' | '.$detail);
            $failed = $failed || ! $ok;
        };
        $unsupported = DB::table('users')->where(fn ($q) => $q->whereNotIn('role', User::ROLES)->orWhereNull('role'))->count();
        $check('Role akun', $unsupported === 0, $unsupported.' akun dengan role tidak didukung');
        foreach (DB::table('users')->select('role', 'status', DB::raw('COUNT(*) AS total'))->groupBy('role', 'status')->get() as $row) {
            $this->line('  '.$row->role.' / '.$row->status.': '.$row->total);
        }
        if (DB::getDriverName() === 'mysql') {
            $type = DB::select("SHOW COLUMNS FROM users LIKE 'role'")[0]->Type;
            $check('Pilihan ENUM role', ! str_contains($type, "'admin'") && ! str_contains($type, "'admin_kecamatan'"), $type);
        }
        $roots = Region::where('type', 'kecamatan')->where('name', 'Kecamatan Bengkalis')->count();
        $check('Akar wilayah Bengkalis', $roots === 1, $roots.' wilayah akar');
        if ($roots === 1) {
            $ids = BengkalisScope::ids();
            $this->line('  Desa/kelurahan dalam cakupan: '.Region::whereIn('id', $ids)->whereIn('type', ['desa', 'kelurahan'])->count());
            foreach (['barang', 'gas', 'mobils', 'fasilitas_umums'] as $table) {
                $outside = DB::table($table)->where(fn ($q) => $q->whereNotIn('region_id', $ids)->orWhereNull('region_id'))->count();
                $check('Wilayah '.$table, $outside === 0, $outside.' record di luar cakupan/tanpa wilayah');
                $missing = DB::table($table.' as items')->whereIn('items.region_id', $ids)
                    ->where(function ($q) {
                        $q->whereNull('items.latitude')->orWhereNull('items.longitude');
                    })->whereNotExists(function ($q) {
                        $q->selectRaw('1')->from('lokasi_layanan as locations')
                            ->whereColumn('locations.region_id', 'items.region_id')->whereColumn('locations.nama', 'items.lokasi')
                            ->whereNotNull('locations.latitude')->whereNotNull('locations.longitude');
                    })->count();
                if ($missing) $this->warn('  LOKASI BELUM LENGKAP | '.$table.': '.$missing.' produk perlu titik lokasi dari pengelola.');
            }
        }
        $sodium = function_exists('sodium_crypto_aead_chacha20poly1305_ietf_encrypt');
        $openssl = function_exists('openssl_get_cipher_methods') && in_array('chacha20-poly1305', openssl_get_cipher_methods(), true);
        $check('Mesin ChaCha20-Poly1305', $sodium || $openssl, $sodium ? 'Sodium IETF' : ($openssl ? 'OpenSSL' : 'Tidak tersedia'));
        $key = (string) config('app.key');
        $bytes = str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7), true) : $key;
        $check('Ukuran material kunci', is_string($bytes) && strlen($bytes) === 32, 'Nilai kunci tidak ditampilkan');
        $check('Rute hapus draf', Route::has('content.destroy'), 'content.destroy');
        $queueReady = Schema::hasTable('identity_cleanup_queue');
        $check('Antrean cleanup', $queueReady, $queueReady ? DB::table('identity_cleanup_queue')->count().' berkas menunggu dihapus' : 'Migrasi belum dijalankan');
        $this->line('Pemeriksaan ini tidak menguji GPS perangkat, billing Google Maps, login browser, atau keberhasilan cron hosting.');

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
