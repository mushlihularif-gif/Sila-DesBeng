<?php
$content = file_get_contents('app/Http/Controllers/User/BerandaController.php');

// Replace the old search block with the new performGlobalSearch call
$oldSearchStart = "        // Handle Search\n        if (\$search) {\n            // Search Rental Items";
$oldSearchEnd = "        // Dapatkan tahun yang dipilih (default ke tahun sekarang)";

// We need to use regex because the exact spaces and newlines might vary
$pattern = '/\s*\/\/ Handle Search\s*if \(\$search\) \{.*?\/\/ Gabungkan Semua Hasil\s*\$searchResults = [^\}]*?\}?\s*\/\/ Dapatkan tahun yang dipilih/s';

$newSearchBlock = <<<PHP
        // Handle Search
        if (\$search) {
            \$searchResults = \$this->performGlobalSearch(\$search);
        }

        // Dapatkan tahun yang dipilih
PHP;

$content = preg_replace($pattern, $newSearchBlock, $content);

// Add the new methods before the last }
$newMethods = <<<PHP

    /**
     * API for Live Search Dropdown
     */
    public function liveSearch(Illuminate\Http\Request \$request)
    {
        \$search = \$request->input('search');
        
        if (!\$search || strlen(\$search) < 2) {
            return response()->json([]);
        }

        \$results = \$this->performGlobalSearch(\$search);
        
        // Limit total results for dropdown to keep it clean
        \$results = collect(\$results)->take(8)->values();

        // Format image URLs for frontend
        \$results = \$results->map(function (\$item) {
            \$imageUrl = Illuminate\Support\Str::startsWith(\$item->image, ['http', 'https', 'User', 'Admin']) 
                        ? asset(\$item->image) 
                        : asset('storage/' . \$item->image);
            \$item->image_url = \$imageUrl;
            return \$item;
        });

        return response()->json(\$results);
    }

    /**
     * Perform global search across all modules
     */
    private function performGlobalSearch(\$search)
    {
        // Search Rental Items
        \$rentalResults = \App\Models\Barang::searchWhereLike(['nama_barang', 'kategori'], \$search)
            ->get()
            ->map(function (\$item) {
                return (object) [
                    'id' => \$item->id,
                    'name' => \$item->nama_barang,
                    'image' => \$item->foto,
                    'price' => \$item->harga_sewa,
                    'price_formatted' => 'Rp ' . number_format(\$item->harga_sewa, 0, ',', '.'),
                    'stock' => \$item->stok,
                    'type' => 'rental',
                    'category' => 'Unit Penyewaan Alat',
                    'real_category' => \$item->kategori,
                    'unit' => \$item->satuan ?? 'unit',
                    'link' => route('rental.equipment.show', \$item->id)
                ];
            });

        // Search Gas Items
        \$gasResults = \App\Models\Gas::searchWhereLike(['jenis_gas', 'kategori'], \$search)
            ->get()
            ->map(function (\$item) {
                return (object) [
                    'id' => \$item->id,
                    'name' => \$item->jenis_gas,
                    'image' => \$item->foto,
                    'price' => \$item->harga_satuan,
                    'price_formatted' => 'Rp ' . number_format(\$item->harga_satuan, 0, ',', '.'),
                    'stock' => \$item->stok,
                    'type' => 'gas',
                    'category' => 'Unit Penjualan Gas',
                    'real_category' => 'Gas',
                    'unit' => 'tabung',
                    'link' => route('gas.sales.show', \$item->id)
                ];
            });

        // Search Mobil Items
        \$mobilResults = \App\Models\Mobil::searchWhereLike(['nama_mobil', 'kategori'], \$search)
            ->get()
            ->map(function (\$item) {
                return (object) [
                    'id' => \$item->id,
                    'name' => \$item->nama_mobil,
                    'image' => \$item->foto,
                    'price' => \$item->harga_sewa,
                    'price_formatted' => 'Rp ' . number_format(\$item->harga_sewa, 0, ',', '.'),
                    'stock' => \$item->stok,
                    'type' => 'mobil',
                    'category' => 'Unit Penyewaan Mobil',
                    'real_category' => \$item->kategori,
                    'unit' => \$item->satuan ?? 'hari',
                    'link' => route('mobil.rental.show', \$item->id)
                ];
            });

        // Search Fasilitas Umum Items
        \$fasilitasResults = \App\Models\FasilitasUmum::searchWhereLike(['nama_fasilitas', 'kategori'], \$search)
            ->get()
            ->map(function (\$item) {
                return (object) [
                    'id' => \$item->id,
                    'name' => \$item->nama_fasilitas,
                    'image' => \$item->foto,
                    'price' => 0,
                    'price_formatted' => 'Peminjaman',
                    'stock' => \$item->stok,
                    'type' => 'fasilitas',
                    'category' => 'Fasilitas Umum',
                    'real_category' => \$item->kategori,
                    'unit' => 'kegiatan',
                    'link' => route('fasilitas.show', \$item->id)
                ];
            });

        // Search BUMDes Members
        \$bumdesResults = \App\Models\BumdesMember::searchWhereLike(['name', 'position'], \$search)
            ->get()
            ->map(function (\$item) {
                \$photoUrl = \$item->photo ? \$item->photo : 'User/img/avatars/logodomain.png';
                return (object) [
                    'id' => \$item->id,
                    'name' => \$item->name,
                    'image' => \$photoUrl,
                    'price' => 0,
                    'price_formatted' => \$item->position,
                    'stock' => 0,
                    'type' => 'profile',
                    'category' => 'Profil BUMDes',
                    'real_category' => 'Personil',
                    'unit' => '',
                    'link' => route('bumdes.detail')
                ];
            });

        // Search Static Developers
        \$developers = [
            [
                'name' => 'Rizqy Hamadi Ken',
                'image' => 'User/img/avatars/ken.png',
                'position' => 'Pengembang SiladesBeng',
                'link' => '#'
            ],
            [
                'name' => 'Mushlihul Arif',
                'image' => 'User/img/avatars/ayep123.jpg',
                'position' => 'Pengembang SiladesBeng',
                'link' => '#'
            ],
            [
                'name' => 'Dicki Wahyudi',
                'image' => 'User/img/avatars/dicki.png',
                'position' => 'Pengembang SiladesBeng',
                'link' => '#'
            ]
        ];

        \$developerResults = collect(\$developers)->filter(function (\$dev) use (\$search) {
            \$cleanSearch = strtolower(str_replace(['desa ', 'kelurahan ', 'kecamatan ', 'kabupaten '], '', strtolower(\$search)));
            return Illuminate\Support\Str::contains(strtolower(\$dev['name']), \$cleanSearch) || 
                   Illuminate\Support\Str::contains(strtolower(\$dev['position']), \$cleanSearch);
        })->map(function (\$dev) {
            return (object) [
                'id' => 'dev-' . Illuminate\Support\Str::slug(\$dev['name']),
                'name' => \$dev['name'],
                'image' => \$dev['image'],
                'price' => 0,
                'price_formatted' => \$dev['position'],
                'stock' => 0,
                'type' => 'developer',
                'category' => 'Tim SiladesBeng',
                'real_category' => 'Pengembang',
                'unit' => '',
                'link' => \$dev['link']
            ];
        });

        // Search Region
        \$regionResults = \App\Models\Region::searchWhereLike(['name', 'type', 'profile_text'], \$search)
            ->get()
            ->map(function (\$item) {
                return (object) [
                    'id' => \$item->id,
                    'name' => \$item->name,
                    'image' => 'User/img/elemen/tugu.png',
                    'price' => 0,
                    'price_formatted' => \$item->contact_phone ?? 'Wilayah Administratif',
                    'stock' => 0,
                    'type' => 'region',
                    'category' => 'Wilayah / Desa',
                    'real_category' => 'Wilayah',
                    'unit' => '',
                    'link' => '#'
                ];
            });

        return collect([])
            ->concat(\$rentalResults)
            ->concat(\$gasResults)
            ->concat(\$mobilResults)
            ->concat(\$fasilitasResults)
            ->concat(\$bumdesResults)
            ->concat(\$regionResults)
            ->concat(\$developerResults)
            ->values()
            ->toArray();
    }
PHP;

$content = preg_replace('/\}\s*$/s', $newMethods . "\n}", $content);

file_put_contents('app/Http/Controllers/User/BerandaController.php', $content);
echo "Rebuild successful.\n";
