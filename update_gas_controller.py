import codecs

filepath = "D:/laragon/www/SilaDesBeng/app/Http/Controllers/User/GasSalesUserController.php"
with codecs.open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

old_index = """    public function index()
    {
        $kategori = request('kategori', '');

        $query = Gas::where('status', '!=', 'rusak');

        // Validasi: Warga hanya bisa melihat gas dari desa/wilayahnya sendiri
        if (auth()->check() && auth()->user()->role === 'user') {
            $query->where('region_id', auth()->user()->region_id);
        }

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        // Statistik
        $stats = [
            'total_produk'   => Gas::where('status', '!=', 'rusak')->count(),
            'total_stok'     => Gas::where('status', '!=', 'rusak')->sum('stok'),
            'total_transaksi'=> GasOrder::count(),
            'selesai'        => GasOrder::where('status', 'completed')->orWhere('status', 'selesai')->count(),
        ];

        return view('users.gas-sales', compact('items', 'kategori', 'stats'));
    }"""

new_index = """    public function index()
    {
        $kategori = request('kategori', '');
        $query = Gas::where('status', '!=', 'rusak');
        
        $isGasCrisis = false;
        $hasKk = false;
        $familyCardNumber = null;
        $pendingKk = false;

        // Validasi: Warga hanya bisa melihat gas dari desa/wilayahnya sendiri
        if (auth()->check() && auth()->user()->role === 'user') {
            $user = auth()->user();
            $query->where('region_id', $user->region_id);
            
            // Cek mode krisis gas
            $region = \\App\\Models\\Region::find($user->region_id);
            if ($region && $region->is_gas_crisis) {
                $isGasCrisis = true;
                
                // Cek apakah user punya KK terverifikasi
                if ($user->familyMember && $user->familyMember->familyCard) {
                    $hasKk = true;
                    $familyCardNumber = $user->familyMember->familyCard->no_kk_masked;
                } else {
                    // Cek apakah ada pengajuan KK yang pending
                    $pending = \\App\\Models\\FamilyCard::where('submitted_by', $user->id)
                        ->where('status', 'pending')->first();
                    if ($pending) {
                        $pendingKk = true;
                    }
                }
            }
        }

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        // Statistik
        $stats = [
            'total_produk'   => Gas::where('status', '!=', 'rusak')->count(),
            'total_stok'     => Gas::where('status', '!=', 'rusak')->sum('stok'),
            'total_transaksi'=> GasOrder::count(),
            'selesai'        => GasOrder::where('status', 'completed')->orWhere('status', 'selesai')->count(),
        ];

        return view('users.gas-sales', compact('items', 'kategori', 'stats', 'isGasCrisis', 'hasKk', 'familyCardNumber', 'pendingKk'));
    }"""

content = content.replace(old_index, new_index)

with codecs.open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Controller updated successfully.")
