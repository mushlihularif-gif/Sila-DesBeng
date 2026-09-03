<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FasilitasUmum;
use App\Models\Category;
use App\Models\Mobil;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageCompressorService;

class UnitFasilitasUmumController extends Controller
{
    use \App\Traits\ChecksStaffDelegation;

    // Default SOP Texts
    private $defaultSopDitanggung = "1. Penyewa wajib menjaga fasilitas umum dengan baik.\n2. Jika terjadi KERUSAKAN fasilitas selama masa peminjaman/penyewaan, maka SEPENUHNYA menjadi tanggung jawab PENGGUNA (penyewa) untuk mengganti rugi atau memperbaiki fasilitas tersebut sesuai dengan kerusakan.\n3. Fasilitas harus dikembalikan dalam keadaan bersih dan rapi.";
    private $defaultSopTidakDitanggung = "1. Penyewa wajib menjaga fasilitas umum dengan baik.\n2. Jika terjadi kerusakan fasilitas selama masa peminjaman/penyewaan yang diakibatkan oleh faktor ketidaksengajaan/bencana, maka TIDAK DITANGGUNG oleh pengguna karena telah didukung oleh dana operasional.\n3. Namun pengguna tetap diwajibkan melaporkan kejadian tersebut secara transparan dan menjaga kebersihan.";

    public function index(Request $request)
    {
        if ($splash = $this->checkDelegation($request, 'fasilitas_umum', 'Fasilitas Umum & Ambulans')) {
            return $splash;
        }

        $search = $request->get('search');
        $tab = $request->get('tab', 'kendaraan'); // default tab
        
        $fasilitas = FasilitasUmum::query()
            ->when($search, function ($query, $search) {
                return $query->searchWhereLike(['nama_fasilitas', 'kategori'], $search);
            })
            ->paginate(6, ['*'], 'page_gedung')
            ->appends(['search' => $search, 'tab' => 'gedung']);
            
        // Ambil kendaraan publik (Ambulans, dsb)
        $mobils = Mobil::query()
            ->where('kategori', 'ambulans') // Untuk saat ini, kendaraan publik = ambulans
            ->when($search, function ($query, $search) {
                return $query->searchWhereLike(['nama_mobil', 'kategori'], $search);
            })
            ->paginate(6, ['*'], 'page_kendaraan')
            ->appends(['search' => $search, 'tab' => 'kendaraan']);
            
        $user = auth()->user();
        $region = Region::find($user->region_id);
        
        $paymentInfo = $region->payment_info ?? [];
        $regionSettings = $region->settings ?? [];
        
        $sop_active = $paymentInfo['sop_fasilitas_active'] ?? 'ditanggung';
        $sop_ditanggung = $paymentInfo['sop_fasilitas_ditanggung'] ?? $this->defaultSopDitanggung;
        $sop_tidak_ditanggung = $paymentInfo['sop_fasilitas_tidak_ditanggung'] ?? $this->defaultSopTidakDitanggung;
        
        $default_ditanggung = $this->defaultSopDitanggung;
        $default_tidak_ditanggung = $this->defaultSopTidakDitanggung;
        
        return view('admin.unit.fasilitas_umum.index', compact('fasilitas', 'mobils', 'tab', 'search', 'sop_active', 'sop_ditanggung', 'sop_tidak_ditanggung', 'default_ditanggung', 'default_tidak_ditanggung', 'regionSettings'));
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
        $user = auth()->user();
        $region = Region::find($user->region_id);

        if (!$region) {
            return redirect()->back()->with('error', 'Region tidak ditemukan.');
        }

        $paymentInfo = $region->payment_info ?? [];
        
        $paymentInfo['sop_fasilitas_active'] = $request->sop_active;
        $paymentInfo['sop_fasilitas_ditanggung'] = $request->sop_ditanggung;
        $paymentInfo['sop_fasilitas_tidak_ditanggung'] = $request->sop_tidak_ditanggung;
        
        $region->payment_info = $paymentInfo;
        
        $settings = $region->settings ?? [];
        $settings['kontak_aula'] = $request->kontak_aula;
        $region->settings = $settings;
        
        $region->save();

        return redirect()->back()->with('success', 'Pengaturan SOP dan Kontak berhasil disimpan.');
    }

    public function create()
    {
        $savedLocations = FasilitasUmum::select('lokasi', 'latitude', 'longitude')
            ->whereNotNull('lokasi')
            ->where('lokasi', '!=', '')
            ->distinct()
            ->get();
            
        $categories = Category::where('region_id', auth()->user()->region_id)
            ->where(function($q) {
                $q->where('type', 'fasilitas')->orWhereNull('type');
            })->orderBy('name')->get();

        return view('admin.unit.fasilitas_umum.create', compact('savedLocations', 'categories'));
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
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'foto_utama' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            
            'bbm_ditanggung' => 'nullable|string|in:Ditanggung Pengguna,Disediakan',
            
            
            'status_biaya' => 'required|in:gratis,berbayar',
            'harga_sewa' => 'nullable|string',
        ]);

        $hargaBersih = 0;
        if ($request->status_biaya === 'berbayar' && $request->harga_sewa) {
            $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_sewa);
        }

        $data = [
            'nama_fasilitas' => $request->nama_fasilitas,
            'deskripsi' => $request->deskripsi,
            'stok' => $request->stok,
            'status' => $request->status,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            
            'bbm_ditanggung' => $request->bbm_ditanggung,
            
            
            'status_biaya' => $request->status_biaya,
            'harga_sewa' => $hargaBersih > 0 ? $hargaBersih : null,
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
        $savedLocations = FasilitasUmum::select('lokasi', 'latitude', 'longitude')
            ->whereNotNull('lokasi')
            ->where('lokasi', '!=', '')
            ->distinct()
            ->get();
            
        $categories = Category::where('region_id', auth()->user()->region_id)
            ->where(function($q) {
                $q->where('type', 'fasilitas')->orWhereNull('type');
            })->orderBy('name')->get();

        return view('admin.unit.fasilitas_umum.edit', compact('fasilitas', 'savedLocations', 'categories'));
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
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'foto_utama' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            
            'bbm_ditanggung' => 'nullable|string|in:Ditanggung Pengguna,Disediakan',
            
            
            'status_biaya' => 'required|in:gratis,berbayar',
            'harga_sewa' => 'nullable|string',
        ]);

        $fasilitas = FasilitasUmum::findOrFail($id);

        $hargaBersih = 0;
        if ($request->status_biaya === 'berbayar' && $request->harga_sewa) {
            $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_sewa);
        }

        $data = [
            'nama_fasilitas' => $request->nama_fasilitas,
            'deskripsi' => $request->deskripsi,
            'stok' => $request->stok,
            'status' => $request->status,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            
            'bbm_ditanggung' => $request->bbm_ditanggung,
            
            
            'status_biaya' => $request->status_biaya,
            'harga_sewa' => $hargaBersih > 0 ? $hargaBersih : null,
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
