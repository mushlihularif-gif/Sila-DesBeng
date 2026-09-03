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

    /* ===== DETAIL PAGE RESPONSIVE NAVBAR WRAPPER ===== */
    .detail-page-wrapper {
        padding-top: 155px;
        transition: padding-top 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    body.navbar-is-hidden .detail-page-wrapper {
        padding-top: 55px;
    }

    /* ===== ACTION CARD ===== */
    .ps-action-card {
        border: 1px solid #e2e8f0;
        border-radius: 1.25rem;
        padding: 1.5rem;
        background: #ffffff;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.07);
    }
    .sticky-card {
        top: 145px;
        transition: top 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    body.navbar-is-hidden .sticky-card {
        top: 25px;
    }

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

    /* Soft Tab UI Cards */
    .ps-empty-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1.25rem;
        padding: 2.5rem 1.5rem;
        text-align: center;
    }
    .ps-rating-banner {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1.25rem;
        padding: 1.5rem;
    }
    .ps-review-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1.25rem;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    .ps-admin-reply-card {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 0.875rem;
        padding: 1rem 1.25rem;
    }
    .ps-info-card-blue {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 1.25rem;
        padding: 1.25rem 1.5rem;
    }
    .ps-info-card-green {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 1.25rem;
        padding: 1.25rem 1.5rem;
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

    /* ===== MAIN DETAIL CARD ===== */
    .ps-main-detail-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        overflow: visible;
        margin-bottom: 32px;
        box-sizing: border-box;
    }

    /* ===== DISCOVERY HUB & CATEGORY SCREEN ===== */
    .discovery-hub-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        padding: 28px;
        margin-top: 32px;
        margin-bottom: 32px;
        width: 100%;
        box-sizing: border-box;
    }
    .hub-header-flex {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding-bottom: 20px;
        border-bottom: 1px solid #f1f5f9;
    }
    @media (min-width: 640px) {
        .hub-header-flex {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }
    .category-hub-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 16px;
        margin-top: 24px;
    }
    @media (min-width: 540px) {
        .category-hub-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .category-hub-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
    }
    .category-hub-item {
        background: #f8fafc;
        border: 1.5px solid #f1f5f9;
        border-radius: 18px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-decoration: none;
        transition: all 0.25s ease;
        min-height: 180px;
        box-sizing: border-box;
    }
    .category-hub-item:hover {
        background: #ffffff;
        border-color: #0284c7;
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(2, 132, 199, 0.12);
    }
    .category-hub-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-bottom: 14px;
    }
    .category-hub-title {
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
        line-height: 1.3;
    }
    .category-hub-desc {
        font-size: 12px;
        color: #64748b;
        line-height: 1.4;
        margin-bottom: 14px;
    }
    .category-hub-link {
        font-size: 12px;
        font-weight: 700;
        color: #0284c7;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .category-hub-item:hover .category-hub-link {
        color: #0369a1;
        gap: 7px;
    }

    /* Banner Styles */
    .hub-banner-gradient {
        background: linear-gradient(135deg, #0d466e 0%, #115789 50%, #0284c7 100%);
        border-radius: 24px;
        padding: 36px 32px;
        color: white;
        position: relative;
        overflow: hidden;
        margin-top: 28px;
        box-shadow: 0 14px 36px rgba(17, 87, 137, 0.25);
        width: 100%;
        box-sizing: border-box;
    }
    .hub-banner-glow {
        position: absolute;
        top: -60px;
        right: -60px;
        width: 280px;
        height: 280px;
        background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .hub-banner-inner {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        gap: 32px;
    }
    @media (min-width: 992px) {
        .hub-banner-inner {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
        .hub-banner-left {
            flex: 1.3;
            min-width: 0;
        }
        .hub-banner-right {
            flex: 1;
            min-width: 320px;
        }
    }
    .hub-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        background: rgba(255, 255, 255, 0.18);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        margin-bottom: 16px;
    }
    .hub-banner-title {
        font-size: 24px;
        font-weight: 900;
        color: #ffffff;
        line-height: 1.3;
        margin-bottom: 12px;
    }
    @media (min-width: 640px) {
        .hub-banner-title {
            font-size: 28px;
        }
    }
    .hub-banner-text {
        font-size: 13.5px;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 24px;
        max-width: 580px;
    }
    .hub-banner-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    .hub-btn-white {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 14px;
        background: #ffffff;
        color: #115789;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(0,0,0,0.12);
        transition: all 0.2s ease;
    }
    .hub-btn-white:hover {
        background: #f0f9ff;
        color: #0c446c;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.18);
    }
    .hub-btn-glass {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 22px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid rgba(255, 255, 255, 0.35);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        transition: all 0.2s ease;
    }
    .hub-btn-glass:hover {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
        transform: translateY(-2px);
    }
    .hub-benefit-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .hub-benefit-item {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 16px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        transition: all 0.2s ease;
    }
    .hub-benefit-item:hover {
        background: rgba(255, 255, 255, 0.18);
        transform: translateY(-2px);
    }
    .hub-benefit-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .hub-benefit-title {
        font-size: 13px;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 2px;
    }
    .hub-benefit-sub {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.85);
        line-height: 1.35;
    }
</style>
<link rel="stylesheet" href="{{ asset('css/pasar-daerah.css') }}">
@endpush

@section('page')
<main id="main-content" class="flex-grow bg-gray-50/50 pb-16 detail-container detail-page-wrapper">
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

        <div class="ps-main-detail-card">
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

@php
    $cleanRegionName = preg_replace('/^Desa\s+/i', '', $produk->region->name ?? 'Bengkalis');
@endphp

                            <!-- Badges on Image -->
                            <div class="absolute top-3 left-3 flex flex-col gap-2" style="z-index:2;">
                                @if($produk->stok > 0 && $produk->stok <= 5)
                                    <span class="ps-badge-stok-terbatas">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.27 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                                        Stok Terbatas
                                    </span>
                                @endif
                                @if(isset($soldCount) && $soldCount >= 5)
                                <span class="ps-badge-laris">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"></path></svg>
                                    Laris
                                </span>
                                @endif
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

                        <!-- Stats Row (Rating & Terjual) -->
                        <div class="flex items-center gap-3 text-sm text-gray-500 mb-5">
                            @if($reviews->isNotEmpty())
                            <div class="flex items-center gap-1 font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-200/60 text-xs">
                                <span>â­</span>
                                <span>{{ number_format($averageRating, 1) }}</span>
                                <span class="text-gray-400 font-normal">({{ $reviews->count() }} ulasan)</span>
                            </div>
                            <span class="text-gray-300">â€¢</span>
                            @endif
                            <div class="flex items-center text-xs">
                                <span class="text-slate-400 mr-1.5">Terjual</span>
                                <span class="font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded">{{ $soldCount ?? 0 }}</span>
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

                        <!-- Toko / BUMDes Profile Box (Clickable directly to store) -->
                        @if($produk->region_id)
                        <a href="{{ route('pasar.toko', $produk->region_id) }}" class="mb-5 p-4 rounded-xl bg-gradient-to-r from-blue-50/50 via-slate-50/30 to-white relative overflow-hidden flex items-center justify-between gap-3 hover:border-blue-300 hover:shadow-sm transition-all duration-200 group block" style="border: 1px solid #e2e8f0; text-decoration:none;" title="Masuk ke Toko BUMDes {{ $cleanRegionName }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-12 h-12 rounded-xl bg-white shadow-sm overflow-hidden flex-shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform" style="border: 1px solid #bae6fd;">
                                    @if($seller && $seller->avatar)
                                        <img src="{{ Storage::url($seller->avatar) }}" alt="{{ $cleanRegionName }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-6 h-6 text-[#115789]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <h4 class="font-bold text-gray-900 text-sm truncate group-hover:text-[#115789] transition">Toko BUMDes {{ $cleanRegionName }}</h4>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">Resmi</span>
                                    </div>
                                    <p class="text-xs text-gray-500 truncate mt-0.5">{{ $seller->store_description ?? 'Unit Usaha Resmi BUMDes ' . $cleanRegionName }}</p>
                                </div>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-white/80 flex items-center justify-center text-gray-400 group-hover:text-[#115789] group-hover:border-[#115789]/30 group-hover:translate-x-0.5 transition-all flex-shrink-0 shadow-2xs" style="border: 1px solid #e2e8f0;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </a>
                        @else
                        <div class="mb-5 p-4 rounded-xl bg-gradient-to-r from-blue-50/50 via-slate-50/30 to-white relative overflow-hidden flex items-center justify-between gap-3" style="border: 1px solid #e2e8f0;">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-12 h-12 rounded-xl bg-white shadow-sm overflow-hidden flex-shrink-0 flex items-center justify-center" style="border: 1px solid #bae6fd;">
                                    <svg class="w-6 h-6 text-[#115789]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <h4 class="font-bold text-gray-900 text-sm truncate">Toko BUMDes {{ $cleanRegionName }}</h4>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">Resmi</span>
                                    </div>
                                    <p class="text-xs text-gray-500 truncate mt-0.5">Unit Usaha Resmi BUMDes {{ $cleanRegionName }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Share Buttons -->
                        <div>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-2">Bagikan</p>
                            <div class="ps-share-group">
                                <button type="button" onclick="copyLink()" class="ps-share-btn copy-link">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                    Salin Link
                                </button>
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

                <!-- ========================================================================= -->
                <!-- FULL-WIDTH TABBED SECTION (LEGA & TIDAK SESAK)                            -->
                <!-- ========================================================================= -->
                <div class="mt-10 pt-8 border-t border-gray-100">
                    <div x-data="{ tab: 'detail', showReviewForm: false, rating: 5 }">
                        <!-- Tab Headers -->
                        <div class="flex border-b border-gray-200 mb-6 overflow-x-auto gap-2" style="-ms-overflow-style:none;scrollbar-width:none;">
                            <button @click="tab = 'detail'" 
                                :class="tab === 'detail' ? 'active-tab' : ''" 
                                class="ps-tab-btn text-sm sm:text-base pb-3">
                                Detail Produk
                            </button>
                            <button @click="tab = 'ulasan'" 
                                :class="tab === 'ulasan' ? 'active-tab' : ''" 
                                class="ps-tab-btn text-sm sm:text-base pb-3 flex items-center gap-1.5">
                                <span>â­ Ulasan Pembeli</span>
                                <span class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-800 font-bold">{{ $reviews->count() }}</span>
                            </button>
                            <button @click="tab = 'info'" 
                                :class="tab === 'info' ? 'active-tab' : ''" 
                                class="ps-tab-btn text-sm sm:text-base pb-3">
                                Info Penting
                            </button>
                        </div>
                        
                        <!-- Tab 1: Detail Produk -->
                        <div x-show="tab === 'detail'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="prose prose-sm md:prose-base prose-blue max-w-none text-gray-700">
                            <div class="leading-relaxed whitespace-pre-line text-[15px]">{!! e($produk->deskripsi ?? 'Tidak ada deskripsi lengkap untuk produk ini.') !!}</div>
                        </div>

                        <!-- Tab 2: Ulasan & Rating -->
                        <div x-show="tab === 'ulasan'" x-cloak style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                            @if($reviews->isEmpty())
                                <div class="ps-empty-box max-w-2xl mx-auto mb-6">
                                    <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    </div>
                                    <h4 class="text-base font-bold text-slate-800 mb-1">Belum Ada Ulasan Pembeli</h4>
                                    <p class="text-xs sm:text-sm text-slate-500 max-w-sm mx-auto mb-4">Jadilah pembeli pertama yang memberikan penilaian dan ulasan untuk produk ini!</p>
                                    
                                    @auth
                                        <button @click="showReviewForm = !showReviewForm" type="button" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#115789] hover:bg-[#0c446c] text-white font-bold text-xs sm:text-sm shadow-md transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            <span x-text="showReviewForm ? 'Tutup Form' : 'Tulis Ulasan'">Tulis Ulasan</span>
                                        </button>
                                    @else
                                        <a href="{{ route('auth.login') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#115789] text-white font-bold text-xs sm:text-sm shadow-md transition">
                                            Login untuk Beri Ulasan
                                        </a>
                                    @endauth
                                </div>
                            @else
                                <!-- Summary & Tulis Ulasan Button -->
                                <div class="ps-rating-banner flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                                    <div class="flex items-center gap-4">
                                        <div class="text-4xl font-black text-amber-500">{{ number_format($averageRating, 1) }}</div>
                                        <div>
                                            <div class="flex items-center text-amber-400 mb-0.5">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-5 h-5 {{ $i <= round($averageRating) ? 'fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                @endfor
                                            </div>
                                            <p class="text-xs text-gray-500 font-medium">{{ $reviews->count() }} ulasan kepuasan dari pembeli</p>
                                        </div>
                                    </div>

                                    @auth
                                        <button @click="showReviewForm = !showReviewForm" type="button" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#115789] hover:bg-[#0c446c] text-white font-bold text-xs sm:text-sm shadow-sm transition self-start sm:self-auto">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            <span x-text="showReviewForm ? 'Tutup Form' : 'Tulis Ulasan'">Tulis Ulasan</span>
                                        </button>
                                    @else
                                        <a href="{{ route('auth.login') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#115789] text-white font-bold text-xs sm:text-sm shadow-sm transition">
                                            Login untuk Beri Ulasan
                                        </a>
                                    @endauth
                                </div>
                            @endif

                            @auth
                                <!-- Form Tulis Ulasan -->
                                <div x-show="showReviewForm" x-collapse x-cloak class="ps-info-card-blue mb-8 max-w-2xl">
                                    <h4 class="font-bold text-gray-800 text-sm mb-3">Bagikan Pengalaman Anda</h4>
                                    <form action="{{ route('pasar.review.store', $produk->id) }}" method="POST">
                                        @csrf
                                        <!-- Rating Stars Selector -->
                                        <div class="mb-4">
                                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rating Bintang</label>
                                            <div class="flex items-center gap-2">
                                                <template x-for="star in [1,2,3,4,5]">
                                                    <button type="button" @click="rating = star" class="p-1 focus:outline-none transition">
                                                        <svg class="w-7 h-7 transition" :class="star <= rating ? 'text-amber-400 fill-current scale-110' : 'text-gray-300 fill-current'" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                    </button>
                                                </template>
                                                <input type="hidden" name="rating" :value="rating">
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Komentar / Ulasan</label>
                                            <textarea name="comment" rows="3" required class="w-full text-sm rounded-xl p-3 focus:ring-2 focus:ring-amber-400 bg-white" style="border: 1px solid #cbd5e1;" placeholder="Ceritakan kepuasan Anda terhadap produk ini..."></textarea>
                                        </div>

                                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#115789] hover:bg-[#0d466e] text-white font-bold text-xs sm:text-sm shadow-sm transition">
                                            Kirim Ulasan
                                        </button>
                                    </form>
                                </div>
                            @endauth

                            @if($reviews->isNotEmpty())
                                <!-- Daftar Ulasan -->
                                <div class="space-y-4">
                                    @foreach($reviews as $rev)
                                        <div class="ps-review-card">
                                            <div class="flex items-start justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-full bg-blue-100 text-[#115789] font-bold text-xs flex items-center justify-center">
                                                        {{ strtoupper(substr($rev->user->name ?? 'W', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <h5 class="font-bold text-gray-900 text-sm">{{ $rev->user->name ?? 'Warga' }}</h5>
                                                        <p class="text-[11px] text-gray-400">{{ $rev->created_at->diffForHumans() }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex text-amber-400">
                                                    @for($s = 1; $s <= 5; $s++)
                                                        <svg class="w-4 h-4 {{ $s <= $rev->rating ? 'fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                    @endfor
                                                </div>
                                            </div>

                                            @if($rev->comment)
                                                <p class="text-sm text-gray-700 mt-3 leading-relaxed">{{ $rev->comment }}</p>
                                            @endif

                                            <!-- Balasan Admin Desa -->
                                            @if($rev->reply)
                                                <div class="ps-admin-reply-card mt-4">
                                                    <div class="flex items-center gap-1.5 mb-1">
                                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                                        <span class="font-bold text-emerald-800 text-xs">Respon Penjual (Admin Desa)</span>
                                                        @if($rev->replied_at)
                                                            <span class="text-[10px] text-emerald-600 ml-auto">{{ $rev->replied_at->diffForHumans() }}</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-xs sm:text-sm text-emerald-900 leading-relaxed">{{ $rev->reply }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Tab 3: Info Penting -->
                        <div x-show="tab === 'info'" x-cloak style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl">
                                <div class="ps-info-card-blue flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-[#115789] flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900 mb-1">Proses & Pengiriman Cepat</h4>
                                        <p class="text-xs text-gray-600 leading-relaxed">Karena penjual berada di desa yang sama, pesanan umumnya diproses dan sampai dalam hitungan jam di hari yang sama.</p>
                                    </div>
                                </div>
                                <div class="ps-info-card-green flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900 mb-1">Garansi & Retur Mudah</h4>
                                        <p class="text-xs text-gray-600 leading-relaxed">Barang tidak sesuai? Hubungi penjual langsung di desa Anda untuk proses penukaran atau pengembalian yang lebih fleksibel.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- SECTION: PRODUK TERPOPULER & JELAJAHI PASAR DAERAH                        -->
        <!-- ========================================================================= -->
        
        @if(isset($popularProducts) && $popularProducts->isNotEmpty())
        <div class="mt-10 mb-8">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-200/80">
                <div class="flex items-center gap-3">
                    <div style="width: 42px; height: 42px; border-radius: 14px; background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35); flex-shrink: 0;">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-tight">Produk Terpopuler</h2>
                        <p class="text-xs sm:text-sm text-slate-500 font-medium">Pilihan produk unggulan khas daerah Kabupaten Bengkalis</p>
                    </div>
                </div>
                
                <a href="{{ route('pasar.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-[#115789] hover:text-white bg-blue-50 hover:bg-[#115789] border border-blue-200 transition-all duration-200 shadow-sm">
                    <span>Lihat Semua</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($popularProducts as $pop)
                    @php
                        $popCleanRegion = preg_replace('/^Desa\s+/i', '', $pop->region->name ?? 'Bengkalis');
                    @endphp
                    <div class="product-card group">
                        <div class="product-img-wrapper">
                            @if($pop->foto)
                                <img src="{{ Storage::url($pop->foto) }}" alt="{{ $pop->nama_produk }}" class="product-img">
                            @else
                                <div class="no-img flex items-center justify-center h-full bg-slate-100 text-slate-300">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif

                            @if($pop->kategori)
                                <span class="product-badge">{{ $pop->kategori }}</span>
                            @endif

                            <div class="product-actions-overlay">
                                <a href="{{ route('pasar.show', $pop->id) }}" class="product-action-btn" title="Lihat Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                @auth
                                    @if($pop->stok > 0)
                                        <button type="button" class="product-action-btn" title="Tambah ke Keranjang" onclick="openOrderModal({{ $pop->id }}, '{{ addslashes($pop->nama_produk) }}', '{{ $pop->foto ? Storage::url($pop->foto) : '' }}', {{ $pop->harga }}, {{ $pop->stok }}, '{{ addslashes($pop->deskripsi ?? '') }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                                        </button>
                                    @endif
                                @endauth
                            </div>
                        </div>

                        <div class="product-info">
                            <a href="{{ route('pasar.show', $pop->id) }}" style="text-decoration:none;">
                                <h3 class="product-name" style="font-size: 1.05rem; line-height: 1.4; margin-bottom: 6px;">{{ $pop->nama_produk }}</h3>
                            </a>
                            <p class="product-desc" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $pop->deskripsi ?? 'Produk khas daerah Kabupaten Bengkalis.' }}</p>
                            
                            <div class="product-price-row">
                                <span class="product-price">Rp {{ number_format($pop->harga, 0, ',', '.') }}</span>
                                
                                @auth
                                    @if($pop->stok > 0)
                                        <button type="button" class="btn-add-cart" onclick="openOrderModal({{ $pop->id }}, '{{ addslashes($pop->nama_produk) }}', '{{ $pop->foto ? Storage::url($pop->foto) : '' }}', {{ $pop->harga }}, {{ $pop->stok }}, '{{ addslashes($pop->deskripsi ?? '') }}')">
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
        </div>
        @endif

        <!-- ========================================================================= -->
        <!-- DISCOVERY HUB: KATEGORI & DUKUNGAN BUMDES                                   -->
        <!-- ========================================================================= -->
        <div class="discovery-hub-card">
            <!-- Hub Header -->
            <div class="hub-header-flex">
                <div class="flex items-center gap-3.5">
                    <div style="width: 44px; height: 44px; border-radius: 14px; background: linear-gradient(135deg, #115789 0%, #0284c7 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 18px rgba(17, 87, 137, 0.3); flex-shrink: 0;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-tight">Jelajahi Kategori Pasar Daerah</h2>
                        <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Temukan komoditas unggulan dan aneka ragam produk BUMDes desa di Bengkalis</p>
                    </div>
                </div>
                
                <a href="{{ route('pasar.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-[#115789] hover:bg-[#0d466e] transition-all duration-200 shadow-md shadow-blue-900/15 flex-shrink-0 self-start sm:self-auto">
                    <span>Buka Katalog Lengkap</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>

            <!-- 5 Interactive Category Cards -->
            <div class="category-hub-grid">
                <!-- Cat 1 -->
                <a href="{{ route('pasar.index', ['kategori' => 'Hasil Tani & Bumi']) }}" class="category-hub-item group">
                    <div>
                        <div class="category-hub-icon" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                            <svg class="w-6 h-6" style="color: #059669;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>
                        </div>
                        <h3 class="category-hub-title group-hover:text-[#115789] transition-colors">Hasil Tani & Bumi</h3>
                        <p class="category-hub-desc">Padi, sayur segar, buah-buahan & kelapa sawit</p>
                    </div>
                    <span class="category-hub-link">
                        <span>Jelajahi</span>
                        <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </a>

                <!-- Cat 2 -->
                <a href="{{ route('pasar.index', ['kategori' => 'Pangan & Olahan']) }}" class="category-hub-item group">
                    <div>
                        <div class="category-hub-icon" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);">
                            <svg class="w-6 h-6" style="color: #ea580c;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        </div>
                        <h3 class="category-hub-title group-hover:text-[#115789] transition-colors">Pangan & Olahan</h3>
                        <p class="category-hub-desc">Lempuk durian, terasi, kerupuk & olahan laut</p>
                    </div>
                    <span class="category-hub-link">
                        <span>Jelajahi</span>
                        <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </a>

                <!-- Cat 3 -->
                <a href="{{ route('pasar.index', ['kategori' => 'Material & Bangunan']) }}" class="category-hub-item group">
                    <div>
                        <div class="category-hub-icon" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                            <svg class="w-6 h-6" style="color: #16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
                        </div>
                        <h3 class="category-hub-title group-hover:text-[#115789] transition-colors">Material & Bangunan</h3>
                        <p class="category-hub-desc">Semen, pasir, batu bata & kebutuhan konstruksi</p>
                    </div>
                    <span class="category-hub-link">
                        <span>Jelajahi</span>
                        <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </a>

                <!-- Cat 4 -->
                <a href="{{ route('pasar.index', ['kategori' => 'Kerajinan & Kesenian']) }}" class="category-hub-item group">
                    <div>
                        <div class="category-hub-icon" style="background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);">
                            <svg class="w-6 h-6" style="color: #9333ea;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
                        </div>
                        <h3 class="category-hub-title group-hover:text-[#115789] transition-colors">Kerajinan & Seni</h3>
                        <p class="category-hub-desc">Anyaman pandan, kain tenun & souvenir khas</p>
                    </div>
                    <span class="category-hub-link">
                        <span>Jelajahi</span>
                        <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </a>

                <!-- Cat 5 -->
                <a href="{{ route('pasar.index', ['kategori' => 'Lainnya']) }}" class="category-hub-item group">
                    <div>
                        <div class="category-hub-icon" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
                            <svg class="w-6 h-6" style="color: #0284c7;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                        </div>
                        <h3 class="category-hub-title group-hover:text-[#115789] transition-colors">Produk Lainnya</h3>
                        <p class="category-hub-desc">Beragam kebutuhan dan peralatan warga desa</p>
                    </div>
                    <span class="category-hub-link">
                        <span>Jelajahi</span>
                        <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </a>
            </div>

            <!-- Gradient Marketplace Hero Banner -->
            <div class="hub-banner-gradient">
                <div class="hub-banner-glow"></div>
                <div class="hub-banner-inner">
                    <!-- Left Copy & CTAs -->
                    <div class="hub-banner-left">
                        <div class="hub-badge-pill">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <span>Belanja Langsung dari BUMDes Desa</span>
                        </div>
                        <h3 class="hub-banner-title">
                            Dukung Usaha Lokal & Petani Daerah Bengkalis
                        </h3>
                        <p class="hub-banner-text">
                            Nikmati kemudahan bertransaksi langsung dengan unit usaha BUMDes di desa Anda. Produk dijamin asli, harga transparan langsung dari produsen lokal, dan dikirim cepat oleh kurir desa.
                        </p>
                        <div class="hub-banner-actions">
                            <a href="{{ route('pasar.index') }}" class="hub-btn-white">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <span>Cari Produk di Katalog</span>
                            </a>
                        </div>
                    </div>

                    <!-- Right 3 Benefit Cards -->
                    <div class="hub-banner-right">
                        <div class="hub-benefit-list">
                            <div class="hub-benefit-item">
                                <div class="hub-benefit-icon">
                                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2v0a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12v0a2 2 0 0 1-2-2V7"/></svg>
                                </div>
                                <div>
                                    <h4 class="hub-benefit-title">Unit Usaha Resmi BUMDes</h4>
                                    <p class="hub-benefit-sub">Tervalidasi langsung oleh pemerintah desa dan BUMDes resmi.</p>
                                </div>
                            </div>

                            <div class="hub-benefit-item">
                                <div class="hub-benefit-icon">
                                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                                </div>
                                <div>
                                    <h4 class="hub-benefit-title">Pengiriman Cepat & Fleksibel</h4>
                                    <p class="hub-benefit-sub">Bisa ambil langsung di toko atau diantar kurir lokal satu desa.</p>
                                </div>
                            </div>

                            <div class="hub-benefit-item">
                                <div class="hub-benefit-icon">
                                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                                </div>
                                <div>
                                    <h4 class="hub-benefit-title">Harga Langsung Produsen</h4>
                                    <p class="hub-benefit-sub">Harga transparan dan terjangkau tanpa perantara pihak ketiga.</p>
                                </div>
                            </div>
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
<div id="copyToast" class="ps-copy-toast">âœ“ Link berhasil disalin!</div>

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
        if (btn) btn.classList.add('active');
    }

    function openLightbox(src) {
        document.getElementById('lightboxImg').src = src;
        document.getElementById('lightbox').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        document.getElementById('lightbox').style.display = 'none';
        document.body.style.overflow = '';
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
                quantity: qtyInput ? qtyInput.value : 1,
                is_direct_buy: isDirectBuy ? 1 : 0,
                catatan: document.getElementById('catatanInput') ? document.getElementById('catatanInput').value : ''
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if(isDirectBuy) {
                    window.location.href = '{{ route("pasar.checkout") }}';
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

    // Modal state for quick adding related/popular products
    let currentModalProduct = null;
    let modalQty = 1;

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
            showSiladesBengToast('success', 'Tersimpan!', `${currentModalProduct.name} ditambahkan ke keranjang.`, 2000);
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

    // Dynamic top spacing & sticky action card position for collapsible master navbar
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
</script>
@endpush
@endsection

