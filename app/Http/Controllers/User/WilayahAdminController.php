<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Laporan;

class WilayahAdminController extends Controller
{
    public function indexLaporan(Request $request)
    {
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
            'image' => 'nullable|image|max:2048'
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
}
