<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mobil;
use App\Models\Region;
use Illuminate\Http\Request;

class UnitAmbulansController extends Controller
{
    public function index()
    {
        $admin = auth()->user();
        $query = Mobil::where('kategori', 'ambulans');
        
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
        return view('admin.unit.ambulans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mobil' => 'required|string|max:255',
            'nama_supir' => 'required|string|max:255',
            'kontak_supir' => 'required|string|max:255',
            'nomor_plat' => 'nullable|string|max:20',
        ]);
        
        $validated['kategori'] = 'ambulans';
        $validated['region_id'] = auth()->user()->region_id;
        $validated['harga_sewa'] = 0;
        
        // Simpan nomor plat ke dalam deskripsi atau tambah kolom khusus. 
        // Jika belum ada kolom nomor_plat, masukkan ke deskripsi sementara
        $validated['deskripsi'] = "Plat: " . ($request->nomor_plat ?? '-');
        
        Mobil::create($validated);
        
        return redirect()->route('admin.unit.ambulans.index')->with('success', 'Ambulans berhasil ditambahkan');
    }

    public function edit($id)
    {
        $ambulans = Mobil::where('kategori', 'ambulans')->findOrFail($id);
        return view('admin.unit.ambulans.edit', compact('ambulans'));
    }

    public function update(Request $request, $id)
    {
        $ambulans = Mobil::where('kategori', 'ambulans')->findOrFail($id);
        
        $validated = $request->validate([
            'nama_mobil' => 'required|string|max:255',
            'nama_supir' => 'required|string|max:255',
            'kontak_supir' => 'required|string|max:255',
            'nomor_plat' => 'nullable|string|max:20',
        ]);
        
        $validated['deskripsi'] = "Plat: " . ($request->nomor_plat ?? '-');
        
        $ambulans->update($validated);
        
        return redirect()->route('admin.unit.ambulans.index')->with('success', 'Ambulans berhasil diubah');
    }

    public function destroy($id)
    {
        $ambulans = Mobil::where('kategori', 'ambulans')->findOrFail($id);
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
