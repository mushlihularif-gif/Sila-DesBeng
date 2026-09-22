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

                    <!-- Navigation Buttons -->
                    <button id="carousel-prev"
                        class="absolute left-4 top-1/2 -translate-y-1/2 bg-white hover:bg-gray-50 text-gray-800 rounded-full p-3 shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110 z-10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <button id="carousel-next"
                        class="absolute right-4 top-1/2 -translate-y-1/2 bg-white hover:bg-gray-50 text-gray-800 rounded-full p-3 shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110 z-10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

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

            <!-- Bilah Pencarian dengan Batas Gradien -->
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-5 py-4 sm:py-8">
                <div class="max-w-2xl mx-auto">
                    <form action="{{ route('beranda') }}" method="GET" class="relative group">
                        <!-- Gradient Border -->
                        <div
                            class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 via-blue-400 to-amber-400 rounded-full opacity-80 group-hover:opacity-100 transition-opacity duration-300">
                        </div>

                        <!-- Search Input -->
                        <div class="relative flex items-center bg-white rounded-full overflow-hidden">
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari produk atau kategori..."
                                class="flex-1 px-5 sm:px-8 py-2.5 sm:py-3.5 text-gray-700 text-sm sm:text-[15px] focus:outline-none bg-transparent">

                            <!-- Search Button -->
                            <button type="submit"
                                class="flex-shrink-0 px-4 sm:px-6 py-2.5 sm:py-3.5 text-blue-600 hover:text-blue-700 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search Results Section (Only visible if searching) -->
            @if(isset($search) && $search)
            <div id="search-results-section" class="max-w-7xl mx-auto px-6 py-4">
                <div class="max-w-7xl mx-auto">
                    <div class="text-center mb-8 relative">
                        <h2 class="text-2xl font-bold text-gray-800">
                            Hasil Pencarian: "{{ $search }}"
                        </h2>
                        <a href="{{ route('beranda') }}" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Reset Pencarian</a>
                    </div>
                    
                    <div class="flex flex-wrap justify-center gap-8 mb-16 max-w-7xl mx-auto">
                        @forelse($searchResults as $item)
                        <!-- Product Card -->
                        <a href="{{ $item->link }}" class="block p-4">
                            <div class="product-card bg-white rounded-[2rem] p-6 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 mx-auto w-full max-w-[380px]">
                                <!-- Product Image -->
                                <div class="product-image-wrapper mb-6 relative aspect-[4/3] overflow-hidden rounded-2xl">
                                    <img src="{{ Str::startsWith($item->image, ['http', 'https', 'User', 'Admin']) ? asset($item->image) : asset('storage/' . $item->image) }}" 
                                         alt="{{ $item->name }}"
                                         loading="lazy"
                                         class="product-image w-full h-full object-cover">
                                </div>
    
                                <!-- Product Name Only -->
                                <div class="product-info text-center">
                                    <h3 class="product-name text-sm font-bold text-gray-800 mb-2">
                                        {{ $item->name }}
                                    </h3>
                                    <!-- Optional Badge for Type -->
                                    <span class="inline-block px-3 py-1 text-[10px] font-bold rounded-full {{ $item->type == 'rental' ? 'bg-blue-100 text-blue-600' : ($item->type == 'gas' ? 'bg-orange-100 text-orange-600' : 'bg-green-100 text-green-600') }} mt-1">
                                            {{ $item->type == 'rental' ? 'Sewa' : ($item->type == 'gas' ? 'Beli' : 'Profil') }}
                                    </span>
                                    
                                    @if($item->type == 'profile')
                                    <p class="text-xs text-gray-500 font-medium mt-2">{{ $item->price_formatted }}</p>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @empty
                        <div class="w-full text-center py-8">
                            <div class="bg-gray-50 rounded-lg p-8 inline-block">
                                <i class="bx bx-search-alt text-4xl text-gray-300 mb-3 block"></i>
                                <p class="text-gray-500">Tidak ada produk yang cocok dengan pencarian Anda.</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            <!-- Section Unit Pelayanan -->
            <div id="unit-carousel-container" class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-16 overflow-hidden">
                <div class="max-w-7xl mx-auto">

                    <div class="text-center mb-6 sm:mb-16 relative">
                        <h2 class="text-2xl sm:text-3xl font-bold mb-2">
                            <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Unit</span> 
                            <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Pelayanan</span>
                        </h2>
                    </div>

                        @php
                            $isLoggedInWithRegion = auth()->check() && auth()->user()->region_id;
                            $userRegionId = $isLoggedInWithRegion ? auth()->user()->region_id : null;
                            
                            $isServiceActive = function($unitName) use ($isLoggedInWithRegion, $activeServices) {
                                if (!$isLoggedInWithRegion) return true;
                                
                                // Pasar Daerah dan Kabar & Informasi Daerah bersifat publik sentral se-Kabupaten
                                if (in_array($unitName, ['Pasar Daerah', 'Pengumuman dan Event'])) {
                                    return true;
                                }

                                $map = [
                                    'Unit Penyewaan Alat' => 'Penyewaan Alat',
                                    'Unit Penjualan Gas' => 'Penjualan Gas',
                                    'Unit Penyewaan Mobil' => 'Penyewaan Mobil',
                                    'Unit Peminjaman Fasilitas Umum' => 'Fasilitas Umum',
                                    'Pelaporan Warga' => 'Pelaporan Warga'
                                ];
                                
                                return in_array($map[$unitName] ?? $unitName, $activeServices ?? []);
                            };

                            $activeCount = 0;
                            $allUnits = ['Unit Penyewaan Alat', 'Unit Penjualan Gas', 'Unit Penyewaan Mobil', 'Unit Peminjaman Fasilitas Umum', 'Pasar Daerah', 'Pelaporan Warga', 'Pengumuman dan Event'];
                            foreach ($allUnits as $unit) {
                                if ($isServiceActive($unit)) $activeCount++;
                            }
                        @endphp

                        @if($activeCount > 0)
                        <div class="relative w-full flex justify-center items-center unit-stage-wrapper">
                            <div class="relative w-full max-w-6xl mx-auto h-full">
                                @if($isServiceActive('Unit Penyewaan Alat'))
                                <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="0" data-name="Unit Penyewaan Alat" onclick="window.location.href='{{ $isLoggedInWithRegion ? route('rental.equipment') . '?region_id=' . $userRegionId : route('bumdes.profil') . '?redirect=rental.equipment' }}'">
                                    <img src="{{ asset('User/img/elemen/F1.png') }}" alt="Alat" loading="lazy">
                                </div>
                                @endif

                                @if($isServiceActive('Unit Penjualan Gas'))
                                <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="1" data-name="Unit Penjualan Gas" onclick="window.location.href='{{ $isLoggedInWithRegion ? route('gas.sales') . '?region_id=' . $userRegionId : route('bumdes.profil') . '?redirect=gas.sales' }}'">
                                    <img src="{{ asset('User/img/elemen/F2.png') }}" alt="Gas" loading="lazy">
                                </div>
                                @endif

                                @if($isServiceActive('Unit Penyewaan Mobil'))
                                <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="2" data-name="Unit Penyewaan Mobil" onclick="window.location.href='{{ $isLoggedInWithRegion ? route('mobil.rental.equipment') . '?region_id=' . $userRegionId : route('bumdes.profil') . '?redirect=mobil.rental.equipment' }}'">
                                    <img src="{{ asset('User/img/elemen/mobil.png') }}" alt="Mobil" loading="lazy">
                                </div>
                                @endif

                                @if($isServiceActive('Unit Peminjaman Fasilitas Umum'))
                                <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="3" data-name="Unit Peminjaman Fasilitas Umum" onclick="window.location.href='{{ $isLoggedInWithRegion ? route('user.fasilitas-umum.equipment') . '?region_id=' . $userRegionId : route('bumdes.profil') . '?redirect=user.fasilitas-umum.equipment' }}'">
                                    <img src="{{ asset('User/img/elemen/fasilitas.png') }}" alt="Fasilitas" loading="lazy">
                                </div>
                                @endif

                                @if($isServiceActive('Pasar Daerah'))
                                <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="4" data-name="Pasar Daerah" onclick="window.location.href='{{ $isLoggedInWithRegion ? route('pasar.index') . '?region_id=' . $userRegionId : route('pasar.index') }}'">
                                    <img src="{{ asset('Admin/img/pasardaerah/PasarDaerah2.png') }}" alt="Pasar Daerah" loading="lazy" onerror="this.src='{{ asset('User/img/elemen/F1.png') }}'">
                                </div>
                                @endif

                                @if($isServiceActive('Pelaporan Warga'))
                                <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="5" data-name="Pelaporan Warga" onclick="window.location.href='{{ $isLoggedInWithRegion ? route('pelaporan.landing') . '?region_id=' . $userRegionId : route('bumdes.profil') . '?redirect=pelaporan.landing' }}'">
                                    <img src="{{ asset('User/img/elemen/lapor.png') }}" alt="Lapor" loading="lazy">
                                </div>
                                @endif

                                @if($isServiceActive('Pengumuman dan Event'))
                                <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="6" data-name="Kabar dan Informasi Daerah" onclick="window.location.href='{{ $isLoggedInWithRegion ? route('announcements.index') . '?region_id=' . $userRegionId : route('announcements.index') }}'">
                                    <img src="{{ asset('User/img/elemen/KabardanInformasiDaerah.png') }}" alt="Kabar dan Informasi Daerah" loading="lazy" onerror="this.src='{{ asset('User/img/elemen/F3.png') }}'">
                                </div>
                                @endif
                            </div>

                            <div class="unit-nav-wrapper absolute -bottom-6 left-0 right-0 flex items-center justify-center gap-2 sm:gap-4 md:gap-12 z-60 px-2 sm:px-4">
                                <button id="unit-prev" class="bg-white hover:bg-gray-50 text-gray-800 rounded-full p-2 sm:p-3 shadow-lg border border-gray-100 transition-transform active:scale-95 flex-shrink-0">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                <div class="unit-title-box text-center min-w-0 flex-1 max-w-[240px] sm:max-w-none sm:min-w-[300px]">
                                    <h3 id="unit-title" class="text-sm sm:text-xl md:text-2xl font-bold text-black transition-all duration-300 truncate">
                                        Unit Penyewaan Alat
                                    </h3>
                                </div>

                                <button id="unit-next" class="bg-white hover:bg-gray-50 text-gray-800 rounded-full p-2 sm:p-3 shadow-lg border border-gray-100 transition-transform active:scale-95 flex-shrink-0">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                    </svg>
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
                            <p class="text-xs sm:text-base text-gray-500">Pengumuman dan agenda terbaru</p>
                        </div>
                        <a href="{{ route('announcements.index') }}" class="hidden md:flex items-center gap-2 text-[#115789] font-semibold hover:text-blue-500 transition-colors">
                            Lihat Semua <i class="bx bx-right-arrow-alt text-xl"></i>
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
                            Lihat Semua Kabar <i class="bx bx-right-arrow-alt text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Section Tentang Kami -->
            <div class="relative max-w-7xl mx-auto px-6 py-16 overflow-visible">
                <!-- Background Elements - Hanya 2 Oval -->
                <div class="absolute inset-0 pointer-events-none" style="left: -200px; right: -200px;">
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
                    <div class="text-center mb-10">
                        <h2 class="text-3xl font-bold mb-2">
                            <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Tentang</span> 
                            <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Kami</span>
                        </h2>
                    </div>

                    <!-- Text Content dengan Glass Effect -->
                    <div class="max-w-5xl mx-auto">
                        <div class="backdrop-blur-sm bg-white/60 rounded-3xl p-8 md:p-12 border border-white/70 shadow-xl">
                            <div class="space-y-5 text-gray-700 text-base leading-relaxed text-justify">
                                <p>
                                    <span class="font-semibold text-gray-800">SiladesBeng</span> (Sistem Sinergi Layanan dan Aspirasi Desa di Kabupaten Bengkalis) merupakan platform digital terpadu berskala kabupaten yang dirancang khusus untuk memodernisasi tata kelola administrasi dan pelayanan publik di seluruh jaringan kecamatan hingga tingkat desa se-Kabupaten Bengkalis. Platform ini mengintegrasikan berbagai pilar layanan esensial masyarakat dan operasional BUMDes dalam satu pintu.
                                </p>
                                <p>
                                    Melalui SiladesBeng, masyarakat Kabupaten Bengkalis dapat dengan mudah mengakses beragam unit layanan, mulai dari penyewaan alat, pendistribusian gas, peminjaman mobilitas (kendaraan), hingga pemanfaatan fasilitas umum. Di samping itu, sistem ini juga mewadahi fitur <span class="font-medium text-gray-800">Pelaporan Warga</span> serta pusat informasi <span class="font-medium text-gray-800">Kabar dan Informasi Daerah</span> secara <i>real-time</i>. Kami percaya bahwa ekosistem digital yang transparan dan terukur dari jenjang kabupaten hingga pelosok desa ini merupakan kunci utama untuk mewujudkan pelayanan publik yang prima, memajukan perekonomian daerah, dan membangun kemandirian masyarakat Bengkalis yang berkelanjutan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        {{-- DECORATIONS --}}

        <!-- ============================================ -->
        <!-- AREA UNIT PELAYANAN - Pakai 2.webp (WAVE) Kanan + 5.webp (GEOMETRIS ROTASI) -->
        <!-- ============================================ -->
        <img src="{{ asset('User/img/backgrounds/2.webp') }}" class="bg-element bg-wave-right-unit" loading="lazy" />

        <svg class="bg-element bg-squares-right-unit">
            <image href="{{ asset('User/img/backgrounds/5.webp') }}" width="100%" height="100%" loading="lazy" />
        </svg>

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

        /* Background Elements - kept as they require precise positioning */
        .bg-element {
            position: absolute;
            pointer-events: none;
            user-select: none;
            object-fit: contain;
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

        /* Area UNIT PELAYANAN - Pakai 2.webp (WAVE) BESAR + 5.webp (GEOMETRIS) SUPER BESAR */
        .bg-wave-right-unit {
            top: 25%;
            right: -150px;
            width: 580px;
            transform: rotate(15deg) scaleX(-1);
            opacity: 0.92;
            z-index: 2;
        }

        /* 5.webp DIPERBESAR LAGI - SUPER BESAR! */
        .bg-squares-right-unit {
            top: 20%;
            right: -230px;
            width: 580px;
            transform: rotate(-100deg) scale(1.5);
            opacity: 0.90;
            z-index: 2;
        }

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

        /* RESPONSIVE MOBILE - 3 COLUMN LAYOUT (CENTER FOCUS) */
        @media (max-width: 768px) {
            #unit-carousel-container {
                padding-top: 1rem !important;
                padding-bottom: 2rem !important;
            }
            .unit-stage-wrapper {
                height: 200px !important;
            }
            .unit-card {
                width: 90px !important;
                height: 90px !important;
                top: 36% !important;
            }

            /* Slot Kiri (Background Preview) */
            .state-0 {
                left: 15% !important;
                transform: translate(-50%, -50%) scale(0.65) !important;
                opacity: 0.5 !important;
                z-index: 20 !important;
                filter: grayscale(20%) !important;
            }

            /* Slot Tengah (Focus Terpusat, Proporsional dan Rapi) */
            .state-1 {
                left: 50% !important;
                transform: translate(-50%, -50%) scale(1.15) !important;
                opacity: 1 !important;
                z-index: 50 !important;
                filter: grayscale(0%) drop-shadow(0 8px 16px rgba(0,0,0,0.18)) !important;
            }

            /* Slot Kanan (Background Preview) */
            .state-2 {
                left: 85% !important;
                transform: translate(-50%, -50%) scale(0.65) !important;
                opacity: 0.5 !important;
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
                bottom: 4px !important;
                left: 0 !important;
                right: 0 !important;
                width: 100% !important;
                padding: 0 16px !important;
                gap: 10px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            #unit-prev, #unit-next {
                width: 36px !important;
                height: 36px !important;
                min-width: 36px !important;
                padding: 0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                box-shadow: 0 2px 8px rgba(0,0,0,0.12) !important;
            }
            #unit-prev svg, #unit-next svg {
                width: 18px !important;
                height: 18px !important;
            }
            .unit-title-box {
                min-width: 0 !important;
                max-width: 210px !important;
                flex: 1 !important;
            }
            #unit-title {
                font-size: 0.95rem !important;
                line-height: 1.25 !important;
            }

            /* Kurangi opasitas latar belakang dekoratif di ponsel agar kontras terbaca */
            .bg-element {
                opacity: 0.25 !important;
                max-width: 100vw !important;
                overflow: hidden !important;
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
                this.initUnitCarousel();
                this.initNavbarMarginSync();
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

                const prevButton = document.getElementById('carousel-prev');
                const nextButton = document.getElementById('carousel-next');
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

                const prevSlide = () => {
                    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
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

                if (nextButton) {
                    const newNext = nextButton.cloneNode(true);
                    nextButton.parentNode.replaceChild(newNext, nextButton);
                    newNext.addEventListener('click', () => {
                        nextSlide();
                        resetAutoSlide();
                    });
                }

                if (prevButton) {
                    const newPrev = prevButton.cloneNode(true);
                    prevButton.parentNode.replaceChild(newPrev, prevButton);
                    newPrev.addEventListener('click', () => {
                        prevSlide();
                        resetAutoSlide();
                    });
                }

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

            // Unit Carousel
            initUnitCarousel() {
                const cards = Array.from(document.querySelectorAll('.unit-card'));
                if (cards.length === 0) return;

                const titleElement = document.getElementById('unit-title');
                const nextBtn = document.getElementById('unit-next');
                const prevBtn = document.getElementById('unit-prev');

                const n = cards.length;
                let currentIndex = 0;
                let autoSlideInterval;
                const autoSlideDelay = 3000;

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

                        if (diff === 0 && titleElement) {
                            titleElement.style.opacity = '0';
                            setTimeout(() => {
                                titleElement.textContent = card.getAttribute('data-name');
                                titleElement.style.opacity = '1';
                            }, 200);
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

                const startAutoSlide = () => {
                    if (n <= 1) return;
                    clearInterval(autoSlideInterval);
                    autoSlideInterval = setInterval(handleNext, autoSlideDelay);
                };

                const resetAutoSlide = () => {
                    if (n <= 1) return;
                    clearInterval(autoSlideInterval);
                    startAutoSlide();
                };

                if (nextBtn) {
                    const newNext = nextBtn.cloneNode(true);
                    nextBtn.parentNode.replaceChild(newNext, nextBtn);
                    newNext.addEventListener('click', () => {
                        handleNext();
                        resetAutoSlide();
                    });
                    newNext.parentElement.classList.remove('z-60');
                    newNext.parentElement.classList.add('z-[60]');
                }
                if (prevBtn) {
                    const newPrev = prevBtn.cloneNode(true);
                    prevBtn.parentNode.replaceChild(newPrev, prevBtn);
                    newPrev.addEventListener('click', () => {
                        handlePrev();
                        resetAutoSlide();
                    });
                }

                // Pause on hover (optional but good UX)
                const container = document.getElementById('unit-carousel-container');
                if (container) {
                    container.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
                    container.addEventListener('mouseleave', startAutoSlide);
                }

                updateCarousel();
                startAutoSlide();

                // Click handlers are managed via inline onclick attributes in HTML
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

