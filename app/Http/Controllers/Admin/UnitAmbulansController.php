<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mobil;
use App\Models\Region;
use App\Models\Supir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitAmbulansController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.unit.fasilitas_umum.index', ['tab' => 'kendaraan']);
    }

    public function create()
    {
        $user = Auth::user();
        if ($user->role === 'admin_desa') {
            $supirs = Supir::where('region_id', $user->region_id)->where('is_fasilitas_umum', 1)->with('ambulans')->get();
        } else {
            $supirs = Supir::where('is_fasilitas_umum', 1)->with('ambulans')->get();
        }

        return view('admin.unit.ambulans.create', compact('supirs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mobil' => 'required|string|max:255',
            'kategori' => 'nullable|string|in:ambulans,kendaraan_operasional',
            'supir_ids' => 'nullable|array',
            'supir_ids.*' => 'exists:supirs,id',
            'nomor_plat' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
        ]);
        
        $kategori = $request->kategori ?? 'ambulans';
        $data = [
            'nama_mobil' => $validated['nama_mobil'],
            'kategori' => $kategori,
            'region_id' => auth()->user()->region_id,
            'harga_sewa' => 0,
            'deskripsi' => "Plat: " . ($request->nomor_plat ?? '-'),
        ];
        
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('unit_layanan/mobil', 'public');
        }
        if ($request->hasFile('foto_2')) {
            $data['foto_2'] = $request->file('foto_2')->store('unit_layanan/mobil', 'public');
        }
        if ($request->hasFile('foto_3')) {
            $data['foto_3'] = $request->file('foto_3')->store('unit_layanan/mobil', 'public');
        }
        
        $mobil = Mobil::create($data);
        
        if ($kategori === 'ambulans' && isset($validated['supir_ids'])) {
            $mobil->supirs()->sync($validated['supir_ids']);
        }
        
        return redirect()->route('admin.unit.fasilitas_umum.index', ['tab' => 'kendaraan'])->with('success', 'Kendaraan Operasional berhasil ditambahkan');
    }

    public function edit($id)
    {
        $ambulans = Mobil::whereIn('kategori', ['ambulans', 'kendaraan_operasional'])->findOrFail($id);
        
        $user = Auth::user();
        if ($user->role === 'admin_desa') {
            $supirs = Supir::where('region_id', $user->region_id)->where('is_fasilitas_umum', 1)->with('ambulans')->get();
        } else {
            $supirs = Supir::where('is_fasilitas_umum', 1)->with('ambulans')->get();
        }

        return view('admin.unit.ambulans.edit', compact('ambulans', 'supirs'));
    }

    public function update(Request $request, $id)
    {
        $ambulans = Mobil::whereIn('kategori', ['ambulans', 'kendaraan_operasional'])->findOrFail($id);
        
        $validated = $request->validate([
            'nama_mobil' => 'required|string|max:255',
            'kategori' => 'nullable|string|in:ambulans,kendaraan_operasional',
            'supir_ids' => 'nullable|array',
            'supir_ids.*' => 'exists:supirs,id',
            'nomor_plat' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
        ]);
        
        $kategori = $request->kategori ?? $ambulans->kategori;
        $data = [
            'nama_mobil' => $validated['nama_mobil'],
            'kategori' => $kategori,
            'deskripsi' => "Plat: " . ($request->nomor_plat ?? '-'),
        ];
        
        // Foto Utama
        if ($request->input('delete_foto') == '1') {
            if ($ambulans->foto && \Storage::disk('public')->exists($ambulans->foto)) {
                \Storage::disk('public')->delete($ambulans->foto);
            }
            $data['foto'] = null;
        }
        if ($request->hasFile('foto')) {
            if ($ambulans->foto && \Storage::disk('public')->exists($ambulans->foto)) {
                \Storage::disk('public')->delete($ambulans->foto);
            }
            $data['foto'] = $request->file('foto')->store('unit_layanan/mobil', 'public');
        }

        // Foto Tambahan 1
        if ($request->input('delete_foto_2') == '1') {
            if ($ambulans->foto_2 && \Storage::disk('public')->exists($ambulans->foto_2)) {
                \Storage::disk('public')->delete($ambulans->foto_2);
            }
            $data['foto_2'] = null;
        }
        if ($request->hasFile('foto_2')) {
            if ($ambulans->foto_2 && \Storage::disk('public')->exists($ambulans->foto_2)) {
                \Storage::disk('public')->delete($ambulans->foto_2);
            }
            $data['foto_2'] = $request->file('foto_2')->store('unit_layanan/mobil', 'public');
        }

        // Foto Tambahan 2
        if ($request->input('delete_foto_3') == '1') {
            if ($ambulans->foto_3 && \Storage::disk('public')->exists($ambulans->foto_3)) {
                \Storage::disk('public')->delete($ambulans->foto_3);
            }
            $data['foto_3'] = null;
        }
        if ($request->hasFile('foto_3')) {
            if ($ambulans->foto_3 && \Storage::disk('public')->exists($ambulans->foto_3)) {
                \Storage::disk('public')->delete($ambulans->foto_3);
            }
            $data['foto_3'] = $request->file('foto_3')->store('unit_layanan/mobil', 'public');
        }
        
        $ambulans->update($data);
        
        if ($kategori === 'ambulans' && isset($validated['supir_ids'])) {
            $ambulans->supirs()->sync($validated['supir_ids']);
        } else {
            $ambulans->supirs()->sync([]);
        }
        
        return redirect()->route('admin.unit.fasilitas_umum.index', ['tab' => 'kendaraan'])->with('success', 'Kendaraan Operasional berhasil diubah');
    }

    public function destroy($id)
    {
        $ambulans = Mobil::whereIn('kategori', ['ambulans', 'kendaraan_operasional'])->findOrFail($id);
        if ($ambulans->foto && \Storage::disk('public')->exists($ambulans->foto)) {
            \Storage::disk('public')->delete($ambulans->foto);
        }
        if ($ambulans->foto_2 && \Storage::disk('public')->exists($ambulans->foto_2)) {
            \Storage::disk('public')->delete($ambulans->foto_2);
        }
        if ($ambulans->foto_3 && \Storage::disk('public')->exists($ambulans->foto_3)) {
            \Storage::disk('public')->delete($ambulans->foto_3);
        }
        $ambulans->supirs()->detach();
        $ambulans->delete();
        
        return redirect()->route('admin.unit.fasilitas_umum.index', ['tab' => 'kendaraan'])->with('success', 'Kendaraan Operasional berhasil dihapus');
    }
}