@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16">
        <!-- Decorative Background Elements -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <!-- Top Left Blue Wave -->
            <svg class="absolute top-0 left-0 w-[500px] h-[400px] opacity-30" style="transform: translate(-20%, -10%);">
                <defs>
                    <linearGradient id="blueWave1" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#60a5fa;stop-opacity:0.6" />
                        <stop offset="100%" style="stop-color:#93c5fd;stop-opacity:0.3" />
                    </linearGradient>
                </defs>
                <path d="M0,100 Q150,50 300,100 T600,100 L600,0 L0,0 Z" fill="url(#blueWave1)" />
            </svg>

            <!-- Top Right Geometric Shape -->
            <div class="absolute top-20 right-0" style="transform: translateX(30%) rotate(15deg);">
                <svg width="300" height="300" viewBox="0 0 300 300" class="opacity-20">
                    <rect x="50" y="50" width="80" height="80" fill="#60a5fa" transform="rotate(45 90 90)" opacity="0.4"/>
                    <rect x="150" y="80" width="60" height="60" fill="#93c5fd" transform="rotate(30 180 110)" opacity="0.3"/>
                </svg>
            </div>

            <!-- Bottom Left Yellow Wave -->
            <svg class="absolute bottom-0 left-0 w-[600px] h-[400px] opacity-40" style="transform: translate(-15%, 20%);">
                <defs>
                    <linearGradient id="yellowWave" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#fbbf24;stop-opacity:0.5" />
                        <stop offset="100%" style="stop-color:#fde68a;stop-opacity:0.2" />
                    </linearGradient>
                </defs>
                <path d="M0,200 Q200,150 400,200 T800,200 L800,400 L0,400 Z" fill="url(#yellowWave)" />
            </svg>

            <!-- Bottom Right Blue Wave -->
            <svg class="absolute bottom-0 right-0 w-[500px] h-[350px] opacity-35" style="transform: translate(20%, 15%);">
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
            <!-- Header Section with Gradient Text - LEFT ALIGNED -->
            <div class="mb-12 mt-12">
                <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">
                    Detail
                </h1>
            </div>

            <!-- Detail Card - HORIZONTAL LAYOUT -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-10">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Left Side: Product Image + Location -->
                    <div class="lg:w-5/12 flex-shrink-0">
                        <!-- Product Image Carousel -->
                        <div class="relative aspect-square overflow-hidden rounded-2xl shadow-lg mb-6 group w-full">
                            @php
                                $images = collect([$item->foto, $item->foto_2, $item->foto_3])->filter()->values();
                                $hasMultipleImages = $images->count() > 1;
                            @endphp

                            <!-- Images Container -->
                            <div id="product-carousel" class="flex w-full h-full transition-transform duration-500 ease-out">
                                @foreach($images as $index => $image)
                                <div class="w-full h-full flex-shrink-0 flex-grow-0">
                                    <img src="{{ asset('storage/' . $image) }}" 
                                         alt="{{ $item->jenis_gas }} - Image {{ $index + 1 }}"
                                         class="w-full h-full object-cover product-image">
                                </div>
                                @endforeach
                            </div>

                            @if($hasMultipleImages)
                            <!-- Navigation Buttons -->
                            <button id="carousel-prev" type="button"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 rounded-full p-2 shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110 z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>

                            <button id="carousel-next" type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 rounded-full p-2 shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110 z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>

                            <!-- Indicators -->
                            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                                @foreach($images as $index => $image)
                                <button type="button"
                                        class="carousel-indicator {{ $index === 0 ? 'w-8 bg-white' : 'w-2.5 bg-white/50' }} h-2.5 rounded-full shadow-md transition-all duration-300 hover:bg-white/75"
                                        data-slide="{{ $index }}">
                                </button>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <!-- Location - Below Image -->
                        @if($setting && $setting->latitude && $setting->longitude)
                        <a href="https://www.google.com/maps?q={{ $setting->latitude }},{{ $setting->longitude }}" 
                           target="_blank"
                           class="flex items-center gap-2 text-gray-700 hover:text-blue-600 transition-colors">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium text-base">{{ $setting->location_name ?? 'Desa Pematang Duku Timur' }}</span>
                        </a>
                        @endif
                    </div>

                    <!-- Right Side: Product Information -->
                    <div class="lg:w-7/12 flex flex-col">
                        <!-- Product Name -->
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">{{ $item->jenis_gas }}</h2>

                        <!-- Description -->
                        <p class="text-gray-600 text-justify mb-6 leading-relaxed text-sm">
                            {{ $item->deskripsi }}
                        </p>

                        <!-- Product Details Grid -->
                        <div class="space-y-2 mb-6">
                            <!-- Stock -->
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium text-sm">Stok Tersedia</span>
                                <span class="text-gray-800 font-semibold text-sm">{{ $item->stok }} {{ $item->satuan }}</span>
                            </div>

                            <!-- Status -->
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium text-sm">Status</span>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                    {{ $item->status == 'tersedia' ? 'bg-green-100 text-green-700' : 
                                       ($item->status == 'dipesan' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </div>

                            <!-- Category -->
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium text-sm">Kategori</span>
                                <span class="text-gray-800 font-semibold text-sm">{{ $item->kategori }}</span>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="mb-6">
                            <p class="text-3xl font-bold text-red-600">Rp. {{ number_format($item->harga_satuan, 0, ',', '.') }}</p>
                        </div>

                        <!-- Quantity Selector + Order Button - SIDE BY SIDE -->
                        <div class="flex items-center gap-4 mt-auto">
                            <!-- Quantity Selector -->
                            <div class="flex items-center gap-3 border-2 border-gray-300 rounded-full px-4 py-2">
                                <button type="button" 
                                        id="decrease-qty"
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </button>
                                
                                <input type="number" 
                                       id="quantity" 
                                       value="1" 
                                       min="1" 
                                       max="{{ $item->stok }}"
                                       class="w-12 text-center text-lg font-semibold border-0 focus:outline-none focus:ring-0">
                                
                                <button type="button" 
                                        id="increase-qty"
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Order Button -->
                            <button type="button" 
                                    id="order-btn"
                                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-8 rounded-full transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                Pesan
                            </button>
                        </div>
                    </div>
                </div>
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

    /* Product Image Animation */
    .product-image {
        transition: transform 0.3s ease;
    }

    .product-image:hover {
        transform: scale(1.05);
    }

    /* Smooth animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Remove spinner from number input */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .lg\:w-5\/12 {
            width: 100%;
        }
        .lg\:w-7\/12 {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Quantity Selector
    const qtyInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decrease-qty');
    const increaseBtn = document.getElementById('increase-qty');
    const maxStock = {{ $item->stok }};

    if (qtyInput && decreaseBtn && increaseBtn) {
        decreaseBtn.addEventListener('click', () => {
            let currentValue = parseInt(qtyInput.value) || 1;
            if (currentValue > 1) {
                qtyInput.value = currentValue - 1;
            }
        });

        increaseBtn.addEventListener('click', () => {
            let currentValue = parseInt(qtyInput.value) || 1;
            if (currentValue < maxStock) {
                qtyInput.value = currentValue + 1;
            }
        });

        // Prevent manual input outside range
        qtyInput.addEventListener('change', () => {
            let value = parseInt(qtyInput.value) || 1;
            if (value < 1) qtyInput.value = 1;
            if (value > maxStock) qtyInput.value = maxStock;
        });
    }

    // Image Carousel
    const carousel = document.getElementById('product-carousel');
    const prevBtn = document.getElementById('carousel-prev');
    const nextBtn = document.getElementById('carousel-next');
    const indicators = document.querySelectorAll('.carousel-indicator');

    if (carousel && indicators.length > 1) {
        let currentSlide = 0;
        const totalSlides = indicators.length;
        let autoSlideInterval;
        const autoSlideDelay = 5000; // 5 seconds

        const goToSlide = (slideIndex) => {
            currentSlide = slideIndex;
            carousel.style.transform = `translateX(-${slideIndex * 100}%)`;

            // Update indicators
            indicators.forEach((indicator, index) => {
                if (index === slideIndex) {
                    indicator.classList.remove('w-2.5', 'bg-white/50');
                    indicator.classList.add('w-8', 'bg-white');
                } else {
                    indicator.classList.remove('w-8', 'bg-white');
                    indicator.classList.add('w-2.5', 'bg-white/50');
                }
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

        // Navigation buttons
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                prevSlide();
                resetAutoSlide();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                nextSlide();
                resetAutoSlide();
            });
        }

        // Indicator buttons
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                goToSlide(index);
                resetAutoSlide();
            });
        });

        // Start auto-slide
        startAutoSlide();

        // Pause on hover
        const carouselContainer = carousel.parentElement;
        if (carouselContainer) {
            carouselContainer.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
            carouselContainer.addEventListener('mouseleave', startAutoSlide);
        }
    }

    // Order Button - Redirect to Booking Page
    const orderBtn = document.getElementById('order-btn');
    if (orderBtn && qtyInput) {
        orderBtn.addEventListener('click', () => {
            const quantity = qtyInput.value || 1;
            window.location.href = '{{ route("gas.booking", ["id" => $item->id]) }}?quantity=' + quantity;
        });
    }

    // Smooth scroll to top on page load
    window.scrollTo({ top: 0, behavior: 'smooth' });
</script>
@endpush
```
