<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mobil;
use App\Models\Category;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageCompressorService;

class UnitPenyewaanMobilController extends Controller
{
    use \App\Traits\ChecksStaffDelegation;

    // Default SOP Texts
    private $defaultSopDitanggung = "1. Penyewa wajib menjaga mobil sewaan dengan baik.\n2. Jika terjadi KERUSAKAN atau KEHILANGAN mobil selama masa penyewaan, maka SEPENUHNYA menjadi tanggung jawab PENGGUNA (penyewa) untuk mengganti rugi atau memperbaiki mobil tersebut sesuai dengan kerusakan.\n3. Keterlambatan pengembalian dapat dikenakan denda sesuai ketentuan yang berlaku.";
    private $defaultSopTidakDitanggung = "1. Penyewa wajib menjaga mobil sewaan dengan baik.\n2. Jika terjadi kerusakan atau kehilangan mobil selama masa penyewaan yang diakibatkan oleh faktor ketidaksengajaan/bencana, maka TIDAK DITANGGUNG oleh pengguna (penyewa) karena telah didukung oleh dana operasional.\n3. Namun pengguna tetap diwajibkan melaporkan kejadian tersebut secara transparan.";

    public function index(Request $request)
    {
        if ($splash = $this->checkDelegation($request, 'sewa_mobil', 'Penyewaan Mobil/Kendaraan')) {
            return $splash;
        }

        $search = $request->get('search');
        
        $mobils = Mobil::query()
            ->where('kategori', '!=', 'ambulans') // Mencegah kendaraan fasilitas umum masuk
            ->when($search, function ($query, $search) {
                return $query->searchWhereLike(['nama_mobil', 'kategori'], $search);
            })
            ->paginate(6)
            ->appends(['search' => $search]);
        
        $tab = $request->get('tab', 'katalog');
        $user = auth()->user();
        $chats = \App\Models\UnitChatSession::where('region_id', $user ? $user->region_id : null)
            ->where('service_type', 'mobil')
            ->with('user')
            ->orderBy('last_message_at', 'desc')
            ->get();
        $totalUnreadChats = $chats->sum('unread_admin_count');
        
        return view('admin.unit.mobil.index', compact('mobils', 'search', 'chats', 'totalUnreadChats', 'tab'));
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
        $savedLocations = Mobil::select('lokasi', 'latitude', 'longitude')
            ->whereNotNull('lokasi')
            ->where('lokasi', '!=', '')
            ->distinct()
            ->get();
            
        $categories = Category::where('region_id', auth()->user()->region_id)
            ->where(function($q) {
                $q->where('type', 'mobil')->orWhereNull('type');
            })->orderBy('name')->get();

        $semuaKecamatan = Region::where('type', 'kecamatan')->orderBy('name', 'asc')->get();

        return view('admin.unit.mobil.create', compact('savedLocations', 'categories', 'semuaKecamatan'));
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
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'satuan' => 'required|string',
            'foto_utama' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'tipe_tarif_borongan' => 'nullable|in:jarak,wilayah',
            'harga_dalam_desa' => 'nullable|string',
            'batas_km_dalam_desa' => 'nullable|integer',
            'harga_luar_desa' => 'nullable|string',
            'batas_km_luar_desa' => 'nullable|integer',
            'harga_luar_kota' => 'nullable|string',
            
            'harga_dalam_desa_wilayah' => 'nullable|string',
            'harga_luar_desa_wilayah' => 'nullable|string',
            'tipe_luar_kecamatan_wilayah' => 'nullable|in:pukul_rata,per_kecamatan',
            'harga_luar_kecamatan_wilayah' => 'nullable|string',
            'harga_kecamatan_khusus' => 'nullable|array',
            'harga_kecamatan_khusus.*' => 'nullable|string',
            'bbm_ditanggung' => 'required|string|in:Pengelola,Penyewa',
            
            'nama_supir' => 'nullable|string|max:255',
            'kontak_supir' => 'nullable|string|max:255',
            
            'nama_supir_borongan' => 'nullable|string|max:255',
            'kontak_supir_borongan' => 'nullable|string|max:255',
            'bbm_ditanggung_borongan' => 'required|string|in:Pengelola,Penyewa',
        ]);

        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_sewa);
        $hargaDalamDesa = (int) preg_replace('/[^0-9]/', '', $request->harga_dalam_desa ?? 0);
        $hargaLuarDesa = (int) preg_replace('/[^0-9]/', '', $request->harga_luar_desa ?? 0);
        $hargaLuarKota = (int) preg_replace('/[^0-9]/', '', $request->harga_luar_kota ?? 0);

        if ($request->has('is_borongan_active') && $request->tipe_tarif_borongan === 'jarak' && $hargaDalamDesa <= 0) {
            return back()->withErrors(['harga_dalam_desa' => 'Harga sewa borongan harus lebih dari 0.'])->withInput();
        }

        $tarifBoronganWilayah = null;
        if ($request->tipe_tarif_borongan === 'wilayah') {
            $khususClean = [];
            if ($request->has('harga_kecamatan_khusus')) {
                foreach ($request->harga_kecamatan_khusus as $kecId => $harga) {
                    if ($harga !== null && $harga !== '') {
                        $khususClean[$kecId] = (int) preg_replace('/[^0-9]/', '', $harga);
                    }
                }
            }

            $tarifBoronganWilayah = [
                'harga_dalam_desa' => (int) preg_replace('/[^0-9]/', '', $request->harga_dalam_desa_wilayah ?? 0),
                'harga_luar_desa' => (int) preg_replace('/[^0-9]/', '', $request->harga_luar_desa_wilayah ?? 0),
                'tipe_luar_kecamatan' => $request->tipe_luar_kecamatan_wilayah ?? 'pukul_rata',
                'harga_luar_kecamatan' => (int) preg_replace('/[^0-9]/', '', $request->harga_luar_kecamatan_wilayah ?? 0),
                'harga_kecamatan_khusus' => $khususClean,
            ];
        }

        if (!$request->has('is_harian_active') && !$request->has('is_borongan_active')) {
            return back()->withErrors(['is_harian_active' => 'Minimal salah satu layanan (Sewa Harian atau Sewa Borongan) harus diaktifkan.'])->withInput();
        }

        $data = [
            'nama_mobil' => $request->nama_mobil,
            'deskripsi' => $request->deskripsi,
            'harga_sewa' => $hargaBersih,
            'stok' => $request->stok,
            'status' => $request->status,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'satuan' => $request->satuan,
            'harga_dalam_desa' => $hargaDalamDesa,
            'batas_km_dalam_desa' => $request->batas_km_dalam_desa ?? 0,
            'harga_luar_desa' => $hargaLuarDesa,
            'batas_km_luar_desa' => $request->batas_km_luar_desa ?? 0,
            'harga_luar_kota' => $hargaLuarKota,
            'tipe_tarif_borongan' => $request->tipe_tarif_borongan ?? 'jarak',
            'tarif_borongan_wilayah' => $tarifBoronganWilayah ? json_encode($tarifBoronganWilayah) : null,
            'bbm_ditanggung' => $request->bbm_ditanggung,
            
            'nama_supir' => $request->nama_supir,
            'kontak_supir' => $request->kontak_supir,
            
            'nama_supir_borongan' => $request->nama_supir_borongan,
            'kontak_supir_borongan' => $request->kontak_supir_borongan,
            'bbm_ditanggung_borongan' => $request->bbm_ditanggung_borongan,
            'is_harian_active' => $request->has('is_harian_active') ? 1 : 0,
            'is_borongan_active' => $request->has('is_borongan_active') ? 1 : 0,
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
        $savedLocations = Mobil::select('lokasi', 'latitude', 'longitude')
            ->whereNotNull('lokasi')
            ->where('lokasi', '!=', '')
            ->distinct()
            ->get();
            
        $categories = Category::where('region_id', auth()->user()->region_id)
            ->where(function($q) {
                $q->where('type', 'mobil')->orWhereNull('type');
            })->orderBy('name')->get();

        $semuaKecamatan = Region::where('type', 'kecamatan')->orderBy('name', 'asc')->get();

        return view('admin.unit.mobil.edit', compact('mobil', 'savedLocations', 'categories', 'semuaKecamatan'));
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
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'satuan' => 'required|string',
            'foto_utama' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'tipe_tarif_borongan' => 'nullable|in:jarak,wilayah',
            'harga_dalam_desa' => 'nullable|string',
            'batas_km_dalam_desa' => 'nullable|integer',
            'harga_luar_desa' => 'nullable|string',
            'batas_km_luar_desa' => 'nullable|integer',
            'harga_luar_kota' => 'nullable|string',
            
            'harga_dalam_desa_wilayah' => 'nullable|string',
            'harga_luar_desa_wilayah' => 'nullable|string',
            'tipe_luar_kecamatan_wilayah' => 'nullable|in:pukul_rata,per_kecamatan',
            'harga_luar_kecamatan_wilayah' => 'nullable|string',
            'harga_kecamatan_khusus' => 'nullable|array',
            'harga_kecamatan_khusus.*' => 'nullable|string',
            'bbm_ditanggung' => 'required|string|in:Pengelola,Penyewa',
            
            
            
            
            'bbm_ditanggung_borongan' => 'required|string|in:Pengelola,Penyewa',
        ]);

        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_sewa);
        $hargaDalamDesa = (int) preg_replace('/[^0-9]/', '', $request->harga_dalam_desa ?? 0);
        $hargaLuarDesa = (int) preg_replace('/[^0-9]/', '', $request->harga_luar_desa ?? 0);
        $hargaLuarKota = (int) preg_replace('/[^0-9]/', '', $request->harga_luar_kota ?? 0);
        
        if ($request->has('is_borongan_active') && $request->tipe_tarif_borongan === 'jarak' && $hargaDalamDesa <= 0) {
            return back()->withErrors(['harga_dalam_desa' => 'Harga sewa borongan harus lebih dari 0.'])->withInput();
        }

        $tarifBoronganWilayah = null;
        if ($request->tipe_tarif_borongan === 'wilayah') {
            $khususClean = [];
            if ($request->has('harga_kecamatan_khusus')) {
                foreach ($request->harga_kecamatan_khusus as $kecId => $harga) {
                    if ($harga !== null && $harga !== '') {
                        $khususClean[$kecId] = (int) preg_replace('/[^0-9]/', '', $harga);
                    }
                }
            }

            $tarifBoronganWilayah = [
                'harga_dalam_desa' => (int) preg_replace('/[^0-9]/', '', $request->harga_dalam_desa_wilayah ?? 0),
                'harga_luar_desa' => (int) preg_replace('/[^0-9]/', '', $request->harga_luar_desa_wilayah ?? 0),
                'tipe_luar_kecamatan' => $request->tipe_luar_kecamatan_wilayah ?? 'pukul_rata',
                'harga_luar_kecamatan' => (int) preg_replace('/[^0-9]/', '', $request->harga_luar_kecamatan_wilayah ?? 0),
                'harga_kecamatan_khusus' => $khususClean,
            ];
        }

        if (!$request->has('is_harian_active') && !$request->has('is_borongan_active')) {
            return back()->withErrors(['is_harian_active' => 'Minimal salah satu layanan (Sewa Harian atau Sewa Borongan) harus diaktifkan.'])->withInput();
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
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'satuan' => $request->satuan,
            'harga_dalam_desa' => $hargaDalamDesa,
            'batas_km_dalam_desa' => $request->batas_km_dalam_desa ?? 0,
            'harga_luar_desa' => $hargaLuarDesa,
            'batas_km_luar_desa' => $request->batas_km_luar_desa ?? 0,
            'harga_luar_kota' => $hargaLuarKota,
            'tipe_tarif_borongan' => $request->tipe_tarif_borongan ?? 'jarak',
            'tarif_borongan_wilayah' => $tarifBoronganWilayah ? json_encode($tarifBoronganWilayah) : null,
            'bbm_ditanggung' => $request->bbm_ditanggung,
            
            
            
            
            
            
            
            
            'bbm_ditanggung_borongan' => $request->bbm_ditanggung_borongan,
            'is_harian_active' => $request->has('is_harian_active') ? 1 : 0,
            'is_borongan_active' => $request->has('is_borongan_active') ? 1 : 0,
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
