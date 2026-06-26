<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('Admin/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Dashboard - SiladesBeng Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="" />
    <link rel="icon" type="image/png" href="{{ asset('Admin/img/illustrations/logodomain.png') }}?v={{ time() }}" />
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
            <img src="{{ asset('Admin/img/illustrations/logodomain.png') }}" alt="Logo" class="preloader-logo">
        </div>
    </div>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Sidebar -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
                        <span class="app-brand-logo demo">
                            <img src="{{ asset('Admin/img/illustrations/logodomain.png') }}" alt="Logo"
                                style="max-height: 40px; width: auto; object-fit: contain;">
                        </span>
                        <span class="app-brand-text demo menu-text fw-bolder ms-2 fs-4" style="text-transform: capitalize;">Administrator</span>
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
                <li class="menu-item {{ request()->is('admin/unit*') ? 'open active show' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-building-house"></i>
                        <div data-i18n="Unit Layanan">Unit Layanan</div>
                    </a>
                    <ul class="menu-sub">
                            <li class="menu-item {{ request()->is('admin/unit/penyewaan*') ? 'active' : '' }}">
                                <a href="{{ route('admin.unit.penyewaan.index') }}" class="menu-link">
                                    <div data-i18n="Penyewaan Alat">Penyewaan Alat</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->is('admin/unit/gas*') ? 'active' : '' }}">
                                <a href="{{ route('admin.unit.penjualan_gas.index') }}" class="menu-link">
                                    <div data-i18n="Penjualan Gas">Penjualan Gas</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->is('admin/unit/mobil*') ? 'active' : '' }}">
                                <a href="{{ route('admin.unit.mobil.index') }}" class="menu-link">
                                    <div data-i18n="Penyewaan Mobil">Penyewaan Mobil</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->is('admin/unit/fasilitas_umum*') ? 'active' : '' }}">
                                <a href="{{ route('admin.unit.fasilitas_umum.index') }}" class="menu-link">
                                    <div data-i18n="Fasilitas Umum">Fasilitas Umum</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Manajemen (Dropdown) -->
                <li class="menu-item {{ request()->is('admin/manajemen-pengguna*') || request()->is('admin/kemitraan*') || request()->is('admin/kelola-wilayah*') || request()->is('admin/banners*') || request()->is('admin/announcements*') || request()->routeIs('lurah.laporan.*') ? 'open active show' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-briefcase"></i>
                        <div data-i18n="Manajemen">Manajemen</div>
                    </a>
                    <ul class="menu-sub">
                        @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan']))
                        <li class="menu-item {{ request()->routeIs('admin.manajemen-pengguna.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.manajemen-pengguna.index') }}" class="menu-link">
                                <div>Pengguna</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('admin.kemitraan.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.kemitraan.index') }}" class="menu-link">
                                <div class="notranslate" translate="no">Persetujuan Mitra</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.banners.index') }}" class="menu-link">
                                <div>Banner</div>
                            </a>
                        </li>
                        @endif
                        <li class="menu-item {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.announcements.index') }}" class="menu-link">
                                <div>Pengumuman</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('lurah.laporan.*') ? 'active' : '' }}">
                            <a href="{{ route('lurah.laporan.index') }}" class="menu-link">
                                <div>Pelaporan Warga</div>
                            </a>
                        </li>
                        @if(in_array(auth()->user()->role, ['admin_kecamatan', 'admin_desa', 'lurah', 'admin_rw', 'super_admin', 'admin']))
                        <li class="menu-item {{ request()->routeIs('admin.kelola-wilayah.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.kelola-wilayah.index') }}" class="menu-link">
                                <div>Kelola Wilayah</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>

                <!-- Aktivitas -->
                @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa']))
                <li
                    class="menu-item {{ request()->is('admin/aktivitas/permintaan-pengajuan*') || request()->is('admin/aktivitas/bukti-transaksi*') || request()->routeIs('admin.laporan.log') ? 'open active show' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-time"></i>
                        <div data-i18n="Aktivitas">Aktivitas</div>
                    </a>
                    <ul class="menu-sub">
                        <li
                            class="menu-item {{ request()->is('admin/aktivitas/permintaan-pengajuan*') ? 'active' : '' }}">
                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="menu-link">
                                <div data-i18n="Permintaan & Pengajuan">Permintaan & Pengajuan</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->is('admin/aktivitas/bukti-transaksi*') ? 'active' : '' }}">
                            <a href="{{ route('admin.aktivitas.bukti-transaksi.index') }}" class="menu-link">
                                <div data-i18n="Bukti Transaksi">Bukti Transaksi</div>
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
                <!-- Data & Laporan (Dropdown) -->
                <li class="menu-item {{ request()->routeIs('admin.laporan.*') && !request()->routeIs('admin.laporan.log') ? 'open active show' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                        <div data-i18n="Data & Laporan">Data & Laporan</div>
                    </a>
                    <ul class="menu-sub">
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
                    </ul>
                </li>
                @endif

                <!-- Pengaturan (Dropdown) -->
                @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan']))
                <li class="menu-item {{ request()->routeIs('admin.system-settings.*') || request()->routeIs('admin.region-settings.*') ? 'open active show' : '' }}">
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
                        <li class="menu-item {{ request()->routeIs('admin.system-settings.payment') ? 'active' : '' }}">
                            <a href="{{ route('admin.system-settings.payment') }}" class="menu-link">
                                <div>Pembayaran Pusat</div>
                            </a>
                        </li>
                        @endif
                        <li class="menu-item {{ request()->routeIs('admin.region-settings.index') ? 'active' : '' }}">
                            <a href="{{ route('admin.region-settings.index') }}" class="menu-link">
                                <div>Layanan Wilayah</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('admin.region-settings.payment') ? 'active' : '' }}">
                            <a href="{{ route('admin.region-settings.payment') }}" class="menu-link">
                                <div>Pembayaran Wilayah</div>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif


                <!-- Profil & Info (Dropdown) -->
                <li class="menu-item {{ request()->is('admin/siladesbeng/profile*') || request()->is('admin/siladesbeng/developer*') || request()->routeIs('admin.siladesbeng.profile-bumdes') || request()->routeIs('admin.siladesbeng.bumdes.*') ? 'open active show' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-info-circle"></i>
                        <div data-i18n="Profil & Info">Profil & Info</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item {{ request()->routeIs('admin.siladesbeng.profile') || request()->routeIs('admin.siladesbeng.developer.profile') ? 'active' : '' }}">
                            <a href="{{ route('admin.siladesbeng.profile') }}" class="menu-link">
                                <div>SiladesBeng</div>
                            </a>
                        </li>
                        <li class="menu-item {{ request()->routeIs('admin.siladesbeng.profile-bumdes') || request()->routeIs('admin.siladesbeng.bumdes.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.siladesbeng.profile-bumdes') }}" class="menu-link">
                                <div>Pemerintah Desa</div>
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
                                    'admin.siladesbeng.*',
                                    'admin.kemitraan.*',
                                    'admin.manajemen-pengguna.*',
                                    'lurah.laporan.*',
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
                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <!-- Notifikasi Bell Icon -->
                            <li class="nav-item dropdown me-3">
                                <a class="nav-link dropdown-toggle hide-arrow position-relative" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                    <i class="bx bx-bell bx-sm"></i>
                                    @php
                                        $unreadCount = \App\Models\AdminNotification::where('is_read', false)->count();
                                    @endphp
                                    @if($unreadCount > 0)
                                    <span class="badge bg-danger rounded-pill badge-notifications position-absolute" style="top: -2px; right: -6px; font-size: 10px; min-width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end py-0" style="width: 380px; max-height: 420px;">
                                    <li class="dropdown-menu-header border-bottom">
                                        <div class="dropdown-header d-flex align-items-center justify-content-between py-3 px-4">
                                            <h6 class="mb-0 fw-bold">Notifikasi Admin</h6>
                                            @if($unreadCount > 0)
                                            <span class="badge rounded-pill bg-label-primary">{{ $unreadCount }} Baru</span>
                                            @endif
                                        </div>
                                    </li>
                                    <li>
                                        <div style="max-height: 280px; overflow-y: auto;">
                                            @php
                                                $recentNotifications = \App\Models\AdminNotification::latest()->take(5)->get();
                                            @endphp
                                            @forelse($recentNotifications as $notif)
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="dropdown-item d-flex align-items-start gap-3 py-3 px-4" style="white-space: normal; {{ !$notif->is_read ? 'background-color: rgba(105, 108, 255, 0.08);' : '' }}">
                                                <div class="flex-shrink-0">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center {{ !$notif->is_read ? 'bg-label-primary' : 'bg-label-secondary' }}" style="width: 36px; height: 36px;">
                                                        <i class="bx {{ $notif->type === 'cancellation_request' ? 'bx-error-circle' : ($notif->type === 'gas_order' ? 'bx-gas-pump' : 'bx-bell') }} {{ !$notif->is_read ? 'text-primary' : 'text-secondary' }}"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <p class="mb-0 fw-semibold small">{{ $notif->title }}</p>
                                                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">{{ \Illuminate\Support\Str::limit($notif->message, 50) }}</p>
                                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $notif->created_at->diffForHumans() }}</small>
                                                </div>
                                            </a>
                                            @empty
                                            <div class="text-center py-4">
                                                <i class="bx bx-bell-off fs-3 text-muted mb-2"></i>
                                                <p class="text-muted small mb-0">Tidak ada notifikasi</p>
                                            </div>
                                            @endforelse
                                        </div>
                                    </li>
                                    <li class="dropdown-menu-footer border-top">
                                        <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="dropdown-item text-center py-3 text-primary fw-semibold">
                                            <i class="bx bx-list-ul me-1"></i> Lihat Semua Permintaan
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
                                            'lurah' => 'Kepala Desa',
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

                    @yield('content')
                    
                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl d-flex flex-wrap justify-content-between py-3 flex-md-row flex-column text-muted">
                            <div class="mb-2 mb-md-0">
                                © {{ date('Y') }} Sistem Sinergi Layanan dan Aspirasi Desa di Kabupaten Bengkalis
                            </div>
                            <div>
                                Made with <span class="text-danger">❤️</span> by <a href="#" target="_blank" class="footer-link fw-bolder">SiladesBeng Project Team</a>
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
                <div class="layout-overlay layout-menu-toggle"></div>
            </div>
            <script src="{{ asset('Admin/vendor/libs/jquery/jquery.js') }}"></script>
            <script src="{{ asset('Admin/vendor/libs/popper/popper.js') }}"></script>
            <script src="{{ asset('Admin/vendor/js/bootstrap.js') }}"></script>
            <script src="{{ asset('Admin/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
            <script src="{{ asset('Admin/vendor/js/menu.js') }}"></script>
            <script src="{{ asset('Admin/vendor/libs/apex-charts/apexcharts.js') }}"></script>
            <script src="{{ asset('Admin/js/main.js') }}"></script>
            <script src="{{ asset('Admin/js/dashboards-analytics.js') }}"></script>
            <!-- Skrip untuk animasi dan fungsionalitas -->
            <script>
                // Fungsi notifikasi sekarang didefinisikan di dashboard/index.blade.php
                // menggunakan SweetAlert2 untuk UX yang lebih baik

                // Fungsi untuk menghasilkan laporan
                function generateReport() {
                    showToast('info',
                        'Laporan PDF sedang diproses. Fitur ini akan terhubung ke backend Laravel untuk menghasilkan file.');
                    // In a real application, this would trigger a server-side PDF generation
                    setTimeout(() => {
                        showToast('success', 'Laporan berhasil dibuat dan siap diunduh!');
                    }, 2000);
                }

                // Fungsi untuk menampilkan notifikasi toast
                function showToast(type, message) {
                    if (typeof Swal !== 'undefined') {
                        // Petakan tipe bootstrap ke tipe sweetalert
                        const iconType = type === 'danger' ? 'error' : type;
                        
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: iconType,
                            title: message,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            },
                            customClass: {
                                popup: 'colored-toast'
                            }
                        });
                    } else {
                        console.warn('SweetAlert2 is not loaded, falling back to alert');
                        alert(message);
                    }
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
            // ⭐ Tangani Pesan Flash Sesi saat Halaman Dimuat
            document.addEventListener('DOMContentLoaded', function() {
                @if(session('success'))
                    showToast('success', "{{ session('success') }}");
                @endif

                @if(session('error'))
                    showToast('danger', "{{ session('error') }}");
                @endif

                @if(session('info'))
                    showToast('info', "{{ session('info') }}");
                @endif

                @if(session('warning'))
                    showToast('warning', "{{ session('warning') }}");
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
                            this.hostname === window.location.hostname &&
                            !e.ctrlKey && !e.shiftKey && !e.metaKey
                        ) {
                            e.preventDefault();
                            const targetUrl = this.href;
                            
                            // Munculkan preloader (kaca blur)
                            const preloader = document.getElementById('page-preloader');
                            if (preloader) preloader.classList.remove('loaded');
                            
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
                });
            </script>
            @yield('scripts')
            @yield('modals')
            @stack('modals')
</body>

</html>
