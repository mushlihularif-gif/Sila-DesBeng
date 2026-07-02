@extends('layouts.user')

@section('title', 'Unit Penjualan Gas - SilaDesBeng')

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
            <div class="text-center mb-20 mt-12">
                <h1 class="text-3xl md:text-4xl font-bold mb-4">
                    <span class="text-gray-800">Unit </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Penjualan Gas</span>
                </h1>
            </div>

            <!-- Grid Kartu Produk -->
            @if($items->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16 max-w-6xl mx-auto">
                    @foreach($items as $item)
                    <a href="{{ route('gas.sales.show', $item->id) }}" class="block">
                    <div class="product-card bg-white rounded-[2rem] p-6 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 mx-auto w-full max-w-[380px]">
                        <!-- Gambar Produk -->
                        <div class="product-image-wrapper mb-6 relative aspect-[4/3] overflow-hidden rounded-2xl bg-gray-50 flex items-center justify-center p-4">
                            <img src="{{ asset('storage/' . $item->foto) }}" 
                                 alt="{{ $item->jenis_gas }}"
                                 loading="lazy"
                                 class="product-image w-full h-full object-contain">
                            
                            <!-- Badges -->
                            @if($item->kategori)
                                <span class="absolute top-3 left-3 px-3 py-1 text-[10px] font-bold rounded-full {{ $item->kategori === 'bersubsidi' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ ucfirst(str_replace('-', ' ', $item->kategori)) }}
                                </span>
                            @endif
                            @if($item->stok > 0)
                                <span class="absolute top-3 right-3 px-3 py-1 text-[10px] font-bold rounded-full bg-emerald-500 text-white shadow-sm">Tersedia</span>
                            @else
                                <span class="absolute top-3 right-3 px-3 py-1 text-[10px] font-bold rounded-full bg-red-500 text-white shadow-sm">Habis</span>
                            @endif
                        </div>

                        <!-- Info Produk -->
                        <div class="product-info text-center">
                            <h3 class="product-name text-sm font-bold text-gray-800 mb-2">
                                {{ $item->jenis_gas }}
                            </h3>
                            <p class="text-[#115789] font-bold text-lg mb-1">
                                Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                                <span class="text-xs text-gray-500 font-normal">/ {{ $item->satuan ?? 'tabung' }}</span>
                            </p>
                            <p class="text-xs text-gray-500 flex items-center justify-center gap-1">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                Stok: {{ $item->stok }} {{ $item->satuan ?? 'tabung' }}
                            </p>
                        </div>
                    </div>
                    </a>
                    @endforeach
                </div>
            @else
                <!-- Kondisi Kosong (Format persis seperti Penyewaan Alat) -->
                <div class="text-center py-20">
                    <svg class="w-24 h-24 mx-auto mb-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Produk Gas Tersedia</h3>
                    <p class="text-gray-500">Produk penjualan gas akan segera ditambahkan oleh BUMDes.</p>
                </div>
            @endif
        </div>
    </section>
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
    });
</script>
@endpush
