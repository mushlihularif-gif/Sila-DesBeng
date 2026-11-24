@props(['item', 'layout' => 'vertical'])
@php
    $location = \App\Support\ServiceLocation::forItem($item);
    $mapId = 'service-location-'.\Illuminate\Support\Str::uuid();
    $lat = $location->latitude;
    $lng = $location->longitude;
    $hasPoint = is_numeric($lat) && is_numeric($lng) && abs((float) $lat) <= 90 && abs((float) $lng) <= 180;
    $address = trim((string) ($location->alamat ?: $location->nama));
    $regionName = $item->region?->name ?? '';
    $query = $hasPoint ? $lat.','.$lng : trim($address.' '.$regionName);
    $isHorizontal = $layout === 'horizontal';
@endphp
<div class="{{ $isHorizontal ? 'rounded-2xl border border-blue-100 bg-slate-50 p-5 shadow-sm' : 'mt-4 rounded-xl border border-blue-100 bg-white p-4 shadow-sm' }}">
    @if($isHorizontal && $hasPoint)
        <div class="flex flex-col lg:flex-row gap-6 items-stretch">
            <div class="lg:w-5/12 flex flex-col justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-bold text-[#115789] text-base">Lokasi Layanan</h3>
                        @if($regionName)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">{{ $regionName }}</span>
                        @endif
                    </div>
                    @if($location->nama && $location->nama !== $address)
                        <p class="font-semibold text-gray-900 mt-3">{{ $location->nama }}</p>
                    @endif
                    <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $address ?: ($regionName ?: 'Lokasi belum dilengkapi pengelola.') }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 pt-2">
                    <a href="https://www.google.com/maps?q={{ rawurlencode($query) }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white text-[#115789] border border-blue-200 hover:bg-blue-50 transition-colors">
                        Buka Titik Lokasi di Peta
                    </a>
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ rawurlencode($lat.','.$lng) }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 transition-colors shadow-sm">
                        Petunjuk Arah ke Lokasi Layanan
                    </a>
                </div>
            </div>

            <div class="lg:w-7/12">
                <div id="{{ $mapId }}" data-service-map data-lat="{{ $lat }}" data-lng="{{ $lng }}" data-nama="{{ $location->nama }}" class="w-full rounded-xl border border-gray-200 bg-white overflow-hidden" style="height:240px"></div>
            </div>
        </div>
        @push('scripts')
            @include('partials.google-maps-loader')
            <script>
                (() => {
                    const element = document.getElementById(@json($mapId));
                    if (!element) return;
                    if ('IntersectionObserver' in window) {
                        const observer = new IntersectionObserver(entries => {
                            if (entries.some(entry => entry.isIntersecting)) { observer.disconnect(); SegiMaps.display(element); }
                        });
                        observer.observe(element);
                    } else SegiMaps.display(element);
                })();
            </script>
        @endpush
    @else
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h3 class="font-semibold text-[#115789]">Lokasi Layanan</h3>
            @if($regionName)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">{{ $regionName }}</span>
            @endif
        </div>
        @if($location->nama && $location->nama !== $address)<p class="font-semibold mt-2">{{ $location->nama }}</p>@endif
        <p class="text-gray-700 mt-1">{{ $address ?: ($regionName ?: 'Lokasi belum dilengkapi pengelola.') }}</p>
        @if($hasPoint || $address)
            <div class="flex flex-wrap items-center gap-3 mt-3">
                <a href="https://www.google.com/maps?q={{ rawurlencode($query) }}" target="_blank" rel="noopener noreferrer" class="inline-flex text-[#115789] font-semibold underline">{{ $hasPoint ? 'Buka Titik Lokasi di Peta' : 'Cari Alamat di Peta' }}</a>
                @if($hasPoint)
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ rawurlencode($lat.','.$lng) }}" target="_blank" rel="noopener noreferrer" class="inline-flex text-[#115789] font-semibold underline">Petunjuk Arah ke Lokasi Layanan</a>
                @endif
            </div>
            @if($hasPoint)
                <div id="{{ $mapId }}" data-service-map data-lat="{{ $lat }}" data-lng="{{ $lng }}" data-nama="{{ $location->nama }}" class="mt-3 rounded-xl border" style="height:240px"></div>
                @push('scripts')
                    @include('partials.google-maps-loader')
                    <script>
                        (() => {
                            const element = document.getElementById(@json($mapId));
                            if (!element) return;
                            if ('IntersectionObserver' in window) {
                                const observer = new IntersectionObserver(entries => {
                                    if (entries.some(entry => entry.isIntersecting)) { observer.disconnect(); SegiMaps.display(element); }
                                });
                                observer.observe(element);
                            } else SegiMaps.display(element);
                        })();
                    </script>
                @endpush
            @endif
            @unless($hasPoint)<p class="text-sm text-gray-500 mt-2">Titik tepat belum ditentukan. Hasil pencarian alamat perlu dikonfirmasi kepada pengelola.</p>@endunless
        @else
            <p class="text-sm text-gray-500 mt-2">Minta titik lokasi melalui Chat Pengelola.</p>
        @endif
    @endif
</div>
