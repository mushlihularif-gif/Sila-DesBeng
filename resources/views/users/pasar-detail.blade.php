@extends('layouts.user')

@section('title', $produk->nama_produk . ' - Pasar Daerah')

@push('styles')
<style>
    /* ===== DETAIL PAGE DESIGN SYSTEM ===== */
    .detail-container { font-family: 'Inter', sans-serif; }

    /* Grid Layout */
    .ps-detail-grid {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }
    @media (min-width: 1024px) {
        .ps-detail-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 2.5rem;
            align-items: start;
        }
        .ps-col-image { grid-column: span 5 / span 5; }
        .ps-col-info { grid-column: span 4 / span 4; min-width: 0; }
        .ps-col-action { grid-column: span 3 / span 3; }
    }

    /* ===== IMAGE GALLERY ===== */
    .ps-gallery-main {
        position: relative;
        width: 100%;
        padding-top: 100%;
        background: #f8fafc;
        border-radius: 1.25rem;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        cursor: zoom-in;
    }
    .ps-gallery-main img {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .ps-gallery-main:hover img {
        transform: scale(1.15);
    }
    .ps-gallery-thumb {
        position: relative;
        width: 100%;
        padding-top: 100%;
        border-radius: 0.75rem;
        overflow: hidden;
        cursor: pointer;
        border: 2.5px solid transparent;
        transition: all 0.25s ease;
        background: #f8fafc;
    }
    .ps-gallery-thumb img {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        object-fit: cover;
    }
    .ps-gallery-thumb.active {
        border-color: #115789;
        box-shadow: 0 0 0 3px rgba(17, 87, 137, 0.15);
    }
    .ps-gallery-thumb:hover:not(.active) {
        border-color: #94a3b8;
        transform: translateY(-2px);
    }

    /* ===== BADGES ===== */
    .ps-badge-discount {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 800;
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #dc2626;
        border: 1px solid #fca5a5;
    }
    .ps-badge-laris {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #b45309;
        border: 1px solid #fcd34d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .ps-badge-stok-terbatas {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #dc2626;
        border: 1px solid #fca5a5;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        animation: pulse-badge 2s infinite;
    }
    @keyframes pulse-badge {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    /* ===== PRICE ===== */
    .ps-price-current {
        font-size: 2rem;
        font-weight: 900;
        background: linear-gradient(135deg, #115789, #1e6faa);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1.2;
    }
    .ps-price-original {
        font-size: 1rem;
        color: #9ca3af;
        text-decoration: line-through;
        font-weight: 500;
    }

    /* ===== SELLER COMPACT ===== */
    .ps-seller-compact {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px 6px 8px;
        border-radius: 999px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        font-size: 0.8125rem;
        color: #334155;
        font-weight: 600;
        transition: all 0.2s;
    }
    .ps-seller-compact:hover {
        background: #e2e8f0;
    }
    .ps-seller-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #22c55e;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.4); }
        50% { box-shadow: 0 0 0 4px rgba(34,197,94,0); }
    }

    /* ===== SHARE BUTTONS ===== */
    .ps-share-group {
        display: flex;
        gap: 8px;
    }
    .ps-share-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 0.625rem;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
    }
    .ps-share-btn:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .ps-share-btn.whatsapp:hover {
        border-color: #22c55e;
        color: #16a34a;
        background: #f0fdf4;
    }
    .ps-share-btn.copy-link:hover {
        border-color: #3b82f6;
        color: #2563eb;
        background: #eff6ff;
    }

    /* ===== ACTION CARD ===== */
    .ps-action-card {
        border: 1px solid #e2e8f0;
        border-radius: 1.25rem;
        padding: 1.5rem;
        background: #ffffff;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.07);
    }
    .sticky-card { top: 20px; transition: top 0.3s ease-in-out; }
    .sticky-card.header-visible { top: 120px; }

    /* Gradient Buy Button */
    .ps-btn-buy {
        width: 100%;
        background: linear-gradient(135deg, #115789, #1a7bc4);
        color: white;
        font-weight: 700;
        height: 48px;
        border-radius: 0.875rem;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 0.9375rem;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(17, 87, 137, 0.3);
        position: relative;
        overflow: hidden;
    }
    .ps-btn-buy::before {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
        transition: left 0.5s;
    }
    .ps-btn-buy:hover::before { left: 100%; }
    .ps-btn-buy:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(17, 87, 137, 0.4);
    }
    .ps-btn-buy:active { transform: translateY(0); }

    .ps-btn-cart {
        width: 100%;
        background: #fff;
        color: #115789;
        font-weight: 700;
        height: 48px;
        border-radius: 0.875rem;
        border: 2px solid #115789;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 0.9375rem;
        transition: all 0.3s;
    }
    .ps-btn-cart:hover {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(17, 87, 137, 0.1);
    }
    .ps-btn-cart:active { transform: translateY(0); }

    /* Quantity Controls */
    .ps-qty-wrap {
        display: flex;
        align-items: center;
        border: 1.5px solid #e2e8f0;
        border-radius: 0.75rem;
        overflow: hidden;
        height: 40px;
        background: #f8fafc;
    }
    .ps-qty-btn {
        width: 36px; height: 100%;
        display: flex; align-items: center; justify-content: center;
        background: transparent;
        border: none;
        cursor: pointer;
        color: #64748b;
        transition: all 0.15s;
    }
    .ps-qty-btn:hover { background: #e2e8f0; color: #115789; }
    .ps-qty-input {
        width: 48px; height: 100%;
        text-align: center;
        border: none; border-left: 1.5px solid #e2e8f0; border-right: 1.5px solid #e2e8f0;
        font-weight: 700; font-size: 0.9375rem;
        color: #1e293b;
        background: #fff;
        -moz-appearance: textfield;
    }
    .ps-qty-input::-webkit-outer-spin-button,
    .ps-qty-input::-webkit-inner-spin-button {
        -webkit-appearance: none; margin: 0;
    }

    /* ===== TABS ===== */
    .ps-tab-btn {
        padding: 12px 20px;
        font-size: 0.875rem;
        font-weight: 600;
        border-bottom: 2.5px solid transparent;
        color: #94a3b8;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .ps-tab-btn:hover { color: #475569; }
    .ps-tab-btn.active-tab {
        border-bottom-color: #115789;
        color: #115789;
    }

    /* ===== LIGHTBOX ===== */
    .ps-lightbox {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.85);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: zoom-out;
        animation: fadeIn 0.2s ease;
    }
    .ps-lightbox img {
        max-width: 90vw;
        max-height: 90vh;
        object-fit: contain;
        border-radius: 0.75rem;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Copy toast */
    .ps-copy-toast {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #1e293b;
        color: #fff;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 0.8125rem;
        font-weight: 600;
        z-index: 10000;
        transition: transform 0.3s ease;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }
    .ps-copy-toast.show {
        transform: translateX(-50%) translateY(0);
    }
</style>
@endpush

@section('page')
<main id="main-content" class="flex-grow bg-gray-50/50 pb-16 detail-container" style="transition: padding-top 0.3s ease-in-out; padding-top: 50px;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-6 text-sm text-gray-500 mt-4">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('beranda') }}" class="hover:text-[#115789] transition font-medium">Beranda</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        <a href="{{ route('pasar.index') }}" class="hover:text-[#115789] transition font-medium">Pasar Daerah</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        <span class="text-gray-800 font-semibold line-clamp-1 max-w-[200px] sm:max-w-md">{{ $produk->nama_produk }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100/60 overflow-visible mb-12">
            <div class="p-6 md:p-8">
                <div class="ps-detail-grid">
                    
                    <!-- ===== COLUMN 1: Image Gallery (5 cols) ===== -->
                    <div class="ps-col-image">
                        <div class="ps-gallery-main group" onclick="openLightbox(document.getElementById('mainImage').src)">
                            @if($produk->foto)
                                <img src="{{ Storage::url($produk->foto) }}" alt="{{ $produk->nama_produk }}" id="mainImage">
                            @else
                                <div class="absolute inset-0 flex items-center justify-center text-gray-300">
                                    <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif

                            <!-- Badges on Image -->
                            <div class="absolute top-3 left-3 flex flex-col gap-2" style="z-index:2;">
                                @if($produk->stok > 0 && $produk->stok <= 5)
                                    <span class="ps-badge-stok-terbatas">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.27 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                                        Stok Terbatas
                                    </span>
                                @endif
                                <span class="ps-badge-laris">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"></path></svg>
                                    Laris
                                </span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-5 gap-3 mt-3">
                            @if($produk->foto)
                            <button onclick="changeImage('{{ Storage::url($produk->foto) }}', this)" class="ps-gallery-thumb active">
                                <img src="{{ Storage::url($produk->foto) }}">
                            </button>
                            @endif
                            @if($produk->foto_2)
                            <button onclick="changeImage('{{ Storage::url($produk->foto_2) }}', this)" class="ps-gallery-thumb">
                                <img src="{{ Storage::url($produk->foto_2) }}">
                            </button>
                            @endif
                            @if($produk->foto_3)
                            <button onclick="changeImage('{{ Storage::url($produk->foto_3) }}', this)" class="ps-gallery-thumb">
                                <img src="{{ Storage::url($produk->foto_3) }}">
                            </button>
                            @endif
                        </div>
                    </div>
                    
                    <!-- ===== COLUMN 2: Product Info (4 cols) ===== -->
                    <div class="ps-col-info flex flex-col">
                        <!-- Category Badge -->
                        <div class="mb-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                {{ $produk->kategori }}
                            </span>
                        </div>

                        <!-- Product Name -->
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-tight mb-3">
                            {{ $produk->nama_produk }}
                        </h1>

                        <!-- Stats Row -->
                        <div class="flex items-center text-sm text-gray-500 mb-5">
                            <div class="flex items-center">
                                <span class="text-slate-400 mr-1.5">Terjual</span>
                                <span class="font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded text-xs">10+</span>
                            </div>
                        </div>
                        
                        <!-- Price Block -->
                        <div class="mb-5">
                            <div class="flex items-center gap-3 flex-wrap">
                                <span class="ps-price-current">Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">per {{ $produk->satuan ?? 'pcs' }}</p>
                        </div>

                        <hr class="border-gray-100 mb-5">

                        <!-- Seller Compact -->
                        <div class="mb-5">
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-2">Penjual</p>
                            <div class="ps-seller-compact">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white flex-shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <span>{{ $produk->region->name ?? 'Wilayah Tidak Diketahui' }}</span>
                                <div class="ps-seller-dot"></div>
                            </div>
                        </div>

                        <!-- Share Buttons -->
                        <div class="mb-6">
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-2">Bagikan</p>
                            <div class="ps-share-group">
                                <button type="button" onclick="shareWhatsApp()" class="ps-share-btn whatsapp">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"></path></svg>
                                    WhatsApp
                                </button>
                                <button type="button" onclick="copyLink()" class="ps-share-btn copy-link">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                    Salin Link
                                </button>
                            </div>
                        </div>

                        <hr class="border-gray-100 mb-5">
                        
                        <!-- Tabbed Content (Detail & Ulasan) -->
                        <div x-data="{ tab: 'detail' }" class="mb-4">
                            <!-- Tab Headers -->
                            <div class="flex border-b border-gray-200 mb-6 overflow-x-auto" style="-ms-overflow-style:none;scrollbar-width:none;">
                                <button @click="tab = 'detail'" 
                                    :class="tab === 'detail' ? 'active-tab' : ''" 
                                    class="ps-tab-btn">
                                    Detail Produk
                                </button>
                                <button @click="tab = 'info'" 
                                    :class="tab === 'info' ? 'active-tab' : ''" 
                                    class="ps-tab-btn">
                                    Info Penting
                                </button>
                            </div>
                            
                            <!-- Tab Content -->
                            <div x-show="tab === 'detail'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="prose prose-sm md:prose-base prose-blue max-w-none text-gray-700">
                                <div class="leading-relaxed whitespace-pre-line text-[15px]">{!! e($produk->deskripsi ?? 'Tidak ada deskripsi lengkap untuk produk ini.') !!}</div>
                            </div>


                            <div x-show="tab === 'info'" x-cloak style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                                <div class="space-y-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-bold text-gray-900">Proses & Pengiriman Cepat</h4>
                                            <p class="text-sm text-gray-600 mt-1">Karena penjual berada di desa yang sama, pesanan umumnya diproses dan sampai dalam hitungan jam di hari yang sama.</p>
                                        </div>
                                    </div>
                                    <div class="flex">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-bold text-gray-900">Garansi & Retur Mudah</h4>
                                            <p class="text-sm text-gray-600 mt-1">Barang tidak sesuai? Hubungi penjual langsung di desa Anda untuk proses penukaran atau pengembalian yang lebih fleksibel.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ===== COLUMN 3: Action Card (3 cols) ===== -->
                    <div class="ps-col-action">
                        <div class="sticky sticky-card ps-action-card" id="action-card">
                            <h3 class="font-bold text-gray-900 mb-4 pb-3 border-b border-gray-100 text-lg">Atur Jumlah dan Catatan</h3>
                            
                            <div class="flex items-center mb-4">
                                <div class="ps-qty-wrap">
                                    <button type="button" onclick="updateQty(-1)" class="ps-qty-btn">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                    </button>
                                    <input type="number" id="qtyInput" name="quantity" value="1" min="1" max="{{ $produk->stok }}" class="ps-qty-input" readonly>
                                    <button type="button" onclick="updateQty(1)" class="ps-qty-btn">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="text-gray-500">Stok: </span>
                                    <span class="font-bold {{ $produk->stok > 0 ? ($produk->stok <= 5 ? 'text-orange-500' : 'text-gray-900') : 'text-red-500' }}">{{ $produk->stok }}</span>
                                    @if($produk->stok > 0 && $produk->stok <= 5)
                                        <span class="text-orange-500 text-xs font-medium ml-1">Tersisa sedikit!</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Catatan Section -->
                            <div x-data="{ openNote: false }" class="mb-5">
                                <button type="button" @click="openNote = !openNote" class="text-sm text-[#115789] font-medium flex items-center hover:underline focus:outline-none">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    <span x-text="openNote ? 'Batalkan Catatan' : 'Tambah Catatan'">Tambah Catatan</span>
                                </button>
                                <div x-show="openNote" x-collapse x-cloak style="display: none;" class="mt-3">
                                    <input type="text" id="catatanInput" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-[#115789]/20 focus:border-[#115789] placeholder-gray-400" placeholder="Contoh: Warna merah, ukuran L, dll.">
                                </div>
                            </div>

                            <div class="flex justify-between items-center mb-6 pt-4 border-t border-gray-100">
                                <span class="text-gray-600 font-medium">Subtotal</span>
                                <span class="text-xl font-extrabold text-gray-900" id="subtotalDisplay">Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                            </div>

                            @auth
                                <div class="flex flex-col gap-3">
                                    <button type="button" onclick="submitCart(true)" class="ps-btn-buy" {{ $produk->stok == 0 ? 'disabled' : '' }}>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                        Beli Langsung
                                    </button>
                                    <button type="button" onclick="submitCart(false)" class="ps-btn-cart" {{ $produk->stok == 0 ? 'disabled' : '' }}>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        Keranjang
                                    </button>
                                </div>
                            @else
                                <a href="{{ route('auth.login') }}" class="ps-btn-buy text-center" style="text-decoration:none;">
                                    Login untuk Membeli
                                </a>
                            @endauth
                            
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Lightbox Overlay -->
<div id="lightbox" class="ps-lightbox" style="display:none;" onclick="closeLightbox()">
    <img id="lightboxImg" src="" alt="Zoom">
</div>

<!-- Copy Toast -->
<div id="copyToast" class="ps-copy-toast">✓ Link berhasil disalin!</div>

@push('scripts')
<script>
    const maxStok = {{ $produk->stok }};
    const hargaSatuan = {{ $produk->harga }};
    const qtyInput = document.getElementById('qtyInput');
    const subtotalDisplay = document.getElementById('subtotalDisplay');

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number).replace('Rp', 'Rp ');
    }

    function updateQty(change) {
        if (!qtyInput) return;
        let current = parseInt(qtyInput.value) || 1;
        let next = current + change;
        if (next >= 1 && next <= maxStok) {
            qtyInput.value = next;
            updateSubtotal();
        }
    }

    function updateSubtotal() {
        if (!qtyInput || !subtotalDisplay) return;
        let current = parseInt(qtyInput.value) || 1;
        subtotalDisplay.innerText = formatRupiah(current * hargaSatuan);
    }

    function changeImage(src, btn) {
        document.getElementById('mainImage').src = src;
        document.querySelectorAll('.ps-gallery-thumb').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');
    }

    // Lightbox
    function openLightbox(src) {
        document.getElementById('lightboxImg').src = src;
        document.getElementById('lightbox').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        document.getElementById('lightbox').style.display = 'none';
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => { if(e.key === 'Escape') closeLightbox(); });

    // Share
    function shareWhatsApp() {
        const text = `Lihat produk ini: *{{ $produk->nama_produk }}* - Rp {{ number_format($produk->harga, 0, ',', '.') }}\n${window.location.href}`;
        window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
    }
    function copyLink() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            const toast = document.getElementById('copyToast');
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2000);
        });
    }

    function submitCart(isDirectBuy) {
        if (maxStok == 0) return;
        fetch('{{ route("pasar.cart.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                pasar_produk_id: {{ $produk->id }},
                quantity: qtyInput.value,
                catatan: document.getElementById('catatanInput') ? document.getElementById('catatanInput').value : ''
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if(isDirectBuy) {
                    window.location.href = '{{ route("pasar.cart") }}';
                } else {
                    showSiladesBengToast('success', 'Tersimpan!', 'Produk ditambahkan ke keranjang.', 2000);
                }
            } else {
                showSiladesBengToast('error', 'Gagal', data.message || 'Gagal menambahkan ke keranjang');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showSiladesBengToast('error', 'Error', 'Terjadi kesalahan sistem');
        });
    }

    // Sync Action Card and Main Content with Header visibility
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.getElementById('master-navbar');
        const actionCard = document.getElementById('action-card');
        const mainContent = document.getElementById('main-content');
        
        if (header) {
            const updatePositions = () => {
                const isHidden = header.classList.contains('hidden-nav');
                if (actionCard) {
                    if (isHidden) {
                        actionCard.classList.remove('header-visible');
                    } else {
                        actionCard.classList.add('header-visible');
                    }
                }
                if (mainContent) {
                    mainContent.style.paddingTop = isHidden ? '0px' : '50px';
                }
            };
            
            updatePositions();
            
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'class') {
                        updatePositions();
                    }
                });
            });
            
            observer.observe(header, { attributes: true });
        }
    });
</script>
@endpush
@endsection
