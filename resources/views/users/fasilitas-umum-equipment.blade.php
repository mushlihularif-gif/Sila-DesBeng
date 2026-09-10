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
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Peminjaman Fasilitas Umum</span>
                </h1>
            </div>

            <!-- Category Filter -->
            @php
                $itemCategories = $items->pluck('kategori')->filter()->unique()->values();
                $hasAmbulans = isset($kendaraans) && $kendaraans->where('kategori', 'ambulans')->isNotEmpty();
                $hasKendaraanOps = isset($kendaraans) && $kendaraans->where('kategori', 'kendaraan_operasional')->isNotEmpty();
                $totalCount = $items->count() + (isset($kendaraans) ? $kendaraans->count() : 0);
            @endphp
            
            @if($totalCount > 0)
            <div class="flex flex-wrap justify-center gap-3 mb-10 max-w-4xl mx-auto px-4">
                <button class="filter-btn active px-6 py-2.5 rounded-full font-semibold text-sm transition-all duration-300 bg-blue-500 text-white shadow-md border border-transparent hover:bg-blue-600 hover:shadow-lg hover:scale-105" data-filter="all">
                    Semua
                </button>
                @if($items->count() > 0)
                <button class="filter-btn px-6 py-2.5 rounded-full font-semibold text-sm transition-all duration-300 bg-white text-gray-600 border border-gray-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 shadow-sm hover:shadow-md" data-filter="gedung">
                    Gedung & Ruang Publik
                </button>
                @endif
                @if($hasAmbulans)
                <button class="filter-btn px-6 py-2.5 rounded-full font-semibold text-sm transition-all duration-300 bg-white text-gray-600 border border-gray-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200 shadow-sm hover:shadow-md" data-filter="ambulans">
                    Layanan Ambulans
                </button>
                @endif
                @if($hasKendaraanOps)
                <button class="filter-btn px-6 py-2.5 rounded-full font-semibold text-sm transition-all duration-300 bg-white text-gray-600 border border-gray-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 shadow-sm hover:shadow-md" data-filter="kendaraan-operasional">
                    Kendaraan Operasional
                </button>
                @endif
                @foreach($itemCategories as $cat)
                    @if(!in_array(Str::slug($cat), ['gedung', 'ambulans', 'kendaraan-operasional']))
                    <button class="filter-btn px-6 py-2.5 rounded-full font-semibold text-sm transition-all duration-300 bg-white text-gray-600 border border-gray-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 shadow-sm hover:shadow-md" data-filter="{{ Str::slug($cat) }}">
                        {{ ucfirst(str_replace('-', ' ', $cat)) }}
                    </button>
                    @endif
                @endforeach
            </div>
            @endif

            @if($hasAmbulans)
            <!-- Banner Siaga Ambulans Desa -->
            <div class="mb-10 max-w-6xl mx-auto">
                <div class="bg-gradient-to-r from-red-600 via-red-500 to-rose-600 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
                    <div class="flex items-center gap-4 sm:gap-5 z-10">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center flex-shrink-0 border border-white/30 shadow-inner">
                            <i class="bx bx-plus-medical text-2xl sm:text-3xl text-white"></i>
                        </div>
                        <div>
                            <span class="inline-block px-3 py-0.5 bg-white/20 backdrop-blur-sm rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider mb-1">Unit Siaga Medis Desa</span>
                            <h3 class="text-lg sm:text-2xl font-black leading-tight">Layanan Ambulans Desa Siaga</h3>
                            <p class="text-red-100 text-xs sm:text-sm mt-0.5 max-w-xl">Armada ambulans siap melayani kebutuhan medis dan rujukan darurat warga.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 z-10 flex-shrink-0 w-full md:w-auto">
                        @if(isset($regionSettings['kontak_ambulans']) && $regionSettings['kontak_ambulans'])
                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', $regionSettings['kontak_ambulans']) }}" target="_blank" class="flex-1 md:flex-initial text-center px-5 py-2.5 bg-white text-red-600 hover:bg-red-50 font-bold rounded-xl shadow-lg transition-all transform hover:scale-105 flex items-center justify-center gap-2 text-xs sm:text-sm">
                            <i class="bx bxs-phone-call text-base"></i> Panggil Darurat
                        </a>
                        @endif
                        <a href="{{ route('user.ambulans.index') }}" class="flex-1 md:flex-initial text-center px-4 py-2.5 bg-red-700/60 hover:bg-red-700 text-white font-semibold rounded-xl border border-white/30 transition-all text-xs sm:text-sm flex items-center justify-center gap-1.5">
                            <i class="bx bx-car text-base"></i> Info Armada & Supir
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Grid Kartu Produk -->
            <!-- Grid Kartu Produk (2 Kolom di Mobile, 2 di Tablet, 3 di Desktop) -->
            @if($totalCount > 0)
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-6 mb-12 sm:mb-16 max-w-6xl mx-auto">
                    <!-- Gedung & Ruang Publik -->
                    @foreach($items as $item)
                    @php
                        $catSlug = $item->kategori ? Str::slug($item->kategori) : '';
                    @endphp
                    <a href="{{ route('user.fasilitas-umum.show', $item->id) }}" class="block group product-item transition-all duration-500" data-category="{{ $catSlug }} gedung">
                    <div class="product-card bg-white rounded-2xl sm:rounded-3xl p-2.5 sm:p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 mx-auto w-full max-w-[350px] flex flex-col h-full">
                        
                        <!-- Gambar Produk -->
                        <div class="product-image-wrapper mb-2 sm:mb-6 relative aspect-square overflow-hidden rounded-xl sm:rounded-2xl bg-gray-100 flex items-center justify-center group-hover:from-blue-50 group-hover:to-blue-50/30 transition-colors">
                            <img src="{{ asset('storage/' . $item->foto) }}" 
                                 alt="{{ $item->nama_fasilitas }}"
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='{{ asset('User/img/elemen/fasilitas.png') }}';"
                                 class="product-image w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            
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
                        <div class="product-info flex flex-col flex-1 px-1 sm:px-2">
                            <!-- Kategori -->
                            @if($item->kategori)
                                <div class="mb-1.5 sm:mb-4">
                                    <span class="inline-flex items-center px-2 sm:px-3 py-0.5 sm:py-1.5 rounded-md text-[9px] sm:text-[10px] font-bold text-white bg-blue-600 shadow-sm">
                                        {{ ucfirst(str_replace('-', ' ', $item->kategori)) }}
                                    </span>
                                </div>
                            @endif

                            <h3 class="product-name text-xs sm:text-base font-bold text-gray-800 mb-1 sm:mb-2 line-clamp-2 group-hover:text-[#115789] transition-colors mt-0">
                                {{ $item->nama_fasilitas }}
                            </h3>
                            
                            <div class="mt-auto pt-2 sm:pt-3 flex items-end justify-between">
                                <div class="flex flex-col">
                                    <span class="text-[10px] sm:text-xs text-gray-500 mb-0.5 font-medium">Akses Layanan</span>
                                    <p class="text-gray-900 font-bold text-xs sm:text-base tracking-tight leading-none text-blue-600">
                                        Fasilitas Desa
                                    </p>
                                </div>
                                <div class="text-right flex flex-col">
                                    <span class="text-[10px] sm:text-xs text-gray-400 mb-0.5 font-medium">Tersedia</span>
                                    <p class="text-xs sm:text-base font-bold {{ $item->stok > 0 ? 'text-gray-800' : 'text-red-500' }} leading-none">
                                        {{ $item->stok }} Unit
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    </a>
                    @endforeach

                    <!-- Armada Kendaraan & Ambulans -->
                    @if(isset($kendaraans))
                    @foreach($kendaraans as $k)
                    @php
                        $isAmb = $k->kategori === 'ambulans';
                        $supir = $k->supirs->first();
                        $kCat = $isAmb ? 'ambulans' : 'kendaraan-operasional';
                    @endphp
                    <div class="block group product-item transition-all duration-500" data-category="{{ $kCat }} kendaraan">
                    <div class="product-card bg-white rounded-2xl sm:rounded-3xl p-2.5 sm:p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 mx-auto w-full max-w-[350px] flex flex-col h-full">
                        
                        <!-- Gambar Kendaraan -->
                        <div class="product-image-wrapper mb-2 sm:mb-6 relative aspect-square overflow-hidden rounded-xl sm:rounded-2xl bg-gray-100 flex items-center justify-center group-hover:from-blue-50 group-hover:to-blue-50/30 transition-colors">
                            <img src="{{ $k->foto ? asset('storage/' . $k->foto) : ($isAmb ? asset('Admin/img/elements/ambulance.png') : asset('User/img/elemen/mobil.png')) }}" 
                                 alt="{{ $k->nama_mobil }}"
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='{{ asset('User/img/elemen/mobil.png') }}';"
                                 class="product-image w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            
                            <!-- Status Badge -->
                            <div class="absolute top-2 right-2 sm:top-4 sm:right-4 px-1.5 sm:px-3 py-0.5 sm:py-1.5 text-[8px] sm:text-[10px] font-bold rounded-full {{ $isAmb ? 'bg-red-500' : 'bg-blue-600' }} text-white shadow-md flex items-center gap-0.5 sm:gap-1 tracking-wider uppercase">
                                <i class="bx {{ $isAmb ? 'bxs-ambulance' : 'bx-car' }}"></i>
                                <span>{{ $isAmb ? 'Ambulans Siaga' : 'Kendaraan Desa' }}</span>
                            </div>
                        </div>

                        <!-- Info Kendaraan -->
                        <div class="product-info flex flex-col flex-1 px-1 sm:px-2">
                            <!-- Kategori -->
                            <div class="mb-1.5 sm:mb-4">
                                <span class="inline-flex items-center px-2 sm:px-3 py-0.5 sm:py-1.5 rounded-md text-[9px] sm:text-[10px] font-bold text-white {{ $isAmb ? 'bg-red-600' : 'bg-indigo-600' }} shadow-sm">
                                    {{ $isAmb ? 'Ambulans Siaga Medis' : 'Kendaraan Operasional' }}
                                </span>
                            </div>

                            <h3 class="product-name text-xs sm:text-base font-bold text-gray-800 mb-1 sm:mb-2 line-clamp-2 mt-0">
                                {{ $k->nama_mobil }}
                            </h3>

                            <div class="text-[10px] sm:text-xs text-gray-500 mb-3 flex items-center gap-1 font-medium">
                                <i class="bx bx-id-card text-gray-400"></i>
                                <span>{{ str_replace('Plat: ', '', $k->deskripsi) }}</span>
                            </div>

                            @if($supir)
                            <div class="bg-gray-50 rounded-xl p-2 sm:p-2.5 mb-3 border border-gray-100 flex items-center justify-between gap-1.5">
                                <div class="flex items-center gap-2 overflow-hidden">
                                    <div class="w-6 h-6 rounded-full {{ $isAmb ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }} flex items-center justify-center font-bold text-[10px] flex-shrink-0">
                                        {{ substr($supir->nama, 0, 1) }}
                                    </div>
                                    <div class="truncate">
                                        <span class="text-[9px] text-gray-400 block leading-none">Penanggung Jawab</span>
                                        <span class="text-[11px] font-bold text-gray-800 truncate block leading-tight mt-0.5">{{ $supir->nama }}</span>
                                    </div>
                                </div>
                                @if($supir->kontak)
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $supir->kontak) }}" target="_blank" class="px-2 py-1 bg-green-500 hover:bg-green-600 text-white rounded-lg text-[10px] font-bold flex items-center gap-0.5 flex-shrink-0 transition-colors">
                                    <i class="bx bxl-whatsapp text-xs"></i> WA
                                </a>
                                @endif
                            </div>
                            @endif
                            
                            <div class="mt-auto pt-2 sm:pt-3">
                                @if($isAmb)
                                <a href="{{ route('user.ambulans.index') }}" class="w-full block text-center py-2 px-3 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white font-bold rounded-xl text-xs sm:text-sm shadow transition-all">
                                    Panggil / Detail Armada
                                </a>
                                @else
                                <span class="w-full block text-center py-2 px-3 bg-blue-50 text-blue-600 font-bold rounded-xl text-xs sm:text-sm border border-blue-200">
                                    Unit Layanan Desa
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            @else
                <div class="text-center py-20">
                    <svg class="w-24 h-24 mx-auto mb-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Fasilitas Tersedia</h3>
                    <p class="text-gray-500">Produk Peminjaman Fasilitas Umum akan segera ditambahkan.</p>
                </div>
            @endif
        </div>
    </section>
    @include('users.partials.service_chat_widget', ['serviceType' => 'fasilitas_umum', 'serviceTitle' => 'Fasilitas Umum'])
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
                
                const itemCategories = (item.getAttribute('data-category') || '').split(' ').filter(Boolean);
                if (filterValue === 'all' || itemCategories.includes(filterValue)) {
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
