@extends('layouts.user')

@section('title', 'Katalog Pasar Daerah - SilaDesBeng')

@section('page')
<main class="flex-grow relative w-full bg-gray-50/50">
    {{-- Custom Vector Abstract Background (Kabar Daerah Style) --}}
    <div class="fixed inset-0 overflow-hidden z-0" id="premium-bg">
        <canvas id="abstract-canvas" class="w-full h-full absolute inset-0"></canvas>
    </div>
    
    <section class="relative z-20 min-h-screen pt-32 pb-16">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10">
            {{-- Header Section --}}
            <div class="text-center mb-8 animate-section mt-4">
                <h1 class="text-3xl md:text-4xl font-bold mb-3">
                    <span class="text-gray-800">Katalog </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Pasar Daerah</span>
                </h1>
                <p class="text-gray-600 text-sm md:text-base max-w-2xl mx-auto">
                    Temukan dan dukung produk asli karya masyarakat Kabupaten Bengkalis.
                </p>
            </div>

            <!-- Marketplace Style Header -->
            <div class="ps-marketplace-header animate-section">
                <form action="{{ route('pasar.index') }}" method="GET" id="filterForm">
                    
                    <!-- Top Row: Logo, Search, Filter, Cart -->
                    <div class="ps-header-main relative">
                        <div class="ps-header-brand hidden sm:flex">
                            <svg class="w-7 h-7 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <span class="font-bold text-xl text-gray-800">Pasar<span class="text-blue-600">Daerah</span></span>
                        </div>

                        <div class="ps-header-search">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   class="ps-header-input" 
                                   placeholder="Cari di Pasar Daerah (Cth: Lempuk, Anyaman)...">
                            <button type="submit" class="ps-header-search-btn">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </button>
                        </div>

                        <div class="ps-header-actions">
                            <!-- Filter Toggle Button -->
                            <button type="button" onclick="toggleFilterMenu()" class="ps-header-cart" title="Filter Pencarian">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                @if(request('kategori') && request('kategori') != 'all' || request('kecamatan_id') && request('kecamatan_id') != 'all' || request('sort') && request('sort') != 'terbaru')
                                    <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                                @endif
                            </button>

                            @auth
                            <a href="{{ route('pasar.cart') }}" class="ps-header-cart" title="Keranjang Belanja">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                @php $cartCount = \App\Models\PasarCart::where('user_id', Auth::id())->sum('quantity'); @endphp
                                @if($cartCount > 0)
                                    <span class="ps-header-badge">{{ $cartCount }}</span>
                                @endif
                            </a>
                            @else
                            <a href="{{ route('auth.login') }}" class="ps-header-cart" title="Login / Keranjang">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </a>
                            @endauth
                        </div>

                        <!-- Dropdown Filter Menu -->
                        <div id="filterDropdownMenu" class="hidden absolute top-full right-0 mt-3 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-100 z-50 overflow-hidden" style="transform-origin: top right;">
                            <!-- Header -->
                            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/80">
                                <h3 class="font-bold text-gray-800 text-base">Filter Pencarian</h3>
                                <button type="button" onclick="toggleFilterMenu()" class="text-gray-400 hover:text-red-500 transition-colors rounded-full p-1 hover:bg-red-50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            <div class="p-5 max-h-[60vh] overflow-y-auto custom-scrollbar">
                                
                                <!-- Urutkan Harga -->
                                <div class="mb-6">
                                    <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                                        Urutkan
                                    </h4>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" onclick="selectSort(this, 'terbaru')" class="ps-filter-pill {{ (!request('sort') || request('sort') == 'terbaru') ? 'active' : '' }}">Terbaru</button>
                                        <button type="button" onclick="selectSort(this, 'termurah')" class="ps-filter-pill {{ request('sort') == 'termurah' ? 'active' : '' }}">Harga Terendah</button>
                                        <button type="button" onclick="selectSort(this, 'termahal')" class="ps-filter-pill {{ request('sort') == 'termahal' ? 'active' : '' }}">Harga Tertinggi</button>
                                    </div>
                                    <input type="hidden" name="sort" id="sortInput" value="{{ request('sort', 'terbaru') }}">
                                </div>

                                <!-- Kategori -->
                                <div class="mb-6">
                                    <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                        Kategori
                                    </h4>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" onclick="selectCategory(this, 'all')" class="ps-filter-pill {{ request('kategori', 'all') == 'all' ? 'active' : '' }}">Semua</button>
                                        @foreach(['Hasil Tani & Bumi', 'Pangan & Olahan', 'Material & Bangunan', 'Kerajinan & Kesenian', 'Lainnya'] as $cat)
                                            <button type="button" onclick="selectCategory(this, '{{ $cat }}')" class="ps-filter-pill {{ request('kategori') == $cat ? 'active' : '' }}">{{ $cat }}</button>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="kategori" id="kategoriInput" value="{{ request('kategori', 'all') }}">
                                </div>

                            </div>
                            
                            <!-- Footer Action -->
                            <div class="p-4 border-t border-gray-100 bg-gray-50 flex gap-3">
                                <button type="button" onclick="resetFilters()" class="flex-1 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl text-sm hover:bg-gray-100 transition-colors shadow-sm">Reset</button>
                                <button type="button" onclick="document.getElementById('filterForm').submit()" class="flex-1 py-2.5 bg-blue-600 text-white font-bold rounded-xl text-sm hover:bg-blue-700 transition-colors shadow-md shadow-blue-200">Terapkan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="mb-6 animate-section text-gray-600 font-medium text-sm flex items-center">
                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg> 
                Menampilkan {{ $produks->count() }} produk untuk Anda
            </div>

            <!-- Product Grid -->
            <div class="w-full animate-section">
                @if($produks->isEmpty())
                <div class="bg-white/80 backdrop-blur-md rounded-3xl p-16 text-center border border-gray-100 shadow-sm max-w-4xl mx-auto flex flex-col items-center">
                    @auth
                        <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 border-4 border-white shadow-inner">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-3">Tidak Ada Produk di Desa Anda</h3>
                        <p class="text-gray-500 max-w-md mx-auto">Belum ada penjual atau BUMDes di desa Anda yang mendaftarkan produk di kategori ini. Coba ubah pencarian atau kategori Anda.</p>
                    @else
                        <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mb-6 border-4 border-white shadow-inner">
                            <svg class="w-12 h-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-3">Silakan Login Terlebih Dahulu</h3>
                        <p class="text-gray-500 max-w-md mx-auto mb-6">Pasar Daerah bersifat hiper-lokal. Anda hanya dapat melihat dan berbelanja produk dari masyarakat dan BUMDes yang berada di desa Anda sendiri.</p>
                        <a href="{{ route('auth.login') }}" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-colors shadow-lg shadow-blue-200">Login Sekarang</a>
                    @endauth
                </div>
                @else
                
                <!-- TRUE E-COMMERCE GRID: Using Safe Tailwind Layout Classes + Custom CSS Components -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-6">
                    @foreach($produks as $produk)
                    <div class="ps-card">
                        
                        <!-- Image -->
                        <a href="{{ route('pasar.show', $produk->id) }}" class="ps-img-box">
                            @if($produk->foto)
                                <img src="{{ Storage::url($produk->foto) }}" alt="{{ $produk->nama_produk }}">
                            @else
                                <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #cbd5e1;">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                            <div class="ps-img-overlay"></div>
                            <div class="ps-badge">{{ $produk->kategori }}</div>
                        </a>

                        <!-- Content -->
                        <div class="ps-card-content">
                            <a href="{{ route('pasar.show', $produk->id) }}" class="ps-title">{{ $produk->nama_produk }}</a>
                            
                            <div class="ps-price">
                                Rp {{ number_format($produk->harga, 0, ',', '.') }}
                                <span class="ps-price-unit">/ {{ $produk->satuan ?? 'pcs' }}</span>
                            </div>
                            
                            <div class="ps-loc">
                                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> 
                                {{ $produk->region->name ?? 'Bengkalis' }}
                            </div>

                            <div class="ps-footer">
                                <div>
                                    <span class="ps-stock-lbl">Sisa Stok</span>
                                    <span class="ps-stock-val {{ $produk->stok == 0 ? 'habis' : '' }}">{{ $produk->stok > 0 ? $produk->stok : 'Habis' }}</span>
                                </div>
                                
                                @auth
                                    @if($produk->stok > 0)
                                        <button type="button" onclick="addToCart({{ $produk->id }})" class="ps-cart-btn" title="Tambah ke Keranjang">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m-3-3h6"></path></svg>
                                        </button>
                                    @else
                                        <button type="button" disabled class="ps-cart-btn" title="Stok Habis">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    @endif
                                @else
                                    <a href="{{ route('auth.login') }}" style="text-decoration: none;" class="ps-cart-btn" title="Login untuk Belanja">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m-3-3h6"></path></svg>
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="mt-10">
                    {{ $produks->links() }}
                </div>
                @endif
            </div>
        </div>
    </section>
