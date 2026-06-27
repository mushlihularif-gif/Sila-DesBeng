<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Hash;
class LurahController extends Controller
{
    /**
     * Dashboard Lurah - Monitoring seluruh RW dan RT
     */
     public function dashboard(Request $request)
{
    // 1. Statistik Global
    $stats = [
        'total_laporan' => Laporan::count(),
        'pending' => Laporan::where('status', 'Pending')->count(),
        'proses' => Laporan::where('status', 'Proses')->count(),
        'selesai' => Laporan::where('status', 'Selesai')->count(),
        'ditolak' => Laporan::where('status', 'Ditolak')->count(),
    ];

    // 2. Laporan per RW
    $laporanPerRw = Laporan::select('rw', DB::raw('count(*) as total'))
        ->whereNotNull('rw')
        ->groupBy('rw')
        ->orderBy('rw')
        ->get();

    // 3. Laporan per Kategori
    $laporanPerKategori = Laporan::select('kategori', DB::raw('count(*) as total'))
        ->whereNotNull('kategori')
        ->groupBy('kategori')
        ->orderBy('total', 'desc')
        ->get();

    // 4. Query Laporan dengan Filter
    $query = Laporan::with(['user'])
        ->orderBy('created_at', 'desc');

    // Menerapkan Filter Region Hierarchy
    $user = auth()->user();
    if ($user->role !== 'super_admin') {
        $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
        $allowedRegionIds[] = $user->region_id;
        $query->whereIn('region_id', $allowedRegionIds);
    }

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

    // Filter Kategori ✅ TAMBAHKAN INI
    if ($request->filled('kategori')) {
        $query->where('kategori', $request->kategori);
    }

    // Search
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('deskripsi', 'like', "%{$search}%")
              ->orWhere('lokasi', 'like', "%{$search}%");
        });
    }

    // Paginate
    $laporans = $query->paginate(15);

    // 5. List RW untuk filter
    $rwList = Laporan::select('rw')
        ->whereNotNull('rw')
        ->distinct()
        ->orderBy('rw')
        ->get();

    // ✅ 6. TAMBAHKAN INI - List Kategori untuk filter
    $kategoriList = Laporan::select('kategori')
        ->whereNotNull('kategori')
        ->distinct()
        ->orderBy('kategori')
        ->get();

    // ✅ PASTIKAN $kategoriList masuk ke compact()
    return view('lurah.dashboard', compact(
        'stats',
        'laporanPerRw',
        'laporanPerKategori',
        'rwList',
        'laporans',
        'kategoriList' // ✅ TAMBAHKAN INI
    ));
    }

    /**
     * Halaman Index Laporan - Kelola Semua Laporan
     */
    public function indexLaporan(Request $request)
    {
        // Statistik Global
        $stats = [
            'total_laporan' => Laporan::count(),
            'pending' => Laporan::where('status', 'Pending')->count(),
            'proses' => Laporan::where('status', 'Proses')->count(),
            'selesai' => Laporan::where('status', 'Selesai')->count(),

            'ditolak' => Laporan::where('status', 'Ditolak')->count(),
        ];

        // Query Laporan dengan Filter
        $query = Laporan::with(['user'])
            ->orderBy('created_at', 'desc');

        // Menerapkan Filter Region Hierarchy
        $user = auth()->user();
        if ($user->role !== 'super_admin') {
            $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
            $allowedRegionIds[] = $user->region_id;
            $query->whereIn('region_id', $allowedRegionIds);
        }

        // Filter Region
        $filter_kecamatan_id = $request->get('filter_kecamatan_id');
        $filter_desa_id = $request->get('filter_desa_id');

        if ($filter_desa_id) {
            $query->where('region_id', $filter_desa_id);
        } elseif ($filter_kecamatan_id) {
            $allowed = \App\Models\Region::getDescendantIds($filter_kecamatan_id);
            $allowed[] = $filter_kecamatan_id;
            $query->whereIn('region_id', $allowed);
        }

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
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        $laporans = $query->paginate(20);

        // Data untuk filter
        $rwList = Laporan::select('rw')
            ->whereNotNull('rw')
            ->distinct()
            ->orderBy('rw')
            ->get();

        $kategoriList = Laporan::select('kategori')
            ->whereNotNull('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->get();

        $kecamatanOptions = collect();
        $desaOptions = collect();

        if (in_array($user->role, ['super_admin', 'admin'])) {
            $kecamatanOptions = \App\Models\Region::where('type', 'kecamatan')->orderBy('name')->get();
            if ($filter_kecamatan_id) {
                $desaOptions = \App\Models\Region::where('type', 'desa')->where('parent_id', $filter_kecamatan_id)->orderBy('name')->get();
            }
        } elseif ($user->role === 'admin_kecamatan') {
            $desaOptions = \App\Models\Region::where('type', 'desa')->where('parent_id', $user->region_id)->orderBy('name')->get();
        }

        // ✅ FIX: View harus ke lurah/laporan/index, bukan admin/laporan/index
        if ($request->ajax()) {
            return view('lurah.laporan.partials.laporan_content', compact('laporans', 'rwList', 'kategoriList', 'stats', 'kecamatanOptions', 'desaOptions', 'filter_kecamatan_id', 'filter_desa_id'))->render();
        }

        return view('lurah.laporan.index', compact('laporans', 'rwList', 'kategoriList', 'stats', 'kecamatanOptions', 'desaOptions', 'filter_kecamatan_id', 'filter_desa_id'));
    }

    /**
     * Detail Laporan
     */
    public function showLaporan($id)
    {
        $laporan = Laporan::with(['user'])->findOrFail($id);
        
        // ✅ FIX: View harus ke lurah/laporan/show, bukan admin/laporan/show
        return view('lurah.laporan.show', compact('laporan'));
    }

    /**
     * Update Status Laporan oleh Lurah
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Proses,Selesai,Ditolak',
            'catatan_admin' => 'nullable|string|max:500'
        ]);

        $laporan = Laporan::findOrFail($id);
        
        $oldStatus = $laporan->status;
        $laporan->status = $request->status;
        
        if ($request->filled('catatan_admin')) {
            $laporan->catatan_admin = $request->catatan_admin;
        }
        
        $laporan->admin_id = auth()->id();
        $laporan->save();

        // Kirim notifikasi jika status berubah (opsional)
        if ($oldStatus !== $request->status) {
            // Bisa ditambahkan sistem notifikasi di sini
        }

        return redirect()->back()->with('success', 'Status laporan berhasil diperbarui oleh Lurah!');
    }

    /**
     * Kelola User (RW dan RT)
     */
    public function indexUsers(Request $request)
    {
        $query = User::whereIn('role', ['admin', 'user'])
            ->orderBy('role')
            ->orderBy('rw')
            ->orderBy('rt');

        // Filter Role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter RW
        if ($request->filled('rw')) {
            $query->where('rw', $request->rw);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        // ✅ FIX: View harus ke lurah/users/index, bukan admin/users/index
        return view('lurah.users.index', compact('users'));
    }

    /**
     * Export Laporan ke CSV
     */
    public function export(Request $request)
    {
        $filename = 'laporan_kelurahan_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $query = Laporan::with('user');

        // Menerapkan Filter Region Hierarchy
        $user = auth()->user();
        if ($user->role !== 'super_admin') {
            $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
            $allowedRegionIds[] = $user->region_id;
            $query->whereIn('region_id', $allowedRegionIds);
        }

        // Apply filters dari request
        if ($request->filled('rw')) {
            $query->where('rw', $request->rw);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $laporans = $query->orderBy('created_at', 'desc')->get();

        $callback = function() use ($laporans) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM untuk Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header CSV
            fputcsv($file, [
                'ID',
                'Judul Laporan',
                'Kategori',
                'RT',
                'RW',
                'Lokasi',
                'Status',
                'Pelapor',
                'Email Pelapor',
                'Tanggal Dibuat',
                'Tanggal Update',
                'Catatan Admin'
            ]);

            // Data
            foreach ($laporans as $laporan) {
                fputcsv($file, [
                    $laporan->id,
                    $laporan->nama,
                    $laporan->kategori ?? '-',
                    $laporan->rt ?? '-',
                    $laporan->rw ?? '-',
                    $laporan->lokasi ?? '-',
                    $laporan->status,
                    $laporan->user->name ?? '-',
                    $laporan->user->email ?? '-',
                    $laporan->created_at->format('Y-m-d H:i:s'),
                    $laporan->updated_at->format('Y-m-d H:i:s'),
                    $laporan->catatan_admin ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
 * Export Laporan ke PDF
 */
public function exportPdf(Request $request)
{
    $query = Laporan::with('user');

    // Menerapkan Filter Region Hierarchy
    $user = auth()->user();
    if ($user->role !== 'super_admin') {
        $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
        $allowedRegionIds[] = $user->region_id;
        $query->whereIn('region_id', $allowedRegionIds);
    }

    // Apply filters
    if ($request->filled('rw')) {
        $query->where('rw', $request->rw);
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('kategori')) {
        $query->where('kategori', $request->kategori);
    }

    $laporans = $query->orderBy('created_at', 'desc')->get();

    // Data untuk PDF
    $data = [
        'title' => 'Laporan Kelurahan Sungai Pakning',
        'date' => now()->format('d F Y'),
        'laporans' => $laporans,
        'stats' => [
            'total' => $laporans->count(),
            'pending' => $laporans->where('status', 'Pending')->count(),
            'proses' => $laporans->where('status', 'Proses')->count(),
            'selesai' => $laporans->where('status', 'Selesai')->count(),
            'ditolak' => $laporans->where('status', 'Ditolak')->count(),
        ],
        'filters' => [
            'rw' => $request->rw,
            'status' => $request->status,
            'kategori' => $request->kategori,
        ],
    ];

    $pdf = Pdf::loadView('exports.lurah-dashboard-pdf', $data)
    ->setPaper('a4', 'landscape');


    $filename = 'Laporan_Kelurahan_' . now()->format('Y-m-d_His') . '.pdf';

    return $pdf->stream($filename);
}

/**
 * Export Detail Laporan ke PDF
 */
public function exportDetailPdf($id)
{
    $laporan = Laporan::with(['user'])->findOrFail($id);

    $data = [
        'title' => 'Detail Laporan #' . $laporan->id,
        'date' => now()->format('d F Y H:i'),
        'laporan' => $laporan,
    ];

    $pdf = Pdf::loadView('exports.laporan-lurah-pdf', $data)
    ->setPaper('a4', 'portrait');


    $filename = 'Laporan_' . $laporan->id . '_' . now()->format('Y-m-d') . '.pdf';

    return $pdf->stream($filename);
}

    /**
     * Statistik (halaman terpisah)
     */
    public function statistik()
    {
        // 1. Statistik Global
        $stats = [
            'total_laporan' => Laporan::count(),
            'pending' => Laporan::where('status', 'Pending')->count(),
            'proses' => Laporan::where('status', 'Proses')->count(),
            'selesai' => Laporan::where('status', 'Selesai')->count(),
            'ditolak' => Laporan::where('status', 'Ditolak')->count(),
        ];

        // 2. Statistik per bulan (12 bulan terakhir)
        $statistikBulanan = Laporan::select(
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('YEAR(created_at) as tahun'),
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN status = "Selesai" THEN 1 ELSE 0 END) as selesai'),
                DB::raw('SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN status = "Proses" THEN 1 ELSE 0 END) as proses')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        // 3. Statistik per RW
        $statistikRW = Laporan::select('rw', 
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN status = "Selesai" THEN 1 ELSE 0 END) as selesai'),
                DB::raw('SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN status = "Proses" THEN 1 ELSE 0 END) as proses'),
                DB::raw('SUM(CASE WHEN status = "Ditolak" THEN 1 ELSE 0 END) as ditolak')
            )
            ->whereNotNull('rw')
            ->groupBy('rw')
            ->orderBy('rw')
            ->get();

        // 4. Statistik per Kategori
        $statistikKategori = Laporan::select('kategori',
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN status = "Selesai" THEN 1 ELSE 0 END) as selesai'),
                DB::raw('SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending')
            )
            ->whereNotNull('kategori')
            ->groupBy('kategori')
            ->orderBy('total', 'desc')
            ->get();

        // 5. Rata-rata waktu penyelesaian per RW
        $waktuPenyelesaian = Laporan::select('rw',
                DB::raw('AVG(DATEDIFF(updated_at, created_at)) as rata_rata_hari'),
                DB::raw('COUNT(*) as total_selesai')
            )
            ->where('status', 'Selesai')
            ->whereNotNull('rw')
            ->groupBy('rw')
            ->orderBy('rata_rata_hari', 'asc')
            ->get();

        return view('lurah.statistik', compact(
            'stats',
            'statistikBulanan', 
            'statistikRW',
            'statistikKategori',
            'waktuPenyelesaian'
        ));
    }
    public function updateProfile(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . auth()->id(),
    ]);

    $user = auth()->user();
    $user->name = $request->name;
    $user->email = $request->email;
    $user->save();

    return redirect()->route('lurah.settings')
        ->with('success', 'Profil berhasil diperbarui!');
}

/**
 * Update Password Lurah
 */
public function updatePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required',
        'new_password' => 'required|min:8|confirmed',
    ]);

    $user = auth()->user();

    // Cek password lama
    if (!Hash::check($request->current_password, $user->password)) {
        return back()->withErrors(['current_password' => 'Password lama tidak sesuai']);
    }

    // Update password baru
    $user->password = Hash::make($request->new_password);
    $user->save();

    return redirect()->route('lurah.settings')
        ->with('success', 'Password berhasil diubah!');
}

    /**
     * Settings (halaman pengaturan)
     */
    public function settings()
    {
        $user = auth()->user();
        return view('lurah.settings', compact('user'));
    }
}