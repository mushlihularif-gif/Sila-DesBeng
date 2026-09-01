@extends('layouts.user')

@php
    $cleanRegionName = preg_replace('/^Desa\s+/i', '', $region->name ?? 'Bengkalis');
    $waPhone = null;
    if ($seller && ($seller->whatsapp || $seller->phone)) {
        $waPhone = preg_replace('/[^0-9]/', '', $seller->whatsapp ?: $seller->phone);
        if (str_starts_with($waPhone, '0')) {
            $waPhone = '62' . substr($waPhone, 1);
        }
    }
@endphp

@section('title', 'Toko BUMDes ' . $cleanRegionName . ' - Pasar Daerah SiladesBeng')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pasar-daerah.css') }}">
<style>
    * { font-family: 'Inter', sans-serif; }

    /* Responsive Store Page Wrapper that adapts to navbar */
    .store-page-wrapper {
        min-height: 100vh;
        padding-top: 140px;
        padding-bottom: 80px;
        transition: padding-top 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: #f8fafc;
    }

    @media (min-width: 640px) {
        .store-page-wrapper {
            padding-top: 165px;
        }
    }

    /* Smoothly adapt when user collapses the master navbar */
    body.navbar-is-hidden .store-page-wrapper {
        padding-top: 60px !important;
    }

    /* Shopee Store Header Container */
    .shopee-store-header {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 4px 25px -4px rgba(0, 0, 0, 0.04), 0 2px 6px -1px rgba(0, 0, 0, 0.02);
        border: 1px solid #f1f5f9;
        margin-bottom: 24px;
        overflow: hidden;
    }

    /* Shopee Split Header Layout */
    .shopee-header-grid {
        display: grid;
        grid-template-columns: 1fr;
    }

    @media (min-width: 992px) {
        .shopee-header-grid {
            grid-template-columns: 390px 1fr;
        }
    }

    /* Left Card: Store Profile & Identity */
    .shopee-left-profile {
        position: relative;
        padding: 24px 28px;
        background: transparent;
        border: none;
        color: #1e293b;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 180px;
    }

    @media (max-width: 991px) {
        .shopee-left-profile {
            border: none;
            padding-bottom: 12px;
        }
    }

    .shopee-avatar-wrapper {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .shopee-avatar {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        border: 3px solid #ffffff;
        background: linear-gradient(135deg, #115789 0%, #0284c7 100%);
        color: #ffffff;
        object-fit: cover;
        box-shadow: 0 4px 14px rgba(17, 87, 137, 0.2);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Promo Banner Toko (Opsi A) */
    .shopee-promo-banner {
        border-radius: 1.25rem;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px -2px rgba(17, 87, 137, 0.12);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    .promo-banner-card {
        background: linear-gradient(135deg, #0f4c75 0%, #115789 50%, #0284c7 100%);
        padding: 24px 28px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }
    .promo-banner-content {
        position: relative;
        z-index: 2;
        max-width: 650px;
    }
    .promo-banner-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(8px);
        color: #ffffff;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 4px 12px;
        border-radius: 9999px;
        margin-bottom: 10px;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }
    .promo-banner-title {
        font-size: 1.35rem;
        font-weight: 900;
        color: #ffffff;
        line-height: 1.3;
        margin-bottom: 6px;
        letter-spacing: -0.01em;
    }
    .promo-banner-subtitle {
        font-size: 0.825rem;
        color: #e0f2fe;
        line-height: 1.5;
        margin-bottom: 14px;
    }
    .promo-banner-features {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 18px;
    }
    .promo-feature-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        color: #ffffff;
    }
    .promo-banner-graphic {
        position: relative;
        display: none;
    }
    @media (min-width: 768px) {
        .promo-banner-graphic {
            display: block;
        }
    }
    .promo-banner-circle-1 {
        position: absolute;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        top: -60px;
        right: -40px;
    }
    .promo-banner-icon-wrap {
        width: 90px;
        height: 90px;
        border-radius: 1.5rem;
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }
    .promo-banner-custom-img {
        width: 100%;
        max-height: 220px;
        object-fit: cover;
        display: block;
    }

    .shopee-badge-mall {
        background: #fee2e2;
        color: #ef4444;
        border: 1px solid #fecaca;
        font-size: 10px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .shopee-btn-chat {
        background: #115789;
        color: white;
        font-weight: 700;
        font-size: 12px;
        padding: 8px 16px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(17, 87, 137, 0.2);
        border: none;
        cursor: pointer;
    }
    .shopee-btn-chat:hover {
        background: #0d466e;
        transform: translateY(-1px);
        color: white;
    }

    .shopee-btn-share {
        background: #ffffff;
        color: #475569;
        font-weight: 700;
        font-size: 12px;
        padding: 8px 14px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s;
        cursor: pointer;
    }
    .shopee-btn-share:hover {
        background: #f1f5f9;
        color: #1e293b;
        border-color: #94a3b8;
    }

    /* In-App Toko Chat Widget (Privasi Terjaga) */
    .toko-chat-widget {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 380px;
        max-width: calc(100vw - 32px);
        height: 540px;
        max-height: calc(100vh - 100px);
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 20px 40px -10px rgba(17, 87, 137, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.08);
        display: flex;
        flex-direction: column;
        z-index: 9999;
        overflow: hidden;
        transform: translateY(20px) scale(0.95);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .toko-chat-widget.active {
        transform: translateY(0) scale(1);
        opacity: 1;
        visibility: visible;
    }
    .toko-chat-header {
        background: linear-gradient(135deg, #115789 0%, #0284c7 100%);
        color: white;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    .toko-chat-privacy {
        background: #f0fdf4;
        border-bottom: 1px solid #dcfce7;
        padding: 6px 14px;
        font-size: 11px;
        color: #166534;
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 500;
        flex-shrink: 0;
    }
    .toko-chat-quick-replies {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding: 8px 12px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        scrollbar-width: none;
        flex-shrink: 0;
    }
    .toko-chat-quick-replies::-webkit-scrollbar {
        display: none;
    }
    .toko-chip-btn {
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 11px;
        color: #334155;
        font-weight: 600;
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .toko-chip-btn:hover {
        background: #e0f2fe;
        color: #0369a1;
        border-color: #7dd3fc;
    }
    .toko-chat-body {
        flex: 1;
        overflow-y: auto;
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: #f8fafc;
    }
    .toko-chat-bubble {
        max-width: 84%;
        padding: 10px 14px;
        border-radius: 16px;
        font-size: 13px;
        line-height: 1.45;
        position: relative;
        word-break: break-word;
    }
    .toko-chat-bubble.toko {
        background: #ffffff;
        color: #1e293b;
        align-self: flex-start;
        border-bottom-left-radius: 4px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .toko-chat-bubble.user {
        background: #115789;
        color: #ffffff;
        align-self: flex-end;
        border-bottom-right-radius: 4px;
        box-shadow: 0 2px 6px rgba(17, 87, 137, 0.25);
    }
    .toko-chat-time {
        font-size: 10px;
        margin-top: 4px;
        opacity: 0.75;
        text-align: right;
    }
    .toko-typing-indicator {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        width: fit-content;
        align-self: flex-start;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .toko-typing-dot {
        width: 5px;
        height: 5px;
        background: #94a3b8;
        border-radius: 50%;
        animation: tokoBounce 1.4s infinite ease-in-out both;
    }
    .toko-typing-dot:nth-child(1) { animation-delay: -0.32s; }
    .toko-typing-dot:nth-child(2) { animation-delay: -0.16s; }
    @keyframes tokoBounce {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1); }
    }
    .toko-chat-footer {
        padding: 10px 14px;
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .toko-chat-input {
        flex: 1;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        padding: 8px 16px;
        font-size: 13px;
        outline: none;
        transition: border-color 0.2s;
        background: #f8fafc;
    }
    .toko-chat-input:focus {
        border-color: #115789;
        background: #ffffff;
    }
    .toko-chat-send-btn {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #115789;
        color: white;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .toko-chat-send-btn:hover {
        background: #0d466e;
        transform: scale(1.05);
    }

    /* Right Card: Shopee Stats Grid */
    .shopee-right-stats {
        padding: 24px 28px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px 24px;
        align-content: center;
        background: transparent;
    }

    .shopee-stat-item {
        display: flex;
        align-items: center;
        gap: 12px;
        background: transparent;
        border: none;
        padding: 0;
    }

    .shopee-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #f0f9ff;
        border: 1px solid #e0f2fe;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #0284c7;
    }

    .shopee-stat-label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 2px;
        line-height: 1.2;
    }

    .shopee-stat-value {
        font-size: 13px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    /* Shopee Tabs Bar */
    .shopee-tabs-nav {
        display: flex;
        align-items: center;
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        margin-bottom: 24px;
        overflow-x: auto;
    }

    .shopee-tab-item {
        padding: 16px 28px;
        font-size: 14px;
        font-weight: 700;
        color: #64748b;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        background: none;
        border-top: none;
        border-left: none;
        border-right: none;
        transition: all 0.2s;
    }
    .shopee-tab-item:hover {
        color: #115789;
    }
    .shopee-tab-item.active {
        color: #115789;
        border-bottom-color: #115789;
    }

    /* Shopee In-Store Toolbar */
    .shopee-store-toolbar {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        padding: 14px 20px;
        margin-bottom: 24px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    @media (min-width: 992px) {
        .shopee-store-toolbar {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
    }

    .shopee-toolbar-categories {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 0;
        overflow-x: auto;
    }

    .shopee-categories-list {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .shopee-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    .shopee-sort-label {
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        margin-right: 4px;
        white-space: nowrap;
    }

    .shopee-sort-btn {
        padding: 7px 16px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 700;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s;
        text-decoration: none;
        display: inline-block;
        white-space: nowrap;
    }
    .shopee-sort-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .shopee-sort-btn.active {
        background: #115789;
        color: white;
        border-color: #115789;
        box-shadow: 0 2px 8px rgba(17, 87, 137, 0.25);
    }

    .shopee-select-sort {
        height: 40px;
        padding: 0 32px 0 14px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        cursor: pointer;
        outline: none;
        transition: all 0.2s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 10px center;
        background-repeat: no-repeat;
        background-size: 16px;
    }

    .shopee-select-sort:focus {
        border-color: #115789;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(17, 87, 137, 0.1);
    }

    .shopee-search-box {
        position: relative;
        width: 100%;
        min-width: 240px;
        max-width: 320px;
    }
    .shopee-search-box input {
        width: 100%;
        height: 40px;
        padding: 0 34px 0 38px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        color: #1e293b;
        outline: none;
        transition: all 0.2s;
    }
    .shopee-search-box input:focus {
        background: #ffffff;
        border-color: #115789;
        box-shadow: 0 0 0 3px rgba(17, 87, 137, 0.1);
    }
    .shopee-search-box svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        color: #94a3b8;
        pointer-events: none;
    }
    .shopee-search-clear {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        background: #cbd5e1;
        color: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        line-height: 1;
        text-decoration: none;
        transition: all 0.15s;
    }
    .shopee-search-clear:hover {
        background: #ef4444;
        color: #ffffff;
    }

    /* Shopee Review Card */
    .shopee-review-row {
        background: white;
        border-bottom: 1px solid #f8fafc;
        padding: 18px 0;
    }
    .shopee-review-row:last-child {
        border-bottom: none;
    }
    /* Store Bar */
    .ps-modal-store-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        margin-bottom: 1.25rem;
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
    }
</style>
@endpush

@section('page')
<div class="store-page-wrapper">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-4">
            <a href="{{ route('beranda') }}" class="hover:text-blue-600">Beranda</a>
            <span>/</span>
            <a href="{{ route('pasar.index') }}" class="hover:text-blue-600">Pasar Daerah</a>
            <span>/</span>
            <span class="text-slate-800 font-bold">Toko BUMDes {{ $cleanRegionName }}</span>
        </nav>

        <!-- ========================================================================= -->
        <!-- SHOPEE STYLE STORE HEADER CARD                                            -->
        <!-- ========================================================================= -->
        <div class="shopee-store-header">
            <div class="shopee-header-grid">
                
                <!-- Left Panel: Store Identity & Fast Actions -->
                <div class="shopee-left-profile">
                    <div>
                        <div class="shopee-avatar-wrapper mb-3">
                            @if($seller && $seller->avatar)
                                <img src="{{ Storage::url($seller->avatar) }}" alt="{{ $cleanRegionName }}" class="shopee-avatar">
                            @else
                                <div class="shopee-avatar flex items-center justify-center bg-blue-600 text-white font-black text-2xl">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                            @endif

                            <div>
                                <div class="flex items-center gap-2 mb-1.5">
                                    <h1 class="text-base sm:text-lg font-black text-slate-900 leading-tight">
                                        Toko BUMDes {{ $cleanRegionName }}
                                    </h1>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="shopee-badge-mall">
                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        Resmi Desa
                                    </span>
                                    <span class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                                        Online
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 pt-2">
                        <button type="button" onclick="openTokoChat()" class="shopee-btn-chat" title="Chat langsung dengan Toko BUMDes (Privasi Terjaga)">
                            <svg class="w-3.5 h-3.5 fill-none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            <span>Chat Toko</span>
                        </button>

                        <button type="button" onclick="navigator.clipboard.writeText(window.location.href); showToast('Link toko berhasil disalin!');" class="shopee-btn-share">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                            <span>Bagikan</span>
                        </button>
                    </div>
                </div>

                <!-- Right Panel: Shopee Store Stats Grid -->
                <div class="shopee-right-stats">
                    <div class="shopee-stat-item">
                        <div class="shopee-stat-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <div>
                            <div class="shopee-stat-label">Produk:</div>
                            <div class="shopee-stat-value text-blue-600">{{ $totalProducts }} Produk</div>
                        </div>
                    </div>

                    <div class="shopee-stat-item">
                        <div class="shopee-stat-icon text-amber-500">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                        <div>
                            <div class="shopee-stat-label">Penilaian Toko:</div>
                            @if($reviews->isNotEmpty())
                                <div class="shopee-stat-value text-amber-500">{{ number_format($averageRating, 1) }} ({{ $reviews->count() }} Ulasan)</div>
                            @else
                                <div class="shopee-stat-value text-slate-400 font-medium text-xs">Belum ada ulasan</div>
                            @endif
                        </div>
                    </div>

                    <div class="shopee-stat-item">
                        <div class="shopee-stat-icon text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <div class="shopee-stat-label">Total Terjual:</div>
                            <div class="shopee-stat-value text-emerald-600">{{ $totalSales }} Pesanan</div>
                        </div>
                    </div>

                    <div class="shopee-stat-item">
                        <div class="shopee-stat-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <div class="shopee-stat-label">Lokasi:</div>
                            <div class="shopee-stat-value">Desa {{ $cleanRegionName }}</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- PROMO BANNER TOKO (HANYA MUNCUL JIKA SUDAH DIUNGGAH ADMIN DESA)           -->
        <!-- ========================================================================= -->
        @if($seller && $seller->store_banner)
        <div class="shopee-promo-banner">
            <img src="{{ Storage::url($seller->store_banner) }}" alt="Banner Toko {{ $cleanRegionName }}" class="promo-banner-custom-img">
        </div>
        @endif

        <!-- ========================================================================= -->
        <!-- SHOPEE TABS BAR                                                           -->
        <!-- ========================================================================= -->
        <div x-data="{ tab: 'produk' }">
            <div class="shopee-tabs-nav">
                <button type="button" @click="tab = 'produk'" :class="tab === 'produk' ? 'active' : ''" class="shopee-tab-item" x-show="tab !== 'produk'" style="display: none;">
                    <span>&larr; Kembali ke Produk</span>
                </button>
                <button type="button" @click="tab = (tab === 'ulasan' ? 'produk' : 'ulasan')" :class="tab === 'ulasan' ? 'active' : ''" class="shopee-tab-item">
                    <span>Penilaian Toko ({{ $reviews->count() }})</span>
                </button>
                <button type="button" @click="tab = (tab === 'tentang' ? 'produk' : 'tentang')" :class="tab === 'tentang' ? 'active' : ''" class="shopee-tab-item">
                    <span>Profil Toko</span>
                </button>
            </div>

            <!-- ===================================================================== -->
            <!-- TAB 1: PRODUK TOKO (Ulfa UI Products Grid)                            -->
            <!-- ===================================================================== -->
            <div x-show="tab === 'produk'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <!-- Shopee In-Store Filter & Search Toolbar -->
                <div class="shopee-store-toolbar">
                    <!-- Left: Kategori Pills -->
                    <div class="shopee-toolbar-categories">
                        <span class="shopee-sort-label">Kategori:</span>
                        <div class="shopee-categories-list">
                            <a href="{{ route('pasar.toko', array_merge(['id' => $region->id], request()->except(['kategori', 'page']))) }}" class="shopee-sort-btn {{ !request('kategori') || request('kategori') == 'all' ? 'active' : '' }}">
                                Semua
                            </a>
                            @foreach($categories as $cat)
                                <a href="{{ route('pasar.toko', array_merge(['id' => $region->id, 'kategori' => $cat], request()->except(['kategori', 'page']))) }}" class="shopee-sort-btn {{ request('kategori') == $cat ? 'active' : '' }}">
                                    {{ $cat }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Right: Sorting & Search Bar -->
                    <div class="shopee-toolbar-actions">
                        <!-- Sort Dropdown -->
                        <div class="shopee-sort-dropdown">
                            <select onchange="window.location.href=this.value" class="shopee-select-sort" title="Urutkan Produk">
                                <option value="{{ route('pasar.toko', array_merge(['id' => $region->id], request()->except(['sort', 'page']))) }}" {{ !request('sort') || request('sort') == 'terbaru' ? 'selected' : '' }}>
                                    Urutkan: Terbaru
                                </option>
                                <option value="{{ route('pasar.toko', array_merge(['id' => $region->id, 'sort' => 'termurah'], request()->except(['sort', 'page']))) }}" {{ request('sort') == 'termurah' ? 'selected' : '' }}>
                                    Harga: Termurah
                                </option>
                                <option value="{{ route('pasar.toko', array_merge(['id' => $region->id, 'sort' => 'termahal'], request()->except(['sort', 'page']))) }}" {{ request('sort') == 'termahal' ? 'selected' : '' }}>
                                    Harga: Termahal
                                </option>
                            </select>
                        </div>

                        <!-- Search Box -->
                        <form action="{{ route('pasar.toko', $region->id) }}" method="GET" class="m-0">
                            @if(request('kategori'))
                                <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                            @endif
                            @if(request('sort'))
                                <input type="hidden" name="sort" value="{{ request('sort') }}">
                            @endif
                            <div class="shopee-search-box">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk di toko ini...">
                                @if(request('search'))
                                    <a href="{{ route('pasar.toko', array_merge(['id' => $region->id], request()->except(['search', 'page']))) }}" class="shopee-search-clear" title="Hapus pencarian">&times;</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Product Grid (Exact Ulfa UI) -->
                @if($produks->isEmpty())
                    <div class="bg-white rounded-2xl p-12 text-center shadow-sm max-w-xl mx-auto my-6" style="border: 1px solid #e2e8f0;">
                        <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-3" style="border: 1px solid #bae6fd;">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-800 mb-1">Belum Ada Produk</h3>
                        <p class="text-xs text-slate-500">Toko ini belum memiliki produk yang sesuai dengan filter Anda.</p>
                    </div>
                @else
                    <div class="products-grid" id="productsGrid">
                        @foreach($produks as $produk)
                        <div class="product-card" data-product-id="{{ $produk->id }}">
                            <div class="product-image-wrapper">
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
                                    <button type="button" class="product-action-btn" title="Lihat Detail" onclick="openOrderModal({{ $produk->id }}, '{{ addslashes($produk->nama_produk) }}', '{{ $produk->foto ? Storage::url($produk->foto) : '' }}', {{ $produk->harga }}, {{ $produk->stok }}, '{{ addslashes($produk->deskripsi ?? '') }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    
                                    @auth
                                        @if($produk->stok > 0)
                                            <button type="button" class="product-action-btn" title="Tambah ke Keranjang" onclick="openOrderModal({{ $produk->id }}, '{{ addslashes($produk->nama_produk) }}', '{{ $produk->foto ? Storage::url($produk->foto) : '' }}', {{ $produk->harga }}, {{ $produk->stok }}, '{{ addslashes($produk->deskripsi ?? '') }}')">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                                            </button>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name" style="font-size: 1.1rem; line-height: 1.4; margin-bottom: 8px; cursor: pointer;" onclick="openOrderModal({{ $produk->id }}, '{{ addslashes($produk->nama_produk) }}', '{{ $produk->foto ? Storage::url($produk->foto) : '' }}', {{ $produk->harga }}, {{ $produk->stok }}, '{{ addslashes($produk->deskripsi ?? '') }}')">{{ $produk->nama_produk }}</h3>
                                <p class="product-desc" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $produk->deskripsi ?? 'Produk khas daerah Bengkalis.' }}</p>
                                <div class="product-price-row">
                                    <span class="product-price">Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                                    
                                    @auth
                                        @if($produk->stok > 0)
                                            <button type="button" class="btn-add-cart" onclick="openOrderModal({{ $produk->id }}, '{{ addslashes($produk->nama_produk) }}', '{{ $produk->foto ? Storage::url($produk->foto) : '' }}', {{ $produk->harga }}, {{ $produk->stok }}, '{{ addslashes($produk->deskripsi ?? '') }}')">
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

                    <div class="mt-8">
                        {{ $produks->links() }}
                    </div>
                @endif
            </div>

            <!-- ===================================================================== -->
            <!-- TAB 2: ULASAN TOKO                                                    -->
            <!-- ===================================================================== -->
            <div x-show="tab === 'ulasan'" x-cloak style="display: none;" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm" style="border: 1px solid #e2e8f0;" x-data="{ starFilter: 'all' }">
                    @if($reviews->isEmpty())
                        <!-- Clean Empty State (Tanpa Data Suntikan Rating) -->
                        <div class="text-center py-12 px-4">
                            <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-3.5" style="border: 1px solid #e2e8f0;">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            </div>
                            <h4 class="text-base font-bold text-slate-700 mb-1">Belum Ada Penilaian Pembeli</h4>
                            <p class="text-xs text-slate-400 max-w-sm mx-auto mb-0 leading-relaxed">Toko BUMDes {{ $cleanRegionName }} belum memiliki ulasan pesanan. Jadilah pembeli pertama yang memberikan penilaian!</p>
                        </div>
                    @else
                        <!-- Shopee Rating Summary Box (Hanya Tampil Jika Ada Ulasan Asli) -->
                        <div class="bg-slate-50/70 p-6 sm:p-7 rounded-2xl flex flex-col md:flex-row items-center gap-6 sm:gap-8 mb-8" style="border: 1px solid #e2e8f0;">
                            <div class="text-center md:text-left md:pr-8 flex-shrink-0" style="border-right: 1px solid #e2e8f0;">
                                <div class="flex items-baseline justify-center md:justify-start gap-1 mb-1">
                                    <span class="text-4xl sm:text-5xl font-black text-amber-500 tracking-tight">{{ number_format($averageRating, 1) }}</span>
                                    <span class="text-base font-bold text-slate-400">/ 5.0</span>
                                </div>
                                <div class="flex items-center justify-center md:justify-start text-amber-400 gap-1 mb-1.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-5 h-5 {{ $i <= round($averageRating) ? 'fill-current' : 'text-slate-200 fill-current' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    @endfor
                                </div>
                                <p class="text-xs text-slate-500 mb-0 font-medium">Berdasarkan {{ $reviews->count() }} penilaian pembeli</p>
                            </div>

                            <!-- Interactive Filter Pills -->
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" @click="starFilter = 'all'" :class="starFilter === 'all' ? 'bg-[#115789] text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50'" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition" style="border: 1px solid #cbd5e1;">
                                    Semua ({{ $reviews->count() }})
                                </button>
                                @foreach([5, 4, 3, 2, 1] as $star)
                                    @php $c = $reviews->where('rating', $star)->count(); @endphp
                                    <button type="button" @click="starFilter = '{{ $star }}'" :class="starFilter === '{{ $star }}' ? 'bg-[#115789] text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition" style="border: 1px solid #cbd5e1;">
                                        {{ $star }} Bintang ({{ $c }})
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Reviews List -->
                        <div class="divide-y divide-slate-100">
                            @foreach($reviews as $rev)
                                <div class="shopee-review-row" x-show="starFilter === 'all' || starFilter === '{{ $rev->rating }}'" x-transition>
                                    <div class="flex items-start gap-3.5">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-[#115789] text-white font-black text-sm flex items-center justify-center flex-shrink-0 shadow-sm">
                                            {{ strtoupper(substr($rev->user->name ?? 'P', 0, 1)) }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex flex-wrap items-center justify-between gap-1 mb-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-xs sm:text-sm text-slate-900">{{ $rev->user->name ?? 'Pembeli' }}</span>
                                                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700" style="border: 1px solid #bbf7d0;">
                                                        <svg class="w-2.5 h-2.5 fill-current text-emerald-600" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                        Terverifikasi
                                                    </span>
                                                </div>
                                                <span class="text-[11px] text-slate-400">{{ $rev->created_at->diffForHumans() }}</span>
                                            </div>

                                            <div class="flex text-amber-400 mb-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-3.5 h-3.5 {{ $i <= $rev->rating ? 'fill-current' : 'text-slate-200 fill-current' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                @endfor
                                            </div>

                                            @if($rev->produk)
                                                <div class="inline-flex items-center gap-1 text-[11px] text-slate-500 bg-slate-50 px-2.5 py-1 rounded-md mb-2.5" style="border: 1px solid #e2e8f0;">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                                    <span>Produk: </span>
                                                    <a href="{{ route('pasar.show', $rev->produk->id) }}" class="text-[#115789] font-bold hover:underline">
                                                        {{ $rev->produk->nama_produk }}
                                                    </a>
                                                </div>
                                            @endif

                                            <p class="text-xs sm:text-sm text-slate-700 leading-relaxed mb-0">
                                                {{ $rev->comment ?? 'Pembeli puas dengan produk ini.' }}
                                            </p>

                                            @if($rev->reply)
                                            <div class="mt-3 p-3.5 rounded-xl bg-slate-50 text-xs" style="border-left: 4px solid #115789; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                                                <div class="font-bold text-[#115789] flex items-center gap-1.5 mb-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                                    <span>Respon Toko BUMDes {{ $cleanRegionName }}:</span>
                                                </div>
                                                <p class="text-slate-600 mb-0 leading-normal">{{ $rev->reply }}</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- ===================================================================== -->
            <!-- TAB 3: PROFIL / TENTANG TOKO                                          -->
            <!-- ===================================================================== -->
            <div x-show="tab === 'tentang'" x-cloak style="display: none;" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm" style="border: 1px solid #e2e8f0;">
                    <div class="text-slate-700 text-sm leading-relaxed whitespace-pre-line">
                        @if($seller && !empty($seller->store_description))
                            {!! nl2br(e($seller->store_description)) !!}
                        @else
                            <p class="text-slate-400 italic text-sm mb-0">
                                Belum ada deskripsi toko yang ditambahkan oleh admin desa.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- INTERACTIVE ORDER MODAL (POPUP ATUR JUMLAH & BELI LANGSUNG)               -->
    <!-- ========================================================================= -->
    <div class="ps-modal-overlay" id="orderModalOverlay">
        <div class="ps-modal-container">
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
                <div class="ps-modal-store-bar" style="cursor: default;">
                    <div class="ps-modal-store-left">
                        <div class="ps-modal-store-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="ps-modal-store-label">Toko Penjual</span>
                            <span class="ps-modal-store-name">Toko BUMDes {{ $cleanRegionName }}</span>
                        </div>
                    </div>
                </div>

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

    <!-- ========================================================================= -->
    <!-- IN-APP TOKO CHAT WIDGET & MODAL (PRIVASI TERJAGA - TANPA WA)             -->
    <!-- ========================================================================= -->
    <div class="toko-chat-widget" id="tokoChatWidget">
        <!-- Header -->
        <div class="toko-chat-header">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="relative">
                    @if($seller && $seller->avatar)
                        <img src="{{ Storage::url($seller->avatar) }}" alt="{{ $cleanRegionName }}" class="w-9 h-9 rounded-full object-cover border-2 border-white/80 shadow-sm">
                    @else
                        <div class="w-9 h-9 rounded-full bg-white/20 text-white font-bold flex items-center justify-center border-2 border-white/80 shadow-sm text-sm">
                            🏬
                        </div>
                    @endif
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-400 border-2 border-white rounded-full"></span>
                </div>
                <div class="min-w-0">
                    <h4 class="font-bold text-white text-sm leading-tight truncate">Toko BUMDes {{ $cleanRegionName }}</h4>
                    <div class="flex items-center gap-1 text-[11px] text-blue-100 font-medium">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>Resmi BUMDes &bull; Online</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button type="button" onclick="closeTokoChat()" class="text-white/80 hover:text-white hover:bg-white/10 p-1.5 rounded-lg transition" title="Tutup Chat">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Privacy Shield Notice -->
        <div class="toko-chat-privacy">
            <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            <span>Privasi Terjaga: Nomor HP & identitas aman dalam sistem.</span>
        </div>

        <!-- Quick Reply Chips -->
        <div class="toko-chat-quick-replies" id="tokoQuickReplies">
            <button type="button" class="toko-chip-btn" onclick="sendTokoQuickReply('Halo, apakah stok produk masih ready?')">📦 Stok ready?</button>
            <button type="button" class="toko-chip-btn" onclick="sendTokoQuickReply('Bisa dikirim ke desa atau kecamatan saya?')">🚚 Kirim antar-desa?</button>
            <button type="button" class="toko-chip-btn" onclick="sendTokoQuickReply('Berapa estimasi biaya ongkir?')">💰 Tarif ongkir?</button>
            <button type="button" class="toko-chip-btn" onclick="sendTokoQuickReply('Bisa bayar COD saat barang sampai?')">💵 Bayar COD?</button>
        </div>

        <!-- Chat Stream Body -->
        <div class="toko-chat-body" id="tokoChatMessages">
            <!-- Greeting Bubble from Toko -->
            <div class="toko-chat-bubble toko">
                <div>Halo{{ Auth::check() ? ' Kak ' . Auth::user()->name : ' Kak' }}! Selamat datang di layanan chat resmi <strong>Toko BUMDes {{ $cleanRegionName }}</strong>. Ada yang bisa kami bantu seputar produk atau pengiriman pesanan Anda?</div>
                <div class="toko-chat-time">{{ date('H:i') }}</div>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div id="tokoTypingIndicator" class="px-3 pb-1" style="display: none;">
            <div class="toko-typing-indicator">
                <span class="text-[11px] text-slate-500 font-medium mr-1">Admin Toko mengetik</span>
                <div class="toko-typing-dot"></div>
                <div class="toko-typing-dot"></div>
                <div class="toko-typing-dot"></div>
            </div>
        </div>

        <!-- Footer / Input -->
        <div class="toko-chat-footer">
            <input type="text" id="tokoChatInput" class="toko-chat-input" placeholder="Tulis pesan ke Toko BUMDes..." autocomplete="off" onkeypress="if(event.key==='Enter') sendTokoMessage()">
            <button type="button" onclick="sendTokoMessage()" class="toko-chat-send-btn" title="Kirim Pesan">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
            </button>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast" style="z-index: 9999;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <span id="toastMessage">Produk ditambahkan ke keranjang!</span>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Order Modal Styles */
    .ps-modal-overlay {
        position: fixed; inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: flex; align-items: center; justify-content: center;
        z-index: 99999;
        opacity: 0; pointer-events: none;
        transition: opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        padding: 1rem;
    }
    .ps-modal-overlay.active {
        opacity: 1; pointer-events: auto;
    }
    .ps-modal-container {
        background: #ffffff;
        width: 100%; max-width: 440px;
        border-radius: 1.5rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        transform: scale(0.92) translateY(10px);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.8);
    }
    .ps-modal-overlay.active .ps-modal-container {
        transform: scale(1) translateY(0);
    }
    .ps-modal-header {
        padding: 1.25rem 1.5rem;
        display: flex; align-items: center; justify-content: space-between;
        border-bottom: 1px solid #f1f5f9;
        background: #fafcff;
    }
    .ps-modal-close-btn {
        width: 32px; height: 32px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: #f1f5f9; color: #64748b;
        border: none; cursor: pointer;
        transition: all 0.2s;
    }
    .ps-modal-close-btn:hover {
        background: #e2e8f0; color: #0f172a; transform: rotate(90deg);
    }
    .ps-modal-body { padding: 1.5rem; }
    .ps-modal-product-card {
        display: flex; gap: 1rem;
        align-items: center;
        padding: 0.875rem;
        background: #f8fafc;
        border-radius: 1rem;
        border: 1px solid #f1f5f9;
        margin-bottom: 1.25rem;
    }
    .ps-modal-product-img-wrap {
        width: 70px; height: 70px;
        border-radius: 0.75rem;
        overflow: hidden;
        background: #e2e8f0;
        flex-shrink: 0;
    }
    .ps-modal-product-img-wrap img {
        width: 100%; height: 100%; object-fit: cover;
    }
    .ps-modal-qty-box {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 1rem;
        padding: 0.875rem 1.25rem;
        margin-bottom: 1.25rem;
    }
    .ps-modal-stepper {
        display: flex; align-items: center; gap: 0.5rem;
        background: #f1f5f9; padding: 3px;
        border-radius: 0.75rem;
    }
    .ps-modal-step-btn {
        width: 28px; height: 28px;
        border-radius: 0.5rem;
        border: none; background: #ffffff;
        color: #1e293b; font-weight: bold; font-size: 1rem;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: all 0.15s;
    }
    .ps-modal-step-btn:hover { background: #0284c7; color: white; }
    .ps-modal-step-val {
        min-width: 24px; text-align: center;
        font-weight: 800; font-size: 0.875rem; color: #0f172a;
    }
    .ps-modal-calc-card {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        display: flex; flex-direction: column; gap: 0.5rem;
    }
    .ps-modal-actions {
        display: flex; gap: 0.6rem; align-items: center;
    }
    .ps-modal-btn-cancel {
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        border: 1.5px solid #e2e8f0;
        background: #ffffff; color: #64748b;
        font-weight: 700; font-size: 0.8125rem;
        cursor: pointer; transition: all 0.2s;
    }
    .ps-modal-btn-cancel:hover {
        background: #f8fafc; color: #1e293b; border-color: #cbd5e1;
    }
    .ps-modal-btn-cart {
        flex: 1; padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        border: 1.5px solid #0284c7;
        background: #f0f9ff; color: #0284c7;
        font-weight: 700; font-size: 0.8125rem;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        gap: 0.35rem; transition: all 0.2s;
    }
    .ps-modal-btn-cart:hover {
        background: #e0f2fe; border-color: #0369a1; color: #0369a1; transform: translateY(-1px);
    }
    .ps-modal-btn-buy {
        flex: 1.15; padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        border: none;
        background: linear-gradient(135deg, #115789 0%, #0284c7 100%);
        color: #ffffff; font-weight: 700; font-size: 0.8125rem;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        gap: 0.35rem; box-shadow: 0 4px 14px rgba(17, 87, 137, 0.3);
        transition: all 0.25s;
    }
    .ps-modal-btn-buy:hover {
        transform: translateY(-2px); box-shadow: 0 6px 20px rgba(17, 87, 137, 0.4);
    }
    .ps-modal-btn-buy:active { transform: translateY(0); }
</style>
@endpush

@push('scripts')
<script>
    // Listen to navbar state changes to automatically adapt top spacing
    (() => {
        const updateNavSpacing = () => {
            const navbar = document.getElementById('master-navbar');
            if (navbar && navbar.classList.contains('hidden-nav')) {
                document.body.classList.add('navbar-is-hidden');
            } else {
                document.body.classList.remove('navbar-is-hidden');
            }
        };

        updateNavSpacing();

        const navbar = document.getElementById('master-navbar');
        if (navbar) {
            const observer = new MutationObserver(updateNavSpacing);
            observer.observe(navbar, { attributes: true, attributeFilter: ['class'] });
        }
    })();

    function showToast(msg) {
        const toast = document.getElementById('toast');
        const msgEl = document.getElementById('toastMessage');
        if (toast && msgEl) {
            msgEl.textContent = msg;
            toast.classList.add('show');
            setTimeout(() => { toast.classList.remove('show'); }, 3000);
        }
    }

    // Modal state
    let currentModalProduct = null;
    let modalQty = 1;

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    }

    function openOrderModal(id, name, img, price, stock, desc) {
        currentModalProduct = { id, name, img, price, stock, desc };
        modalQty = 1;

        document.getElementById('modalProductName').textContent = name;
        document.getElementById('modalProductDesc').textContent = desc || 'Produk daerah berkualitas.';
        document.getElementById('modalProductPrice').textContent = formatRupiah(price);
        
        const imgEl = document.getElementById('modalProductImage');
        if (img) {
            imgEl.src = img;
            imgEl.style.display = 'block';
        } else {
            imgEl.style.display = 'none';
        }

        updateModalCalc();
        document.getElementById('orderModalOverlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeOrderModal() {
        document.getElementById('orderModalOverlay').classList.remove('active');
        document.body.style.overflow = '';
        currentModalProduct = null;
    }

    function changeModalQty(delta) {
        modalQty += delta;
        if (modalQty < 1) modalQty = 1;
        if (currentModalProduct && currentModalProduct.stock && modalQty > currentModalProduct.stock) {
            modalQty = currentModalProduct.stock;
        }
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

    // =========================================================================
    // TOKO CHAT IN-APP SYSTEM (PRIVASI TERJAGA - TANPA WA)
    // =========================================================================
    function openTokoChat() {
        const widget = document.getElementById('tokoChatWidget');
        if (widget) {
            widget.classList.add('active');
            setTimeout(() => {
                document.getElementById('tokoChatInput')?.focus();
                scrollTokoChatToBottom();
            }, 100);
        }
    }

    function closeTokoChat() {
        const widget = document.getElementById('tokoChatWidget');
        if (widget) {
            widget.classList.remove('active');
        }
    }

    function scrollTokoChatToBottom() {
        const container = document.getElementById('tokoChatMessages');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    function getCurrentTimeStr() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        return `${h}:${m}`;
    }

    function sendTokoQuickReply(text) {
        const input = document.getElementById('tokoChatInput');
        if (input) {
            input.value = text;
            sendTokoMessage();
        }
    }

    function sendTokoMessage() {
        const input = document.getElementById('tokoChatInput');
        const text = input ? input.value.trim() : '';
        if (!text) return;

        input.value = '';
        const timeStr = getCurrentTimeStr();
        const container = document.getElementById('tokoChatMessages');

        // Add user bubble
        const userBubble = document.createElement('div');
        userBubble.className = 'toko-chat-bubble user';
        userBubble.innerHTML = `
            <div>${escapeHtml(text)}</div>
            <div class="toko-chat-time">${timeStr}</div>
        `;
        container.appendChild(userBubble);
        scrollTokoChatToBottom();

        // Simulate Toko Reply
        simulateTokoReply(text);
    }

    function simulateTokoReply(userQuery) {
        const typingEl = document.getElementById('tokoTypingIndicator');
        if (typingEl) typingEl.style.display = 'block';
        scrollTokoChatToBottom();

        setTimeout(() => {
            if (typingEl) typingEl.style.display = 'none';

            let replyText = 'Baik Kak! ';
            const q = userQuery.toLowerCase();

            if (q.includes('stok') || q.includes('ready') || q.includes('ada')) {
                replyText += 'Stok produk di Toko BUMDes {{ $cleanRegionName }} selalu terpantau ready dan siap segera dikemas.';
            } else if (q.includes('kirim') || q.includes('antar') || q.includes('desa') || q.includes('kecamatan')) {
                replyText += 'Tentu bisa! Kami melayani pengiriman kurir lokal antar-desa dan antar-kecamatan se-Kabupaten Bengkalis.';
            } else if (q.includes('ongkir') || q.includes('biaya') || q.includes('tarif')) {
                replyText += 'Ongkir dalam satu desa flat Rp 5.000 (bahkan gratis promo tertentu). Pengiriman antar-desa sekitar Rp 10.000.';
            } else if (q.includes('cod') || q.includes('bayar') || q.includes('transfer') || q.includes('qris')) {
                replyText += 'Bisa bayar COD tunai saat kurir tiba, atau lewat QRIS dan Transfer Bank Virtual Account saat checkout.';
            } else if (q.includes('retur') || q.includes('rusak') || q.includes('garansi') || q.includes('komplain')) {
                replyText += 'Jika produk tidak sesuai atau terdapat kerusakan saat diterima, Kakak bisa langsung mengajukan komplain & retur di menu riwayat transaksi. Kami menjamin penggantian barang baru atau pengembalian dana 100%.';
            } else if (q.includes('lokasi') || q.includes('alamat') || q.includes('ambil')) {
                replyText += 'Kantor Toko BUMDes kami berlokasi di Desa {{ $cleanRegionName }}. Kakak juga bisa memilih opsi "Ambil Sendiri" saat checkout gratis ongkir.';
            } else {
                replyText += 'Terima kasih sudah menghubungi Toko BUMDes {{ $cleanRegionName }}. Pertanyaan atau pesanan Kakak siap kami layani dengan senang hati!';
            }

            const timeStr = getCurrentTimeStr();
            const container = document.getElementById('tokoChatMessages');
            const tokoBubble = document.createElement('div');
            tokoBubble.className = 'toko-chat-bubble toko';
            tokoBubble.innerHTML = `
                <div>${escapeHtml(replyText)}</div>
                <div class="toko-chat-time">${timeStr}</div>
            `;
            container.appendChild(tokoBubble);
            scrollTokoChatToBottom();
        }, 1100);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
@endpush
