<form action="{{ route($catalogRoute) }}" method="GET"
      class="catalog-region-filter mx-auto mb-8 max-w-3xl rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    @if($targetRegionId ?? null)
        <input type="hidden" name="region_id" value="{{ $targetRegionId }}">
    @endif
    @if(request()->filled('kategori'))
        <input type="hidden" name="kategori" value="{{ request('kategori') }}">
    @endif
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="catalog-region-filter" class="mb-1.5 block text-sm font-bold text-slate-800">
                {{ $filterLabel ?? 'Filter wilayah' }}
            </label>
            <select id="catalog-region-filter" name="filter_region_id"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 focus:border-[#115789] focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Semua desa dan bagian wilayah</option>
                @foreach($filterRegions as $filterRegion)
                    <option value="{{ $filterRegion->id }}" {{ (string) ($filterRegionId ?? '') === (string) $filterRegion->id ? 'selected' : '' }}>
                        {{ $filterRegion->filter_label }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1.5 text-xs text-slate-500">Pilih desa atau bagian wilayah untuk mempersempit daftar layanan.</p>
        </div>
        <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#115789] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#0d4267] focus:outline-none focus:ring-2 focus:ring-blue-300">
            <i class="bx bx-filter-alt text-lg" aria-hidden="true"></i>
            Terapkan Filter
        </button>
    </div>
</form>
