<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FasilitasUmum;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageCompressorService;

class UnitFasilitasUmumController extends Controller
{
    // Default SOP Texts
    private $defaultSopDitanggung = "1. Penyewa wajib menjaga fasilitas umum dengan baik.\n2. Jika terjadi KERUSAKAN fasilitas selama masa peminjaman/penyewaan, maka SEPENUHNYA menjadi tanggung jawab PENGGUNA (penyewa) untuk mengganti rugi atau memperbaiki fasilitas tersebut sesuai dengan kerusakan.\n3. Fasilitas harus dikembalikan dalam keadaan bersih dan rapi.";
    private $defaultSopTidakDitanggung = "1. Penyewa wajib menjaga fasilitas umum dengan baik.\n2. Jika terjadi kerusakan fasilitas selama masa peminjaman/penyewaan yang diakibatkan oleh faktor ketidaksengajaan/bencana, maka TIDAK DITANGGUNG oleh pengguna karena telah didukung oleh dana operasional.\n3. Namun pengguna tetap diwajibkan melaporkan kejadian tersebut secara transparan dan menjaga kebersihan.";

    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $fasilitas = FasilitasUmum::query()
            ->when($search, function ($query, $search) {
                return $query->searchWhereLike(['nama_fasilitas', 'kategori'], $search);
            })
            ->paginate(6)
            ->appends(['search' => $search]);
        
        return view('admin.unit.fasilitas_umum.index', compact('fasilitas', 'search'));
    }

    public function sop()
    {
        $user = auth()->user();
        $region = Region::find($user->region_id);

        if (!$region) {
            return redirect()->back()->with('error', 'Region tidak ditemukan.');
        }

        $paymentInfo = $region->payment_info ?? [];
        
        $sop_active = $paymentInfo['sop_fasilitas_active'] ?? 'ditanggung';
        $sop_ditanggung = $paymentInfo['sop_fasilitas_ditanggung'] ?? $this->defaultSopDitanggung;
        $sop_tidak_ditanggung = $paymentInfo['sop_fasilitas_tidak_ditanggung'] ?? $this->defaultSopTidakDitanggung;
        
        $default_ditanggung = $this->defaultSopDitanggung;
        $default_tidak_ditanggung = $this->defaultSopTidakDitanggung;

        return view('admin.unit.fasilitas_umum.sop', compact('sop_active', 'sop_ditanggung', 'sop_tidak_ditanggung', 'default_ditanggung', 'default_tidak_ditanggung'));
    }

    public function updateSop(Request $request)
    {
        $request->validate([
            'sop_fasilitas_active' => 'required|in:ditanggung,tidak_ditanggung',
            'sop_fasilitas_ditanggung' => 'nullable|string',
            'sop_fasilitas_tidak_ditanggung' => 'nullable|string',
        ]);

        $user = auth()->user();
        $region = Region::find($user->region_id);

        if (!$region) {
            return redirect()->back()->with('error', 'Region tidak ditemukan.');
        }

        $paymentInfo = $region->payment_info ?? [];
        $paymentInfo['sop_fasilitas_active'] = $request->sop_fasilitas_active;
        $paymentInfo['sop_fasilitas_ditanggung'] = $request->sop_fasilitas_ditanggung ?? $this->defaultSopDitanggung;
        $paymentInfo['sop_fasilitas_tidak_ditanggung'] = $request->sop_fasilitas_tidak_ditanggung ?? $this->defaultSopTidakDitanggung;

        $region->update([
            'payment_info' => $paymentInfo,
        ]);

        return redirect()->route('admin.unit.fasilitas_umum.sop')->with('success', 'Ketentuan SOP berhasil diperbarui.');
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
            'opsi_supir' => 'nullable|string|in:Lepas Kunci,Dengan Supir,Bebas Pilih',
            'bbm_ditanggung' => 'nullable|string|in:Pemerintah Desa,Penyewa',
        ]);

        $data = [
            'nama_fasilitas' => $request->nama_fasilitas,
            'deskripsi' => $request->deskripsi,
            'stok' => $request->stok,
            'status' => $request->status,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'opsi_supir' => $request->opsi_supir,
            'bbm_ditanggung' => $request->bbm_ditanggung,
        ];

        if ($request->hasFile('foto_utama')) { 
            $data['foto'] = ImageCompressorService::compressAndStore($request->file('foto_utama'), 'fasilitas_umum'); 
        }
        if ($request->hasFile('foto_2')) {
            $data['foto_2'] = ImageCompressorService::compressAndStore($request->file('foto_2'), 'fasilitas_umum');
        }
        if ($request->hasFile('foto_3')) {
            $data['foto_3'] = ImageCompressorService::compressAndStore($request->file('foto_3'), 'fasilitas_umum');
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
            'opsi_supir' => 'nullable|string|in:Lepas Kunci,Dengan Supir,Bebas Pilih',
            'bbm_ditanggung' => 'nullable|string|in:Pemerintah Desa,Penyewa',
        ]);

        $fasilitas = FasilitasUmum::findOrFail($id);

        $data = [
            'nama_fasilitas' => $request->nama_fasilitas,
            'deskripsi' => $request->deskripsi,
            'stok' => $request->stok,
            'status' => $request->status,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'opsi_supir' => $request->opsi_supir,
            'bbm_ditanggung' => $request->bbm_ditanggung,
        ];

        if ($request->hasFile('foto_utama')) {
            if ($fasilitas->foto) Storage::disk('public')->delete($fasilitas->foto);
            $data['foto'] = ImageCompressorService::compressAndStore($request->file('foto_utama'), 'fasilitas_umum');
        } elseif ($request->input('delete_foto') == '1') {
            if ($fasilitas->foto) Storage::disk('public')->delete($fasilitas->foto);
            $data['foto'] = null;
        }

        if ($request->hasFile('foto_2')) {
            if ($fasilitas->foto_2) Storage::disk('public')->delete($fasilitas->foto_2);
            $data['foto_2'] = ImageCompressorService::compressAndStore($request->file('foto_2'), 'fasilitas_umum');
        } elseif ($request->input('delete_foto_2') == '1') {
            if ($fasilitas->foto_2) Storage::disk('public')->delete($fasilitas->foto_2);
            $data['foto_2'] = null;
        }

        if ($request->hasFile('foto_3')) {
            if ($fasilitas->foto_3) Storage::disk('public')->delete($fasilitas->foto_3);
            $data['foto_3'] = ImageCompressorService::compressAndStore($request->file('foto_3'), 'fasilitas_umum');
        } elseif ($request->input('delete_foto_3') == '1') {
            if ($fasilitas->foto_3) Storage::disk('public')->delete($fasilitas->foto_3);
            $data['foto_3'] = null;
        }

        $fasilitas->update($data);

        return redirect()->route('admin.unit.fasilitas_umum.index')->with('success', 'Fasilitas Umum berhasil diperbarui.');
    }
}
