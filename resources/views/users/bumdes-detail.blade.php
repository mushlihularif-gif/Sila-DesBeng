@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-40 pb-16">
        <!-- Decorative Background Elements -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <img src="{{ asset('Admin/img/elements/background.png') }}" class="w-full h-full object-cover" alt="Background">
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <!-- Header Section -->
            <div class="text-center mb-16">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-8">
                    {{ $region ? $region->name : 'Profil Layanan Daerah' }}
                </h1>
            </div>

            <!-- Unit Pelayanan Section -->
            <div id="unit-carousel-container" class="max-w-7xl mx-auto px-6 py-16 overflow-hidden">
                <div class="max-w-7xl mx-auto">
                    <div class="text-center mb-16 relative">
                        <h2 class="text-3xl font-bold mb-2">
                            <span class="text-gray-800">Unit </span>
                            <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Pelayanan</span>
                        </h2>
                    </div>

                    <div class="relative h-[400px] w-full flex justify-center items-center">
                        <div class="relative w-full max-w-6xl mx-auto h-full">
                            @php
                                $isServiceActive = function($name) use ($activeServices, $region) {
                                    // Jika tidak ada region spesifik (misal diakses manual), tampilkan semua
                                    if (!$region) return true; 
                                    
                                    // Mapping nama tampilan ke nama layanan di database
                                    $map = [
                                        'Unit Penyewaan Alat' => 'Penyewaan Alat',
                                        'Unit Penjualan Gas' => 'Penjualan Gas',
                                        'Unit Penyewaan Mobil' => 'Penyewaan Mobil',
                                        'Unit Peminjaman Fasilitas Umum' => 'Peminjaman Fasilitas Umum',
                                        'Pelaporan Warga' => 'Pelaporan Warga',
                                        'Pengumuman dan Event' => 'Pengumuman dan Event'
                                    ];
                                    
                                    $dbName = $map[$name] ?? $name;
                                    
                                    // Pengecualian: Mungkin 'Pengumuman dan Event' selalu aktif untuk semua desa
                                    if ($dbName === 'Pengumuman dan Event') return true;
                                    
                                    return in_array($dbName, $activeServices);
                                };

                                $index = 0;
                            @endphp

                            @if($isServiceActive('Unit Penyewaan Alat'))
                            <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="{{ $index++ }}" data-name="Unit Penyewaan Alat" onclick="window.location.href='{{ route('rental.equipment') . '?region_id=' . $region->id }}'">
                                <img src="{{ asset('User/img/elemen/F1.png') }}" alt="Alat">
                            </div>
                            @endif

                            @if($isServiceActive('Unit Penjualan Gas'))
                            <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="{{ $index++ }}" data-name="Unit Penjualan Gas" onclick="window.location.href='{{ route('gas.sales') . '?region_id=' . $region->id }}'">
                                <img src="{{ asset('User/img/elemen/F2.png') }}" alt="Gas">
                            </div>
                            @endif

                            @if($isServiceActive('Unit Penyewaan Mobil'))
                            <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="{{ $index++ }}" data-name="Unit Penyewaan Mobil" onclick="window.location.href='{{ route('mobil.rental.equipment') . '?region_id=' . $region->id }}'">
                                <img src="{{ asset('User/img/elemen/mobil.png') }}" alt="Mobil">
                            </div>
                            @endif

                            @if($isServiceActive('Unit Peminjaman Fasilitas Umum'))
                            <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="{{ $index++ }}" data-name="Unit Peminjaman Fasilitas Umum" onclick="window.location.href='{{ route('user.fasilitas-umum.equipment') . '?region_id=' . $region->id }}'">
                                <img src="{{ asset('User/img/elemen/fasilitas.png') }}" alt="Fasilitas">
                            </div>
                            @endif
                            
                            @if($isServiceActive('Pelaporan Warga'))
                            <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="{{ $index++ }}" data-name="Pelaporan Warga" onclick="window.location.href='{{ route('pelaporan.landing') . '?region_id=' . $region->id }}'">
                                <img src="{{ asset('User/img/elemen/lapor.png') }}" alt="Lapor">
                            </div>
                            @endif

                            @if($isServiceActive('Pengumuman dan Event'))
                            <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="{{ $index++ }}" data-name="Pengumuman dan Event" onclick="window.location.href='{{ route('announcements.index') . '?region_id=' . $region->id }}'">
                                <img src="{{ asset('User/img/elemen/event.png') }}" alt="Event">
                            </div>
                            @endif
                        </div>

                        <div class="absolute -bottom-6 left-0 right-0 flex items-center justify-center gap-4 md:gap-12 z-[60]">
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

            <!-- Pemerintahan Section -->
            <div class="mb-16 mt-24">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold">
                        <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Pemerintah {{ $region ? $region->name : 'Daerah' }}</span>
                    </h2>
                </div>

                <!-- Members Grid -->
                <div class="flex flex-wrap justify-center gap-8 mb-16">
                    @foreach($members as $member)
                    <div class="member-card">
                        <div class="member-photo-wrapper">
                            <img src="{{ $member->photo_url }}" 
                                 alt="{{ $member->name }}"
                                 class="member-photo">
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mt-5 mb-1">{{ $member->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $member->position }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- WhatsApp Contact Button -->
            <div class="text-center mb-16">
                <a href="{{ $whatsappLink }}" 
                   target="_blank"
                   class="inline-flex items-center gap-3 px-8 py-4 bg-[#25D366] text-white font-semibold rounded-full hover:bg-[#20BA5A] hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    <span>Halo Layanan</span>
                </a>
            </div>
        </div>
    </section>
