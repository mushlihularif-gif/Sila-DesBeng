<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('Admin/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Dashboard - SidesBeng Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="" />
    <link rel="icon" type="image/x-icon" href="{{ asset('Admin/img/favicon/logoisewa.png') }}" />
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

        /* Gaya tombol Laporan BUMDes */
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

    <script src="{{ asset('Admin/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('Admin/js/config.js') }}"></script>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Sidebar -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
                        <span class="app-brand-logo demo">
                            <img src="{{ asset('Admin/img/illustrations/isewalogo.png') }}" alt="Logo"
                                width="130" height="130">
                        </span>
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

                    <!-- Unit Layanan -->
                    <li
                        class="menu-item {{ request()->is('admin/unit/penyewaan*') || request()->is('admin/unit/gas*') ? 'open active show' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-collection"></i>
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
                    </li>
                </ul>
                </li>
                <!-- Aktivitas -->
                <li
                    class="menu-item {{ request()->is('admin/aktivitas/permintaan-pengajuan*') || request()->is('admin/aktivitas/bukti-transaksi*') ? 'open active show' : '' }}">
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
                    </ul>
                </li>
                <!-- Data & Laporan -->
                <li
                    class="menu-item {{ request()->is('admin/laporan/transaksi*') || request()->is('admin/laporan/pendapatan*') || request()->is('admin/laporan/log*') ? 'open active show' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-bar-chart"></i>
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
                        <li class="menu-item {{ request()->routeIs('admin.laporan.log') ? 'active' : '' }}">
                            <a href="{{ route('admin.laporan.log') }}" class="menu-link">
                                <div data-i18n="Log Aktivitas">Log Aktivitas</div>
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- Pengaturan Sistem -->
                <li class="menu-item {{ request()->routeIs('admin.system-settings.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.system-settings.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-cog"></i>
                        <div>Pengaturan Sistem</div>
                    </a>
                </li>
                <!-- Manajemen Pengguna -->
                <li class="menu-item {{ request()->routeIs('admin.manajemen-pengguna.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.manajemen-pengguna.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-user"></i>
                        <div data-i18n="Manajemen Pengguna">Manajemen Pengguna</div>
                    </a>
                </li>
                <!-- Notifikasi -->
                <li class="menu-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.notifications.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-bell"></i>
                        <div data-i18n="Notifikasi">Notifikasi</div>
                    </a>
                </li>
                <!-- Profil SidesBeng -->
                <li class="menu-item {{ request()->is('admin/isewa/profile*') || request()->is('admin/isewa/developer*') ? 'active' : '' }}">
                    <a href="{{ route('admin.isewa.profile') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-info-circle"></i>
                        <div data-i18n="Profil SidesBeng">Profil SidesBeng</div>
                    </a>
                </li>
                <!-- Profil BUMDes -->
                <!-- Profil BUMDes -->
                <li class="menu-item {{ request()->routeIs('admin.isewa.profile-bumdes') || request()->routeIs('admin.isewa.bumdes.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.isewa.profile-bumdes') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-buildings"></i>
                        <div data-i18n="Profil BUMDes">Profil BUMDes</div>
                    </a>
                </li>
                </ul>
            </aside>
            <!-- Layout page -->
            <div class="layout-page">
                <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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
                            </div>
                        </div>
                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <!-- Nama Admin -->
                            <li class="nav-item lh-1 me-3">
                                <span class="fw-semibold">{{ Auth::user()->name ?? 'Admin' }}</span>
                            </li>
                            <!-- Profil Admin -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                                    data-bs-toggle="dropdown">
                                    @if(Auth::user() && Auth::user()->file)
                                        <img src="{{ route('media.avatar', ['filename' => basename(Auth::user()->file->path)]) }}" alt="Avatar"
                                            class="w-px-40 h-auto rounded-circle" />
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
                        <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
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
            <script async defer src="https://buttons.github.io/buttons.js"></script>
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
            });
            </script>
            @yield('scripts')
</body>

</html>
