<form action="{{ route($catalogRoute) }}" method="GET"
      class="catalog-region-filter max-w-5xl mx-auto mb-8 sm:mb-10 px-4">
    @if($targetRegionId ?? null)
        <input type="hidden" name="region_id" value="{{ $targetRegionId }}">
    @endif
    @if(request()->filled('kategori'))
        <input type="hidden" name="kategori" value="{{ request('kategori') }}">
    @endif

    <div class="max-w-4xl mx-auto flex flex-col sm:flex-row justify-center items-stretch sm:items-center gap-2.5 sm:gap-3.5 bg-white/40 backdrop-blur-md p-2.5 sm:p-3.5 rounded-2xl border border-white/50 shadow-md w-full sm:w-fit">
        <!-- Kotak 1: Wilayah Induk Terkunci (Icon Gembok Abu-abu, Tidak Patah Baris) -->
        <div class="h-11 sm:h-12 px-4 text-xs sm:text-sm border border-gray-300 rounded-xl bg-gray-50/90 backdrop-blur-md text-gray-700 font-bold flex items-center justify-center gap-2 cursor-not-allowed shadow-sm whitespace-nowrap flex-shrink-0 select-none" title="Wilayah Induk: Kecamatan Bengkalis">
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
            </svg>
            <span class="whitespace-nowrap">Kecamatan Bengkalis</span>
        </div>

        <!-- Kotak 2: Dropdown Desa / Kelurahan (Lebar Proporsional, Auto-submit) -->
        <div class="relative w-full sm:w-64 sm:max-w-[280px]">
            <select id="catalog-region-filter" name="filter_region_id" onchange="this.blur(); this.form.submit()"
                    class="w-full h-11 sm:h-12 appearance-none pl-3.5 sm:pl-4 pr-9 sm:pr-10 text-xs sm:text-sm border border-gray-300 rounded-xl bg-white/80 backdrop-blur-md text-gray-800 font-bold focus:outline-none focus:border-gray-400 hover:bg-white transition-all shadow-sm">
                <option value="">Semua Kelurahan/Desa</option>
                @foreach($filterRegions as $filterRegion)
                    <option value="{{ $filterRegion->id }}" {{ (string) ($filterRegionId ?? '') === (string) $filterRegion->id ? 'selected' : '' }}>
                        {{ $filterRegion->name ?? $filterRegion->filter_label }}
                    </option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 sm:px-3.5 text-gray-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>

        <!-- Kotak 3: Dropdown Urutkan Produk (Lebar Rapi, Border Abu-abu Seragam) -->
        <div class="relative w-full sm:w-44 sm:max-w-[190px]">
            <select id="catalog-sort-filter" name="sort" onchange="this.blur(); this.form.submit()"
                    class="w-full h-11 sm:h-12 appearance-none pl-3.5 sm:pl-4 pr-9 sm:pr-10 text-xs sm:text-sm border border-gray-300 rounded-xl bg-white/80 backdrop-blur-md text-gray-800 font-bold focus:outline-none focus:border-gray-400 hover:bg-white transition-all shadow-sm">
                <option value="terbaru" {{ request('sort', 'terbaru') == 'terbaru' ? 'selected' : '' }}>Urutkan: Terbaru</option>
                <option value="terlama" {{ request('sort', 'terlama') == 'terlama' ? 'selected' : '' }}>Urutkan: Terlama</option>
                <option value="termurah" {{ request('sort', 'termurah') == 'termurah' ? 'selected' : '' }}>Harga: Terendah</option>
                <option value="termahal" {{ request('sort', 'termahal') == 'termahal' ? 'selected' : '' }}>Harga: Tertinggi</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 sm:px-3.5 text-gray-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    </div>
</form>
