<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Laporan;

class WilayahAdminController extends Controller
{
    use \App\Traits\ChecksStaffDelegation;



    /**
     * Wilayah yang boleh diurus admin RT/RW ini.
     *
     * Region::getDescendantIds(null) memakai where('parent_id', null), yang oleh
     * Laravel diubah menjadi IS NULL — jadi yang kembali adalah SELURUH pohon
     * wilayah dari akarnya. Akibatnya admin yang region_id-nya kosong justru
     * melihat, dan bisa menanggapi, laporan satu kabupaten penuh. Di sini
     * ketiadaan wilayah berarti tidak ada yang bisa dijangkau.
     *
     * @return array<int>
     */
    private function wilayahDiurus($user): array
    {
        if (! $user->region_id) {
            return [];
        }

        $ids = \App\Models\Region::getDescendantIds($user->region_id);
        $ids[] = $user->region_id;

        return $ids;
    }

    /**
     * Tingkat jabatan admin ini: 'rt', 'rw', 'desa', dan seterusnya.
     *
     * $user->region bisa null — akun yang belum ditempatkan, atau wilayahnya
     * sudah dihapus — dan membaca ->type langsung membuat halamannya jatuh 500.
     */
    private function tingkatAdmin($user): ?string
    {
        return $user->region?->type;
    }

