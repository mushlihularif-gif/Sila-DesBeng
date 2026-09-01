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

                <!-- Unit Layanan (Dropdown) - bukan urusan Super Admin Sistem, ini katalog komersial per wilayah -->
                {{-- Staf ikut di sini; tiap sub-menunya masih disaring lagi
                     lewat hasUnitPermission() di bawah. --}}
                @if(in_array(auth()->user()->role, ['admin', 'admin_kecamatan', 'admin_desa', 'staff']))
                    @if(isset($hasActiveServices) && $hasActiveServices)
                        <li class="menu-item {{ (request()->is('admin/unit*') && !request()->is('admin/unit/supir*')) || request()->routeIs('admin.announcements.*') ? 'open active show' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <i class="menu-icon tf-icons bx bx-building-house"></i>
                                <div data-i18n="Unit Layanan">Unit Layanan</div>
                            </a>
                            <ul class="menu-sub">
                                @if(in_array('Penyewaan Alat', $activeServicesMenu ?? []) && auth()->user()->hasUnitPermission('sewa_alat'))
                                <li class="menu-item {{ request()->is('admin/unit/penyewaan*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.unit.penyewaan.index') }}" class="menu-link">
                                        <div data-i18n="Penyewaan Alat">Penyewaan Alat</div>
                                    </a>
                                </li>
                                @endif
                                @if(in_array('Penjualan Gas', $activeServicesMenu ?? []) && auth()->user()->hasUnitPermission('gas'))
                                <li class="menu-item {{ request()->is('admin/unit/gas*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.unit.penjualan_gas.index') }}" class="menu-link">
                                        <div data-i18n="Penjualan Gas">Penjualan Gas</div>
                                    </a>
                                </li>
                                @endif
                                @if(in_array('Penyewaan Mobil', $activeServicesMenu ?? []) && auth()->user()->hasUnitPermission('sewa_mobil'))
                                <li class="menu-item {{ request()->is('admin/unit/mobil*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.unit.mobil.index') }}" class="menu-link">
                                        <div data-i18n="Penyewaan Mobil">Penyewaan Mobil</div>
                                    </a>
                                </li>
                                @endif
                                @if(in_array('Fasilitas Umum', $activeServicesMenu ?? []) && auth()->user()->hasUnitPermission('fasilitas_umum'))
                                <li class="menu-item {{ request()->is('admin/unit/fasilitas_umum*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.unit.fasilitas_umum.index') }}" class="menu-link">
                                        <div data-i18n="Fasilitas Umum">Fasilitas Umum</div>
                                    </a>
                                </li>
                                @endif
                                @if(in_array('Pasar Daerah', $activeServicesMenu ?? []) && auth()->user()->hasUnitPermission('pasar_daerah'))
                                <li class="menu-item {{ request()->is('admin/unit/pasar-daerah*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.unit.pasar_daerah.index') }}" class="menu-link">
                                        <div data-i18n="Pasar Daerah">Pasar Daerah</div>
                                    </a>
                                </li>
                                @endif

                                {{-- Tidak terikat layanan aktif wilayah, jadi cukup izinnya. --}}
                                @if(auth()->user()->hasUnitPermission('kabar_informasi'))
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
                            <a href="{{ $settingsRoute }}" class="menu-link text-warning">
                                <i class="menu-icon tf-icons bx bx-building-house"></i>
                                <div>Ayo aktifkan layanan daerah mu!</div>
                            </a>
                        </li>
                    @endif
                @endif

                <!-- Manajemen (Dropdown) -->
                {{-- Seluruh isinya data warga/wilayah; staf platform tidak punya satu pun
                     anak menu di sini, jadi grupnya disembunyikan agar tidak jadi dropdown hampa. --}}
                @if(auth()->user()->role !== 'staff' || auth()->user()->bolehSalahSatu(\App\Models\User::kunciIzinGrup('Manajemen')))
                <li class="menu-item {{ request()->is('admin/manajemen-pengguna*') || request()->is('admin/kelola-wilayah*') || request()->is('admin/banners*') || request()->routeIs('admin.warga.mutasi.*') || request()->routeIs('admin.kyc.*') || request()->routeIs('admin.staff.*') ? 'open active show' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-briefcase"></i>
                        <div data-i18n="Manajemen">Manajemen</div>
                    </a>
                    <ul class="menu-sub">
                        {{-- Pengguna: data warga per wilayah. Staf platform bisa diberi izin khusus. --}}
                        @if(in_array(auth()->user()->role, ['admin', 'admin_kecamatan', 'admin_desa']))
                        <li class="menu-item {{ request()->routeIs('admin.manajemen-pengguna.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.manajemen-pengguna.index') }}" class="menu-link">
                                <div>Pengguna</div>
                            </a>
                        </li>
                        @endif

                        {{-- Kelola Staf: mendaftarkan akun operator supaya bisa login.
                             Super Admin Sistem ikut berwenang di sini karena ini
                             urusan akses ke aplikasi, bukan data warga per wilayah. --}}
                        @if(auth()->user()->bolehMenu(['super_admin', 'admin', 'admin_kecamatan', 'admin_desa'], 'platform_staf'))
                        <li class="menu-item {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.staff.index') }}" class="menu-link">
                                <div>Kelola Staf</div>
                            </a>
                        </li>
                        @endif

                        @if(in_array(auth()->user()->role, ['admin', 'admin_kecamatan', 'admin_desa']))
                        <li class="menu-item {{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.kyc.index') }}" class="menu-link">
                                <div>Verifikasi Identitas</div>
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->bolehMenu(['super_admin', 'admin', 'admin_kecamatan'], 'platform_banner'))
                        <li class="menu-item {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.banners.index') }}" class="menu-link">
                                <div>Banner</div>
                            </a>
                        </li>
                        @endif
                        {{-- Mutasi Penduduk & Kelola Wilayah: administrasi kependudukan/wilayah, bukan urusan Super Admin Sistem --}}
                        @if(in_array(auth()->user()->role, ['admin', 'admin_kecamatan', 'admin_desa']))
                        <li class="menu-item {{ request()->routeIs('admin.warga.mutasi.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.warga.mutasi.index') }}" class="menu-link">
                                <div>Mutasi Penduduk</div>
                            </a>
                        </li>
                        @endif

                        @if(in_array(auth()->user()->role, ['admin', 'admin_kecamatan', 'admin_desa', 'admin_rw']))
                        <li class="menu-item {{ request()->routeIs('admin.kelola-wilayah.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.kelola-wilayah.index') }}" class="menu-link">
                                <div>Kelola Wilayah</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                <!-- Aktivitas -->
                {{-- Seluruh isi grup ini operasional per wilayah. Setelah Persetujuan
                     Mitra tidak lagi untuk Super Admin, grup ini kosong bagi mereka —
                     jadi super_admin dikeluarkan supaya tidak muncul dropdown hampa. --}}
                @if(in_array(auth()->user()->role, ['admin', 'admin_kecamatan', 'admin_desa', 'admin_rw', 'admin_rt']) || auth()->user()->punyaIzinUnit())
                <li
                    class="menu-item {{ request()->is('admin/aktivitas/permintaan-pengajuan*') || request()->is('admin/aktivitas/bukti-transaksi*') || request()->is('admin/kemitraan*') || (request()->routeIs('admin.pelaporan.*') && !request()->routeIs('admin.pelaporan.archive')) ? 'open active show' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-time"></i>
                        <div data-i18n="Permintaan & Aktivitas">Permintaan & Aktivitas</div>
                    </a>
                    <ul class="menu-sub">
                        {{-- Di sinilah pekerjaan harian staf unit berada: pesanan masuk
                             dan bukti bayar, keduanya sudah disaring per unit oleh
                             RequestController dan TransactionController. --}}
                        @if(in_array(auth()->user()->role, ['admin', 'admin_kecamatan', 'admin_desa', 'admin_rw', 'admin_rt']) || auth()->user()->punyaIzinUnit())
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
                        @endif
                        {{-- Persetujuan Mitra: kewenangan pemerintahan berjenjang
                             (kabupaten, kecamatan, desa). Super Admin Sistem
                             (Diskominfotik) mengurus platform, bukan menyetujui
                             mitra per wilayah. --}}
                        @if(in_array(auth()->user()->role, ['admin', 'admin_kecamatan', 'admin_desa']))
                        <li class="menu-item {{ request()->routeIs('admin.kemitraan.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.kemitraan.index') }}" class="menu-link">
                                <div class="notranslate" translate="no">Persetujuan Mitra</div>
                            </a>
                        </li>
                        @endif
                        @if(in_array(auth()->user()->role, ['admin', 'admin_kecamatan', 'admin_desa', 'admin_rw', 'admin_rt']))
                        <li class="menu-item {{ request()->routeIs('admin.pelaporan.*') && !request()->routeIs('admin.pelaporan.archive') ? 'active' : '' }}">
                            <a href="{{ Route::has('admin.pelaporan.index') ? route('admin.pelaporan.index') : '#' }}" class="menu-link">
                                <div>Pelaporan Warga</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif
                <!-- Data & Laporan (Dropdown) -->
                {{-- Laporan operasional/keuangan per wilayah. Anak-anaknya disaring dengan
                     `role !== super_admin`, kondisi yang justru DILEWATI role staff —
                     itulah kenapa staf platform sempat melihat Laporan Transaksi dkk. --}}
                @if(auth()->user()->role !== 'staff'
                    || auth()->user()->punyaIzinUnit()
                    || auth()->user()->bolehSalahSatu(\App\Models\User::kunciIzinGrup('Data & Laporan')))
                <li class="menu-item {{ request()->routeIs('admin.laporan.*') || request()->routeIs('admin.pelaporan.archive') ? 'open active show' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                        <div data-i18n="Data & Laporan">Data & Laporan</div>
                    </a>
                    <ul class="menu-sub">
                        {{-- Bukti Pelaporan Warga tetap khusus admin wilayah: route arsipnya
                             dijaga staff.permission:pelaporan_warga, jadi menampilkannya untuk
                             staf platform hanya akan berujung ditolak. --}}
                        @if(auth()->user()->bolehMenu(['admin', 'admin_kecamatan', 'admin_desa', 'admin_rw', 'admin_rt'], 'pelaporan_warga'))
                        <li class="menu-item {{ request()->routeIs('admin.pelaporan.archive') ? 'active' : '' }}">
                            <a href="{{ route('admin.pelaporan.archive') }}" class="menu-link">
                                <div data-i18n="Bukti Pelaporan Warga">Bukti Pelaporan Warga</div>
                            </a>
                        </li>
                        @endif

                        {{-- Laporan transaksi/pendapatan/wilayah: boleh dibuka staf platform berizin --}}
                        @if(auth()->user()->role !== 'super_admin' && ! auth()->user()->hanyaPlatform())
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
                        @endif
                        @if(auth()->user()->bolehMenu(['super_admin', 'admin', 'admin_kecamatan'], 'platform_aktivitas'))
                        <li class="menu-item {{ request()->routeIs('admin.laporan.log') ? 'active' : '' }}">
                            <a href="{{ route('admin.laporan.log') }}" class="menu-link">
                                <div data-i18n="Log Aktivitas">Log Aktivitas</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                <!-- Pengaturan (Dropdown) - layanan & pembayaran milik entitas kabupaten/kecamatan/desa sendiri, sudah digantikan "Sistem Platform" untuk Super Admin Sistem -->
                @if(in_array(auth()->user()->role, ['admin', 'admin_kecamatan', 'admin_desa']))
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

                <!-- Sistem Platform. Tampil untuk Super Admin Sistem dan akun staf
                     platform yang diberi izin; tiap item disaring per izinnya. -->
                @if(auth()->user()->bolehAksesPlatform())
                <li class="menu-item {{ request()->routeIs('admin.sistem-platform.*') ? 'open active show' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-server"></i>
                        <div data-i18n="Sistem Platform">Sistem Platform</div>
                    </a>
                    <ul class="menu-sub">
                        @if(auth()->user()->hasPlatformPermission('platform_integrasi'))
                        <li class="menu-item {{ request()->routeIs('admin.sistem-platform.gateway') ? 'active' : '' }}">
                            <a href="{{ route('admin.sistem-platform.gateway') }}" class="menu-link">
                                <div>Integrasi Payment Gateway</div>
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPlatformPermission('platform_monitoring'))
                        <li class="menu-item {{ request()->routeIs('admin.sistem-platform.monitoring') ? 'active' : '' }}">
                            <a href="{{ route('admin.sistem-platform.monitoring') }}" class="menu-link">
                                <div>Monitoring Transaksi</div>
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPlatformPermission('platform_keamanan'))
                        <li class="menu-item {{ request()->routeIs('admin.sistem-platform.security-log') ? 'active' : '' }}">
                            <a href="{{ route('admin.sistem-platform.security-log') }}" class="menu-link">
                                <div>Log Keamanan & Audit</div>
                            </a>
                        </li>
                        @endif
                        @if(auth()->user()->hasPlatformPermission('platform_biaya'))
                        <li class="menu-item {{ request()->routeIs('admin.sistem-platform.expenses') ? 'active' : '' }}">
                            <a href="{{ route('admin.sistem-platform.expenses') }}" class="menu-link">
                                <div>Biaya Server & Domain</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                <!-- Profil & Info (Dropdown) -->
                <li class="menu-item {{ request()->is('admin/siladesbeng/profile*') || request()->is('admin/siladesbeng/developer*') || request()->routeIs('admin.siladesbeng.bumdes.index') || request()->routeIs('admin.siladesbeng.bumdes.*') ? 'open active show' : '' }}">
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
                        <li class="menu-item {{ request()->routeIs('admin.siladesbeng.bumdes.index') || request()->routeIs('admin.siladesbeng.bumdes.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.siladesbeng.bumdes.index') }}" class="menu-link">
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
                                    'admin.siladesbeng.*',
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
                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <!-- Notifikasi Bell Icon -->
                            <li class="nav-item dropdown me-3">
                                <a class="nav-link dropdown-toggle hide-arrow position-relative" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                    <i class="bx bx-bell bx-sm"></i>
                                    @php
                                        $notifQuery = \App\Models\AdminNotification::query();
                                        if (in_array(auth()->user()->role, ['super_admin', 'admin'])) {
                                            $notifQuery->whereNull('region_id');
                                        } else {
                                            $notifQuery->where('region_id', auth()->user()->region_id);
                                        }
                                        $unreadCount = (clone $notifQuery)->where('is_read', false)->count();
                                    @endphp
                                    @if($unreadCount > 0)
                                    <span class="badge bg-danger rounded-pill badge-notifications position-absolute" style="top: -2px; right: -6px; font-size: 10px; min-width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end py-0" style="width: 420px; max-height: 520px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden;">
                                    <li class="dropdown-menu-header border-bottom bg-light">
                                        <div class="dropdown-header d-flex align-items-center justify-content-between py-3 px-4">
                                            <h6 class="mb-0 fw-bold text-dark fs-5">Notifikasi</h6>
                                            @if($unreadCount > 0)
                                            <span class="badge rounded-pill bg-primary px-3 py-2 shadow-sm">{{ $unreadCount }} Baru</span>
                                            @endif
                                        </div>
                                        <!-- Pill Tabs for 6 Layanan -->
                                        <div class="px-4 pb-3 overflow-auto d-flex align-items-center" style="white-space: nowrap; scrollbar-width: none;">
                                            <button class="btn btn-sm btn-primary rounded-pill me-2 notif-filter-btn active fw-semibold shadow-sm" data-filter="all">Semua</button>
                                            <button class="btn btn-sm btn-outline-secondary rounded-pill me-2 notif-filter-btn fw-medium" data-filter="mobil">Sewa Mobil</button>
                                            <button class="btn btn-sm btn-outline-secondary rounded-pill me-2 notif-filter-btn fw-medium" data-filter="fasilitas">Fasilitas Umum</button>
                                            <button class="btn btn-sm btn-outline-secondary rounded-pill me-2 notif-filter-btn fw-medium" data-filter="gas">Gas LPG</button>
                                            <button class="btn btn-sm btn-outline-secondary rounded-pill me-2 notif-filter-btn fw-medium" data-filter="pasar">Pasar Desa</button>
                                            <button class="btn btn-sm btn-outline-secondary rounded-pill me-2 notif-filter-btn fw-medium" data-filter="mutasi">Administrasi</button>
                                            <button class="btn btn-sm btn-outline-secondary rounded-pill notif-filter-btn fw-medium" data-filter="laporan">Laporan Warga</button>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="notif-list-container" style="max-height: 350px; overflow-y: auto;">
                                            @php
                                                $recentNotifications = (clone $notifQuery)->latest()->take(30)->get();
                                            @endphp
                                            @forelse($recentNotifications as $notif)
                                            @php
                                                // Mapping icon and category
                                                $cat = 'lainnya';
                                                $icon = 'bx-bell';
                                                $color = 'primary';
                                                $bg = 'rgba(105, 108, 255, 0.08)';

                                                if (in_array($notif->type, ['cancellation_request', 'mobil_order', 'rental_mobil'])) {
                                                    $cat = 'mobil'; $icon = 'bx-car'; $color = 'info'; $bg = 'rgba(3, 195, 236, 0.08)';
                                                } elseif (in_array($notif->type, ['fasilitas_order', 'rental_fasilitas'])) {
                                                    $cat = 'fasilitas'; $icon = 'bx-building-house'; $color = 'success'; $bg = 'rgba(113, 221, 55, 0.08)';
                                                } elseif ($notif->type === 'gas_order') {
                                                    $cat = 'gas'; $icon = 'bx-gas-pump'; $color = 'warning'; $bg = 'rgba(255, 171, 0, 0.08)';
                                                } elseif (in_array($notif->type, ['pasar_order', 'pasar'])) {
                                                    $cat = 'pasar'; $icon = 'bx-store-alt'; $color = 'danger'; $bg = 'rgba(255, 62, 29, 0.08)';
                                                } elseif ($notif->type === 'mutasi') {
                                                    $cat = 'mutasi'; $icon = 'bx-id-card'; $color = 'secondary'; $bg = 'rgba(133, 146, 163, 0.08)';
                                                } elseif (in_array($notif->type, ['laporan', 'pengumuman'])) {
                                                    $cat = 'laporan'; $icon = 'bx-message-square-error'; $color = 'danger'; $bg = 'rgba(255, 62, 29, 0.08)';
                                                }
                                            @endphp
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="dropdown-item notif-item d-flex align-items-start gap-3 py-3 px-4 border-bottom" data-category="{{ $cat }}" style="white-space: normal; {{ !$notif->is_read ? 'background-color: '.$bg.';' : '' }} transition: all 0.2s;">
                                                <div class="flex-shrink-0 mt-1">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm {{ !$notif->is_read ? 'bg-'.$color : 'bg-label-secondary' }}" style="width: 42px; height: 42px;">
                                                        <i class="bx {{ $icon }} fs-4 {{ !$notif->is_read ? 'text-white' : 'text-secondary' }}"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-bold {{ !$notif->is_read ? 'text-dark' : 'text-muted' }}">{{ $notif->title }}</h6>
                                                    <p class="mb-2 {{ !$notif->is_read ? 'text-body' : 'text-muted' }} small" style="line-height: 1.5;">{{ Str::limit($notif->message, 80) }}</p>
                                                    <small class="text-muted d-flex align-items-center fw-medium" style="font-size: 0.75rem;">
                                                        <i class="bx bx-time-five me-1"></i> {{ $notif->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                                @if(!$notif->is_read)
                                                <div class="flex-shrink-0 align-self-center">
                                                    <span class="badge badge-dot bg-{{ $color }} shadow-sm p-1"></span>
                                                </div>
                                                @endif
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
                                    <li class="dropdown-menu-footer border-top bg-light">
                                        <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="dropdown-item text-center py-3 text-primary fw-bold">
                                            <i class="bx bx-list-ul me-2"></i>Lihat Semua Aktivitas
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
                                    {{ Auth::user()->labelRole() }}
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
            // ⭐ Tangani Pesan Flash Sesi saat Halaman Dimuat
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
                });
            </script>
            @yield('modals')
            @stack('modals')
            @yield('scripts')
            @stack('scripts')
    @include('components.cropper-modal')

    {{-- Panel kotak masuk Gmail. Ditaruh di layout, bukan di halaman dashboard,
         karena super_admin justru dialihkan dari dashboard ke Monitoring
         (lihat DashboardController::index) sehingga panel tidak akan pernah
         terlihat kalau menumpang di sana. Partial-nya mandiri (style & script
         inline), jadi aman di-include setelah @stack('styles') dirender. --}}
    @auth
        @if(auth()->user()->hasPlatformPermission('platform_inbox'))
            @include('admin.partials.inbox-rail')
        @endif
    @endauth
</body>

</html>
