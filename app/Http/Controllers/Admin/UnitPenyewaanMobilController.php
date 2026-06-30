<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mobil;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageCompressorService;

class UnitPenyewaanMobilController extends Controller
{
    // Default SOP Texts
    private $defaultSopDitanggung = "1. Penyewa wajib menjaga mobil sewaan dengan baik.\n2. Jika terjadi KERUSAKAN atau KEHILANGAN mobil selama masa penyewaan, maka SEPENUHNYA menjadi tanggung jawab PENGGUNA (penyewa) untuk mengganti rugi atau memperbaiki mobil tersebut sesuai dengan kerusakan.\n3. Keterlambatan pengembalian dapat dikenakan denda sesuai ketentuan yang berlaku.";
    private $defaultSopTidakDitanggung = "1. Penyewa wajib menjaga mobil sewaan dengan baik.\n2. Jika terjadi kerusakan atau kehilangan mobil selama masa penyewaan yang diakibatkan oleh faktor ketidaksengajaan/bencana, maka TIDAK DITANGGUNG oleh pengguna (penyewa) karena telah didukung oleh dana operasional.\n3. Namun pengguna tetap diwajibkan melaporkan kejadian tersebut secara transparan.";

    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $mobils = Mobil::query()
            ->when($search, function ($query, $search) {
                return $query->searchWhereLike(['nama_mobil', 'kategori'], $search);
            })
            ->paginate(6)
            ->appends(['search' => $search]);
        
