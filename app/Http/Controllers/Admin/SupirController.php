<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupirController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->role === 'admin_desa') {
            $supirs = Supir::where('region_id', $user->region_id)->get();
        } else {
            $supirs = Supir::all();
        }
        
        $supirMobil = $supirs->where('layanan', 'Penyewaan Mobil');
        $supirFasilitas = $supirs->where('layanan', 'Fasilitas Umum');
        
        return view('admin.supir.index', compact('supirMobil', 'supirFasilitas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'layanan' => 'required|in:Penyewaan Mobil,Fasilitas Umum',
            'nama' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:50',
            'status' => 'required|in:Tersedia,Sedang Bertugas,Tidak Aktif'
        ]);

        $user = Auth::user();
        $region_id = ($user->role === 'admin_desa') ? $user->region_id : $request->region_id;

        Supir::create([
            'region_id' => $region_id,
            'layanan' => $request->layanan,
            'nama' => $request->nama,
            'kontak' => $request->kontak,
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Data supir berhasil ditambahkan!');
    }

    public function update(Request $request, Supir $supir)
    {
        $request->validate([
            'layanan' => 'required|in:Penyewaan Mobil,Fasilitas Umum',
            'nama' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:50',
            'status' => 'required|in:Tersedia,Sedang Bertugas,Tidak Aktif'
        ]);

        $supir->update([
            'layanan' => $request->layanan,
            'nama' => $request->nama,
            'kontak' => $request->kontak,
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Data supir berhasil diperbarui!');
    }

    public function destroy(Supir $supir)
    {
        $supir->delete();
        return redirect()->back()->with('success', 'Data supir berhasil dihapus!');
    }
}
