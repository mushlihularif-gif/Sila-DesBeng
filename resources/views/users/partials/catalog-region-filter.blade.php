<form action="{{ route($catalogRoute) }}" method="GET"
      class="catalog-region-filter mx-auto mb-8 max-w-6xl rounded-2xl border border-slate-100 bg-white/95 px-4 py-4 shadow-lg sm:px-6">
    @if($targetRegionId ?? null)
        <input type="hidden" name="region_id" value="{{ $targetRegionId }}">
    @endif
    @if(request()->filled('kategori'))
        <input type="hidden" name="kategori" value="{{ request('kategori') }}">
    @endif
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-center">
        <div class="w-full sm:max-w-2xl sm:flex-1">
            <select id="catalog-region-filter" name="filter_region_id"
                    aria-label="{{ $filterLabel ?? 'Filter wilayah' }}"
                    class="w-full rounded-xl border border-slate-300 bg-white px-5 py-3.5 text-base font-semibold text-slate-700 shadow-sm focus:border-[#115789] focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">{{ $filterLabel ?? 'Filter wilayah' }}: Semua desa dan bagian wilayah</option>
                @foreach($filterRegions as $filterRegion)
                    <option value="{{ $filterRegion->id }}" {{ (string) ($filterRegionId ?? '') === (string) $filterRegion->id ? 'selected' : '' }}>
                        {{ $filterRegion->filter_label }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#115789] px-7 py-3 text-base font-bold text-white shadow-md transition hover:bg-[#0d4267] focus:outline-none focus:ring-2 focus:ring-blue-300">
            <i class="bx bx-filter-alt text-lg" aria-hidden="true"></i>
            Terapkan Filter
        </button>
    </div>
</form>
