@extends('layouts.user')

@section('page')
@include('partials.abstract-bg')
<main class="flex-grow relative w-full">
    <section class="catalog-detail-section relative z-10 min-h-screen pt-32 pb-16">

        <div class="max-w-6xl mx-auto px-6 relative z-10">
            <!-- Header Section with Gradient Text - LEFT ALIGNED -->
            <div class="catalog-detail-header mt-4">
                <h1 class="catalog-detail-heading">
                    Detail Produk
                </h1>
            </div>

            <!-- Detail Card - HORIZONTAL LAYOUT -->
            <div class="catalog-detail-card">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Left Side: Product Image -->
                    <div class="lg:w-5/12 flex-shrink-0">
                        <!-- Product Image Carousel -->
                        <div class="catalog-detail-gallery group">
                            @php
                                $images = collect([$item->foto, $item->foto_2, $item->foto_3])->filter()->values();
                            @endphp

                            <!-- Images Container -->
                            <div id="product-carousel" class="flex w-full h-full transition-transform duration-500 ease-out">
                                @foreach($images as $index => $image)
                                <div class="w-full h-full flex-shrink-0 flex-grow-0">
                                    <img src="{{ asset('storage/' . $image) }}" 
                                         alt="{{ $item->nama_mobil }} - Image {{ $index + 1 }}"
                                         {{ $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"' }}
                                          class="product-image">
                                </div>
                                @endforeach
                            </div>

                            @include('users.partials.product-gallery-controls')
                        </div>
                    </div>

                    <!-- Right Side: Product Information -->
                    <div class="lg:w-7/12 flex flex-col">
                        <!-- Product Name -->
                        <h2 class="catalog-detail-name mb-4">{{ $item->nama_mobil }}</h2>

                        <!-- Description -->
                        <p class="text-gray-600 text-justify mb-6 leading-relaxed text-sm">
                            {{ $item->deskripsi }}
                        </p>

                        <!-- Pricing Label -->
                        @php
                            $displayPrice = (float) ($item->harga_mulai ?: $item->harga_sewa);
                            if ($item->is_public_service) {
                                $priceLabel = 'Layanan Publik Daerah';
                            } elseif ($item->is_harian_active && $item->is_borongan_active) {
                                $priceLabel = 'Tarif Sewa Harian & Borongan (Mulai dari)';
                            } elseif ($item->is_borongan_active && !$item->is_harian_active) {
                                $priceLabel = 'Harga Sewa Borongan (Mulai dari)';
                            } else {
                                $priceLabel = 'Harga Sewa dihitung per hari';
                            }
                        @endphp
                        <div class="mb-4">
                            <p class="text-sm font-semibold text-gray-700">{{ $priceLabel }}</p>
                        </div>

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
                                       ($item->status == 'disewa' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
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
                            <p class="catalog-detail-price">
                                @if($item->is_public_service)
                                    Gratis
                                @else
                                    Rp {{ number_format($displayPrice, 0, ',', '.') }}
                                @endif
                            </p>
                        </div>

                        <!-- Quantity Selector + Rent Button - SIDE BY SIDE -->
                        <div class="segi-catalog-actions flex items-center gap-4 mt-auto">
                            <!-- Quantity Selector -->
                            <div class="flex items-center gap-3 border-2 border-gray-300 rounded-full px-4 py-2 {{ $item->stok <= 0 ? 'opacity-50 pointer-events-none' : '' }}">
                                <button type="button" 
                                        id="decrease-qty"
                                        @disabled($item->stok <= 0)
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </button>
                                
                                <input type="number" 
                                       id="quantity" 
                                       value="{{ $item->stok > 0 ? 1 : 0 }}" 
                                       min="{{ $item->stok > 0 ? 1 : 0 }}" 
                                       max="{{ $item->stok }}"
                                       @disabled($item->stok <= 0)
                                       class="w-12 text-center text-lg font-semibold border-0 focus:outline-none focus:ring-0">
                                
                                <button type="button" 
                                        id="increase-qty"
                                        @disabled($item->stok <= 0)
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

                            <!-- Rent Button -->
                            @if($item->stok > 0)
                            <a href="{{ route('mobil.rental.booking', $item->id) }}?quantity=1" 
                               id="rent-button"
                               data-turbo="false"
                               class="catalog-detail-primary flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-8 rounded-xl transition-all duration-300 shadow-sm text-center">
                                Sewa
                            </a>
                            @else
                            <button type="button"
                                    disabled
                                    class="catalog-detail-primary flex-1 bg-gray-300 text-gray-500 font-bold py-3 px-8 rounded-xl cursor-not-allowed text-center">
                                Stok Habis
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Location & Map: Left Info, Right Map -->
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <x-service-location :item="$item" layout="horizontal" />
                </div>
            </div>
        </div>
    </section>
</main>


    <x-unit-chat-widget 
        service="mobil" 
        title="Layanan Sewa Transportasi" 
        :regionId="$item->region_id" 
        :regionName="$item->region->name ?? ''"
        :itemName="$item->nama_mobil"
        :itemPrice="$item->harga_mulai > 0 ? 'Rp ' . number_format($item->harga_mulai, 0, ',', '.') . ' / ' . ($item->satuan ?: 'hari') : 'Gratis'"
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
    function initMobilDetail() {
        // Quantity Selector
        const qtyInput = document.getElementById('quantity');
        const decreaseBtn = document.getElementById('decrease-qty');
        const increaseBtn = document.getElementById('increase-qty');
        const rentButton = document.getElementById('rent-button');
        const maxStock = {{ $item->stok ?? 0 }};
        const itemId = {{ $item->id }};

        function updateRentButtonUrl() {
            if (!rentButton || !qtyInput) return;
            const qty = parseInt(qtyInput.value) || 1;
            rentButton.href = `/unit-penyewaan-mobil/${itemId}/booking?quantity=${qty}`;
        }

        if (qtyInput && decreaseBtn && increaseBtn) {
            decreaseBtn.onclick = () => {
                let currentValue = parseInt(qtyInput.value) || 1;
                if (currentValue > 1) {
                    qtyInput.value = currentValue - 1;
                    updateRentButtonUrl();
                }
            };

            increaseBtn.onclick = () => {
                let currentValue = parseInt(qtyInput.value) || 1;
                if (currentValue < maxStock) {
                    qtyInput.value = currentValue + 1;
                    updateRentButtonUrl();
                }
            };

            qtyInput.onchange = () => {
                let value = parseInt(qtyInput.value) || 1;
                if (value < 1) qtyInput.value = 1;
                if (value > maxStock) qtyInput.value = maxStock;
                updateRentButtonUrl();
            };
        }

    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobilDetail);
    } else {
        initMobilDetail();
    }
    document.addEventListener('turbo:load', initMobilDetail);
})();
</script>
@endpush
