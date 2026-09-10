<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Notification;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    /**
     * Desa (atau kelurahan) tempat warga ini berdomisili.
     *
     * region_id warga bisa menunjuk RT, RW, atau langsung desa, jadi ditelusuri
     * ke atas sampai bertemu tingkat desa. Penelusurannya dibatasi jumlah
     * langkah: data wilayah yang saling menunjuk (RT -> RW -> RT) sebelumnya
     * membuat perulangan ini berputar selamanya.
     */
    private function desaWarga(?int $regionId): ?Region
    {
        if (! $regionId) {
            return null;
        }

        $wilayahWarga = Region::find($regionId);
        if (! $wilayahWarga) {
            return null;
        }

        $temp = $wilayahWarga;
        $langkah = 0;
        while ($temp && $langkah++ < 10) {
            if ($temp->type === 'desa' || $temp->type === 'kelurahan') {
                return $temp;
            }
            $temp = $temp->parent;
        }

        // Cadangan untuk data wilayah yang tingkat desanya belum diberi type.
        if ($wilayahWarga->type === 'rt') {
            return $wilayahWarga->parent?->parent;
        }
        if ($wilayahWarga->type === 'rw') {
            return $wilayahWarga->parent;
        }

        return null;
    }

    /**
     * Wilayah yang boleh dituju sebuah laporan dari warga ini.
     *
     * @return array<int>
     */
    private function wilayahTujuanSah(User $user): array
    {
        $desa = $this->desaWarga($user->region_id);

        $ids = [];
        if ($desa) {
            $ids = Region::getDescendantIds($desa->id);
            $ids[] = $desa->id;
        }
        if ($user->region_id) {
            $ids[] = $user->region_id;
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    /**
     * Siapa yang boleh membuka surat bukti sebuah laporan.
     *
     * Sebelumnya yang dicek hanya perannya: admin RT atau RW mana pun bisa
     * mengunduh PDF laporan siapa saja di seluruh kabupaten, padahal isinya
     * nama dan alamat pelapor dalam bentuk terbaca — dua kolom yang justru
     * sengaja dienkripsi di basis data. Daftar perannya juga menyebut
     * 'superadmin', peran yang tidak ada di sistem ini (yang benar
     * 'super_admin'), sehingga Kominfo malah ikut tertolak.
     */
    private function bolehMelihatLaporan(Laporan $laporan, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ((int) $laporan->user_id === (int) $user->id) {
            return true;
        }

        if ($user->role === 'super_admin') {
            return true;
        }

        $peranWilayah = ['admin', 'admin_kecamatan', 'admin_desa', 'admin_rw', 'admin_rt', 'staff'];

        // Tanpa wilayah, tidak ada yang bisa dijangkau. Region::getDescendantIds(null)
        // justru mengembalikan SELURUH pohon wilayah, jadi tanpa penjaga ini
        // admin yang region_id-nya kosong malah melihat satu kabupaten penuh.
        if (! in_array($user->role, $peranWilayah, true) || ! $user->region_id) {
            return false;
        }

        $jangkauan = Region::getDescendantIds($user->region_id);
        $jangkauan[] = $user->region_id;

        return in_array((int) $laporan->region_id, array_map('intval', $jangkauan), true);
    }

    public function index()
    {
        $laporans = Laporan::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('user.laporan.index', compact('laporans'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // TODO (KYC): Validasi KYC dimatikan sementara untuk kemudahan development
        // if ($user->verification_status !== 'verified') {
        //     return redirect()->back()->with('show_kyc_modal', true)->with('error', 'Anda harus melakukan verifikasi KTP terlebih dahulu untuk membuat laporan.');
        // }
        
        // Desa warga, lewat penelusuran yang sama dengan yang dipakai store()
        // untuk memeriksa wilayah tujuan — supaya daftar RT/RW yang ditawarkan
        // di formulir persis sama dengan yang nanti diterima saat disimpan.
        $desaId = $this->desaWarga($user->region_id)?->id;

        // Ambil semua RW dan RT di bawah desa ini, dengan status admin-nya
        $allRWData = collect();
        $allRTData = collect();
        $hasAdminRT = false;
        $hasAdminRW = false;

        if ($desaId) {
            // Ambil semua RW di desa
            $allRWs = \App\Models\Region::where('parent_id', $desaId)
                ->where('type', 'rw')
                ->get();

            foreach ($allRWs as $rw) {
                // Cek apakah RW ini punya admin
                $hasAdmin = User::where('role', 'admin_rw')
                    ->where('region_id', $rw->id)
                    ->exists();
                
                if ($hasAdmin) $hasAdminRW = true;
                
                $rw->has_admin = $hasAdmin;
                $allRWData->push($rw);

                // Ambil semua RT di bawah RW ini
                $rts = \App\Models\Region::where('parent_id', $rw->id)
                    ->where('type', 'rt')
                    ->get();

                foreach ($rts as $rt) {
                    $hasRtAdmin = User::where('role', 'admin_rt')
                        ->where('region_id', $rt->id)
                        ->exists();
                    
                    if ($hasRtAdmin) $hasAdminRT = true;

                    // Tambahkan info RW parent ke RT untuk label dropdown
                    $rt->rw_name = $rw->name;
                    $rt->has_admin = $hasRtAdmin;
                    $allRTData->push($rt);
                }
            }
        }

        return view('user.laporan.create', compact(
            'hasAdminRT', 'hasAdminRW', 'allRTData', 'allRWData'
        ));
    }

    public function store(Request $request)
    {
        // TODO (KYC): Validasi KYC dimatikan sementara untuk kemudahan development
        // if (auth()->user()->verification_status !== 'verified') {
        //     return redirect()->back()->with('show_kyc_modal', true)->with('error', 'Anda harus melakukan verifikasi KTP terlebih dahulu untuk membuat laporan.');
        // }
        
        // Validasi
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string|min:20',
            'kategori' => 'required|string',
            'lokasi' => 'required|string|max:255',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'tujuan_laporan' => 'required|in:rt,rw,desa',
            'target_region_id' => 'nullable|integer|exists:regions,id',
            'bukti' => 'nullable|array|max:3',
            'bukti.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = auth()->user();

        // Wilayah tujuan wajib berada di dalam desa warga sendiri.
        //
        // Aturan 'exists:regions,id' saja hanya memastikan wilayahnya ada, bukan
        // bahwa wilayah itu urusan warga ini: dengan menyisipkan target_region_id
        // sebuah RT di desa lain, laporannya benar-benar tersimpan di sana dan
        // muncul di daftar admin RT desa tersebut.
        if (! empty($validated['target_region_id'])
            && ! in_array((int) $validated['target_region_id'], $this->wilayahTujuanSah($user), true)) {
            return back()
                ->withInput()
                ->withErrors(['target_region_id' => 'Wilayah tujuan harus berada di desa Anda sendiri.']);
        }

        // Tentukan region_id tujuan laporan
        // Jika user memilih RT/RW dari dropdown, gunakan target_region_id
        // Jika tidak (pilih Desa), fallback ke region_id domisili user
        $targetRegionId = $validated['target_region_id'] ?? $user->region_id;

        // Jika tujuan_laporan adalah 'desa', arahkan langsung ke Region Desa
        if ($validated['tujuan_laporan'] === 'desa') {
            $desaRegion = \App\Models\Region::where('type', 'desa')->first();
            if ($desaRegion) {
                $targetRegionId = $desaRegion->id;
            }
        }

        // Prepare data TANPA bukti dulu
        $data = [
            'user_id' => $user->id,
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'],
            'kategori' => $validated['kategori'],
            'lokasi' => $validated['lokasi'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'tujuan_laporan' => $validated['tujuan_laporan'],
            // Tingkat awal mengikuti tujuan laporan. Sebelumnya kolom ini tidak
            // pernah diisi sehingga selalu memakai bawaannya, 'rt', walau
            // laporannya ditujukan ke desa — akibatnya admin desa yang menekan
            // Eskalasi justru menaikkannya ke 'rw', satu tingkat DI BAWAH
            // dirinya sendiri.
            'escalation_level' => $validated['tujuan_laporan'],
            'status' => 'Pending',
            'rw' => $user->rw,
            'rt' => $user->rt,
            'rw_number' => $user->rw,
            'rt_number' => $user->rt,
            'region_id' => $targetRegionId,
        ];

        // Upload bukti SETELAH validasi.
        //
        // Lewat disk 'public', bukan $_SERVER['DOCUMENT_ROOT']. Nilai itu kosong
        // di luar request web (antrean, artisan, pengujian), sehingga tujuannya
        // jatuh menjadi '/storage/laporan' di akar drive. Jalur yang disimpan
        // tetap berbentuk 'laporan/xxx.jpg', jadi asset('storage/'.$p) di view
        // tidak berubah sama sekali — dan berkasnya kini bisa dihapus lagi lewat
        // disk yang sama saat laporannya dibatalkan warga.
        if ($request->hasFile('bukti')) {
            $buktiPaths = [];

            foreach ($request->file('bukti') as $file) {
                if (! $file->isValid()) {
                    continue;
                }

                $extension = strtolower($file->extension());

                // Strict whitelist extension
                $allowedExtensions = ['jpg', 'jpeg', 'png'];
                if (!in_array($extension, $allowedExtensions)) {
                    return back()->with('error', 'Format file bukti tidak valid. Hanya JPG, JPEG, PNG yang diizinkan.')->withInput();
                }

                $filename = time() . '_' . Str::random(24) . '.' . $extension;
                $file->storeAs('laporan', $filename, 'public');

                // SIMPAN RELATIVE URL
                $buktiPaths[] = 'laporan/' . $filename;
            }

            if (!empty($buktiPaths)) {
                $data['bukti'] = json_encode($buktiPaths);
            }
        }

        // Simpan laporan
        $laporan = Laporan::create($data);

        Log::info('Laporan created', [
            'id' => $laporan->id,
            'has_bukti' => isset($data['bukti']),
            'bukti_value' => $data['bukti'] ?? 'null',
        ]);

        // Smart Routing - Kirim notifikasi berdasarkan region tujuan yang dipilih
        try {
            $targetRegion = \App\Models\Region::find($targetRegionId);
            $regionName = $targetRegion ? $targetRegion->name : 'Unknown';

            // Kumpulkan region_id dari target ke kabupaten (untuk fallback)
            $regionIds = [];
            $tempRegion = $targetRegion;
            while ($tempRegion) {
                $regionIds[] = $tempRegion->id;
                $tempRegion = $tempRegion->parent;
            }

            $targetAdmins = collect();
            $actualDestination = $validated['tujuan_laporan']; // rt, rw, atau desa

            // STEP 1: Cek admin di region tujuan yang dipilih (RT)
            if ($actualDestination === 'rt' && $targetRegionId) {
                $adminRt = User::where('role', 'admin_rt')
                    ->where('region_id', $targetRegionId)
                    ->get();

                if ($adminRt->isNotEmpty()) {
                    $targetAdmins = $adminRt;
                    Log::info('Laporan dikirim ke Admin RT', ['region_id' => $targetRegionId]);
                } else {
                    // RT belum ada admin, eskalasi otomatis ke RW
                    $actualDestination = 'rw';
                    Log::info('Admin RT belum ada, eskalasi otomatis ke RW');
                }
            }

            // STEP 2: Cek ketersediaan Admin RW
            if ($targetAdmins->isEmpty() && in_array($actualDestination, ['rw', 'rt'])) {
                // Cari RW region: jika target adalah RT, naik ke parent (RW)
                $rwRegionId = $targetRegionId;
                if ($targetRegion && $targetRegion->type === 'rt') {
                    $rwRegionId = $targetRegion->parent_id;
                }

                if ($rwRegionId) {
                    $adminRw = User::where('role', 'admin_rw')
                        ->where('region_id', $rwRegionId)
                        ->get();

                    if ($adminRw->isNotEmpty()) {
                        $targetAdmins = $adminRw;
                        $actualDestination = 'rw';
                        Log::info('Laporan dikirim ke Admin RW', ['region_id' => $rwRegionId]);
                    } else {
                        $actualDestination = 'desa';
                        Log::info('Admin RW belum ada, eskalasi otomatis ke Desa');
                    }
                } else {
                    $actualDestination = 'desa';
                }
            }

            // STEP 3: Fallback ke Admin Desa / Super Admin
            if ($targetAdmins->isEmpty()) {
                $targetAdmins = User::whereIn('role', ['admin', 'super_admin', 'admin_desa'])
                    ->whereIn('region_id', $regionIds)
                    ->get();
                $actualDestination = 'desa';
                Log::info('Laporan dikirim ke Admin Desa (fallback)');
            }

            // Update tujuan laporan jika berubah karena eskalasi otomatis.
            // escalation_level ikut disamakan, kalau tidak laporan yang dialihkan
            // ke desa karena RT-nya belum punya admin tetap tercatat di tingkat
            // 'rt' dan tombol Eskalasi milik admin desa kembali salah arah.
            if ($actualDestination !== $validated['tujuan_laporan']) {
                $laporan->update([
                    'tujuan_laporan'   => $actualDestination,
                    'escalation_level' => $actualDestination,
                ]);
            }

            // Kirim notifikasi ke admin yang sudah ditentukan
            foreach ($targetAdmins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'laporan_id' => $laporan->id,
                    'type' => 'laporan_baru',
                    'title' => 'Laporan Baru Masuk',
                    'message' => "User {$user->name} telah melakukan pelaporan dari {$regionName}. Kategori: {$laporan->kategori}",
                    // Rute admin untuk detail laporan warga adalah
                    // /admin/pelaporan/{id}; '/admin/laporan/{id}' tidak pernah
                    // ada, jadi setiap notifikasi laporan baru berujung 404.
                    'link' => route('admin.pelaporan.show', $laporan->id),
                    'icon' => 'fas fa-file-alt',
                ]);
            }

            // Buat AdminNotification untuk dropdown navbar & dashboard
            \App\Models\AdminNotification::create([
                'type' => 'laporan',
                'reference_id' => $laporan->id,
                'region_id' => $targetRegionId,
                'title' => 'Laporan Warga Baru',
                'message' => "Laporan baru dari {$user->name} ({$regionName}) - Kategori: {$laporan->kategori}",
                'is_read' => false,
            ]);

            // Kirim notifikasi ke warga pelapor
            \App\Services\NotificationService::notifyLaporanSubmitted($laporan);
        } catch (\Exception $e) {
            Log::error('Notif error: ' . $e->getMessage());
        }

        return redirect()->route('user.laporan.show', $laporan->id)
            ->with('success', 'Laporan berhasil dibuat!');
    }

    public function exportPdf(Request $request, $id)
    {
        // Pastikan user login (session atau token Sanctum)
        $user = auth()->user();
        if (!$user && $request->has('token')) {
            $tokenStr = $request->query('token');
            $personalToken = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenStr);
            if ($personalToken) {
                $user = $personalToken->tokenable;
            }
        }

        if (!$user) {
            return redirect()->route('login');
        }

        $laporan = Laporan::with(['user', 'admin'])->findOrFail($id);

        if (! $this->bolehMelihatLaporan($laporan, $user)) {
            abort(403, 'Laporan ini berada di luar wilayah wewenang Anda.');
        }

        // QR kode validasi surat.
        //
        // Dulu memakai chart.googleapis.com — layanan Google Image Charts yang
        // sudah dihentikan dan kini menjawab HTTP 404. Halaman galatnya ikut
        // ter-base64 lalu dipasang sebagai gambar QR, jadi surat buktinya membawa
        // QR rusak yang tidak bisa dipindai. Sekarang memakai penyedia yang sama
        // dengan cetakBukti() milik RT/RW supaya keduanya seragam.
        $qrData = urlencode(url('/validasi/laporan/' . $laporan->id . '?token=' . hash_hmac('sha256', $laporan->id . $laporan->created_at, config('app.key'))));
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . $qrData;
        
        try {
            $qrImage = \Illuminate\Support\Facades\Http::withoutVerifying()->timeout(10)->get($qrUrl)->body();
            $qrBase64 = base64_encode($qrImage);
        } catch (\Exception $e) {
            $qrBase64 = null;
        }

        $handlerName = $laporan->admin ? $laporan->admin->name : 'Pemerintah Desa Bengkalis';

        return Pdf::loadView('pdf.bukti_laporan', [
            'laporan' => $laporan,
            'handler_name' => $handlerName,
            'waktu_cetak' => now()->format('d F Y, H:i'),
            'qrBase64' => $qrBase64
        ])->download('Bukti_Laporan_'.$laporan->id.'.pdf');
    }

public function show($id)
{
    $userId = auth()->id();

    if (!$userId) {
        abort(403);
    }

    $laporan = Laporan::with(['user', 'rating'])
        ->where('id', $id)
        ->where('user_id', $userId)
        ->firstOrFail();

    return view('user.laporan.show', compact('laporan'));
}


    public function destroy(Laporan $laporan)
{
    // Pastikan login
    if (!auth()->check()) {
        abort(403, 'Unauthorized');
    }

    // Pastikan pemilik
    if ((int) $laporan->user_id !== (int) auth()->id()) {
        abort(403, 'Anda tidak berhak menghapus laporan ini.');
    }

    // Status harus Pending (WAJIB KONSISTEN)
    if ($laporan->status !== 'Pending') {
        return back()->with('error', 'Laporan yang sudah diproses tidak dapat dihapus.');
    }

    // Batas waktu 24 jam
    if ($laporan->created_at->diffInHours(now()) >= 24) {
        return back()->with('error', 'Laporan sudah melewati batas waktu penghapusan (24 jam).');
    }

    // Hapus semua file bukti.
    //
    // Kolom `bukti` berisi JSON array jalur foto, bukan satu jalur. Menyusun
    // path langsung dari nilainya menghasilkan
    // 'storage/["laporan\/a.jpg","laporan\/b.jpg"]' yang tidak pernah cocok
    // dengan berkas mana pun, sehingga foto warga tertinggal di server meski
    // laporannya sudah dihapus. bukti_array sudah menangani laporan lama yang
    // hanya menyimpan satu jalur sebagai string biasa.
    foreach ($laporan->bukti_array as $jalur) {
        Storage::disk('public')->delete($jalur);
    }

    // Hapus notifikasi terkait
    Notification::where('laporan_id', $laporan->id)->delete();

    // Hapus laporan
    $laporan->delete();

    return redirect()
        ->route('user.laporan.index')
        ->with('success', 'Laporan berhasil dihapus.');
}


}
