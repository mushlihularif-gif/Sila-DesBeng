<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\Notification;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminPelaporanController extends Controller
{
    /**
     * Mendapatkan daftar region_id yang diizinkan untuk admin saat ini.
     * Super Admin / Admin Kecamatan: semua region di bawah wilayahnya.
     * Admin Desa: hanya desa + RT/RW di bawahnya.
     */
    private function getAllowedRegionIds(): array
    {
        $user = auth()->user();
        $allowedIds = Region::getDescendantIds($user->region_id);
        $allowedIds[] = $user->region_id;
        return $allowedIds;
    }

    /**
     * Halaman arsip: daftar semua laporan warga yang sudah selesai.
     */
    public function archive(Request $request)
    {
        $request->merge(['is_archive' => true, 'status' => 'Selesai']);
        return $this->index($request);
    }

    /**
     * Halaman utama: daftar semua laporan warga dengan statistik & filter.
     */
    public function index(Request $request)
    {
        $allowedRegionIds = $this->getAllowedRegionIds();

        // === STATISTIK ===
        $baseQuery = Laporan::whereIn('region_id', $allowedRegionIds);

        $stats = [
            'total'       => (clone $baseQuery)->count(),
            'pending'     => (clone $baseQuery)->where('status', 'Pending')->count(),
            'proses'      => (clone $baseQuery)->where('status', 'Proses')->count(),
            'dilanjutkan' => (clone $baseQuery)->where('status', 'Dilanjutkan')->count(),
            'selesai'     => (clone $baseQuery)->where('status', 'Selesai')->count(),
            'ditolak'     => (clone $baseQuery)->where('status', 'Ditolak')->count(),
        ];

        // Hitung laporan overdue (SLA terlewat)
        $allActive = (clone $baseQuery)->whereIn('status', ['Pending', 'Proses', 'Dilanjutkan'])->get();
        $stats['overdue'] = $allActive->filter(fn($l) => $l->isOverdue())->count();

        // Cek apakah ini mode Arsip (Bukti Pelaporan Warga)
        $isArchive = $request->get('is_archive') == true || $request->routeIs('admin.pelaporan.archive');

        // === QUERY UTAMA DENGAN FILTER ===
        $query = Laporan::with(['user', 'region', 'rtHandler', 'rwHandler', 'admin', 'rating'])
            ->whereIn('region_id', $allowedRegionIds)
            ->orderBy('created_at', 'desc');

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: Kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Filter: Level Eskalasi
        if ($request->filled('eskalasi')) {
            $query->where('escalation_level', $request->eskalasi);
        }

        // Filter: Tanggal
        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->sampai);
        }

        // Filter: Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('deskripsi', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $laporans = $query->paginate(15)->appends($request->query());

        // Dropdown filter options (kategori standar sistem + kategori dari database jika ada)
        $defaultCategories = ['Infrastruktur', 'Kebersihan', 'Keamanan', 'Fasilitas', 'Lingkungan', 'Pelayanan Publik', 'Administrasi', 'Lainnya'];
        $dbCategories = Laporan::whereIn('region_id', $allowedRegionIds)
            ->select('kategori')
            ->whereNotNull('kategori')
            ->distinct()
            ->pluck('kategori')
            ->toArray();
        $kategoriList = collect(array_unique(array_merge($defaultCategories, $dbCategories)))->sort()->values();

        if ($request->ajax()) {
            return view('admin.pelaporan.partials.table', compact('laporans', 'isArchive'))->render();
        }

        return view('admin.pelaporan.index', compact('laporans', 'stats', 'kategoriList', 'isArchive'));
    }

    /**
     * Detail laporan lengkap.
     */
    public function show($id)
    {
        $allowedRegionIds = $this->getAllowedRegionIds();

        $laporan = Laporan::with(['user', 'region', 'rtHandler', 'rwHandler', 'admin', 'rating'])
            ->whereIn('region_id', $allowedRegionIds)
            ->findOrFail($id);

        // Bangun timeline eskalasi
        $timeline = $this->buildTimeline($laporan);

        return view('admin.pelaporan.show', compact('laporan', 'timeline'));
    }

    /**
     * Tanggapi laporan (Admin Desa ke atas).
     */
    public function respond(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|min:5',
        ]);

        $allowedRegionIds = $this->getAllowedRegionIds();
        $laporan = Laporan::whereIn('region_id', $allowedRegionIds)->findOrFail($id);

        if (in_array($laporan->status, ['Selesai', 'Ditolak'])) {
            return back()->with('error', 'Laporan sudah ditutup dan tidak bisa ditanggapi lagi.');
        }

        $user = auth()->user();
        $laporan->catatan_admin = $request->catatan;
        $laporan->admin_id = $user->id;
        $laporan->status = 'Proses';
        $laporan->save();

        // Kirim notifikasi ke pelapor
        $this->notifyReporter($laporan, 'Laporan Anda sedang diproses oleh Admin.', 'laporan_proses');

        return back()->with('success', 'Tanggapan berhasil dikirim. Status laporan diubah menjadi "Proses".');
    }

    /**
     * Eskalasi laporan ke tingkat yang lebih tinggi.
     */
    public function escalate(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|min:5',
        ]);

        $allowedRegionIds = $this->getAllowedRegionIds();
        $laporan = Laporan::whereIn('region_id', $allowedRegionIds)->findOrFail($id);

        if (!$laporan->canBeEscalated()) {
            return back()->with('error', 'Laporan ini tidak dapat di-eskalasi (sudah di tingkat tertinggi atau sudah ditutup).');
        }

        $user = auth()->user();
        $laporan->catatan_admin = $request->catatan;
        $laporan->admin_id = $user->id;
        $laporan->escalateTo($user->id, $request->catatan);

        // Kirim notifikasi ke pelapor
        $this->notifyReporter($laporan, 'Laporan Anda telah diteruskan ke tingkat "' . ucfirst($laporan->escalation_level) . '" untuk penanganan lebih lanjut.', 'laporan_eskalasi');

        return back()->with('success', 'Laporan berhasil di-eskalasi ke tingkat "' . ucfirst($laporan->escalation_level) . '".');
    }

    /**
     * Selesaikan laporan.
     */
    public function resolve(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'nullable|string',
        ]);

        $allowedRegionIds = $this->getAllowedRegionIds();
        $laporan = Laporan::whereIn('region_id', $allowedRegionIds)->findOrFail($id);

        if (in_array($laporan->status, ['Selesai', 'Ditolak'])) {
            return back()->with('error', 'Laporan sudah ditutup.');
        }

        $user = auth()->user();
        $laporan->catatan_admin = $request->catatan ?? $laporan->catatan_admin;
        $laporan->admin_id = $user->id;
        $laporan->status = 'Selesai';
        $laporan->save();

        // Kirim notifikasi ke pelapor
        $this->notifyReporter($laporan, 'Laporan Anda telah diselesaikan oleh Admin. Terima kasih atas partisipasinya!', 'laporan_selesai');

        return back()->with('success', 'Laporan berhasil diselesaikan!');
    }

    /**
     * Tolak laporan.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|min:5',
        ]);

        $allowedRegionIds = $this->getAllowedRegionIds();
        $laporan = Laporan::whereIn('region_id', $allowedRegionIds)->findOrFail($id);

        if (in_array($laporan->status, ['Selesai', 'Ditolak'])) {
            return back()->with('error', 'Laporan sudah ditutup.');
        }

        $user = auth()->user();
        $laporan->catatan_admin = $request->catatan;
        $laporan->admin_id = $user->id;
        $laporan->status = 'Ditolak';
        $laporan->save();

        // Kirim notifikasi ke pelapor
        $this->notifyReporter($laporan, 'Mohon maaf, laporan Anda ditolak. Alasan: ' . $request->catatan, 'laporan_ditolak');

        return back()->with('success', 'Laporan telah ditolak.');
    }

    // ===================================
    // PRIVATE HELPERS
    // ===================================

    /**
     * Bangun data timeline eskalasi untuk tampilan detail.
     */
    private function buildTimeline(Laporan $laporan): array
    {
        $timeline = [];

        // 1. Laporan dibuat
        $timeline[] = [
            'icon'  => 'bx-edit',
            'color' => 'primary',
            'title' => 'Laporan Dibuat',
            'desc'  => 'Dilaporkan oleh ' . ($laporan->user->name ?? 'Unknown'),
            'time'  => $laporan->created_at,
            'notes' => null,
        ];

        // 2. Masuk ke RT (jika tujuan awal RT)
        if ($laporan->tujuan_laporan === 'rt' || $laporan->rt_handler_id) {
            $timeline[] = [
                'icon'  => 'bx-user',
                'color' => 'info',
                'title' => 'Diterima di Tingkat RT',
                'desc'  => $laporan->rtHandler ? 'Ditangani oleh ' . $laporan->rtHandler->name : 'Menunggu tanggapan RT',
                'time'  => $laporan->rt_handler_id ? $laporan->updated_at : null,
                'notes' => $laporan->catatan_rt,
            ];
        }

        // 3. Eskalasi / masuk ke RW
        if (in_array($laporan->escalation_level, ['rw', 'desa', 'kecamatan', 'kabupaten']) || $laporan->rw_handler_id) {
            $timeline[] = [
                'icon'  => 'bx-group',
                'color' => 'warning',
                'title' => 'Dilanjutkan ke Tingkat RW',
                'desc'  => $laporan->rwHandler ? 'Ditangani oleh ' . $laporan->rwHandler->name : 'Menunggu tanggapan RW',
                'time'  => $laporan->escalated_to_rw_at,
                'notes' => $laporan->catatan_rw,
            ];
        }

        // 4. Eskalasi ke Desa / Admin
        if (in_array($laporan->escalation_level, ['desa', 'kecamatan', 'kabupaten']) || $laporan->admin_id) {
            $timeline[] = [
                'icon'  => 'bx-building-house',
                'color' => 'danger',
                'title' => 'Ditangani Admin (' . ucfirst($laporan->escalation_level) . ')',
                'desc'  => $laporan->admin ? 'Ditangani oleh ' . $laporan->admin->name : 'Menunggu tanggapan Admin',
                'time'  => $laporan->admin_id ? $laporan->updated_at : null,
                'notes' => $laporan->catatan_admin,
            ];
        }

        // 5. Status akhir
        if ($laporan->status === 'Selesai') {
            $timeline[] = [
                'icon'  => 'bx-check-circle',
                'color' => 'success',
                'title' => 'Laporan Diselesaikan',
                'desc'  => 'Masalah telah ditangani dan laporan ditutup.',
                'time'  => $laporan->updated_at,
                'notes' => null,
            ];
        } elseif ($laporan->status === 'Ditolak') {
            $timeline[] = [
                'icon'  => 'bx-x-circle',
                'color' => 'secondary',
                'title' => 'Laporan Ditolak',
                'desc'  => 'Laporan tidak dapat ditindaklanjuti.',
                'time'  => $laporan->updated_at,
                'notes' => $laporan->catatan_admin,
            ];
        }

        return $timeline;
    }

    /**
     * Kirim notifikasi ke pelapor (warga).
     */
    private function notifyReporter(Laporan $laporan, string $message, string $type): void
    {
        try {
            Notification::create([
                'user_id'    => $laporan->user_id,
                'laporan_id' => $laporan->id,
                'type'       => $type,
                'title'      => 'Update Laporan #' . $laporan->id,
                'message'    => $message,
                'link'       => '/user/laporan/' . $laporan->id,
                'icon'       => 'fas fa-clipboard-check',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal kirim notifikasi pelaporan: ' . $e->getMessage());
        }
    }
}
