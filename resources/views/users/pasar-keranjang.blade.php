@extends('layouts.user')

@section('title', 'Keranjang Belanja - Pasar Daerah')

@push('styles')
<style>
    * { font-family: 'Inter', sans-serif; }

    .cart-page { min-height: 80vh; position: relative; }

    /* Animated Background */
    .cart-bg {
        position: fixed; inset: 0; z-index: 0;
        background: linear-gradient(135deg, #f0f7ff 0%, #fafbff 40%, #fff8f0 100%);
    }
    .cart-bg::before {
        content: ''; position: absolute; top: -50%; right: -30%; width: 80%; height: 80%;
        background: radial-gradient(circle, rgba(17,87,137,0.04) 0%, transparent 70%);
        border-radius: 50%;
    }
    .cart-bg::after {
        content: ''; position: absolute; bottom: -30%; left: -20%; width: 60%; height: 60%;
        background: radial-gradient(circle, rgba(245,158,11,0.04) 0%, transparent 70%);
        border-radius: 50%;
    }

    /* Header Breadcrumb */
    .cart-header {
        display: flex; align-items: center; gap: 16px; margin-bottom: 32px;
    }
    .cart-back-btn {
        width: 44px; height: 44px; border-radius: 14px;
        background: white; border: 1px solid #e2e8f0;
        display: flex; align-items: center; justify-content: center;
        color: #64748b; text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .cart-back-btn:hover {
        background: #eff6ff; border-color: #bfdbfe; color: #2563eb;
        transform: translateX(-3px);
    }
    .cart-title-group { flex: 1; }
    .cart-title {
        font-size: 1.75rem; font-weight: 800; color: #0f172a;
        letter-spacing: -0.02em; line-height: 1.2;
    }
    .cart-subtitle {
        font-size: 0.8rem; color: #94a3b8; font-weight: 500; margin-top: 4px;
        display: flex; align-items: center; gap: 6px;
    }

    /* Cart Item Card */
    .cart-items-card {
        background: white; border-radius: 20px;
        border: 1px solid rgba(226,232,240,0.6);
        box-shadow: 0 4px 24px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .cart-items-header {
        padding: 20px 24px; border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; justify-content: space-between;
        background: linear-gradient(to right, #fafbff, white);
    }
    .cart-items-header h3 {
        font-size: 1rem; font-weight: 700; color: #1e293b;
        display: flex; align-items: center; gap: 10px;
    }
    .cart-count-badge {
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        color: white; font-size: 11px; font-weight: 700;
        padding: 3px 10px; border-radius: 20px;
    }

    /* Store Section */
    .store-header {
        padding: 14px 24px;
        background: linear-gradient(to right, #f8fafc, #fafbff);
        border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; gap: 10px;
    }
    .store-icon {
        width: 32px; height: 32px; border-radius: 10px;
        background: linear-gradient(135deg, #dbeafe, #eff6ff);
        display: flex; align-items: center; justify-content: center;
        color: #2563eb;
    }
    .store-name { font-weight: 700; color: #1e293b; font-size: 0.9rem; }
    .store-badge {
        font-size: 9px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.08em;
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        color: #1d4ed8; padding: 3px 10px; border-radius: 20px;
    }

    /* Cart Item Row */
    .cart-item {
        padding: 20px 24px;
        display: flex; gap: 16px; align-items: flex-start;
        transition: background 0.2s;
        border-bottom: 1px solid #f8fafc;
    }
    .cart-item:last-child { border-bottom: none; }
    .cart-item:hover { background: #fafbff; }

    .cart-item-img {
        width: 100px; height: 100px; border-radius: 14px;
        overflow: hidden; flex-shrink: 0;
        border: 2px solid #f1f5f9;
        transition: all 0.3s ease;
        position: relative;
    }
    .cart-item-img:hover { border-color: #bfdbfe; transform: scale(1.03); }
    .cart-item-img img { width: 100%; height: 100%; object-fit: cover; }
    .cart-item-img .no-img {
        width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
        background: #f8fafc; color: #cbd5e1;
    }

    .cart-item-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
    .cart-item-name {
        font-weight: 700; color: #1e293b; font-size: 0.95rem;
        text-decoration: none; line-height: 1.3;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        transition: color 0.2s;
    }
    .cart-item-name:hover { color: #2563eb; }

    .cart-item-price {
        font-size: 1.15rem; font-weight: 900;
        background: linear-gradient(135deg, #1d4ed8, #3b82f6);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .cart-item-unit {
        font-size: 0.75rem; font-weight: 500;
        -webkit-text-fill-color: #94a3b8;
    }
    .cart-item-subtotal {
        font-size: 0.8rem; color: #64748b; font-weight: 500;
        display: flex; align-items: center; gap: 4px;
    }
    .cart-item-subtotal span { color: #1e293b; font-weight: 700; }

    .cart-item-actions {
        display: flex; align-items: center; justify-content: space-between;
        margin-top: 8px; padding-top: 10px;
        border-top: 1px dashed #f1f5f9;
    }

    /* Delete Button */
    .cart-del-btn {
        background: none; border: none; cursor: pointer;
        color: #94a3b8; font-size: 0.8rem; font-weight: 600;
        display: flex; align-items: center; gap: 5px;
        padding: 6px 12px; border-radius: 10px;
        transition: all 0.2s;
    }
    .cart-del-btn:hover { background: #fef2f2; color: #ef4444; }

    /* Quantity Stepper */
    .qty-stepper {
        display: flex; align-items: center;
        border: 2px solid #e2e8f0; border-radius: 12px;
        overflow: hidden; height: 38px;
        background: white;
        transition: border-color 0.2s;
    }
    .qty-stepper:hover { border-color: #bfdbfe; }
    .qty-btn {
        width: 36px; height: 100%; border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: #64748b; background: #f8fafc;
        transition: all 0.2s;
    }
    .qty-btn:hover { background: #eff6ff; color: #2563eb; }
    .qty-btn:active { transform: scale(0.9); }
    .qty-input {
        width: 44px; height: 100%; text-align: center;
        border: none; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;
        font-weight: 800; font-size: 0.9rem; color: #1e293b;
        outline: none; background: white;
    }

    /* Summary Card */
    .summary-card {
        background: white; border-radius: 20px;
        border: 1px solid rgba(226,232,240,0.6);
        box-shadow: 0 4px 24px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .summary-header {
        padding: 20px 24px; border-bottom: 1px solid #f1f5f9;
        background: linear-gradient(to right, #fafbff, white);
    }
    .summary-header h3 {
        font-size: 1rem; font-weight: 700; color: #1e293b;
        display: flex; align-items: center; gap: 8px;
    }
    .summary-body { padding: 20px 24px; }

    .summary-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 0; font-size: 0.85rem;
    }
    .summary-row .label { color: #64748b; }
    .summary-row .value { font-weight: 600; color: #1e293b; }

    .summary-divider { border: none; border-top: 1px dashed #e2e8f0; margin: 8px 0; }

    .summary-total {
        display: flex; justify-content: space-between; align-items: center;
        padding: 14px 0;
    }
    .summary-total .label { font-weight: 700; color: #0f172a; font-size: 0.95rem; }
    .summary-total .value {
        font-size: 1.3rem; font-weight: 900;
        background: linear-gradient(135deg, #1d4ed8, #3b82f6);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    .summary-info {
        background: linear-gradient(135deg, #fefce8, #fffbeb);
        border: 1px solid #fde68a; border-radius: 12px;
        padding: 12px 14px; margin-bottom: 16px;
        display: flex; align-items: flex-start; gap: 10px;
        font-size: 0.78rem; color: #92400e; line-height: 1.5;
    }
    .summary-info svg { flex-shrink: 0; margin-top: 1px; }

    .summary-checkout-btn {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; padding: 14px 20px;
        background: linear-gradient(135deg, #115789, #1d6aaa);
        color: white; font-weight: 800; font-size: 0.95rem;
        border: none; border-radius: 14px; cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 16px rgba(17,87,137,0.25);
        position: relative; overflow: hidden;
    }
    .summary-checkout-btn::before {
        content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
        transition: left 0.6s;
    }
    .summary-checkout-btn:hover::before { left: 100%; }
    .summary-checkout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(17,87,137,0.35);
    }

    /* Promo Card */
    .promo-card {
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        border: 1px solid #fde68a; border-radius: 16px;
        padding: 16px 20px; margin-bottom: 16px;
        display: flex; align-items: center; justify-content: space-between;
        cursor: pointer; transition: all 0.3s ease;
    }
    .promo-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(245,158,11,0.15); }
    .promo-left { display: flex; align-items: center; gap: 12px; }
    .promo-icon {
        width: 40px; height: 40px; border-radius: 12px;
        background: linear-gradient(135deg, #f59e0b, #fbbf24);
        display: flex; align-items: center; justify-content: center; color: white;
    }
    .promo-text { font-weight: 700; color: #92400e; font-size: 0.88rem; }
    .promo-sub { font-size: 0.75rem; color: #b45309; font-weight: 500; }

    /* Security Badge */
    .security-badge {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 12px; margin-top: 16px;
        font-size: 0.75rem; color: #64748b; font-weight: 500;
    }

    /* Empty Cart */
    .empty-cart {
        background: white; border-radius: 24px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 24px rgba(0,0,0,0.04);
        padding: 60px 40px; text-align: center;
        max-width: 500px; margin: 0 auto;
    }
    .empty-cart-icon {
        width: 100px; height: 100px; border-radius: 28px; margin: 0 auto 24px;
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        display: flex; align-items: center; justify-content: center;
    }
    .empty-cart h2 { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .empty-cart p { color: #64748b; font-size: 0.9rem; margin-bottom: 28px; line-height: 1.6; }
    .empty-cart-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 14px 32px; border-radius: 14px;
        background: linear-gradient(135deg, #115789, #1d6aaa);
        color: white; font-weight: 700; text-decoration: none;
        transition: all 0.3s; box-shadow: 0 4px 16px rgba(17,87,137,0.25);
    }
    .empty-cart-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(17,87,137,0.35); }

    /* Responsive */
    @media (max-width: 640px) {
        .cart-item { flex-direction: column; align-items: stretch; }
        .cart-item-img { width: 100%; height: 180px; border-radius: 12px; }
        .cart-title { font-size: 1.4rem; }
        .cart-item-actions { flex-direction: column; gap: 12px; align-items: stretch; }
        .qty-stepper { width: 100%; justify-content: center; }
        .cart-del-btn { justify-content: center; }
    }

    /* Animations */
    .animate-in {
        animation: slideIn 0.5s ease forwards;
        opacity: 0; transform: translateY(16px);
    }
    @keyframes slideIn {
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('page')
<div class="cart-bg"></div>

<div id="main-content" class="cart-page relative z-10 pb-20" style="transition: padding-top 0.3s ease-in-out; padding-top: 50px;">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">

        @if(session('error'))
        <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 flex items-center shadow-sm border border-red-100 animate-in">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('error') }}
        </div>
        @endif
        @if(session('success'))
        <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 flex items-center shadow-sm border border-green-100 animate-in">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
        </div>
        @endif

        <!-- Header -->
        <div class="cart-header animate-in">
            <a href="{{ route('pasar.index') }}" class="cart-back-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div class="cart-title-group">
                <h1 class="cart-title">Keranjang Belanja</h1>
                <p class="cart-subtitle">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Pasar Daerah &mdash; {{ Auth::user()->region->name ?? 'Desa Anda' }}
                </p>
            </div>
        </div>

        @if($carts->isEmpty())
        <!-- Empty State -->
        <div class="empty-cart animate-in" style="animation-delay: 0.1s;">
            <div class="empty-cart-icon">
                <svg class="w-12 h-12 text-[#115789]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h2>Keranjang Anda Kosong</h2>
            <p>Belum ada produk di keranjang. Yuk, jelajahi produk dari desa Anda!</p>
            <a href="{{ route('pasar.index') }}" class="empty-cart-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                Mulai Belanja
            </a>
        </div>
        @else

        @php
            $total = 0;
            $groupedCarts = $carts->groupBy(function($item) {
                return $item->produk->region->name ?? 'Wilayah Tidak Diketahui';
            });
        @endphp

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Cart Items -->
            <div class="w-full lg:w-[62%] animate-in" style="animation-delay: 0.1s;">
                <div class="cart-items-card">
                    <div class="cart-items-header">
                        <h3>
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            Daftar Produk
                            <span class="cart-count-badge">{{ $carts->count() }} item</span>
                        </h3>
                    </div>

                    @foreach($groupedCarts as $storeName => $storeCarts)
                    <div>
                        <!-- Store Header -->
                        <div class="store-header">
                            <div class="store-icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <span class="store-name">{{ $storeName }}</span>
                            <span class="store-badge">Toko Resmi</span>
                        </div>

                        <!-- Items -->
                        @foreach($storeCarts as $cart)
                            @php
                                $subtotal = $cart->produk->harga * $cart->quantity;
                                $total += $subtotal;
                            @endphp
                            <div class="cart-item">
                                <a href="{{ route('pasar.show', $cart->produk->id) }}" class="cart-item-img">
                                    @if($cart->produk->foto)
                                        <img src="{{ Storage::url($cart->produk->foto) }}" alt="{{ $cart->produk->nama_produk }}">
                                    @else
                                        <div class="no-img">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif
                                </a>

                                <div class="cart-item-body">
                                    <a href="{{ route('pasar.show', $cart->produk->id) }}" class="cart-item-name">{{ $cart->produk->nama_produk }}</a>

                                    <div class="cart-item-price">
                                        Rp {{ number_format($cart->produk->harga, 0, ',', '.') }}
                                        <span class="cart-item-unit">/ {{ $cart->produk->satuan ?? 'pcs' }}</span>
                                    </div>

                                    <div class="cart-item-subtotal">
                                        Subtotal: <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                    </div>

                                    <div class="cart-item-actions">
                                        <button type="button" onclick="removeItem({{ $cart->id }})" class="cart-del-btn">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Hapus
                                        </button>

                                        <div class="qty-stepper">
                                            <button type="button" onclick="updateItem({{ $cart->id }}, -1, {{ $cart->produk->stok }})" class="qty-btn">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"></path></svg>
                                            </button>
                                            <input type="number" id="qty_{{ $cart->id }}" value="{{ $cart->quantity }}" readonly class="qty-input">
                                            <button type="button" onclick="updateItem({{ $cart->id }}, 1, {{ $cart->produk->stok }})" class="qty-btn">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Summary Sidebar -->
            <div class="w-full lg:w-[38%] animate-in" style="animation-delay: 0.2s;">
                <div id="summary-card" class="sticky" style="top: 20px; transition: top 0.3s ease-in-out;">

                    <!-- Promo Card -->
                    <div class="promo-card">
                        <div class="promo-left">
                            <div class="promo-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                            </div>
                            <div>
                                <div class="promo-text">Makin hemat pakai promo</div>
                                <div class="promo-sub">Cek voucher tersedia</div>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>

                    <!-- Summary -->
                    <div class="summary-card">
                        <div class="summary-header">
                            <h3>
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                Ringkasan Belanja
                            </h3>
                        </div>
                        <div class="summary-body">
                            <div class="summary-row">
                                <span class="label">Total Harga ({{ $carts->sum('quantity') }} barang)</span>
                                <span class="value">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>

                            <hr class="summary-divider">

                            <div class="summary-info">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Biaya pengiriman akan dihitung pada langkah berikutnya (Checkout).</span>
                            </div>

                            <div class="summary-total">
                                <span class="label">Total Belanja</span>
                                <span class="value">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>

                            <a href="{{ route('pasar.checkout') }}" class="summary-checkout-btn">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                Lanjut Bayar ({{ $carts->sum('quantity') }})
                            </a>
                        </div>
                    </div>

                    <!-- Trust Badges -->
                    <div class="security-badge">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Transaksi aman &amp; terverifikasi
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateItem(cartId, change, maxStok) {
        const input = document.getElementById(`qty_${cartId}`);
        let current = parseInt(input.value);
        let next = current + change;

        if (next < 1) next = 1;
        if (next > maxStok) {
            Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Stok tidak mencukupi!', showConfirmButton: false, timer: 3000});
            return;
        }

        // Optimistic UI update
        input.value = next;

        fetch('{{ route('pasar.cart.update') }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                cart_id: cartId,
                quantity: next
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Gagal', text: data.message || 'Gagal update keranjang', showConfirmButton: false, timer: 3000}).then(() => {
                    location.reload();
                });
            }
        });
    }

    function removeItem(cartId) {
        Swal.fire({
            title: 'Hapus Produk?',
            text: "Produk ini akan dihapus dari keranjang belanja Anda.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-2xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/pasar-daerah/cart/remove/${cartId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        });
    }

    // Sync Summary Card and Main Content with Header visibility
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.getElementById('master-navbar');
        const summaryCard = document.getElementById('summary-card');
        const mainContent = document.getElementById('main-content');

        if (header) {
            const updatePositions = () => {
                const isHidden = header.classList.contains('hidden-nav');

                if (summaryCard) {
                    summaryCard.style.top = isHidden ? '20px' : '100px';
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
