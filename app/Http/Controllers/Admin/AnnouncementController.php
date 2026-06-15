<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Laporan;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $query = Announcement::with(['admin', 'laporan', 'region'])->orderBy('created_at', 'desc');
        
        if ($user->role !== 'super_admin') {
            // Only see announcements from their own region and its descendants
            $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
            $allowedRegionIds[] = $user->region_id;
            $query->whereIn('region_id', $allowedRegionIds);
        }

        $announcements = $query->paginate(10);
        return view('admin.announcements.index', compact('announcements'));
    }

    public function create(Request $request)
    {
        $laporan = null;
        if ($request->has('laporan_id')) {
            $laporan = Laporan::find($request->laporan_id);
        }
        
        $user = auth()->user();
        $regions = collect();
        if ($user->role !== 'super_admin' && $user->region_id) {
            $regionIds = \App\Models\Region::getDescendantIds($user->region_id);
            array_unshift($regionIds, $user->region_id); // Termasuk wilayah admin itu sendiri
            $regions = \App\Models\Region::whereIn('id', $regionIds)->get();
        } elseif ($user->role === 'super_admin') {
            $regions = \App\Models\Region::all();
        }
        
        return view('admin.announcements.form', compact('laporan', 'regions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:Pengumuman,Event,Gotong Royong',
            'description' => 'required|string',
            'event_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'target_region_id' => 'required|exists:regions,id',
        ]);

        $user = auth()->user();
        if ($user->role !== 'super_admin' && $user->region_id) {
            $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
            $allowedRegionIds[] = $user->region_id;
            if (!in_array($request->target_region_id, $allowedRegionIds)) {
                abort(403, 'Anda tidak berhak membuat pengumuman untuk wilayah tersebut.');
            }
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('announcements', 'public');
        }

        Announcement::create([
            'region_id' => $request->target_region_id,
            'admin_id' => auth()->user()->id,
            'laporan_id' => $request->laporan_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'event_date' => $request->event_date,
            'location' => $request->location,
            'image_path' => $imagePath,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman/Event berhasil dibuat.');
    }

    public function edit($id)
    {
        $announcement = Announcement::findOrFail($id);
        
        // Cek otorisasi
        $user = auth()->user();
        $regions = collect();
        
        if ($user->role !== 'super_admin' && $user->region_id) {
            $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
            $allowedRegionIds[] = $user->region_id;
            
            if (!in_array($announcement->region_id, $allowedRegionIds)) {
                abort(403, 'Anda tidak berhak mengedit pengumuman ini.');
            }
            
            $regions = \App\Models\Region::whereIn('id', $allowedRegionIds)->get();
        } elseif ($user->role === 'super_admin') {
            $regions = \App\Models\Region::all();
        }

        return view('admin.announcements.form', compact('announcement', 'regions'));
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);

        // Cek otorisasi untuk update data
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $user->region_id) {
            $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
            $allowedRegionIds[] = $user->region_id;
            if (!in_array($announcement->region_id, $allowedRegionIds)) {
                abort(403, 'Anda tidak berhak mengubah pengumuman ini.');
            }
            if (!in_array($request->target_region_id, $allowedRegionIds)) {
                abort(403, 'Anda tidak berhak memindahkan pengumuman ke wilayah tersebut.');
            }
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:Pengumuman,Event,Gotong Royong',
            'description' => 'required|string',
            'event_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'target_region_id' => 'required|exists:regions,id',
        ]);

        $data = $request->only(['title', 'description', 'type', 'event_date', 'location']);
        $data['is_active'] = $request->has('is_active');
        $data['region_id'] = $request->target_region_id;

        if ($request->hasFile('image')) {
            if ($announcement->image_path && Storage::disk('public')->exists($announcement->image_path)) {
                Storage::disk('public')->delete($announcement->image_path);
            }
            $data['image_path'] = $request->file('image')->store('announcements', 'public');
        }

        $announcement->update($data);

        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman/Event berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        
        // Cek otorisasi untuk menghapus
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $user->region_id) {
            $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
            $allowedRegionIds[] = $user->region_id;
            if (!in_array($announcement->region_id, $allowedRegionIds)) {
                abort(403, 'Anda tidak berhak menghapus pengumuman ini.');
            }
        }
        
        if ($announcement->image_path && Storage::disk('public')->exists($announcement->image_path)) {
            Storage::disk('public')->delete($announcement->image_path);
        }
        
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman/Event berhasil dihapus.');
    }
}
