<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiCredential;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Beranda Super Admin Sistem (Diskominfotik).
 *
 * Sengaja BUKAN salinan dashboard kabupaten: super admin tidak mengurus
 * operasional/keuangan per wilayah, melainkan kesehatan platform secara
 * keseluruhan. Isinya rangkuman lintas fitur + pintasan ke tiap modul.
 *
 * Semua hitungan dibungkus penjaga tabel supaya halaman tetap tampil di
 * instalasi baru yang tabelnya belum lengkap, dan di-cache sebentar supaya
 * puluhan query hitung tidak dijalankan ulang tiap kali halaman dibuka.
 */
class SuperAdminDashboardController extends Controller
{
    private const CACHE_KEY = 'dashboard.super_admin';
    private const CACHE_TTL = 60; // detik

    public function index()
    {
        $data = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => $this->kumpulkan());

        // Aktivitas terbaru tidak ikut di-cache: murah, dan lebih berguna kalau segar.
        $data['aktivitas'] = $this->aktivitasTerbaru();

        return view('admin.dashboard.super_admin', $data);
    }

    // =====================================================

    private function kumpulkan(): array
    {
        return [
            'wilayah'    => $this->wilayah(),
            'pengguna'   => $this->pengguna(),
            'unit'       => $this->unit(),
            'dompet'     => $this->dompet(),
            'integrasi'  => $this->integrasi(),
            'biaya'      => $this->biaya(),
        ];
    }

    /**
     * Hitung baris dengan aman: kembalikan 0 kalau tabelnya belum ada.
     */
    private function hitung(string $tabel, ?callable $filter = null): int
    {
        if (! Schema::hasTable($tabel)) {
            return 0;
        }

        $query = DB::table($tabel);

        if (Schema::hasColumn($tabel, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if ($filter) {
            $filter($query);
        }

        return (int) $query->count();
    }

    private function wilayah(): array
    {
        if (! Schema::hasTable('regions')) {
            return ['kabupaten' => 0, 'kecamatan' => 0, 'desa' => 0, 'total' => 0];
        }

        $per = DB::table('regions')->select('type', DB::raw('count(*) as n'))
            ->groupBy('type')->pluck('n', 'type');

        return [
            'kabupaten' => (int) ($per['kabupaten'] ?? 0),
            'kecamatan' => (int) ($per['kecamatan'] ?? 0),
            'desa'      => (int) ($per['desa'] ?? 0),
            'total'     => (int) $per->sum(),
        ];
    }

    private function pengguna(): array
    {
        if (! Schema::hasTable('users')) {
            return ['total' => 0, 'per_role' => [], 'belum_aktif' => 0, 'baru_7hari' => 0];
        }

        $perRole = DB::table('users')->select('role', DB::raw('count(*) as n'))
            ->groupBy('role')->pluck('n', 'role')->map(fn ($n) => (int) $n)->all();

        return [
            'total'       => array_sum($perRole),
            'per_role'    => $perRole,
            'belum_aktif' => $this->hitung('users', fn ($q) => $q->where('status', '!=', 'aktif')),
            'baru_7hari'  => $this->hitung('users', fn ($q) => $q->where('created_at', '>=', now()->subDays(7))),
        ];
    }

    private function unit(): array
    {
        $daftar = [
            'Penjualan Gas'  => 'gas_orders',
            'Sewa Alat'      => 'rental_bookings',
            'Sewa Mobil'     => 'mobil_bookings',
            'Fasilitas Umum' => 'fasilitas_umum_bookings',
            'Pasar Daerah'   => 'pasar_orders',
        ];

        $hasil = [];

        foreach ($daftar as $label => $tabel) {
            $total = $this->hitung($tabel);

            $hasil[] = [
                'label'    => $label,
                'total'    => $total,
                'selesai'  => $this->hitung($tabel, fn ($q) => $q->where('status', 'completed')),
                'menunggu' => $this->hitung($tabel, fn ($q) => $q->where('status', 'pending')),
                'gagal'    => $this->hitung($tabel, fn ($q) => $q->whereIn('status', ['rejected', 'cancelled'])),
            ];
        }

        return $hasil;
    }

    private function dompet(): array
    {
        return [
            'tertahan'         => $this->hitung('wallet_transactions', fn ($q) => $q->where('type', 'ditahan')->where('status', 'pending')),
            'gagal_verifikasi' => $this->hitung('wallet_transactions', fn ($q) => $q->where('status', 'rejected')),
            'region_aktif'     => Schema::hasTable('wallet_transactions')
                ? (int) DB::table('wallet_transactions')->distinct()->count('region_id')
                : 0,
        ];
    }

    /**
     * Status tiap kategori kredensial di config/api_providers.php.
     * Bertambah sendiri kalau ada provider baru didaftarkan di sana.
     */
    private function integrasi(): array
    {
        $tersimpan = Schema::hasTable('api_credentials') ? ApiCredential::allCached() : collect();

        $hasil = [];

        foreach (config('api_providers', []) as $kategori => $provider) {
            $baris = $tersimpan->get($kategori);

            $hasil[] = [
                'label'  => $provider['label'],
                'ikon'   => $provider['icon'] ?? 'bx-key',
                'aktif'  => ! empty($baris?->credentials),
                'diubah' => $baris?->updated_at,
            ];
        }

        return $hasil;
    }

    /**
     * Tagihan server, domain, SSL, dan langganan lain yang belum lunas.
     *
     * Yang dikirim ke view sudah lengkap dengan sisa hari dan tanggal jatuh tempo
     * dalam bentuk ISO, supaya hitung mundurnya bisa berjalan hidup di browser
     * tanpa halaman perlu dimuat ulang.
     */
    private function biaya(): array
    {
        $kosong = ['items' => [], 'peringatan' => [], 'terdekat' => null, 'jumlah_terlambat' => 0];

        if (! Schema::hasTable('operational_expenses')) {
            return $kosong;
        }

        $tagihan = \App\Models\OperationalExpense::query()
            ->where('status', '!=', 'lunas')
            ->orderBy('due_date')
            ->get();

        if ($tagihan->isEmpty()) {
            return $kosong;
        }

        $items = $tagihan->map(function ($e) {
            $sisaHari = (int) floor(now()->startOfDay()->diffInDays($e->due_date->copy()->startOfDay(), false));

            return [
                'id'          => $e->id,
                'nama'        => $e->item_name,
                'kategori'    => $e->category,
                'nominal'     => (float) $e->amount,
                'siklus'      => $e->billing_cycle,
                'jatuh_tempo' => $e->due_date->toDateString(),
                'tenggat_iso' => $e->due_date->copy()->endOfDay()->toIso8601String(),
                'sisa_hari'   => $sisaHari,
                'badge'       => $e->due_badge,
            ];
        })->all();

        // Hanya yang terlambat atau sudah mendekati yang perlu kotak peringatan.
        $peringatan = array_values(array_filter(
            $items,
            fn ($i) => in_array($i['badge'], ['terlambat', 'mendekati_jatuh_tempo'], true)
        ));

        return [
            'items'            => $items,
            'peringatan'       => $peringatan,
            'terdekat'         => $items[0] ?? null,
            'jumlah_terlambat' => count(array_filter($items, fn ($i) => $i['badge'] === 'terlambat')),
        ];
    }

    private function aktivitasTerbaru(int $limit = 8): array
    {
        if (! Schema::hasTable('activity_log')) {
            return [];
        }

        $log = DB::table('activity_log')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['action', 'description', 'created_at', 'user_id']);

        if ($log->isEmpty()) {
            return [];
        }

        // PENTING: nama pengguna tersimpan terenkripsi (cast ChaCha20Encrypted di
        // model User). Query builder mentah TIDAK menjalankan cast, jadi join biasa
        // akan menampilkan ciphertext "$chacha20$..." di layar. Karena itu datanya
        // diambil lewat Eloquent, bukan lewat join.
        $pengguna = \App\Models\User::query()
            ->whereIn('id', $log->pluck('user_id')->filter()->unique())
            ->get(['id', 'name', 'role'])
            ->keyBy('id');

        return $log->map(function ($r) use ($pengguna) {
            $u = $r->user_id ? $pengguna->get($r->user_id) : null;

            return [
                'action'      => $r->action,
                'description' => $r->description,
                'created_at'  => $r->created_at,
                'nama'        => $u?->name,
                'role'        => $u?->role,
            ];
        })->all();
    }
}
