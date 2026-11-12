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

    private function scopedGas()
    {
        $user = auth()->user();
        abort_unless($user && $user->isAdmin() && $user->region_id && $user->hasUnitPermission('gas'), 403);
        return Gas::where('region_id', $user->region_id);
    }

    // ===========================
    // INDEX
    // ===========================
    public function index(Request $request)
    {
        if ($splash = $this->checkDelegation($request, 'gas', 'Penjualan Gas LPG')) {
            return $splash;
        }

        $search = $request->get('search');
        $gases = $this->scopedGas()
            ->when($search, function ($query, $search) {
                return $query->where(fn ($q) => $q->where('jenis_gas', 'LIKE', "%{$search}%")->orWhere('kategori', 'LIKE', "%{$search}%"));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(9)
            ->appends(['search' => $search]);

        // Lokasi layanan ikut dikirim supaya petugas bisa mendaftarkannya
        // langsung dari halaman ini — daftar yang kosong berarti pilihan
        // "Lokasi Tersimpan" di formulir produk juga kosong, dan itu perlu
        // terlihat sebelum ia menekan Tambah Gas.
        $lokasiLayanan = \App\Models\LokasiLayanan::untukWilayah(auth()->user()->region_id);

        $tab = $request->get('tab', 'katalog');
        $admin = auth()->user();
        $chats = \App\Models\UnitChatSession::where('region_id', $admin ? $admin->region_id : null)
            ->where('service_type', 'gas')
            ->with('user')
            ->orderBy('last_message_at', 'desc')
            ->get();
        $totalUnreadChats = $chats->sum('unread_admin_count');

        return view('admin.unit.penjualan_gas.index', compact('gases', 'search', 'chats', 'totalUnreadChats', 'tab', 'lokasiLayanan'));
    }


    // ===========================
    // CREATE
    // ===========================
    public function create()
    {
        $this->scopedGas();
        // Lokasi diambil dari daftar milik WILAYAH, bukan lagi SELECT DISTINCT
        // pada tabel produk unit ini. Query lama tidak menyaring region_id sama
        // sekali, sehingga admin satu desa ikut melihat nama lokasi desa lain;
        // selain itu lokasinya lenyap begitu produk terakhir yang memakainya
        // dihapus, dan koordinatnya harus diketik ulang tiap kali.
        $savedLocations = \App\Models\LokasiLayanan::untukWilayah(auth()->user()->region_id);
            
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
        $this->scopedGas();
        $validated = $request->validate([
            'jenis_gas' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_satuan' => 'required|string|min:0',
            'stok' => 'required|integer|min:0',
            'status' => 'required|in:tersedia,dipesan,rusak',
            'kategori' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'satuan' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
        ]);

        // Bersihkan harga dari karakter non-angka
        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_satuan);
        if ($hargaBersih <= 0 || $hargaBersih > 99999999) {
            return back()->withErrors(['harga_satuan' => 'Harga satuan harus antara Rp 1 dan Rp 99.999.999.'])->withInput();
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

        // Auto-aktifkan layanan Penjualan Gas untuk wilayah ini di region_services
        if ($gas->region_id) {
            $serviceGas = \App\Models\Service::where('slug', 'penjualan-gas')->first();
            if ($serviceGas) {
                \App\Models\RegionService::updateOrInsert(
                    ['region_id' => $gas->region_id, 'service_id' => $serviceGas->id],
                    ['is_active' => true, 'updated_at' => now()]
                );
            }
        }

        // Broadcast produk gas baru ke warga
        \App\Services\NotificationService::broadcastNewProduct('Gas LPG', $gas->jenis_gas, $gas->region_id, route('gas.sales.show', $gas->id));

        return redirect()->route('admin.unit.penjualan_gas.index')->with('success', 'Gas berhasil ditambahkan.');
    }

    // ===========================
    // SHOW
    // ===========================
    public function show($id)
    {
        $gas = $this->scopedGas()->findOrFail($id);
        return view('admin.unit.penjualan_gas.show', compact('gas'));
    }

    // ===========================
    // EDIT
    // ===========================
    public function edit($id)
    {
        $gas = $this->scopedGas()->findOrFail($id);
        // Lokasi diambil dari daftar milik WILAYAH, bukan lagi SELECT DISTINCT
        // pada tabel produk unit ini. Query lama tidak menyaring region_id sama
        // sekali, sehingga admin satu desa ikut melihat nama lokasi desa lain;
        // selain itu lokasinya lenyap begitu produk terakhir yang memakainya
        // dihapus, dan koordinatnya harus diketik ulang tiap kali.
        $savedLocations = \App\Models\LokasiLayanan::untukWilayah(auth()->user()->region_id);
            
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
        $this->scopedGas()->findOrFail($id);
        $validated = $request->validate([
            'jenis_gas' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_satuan' => 'required|string|min:0',
            'stok' => 'required|integer|min:0',
            'original_stock' => 'required|integer|min:0',
            'status' => 'required|in:tersedia,dipesan,rusak',
            'kategori' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'satuan' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
        ]);

        // Bersihkan harga
        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_satuan);
        if ($hargaBersih <= 0 || $hargaBersih > 99999999) {
            return back()->withErrors(['harga_satuan' => 'Harga satuan harus antara Rp 1 dan Rp 99.999.999.'])->withInput();
        }

        // Cari data gas
        $gas = $this->scopedGas()->findOrFail($id);
        $oldStock = $gas->stok;

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

        $newPaths = [];
        $oldPaths = [];
        try {
            foreach (['foto', 'foto_2', 'foto_3'] as $field) {
                if ($request->hasFile($field)) {
                    $newPaths[] = $dataUpdate[$field] = ImageCompressorService::compressAndStore($request->file($field), 'gas');
                } elseif ($request->input('delete_'.$field) == '1') {
                    $dataUpdate[$field] = null;
                }
            }
            \Illuminate\Support\Facades\DB::transaction(function () use ($id, $dataUpdate, $validated, &$gas, &$oldStock, &$oldPaths) {
                $gas = $this->scopedGas()->lockForUpdate()->findOrFail($id);
                if ((int) $gas->stok !== (int) $validated['original_stock']) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['stok' => 'Stok berubah karena pesanan atau petugas lain. Muat ulang halaman sebelum menyimpan.']);
                }
                $oldStock = $gas->stok;
                foreach (['foto', 'foto_2', 'foto_3'] as $field) if (array_key_exists($field, $dataUpdate) && $gas->$field) $oldPaths[] = $gas->$field;
                $gas->update($dataUpdate);
            });
        } catch (\Throwable $e) {
            foreach ($newPaths as $path) if ($path) Storage::disk('public')->delete($path);
            throw $e;
        }
        foreach ($oldPaths as $path) Storage::disk('public')->delete($path);

        // Jika stok bertambah (restock), broadcast notifikasi ke warga
        if ($validated['stok'] > $oldStock) {
            \App\Services\NotificationService::broadcastStockUpdate(
                $gas->jenis_gas,
                $gas->stok,
                $gas->satuan ?? 'tabung',
                $gas->region_id,
                route('gas.sales.show', $gas->id)
            );
        }

        return redirect()->route('admin.unit.penjualan_gas.index')->with('success', 'Gas berhasil diubah.');
    }

    // ===========================
    // DESTROY
    // ===========================
    public function destroy($id)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            $gas = $this->scopedGas()->lockForUpdate()->findOrFail($id);
            abort_if($gas->gasOrders()->withTrashed()->exists(), 409, 'Produk memiliki riwayat pesanan. Ubah status menjadi Tidak Tersedia agar arsip tetap terjaga.');
            $gas->delete();
            \Illuminate\Support\Facades\DB::afterCommit(function () use ($gas) {
                foreach ([$gas->foto, $gas->foto_2, $gas->foto_3] as $path) if ($path) Storage::disk('public')->delete($path);
            });
        });
        return redirect()->route('admin.unit.penjualan_gas.index')->with('success', 'Gas berhasil dihapus.');
    }

    // ===========================
    // CRISIS MODE (Elpiji 3kg)
    // ===========================
    public function updateCrisisSettings(Request $request)
    {
        $this->scopedGas();
        $request->validate([
            'is_crisis_mode' => 'required|boolean',
            'quota_limit' => 'nullable|required_if:is_crisis_mode,1|integer|min:1|max:365',
            'quota_days' => 'nullable|required_if:is_crisis_mode,1|integer|min:1|max:365',
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
        $region->is_gas_crisis = $settings['crisis_mode_gas'];
        $region->save();

        return redirect()->back()->with('success', "Pengaturan Mode Krisis Elpiji berhasil diperbarui: $statusText.");
    }
}
