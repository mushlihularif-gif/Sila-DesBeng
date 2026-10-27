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
                            <img src="{{ asset('User/img/slidebanner/slide2sd.png') }}?v={{ time() }}" class="w-full h-auto object-contain object-top" style="max-height: 500px;" alt="Slide 2">
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

                                        <!-- Badge Asal Unit (Pojok Kiri Atas) -->
                                        <span class="rekomendasi-unit-badge" style="position: absolute !important; top: 8px !important; left: 8px !important; z-index: 10 !important;">
                                            <span class="badge-dot"></span>
                                            <span>{{ str_replace('&', 'dan', $item->category) }}</span>
                                        </span>
                                    </div>

                                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-[#2563eb] transition-colors line-clamp-1 mb-1" title="{{ $item->name }}">
                                        {{ $item->name }}
                                    </h3>

                                    <div class="text-xs sm:text-sm font-black text-[#2563eb] mb-1">
                                        {{ $item->price_formatted }}
                                        @if(!empty($item->unit) && $item->type != 'fasilitas')
                                        <span class="text-[10px] font-normal text-slate-400">/{{ $item->unit }}</span>
                                        @endif
                                    </div>

                                    <div class="text-[10px] sm:text-[11px] text-slate-400 font-medium">
                                        @if($item->type == 'fasilitas')
                                            Tersedia izin kegiatan
                                        @elseif(isset($item->stock) && $item->stock > 0)
                                            Stok: {{ $item->stock }} {{ $item->unit }}
                                        @else
                                            Siap Dipesan
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-3 pt-2.5 border-t border-slate-100">
                                    @php
                                        $btnLabel = 'Beli Produk';
                                        if ($item->type == 'gas') {
                                            $btnLabel = 'Pesan Gas';
                                        } elseif ($item->type == 'rental') {
                                            $btnLabel = 'Sewa Alat';
                                        } elseif ($item->type == 'mobil') {
                                            $btnLabel = 'Sewa Kendaraan';
                                        } elseif ($item->type == 'fasilitas') {
                                            $btnLabel = 'Ajukan Izin';
                                        }
                                    @endphp
                                    <a href="{{ $item->link }}" class="w-full block text-center py-2 sm:py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold text-white transition-all shadow-xs hover:shadow-md hover:shadow-blue-500/25 btn-action-brand" style="background-color: #2563eb !important; color: #ffffff !important;">
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

                        @endphp
                    @include('users.partials.unit-carousel-content', ['carouselRegionId' => auth()->user()?->region_id])
                </div>
            </div>

            <!-- Section Rekomendasi Produk Buat Kamu -->
            @if(isset($popularProducts) && $popularProducts->count() > 0)
            <div id="rekomendasi-produk-section" class="max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-10 sm:pt-20 sm:pb-16 relative">
                <div class="max-w-7xl mx-auto relative z-10">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6 sm:mb-8">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-bold mb-1 sm:mb-2">
                                <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Rekomendasi Produk</span> 
                                <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Buat Kamu</span>
                            </h2>
                            <p class="text-xs sm:text-base text-gray-500">Ketersediaan resmi terdekat di wilayah Anda</p>
                        </div>

                        <!-- Filter unit cepat (Konsisten & Elegan) -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[11px] text-slate-400 font-medium mr-1 hidden sm:inline">Pilih unit:</span>
                            @if(!isset($isServiceActive) || $isServiceActive('Unit Penjualan Gas'))
                            <a href="{{ $isLoggedInWithRegion ? route('gas.sales') . '?region_id=' . $userRegionId : route('bumdes.profil') . '?redirect=gas.sales' }}" class="px-3 py-1.5 text-xs font-semibold rounded-full bg-white text-slate-700 hover:bg-blue-600 hover:text-white border border-slate-200 hover:border-blue-600 transition-all shadow-2xs hover:shadow-xs">Unit Penjualan Gas</a>
                            @endif
                            @if(!isset($isServiceActive) || $isServiceActive('Unit Penyewaan Alat'))
                            <a href="{{ $isLoggedInWithRegion ? route('rental.equipment') . '?region_id=' . $userRegionId : route('bumdes.profil') . '?redirect=rental.equipment' }}" class="px-3 py-1.5 text-xs font-semibold rounded-full bg-white text-slate-700 hover:bg-blue-600 hover:text-white border border-slate-200 hover:border-blue-600 transition-all shadow-2xs hover:shadow-xs">Unit Penyewaan Alat</a>
                            @endif
                            @if(!isset($isServiceActive) || $isServiceActive('Unit Penyewaan Transportasi') || $isServiceActive('Unit Penyewaan Mobil'))
                            <a href="{{ $isLoggedInWithRegion ? route('mobil.rental.equipment') . '?region_id=' . $userRegionId : route('bumdes.profil') . '?redirect=mobil.rental.equipment' }}" class="px-3 py-1.5 text-xs font-semibold rounded-full bg-white text-slate-700 hover:bg-blue-600 hover:text-white border border-slate-200 hover:border-blue-600 transition-all shadow-2xs hover:shadow-xs">Unit Penyewaan Kendaraan</a>
                            @endif
                            <a href="{{ route('pasar.index') }}" class="px-3 py-1.5 text-xs font-semibold rounded-full bg-white text-slate-700 hover:bg-blue-600 hover:text-white border border-slate-200 hover:border-blue-600 transition-all shadow-2xs hover:shadow-xs">Pasar Daerah</a>
                        </div>
                    </div>

                    <!-- Grid Kartu Rekomendasi (2 Kolom di HP, 4 Kolom di Desktop) -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
                        @foreach($popularProducts as $item)
                        <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-100 shadow-xs hover:shadow-xl hover:border-blue-100 transition-all duration-300 p-3 sm:p-4 flex flex-col justify-between group transform hover:-translate-y-1">
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

                                    <!-- Badge Asal Unit (Pojok Kiri Atas) -->
                                    <span class="rekomendasi-unit-badge" style="position: absolute !important; top: 8px !important; left: 8px !important; z-index: 10 !important;">
                                        <span class="badge-dot"></span>
                                        <span>{{ str_replace('&', 'dan', $item->category) }}</span>
                                    </span>
                                </div>

                                <!-- Product Title -->
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-[#2563eb] transition-colors line-clamp-1 mb-1" title="{{ $item->name }}">
                                    {{ $item->name }}
                                </h3>

                                <!-- Price (Vibrant SegiDaerah Brand Blue #2563eb) -->
                                <div class="text-xs sm:text-sm font-black text-[#2563eb] mb-1">
                                    {{ $item->price_formatted }}
                                    @if(!empty($item->unit) && $item->type != 'fasilitas')
                                    <span class="text-[10px] font-normal text-slate-400">/{{ $item->unit }}</span>
                                    @endif
                                </div>

                                <!-- Status / Info -->
                                <div class="text-[10px] sm:text-[11px] text-slate-400 font-medium">
                                    @if($item->type == 'fasilitas')
                                        Tersedia izin kegiatan
                                    @elseif(isset($item->stock) && $item->stock > 0)
                                        Stok: {{ $item->stock }} {{ $item->unit }}
                                    @else
                                        Siap Dipesan
                                    @endif
                                </div>
                            </div>

                            <!-- Button Action (Konsisten Biru Brand SegiDaerah #2563eb - Premium & Elegan) -->
                            <div class="mt-3 pt-2.5 border-t border-slate-100">
                                @php
                                    $btnLabel = 'Beli Produk';
                                    if ($item->type == 'gas') {
                                        $btnLabel = 'Pesan Gas';
                                    } elseif ($item->type == 'rental') {
                                        $btnLabel = 'Sewa Alat';
                                    } elseif ($item->type == 'mobil') {
                                        $btnLabel = 'Sewa Kendaraan';
                                    } elseif ($item->type == 'fasilitas') {
                                        $btnLabel = 'Ajukan Izin';
                                    }
                                @endphp
                                <a href="{{ $item->link }}" class="w-full block text-center py-2 sm:py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold text-white transition-all shadow-xs hover:shadow-md hover:shadow-blue-500/25 btn-action-brand" style="background-color: #2563eb !important; color: #ffffff !important;">
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
                                SegiDaerah memudahkan warga mengakses layanan desa, berbelanja produk lokal, menyampaikan laporan, dan mendapatkan informasi daerah dalam satu platform.
                            </p>
                            <details class="sm:hidden mt-3 border-t border-slate-200/80 pt-3">
                                <summary class="cursor-pointer list-none text-sm font-bold text-[#115789] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-300 rounded">
                                    Baca selengkapnya
                                    <i class="bx bx-chevron-down ml-1" aria-hidden="true"></i>
                                </summary>
                                <div class="mt-3 space-y-3 text-left text-sm leading-relaxed text-gray-600">
                                    <p>SegiDaerah (Sistem Sinergi Layanan dan Aspirasi Daerah Kecamatan Bengkalis) merupakan platform digital terpadu yang dirancang untuk mendukung tata kelola administrasi dan pelayanan publik secara modern, transparan, dan efisien.</p>
                                    <p>Warga dapat mengakses penyewaan alat, penjualan gas, transportasi, fasilitas umum, Pasar Daerah, Pelaporan Warga, serta Kabar dan Informasi Daerah melalui satu platform.</p>
                                </div>
                            </details>
                            <div class="hidden sm:block space-y-4 text-left md:text-justify text-sm md:text-base text-gray-700 leading-relaxed">
                                <p>
                                    <span class="font-semibold text-gray-800">SegiDaerah</span> (Sistem Sinergi Layanan dan Aspirasi Daerah Kecamatan Bengkalis) merupakan platform digital terpadu yang dirancang khusus untuk memodernisasi tata kelola administrasi dan pelayanan publik di wilayah Kecamatan Bengkalis. Platform ini hadir sebagai solusi inovatif yang mengintegrasikan berbagai pilar layanan esensial masyarakat dan operasional layanan daerah ke dalam satu pintu yang modern, transparan, dan mudah dijangkau.
                                </p>
                                <p>
                                    Melalui SegiDaerah, masyarakat Kecamatan Bengkalis dapat dengan mudah mengakses beragam unit layanan, mulai dari penyewaan alat, pendistribusian gas, sarana transportasi, pemanfaatan fasilitas umum, hingga Pasar Daerah untuk produk usaha lokal. Di samping itu, sistem ini juga mewadahi fitur <span class="font-medium text-gray-800">Pelaporan Warga</span> serta pusat informasi <span class="font-medium text-gray-800">Kabar dan Informasi Daerah</span> secara <i>real-time</i>. Kami percaya bahwa digitalisasi layanan daerah merupakan langkah penting menuju pelayanan publik yang prima, penguatan perekonomian daerah, dan kemandirian masyarakat yang berkelanjutan.
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
            height: auto;
            width: 100%;
            aspect-ratio: 1 / 1;
            padding: 0;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        @media (min-width: 640px) {
            .rekomendasi-img-wrapper {
                height: auto;
                aspect-ratio: 1 / 1;
                padding: 0;
            }
        }
        .rekomendasi-img-wrapper img {
            max-width: none !important;
            max-height: none !important;
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            margin: 0;
            display: block;
        }
        .rekomendasi-img-wrapper img.gas-product-photo { transform: scale(1.28); }

        /* Badge Asal Unit Produk (Pojok Kiri Atas - Kebal Bug Posisi) */
        .rekomendasi-unit-badge {
            position: absolute !important;
            top: 8px !important;
            left: 8px !important;
            right: auto !important;
            bottom: auto !important;
            z-index: 10 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
            border-radius: 6px !important;
            padding: 3px 8px !important;
            font-size: 10px !important;
            font-weight: 600 !important;
            color: #334155 !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
            line-height: 1.2 !important;
            pointer-events: none !important;
        }
        .rekomendasi-unit-badge .badge-dot {
            width: 6px !important;
            height: 6px !important;
            border-radius: 50% !important;
            background-color: #2563eb !important;
            display: inline-block !important;
            flex-shrink: 0 !important;
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

        /* Tombol Aksi Unit Layanan (Solid SegiDaerah Brand Blue #2563eb - Konsisten Ala Shopee/Tokopedia) */
        .btn-action-gas,
        .btn-action-rental,
        .btn-action-mobil,
        .btn-action-fasilitas,
        .btn-action-pasar,
        .btn-action-brand {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }
        .btn-action-gas:hover,
        .btn-action-rental:hover,
        .btn-action-mobil:hover,
        .btn-action-fasilitas:hover,
        .btn-action-pasar:hover,
        .btn-action-brand:hover {
            background-color: #1d4ed8 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.28) !important;
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
                    let btnText = 'Beli Produk';
                    if (item.type === 'gas') {
                        btnText = 'Pesan Gas';
                    } else if (item.type === 'rental') {
                        btnText = 'Sewa Alat';
                    } else if (item.type === 'mobil') {
                        btnText = 'Sewa Kendaraan';
                    } else if (item.type === 'fasilitas') {
                        btnText = 'Ajukan Izin';
                    }

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

                    const unitText = (item.unit && item.type !== 'fasilitas') ? `<span class="text-[10px] font-normal text-slate-400">/${escapeHtml(item.unit)}</span>` : '';

                    return `
                    <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-100 shadow-xs hover:shadow-xl hover:border-blue-100 transition-all duration-300 p-3 sm:p-4 flex flex-col justify-between group transform hover:-translate-y-1">
                        <div>
                            <div class="rekomendasi-img-wrapper relative rounded-lg sm:rounded-xl overflow-hidden bg-slate-50 mb-3 flex items-center justify-center border border-gray-100 p-2 sm:p-3">
                                ${imgHtml}
                                <span class="rekomendasi-unit-badge" style="position: absolute !important; top: 8px !important; left: 8px !important; z-index: 10 !important;">
                                    <span class="badge-dot"></span>
                                    <span>${escapeHtml(item.category)}</span>
                                </span>
                            </div>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-[#2563eb] transition-colors line-clamp-1 mb-1" title="${escapeHtml(item.name)}">
                                ${escapeHtml(item.name)}
                            </h3>
                            <div class="text-xs sm:text-sm font-black text-[#2563eb] mb-1">
                                ${escapeHtml(item.price_formatted)} ${unitText}
                            </div>
                            <div class="text-[10px] sm:text-[11px] text-slate-400 font-medium">
                                ${escapeHtml(statusText)}
                            </div>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-slate-100">
                            <a href="${item.link}" class="w-full block text-center py-2 sm:py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold text-white transition-all shadow-xs hover:shadow-md hover:shadow-blue-500/25 btn-action-brand" style="background-color: #2563eb !important; color: #ffffff !important;">
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
