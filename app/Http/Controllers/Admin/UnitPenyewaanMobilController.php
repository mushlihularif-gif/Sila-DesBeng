<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mobil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UnitPenyewaanMobilController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $mobils = Mobil::query()
            ->when($search, function ($query, $search) {
                return $query->where('nama_mobil', 'LIKE', "%{$search}%")
                           ->orWhere('kategori', 'LIKE', "%{$search}%");
            })
            ->paginate(6)
            ->appends(['search' => $search]);
        
        return view('admin.unit.mobil.index', compact('mobils', 'search'));
    }

    public function create()
    {
        return view('admin.unit.mobil.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mobil' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_sewa' => 'required|string',
            'stok' => 'required|integer',
            'status' => 'required|in:tersedia,disewa,rusak',
            'kategori' => 'required|string',
            'lokasi' => 'required|string',
            'satuan' => 'required|string',
            'foto_utama' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
        ]);

        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_sewa);
        if ($hargaBersih <= 0) {
            return back()->withErrors(['harga_sewa' => 'Harga sewa harus angka valid dan lebih dari 0.'])->withInput();
        }

        $data = [
            'nama_mobil' => $request->nama_mobil,
            'deskripsi' => $request->deskripsi,
            'harga_sewa' => $hargaBersih,
            'stok' => $request->stok,
            'status' => $request->status,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'satuan' => $request->satuan,
        ];

        if ($request->hasFile('foto_utama')) { 
            $data['foto'] = $request->file('foto_utama')->store('mobils', 'public'); 
        }
        if ($request->hasFile('foto_2')) {
            $data['foto_2'] = $request->file('foto_2')->store('mobils', 'public');
        }
        if ($request->hasFile('foto_3')) {
            $data['foto_3'] = $request->file('foto_3')->store('mobils', 'public');
        }

        Mobil::create($data);

        return redirect()->route('admin.unit.mobil.index')->with('success', 'Mobil berhasil ditambahkan.');
    }

    public function show($id)
    {
        $mobil = Mobil::findOrFail($id);
        return view('admin.unit.mobil.show', compact('mobil'));
    }

    public function edit($id)
    {
        $mobil = Mobil::findOrFail($id);
        return view('admin.unit.mobil.edit', compact('mobil'));
    }

    public function destroy($id)
    {
        $mobil = Mobil::findOrFail($id);

        if ($mobil->foto) Storage::disk('public')->delete($mobil->foto);
        if ($mobil->foto_2) Storage::disk('public')->delete($mobil->foto_2);
        if ($mobil->foto_3) Storage::disk('public')->delete($mobil->foto_3);

        $mobil->delete();

        return redirect()->route('admin.unit.mobil.index')->with('success', 'Mobil berhasil dihapus.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_mobil' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_sewa' => 'required|string',
            'stok' => 'required|integer',
            'status' => 'required|in:tersedia,disewa,rusak',
            'kategori' => 'required|string',
            'lokasi' => 'required|string',
            'satuan' => 'required|string',
            'foto_utama' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_sewa);
        if ($hargaBersih <= 0) {
            return back()->withErrors(['harga_sewa' => 'Harga sewa harus angka valid dan lebih dari 0.'])->withInput();
        }

        $mobil = Mobil::findOrFail($id);

        $data = [
            'nama_mobil' => $request->nama_mobil,
            'deskripsi' => $request->deskripsi,
            'harga_sewa' => $hargaBersih,
            'stok' => $request->stok,
            'status' => $request->status,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'satuan' => $request->satuan,
        ];

        if ($request->hasFile('foto_utama')) {
            if ($mobil->foto) Storage::disk('public')->delete($mobil->foto);
            $data['foto'] = $request->file('foto_utama')->store('mobils', 'public');
        } elseif ($request->input('delete_foto') == '1') {
            if ($mobil->foto) Storage::disk('public')->delete($mobil->foto);
            $data['foto'] = null;
        }

        if ($request->hasFile('foto_2')) {
            if ($mobil->foto_2) Storage::disk('public')->delete($mobil->foto_2);
            $data['foto_2'] = $request->file('foto_2')->store('mobils', 'public');
        } elseif ($request->input('delete_foto_2') == '1') {
            if ($mobil->foto_2) Storage::disk('public')->delete($mobil->foto_2);
            $data['foto_2'] = null;
        }

        if ($request->hasFile('foto_3')) {
            if ($mobil->foto_3) Storage::disk('public')->delete($mobil->foto_3);
            $data['foto_3'] = $request->file('foto_3')->store('mobils', 'public');
        } elseif ($request->input('delete_foto_3') == '1') {
            if ($mobil->foto_3) Storage::disk('public')->delete($mobil->foto_3);
            $data['foto_3'] = null;
        }

        $mobil->update($data);

        return redirect()->route('admin.unit.mobil.index')->with('success', 'Mobil berhasil diperbarui.');
    }
}
