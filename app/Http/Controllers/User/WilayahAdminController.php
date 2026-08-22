<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Laporan;

class WilayahAdminController extends Controller
{
    use \App\Traits\ChecksStaffDelegation;



    public function indexLaporan(Request $request)
    {
        if ($splash = $this->checkDelegation($request, 'pelaporan', 'Pelaporan Masyarakat')) {
            return $splash;
        }

        $user = auth()->user();
        
        // Dapatkan Region milik User beserta descendants
        $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
        $allowedRegionIds[] = $user->region_id;

        $query = Laporan::with(['user'])->whereIn('region_id', $allowedRegionIds)->orderBy('created_at', 'desc');

        // Filter RW
        if ($request->filled('rw')) {
            $query->where('rw', $request->rw);
        }

        // Filter RT
        if ($request->filled('rt')) {
            $query->where('rt', $request->rt);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->searchWhereLike(['nama', 'deskripsi', 'lokasi'], $search);
            });
        }

        $laporans = $query->paginate(15);

        // Hitung Statistik khusus wilayah ini
        $statsQuery = Laporan::whereIn('region_id', $allowedRegionIds);
        
        $stats = [
            'total_laporan' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'Pending')->count(),
            'proses' => (clone $statsQuery)->where('status', 'Proses')->count(),
            'selesai' => (clone $statsQuery)->where('status', 'Selesai')->count(),
            'ditolak' => (clone $statsQuery)->where('status', 'Ditolak')->count(),
            'dilanjutkan' => (clone $statsQuery)->where('status', 'Dilanjutkan')->count(),
        ];

        // List untuk dropdown filter
        $rwList = Laporan::whereIn('region_id', $allowedRegionIds)->select('rw')->whereNotNull('rw')->distinct()->orderBy('rw')->get();
        $kategoriList = Laporan::whereIn('region_id', $allowedRegionIds)->select('kategori')->whereNotNull('kategori')->distinct()->orderBy('kategori')->get();

        return view('user.wilayah.laporan', compact('laporans', 'stats', 'rwList', 'kategoriList'));
    }

    public function indexPengumuman(Request $request)
    {
        $user = auth()->user();
        
        $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
        $allowedRegionIds[] = $user->region_id;
        
        $query = \App\Models\Announcement::with(['admin', 'region'])->whereIn('region_id', $allowedRegionIds)->orderBy('created_at', 'desc');
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->searchWhereLike(['title', 'description'], $search);
            });
        }
        
        $pengumumans = $query->paginate(15);
        
        // Buat Opsi Jangkauan Publikasi
        $region = \App\Models\Region::with('parent.parent')->find($user->region_id);
        $jangkauanOptions = [];
        if ($region) {
            $jangkauanOptions[] = ['id' => $region->id, 'label' => 'Internal ' . $region->name];
            if ($region->parent) {
                $jangkauanOptions[] = ['id' => $region->parent->id, 'label' => 'Publik Tingkat ' . $region->parent->name];
                if ($region->parent->parent && $region->type != 'desa') { // Limit up to 2 levels usually enough
                    $jangkauanOptions[] = ['id' => $region->parent->parent->id, 'label' => 'Publik Tingkat ' . $region->parent->parent->name];
                }
            }
        }
        
        return view('user.wilayah.pengumuman', compact('pengumumans', 'jangkauanOptions'));
    }

    public function storePengumuman(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'target_region_id' => 'required|exists:regions,id',
            'event_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        $user = auth()->user();
        
        // Verifikasi apakah target_region_id adalah parent/ancestor yang sah, atau diri sendiri
        $validRegionIds = \App\Models\Region::getAncestorIds($user->region_id);
        $validRegionIds[] = $user->region_id;
        
        if (!in_array($request->target_region_id, $validRegionIds)) {
            return back()->with('error', 'Anda tidak memiliki hak untuk mempublikasikan di wilayah tersebut.');
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('announcements', 'public');
        }

        \App\Models\Announcement::create([
            'admin_id' => $user->id,
            'region_id' => $request->target_region_id,
            'title' => $request->title,
            'type' => $request->type,
            'event_date' => $request->event_date,
            'location' => $request->location,
            'description' => $request->description,
            'image_path' => $imagePath,
            'is_active' => true, // Langsung aktif
        ]);

        return back()->with('success', 'Pengumuman baru berhasil dipublikasikan!');
    }

    public function indexBerita(Request $request)
    {
        $user = auth()->user();
        
        $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
        $allowedRegionIds[] = $user->region_id;
        
        $query = \App\Models\Announcement::with(['admin', 'region'])->whereIn('region_id', $allowedRegionIds)->orderBy('created_at', 'desc');
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->searchWhereLike(['title', 'description'], $search);
            });
        }
        
        $beritas = $query->paginate(15);
        
        // Buat Opsi Jangkauan Publikasi
        $region = \App\Models\Region::with('parent.parent')->find($user->region_id);
        $jangkauanOptions = [];
        if ($region) {
            $jangkauanOptions[] = ['id' => $region->id, 'label' => 'Internal ' . $region->name];
            if ($region->parent) {
                $jangkauanOptions[] = ['id' => $region->parent->id, 'label' => 'Publik Tingkat ' . $region->parent->name];
                if ($region->parent->parent && $region->type != 'desa') { // Limit up to 2 levels usually enough
                    $jangkauanOptions[] = ['id' => $region->parent->parent->id, 'label' => 'Publik Tingkat ' . $region->parent->parent->name];
                }
            }
        }
        
        return view('user.wilayah.berita', compact('beritas', 'jangkauanOptions'));
    }

    public function storeBerita(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'target_region_id' => 'required|exists:regions,id',
            'event_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        $user = auth()->user();
        
        // Verifikasi apakah target_region_id adalah parent/ancestor yang sah, atau diri sendiri
        $validRegionIds = \App\Models\Region::getAncestorIds($user->region_id);
        $validRegionIds[] = $user->region_id;
        
        if (!in_array($request->target_region_id, $validRegionIds)) {
            return back()->with('error', 'Anda tidak memiliki hak untuk mempublikasikan di wilayah tersebut.');
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('announcements', 'public');
        }

        \App\Models\Announcement::create([
            'admin_id' => $user->id,
            'region_id' => $request->target_region_id,
            'title' => $request->title,
            'type' => $request->type,
            'event_date' => $request->event_date,
            'location' => $request->location,
            'description' => $request->description,
            'image_path' => $imagePath,
            'is_active' => true, // Langsung aktif
        ]);

        return back()->with('success', 'Berita baru berhasil dipublikasikan!');
    }

    public function indexWarga(Request $request)
    {
        $search = $request->get('search');
        $user = auth()->user();
        
        $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
        $allowedRegionIds[] = $user->region_id;
        
        $usersQuery = \App\Models\User::with('region')
            ->whereIn('region_id', $allowedRegionIds)
            ->where('role', 'user');

        $wargas = $usersQuery->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->searchWhereLike(['name', 'email'], $search);
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->appends(['search' => $search]);

        return view('user.wilayah.warga', compact('wargas', 'search'));
    }

    public function showLaporan($id)
    {
        $user = auth()->user();
        $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
        $allowedRegionIds[] = $user->region_id;

        $laporan = Laporan::with(['user', 'region'])->whereIn('region_id', $allowedRegionIds)->findOrFail($id);
        
        return view('user.wilayah.laporan_detail', compact('laporan'));
    }

    public function respondLaporan(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string',
        ]);

        $user = auth()->user();
        $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
        $allowedRegionIds[] = $user->region_id;

        $laporan = Laporan::whereIn('region_id', $allowedRegionIds)->findOrFail($id);

        if ($laporan->status === 'Selesai' || $laporan->status === 'Ditolak') {
            return back()->with('error', 'Laporan sudah ditutup.');
        }

        // Tentukan level admin saat ini berdasarkan jabatannya
        $currentAdminLevel = $user->region->type; // 'rt', 'rw', 'desa', dll

        if ($currentAdminLevel === 'rt') {
            $laporan->catatan_rt = $request->catatan;
            $laporan->rt_handler_id = $user->id;
        } elseif ($currentAdminLevel === 'rw') {
            $laporan->catatan_rw = $request->catatan;
            $laporan->rw_handler_id = $user->id;
        } else {
            $laporan->catatan_admin = $request->catatan;
            $laporan->admin_id = $user->id;
        }

        $laporan->status = 'Proses';
        $laporan->save();

        return back()->with('success', 'Tanggapan berhasil dikirim dan status diubah menjadi Proses.');
    }

    public function escalateLaporan(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string',
        ]);

        $user = auth()->user();
        $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
        $allowedRegionIds[] = $user->region_id;

        $laporan = Laporan::whereIn('region_id', $allowedRegionIds)->findOrFail($id);

        if (!$laporan->canBeEscalated()) {
            return back()->with('error', 'Laporan ini tidak dapat di-eskalasi (sudah mencapai tingkat tertinggi atau sudah ditutup).');
        }

        // Pastikan level admin yang mencoba eskalasi sesuai dengan level laporan saat ini
        $currentAdminLevel = $user->region->type;
        $laporanLevel = $laporan->escalation_level ?? 'rt';

        if ($currentAdminLevel !== $laporanLevel && $user->role !== 'super_admin') {
            return back()->with('error', "Anda tidak dapat me-eskalasi laporan ini. Laporan saat ini berada di tingkat: $laporanLevel");
        }

        // Lakukan eskalasi manual
        $laporan->escalateTo($user->id, $request->catatan);

        return back()->with('success', 'Laporan berhasil di-eskalasi ke tingkat atasnya.');
    }

    public function resolveLaporan(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'nullable|string',
        ]);

        $user = auth()->user();
        $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
        $allowedRegionIds[] = $user->region_id;

        $laporan = Laporan::whereIn('region_id', $allowedRegionIds)->findOrFail($id);

        if ($laporan->status === 'Selesai' || $laporan->status === 'Ditolak') {
            return back()->with('error', 'Laporan sudah ditutup.');
        }

        $currentAdminLevel = $user->region->type;

        if ($currentAdminLevel === 'rt') {
            $laporan->catatan_rt = $request->catatan ?? $laporan->catatan_rt;
            $laporan->rt_handler_id = $user->id;
        } elseif ($currentAdminLevel === 'rw') {
            $laporan->catatan_rw = $request->catatan ?? $laporan->catatan_rw;
            $laporan->rw_handler_id = $user->id;
        } else {
            $laporan->catatan_admin = $request->catatan ?? $laporan->catatan_admin;
            $laporan->admin_id = $user->id;
        }

        $laporan->status = 'Selesai';
        $laporan->save();

        return back()->with('success', 'Laporan berhasil diselesaikan!');
    }

    /**
     * Cetak Surat Bukti Pelaporan (PDF)
     * Menggunakan gambar latar desain dari user dan QR Code validasi digital.
     */
    public function cetakBukti($id)
    {
        $user = auth()->user();
        $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
        $allowedRegionIds[] = $user->region_id;

        $laporan = Laporan::with(['user', 'region'])->whereIn('region_id', $allowedRegionIds)->findOrFail($id);

        // Hanya bisa cetak jika status sudah Proses, Dilanjutkan, atau Selesai
        if (!in_array($laporan->status, ['Proses', 'Dilanjutkan', 'Selesai'])) {
            return back()->with('error', 'Surat bukti hanya dapat dicetak untuk laporan yang sudah diproses.');
        }

        // Tentukan nama handler (penanggung jawab terakhir)
        $handler_name = 'Sistem SiladesBeng';
        if ($laporan->admin_id) {
            $handler = \App\Models\User::find($laporan->admin_id);
            if ($handler) $handler_name = $handler->name;
        } elseif ($laporan->rw_handler_id) {
            $handler = \App\Models\User::find($laporan->rw_handler_id);
            if ($handler) $handler_name = $handler->name;
        } elseif ($laporan->rt_handler_id) {
            $handler = \App\Models\User::find($laporan->rt_handler_id);
            if ($handler) $handler_name = $handler->name;
        }

        $qrUrl = url('/validasi/laporan/' . $laporan->id . '?token=' . hash_hmac('sha256', $laporan->id . $laporan->created_at, config('app.key')));
        $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qrUrl);
        $qrBase64 = '';
        try {
            $context = stream_context_create(["ssl" => ["verify_peer" => false, "verify_peer_name" => false]]);
            $qrData = @file_get_contents($qrApiUrl, false, $context);
            if ($qrData) {
                $qrBase64 = 'data:image/png;base64,' . base64_encode($qrData);
            }
        } catch (\Exception $e) {}

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.bukti_laporan', [
            'laporan' => $laporan,
            'handler_name' => $handler_name,
            'qr_base64' => $qrBase64,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'Bukti_Laporan_SDB_' . str_pad($laporan->id, 5, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->stream($filename);
    }
}
