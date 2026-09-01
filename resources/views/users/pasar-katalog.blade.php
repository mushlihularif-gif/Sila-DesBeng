@extends('layouts.user')

@section('title', 'Katalog Pasar Daerah - SiladesBeng')

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
                    Temukan dan dukung produk asli dari instansi dan unit usaha daerah di seluruh Kabupaten Bengkalis.
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
                            <a href="javascript:void(0)" onclick="document.getElementById('btn-open-login').click();" class="ps-header-cart" title="Login / Keranjang">
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
            
            @if(request('region_id'))
                @php
                    $activeRegion = \App\Models\Region::find(request('region_id'));
                @endphp
                @if($activeRegion)
                <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 via-sky-50 to-white rounded-2xl border border-blue-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-section">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-gray-900 text-base">Toko {{ $activeRegion->name }}</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-700 uppercase">Toko Resmi</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">Menampilkan seluruh katalog produk dari unit usaha BUMDes {{ $activeRegion->name }}.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <a href="{{ route('pasar.toko', $activeRegion->id) }}" class="inline-flex items-center justify-center gap-1 px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-xs font-bold text-white transition shadow-sm">
                            <span>Profil Toko</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                        <a href="{{ route('pasar.index') }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            <span>Semua Toko</span>
                        </a>
                    </div>
                </div>
                @endif
            @endif

            <div class="mb-6 animate-section text-gray-600 font-medium text-sm flex items-center">
                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg> 
                Menampilkan {{ $produks->count() }} produk untuk Anda
            </div>

            <!-- Product Grid -->
            <div class="w-full animate-section">
                @if($produks->isEmpty())
                <div class="bg-white/80 backdrop-blur-md rounded-3xl py-20 px-8 text-center border border-gray-100 shadow-sm max-w-4xl mx-auto flex flex-col items-center justify-center min-h-[400px]">
                    <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 border-4 border-white shadow-inner">
                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Tidak Ada Produk</h3>
                    <p class="text-gray-500 max-w-md mx-auto">Belum ada instansi atau unit usaha daerah yang mendaftarkan produk di kategori/lokasi ini.<br>Coba ubah pencarian atau filter Anda.</p>
                </div>
                @else
                
                <!-- ULFA UI GRID -->
                <div class="products-grid" id="productsGrid">
                    @foreach($produks as $produk)
                    <div class="product-card" data-product-id="{{ $produk->id }}">
                        <div class="product-image-wrapper" style="cursor: pointer;" onclick="openOrderModal({{ $produk->id }}, '{{ addslashes($produk->nama_produk) }}', '{{ $produk->foto ? Storage::url($produk->foto) : '' }}', '{{ addslashes($produk->deskripsi ?? 'Tidak ada deskripsi.') }}', {{ $produk->harga }}, {{ $produk->stok ?? 10 }}, 'Toko BUMDes {{ addslashes($produk->region->name ?? 'Desa') }}', '{{ route('pasar.toko', $produk->region_id ?? 1) }}')">
                            @if($produk->foto)
                                <img src="{{ Storage::url($produk->foto) }}" alt="{{ $produk->nama_produk }}">
                            @else
                                <div style="width:100%; height:100%; background:#f3f4f6; display:flex; align-items:center; justify-content:center; color:#cbd5e1;">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                            
                            @if($produk->kategori)
                                <span class="product-badge" style="text-transform: capitalize;">{{ $produk->kategori }}</span>
                            @endif
                            
                            <div class="product-actions-overlay">
                                <button type="button" class="product-action-btn" title="Lihat Detail" onclick="event.stopPropagation(); openOrderModal({{ $produk->id }}, '{{ addslashes($produk->nama_produk) }}', '{{ $produk->foto ? Storage::url($produk->foto) : '' }}', '{{ addslashes($produk->deskripsi ?? 'Tidak ada deskripsi.') }}', {{ $produk->harga }}, {{ $produk->stok ?? 10 }}, 'Toko BUMDes {{ addslashes($produk->region->name ?? 'Desa') }}', '{{ route('pasar.toko', $produk->region_id ?? 1) }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                
                                @auth
                                    @if($produk->stok > 0)
                                        <button type="button" class="product-action-btn" title="Tambah ke Keranjang" onclick="event.stopPropagation(); addToCartUI({{ $produk->id }}, '{{ addslashes($produk->nama_produk) }}', '{{ $produk->foto ? Storage::url($produk->foto) : '' }}', {{ $produk->harga }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                                        </button>
                                    @endif
                                @endauth
                            </div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name" style="font-size: 1.1rem; line-height: 1.4; margin-bottom: 8px; cursor: pointer;" onclick="openOrderModal({{ $produk->id }}, '{{ addslashes($produk->nama_produk) }}', '{{ $produk->foto ? Storage::url($produk->foto) : '' }}', '{{ addslashes($produk->deskripsi ?? 'Tidak ada deskripsi.') }}', {{ $produk->harga }}, {{ $produk->stok ?? 10 }}, 'Toko BUMDes {{ addslashes($produk->region->name ?? 'Desa') }}', '{{ route('pasar.toko', $produk->region_id ?? 1) }}')">{{ $produk->nama_produk }}</h3>
                            <p class="product-desc" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $produk->deskripsi ?? 'Produk khas daerah Bengkalis.' }}</p>
                            <div class="product-price-row">
                                <span class="product-price">Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                                
                                @auth
                                    @if($produk->stok > 0)
                                        <button type="button" class="btn-add-cart" onclick="openOrderModal({{ $produk->id }}, '{{ addslashes($produk->nama_produk) }}', '{{ $produk->foto ? Storage::url($produk->foto) : '' }}', '{{ addslashes($produk->deskripsi ?? 'Tidak ada deskripsi.') }}', {{ $produk->harga }}, {{ $produk->stok ?? 10 }}, 'Toko BUMDes {{ addslashes($produk->region->name ?? 'Desa') }}', '{{ route('pasar.toko', $produk->region_id ?? 1) }}')">
                                            + Keranjang
                                        </button>
                                    @else
                                        <button type="button" class="btn-add-cart" style="background:#e5e7eb; color:#9ca3af; cursor:not-allowed;" disabled>
                                            Habis
                                        </button>
                                    @endif
                                @else
                                    <a href="javascript:void(0)" onclick="document.getElementById('btn-open-login').click();" class="btn-add-cart" style="text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                        + Keranjang
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
    <!-- ============================================
         CART SIDEBAR
         ============================================ -->
    <div class="cart-overlay" id="cartOverlay"></div>
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header" style="display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; border-bottom: 1px solid #e2e8f0; background: #ffffff;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; border: 1px solid #bae6fd;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.2;">Keranjang Belanja</h3>
                    <p style="font-size: 11px; color: #64748b; margin: 2px 0 0 0;" id="cartHeaderSubtitle">Produk Desa Pilihan</p>
                </div>
            </div>
            <button class="cart-close" id="closeCartBtn" title="Tutup Keranjang" style="width: 32px; height: 32px; border-radius: 50%; background: #f1f5f9; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b; transition: all 0.2s;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="cart-items" id="cartItems" style="padding: 16px; background: #f8fafc; overflow-y: auto; flex: 1;">
            <!-- Cart items will be rendered here by JS -->
        </div>
        <div class="cart-footer" id="cartFooter" style="display: none; background: #ffffff; border-top: 1px solid #e2e8f0; padding: 16px 20px;">
            <div class="cart-subtotal" style="display: flex; justify-content: space-between; font-size: 13px; color: #64748b; margin-bottom: 6px;">
                <span>Subtotal (<span id="cartTotalItems">0</span> item)</span>
                <span id="cartSubtotal" style="font-weight: 700; color: #1e293b;">Rp 0</span>
            </div>
            <div class="cart-total" style="display: flex; justify-content: space-between; font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 16px;">
                <span>Total Belanja</span>
                <span id="cartTotal" style="color: #115789;">Rp 0</span>
            </div>
            <a href="{{ route('pasar.checkout') }}" class="btn-checkout" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 13px 16px; background: linear-gradient(135deg, #115789, #0284c7); color: white; font-weight: 700; font-size: 14px; border-radius: 12px; text-decoration: none; box-shadow: 0 4px 14px rgba(17, 87, 137, 0.25); transition: all 0.2s;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                <span>Lanjut ke Checkout</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
    </div>

    <!-- ============================================
         ORDER MODAL
         ============================================ -->
    <!-- ============================================
         ORDER MODAL (MODERN MARKETPLACE REDESIGN)
         ============================================ -->
    <div class="modal-overlay" id="orderModalOverlay">
        <div class="ps-quick-modal" id="orderModal">
            <!-- Modal Header -->
            <div class="ps-modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#115789] flex items-center justify-center flex-shrink-0 shadow-sm border border-blue-100/80">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg leading-tight">Detail Produk</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Atur jumlah pesanan ke keranjang belanja</p>
                    </div>
                </div>
                <button type="button" class="ps-modal-close-btn" id="closeModalBtn" title="Tutup Modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="ps-modal-body">
                <!-- Product Card Preview -->
                <div class="ps-modal-product-card">
                    <div class="ps-modal-product-img-wrap">
                        <img id="modalProductImage" src="" alt="Produk">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 id="modalProductName" class="font-extrabold text-gray-900 text-base leading-snug line-clamp-1"></h4>
                        <p class="text-xs text-gray-500 line-clamp-2 mt-1 leading-relaxed" id="modalProductDesc"></p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-lg font-black text-[#115789]" id="modalProductPrice">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Clickable Store Bar -->
                <a id="modalStoreLink" href="#" class="ps-modal-store-bar" title="Kunjungi Toko">
                    <div class="ps-modal-store-left">
                        <div class="ps-modal-store-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="ps-modal-store-label">Toko Penjual</span>
                            <span id="modalStoreName" class="ps-modal-store-name">Toko BUMDes</span>
                        </div>
                    </div>
                    <div class="ps-modal-store-arrow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </a>

                <!-- Quantity Control -->
                <div class="ps-modal-qty-box">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Jumlah Pesanan</span>
                            <p class="text-[11px] text-gray-400">Atur kuantitas barang</p>
                        </div>
                        <div class="ps-modal-stepper">
                            <button type="button" onclick="changeModalQty(-1)" class="ps-modal-step-btn" title="Kurang">-</button>
                            <span id="modalQty" class="ps-modal-step-val">1</span>
                            <button type="button" onclick="changeModalQty(1)" class="ps-modal-step-btn" title="Tambah">+</button>
                        </div>
                    </div>
                </div>

                <!-- Calculation Card -->
                <div class="ps-modal-calc-card" id="modalCalcSection">
                    <div class="flex justify-between items-center text-xs text-gray-600">
                        <span>Harga Satuan</span>
                        <span id="modalUnitPrice" class="font-semibold text-gray-800">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center text-xs text-gray-600">
                        <span>Jumlah</span>
                        <span id="modalQtyDisplay" class="font-semibold text-gray-800">x 1</span>
                    </div>
                    <div class="pt-2.5 mt-1 border-t border-blue-100/80 flex justify-between items-center">
                        <span class="font-bold text-gray-800 text-sm">Total Harga</span>
                        <span id="modalTotalPrice" class="font-black text-lg text-[#115789]">Rp 0</span>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="ps-modal-actions">
                    <button type="button" class="ps-modal-btn-cancel" onclick="closeOrderModal()">
                        Batal
                    </button>
                    @auth
                    <button type="button" class="ps-modal-btn-cart" onclick="addToCartFromModal(false)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                        + Keranjang
                    </button>
                    <button type="button" class="ps-modal-btn-buy" onclick="addToCartFromModal(true)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        Beli Langsung
                    </button>
                    @else
                    <a href="javascript:void(0)" onclick="document.getElementById('btn-open-login').click(); closeOrderModal();" class="ps-modal-btn-buy text-center" style="text-decoration:none;">
                        Login untuk Beli
                    </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <span id="toastMessage">Produk ditambahkan ke keranjang!</span>
    </div>
</main>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pasar-daerah.css') }}">
<style>
    * { font-family: 'Inter', sans-serif; }
    
    /* Instant Rendering - Eliminasi Blank / Delay Putih */
    .animate-section {
        opacity: 1 !important;
        transform: none !important;
        animation: none !important;
    }
    .product-card {
        opacity: 1 !important;
        transform: none !important;
        visibility: visible !important;
    }
    
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
        margin-bottom: 0;
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
        border-radius: 9999px;
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
        gap: 12px;
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
        line-clamp: 2;
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

    /* ===== MODERN ORDER MODAL STYLING ===== */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        padding: 16px;
    }
    .modal-overlay.open {
        opacity: 1;
        visibility: visible;
    }
    .ps-quick-modal {
        background: #ffffff;
        border-radius: 1.5rem;
        width: 100%;
        max-width: 480px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(226, 232, 240, 0.8);
        transform: scale(0.92) translateY(20px);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .modal-overlay.open .ps-quick-modal {
        transform: scale(1) translateY(0);
    }
    .ps-modal-header {
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .ps-modal-close-btn {
        width: 36px;
        height: 36px;
        border-radius: 9999px;
        background: #f1f5f9;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .ps-modal-close-btn:hover {
        background: #fee2e2;
        color: #ef4444;
        transform: rotate(90deg);
    }
    .ps-modal-body {
        padding: 1.5rem;
    }
    .ps-modal-product-card {
        display: flex;
        gap: 1rem;
        align-items: center;
        background: #f8fafc;
        padding: 1rem;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        margin-bottom: 1.25rem;
    }
    .ps-modal-product-img-wrap {
        width: 76px;
        height: 76px;
        border-radius: 0.75rem;
        background: #ffffff;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ps-modal-product-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .ps-modal-store-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        margin-bottom: 1.25rem;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .ps-modal-store-bar:hover {
        background: #f0f9ff;
        border-color: #bae6fd;
        transform: translateY(-1px);
    }
    .ps-modal-store-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }
    .ps-modal-store-icon {
        width: 34px;
        height: 34px;
        border-radius: 0.65rem;
        background: #e0f2fe;
        color: #0369a1;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }
    .ps-modal-store-bar:hover .ps-modal-store-icon {
        background: #115789;
        color: #ffffff;
    }
    .ps-modal-store-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #94a3b8;
        display: block;
        line-height: 1.2;
    }
    .ps-modal-store-name {
        font-size: 13px;
        font-weight: 800;
        color: #1e293b;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: color 0.2s ease;
    }
    .ps-modal-store-bar:hover .ps-modal-store-name {
        color: #115789;
    }
    .ps-modal-store-arrow {
        color: #94a3b8;
        display: flex;
        align-items: center;
        flex-shrink: 0;
        transition: transform 0.2s ease, color 0.2s ease;
    }
    .ps-modal-store-bar:hover .ps-modal-store-arrow {
        color: #115789;
        transform: translateX(3px);
    }
    .ps-modal-qty-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 0.875rem 1rem;
        border-radius: 1rem;
        margin-bottom: 1.25rem;
    }
    .ps-modal-stepper {
        display: inline-flex;
        align-items: center;
        border: 1.5px solid #cbd5e1;
        border-radius: 0.75rem;
        overflow: hidden;
        background: #ffffff;
    }
    .ps-modal-step-btn {
        width: 36px;
        height: 36px;
        border: none;
        background: #f8fafc;
        color: #334155;
        font-weight: bold;
        font-size: 1.125rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s;
    }
    .ps-modal-step-btn:hover {
        background: #e2e8f0;
        color: #115789;
    }
    .ps-modal-step-val {
        width: 44px;
        text-align: center;
        font-weight: 800;
        font-size: 0.9375rem;
        color: #0f172a;
    }
    .ps-modal-calc-card {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border: 1px solid #bae6fd;
        border-radius: 1rem;
        padding: 1.125rem 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .ps-modal-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }
    .ps-modal-btn-cancel {
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        color: #64748b;
        font-weight: 700;
        font-size: 0.8125rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .ps-modal-btn-cancel:hover {
        background: #f8fafc;
        color: #1e293b;
        border-color: #cbd5e1;
    }
    .ps-modal-btn-cart {
        flex: 1;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        border: 1.5px solid #0284c7;
        background: #f0f9ff;
        color: #0284c7;
        font-weight: 700;
        font-size: 0.8125rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        transition: all 0.2s;
    }
    .ps-modal-btn-cart:hover {
        background: #e0f2fe;
        border-color: #0369a1;
        color: #0369a1;
        transform: translateY(-1px);
    }
    .ps-modal-btn-buy {
        flex: 1.15;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        border: none;
        background: linear-gradient(135deg, #115789 0%, #0284c7 100%);
        color: #ffffff;
        font-weight: 700;
        font-size: 0.8125rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        box-shadow: 0 4px 14px rgba(17, 87, 137, 0.3);
        transition: all 0.25s;
    }
    .ps-modal-btn-buy:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(17, 87, 137, 0.4);
    }
    .ps-modal-btn-buy:active {
        transform: translateY(0);
    }
</style>
@endpush

@push('scripts')
<script>
    // ============================================
    // CART STATE
    // ============================================
    let currentModalProduct = null;
    let modalQty = 1;

    // ============================================
    // FORMAT CURRENCY
    // ============================================
    function formatRupiah(num) {
        return 'Rp ' + parseInt(num).toLocaleString('id-ID');
    }

    // ============================================
    // TOAST NOTIFICATION
    // ============================================
    function showToast(message) {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toastMessage');
        toastMsg.textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // ============================================
    // CART FUNCTIONS (AJAX)
    // ============================================
    function addToCartUI(productId, productName, productImg, productPrice) {
        // Here we simulate adding to cart with UI toast, then send AJAX
        showToast(`${productName} ditambahkan ke keranjang!`);
        
        fetch("{{ route('pasar.cart.add') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                pasar_produk_id: productId,
                quantity: 1
            })
        }).then(res => {
            fetchCartItems(); 
        });
    }

    // ============================================
    // CART SIDEBAR OPEN/CLOSE
    // ============================================
    function openCart() {
        document.getElementById('cartOverlay').classList.add('open');
        document.getElementById('cartSidebar').classList.add('open');
        document.body.style.overflow = 'hidden';
        fetchCartItems();
    }

    function closeCart() {
        document.getElementById('cartOverlay').classList.remove('open');
        document.getElementById('cartSidebar').classList.remove('open');
        document.body.style.overflow = '';
    }
    
    function formatRupiah(amount) {
        return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function fetchCartItems() {
        const cartItemsContainer = document.getElementById('cartItems');
        const cartFooter = document.getElementById('cartFooter');
        const subtitle = document.getElementById('cartHeaderSubtitle');
        
        cartItemsContainer.innerHTML = `
            <div style="padding: 40px 20px; text-align: center; color: #64748b; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                <div class="ps-spinner" style="width: 32px; height: 32px; border: 3px solid #e2e8f0; border-top-color: #115789; border-radius: 50%; animation: spin 0.8s linear infinite; margin-bottom: 12px;"></div>
                <p style="font-size: 13px; font-weight: 500; color: #64748b; margin: 0;">Memuat keranjang...</p>
            </div>
        `;
        
        fetch("{{ route('pasar.cart.api') }}")
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    if(data.items.length === 0) {
                        if (subtitle) subtitle.textContent = '0 Produk';
                        cartItemsContainer.innerHTML = `
                            <div style="padding: 48px 20px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                                <div style="width: 68px; height: 68px; border-radius: 20px; background: #e0f2fe; border: 1px solid #bae6fd; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: #0284c7;">
                                    <svg style="width: 32px; height: 32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                </div>
                                <h4 style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Keranjang Masih Kosong</h4>
                                <p style="font-size: 12px; color: #64748b; margin-bottom: 20px; max-width: 220px; line-height: 1.5;">Pilih produk unggulan desa dan masukkan ke keranjang belanja.</p>
                                <button type="button" onclick="closeCart()" style="background: #115789; color: white; font-size: 12px; font-weight: 700; padding: 9px 20px; border-radius: 10px; border: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 8px rgba(17, 87, 137, 0.25);">
                                    Mulai Belanja
                                </button>
                            </div>
                        `;
                        cartFooter.style.display = 'none';
                    } else {
                        if (subtitle) subtitle.textContent = `${data.total_items} Produk Terpilih`;
                        let html = '';
                        data.items.forEach(item => {
                            let imageHtml = item.foto_url 
                                ? `<img src="${item.foto_url}" alt="${escapeHtml(item.nama_produk)}" style="width: 100%; height: 100%; object-fit: cover;">`
                                : `<div style="width: 100%; height: 100%; background: #f1f5f9; display: flex; align-items: center; justify-content: center;"><svg style="width: 24px; height: 24px; color: #cbd5e1;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>`;
                                
                            html += `
                                <div class="drawer-cart-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); display: flex; gap: 12px; align-items: flex-start; transition: all 0.2s;">
                                    <div style="width: 64px; height: 64px; border-radius: 10px; overflow: hidden; background: #f8fafc; border: 1px solid #e2e8f0; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                        ${imageHtml}
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 6px;">
                                            <h4 style="font-size: 13px; font-weight: 700; color: #1e293b; line-height: 1.35; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${escapeHtml(item.nama_produk)}</h4>
                                            <button type="button" onclick="removeFromDrawerCart(${item.id})" style="background: none; border: none; padding: 2px 4px; color: #94a3b8; cursor: pointer; border-radius: 6px; transition: color 0.2s;" title="Hapus item" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">
                                                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                        <div style="font-size: 13px; font-weight: 800; color: #115789; margin: 3px 0;">${formatRupiah(item.harga)}</div>
                                        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 6px;">
                                            <div style="display: inline-flex; align-items: center; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #f8fafc;">
                                                <button type="button" onclick="updateDrawerQty(${item.id}, ${item.quantity - 1})" style="width: 24px; height: 24px; border: none; background: #ffffff; color: #475569; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; transition: background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#ffffff'">-</button>
                                                <span style="min-width: 26px; text-align: center; font-size: 12px; font-weight: 700; color: #1e293b;">${item.quantity}</span>
                                                <button type="button" onclick="updateDrawerQty(${item.id}, ${item.quantity + 1})" style="width: 24px; height: 24px; border: none; background: #ffffff; color: #475569; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; transition: background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#ffffff'">+</button>
                                            </div>
                                            <div style="font-size: 11px; color: #64748b;">
                                                Subtotal: <strong style="color: #0f172a; font-weight: 700;">${formatRupiah(item.harga * item.quantity)}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        cartItemsContainer.innerHTML = html;
                        cartFooter.style.display = 'block';
                        
                        document.getElementById('cartTotalItems').textContent = data.total_items;
                        document.getElementById('cartSubtotal').textContent = formatRupiah(data.subtotal);
                        document.getElementById('cartTotal').textContent = formatRupiah(data.subtotal);
                    }
                    
                    // Update header badge
                    const badge = document.querySelector('.ps-header-badge');
                    if(badge) {
                        badge.textContent = data.total_items;
                        if(data.total_items == 0) badge.style.display = 'none';
                        else badge.style.display = 'block';
                    }
                }
            })
            .catch(err => {
                cartItemsContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #ef4444; font-size: 13px;">Gagal memuat keranjang.</div>';
            });
    }

    function updateDrawerQty(cartId, newQty) {
        if (newQty < 1) {
            removeFromDrawerCart(cartId);
            return;
        }

        fetch("{{ route('pasar.cart.update') }}", {
            method: "PATCH",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                cart_id: cartId,
                quantity: newQty
            })
        }).then(res => res.json())
          .then(data => {
            if(data.success) {
                fetchCartItems();
            } else {
                showToast(data.message || 'Gagal mengubah kuantitas.');
            }
        }).catch(err => {
            showToast('Terjadi kesalahan jaringan.');
        });
    }

    function removeFromDrawerCart(cartId) {
        fetch(`{{ url('/pasar-daerah/cart/remove') }}/${cartId}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        }).then(res => res.json())
          .then(data => {
            if(data.success) {
                showToast('Item berhasil dihapus dari keranjang.');
                fetchCartItems();
            }
        }).catch(err => {
            showToast('Gagal menghapus item.');
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Replace the header cart link behavior
    const openCartBtns = document.querySelectorAll('.ps-header-cart[href="{{ route('pasar.cart') }}"]');
    openCartBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            openCart();
        });
    });

    document.getElementById('closeCartBtn')?.addEventListener('click', closeCart);
    document.getElementById('cartOverlay')?.addEventListener('click', closeCart);

    // ============================================
    // ORDER MODAL
    // ============================================
    function openOrderModal(id, name, img, desc, price, stock = 10, storeName = '', storeUrl = '') {
        currentModalProduct = { id, name, img, desc, price, stock, storeName, storeUrl };
        modalQty = 1;

        const imgEl = document.getElementById('modalProductImage');
        if(img) {
            imgEl.src = img;
            imgEl.style.display = 'block';
        } else {
            imgEl.style.display = 'none';
        }
        
        imgEl.alt = name;
        document.getElementById('modalProductName').textContent = name;
        document.getElementById('modalProductDesc').textContent = desc;
        document.getElementById('modalProductPrice').textContent = formatRupiah(price);

        const storeLinkEl = document.getElementById('modalStoreLink');
        const storeNameEl = document.getElementById('modalStoreName');
        if (storeLinkEl && storeNameEl) {
            if (storeName && storeUrl) {
                storeNameEl.textContent = storeName;
                storeLinkEl.href = storeUrl;
                storeLinkEl.style.display = 'flex';
            } else {
                storeLinkEl.style.display = 'none';
            }
        }

        updateModalCalc();

        document.getElementById('orderModalOverlay').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeOrderModal() {
        document.getElementById('orderModalOverlay').classList.remove('open');
        document.body.style.overflow = '';
        currentModalProduct = null;
        modalQty = 1;
    }

    function changeModalQty(delta) {
        modalQty += delta;
        if (modalQty < 1) modalQty = 1;
        if (modalQty > 99) modalQty = 99;
        updateModalCalc();
    }

    function updateModalCalc() {
        if (!currentModalProduct) return;
        const total = currentModalProduct.price * modalQty;

        document.getElementById('modalQty').textContent = modalQty;
        document.getElementById('modalUnitPrice').textContent = formatRupiah(currentModalProduct.price);
        document.getElementById('modalQtyDisplay').textContent = 'x ' + modalQty;
        document.getElementById('modalTotalPrice').textContent = formatRupiah(total);
    }

    function addToCartFromModal(isDirectBuy = false) {
        if (!currentModalProduct) return;

        if (!isDirectBuy) {
            showToast(`${currentModalProduct.name} (${modalQty}x) ditambahkan ke keranjang!`);
        }
        
        fetch("{{ route('pasar.cart.add') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                pasar_produk_id: currentModalProduct.id,
                quantity: modalQty,
                is_direct_buy: isDirectBuy ? 1 : 0
            })
        }).then(res => res.json())
          .then(data => {
            closeOrderModal();
            if (isDirectBuy) {
                window.location.href = "{{ route('pasar.checkout') }}";
            } else {
                fetchCartItems(); 
            }
        }).catch(err => {
            if (isDirectBuy) {
                window.location.href = "{{ route('pasar.checkout') }}";
            }
        });
    }

    document.getElementById('closeModalBtn')?.addEventListener('click', closeOrderModal);
    document.getElementById('orderModalOverlay')?.addEventListener('click', function(e) {
        if (e.target === this) closeOrderModal();
    });

    // Auto-open modal if ?product=ID is present in URL (e.g. from Beranda popular section)
    (() => {
        const urlParams = new URLSearchParams(window.location.search);
        const targetProductId = urlParams.get('product');
        if (targetProductId) {
            const targetCard = document.querySelector(`.product-card[data-product-id="${targetProductId}"]`);
            if (targetCard) {
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const actionBtn = targetCard.querySelector('.product-action-btn[title="Lihat Detail"]') || targetCard;
                if (actionBtn) {
                    setTimeout(() => actionBtn.click(), 400);
                }
            }
        }
    })();

    // ============================================
    // SCROLL REVEAL ANIMATION (ULFA)
    // ============================================
    function revealOnScroll() {
        const reveals = document.querySelectorAll('.reveal');
        const windowHeight = window.innerHeight;

        reveals.forEach(el => {
            const revealTop = el.getBoundingClientRect().top;
            const revealPoint = 120;

            if (revealTop < windowHeight - revealPoint) {
                el.classList.add('active');
            }
        });
    }
    window.addEventListener('scroll', revealOnScroll);
    revealOnScroll(); // Trigger on load

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
                showSiladesBengToast('success', 'Berhasil', 'Produk ditambahkan ke keranjang!');
                setTimeout(() => { location.reload(); }, 1500);
            } else {
                showSiladesBengToast('error', 'Gagal', data.message || 'Gagal menambahkan produk');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showSiladesBengToast('error', 'Error', 'Terjadi kesalahan sistem');
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
