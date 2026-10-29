<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

    public function indexPengumuman(Request $request)
    {
        $user = auth()->user();
        
        $allowedRegionIds = $this->wilayahDiurus($user);
        
        $query = \App\Models\Announcement::with(['admin', 'region'])->where('post_category', 'Pengumuman')->where(function ($q) use ($allowedRegionIds, $user) {
            $q->whereIn('region_id', $allowedRegionIds)->orWhere('admin_id', $user->id);
        })->orderBy('created_at', 'desc');
        
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
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
        
        $query = \App\Models\Announcement::with(['admin', 'region'])->where('post_category', 'Berita')->where(function ($q) use ($allowedRegionIds, $user) {
            $q->whereIn('region_id', $allowedRegionIds)->orWhere('admin_id', $user->id);
        })->orderBy('created_at', 'desc');
        
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
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

    private function kontenMilikSendiri(\App\Models\Announcement $announcement): void
    {
        abort_unless((int) $announcement->admin_id === (int) auth()->id(), 403);
        abort_unless(in_array($announcement->post_category, ['Berita', 'Pengumuman']), 404);
    }

    public function editKonten(\App\Models\Announcement $announcement)
    {
        $this->kontenMilikSendiri($announcement);
        $regionIds = auth()->user()->region_id
            ? array_merge([auth()->user()->region_id], \App\Models\Region::getAncestorIds(auth()->user()->region_id))
            : [];
        $regions = \App\Models\Region::whereIn('id', $regionIds)->get();

        return view('user.wilayah.konten-edit', compact('announcement', 'regions'));
    }

    public function updateKonten(Request $request, \App\Models\Announcement $announcement)
    {
        $this->kontenMilikSendiri($announcement);
        $regionIds = auth()->user()->region_id
            ? array_merge([auth()->user()->region_id], \App\Models\Region::getAncestorIds(auth()->user()->region_id))
            : [];
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string|max:50',
            'target_region_id' => ['required', \Illuminate\Validation\Rule::in($regionIds)],
            'event_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);
        $data['region_id'] = $data['target_region_id'];
        unset($data['target_region_id'], $data['image']);
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('announcements', 'public');
        }
        $announcement->update($data);

        return redirect()->route($announcement->post_category === 'Berita' ? 'wilayah.berita.index' : 'wilayah.pengumuman.index')
            ->with('success', 'Konten berhasil diperbarui.');
    }

    public function destroyKonten(\App\Models\Announcement $announcement)
    {
        $this->kontenMilikSendiri($announcement);
        $route = $announcement->post_category === 'Berita' ? 'wilayah.berita.index' : 'wilayah.pengumuman.index';
        $announcement->delete();

        return redirect()->route($route)->with('success', 'Konten berhasil dihapus.');
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

}
