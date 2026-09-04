<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Gas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageCompressorService;

class GasController extends Controller
{
    use \App\Traits\ChecksStaffDelegation;

    // ===========================
    // INDEX
    // ===========================
    public function index(Request $request)
    {
        if ($splash = $this->checkDelegation($request, 'gas', 'Penjualan Gas LPG')) {
            return $splash;
        }

        $search = $request->get('search');
        $gases = Gas::query()
            ->when($search, function ($query, $search) {
                return $query->where('jenis_gas', 'LIKE', "%{$search}%")
                           ->orWhere('kategori', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(9)
            ->appends(['search' => $search]);

        $tab = $request->get('tab', 'katalog');
        $admin = auth()->user();
        $chats = \App\Models\UnitChatSession::where('region_id', $admin ? $admin->region_id : null)
            ->where('service_type', 'gas')
            ->with('user')
            ->orderBy('last_message_at', 'desc')
            ->get();
        $totalUnreadChats = $chats->sum('unread_admin_count');

        return view('admin.unit.penjualan_gas.index', compact('gases', 'search', 'chats', 'totalUnreadChats', 'tab'));
    }


    // ===========================
    // CREATE
    // ===========================
    public function create()
    {
        $savedLocations = Gas::select('lokasi', 'latitude', 'longitude')
            ->whereNotNull('lokasi')
            ->where('lokasi', '!=', '')
            ->distinct()
            ->get();
            
        $categories = Category::where('region_id', auth()->user()->region_id)
            ->where(function($q) {
                $q->where('type', 'gas')->orWhereNull('type');
            })->orderBy('name')->get();

        return view('admin.unit.penjualan_gas.create', compact('savedLocations', 'categories'));
    }

    // ===========================
    // STORE
    // ===========================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_gas' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_satuan' => 'required|string|min:0',
            'stok' => 'required|integer|min:0',
            'status' => 'required|in:tersedia,dipesan,rusak',
            'kategori' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'satuan' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
        ]);

        // Bersihkan harga dari karakter non-angka
        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_satuan);
        if ($hargaBersih <= 0) {
            return back()->withErrors(['harga_satuan' => 'Harga satuan harus angka valid dan lebih dari 0.'])->withInput();
        }

        $gas = new Gas();
        $gas->jenis_gas = $validated['jenis_gas'];
        $gas->deskripsi = $validated['deskripsi'];
        $gas->harga_satuan = $hargaBersih;
        $gas->stok = $validated['stok'];
        $gas->status = $validated['status'];
        $gas->kategori = $validated['kategori'];
        $gas->lokasi = $validated['lokasi'];
        $gas->latitude = $validated['latitude'] ?? null;
        $gas->longitude = $validated['longitude'] ?? null;
        $gas->satuan = $validated['satuan'];
        $gas->region_id = auth()->user()->region_id;

        if ($request->hasFile('foto')) {
            $gas->foto = ImageCompressorService::compressAndStore($request->file('foto'), 'gas');
        }
        if ($request->hasFile('foto_2')) {
            $gas->foto_2 = ImageCompressorService::compressAndStore($request->file('foto_2'), 'gas');
        }
        if ($request->hasFile('foto_3')) {
            $gas->foto_3 = ImageCompressorService::compressAndStore($request->file('foto_3'), 'gas');
        }

        $gas->save();

