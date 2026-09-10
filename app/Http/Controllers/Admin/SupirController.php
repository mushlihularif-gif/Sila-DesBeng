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
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'supir');
        if (!in_array($tab, ['supir', 'pengurus_gedung'])) {
            $tab = 'supir';
        }

        $query = Supir::with(['user', 'ambulans', 'fasilitas']);
        if ($user && $user->role === 'admin_desa' && $user->region_id) {
            $query->where('region_id', $user->region_id);
            $users = \App\Models\User::where('region_id', $user->region_id)->get();
        } else {
            $users = \App\Models\User::all();
        }

        // Hitung total untuk badge tab
        $countQuery = clone $query;
        $countSupir = (clone $countQuery)->where('tipe', 'supir')->count();
        $countPengurus = (clone $countQuery)->where('tipe', 'pengurus_gedung')->count();

        // Ambil data sesuai tab aktif
        $supirs = $query->where('tipe', $tab)->get();

        // Jika request via AJAX untuk pergantian tab cepat
        if ($request->ajax() && $request->has('ajax_tab')) {
            $tableHtml = view('admin.supir.partials.table_rows', compact('supirs', 'tab'))->render();
            $cardsHtml = view('admin.supir.partials.mobile_cards', compact('supirs', 'tab'))->render();
            $modalsHtml = view('admin.supir.partials.modals', compact('supirs', 'users', 'tab'))->render();

            return response()->json([
                'success' => true,
                'tab' => $tab,
                'count_supir' => $countSupir,
                'count_pengurus' => $countPengurus,
                'table_html' => $tableHtml,
                'cards_html' => $cardsHtml,
                'modals_html' => $modalsHtml,
            ]);
        }

        return view('admin.supir.index', compact('supirs', 'users', 'tab', 'countSupir', 'countPengurus'));
    }

    public function store(Request $request)
    {
        // Default tipe ke 'supir' jika tidak dikirim dari form
        if (!$request->filled('tipe')) {
            $request->merge(['tipe' => 'supir']);
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'tipe' => 'required|in:supir,pengurus_gedung',
            'kontak' => 'nullable|string|max:50',
            'status' => 'required|in:Tersedia,Sedang Bertugas,Tidak Aktif',
            'user_id' => 'nullable|exists:users,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192'
        ]);

        $user = Auth::user();
        $region_id = ($user && $user->region_id) ? $user->region_id : ($request->region_id ?? null);
        $tipe = $request->input('tipe', 'supir');

        $data = [
            'region_id' => $region_id,
            'tipe' => $tipe,
            'nama' => $request->nama,
            'kontak' => $request->kontak,
            'status' => $request->status,
            'user_id' => $request->user_id,
            'is_sewa_mobil' => ($tipe === 'supir' && $request->has('is_sewa_mobil')) ? 1 : 0,
            'is_fasilitas_umum' => ($tipe === 'pengurus_gedung' || $request->has('is_fasilitas_umum')) ? 1 : 0,
            'layanan' => null,
        ];

        if ($request->hasFile('foto')) {
            $data['foto'] = ImageCompressorService::compressAndStore($request->file('foto'), 'supirs');
        }

        $newSupir = Supir::create($data);

        // Respons AJAX (misal dari modal penambahan langsung di form ambulans atau fasilitas umum)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $tipe === 'pengurus_gedung' ? 'Pengurus gedung berhasil ditambahkan' : 'Supir berhasil ditambahkan',
                'supir' => $newSupir,
                'avatar_url' => $newSupir->foto ? asset('storage/' . $newSupir->foto) : asset('Admin/img/avatars/pria.png'),
            ]);
        }

        $pesan = $tipe === 'pengurus_gedung' ? 'Data pengurus gedung berhasil ditambahkan!' : 'Data supir berhasil ditambahkan!';
        return redirect()->route('supir.index', ['tab' => $tipe])->with('success', $pesan);
    }

    public function update(Request $request, Supir $supir)
    {
        if (!$request->filled('tipe')) {
            $request->merge(['tipe' => $supir->tipe ?? 'supir']);
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'tipe' => 'required|in:supir,pengurus_gedung',
            'kontak' => 'nullable|string|max:50',
            'status' => 'required|in:Tersedia,Sedang Bertugas,Tidak Aktif',
            'user_id' => 'nullable|exists:users,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192'
        ]);

        $tipe = $request->input('tipe', $supir->tipe ?? 'supir');

        $data = [
            'tipe' => $tipe,
            'nama' => $request->nama,
            'kontak' => $request->kontak,
            'status' => $request->status,
            'user_id' => $request->user_id,
            'is_sewa_mobil' => ($tipe === 'supir' && $request->has('is_sewa_mobil')) ? 1 : 0,
            'is_fasilitas_umum' => ($tipe === 'pengurus_gedung' || $request->has('is_fasilitas_umum')) ? 1 : 0,
        ];

        if ($request->hasFile('foto')) {
            if ($supir->foto) Storage::disk('public')->delete($supir->foto);
            $data['foto'] = ImageCompressorService::compressAndStore($request->file('foto'), 'supirs');
        } elseif ($request->input('delete_foto') == '1') {
            if ($supir->foto) Storage::disk('public')->delete($supir->foto);
            $data['foto'] = null;
        }

        $supir->update($data);

        $pesan = $tipe === 'pengurus_gedung' ? 'Data pengurus gedung berhasil diperbarui!' : 'Data supir berhasil diperbarui!';
        return redirect()->route('supir.index', ['tab' => $tipe])->with('success', $pesan);
    }

    public function destroy(Supir $supir)
    {
        $tab = $supir->tipe ?? 'supir';
        if ($supir->foto) Storage::disk('public')->delete($supir->foto);
        $supir->delete();

        $pesan = $tab === 'pengurus_gedung' ? 'Data pengurus gedung berhasil dihapus!' : 'Data supir berhasil dihapus!';
        return redirect()->route('supir.index', ['tab' => $tab])->with('success', $pesan);
    }
}
