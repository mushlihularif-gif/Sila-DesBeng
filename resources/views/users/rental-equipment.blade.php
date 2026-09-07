@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16">
        <!-- Elemen Dekoratif Latar Belakang -->
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

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <!-- Header Section -->
            <div class="text-center mb-12 mt-12">
                <h1 class="text-3xl md:text-4xl font-bold mb-4">
                    <span class="text-gray-800">Unit </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Penyewaan Alat</span>
                </h1>
            </div>

            <!-- Category Filter -->
            @php
                $categories = $items->pluck('kategori')->filter()->unique()->values();
            @endphp
            
            @if($categories->count() > 0)
            <div class="flex flex-wrap justify-center gap-3 mb-10 max-w-4xl mx-auto px-4">
                <button class="filter-btn active px-6 py-2.5 rounded-full font-semibold text-sm transition-all duration-300 bg-blue-500 text-white shadow-md border border-transparent hover:bg-blue-600 hover:shadow-lg hover:scale-105" data-filter="all">
                    Semua
                </button>
                @foreach($categories as $category)
                <button class="filter-btn px-6 py-2.5 rounded-full font-semibold text-sm transition-all duration-300 bg-white text-gray-600 border border-gray-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 shadow-sm hover:shadow-md" data-filter="{{ Str::slug($category) }}">
                    {{ ucfirst(str_replace('-', ' ', $category)) }}
                </button>
                @endforeach
            </div>
            @endif

            <!-- Grid Kartu Produk -->
            @if($items->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16 max-w-6xl mx-auto">
                    @foreach($items as $item)
                    <a href="{{ route('rental.equipment.show', $item->id) }}" class="block group product-item transition-all duration-500" data-category="{{ $item->kategori ? Str::slug($item->kategori) : '' }}">
                    <div class="product-card bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 mx-auto w-full max-w-[350px] flex flex-col h-full">
                        
                        <!-- Gambar Produk -->
                        <div class="product-image-wrapper mb-6 relative aspect-square overflow-hidden rounded-2xl bg-gray-100 flex items-center justify-center group-hover:from-blue-50 group-hover:to-blue-50/30 transition-colors">
                            <img src="{{ asset('storage/' . $item->foto) }}" 
                                 alt="{{ $item->nama_barang }}"
                                 loading="lazy"
                                 class="product-image w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            
                            <!-- Status Badge -->
                            @if($item->stok > 0 && strtolower($item->status) != 'disewa')
                                <div class="absolute top-4 right-4 px-3 py-1.5 text-[10px] font-bold rounded-full bg-green-500 text-white shadow-md flex items-center gap-1 tracking-wider uppercase">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Tersedia
                                </div>
                            @else
                                <div class="absolute top-4 right-4 px-3 py-1.5 text-[10px] font-bold rounded-full bg-red-500 text-white shadow-md flex items-center gap-1 tracking-wider uppercase">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Habis / Disewa
                                </div>
                            @endif
                        </div>

                        <!-- Info Produk -->
                        <div class="product-info flex flex-col flex-1 px-2">
                            <!-- Kategori -->
                            @if($item->kategori)
                                <div class="mb-4">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-md text-[10px] font-bold text-white bg-blue-600 shadow-sm">
                                        {{ ucfirst(str_replace('-', ' ', $item->kategori)) }}
                                    </span>
                                </div>
                            @endif

                            <h3 class="product-name text-base font-bold text-gray-800 mb-2 line-clamp-2 group-hover:text-[#115789] transition-colors mt-0">
                                {{ $item->nama_barang }}
                            </h3>
                            
                            <div class="mt-auto pt-3 flex items-end justify-between">
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 mb-0.5 font-medium">Harga Sewa</span>
                                    <p class="text-gray-900 font-bold text-xl tracking-tight leading-none">
                                        Rp {{ number_format($item->harga_sewa, 0, ',', '.') }}<span class="text-xs text-gray-400 font-medium tracking-normal ml-0.5">/{{ $item->satuan ?? 'Unit' }}</span>
                                    </p>
                                </div>
                                <div class="text-right flex flex-col">
                                    <span class="text-xs text-gray-400 mb-0.5 font-medium">Sisa Stok</span>
                                    <p class="text-base font-bold {{ $item->stok > 0 ? 'text-gray-800' : 'text-red-500' }} leading-none">
                                        {{ $item->stok }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    </a>
                    @endforeach
                </div>
            @else
                <!-- Kondisi Kosong -->
                <div class="text-center py-20">
                    <svg class="w-24 h-24 mx-auto mb-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Produk Tersedia</h3>
                </div>
            @endif
        </div>
    </section>
    @include('users.partials.service_chat_widget', ['serviceType' => 'penyewaan', 'serviceTitle' => 'Sewa Alat & Mesin'])
</main>
@endsection

@push('styles')
<style>
    * {
        font-family: 'Inter', sans-serif;
    }

    /* Product Cards */
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

    .product-card {
        animation: fadeInUp 0.6s ease-out;
        animation-fill-mode: both;
    }

    .product-card:nth-child(1) { animation-delay: 0.1s; }
    .product-card:nth-child(2) { animation-delay: 0.2s; }
    .product-card:nth-child(3) { animation-delay: 0.3s; }
    .product-card:nth-child(4) { animation-delay: 0.4s; }
    .product-card:nth-child(5) { animation-delay: 0.5s; }
    .product-card:nth-child(6) { animation-delay: 0.6s; }

    /* Responsive */
    @media (max-width: 768px) {
        .product-name {
            font-size: 1.125rem;
        }

        .product-image {
            height: 200px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Gulir halus ke atas saat halaman dimuat
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // Tambahkan status loading untuk gambar
    document.addEventListener('DOMContentLoaded', () => {
        const images = document.querySelectorAll('.product-image');
        images.forEach(img => {
            if (img.complete) {
                img.style.opacity = '1';
            } else {
                img.style.opacity = '0';
                img.addEventListener('load', function() {
                    this.style.opacity = '1';
                });
            }
        });

        // Filter Logic with State Persistence
        const filterBtns = document.querySelectorAll('.filter-btn');
        const productItems = document.querySelectorAll('.product-item');
        
        // Gunakan path URL untuk membedakan state antar halaman
        const storageKey = 'filter_' + window.location.pathname;

        const activeClasses = ['bg-blue-500', 'text-white', 'shadow-md', 'border-transparent', 'hover:bg-blue-600', 'hover:shadow-lg', 'hover:scale-105', 'active'];
        const inactiveClasses = ['bg-white', 'text-gray-600', 'border-gray-200', 'hover:bg-blue-50', 'hover:text-blue-600', 'hover:border-blue-200', 'shadow-sm', 'hover:shadow-md'];

        function applyFilter(filterValue) {
            // Update button UI
            filterBtns.forEach(btn => {
                if (btn.getAttribute('data-filter') === filterValue) {
                    btn.classList.remove(...inactiveClasses);
                    btn.classList.add(...activeClasses);
                } else {
                    btn.classList.remove(...activeClasses);
                    btn.classList.add(...inactiveClasses);
                }
            });

            // Update items display with smooth opacity
            productItems.forEach(item => {
                // Disable transition temporarily to prevent weird jumping
                item.style.transition = 'none';
                
                if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                    item.style.display = 'block';
                    item.style.opacity = '0';
                    // Force reflow
                    void item.offsetWidth; 
                    // Re-enable transition and fade in
                    item.style.transition = 'opacity 0.4s ease-out, transform 0.3s ease';
                    item.style.opacity = '1';
                } else {
                    item.style.display = 'none';
                    item.style.opacity = '0';
                }
            });
            
            // Simpan state pilihan terakhir
            sessionStorage.setItem(storageKey, filterValue);
        }

        // Initialize state saat halaman dimuat (BfCache / Back button support)
        const savedFilter = sessionStorage.getItem(storageKey) || 'all';
        applyFilter(savedFilter);

        // Click handlers
        filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault(); // Mencegah default behavior
                const filterValue = btn.getAttribute('data-filter');
                applyFilter(filterValue);
            });
        });
    });
</script>
@endpush
