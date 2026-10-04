@extends('layouts.user')

@section('page')
    {{-- NAVIGASI --}}

    <!-- Bagian Carousel -->
    @push('styles')
    <style>
        /* Styling untuk layer sinkron di belakang navbar */
        #navbar-blur-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: var(--nav-height, 96px);
            z-index: 40;
            overflow: hidden;
            transform: translateZ(0); /* HW acceleration */
            transition: transform 0.3s ease-in-out;
            pointer-events: none;
            will-change: transform;
        }
        body:has(#master-navbar.hidden-nav) #navbar-blur-bg {
            transform: translateY(-100%) translateZ(0);
        }
        .blur-slide {
            width: 100%;
            min-width: 100%;
            height: 500px;
            max-height: 60vh;
            min-height: 250px;
            background-size: cover;
            background-position: center;
            flex-shrink: 0;
        }

        #beranda {
            padding-top: var(--nav-height, 96px);
            transition: padding-top 0.3s ease-in-out;
            will-change: padding-top, scroll-position;
        }
        body:has(#master-navbar.hidden-nav) #beranda {
            padding-top: 0 !important;
        }
    </style>
    @endpush

    {{-- UTAMA --}}<main class="flex-grow relative w-full overflow-hidden">
        @include('partials.abstract-bg')

        <!-- Layer khusus untuk efek blur di belakang navbar (hanya di paling atas) -->
        <div id="navbar-blur-bg">
            <div id="blur-carousel-slides" class="flex transition-transform duration-500 ease-out h-full w-full"></div>
        </div>

        {{-- BAGIAN BERANDA --}}<section id="beranda" class="relative z-10">
            <div class="w-full mx-auto">
                <div class="relative overflow-hidden group">
                    <!-- Wadah Slide -->
                    <div id="carousel-slides" class="flex transition-transform duration-500 ease-out w-full">
                        <!-- Default Slides (Terkunci 2 Slide Bawaan Sistem) -->
                        <div class="carousel-slide w-full min-w-full flex-shrink-0 relative">
                            <img src="{{ asset('User/img/slidebanner/kuncislide1r.png') }}?v={{ time() }}" class="w-full h-auto object-contain object-top" style="max-height: 500px;" alt="Slide 1">
                        </div>
                        <div class="carousel-slide w-full min-w-full flex-shrink-0 relative">
                            <img src="{{ asset('User/img/slidebanner/kuncislide2r.png') }}?v={{ time() }}" class="w-full h-auto object-contain object-top" style="max-height: 500px;" alt="Slide 2">
                        </div>
                    </div>

                    <!-- Indicators (Terkunci 2 Slide) -->
                    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2.5 z-10">
                        @for($i = 0; $i < 2; $i++)
                        <button
                            class="carousel-indicator {{ $i == 0 ? 'w-8 h-2.5 bg-white' : 'w-2.5 h-2.5 bg-white/50 hover:bg-white/75' }} rounded-full shadow-md transition-all duration-300"
                            data-slide="{{ $i }}"></button>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Bilah Pencarian Modern & Live AJAX Search -->
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 py-6 sm:py-10">
                <div class="max-w-2xl mx-auto">
                    <form id="live-search-form" action="{{ route('beranda') }}" method="GET" class="relative group">
                        <!-- Gradient Border ("Warna Kita") -->
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 via-sky-400 to-amber-400 rounded-full opacity-80 group-hover:opacity-100 group-focus-within:opacity-100 transition-opacity duration-300 shadow-sm"></div>

                        <!-- Search Input Pill Container -->
                        <div class="relative flex items-center bg-white rounded-full p-0.5 sm:p-1.5 shadow-sm">
                            <div class="pl-3 sm:pl-5 pr-1.5 sm:pr-2 text-gray-400 flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-[#115789]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <input type="text" id="live-search-input" name="search" value="{{ $search ?? '' }}" 
                                placeholder="Cari Gas 3kg, Mobil Pick Up, Tenda, Kursi..." autocomplete="off"
                                class="flex-1 min-w-0 py-2 sm:py-3 px-1.5 sm:px-2 text-gray-800 text-xs sm:text-base font-medium placeholder-gray-400 focus:outline-none bg-transparent">

                            <!-- Loading Spinner -->
                            <div id="search-spinner" class="hidden pr-3 text-blue-600 animate-spin">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                            </div>

                            <!-- Clear Button -->
                            <button type="button" id="search-clear-btn" class="{{ (!empty($search)) ? '' : 'hidden' }} px-2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer" title="Hapus pencarian">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <!-- Search Submit Button (Solid Biru, Tanpa Gradasi) -->
                            <button type="submit" id="search-submit-btn"
                                class="flex-shrink-0 px-4 sm:px-8 py-2 sm:py-3 rounded-full text-white font-bold text-[11px] sm:text-sm shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer flex items-center justify-center gap-1.5 ml-1 bg-[#2563eb] hover:bg-[#1d4ed8] active:scale-95" style="background-color: #2563eb !important; color: #ffffff !important;">
                                <span>Cari</span>
                            </button>
                        </div>
                    </form>

                    <!-- Quick Popular Search Chips (Live AJAX Triggers) -->
                    <div class="mt-3 sm:mt-7 flex items-center justify-center gap-1.5 sm:gap-2.5 flex-wrap text-xs text-gray-500">
                        <span class="text-[10px] sm:text-[12px] text-gray-500 font-semibold mr-0.5 sm:mr-1">Sering dicari:</span>
                        <button type="button" data-query="Gas 3kg" class="popular-search-chip px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-full bg-white hover:bg-blue-50 text-gray-700 hover:text-[#115789] border border-gray-200 shadow-xs hover:border-blue-300 hover:shadow-sm transition-all text-[10px] sm:text-xs font-medium cursor-pointer">Gas 3kg</button>
                        <button type="button" data-query="Mobil Pick Up" class="popular-search-chip px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-full bg-white hover:bg-blue-50 text-gray-700 hover:text-[#115789] border border-gray-200 shadow-xs hover:border-blue-300 hover:shadow-sm transition-all text-[10px] sm:text-xs font-medium cursor-pointer">Mobil Pick Up</button>
                        <button type="button" data-query="Tenda" class="popular-search-chip px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-full bg-white hover:bg-blue-50 text-gray-700 hover:text-[#115789] border border-gray-200 shadow-xs hover:border-blue-300 hover:shadow-sm transition-all text-[10px] sm:text-xs font-medium cursor-pointer">Tenda Acara</button>
                        <button type="button" data-query="Kursi" class="popular-search-chip px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-full bg-white hover:bg-blue-50 text-gray-700 hover:text-[#115789] border border-gray-200 shadow-xs hover:border-blue-300 hover:shadow-sm transition-all text-[10px] sm:text-xs font-medium cursor-pointer">Kursi Lipat</button>
                        <button type="button" data-query="Pasar" class="popular-search-chip px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-full bg-white hover:bg-blue-50 text-gray-700 hover:text-[#115789] border border-gray-200 shadow-xs hover:border-blue-300 hover:shadow-sm transition-all text-[10px] sm:text-xs font-medium cursor-pointer">Pasar Daerah</button>
                    </div>
                </div>
            </div>

            <!-- Search Results Section (Supports both server-side initial render and live AJAX updates) -->
            <div id="search-results-section" class="{{ (isset($search) && !empty($search)) ? '' : 'hidden' }} max-w-7xl mx-auto px-4 sm:px-6 py-6 transition-all duration-300">
                <div class="max-w-7xl mx-auto bg-gradient-to-b from-blue-50/60 to-white/90 rounded-2xl sm:rounded-3xl p-4 sm:p-8 border border-blue-100 shadow-sm">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-blue-100">
                        <div>
                            <span class="text-[11px] sm:text-xs font-bold text-blue-600 uppercase tracking-wider block">Hasil Pencarian Produk dan Layanan</span>
                            <h2 id="search-results-title" class="text-xl sm:text-2xl font-black text-gray-900">
                                @if(isset($search) && !empty($search))
                                    Menampilkan hasil untuk: "<span class="text-[#115789]">{{ $search }}</span>"
                                @endif
                            </h2>
                            <p id="search-results-count" class="text-xs sm:text-sm text-gray-500 mt-0.5">
                                @if(isset($searchResults))
                                    Ditemukan {{ count($searchResults) }} produk / layanan
                                @endif
                            </p>
                        </div>
                        <button type="button" id="btn-close-search" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:text-red-600 hover:bg-red-50 border border-gray-200 transition-colors flex items-center gap-1 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Tutup Hasil</span>
                        </button>
                    </div>
                    
                    <!-- Search Results Grid -->
                    <div id="search-results-grid" class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
                        @if(isset($searchResults) && count($searchResults) > 0)
                            @foreach($searchResults as $item)
                            <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-100 shadow-xs hover:shadow-xl transition-all duration-300 p-3 sm:p-4 flex flex-col justify-between group transform hover:-translate-y-1">
                                <div>
                                    <div class="rekomendasi-img-wrapper relative rounded-lg sm:rounded-xl overflow-hidden bg-slate-50 mb-3 flex items-center justify-center border border-gray-100 p-2 sm:p-3">
                                        @php
                                            $imgUrl = null;
                                            if (!empty($item->image)) {
                                                $imgUrl = \Illuminate\Support\Str::startsWith($item->image, ['http://', 'https://', 'User/', 'Admin/']) 
                                                    ? asset($item->image) 
                                                    : asset('storage/' . $item->image);
                                            }
                                        @endphp
                                        @if($imgUrl)
                                        <img src="{{ $imgUrl }}" alt="{{ $item->name }}" loading="lazy" class="{{ ($item->type ?? '') === 'gas' ? 'gas-product-photo' : '' }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="w-full h-full hidden items-center justify-center text-gray-300 bg-gray-100">
                                            <i class="bx bx-package text-3xl"></i>
                                        </div>
                                        @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-100">
                                            <i class="bx bx-package text-3xl"></i>
                                        </div>
                                        @endif

                                        <span class="absolute top-2 left-2 px-2 py-0.5 text-[9px] font-bold rounded-md shadow-xs {{ $item->badge_color ?? 'bg-[#115789] text-white' }}" style="color: #ffffff !important;">
                                            {{ str_replace('&', 'dan', $item->category) }}
                                        </span>
                                    </div>

                                    <h3 class="text-xs sm:text-sm font-bold text-gray-900 line-clamp-1 mb-1" title="{{ $item->name }}">
                                        {{ $item->name }}
                                    </h3>

                                    <div class="text-xs sm:text-sm font-black text-[#115789] mb-1">
                                        {{ $item->price_formatted }}
                                        @if(!empty($item->unit) && $item->type != 'fasilitas')
                                        <span class="text-[10px] font-normal text-gray-500">/{{ $item->unit }}</span>
                                        @endif
                                    </div>

                                    <div class="text-[10px] sm:text-[11px] text-gray-400">
                                        @if($item->type == 'fasilitas')
                                            Tersedia izin kegiatan
                                        @elseif(isset($item->stock) && $item->stock > 0)
                                            Stok: {{ $item->stock }} {{ $item->unit }}
                                        @else
                                            Siap Dipesan
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-3 pt-2 border-t border-gray-50">
                                    @php
                                        $btnClass = 'btn-action-pasar';
                                        $btnStyle = 'background-color: #115789 !important; color: #ffffff !important;';
                                        $btnLabel = 'Beli Produk';
                                        if ($item->type == 'gas') {
                                            $btnClass = 'btn-action-gas';
                                            $btnStyle = 'background-color: #ea580c !important; color: #ffffff !important;';
                                            $btnLabel = 'Pesan Gas';
                                        } elseif ($item->type == 'rental') {
                                            $btnClass = 'btn-action-rental';
                                            $btnStyle = 'background-color: #059669 !important; color: #ffffff !important;';
                                            $btnLabel = 'Sewa Alat';
                                        } elseif ($item->type == 'mobil') {
                                            $btnClass = 'btn-action-mobil';
                                            $btnStyle = 'background-color: #2563eb !important; color: #ffffff !important;';
                                            $btnLabel = 'Sewa Transportasi';
                                        } elseif ($item->type == 'fasilitas') {
                                            $btnClass = 'btn-action-fasilitas';
                                            $btnStyle = 'background-color: #9333ea !important; color: #ffffff !important;';
                                            $btnLabel = 'Ajukan Izin';
                                        }
                                    @endphp
                                    <a href="{{ $item->link }}" class="w-full block text-center py-2 px-3 rounded-lg text-xs font-bold transition-all shadow-xs {{ $btnClass }}" style="{{ $btnStyle }}">
                                        {{ $btnLabel }}
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        @elseif(isset($search) && !empty($search))
                            <div class="col-span-2 md:col-span-4 text-center py-12">
                                <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-blue-50 flex items-center justify-center text-blue-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <h3 class="text-base font-bold text-gray-800">Tidak ada produk ditemukan</h3>
                                <p class="text-xs text-gray-500 max-w-sm mx-auto mt-1">Coba gunakan kata kunci lain seperti "Transportasi", "Pikap", "Gas 3kg", "Tenda", atau "Kursi".</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sapaan dan tiga kategori utama -->
            <div id="unit-carousel-container" class="max-w-7xl mx-auto px-4 sm:px-6 pt-8 pb-14 sm:pt-14 sm:pb-20 overflow-hidden relative">
                <div class="max-w-7xl mx-auto relative z-10">

                    <!-- Sapaan Ramah Dinamis (Animasi Mengetik & Menghapus Bergantian) -->
                    <div class="mb-4 sm:mb-6">
                        @php
                            $greetingPhrase1 = auth()->check() ? ('Halo, ' . (auth()->user()->nama_lengkap ?? auth()->user()->name)) : 'Halo Warga Bengkalis';
                            $greetingPhrase2 = 'Mau cari layanan apa hari ini?';
                        @endphp
                        <div class="min-h-[58px] sm:min-h-[72px] md:min-h-[80px] flex items-center justify-center px-4">
                            <h2 class="text-lg sm:text-2xl md:text-3xl lg:text-4xl font-black text-gray-900 tracking-tight leading-snug">
                                <span id="typewriter-text" 
                                      class="font-black text-gray-900"
                                      style="color: #111827 !important;"
                                      data-phrase1="{{ $greetingPhrase1 }}"
                                      data-phrase2="{{ $greetingPhrase2 }}">{{ $greetingPhrase1 }}</span><span id="typewriter-cursor" class="typewriter-cursor text-gray-900" style="color: #111827 !important;">|</span>
                            </h2>
                        </div>
                    </div>

                        <!-- Sapaan informasi singkat untuk menu 3D -->
                        <div class="mt-5 sm:mt-7 max-w-3xl mx-auto px-2">
                            <div id="unit-speech-box" class="unit-speech-box relative overflow-hidden rounded-2xl border border-white/80 bg-white/75 px-4 py-4 shadow-md shadow-slate-900/5 backdrop-blur-xl transition-all duration-300 sm:rounded-3xl sm:px-6 sm:py-5">
                                <div id="speech-text-wrapper" class="speech-text-wrapper text-center">
                                    <div class="flex flex-col items-center gap-2.5">
                                        <span id="speech-badge" class="inline-flex shrink-0 self-center items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.1em] text-amber-800">
                                            <i id="speech-badge-icon" class="bx bx-shopping-bag text-sm" aria-hidden="true"></i>
                                            <span id="speech-badge-label">Belanja dan Kebutuhan</span>
                                        </span>
                                        <div class="min-w-0 w-full">
                                            <p id="speech-heading" class="text-sm font-extrabold leading-snug text-slate-900 sm:text-base">Cari kebutuhan rumah atau produk lokal?</p>
                                            <p id="speech-body" class="mt-1 text-xs font-medium leading-relaxed text-slate-600 sm:text-sm">Pesan gas atau jelajahi produk usaha daerah melalui Pasar Daerah.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Judul kategori utama -->
                    <div class="text-center mt-10 sm:mt-16 mb-8 sm:mb-12 relative">
                        <a href="{{ route('pelayanan') }}" aria-label="Buka halaman Tentang Layanan" class="inline-flex items-center gap-2 text-xl font-extrabold tracking-tight no-underline transition hover:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#60a5fa] focus-visible:ring-offset-4 sm:text-2xl md:text-3xl">
                            <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Unit</span>
                            <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Layanan</span>
                        </a>
                    </div>

                        @php
                            $isLoggedInWithRegion = auth()->check() && auth()->user()->region_id;
                            $userRegionId = $isLoggedInWithRegion ? auth()->user()->region_id : null;
                            
                            $isServiceActive = function($unitName) use ($isLoggedInWithRegion, $activeServices) {
                                if (!$isLoggedInWithRegion) return true;

                                // Menu informasi dan Pasar Daerah tersedia untuk semua wilayah.
                                if (in_array($unitName, ['Belanja dan Kebutuhan', 'Pasar Daerah', 'Kabar dan Informasi Daerah', 'Pengumuman dan Event'])) {
                                    return true;
                                }

                                $serviceNames = [
                                    'Unit Penyewaan Alat' => ['Penyewaan Alat'],
                                    'Unit Penjualan Gas' => ['Penjualan Gas'],
                                    'Unit Penyewaan Mobil' => ['Penyewaan Mobil', 'Penyewaan Transportasi'],
                                    'Unit Penyewaan Transportasi' => ['Penyewaan Mobil', 'Penyewaan Transportasi'],
                                    'Unit Peminjaman Fasilitas Umum' => ['Fasilitas Umum'],
                                    'Pelaporan Warga' => ['Pelaporan Warga'],
                                ];

                                return collect($serviceNames[$unitName] ?? [$unitName])
                                    ->intersect($activeServices ?? [])->isNotEmpty();
                            };

                            $hasBelanja = $isServiceActive('Belanja dan Kebutuhan');
                            $hasLayanan = collect(['Unit Penyewaan Alat', 'Unit Penyewaan Transportasi', 'Unit Peminjaman Fasilitas Umum', 'Pelaporan Warga'])
                                ->contains(fn ($unit) => $isServiceActive($unit));
                            $hasKabar = $isServiceActive('Kabar dan Informasi Daerah');
                            $activeCount = (int) $hasBelanja + (int) $hasLayanan + (int) $hasKabar;
                            $categoryPageUrl = function ($slug) use ($isLoggedInWithRegion, $userRegionId) {
                                $url = route('service-category.show', ['category' => $slug]);
                                return $isLoggedInWithRegion ? $url . '?region_id=' . $userRegionId : $url;
                            };
                        @endphp

                        @if($activeCount > 0)
                        <div class="relative w-full flex justify-center items-center unit-stage-wrapper">
                            <div class="relative w-full max-w-6xl mx-auto h-full">
                                @if($hasBelanja)
                                <a href="{{ $categoryPageUrl('belanja-kebutuhan') }}" aria-label="Buka kategori Belanja dan Kebutuhan" class="unit-card cursor-pointer no-underline hover:scale-105 transition-transform focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-300 rounded-2xl"
                                     data-index="0" 
                                     data-name="Belanja dan Kebutuhan"
                                     data-heading="&quot;Cari kebutuhan rumah atau produk lokal?&quot;"
                                     data-body="Pesan gas atau jelajahi produk usaha daerah melalui Pasar Daerah."
                                     data-badge="Belanja dan Kebutuhan"
                                     data-icon="bx-shopping-bag"
                                     data-box-bg="bg-amber-50/90"
                                     data-box-border="border-amber-200"
                                     data-badge-bg="bg-amber-100 text-amber-800 border-amber-300"
                                     data-text-color="text-amber-950">
                                    <img src="{{ asset('Admin/img/menu3dberanda/belanja-kebutuhan.png') }}" onerror="this.onerror=null; this.src='{{ asset('Admin/img/pasardaerah/PasarDaerah.png') }}';" alt="Belanja dan Kebutuhan" loading="lazy">
                                </a>
                                @endif

                                @if($hasLayanan)
                                <a href="{{ $categoryPageUrl('layanan-daerah') }}" aria-label="Buka kategori Layanan Daerah" class="unit-card cursor-pointer no-underline hover:scale-105 transition-transform focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-300 rounded-2xl"
                                     data-index="1" 
                                     data-name="Layanan Daerah"
                                     data-heading="&quot;Butuh bantuan layanan dari daerah?&quot;"
                                     data-body="Sewa perlengkapan atau transportasi, gunakan fasilitas umum, dan sampaikan laporan warga dari satu tempat."
                                     data-badge="Layanan Daerah"
                                     data-icon="bx-grid-alt"
                                     data-box-bg="bg-blue-50/90"
                                     data-box-border="border-blue-200"
                                     data-badge-bg="bg-blue-100 text-blue-800 border-blue-300"
                                     data-text-color="text-blue-950">
                                    <img src="{{ asset('Admin/img/menu3dberanda/layanan-daerah.webp') }}" onerror="this.onerror=null; this.src='{{ asset('User/img/elemen/fasilitas.png') }}';" alt="Layanan Daerah" loading="lazy">
                                </a>
                                @endif

                                @if($hasKabar)
                                <a href="{{ route('announcements.index', $isLoggedInWithRegion ? ['region_id' => $userRegionId] : []) }}" aria-label="Buka Kabar dan Informasi Daerah" class="unit-card cursor-pointer no-underline hover:scale-105 transition-transform focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-300 rounded-2xl"
                                     data-index="2"
                                     data-name="Kabar dan Informasi Daerah"
                                     data-heading="&quot;Ingin tahu kabar terbaru di daerah?&quot;"
                                     data-body="Baca berita, pengumuman, dan informasi resmi untuk warga."
                                     data-badge="Kabar dan Informasi"
                                     data-icon="bx-news"
                                     data-box-bg="bg-sky-50/90"
                                     data-box-border="border-sky-200"
                                     data-badge-bg="bg-sky-100 text-sky-800 border-sky-300"
                                     data-text-color="text-sky-950">
                                    <img src="{{ asset('User/img/elemen/KabardanInformasiDaerah.png') }}" onerror="this.onerror=null; this.src='{{ asset('Admin/img/kabardaerah/KabardanInformasiDaerah.png') }}';" alt="Kabar dan Informasi Daerah" loading="lazy">
                                </a>
                                @endif
                            </div>
                        </div>

                        <div class="unit-nav-wrapper mt-4 sm:mt-8 mb-4 sm:mb-8 flex flex-col items-center justify-center gap-3 px-4 relative z-[70]">
                            <div class="unit-nav-controls flex w-full max-w-3xl items-center justify-center gap-2 sm:gap-5">
                                <button type="button" id="unit-prev" class="unit-nav-button" aria-label="Layanan sebelumnya">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                                </button>
                                <div class="unit-title-box text-center min-w-0 max-w-full flex-1">
                                    <h3 id="unit-title" class="text-base sm:text-xl md:text-2xl font-bold text-gray-900 transition-all duration-300 truncate">
                                        Belanja dan Kebutuhan
                                    </h3>
                                </div>
                                <button type="button" id="unit-next" class="unit-nav-button" aria-label="Layanan berikutnya">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="w-full flex flex-col items-center justify-center text-center p-12 bg-white/60 backdrop-blur-md rounded-3xl border border-white/50 shadow-lg mt-4 max-w-4xl mx-auto">
                            <div class="w-20 h-20 mb-6 rounded-full bg-blue-50/80 flex items-center justify-center border border-blue-100 shadow-inner">
                                <svg class="w-10 h-10 text-[#115789]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-3">Unit Pelayanan Belum Tersedia</h3>
                            <p class="text-gray-500 max-w-lg text-lg leading-relaxed">Mohon maaf, Kelurahan atau Desa Anda saat ini belum mengaktifkan layanan operasional di sistem SiladesBeng.</p>
                        </div>
                        @endif
                </div>
            </div>

            <!-- Section Rekomendasi Produk Buat Kamu -->
            @if(isset($popularProducts) && $popularProducts->count() > 0)
            <div id="rekomendasi-produk-section" class="max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-10 sm:pt-20 sm:pb-16 relative">
                <div class="max-w-7xl mx-auto relative z-10">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6 sm:mb-8">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                                <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Rekomendasi Produk</span> 
                                <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Buat Kamu</span>
                            </h2>
                            <p class="text-xs sm:text-sm text-gray-500 mt-1">Ketersediaan resmi terdekat di wilayah Anda</p>
                        </div>

                        <!-- Tautan unit cepat dengan label unit yang konsisten -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[11px] text-gray-400 font-medium mr-1 hidden sm:inline">Pilih unit:</span>
                            @if(!isset($isServiceActive) || $isServiceActive('Unit Penjualan Gas'))
                            <a href="{{ $isLoggedInWithRegion ? route('gas.sales') . '?region_id=' . $userRegionId : route('bumdes.profil') . '?redirect=gas.sales' }}" class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white text-orange-700 hover:bg-orange-600 hover:text-white border border-orange-200 transition-colors shadow-xs">Unit Penjualan Gas</a>
                            @endif
                            @if(!isset($isServiceActive) || $isServiceActive('Unit Penyewaan Alat'))
                            <a href="{{ $isLoggedInWithRegion ? route('rental.equipment') . '?region_id=' . $userRegionId : route('bumdes.profil') . '?redirect=rental.equipment' }}" class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-200 transition-colors shadow-xs">Unit Penyewaan Alat</a>
                            @endif
                            @if(!isset($isServiceActive) || $isServiceActive('Unit Penyewaan Transportasi') || $isServiceActive('Unit Penyewaan Mobil'))
                            <a href="{{ $isLoggedInWithRegion ? route('mobil.rental.equipment') . '?region_id=' . $userRegionId : route('bumdes.profil') . '?redirect=mobil.rental.equipment' }}" class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white text-blue-700 hover:bg-blue-600 hover:text-white border border-blue-200 transition-colors shadow-xs">Unit Penyewaan Kendaraan</a>
                            @endif
                            <a href="{{ route('pasar.index') }}" class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white text-amber-700 hover:bg-amber-600 hover:text-white border border-amber-200 transition-colors shadow-xs">Pasar Daerah</a>
                        </div>
                    </div>

                    <!-- Grid Kartu Rekomendasi (2 Kolom di HP, 4 Kolom di Desktop) -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
                        @foreach($popularProducts as $item)
                        <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-100 shadow-xs hover:shadow-xl transition-all duration-300 p-3 sm:p-4 flex flex-col justify-between group transform hover:-translate-y-1">
                            <div>
                                <!-- Image Container (Anti-Gepeng, Padded, Centered) -->
                                <div class="rekomendasi-img-wrapper relative rounded-lg sm:rounded-xl overflow-hidden bg-slate-50 mb-3 flex items-center justify-center border border-gray-100 p-2 sm:p-3">
                                    @php
                                        $imgUrl = null;
                                        if (!empty($item->image)) {
                                            $imgUrl = \Illuminate\Support\Str::startsWith($item->image, ['http://', 'https://', 'User/', 'Admin/']) 
                                                ? asset($item->image) 
                                                : asset('storage/' . $item->image);
                                        }
                                    @endphp
                                    @if($imgUrl)
                                    <img src="{{ $imgUrl }}" alt="{{ $item->name }}" loading="lazy" class="{{ ($item->type ?? '') === 'gas' ? 'gas-product-photo' : '' }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="w-full h-full hidden items-center justify-center text-gray-300 bg-gray-100">
                                        <i class="bx bx-package text-3xl"></i>
                                    </div>
                                    @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-100">
                                        <i class="bx bx-package text-3xl"></i>
                                    </div>
                                    @endif

                                    <!-- Badge Asal Unit -->
                                    <span class="absolute top-2 left-2 px-2 py-0.5 text-[9px] font-bold rounded-md shadow-xs {{ $item->badge_color ?? 'bg-[#115789] text-white' }}" style="color: #ffffff !important;">
                                        {{ str_replace('&', 'dan', $item->category) }}
                                    </span>
                                </div>

                                <!-- Product Title -->
                                <h3 class="text-xs sm:text-sm font-bold text-gray-900 line-clamp-1 mb-1" title="{{ $item->name }}">
                                    {{ $item->name }}
                                </h3>

                                <!-- Price -->
                                <div class="text-xs sm:text-sm font-black text-[#115789] mb-1">
                                    {{ $item->price_formatted }}
                                    @if(!empty($item->unit) && $item->type != 'fasilitas')
                                    <span class="text-[10px] font-normal text-gray-500">/{{ $item->unit }}</span>
                                    @endif
                                </div>

                                <!-- Status / Info -->
                                <div class="text-[10px] sm:text-[11px] text-gray-400">
                                    @if($item->type == 'fasilitas')
                                        Tersedia izin kegiatan
                                    @elseif(isset($item->stock) && $item->stock > 0)
                                        Stok: {{ $item->stock }} {{ $item->unit }}
                                    @else
                                        Siap Dipesan
                                    @endif
                                </div>
                            </div>

                            <!-- Button Action (Solid, High-Contrast, Never Washes Out) -->
                            <div class="mt-3 pt-2 border-t border-gray-50">
                                @php
                                    $btnClass = 'btn-action-pasar';
                                    $btnStyle = 'background-color: #115789 !important; color: #ffffff !important;';
                                    $btnLabel = 'Beli Produk';
                                    if ($item->type == 'gas') {
                                        $btnClass = 'btn-action-gas';
                                        $btnStyle = 'background-color: #ea580c !important; color: #ffffff !important;';
                                        $btnLabel = 'Pesan Gas';
                                    } elseif ($item->type == 'rental') {
                                        $btnClass = 'btn-action-rental';
                                        $btnStyle = 'background-color: #059669 !important; color: #ffffff !important;';
                                        $btnLabel = 'Sewa Alat';
                                    } elseif ($item->type == 'mobil') {
                                        $btnClass = 'btn-action-mobil';
                                        $btnStyle = 'background-color: #2563eb !important; color: #ffffff !important;';
                                        $btnLabel = 'Sewa Transportasi';
                                    } elseif ($item->type == 'fasilitas') {
                                        $btnClass = 'btn-action-fasilitas';
                                        $btnStyle = 'background-color: #9333ea !important; color: #ffffff !important;';
                                        $btnLabel = 'Ajukan Izin';
                                    }
                                @endphp
                                <a href="{{ $item->link }}" class="w-full block text-center py-2 px-3 rounded-lg text-xs font-bold transition-all shadow-xs {{ $btnClass }}" style="{{ $btnStyle }}">
                                    {{ $btnLabel }}
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Section Pengumuman Terbaru -->
            @if(isset($recentAnnouncements) && $recentAnnouncements->count() > 0)
            <div id="kabar-daerah-section" class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-12 relative">
                <!-- Decorative background elements -->
                <div class="absolute top-0 right-0 w-32 h-32 md:w-64 md:h-64 bg-yellow-400/5 rounded-full filter blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-80 h-80 bg-[#115789]/5 rounded-full filter blur-3xl"></div>
                
                <div class="max-w-7xl mx-auto relative z-10">
                    <div class="flex justify-between items-end mb-6 sm:mb-8">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-bold mb-1 sm:mb-2">
                                <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Kabar dan Informasi</span> 
                                <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Daerah</span>
                            </h2>
                            <p class="text-xs sm:text-base text-gray-500">Pengumuman dan berita terbaru</p>
                        </div>
                        <a href="{{ route('announcements.index') }}" class="hidden md:flex items-center gap-2 text-[#115789] font-semibold hover:text-blue-500 transition-colors">
                            Lihat Semua
                        </a>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
                        @foreach($recentAnnouncements as $item)
                        <a href="{{ route('announcements.show', $item->id) }}" class="group bg-white rounded-xl sm:rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 flex flex-col h-full transform hover:-translate-y-1 {{ $loop->iteration == 4 ? 'flex md:hidden' : 'flex' }}">
                            <div class="kabar-img-wrapper">
                                @if($item->image_path)
                                    <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->title }}" class="w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-500" onerror="this.onerror=null; this.src='{{ asset('User/img/elemen/KabardanInformasiDaerah.png') }}';">
                                @elseif($item->images && $item->images->count() > 0)
                                    <img src="{{ Storage::url($item->images->first()->image_path) }}" alt="{{ $item->title }}" class="w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-500" onerror="this.onerror=null; this.src='{{ asset('User/img/elemen/KabardanInformasiDaerah.png') }}';">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#115789]/10 to-blue-500/10">
                                        @if($item->type == 'Pengumuman') <i class="bx bx-broadcast text-3xl sm:text-4xl text-blue-500"></i>
                                        @elseif($item->type == 'Event') <i class="bx bx-calendar-event text-3xl sm:text-4xl text-purple-500"></i>
                                        @else <i class="bx bx-group text-3xl sm:text-4xl text-emerald-500"></i>
                                        @endif
                                    </div>
                                @endif
                                
                                <div class="absolute top-2 left-2 sm:top-3 sm:left-3 flex gap-1 sm:gap-2">
                                    @if($item->type == 'Gotong Royong')
                                        <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 bg-emerald-500 text-white rounded-md text-[10px] sm:text-xs font-bold shadow-sm">Gotong Royong</span>
                                    @elseif($item->type == 'Event')
                                        <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 bg-purple-500 text-white rounded-md text-[10px] sm:text-xs font-bold shadow-sm">Event</span>
                                    @else
                                        <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 bg-blue-500 text-white rounded-md text-[10px] sm:text-xs font-bold shadow-sm">Pengumuman</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="p-3 sm:p-4 md:p-5 flex flex-col flex-1">
                                <div class="text-[10px] sm:text-xs text-gray-500 mb-1.5 sm:mb-2 flex items-center justify-between gap-1">
                                    <span class="flex items-center gap-1 shrink-0"><i class="bx bx-calendar text-[#115789]"></i> {{ $item->created_at->format('d M Y') }}</span>
                                    <span class="font-medium text-[#115789] truncate max-w-[90px] sm:max-w-[140px] md:max-w-none text-right">{{ $item->region->name ?? 'Pusat' }}</span>
                                </div>
                                <h3 class="font-bold text-gray-800 text-xs sm:text-sm md:text-base lg:text-lg mb-1.5 sm:mb-2 line-clamp-2 group-hover:text-[#115789] transition-colors leading-snug">{{ $item->title }}</h3>
                                <p class="text-gray-500 text-[11px] sm:text-xs md:text-sm line-clamp-2 mt-auto leading-relaxed">{{ \Illuminate\Support\Str::limit(strip_tags($item->description), 80) }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    
                    <!-- Tombol Lihat Semua Kabar (Muncul di Bawah pada Desktop maupun Mobile) -->
                    <div class="mt-8 text-center">
                        <a href="{{ route('announcements.index') }}" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-gray-50 hover:bg-gray-100 text-[#115789] font-semibold rounded-xl transition-all duration-300 border border-gray-200 hover:border-[#115789]/30 shadow-sm hover:shadow w-full sm:w-auto">
                            Lihat Semua Kabar
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Section Tentang Kami -->
            <div class="relative max-w-7xl mx-auto px-4 py-8 sm:px-6 sm:py-16 overflow-visible">
                <!-- Background Elements - Hanya 2 Oval -->
                <div id="about-us-background" class="absolute inset-0 pointer-events-none" style="left: -200px; right: -200px;">
                    <svg class="absolute inset-0 w-full h-full" preserveAspectRatio="none" viewBox="0 0 1440 500"
                        xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="ovalGradient" x1="0%" y1="0%" x2="100%"
                                y2="100%">
                                <stop offset="0%" style="stop-color:#7dd3fc;stop-opacity:0.45" />
                                <stop offset="100%" style="stop-color:#bae6fd;stop-opacity:0.25" />
                            </linearGradient>
                        </defs>

                        <!-- Oval Kiri -->
                        <ellipse cx="120" cy="250" rx="320" ry="280"
                            fill="url(#ovalGradient)" />

                        <!-- Oval Kanan Bawah -->
                        <ellipse cx="1350" cy="420" rx="280" ry="240"
                            fill="url(#ovalGradient)" />
                    </svg>
                </div>

                <!-- Content -->
                <div class="relative z-10">
                    <!-- Title -->
                    <div class="text-center mb-6 sm:mb-10">
                        <h2 class="text-2xl sm:text-3xl font-bold mb-2">
                            <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Tentang</span> 
                            <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Kami</span>
                        </h2>
                    </div>

                    <!-- Text Content dengan Glass Effect -->
                    <div class="max-w-5xl mx-auto">
                        <div class="backdrop-blur-sm bg-white/70 rounded-2xl sm:rounded-3xl p-5 sm:p-8 md:p-12 border border-white/70 shadow-lg sm:shadow-xl">
                            <p class="text-sm sm:hidden text-gray-700 leading-relaxed">
                                SiladesBeng memudahkan warga mengakses layanan desa, berbelanja produk lokal, menyampaikan laporan, dan mendapatkan informasi daerah dalam satu platform.
                            </p>
                            <details class="sm:hidden mt-3 border-t border-slate-200/80 pt-3">
                                <summary class="cursor-pointer list-none text-sm font-bold text-[#115789] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-300 rounded">
                                    Baca selengkapnya
                                    <i class="bx bx-chevron-down ml-1" aria-hidden="true"></i>
                                </summary>
                                <div class="mt-3 space-y-3 text-left text-sm leading-relaxed text-gray-600">
                                    <p>SiladesBeng (Sistem Sinergi Layanan dan Aspirasi Desa di Kabupaten Bengkalis) merupakan platform digital terpadu yang mendukung tata kelola dan pelayanan publik dari tingkat kabupaten hingga desa.</p>
                                    <p>Warga dapat mengakses penyewaan alat, penjualan gas, transportasi, fasilitas umum, Pasar Daerah, Pelaporan Warga, serta Kabar dan Informasi Daerah melalui satu platform.</p>
                                </div>
                            </details>
                            <div class="hidden sm:block space-y-4 text-left md:text-justify text-sm md:text-base text-gray-700 leading-relaxed">
                                <p>
                                    <span class="font-semibold text-gray-800">SiladesBeng</span> (Sistem Sinergi Layanan dan Aspirasi Desa di Kabupaten Bengkalis) merupakan platform digital terpadu berskala kabupaten yang dirancang khusus untuk memodernisasi tata kelola administrasi dan pelayanan publik di seluruh jaringan kecamatan hingga tingkat desa se-Kabupaten Bengkalis. Platform ini mengintegrasikan berbagai pilar layanan esensial masyarakat dan operasional layanan desa dalam satu pintu.
                                </p>
                                <p>
                                    Melalui SiladesBeng, masyarakat Kabupaten Bengkalis dapat dengan mudah mengakses beragam unit layanan, mulai dari penyewaan alat, pendistribusian gas, penyewaan transportasi, hingga pemanfaatan fasilitas umum. Di samping itu, sistem ini juga mewadahi fitur <span class="font-medium text-gray-800">Pelaporan Warga</span> serta pusat informasi <span class="font-medium text-gray-800">Kabar dan Informasi Daerah</span> secara <i>real-time</i>. Kami percaya bahwa ekosistem digital yang transparan dan terukur dari jenjang kabupaten hingga pelosok desa ini merupakan kunci utama untuk mewujudkan pelayanan publik yang prima, memajukan perekonomian daerah, dan membangun kemandirian masyarakat Bengkalis yang berkelanjutan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        {{-- DECORATIONS --}}

    </main>

    {{-- FOOTER --}}

    {{-- SCRIPT --}}
@endsection


@push('styles')
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        /* Gradient Radial untuk Background Tentang Kami */
        .bg-gradient-radial {
            background-image: radial-gradient(circle, var(--tw-gradient-stops));
        }

        /* --- Product Card Styles (Matches Rental/Gas Page) --- */
        .product-card {
            position: relative;
            background: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .product-card:hover {
            transform: translateY(-8px);
        }

        .product-image {
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.4;
            margin-top: 1rem;
        }

        /* Kontainer Gambar Kabar Daerah (Responsif & Anti-Gepeng) */
        .kabar-img-wrapper {
            position: relative;
            width: 100%;
            height: 140px;
            overflow: hidden;
            background-color: #f3f4f6;
            flex-shrink: 0;
        }
        @media (min-width: 640px) {
            .kabar-img-wrapper {
                height: 170px;
            }
        }
        @media (min-width: 768px) {
            .kabar-img-wrapper {
                height: 230px;
            }
        }
        @media (min-width: 1024px) {
            .kabar-img-wrapper {
                height: 250px;
            }
        }

        /* Rekomendasi dan hasil pencarian memakai frame penuh yang konsisten. */
        .rekomendasi-img-wrapper {
            height: 165px;
            width: 100%;
            padding: 8px;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        @media (min-width: 640px) {
            .rekomendasi-img-wrapper {
                height: 195px;
                padding: 10px;
            }
        }
        .rekomendasi-img-wrapper img {
            max-width: 100% !important;
            max-height: 100% !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain !important;
            margin: 0 auto;
            display: block;
        }

        /* Kotak Sapaan Dinamis Unit Pelayanan */
        .unit-speech-box {
            transition: background-color 0.35s ease, border-color 0.35s ease, box-shadow 0.35s ease;
        }
        .speech-text-wrapper {
            transition: opacity 0.22s ease, transform 0.22s ease;
        }
        .speech-text-wrapper.fade-out {
            opacity: 0;
            transform: translateY(-4px);
        }
        .speech-text-wrapper.fade-in {
            opacity: 1;
            transform: translateY(0);
        }

        .unit-nav-wrapper { position: relative; z-index: 70 !important; isolation: isolate; }
        .unit-nav-controls { position: relative; z-index: 71; }
        .unit-nav-button { position: relative; z-index: 72; display: inline-flex; width: 3rem; height: 3rem; flex-shrink: 0; align-items: center; justify-content: center; border: 1px solid #2563eb; border-radius: 999px; background: #2563eb; padding: 0; color: #fff; box-shadow: 0 5px 14px rgba(37, 99, 235, .28); transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease, transform .15s ease; }
        .unit-nav-button svg { width: 1.25rem; height: 1.25rem; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .unit-nav-button:hover { transform: translateY(-1px); border-color: #1d4ed8; background: #1d4ed8; color: #fff; box-shadow: 0 7px 17px rgba(37, 99, 235, .34); }
        .unit-nav-button:focus-visible { outline: 3px solid #93c5fd; outline-offset: 3px; }

        /* --- UNIT CAROUSEL STYLES (4 VISIBLE ITEMS) --- */
        .unit-stage-wrapper {
            height: 440px;
        }

        .unit-card {
            width: 280px;
            height: 280px;
            position: absolute;
            top: 45%;
            transform-origin: center center;
            /* Transisi halus saat tukar tempat */
            transition: all 0.6s cubic-bezier(0.25, 1, 0.5, 1);
            will-change: transform, left, opacity;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .unit-card img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 15px 25px rgba(0, 0, 0, 0.15));
        }

        /* POSISI 0: KIRI */
        .state-0 {
            left: 15% !important;
            transform: translate(-50%, -50%) scale(0.65) !important;
            opacity: 0.8;
            z-index: 20;
            filter: grayscale(10%);
        }

        /* POSISI 1: (TENGAH FOKUS) */
        .state-1 {
            left: 50% !important;
            transform: translate(-50%, -50%) scale(1.5) !important;
            opacity: 1;
            z-index: 50;
            filter: grayscale(0%) drop-shadow(0 25px 35px rgba(0, 0, 0, 0.25));
        }

        /* POSISI 2: KANAN  */
        .state-2 {
            left: 80% !important;
            transform: translate(-50%, -50%) scale(0.65) !important;
            opacity: 0.8;
            z-index: 20;
            filter: grayscale(10%);
        }

        /* POSISI 3: KANAN UJUNG (HIDDEN) */
        .state-3 {
            left: 100% !important;
            transform: translate(-50%, -50%) scale(0.5) !important;
            opacity: 0;
            z-index: 10;
            pointer-events: none;
        }

        /* POSISI 4: KIRI UJUNG (HIDDEN) */
        .state-4 {
            left: 0% !important;
            transform: translate(-50%, -50%) scale(0.5) !important;
            opacity: 0;
            z-index: 10;
            pointer-events: none;
        }

        /* POSISI 5: TERSEMBUNYI */
        .state-5 {
            left: 50% !important;
            transform: translate(-50%, -50%) scale(0.1) !important;
            opacity: 0;
            z-index: 5;
            pointer-events: none;
        }

        /* Kursor Kedip Typewriter Ala Tanya Assistant */
        .typewriter-cursor {
            display: inline-block;
            font-weight: 300;
            color: #111827;
            margin-left: 2px;
            animation: typewriter-blink 0.8s step-end infinite;
        }
        @keyframes typewriter-blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }

        /* Tombol Aksi Unit Layanan (Solid, Anti-Washed Out) */
        .btn-action-gas {
            background-color: #ea580c !important;
            color: #ffffff !important;
        }
        .btn-action-gas:hover {
            background-color: #c2410c !important;
            color: #ffffff !important;
        }
        .btn-action-rental {
            background-color: #059669 !important;
            color: #ffffff !important;
        }
        .btn-action-rental:hover {
            background-color: #047857 !important;
            color: #ffffff !important;
        }
        .btn-action-mobil {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }
        .btn-action-mobil:hover {
            background-color: #1d4ed8 !important;
            color: #ffffff !important;
        }
        .btn-action-fasilitas {
            background-color: #9333ea !important;
            color: #ffffff !important;
        }
        .btn-action-fasilitas:hover {
            background-color: #7e22ce !important;
            color: #ffffff !important;
        }
        .btn-action-pasar {
            background-color: #115789 !important;
            color: #ffffff !important;
        }
        .btn-action-pasar:hover {
            background-color: #0c446c !important;
            color: #ffffff !important;
        }

        /* Navigasi Tombol Geser di Bawah - Aliran Dokumen Normal (Bebas Tabrakan) */
        .unit-nav-wrapper {
            position: relative !important;
            bottom: auto !important;
            left: auto !important;
            right: auto !important;
            width: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* RESPONSIVE MOBILE - 3 COLUMN LAYOUT (CENTER FOCUS) */
        @media (max-width: 768px) {
            #unit-carousel-container {
                padding-top: 1rem !important;
                padding-bottom: 1.5rem !important;
            }
            .unit-stage-wrapper {
                height: 210px !important;
            }
            .unit-card {
                width: clamp(88px, 26vw, 110px) !important;
                height: clamp(88px, 26vw, 110px) !important;
                top: 43% !important;
            }
            .unit-card img {
                width: 96% !important;
                height: 96% !important;
                filter: drop-shadow(0 8px 14px rgba(15, 23, 42, 0.16)) !important;
            }

            /* Slot Kiri (Background Preview) */
            .state-0 {
                left: 18% !important;
                transform: translate(-50%, -50%) scale(0.64) !important;
                opacity: 0.58 !important;
                z-index: 20 !important;
                filter: grayscale(20%) !important;
            }

            /* Slot Tengah (Focus Terpusat, Proporsional dan Rapi) */
            .state-1 {
                left: 50% !important;
                transform: translate(-50%, -50%) scale(1.12) !important;
                opacity: 1 !important;
                z-index: 50 !important;
                filter: grayscale(0%) drop-shadow(0 8px 16px rgba(0,0,0,0.18)) !important;
            }

            /* Slot Kanan (Background Preview) */
            .state-2 {
                left: 82% !important;
                transform: translate(-50%, -50%) scale(0.64) !important;
                opacity: 0.58 !important;
                z-index: 20 !important;
                filter: grayscale(20%) !important;
            }

            /* Antrian (Hidden) */
            .state-3 {
                left: 120% !important;
                transform: translate(-50%, -50%) scale(0.3) !important;
                opacity: 0 !important;
                pointer-events: none !important;
            }
            .state-4 {
                left: -20% !important;
                transform: translate(-50%, -50%) scale(0.3) !important;
                opacity: 0 !important;
                pointer-events: none !important;
            }
            .state-5 {
                left: 50% !important;
                transform: translate(-50%, -50%) scale(0.1) !important;
                opacity: 0 !important;
                pointer-events: none !important;
            }

            /* Navigasi Tombol Geser di Bawah */
            .unit-nav-wrapper {
                position: relative !important;
                bottom: auto !important;
                left: auto !important;
                right: auto !important;
                width: 100% !important;
                margin-top: 0.25rem !important;
                margin-bottom: 0.75rem !important;
                padding: 0 8px !important;
                gap: 8px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            .unit-nav-button { width: 2.5rem; height: 2.5rem; }
            .unit-nav-button svg { width: 1.1rem; height: 1.1rem; }
            .unit-title-box {
                min-width: 0 !important;
                max-width: 180px !important;
                flex: 1 !important;
            }
            #unit-title {
                font-size: 0.95rem !important;
                line-height: 1.25 !important;
            }

        }

        @media (max-width: 420px) {
            #unit-carousel-container {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            .unit-stage-wrapper {
                height: 190px !important;
            }
            .unit-card {
                width: 92px !important;
                height: 92px !important;
            }
            .unit-card img {
                width: 94% !important;
                height: 94% !important;
            }
            .unit-title-box {
                max-width: 150px !important;
            }
            #unit-title {
                font-size: 0.9rem !important;
            }
            #unit-speech-box {
                padding: 0.75rem 0.85rem !important;
            }
        }

        @media (max-width: 640px) {
            .rekomendasi-img-wrapper {
                height: clamp(112px, 34vw, 138px);
                padding: 8px;
            }
            #about-us-background {
                left: -48px !important;
                right: -48px !important;
                opacity: 0.65;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (() => {
            const BerandaPage = {
            // Initialize all components
            init() {
                try {
                    this.initCarousel();
                } catch (e) {
                    console.error("Carousel failed to initialize:", e);
                }
                this.initTypewriter();
                this.initUnitCarousel();
                this.initNavbarMarginSync();
                this.initLiveSearch();
            },

            // Animasi Mengetik dan Menghapus Sapaan (Loop Bergantian)
            initTypewriter() {
                const textElem = document.getElementById('typewriter-text');
                if (!textElem) return;

                const phrase1 = textElem.getAttribute('data-phrase1') || 'Halo Warga Bengkalis';
                const phrase2 = textElem.getAttribute('data-phrase2') || 'Mau cari layanan apa hari ini?';
                const phrases = [phrase1, phrase2];

                let phraseIndex = 0;
                let charIndex = phrases[0].length;
                let isDeleting = true;

                const typeSpeed = 45;
                const deleteSpeed = 22;
                const holdTime = 2400;
                const betweenTime = 350;

                textElem.textContent = phrases[0];

                const loop = () => {
                    const currentPhrase = phrases[phraseIndex];

                    if (isDeleting) {
                        charIndex--;
                        textElem.textContent = currentPhrase.substring(0, charIndex);

                        if (charIndex === 0) {
                            isDeleting = false;
                            phraseIndex = (phraseIndex + 1) % phrases.length;
                            setTimeout(loop, betweenTime);
                            return;
                        }
                        setTimeout(loop, deleteSpeed);
                    } else {
                        charIndex++;
                        textElem.textContent = currentPhrase.substring(0, charIndex);

                        if (charIndex === currentPhrase.length) {
                            isDeleting = true;
                            setTimeout(loop, holdTime);
                            return;
                        }
                        setTimeout(loop, typeSpeed);
                    }
                };

                setTimeout(loop, holdTime);
            },

            // Sinkronisasi tinggi layer blur dan padding
            initNavbarMarginSync() {
                const navbar = document.getElementById('master-navbar');
                if (!navbar) return;

                const syncHeights = () => {
                    document.body.style.setProperty('--nav-height', navbar.offsetHeight + 'px');
                };
                
                syncHeights();
                window.addEventListener('resize', syncHeights);
                const logoImg = navbar.querySelector('.sd-nav-logo img');
                if (logoImg) logoImg.addEventListener('load', syncHeights);
            },

            // Carousel initialization
            initCarousel() {
                const carouselSlides = document.getElementById('carousel-slides');
                if (!carouselSlides) return;

                const blurCarouselSlides = document.getElementById('blur-carousel-slides');
                if (blurCarouselSlides) {
                    const populateBlurSlides = () => {
                        blurCarouselSlides.innerHTML = '';
                        const slides = carouselSlides.querySelectorAll('.carousel-slide');
                        slides.forEach((slide) => {
                            const desktopImg = slide.querySelector('img[class*="md:block"]');
                            const mobileImg = slide.querySelector('img[class*="md:hidden"]');
                            let img = slide.querySelector('img');
                            if (desktopImg && mobileImg) {
                                img = window.innerWidth >= 768 ? desktopImg : mobileImg;
                            }
                            const blurSlide = document.createElement('div');
                            blurSlide.className = 'blur-slide';
                            if (img) blurSlide.style.backgroundImage = `url('${img.getAttribute('src')}')`;
                            blurCarouselSlides.appendChild(blurSlide);
                        });
                    };
                    populateBlurSlides();
                    window.addEventListener('resize', () => { setTimeout(populateBlurSlides, 200); }, { passive: true });
                }

                // Use let so we can update the reference after cloning
                let indicators = document.querySelectorAll('.carousel-indicator');

                let currentSlide = 0;
                const totalSlides = 2;
                let autoSlideInterval;
                const autoSlideDelay = 7000; // 7 Seconds

                let blurTimeout;
                const goToSlide = (slideIndex) => {
                    currentSlide = slideIndex;
                    requestAnimationFrame(() => {
                        carouselSlides.style.transform = `translateX(-${slideIndex * 100}%)`;
                        const blurCarouselSlides = document.getElementById('blur-carousel-slides');
                        if (blurCarouselSlides) blurCarouselSlides.style.transform = `translateX(-${slideIndex * 100}%)`;
                    });

                    // indicators variable now points to the LIVE elements in DOM
                    indicators.forEach((indicator, index) => {
                        indicator.classList.toggle('bg-white', index === slideIndex);
                        indicator.classList.toggle('w-8', index === slideIndex);
                        indicator.classList.toggle('bg-white/50', index !== slideIndex);
                        indicator.classList.toggle('w-2.5', index !== slideIndex);
                    });
                };

                const nextSlide = () => {
                    currentSlide = (currentSlide + 1) % totalSlides;
                    goToSlide(currentSlide);
                };

                const startAutoSlide = () => {
                    clearInterval(autoSlideInterval);
                    autoSlideInterval = setInterval(nextSlide, autoSlideDelay);
                };

                const resetAutoSlide = () => {
                    clearInterval(autoSlideInterval);
                    startAutoSlide();
                };

                // Fix: Update indicators reference after cloning
                const newIndicatorsList = [];
                indicators.forEach((indicator, index) => {
                    const newIndicator = indicator.cloneNode(true);
                    indicator.parentNode.replaceChild(newIndicator, indicator);
                    newIndicator.addEventListener('click', () => {
                        goToSlide(index);
                        resetAutoSlide();
                    });
                    newIndicatorsList.push(newIndicator);
                });
                indicators = newIndicatorsList; // Update reference to new nodes

                startAutoSlide();
            },

            // Unit Carousel dengan Sinkronisasi Kotak Narasi Dinamis
            initUnitCarousel() {
                const cards = Array.from(document.querySelectorAll('.unit-card'));
                if (cards.length === 0) return;

                const titleElement = document.getElementById('unit-title');
                const nextBtn = document.getElementById('unit-next');
                const prevBtn = document.getElementById('unit-prev');
                const speechBox = document.getElementById('unit-speech-box');
                const speechWrapper = document.getElementById('speech-text-wrapper');
                const speechHeading = document.getElementById('speech-heading');
                const speechBody = document.getElementById('speech-body');
                const speechBadge = document.getElementById('speech-badge');
                const speechBadgeLabel = document.getElementById('speech-badge-label');
                const speechBadgeIcon = document.getElementById('speech-badge-icon');

                const n = cards.length;
                let currentIndex = 0;
                const updateCarousel = () => {
                    cards.forEach((card, index) => {
                        // Remove all state classes
                        card.className = card.className.replace(/\bstate-\d\b/g, '').trim();
                        
                        let diff = (index - currentIndex) % n;
                        if (diff < 0) diff += n;
                        
                        let state = 5; // Default Hidden
                        
                        if (n === 1) {
                            if (diff === 0) state = 1;
                        } else if (n === 2) {
                            if (diff === 0) state = 1;
                            if (diff === 1) state = 2;
                        } else if (n === 3) {
                            if (diff === 0) state = 1;
                            if (diff === 1) state = 2;
                            if (diff === 2) state = 0;
                        } else {
                            if (diff === 0) state = 1; // Center
                            else if (diff === 1) state = 2; // Right
                            else if (diff === 2) state = 3; // Far Right (Hidden, fading out)
                            else if (diff === n - 2) state = 4; // Far Left (Hidden, fading in)
                            else if (diff === n - 1) state = 0; // Left
                            else state = 5; // Deep Hidden
                        }
                        
                        card.classList.add(`state-${state}`);

                        if (diff === 0) {
                            // 1. Update Title Navigasi Bawah
                            if (titleElement) {
                                titleElement.style.opacity = '0';
                                setTimeout(() => {
                                    titleElement.textContent = card.getAttribute('data-name');
                                    titleElement.style.opacity = '1';
                                }, 180);
                            }

                            // 2. Update Kotak Narasi Dinamis Atas
                            if (speechBox && speechWrapper) {
                                speechWrapper.classList.remove('fade-in');
                                speechWrapper.classList.add('fade-out');

                                setTimeout(() => {
                                    if (speechHeading) speechHeading.textContent = card.getAttribute('data-heading') || '';
                                    if (speechBody) speechBody.textContent = card.getAttribute('data-body') || '';
                                    if (speechBadgeLabel) speechBadgeLabel.textContent = card.getAttribute('data-badge') || '';
                                    if (speechBadgeIcon) speechBadgeIcon.className = `bx ${card.getAttribute('data-icon') || 'bx-grid-alt'} text-sm`;

                                    const boxBg = card.getAttribute('data-box-bg') || 'bg-amber-50/90';
                                    const boxBorder = card.getAttribute('data-box-border') || 'border-amber-200';
                                    const badgeBg = card.getAttribute('data-badge-bg') || 'bg-amber-100 text-amber-800 border-amber-300';
                                    const textColor = card.getAttribute('data-text-color') || 'text-amber-950';

                                    speechBox.className = `unit-speech-box relative overflow-hidden rounded-2xl sm:rounded-3xl px-4 py-4 sm:px-6 sm:py-5 border shadow-md shadow-slate-900/5 backdrop-blur-xl transition-all duration-300 ${boxBg} ${boxBorder}`;
                                    if (speechBadge) speechBadge.className = `inline-flex shrink-0 self-center items-center gap-1.5 rounded-full border px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.1em] ${badgeBg}`;
                                    if (speechHeading) speechHeading.className = `text-sm sm:text-base font-extrabold leading-snug ${textColor}`;

                                    speechWrapper.classList.remove('fade-out');
                                    speechWrapper.classList.add('fade-in');
                                }, 180);
                            }
                        }
                    });

                };

                const handleNext = () => {
                    if (n <= 1) return;
                    currentIndex = (currentIndex + 1) % n;
                    updateCarousel();
                };

                const handlePrev = () => {
                    if (n <= 1) return;
                    currentIndex = (currentIndex - 1 + n) % n;
                    updateCarousel();
                };

                if (nextBtn) {
                    nextBtn.hidden = n <= 1;
                    nextBtn.onclick = handleNext;
                }
                if (prevBtn) {
                    prevBtn.hidden = n <= 1;
                    prevBtn.onclick = handlePrev;
                }

                updateCarousel();
            },

            // Inisialisasi Live AJAX Search
            initLiveSearch() {
                const searchForm = document.getElementById('live-search-form');
                const searchInput = document.getElementById('live-search-input');
                const spinner = document.getElementById('search-spinner');
                const clearBtn = document.getElementById('search-clear-btn');
                const resultsSection = document.getElementById('search-results-section');
                const resultsTitle = document.getElementById('search-results-title');
                const resultsCount = document.getElementById('search-results-count');
                const resultsGrid = document.getElementById('search-results-grid');
                const closeBtn = document.getElementById('btn-close-search');
                const chips = document.querySelectorAll('.popular-search-chip');

                if (!searchInput || !resultsSection || !resultsGrid) return;

                let debounceTimer = null;
                let currentController = null;

                const escapeHtml = (str) => {
                    if (!str) return '';
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                };

                const buildProductCard = (item) => {
                    let btnClass = 'btn-action-pasar';
                    let btnStyle = 'background-color: #115789 !important; color: #ffffff !important;';
                    let btnText = 'Beli Produk';

                    if (item.type === 'gas') {
                        btnClass = 'btn-action-gas';
                        btnStyle = 'background-color: #ea580c !important; color: #ffffff !important;';
                        btnText = 'Pesan Gas';
                    } else if (item.type === 'rental') {
                        btnClass = 'btn-action-rental';
                        btnStyle = 'background-color: #059669 !important; color: #ffffff !important;';
                        btnText = 'Sewa Alat';
                    } else if (item.type === 'mobil') {
                        btnClass = 'btn-action-mobil';
                        btnStyle = 'background-color: #2563eb !important; color: #ffffff !important;';
                        btnText = 'Sewa Transportasi';
                    } else if (item.type === 'fasilitas') {
                        btnClass = 'btn-action-fasilitas';
                        btnStyle = 'background-color: #9333ea !important; color: #ffffff !important;';
                        btnText = 'Ajukan Izin';
                    }

                    const badgeBg = item.badge_color || 'bg-[#115789] text-white';

                    let imgHtml = '';
                    if (item.image) {
                        let src = item.image;
                        if (!src.startsWith('http://') && !src.startsWith('https://') && !src.startsWith('User/') && !src.startsWith('Admin/')) {
                            src = '/storage/' + src;
                        } else if (!src.startsWith('http://') && !src.startsWith('https://')) {
                            src = '/' + src;
                        }
                        imgHtml = `<img src="${src}" alt="${escapeHtml(item.name)}" loading="lazy" class="${item.type === 'gas' ? 'gas-product-photo' : ''}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-full h-full hidden items-center justify-center text-gray-300 bg-gray-100"><i class="bx bx-package text-3xl"></i></div>`;
                    } else {
                        imgHtml = `<div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-100"><i class="bx bx-package text-3xl"></i></div>`;
                    }

                    let statusText = 'Siap Dipesan';
                    if (item.type === 'fasilitas') {
                        statusText = 'Tersedia izin kegiatan';
                    } else if (item.stock && item.stock > 0) {
                        statusText = `Stok: ${item.stock} ${item.unit || ''}`;
                    }

                    const unitText = (item.unit && item.type !== 'fasilitas') ? `<span class="text-[10px] font-normal text-gray-500">/${escapeHtml(item.unit)}</span>` : '';

                    return `
                    <div class="bg-white rounded-xl sm:rounded-2xl border border-gray-100 shadow-xs hover:shadow-xl transition-all duration-300 p-3 sm:p-4 flex flex-col justify-between group transform hover:-translate-y-1">
                        <div>
                            <div class="rekomendasi-img-wrapper relative rounded-lg sm:rounded-xl overflow-hidden bg-slate-50 mb-3 flex items-center justify-center border border-gray-100 p-2 sm:p-3">
                                ${imgHtml}
                                <span class="absolute top-2 left-2 px-2 py-0.5 text-[9px] font-bold rounded-md shadow-xs ${badgeBg}" style="color: #ffffff !important;">
                                    ${escapeHtml(item.category)}
                                </span>
                            </div>
                            <h3 class="text-xs sm:text-sm font-bold text-gray-900 line-clamp-1 mb-1" title="${escapeHtml(item.name)}">
                                ${escapeHtml(item.name)}
                            </h3>
                            <div class="text-xs sm:text-sm font-black text-[#115789] mb-1">
                                ${escapeHtml(item.price_formatted)} ${unitText}
                            </div>
                            <div class="text-[10px] sm:text-[11px] text-gray-400">
                                ${escapeHtml(statusText)}
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-t border-gray-50">
                            <a href="${item.link}" class="w-full block text-center py-2 px-3 rounded-lg text-xs font-bold transition-all shadow-xs ${btnClass}" style="${btnStyle}">
                                ${btnText}
                            </a>
                        </div>
                    </div>`;
                };

                const performSearch = (query) => {
                    const trimmed = (query || '').trim();

                    if (clearBtn) {
                        if (trimmed.length > 0) {
                            clearBtn.classList.remove('hidden');
                        } else {
                            clearBtn.classList.add('hidden');
                        }
                    }

                    if (trimmed.length === 0) {
                        resultsSection.classList.add('hidden');
                        resultsGrid.innerHTML = '';
                        return;
                    }

                    if (spinner) spinner.classList.remove('hidden');

                    if (currentController) {
                        currentController.abort();
                    }
                    currentController = new AbortController();

                    const url = `{{ route('beranda') }}?search=${encodeURIComponent(trimmed)}&ajax=1`;
                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        signal: currentController.signal
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (spinner) spinner.classList.add('hidden');
                        if (!data || !data.results) return;

                        resultsSection.classList.remove('hidden');

                        if (resultsTitle) {
                            resultsTitle.innerHTML = `Menampilkan hasil untuk: "<span class="text-[#115789]">${escapeHtml(data.query)}</span>"`;
                        }
                        if (resultsCount) {
                            resultsCount.textContent = `Ditemukan ${data.count} produk / layanan`;
                        }

                        if (data.results.length === 0) {
                            resultsGrid.innerHTML = `
                                <div class="col-span-2 md:col-span-4 text-center py-12">
                                    <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-blue-50 flex items-center justify-center text-blue-400">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <h3 class="text-base font-bold text-gray-800">Tidak ada produk ditemukan</h3>
                                    <p class="text-xs text-gray-500 max-w-sm mx-auto mt-1">Coba gunakan kata kunci lain seperti "Mobil Pick Up", "Gas 3kg", "Tenda", atau "Kursi".</p>
                                </div>`;
                        } else {
                            resultsGrid.innerHTML = data.results.map(item => buildProductCard(item)).join('');
                        }

                        // Scroll smoothly to results container
                        resultsSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    })
                    .catch(err => {
                        if (err.name !== 'AbortError') {
                            if (spinner) spinner.classList.add('hidden');
                            console.error("Search AJAX error:", err);
                        }
                    });
                };

                searchInput.addEventListener('input', (e) => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        performSearch(e.target.value);
                    }, 300);
                });

                if (searchForm) {
                    searchForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        clearTimeout(debounceTimer);
                        performSearch(searchInput.value);
                    });
                }

                if (clearBtn) {
                    clearBtn.addEventListener('click', () => {
                        searchInput.value = '';
                        performSearch('');
                        searchInput.focus();
                    });
                }

                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        searchInput.value = '';
                        resultsSection.classList.add('hidden');
                        if (clearBtn) clearBtn.classList.add('hidden');
                    });
                }

                chips.forEach(chip => {
                    chip.addEventListener('click', (e) => {
                        e.preventDefault();
                        const query = chip.getAttribute('data-query');
                        if (query) {
                            searchInput.value = query;
                            performSearch(query);
                        }
                    });
                });
            },
        };
        // Initialize
        BerandaPage.init();

        // Check if user just logged out and show login modal
        @if(session('logout_success'))
            // Wait a bit for the page to fully load
            setTimeout(function() {
                const loginButton = document.getElementById('btn-open-login');
                if (loginButton) {
                    loginButton.click();
                }
            }, 300);
        @endif
        })();
    </script>
@endpush
