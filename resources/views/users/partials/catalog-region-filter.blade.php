<form action="{{ route($catalogRoute) }}" method="GET"
      class="catalog-region-filter max-w-5xl mx-auto mb-8 sm:mb-10 px-4">
    @if($targetRegionId ?? null)
        <input type="hidden" name="region_id" value="{{ $targetRegionId }}">
    @endif
    @if(request()->filled('kategori'))
        <input type="hidden" name="kategori" value="{{ request('kategori') }}">
    @endif

    <div class="flex flex-col sm:flex-row justify-center items-stretch sm:items-center gap-2 sm:gap-4 bg-white/40 backdrop-blur-md p-2.5 sm:p-4 rounded-2xl border border-white/50 shadow-lg w-full">
        <!-- Kotak 1: Wilayah Induk Terkunci (Icon Gembok Abu-abu, Tidak Bisa Diklik) -->
        <div class="w-full sm:w-auto px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm border border-gray-300 rounded-xl bg-gray-50/90 backdrop-blur-md text-gray-700 font-bold flex items-center justify-center gap-2 cursor-not-allowed shadow-sm sm:min-w-[190px] select-none" title="Wilayah Induk: Kecamatan Bengkalis">
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
            </svg>
            <span>Kecamatan Bengkalis</span>
        </div>

        <!-- Kotak 2: Dropdown Desa / Kelurahan (Auto-submit saat dipilih) -->
        <div class="relative w-full sm:w-auto sm:min-w-[220px]">
            <select id="catalog-region-filter" name="filter_region_id" onchange="this.form.submit()"
                    class="w-full appearance-none px-3 sm:px-4 py-2 sm:py-3 pr-8 sm:pr-10 text-xs sm:text-sm border border-gray-300 rounded-xl bg-white/80 backdrop-blur-md text-gray-800 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 hover:bg-white transition-all shadow-sm">
                <option value="">Semua Kelurahan/Desa</option>
                @foreach($filterRegions as $filterRegion)
                    <option value="{{ $filterRegion->id }}" {{ (string) ($filterRegionId ?? '') === (string) $filterRegion->id ? 'selected' : '' }}>
                        {{ $filterRegion->filter_label }}
                    </option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 sm:px-4 text-gray-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>

        <!-- Kotak 3: Dropdown Urutkan Produk (Terbaru, Terlama, Termurah, Termahal) -->
        <div class="relative w-full sm:w-auto sm:min-w-[170px]">
            <select id="catalog-sort-filter" name="sort" onchange="this.form.submit()"
                    class="w-full appearance-none px-3 sm:px-4 py-2 sm:py-3 pr-8 sm:pr-10 text-xs sm:text-sm border border-gray-300 rounded-xl bg-white/80 backdrop-blur-md text-gray-800 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 hover:bg-white transition-all shadow-sm">
                <option value="terbaru" {{ request('sort', 'terbaru') == 'terbaru' ? 'selected' : '' }}>Urutkan: Terbaru</option>
                <option value="terlama" {{ request('sort') == 'terlama' ? 'selected' : '' }}>Urutkan: Terlama</option>
                <option value="termurah" {{ request('sort') == 'termurah' ? 'selected' : '' }}>Harga: Terendah</option>
                <option value="termahal" {{ request('sort') == 'termahal' ? 'selected' : '' }}>Harga: Tertinggi</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 sm:px-4 text-gray-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    </div>
</form>
