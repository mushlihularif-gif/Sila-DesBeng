@extends('layouts.user')

@section('page')
    {{-- NAVIGASI --}}

    <!-- Bagian Carousel -->


    {{-- UTAMA --}}<main class="flex-grow relative w-full overflow-x-hidden">

        {{-- BAGIAN BERANDA --}}<section id="beranda" class="relative z-10">
            <div class="w-full mx-auto">
                <div class="relative overflow-hidden group">
                    <!-- Wadah Slide -->
                    <div id="carousel-slides" class="flex transition-transform duration-500 ease-out">
                        <!-- Slide 1 -->
                        <div class="carousel-slide min-w-full flex-shrink-0">
                            <!-- Mobile Image -->
                            <img src="{{ asset('User/img/elemen/entrance-mobile.png') }}" alt="Slide 1 Mobile"
                                fetchpriority="high"
                                class="block md:hidden w-full h-[400px] object-cover">
                            <!-- Desktop Image -->
                            <img src="{{ asset('User/img/elemen/entrance.png') }}" alt="Slide 1"
                                fetchpriority="high"
                                class="hidden md:block w-full md:h-[40vw] lg:h-[45vw] object-cover">
                        </div>
                        <!-- Slide 2 -->
                        <div class="carousel-slide min-w-full flex-shrink-0">
                            <!-- Mobile Image -->
                            <img src="{{ asset('User/img/elemen/biru-mobile.png') }}" alt="Slide 2 Mobile"
                                loading="lazy"
                                class="block md:hidden w-full h-[400px] object-cover">
                            <!-- Desktop Image -->
                            <img src="{{ asset('User/img/elemen/biru.png') }}" alt="Slide 2"
                                loading="lazy"
                                class="hidden md:block w-full md:h-[40vw] lg:h-[45vw] object-cover">
                        </div>
                        <!--Slide 3-->
                        <div class="carousel-slide min-w-full flex-shrink-0">
                            <!-- Mobile Image -->
                            <img src="{{ asset('User/img/elemen/ppq-mobile.png') }}" alt="Slide 3 Mobile"
                                loading="lazy"
                                class="block md:hidden w-full h-[400px] object-cover">
                            <!-- Desktop Image -->
                            <img src="{{ asset('User/img/elemen/ppq.png') }}" alt="Slide 3"
                                loading="lazy"
                                class="hidden md:block w-full md:h-[40vw] lg:h-[45vw] object-fill">
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

                    <!-- Indicators - 3 Dots Only -->
                    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2.5 z-10">
                        <button
                            class="carousel-indicator w-8 h-2.5 rounded-full bg-white shadow-md transition-all duration-300"
                            data-slide="0"></button>
                        <button
                            class="carousel-indicator w-2.5 h-2.5 rounded-full bg-white/50 hover:bg-white/75 shadow-md transition-all duration-300"
                            data-slide="1"></button>
                        <button
                            class="carousel-indicator w-2.5 h-2.5 rounded-full bg-white/50 hover:bg-white/75 shadow-md transition-all duration-300"
                            data-slide="2"></button>
                    </div>
                </div>
            </div>

            <!-- Bilah Pencarian dengan Batas Gradien -->
            <div class="max-w-screen-2xl mx-auto px-5 py-8">
                <div class="max-w-2xl mx-auto">
                    <form action="{{ route('beranda') }}" method="GET" class="relative group">
                        <!-- Gradient Border -->
                        <div
                            class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 via-blue-400 to-amber-400 rounded-full opacity-80 group-hover:opacity-100 transition-opacity duration-300">
                        </div>

                        <!-- Search Input -->
                        <div class="relative flex items-center bg-white rounded-full overflow-hidden">
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari produk atau kategori..."
                                class="flex-1 px-8 py-3.5 text-gray-700 text-[15px] focus:outline-none bg-transparent">

                            <!-- Search Button -->
                            <button type="submit"
                                class="flex-shrink-0 px-6 py-3.5 text-blue-600 hover:text-blue-700 transition-colors duration-200">
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
            <div class="max-w-7xl mx-auto px-6 py-4">
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

            <!-- Section Populer -->
            <div class="max-w-7xl mx-auto px-6 py-12">
                <div class="max-w-7xl mx-auto">
                    <!-- Judul Populer -->
                    <div class="text-center mb-8 relative">
                        <h2
                            class="text-3xl font-bold bg-gradient-to-r from-[#115789] to-blue-300 bg-clip-text text-transparent relative inline-block drop-shadow-[0_0_15px_rgba(59,130,246,0.5)]">
                            Populer
                        </h2>
                    </div>
                    <!-- Flex Container (Centered) -->
                    <div class="flex flex-wrap justify-center gap-6 max-w-6xl mx-auto">
                        @forelse($popularProducts as $item)
                        <!-- Product Card -->
                        <div class="flex flex-col items-center">
                            <div onclick="window.location.href='{{ $item->link }}'"
                                class="bg-white/80 backdrop-blur-sm rounded-lg border border-white shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group cursor-pointer w-full max-w-[280px]">
                                <div
                                    class="aspect-square p-4 flex items-center justify-center bg-gradient-to-br from-white/50 to-blue-50/30 relative">
                                    <img src="{{ Str::startsWith($item->image, ['http', 'https', 'User', 'Admin']) ? asset($item->image) : asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                                        loading="lazy"
                                        class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-300">
                                    @if($loop->iteration <= 2)
                                    <span class="absolute top-2 right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                        HOT
                                    </span>
                                    @endif
                                </div>
                                <div class="p-3 text-center bg-white/90 backdrop-blur-sm">
                                    <h3 class="text-sm font-semibold text-gray-800 line-clamp-1">{{ $item->name }}</h3>
                                    <p class="text-xs text-blue-600 font-medium mt-1">{{ $item->price_formatted }} <span class="text-gray-400">/ {{ $item->unit }}</span></p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-full text-center py-8">
                            <p class="text-gray-500">Belum ada data produk populer untuk tahun ini.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Section Unit Pelayanan -->
            <div id="unit-carousel-container" class="max-w-7xl mx-auto px-6 py-16 overflow-hidden">
                <div class="max-w-7xl mx-auto">

                    <div class="text-center mb-16 relative">
                        <h2
                            class="text-3xl font-bold bg-gradient-to-r from-[#115789] to-blue-300 bg-clip-text text-transparent relative inline-block drop-shadow-[0_0_15px_rgba(59,130,246,0.5)]">
                            Unit Pelayanan
                        </h2>
                    </div>

                    <div class="relative h-[400px] w-full flex justify-center items-center">

                        <div class="relative w-full max-w-6xl mx-auto h-full">

                            <div class="unit-card" data-index="0" data-name="Unit Penyewaan Alat">
                                <img src="{{ asset('User/img/elemen/F1.png') }}" alt="Alat" loading="lazy">
                            </div>

                            <div class="unit-card" data-index="1" data-name="Unit Penjualan Gas">
                                <img src="{{ asset('User/img/elemen/F2.png') }}" alt="Gas" loading="lazy">
                            </div>

                            <div class="unit-card" data-index="2" data-name="Unit Penyewaan Alat">
                                <img src="{{ asset('User/img/elemen/F1.png') }}" alt="Alat" loading="lazy">
                            </div>

                            <div class="unit-card" data-index="3" data-name="Unit Penjualan Gas">
                                <img src="{{ asset('User/img/elemen/F2.png') }}" alt="Gas" loading="lazy">
                            </div>
                        </div>

                        <div
                            class="absolute -bottom-6 left-0 right-0 flex items-center justify-center gap-4 md:gap-12 z-60">
                            <button id="unit-prev"
                                class="bg-white hover:bg-gray-50 text-gray-800 rounded-full p-3 shadow-lg border border-gray-100 transition-transform active:scale-95">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div class="min-w-[300px] text-center">
                                <h3 id="unit-title"
                                    class="text-xl md:text-2xl font-bold text-black transition-all duration-300">
                                    Unit Penyewaan Alat
                                </h3>
                            </div>

                            <button id="unit-next"
                                class="bg-white hover:bg-gray-50 text-gray-800 rounded-full p-3 shadow-lg border border-gray-100 transition-transform active:scale-95">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Grafik Umum -->
            <div class="max-w-6xl mx-auto px-6 py-16">
                <!-- Title dengan gradient -->
                <div class="text-center mb-12">
                    <h2
                        class="text-3xl font-bold bg-gradient-to-r from-[#115789] to-blue-300 bg-clip-text text-transparent relative inline-block drop-shadow-[0_0_15px_rgba(59,130,246,0.5)]">
                        Grafik Umum
                    </h2>
                </div>
                <div class="max-w-5xl mx-auto space-y-12">
                    <!-- Grafik Kinerja BUMDES -->
                    <div>
                        <div
                            class="relative rounded-3xl p-6 backdrop-blur-md bg-white/20 border border-white/30 shadow-[0_8px_32px_0_rgba(31,38,135,0.15)]">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-5">
                                <h3 class="text-xl font-bold text-gray-800 mb-3 md:mb-0">Kinerja BUMDES</h3>
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <select
                                        class="px-4 py-2 text-sm border border-white/40 rounded-lg bg-white/60 backdrop-blur-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white/80 transition-all">
                                        <option>Desa Pematang Duku Timur</option>
                                    </select>
                                    <select id="kinerjaYearSelect"
                                        class="px-4 py-2 text-sm border border-white/40 rounded-lg bg-white/60 backdrop-blur-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white/80 transition-all">
                                        @foreach($availableYears as $optYear)
                                            <option value="{{ $optYear }}" {{ $optYear == $year ? 'selected' : '' }}>{{ $optYear }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="bg-white/30 backdrop-blur-sm rounded-2xl p-5 border border-white/20">
                                <div id="kinerjaChart"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Grafik Unit Populer -->
                    <div>
                        <div
                            class="relative rounded-3xl p-6 backdrop-blur-md bg-white/20 border border-white/30 shadow-[0_8px_32px_0_rgba(31,38,135,0.15)]">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-5">
                                <h3 class="text-xl font-bold text-gray-800 mb-3 md:mb-0">Unit Populer</h3>
                                <select id="populerYearSelect"
                                    class="px-4 py-2 text-sm border border-white/40 rounded-lg bg-white/60 backdrop-blur-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white/80 transition-all">
                                    @foreach($availableYears as $optYear)
                                        <option value="{{ $optYear }}" {{ $optYear == $year ? 'selected' : '' }}>{{ $optYear }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="bg-white/30 backdrop-blur-sm rounded-2xl p-5 mb-5 border border-white/20">
                                <div id="unitChart"></div>
                            </div>
                            <!-- Legend - Compact (2 unit saja) -->
                            <div class="flex flex-wrap justify-center gap-6 text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 bg-[#f59e0b] rounded"></div>
                                    <span class="text-gray-700 font-medium">Unit Penyewaan Alat</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 bg-[#3b82f6] rounded"></div>
                                    <span class="text-gray-700 font-medium">Unit Penjualan Gas</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Tombol Lihat Lainnya -->
                    <div class="text-center">
                        <a href="{{ route('bumdes.laporan') }}"
                            class="inline-block px-10 py-3 bg-white/70 backdrop-blur-sm text-[#45aaf2] border-2 border-[#45aaf2] rounded-full font-semibold hover:bg-[#45aaf2] hover:text-white transition-all duration-300 shadow-md hover:shadow-xl">
                            Lihat Lainnya
                        </a>
                    </div>
                </div>
            </div>

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
                        <h2
                            class="text-3xl font-bold bg-gradient-to-r from-[#115789] to-blue-300 bg-clip-text text-transparent relative inline-block drop-shadow-[0_0_15px_rgba(59,130,246,0.5)]">
                            Tentang Kami
                        </h2>
                    </div>

                    <!-- Text Content dengan Glass Effect -->
                    <div class="max-w-5xl mx-auto">
                        <div class="backdrop-blur-sm bg-white/60 rounded-3xl p-8 md:p-12 border border-white/70 shadow-xl">
                            <div class="space-y-5 text-gray-700 text-base leading-relaxed text-justify">
                                <p>
                                    <span class="font-semibold text-gray-800">iSewa</span> merupakan platform digital
                                    terpadu yang dirancang untuk mendukung kegiatan operasional dan pelayanan BUMDes secara
                                    modern dan efisien. Sistem ini hadir sebagai solusi inovatif untuk mengelola berbagai
                                    unit usaha desa seperti penyewaan alat, dan layanan pembelian gas.
                                    Melalui iSewa, proses administrasi dan transaksi menjadi lebih cepat, transparan, dan
                                    mudah dijangkau oleh masyarakat.
                                </p>
                                <p>
                                    Dengan mengedepankan kemudahan akses dan efisiensi layanan, iSewa membantu BUMDes dalam
                                    meningkatkan produktivitas, memperluas jangkauan usaha, serta memperkuat perekonomian
                                    desa secara berkelanjutan. Kami percaya bahwa digitalisasi layanan desa merupakan
                                    langkah penting menuju kemandirian ekonomi masyarakat, dan iSewa hadir untuk mewujudkan
                                    hal tersebut dengan sistem yang modern, aman, dan berorientasi pada kemajuan desa.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        {{-- DECORATIONS --}}

        <!-- ============================================ -->
        <!-- AREA POPULER - Pakai 4.webp (BLUR) Kiri & Kanan -->
        <!-- ============================================ -->
        <svg class="bg-element bg-blur-left-populer">
            <image href="{{ asset('User/img/backgrounds/4.webp') }}" width="100%" height="100%" loading="lazy" />
        </svg>

        <svg class="bg-element bg-blur-right-populer">
            <image href="{{ asset('User/img/backgrounds/4.webp') }}" width="100%" height="100%" loading="lazy" />
        </svg>

        <!-- ============================================ -->
        <!-- BAWAH POPULER - Pakai 2.webp (WAVE) Kiri -->
        <!-- ============================================ -->
        <img src="{{ asset('User/img/backgrounds/2.webp') }}" class="bg-element bg-wave-left-lower-populer" loading="lazy" />

        <!-- ============================================ -->
        <!-- AREA UNIT PELAYANAN - Pakai 2.webp (WAVE) Kanan + 5.webp (GEOMETRIS ROTASI) -->
        <!-- ============================================ -->
        <img src="{{ asset('User/img/backgrounds/2.webp') }}" class="bg-element bg-wave-right-unit" loading="lazy" />

        <svg class="bg-element bg-squares-right-unit">
            <image href="{{ asset('User/img/backgrounds/5.webp') }}" width="100%" height="100%" loading="lazy" />
        </svg>

        <!-- ============================================ -->
        <!-- AREA GRAFIK UMUM - Pakai 3.webp (WAVE BESAR TENGAH) -->
        <!-- ============================================ -->
        <img src="{{ asset('User/img/backgrounds/3.webp') }}" class="bg-element bg-wave-center-grafik" loading="lazy" />

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


        /* Area Carousel/Hero - TIDAK PAKAI BACKGROUND */

        /* Area POPULER - Pakai 4.webp (BLUR) KIRI BESAR + KANAN BAWAH BESAR (KEMBAR) */
        .bg-blur-left-populer {
            top: 21%;
            left: -220px;
            width: 650px;
            transform: rotate(-15deg) scaleX(1.2) scale(3.0);
            opacity: 0.88;
            z-index: 2;
        }

        /* KEMBARAN 4.webp di KANAN BAWAH - MIRROR HORIZONTAL */
        .bg-blur-right-populer {
            top: 27%;
            right: -180px;
            width: 680px;
            transform: rotate(18deg) scaleX(-1.5) scale(3.0);
            opacity: 0.90;
            z-index: 2;
        }

        /* Bawah POPULER / Atas UNIT PELAYANAN - Pakai 2.webp (WAVE) BESAR */
        .bg-wave-left-lower-populer {
            top: 30%;
            left: -140px;
            width: 550px;
            transform: rotate(-8deg);
            opacity: 0.90;
            z-index: 2;
        }

        /* Area UNIT PELAYANAN - Pakai 2.webp (WAVE) BESAR + 5.webp (GEOMETRIS) SUPER BESAR */
        .bg-wave-right-unit {
            top: 40%;
            right: -150px;
            width: 580px;
            transform: rotate(15deg) scaleX(-1);
            opacity: 0.92;
            z-index: 2;
        }

        /* 5.webp DIPERBESAR LAGI - SUPER BESAR! */
        .bg-squares-right-unit {
            top: 35%;
            right: -230px;
            width: 580px;
            transform: rotate(-100deg) scale(1.5);
            opacity: 0.90;
            z-index: 2;
        }

        /* Area GRAFIK UMUM - Pakai 3.webp (WAVE BESAR TENGAH) */
        .bg-wave-center-grafik {
            top: 45%;
            left: 50%;
            width: 115%;
            transform: translateX(-50%) scale(1.1);
            opacity: 0.95;
            z-index: 1;
        }

        /* --- UNIT CAROUSEL STYLES (4 VISIBLE ITEMS) --- */
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

        /* POSISI 3: KANAN UJUNG*/
        .state-3 {
            left: 100% !important;
            /* Benar-benar di ujung kanan */
            transform: translate(-50%, -50%) scale(0.5) !important;
            opacity: 0.6;
            z-index: 10;
            filter: grayscale(30%);
        }

        /* RESPONSIVE MOBILE - 3 COLUMN LAYOUT (CENTER FOCUS) */
        @media (max-width: 768px) {
            .unit-card {
                width: 150px; /* Slightly larger for visibility */
                height: 150px;
            }

            /* Slot Kiri (Background) */
            .state-0 {
                left: 10% !important;
                transform: translate(-50%, -50%) scale(0.6) !important;
                opacity: 0.6 !important;
                z-index: 20;
                filter: grayscale(20%);
            }

            /* Slot Tengah (Focus) */
            .state-1 {
                left: 50% !important;
                transform: translate(-50%, -50%) scale(1.8) !important;
                opacity: 1 !important;
                z-index: 50;
                filter: grayscale(0%) drop-shadow(0 10px 15px rgba(0,0,0,0.2));
            }

            /* Slot Kanan (Background) */
            .state-2 {
                left: 90% !important;
                transform: translate(-50%, -50%) scale(0.6) !important;
                opacity: 0.6 !important;
                z-index: 20;
                filter: grayscale(20%);
            }

            /* Antrian (Hidden) */
            .state-3 {
                left: 150% !important;
                transform: translate(-50%, -50%) scale(0.5) !important;
                opacity: 0;
            }
        }
    </style>
@endpush

@push('scripts')
    {{-- ApexCharts Library - Minified Version --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>
    <script>
        const BerandaPage = {
            // Initialize all components
            init() {
                this.initCarousel();
                this.initCharts();
                this.initUnitCarousel();
                this.initYearSelectors();
            },

            // Initialize Year Selectors
            initYearSelectors() {
                const handleYearChange = function() {
                    const selectedYear = this.value;
                    const url = new URL(window.location.href);
                    url.searchParams.set('year', selectedYear);
                    window.location.href = url.toString();
                };

                const kinerjaSelect = document.getElementById('kinerjaYearSelect');
                if (kinerjaSelect) {
                    kinerjaSelect.addEventListener('change', handleYearChange);
                }

                const populerSelect = document.getElementById('populerYearSelect');
                if (populerSelect) {
                    populerSelect.addEventListener('change', handleYearChange);
                }
            },

            // Carousel initialization
            initCarousel() {
                const carouselSlides = document.getElementById('carousel-slides');
                if (!carouselSlides) return;

                const prevButton = document.getElementById('carousel-prev');
                const nextButton = document.getElementById('carousel-next');
                // Use let so we can update the reference after cloning
                let indicators = document.querySelectorAll('.carousel-indicator');

                let currentSlide = 0;
                const totalSlides = 3;
                let autoSlideInterval;
                const autoSlideDelay = 7000; // 7 Seconds

                const goToSlide = (slideIndex) => {
                    currentSlide = slideIndex;
                    carouselSlides.style.transform = `translateX(-${slideIndex * 100}%)`;

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

            // Charts initialization
            initCharts() {
                // if (typeof ApexCharts === 'undefined') return; // Removed to allow init even if lib loads late

                this.initKinerjaChart();
                this.initUnitChart();
            },

            // Kinerja BUMDES Chart
            initKinerjaChart() {
                const container = document.querySelector("#kinerjaChart");
                if (!container) return;

                container.innerHTML = '';

                const options = {
                    series: [{
                        name: 'Kinerja (Juta Rupiah)',
                        data: @json($kinerjaData['data'])
                    }],
                    chart: {
                        type: 'area',
                        height: 280,
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        },
                        background: 'transparent'
                    },
                    colors: ['#f59e0b'],
                    stroke: {
                        curve: 'smooth',
                        width: 2.5
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.45,
                            opacityTo: 0.05,
                            stops: [0, 90, 100]
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    markers: {
                        size: 0,
                        hover: {
                            size: 5
                        }
                    },
                    xaxis: {
                        categories: @json($kinerjaData['categories']),
                        labels: {
                            style: {
                                colors: '#374151',
                                fontSize: '12px',
                                fontWeight: 500
                            }
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    yaxis: {
                        tickAmount: 5,
                        labels: {
                            formatter: (val) => val.toFixed(1),
                            style: {
                                colors: '#6b7280',
                                fontSize: '11px'
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e5e7eb',
                        strokeDashArray: 3,
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        yaxis: {
                            lines: {
                                show: true
                            }
                        },
                        padding: {
                            top: 0,
                            right: 5,
                            bottom: 0,
                            left: 5
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: (val) => 'Rp ' + val.toFixed(1) + ' Juta'
                        }
                    }
                };

                const chart = new ApexCharts(container, options);
                chart.render();
            },

            // Unit Populer Chart
            initUnitChart() {
                const container = document.querySelector("#unitChart");
                if (!container) return;

                container.innerHTML = '';

                const options = {
                    series: [{
                            name: 'Unit Penyewaan Alat',
                            data: @json($unitPopulerData['rental'])
                        },
                        {
                            name: 'Unit Penjualan Gas',
                            data: @json($unitPopulerData['gas'])
                        }
                    ],
                    chart: {
                        type: 'bar',
                        height: 280,
                        toolbar: {
                            show: false
                        },
                        stacked: false,
                        background: 'transparent'
                    },
                    colors: ['#f59e0b', '#3b82f6'],
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '50%',
                            borderRadius: 4,
                            dataLabels: {
                                position: 'top'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: @json($unitPopulerData['categories']),
                        labels: {
                            style: {
                                colors: '#374151',
                                fontSize: '12px',
                                fontWeight: 500
                            }
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#6b7280',
                                fontSize: '11px'
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e5e7eb',
                        strokeDashArray: 3,
                        padding: {
                            top: 0,
                            right: 10,
                            bottom: 0,
                            left: 5
                        }
                    },
                    legend: {
                        show: false
                    },
                    tooltip: {
                        shared: true,
                        intersect: false
                    }
                };

                const chart = new ApexCharts(container, options);
                chart.render();
            },

            // Unit Carousel
            initUnitCarousel() {
                const cards = Array.from(document.querySelectorAll('.unit-card'));
                if (cards.length === 0) return;

                const titleElement = document.getElementById('unit-title');
                const nextBtn = document.getElementById('unit-next');
                const prevBtn = document.getElementById('unit-prev');

                const stateClasses = ['state-0', 'state-1', 'state-2', 'state-3'];
                let positions = [1, 2, 3, 0];

                let autoSlideInterval;
                const autoSlideDelay = 3000; // 3 seconds delay

                const updateCarousel = () => {
                    cards.forEach((card, index) => {
                        card.classList.remove(...stateClasses);
                        const currentPos = positions[index];
                        card.classList.add(stateClasses[currentPos]);

                        if (currentPos === 1 && titleElement) {
                            titleElement.style.opacity = '0';
                            setTimeout(() => {
                                titleElement.textContent = card.getAttribute('data-name');
                                titleElement.style.opacity = '1';
                            }, 200);
                        }
                    });
                };

                const handleNext = () => {
                    positions = positions.map(pos => (pos - 1 < 0 ? 3 : pos - 1));
                    updateCarousel();
                };

                const handlePrev = () => {
                    positions = positions.map(pos => (pos + 1 > 3 ? 0 : pos + 1));
                    updateCarousel();
                };

                const startAutoSlide = () => {
                    clearInterval(autoSlideInterval);
                    autoSlideInterval = setInterval(handleNext, autoSlideDelay);
                };

                const resetAutoSlide = () => {
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
                    // Fix: Ensure z-index is high enough
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

                // Add click handler for rental equipment cards
                const rentalCards = document.querySelectorAll('.unit-card[data-name="Unit Penyewaan Alat"]');
                rentalCards.forEach(card => {
                    card.style.cursor = 'pointer';
                    card.addEventListener('click', () => {
                        window.location.href = "{{ route('rental.equipment') }}";
                    });
                });

                // Add click handler for gas sales cards
                const gasCards = document.querySelectorAll('.unit-card[data-name="Unit Penjualan Gas"]');
                gasCards.forEach(card => {
                    card.style.cursor = 'pointer';
                    card.addEventListener('click', () => {
                        window.location.href = "{{ route('gas.sales') }}";
                    });
                });
            },
        };
        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            BerandaPage.init();
        });

        // Check if user just logged out and show login modal
        @if(session('logout_success'))
            document.addEventListener('DOMContentLoaded', function() {
                // Wait a bit for the page to fully load
                setTimeout(function() {
                    const loginButton = document.getElementById('btn-open-login');
                    if (loginButton) {
                        loginButton.click();
                    }
                }, 300);
            });
        @endif
    </script>
@endpush
