@extends('layouts.user')

@section('title', 'Checkout - Pasar Daerah')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    * { font-family: 'Inter', sans-serif; }

    /* Background */
    .checkout-bg {
        position: fixed; inset: 0; z-index: 0;
        background: linear-gradient(135deg, #f0f7ff 0%, #fafbff 40%, #fff8f0 100%);
    }
    .checkout-bg::before {
        content: ''; position: absolute; top: -50%; right: -30%; width: 80%; height: 80%;
        background: radial-gradient(circle, rgba(17,87,137,0.04) 0%, transparent 70%); border-radius: 50%;
    }
    .checkout-bg::after {
        content: ''; position: absolute; bottom: -30%; left: -20%; width: 60%; height: 60%;
        background: radial-gradient(circle, rgba(245,158,11,0.04) 0%, transparent 70%); border-radius: 50%;
    }

    /* Header */
    .co-header { display: flex; align-items: center; gap: 16px; margin-bottom: 12px; }
    .co-back-btn {
        width: 44px; height: 44px; border-radius: 14px; background: white; border: 1px solid #e2e8f0;
        display: flex; align-items: center; justify-content: center; color: #64748b; text-decoration: none;
        transition: all 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .co-back-btn:hover { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; transform: translateX(-3px); }
    .co-title { font-size: 1.75rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
    .co-subtitle { font-size: 0.8rem; color: #94a3b8; font-weight: 500; margin-top: 2px; display: flex; align-items: center; gap: 6px; }

    /* Progress Steps */
    .progress-steps {
        display: flex; align-items: center; justify-content: center; gap: 0;
        margin-bottom: 32px; padding: 16px 24px;
        background: white; border-radius: 16px; border: 1px solid rgba(226,232,240,0.6);
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
    }
    .step-item { display: flex; align-items: center; gap: 10px; }
    .step-circle {
        width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 800; transition: all 0.3s;
    }
    .step-circle.done { background: linear-gradient(135deg, #10b981, #34d399); color: white; }
    .step-circle.active { background: linear-gradient(135deg, #115789, #1d6aaa); color: white; box-shadow: 0 0 0 4px rgba(17,87,137,0.15); }
    .step-circle.pending { background: #f1f5f9; color: #94a3b8; border: 2px solid #e2e8f0; }
    .step-label { font-size: 0.8rem; font-weight: 600; }
    .step-label.done { color: #10b981; }
    .step-label.active { color: #115789; }
    .step-label.pending { color: #94a3b8; }
    .step-line { width: 48px; height: 2px; margin: 0 12px; border-radius: 2px; }
    .step-line.done { background: linear-gradient(90deg, #10b981, #34d399); }
    .step-line.active { background: linear-gradient(90deg, #10b981, #115789); }
    .step-line.pending { background: #e2e8f0; }

    /* Section Card */
    .co-card {
        background: white; border-radius: 20px;
        border: 1px solid rgba(226,232,240,0.6);
        box-shadow: 0 4px 24px rgba(0,0,0,0.04);
        overflow: hidden; margin-bottom: 20px;
    }
    .co-card-header {
        padding: 20px 24px; border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; gap: 12px;
        background: linear-gradient(to right, #fafbff, white);
    }
    .co-card-icon {
        width: 38px; height: 38px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .co-card-icon.blue { background: linear-gradient(135deg, #dbeafe, #eff6ff); color: #2563eb; }
    .co-card-icon.emerald { background: linear-gradient(135deg, #d1fae5, #ecfdf5); color: #059669; }
    .co-card-icon.amber { background: linear-gradient(135deg, #fef3c7, #fffbeb); color: #d97706; }
    .co-card-icon.purple { background: linear-gradient(135deg, #ede9fe, #f5f3ff); color: #7c3aed; }
    .co-card-title { font-size: 1rem; font-weight: 700; color: #1e293b; }
    .co-card-body { padding: 24px; }

    /* Form Input */
    .co-input-group { margin-bottom: 16px; }
    .co-input-group:last-child { margin-bottom: 0; }
    .co-label {
        display: flex; align-items: center; justify-content: space-between;
        font-size: 0.82rem; font-weight: 600; color: #475569; margin-bottom: 8px;
    }
    .co-label .required { color: #ef4444; margin-left: 2px; }
    .co-input {
        width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 14px;
        font-size: 0.9rem; color: #1e293b; background: #fafbff;
        transition: all 0.3s; outline: none;
    }
    .co-input:focus { border-color: #93c5fd; background: white; box-shadow: 0 0 0 4px rgba(59,130,246,0.1); }
    .co-input[readonly] { background: #f1f5f9; color: #64748b; cursor: default; }
    .co-textarea { resize: vertical; min-height: 80px; }

    /* Radio Option Cards */
    .radio-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .radio-card {
        position: relative; border: 2px solid #e2e8f0; border-radius: 16px;
        padding: 16px 18px; cursor: pointer; transition: all 0.3s;
        background: white; display: flex; align-items: center; gap: 14px;
    }
    .radio-card:hover { border-color: #93c5fd; background: #fafbff; }
    .radio-card.selected { border-color: #115789; background: linear-gradient(135deg, #eff6ff, #f0f7ff); box-shadow: 0 0 0 3px rgba(17,87,137,0.1); }
    .radio-card input[type="radio"] { display: none; }

    .radio-indicator {
        width: 22px; height: 22px; border-radius: 50%; border: 2px solid #cbd5e1;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; transition: all 0.3s;
    }
    .radio-card.selected .radio-indicator { border-color: #115789; background: #115789; }
    .radio-indicator::after {
        content: ''; width: 8px; height: 8px; border-radius: 50%;
        background: white; opacity: 0; transition: opacity 0.2s;
    }
    .radio-card.selected .radio-indicator::after { opacity: 1; }

    .radio-icon {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; font-size: 16px;
    }
    .radio-info { flex: 1; min-width: 0; }
    .radio-title { font-weight: 700; color: #1e293b; font-size: 0.88rem; }
    .radio-desc { font-size: 0.75rem; color: #64748b; margin-top: 2px; }
    .radio-badge {
        display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 8px;
        border-radius: 8px; margin-top: 4px;
    }

    /* Payment Grid */
    .pay-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

    /* Map */
    #map { height: 280px; z-index: 1; border-radius: 14px; border: 2px solid #e2e8f0; }

    /* Transfer Info */
    .transfer-box {
        background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #93c5fd;
        border-radius: 14px; padding: 16px; margin-bottom: 16px;
    }
    .transfer-box .bank-row {
        display: flex; justify-content: space-between; align-items: center;
        background: white; border-radius: 10px; padding: 12px 14px; margin-top: 12px;
        border: 1px solid #bfdbfe;
    }
    .bank-name { font-weight: 700; color: #1e293b; font-size: 0.85rem; }
    .bank-number { font-family: 'JetBrains Mono', monospace; font-size: 1.1rem; font-weight: 700; color: #115789; letter-spacing: 0.1em; }
    .bank-holder { font-size: 0.78rem; color: #64748b; margin-top: 2px; }
    .copy-btn {
        width: 36px; height: 36px; border-radius: 10px; border: 1px solid #bfdbfe;
        background: #eff6ff; color: #2563eb; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.2s;
    }
    .copy-btn:hover { background: #dbeafe; transform: scale(1.05); }

    /* File Upload */
    .file-upload-area {
        border: 2px dashed #cbd5e1; border-radius: 14px; padding: 24px; text-align: center;
        background: #fafbff; cursor: pointer; transition: all 0.3s;
    }
    .file-upload-area:hover { border-color: #93c5fd; background: #eff6ff; }
    .file-upload-area.has-file { border-color: #10b981; background: #ecfdf5; }
    .file-upload-area input[type="file"] { display: none; }

    /* Summary Card */
    .summary-card {
        background: white; border-radius: 20px;
        border: 1px solid rgba(226,232,240,0.6);
        box-shadow: 0 4px 24px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .summary-header {
        padding: 18px 22px; border-bottom: 1px solid #f1f5f9;
        background: linear-gradient(to right, #fafbff, white);
    }
    .summary-header h3 {
        font-size: 1rem; font-weight: 700; color: #1e293b;
        display: flex; align-items: center; gap: 8px;
    }
    .summary-body { padding: 20px 22px; }

    .summary-item {
        display: flex; align-items: center; gap: 12px; padding: 10px 0;
        border-bottom: 1px solid #f8fafc;
    }
    .summary-item:last-child { border-bottom: none; }
    .summary-item-img {
        width: 48px; height: 48px; border-radius: 10px; overflow: hidden;
        border: 1px solid #f1f5f9; flex-shrink: 0; background: #f8fafc;
    }
    .summary-item-img img { width: 100%; height: 100%; object-fit: cover; }
    .summary-item-info { flex: 1; min-width: 0; }
    .summary-item-name { font-size: 0.82rem; font-weight: 700; color: #1e293b; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
    .summary-item-qty { font-size: 0.72rem; color: #94a3b8; }
    .summary-item-price { font-size: 0.85rem; font-weight: 700; color: #1e293b; white-space: nowrap; }

    .summary-divider { border: none; border-top: 1px dashed #e2e8f0; margin: 12px 0; }

    .summary-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 6px 0; font-size: 0.82rem;
    }
    .summary-row .label { color: #64748b; }
    .summary-row .value { font-weight: 600; color: #1e293b; }

    .summary-total {
        display: flex; justify-content: space-between; align-items: center; padding: 14px 0;
    }
    .summary-total .label { font-weight: 700; color: #0f172a; font-size: 0.95rem; }
    .summary-total .value {
        font-size: 1.3rem; font-weight: 900;
        background: linear-gradient(135deg, #1d4ed8, #3b82f6);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    .co-submit-btn {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; padding: 15px 20px;
        background: linear-gradient(135deg, #115789, #1d6aaa); color: white;
        font-weight: 800; font-size: 0.95rem; border: none; border-radius: 14px;
        cursor: pointer; text-decoration: none; transition: all 0.3s;
        box-shadow: 0 4px 16px rgba(17,87,137,0.25); position: relative; overflow: hidden;
    }
    .co-submit-btn::before {
        content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
        transition: left 0.6s;
    }
    .co-submit-btn:hover::before { left: 100%; }
    .co-submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(17,87,137,0.35); }
    .co-submit-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .security-badge {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 12px; margin-top: 12px;
        font-size: 0.75rem; color: #64748b; font-weight: 500;
    }

    /* Delivery expansion */
    .delivery-expand {
        padding: 20px 24px 0; border-top: 1px dashed #e2e8f0; margin-top: 16px;
    }
    .map-info {
        background: linear-gradient(135deg, #fefce8, #fffbeb); border: 1px solid #fde68a;
        border-radius: 12px; padding: 12px 14px; margin-bottom: 14px;
        display: flex; align-items: flex-start; gap: 10px;
        font-size: 0.78rem; color: #92400e; line-height: 1.5;
    }
    .map-info svg { flex-shrink: 0; margin-top: 1px; }
    .distance-info { font-size: 0.75rem; color: #64748b; text-align: right; margin-top: 8px; font-weight: 500; }
    .profile-addr-btn {
        font-size: 0.75rem; font-weight: 700; color: #2563eb; background: none; border: none;
        cursor: pointer; transition: color 0.2s;
    }
    .profile-addr-btn:hover { color: #1d4ed8; text-decoration: underline; }

    /* Animations */
    .animate-in { animation: slideIn 0.5s ease forwards; opacity: 0; transform: translateY(16px); }
    @keyframes slideIn { to { opacity: 1; transform: translateY(0); } }

    /* Responsive */
    @media (max-width: 640px) {
        .radio-grid, .pay-grid { grid-template-columns: 1fr; }
        .co-title { font-size: 1.3rem; }
        .progress-steps { gap: 0; padding: 12px 16px; flex-wrap: nowrap; overflow-x: auto; }
        .step-label { font-size: 0.7rem; }
        .step-line { width: 24px; margin: 0 6px; }
    }
</style>
@endpush

@section('page')
<div class="checkout-bg"></div>

<div id="main-content" class="relative z-10 min-h-[80vh] pb-20" style="transition: padding-top 0.3s ease-in-out; padding-top: 50px;">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">

        <!-- Header -->
        <div class="co-header animate-in">
            <a href="{{ route('pasar.cart') }}" class="co-back-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="co-title">Checkout Pemesanan</h1>
                <p class="co-subtitle">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Pasar Daerah &mdash; {{ Auth::user()->region->name ?? 'Desa Anda' }}
                </p>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="progress-steps animate-in" style="animation-delay: 0.05s;">
            <div class="step-item">
                <div class="step-circle done">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="step-label done">Keranjang</span>
            </div>
            <div class="step-line done"></div>
            <div class="step-item">
                <div class="step-circle active">2</div>
                <span class="step-label active">Checkout</span>
            </div>
            <div class="step-line pending"></div>
            <div class="step-item">
                <div class="step-circle pending">3</div>
                <span class="step-label pending">Pembayaran</span>
            </div>
        </div>

        <form id="checkoutForm" action="{{ route('pasar.order.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Main Form -->
                <div class="w-full lg:w-[62%]">

                    <!-- 1. Informasi Pembeli -->
                    <div class="co-card animate-in" style="animation-delay: 0.1s;">
                        <div class="co-card-header">
                            <div class="co-card-icon blue">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <span class="co-card-title">Informasi Pembeli</span>
                        </div>
                        <div class="co-card-body">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="co-input-group">
                                    <label class="co-label">Nama Lengkap <span class="required">*</span></label>
                                    <input type="text" name="full_name" value="{{ Auth::user()->name }}" required class="co-input" readonly>
                                </div>
                                <div class="co-input-group">
                                    <label class="co-label">No. WhatsApp <span class="required">*</span></label>
                                    <input type="text" name="phone" value="{{ Auth::user()->phone }}" required class="co-input" placeholder="08xxxxxxxxxx">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Metode Pengiriman -->
                    <div class="co-card animate-in" style="animation-delay: 0.15s;">
                        <div class="co-card-header">
                            <div class="co-card-icon emerald">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                            </div>
                            <span class="co-card-title">Metode Pengiriman</span>
                        </div>
                        <div class="co-card-body">
                            <div class="radio-grid">
                                <label class="radio-card selected">
                                    <input type="radio" name="delivery_method" value="jemput" checked onchange="toggleDelivery(this.value)">
                                    <div class="radio-indicator"></div>
                                    <div class="radio-icon" style="background: linear-gradient(135deg, #d1fae5, #ecfdf5); color: #059669;">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    </div>
                                    <div class="radio-info">
                                        <div class="radio-title">Ambil Sendiri</div>
                                        <div class="radio-desc">Ambil di lokasi seller/toko</div>
                                        <span class="radio-badge" style="background: #d1fae5; color: #059669;">GRATIS</span>
                                    </div>
                                </label>

                                <label class="radio-card">
                                    <input type="radio" name="delivery_method" value="antar" onchange="toggleDelivery(this.value)">
                                    <div class="radio-indicator"></div>
                                    <div class="radio-icon" style="background: linear-gradient(135deg, #dbeafe, #eff6ff); color: #2563eb;">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                                    </div>
                                    <div class="radio-info">
                                        <div class="radio-title">Diantar Kurir Lokal</div>
                                        <div class="radio-desc">Pengantaran langsung di dalam desa</div>
                                        <span class="radio-badge" style="background: #dbeafe; color: #1d4ed8;">ADA ONGKIR</span>
                                    </div>
                                </label>
                            </div>

                            <!-- Delivery Form (Hidden) -->
                            <div id="deliveryForm" class="delivery-expand" style="display: none;">
                                <div class="co-input-group">
                                    <label class="co-label">
                                        <span>Alamat Pengiriman <span class="required">*</span></span>
                                        <button type="button" onclick="useProfileAddress()" class="profile-addr-btn">
                                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                            Gunakan Alamat Profil
                                        </button>
                                    </label>
                                    <textarea name="delivery_address" id="delivery_address" rows="3" class="co-input co-textarea" placeholder="Masukkan alamat lengkap pengiriman..."></textarea>
                                </div>

                                <div class="co-input-group">
                                    <label class="co-label">Titik Lokasi Antar <span class="required">*</span></label>
                                    <div class="map-info">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>Geser <b>marker merah</b> pada peta ke titik lokasi pengantaran untuk perhitungan ongkir.</span>
                                    </div>
                                    <div id="map"></div>
                                    <input type="hidden" name="delivery_latitude" id="delivery_latitude">
                                    <input type="hidden" name="delivery_longitude" id="delivery_longitude">
                                    <p class="distance-info" id="distanceInfo">Jarak belum dihitung</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Metode Pembayaran -->
                    <div class="co-card animate-in" style="animation-delay: 0.2s;">
                        <div class="co-card-header">
                            <div class="co-card-icon purple">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            </div>
                            <span class="co-card-title">Metode Pembayaran</span>
                        </div>
                        <div class="co-card-body">
                            <input type="hidden" name="payment_method" id="payment-method-hidden" value="tunai">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-2">
                                <!-- Tunai -->
                                <button type="button" 
                                        onclick="setPaymentMethod('tunai')"
                                        id="btn-tunai"
                                        class="payment-method-btn group relative py-4 px-2 rounded-2xl font-bold transition-all duration-300 active ring-2 ring-blue-500 bg-blue-50 shadow-md transform scale-105">
                                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="flex flex-col items-center justify-center gap-2 text-center relative z-10">
                                        <div class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center transition-colors shadow-inner" id="icon-tunai">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </div>
                                        <span class="text-[11px] uppercase tracking-wider text-blue-700" id="text-tunai">Bayar Tunai</span>
                                    </div>
                                </button>

                                <!-- Transfer Manual -->
                                <button type="button" 
                                        onclick="setPaymentMethod('transfer_manual')"
                                        id="btn-transfer_manual"
                                        class="payment-method-btn group relative py-4 px-2 rounded-2xl font-bold transition-all duration-300 bg-white shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md hover:-translate-y-1">
                                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="flex flex-col items-center justify-center gap-2 text-center relative z-10">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 group-hover:bg-blue-500 group-hover:text-white flex items-center justify-center transition-colors shadow-inner" id="icon-transfer_manual">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                        </div>
                                        <span class="text-[11px] uppercase tracking-wider text-gray-600 group-hover:text-blue-700" id="text-transfer_manual">Transfer Manual</span>
                                    </div>
                                </button>

                                <!-- QRIS -->
                                <button type="button" 
                                        onclick="setPaymentMethod('qris')"
                                        id="btn-qris"
                                        class="payment-method-btn group relative py-4 px-2 rounded-2xl font-bold transition-all duration-300 bg-white shadow-sm border border-gray-100 hover:border-red-500 hover:shadow-md hover:-translate-y-1 overflow-hidden">
                                    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(239,68,68,0.08)_0%,transparent_70%)] opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                    
                                    <div class="flex flex-col items-center justify-center gap-3 text-center h-full relative z-10">
                                        <div class="bg-white p-1 rounded-lg shadow-sm group-hover:shadow border border-gray-50 transform group-hover:scale-110 transition-all">
                                            <img src="{{ asset('admin/img/banks/qris.svg') }}" alt="QRIS" class="h-6 object-contain" onerror="this.src='{{ asset('admin/img/banks/dana.png') }}'">
                                        </div>
                                        <span class="text-[10px] uppercase tracking-widest text-gray-700 group-hover:text-red-600 font-black">All E-Wallet</span>
                                    </div>
                                </button>
                                
                                <!-- BCA VA -->
                                <button type="button" 
                                        onclick="setPaymentMethod('bank_transfer_bca')"
                                        id="btn-bank_transfer_bca"
                                        class="payment-method-btn group relative py-4 px-2 rounded-2xl font-bold transition-all duration-300 bg-white shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md hover:-translate-y-1">
                                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="flex flex-col items-center justify-center gap-3 text-center h-full relative z-10">
                                        <img src="{{ asset('admin/img/banks/bca.png') }}" alt="BCA" class="h-6 object-contain transform group-hover:scale-110 transition-transform">
                                        <span class="text-[10px] uppercase tracking-widest text-gray-700 group-hover:text-blue-600 font-bold">Virtual Account</span>
                                    </div>
                                </button>
                                
                                <!-- BRI VA -->
                                <button type="button" 
                                        onclick="setPaymentMethod('bank_transfer_bri')"
                                        id="btn-bank_transfer_bri"
                                        class="payment-method-btn group relative py-4 px-2 rounded-2xl font-bold transition-all duration-300 bg-white shadow-sm border border-gray-100 hover:border-orange-300 hover:shadow-md hover:-translate-y-1">
                                    <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="flex flex-col items-center justify-center gap-3 text-center h-full relative z-10">
                                        <img src="{{ asset('admin/img/banks/bri.png') }}" alt="BRI" class="h-6 object-contain transform group-hover:scale-110 transition-transform">
                                        <span class="text-[10px] uppercase tracking-widest text-gray-700 group-hover:text-orange-600 font-bold">Virtual Account</span>
                                    </div>
                                </button>
                                
                                <!-- Mandiri VA -->
                                <button type="button" 
                                        onclick="setPaymentMethod('bank_transfer_mandiri')"
                                        id="btn-bank_transfer_mandiri"
                                        class="payment-method-btn group relative py-4 px-2 rounded-2xl font-bold transition-all duration-300 bg-white shadow-sm border border-gray-100 hover:border-yellow-400 hover:shadow-md hover:-translate-y-1">
                                    <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/5 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="flex flex-col items-center justify-center gap-3 text-center h-full relative z-10">
                                        <img src="{{ asset('admin/img/banks/mandiri.png') }}" alt="Mandiri" class="h-6 object-contain transform group-hover:scale-110 transition-transform">
                                        <span class="text-[10px] uppercase tracking-widest text-gray-700 group-hover:text-yellow-600 font-bold">Virtual Account</span>
                                    </div>
                                </button>
                                
                                <!-- BNI VA -->
                                <button type="button" 
                                        onclick="setPaymentMethod('bank_transfer_bni')"
                                        id="btn-bank_transfer_bni"
                                        class="payment-method-btn group relative py-4 px-2 rounded-2xl font-bold transition-all duration-300 bg-white shadow-sm border border-gray-100 hover:border-orange-500 hover:shadow-md hover:-translate-y-1">
                                    <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="flex flex-col items-center justify-center gap-3 text-center h-full relative z-10">
                                        <img src="{{ asset('admin/img/banks/bni.png') }}" alt="BNI" class="h-6 object-contain transform group-hover:scale-110 transition-transform">
                                        <span class="text-[10px] uppercase tracking-widest text-gray-700 group-hover:text-orange-600 font-bold">Virtual Account</span>
                                    </div>
                                </button>
                            </div>

                            <!-- Transfer Manual Form -->
                            <div id="manualTransferForm" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 1px dashed #e2e8f0;">
                                <div class="transfer-box">
                                    <p style="font-size: 0.82rem; font-weight: 600; color: #1e40af; margin-bottom: 4px;">Instruksi Transfer</p>
                                    <p style="font-size: 0.78rem; color: #3b82f6;">Transfer sejumlah <strong id="transferAmount" style="color: #1d4ed8;">Rp 0</strong> ke rekening berikut:</p>
                                    <div class="bank-row">
                                        <div>
                                            <div class="bank-name">{{ $region->settings['rekening_bank'] ?? 'Bank Riau Kepri' }}</div>
                                            <div class="bank-number">{{ $region->settings['rekening_nomor'] ?? '123-456-7890' }}</div>
                                            <div class="bank-holder">a.n {{ $region->settings['rekening_nama'] ?? 'Pusat Layanan Daerah ' . $region->name }}</div>
                                        </div>
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $region->settings['rekening_nomor'] ?? '1234567890' }}'); Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Disalin!', showConfirmButton: false, timer: 1500})" class="copy-btn">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="co-input-group">
                                    <label class="co-label">Upload Bukti Transfer <span class="required">*</span></label>
                                    <div class="file-upload-area" id="uploadArea" onclick="document.getElementById('proof_of_payment').click()">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <p style="font-size: 0.82rem; font-weight: 600; color: #475569;" id="uploadLabel">Klik untuk upload bukti transfer</p>
                                        <p style="font-size: 0.72rem; color: #94a3b8; margin-top: 4px;">JPG, PNG, PDF &bull; Maks 5MB</p>
                                        <input type="file" name="proof_of_payment" id="proof_of_payment" accept="image/*,.pdf">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Catatan -->
                    <div class="co-card animate-in" style="animation-delay: 0.25s;">
                        <div class="co-card-header">
                            <div class="co-card-icon amber">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </div>
                            <span class="co-card-title">Catatan Pesanan</span>
                        </div>
                        <div class="co-card-body">
                            <textarea name="notes" rows="2" class="co-input co-textarea" placeholder="Opsional: Tolong dibungkus rapi, jangan pedas, dll..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div class="w-full lg:w-[38%] animate-in" style="animation-delay: 0.15s;">
                    <div id="summary-card" class="sticky" style="top: 20px; transition: top 0.3s ease-in-out;">
                        <div class="summary-card">
                            <div class="summary-header">
                                <h3>
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    Ringkasan Pesanan
                                </h3>
                            </div>
                            <div class="summary-body">
                                <div style="max-height: 240px; overflow-y: auto; padding-right: 4px;">
                                    @php $totalAmount = 0; @endphp
                                    @foreach($carts as $cart)
                                        @php $totalAmount += ($cart->produk->harga * $cart->quantity); @endphp
                                        <div class="summary-item">
                                            <div class="summary-item-img">
                                                @if($cart->produk->foto)
                                                    <img src="{{ Storage::url($cart->produk->foto) }}" alt="{{ $cart->produk->nama_produk }}">
                                                @endif
                                            </div>
                                            <div class="summary-item-info">
                                                <div class="summary-item-name">{{ $cart->produk->nama_produk }}</div>
                                                <div class="summary-item-qty">{{ $cart->quantity }} x Rp {{ number_format($cart->produk->harga, 0, ',', '.') }}</div>
                                            </div>
                                            <div class="summary-item-price">Rp {{ number_format($cart->produk->harga * $cart->quantity, 0, ',', '.') }}</div>
                                        </div>
                                    @endforeach
                                </div>

                                <hr class="summary-divider">

                                <div class="summary-row">
                                    <span class="label">Total Harga Barang</span>
                                    <span class="value" id="summaryTotal">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                                </div>
                                <div class="summary-row">
                                    <span class="label">Ongkos Kirim <small style="color: #94a3b8;" id="summaryDistance"></small></span>
                                    <span class="value" id="summaryOngkir">Rp 0</span>
                                </div>

                                <hr class="summary-divider">

                                <div class="summary-total">
                                    <span class="label">Total Pembayaran</span>
                                    <span class="value" id="summaryGrandTotal">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                                </div>

                                <button type="button" onclick="submitOrder()" id="btnSubmit" class="co-submit-btn">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                    Buat Pesanan Sekarang
                                </button>
                            </div>
                        </div>

                        <div class="security-badge">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Data Anda aman &amp; terenkripsi
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Payment Method Selection
    function setPaymentMethod(method) {
        // Update hidden input
        document.getElementById('payment-method-hidden').value = method;

        // Reset all buttons
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('active', 'ring-2', 'ring-blue-500', 'bg-blue-50', 'shadow-md', 'scale-105');
            btn.classList.add('bg-white', 'shadow-sm', 'border-gray-100');
            btn.classList.remove('transform'); // Removing transform ensures scale-105 is fully removed
        });

        // Reset specific icons for Tunai and Transfer Manual
        const tunaiIcon = document.getElementById('icon-tunai');
        const tunaiText = document.getElementById('text-tunai');
        const tmIcon = document.getElementById('icon-transfer_manual');
        const tmText = document.getElementById('text-transfer_manual');

        if (tunaiIcon) {
            tunaiIcon.className = "w-10 h-10 rounded-full bg-blue-100 text-blue-600 group-hover:bg-blue-500 group-hover:text-white flex items-center justify-center transition-colors shadow-inner";
            tunaiText.className = "text-[11px] uppercase tracking-wider text-gray-600 group-hover:text-blue-700";
        }
        if (tmIcon) {
            tmIcon.className = "w-10 h-10 rounded-full bg-blue-100 text-blue-600 group-hover:bg-blue-500 group-hover:text-white flex items-center justify-center transition-colors shadow-inner";
            tmText.className = "text-[11px] uppercase tracking-wider text-gray-600 group-hover:text-blue-700";
        }

        // Set active state
        const activeBtn = document.getElementById('btn-' + method);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'shadow-sm', 'border-gray-100');
            activeBtn.classList.add('active', 'ring-2', 'ring-blue-500', 'bg-blue-50', 'shadow-md', 'transform', 'scale-105');
        }

        // Set active specific icons
        if (method === 'tunai' && tunaiIcon) {
            tunaiIcon.className = "w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center transition-colors shadow-inner";
            tunaiText.className = "text-[11px] uppercase tracking-wider text-blue-700";
        } else if (method === 'transfer_manual' && tmIcon) {
            tmIcon.className = "w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center transition-colors shadow-inner";
            tmText.className = "text-[11px] uppercase tracking-wider text-blue-700";
        }

        // Handle Transfer Manual visibility
        const tmForm = document.getElementById('manualTransferForm');
        const input = document.getElementById('proof_of_payment');
        if (method === 'transfer_manual') {
            tmForm.style.display = 'block';
            input.required = true;
        } else {
            tmForm.style.display = 'none';
            input.required = false;
        }
    }

    // File Upload Label
    document.getElementById('proof_of_payment').addEventListener('change', function() {
        const area = document.getElementById('uploadArea');
        const label = document.getElementById('uploadLabel');
        if (this.files.length > 0) {
            area.classList.add('has-file');
            label.textContent = this.files[0].name;
            label.style.color = '#059669';
        } else {
            area.classList.remove('has-file');
            label.textContent = 'Klik untuk upload bukti transfer';
            label.style.color = '#475569';
        }
    });

    // Constants
    const baseTotal = {{ $totalAmount }};
    const ongkirPerKm = {{ $region->settings['ongkir_per_km'] ?? 0 }};
    const storeLat = parseFloat("{{ $carts->first()->produk->latitude ?? '1.482755' }}") || 1.482755;
    const storeLon = parseFloat("{{ $carts->first()->produk->longitude ?? '102.138407' }}") || 102.138407;

    let currentOngkir = 0;
    let currentDistance = 0;
    let map, marker;

    function initMap() {
        if(map) return;
        const defaultLat = parseFloat("{{ Auth::user()->latitude ?? '1.482755' }}") || 1.482755;
        const defaultLon = parseFloat("{{ Auth::user()->longitude ?? '102.138407' }}") || 102.138407;
        const defaultLocation = [defaultLat, defaultLon];
        map = L.map('map').setView(defaultLocation, 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        L.marker([storeLat, storeLon], {
            icon: L.divIcon({
                className: 'custom-div-icon',
                html: "<div style='background:linear-gradient(135deg,#2563eb,#3b82f6);width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3)'></div>",
                iconSize: [16, 16], iconAnchor: [8, 8]
            })
        }).addTo(map).bindPopup("Lokasi Toko");

        marker = L.marker(defaultLocation, {
            draggable: true,
            icon: L.divIcon({
                className: 'custom-div-icon',
                html: "<div style='background:linear-gradient(135deg,#ef4444,#f87171);width:20px;height:20px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3)'></div>",
                iconSize: [20, 20], iconAnchor: [10, 10]
            })
        }).addTo(map);

        updateCoordinates(marker.getLatLng());
        marker.on('dragend', e => updateCoordinates(marker.getLatLng()));
        map.on('click', e => { marker.setLatLng(e.latlng); updateCoordinates(e.latlng); });
    }

    function updateCoordinates(latlng) {
        document.getElementById('delivery_latitude').value = latlng.lat;
        document.getElementById('delivery_longitude').value = latlng.lng;
        calculateDistance(latlng.lat, latlng.lng);
    }

    function calculateDistance(lat, lon) {
        const R = 6371;
        const dLat = (lat - storeLat) * Math.PI / 180;
        const dLon = (lon - storeLon) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(storeLat * Math.PI / 180) * Math.cos(lat * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        currentDistance = (R * c).toFixed(2);
        currentOngkir = Math.round(currentDistance * ongkirPerKm);
        document.getElementById('distanceInfo').innerText = `Jarak perkiraan: ${currentDistance} km`;
        updateSummary();
    }

    function toggleDelivery(method) {
        const form = document.getElementById('deliveryForm');
        if (method === 'antar') {
            form.style.display = 'block';
            setTimeout(() => { initMap(); map.invalidateSize(); }, 150);
            updateSummary();
        } else {
            form.style.display = 'none';
            currentOngkir = 0;
            updateSummary();
        }
    }

    function toggleDelivery(method) {
        const form = document.getElementById('deliveryForm');
        if (method === 'antar') {
            form.style.display = 'block';
            setTimeout(() => { initMap(); map.invalidateSize(); }, 150);
            updateSummary();
        } else {
            form.style.display = 'none';
            currentOngkir = 0;
            updateSummary();
        }
    }

    function formatMoney(amount) {
        return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function updateSummary() {
        const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked').value;
        const appliedOngkir = deliveryMethod === 'antar' ? currentOngkir : 0;
        const grandTotal = baseTotal + appliedOngkir;
        document.getElementById('summaryOngkir').innerText = formatMoney(appliedOngkir);
        document.getElementById('summaryDistance').innerText = deliveryMethod === 'antar' && currentDistance > 0 ? `(${currentDistance} km)` : '';
        document.getElementById('summaryGrandTotal').innerText = formatMoney(grandTotal);
        document.getElementById('transferAmount').innerText = formatMoney(grandTotal);
    }

    function useProfileAddress() {
        document.getElementById('delivery_address').value = `{{ Auth::user()->address }}`;
        const lat = {{ Auth::user()->latitude ?? 'null' }};
        const lon = {{ Auth::user()->longitude ?? 'null' }};
        if(lat && lon && map) {
            const latlng = L.latLng(lat, lon);
            map.setView(latlng, 15);
            marker.setLatLng(latlng);
            updateCoordinates(latlng);
        }
    }

    function submitOrder() {
        const form = document.getElementById('checkoutForm');
        const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked').value;

        if (deliveryMethod === 'antar') {
            if (!document.getElementById('delivery_address').value) {
                Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Silakan isi alamat pengiriman.', showConfirmButton: false, timer: 3000});
                return;
            }
        }

        if (!form.checkValidity()) { form.reportValidity(); return; }

        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Memproses...';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = `/pasar-daerah/payment/${data.order_id}`;
            } else {
                Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Gagal', text: data.message || 'Terjadi kesalahan.', showConfirmButton: false, timer: 3000});
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg> Buat Pesanan Sekarang';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Error', text: 'Terjadi kesalahan pada sistem.', showConfirmButton: false, timer: 3000});
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg> Buat Pesanan Sekarang';
        });
    }

    // Sync with Header
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.getElementById('master-navbar');
        const summaryCard = document.getElementById('summary-card');
        const mainContent = document.getElementById('main-content');

        if (header) {
            const updatePositions = () => {
                const isHidden = header.classList.contains('hidden-nav');
                if (summaryCard) summaryCard.style.top = isHidden ? '20px' : '100px';
                if (mainContent) mainContent.style.paddingTop = isHidden ? '0px' : '50px';
            };
            updatePositions();
            new MutationObserver(mutations => {
                mutations.forEach(m => { if (m.attributeName === 'class') updatePositions(); });
            }).observe(header, { attributes: true });
        }

        // Init state
        toggleDelivery(document.querySelector('input[name="delivery_method"]:checked').value);
        setPaymentMethod(document.getElementById('payment-method-hidden').value);
        updateSummary();
    });
</script>
@endpush
