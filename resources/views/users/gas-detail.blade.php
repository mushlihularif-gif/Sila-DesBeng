@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="catalog-detail-section relative z-10 min-h-screen pt-32 pb-16">

        <div class="max-w-6xl mx-auto px-6 relative z-10">
            <!-- Header Section with Gradient Text - LEFT ALIGNED -->
            <div class="mb-12 mt-12">
                <h1 class="catalog-detail-heading">
                    Detail Produk
                </h1>
            </div>

            <!-- Detail Card - HORIZONTAL LAYOUT -->
            <div class="catalog-detail-card">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Left Side: Product Image + Location -->
                    <div class="lg:w-5/12 flex-shrink-0">
                        <!-- Product Image Carousel -->
                        <div class="catalog-detail-gallery mb-5 group">
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
                                          class="product-image gas-product-photo">
                                </div>
                                @endforeach
                            </div>

                            @if($hasMultipleImages)
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
                        @if($item->latitude && $item->longitude)
                        {{-- Produk punya koordinat sendiri: tampilkan sebagai link ke Google Maps --}}
                        <a href="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}" 
                           target="_blank"
                           class="flex items-center gap-2 text-gray-700 hover:text-blue-600 transition-colors">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium text-base">{{ $item->lokasi ?? 'Lihat di Peta' }}</span>
                        </a>
                        @elseif($item->lokasi)
                        {{-- Produk punya teks lokasi tapi tanpa koordinat: tampilkan teks biasa (tidak bisa diklik) --}}
                        <div class="flex items-center gap-2 text-gray-600">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium text-base">{{ $item->lokasi }}</span>
                        </div>
                        @elseif($setting && $setting->latitude && $setting->longitude)
                        {{-- Fallback: gunakan lokasi pusat BUMDes dari SystemSetting --}}
                        <a href="https://www.google.com/maps?q={{ $setting->latitude }},{{ $setting->longitude }}" 
                           target="_blank"
                           class="flex items-center gap-2 text-gray-700 hover:text-blue-600 transition-colors">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium text-base">{{ $setting->location_name ?? 'Lokasi BUMDes' }}</span>
                        </a>
                        @endif
                    </div>

                    <!-- Right Side: Product Information -->
                    <div class="lg:w-7/12 flex flex-col">
                        <!-- Product Name -->
                        <h2 class="catalog-detail-name mb-4">{{ $item->jenis_gas }}</h2>

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
                                <span class="text-gray-800 font-semibold text-sm">{{ str_replace('&', 'dan', $item->kategori) }}</span>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="mb-6">
                            <p class="catalog-detail-price">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</p>
                        </div>

                        <!-- Quantity Selector + Order Button -->
                        <div class="flex items-center gap-4 mt-auto">
                            <!-- Quantity Selector -->
                            <div class="flex items-center gap-3 border-2 border-gray-300 rounded-full px-4 py-2">
                                <button type="button" 
                                        onclick="let q = document.getElementById('quantity'); let v = parseInt(q.value) || 1; if(v > 1) { q.value = v - 1; document.getElementById('rent-button').href = '{{ route('gas.booking', ['id' => $item->id]) }}?quantity=' + q.value; }"
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
                                       onchange="let max = {{ $item->stok ?? 0 }}; if(this.value < 1) this.value = 1; if(this.value > max) this.value = max; document.getElementById('rent-button').href = '{{ route('gas.booking', ['id' => $item->id]) }}?quantity=' + this.value;"
                                       class="w-12 text-center text-lg font-semibold border-0 focus:outline-none focus:ring-0 p-0 bg-transparent text-gray-900">
                                
                                <button type="button" 
                                        onclick="let q = document.getElementById('quantity'); let v = parseInt(q.value) || 1; let max = {{ $item->stok ?? 0 }}; if(v < max) { q.value = v + 1; document.getElementById('rent-button').href = '{{ route('gas.booking', ['id' => $item->id]) }}?quantity=' + q.value; }"
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Chat Button -->
                            <button type="button" onclick="chatAboutCurrentItem()" class="flex-shrink-0 bg-white text-blue-500 border border-blue-500 hover:bg-blue-50 hover:text-blue-600 font-bold py-3 px-5 rounded-full transition-all duration-300 shadow-md flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                Chat
                            </button>
                            
                            <!-- Order Button -->
                            <a href="{{ route('gas.booking', ['id' => $item->id]) }}?quantity=1" 
                               id="rent-button"
                               data-turbo="false"
                               class="catalog-detail-primary flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-8 rounded-xl transition-all duration-300 shadow-sm text-center">
                                Pesan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>


    <x-unit-chat-widget 
        service="gas" 
        title="Layanan Pesan Gas" 
        :regionId="$item->region_id" 
        :regionName="$item->region->name ?? ''"
        :itemName="$item->jenis_gas"
        :itemPrice="'Rp ' . number_format($item->harga_satuan, 0, ',', '.')"
        :itemImage="$item->foto ? url('storage/' . $item->foto) : ''"
    />
@endsection

@push('styles')
@include('users.partials.catalog-detail-styles')
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
(() => {
    // Prevent manual input outside range
    const qtyInput = document.getElementById('quantity');
    const maxStock = {{ $item->stok ?? 0 }};
    if (qtyInput) {
        qtyInput.addEventListener('change', () => {
            let value = parseInt(qtyInput.value) || 1;
            if (value < 1) qtyInput.value = 1;
            if (value > maxStock) qtyInput.value = maxStock;
        });
    }

    // Image Carousel
    const carousel = document.getElementById('product-carousel');
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

        const startAutoSlide = () => {
            clearInterval(autoSlideInterval);
            autoSlideInterval = setInterval(nextSlide, autoSlideDelay);
        };

        const resetAutoSlide = () => {
            clearInterval(autoSlideInterval);
            startAutoSlide();
        };

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

    // Order logic is now handled via inline onclick in the button html

    // Smooth scroll to top on page load
    window.scrollTo({ top: 0, behavior: 'smooth' });

})();
</script>
@endpush