        return view('admin.unit.mobil.index', compact('mobils', 'search'));
    }

    public function sop()
    {
        $user = auth()->user();
        $region = Region::find($user->region_id);

        if (!$region) {
            return redirect()->back()->with('error', 'Region tidak ditemukan.');
        }

        $paymentInfo = $region->payment_info ?? [];
        
        $sop_active = $paymentInfo['sop_mobil_active'] ?? 'ditanggung';
        $sop_ditanggung = $paymentInfo['sop_mobil_ditanggung'] ?? $this->defaultSopDitanggung;
        $sop_tidak_ditanggung = $paymentInfo['sop_mobil_tidak_ditanggung'] ?? $this->defaultSopTidakDitanggung;
        
        $default_ditanggung = $this->defaultSopDitanggung;
        $default_tidak_ditanggung = $this->defaultSopTidakDitanggung;

        return view('admin.unit.mobil.sop', compact('sop_active', 'sop_ditanggung', 'sop_tidak_ditanggung', 'default_ditanggung', 'default_tidak_ditanggung'));
    }

    public function updateSop(Request $request)
    {
        $request->validate([
            'sop_mobil_active' => 'required|in:ditanggung,tidak_ditanggung',
            'sop_mobil_ditanggung' => 'nullable|string',
            'sop_mobil_tidak_ditanggung' => 'nullable|string',
        ]);

        $user = auth()->user();
        $region = Region::find($user->region_id);

        if (!$region) {
            return redirect()->back()->with('error', 'Region tidak ditemukan.');
        }

        $paymentInfo = $region->payment_info ?? [];
        $paymentInfo['sop_mobil_active'] = $request->sop_mobil_active;
        $paymentInfo['sop_mobil_ditanggung'] = $request->sop_mobil_ditanggung ?? $this->defaultSopDitanggung;
        $paymentInfo['sop_mobil_tidak_ditanggung'] = $request->sop_mobil_tidak_ditanggung ?? $this->defaultSopTidakDitanggung;

        $region->update([
            'payment_info' => $paymentInfo,
        ]);

        return redirect()->route('admin.unit.mobil.sop')->with('success', 'Ketentuan SOP berhasil diperbarui.');
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
            'harga_dalam_desa' => 'required|string',
            'batas_km_dalam_desa' => 'required|integer',
            'harga_luar_desa' => 'required|string',
            'batas_km_luar_desa' => 'required|integer',
            'harga_luar_kota' => 'required|string',
            'bbm_ditanggung' => 'required|string|in:Pemerintah Desa,Penyewa',
            'opsi_supir' => 'required|string|in:Lepas Kunci,Dengan Supir,Bebas Pilih',
        ]);

        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_sewa);
        $hargaDalamDesa = (int) preg_replace('/[^0-9]/', '', $request->harga_dalam_desa);
        $hargaLuarDesa = (int) preg_replace('/[^0-9]/', '', $request->harga_luar_desa);
        $hargaLuarKota = (int) preg_replace('/[^0-9]/', '', $request->harga_luar_kota);

        if ($hargaDalamDesa <= 0) {
            return back()->withErrors(['harga_dalam_desa' => 'Harga sewa harus angka valid dan lebih dari 0.'])->withInput();
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
            'harga_dalam_desa' => $hargaDalamDesa,
            'batas_km_dalam_desa' => $request->batas_km_dalam_desa,
            'harga_luar_desa' => $hargaLuarDesa,
            'batas_km_luar_desa' => $request->batas_km_luar_desa,
            'harga_luar_kota' => $hargaLuarKota,
            'bbm_ditanggung' => $request->bbm_ditanggung,
            'opsi_supir' => $request->opsi_supir,
        ];

        if ($request->hasFile('foto_utama')) { 
            $data['foto'] = ImageCompressorService::compressAndStore($request->file('foto_utama'), 'mobils'); 
        }
        if ($request->hasFile('foto_2')) {
            $data['foto_2'] = ImageCompressorService::compressAndStore($request->file('foto_2'), 'mobils');
        }
        if ($request->hasFile('foto_3')) {
            $data['foto_3'] = ImageCompressorService::compressAndStore($request->file('foto_3'), 'mobils');
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
            'harga_dalam_desa' => 'required|string',
            'batas_km_dalam_desa' => 'required|integer',
            'harga_luar_desa' => 'required|string',
            'batas_km_luar_desa' => 'required|integer',
            'harga_luar_kota' => 'required|string',
            'bbm_ditanggung' => 'required|string|in:Pemerintah Desa,Penyewa',
            'opsi_supir' => 'required|string|in:Lepas Kunci,Dengan Supir,Bebas Pilih',
        ]);

        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_sewa);
        $hargaDalamDesa = (int) preg_replace('/[^0-9]/', '', $request->harga_dalam_desa);
        $hargaLuarDesa = (int) preg_replace('/[^0-9]/', '', $request->harga_luar_desa);
        $hargaLuarKota = (int) preg_replace('/[^0-9]/', '', $request->harga_luar_kota);

        if ($hargaDalamDesa <= 0) {
            return back()->withErrors(['harga_dalam_desa' => 'Harga sewa harus angka valid dan lebih dari 0.'])->withInput();
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
            'harga_dalam_desa' => $hargaDalamDesa,
            'batas_km_dalam_desa' => $request->batas_km_dalam_desa,
            'harga_luar_desa' => $hargaLuarDesa,
            'batas_km_luar_desa' => $request->batas_km_luar_desa,
            'harga_luar_kota' => $hargaLuarKota,
            'bbm_ditanggung' => $request->bbm_ditanggung,
            'opsi_supir' => $request->opsi_supir,
        ];

        if ($request->hasFile('foto_utama')) {
            if ($mobil->foto) Storage::disk('public')->delete($mobil->foto);
            $data['foto'] = ImageCompressorService::compressAndStore($request->file('foto_utama'), 'mobils');
        } elseif ($request->input('delete_foto') == '1') {
            if ($mobil->foto) Storage::disk('public')->delete($mobil->foto);
            $data['foto'] = null;
        }

        if ($request->hasFile('foto_2')) {
            if ($mobil->foto_2) Storage::disk('public')->delete($mobil->foto_2);
            $data['foto_2'] = ImageCompressorService::compressAndStore($request->file('foto_2'), 'mobils');
        } elseif ($request->input('delete_foto_2') == '1') {
            if ($mobil->foto_2) Storage::disk('public')->delete($mobil->foto_2);
            $data['foto_2'] = null;
        }

        if ($request->hasFile('foto_3')) {
            if ($mobil->foto_3) Storage::disk('public')->delete($mobil->foto_3);
            $data['foto_3'] = ImageCompressorService::compressAndStore($request->file('foto_3'), 'mobils');
        } elseif ($request->input('delete_foto_3') == '1') {
            if ($mobil->foto_3) Storage::disk('public')->delete($mobil->foto_3);
            $data['foto_3'] = null;
        }

        $mobil->update($data);

        return redirect()->route('admin.unit.mobil.index')->with('success', 'Mobil berhasil diperbarui.');
    }
}
