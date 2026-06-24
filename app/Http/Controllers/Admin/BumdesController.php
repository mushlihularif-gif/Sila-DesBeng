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
        
        $members = BumdesMember::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'LIKE', "%{$search}%")
                           ->orWhere('position', 'LIKE', "%{$search}%");
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

        $member = new BumdesMember();
        $member->name = $request->name;
        $member->position = $request->position;
        $member->order = BumdesMember::max('order') + 1;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('bumdes', 'public');
            $member->photo = $path;
        }

        $member->save();

        return redirect()->route('admin.siladesbeng.bumdes.index')->with('success', 'Anggota BUMDes berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $member = BumdesMember::findOrFail($id);
        return view('admin.isewa.bumdes.edit', compact('member'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:8192',
        ]);

        $member = BumdesMember::findOrFail($id);
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

        return redirect()->route('admin.siladesbeng.bumdes.index')->with('success', 'Anggota BUMDes berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $member = BumdesMember::findOrFail($id);
        if ($member->photo) {
            Storage::disk('public')->delete($member->photo);
        }
        $member->delete();

        return redirect()->route('admin.siladesbeng.bumdes.index')->with('success', 'Anggota BUMDes berhasil dihapus.');
    }
}