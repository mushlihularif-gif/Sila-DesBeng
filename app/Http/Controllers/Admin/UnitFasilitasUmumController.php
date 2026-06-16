<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FasilitasUmum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UnitFasilitasUmumController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $fasilitas = FasilitasUmum::query()
            ->when($search, function ($query, $search) {
                return $query->where('nama_fasilitas', 'LIKE', "%{$search}%")
                           ->orWhere('kategori', 'LIKE', "%{$search}%");
            })
            ->paginate(6)
            ->appends(['search' => $search]);
        
        return view('admin.unit.fasilitas_umum.index', compact('fasilitas', 'search'));
    }

    public function create()
    {
        return view('admin.unit.fasilitas_umum.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_fasilitas' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'stok' => 'required|integer',
            'status' => 'required|in:Tersedia,Tidak Tersedia,Disewa',
            'kategori' => 'required|string',
            'lokasi' => 'required|string',
            'foto_utama' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
        ]);

        $data = [
            'nama_fasilitas' => $request->nama_fasilitas,
            'deskripsi' => $request->deskripsi,
            'stok' => $request->stok,
            'status' => $request->status,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
        ];

        if ($request->hasFile('foto_utama')) { 
            $data['foto'] = $request->file('foto_utama')->store('fasilitas_umum', 'public'); 
        }
        if ($request->hasFile('foto_2')) {
            $data['foto_2'] = $request->file('foto_2')->store('fasilitas_umum', 'public');
        }
        if ($request->hasFile('foto_3')) {
            $data['foto_3'] = $request->file('foto_3')->store('fasilitas_umum', 'public');
        }

        FasilitasUmum::create($data);

        return redirect()->route('admin.unit.fasilitas_umum.index')->with('success', 'Fasilitas Umum berhasil ditambahkan.');
    }

    public function show($id)
    {
        $fasilitas = FasilitasUmum::findOrFail($id);
        return view('admin.unit.fasilitas_umum.show', compact('fasilitas'));
    }

    public function edit($id)
    {
        $fasilitas = FasilitasUmum::findOrFail($id);
        return view('admin.unit.fasilitas_umum.edit', compact('fasilitas'));
    }

    public function destroy($id)
    {
        $fasilitas = FasilitasUmum::findOrFail($id);

        if ($fasilitas->foto) Storage::disk('public')->delete($fasilitas->foto);
        if ($fasilitas->foto_2) Storage::disk('public')->delete($fasilitas->foto_2);
        if ($fasilitas->foto_3) Storage::disk('public')->delete($fasilitas->foto_3);

        $fasilitas->delete();

        return redirect()->route('admin.unit.fasilitas_umum.index')->with('success', 'Fasilitas Umum berhasil dihapus.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_fasilitas' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'stok' => 'required|integer',
            'status' => 'required|in:Tersedia,Tidak Tersedia,Disewa',
            'kategori' => 'required|string',
            'lokasi' => 'required|string',
            'foto_utama' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $fasilitas = FasilitasUmum::findOrFail($id);

        $data = [
            'nama_fasilitas' => $request->nama_fasilitas,
            'deskripsi' => $request->deskripsi,
            'stok' => $request->stok,
            'status' => $request->status,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
        ];

        if ($request->hasFile('foto_utama')) {
            if ($fasilitas->foto) Storage::disk('public')->delete($fasilitas->foto);
            $data['foto'] = $request->file('foto_utama')->store('fasilitas_umum', 'public');
        } elseif ($request->input('delete_foto') == '1') {
            if ($fasilitas->foto) Storage::disk('public')->delete($fasilitas->foto);
            $data['foto'] = null;
        }

        if ($request->hasFile('foto_2')) {
            if ($fasilitas->foto_2) Storage::disk('public')->delete($fasilitas->foto_2);
            $data['foto_2'] = $request->file('foto_2')->store('fasilitas_umum', 'public');
        } elseif ($request->input('delete_foto_2') == '1') {
            if ($fasilitas->foto_2) Storage::disk('public')->delete($fasilitas->foto_2);
            $data['foto_2'] = null;
        }

        if ($request->hasFile('foto_3')) {
            if ($fasilitas->foto_3) Storage::disk('public')->delete($fasilitas->foto_3);
            $data['foto_3'] = $request->file('foto_3')->store('fasilitas_umum', 'public');
        } elseif ($request->input('delete_foto_3') == '1') {
            if ($fasilitas->foto_3) Storage::disk('public')->delete($fasilitas->foto_3);
            $data['foto_3'] = null;
        }

        $fasilitas->update($data);

        return redirect()->route('admin.unit.fasilitas_umum.index')->with('success', 'Fasilitas Umum berhasil diperbarui.');
    }
}
