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
            ->orderBy('order')
            ->get();
            
        $whatsapp = env('BUMDES_WHATSAPP', '+6283846078693');
        return view('admin.isewa.profile-bumdes', compact('members', 'whatsapp', 'search'));
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
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:8192',
        ]);

        $user = auth()->user();
        $member = new BumdesMember();
        $member->name = $request->name;
        $member->position = $request->position;
        $member->region_id = in_array($user->role, ['super_admin', 'admin']) ? null : $user->region_id;
        $member->order = BumdesMember::where('region_id', $member->region_id)->max('order') + 1;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('bumdes', 'public');
            $member->photo = $path;
        }

        $member->save();

        return redirect()->route('admin.siladesbeng.bumdes.index')->with('success', 'Pengurus berhasil ditambahkan.');
    }

    public function edit($id)
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
        
        $member = $query->firstOrFail();
        
        return view('admin.isewa.bumdes.edit', compact('member'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:8192',
        ]);

        $user = auth()->user();
        $query = BumdesMember::where('id', $id);
        
        if (!in_array($user->role, ['super_admin', 'admin'])) {
            $query->where('region_id', $user->region_id);
        } else {
            $query->where(function($q) {
                $q->whereNull('region_id')->orWhere('region_id', 0);
            });
        }
        
        $member = $query->firstOrFail();
        
        $member->name = $request->name;
        $member->position = $request->position;

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

        return redirect()->route('admin.siladesbeng.bumdes.index')->with('success', 'Pengurus berhasil diperbarui.');
    }

    public function destroy($id)
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
        
        $member = $query->firstOrFail();
        
        if ($member->photo) {
            Storage::disk('public')->delete($member->photo);
        }
        $member->delete();

        return redirect()->route('admin.siladesbeng.bumdes.index')->with('success', 'Pengurus berhasil dihapus.');
    }
}