@props(['item'])
@php
    $location = \App\Support\ServiceLocation::forItem($item);
    $mapId = 'service-location-'.\Illuminate\Support\Str::uuid();
    $lat = $location->latitude;
    $lng = $location->longitude;
    $hasPoint = is_numeric($lat) && is_numeric($lng) && abs((float) $lat) <= 90 && abs((float) $lng) <= 180;
    $address = trim((string) ($location->alamat ?: $location->nama));
    $regionName = $item->region?->name ?? '';
    $query = $hasPoint ? $lat.','.$lng : trim($address.' '.$regionName);
@endphp
<div class="mt-4 rounded-xl border border-blue-100 bg-white p-4">
    <h3 class="font-semibold text-[#115789]">Lokasi Layanan</h3>
    @if($location->nama && $location->nama !== $address)<p class="font-semibold mt-2">{{ $location->nama }}</p>@endif
    <p class="text-gray-700 mt-1">{{ $address ?: ($regionName ?: 'Lokasi belum dilengkapi pengelola.') }}</p>
    @if($hasPoint || $address)
        <a href="https://www.google.com/maps?q={{ rawurlencode($query) }}" target="_blank" rel="noopener noreferrer" class="inline-flex mt-3 text-[#115789] font-semibold underline">{{ $hasPoint ? 'Buka Titik Lokasi di Peta' : 'Cari Alamat di Peta' }}</a>
        @if($hasPoint)
            <a href="https://www.google.com/maps/dir/?api=1&destination={{ rawurlencode($lat.','.$lng) }}" target="_blank" rel="noopener noreferrer" class="inline-flex mt-3 ml-4 text-[#115789] font-semibold underline">Petunjuk Arah ke Lokasi Layanan</a>
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
</div>
