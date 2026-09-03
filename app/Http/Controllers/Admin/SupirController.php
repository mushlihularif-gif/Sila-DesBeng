<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageCompressorService;

class SupirController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->role === 'admin_desa') {
            $supirs = Supir::with('user')
                           ->where('region_id', $user->region_id)->get();
            $users = \App\Models\User::where('region_id', $user->region_id)->get();
        } else {
            $supirs = Supir::with('user')->get();
            $users = \App\Models\User::all();
        }
        
        return view('admin.supir.index', compact('supirs', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:50',
            'status' => 'required|in:Tersedia,Sedang Bertugas,Tidak Aktif',
            'user_id' => 'nullable|exists:users,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $user = Auth::user();
        $region_id = ($user->role === 'admin_desa') ? $user->region_id : $request->region_id;
        
        $data = [
            'region_id' => $region_id,
            'nama' => $request->nama,
            'kontak' => $request->kontak,
            'status' => $request->status,
            'user_id' => $request->user_id,
            'is_sewa_mobil' => $request->has('is_sewa_mobil') ? 1 : 0,
            'is_fasilitas_umum' => $request->has('is_fasilitas_umum') ? 1 : 0,
            'layanan' => null, // No longer used
        ];

        if ($request->hasFile('foto')) {
            $data['foto'] = ImageCompressorService::compressAndStore($request->file('foto'), 'supirs');
        }

        Supir::create($data);

        // If it's an AJAX request (from the modal on vehicle create pages)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Supir berhasil ditambahkan',
                'supir' => Supir::latest()->first()
            ]);
        }

        return redirect()->back()->with('success', 'Data supir berhasil ditambahkan!');
    }

    public function update(Request $request, Supir $supir)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:50',
            'status' => 'required|in:Tersedia,Sedang Bertugas,Tidak Aktif',
            'user_id' => 'nullable|exists:users,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $data = [
            'nama' => $request->nama,
            'kontak' => $request->kontak,
            'status' => $request->status,
            'user_id' => $request->user_id,
            'is_sewa_mobil' => $request->has('is_sewa_mobil') ? 1 : 0,
            'is_fasilitas_umum' => $request->has('is_fasilitas_umum') ? 1 : 0,
        ];

        if ($request->hasFile('foto')) {
            if ($supir->foto) Storage::disk('public')->delete($supir->foto);
            $data['foto'] = ImageCompressorService::compressAndStore($request->file('foto'), 'supirs');
        } elseif ($request->input('delete_foto') == '1') {
            if ($supir->foto) Storage::disk('public')->delete($supir->foto);
            $data['foto'] = null;
        }

        $supir->update($data);

        return redirect()->back()->with('success', 'Data supir berhasil diperbarui!');
    }

    public function destroy(Supir $supir)
    {
        if ($supir->foto) Storage::disk('public')->delete($supir->foto);
        $supir->delete();
        return redirect()->back()->with('success', 'Data supir berhasil dihapus!');
    }
}
