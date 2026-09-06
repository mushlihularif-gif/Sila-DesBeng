<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}" translate="no" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('Admin/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Dashboard - SiladesBeng Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="" />
    <link rel="icon" type="image/png" href="{{ asset('Admin/img/illustrations/logodomain.webp') }}?v={{ time() }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('Admin/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('Admin/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('Admin/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('Admin/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('Admin/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('Admin/vendor/libs/apex-charts/apex-charts.css') }}" />
    <!-- CSS Kustom untuk Gaya -->
    <style>
        /* Animasi Transisi Halaman */
        @keyframes pageFadeIn {
            0% {
                opacity: 0;
                transform: translateY(15px);
            }
            100% {
                opacity: 1;
                transform: none;
            }
        }

        .layout-page {
            animation: pageFadeIn 0.6s ease-out forwards;
        }

        .card {
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .unit-card {
            border-left: 4px solid #007bff;
            transition: all 0.3s ease;
        }

        .unit-card:hover {
            border-left-width: 6px;
            background-color: #f8f9fa;
        }

        .unit-card.warning {
            border-left-color: #ffc107;
        }

        .unit-card.danger {
            border-left-color: #dc3545;
        }

        .unit-card.success {
            border-left-color: #28a745;
        }

        .unit-card.info {
            border-left-color: #17a2b8;
        }

        .notification-item {
            transition: background-color 0.3s ease;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 8px;
        }

        .product-item {
            transition: transform 0.3s ease;
        }

        .product-item:hover {
            transform: scale(1.02);
            z-index: 1;
        }

        .partnership-card {
            transition: all 0.3s ease;
        }

        .partnership-card:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-3px);
        }

        /* Notifikasi Dropdown Styles & Responsive Optimization */
        .notif-dropdown-menu {
            max-height: 560px !important;
            border-radius: 16px !important;
            box-shadow: 0 12px 42px rgba(67, 89, 113, 0.16) !important;
            overflow: hidden !important;
            border: 1px solid rgba(67, 89, 113, 0.12) !important;
            background: #ffffff !important;
        }

        @media (min-width: 576px) {
            .notif-dropdown-menu {
                width: 420px !important;
                max-width: 92vw !important;
                right: 0 !important;
                left: auto !important;
                transform: none !important;
            }
        }

        @media (max-width: 575.98px) {
            .notif-dropdown-menu {
                position: fixed !important;
                top: 64px !important;
                left: 10px !important;
                right: 10px !important;
                width: auto !important;
                max-width: none !important;
                min-width: 0 !important;
                margin: 0 !important;
                transform: none !important;
                max-height: 84vh !important;
                z-index: 1090 !important;
                border-radius: 14px !important;
            }
        }

        /* Filter Scroll Container with Drag & Scroll */
        .notif-filter-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .notif-filter-scroll {
            white-space: nowrap;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            -webkit-overflow-scrolling: touch;
            display: flex;
            gap: 7px;
            padding: 4px 2px 6px 2px;
            cursor: grab;
            scroll-behavior: smooth;
            user-select: none;
            width: 100%;
        }

        .notif-filter-scroll.dragging {
            cursor: grabbing !important;
            scroll-behavior: auto !important;
        }

        .notif-filter-scroll::-webkit-scrollbar {
            display: none;
        }

        .notif-scroll-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #ffffff;
            border: 1px solid #d9dfe7;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.14);
            color: #566a7f;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            padding: 0;
            font-size: 1.15rem;
            transition: all 0.2s ease;
        }

        .notif-scroll-arrow:hover {
            background: #696cff;
            color: #ffffff;
            border-color: #696cff;
        }

        .notif-scroll-prev {
            left: -4px;
        }

        .notif-scroll-next {
            right: -4px;
        }

        .notif-filter-btn {
            font-size: 0.76rem !important;
            padding: 5px 13px !important;
            border-radius: 50px !important;
            white-space: nowrap !important;
            transition: all 0.2s ease !important;
            flex-shrink: 0 !important;
            border: 1px solid transparent !important;
            font-weight: 600 !important;
            background-color: #f1f3f6 !important;
            color: #566a7f !important;
        }

        .notif-filter-btn:hover {
            background-color: #e6e9ef !important;
            color: #384554 !important;
        }

        .notif-filter-btn.active {
            background-color: #696cff !important;
            color: #ffffff !important;
            box-shadow: 0 3px 10px rgba(105, 108, 255, 0.35) !important;
        }

        .notif-item {
            border-bottom: 1px solid #f2f4f7;
            transition: all 0.2s ease;
            text-decoration: none;
            display: flex;
            align-items: flex-start;
            padding: 14px 18px;
            position: relative;
        }

        .notif-item:hover {
            background-color: #f8fafc !important;
        }

        .notif-item.is-unread {
            background-color: rgba(105, 108, 255, 0.035);
            border-left: 3.5px solid #696cff;
        }

        .notif-item.is-unread.category-rental { border-left-color: #ffab00; background-color: rgba(255, 171, 0, 0.035); }
        .notif-item.is-unread.category-mobil { border-left-color: #03c3ec; background-color: rgba(3, 195, 236, 0.035); }
        .notif-item.is-unread.category-fasilitas { border-left-color: #71dd37; background-color: rgba(113, 221, 55, 0.035); }
        .notif-item.is-unread.category-gas { border-left-color: #ff3e1d; background-color: rgba(255, 62, 29, 0.035); }
        .notif-item.is-unread.category-kyc { border-left-color: #03c3ec; background-color: rgba(3, 195, 236, 0.035); }
        .notif-item.is-unread.category-laporan { border-left-color: #ff3e1d; background-color: rgba(255, 62, 29, 0.035); }
        .notif-item.is-unread.category-mutasi { border-left-color: #8592a3; background-color: rgba(133, 146, 163, 0.035); }

        .notif-item-desc {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.45;
            color: #697a8d;
        }

        .nav-link {
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #007bff !important;
            background-color: rgba(0, 123, 255, 0.1) !important;
        }

        .menu-item.active .menu-link {
            background-color: rgba(0, 123, 255, 0.1) !important;
            color: #007bff !important;
        }

        .avatar {
            transition: transform 0.3s ease;
        }

        .avatar:hover {
            transform: scale(1.1);
        }

        /* Animasi scroll halus */
        .animate-fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .animate-fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Gaya wadah grafik */
        .chart-container {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chart-wrapper {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Lencana notifikasi */
        .notification-badge {
            position: relative;
        }

        .notification-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        /* Gaya sidebar */
        .layout-menu {
            transition: all 0.3s ease;
        }

        .layout-menu-toggle {
            transition: all 0.3s ease;
        }

        .layout-menu-toggle:hover {
            transform: rotate(180deg);
        }

        /* Kartu statistik keuangan */
        .financial-stat-card {
            transition: all 0.3s ease;
        }

        .financial-stat-card:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        /* Gaya kartu produk */
        .product-card {
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .product-card:hover {
            border-color: #007bff;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
        }

        /* Gaya kartu kemitraan */
        .partnership-card {
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .partnership-card:hover {
            border-color: #007bff;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
        }

        /* Gaya header kartu */
        .card-header {
            border-bottom: 1px solid #e9ecef;
        }

        /* Gaya tombol */
        .btn {
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Gaya baru untuk tata letak tiga kolom */
        .dashboard-stats-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .dashboard-stats-col {
            flex: 1;
            min-width: 0;
        }

        .dashboard-stats-col .card {
            height: 100%;
        }

        .dashboard-stats-col .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .dashboard-stats-col .card-title {
            margin-bottom: 1rem;
        }

        .dashboard-stats-col .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .dashboard-stats-col .stat-change {
            font-size: 0.875rem;
            color: #28a745;
        }

        .dashboard-stats-col .stat-change.negative {
            color: #dc3545;
        }

        .dashboard-stats-col .stat-label {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .dashboard-stats-col .chart-placeholder {
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .dashboard-stats-col .chart-placeholder p {
            margin: 0;
            text-align: center;
            color: #6c757d;
        }

        /* Perbaikan z-index dropdown */
        .dropdown-menu {
            z-index: 10000 !important;
        }

        /* Hapus lencana peringkat produk */
        .product-rank {
            display: none;
        }

        /* Gaya gambar produk */
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        /* Partnership card styling */
        .partnership-card .avatar {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
        }

        /* Gaya tombol Laporan Layanan Daerah */
        .laporan-bumdes-btn {
            margin-top: 1rem;
        }

        /* Jarak antar bagian */
        .section-gap {
            margin-bottom: 2rem;
        }

        /* Gaya default avatar navbar */
        .navbar-avatar-default {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #0099ff 0%, #ffb300 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .navbar-avatar-initials {
            font-size: 18px;
            font-weight: bold;
            color: white;
        }

        /* Toast Berwarna SweetAlert2 */
        .colored-toast.swal2-icon-success {
            background-color: #28c76f !important;
        }

        .colored-toast.swal2-icon-error {
            background-color: #ea5455 !important;
        }

        .colored-toast.swal2-icon-warning {
            background-color: #ff9f43 !important;
        }

        .colored-toast.swal2-icon-info {
            background-color: #00cfe8 !important;
        }

        .colored-toast .swal2-title {
            color: white !important;
        }

        .colored-toast .swal2-close {
            color: white !important;
        }
        
        .colored-toast .swal2-html-container {
            color: white !important;
        }

        /* Pastikan z-index SweetAlert lebih tinggi dari yang lain (Navbar, Sidebar) */
        .swal2-container {
            z-index: 100000 !important;
        }
        
        /* Efek Preloader Baru */
        .page-preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.4); /* Putih transparan */
            backdrop-filter: blur(5px); /* Efek blur halus */
            z-index: 999999;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 1;
            visibility: visible;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .page-preloader.loaded {
            opacity: 0;
            visibility: hidden;
        }

        .preloader-logo {
            width: 2.5rem;
            height: auto;
            z-index: 10;
            animation: pulse-logo 1.5s ease-in-out infinite;
        }

        @keyframes pulse-logo {
            0% { transform: scale(0.85); opacity: 0.8; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(0.85); opacity: 0.8; }
        }
    </style>
    <script src="{{ asset('Admin/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('Admin/js/config.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('Admin/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <!-- Custom CSS for Styling -->
    <style>
        /* ... (CSS Anda sebelumnya) ... */
    </style>

    <!-- Croppie CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css" />

    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom Page Styles -->
    @yield('styles')
    @stack('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>

<body>
    <!-- Preloader Overlay -->
    <div id="page-preloader" class="page-preloader">
        <div class="position-relative d-flex align-items-center justify-content-center">
            <!-- Spinner berputar di luar -->
            <div class="spinner-border text-primary shadow-sm position-absolute" style="width: 5rem; height: 5rem; border-width: 0.25em;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <!-- Logo berdenyut di tengah -->
            <img src="{{ asset('Admin/img/illustrations/logodomain.webp') }}" alt="Logo" class="preloader-logo">
        </div>
    </div>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Sidebar -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="{{ route('admin.dashboard') }}" class="app-brand-link" style="display: flex; align-items: center;">
                        <img src="{{ asset('Admin/img/illustrations/logodomain.webp') }}?v={{ time() }}" alt="Logo"
                            style="height: 40px; width: auto; object-fit: contain;">
                        <span class="app-brand-text demo menu-text fw-bold ms-2" style="font-size: 1.2rem; color: #566a7f; letter-spacing: 0.5px; text-transform: capitalize;">Administrator</span>
                    </a>
                    <a href="javascript:void(0);"
                        class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                        <i class="bx bx-chevron-left bx-sm align-middle"></i>
                    </a>
                </div>
                <div class="menu-inner-shadow"></div>
                <ul class="menu-inner py-1">
                    <!-- Dashboard -->
                    <li class="menu-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div data-i18n="Dashboard">Dashboard</div>
                        </a>
                    </li>

                <!-- Unit Layanan (Dropdown) -->
                @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa']))
                    @if(isset($hasActiveServices) && $hasActiveServices)
                        <li class="menu-item {{ (request()->is('admin/unit*') && !request()->is('admin/unit/supir*')) || request()->routeIs('admin.announcements.*') ? 'open active show' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <i class="menu-icon tf-icons bx bx-building-house"></i>
                                <div data-i18n="Unit Layanan">Unit Layanan</div>
                            </a>
                            <ul class="menu-sub">
                                @if(in_array('Penyewaan Alat', $activeServicesMenu ?? []))
                                <li class="menu-item {{ request()->is('admin/unit/penyewaan*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.unit.penyewaan.index') }}" class="menu-link">
                                        <div data-i18n="Penyewaan Alat">Penyewaan Alat</div>
                                    </a>
                                </li>
                                @endif
                                @if(in_array('Penjualan Gas', $activeServicesMenu ?? []))
                                <li class="menu-item {{ request()->is('admin/unit/gas*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.unit.penjualan_gas.index') }}" class="menu-link">
                                        <div data-i18n="Penjualan Gas">Penjualan Gas</div>
                                    </a>
                                </li>
                                @endif
                                @if(in_array('Penyewaan Mobil', $activeServicesMenu ?? []))
                                <li class="menu-item {{ request()->is('admin/unit/mobil*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.unit.mobil.index') }}" class="menu-link">
                                        <div data-i18n="Penyewaan Mobil">Penyewaan Mobil</div>
                                    </a>
                                </li>
                                @endif
                                @if(in_array('Fasilitas Umum', $activeServicesMenu ?? []))
                                <li class="menu-item {{ request()->is('admin/unit/fasilitas_umum*') || request()->is('admin/unit/ambulans*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.unit.fasilitas_umum.index') }}" class="menu-link">
                                        <div data-i18n="Fasilitas Umum">Fasilitas Umum</div>
                                    </a>
                                </li>
                                @endif
                                @if(in_array('Pasar Daerah', $activeServicesMenu ?? []))
                                <li class="menu-item {{ request()->is('admin/unit/pasar-daerah*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.unit.pasar_daerah.index') }}" class="menu-link">
                                        <div data-i18n="Pasar Daerah">Pasar Daerah</div>
                                    </a>
                                </li>
                                @endif

                                @if(in_array('Pengumuman', $activeServicesMenu ?? []) || in_array('Kabar dan Informasi Daerah', $activeServicesMenu ?? []))
                                <li class="menu-item {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.announcements.index') }}" class="menu-link">
                                        <div data-i18n="Kabar dan Informasi Daerah">Kabar dan Informasi Daerah</div>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                    @else
                        <!-- No active services: Show prompt to activate -->
                        <li class="menu-item">
                            @php
                                $settingsRoute = in_array(auth()->user()->role, ['super_admin', 'admin']) ? route('admin.system-settings.index') : route('admin.region-settings.index');
                            @endphp
                            <a href="{{ $settingsRoute }}" class="menu-link text-warning d-flex align-items-center justify-content-between" style="background: rgba(255, 171, 0, 0.08); border: 1px dashed rgba(255, 171, 0, 0.4); border-radius: 8px; margin: 4px 12px; padding: 10px 12px;">
                                <div class="d-flex align-items-center overflow-hidden">
                                    <i class="menu-icon tf-icons bx bx-lock-alt text-warning me-2" style="font-size: 1.25rem;"></i>
                                    <div class="fw-semibold text-warning" style="font-size: 0.82rem; line-height: 1.2;">Ayo aktifkan layanan!</div>
                                </div>
                                <span class="badge bg-label-warning rounded-pill px-2 py-1" style="font-size: 0.65rem;">Terkunci</span>
                            </a>
                        </li>
                    @endif
                @endif

                <!-- Manajemen (Dropdown) -->
                <li class="menu-item {{ request()->is('admin/manajemen-pengguna*') || request()->is('admin/kelola-wilayah*') || request()->is('admin/banners*') || request()->routeIs('admin.warga.mutasi.*') || request()->routeIs('admin.staff.*') || request()->routeIs('admin.wilayah-admins.*') ? 'open active show' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-briefcase"></i>
                        <div data-i18n="Manajemen">Manajemen</div>
                    </a>
                    <ul class="menu-sub">
                        @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa']))
                        <li class="menu-item {{ request()->routeIs('admin.manajemen-pengguna.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.manajemen-pengguna.index') }}" class="menu-link">
                                <div>Pengguna</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.staff.index') }}" class="menu-link">
                                <div>Staf Layanan</div>
                            </a>
                        </li>
                        @if(auth()->user()->role === 'admin_desa')
                        <li class="menu-item {{ request()->routeIs('admin.wilayah-admins.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.wilayah-admins.index') }}" class="menu-link">
                                <div>Wilayah & RT/RW</div>
                            </a>
                        </li>
                        @endif
                        @endif

                        @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan']))
                        <li class="menu-item {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.banners.index') }}" class="menu-link">
                                <div>Banner</div>
                            </a>
                        </li>
                        @endif
                        @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa']))
                        <li class="menu-item {{ request()->routeIs('admin.warga.mutasi.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.warga.mutasi.index') }}" class="menu-link">
                                <div>Mutasi Penduduk</div>
                            </a>
                        </li>
                        @endif

                        @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan', 'admin_rw']))
                        <li class="menu-item {{ request()->routeIs('admin.kelola-wilayah.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.kelola-wilayah.index') }}" class="menu-link">
                                <div>Kelola Wilayah</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>

                <!-- Aktivitas -->
                @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa', 'admin_rw', 'admin_rt']))
                <li
                    class="menu-item {{ request()->is('admin/aktivitas/permintaan-pengajuan*') || request()->is('admin/aktivitas/bukti-transaksi*') || request()->is('admin/kemitraan*') || request()->routeIs('admin.kyc.*') || (request()->routeIs('admin.pelaporan.*') && !request()->routeIs('admin.pelaporan.archive')) ? 'open active show' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-time"></i>
                        <div data-i18n="Permintaan & Aktivitas">Permintaan & Aktivitas</div>
                    </a>
                    <ul class="menu-sub">
                        <li
                            class="menu-item {{ request()->is('admin/aktivitas/permintaan-pengajuan*') ? 'active' : '' }}">
                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="menu-link">
                                <div data-i18n="Permintaan & Pengajuan">Permintaan & Pengajuan</div>
                            </a>
                        </li>
                        @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa']))
                        <li class="menu-item {{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.kyc.index') }}" class="menu-link">
                                <div data-i18n="Verifikasi Identitas">Verifikasi Identitas</div>
                            </a>
                        </li>
                        @endif
                        <li class="menu-item {{ request()->is('admin/aktivitas/bukti-transaksi*') ? 'active' : '' }}">
                            <a href="{{ route('admin.aktivitas.bukti-transaksi.index') }}" class="menu-link">
                                <div data-i18n="Bukti Transaksi">Bukti Transaksi</div>
                            </a>
                        </li>
                        @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan']))
                        <li class="menu-item {{ request()->routeIs('admin.kemitraan.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.kemitraan.index') }}" class="menu-link">
                                <div class="notranslate" translate="no">Persetujuan Mitra</div>
                            </a>
                        </li>
                        @endif
                        <li class="menu-item {{ request()->routeIs('admin.pelaporan.*') && !request()->routeIs('admin.pelaporan.archive') ? 'active' : '' }}">
                            <a href="{{ Route::has('admin.pelaporan.index') ? route('admin.pelaporan.index') : '#' }}" class="menu-link">
                                <div>Pelaporan Warga</div>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                <!-- Data & Laporan (Dropdown) -->
                <li class="menu-item {{ request()->routeIs('admin.laporan.*') || request()->routeIs('admin.pelaporan.archive') ? 'open active show' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                        <div data-i18n="Data & Laporan">Data & Laporan</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item {{ request()->routeIs('admin.pelaporan.archive') ? 'active' : '' }}">
                            <a href="{{ route('admin.pelaporan.archive') }}" class="menu-link">
                                <div data-i18n="Bukti Pelaporan Warga">Bukti Pelaporan Warga</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('admin.laporan.transaksi') ? 'active' : '' }}">
                            <a href="{{ route('admin.laporan.transaksi') }}" class="menu-link">
                                <div data-i18n="Laporan Transaksi">Laporan Transaksi</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('admin.laporan.pendapatan') ? 'active' : '' }}">
                            <a href="{{ route('admin.laporan.pendapatan') }}" class="menu-link">
                                <div data-i18n="Laporan Pendapatan">Laporan Pendapatan</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('admin.laporan.wilayah') ? 'active' : '' }}">
                            <a href="{{ route('admin.laporan.wilayah') }}" class="menu-link">
                                <div data-i18n="Laporan Wilayah">Laporan Wilayah</div>
                            </a>
                        </li>
                        @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan']))
                        <li class="menu-item {{ request()->routeIs('admin.laporan.log') ? 'active' : '' }}">
                            <a href="{{ route('admin.laporan.log') }}" class="menu-link">
                                <div data-i18n="Log Aktivitas">Log Aktivitas</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>

                <!-- Pengaturan (Dropdown) -->
                @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa']))
                <li class="menu-item {{ request()->routeIs('admin.system-settings.*') || request()->routeIs('admin.region-settings.*') || request()->is('admin/unit/supir*') ? 'open active show' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-cog"></i>
                        <div data-i18n="Pengaturan">Pengaturan</div>
                    </a>
                    <ul class="menu-sub">
                        @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                        <li class="menu-item {{ request()->routeIs('admin.system-settings.index') ? 'active' : '' }}">
                            <a href="{{ route('admin.system-settings.index') }}" class="menu-link">
                                <div>Layanan Pusat</div>
                            </a>
                        </li>
                        @if(isset($hasActiveServices) && $hasActiveServices)
                        <li class="menu-item {{ request()->routeIs('admin.system-settings.payment') ? 'active' : '' }}">
                            <a href="{{ route('admin.system-settings.payment') }}" class="menu-link">
                                <div>Pembayaran Pusat</div>
                            </a>
                        </li>
                        @endif
                        @else
                        <li class="menu-item {{ request()->routeIs('admin.region-settings.index') ? 'active' : '' }}">
                            <a href="{{ route('admin.region-settings.index') }}" class="menu-link">
                                <div>Layanan & Metode Pengiriman</div>
                            </a>
                        </li>
                        @if(isset($hasActiveServices) && $hasActiveServices)
                        <li class="menu-item {{ request()->routeIs('admin.region-settings.payment') ? 'active' : '' }}">
                            <a href="{{ route('admin.region-settings.payment') }}" class="menu-link">
                                <div>Pembayaran Wilayah</div>
                            </a>
                        </li>
                        @endif
                        @endif
                        
                        @if(in_array('Penyewaan Mobil', $activeServicesMenu ?? []) || in_array('Fasilitas Umum', $activeServicesMenu ?? []))
                        <li class="menu-item {{ request()->is('admin/unit/supir*') ? 'active' : '' }}">
                            <a href="{{ route('supir.index') }}" class="menu-link">
                                <div data-i18n="Data Supir & Petugas">Data Supir & Petugas</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif


                <!-- Profil & Info (Dropdown) -->
                <li class="menu-item {{ request()->is('admin/SiladesBeng/profile*') || request()->is('admin/SiladesBeng/developer*') || request()->routeIs('admin.SiladesBeng.bumdes.index') || request()->routeIs('admin.SiladesBeng.bumdes.*') ? 'open active show' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-info-circle"></i>
                        <div data-i18n="Profil & Info">Profil & Info</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item {{ request()->routeIs('admin.SiladesBeng.profile') || request()->routeIs('admin.SiladesBeng.developer.profile') ? 'active' : '' }}">
                            <a href="{{ route('admin.SiladesBeng.profile') }}" class="menu-link">
                                <div>SiladesBeng</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('admin.SiladesBeng.bumdes.index') || request()->routeIs('admin.SiladesBeng.bumdes.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.SiladesBeng.bumdes.index') }}" class="menu-link">
                                @php
                                    $sidebarRegionLabel = 'Pemerintah Desa';
                                    if(auth()->user()->role === 'admin_kecamatan') {
                                        $sidebarRegionLabel = 'Pemerintah Kecamatan';
                                    } elseif(in_array(auth()->user()->role, ['super_admin', 'admin'])) {
                                        $sidebarRegionLabel = 'Pemerintah Kabupaten';
                                    }
                                @endphp
                                <div>{{ $sidebarRegionLabel }}</div>
                            </a>
                        </li>
                    </ul>
                </li>
                </ul>
            </aside>
            <!-- Layout page -->
            <div class="layout-page">
                <!-- Helpers -->
                <!-- Navbar -->
                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
                    id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>
                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item d-flex align-items-center position-relative">
                                @if(!request()->routeIs(
                                    'admin.laporan.log',
                                    'admin.SiladesBeng.*',
                                    'admin.kemitraan.*',
                                    'admin.manajemen-pengguna.*',
                                    'admin.system-settings.*',
                                    'admin.region-settings.*',
                                    'admin.notifications.*',
                                    'admin.kelola-wilayah.*',
                                    'admin.banners.*',
                                    'admin.announcements.*'
                                ))
                                <form action="{{ route('admin.search') }}" method="GET" class="d-flex align-items-center w-100" id="headerSearchForm">
                                    <i class="bx bx-search fs-4 lh-0"></i>
                                    <input type="text" 
                                           name="search" 
                                           id="headerSearchInput"
                                           class="form-control border-0 shadow-none" 
                                           placeholder="Cari..."
                                           value="{{ request('search') }}"
                                           aria-label="Search..." />
                                    @if(request('search'))
                                        <a href="{{ url()->current() }}" 
                                           class="btn btn-sm btn-link text-muted p-0 ms-2" 
                                           title="Hapus pencarian"
                                           style="text-decoration: none;">
                                            <i class="bx bx-x fs-5"></i>
                                        </a>
                                    @endif
                                </form>
                                @endif
                            </div>
                        </div>
                        @if(isset($hasActiveServices) && !$hasActiveServices && in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa']))
                            @php
                                $settingsRoute = in_array(auth()->user()->role, ['super_admin', 'admin']) ? route('admin.system-settings.index') : route('admin.region-settings.index');
                            @endphp
                            <div class="ms-auto me-3 d-none d-md-block">
                                <a href="{{ $settingsRoute }}" class="btn btn-sm btn-outline-warning d-flex align-items-center rounded-pill px-3 py-1 shadow-none" style="font-size: 0.78rem; font-weight: 600;">
                                    <i class="bx bx-lock-alt me-1 fs-6"></i>
                                    <span>Layanan Terkunci &bull; Ayo Aktifkan Layanan!</span>
                                </a>
                            </div>
                        @endif
                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <!-- Notifikasi Bell Icon -->
                            <li class="nav-item dropdown me-3 position-static position-sm-relative">
                                <a class="nav-link dropdown-toggle hide-arrow position-relative" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-display="static" data-bs-auto-close="outside">
                                    <i class="bx bx-bell bx-sm"></i>
                                    @php
                                        $notifQuery = \App\Models\AdminNotification::query();
                                        $currentUser = auth()->user();
                                        if ($currentUser) {
                                            if (in_array($currentUser->role, ['super_admin'])) {
                                                // Super Admin melihat semua notifikasi
                                            } else {
                                                $userRegionId = $currentUser->region_id;
                                                if ($userRegionId) {
                                                    $allowedRegionIds = \App\Models\Region::getDescendantIds($userRegionId);
                                                    $allowedRegionIds[] = $userRegionId;
                                                    $notifQuery->where(function($q) use ($allowedRegionIds) {
                                                        $q->whereIn('region_id', $allowedRegionIds)->orWhereNull('region_id');
                                                    });
                                                } else {
                                                    $notifQuery->whereNull('region_id');
                                                }
                                            }
                                        }
                                        $unreadCount = (clone $notifQuery)->where('is_read', false)->count();
                                    @endphp
                                    @if($unreadCount > 0)
                                    <span class="badge bg-danger rounded-pill badge-notifications position-absolute" style="top: -2px; right: -6px; font-size: 10px; min-width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end py-0 notif-dropdown-menu">
                                    <li class="dropdown-menu-header border-bottom bg-white">
                                        <div class="dropdown-header d-flex align-items-center justify-content-between py-3 px-3 px-sm-4">
                                            <h6 class="mb-0 fw-bold text-dark fs-5">Notifikasi</h6>
                                            @if($unreadCount > 0)
                                            <span class="badge bg-label-primary rounded-pill px-3 py-1 fw-bold fs-7">{{ $unreadCount }} Baru</span>
                                            @endif
                                        </div>
                                        <!-- Pill Tabs untuk Semua Layanan -->
                                        <div class="px-3 px-sm-4 pb-3">
                                            <div class="notif-filter-wrapper">
                                                <button type="button" class="notif-scroll-arrow notif-scroll-prev d-none" aria-label="Geser ke kiri">
                                                    <i class="bx bx-chevron-left"></i>
                                                </button>
                                                <div class="notif-filter-scroll">
                                                    <button type="button" class="btn btn-sm notif-filter-btn active" data-filter="all">Semua</button>
                                                    <button type="button" class="btn btn-sm notif-filter-btn" data-filter="rental">Sewa Alat</button>
                                                    <button type="button" class="btn btn-sm notif-filter-btn" data-filter="mobil">Sewa Mobil</button>
                                                    <button type="button" class="btn btn-sm notif-filter-btn" data-filter="fasilitas">Fasilitas Umum</button>
                                                    <button type="button" class="btn btn-sm notif-filter-btn" data-filter="gas">Gas LPG</button>
                                                    <button type="button" class="btn btn-sm notif-filter-btn" data-filter="pasar">Pasar Desa</button>
                                                    <button type="button" class="btn btn-sm notif-filter-btn" data-filter="kyc">Verifikasi Identitas</button>
                                                    <button type="button" class="btn btn-sm notif-filter-btn" data-filter="laporan">Laporan Warga</button>
                                                    <button type="button" class="btn btn-sm notif-filter-btn" data-filter="mutasi">Mutasi Penduduk</button>
                                                </div>
                                                <button type="button" class="notif-scroll-arrow notif-scroll-next" aria-label="Geser ke kanan">
                                                    <i class="bx bx-chevron-right"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="notif-list-container" style="max-height: 360px; overflow-y: auto;">
                                            @php
                                                $recentNotifications = (clone $notifQuery)->latest()->take(30)->get();
                                            @endphp
                                            @forelse($recentNotifications as $notif)
                                            @php
                                                // Mapping icon, category, dan link tujuan
                                                $cat = 'lainnya';
                                                $icon = 'bx-bell';
                                                $color = 'primary';

                                                if (in_array($notif->type, ['rental_request', 'rental_order', 'rental'])) {
                                                    $cat = 'rental'; $icon = 'bx-wrench'; $color = 'warning';
                                                    $targetUrl = route('admin.aktivitas.permintaan-pengajuan.index');
                                                } elseif (in_array($notif->type, ['cancellation_request', 'mobil_order', 'rental_mobil', 'mobil'])) {
                                                    $cat = 'mobil'; $icon = 'bx-car'; $color = 'info';
                                                    $targetUrl = route('admin.aktivitas.permintaan-pengajuan.index');
                                                } elseif (in_array($notif->type, ['fasilitas_order', 'rental_fasilitas', 'fasilitas'])) {
                                                    $cat = 'fasilitas'; $icon = 'bx-building-house'; $color = 'success';
                                                    $targetUrl = route('admin.aktivitas.permintaan-pengajuan.index');
                                                } elseif (in_array($notif->type, ['gas_order', 'gas'])) {
                                                    $cat = 'gas'; $icon = 'bxs-gas-pump'; $color = 'danger';
                                                    $targetUrl = route('admin.aktivitas.permintaan-pengajuan.index');
                                                } elseif (in_array($notif->type, ['pasar_order', 'pasar', 'pasar_daerah'])) {
                                                    $cat = 'pasar'; $icon = 'bx-store-alt'; $color = 'primary';
                                                    $targetUrl = Route::has('admin.unit.pasar_daerah.pesanan.index') ? route('admin.unit.pasar_daerah.pesanan.index') : route('admin.aktivitas.permintaan-pengajuan.index');
                                                } elseif (in_array($notif->type, ['kyc', 'kyc_verification'])) {
                                                    $cat = 'kyc'; $icon = 'bx-id-card'; $color = 'info';
                                                    $targetUrl = ($notif->reference_id && Route::has('admin.kyc.show')) ? route('admin.kyc.show', $notif->reference_id) : route('admin.kyc.index');
                                                } elseif ($notif->type === 'mutasi') {
                                                    $cat = 'mutasi'; $icon = 'bx-user-pin'; $color = 'secondary';
                                                    $targetUrl = route('admin.warga.mutasi.index');
                                                } elseif (in_array($notif->type, ['laporan', 'pengumuman'])) {
                                                    $cat = 'laporan'; $icon = 'bx-message-square-error'; $color = 'danger';
                                                    $targetUrl = ($notif->reference_id && Route::has('admin.pelaporan.show')) ? route('admin.pelaporan.show', $notif->reference_id) : (Route::has('admin.pelaporan.index') ? route('admin.pelaporan.index') : route('admin.aktivitas.permintaan-pengajuan.index'));
                                                } else {
                                                    $targetUrl = route('admin.aktivitas.permintaan-pengajuan.index');
                                                }
                                            @endphp
                                            <a href="{{ $targetUrl }}" class="dropdown-item notif-item {{ !$notif->is_read ? 'is-unread category-'.$cat : '' }} gap-3" data-category="{{ $cat }}">
                                                <div class="flex-shrink-0 mt-1">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-xs {{ !$notif->is_read ? 'bg-label-'.$color : 'bg-light' }}" style="width: 40px; height: 40px;">
                                                        <i class="bx {{ $icon }} fs-5 {{ !$notif->is_read ? 'text-'.$color : 'text-secondary' }}"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1" style="min-width: 0;">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <h6 class="mb-0 fw-bold {{ !$notif->is_read ? 'text-dark' : 'text-secondary' }} text-truncate" style="font-size: 0.88rem;">{{ $notif->title }}</h6>
                                                        @if(!$notif->is_read)
                                                            <span class="badge badge-dot bg-{{ $color }} ms-2 flex-shrink-0" style="width: 8px; height: 8px;"></span>
                                                        @endif
                                                    </div>
                                                    <p class="notif-item-desc mb-1 small">{{ $notif->message }}</p>
                                                    <small class="text-muted d-flex align-items-center fw-medium" style="font-size: 0.72rem;">
                                                        <i class="bx bx-time-five me-1"></i> {{ $notif->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </a>
                                            @empty
                                            <div class="text-center py-5 notif-empty-state">
                                                <div class="mb-3">
                                                    <i class="bx bx-bell-off text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                                                </div>
                                                <h6 class="fw-semibold text-dark">Belum ada notifikasi</h6>
                                                <p class="text-muted small mb-0">Saat ini tidak ada pemberitahuan baru.</p>
                                            </div>
                                            @endforelse
                                            
                                            <div class="text-center py-5 d-none notif-filtered-empty">
                                                <div class="mb-3">
                                                    <i class="bx bx-filter text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                                                </div>
                                                <h6 class="fw-semibold text-dark">Tidak ada notifikasi</h6>
                                                <p class="text-muted small mb-0">Tidak ada notifikasi untuk layanan ini.</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dropdown-menu-footer border-top bg-white">
                                        <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="dropdown-item text-center py-3 text-primary fw-bold d-flex align-items-center justify-content-center" style="font-size: 0.84rem;">
                                            <i class="bx bx-list-ul me-2 fs-5"></i> Lihat Semua Aktivitas
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Nama Admin -->
                            <li class="nav-item lh-1 me-3 d-none d-sm-block text-end">
                                <span class="fw-semibold d-block" style="line-height: 1.2;">{{ Auth::user()->name ?? 'Admin' }}</span>
                                <small class="text-muted" style="font-size: 11px;">
                                    @php
                                        $roleLabels = [
                                            'super_admin' => 'Super Admin',
                                            'admin' => 'Admin Pusat',
                                            'admin_desa' => 'Admin Desa',
                                            'admin_rw' => 'Admin RW',
                                            'admin_rt' => 'Admin RT',
                                            'admin_kecamatan' => 'Admin Kecamatan',
                                            'staff' => 'Staf Unit',
                                            'user' => 'Pengguna',
                                        ];
                                    @endphp
                                    {{ $roleLabels[Auth::user()->role] ?? ucfirst(Auth::user()->role) }}
                                </small>
                            </li>
                            <!-- Profil Admin -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                                    data-bs-toggle="dropdown">
                                    @if(Auth::user() && Auth::user()->file)
                                        <img src="{{ route('media.avatar', ['filename' => basename(Auth::user()->file->path)]) }}" alt="Avatar"
                                            class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" />
                                    @else
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #D1D5DB;">
                                            <svg viewBox="0 0 24 24" fill="currentColor" style="width: 24px; height: 24px; color: white;">
                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.profile') }}">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    @if(Auth::user() && Auth::user()->file)
                                                        <img src="{{ route('media.avatar', ['filename' => basename(Auth::user()->file->path)]) }}" alt="Avatar"
                                                            class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" />
                                                    @else
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #D1D5DB;">
                                                            <svg viewBox="0 0 24 24" fill="currentColor" style="width: 24px; height: 24px; color: white;">
                                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <span class="fw-semibold d-block">{{ Auth::user()->name ?? 'Admin' }}</span>
                                                    <small class="text-muted">{{ Auth::user()->position ?? 'Admin' }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.profile') }}">
                                            <i class="bx bx-user me-2"></i>
                                            <span class="align-middle">My Profile</span>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('auth.logout') }}"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class="bx bx-power-off me-2"></i>
                                            <span class="align-middle">Log Out</span>
                                        </a>
                                        <form id="logout-form" action="{{ route('auth.logout') }}" method="POST"
                                            class="d-none">
                                            @csrf
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!-- Content wrapper -->
                <div class="content-wrapper">

                    @if(isset($hasActiveServices) && !$hasActiveServices && in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa']) && !request()->routeIs('admin.region-settings.*') && !request()->routeIs('admin.system-settings.*'))
                    <div class="container-xxl pt-3">
                        <div class="alert alert-warning alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center mb-0" role="alert">
                            <i class="bx bx-lock-alt fs-3 me-3 text-warning flex-shrink-0"></i>
                            <div class="flex-grow-1">
                                <strong class="d-block">Unit Layanan Wilayah Sedang Terkunci</strong>
                                <span class="small">Seluruh unit layanan wilayah Anda saat ini nonaktif. Warga tidak dapat melihat atau mengakses layanan di beranda.</span>
                                @php
                                    $settingsRoute = in_array(auth()->user()->role, ['super_admin', 'admin']) ? route('admin.system-settings.index') : route('admin.region-settings.index');
                                @endphp
                                <a href="{{ $settingsRoute }}" class="btn btn-sm btn-warning rounded-pill px-3 ms-2 py-1 fw-bold shadow-none" style="font-size: 0.75rem;">
                                    Ayo Aktifkan Layanan Sekarang
                                </a>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    @yield('content')
                    
                    <!-- Footer -->
                    <footer class="content-footer footer bg-white border-top mt-auto">
                        <div class="container-xxl py-4 text-center">
                            <p class="mb-1 text-muted">
                                &copy; {{ date('Y') }} <strong>Sistem Sinergi Layanan dan Aspirasi Desa</strong> di Kabupaten Bengkalis
                            </p>

                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
                <div class="layout-overlay layout-menu-toggle"></div>
            </div>
            <!-- / Layout page -->
        </div>
        <!-- / Layout container -->
    </div>
    <!-- / Layout wrapper -->

    <script src="{{ asset('Admin/vendor/libs/jquery/jquery.js') }}"></script>
            <script src="{{ asset('Admin/vendor/libs/popper/popper.js') }}"></script>
            <script src="{{ asset('Admin/vendor/js/bootstrap.js') }}"></script>
            <script src="{{ asset('Admin/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
            <script src="{{ asset('Admin/vendor/js/menu.js') }}"></script>
            <script src="{{ asset('Admin/vendor/libs/apex-charts/apexcharts.js') }}"></script>
            <script src="{{ asset('Admin/js/main.js') }}"></script>
            <script src="{{ asset('Admin/js/dashboards-analytics.js') }}"></script>
            {{-- SiladesBeng Global Toast System (Admin) --}}
            <style>
                .sdb-toast-container { position: fixed; top: 70px; right: 24px; z-index: 999999 !important; display: flex; flex-direction: column; gap: 12px; pointer-events: none; }
                .sdb-toast { pointer-events: auto; display: flex; align-items: flex-start; padding: 16px 18px; border-radius: 14px; box-shadow: 0 8px 30px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06); border-left: 4px solid; max-width: 380px; width: 100%; background: white; opacity: 0; transform: translateX(50px) scale(0.95); transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
                .sdb-toast.sdb-toast-show { opacity: 1; transform: translateX(0) scale(1); }
                .sdb-toast.sdb-toast-hide { opacity: 0; transform: translateX(50px) scale(0.95); transition: all 0.4s ease-in; }
                .sdb-toast-success { border-left-color: #22c55e; }
                .sdb-toast-error { border-left-color: #ef4444; }
                .sdb-toast-warning { border-left-color: #f59e0b; }
                .sdb-toast-info { border-left-color: #3b82f6; }
                .sdb-toast-icon { flex-shrink: 0; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 14px; margin-top: 1px; }
                .sdb-toast-success .sdb-toast-icon { background: #dcfce7; color: #16a34a; }
                .sdb-toast-error .sdb-toast-icon { background: #fee2e2; color: #dc2626; }
                .sdb-toast-warning .sdb-toast-icon { background: #fef3c7; color: #d97706; }
                .sdb-toast-info .sdb-toast-icon { background: #dbeafe; color: #2563eb; }
                .sdb-toast-body { flex: 1; min-width: 0; }
                .sdb-toast-title { font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 2px; }
                .sdb-toast-msg { font-size: 13px; color: #64748b; line-height: 1.4; }
                .sdb-toast-close { flex-shrink: 0; margin-left: 12px; background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px; border-radius: 6px; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
                .sdb-toast-close:hover { background: #f1f5f9; color: #475569; }
                .sdb-toast-progress { position: absolute; bottom: 0; left: 4px; right: 0; height: 3px; border-radius: 0 0 14px 0; }
                .sdb-toast-success .sdb-toast-progress { background: linear-gradient(90deg, #22c55e, #86efac); }
                .sdb-toast-error .sdb-toast-progress { background: linear-gradient(90deg, #ef4444, #fca5a5); }
                .sdb-toast-warning .sdb-toast-progress { background: linear-gradient(90deg, #f59e0b, #fde68a); }
                .sdb-toast-info .sdb-toast-progress { background: linear-gradient(90deg, #3b82f6, #93c5fd); }
                @keyframes sdb-toast-progress { from { width: 100%; } to { width: 0%; } }
            </style>
            <div id="sdbToastContainer" class="sdb-toast-container"></div>
            <script>
            window.showSiladesBengToast = function(type, title, message, duration) {
                duration = duration || 5000;
                var container = document.getElementById('sdbToastContainer');
                if (!container) return;
                var icons = {
                    success: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>',
                    error: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>',
                    warning: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M10.29 3.86l-8.4 14.56a1.35 1.35 0 001.16 2.02h16.88a1.35 1.35 0 001.16-2.02L12.7 3.86a1.35 1.35 0 00-2.42 0z"></path></svg>',
                    info: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
                };
                var toast = document.createElement('div');
                toast.className = 'sdb-toast sdb-toast-' + type;
                toast.style.position = 'relative';
                toast.style.overflow = 'hidden';
                toast.innerHTML =
                    '<div class="sdb-toast-icon">' + (icons[type] || icons.info) + '</div>' +
                    '<div class="sdb-toast-body">' +
                        '<div class="sdb-toast-title">' + title + '</div>' +
                        (message ? '<div class="sdb-toast-msg">' + message + '</div>' : '') +
                    '</div>' +
                    '<button class="sdb-toast-close" onclick="this.parentElement.classList.add(\'sdb-toast-hide\'); setTimeout(function(){this.parentElement.remove()}.bind(this), 400)">' +
                        '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>' +
                    '</button>' +
                    '<div class="sdb-toast-progress" style="animation: sdb-toast-progress ' + duration + 'ms linear forwards;"></div>';
                container.appendChild(toast);
                setTimeout(function() { toast.classList.add('sdb-toast-show'); }, 30);
                setTimeout(function() {
                    toast.classList.add('sdb-toast-hide');
                    setTimeout(function() { if(toast.parentElement) toast.remove(); }, 400);
                }, duration);
            };
            </script>

            <!-- Skrip untuk animasi dan fungsionalitas -->
            <script>
                // Wrapper: showToast sekarang memanggil showSiladesBengToast
                function showToast(type, message) {
                    const mappedType = type === 'danger' ? 'error' : type;
                    const titleMap = { success: 'Berhasil', error: 'Peringatan', warning: 'Perhatian', info: 'Informasi' };
                    showSiladesBengToast(mappedType, titleMap[mappedType] || 'Notifikasi', message);
                }

                // Fungsi untuk menghasilkan laporan
                function generateReport() {
                    showToast('info', 'Laporan PDF sedang diproses. Fitur ini akan terhubung ke backend Laravel untuk menghasilkan file.');
                    setTimeout(() => {
                        showToast('success', 'Laporan berhasil dibuat dan siap diunduh!');
                    }, 2000);
                }

                // Animasi saat digulir
                document.addEventListener('DOMContentLoaded', function() {
                    const animateElements = document.querySelectorAll('.animate-fade-in');

                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('visible');
                            }
                        });
                    }, {
                        threshold: 0.1
                    });

                    animateElements.forEach(el => {
                        observer.observe(el);
                    });
                });

                // Tambahkan scroll halus ke link jangkar
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();

                        document.querySelector(this.getAttribute('href')).scrollIntoView({
                            behavior: 'smooth'
                        });
                    });
                });
            // â­ Tangani Pesan Flash Sesi saat Halaman Dimuat
            document.addEventListener('DOMContentLoaded', function() {
                @if(session('success'))
                    showSiladesBengToast('success', 'Berhasil', {!! json_encode(session('success')) !!});
                @endif

                @if(session('error'))
                    showSiladesBengToast('error', 'Peringatan', {!! json_encode(session('error')) !!});
                @endif

                @if(session('info'))
                    showSiladesBengToast('info', 'Informasi', {!! json_encode(session('info')) !!});
                @endif

                @if(session('warning'))
                    showSiladesBengToast('warning', 'Perhatian', {!! json_encode(session('warning')) !!});
                @endif

                // Eksekusi Efek Preloader saat pertama dimuat
                setTimeout(() => {
                    const preloader = document.getElementById('page-preloader');
                    if (preloader) preloader.classList.add('loaded');
                }, 100);

                // Tangani masalah saat user menekan tombol 'Back' atau 'Forward' di browser (BFCache)
                window.addEventListener('pageshow', function (event) {
                    // event.persisted bernilai true jika halaman dimuat dari cache browser
                    if (event.persisted) {
                        const preloader = document.getElementById('page-preloader');
                        if (preloader) preloader.classList.add('loaded');
                    }
                });

                // Tangkap event klik pada link untuk memunculkan preloader
                const links = document.querySelectorAll('a');
                links.forEach(link => {
                    link.addEventListener('click', function(e) {
                        // Pastikan ini link valid internal (bukan anchor, js, atau tab baru)
                        const href = this.getAttribute('href');
                        if (
                            href && 
                            this.target !== '_blank' && 
                            !href.startsWith('#') && 
                            !href.startsWith('javascript:') && 
                            !this.hasAttribute('onclick') &&
                            !this.hasAttribute('data-bs-toggle') &&
                            !this.hasAttribute('data-toggle') &&
                            this.hostname === window.location.hostname &&
                            !e.ctrlKey && !e.shiftKey && !e.metaKey
                        ) {
                            e.preventDefault();
                            const targetUrl = this.href;
                            
                            // Munculkan preloader (kaca blur)
                            const preloader = document.getElementById('page-preloader');
                            if (preloader) {
                                preloader.classList.remove('loaded');
                                // Failsafe: sembunyikan kembali setelah 10 detik jika gagal pindah
                                setTimeout(() => {
                                    preloader.classList.add('loaded');
                                }, 10000);
                            }
                            
                            // Pindah halaman
                            setTimeout(() => {
                                window.location.href = targetUrl;
                            }, 150);
                        }
                    });
                });
            });
            </script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Global Count-Up Animation Logic (Fixed Duration: 1.5 seconds)
                    const animateCountUp = (selector, isRupiah = false) => {
                        const counters = document.querySelectorAll(selector);
                        counters.forEach(counter => {
                            const target = parseInt(counter.getAttribute('data-value')) || 0; 
                            const duration = 3000;
                            const frameDuration = 1000 / 60;
                            const totalFrames = Math.round(duration / frameDuration);
                            let frame = 0;

                            const updateCount = () => {
                                frame++;
                                const progress = frame / totalFrames;
                                const easeOutProgress = 1 - Math.pow(1 - progress, 3);
                                
                                const currentCount = Math.round(target * easeOutProgress);
                                
                                if (isRupiah) {
                                    counter.innerText = new Intl.NumberFormat('id-ID').format(currentCount);
                                } else {
                                    counter.innerText = currentCount;
                                }
                                
                                if (frame < totalFrames) {
                                    requestAnimationFrame(updateCount);
                                } else {
                                    if (isRupiah) {
                                        counter.innerText = new Intl.NumberFormat('id-ID').format(target);
                                    } else {
                                        counter.innerText = target;
                                    }
                                }
                            };
                            
                            setTimeout(() => {
                                requestAnimationFrame(updateCount);
                            }, 300);
                        });
                    };

                    animateCountUp('.count-up', false);
                    animateCountUp('.count-up-rupiah', true);

                    // Filter Interaktif Notifikasi Navbar & Drag/Scroll Handler
                    const notifDropdownEl = document.querySelector('.notif-dropdown-menu');
                    const filterScroll = document.querySelector('.notif-filter-scroll');
                    const prevArrow = document.querySelector('.notif-scroll-prev');
                    const nextArrow = document.querySelector('.notif-scroll-next');
                    let hasDragged = false;

                    function updateScrollArrows() {
                        if (!filterScroll) return;
                        const maxScroll = filterScroll.scrollWidth - filterScroll.clientWidth;
                        if (maxScroll <= 5) {
                            if (prevArrow) prevArrow.classList.add('d-none');
                            if (nextArrow) nextArrow.classList.add('d-none');
                            return;
                        }
                        if (prevArrow) {
                            if (filterScroll.scrollLeft > 8) {
                                prevArrow.classList.remove('d-none');
                            } else {
                                prevArrow.classList.add('d-none');
                            }
                        }
                        if (nextArrow) {
                            if (filterScroll.scrollLeft < maxScroll - 8) {
                                nextArrow.classList.remove('d-none');
                            } else {
                                nextArrow.classList.add('d-none');
                            }
                        }
                    }

                    if (filterScroll) {
                        let isDown = false;
                        let startX = 0;
                        let scrollStart = 0;

                        filterScroll.addEventListener('mousedown', function(e) {
                            // Jangan drag jika yang diklik tombol navigasi panah
                            if (e.target.closest('.notif-scroll-arrow')) return;
                            isDown = true;
                            hasDragged = false;
                            filterScroll.classList.add('dragging');
                            startX = e.pageX - filterScroll.offsetLeft;
                            scrollStart = filterScroll.scrollLeft;
                        });

                        window.addEventListener('mouseup', function() {
                            if (isDown) {
                                isDown = false;
                                filterScroll.classList.remove('dragging');
                            }
                        });

                        filterScroll.addEventListener('mouseleave', function() {
                            if (isDown) {
                                isDown = false;
                                filterScroll.classList.remove('dragging');
                            }
                        });

                        filterScroll.addEventListener('mousemove', function(e) {
                            if (!isDown) return;
                            e.preventDefault();
                            const x = e.pageX - filterScroll.offsetLeft;
                            const walk = (x - startX) * 1.5;
                            if (Math.abs(walk) > 4) {
                                hasDragged = true;
                            }
                            filterScroll.scrollLeft = scrollStart - walk;
                            updateScrollArrows();
                        });

                        // Mouse wheel horizontal scroll di desktop
                        filterScroll.addEventListener('wheel', function(e) {
                            if (e.deltaY !== 0) {
                                e.preventDefault();
                                filterScroll.scrollLeft += e.deltaY * 0.9;
                                updateScrollArrows();
                            }
                        }, { passive: false });

                        filterScroll.addEventListener('scroll', updateScrollArrows);

                        if (prevArrow) {
                            prevArrow.addEventListener('click', function(e) {
                                e.stopPropagation();
                                e.preventDefault();
                                filterScroll.scrollBy({ left: -140, behavior: 'smooth' });
                                setTimeout(updateScrollArrows, 250);
                            });
                        }

                        if (nextArrow) {
                            nextArrow.addEventListener('click', function(e) {
                                e.stopPropagation();
                                e.preventDefault();
                                filterScroll.scrollBy({ left: 140, behavior: 'smooth' });
                                setTimeout(updateScrollArrows, 250);
                            });
                        }
                    }

                    document.querySelectorAll('.notif-filter-btn').forEach(function(btn) {
                        btn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            e.preventDefault();

                            // Jika barusan selesai drag/geser mouse, abaikan klik
                            if (hasDragged) {
                                hasDragged = false;
                                return;
                            }

                            document.querySelectorAll('.notif-filter-btn').forEach(function(b) {
                                b.classList.remove('active');
                            });
                            this.classList.add('active');

                            const filter = this.getAttribute('data-filter');
                            const items = document.querySelectorAll('.notif-item');
                            const filteredEmpty = document.querySelector('.notif-filtered-empty');
                            let visibleCount = 0;

                            items.forEach(function(item) {
                                const cat = item.getAttribute('data-category');
                                if (filter === 'all' || cat === filter) {
                                    item.style.setProperty('display', 'flex', 'important');
                                    visibleCount++;
                                } else {
                                    item.style.setProperty('display', 'none', 'important');
                                }
                            });

                            if (filteredEmpty) {
                                if (visibleCount === 0 && items.length > 0) {
                                    filteredEmpty.classList.remove('d-none');
                                } else {
                                    filteredEmpty.classList.add('d-none');
                                }
                            }
                        });
                    });

                    // Reset posisi scroll pill filter & update panah saat notifikasi dibuka
                    if (notifDropdownEl) {
                        const parentDrop = notifDropdownEl.closest('.dropdown');
                        if (parentDrop) {
                            parentDrop.addEventListener('shown.bs.dropdown', function() {
                                if (filterScroll) {
                                    filterScroll.scrollLeft = 0;
                                    updateScrollArrows();
                                }
                            });
                        }
                    }
                });
            </script>
            @yield('modals')
            @stack('modals')
            @yield('scripts')
            @stack('scripts')
    @include('components.cropper-modal')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }
        });
    </script>
</body>

</html>