        return redirect()->route('admin.unit.penjualan_gas.index')->with('success', 'Gas berhasil ditambahkan.');
    }

    // ===========================
    // SHOW
    // ===========================
    public function show($id)
    {
        $gas = Gas::findOrFail($id);
        return view('admin.unit.penjualan_gas.show', compact('gas'));
    }

    // ===========================
    // EDIT
    // ===========================
    public function edit($id)
    {
        $gas = Gas::findOrFail($id);
        $savedLocations = Gas::select('lokasi', 'latitude', 'longitude')
            ->whereNotNull('lokasi')
            ->where('lokasi', '!=', '')
            ->distinct()
            ->get();
            
        $categories = Category::where('region_id', auth()->user()->region_id)
            ->where(function($q) {
                $q->where('type', 'gas')->orWhereNull('type');
            })->orderBy('name')->get();

        return view('admin.unit.penjualan_gas.edit', compact('gas', 'savedLocations', 'categories'));
    }

    // ===========================
    // UPDATE
    // ===========================
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'jenis_gas' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_satuan' => 'required|string|min:0',
            'stok' => 'required|integer|min:0',
            'status' => 'required|in:tersedia,dipesan,rusak',
            'kategori' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'satuan' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
        ]);

        // Bersihkan harga
        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_satuan);
        if ($hargaBersih <= 0) {
            return back()->withErrors(['harga_satuan' => 'Harga satuan harus angka valid dan lebih dari 0.'])->withInput();
        }

        // Cari data gas
        $gas = Gas::findOrFail($id);

        // Siapkan data untuk update
        $dataUpdate = [
            'jenis_gas' => $validated['jenis_gas'],
            'deskripsi' => $validated['deskripsi'],
            'harga_satuan' => $hargaBersih,
            'stok' => $validated['stok'],
            'status' => $validated['status'],
            'kategori' => $validated['kategori'],
            'lokasi' => $validated['lokasi'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'satuan' => $validated['satuan'],
        ];

        // Foto Utama
        if ($request->hasFile('foto')) {
            if ($gas->foto)
                Storage::disk('public')->delete($gas->foto);
            $dataUpdate['foto'] = ImageCompressorService::compressAndStore($request->file('foto'), 'gas');
        } elseif ($request->input('delete_foto') == '1') {
            if ($gas->foto)
                Storage::disk('public')->delete($gas->foto);
            $dataUpdate['foto'] = null;
        }

        // Foto 2
        if ($request->hasFile('foto_2')) {
            if ($gas->foto_2)
                Storage::disk('public')->delete($gas->foto_2);
            $dataUpdate['foto_2'] = ImageCompressorService::compressAndStore($request->file('foto_2'), 'gas');
        } elseif ($request->input('delete_foto_2') == '1') {
            if ($gas->foto_2)
                Storage::disk('public')->delete($gas->foto_2);
            $dataUpdate['foto_2'] = null;
        }

        // Foto 3
        if ($request->hasFile('foto_3')) {
            if ($gas->foto_3)
                Storage::disk('public')->delete($gas->foto_3);
            $dataUpdate['foto_3'] = ImageCompressorService::compressAndStore($request->file('foto_3'), 'gas');
        } elseif ($request->input('delete_foto_3') == '1') {
            if ($gas->foto_3)
                Storage::disk('public')->delete($gas->foto_3);
            $dataUpdate['foto_3'] = null;
        }

        // Eksekusi Update Satu Kali
        $gas->update($dataUpdate);

        return redirect()->route('admin.unit.penjualan_gas.index')->with('success', 'Gas berhasil diubah.');
    }

    // ===========================
    // DESTROY
    // ===========================
    public function destroy($id)
    {
        try {
            $gas = Gas::findOrFail($id);

            if ($gas->foto)
                Storage::disk('public')->delete($gas->foto);
            if ($gas->foto_2)
                Storage::disk('public')->delete($gas->foto_2);
            if ($gas->foto_3)
                Storage::disk('public')->delete($gas->foto_3);

            $gas->delete();

            return redirect()->route('admin.unit.penjualan_gas.index')->with('success', 'Gas berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.unit.penjualan_gas.index')->with('error', 'Gagal menghapus gas: ' . $e->getMessage());
        }
    }

    // ===========================
    // CRISIS MODE (Elpiji 3kg)
    // ===========================
    public function updateCrisisSettings(Request $request)
    {
        $request->validate([
            'is_crisis_mode' => 'required|boolean',
            'quota_limit' => 'required_if:is_crisis_mode,1|integer|min:1',
            'quota_days' => 'required_if:is_crisis_mode,1|integer|min:1',
        ]);

        $admin = auth()->user();
        $region = \App\Models\Region::find($admin->region_id);
        
        if (!$region) {
            return redirect()->back()->with('error', 'Wilayah tidak ditemukan.');
        }

        $settings = $region->settings ?? [];
        
        // Update status
        $settings['crisis_mode_gas'] = (bool) $request->is_crisis_mode;
        
        if ($settings['crisis_mode_gas']) {
            $settings['gas_quota_limit'] = (int) $request->quota_limit;
            $settings['gas_quota_days'] = (int) $request->quota_days;
            $statusText = "DIAKTIFKAN (Batas {$settings['gas_quota_limit']} Tabung per {$settings['gas_quota_days']} Hari)";
        } else {
            $statusText = "DINONAKTIFKAN (Kuota Normal / Bebas Tanpa Batas)";
        }
        
        $region->settings = $settings;
        $region->save();

        return redirect()->back()->with('success', "Pengaturan Mode Krisis Elpiji berhasil diperbarui: $statusText.");
    }
}
