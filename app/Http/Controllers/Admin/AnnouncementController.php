<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\AnnouncementImage;
use App\Models\Laporan;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageCompressorService;
use App\Models\Region;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $tab = $request->get('tab', 'berita');
        
        // --- Query Berita Daerah ---
        $beritasQuery = Announcement::with(['admin', 'laporan', 'region'])
            ->where('post_category', 'Berita')
            ->orderBy('created_at', 'desc');
            
        if ($user->role !== 'super_admin') {
            $allowedRegionIds = Region::getDescendantIds($user->region_id);
            $allowedRegionIds[] = $user->region_id;
            $beritasQuery->whereIn('region_id', $allowedRegionIds);
        }
        
        if ($tab === 'berita') {
            if ($request->filled('search')) {
                $search = $request->search;
                $beritasQuery->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                });
            }
        }
        $beritas = $beritasQuery->paginate(10, ['*'], 'page_b')->withQueryString();

        // --- Query Pengumuman Warga ---
        $pengumumansQuery = Announcement::with(['admin', 'laporan', 'region'])
            ->where('post_category', 'Pengumuman')
            ->orderBy('created_at', 'desc');
            
        if ($user->role !== 'super_admin') {
            $allowedRegionIds = Region::getDescendantIds($user->region_id);
            $allowedRegionIds[] = $user->region_id;
            $pengumumansQuery->whereIn('region_id', $allowedRegionIds);
        }

        $filter_type = $request->get('type');
        $filter_kecamatan_id = $request->get('filter_kecamatan_id');
        $filter_desa_id = $request->get('filter_desa_id');

        if ($tab === 'pengumuman') {
            if ($filter_type) {
                $pengumumansQuery->where('type', $filter_type);
            }
            if ($filter_desa_id) {
                $pengumumansQuery->where('target_audience_id', $filter_desa_id);
            } elseif ($filter_kecamatan_id) {
                $allowed = Region::getDescendantIds($filter_kecamatan_id);
                $allowed[] = $filter_kecamatan_id;
                $pengumumansQuery->whereIn('target_audience_id', $allowed);
            }
        }
        $pengumumans = $pengumumansQuery->paginate(10, ['*'], 'page_p')->withQueryString();

        // Data filter selects
        $kecamatanOptions = collect();
        $desaOptions = collect();

        if (in_array($user->role, ['super_admin', 'admin'])) {
            $kecamatanOptions = Region::where('type', 'kecamatan')->orderBy('name')->get();
            if ($filter_kecamatan_id) {
                $desaOptions = Region::where('type', 'desa')->where('parent_id', $filter_kecamatan_id)->orderBy('name')->get();
            }
        } elseif ($user->role === 'admin_kecamatan') {
            $desaOptions = Region::where('type', 'desa')->where('parent_id', $user->region_id)->orderBy('name')->get();
        }

        return view('admin.announcements.index', compact('beritas', 'pengumumans', 'tab', 'kecamatanOptions', 'desaOptions', 'filter_kecamatan_id', 'filter_desa_id'));
    }

    public function create(Request $request)
    {
        $category = $request->get('category', 'Pengumuman');
        
        $laporan = null;
        if ($request->has('laporan_id')) {
            $laporan = Laporan::find($request->laporan_id);
        }
        
        $user = auth()->user();
        $regions = Region::whereNotIn('type', ['rw', 'rt'])->get();
        
        $userRole = $user->role;
        
        $regions->transform(function ($region) {
            $region->display_name = ucwords($region->type) . ' ' . $region->name;
            return $region;
        });
        
        return view('admin.announcements.form', compact('laporan', 'regions', 'userRole', 'category'));
    }

    public function store(Request $request)
    {
        $category = $request->post_category ?? 'Pengumuman';
        
        $rules = [
            'title' => 'required|string|max:255',
            'type' => 'required|in:Pengumuman,Event,Gotong Royong',
            'description' => 'required|string',
            'event_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'post_category' => 'required|in:Berita,Pengumuman',
        ];

        if ($category === 'Berita') {
            $rules['images.*'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120';
        } else {
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120';
            $rules['target_region_id'] = 'required|exists:regions,id';
        }

        $request->validate($rules);

        $user = auth()->user();
        $publisherRegionId = $user->role === 'super_admin' ? ($request->target_region_id ?? null) : $user->region_id;
        
        $targetAudienceId = null;
        $targetAudienceType = null;

        if ($category === 'Pengumuman') {
            $targetAudienceId = $request->target_region_id;
            if ($targetAudienceId) {
                $targetRegion = Region::find($targetAudienceId);
                $targetAudienceType = $targetRegion ? $targetRegion->type : null;
            }
        } else {
            $targetAudienceType = 'all';
        }

        $imagePath = null;
        if ($category === 'Pengumuman' && $request->hasFile('image')) {
            $imagePath = ImageCompressorService::compressAndStore($request->file('image'), 'announcements');
        }

        $announcement = Announcement::create([
            'region_id' => $publisherRegionId,
            'admin_id' => $user->id,
            'laporan_id' => $request->laporan_id,
            'post_category' => $category,
            'target_audience_type' => $targetAudienceType,
            'target_audience_id' => $targetAudienceId,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'event_date' => $request->event_date,
            'location' => $request->location,
            'image_path' => $imagePath,
            'is_active' => $request->has('is_active'),
        ]);

        if ($category === 'Berita' && $request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = ImageCompressorService::compressAndStore($file, 'announcements');
                AnnouncementImage::create([
                    'announcement_id' => $announcement->id,
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('admin.announcements.index', ['tab' => strtolower($category)])->with('success', $category . ' berhasil dipublikasikan.');
    }

    public function edit(Request $request, $id)
    {
        $announcement = Announcement::with('images')->findOrFail($id);
        $category = $announcement->post_category;
        
        $user = auth()->user();
        
        if ($user->role !== 'super_admin' && $user->region_id) {
            $allowedRegionIds = Region::getDescendantIds($user->region_id);
            $allowedRegionIds[] = $user->region_id;
            
            if (!in_array($announcement->region_id, $allowedRegionIds)) {
                abort(403, 'Anda tidak berhak mengedit publikasi ini.');
            }
        }
            
        $regions = Region::whereNotIn('type', ['rw', 'rt'])->get();
        $userRole = $user->role;

        $regions->transform(function ($region) {
            $region->display_name = ucwords($region->type) . ' ' . $region->name;
            return $region;
        });

        return view('admin.announcements.form', compact('announcement', 'regions', 'userRole', 'category'));
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);
        $category = $announcement->post_category;

        $user = auth()->user();
        if ($user->role !== 'super_admin' && $user->region_id) {
            $allowedRegionIds = Region::getDescendantIds($user->region_id);
            $allowedRegionIds[] = $user->region_id;
            if (!in_array($announcement->region_id, $allowedRegionIds)) {
                abort(403, 'Anda tidak berhak mengubah publikasi ini.');
            }
        }

        $rules = [
            'title' => 'required|string|max:255',
            'type' => 'required|in:Pengumuman,Event,Gotong Royong',
            'description' => 'required|string',
            'event_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
        ];

        if ($category === 'Berita') {
            $rules['images.*'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120';
        } else {
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120';
            $rules['target_region_id'] = 'required|exists:regions,id';
        }

        $request->validate($rules);

        $data = $request->only(['title', 'description', 'type', 'event_date', 'location']);
        $data['is_active'] = $request->has('is_active');
        
        if ($category === 'Pengumuman') {
            $data['target_audience_id'] = $request->target_region_id;
            if ($request->target_region_id) {
                $targetRegion = Region::find($request->target_region_id);
                $data['target_audience_type'] = $targetRegion ? $targetRegion->type : null;
            }
        }

        if ($category === 'Pengumuman' && $request->hasFile('image')) {
            if ($announcement->image_path && Storage::disk('public')->exists($announcement->image_path)) {
                Storage::disk('public')->delete($announcement->image_path);
            }
            $data['image_path'] = ImageCompressorService::compressAndStore($request->file('image'), 'announcements');
        }

        $announcement->update($data);

        if ($category === 'Berita' && $request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = ImageCompressorService::compressAndStore($file, 'announcements');
                AnnouncementImage::create([
                    'announcement_id' => $announcement->id,
                    'image_path' => $path,
                ]);
            }
        }
        
        if ($request->has('delete_images')) {
            $imagesToDelete = AnnouncementImage::whereIn('id', $request->delete_images)->where('announcement_id', $announcement->id)->get();
            foreach ($imagesToDelete as $img) {
                if (Storage::disk('public')->exists($img->image_path)) {
                    Storage::disk('public')->delete($img->image_path);
                }
                $img->delete();
            }
        }

        return redirect()->route('admin.announcements.index', ['tab' => strtolower($category)])->with('success', $category . ' berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $announcement = Announcement::with('images')->findOrFail($id);
        $category = $announcement->post_category;
        
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $user->region_id) {
            $allowedRegionIds = Region::getDescendantIds($user->region_id);
            $allowedRegionIds[] = $user->region_id;
            if (!in_array($announcement->region_id, $allowedRegionIds)) {
                abort(403, 'Anda tidak berhak menghapus publikasi ini.');
            }
        }
        
        if ($announcement->image_path && Storage::disk('public')->exists($announcement->image_path)) {
            Storage::disk('public')->delete($announcement->image_path);
        }
        
        foreach ($announcement->images as $img) {
            if (Storage::disk('public')->exists($img->image_path)) {
                Storage::disk('public')->delete($img->image_path);
            }
        }
        
        $announcement->delete();

        return redirect()->route('admin.announcements.index', ['tab' => strtolower($category)])->with('success', $category . ' berhasil dihapus.');
    }
}
