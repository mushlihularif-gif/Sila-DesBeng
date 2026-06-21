@extends('layouts.user')
@section('title', 'Pengaduan Warga - Kelurahan Sungai Pakning')
@push('styles')
<style>
        * { font-family: 'Inter', sans-serif; }
        .page-wrapper {
            position: relative; z-index: 10;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f0f4f8 url('{{ asset("Admin/img/elements/background.png") }}') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        
        

        @media (max-width: 768px) { .hero-buttons { flex-direction: column; } }

                @keyframes fade-in-up {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up { animation: fade-in-up 0.8s ease-out forwards; opacity: 0; }
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }

        /* ============ HERO SECTION ============ */
        .hero {
            z-index: 10;
            z-index: 10;
            position: relative; padding: 120px 0 100px; overflow: hidden;
        }
        .hero-bg {
            position: absolute; inset: 0; z-index: 0;
            /* Background dihapus - menggunakan background.png dari body */
        }
        .hero-content { position: relative; z-index: 10; }

        /* Particles */
        .particle {
            position: absolute;
            background: radial-gradient(circle, rgba(250,204,21,0.2) 0%, transparent 70%);
            border-radius: 50%; opacity: 0.3;
            animation: particle-float 20s infinite;
        }
        .particle:nth-child(1) { width: 80px; height: 80px; top: 20%; left: 10%; }
        .particle:nth-child(2) { width: 120px; height: 120px; top: 60%; left: 70%; animation-delay: 2s; }
        .particle:nth-child(3) { width: 60px; height: 60px; top: 40%; left: 80%; animation-delay: 4s; }
        .particle:nth-child(4) { width: 100px; height: 100px; top: 80%; left: 20%; animation-delay: 6s; }
        .particle:nth-child(5) { width: 90px; height: 90px; top: 10%; left: 60%; animation-delay: 8s; }
        @keyframes particle-float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(20px, -20px) scale(1.1); }
            50% { transform: translate(-20px, 20px) scale(0.9); }
            75% { transform: translate(20px, 20px) scale(1.05); }
        }

        /* Badge */
        .hero-badge {
            display: inline-flex; align-items: center; gap: 12px;
            padding: 12px 24px;
            background: linear-gradient(to right, rgba(30,58,95,0.1), rgba(59,130,246,0.1));
            color: #1e3a5f; border-radius: 9999px; font-size: 14px; font-weight: 600;
            border: 1px solid rgba(30,58,95,0.3);
            backdrop-filter: blur(4px); box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

        /* Title */
        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4.5rem); font-weight: 800;
            line-height: 1.1; margin-bottom: 32px;
        }
        .hero-title-gold {
            background: linear-gradient(to right, #1e3a5f, #2563eb, #1e3a5f);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
            background-size: 200% 200%; animation: gradient-anim 3s ease infinite;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        @keyframes gradient-anim { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        .hero-subtitle {
            display: block; color: #1e293b;
            font-size: clamp(1.5rem, 3vw, 3rem); font-weight: 600; margin-top: 16px;
            -webkit-text-fill-color: #1e293b;
        }

        /* Description */
        .hero-desc {
            font-size: clamp(1rem, 2vw, 1.25rem); color: #4b5563;
            max-width: 800px; margin: 0 auto 40px; line-height: 1.8;
        }
        .hero-desc .highlight { color: #2563eb; font-weight: 700; }

        /* Buttons */
        .hero-buttons { display: flex; gap: 20px; justify-content: center; align-items: center; flex-wrap: wrap; }
        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 20px 40px; font-size: 1.25rem; font-weight: 700;
            color: #fff;
            background: linear-gradient(to right, #2563eb, #3b82f6, #2563eb);
            background-size: 200% auto;
            border-radius: 16px; border: none; cursor: pointer;
            box-shadow: 0 10px 40px rgba(37,99,235,0.3);
            transition: all 0.5s ease; position: relative; overflow: hidden;
        }
        .btn-primary:hover {
            transform: scale(1.1) translateY(-8px);
            box-shadow: 0 20px 60px rgba(37,99,235,0.5);
            background-position: 100% center;
        }
        .btn-primary .icon { font-size: 1.5rem; margin-right: 12px; }
        .btn-primary:hover .icon { animation: bounce 0.5s; }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }

        .btn-outline {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 20px 40px; font-size: 1.25rem; font-weight: 700;
            color: #2563eb; background: transparent;
            border: 3px solid #2563eb; border-radius: 9999px;
            cursor: pointer; transition: all 0.5s ease;
            position: relative; overflow: hidden;
            text-decoration: none;
        }
        .btn-outline::before {
            content: ''; position: absolute; inset: 0;
            background: #2563eb; transform: translateY(100%);
            transition: transform 0.5s ease;
        }
        .btn-outline:hover { color: #fff !important; }
        .btn-outline:hover::before { transform: translateY(0); }
        .btn-outline span { position: relative; z-index: 1; display: flex; align-items: center; gap: 8px; }

        /* Daftar text */
        .hero-register { font-size: 16px; color: #6b7280; margin-top: 32px; }
        .hero-register a { color: #2563eb; font-weight: 600; text-decoration: underline; }
        .hero-register a:hover { color: #1d4ed8; }

        /* Scroll down */
        .scroll-down { margin-top: 80px; animation: bounce-slow 2s ease-in-out infinite; }
        .scroll-down a { color: #2563eb; transition: color 0.3s; }
        .scroll-down a:hover { color: #1d4ed8; }
        .scroll-down svg { width: 32px; height: 32px; margin: 0 auto; }
        .scroll-down .text { font-size: 14px; margin-top: 8px; display: block; }
        @keyframes bounce-slow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }

        /* ============ SECTIONS ============ */
        .section {
            z-index: 10;
            z-index: 10; padding: 96px 0; position: relative; }
        .section-stats { /* transparent - background.png terlihat */ }
        .section-kategori { /* transparent - background.png terlihat */ }
        .section-cara { /* transparent - background.png terlihat */ }
        .section-cta {
            /* transparent - background.png terlihat */
            overflow: hidden;
        }

        .section-title {
            font-size: clamp(2rem, 4vw, 3rem); font-weight: 700;
            color: #1e3a5f; margin-bottom: 16px;
        }
        .section-subtitle { font-size: 1.125rem; color: #4b5563; }

        /* Blur orbs */
        .blur-orb {
            position: absolute; width: 384px; height: 384px;
            background: rgba(250, 204, 21, 0.05);
            border-radius: 50%; filter: blur(80px);
        }
        .blur-orb-tl { top: 0; left: 0; }
        .blur-orb-br { bottom: 0; right: 0; }

        /* ============ STAT CARDS ============ */
        .stat-card {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 16px; padding: 32px; text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .stat-card:hover {
            transform: scale(1.05) translateY(-8px);
            box-shadow: 0 20px 60px rgba(37,99,235,0.15);
        }
        .stat-icon-wrap { position: relative; display: inline-block; margin-bottom: 20px; }
        .stat-icon-glow {
            position: absolute; inset: 0; opacity: 0.3; filter: blur(20px); border-radius: 50%;
        }
        .stat-icon {
            position: relative; width: 80px; height: 80px;
            border-radius: 16px; display: flex; align-items: center; justify-content: center;
            font-size: 3rem; transition: transform 0.3s;
            border: 1px solid rgba(250,204,21,0.2);
        }
        .stat-icon:hover { transform: rotate(12deg); }
        .stat-icon.blue { background: rgba(59,130,246,0.2); }
        .stat-icon.yellow { background: rgba(250,204,21,0.2); }
        .stat-icon.purple { background: rgba(168,85,247,0.2); }
        .stat-icon.green { background: rgba(34,197,94,0.2); }

        .stat-value {
            font-size: clamp(2.5rem, 4vw, 3.5rem); font-weight: 900;
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-bottom: 12px;
        }
        .stat-value.blue { background-image: linear-gradient(to right, #60a5fa, #2563eb); }
        .stat-value.yellow { background-image: linear-gradient(to right, #facc15, #f97316); }
        .stat-value.purple { background-image: linear-gradient(to right, #c084fc, #ec4899); }
        .stat-value.green { background-image: linear-gradient(to right, #4ade80, #059669); }

        .stat-label { color: #4b5563; font-size: 1rem; font-weight: 600; letter-spacing: 0.05em; }
        .stat-bar { margin-top: 16px; height: 8px; background: rgba(0,0,0,0.08); border-radius: 9999px; overflow: hidden; }
        .stat-bar-fill { height: 100%; border-radius: 9999px; width: 0; animation: progress 2s ease-out forwards 0.5s; }
        .stat-bar-fill.blue { background: linear-gradient(to right, #60a5fa, #2563eb); }
        .stat-bar-fill.yellow { background: linear-gradient(to right, #facc15, #f97316); }
        .stat-bar-fill.purple { background: linear-gradient(to right, #c084fc, #ec4899); }
        .stat-bar-fill.green { background: linear-gradient(to right, #4ade80, #059669); }
        @keyframes progress { from { width: 0; } to { width: 100%; } }

        /* ============ CATEGORY CARDS ============ */
        .cat-card {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 16px; padding: 24px; text-align: center;
            overflow: hidden; position: relative;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .cat-card:hover { transform: scale(1.05); }
        .cat-card-overlay {
            position: absolute; inset: 0; opacity: 0;
            transition: opacity 0.5s;
        }
        .cat-card:hover .cat-card-overlay { opacity: 0.1; }
        .cat-icon {
            position: relative; font-size: 3.5rem; margin-bottom: 16px;
            transition: all 0.5s;
        }
        .cat-card:hover .cat-icon { transform: scale(1.25) rotate(12deg); filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3)); }
        .cat-name {
            position: relative; color: #1e3a5f; font-weight: 700;
            font-size: 1.125rem; margin-bottom: 8px;
            transition: color 0.3s;
        }
        .cat-card:hover .cat-name { color: #2563eb; }
        .cat-desc { position: relative; color: #6b7280; font-size: 0.875rem; opacity: 0.7; transition: opacity 0.3s; }
        .cat-card:hover .cat-desc { opacity: 1; }

        /* ============ STEP CARDS ============ */
        .steps-wrapper { position: relative; }
        .steps-line {
            display: none; position: absolute; top: 112px; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(to right, transparent, #facc15, transparent);
        }
        @media (min-width: 769px) { .steps-line { display: block; } }

        .step-card {
            height: 100%;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 16px; padding: 32px; text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .step-card:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 60px rgba(37,99,235,0.15);
        }
        .step-num-wrap { position: relative; display: inline-block; margin-bottom: 20px; }
        .step-num-glow { position: absolute; inset: 0; filter: blur(20px); opacity: 0.5; }
        .step-num {
            position: relative; width: 80px; height: 80px;
            border-radius: 16px; display: flex; align-items: center; justify-content: center;
            font-size: 1.875rem; font-weight: 900; color: #fff;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            border: 4px solid rgba(255,255,255,0.2);
        }
        .step-num.blue { background: linear-gradient(to right, #60a5fa, #06b6d4); }
        .step-num.pink { background: linear-gradient(to right, #c084fc, #ec4899); }
        .step-num.orange { background: linear-gradient(to right, #fb923c, #ef4444); }
        .step-num.green-step { background: linear-gradient(to right, #4ade80, #10b981); }

        .step-icon { font-size: 3rem; margin-bottom: 16px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3)); }
        .step-title { color: #1e3a5f; font-weight: 700; font-size: 1.25rem; margin-bottom: 12px; }
        .step-desc { color: #6b7280; font-size: 0.875rem; line-height: 1.6; }

        /* ============ CTA SECTION ============ */
        .cta-orb {
            position: absolute; width: 384px; height: 384px;
            background: rgba(250, 204, 21, 0.1);
            border-radius: 50%; filter: blur(80px);
            animation: pulse 2s ease-in-out infinite;
        }
        .cta-orb-tl { top: 0; left: 0; }
        .cta-orb-br { bottom: 0; right: 0; animation-delay: 1s; }
        @keyframes pulse { 0%, 100% { opacity: 0.5; } 50% { opacity: 1; } }

        .cta-house { font-size: 4.5rem; margin-bottom: 16px; animation: bounce-slow 2s ease-in-out infinite; }
        .cta-title {
            font-size: clamp(2rem, 4vw, 3.5rem); font-weight: 700;
            background: linear-gradient(to right, #1e3a5f, #2563eb, #1e3a5f);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; line-height: 1.2; margin-bottom: 24px;
        }
        .cta-desc {
            font-size: clamp(1rem, 2vw, 1.25rem); color: #4b5563;
            max-width: 720px; margin: 0 auto 40px; line-height: 1.8;
        }
        .cta-desc .highlight { color: #2563eb; font-weight: 700; -webkit-text-fill-color: #2563eb; }
        .cta-quote { color: #6b7280; font-size: 0.875rem; font-style: italic; margin-top: 20px; }

        .btn-gradient-wrapper {
            position: relative; display: inline-block;
        }
        .btn-gradient-wrapper::before {
            content: ""; position: absolute; inset: -3px;
            background: linear-gradient(to right, #60a5fa, #f59e0b);
            border-radius: 9999px; opacity: 0.8; filter: blur(3px);
            transition: all 0.3s ease; z-index: -1;
        }
        .btn-gradient-wrapper:hover::before { opacity: 1; filter: blur(4px); inset: -4px; }
        .btn-gradient {
            position: relative; display: inline-flex; align-items: center; justify-content: center;
            border-radius: 9999px; font-weight: 700;
            color: #2563eb !important; background: #fff !important; text-decoration: none !important;
            transition: all 0.3s ease; border: none; outline: none; cursor: pointer;
        }
        .btn-gradient:hover { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    </style>
@endpush
@section('page')
<div class="page-wrapper">
<div class="fixed inset-0 overflow-hidden z-0" id="premium-bg">
<canvas id="abstract-canvas" class="w-full h-full absolute inset-0"></canvas>
</div>

<!-- ==================== BACKGROUND CANVAS ==================== -->
<div id="premium-bg" style="position: fixed; inset: 0; z-index: 0; pointer-events: none;">
    <canvas id="abstract-canvas" style="width: 100%; height: 100%; position: absolute; inset: 0;"></canvas>
</div>


<!-- ==================== HERO ==================== -->
<section id="home" class="hero">
    <div class="hero-bg">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    <div class="hero-content container mx-auto px-4 md:px-6 text-center">
        

        <h1 class="hero-title animate-fade-in-up">
            <span class="hero-title-gold">Sampaikan Aspirasi</span>
            <span class="hero-subtitle">Dengan Hormat dan Sopan</span>
        </h1>

        <p class="hero-desc animate-fade-in-up delay-100">
            <span class="highlight">Suara Anda</span> adalah kunci kemajuan lingkungan kita bersama.
        </p>

        <div class="hero-buttons animate-fade-in-up delay-200">
            @guest
                <div class="btn-gradient-wrapper">
                    <a href="{{ url('/auth') }}" class="btn-gradient" style="padding: 20px 40px; font-size: 1.25rem;">
                        <span>Laporkan Keluhan</span>
                    </a>
                </div>
                <a href="{{ url('/auth') }}" class="btn-outline">
                    <span>Daftar Sekarang &rarr;</span>
                </a>
            @else
                <div class="btn-gradient-wrapper">
                    <a href="{{ route('user.laporan.index') }}" class="btn-gradient" style="padding: 24px 48px; font-size: 1.5rem;">
                        <span>Buat Laporan Sekarang</span>
                    </a>
                </div>
            @endguest
        </div>

        @guest
            <p class="hero-register animate-fade-in-up delay-300">
                Belum punya akun? <a href="{{ url('/auth') }}">Daftar sekarang</a>
            </p>
        @endguest

        <div class="scroll-down">
            <a href="#statistik" class="nav-scroll">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                <span class="text">Gulir ke bawah</span>
            </a>
        </div>
    </div>
</section>

<!-- ==================== STATISTIK ==================== -->
<section id="statistik" class="section section-stats">
    <div class="blur-orb blur-orb-tl"></div>
    <div class="blur-orb blur-orb-br"></div>
    <div class="container mx-auto px-4 md:px-6 relative" style="z-index:10">
        <div class="text-center mb-16">
            <h2 class="section-title">Statistik Real-Time</h2>
            <p class="section-subtitle">Transparansi data pengaduan warga</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @php
                $totalLaporan = \App\Models\Laporan::count() ?? 0;
                $menunggu = \App\Models\Laporan::where('status', 'Pending')->count() ?? 0;
                $proses = \App\Models\Laporan::whereIn('status', ['Proses','Diproses'])->count() ?? 0;
                $selesai = \App\Models\Laporan::where('status', 'Selesai')->count() ?? 0;
            @endphp
            <!-- Total Laporan -->
            <div class="stat-card">
                <div class="stat-icon-wrap">
                    <div class="stat-icon-glow" style="background: linear-gradient(to right,#60a5fa,#2563eb)"></div>
                    <div class="stat-icon blue"><img src="{{ asset('User/img/pelaporanicon/laporkankeluhan.png') }}" alt="Icon" class="w-12 h-12 object-contain"></div>
                </div>
                <div class="stat-value blue counter" data-target="{{ $totalLaporan }}">0</div>
                <div class="stat-label">Total Laporan</div>
                <div class="stat-bar"><div class="stat-bar-fill blue"></div></div>
            </div>
            <!-- Menunggu -->
            <div class="stat-card">
                <div class="stat-icon-wrap">
                    <div class="stat-icon-glow" style="background: linear-gradient(to right,#facc15,#f97316)"></div>
                    <div class="stat-icon yellow"><img src="{{ asset('User/img/pelaporanicon/menunggu1.png') }}" alt="Icon" class="w-12 h-12 object-contain"></div>
                </div>
                <div class="stat-value yellow counter" data-target="{{ $menunggu }}">0</div>
                <div class="stat-label">Menunggu</div>
                <div class="stat-bar"><div class="stat-bar-fill yellow"></div></div>
            </div>
            <!-- Dalam Proses -->
            <div class="stat-card">
                <div class="stat-icon-wrap">
                    <div class="stat-icon-glow" style="background: linear-gradient(to right,#c084fc,#ec4899)"></div>
                    <div class="stat-icon purple"><img src="{{ asset('User/img/pelaporanicon/dalamproses1.png') }}" alt="Icon" class="w-12 h-12 object-contain"></div>
                </div>
                <div class="stat-value purple counter" data-target="{{ $proses }}">0</div>
                <div class="stat-label">Dalam Proses</div>
                <div class="stat-bar"><div class="stat-bar-fill purple"></div></div>
            </div>
            <!-- Selesai -->
            <div class="stat-card">
                <div class="stat-icon-wrap">
                    <div class="stat-icon-glow" style="background: linear-gradient(to right,#4ade80,#059669)"></div>
                    <div class="stat-icon green"><img src="{{ asset('User/img/pelaporanicon/selesai.png') }}" alt="Icon" class="w-12 h-12 object-contain"></div>
                </div>
                <div class="stat-value green counter" data-target="{{ $selesai }}">0</div>
                <div class="stat-label">Selesai</div>
                <div class="stat-bar"><div class="stat-bar-fill green"></div></div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== KATEGORI ==================== -->
<section id="kategori" class="section section-kategori">
    <div class="container mx-auto px-4 md:px-6">
        <div class="text-center mb-16">
            <h2 class="section-title">Kategori Pengaduan</h2>
            <p class="section-subtitle">Pilih kategori sesuai keluhan Anda</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="cat-card"><div class="cat-icon"><img src="{{ asset('User/img/pelaporanicon/kebersihan.png') }}" alt="Kebersihan" class="h-20 w-auto object-contain mx-auto inline-block"></div><div class="cat-name">Kebersihan</div><div class="cat-desc">Sampah, Parit, Kebersihan</div></div>
            <div class="cat-card"><div class="cat-icon"><img src="{{ asset('User/img/pelaporanicon/keselamatan.png') }}" alt="Keselamatan" class="h-20 w-auto object-contain mx-auto inline-block"></div><div class="cat-name">Keselamatan</div><div class="cat-desc">Kemalangan, Jenayah</div></div>
            <div class="cat-card"><div class="cat-icon"><img src="{{ asset('User/img/pelaporanicon/infrastruktur.png') }}" alt="Infrastruktur" class="h-20 w-auto object-contain mx-auto inline-block"></div><div class="cat-name">Infrastruktur</div><div class="cat-desc">Jalan, Lampu, Bangunan</div></div>
            <div class="cat-card"><div class="cat-icon"><img src="{{ asset('User/img/pelaporanicon/kesehatan.png') }}" alt="Kesehatan" class="h-20 w-auto object-contain mx-auto inline-block"></div><div class="cat-name">Kesehatan</div><div class="cat-desc">Layanan Medis, Sanitasi</div></div>
            <div class="cat-card"><div class="cat-icon"><img src="{{ asset('User/img/pelaporanicon/lingkungan.png') }}" alt="Lingkungan" class="h-20 w-auto object-contain mx-auto inline-block"></div><div class="cat-name">Lingkungan</div><div class="cat-desc">Pencemaran, Banjir</div></div>
            <div class="cat-card"><div class="cat-icon"><img src="{{ asset('User/img/pelaporanicon/fasilitas.png') }}" alt="Fasilitas" class="h-20 w-auto object-contain mx-auto inline-block"></div><div class="cat-name">Fasilitas</div><div class="cat-desc">Balai, Taman, Rumah Ibadah</div></div>
            <div class="cat-card"><div class="cat-icon"><img src="{{ asset('User/img/pelaporanicon/lainnya.png') }}" alt="Lainnya" class="h-20 w-auto object-contain mx-auto inline-block"></div><div class="cat-name">Lainnya</div><div class="cat-desc">Pengaduan Umum</div></div>
        </div>
    </div>
</section>

<!-- ==================== CARA MELAPOR ==================== -->
<section id="cara" class="section section-cara">
    <div class="container mx-auto px-4 md:px-6">
        <div class="text-center mb-16">
            <h2 class="section-title">Cara Membuat Laporan</h2>
            <p class="section-subtitle">Proses mudah dalam 4 langkah sederhana</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 steps-wrapper">
            <div class="steps-line"></div>
            <div><div class="step-card">
                <div class="step-num-wrap"><div class="step-num-glow" style="background:linear-gradient(to right,#60a5fa,#06b6d4)"></div><div class="step-num blue">1</div></div>
                <h3 class="step-title">Daftar / Masuk</h3><p class="step-desc">Buat akun atau login ke sistem kami</p>
            </div></div>
            <div><div class="step-card">
                <div class="step-num-wrap"><div class="step-num-glow" style="background:linear-gradient(to right,#c084fc,#ec4899)"></div><div class="step-num pink">2</div></div>
                <h3 class="step-title">Isi Formulir</h3><p class="step-desc">Lengkapi detail pengaduan dengan jelas</p>
            </div></div>
            <div><div class="step-card">
                <div class="step-num-wrap"><div class="step-num-glow" style="background:linear-gradient(to right,#fb923c,#ef4444)"></div><div class="step-num orange">3</div></div>
                <h3 class="step-title">Upload Bukti</h3><p class="step-desc">Lampirkan foto atau dokumen pendukung</p>
            </div></div>
            <div><div class="step-card">
                <div class="step-num-wrap"><div class="step-num-glow" style="background:linear-gradient(to right,#4ade80,#10b981)"></div><div class="step-num green-step">4</div></div>
                <h3 class="step-title">Submit</h3><p class="step-desc">Kirim dan pantau status laporan Anda</p>
            </div></div>
        </div>
    </div>
</section>

<!-- ==================== CTA ==================== -->
<section class="section section-cta">
    <div class="cta-orb cta-orb-tl"></div>
    <div class="cta-orb cta-orb-br"></div>
    <div class="container mx-auto px-4 md:px-6 text-center relative" style="z-index:10">
        <div class="cta-house"><img src="{{ asset('User/img/pelaporanicon/17.png') }}" alt="Icon" class="h-20 w-auto object-contain mx-auto inline-block"></div>
        <h2 class="cta-title">Mari Bersama Membangun<br>Lingkungan yang Lebih Baik</h2>
        <p class="cta-desc">
            Suara Anda adalah kunci kemajuan. Bersama kita wujudkan.
        </p>
        <div class="hero-buttons animate-fade-in-up delay-200">
            @guest
                <div class="btn-gradient-wrapper">
                    <a href="{{ route('user.laporan.index') }}" class="btn-gradient" style="padding: 20px 40px; font-size: 1.25rem;">
                        <span>Mulai Sekarang</span>
                    </a>
                </div>
                <a href="{{ url('/auth') }}" class="btn-outline"><span>Login</span></a>
            @else
                <div class="btn-gradient-wrapper">
                    <a href="{{ route('user.laporan.index') }}" class="btn-gradient" style="padding: 24px 48px; font-size: 1.5rem;">
                        <span>Buat Laporan Sekarang</span>
                    </a>
                </div>
            @endguest
        </div>
        <p class="cta-quote"></p>
    </div>
</section>

<!-- ==================== SCRIPTS ==================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth Scroll
    document.querySelectorAll('.nav-scroll').forEach(a => {
        a.addEventListener('click', function(e) {
            e.preventDefault();
            const t = document.querySelector(this.getAttribute('href'));
            if (t) t.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Counter animation
    const counters = document.querySelectorAll('.counter');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.getAttribute('data-target')) || 0;
                let current = 0;
                const step = Math.max(1, Math.ceil(target / 50));
                
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    el.textContent = current.toLocaleString('id-ID');
                }, 30);
                
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.5 });
    counters.forEach(c => observer.observe(c));

    // Fade-in on scroll (simple AOS replacement)
    const fadeEls = document.querySelectorAll('.stat-card, .cat-card, .step-card');
    fadeEls.forEach(el => { el.style.opacity = '0'; el.style.transform = 'translateY(30px)'; el.style.transition = 'all 0.6s ease'; });
    const fadeObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => { entry.target.style.opacity = '1'; entry.target.style.transform = 'translateY(0)'; }, i * 100);
                fadeObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    fadeEls.forEach(el => fadeObserver.observe(el));
});
</script>

</div>
@endsection
@push('scripts')
<script>
    // Canvas Vector Abstract Background Script
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('abstract-canvas');
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

        window.addEventListener('mousemove', (e) => {
            targetMouse.x = e.clientX;
            targetMouse.y = e.clientY;
        });
        window.addEventListener('mouseout', () => {
            targetMouse.x = -1000;
            targetMouse.y = -1000;
        });

        let scrollY = window.scrollY;
        window.addEventListener('scroll', () => {
            scrollY = window.scrollY;
        });

        class Wave {
            constructor(getGradient, yOffset, amplitude, speed, wavelength) {
                this.getGradient = getGradient;
                this.yOffset = yOffset; 
                this.amplitude = amplitude; 
                this.speed = speed; 
                this.wavelength = wavelength; 
                this.points = [];
                this.time = Math.random() * 100;
            }

            init() {
                this.points = [];
                let numPoints = Math.ceil(width / 25) + 2; // Resolusi tinggi agar kursor presisi
                for(let i = 0; i < numPoints; i++) {
                    let startX = (i - 1) * 25;
                    let startBaseY = height * this.yOffset;
                    let startY = startBaseY + Math.sin(this.time + startX / this.wavelength) * this.amplitude;
                    this.points.push({
                        x: startX,
                        baseY: startBaseY,
                        y: startY,
                        vy: 0,
                        spring: 0.05, 
                        friction: 0.90 
                    });
                }
            }

            update() {
                this.time += this.speed;
                for(let i = 0; i < this.points.length; i++) {
                    let pt = this.points[i];
                    
                    // Gerakan gelombang natural
                    let targetY = pt.baseY + Math.sin(this.time + pt.x / this.wavelength) * this.amplitude;
                    
                    // Interaksi Kursor: Menyebar saat disentuh
                    let dx = mouse.x - pt.x;
                    let dy = mouse.y - targetY;
                    let distance = Math.sqrt(dx*dx + dy*dy);
                    
                    if (distance < 200) {
                        let force = Math.pow((200 - distance) / 200, 2); 
                        let pushDir = (dy > 0) ? -1 : 1; 
                        targetY += pushDir * force * 60; // Dorongan diperhalus agar tidak terlalu liar
                    }
                    
                    let forceY = (targetY - pt.y) * pt.spring;
                    pt.vy += forceY;
                    pt.vy *= pt.friction;
                    pt.y += pt.vy;
                }
            }

            draw() {
                ctx.beginPath();
                ctx.moveTo(this.points[0].x, this.points[0].y);
                
                for(let i = 0; i < this.points.length - 1; i++) {
                    let cx = (this.points[i].x + this.points[i+1].x) / 2;
                    let cy = (this.points[i].y + this.points[i+1].y) / 2;
                    ctx.quadraticCurveTo(this.points[i].x, this.points[i].y, cx, cy);
                }
                
                let last = this.points[this.points.length - 1];
                ctx.lineTo(last.x, last.y);
                // Gambar ekstra jauh ke bawah agar saat di-scroll ke atas tidak terpotong bolong
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
                // 1. Biru Muda (Diturunkan dan diperlambat agar lebih tenang)
                new Wave((ctx, w, h) => {
                    let grad = ctx.createLinearGradient(0, h*0.5, 0, h*1.2);
                    grad.addColorStop(0, 'rgba(140, 190, 250, 0.7)');
                    grad.addColorStop(1, 'rgba(180, 215, 255, 0.1)');
                    return grad;
                }, 0.65, 40, 0.005, 600),

                // 2. Putih Solid (Pemisah)
                new Wave((ctx, w, h) => {
                    let grad = ctx.createLinearGradient(0, h*0.6, 0, h*1.2);
                    grad.addColorStop(0, 'rgba(255, 255, 255, 1)');
                    grad.addColorStop(1, 'rgba(245, 250, 255, 0.5)');
                    return grad;
                }, 0.75, 30, 0.003, 500),

                // 3. Kuning Amber (Lebih pudar dan gradasi halus ke putih transparan)
                new Wave((ctx, w, h) => {
                    let grad = ctx.createLinearGradient(0, h*0.7, 0, h*1.1);
                    grad.addColorStop(0, 'rgba(245, 225, 130, 0.5)'); // Agak pudar di puncak
                    grad.addColorStop(1, 'rgba(255, 255, 255, 0)'); // Pudar sempurna ke transparan
                    return grad;
                }, 0.85, 45, 0.007, 700)
            ];
            waves.forEach(w => w.init());
        }

        function animate() {
            // Lerp mouse
            mouse.x += (targetMouse.x - mouse.x) * 0.1;
            mouse.y += (targetMouse.y - mouse.y) * 0.1;

            // Background layer solid (agar saat parallax tidak bolong)
            ctx.fillStyle = '#e8eff5'; 
            ctx.fillRect(0, 0, width, height);

            ctx.save();
            // Terapkan Parallax Scrolling (Background bergerak 40% kecepatan scroll content)
            ctx.translate(0, -scrollY * 0.4); 

            // Cahaya Matahari Halus (Kiri) - Diperhalus
            let glowX = width * 0.15;
            let glowY = height * 0.4;
            let gradGlow = ctx.createRadialGradient(glowX, glowY, 0, glowX, glowY, width * 0.3);
            gradGlow.addColorStop(0, 'rgba(245, 235, 150, 0.15)'); // Opasitas diturunkan
            gradGlow.addColorStop(1, 'rgba(245, 235, 150, 0)');
            ctx.fillStyle = gradGlow;
            ctx.beginPath();
            ctx.arc(glowX, glowY, width * 0.3, 0, Math.PI*2);
            ctx.fill();

            // Gambar ombak-ombak
            waves.forEach(w => {
                w.update();
                w.draw();
            });

            // Ikon Wajik (Kanan Atas) - Dibuat lebih kecil & pudar agar tidak mendominasi
            ctx.save();
            ctx.translate(width * 0.9, height * 0.08);
            
            // Parallax menjauh dari kursor
            let dxD = mouse.x - (width * 0.9);
            let dyD = mouse.y - (height * 0.08);
            let distD = Math.sqrt(dxD*dxD + dyD*dyD);
            if(distD < 300) {
                let f = (300 - distD)/300;
                ctx.translate(-(dxD/distD)*f*20, -(dyD/distD)*f*20);
            }

            ctx.rotate(Math.PI / 4);
            
            ctx.fillStyle = 'rgba(74, 144, 226, 0.4)';
            ctx.fillRect(-15, -15, 30, 30);
            
            ctx.fillStyle = 'rgba(120, 175, 240, 0.3)';
            ctx.fillRect(5, 5, 25, 25);
            
            ctx.strokeStyle = 'rgba(150, 190, 250, 0.4)';
            ctx.lineWidth = 1.5;
            ctx.strokeRect(20, 20, 15, 15);

            ctx.restore(); // Restore efek rotasi wajik
            ctx.restore(); // Restore efek Parallax Scroll

            requestAnimationFrame(animate);
        }

        resize();
        animate();
    });
</script>
@endpush















































