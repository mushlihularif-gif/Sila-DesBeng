@extends('layouts.user')

@section('page')
@php
    $cleanPlat = $ambulans->plat_nomor ?: str_replace('Plat: ', '', $ambulans->deskripsi);
    $images = collect([$ambulans->foto, $ambulans->foto_2, $ambulans->foto_3])->filter()->values();
    $hasMultipleImages = $images->count() > 1;
    $rawDeskripsi = $ambulans->deskripsi;
    $hasCustomDesc = $rawDeskripsi && !str_starts_with(trim($rawDeskripsi), 'Plat:');
@endphp

<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-28 pb-16">
        <!-- Elemen Dekoratif Latar Belakang -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <svg class="absolute top-0 left-0 w-[500px] h-[400px] opacity-30" style="transform: translate(-20%, -10%);">
                <defs>
                    <linearGradient id="redWave1" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#f87171;stop-opacity:0.6" />
                        <stop offset="100%" style="stop-color:#fca5a5;stop-opacity:0.3" />
                    </linearGradient>
                </defs>
                <path d="M0,100 Q150,50 300,100 T600,100 L600,0 L0,0 Z" fill="url(#redWave1)" />
            </svg>
            <div class="absolute top-20 right-0" style="transform: translateX(30%) rotate(15deg);">
                <svg width="300" height="300" viewBox="0 0 300 300" class="opacity-20">
                    <rect x="50" y="50" width="80" height="80" fill="#f87171" transform="rotate(45 90 90)" opacity="0.4"/>
                    <rect x="150" y="80" width="60" height="60" fill="#fca5a5" transform="rotate(30 180 110)" opacity="0.3"/>
                </svg>
            </div>
            <svg class="absolute bottom-0 right-0 w-[500px] h-[350px] opacity-30" style="transform: translate(20%, 15%);">
                <defs>
                    <linearGradient id="blueWave2" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:0.4" />
                        <stop offset="100%" style="stop-color:#60a5fa;stop-opacity:0.2" />
                    </linearGradient>
                </defs>
                <path d="M0,150 Q150,100 300,150 T600,150 L600,400 L0,400 Z" fill="url(#blueWave2)" />
            </svg>
        </div>

        <div class="max-w-6xl mx-auto px-6 relative z-10">
            <!-- Navigasi Kembali -->
            <div class="mb-6 flex items-center justify-between">
                <a href="{{ route('user.ambulans.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-red-600 transition-colors">
                    <i class="bx bx-left-arrow-alt text-xl"></i>
                    <span>Kembali ke Layanan Ambulans</span>
                </a>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 text-red-600 border border-red-200 rounded-full text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                    Siaga 24 Jam
                </span>
            </div>

            <!-- Detail Card -->
            <div class="bg-white rounded-3xl shadow-2xl p-6 sm:p-8 md:p-10 border border-gray-100">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Sisi Kiri: Foto Carousel & Plat -->
                    <div class="lg:w-5/12 flex-shrink-0">
                        <!-- Product Image Carousel -->
                        <div class="relative aspect-square overflow-hidden rounded-2xl shadow-md mb-4 group w-full bg-gray-100">
                            @if($images->count() > 0)
                                <!-- Slider Images Container -->
                                <div id="product-carousel" class="flex w-full h-full transition-transform duration-500 ease-out">
                                    @foreach($images as $index => $image)
                                    <div class="w-full h-full flex-shrink-0 flex-grow-0">
                                        <img src="{{ asset('storage/' . $image) }}" 
                                             alt="{{ $ambulans->nama_mobil }} - Foto {{ $index + 1 }}"
                                             {{ $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"' }}
                                             class="w-full h-full object-cover">
                                    </div>
                                    @endforeach
                                </div>

                                @if($hasMultipleImages)
                                <!-- Tombol Navigasi Geser Gambar -->
                                <button id="carousel-prev" type="button" aria-label="Foto Sebelumnya"
                                        class="absolute left-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 rounded-full p-2.5 shadow-lg opacity-90 sm:opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110 z-10">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>

                                <button id="carousel-next" type="button" aria-label="Foto Berikutnya"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 rounded-full p-2.5 shadow-lg opacity-90 sm:opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110 z-10">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>

                                <!-- Dot Indicators -->
                                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10 bg-black/30 backdrop-blur-sm px-3 py-1.5 rounded-full">
                                    @foreach($images as $index => $image)
                                    <button type="button"
                                            class="carousel-indicator {{ $index === 0 ? 'w-6 bg-white' : 'w-2 bg-white/50' }} h-2 rounded-full transition-all duration-300 hover:bg-white/90"
                                            data-slide="{{ $index }}"
                                            aria-label="Slide {{ $index + 1 }}">
                                    </button>
                                    @endforeach
                                </div>
                                @endif
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 p-6 text-center">
                                    <i class="bx bx-car text-6xl text-red-300 mb-2"></i>
                                    <span class="text-xs font-semibold text-gray-500">Foto resmi armada belum diunggah</span>
                                </div>
                            @endif

                            <!-- Kategori Badge Overlay -->
                            <div class="absolute top-3 left-3 px-3 py-1 rounded-full bg-red-600 text-white text-xs font-bold shadow flex items-center gap-1">
                                <i class="bx bxs-ambulance"></i>
                                <span>Ambulans Siaga Desa</span>
                            </div>
                        </div>

                        <!-- Plat Nomor & Info Wilayah -->
                        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 space-y-2.5 text-xs sm:text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 font-medium">Plat Nomor Polisi</span>
                                <span class="font-mono font-black text-gray-900 bg-white px-2.5 py-1 rounded-lg border border-gray-200 shadow-xs">
                                    {{ $cleanPlat ?: 'Tersedia' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 font-medium">Status Operasional</span>
                                <span class="font-bold text-green-600 flex items-center gap-1">
                                    <i class="bx bx-check-circle"></i> Siaga Medis
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 font-medium">Biaya Layanan</span>
                                <span class="font-bold text-gray-900">Program Desa / Siaga Warga</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sisi Kanan: Informasi Armada & Supir -->
                    <div class="lg:w-7/12 flex flex-col">
                        <!-- Judul Armada -->
                        <div class="mb-4">
                            <span class="inline-block px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold mb-2">
                                Layanan Kesehatan & Transportasi Medis
                            </span>
                            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 leading-tight">
                                {{ $ambulans->nama_mobil }}
                            </h1>
                        </div>

                        <!-- Deskripsi Armada -->
                        <div class="mb-6">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Deskripsi & Fasilitas Armada</h3>
                            <div class="text-gray-700 text-sm leading-relaxed bg-gray-50/70 rounded-2xl p-4 border border-gray-100">
                                @if($hasCustomDesc)
                                    <p class="whitespace-pre-line">{{ $rawDeskripsi }}</p>
                                @else
                                    <p>Armada ambulans siaga desa yang dipersiapkan untuk melayani rujukan darurat, penjemputan warga yang membutuhkan penanganan medis segera, persalinan, serta kebutuhan transportasi medis warga desa.</p>
                                @endif
                            </div>
                        </div>

                        <!-- Daftar Tim Supir Siaga -->
                        <div class="mb-8">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="bx bx-user-pin text-base text-red-500"></i>
                                    Tim Supir & Penanggung Jawab
                                </h3>
                                <span class="text-xs font-bold text-gray-500">
                                    {{ $ambulans->supirs->count() }} Supir
                                </span>
                            </div>

                            @if($ambulans->supirs && $ambulans->supirs->count() > 0)
                                <div class="space-y-2.5">
                                    @foreach($ambulans->supirs as $supir)
                                    @php
                                        $cleanWa = preg_replace('/[^0-9]/', '', $supir->kontak ?? '');
                                        $waUrl = $cleanWa ? ('https://wa.me/' . (str_starts_with($cleanWa, '0') ? '62' . substr($cleanWa, 1) : $cleanWa)) : null;
                                        $avatar = $supir->foto ? asset('storage/' . $supir->foto) : asset('Admin/img/avatars/pria.png');
                                        $isTersedia = ($supir->status == 'Tersedia');
                                    @endphp
                                    <div class="p-3 bg-white rounded-2xl border border-gray-200/90 shadow-xs flex items-center justify-between gap-3 hover:border-red-200 transition-colors">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="relative flex-shrink-0">
                                                <img src="{{ $avatar }}" alt="{{ $supir->nama }}" class="w-11 h-11 rounded-full object-cover border border-gray-200" onerror="this.src='{{ asset('Admin/img/avatars/pria.png') }}'">
                                                <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full {{ $isTersedia ? 'bg-green-500' : 'bg-yellow-500' }} border-2 border-white"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <h4 class="text-sm font-bold text-gray-900 truncate leading-snug">{{ $supir->nama }}</h4>
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold {{ $isTersedia ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                        {{ $supir->status }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                                    <i class="bx bx-phone text-gray-400"></i>
                                                    <span>{{ $supir->kontak ?? 'Tidak ada kontak' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                        @if($waUrl)
                                        <a href="{{ $waUrl }}" target="_blank" class="flex-shrink-0 px-3.5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                            <i class="bx bxl-whatsapp text-base"></i>
                                            <span class="hidden sm:inline">Hubungi WA</span>
                                            <span class="sm:hidden">WA</span>
                                        </a>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="bg-gray-50 border border-dashed border-gray-200 rounded-2xl p-4 text-center">
                                    <i class="bx bx-user-x text-gray-400 text-2xl mb-1"></i>
                                    <p class="text-xs sm:text-sm text-gray-700 font-medium mb-0.5">Belum ada supir siaga yang ditugaskan untuk armada ini oleh admin desa.</p>
                                    <p class="text-[11px] text-gray-500 mb-0">Untuk kebutuhan medis darurat, silakan hubungi nomor darurat ambulans desa.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="mt-auto space-y-3 pt-4 border-t border-gray-100">
                            @if(isset($regionSettings['kontak_ambulans']) && $regionSettings['kontak_ambulans'])
                            <a href="https://wa.me/{{ preg_replace('/^0/', '62', $regionSettings['kontak_ambulans']) }}" target="_blank"
                               class="w-full block text-center py-3.5 px-6 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-black rounded-2xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2 text-sm sm:text-base">
                                <i class="bx bxs-phone-call text-xl animate-pulse"></i>
                                <span>PANGGIL DARURAT (HOTLINE DESA)</span>
                            </a>
                            @endif

                            <a href="{{ route('mobil.rental.booking', $ambulans->id) }}"
                               class="w-full block text-center py-3 px-6 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-2xl transition-all text-xs sm:text-sm shadow-sm">
                                Jadwalkan Ambulans (Non-Darurat)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const carousel = document.getElementById('product-carousel');
        const prevBtn = document.getElementById('carousel-prev');
        const nextBtn = document.getElementById('carousel-next');
        const indicators = document.querySelectorAll('.carousel-indicator');
        
        if (!carousel || indicators.length <= 1) return;

        let currentSlide = 0;
        const totalSlides = indicators.length;

        function goToSlide(slideIndex) {
            currentSlide = (slideIndex + totalSlides) % totalSlides;
            carousel.style.transform = `translateX(-${currentSlide * 100}%)`;
            
            indicators.forEach((indicator, index) => {
                if (index === currentSlide) {
                    indicator.classList.remove('w-2', 'bg-white/50');
                    indicator.classList.add('w-6', 'bg-white');
                } else {
                    indicator.classList.remove('w-6', 'bg-white');
                    indicator.classList.add('w-2', 'bg-white/50');
                }
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                goToSlide(currentSlide - 1);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                goToSlide(currentSlide + 1);
            });
        }

        indicators.forEach((indicator) => {
            indicator.addEventListener('click', function(e) {
                e.preventDefault();
                const targetSlide = parseInt(this.getAttribute('data-slide'));
                goToSlide(targetSlide);
            });
        });

        // Touch swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        carousel.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        carousel.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });

        function handleSwipe() {
            const threshold = 40;
            if (touchEndX < touchStartX - threshold) {
                goToSlide(currentSlide + 1);
            }
            if (touchEndX > touchStartX + threshold) {
                goToSlide(currentSlide - 1);
            }
        }
    });
</script>
@endpush