</main>
@endsection

@push('styles')
<style>
    * {
        font-family: 'Inter', sans-serif;
    }

    /* Unit Carousel Styles */
    .unit-card {
        width: 280px;
        height: 280px;
        position: absolute;
        top: 45%;
        transform-origin: center center;
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

    .state-0 {
        left: 15% !important;
        transform: translate(-50%, -50%) scale(0.65) !important;
        opacity: 0.8;
        z-index: 20;
        filter: grayscale(10%);
    }

    .state-1 {
        left: 50% !important;
        transform: translate(-50%, -50%) scale(1.5) !important;
        opacity: 1;
        z-index: 50;
        filter: grayscale(0%) drop-shadow(0 25px 35px rgba(0, 0, 0, 0.25));
    }

    .state-2 {
        left: 80% !important;
        transform: translate(-50%, -50%) scale(0.65) !important;
        opacity: 0.8;
        z-index: 20;
        filter: grayscale(10%);
    }

    .state-3 {
        left: 100% !important;
        transform: translate(-50%, -50%) scale(0.5) !important;
        opacity: 0.6;
        z-index: 10;
        filter: grayscale(30%);
    }

    .state-4 {
        left: 0% !important;
        transform: translate(-50%, -50%) scale(0.5) !important;
        opacity: 0.6;
        z-index: 10;
        filter: grayscale(30%);
    }

    .state-5 {
        left: -20% !important;
        transform: translate(-50%, -50%) scale(0.5) !important;
        opacity: 0;
        z-index: 5;
    }

    /* Member Card Styles */
    .member-card {
        background: white;
        border-radius: 1rem;
        padding: 2rem 1.5rem 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04);
        text-align: center;
        width: 240px;
        transition: all 0.3s ease;
    }

    .member-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        transform: translateY(-4px);
    }

    .member-photo-wrapper {
        width: 180px;
        height: 200px;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 0.75rem;
        background: #f8fafc;
    }

    .member-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* Responsive Mobile - 3 Column Layout (Center Focus) */
    @media (max-width: 768px) {
        .unit-card {
            width: 150px;
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

    /* Smooth animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    section > div {
        animation: fadeIn 0.6s ease-out;
    }
</style>
@endpush

@push('scripts')
<script>
    const BumdesDetailPage = {
        init() {
            this.initUnitCarousel();
        },

        initUnitCarousel() {
            const cards = Array.from(document.querySelectorAll('.unit-card'));
            if (cards.length === 0) return;

            const titleElement = document.getElementById('unit-title');
            const nextBtn = document.getElementById('unit-next');
            const prevBtn = document.getElementById('unit-prev');

            const stateClasses = ['state-0', 'state-1', 'state-2', 'state-3', 'state-4', 'state-5'];
            
            // Inisialisasi posisi secara dinamis sesuai jumlah cards
            let positions = [];
            const n = cards.length;
            
            if (n === 1) {
                positions = [1];
            } else if (n === 2) {
                positions = [1, 2];
            } else if (n === 3) {
                positions = [1, 2, 0];
            } else {
                // Untuk n >= 4
                positions = Array.from({length: n}, (_, i) => {
                    if (i === 0) return 1;
                    if (i === 1) return 2;
                    if (i === n - 1) return 0;
                    return 3; // sisanya hidden
                });
            }

            let autoSlideInterval;
            const autoSlideDelay = 3000;

            const updateCarousel = () => {
                cards.forEach((card, index) => {
                    card.classList.remove(...stateClasses);
                    const currentPos = positions[index];
                    card.classList.add(stateClasses[currentPos] || 'state-3');

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
                if (n === 1) return;
                
                // Shift positions array to the right cyclically
                // example for n=3: [1, 2, 0] -> [0, 1, 2] -> [2, 0, 1]
                positions.unshift(positions.pop());
                updateCarousel();
            };

            const handlePrev = () => {
                if (n === 1) return;
                
                // Shift positions array to the left cyclically
                positions.push(positions.shift());
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
            }
            if (prevBtn) {
                const newPrev = prevBtn.cloneNode(true);
                prevBtn.parentNode.replaceChild(newPrev, prevBtn);
                newPrev.addEventListener('click', () => {
                    handlePrev();
                    resetAutoSlide();
                });
            }

            const container = document.getElementById('unit-carousel-container');
            if (container) {
                container.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
                container.addEventListener('mouseleave', startAutoSlide);
            }

            updateCarousel();
            startAutoSlide();

        },
    };
    // Initialize
    BumdesDetailPage.init();
 </script>
@endpush
