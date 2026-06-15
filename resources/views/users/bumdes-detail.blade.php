@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-40 pb-16">
        <!-- Decorative Background Elements -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <!-- Top Left Blue Gradient Oval -->
            <svg class="absolute top-0 left-0 w-[600px] h-[600px] opacity-40" style="transform: translate(-30%, -20%);">
                <defs>
                    <linearGradient id="blueGradient1" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#7dd3fc;stop-opacity:0.6" />
                        <stop offset="100%" style="stop-color:#bae6fd;stop-opacity:0.3" />
                    </linearGradient>
                </defs>
                <ellipse cx="300" cy="300" rx="280" ry="320" fill="url(#blueGradient1)" />
            </svg>

            <!-- Top Right Geometric Shapes -->
            <div class="absolute top-20 right-0" style="transform: translateX(20%);">
                <svg width="400" height="400" viewBox="0 0 400 400" class="opacity-30">
                    <rect x="50" y="50" width="100" height="100" fill="#3b82f6" transform="rotate(45 100 100)" opacity="0.3"/>
                    <rect x="200" y="100" width="80" height="80" fill="#60a5fa" transform="rotate(30 240 140)" opacity="0.4"/>
                    <rect x="280" y="200" width="60" height="60" fill="#93c5fd" transform="rotate(60 310 230)" opacity="0.3"/>
                </svg>
            </div>

            <!-- Bottom Yellow Gradient Oval -->
            <svg class="absolute bottom-0 left-0 w-[500px] h-[500px] opacity-50" style="transform: translate(-20%, 30%);">
                <defs>
                    <linearGradient id="yellowGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#fbbf24;stop-opacity:0.5" />
                        <stop offset="100%" style="stop-color:#fde68a;stop-opacity:0.2" />
                    </linearGradient>
                </defs>
                <ellipse cx="250" cy="250" rx="240" ry="280" fill="url(#yellowGradient)" />
            </svg>

            <!-- Bottom Right Blue Wave -->
            <svg class="absolute bottom-0 right-0 w-[450px] h-[450px] opacity-35" style="transform: translate(25%, 25%) rotate(15deg);">
                <defs>
                    <linearGradient id="blueGradient2" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#60a5fa;stop-opacity:0.4" />
                        <stop offset="100%" style="stop-color:#93c5fd;stop-opacity:0.2" />
                    </linearGradient>
                </defs>
                <ellipse cx="225" cy="225" rx="200" ry="240" fill="url(#blueGradient2)" />
            </svg>
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <!-- Header Section -->
            <div class="text-center mb-16">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-8">
                    Desa Pematang Duku Timur
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
                            <div class="unit-card" data-index="0" data-name="Unit Penyewaan Alat">
                                <img src="{{ asset('User/img/elemen/F1.png') }}" alt="Alat">
                            </div>

                            <div class="unit-card" data-index="1" data-name="Unit Penjualan Gas">
                                <img src="{{ asset('User/img/elemen/F2.png') }}" alt="Gas">
                            </div>

                            <div class="unit-card" data-index="2" data-name="Unit Penyewaan Mobil">
                                <img src="{{ asset('User/img/elemen/mobil.png') }}" alt="Mobil">
                            </div>

                            <div class="unit-card" data-index="3" data-name="Unit Peminjaman Fasilitas Umum">
                                <img src="{{ asset('User/img/elemen/fasilitas.png') }}" alt="Fasilitas">
                            </div>
                            <div class="unit-card" data-index="4" data-name="Pengumuman dan Event">
                                <img src="{{ asset('User/img/elemen/event.png') }}" alt="Event">
                            </div>
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

            <!-- Struktur Pemerintahan dan BUMDes Section -->
            <div class="mb-16 mt-24">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold">
                        <span class="text-gray-800">Struktur </span>
                        <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Pemerintahan dan BUMDes</span>
                    </h2>
                </div>

                <!-- Members Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
                    @foreach($members as $member)
                    <div class="member-card bg-white/60 backdrop-blur-sm rounded-3xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300">
                        <div class="flex flex-col items-center text-center">
                            <div class="member-photo-wrapper mb-4">
                                <img src="{{ $member->photo_url }}" 
                                     alt="{{ $member->name }}"
                                     class="member-photo">
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1">{{ $member->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $member->position }}</p>
                        </div>
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
                    <span>Halo BUMDes</span>
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
        position: relative;
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(10px);
        border: none;
    }

    .member-photo-wrapper {
        position: relative;
        padding: 5px;
        background: linear-gradient(135deg, #fbbf24, #60a5fa, #3b82f6);
        border-radius: 50%;
        display: inline-block;
    }

    .member-photo {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid white;
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
            let positions = [1, 2, 3, 4, 5, 0];

            let autoSlideInterval;
            const autoSlideDelay = 3000;

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
                positions = positions.map(pos => (pos - 1 < 0 ? 5 : pos - 1));
                updateCarousel();
            };

            const handlePrev = () => {
                positions = positions.map(pos => (pos + 1 > 5 ? 0 : pos + 1));
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
    BumdesDetailPage.init();
 </script>
@endpush
