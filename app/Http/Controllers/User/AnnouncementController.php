<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Region;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        // 1. Berita Daerah (Global / Se-Kabupaten)
        $beritasQuery = Announcement::with(['region', 'admin', 'images'])
            ->where('is_active', true)
            ->where('post_category', 'Berita')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $beritasQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $beritas = $beritasQuery->paginate(12, ['*'], 'page_b');

        // 2. Pengumuman Warga (Sesuai domisili)
        $user = auth()->user();
        $pengumumanQuery = Announcement::with(['region', 'admin'])
            ->where('is_active', true)
            ->where('post_category', 'Pengumuman')
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan wilayah pengguna
        if ($user && $user->region_id) {
            $ancestorIds = Region::getAncestorIds($user->region_id);
            $relevantRegionIds = array_merge([$user->region_id], $ancestorIds);
            
            $pengumumanQuery->where(function($q) use ($relevantRegionIds) {
                $q->whereIn('target_audience_id', $relevantRegionIds)
                  ->orWhere('target_audience_type', 'all')
                  ->orWhereNull('target_audience_id'); // Untuk data lama yang tidak punya target khusus
            });
        }

        if ($request->filled('type')) {
            $pengumumanQuery->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $pengumumanQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $pengumumans = $pengumumanQuery->paginate(12, ['*'], 'page_p');

        $activeTab = $request->get('tab', 'berita'); // Default tab

        return view('users.announcements.index', compact('beritas', 'pengumumans', 'activeTab'));
    }

    public function show($id)
    {
        $announcement = Announcement::with(['region', 'admin', 'laporan', 'laporan.user', 'images'])
            ->where('is_active', true)
            ->findOrFail($id);

        // Fetch related announcements from same category
        $relatedAnnouncements = Announcement::where('is_active', true)
            ->where('id', '!=', $id)
            ->where('post_category', $announcement->post_category)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return view('users.announcements.show', compact('announcement', 'relatedAnnouncements'));
    }
}
