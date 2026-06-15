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
        $query = Announcement::with(['region', 'admin'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc');

        // If user is logged in, prioritize their region
        $user = auth()->user();
        if ($user && $user->region_id) {
            // Get ancestors (Kecamatan -> Desa -> RW -> RT)
            $ancestorIds = Region::getAncestorIds($user->region_id);
            $relevantRegionIds = array_merge([$user->region_id], $ancestorIds);
            
            // First show announcements from their region hierarchy, then others
            $query->orderByRaw("FIELD(region_id, " . implode(',', $relevantRegionIds) . ") DESC");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $announcements = $query->paginate(12);

        return view('users.announcements.index', compact('announcements'));
    }

    public function show($id)
    {
        $announcement = Announcement::with(['region', 'admin', 'laporan', 'laporan.user'])
            ->where('is_active', true)
            ->findOrFail($id);

        // Fetch related announcements from same region
        $relatedAnnouncements = Announcement::where('is_active', true)
            ->where('id', '!=', $id)
            ->where('region_id', $announcement->region_id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return view('users.announcements.show', compact('announcement', 'relatedAnnouncements'));
    }
}