    /**
     * Laporan hanya boleh disentuh oleh tingkat yang sedang memegangnya.
     *
     * escalateLaporan() sudah memeriksa hal ini, tetapi respondLaporan() dan
     * resolveLaporan() tidak: admin RT bisa MENUTUP laporan yang sudah
     * dieskalasi ke desa, mendahului tingkat yang seharusnya menanganinya.
     */
    private function tingkatSesuai($user, Laporan $laporan): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        return $this->tingkatAdmin($user) === ($laporan->escalation_level ?? 'rt');
    }

    public function indexLaporan(Request $request)
    {
        if ($splash = $this->checkDelegation($request, 'pelaporan', 'Pelaporan Masyarakat')) {
            return $splash;
        }

        $user = auth()->user();
        
        // Dapatkan Region milik User beserta descendants
        $allowedRegionIds = $this->wilayahDiurus($user);

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
                // 'nama' dan 'lokasi' tersimpan terenkripsi ChaCha20, jadi LIKE
                // atas ciphertext tidak akan pernah cocok dengan kata yang
                // diketik — pencariannya selama ini diam-diam nihil. Nama
                // pelapor dicari lewat relasi user yang tidak dienkripsi.
                $q->searchWhereLike(['deskripsi', 'kategori'], $search)
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->searchWhereLike(['name'], $search);
                  });
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
        
        $allowedRegionIds = $this->wilayahDiurus($user);
        
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

        $announcement = \App\Models\Announcement::create([
            'admin_id' => $user->id,
            'region_id' => $request->target_region_id,
            'title' => $request->title,
            'type' => $request->type,
            'post_category' => 'Pengumuman',
            'event_date' => $request->event_date,
            'location' => $request->location,
            'description' => $request->description,
            'image_path' => $imagePath,
            'is_active' => true, // Langsung aktif
        ]);

        \App\Services\NotificationService::broadcastAnnouncement($announcement);

        return back()->with('success', 'Pengumuman baru berhasil dipublikasikan!');
    }

    public function indexBerita(Request $request)
    {
        $user = auth()->user();
        
        $allowedRegionIds = $this->wilayahDiurus($user);
        
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

        $berita = \App\Models\Announcement::create([
            'admin_id' => $user->id,
            'region_id' => $request->target_region_id,
            'title' => $request->title,
            'type' => $request->type,
            'post_category' => 'Berita',
            'event_date' => $request->event_date,
            'location' => $request->location,
            'description' => $request->description,
            'image_path' => $imagePath,
            'is_active' => true, // Langsung aktif
        ]);

        \App\Services\NotificationService::broadcastAnnouncement($berita);

        return back()->with('success', 'Berita baru berhasil dipublikasikan!');
    }

    public function indexWarga(Request $request)
    {
        $search = $request->get('search');
        $user = auth()->user();
        
        $allowedRegionIds = $this->wilayahDiurus($user);
        
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
        $allowedRegionIds = $this->wilayahDiurus($user);

        $laporan = Laporan::with(['user', 'region'])->whereIn('region_id', $allowedRegionIds)->findOrFail($id);
        
        return view('user.wilayah.laporan_detail', compact('laporan'));
    }

    public function respondLaporan(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string',
        ]);

        $user = auth()->user();
        $allowedRegionIds = $this->wilayahDiurus($user);

        $laporan = Laporan::whereIn('region_id', $allowedRegionIds)->findOrFail($id);

        if ($laporan->status === 'Selesai' || $laporan->status === 'Ditolak') {
            return back()->with('error', 'Laporan sudah ditutup.');
        }

        if (! $this->tingkatSesuai($user, $laporan)) {
            return back()->with('error', 'Laporan ini sedang berada di tingkat "'
                . ($laporan->escalation_level ?? 'rt') . '", bukan tingkat Anda.');
        }

        // Tentukan level admin saat ini berdasarkan jabatannya
        $currentAdminLevel = $this->tingkatAdmin($user); // 'rt', 'rw', 'desa', dll

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

        // Notifikasi ke warga pelapor
        \App\Services\NotificationService::notifyLaporanResponded($laporan, strtoupper($currentAdminLevel), $request->catatan);

        return back()->with('success', 'Tanggapan berhasil dikirim dan status diubah menjadi Proses.');
    }

    public function escalateLaporan(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string',
        ]);

        $user = auth()->user();
        $allowedRegionIds = $this->wilayahDiurus($user);

        $laporan = Laporan::whereIn('region_id', $allowedRegionIds)->findOrFail($id);

        if (!$laporan->canBeEscalated()) {
            return back()->with('error', 'Laporan ini tidak dapat di-eskalasi (sudah mencapai tingkat tertinggi atau sudah ditutup).');
        }

        // Pastikan level admin yang mencoba eskalasi sesuai dengan level laporan saat ini
        $currentAdminLevel = $this->tingkatAdmin($user);
        $laporanLevel = $laporan->escalation_level ?? 'rt';

        if ($currentAdminLevel !== $laporanLevel && $user->role !== 'super_admin') {
            return back()->with('error', "Anda tidak dapat me-eskalasi laporan ini. Laporan saat ini berada di tingkat: $laporanLevel");
        }

        // Lakukan eskalasi manual
        $laporan->escalateTo($user->id, $request->catatan);

        // Notifikasi ke warga pelapor
        \App\Services\NotificationService::notifyLaporanEscalated($laporan, $laporan->escalation_level ?? 'atas');

        return back()->with('success', 'Laporan berhasil di-eskalasi ke tingkat atasnya.');
    }

    public function resolveLaporan(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'nullable|string',
        ]);

        $user = auth()->user();
        $allowedRegionIds = $this->wilayahDiurus($user);

        $laporan = Laporan::whereIn('region_id', $allowedRegionIds)->findOrFail($id);

        if ($laporan->status === 'Selesai' || $laporan->status === 'Ditolak') {
            return back()->with('error', 'Laporan sudah ditutup.');
        }

        if (! $this->tingkatSesuai($user, $laporan)) {
            return back()->with('error', 'Laporan ini sudah diteruskan ke tingkat "'
                . ($laporan->escalation_level ?? 'rt') . '" dan hanya bisa diselesaikan di sana.');
        }

        $currentAdminLevel = $this->tingkatAdmin($user);

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

        // Notifikasi ke warga pelapor
        \App\Services\NotificationService::notifyLaporanResolved($laporan, $request->catatan);

        return back()->with('success', 'Laporan berhasil diselesaikan!');
    }

    /**
     * Cetak Surat Bukti Pelaporan (PDF)
     * Menggunakan gambar latar desain dari user dan QR Code validasi digital.
     */
    public function cetakBukti($id)
    {
        ini_set('memory_limit', '256M');
        $user = auth()->user();
        $allowedRegionIds = $this->wilayahDiurus($user);

        $laporan = Laporan::with(['user', 'region'])->whereIn('region_id', $allowedRegionIds)->findOrFail($id);

        // Hanya bisa cetak jika status sudah Proses, Dilanjutkan, atau Selesai
        if (!in_array($laporan->status, ['Proses', 'Dilanjutkan', 'Selesai'])) {
            return back()->with('error', 'Surat bukti hanya dapat dicetak untuk laporan yang sudah diproses.');
        }

        // Tentukan nama handler (penanggung jawab)
        $handler_name = null;
        $jabatan_handler = 'Pemerintah Desa';

        if ($laporan->admin_id) {
            $handler = \App\Models\User::find($laporan->admin_id);
            if ($handler) {
                $handler_name = $handler->name;
                $jabatan_handler = 'Pemerintah Desa';
            }
        } elseif ($laporan->rw_handler_id) {
            $handler = \App\Models\User::find($laporan->rw_handler_id);
            if ($handler) {
                $handler_name = $handler->name;
                $jabatan_handler = 'Admin RW ' . ($laporan->rw_number ?? '');
            }
        } elseif ($laporan->rt_handler_id) {
            $handler = \App\Models\User::find($laporan->rt_handler_id);
            if ($handler) {
                $handler_name = $handler->name;
                $jabatan_handler = 'Admin RT ' . ($laporan->rt_number ?? '');
            }
        }

        // Jika belum ada handler spesifik, gunakan Nama Lengkap profil Admin Desa wilayah laporan atau admin yang sedang login
        if (empty($handler_name)) {
            $desaId = $laporan->region_id ?? $laporan->user?->region_id;
            $adminDesa = \App\Models\User::where('role', 'admin_desa')
                ->where('region_id', $desaId)
                ->first();

            if (!$adminDesa && in_array($user->role, ['admin_desa', 'admin', 'super_admin'])) {
                $adminDesa = $user;
            }

            if ($adminDesa && !empty($adminDesa->name)) {
                $handler_name = $adminDesa->name;
                $jabatan_handler = 'Pemerintah Desa';
            } else {
                $regionName = $laporan->region?->name ?? 'Desa';
                $handler_name = 'Pemerintah ' . $regionName;
                $jabatan_handler = 'Pemerintah Desa';
            }
        }

        $validasiUrl = url('/validasi/laporan/' . $laporan->id . '?token=' . hash_hmac('sha256', $laporan->id . $laporan->created_at, config('app.key')));
        $qrSize = 200;
        $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$qrSize}x{$qrSize}&ecc=H&margin=4&data=" . urlencode($validasiUrl);
        $qrBase64 = '';
        try {
            $context = stream_context_create([
                "ssl"  => ["verify_peer" => false, "verify_peer_name" => false],
                "http" => ["timeout" => 5],
            ]);
            $qrRawData = @file_get_contents($qrApiUrl, false, $context);
            if ($qrRawData) {
                $qrImg = @imagecreatefromstring($qrRawData);
                if ($qrImg) {
                    $logoPath = public_path('Admin/img/illustrations/logodomain-256.png');
                    if (file_exists($logoPath)) {
                        $logoImg = @imagecreatefrompng($logoPath);
                        if ($logoImg) {
                            $logoSize = (int) round($qrSize * 0.16);
                            $logoX = (int) round(($qrSize - $logoSize) / 2);
                            $logoY = (int) round(($qrSize - $logoSize) / 2);
                            imagefilledrectangle($qrImg, $logoX - 2, $logoY - 2, $logoX + $logoSize + 2, $logoY + $logoSize + 2, imagecolorallocate($qrImg, 255, 255, 255));
                            imagecopyresampled($qrImg, $logoImg, $logoX, $logoY, 0, 0, $logoSize, $logoSize, imagesx($logoImg), imagesy($logoImg));
                            imagedestroy($logoImg);
                        }
                    }
                    ob_start();
                    imagepng($qrImg);
                    $qrBase64 = base64_encode(ob_get_clean());
                    imagedestroy($qrImg);
                } else {
                    $qrBase64 = base64_encode($qrRawData);
                }
            }
        } catch (\Exception $e) {}

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.bukti_laporan', [
            'laporan' => $laporan,
            'handler_name' => $handler_name,
            'jabatan_handler' => $jabatan_handler,
            'qrBase64' => $qrBase64,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'Bukti_Laporan_SDB_' . str_pad($laporan->id, 5, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->stream($filename);
    }
}

