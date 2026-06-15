<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengaduan Warga - Kelurahan Sungai Pakning</title>
    <link rel="icon" type="image/png" href="{{ asset('Admin/img/illustrations/logodomain.png') }}?v={{ time() }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* ============ RESET & BASE ============ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f0f4f8 url('{{ asset("Admin/img/elements/background.png") }}') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        a { text-decoration: none; color: inherit; }
        img { max-width: 100%; }

        /* ============ UTILITIES ============ */
        .container { max-width: 1280px; margin: 0 auto; padding: 0 24px; }
        .text-center { text-align: center; }
        .relative { position: relative; }
        .overflow-hidden { overflow: hidden; }
        .inline-block { display: inline-block; }
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .gap-5 { gap: 20px; }
        .gap-3 { gap: 12px; }
        .gap-2 { gap: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .mb-6 { margin-bottom: 24px; }
        .mb-8 { margin-bottom: 32px; }
        .mb-10 { margin-bottom: 40px; }
        .mb-16 { margin-bottom: 64px; }
        .mt-4 { margin-top: 16px; }
        .mt-5 { margin-top: 20px; }
        .mt-8 { margin-top: 32px; }
        .mt-20 { margin-top: 80px; }

        /* ============ GRID ============ */
        .grid { display: grid; gap: 24px; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        @media (max-width: 768px) {
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-3 { grid-template-columns: 1fr; }
            .hero-buttons { flex-direction: column; }
        }

        /* ============ SILA DESBENG NAVBAR ============ */
        .sd-navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 50;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            padding: 0; transition: transform 0.3s ease-in-out;
        }
        .sd-navbar.hidden-nav {
            transform: translateY(-100%);
        }
        .sd-navbar-toggle {
            position: absolute; bottom: -28px; right: 32px;
            width: 40px; height: 28px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: #38bdf8; z-index: 51;
            transition: all 0.3s;
        }
        .sd-navbar-toggle:hover { color: #0284c7; }
        .sd-navbar-toggle svg { width: 32px; height: 32px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1)); }
        .sd-nav-container {
            max-width: 1536px; margin: 0 auto; padding: 0 20px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .sd-nav-logo img { height: 80px; width: auto; object-fit: contain; padding: 8px 0; }
        @media (min-width: 640px) { .sd-nav-logo img { height: 96px; } }
        .sd-nav-links { display: flex; align-items: center; gap: 32px; margin-left: auto; margin-right: 32px; }
        @media (max-width: 768px) { .sd-nav-links { display: none; } }
        .sd-nav-link {
            font-size: 15px; font-weight: 500; color: #111827;
            text-decoration: none; transition: color 0.2s;
            padding-bottom: 2px;
        }
        .sd-nav-link:hover { color: #2563eb; }
        
        .sd-nav-auth { display: flex; align-items: center; gap: 12px; }
        @media (max-width: 768px) { .sd-nav-auth { display: none; } }
        
        .sd-btn-login-wrapper {
            position: relative; display: inline-block; padding: 2px;
            border-radius: 9999px; background: linear-gradient(to right, #60a5fa, #f59e0b);
            opacity: 0.8; transition: opacity 0.3s;
        }
        .sd-btn-login-wrapper:hover { opacity: 1; }
        .sd-btn-login {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 10px 40px; border-radius: 9999px; font-size: 15px; font-weight: 500;
            color: #2563eb; background: #fff; text-decoration: none;
        }
        
        .sd-btn-register {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 12px 40px; border-radius: 9999px; font-size: 15px; font-weight: 500;
            color: #fff; text-decoration: none; border: none; cursor: pointer;
            background: linear-gradient(to right, #7dc8f0, #45aaf2);
            transition: all 0.3s;
        }
        .sd-btn-register:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.15); transform: translateY(-1px); }

        .sd-nav-user { display: flex; align-items: center; gap: 10px; cursor: pointer; text-decoration: none; }
        .sd-nav-user-name { font-weight: 700; color: #111827; font-size: 16px; transition: color 0.2s; }
        .sd-nav-user:hover .sd-nav-user-name { color: #2563eb; }
        .sd-nav-user-avatar {
            width: 44px; height: 44px; border-radius: 50%; background: #d1d5db;
            overflow: hidden; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .sd-nav-user-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .sd-nav-user-avatar svg { width: 28px; height: 28px; fill: #fff; }

        /* ============ HERO SECTION ============ */
        .hero {
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
            border: 3px solid #2563eb; border-radius: 16px;
            cursor: pointer; transition: all 0.5s ease;
            position: relative; overflow: hidden;
        }
        .btn-outline::before {
            content: ''; position: absolute; inset: 0;
            background: #2563eb; transform: translateY(100%);
            transition: transform 0.5s ease;
        }
        .btn-outline:hover { color: #fff; }
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
        .section { padding: 96px 0; position: relative; }
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

        /* ============ FOOTER (SILA DESBENG) ============ */
        .footer {
            background: #115789;
            color: #fff;
            padding: 40px 0 24px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .footer-grid {
            display: grid; grid-template-columns: 1fr 1fr 1fr;
            gap: 32px; margin-bottom: 0;
        }
        @media (max-width: 768px) { .footer-grid { grid-template-columns: 1fr; text-align: center; } }
        .footer-col-logo { display: flex; flex-direction: column; align-items: center; }
        @media (min-width: 769px) { .footer-col-logo { align-items: flex-start; margin-top: -40px; } }
        .footer-col-logo img { height: auto; max-height: 120px; width: auto; object-fit: contain; }
        .footer-col-logo img + img { margin-top: -16px; }
        .footer-col-nav { display: flex; flex-direction: column; align-items: center; gap: 12px; padding-top: 8px; }
        .footer-col-nav a {
            font-size: 1rem; font-weight: 500; color: #fff;
            transition: color 0.2s;
        }
        .footer-col-nav a:hover { color: #93c5fd; }
        .footer-col-contact {
            display: flex; flex-direction: column; gap: 16px;
            align-items: center; padding-top: 8px;
        }
        @media (min-width: 769px) { .footer-col-contact { align-items: flex-end; } }
        .footer-contact-item {
            display: flex; align-items: center; gap: 12px;
            font-size: 0.9375rem; color: #fff;
        }
        @media (min-width: 769px) { .footer-contact-item { flex-direction: row-reverse; text-align: right; } }
        .footer-contact-item:hover { color: #93c5fd; }
        .footer-contact-icon {
            background: rgba(255,255,255,0.1); padding: 8px;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            transition: background 0.3s;
        }
        .footer-contact-item:hover .footer-contact-icon { background: rgba(255,255,255,0.2); }
        .footer-contact-icon svg { width: 18px; height: 18px; fill: none; stroke: currentColor; }
        .footer-socials {
            display: flex; gap: 10px; padding-top: 8px;
            justify-content: center;
        }
        @media (min-width: 769px) { .footer-socials { justify-content: flex-end; } }
        .footer-social-btn {
            background: #fff; color: #115789; padding: 8px;
            border-radius: 6px; display: flex; align-items: center; justify-content: center;
            transition: all 0.3s;
        }
        .footer-social-btn:hover { background: #dbeafe; transform: translateY(-3px); }
        .footer-social-btn svg { width: 18px; height: 18px; fill: currentColor; }
        .footer-divider {
            border: none; border-top: 1px solid rgba(255,255,255,0.2);
            margin: 24px 0;
        }
        .footer-bottom {
            text-align: center; font-size: 0.875rem; color: #e5e7eb;
            font-weight: 500; letter-spacing: 0.025em;
        }
    </style>
</head>
<body>

<!-- ==================== SILA DESBENG NAVBAR ==================== -->
<nav class="sd-navbar" id="sdNavbar">
    <div class="sd-nav-container">
        <!-- Logo -->
        <a href="{{ route('beranda') }}" class="sd-nav-logo">
            <img src="{{ asset('User/img/logo/iSewa.png') }}" alt="SidesBeng Logo">
        </a>

        <!-- Menu Desktop -->
        <div class="sd-nav-links">
            <a href="{{ route('beranda') }}" class="sd-nav-link">Beranda</a>
            <a href="{{ route('pelayanan') }}" class="sd-nav-link">Pelayanan</a>
            <a href="{{ route('bumdes.profil') }}" class="sd-nav-link">BUMDes</a>
            <a href="{{ route('isewa.profile') }}" class="sd-nav-link">Profil SidesBeng</a>
        </div>

        <!-- Auth Buttons / User Profile -->
        <div class="sd-nav-auth">
            @auth
                <a href="{{ route('profile') }}" class="sd-nav-user">
                    <span class="sd-nav-user-name">{{ auth()->user()->name }}</span>
                    <div class="sd-nav-user-avatar">
                        @if (auth()->user()->file)
                            <img src="{{ auth()->user()->file->file_stream }}" alt="Avatar">
                        @else
                            <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        @endif
                    </div>
                </a>
            @else
                <div class="sd-btn-login-wrapper">
                    <a href="{{ url('/auth') }}" class="sd-btn-login">Masuk</a>
                </div>
                <a href="{{ url('/auth') }}" class="sd-btn-register">Daftar</a>
            @endauth
        </div>
    </div>
    
    <!-- Toggle Button -->
    <div class="sd-navbar-toggle" id="sdNavbarToggle">
        <svg id="sdIconUp" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 8l-6 6h12l-6-6z"/>
        </svg>
        <svg id="sdIconDown" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
            <path d="M12 16l6-6H6l6 6z"/>
        </svg>
    </div>
</nav>

<!-- ==================== HERO ==================== -->
<section id="home" class="hero">
    <div class="hero-bg">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    <div class="hero-content container text-center">
        <div class="mb-6 inline-block">
            <span class="hero-badge">
                <span style="font-size:1.25rem" class="animate-pulse-emoji">🌺</span>
                Adat Bersendang, Syarak Bersendeng
                <span style="font-size:1.25rem" class="animate-pulse-emoji">🌺</span>
            </span>
        </div>

        <h1 class="hero-title">
            <span class="hero-title-gold">Sampaikan Aspirasi</span>
            <span class="hero-subtitle">Dengan Hormat dan Sopan</span>
        </h1>

        <p class="hero-desc">
            Platform pengaduan warga yang menghormati nilai-nilai budaya Melayu.<br>
            <span class="highlight">Suara Anda</span> adalah kunci kemajuan lingkungan kita bersama.
        </p>

        <div class="hero-buttons">
            @guest
                <a href="{{ url('/auth') }}" class="btn-primary">
                    <span class="icon">📝</span>
                    <span>Laporkan Keluhan</span>
                </a>
                <a href="{{ url('/auth') }}" class="btn-outline">
                    <span>Daftar Sekarang →</span>
                </a>
            @else
                <a href="{{ route('user.laporan.index') }}" class="btn-primary" style="padding: 24px 48px; font-size: 1.5rem;">
                    <span class="icon" style="font-size: 1.875rem;">📝</span>
                    <span>Buat Laporan Sekarang</span>
                </a>
            @endguest
        </div>

        @guest
            <p class="hero-register">
                Belum punya akun? <a href="{{ url('/auth') }}">Daftar gratis sekarang</a>
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
    <div class="container relative" style="z-index:10">
        <div class="text-center mb-16">
            <h2 class="section-title">📊 Statistik Real-Time</h2>
            <p class="section-subtitle">Transparansi data pengaduan warga</p>
        </div>
        <div class="grid grid-4">
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
                    <div class="stat-icon blue">📋</div>
                </div>
                <div class="stat-value blue counter" data-target="{{ $totalLaporan }}">0</div>
                <div class="stat-label">Total Laporan</div>
                <div class="stat-bar"><div class="stat-bar-fill blue"></div></div>
            </div>
            <!-- Menunggu -->
            <div class="stat-card">
                <div class="stat-icon-wrap">
                    <div class="stat-icon-glow" style="background: linear-gradient(to right,#facc15,#f97316)"></div>
                    <div class="stat-icon yellow">⏳</div>
                </div>
                <div class="stat-value yellow counter" data-target="{{ $menunggu }}">0</div>
                <div class="stat-label">Menunggu</div>
                <div class="stat-bar"><div class="stat-bar-fill yellow"></div></div>
            </div>
            <!-- Dalam Proses -->
            <div class="stat-card">
                <div class="stat-icon-wrap">
                    <div class="stat-icon-glow" style="background: linear-gradient(to right,#c084fc,#ec4899)"></div>
                    <div class="stat-icon purple">🔄</div>
                </div>
                <div class="stat-value purple counter" data-target="{{ $proses }}">0</div>
                <div class="stat-label">Dalam Proses</div>
                <div class="stat-bar"><div class="stat-bar-fill purple"></div></div>
            </div>
            <!-- Selesai -->
            <div class="stat-card">
                <div class="stat-icon-wrap">
                    <div class="stat-icon-glow" style="background: linear-gradient(to right,#4ade80,#059669)"></div>
                    <div class="stat-icon green">✅</div>
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
    <div class="container">
        <div class="text-center mb-16">
            <h2 class="section-title">📂 Kategori Pengaduan</h2>
            <p class="section-subtitle">Pilih kategori sesuai keluhan Anda</p>
        </div>
        <div class="grid grid-4">
            <div class="cat-card"><div class="cat-icon">🧹</div><div class="cat-name">Kebersihan</div><div class="cat-desc">Sampah, parit, kebersihan</div></div>
            <div class="cat-card"><div class="cat-icon">🚨</div><div class="cat-name">Keselamatan</div><div class="cat-desc">Kemalangan, jenayah</div></div>
            <div class="cat-card"><div class="cat-icon">🏗️</div><div class="cat-name">Infrastruktur</div><div class="cat-desc">Jalan, lampu, bangunan</div></div>
            <div class="cat-card"><div class="cat-icon">🏥</div><div class="cat-name">Kesehatan</div><div class="cat-desc">Layanan medis, sanitasi</div></div>
            <div class="cat-card"><div class="cat-icon">🌳</div><div class="cat-name">Lingkungan</div><div class="cat-desc">Pencemaran, banjir</div></div>
            <div class="cat-card"><div class="cat-icon">🏢</div><div class="cat-name">Fasilitas</div><div class="cat-desc">Balai, taman, masjid</div></div>
            <div class="cat-card"><div class="cat-icon">📝</div><div class="cat-name">Administrasi</div><div class="cat-desc">Dokumen, surat</div></div>
            <div class="cat-card"><div class="cat-icon">📦</div><div class="cat-name">Lainnya</div><div class="cat-desc">Pengaduan umum</div></div>
        </div>
    </div>
</section>

<!-- ==================== CARA MELAPOR ==================== -->
<section id="cara" class="section section-cara">
    <div class="container">
        <div class="text-center mb-16">
            <h2 class="section-title">📖 Cara Membuat Laporan</h2>
            <p class="section-subtitle">Proses mudah dalam 4 langkah sederhana</p>
        </div>
        <div class="grid grid-4 steps-wrapper">
            <div class="steps-line"></div>
            <div><div class="step-card">
                <div class="step-num-wrap"><div class="step-num-glow" style="background:linear-gradient(to right,#60a5fa,#06b6d4)"></div><div class="step-num blue">1</div></div>
                <div class="step-icon">👤</div><h3 class="step-title">Daftar / Masuk</h3><p class="step-desc">Buat akun atau login ke sistem kami</p>
            </div></div>
            <div><div class="step-card">
                <div class="step-num-wrap"><div class="step-num-glow" style="background:linear-gradient(to right,#c084fc,#ec4899)"></div><div class="step-num pink">2</div></div>
                <div class="step-icon">📝</div><h3 class="step-title">Isi Formulir</h3><p class="step-desc">Lengkapi detail pengaduan dengan jelas</p>
            </div></div>
            <div><div class="step-card">
                <div class="step-num-wrap"><div class="step-num-glow" style="background:linear-gradient(to right,#fb923c,#ef4444)"></div><div class="step-num orange">3</div></div>
                <div class="step-icon">📸</div><h3 class="step-title">Upload Bukti</h3><p class="step-desc">Lampirkan foto atau dokumen pendukung</p>
            </div></div>
            <div><div class="step-card">
                <div class="step-num-wrap"><div class="step-num-glow" style="background:linear-gradient(to right,#4ade80,#10b981)"></div><div class="step-num green-step">4</div></div>
                <div class="step-icon">✅</div><h3 class="step-title">Submit</h3><p class="step-desc">Kirim dan pantau status laporan Anda</p>
            </div></div>
        </div>
    </div>
</section>

<!-- ==================== CTA ==================== -->
<section class="section section-cta">
    <div class="cta-orb cta-orb-tl"></div>
    <div class="cta-orb cta-orb-br"></div>
    <div class="container text-center relative" style="z-index:10">
        <div class="cta-house">🏡</div>
        <h2 class="cta-title">Mari Bersama Membangun<br>Lingkungan yang Lebih Baik</h2>
        <p class="cta-desc">
            Suara Anda adalah kunci kemajuan. Bersama kita wujudkan
            <span class="highlight">Sungai Pakning</span> yang lebih baik.
        </p>
        <div class="hero-buttons">
            @guest
                <a href="{{ route('user.laporan.index') }}" class="btn-primary">
                    <span class="icon">🚀</span><span>Mulai Sekarang</span>
                </a>
                <a href="{{ url('/auth') }}" class="btn-outline"><span>Login</span></a>
            @else
                <a href="{{ route('user.laporan.index') }}" class="btn-primary" style="padding:24px 48px;font-size:1.5rem;">
                    <span class="icon" style="font-size:1.875rem">📝</span><span>Buat Laporan Sekarang</span>
                </a>
            @endguest
        </div>
        <p class="cta-quote">"adat bersendi, syarak bersendi. Syarak mengata, adat memakai."</p>
    </div>
</section>

<!-- ==================== FOOTER (SILA DESBENG) ==================== -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Kolom 1: Logo -->
            <div class="footer-col-logo">
                <img src="{{ asset('User/img/logo/iSewaF.png') }}?v={{ time() }}" alt="SidesBeng Logo" style="transform: scale(1.3); transform-origin: center center;">
                <img src="{{ asset('User/img/logo/bklss.png') }}?v={{ time() }}" alt="Bengkalis Bermasa" style="transform: scale(2.4); transform-origin: center center; margin-top: 40px;">
            </div>

            <!-- Kolom 2: Navigasi -->
            <div class="footer-col-nav">
                <a href="{{ route('beranda') }}">Beranda</a>
                <a href="{{ route('pelayanan') }}">Pelayanan</a>
                <a href="{{ route('bumdes.profil') }}">BUMDes</a>
                <a href="{{ route('isewa.profile') }}">Profil SidesBeng</a>
            </div>

            <!-- Kolom 3: Kontak -->
            <div class="footer-col-contact">
                <a href="https://maps.app.goo.gl/77Vy8U9MWY8rJpys6" target="_blank" class="footer-contact-item">
                    <span class="footer-contact-icon">
                        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <span>Bengkalis, Riau, Indonesia</span>
                </a>
                <a href="mailto:sdesbengdigital@gmail.com" class="footer-contact-item">
                    <span class="footer-contact-icon">
                        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <span>sdesbengdigital@gmail.com</span>
                </a>
                <a href="https://wa.me/6282249213061" class="footer-contact-item">
                    <span class="footer-contact-icon">
                        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </span>
                    <span>(+62) 822-4921-3061</span>
                </a>
                <div class="footer-socials">
                    <a href="#" class="footer-social-btn"><svg viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/></svg></a>
                    <a href="#" class="footer-social-btn"><svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
                    <a href="https://www.instagram.com/isewa_id" target="_blank" class="footer-social-btn"><svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>
                    <a href="#" class="footer-social-btn"><svg viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a>
                </div>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} SISTEM PENYEWAAN ALAT DAN PROMOSI USAHA BUMDES BERBASIS DIGITAL</p>
        </div>
    </div>
</footer>

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

    // Navbar Toggle Logic
    const sdNavbar = document.getElementById('sdNavbar');
    const sdNavbarToggle = document.getElementById('sdNavbarToggle');
    const sdIconUp = document.getElementById('sdIconUp');
    const sdIconDown = document.getElementById('sdIconDown');

    if(sdNavbarToggle) {
        sdNavbarToggle.addEventListener('click', () => {
            sdNavbar.classList.toggle('hidden-nav');
            if(sdNavbar.classList.contains('hidden-nav')) {
                sdIconUp.style.display = 'none';
                sdIconDown.style.display = 'block';
            } else {
                sdIconUp.style.display = 'block';
                sdIconDown.style.display = 'none';
            }
        });
    }

    // Auto-hide navbar on scroll down
    let lastScrollY = window.scrollY;
    window.addEventListener('scroll', () => {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > lastScrollY && currentScrollY > 50) {
            // Scroll down: Hide navbar
            if (!sdNavbar.classList.contains('hidden-nav')) {
                sdNavbar.classList.add('hidden-nav');
                if(sdIconUp && sdIconDown) {
                    sdIconUp.style.display = 'none';
                    sdIconDown.style.display = 'block';
                }
            }
        } else if (currentScrollY < lastScrollY) {
            // Scroll up: Show navbar
            if (sdNavbar.classList.contains('hidden-nav')) {
                sdNavbar.classList.remove('hidden-nav');
                if(sdIconUp && sdIconDown) {
                    sdIconUp.style.display = 'block';
                    sdIconDown.style.display = 'none';
                }
            }
        }
        lastScrollY = currentScrollY;
    });

    // Counter Animation
    const counters = document.querySelectorAll('.counter');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = +counter.dataset.target;
                const increment = target / 200;
                let count = 0;
                const update = () => {
                    count += increment;
                    if (count < target) { counter.innerText = Math.ceil(count); requestAnimationFrame(update); }
                    else { counter.innerText = target; }
                };
                update();
                observer.unobserve(counter);
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
</body>
</html>