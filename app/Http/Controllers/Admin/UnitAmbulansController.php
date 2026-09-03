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
        $admin = auth()->user();
        $query = Mobil::where('kategori', 'ambulans')->with('supirs');
        
        if ($admin->region_id) {
            $query->where('region_id', $admin->region_id);
        }
        
        $ambulansList = $query->paginate(10);
        
        $regionSettings = null;
        if ($admin->region_id) {
            $regionSettings = Region::find($admin->region_id);
        }
        
        return view('admin.unit.ambulans.index', compact('ambulansList', 'regionSettings'));
    }

    public function create()
    {
        $user = Auth::user();
        if ($user->role === 'admin_desa') {
            $supirs = Supir::where('region_id', $user->region_id)->where('is_fasilitas_umum', 1)->get();
        } else {
            $supirs = Supir::where('is_fasilitas_umum', 1)->get();
        }

        return view('admin.unit.ambulans.create', compact('supirs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mobil' => 'required|string|max:255',
            'supir_ids' => 'nullable|array',
            'supir_ids.*' => 'exists:supirs,id',
            'nomor_plat' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);
        
        $data = [
            'nama_mobil' => $validated['nama_mobil'],
            'kategori' => 'ambulans',
            'region_id' => auth()->user()->region_id,
            'harga_sewa' => 0,
            'deskripsi' => "Plat: " . ($request->nomor_plat ?? '-'),
        ];
        
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('unit_layanan/mobil', 'public');
            $data['foto'] = $path;
        }
        
        $mobil = Mobil::create($data);
        
        if (isset($validated['supir_ids'])) {
            $mobil->supirs()->sync($validated['supir_ids']);
        }
        
        return redirect()->route('admin.unit.ambulans.index')->with('success', 'Kendaraan Ambulans berhasil ditambahkan');
    }

    public function edit($id)
    {
        $ambulans = Mobil::where('kategori', 'ambulans')->findOrFail($id);
        
        $user = Auth::user();
        if ($user->role === 'admin_desa') {
            $supirs = Supir::where('region_id', $user->region_id)->where('is_fasilitas_umum', 1)->get();
        } else {
            $supirs = Supir::where('is_fasilitas_umum', 1)->get();
        }

        return view('admin.unit.ambulans.edit', compact('ambulans', 'supirs'));
    }

    public function update(Request $request, $id)
    {
        $ambulans = Mobil::where('kategori', 'ambulans')->findOrFail($id);
        
        $validated = $request->validate([
            'nama_mobil' => 'required|string|max:255',
            'supir_ids' => 'nullable|array',
            'supir_ids.*' => 'exists:supirs,id',
            'nomor_plat' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);
        
        $data = [
            'nama_mobil' => $validated['nama_mobil'],
            'deskripsi' => "Plat: " . ($request->nomor_plat ?? '-'),
        ];
        
        if ($request->hasFile('foto')) {
            if ($ambulans->foto && \Storage::disk('public')->exists($ambulans->foto)) {
                \Storage::disk('public')->delete($ambulans->foto);
            }
            $path = $request->file('foto')->store('unit_layanan/mobil', 'public');
            $data['foto'] = $path;
        }
        
        $ambulans->update($data);
        
        if (isset($validated['supir_ids'])) {
            $ambulans->supirs()->sync($validated['supir_ids']);
        } else {
            $ambulans->supirs()->sync([]);
        }
        
        return redirect()->route('admin.unit.ambulans.index')->with('success', 'Kendaraan Ambulans berhasil diubah');
    }

    public function destroy($id)
    {
        $ambulans = Mobil::where('kategori', 'ambulans')->findOrFail($id);
        $ambulans->supirs()->detach();
        $ambulans->delete();
        
        return redirect()->route('admin.unit.ambulans.index')->with('success', 'Ambulans berhasil dihapus');
    }

    public function updateSop(Request $request)
    {
        $admin = auth()->user();
        if (!$admin->region_id) {
            return redirect()->back()->with('error', 'Anda harus terhubung dengan suatu wilayah untuk mengatur SOP');
        }

        $region = Region::find($admin->region_id);
        $settings = $region->settings ?? [];
        
        $settings['sop_ambulans'] = $request->input('sop_ambulans');
        $settings['kontak_ambulans'] = $request->input('kontak_ambulans');
        
        $region->settings = $settings;
        $region->save();

        return redirect()->back()->with('success', 'Pengaturan Ambulans Darurat berhasil disimpan.');
    }
}