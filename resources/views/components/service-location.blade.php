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

@once
<style>
    .segi-service-map-wrap > a { display: none !important; }
    .segi-service-map-wrap > p[data-map-status] { margin: 8px 0 0 !important; font-size: 12px !important; color: #64748b !important; }
</style>
@endonce

<div class="rounded-2xl border border-blue-100 bg-slate-50 p-4 sm:p-5 shadow-sm {{ $isHorizontal ? '' : 'mt-4' }}">
    @if($isHorizontal && $hasPoint)
        <div class="flex flex-col lg:flex-row gap-5 items-stretch">
            <div class="lg:w-5/12 flex flex-col justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-bold text-[#115789] text-base flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Lokasi Layanan</span>
                        </h3>
                        @if($regionName)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">{{ $regionName }}</span>
                        @endif
                    </div>

                    <div class="mt-3 rounded-xl border border-blue-100 bg-white px-4 py-3">
                        @if($location->nama && $location->nama !== $address)
                            <p class="font-bold text-gray-900 text-sm mb-1">{{ $location->nama }}</p>
                        @endif
                        <p class="text-sm text-gray-600 leading-relaxed mb-0">{{ $address ?: ($regionName ?: 'Lokasi belum dilengkapi pengelola.') }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="https://www.google.com/maps?q={{ rawurlencode($query) }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-white text-blue-600 border border-blue-600 hover:bg-blue-50 transition-colors shadow-sm">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Buka Titik Lokasi di Peta</span>
                    </a>
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ rawurlencode($lat.','.$lng) }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors shadow-sm">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span>Petunjuk Arah</span>
                    </a>
                </div>
            </div>

            <div class="lg:w-7/12 segi-service-map-wrap">
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
            <h3 class="font-bold text-[#115789] text-sm sm:text-base flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Lokasi Layanan</span>
            </h3>
            @if($regionName)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">{{ $regionName }}</span>
            @endif
        </div>

        <div class="mt-3 rounded-xl border border-blue-100 bg-white px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                @if($location->nama && $location->nama !== $address)
                    <p class="font-bold text-gray-900 text-sm mb-0.5">{{ $location->nama }}</p>
                @endif
                <p class="text-sm text-gray-600 mb-0">{{ $address ?: ($regionName ?: 'Lokasi belum dilengkapi pengelola.') }}</p>
            </div>

            @if($hasPoint || $address)
                <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                    <a href="https://www.google.com/maps?q={{ rawurlencode($query) }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-white text-blue-600 border border-blue-600 hover:bg-blue-50 transition-colors shadow-sm">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>{{ $hasPoint ? 'Buka Titik Lokasi di Peta' : 'Cari Alamat di Peta' }}</span>
                    </a>
                    @if($hasPoint)
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ rawurlencode($lat.','.$lng) }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors shadow-sm">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <span>Petunjuk Arah</span>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if($hasPoint || $address)
            @if($hasPoint)
                <div class="mt-3 segi-service-map-wrap">
                    <div id="{{ $mapId }}" data-service-map data-lat="{{ $lat }}" data-lng="{{ $lng }}" data-nama="{{ $location->nama }}" class="w-full rounded-xl border border-gray-200 bg-white overflow-hidden" style="height:240px"></div>
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
            @endif
            @unless($hasPoint)<p class="text-xs text-gray-500 mt-2 mb-0">Titik tepat belum ditentukan. Hasil pencarian alamat perlu dikonfirmasi kepada pengelola.</p>@endunless
        @else
            <p class="text-xs text-gray-500 mt-2 mb-0">Minta titik lokasi melalui Chat Pengelola.</p>
        @endif
    @endif
</div>
