<?php

namespace App\Http\Controllers\Admin;

use App\Models\BumdesMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class BumdesController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $user = auth()->user();
        
        $members = BumdesMember::query()
            ->when($user->role !== 'super_admin' && $user->role !== 'admin', function($q) use ($user) {
                return $q->where('region_id', $user->region_id);
            })
            ->when(in_array($user->role, ['super_admin', 'admin']), function($q) {
                return $q->whereNull('region_id')->orWhere('region_id', 0);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('position', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('level', 'asc')
            ->orderBy('order', 'asc')
            ->get();
            
        $whatsapp = env('BUMDES_WHATSAPP', '+6283846078693');
        
        $region = $user->region;
        if (!$region && in_array($user->role, ['admin', 'super_admin'])) {
            $region = \App\Models\Region::where('type', 'kabupaten')->first();
        }
        $strukturLayout = $region->settings['struktur_layout'] ?? 'hierarki';

        return view('admin.isewa.profile-bumdes', compact('members', 'whatsapp', 'search', 'strukturLayout'));
    }

    public function updateLayout(Request $request)
    {
        $request->validate([
            'struktur_layout' => 'required|in:hierarki,sejajar',
        ]);

        $user = auth()->user();
        $region = $user->region;
        if (!$region && in_array($user->role, ['admin', 'super_admin'])) {
            $region = \App\Models\Region::where('type', 'kabupaten')->first();
        }

        if ($region) {
            $settings = $region->settings ?? [];
            $settings['struktur_layout'] = $request->struktur_layout;
            $region->settings = $settings;
            $region->save();
        }

        $namaMode = $request->struktur_layout === 'sejajar' ? 'Sejajar (Grid Mendatar)' : 'Bagan Berjenjang (Hierarki)';
        return back()->with('success', 'Model tampilan struktur berhasil diubah menjadi: ' . $namaMode);
    }

    public function create()
    {
        return view('admin.isewa.bumdes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'level' => 'required|integer|min:1|max:4',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:8192',
        ]);

        $user = auth()->user();
        $member = new BumdesMember();
        $member->name = $request->name;
        $member->position = $request->position;
        $member->level = (int)$request->level;
        $member->region_id = in_array($user->role, ['super_admin', 'admin']) ? null : $user->region_id;
        
        $maxOrder = BumdesMember::where('region_id', $member->region_id)
            ->where('level', $member->level)
            ->max('order');
        $member->order = ($maxOrder !== null) ? $maxOrder + 1 : 1;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('bumdes', 'public');
            $member->photo = $path;
        }

        $member->save();

        return redirect()->route('admin.SiladesBeng.bumdes.index')->with('success', 'Aparatur berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $member = $this->getScopedMember($id);
        return view('admin.isewa.bumdes.edit', compact('member'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'level' => 'required|integer|min:1|max:4',
            'order' => 'nullable|integer|min:1',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:8192',
        ]);

        $member = $this->getScopedMember($id);
        
        $member->name = $request->name;
        $member->position = $request->position;
        $member->level = (int)$request->level;
        if ($request->filled('order')) {
            $member->order = (int)$request->order;
        }

        if ($request->hasFile('photo')) {
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }
            $path = $request->file('photo')->store('bumdes', 'public');
            $member->photo = $path;
        } elseif ($request->input('delete_photo') == '1') {
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }
            $member->photo = null;
        }

        $member->save();

        return redirect()->route('admin.SiladesBeng.bumdes.index')->with('success', 'Data aparatur berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $member = $this->getScopedMember($id);
        
        if ($member->photo) {
            Storage::disk('public')->delete($member->photo);
        }
        $member->delete();

        return redirect()->route('admin.SiladesBeng.bumdes.index')->with('success', 'Data aparatur berhasil dihapus.');
    }

    public function moveUp($id)
    {
        $member = $this->getScopedMember($id);
        
        // Cari anggota di level yang sama dengan order lebih kecil terdekat
        $prev = BumdesMember::where('region_id', $member->region_id)
            ->where('level', $member->level)
            ->where('order', '<', $member->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($prev) {
            $tempOrder = $member->order;
            $member->order = $prev->order;
            $prev->order = $tempOrder;
            $member->save();
            $prev->save();
            return back()->with('success', 'Urutan berhasil dinaikkan.');
        }

        // Jika sudah di puncak levelnya tetapi level > 1, naikkan ke level di atasnya
        if ($member->level > 1) {
            $member->level -= 1;
            $maxOrder = BumdesMember::where('region_id', $member->region_id)
                ->where('level', $member->level)
                ->max('order');
            $member->order = ($maxOrder !== null) ? $maxOrder + 1 : 1;
            $member->save();
            return back()->with('success', 'Aparatur berhasil dinaikkan ke tingkat atasnya.');
        }

        return back()->with('info', 'Posisi aparatur sudah berada di urutan teratas.');
    }

    public function moveDown($id)
    {
        $member = $this->getScopedMember($id);
        
        // Cari anggota di level yang sama dengan order lebih besar terdekat
        $next = BumdesMember::where('region_id', $member->region_id)
            ->where('level', $member->level)
            ->where('order', '>', $member->order)
            ->orderBy('order', 'asc')
            ->first();

        if ($next) {
            $tempOrder = $member->order;
            $member->order = $next->order;
            $next->order = $tempOrder;
            $member->save();
            $next->save();
            return back()->with('success', 'Urutan berhasil diturunkan.');
        }

        // Jika sudah di paling bawah levelnya tetapi level < 4, turunkan ke level di bawahnya
        if ($member->level < 4) {
            $member->level += 1;
            $maxOrder = BumdesMember::where('region_id', $member->region_id)
                ->where('level', $member->level)
                ->max('order');
            $member->order = ($maxOrder !== null) ? $maxOrder + 1 : 1;
            $member->save();
            return back()->with('success', 'Aparatur berhasil dipindahkan ke tingkatan di bawahnya.');
        }

        return back()->with('info', 'Posisi aparatur sudah berada di urutan terbawah.');
    }

    public function changeLevel(Request $request, $id)
    {
        $request->validate([
            'level' => 'required|integer|min:1|max:4',
        ]);

        $member = $this->getScopedMember($id);
        $newLevel = (int)$request->level;

        if ($member->level !== $newLevel) {
            $member->level = $newLevel;
            $maxOrder = BumdesMember::where('region_id', $member->region_id)
                ->where('level', $member->level)
                ->max('order');
            $member->order = ($maxOrder !== null) ? $maxOrder + 1 : 1;
            $member->save();
        }

        return back()->with('success', 'Tingkatan hierarki aparatur berhasil diperbarui.');
    }

    private function getScopedMember($id)
    {
        $user = auth()->user();
        $query = BumdesMember::where('id', $id);
        
        if (!in_array($user->role, ['super_admin', 'admin'])) {
            $query->where('region_id', $user->region_id);
        } else {
            $query->where(function($q) {
                $q->whereNull('region_id')->orWhere('region_id', 0);
            });
        }
        
        return $query->firstOrFail();
    }
}