</main>
@endsection

@push('styles')
<style>
    * { font-family: 'Inter', sans-serif; }
    
    /* Premium UI Styles for Pasar Daerah (Marketplace Style) */
    .ps-marketplace-header {
        position: relative;
        z-index: 50;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        padding: 16px 24px;
        margin-bottom: 24px;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
        border: 1px solid rgba(255, 255, 255, 0.8);
    }
    
    /* Top Row: Brand, Search, Cart */
    .ps-header-main {
        display: flex;
        align-items: center;
        gap: 24px;
        margin-bottom: 16px;
    }
    .ps-header-brand {
        display: flex;
        align-items: center;
        min-width: max-content;
    }
    .ps-header-search {
        flex: 1;
        display: flex;
        border: 2px solid #3b82f6;
        border-radius: 10px;
        overflow: hidden;
        height: 46px;
        background: #fff;
    }
    .ps-header-input {
        flex: 1;
        border: none;
        padding: 0 16px;
        outline: none;
        font-size: 14px;
        color: #1e293b;
    }
    .ps-header-search-btn {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 0 24px;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ps-header-search-btn:hover {
        background: #2563eb;
    }
    .ps-header-actions {
        display: flex;
        align-items: center;
    }
    .ps-header-cart {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #64748b;
        border-radius: 12px;
        transition: all 0.2s ease;
        position: relative;
    }
    .ps-header-cart:hover {
        background: #e2e8f0;
        color: #3b82f6;
    }
    .ps-header-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        background: #ef4444;
        color: white;
        font-size: 11px;
        font-weight: 700;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 2px solid white;
    }
    
    /* Custom Filter Modal Styles */
    .ps-filter-pill {
        padding: 8px 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }
    .ps-filter-pill:hover {
        background: #f1f5f9;
        color: #334155;
    }
    .ps-filter-pill.active {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #2563eb;
    }
    
    .custom-opt {
        padding: 10px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        color: #475569;
        transition: all 0.15s;
        margin-bottom: 2px;
        display: flex;
        align-items: center;
    }
    .custom-opt:hover {
        background: #f8fafc;
        color: #2563eb;
    }
    .custom-opt.active {
        background: #eff6ff;
        color: #2563eb;
        font-weight: 600;
    }
    
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 10px;
    }
    
    @media (max-width: 768px) {
        .ps-header-main {
            flex-wrap: wrap;
        }
        .ps-header-search {
            order: 3;
            min-width: 100%;
            margin-top: 8px;
        }
        .ps-header-actions {
            margin-left: auto;
        }
        .ps-marketplace-header {
            padding: 16px;
        }
    }

    /* Product Card */
    .ps-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        border: 1px solid #f1f5f9;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
        position: relative;
        height: 100%;
    }
    .ps-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        border-color: #bfdbfe;
    }
    .ps-img-box {
        position: relative;
        width: 100%;
        padding-top: 75%; /* 4:3 Aspect Ratio */
        background: #f8fafc;
        overflow: hidden;
        display: block;
    }
    .ps-img-box img {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.7s ease;
    }
    .ps-card:hover .ps-img-box img {
        transform: scale(1.1);
    }
    .ps-img-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 60%);
        opacity: 0.8;
        pointer-events: none;
    }
    .ps-badge {
        position: absolute;
        bottom: 12px;
        left: 12px;
        background: rgba(255,255,255,0.25);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,0.4);
        color: white;
        font-size: 10px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .ps-card-content {
        padding: 16px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .ps-title {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-decoration: none;
    }
    .ps-title:hover {
        color: #2563eb;
    }
    .ps-price {
        font-size: 1.15rem;
        font-weight: 900;
        background: linear-gradient(to right, #1d4ed8, #3b82f6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 12px;
    }
    .ps-price-unit {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 500;
        -webkit-text-fill-color: #94a3b8;
    }
    .ps-loc {
        font-size: 11px;
        color: #64748b;
        background: #f8fafc;
        padding: 6px 10px;
        border-radius: 8px;
        border: 1px solid #f1f5f9;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 16px;
    }
    .ps-footer {
        margin-top: auto;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .ps-stock-lbl {
        font-size: 10px;
        color: #94a3b8;
        text-transform: uppercase;
        font-weight: 800;
        display: block;
    }
    .ps-stock-val {
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
    }
    .ps-stock-val.habis {
        color: #ef4444;
    }
    .ps-cart-btn {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #eff6ff;
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
    }
    .ps-cart-btn:hover {
        background: #2563eb;
        color: white;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        transform: scale(1.05);
    }
    .ps-cart-btn:disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleFilterMenu() {
        const menu = document.getElementById('filterDropdownMenu');
        menu.classList.toggle('hidden');
        if(!menu.classList.contains('hidden')) {
            // Close location menu if open
            document.getElementById('customLocationMenu').classList.add('hidden');
        }
    }

    function toggleCustomLocation() {
        document.getElementById('customLocationMenu').classList.toggle('hidden');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('filterDropdownMenu');
        const filterBtn = document.querySelector('button[onclick="toggleFilterMenu()"]');
        const locMenu = document.getElementById('customLocationMenu');
        const locBtn = document.getElementById('customLocationBtn');
        
        if (menu && !menu.contains(event.target) && filterBtn && !filterBtn.contains(event.target)) {
            menu.classList.add('hidden');
            if(locMenu) locMenu.classList.add('hidden');
        }
        
        if (locMenu && !locMenu.contains(event.target) && locBtn && !locBtn.contains(event.target)) {
            locMenu.classList.add('hidden');
        }
    });

    function selectSort(btn, sortType) {
        // Update styling
        const siblings = btn.parentElement.children;
        for(let el of siblings) el.classList.remove('active');
        btn.classList.add('active');
        
        // Update input
        document.getElementById('sortInput').value = sortType;
    }
    
    function selectCategory(btn, category) {
        // Update styling
        const siblings = btn.parentElement.children;
        for(let el of siblings) el.classList.remove('active');
        btn.classList.add('active');
        
        // Update input
        document.getElementById('kategoriInput').value = category;
    }

    function selectLocation(kecId, desaId, text) {
        // Update inputs
        document.getElementById('hiddenKecId').value = kecId;
        document.getElementById('hiddenDesaId').value = desaId;
        
        // Update text
        document.getElementById('customLocationText').innerText = text;
        
        // Hide menu
        document.getElementById('customLocationMenu').classList.add('hidden');
        
        // Update active class
        const opts = document.querySelectorAll('.custom-opt');
        opts.forEach(opt => opt.classList.remove('active'));
        event.currentTarget.classList.add('active');
    }
    
    function resetFilters() {
        document.getElementById('sortInput').value = 'terbaru';
        document.getElementById('kategoriInput').value = 'all';
        document.getElementById('filterForm').submit();
    }

    @auth
    function addToCart(produkId) {
        fetch('{{ route('pasar.cart.add') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                pasar_produk_id: produkId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    toast: true, 
                    position: 'top-end', 
                    icon: 'success', 
                    title: 'Berhasil', 
                    text: 'Produk ditambahkan ke keranjang!', 
                    showConfirmButton: false, 
                    timer: 1500
                }).then(() => {
                    location.reload(); 
                });
            } else {
                Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Gagal', text: data.message || 'Gagal menambahkan produk', showConfirmButton: false, timer: 3000});
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem', showConfirmButton: false, timer: 3000});
        });
    }
    @endauth

    // Canvas Background Animation Script (Identical to Kabar Daerah)
    (function() {
        const initCanvas = function() {
            const canvas = document.getElementById('abstract-canvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            let width, height;
            let waves = [];
            let scrollY = window.scrollY;
            let mouse = { x: 0, y: 0 };
            let targetMouse = { x: 0, y: 0 };

            function resize() {
                width = window.innerWidth;
                height = window.innerHeight;
                canvas.width = width;
                canvas.height = height;
                initWaves();
            }

            window.addEventListener('resize', resize);
            
            document.addEventListener('mousemove', (e) => {
                targetMouse.x = e.clientX;
                targetMouse.y = e.clientY;
            });

            window.addEventListener('scroll', () => {
                scrollY = window.scrollY;
            });

            class Wave {
                constructor(colorFunc, heightPercent, amplitude, speed, offset) {
                    this.colorFunc = colorFunc;
                    this.heightPercent = heightPercent;
                    this.amplitude = amplitude;
                    this.speed = speed;
                    this.offset = offset;
                    this.points = [];
                    this.time = 0;
                }
                init() {
                    this.points = [];
                    for(let i = 0; i <= width + 50; i += 50) {
                        this.points.push(i);
                    }
                }
                update() {
                    this.time += this.speed;
                }
                draw() {
                    ctx.beginPath();
                    ctx.moveTo(0, height);
                    for(let i = 0; i < this.points.length; i++) {
                        let x = this.points[i];
                        let dx = x - mouse.x;
                        let dy = (height * this.heightPercent) - mouse.y;
                        let dist = Math.sqrt(dx*dx + dy*dy);
                        let repel = 0;
                        
                        if(dist < 400) {
                            repel = (400 - dist) * 0.15;
                        }

                        let y = height * this.heightPercent 
                              + Math.sin(this.time + (x * 0.005) + this.offset) * this.amplitude
                              + repel;
                        ctx.lineTo(x, y);
                    }
                    ctx.lineTo(width, height);
                    ctx.fillStyle = this.colorFunc(ctx, width, height);
                    ctx.fill();
                }
            }

            function initWaves() {
                waves = [
                    new Wave((ctx, w, h) => {
                        let grad = ctx.createLinearGradient(0, h*0.5, 0, h);
                        grad.addColorStop(0, 'rgba(230, 240, 255, 1)');
                        grad.addColorStop(1, 'rgba(255, 255, 255, 1)');
                        return grad;
                    }, 0.65, 20, 0.005, 0),

                    new Wave((ctx, w, h) => {
                        let grad = ctx.createLinearGradient(0, h*0.6, 0, h*1.2);
                        grad.addColorStop(0, 'rgba(255, 255, 255, 1)');
                        grad.addColorStop(1, 'rgba(245, 250, 255, 0.5)');
                        return grad;
                    }, 0.75, 30, 0.003, 500),

                    new Wave((ctx, w, h) => {
                        let grad = ctx.createLinearGradient(0, h*0.7, 0, h*1.1);
                        grad.addColorStop(0, 'rgba(245, 225, 130, 0.5)');
                        grad.addColorStop(1, 'rgba(255, 255, 255, 0)');
                        return grad;
                    }, 0.85, 45, 0.007, 700)
                ];
                waves.forEach(w => w.init());
            }

            function animate() {
                mouse.x += (targetMouse.x - mouse.x) * 0.1;
                mouse.y += (targetMouse.y - mouse.y) * 0.1;
                ctx.fillStyle = '#e8eff5'; 
                ctx.fillRect(0, 0, width, height);
                ctx.save();
                ctx.translate(0, -scrollY * 0.4); 
                let glowX = width * 0.15;
                let glowY = height * 0.4;
                let gradGlow = ctx.createRadialGradient(glowX, glowY, 0, glowX, glowY, width * 0.3);
                gradGlow.addColorStop(0, 'rgba(245, 235, 150, 0.15)');
                gradGlow.addColorStop(1, 'rgba(245, 235, 150, 0)');
                ctx.fillStyle = gradGlow;
                ctx.beginPath();
                ctx.arc(glowX, glowY, width * 0.3, 0, Math.PI*2);
                ctx.fill();
                waves.forEach(w => { w.update(); w.draw(); });
                ctx.restore();
                requestAnimationFrame(animate);
            }
            resize();
            animate();
        };
        if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initCanvas); } else { initCanvas(); }
    })();
</script>
@endpush
