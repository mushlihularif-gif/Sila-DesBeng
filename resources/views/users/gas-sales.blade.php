@extends('layouts.user')

@section('title', 'Unit Penjualan Gas - SiladesBeng')

@push('styles')
<style>
    * { font-family: 'Inter', sans-serif; }

    /* ====== HERO ====== */
    .gas-hero {
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        padding: 140px 24px 100px;
    }
    .gas-hero-content { position: relative; z-index: 10; text-align: center; }

    .gas-badge {
        display: inline-flex; align-items: center; gap: 10px;
        padding: 10px 22px;
        background: rgba(17, 87, 137, 0.08);
        border: 1px solid rgba(17, 87, 137, 0.2);
        border-radius: 9999px;
        font-size: 13px; font-weight: 600; color: #115789;
        backdrop-filter: blur(4px);
        margin-bottom: 24px;
        animation: float 3s ease-in-out infinite;
    }
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }

    .gas-title {
        font-size: clamp(2.2rem, 5vw, 4.2rem);
        font-weight: 800; line-height: 1.1; margin-bottom: 20px;
    }
    .gas-title-blue {
        background: linear-gradient(135deg, #115789, #2563eb, #60a5fa);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text;
        background-size: 200%; animation: gradAnim 4s ease infinite;
    }
    @keyframes gradAnim { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }
    .gas-desc {
        font-size: clamp(1rem, 2vw, 1.2rem);
        color: #4b5563; max-width: 700px; margin: 0 auto 36px; line-height: 1.8;
    }

    .scroll-indicator {
        display: inline-flex; flex-direction: column; align-items: center;
        gap: 6px; color: #6b7280; font-size: 13px; cursor: pointer;
        animation: bounce-arrow 2s ease infinite;
    }
    .scroll-indicator svg { width: 22px; height: 22px; }
    @keyframes bounce-arrow { 0%,100%{transform:translateY(0)} 50%{transform:translateY(8px)} }

    /* ====== SECTION BASE ====== */
    .gas-section {
        position: relative; z-index: 10;
        padding: 80px 24px;
    }
    .gas-section-white { background: #ffffff; }
    .gas-section-light { background: #f8fafc; }

    .section-heading { text-align: center; margin-bottom: 56px; }
    .section-heading h2 {
        font-size: clamp(1.8rem, 4vw, 2.8rem);
        font-weight: 800; color: #1e293b; margin-bottom: 10px;
    }
    .section-heading p { color: #6b7280; font-size: 1rem; }

    /* ====== STEPS ====== */
    .steps-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 24px;
        max-width: 1000px; margin: 0 auto;
        position: relative;
    }
    .steps-connector {
        position: absolute; top: 48px; left: 15%; right: 15%;
        height: 2px;
        background: linear-gradient(to right, #60a5fa, #115789, #60a5fa);
        z-index: 0;
        display: none;
    }
    @media(min-width:768px){ .steps-connector{display:block;} }

    .step-card {
        background: #fff;
        border-radius: 20px;
        padding: 28px 20px;
        text-align: center;
        box-shadow: 0 4px 24px rgba(0,0,0,0.07);
        border: 1px solid #e2e8f0;
        position: relative; z-index: 1;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .step-card:hover { transform: translateY(-6px); box-shadow: 0 12px 40px rgba(17,87,137,0.15); }

    .step-num-wrap { position: relative; display: inline-flex; margin-bottom: 16px; }
    .step-num-glow {
        position: absolute; inset: -4px;
        border-radius: 50%; opacity: 0.3;
        filter: blur(8px);
    }
    .step-num {
        width: 52px; height: 52px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; font-weight: 800; color: #fff;
        position: relative; z-index: 1;
    }
    .step-num.blue   { background: linear-gradient(135deg,#60a5fa,#2563eb); }
    .step-num.green  { background: linear-gradient(135deg,#34d399,#059669); }
    .step-num.amber  { background: linear-gradient(135deg,#fbbf24,#f97316); }
    .step-num.purple { background: linear-gradient(135deg,#a78bfa,#7c3aed); }

    .step-title { font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
    .step-desc  { font-size: 0.85rem; color: #6b7280; line-height: 1.6; }

    /* ====== FILTER BAR ====== */
    .filter-bar {
        display: flex; flex-wrap: wrap; gap: 10px;
        justify-content: center; margin-bottom: 40px;
    }
    .filter-pill {
        padding: 9px 22px;
        border-radius: 9999px;
        font-size: 0.875rem; font-weight: 600;
        border: 2px solid #e2e8f0;
        background: #fff; color: #6b7280;
        cursor: pointer; transition: all 0.25s ease;
        text-decoration: none; display: inline-block;
    }
    .filter-pill:hover, .filter-pill.active {
        background: #115789; color: #fff;
        border-color: #115789;
        box-shadow: 0 4px 14px rgba(17,87,137,0.25);
    }
    .filter-pill.active-green { background: #059669; border-color: #059669; }
    .filter-pill.active-amber { background: #f97316; border-color: #f97316; }

    /* ====== PRODUCT CARDS ====== */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 28px;
        max-width: 1100px; margin: 0 auto;
    }
    .product-card {
        background: rgba(255,255,255,0.9);
        backdrop-filter: blur(8px);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid rgba(255,255,255,0.7);
        transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 48px rgba(17,87,137,0.18);
    }
    .product-img-wrap {
        aspect-ratio: 4/3; overflow: hidden; position: relative;
    }
    .product-img-wrap img {
        width: 100%; height: 100%; object-fit: cover;
        transition: transform 0.4s ease;
    }
    .product-card:hover .product-img-wrap img { transform: scale(1.06); }

    .badge-stok {
        position: absolute; top: 12px; right: 12px;
        padding: 4px 10px; border-radius: 9999px;
        font-size: 11px; font-weight: 700; color: #fff;
    }
    .badge-stok.available { background: #059669; }
    .badge-stok.empty     { background: #ef4444; }
    .badge-kategori {
        position: absolute; top: 12px; left: 12px;
        padding: 4px 10px; border-radius: 9999px;
        font-size: 11px; font-weight: 700;
    }
    .badge-kategori.subsidi     { background: #dbeafe; color: #1d4ed8; }
    .badge-kategori.non-subsidi { background: #fef3c7; color: #b45309; }

    .product-body { padding: 18px 18px 20px; }
    .product-name { font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
    .product-price { font-size: 1.2rem; font-weight: 800; color: #115789; }
    .product-price span { font-size: 0.8rem; font-weight: 400; color: #9ca3af; }
    .product-meta { font-size: 0.78rem; color: #9ca3af; margin-top: 6px; display: flex; align-items: center; gap: 4px; }
    .product-btn {
        display: block; margin-top: 14px;
        padding: 10px;
        background: linear-gradient(135deg, #115789, #60a5fa);
        color: #fff; font-weight: 600; font-size: 0.875rem;
        border-radius: 12px; text-align: center;
        transition: opacity 0.2s;
    }
    .product-btn:hover { opacity: 0.9; }

    /* ====== EMPTY STATE ====== */
    .empty-state {
        text-align: center; padding: 64px 24px;
    }
    .empty-icon {
        width: 88px; height: 88px; border-radius: 24px;
        background: #f1f5f9;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px;
    }
    .empty-icon svg { width: 44px; height: 44px; color: #cbd5e1; }

    /* ====== FADE IN ====== */
    .fade-up {
        opacity: 0; transform: translateY(28px);
        animation: fadeUp 0.7s ease forwards;
    }
    .fade-up:nth-child(1){animation-delay:0s}
    .fade-up:nth-child(2){animation-delay:0.1s}
    .fade-up:nth-child(3){animation-delay:0.2s}
    .fade-up:nth-child(4){animation-delay:0.3s}
    @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }

    /* ====== STAT CARDS GAS ====== */
    .stat-card-gas {
        background: #fff;
        border-radius: 20px;
        padding: 24px 20px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        border: 1px solid #f1f5f9;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .stat-card-gas:hover { transform: translateY(-6px); box-shadow: 0 12px 36px rgba(0,0,0,0.12); }
    .stat-icon-wrap-gas { position: relative; display: inline-flex; margin-bottom: 14px; }
    .stat-glow {
        position: absolute; inset: -4px; border-radius: 50%;
        opacity: 0.25; filter: blur(8px);
    }
    .stat-icon-gas {
        width: 56px; height: 56px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        position: relative; z-index: 1;
    }
    .stat-icon-gas svg { width: 26px; height: 26px; stroke: #fff; }
    .stat-icon-gas.blue   { background: linear-gradient(135deg,#60a5fa,#2563eb); }
    .stat-icon-gas.green  { background: linear-gradient(135deg,#34d399,#059669); }
    .stat-icon-gas.amber  { background: linear-gradient(135deg,#fbbf24,#f97316); }
    .stat-icon-gas.purple { background: linear-gradient(135deg,#a78bfa,#7c3aed); }
    .stat-value-gas {
        font-size: 2.2rem; font-weight: 800; line-height: 1;
        margin-bottom: 6px;
    }
    .stat-value-gas.blue   { color: #2563eb; }
    .stat-value-gas.green  { color: #059669; }
    .stat-value-gas.amber  { color: #f97316; }
    .stat-value-gas.purple { color: #7c3aed; }
    .stat-label-gas { font-size: 0.85rem; color: #6b7280; font-weight: 500; margin-bottom: 12px; }
    .stat-bar-gas {
        height: 4px; background: #f1f5f9; border-radius: 9999px; overflow: hidden;
    }
    .stat-bar-fill-gas {
        height: 100%; border-radius: 9999px; width: 0%;
        transition: width 1.5s ease;
    }

    /* ====== HARGA CARD ====== */
    .harga-card {
        background: #fff;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        border: 1px solid #f1f5f9;
        transition: transform 0.3s ease;
    }
    .harga-card:hover { transform: translateY(-4px); }
</style>
@endpush

@section('page')
<main class="flex-grow relative w-full">

    {{-- ===== ANIMATED CANVAS BG ===== --}}
    <div class="fixed inset-0 overflow-hidden z-0">
        <canvas id="gas-canvas" class="w-full h-full absolute inset-0"></canvas>
    </div>

    {{-- ===== HERO ===== --}}
    <section class="gas-hero" id="hero">
        <div class="gas-hero-content">
            {{-- Badge --}}
            <div class="gas-badge">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2a7 7 0 0 1 7 7c0 4.5-7 13-7 13S5 13.5 5 9a7 7 0 0 1 7-7z"/>
                    <circle cx="12" cy="9" r="2.5"/>
                </svg>
                Unit BUMDes — Penjualan Gas LPG
            </div>

            {{-- Judul --}}
            <h1 class="gas-title">
                <span class="gas-title-blue">Unit Penjualan Gas</span><br>
                <span style="font-size:0.55em; font-weight:600; color:#475569;">Bersubsidi &amp; Non-Subsidi</span>
            </h1>

            <p class="gas-desc">
                Pesan Gas LPG <strong>bersubsidi</strong> maupun <strong>non-subsidi</strong> secara mudah dan transparan.
                Layanan resmi BUMDes untuk memenuhi kebutuhan energi masyarakat desa.
            </p>

            {{-- CTA --}}
            <a href="#produk" class="scroll-indicator nav-scroll" style="display:inline-flex; flex-direction:column; align-items:center;">
                <span style="font-size:13px; color:#6b7280; margin-bottom:6px;">Lihat Produk</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="#115789" stroke-width="2.5" stroke-linecap="round">
                    <path d="M7 10l5 5 5-5"/>
                </svg>
            </a>
        </div>
    </section>

    {{-- ===== STATISTIK ===== --}}
    <section class="gas-section gas-section-white" id="statistik">
        <div class="max-w-5xl mx-auto">
            <div class="section-heading">
                <h2>Data Real-Time</h2>
                <p>Transparansi informasi layanan gas BUMDes</p>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px;">

                {{-- Total Produk --}}
                <div class="stat-card-gas">
                    <div class="stat-icon-wrap-gas">
                        <div class="stat-glow" style="background:linear-gradient(135deg,#60a5fa,#2563eb)"></div>
                        <div class="stat-icon-gas blue">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="6" y="4" width="12" height="17" rx="3"/>
                                <path d="M9 4V2h6v2"/>
                                <path d="M20 9h1.5a1.5 1.5 0 0 1 0 3H20"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value-gas blue counter-gas" data-target="{{ $stats['total_produk'] }}">0</div>
                    <div class="stat-label-gas">Jenis Produk Gas</div>
                    <div class="stat-bar-gas"><div class="stat-bar-fill-gas" style="background:linear-gradient(to right,#60a5fa,#2563eb)"></div></div>
                </div>

                {{-- Total Stok --}}
                <div class="stat-card-gas">
                    <div class="stat-icon-wrap-gas">
                        <div class="stat-glow" style="background:linear-gradient(135deg,#34d399,#059669)"></div>
                        <div class="stat-icon-gas green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value-gas green counter-gas" data-target="{{ $stats['total_stok'] }}">0</div>
                    <div class="stat-label-gas">Total Stok Tersedia</div>
                    <div class="stat-bar-gas"><div class="stat-bar-fill-gas" style="background:linear-gradient(to right,#34d399,#059669)"></div></div>
                </div>

                {{-- Total Transaksi --}}
                <div class="stat-card-gas">
                    <div class="stat-icon-wrap-gas">
                        <div class="stat-glow" style="background:linear-gradient(135deg,#fbbf24,#f97316)"></div>
                        <div class="stat-icon-gas amber">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                                <rect x="9" y="3" width="6" height="4" rx="1"/>
                                <path d="M9 12h6M9 16h4"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value-gas amber counter-gas" data-target="{{ $stats['total_transaksi'] }}">0</div>
                    <div class="stat-label-gas">Total Transaksi</div>
                    <div class="stat-bar-gas"><div class="stat-bar-fill-gas" style="background:linear-gradient(to right,#fbbf24,#f97316)"></div></div>
                </div>

                {{-- Pesanan Selesai --}}
                <div class="stat-card-gas">
                    <div class="stat-icon-wrap-gas">
                        <div class="stat-glow" style="background:linear-gradient(135deg,#a78bfa,#7c3aed)"></div>
                        <div class="stat-icon-gas purple">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <path d="M22 4L12 14.01l-3-3"/>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value-gas purple counter-gas" data-target="{{ $stats['selesai'] }}">0</div>
                    <div class="stat-label-gas">Pesanan Selesai</div>
                    <div class="stat-bar-gas"><div class="stat-bar-fill-gas" style="background:linear-gradient(to right,#a78bfa,#7c3aed)"></div></div>
                </div>

            </div>
        </div>
    </section>

    {{-- ===== INFO HARGA LPG ===== --}}
    <section class="gas-section gas-section-light" id="harga">
        <div class="max-w-5xl mx-auto">
            <div class="section-heading">
                <h2>Informasi Harga LPG</h2>
                <p>Referensi harga resmi berdasarkan ketentuan pemerintah</p>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:20px;">

                {{-- Bersubsidi --}}
                <div class="harga-card" style="border-top: 4px solid #059669;">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
                        <div style="width:44px;height:44px;border-radius:12px;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                        </div>
                        <div>
                            <p style="font-weight:800;color:#1e293b;font-size:1rem;">Gas LPG Bersubsidi</p>
                            <span style="font-size:11px;background:#dcfce7;color:#059669;padding:2px 8px;border-radius:9999px;font-weight:600;">Subsidi Pemerintah</span>
                        </div>
                    </div>
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f0fdf4;">
                                <th style="padding:8px 12px;text-align:left;font-size:12px;color:#6b7280;font-weight:600;border-radius:8px 0 0 8px;">Ukuran</th>
                                <th style="padding:8px 12px;text-align:right;font-size:12px;color:#6b7280;font-weight:600;border-radius:0 8px 8px 0;">HET</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:10px 12px;font-size:0.9rem;color:#374151;font-weight:500;">3 kg</td>
                                <td style="padding:10px 12px;text-align:right;font-weight:700;color:#059669;">Rp 16.000 – 18.500</td>
                            </tr>
                        </tbody>
                    </table>
                    <p style="font-size:11px;color:#9ca3af;margin-top:10px;">* HET (Harga Eceran Tertinggi) sesuai Perpres dan dapat berbeda per daerah</p>
                </div>

                {{-- Non-Subsidi --}}
                <div class="harga-card" style="border-top: 4px solid #f97316;">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
                        <div style="width:44px;height:44px;border-radius:12px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round">
                                <rect x="6" y="4" width="12" height="17" rx="3"/>
                                <path d="M9 4V2h6v2"/>
                                <path d="M20 9h1.5a1.5 1.5 0 0 1 0 3H20"/>
                            </svg>
                        </div>
                        <div>
                            <p style="font-weight:800;color:#1e293b;font-size:1rem;">Gas LPG Non-Subsidi</p>
                            <span style="font-size:11px;background:#fff7ed;color:#f97316;padding:2px 8px;border-radius:9999px;font-weight:600;">Harga Pasar</span>
                        </div>
                    </div>
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#fff7ed;">
                                <th style="padding:8px 12px;text-align:left;font-size:12px;color:#6b7280;font-weight:600;">Ukuran</th>
                                <th style="padding:8px 12px;text-align:right;font-size:12px;color:#6b7280;font-weight:600;">Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:10px 12px;font-size:0.9rem;color:#374151;font-weight:500;">5,5 kg (Bright Gas)</td>
                                <td style="padding:10px 12px;text-align:right;font-weight:700;color:#f97316;">Rp 45.000</td>
                            </tr>
                            <tr>
                                <td style="padding:10px 12px;font-size:0.9rem;color:#374151;font-weight:500;">12 kg</td>
                                <td style="padding:10px 12px;text-align:right;font-weight:700;color:#f97316;">Rp 120.000 – 125.000</td>
                            </tr>
                        </tbody>
                    </table>
                    <p style="font-size:11px;color:#9ca3af;margin-top:10px;">* Harga dapat berubah mengikuti kebijakan Pertamina</p>
                </div>

            </div>
        </div>
    </section>

    {{-- ===== CARA MEMESAN ===== --}}

    <section class="gas-section gas-section-white" id="cara">
        <div class="max-w-6xl mx-auto">
            <div class="section-heading">
                <h2>Cara Memesan Gas</h2>
                <p>Proses mudah dalam 4 langkah sederhana</p>
            </div>

            <div class="steps-grid">
                <div class="steps-connector"></div>

                <div class="step-card fade-up">
                    <div class="step-num-wrap">
                        <div class="step-num-glow" style="background:linear-gradient(135deg,#60a5fa,#2563eb)"></div>
                        <div class="step-num blue">1</div>
                    </div>
                    <p class="step-title">Daftar / Masuk</p>
                    <p class="step-desc">Buat akun atau login ke sistem untuk dapat melakukan pemesanan</p>
                </div>

                <div class="step-card fade-up">
                    <div class="step-num-wrap">
                        <div class="step-num-glow" style="background:linear-gradient(135deg,#34d399,#059669)"></div>
                        <div class="step-num green">2</div>
                    </div>
                    <p class="step-title">Pilih Produk Gas</p>
                    <p class="step-desc">Pilih jenis gas LPG sesuai kebutuhan — bersubsidi atau non-subsidi</p>
                </div>

                <div class="step-card fade-up">
                    <div class="step-num-wrap">
                        <div class="step-num-glow" style="background:linear-gradient(135deg,#fbbf24,#f97316)"></div>
                        <div class="step-num amber">3</div>
                    </div>
                    <p class="step-title">Tentukan Jumlah</p>
                    <p class="step-desc">Isi jumlah tabung yang ingin dipesan sesuai ketersediaan stok</p>
                </div>

                <div class="step-card fade-up">
                    <div class="step-num-wrap">
                        <div class="step-num-glow" style="background:linear-gradient(135deg,#a78bfa,#7c3aed)"></div>
                        <div class="step-num purple">4</div>
                    </div>
                    <p class="step-title">Konfirmasi Pesanan</p>
                    <p class="step-desc">Kirim pesanan dan tunggu konfirmasi dari petugas BUMDes</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== PRODUK ===== --}}
    <section class="gas-section gas-section-light" id="produk">
        <div class="max-w-6xl mx-auto">
            <div class="section-heading">
                <h2>Produk Gas Tersedia</h2>
                <p>Filter produk sesuai jenis yang Anda butuhkan</p>
            </div>

            {{-- Filter Pills --}}
            <div class="filter-bar">
                <a href="{{ route('gas.sales') }}"
                   class="filter-pill {{ $kategori === '' ? 'active' : '' }}">
                    Semua
                </a>
                <a href="{{ route('gas.sales', ['kategori' => 'bersubsidi']) }}"
                   class="filter-pill {{ $kategori === 'bersubsidi' ? 'active active-green' : '' }}">
                    <span style="display:inline-flex; align-items:center; gap:6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                        Gas Bersubsidi
                    </span>
                </a>
                <a href="{{ route('gas.sales', ['kategori' => 'non-bersubsidi']) }}"
                   class="filter-pill {{ $kategori === 'non-bersubsidi' ? 'active active-amber' : '' }}">
                    <span style="display:inline-flex; align-items:center; gap:6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        Gas Non-Subsidi
                    </span>
                </a>
            </div>

            {{-- Grid Produk --}}
            @if($items->count() > 0)
                <div class="product-grid">
                    @foreach($items as $item)
                    <a href="{{ route('gas.sales.show', $item->id) }}" class="product-card">
                        <div class="product-img-wrap">
                            <img src="{{ asset('storage/' . $item->foto) }}"
                                 alt="{{ $item->jenis_gas }}"
                                 loading="lazy">

                            {{-- Badge Stok --}}
                            @if($item->stok > 0)
                                <span class="badge-stok available">Tersedia</span>
                            @else
                                <span class="badge-stok empty">Habis</span>
                            @endif

                            {{-- Badge Kategori --}}
                            @if($item->kategori)
                                <span class="badge-kategori {{ $item->kategori === 'bersubsidi' ? 'subsidi' : 'non-subsidi' }}">
                                    {{ ucfirst($item->kategori) }}
                                </span>
                            @endif
                        </div>

                        <div class="product-body">
                            <p class="product-name">{{ $item->jenis_gas }}</p>
                            <p class="product-price">
                                Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                                <span>/ {{ $item->satuan ?? 'tabung' }}</span>
                            </p>
                            <p class="product-meta">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                Stok: {{ $item->stok }} {{ $item->satuan ?? 'tabung' }}
                                @if($item->lokasi)
                                &nbsp;&bull;&nbsp;{{ $item->lokasi }}
                                @endif
                            </p>
                            <span class="product-btn">Lihat Detail &amp; Pesan</span>
                        </div>
                    </a>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                            <rect x="6" y="4" width="12" height="17" rx="3"/>
                            <path d="M9 4V2h6v2"/>
                            <path d="M20 9h1.5a1.5 1.5 0 0 1 0 3H20"/>
                        </svg>
                    </div>
                    <h3 style="font-size:1.1rem; font-weight:700; color:#374151; margin-bottom:8px;">
                        @if($kategori)
                            Tidak ada produk untuk kategori ini
                        @else
                            Belum Ada Produk Gas Tersedia
                        @endif
                    </h3>
                    <p style="color:#9ca3af; font-size:0.9rem;">
                        @if($kategori)
                            Coba pilih kategori lain atau lihat semua produk.
                        @else
                            Produk gas akan segera ditambahkan oleh pengelola BUMDes.
                        @endif
                    </p>
                    @if($kategori)
                        <a href="{{ route('gas.sales') }}" class="filter-pill active" style="display:inline-block; margin-top:16px;">
                            Lihat Semua Produk
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </section>

</main>
@endsection

@push('scripts')
<script>
    // Counter animation for stats
    const counters = document.querySelectorAll('.counter-gas');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.getAttribute('data-target')) || 0;
                const bar = el.closest('.stat-card-gas')?.querySelector('.stat-bar-fill-gas');
                let current = 0;
                const step = Math.max(1, Math.ceil(target / 60));
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) { current = target; clearInterval(timer); }
                    el.textContent = current.toLocaleString('id-ID');
                    if (bar) bar.style.width = Math.min(100, (current / Math.max(target, 1)) * 100) + '%';
                }, 25);
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.3 });
    counters.forEach(c => observer.observe(c));

    // Smooth scroll
    document.querySelectorAll('.nav-scroll').forEach(a => {
        a.addEventListener('click', function(e) {
            e.preventDefault();
            const t = document.querySelector(this.getAttribute('href'));
            if (t) t.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Animated Canvas Background
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('gas-canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        let width, height;
        let mouse = { x: -1000, y: -1000 };
        let targetMouse = { x: -1000, y: -1000 };

        function resize() {
            if (width !== window.innerWidth || height !== window.innerHeight) {
                width = window.innerWidth;
                height = window.innerHeight;
                canvas.width = width;
                canvas.height = height;
                initWaves();
            }
        }

        window.addEventListener('resize', resize);
        window.addEventListener('mousemove', (e) => { targetMouse.x = e.clientX; targetMouse.y = e.clientY; });
        window.addEventListener('mouseout', () => { targetMouse.x = -1000; targetMouse.y = -1000; });

        let scrollY = window.scrollY;
        window.addEventListener('scroll', () => { scrollY = window.scrollY; });

        class Wave {
            constructor(getGradient, yOffset, amplitude, speed, wavelength) {
                this.getGradient = getGradient;
                this.yOffset = yOffset; this.amplitude = amplitude;
                this.speed = speed; this.wavelength = wavelength;
                this.points = []; this.time = Math.random() * 100;
            }
            init() {
                this.points = [];
                let n = Math.ceil(width / 25) + 2;
                for (let i = 0; i < n; i++) {
                    let x = (i - 1) * 25;
                    let baseY = height * this.yOffset;
                    this.points.push({ x, baseY, y: baseY + Math.sin(this.time + x / this.wavelength) * this.amplitude, vy: 0, spring: 0.05, friction: 0.90 });
                }
            }
            update() {
                this.time += this.speed;
                for (let p of this.points) {
                    let tY = p.baseY + Math.sin(this.time + p.x / this.wavelength) * this.amplitude;
                    let dx = mouse.x - p.x, dy = mouse.y - tY;
                    let dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < 200) { let f = Math.pow((200 - dist) / 200, 2); tY += (dy > 0 ? -1 : 1) * f * 60; }
                    p.vy += (tY - p.y) * p.spring; p.vy *= p.friction; p.y += p.vy;
                }
            }
            draw() {
                ctx.beginPath();
                ctx.moveTo(this.points[0].x, this.points[0].y);
                for (let i = 0; i < this.points.length - 1; i++) {
                    let cx = (this.points[i].x + this.points[i+1].x) / 2;
                    let cy = (this.points[i].y + this.points[i+1].y) / 2;
                    ctx.quadraticCurveTo(this.points[i].x, this.points[i].y, cx, cy);
                }
                let last = this.points[this.points.length - 1];
                ctx.lineTo(last.x, last.y);
                ctx.lineTo(width, height * 2 + scrollY);
                ctx.lineTo(0, height * 2 + scrollY);
                ctx.closePath();
                ctx.fillStyle = this.getGradient(ctx, width, height);
                ctx.fill();
            }
        }

        let waves = [];
        function initWaves() {
            waves = [
                new Wave((ctx,w,h) => { let g = ctx.createLinearGradient(0,h*.5,0,h*1.2); g.addColorStop(0,'rgba(140,190,250,0.7)'); g.addColorStop(1,'rgba(180,215,255,0.1)'); return g; }, 0.65, 40, 0.005, 600),
                new Wave((ctx,w,h) => { let g = ctx.createLinearGradient(0,h*.6,0,h*1.2); g.addColorStop(0,'rgba(255,255,255,1)'); g.addColorStop(1,'rgba(245,250,255,0.5)'); return g; }, 0.75, 30, 0.003, 500),
                new Wave((ctx,w,h) => { let g = ctx.createLinearGradient(0,h*.7,0,h*1.1); g.addColorStop(0,'rgba(245,225,130,0.5)'); g.addColorStop(1,'rgba(255,255,255,0)'); return g; }, 0.85, 45, 0.007, 700),
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

            let gx = width * 0.15, gy = height * 0.4;
            let grad = ctx.createRadialGradient(gx, gy, 0, gx, gy, width * 0.3);
            grad.addColorStop(0, 'rgba(245,235,150,0.15)');
            grad.addColorStop(1, 'rgba(245,235,150,0)');
            ctx.fillStyle = grad;
            ctx.beginPath(); ctx.arc(gx, gy, width * 0.3, 0, Math.PI * 2); ctx.fill();

            waves.forEach(w => { w.update(); w.draw(); });

            ctx.save();
            ctx.translate(width * 0.9, height * 0.08);
            let dxD = mouse.x - width * 0.9, dyD = mouse.y - height * 0.08;
            let distD = Math.sqrt(dxD*dxD + dyD*dyD);
            if (distD < 300) { let f = (300-distD)/300; ctx.translate(-dxD/distD*f*20, -dyD/distD*f*20); }
            ctx.rotate(Math.PI / 4);
            ctx.fillStyle = 'rgba(74,144,226,0.4)'; ctx.fillRect(-15,-15,30,30);
            ctx.fillStyle = 'rgba(120,175,240,0.3)'; ctx.fillRect(5,5,25,25);
            ctx.restore();
            ctx.restore();

            requestAnimationFrame(animate);
        }

        resize();
        animate();
    });
</script>
@endpush
