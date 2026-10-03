@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16">
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <!-- Header Section -->
            <div class="text-center mb-12 mt-12">
                <h1 class="text-3xl md:text-4xl font-bold mb-4">
                    <span class="text-gray-800">Unit </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Penyewaan Alat</span>
                </h1>
                @if(isset($targetRegion) && $targetRegion)
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-blue-50 border border-blue-200 rounded-full text-blue-700 text-sm font-semibold shadow-sm">
                    <i class="bx bx-map-pin"></i> Wilayah: {{ $targetRegion->name }}
                </div>
                @endif
            </div>

            @if($filterRegions->isNotEmpty())
                @include('users.partials.catalog-region-filter', ['catalogRoute' => 'rental.equipment', 'filterLabel' => 'Filter wilayah alat'])
            @endif

            <!-- Category Filter -->
            @php
                $categories = $items->pluck('kategori')->filter()->unique()->values();
            @endphp
            
            @if($categories->count() > 0)
            <div class="flex flex-wrap justify-center gap-3 mb-10 max-w-4xl mx-auto px-4">
                <button class="catalog-filter-btn filter-btn active" data-filter="all">
                    Semua
                </button>
                @foreach($categories as $category)
                <button class="catalog-filter-btn filter-btn" data-filter="{{ Str::slug($category) }}">
                    {{ ucfirst(str_replace('-', ' ', $category)) }}
                </button>
                @endforeach
            </div>
            @endif

            <!-- Grid Kartu Produk (2 Kolom di Mobile, 2 di Tablet, 3 di Desktop) -->
            @if($items->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-5 lg:gap-6 mb-12 sm:mb-16 max-w-6xl mx-auto">
                    @foreach($items as $item)
                    <a href="{{ route('rental.equipment.show', $item->id) }}" data-turbo="false" class="block group product-item transition-all duration-500" data-category="{{ $item->kategori ? Str::slug($item->kategori) : '' }}">
                    <div class="product-card p-3 sm:p-5">
                        
                        <!-- Gambar Produk -->
                        <div class="product-image-wrapper mb-3 sm:mb-4">
                            <img src="{{ asset('storage/' . $item->foto) }}" 
                                 alt="{{ $item->nama_barang }}"
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='{{ asset('User/img/elemen/soundsystem.png') }}';"
                                 class="product-image">
                            
                            <!-- Status Badge -->
                            @if($item->stok > 0 && strtolower($item->status) != 'disewa')
                                <div class="absolute top-2 right-2 sm:top-4 sm:right-4 px-1.5 sm:px-3 py-0.5 sm:py-1.5 text-[8px] sm:text-[10px] font-bold rounded-full bg-green-500 text-white shadow-md flex items-center gap-0.5 sm:gap-1 tracking-wider uppercase">
                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="hidden xs:inline sm:inline">Tersedia</span>
                                </div>
                            @else
                                <div class="absolute top-2 right-2 sm:top-4 sm:right-4 px-1.5 sm:px-3 py-0.5 sm:py-1.5 text-[8px] sm:text-[10px] font-bold rounded-full bg-red-500 text-white shadow-md flex items-center gap-0.5 sm:gap-1 tracking-wider uppercase">
                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <span class="hidden xs:inline sm:inline">Habis</span>
                                </div>
                            @endif
                        </div>

                        <!-- Info Produk -->
                        <div class="product-info flex flex-col flex-1 px-0.5 sm:px-1">
                            <!-- Kategori -->
                            @if($item->kategori)
                                <div class="mb-1.5 sm:mb-4">
                                    <span class="inline-flex items-center px-2 sm:px-3 py-0.5 sm:py-1.5 rounded-md text-[9px] sm:text-[10px] font-bold text-white bg-blue-600 shadow-sm">
                                        {{ ucfirst(str_replace('-', ' ', $item->kategori)) }}
                                    </span>
                                </div>
                            @endif

                            <h3 class="product-name text-xs sm:text-base font-bold text-gray-800 mb-1 sm:mb-2 line-clamp-2 group-hover:text-[#115789] transition-colors mt-0">
                                {{ $item->nama_barang }}
                            </h3>
                            
                            <div class="mt-auto pt-3 flex items-end justify-between gap-2 border-t border-slate-100">
                                <div class="flex flex-col">
                                    <span class="text-[10px] sm:text-xs text-gray-500 mb-0.5 font-medium">Harga</span>
                                    <p class="text-gray-900 font-bold text-xs sm:text-xl tracking-tight leading-none">
                                        Rp {{ number_format($item->harga_sewa, 0, ',', '.') }}<span class="text-[9px] sm:text-xs text-gray-400 font-medium tracking-normal ml-0.5">/{{ $item->satuan ?? 'Unit' }}</span>
                                    </p>
                                </div>
                                <div class="text-right flex flex-col">
                                    <span class="text-[10px] sm:text-xs text-gray-400 mb-0.5 font-medium">Stok</span>
                                    <p class="text-xs sm:text-base font-bold {{ $item->stok > 0 ? 'text-gray-800' : 'text-red-500' }} leading-none">
                                        {{ $item->stok }}
                                    </p>
                                </div>
                            </div>
                            <span class="catalog-card-cta bg-emerald-600">Pilih Alat Sewa</span>
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
    @include('users.partials.service_chat_widget', ['serviceType' => 'penyewaan', 'serviceTitle' => 'Layanan Sewa Alat'])
</main>
@endsection

@push('styles')
@include('users.partials.catalog-ui-styles')
@endpush

@push('scripts')
<script>
    // Gulir halus ke atas saat halaman dimuat
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // Tambahkan status loading untuk gambar
    document.addEventListener('DOMContentLoaded', () => {
        const images = document.querySelectorAll('.product-image');
        images.forEach(img => img.addEventListener('error', function () {
            this.classList.add('image-fallback');
        }, { once: true }));

        // Filter Logic with State Persistence
        const filterBtns = document.querySelectorAll('.filter-btn');
        const productItems = document.querySelectorAll('.product-item');
        
        // Gunakan path URL untuk membedakan state antar halaman
        const storageKey = 'filter_' + window.location.pathname;

        function applyFilter(filterValue) {
            filterBtns.forEach(btn => {
                btn.classList.toggle('active', btn.getAttribute('data-filter') === filterValue);
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
