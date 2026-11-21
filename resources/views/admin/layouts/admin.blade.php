<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}" translate="no" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('Admin/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0, maximum-scale=5.0" />
    <title>Dashboard - SegiDaerah Admin</title>
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
    <link rel="stylesheet" href="{{ asset('vendor/cropperjs/cropper.min.css') }}" />
    <script src="{{ asset('vendor/cropperjs/cropper.min.js') }}"></script>
    <!-- CSS Kustom untuk Gaya -->
    <style>
        /* Pencegahan Overflow Horizontal & Optimalisasi Mobile Global */
        html, body {
            overflow-x: hidden !important;
            max-width: 100vw !important;
            width: 100% !important;
            position: relative;
        }

        .layout-wrapper,
        .layout-container,
        .layout-page,
        .content-wrapper {
            overflow-x: hidden !important;
            max-width: 100vw !important;
            width: 100% !important;
            min-width: 0 !important;
        }

        @media (max-width: 575.98px) {
            /* Kompaksi padding wadah untuk layar ponsel (< 576px) */
            .container-xxl,
            .container-fluid,
            .container-p-x {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            .container-p-y:not([class^=pt-]):not([class*=" pt-"]) {
                padding-top: 0.75rem !important;
            }
            .container-p-y:not([class^=pb-]):not([class*=" pb-"]) {
                padding-bottom: 0.75rem !important;
            }

            /* Navbar detached pas presisi di mobile */
            .layout-navbar.navbar-detached {
                width: calc(100% - 1.5rem) !important;
                margin: 0.5rem auto 0 !important;
                padding: 0 0.75rem !important;
            }

            #headerSearchInput {
                max-width: 110px !important;
            }
        }

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

        /* Notifikasi Dropdown Styles & Responsive Optimization (Dashboard Admin Theme) */
        .notif-dropdown-menu {
            max-height: 540px !important;
            border-radius: 18px !important;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.14), 0 2px 8px rgba(15, 23, 42, 0.06) !important;
            overflow: hidden !important;
            border: 1px solid #e2e8f0 !important;
            background: #ffffff !important;
            padding: 0 !important;
            transition: width 0.22s ease, max-height 0.22s ease;
        }

        .notif-dropdown-menu.show {
            display: flex !important;
            flex-direction: column !important;
        }

        .notif-dropdown-menu > li {
            flex-shrink: 0;
        }

        .notif-dropdown-menu > li.notif-body-li {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }

        @media (min-width: 576px) {
            .notif-dropdown-menu {
                width: 420px !important;
                max-width: 92vw !important;
                right: 0 !important;
                left: auto !important;
                transform: none !important;
            }

            .notif-dropdown-menu.is-expanded {
                width: min(680px, calc(100vw - 2rem)) !important;
                max-width: calc(100vw - 2rem) !important;
                max-height: min(84vh, 760px) !important;
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
                max-height: 78vh !important;
                z-index: 1090 !important;
                border-radius: 16px !important;
            }

            .notif-dropdown-menu.is-expanded {
                top: 10px !important;
                bottom: 10px !important;
                left: 8px !important;
                right: 8px !important;
                max-height: calc(100vh - 20px) !important;
                height: calc(100vh - 20px) !important;
            }
        }

        .notif-header-bar {
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
            border-bottom: 1px solid #eef2f6;
        }

        .notif-action-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #475569;
            cursor: pointer;
            transition: all 0.16s ease;
            line-height: 1.3;
        }

        .notif-action-pill:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .notif-action-pill.notif-action-primary {
            color: #0d6efd;
            border-color: #dbeafe;
            background: #eff6ff;
        }

        .notif-action-pill.notif-action-primary:hover {
            background: #dbeafe;
            border-color: #bfdbfe;
            color: #0a58ca;
        }

        .notif-action-pill.notif-action-danger {
            color: #dc2626;
            border-color: #fecaca;
            background: #fef2f2;
        }

        .notif-action-pill.notif-action-danger:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #b91c1c;
        }

        .notif-confirm-bar {
            background: #fef2f2;
            border-top: 1px solid #fecaca;
            padding: 9px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Filter Scroll Container with Soft Edge Fade */
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
            gap: 6px;
            padding: 2px 26px 4px 2px;
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
            transform: translateY(-54%);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.1);
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            padding: 0;
            font-size: 1rem;
            transition: all 0.18s ease;
        }

        .notif-scroll-arrow:hover {
            background: #0d6efd;
            color: #ffffff;
            border-color: #0d6efd;
        }

        .notif-scroll-prev {
            left: 0;
            box-shadow: 6px 0 12px 4px #ffffff, 0 2px 6px rgba(15, 23, 42, 0.1);
        }

        .notif-scroll-next {
            right: 0;
            box-shadow: -6px 0 12px 4px #ffffff, 0 2px 6px rgba(15, 23, 42, 0.1);
        }

        .notif-filter-btn {
            font-size: 0.74rem !important;
            padding: 4px 12px !important;
            border-radius: 999px !important;
            white-space: nowrap !important;
            transition: all 0.18s ease !important;
            flex-shrink: 0 !important;
            border: 1px solid #e2e8f0 !important;
            font-weight: 600 !important;
            background-color: #f8fafc !important;
            color: #475569 !important;
            line-height: 1.45 !important;
        }

        .notif-filter-btn:hover {
            background-color: #eff6ff !important;
            border-color: #bfdbfe !important;
            color: #1d4ed8 !important;
        }

        .notif-filter-btn.active {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.25) !important;
        }

        /* Sleek Thin Scrollbar without Windows Up/Down Arrows */
        .notif-list-container {
            max-height: 350px;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 8px;
            background: #fbfcfe;
            flex: 1 1 auto;
        }

        .notif-dropdown-menu.is-expanded .notif-list-container {
            max-height: calc(min(84vh, 760px) - 150px);
            padding: 10px 12px;
        }

        @media (max-width: 575.98px) {
            .notif-dropdown-menu.is-expanded .notif-list-container {
                max-height: none;
            }
        }

        @supports not selector(::-webkit-scrollbar) {
            .notif-list-container {
                scrollbar-width: thin;
                scrollbar-color: #cbd5e1 transparent;
            }
        }

        .notif-list-container::-webkit-scrollbar {
            width: 5px;
        }

        .notif-list-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .notif-list-container::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 999px;
        }

        .notif-list-container::-webkit-scrollbar-thumb:hover {
            background-color: #94a3b8;
        }

        .notif-list-container::-webkit-scrollbar-button {
            display: none;
            width: 0;
            height: 0;
        }

        /* Notification Feed Row & Single Delete Action */
        .notif-row {
            position: relative;
            margin-bottom: 6px;
            transition: opacity 0.2s ease, transform 0.2s ease, max-height 0.22s ease, margin 0.22s ease;
        }

        .notif-row:last-of-type {
            margin-bottom: 0;
        }

        .notif-row.is-removing {
            opacity: 0;
            transform: translateX(16px);
            pointer-events: none;
        }

        .notif-item {
            white-space: normal !important;
            border-radius: 12px !important;
            border: 1px solid #eef2f6 !important;
            transition: background-color 0.18s ease, border-color 0.18s ease;
            text-decoration: none;
            display: flex !important;
            align-items: flex-start;
            padding: 11px 40px 11px 12px !important;
            position: relative;
            background-color: #ffffff;
        }

        .notif-row.no-delete .notif-item {
            padding-right: 12px !important;
        }

        .notif-item:hover {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
            color: inherit !important;
        }

        .notif-item.is-unread {
            background-color: #f0f7ff !important;
            border-color: #dbeafe !important;
        }

        .notif-item.is-unread:hover {
            background-color: #e6f2ff !important;
            border-color: #bfdbfe !important;
        }

        .notif-delete-btn {
            position: absolute;
            top: 9px;
            right: 8px;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 4;
            transition: all 0.16s ease;
            padding: 0;
            font-size: 0.95rem;
        }

        .notif-delete-btn:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        .notif-item-icon {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: opacity 0.18s ease;
        }

        .notif-item:not(.is-unread) .notif-item-icon {
            opacity: 0.78;
        }

        .notif-item-title {
            font-size: 0.84rem;
            line-height: 1.3;
            color: #0f172a;
        }

        .notif-item:not(.is-unread) .notif-item-title {
            color: #334155;
            font-weight: 600 !important;
        }

        .notif-item-desc {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            white-space: normal !important;
            word-break: break-word;
            line-height: 1.45;
            font-size: 0.78rem;
            color: #475569;
        }

        .notif-dropdown-menu.is-expanded .notif-item-desc {
            -webkit-line-clamp: 5;
            font-size: 0.83rem;
        }

        .notif-dropdown-menu.is-expanded .notif-item-title {
            font-size: 0.9rem;
        }

        .notif-item:not(.is-unread) .notif-item-desc {
            color: #64748b;
        }

        .notif-meta-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 5px;
            font-size: 0.7rem;
            color: #94a3b8;
        }

        .notif-cat-chip {
            display: inline-flex;
            align-items: center;
            padding: 1px 7px;
            border-radius: 999px;
            font-size: 0.67rem;
            font-weight: 600;
            background: #f1f5f9;
            color: #64748b;
        }

        .notif-footer-link {
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 11px 16px !important;
            font-size: 0.82rem;
            font-weight: 600;
            color: #0d6efd !important;
            background: #ffffff;
            transition: background-color 0.18s ease;
        }

        .notif-footer-link:hover {
            background: #f8fbff !important;
            color: #0a58ca !important;
        }

        .admin-user-dropdown {
            border-radius: 14px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.12) !important;
            padding: 6px !important;
            min-width: 230px !important;
        }

        .admin-user-dropdown .dropdown-item {
            border-radius: 10px !important;
            padding: 9px 12px !important;
            font-size: 0.86rem;
        }

        .admin-user-dropdown .dropdown-item:hover {
            background-color: #f1f5f9 !important;
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
    <!-- Modern Sidebar Enhancements -->
    <style>
        /* Native smooth scrolling container for sidebar menu */
        .layout-menu .menu-inner {
            overflow-y: auto !important;
            overflow-x: hidden !important;
            -webkit-overflow-scrolling: touch !important;
            overscroll-behavior-y: contain !important;
            scrollbar-width: thin !important;
            scrollbar-color: rgba(67, 89, 113, 0.25) transparent !important;
            padding-bottom: 1.5rem !important;
            height: calc(100% - 64px) !important;
            min-height: 0 !important;
        }

        .layout-menu .menu-inner::-webkit-scrollbar {
            width: 5px !important;
        }

        .layout-menu .menu-inner::-webkit-scrollbar-track {
            background: transparent !important;
        }

        .layout-menu .menu-inner::-webkit-scrollbar-thumb {
            background: rgba(67, 89, 113, 0.2) !important;
            border-radius: 10px !important;
        }

        .layout-menu .menu-inner::-webkit-scrollbar-thumb:hover {
            background: rgba(67, 89, 113, 0.45) !important;
        }

        /* Hilangkan rel scrollbar ganda PerfectScrollbar di sidebar */
        .layout-menu .ps__rail-y,
        .layout-menu .ps__rail-x,
        .layout-menu .ps__thumb-y,
        .layout-menu .ps__thumb-x {
            display: none !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        /* Menu Header Styling - Clean SaaS Divider without awkward ticks */
        .layout-menu .menu-header {
            margin-top: 0.9rem !important;
            margin-bottom: 0.25rem !important;
            padding: 0.5rem 1.25rem 0.25rem 1.25rem !important;
        }

        .layout-menu .menu-header::before {
            display: none !important;
        }

        .layout-menu .menu-header .menu-header-text {
            font-size: 0.68rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.9px !important;
            color: #8592a3 !important;
            text-transform: uppercase !important;
            opacity: 0.95;
        }

        /* Menu Links & Hover Transitions */
        .layout-menu .menu-item .menu-link {
            border-radius: 8px !important;
            margin: 2px 0.75rem !important;
            padding: 0.6rem 0.95rem !important;
            font-weight: 500 !important;
            color: #566a7f !important;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.15s ease !important;
        }

        /* Hapus outline hitam bawaan browser saat tombol menu diklik atau difokuskan */
        .layout-menu .menu-link:focus,
        .layout-menu .menu-link:focus-visible,
        .layout-menu .menu-toggle:focus,
        .layout-menu .menu-toggle:focus-visible {
            outline: none !important;
            box-shadow: none !important;
        }

        .layout-menu .menu-item .menu-link:hover {
            background-color: rgba(105, 108, 255, 0.07) !important;
            color: #696cff !important;
        }

        .layout-menu .menu-item.active:not(.open) > .menu-link {
            background-color: rgba(105, 108, 255, 0.12) !important;
            color: #696cff !important;
            font-weight: 600 !important;
        }

        .layout-menu .menu-item.open > .menu-link {
            background-color: rgba(105, 108, 255, 0.08) !important;
            color: #696cff !important;
            font-weight: 600 !important;
        }

        /* Submenu Container: Desain pohon ramping, bersih, transparan dan lapang */
        .layout-menu .menu-sub {
            background: transparent !important;
            margin: 2px 0.75rem 4px 1.25rem !important;
            padding: 2px 0 2px 0.4rem !important;
            border-left: 1.5px solid rgba(105, 108, 255, 0.22) !important;
        }

        /* Hilangkan titik/lingkaran abu-abu bawaan tema yang menutupi huruf di submenu */
        .layout-menu .menu-sub > .menu-item > .menu-link::before,
        .layout-menu .menu-sub .menu-link::before,
        .layout-menu .menu-sub .menu-link:before {
            display: none !important;
            content: none !important;
        }

        .layout-menu .menu-sub .menu-item .menu-link {
            padding: 0.46rem 0.85rem !important;
            font-size: 0.835rem !important;
            font-weight: 500 !important;
            color: #697a8d !important;
            margin: 2px 0 !important;
            border-radius: 6px !important;
            background: transparent !important;
            transition: all 0.18s ease !important;
        }

        .layout-menu .menu-sub .menu-item .menu-link i {
            font-size: 1rem !important;
            transition: transform 0.18s ease, color 0.18s ease !important;
        }

        .layout-menu .menu-sub .menu-item .menu-link:hover {
            color: #696cff !important;
            background-color: rgba(105, 108, 255, 0.07) !important;
            transform: translateX(3px) !important;
        }

        .layout-menu .menu-sub .menu-item .menu-link:hover i {
            color: #696cff !important;
            transform: scale(1.1) !important;
        }

        .layout-menu .menu-sub .menu-item.active > .menu-link {
            background: linear-gradient(90deg, rgba(105, 108, 255, 0.14) 0%, rgba(105, 108, 255, 0.04) 100%) !important;
            color: #696cff !important;
            font-weight: 600 !important;
            box-shadow: none !important;
        }

        .layout-menu .menu-sub .menu-item.active > .menu-link i {
            color: #696cff !important;
        }

        /* Lencana jumlah unit aktif pada toggle induk */
        .sidebar-unit-badge {
            font-size: 0.62rem !important;
            font-weight: 700 !important;
            padding: 0.18rem 0.48rem !important;
            border-radius: 50rem !important;
            letter-spacing: 0.2px !important;
            background-color: rgba(105, 108, 255, 0.1) !important;
            color: #696cff !important;
            border: 1px solid rgba(105, 108, 255, 0.22) !important;
            margin-right: 1.35rem !important;
            line-height: 1.15 !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
        }

        .sidebar-sub-label {
            font-size: 0.64rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.6px !important;
            color: #9aa5b1 !important;
            text-transform: uppercase !important;
            padding: 0.45rem 0.85rem 0.15rem !important;
            display: block !important;
        }

        /* Responsive Width, Overflow & Half-Step Spacing Utilities (Pelengkap Bootstrap 5.2 -> 5.3) */
        .overflow-x-auto {
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch !important;
            overscroll-behavior-x: contain;
            touch-action: pan-x pan-y;
            max-width: 100%;
        }
        .overflow-x-hidden { overflow-x: hidden !important; }
        .overflow-y-auto { overflow-y: auto !important; -webkit-overflow-scrolling: touch !important; }
        .overflow-y-hidden { overflow-y: hidden !important; }

        /* Horizontal Scrollable Tab Bar Enhancer (Desktop & Mobile Chrome) */
        .admin-scroll-tabs-wrap {
            position: relative;
            max-width: 100%;
            min-width: 0;
            display: flex;
            align-items: center;
        }
        .admin-scroll-tabs-wrap > .nav,
        .admin-scroll-tabs-wrap > div {
            flex: 1 1 auto;
            min-width: 0;
            max-width: 100%;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding: 2px;
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch !important;
            scroll-behavior: smooth;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .admin-scroll-tabs-wrap > .nav::-webkit-scrollbar,
        .admin-scroll-tabs-wrap > div::-webkit-scrollbar {
            display: none;
        }
        .admin-scroll-tabs-wrap.can-scroll-right::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 44px;
            background: linear-gradient(to left, rgba(255, 255, 255, 0.96) 35%, rgba(255, 255, 255, 0));
            pointer-events: none;
            z-index: 2;
            border-radius: 0 12px 12px 0;
        }
        .admin-scroll-tabs-wrap.can-scroll-left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 36px;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.96) 35%, rgba(255, 255, 255, 0));
            pointer-events: none;
            z-index: 2;
            border-radius: 12px 0 0 12px;
        }
        .admin-tabs-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 1px solid #dbe2ea;
            background: #ffffff;
            color: #475569;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 4;
            padding: 0;
            font-size: 1rem;
            transition: all 0.16s ease;
        }
        .admin-tabs-arrow:hover {
            background: #0d6efd;
            color: #ffffff;
            border-color: #0d6efd;
        }
        .admin-tabs-arrow.prev { left: 2px; }
        .admin-tabs-arrow.next { right: 2px; }

        .gap-1\.5 { gap: 0.375rem !important; }
        .gap-2\.5 { gap: 0.625rem !important; }
        .p-1\.5 { padding: 0.375rem !important; }
        .p-2\.5 { padding: 0.625rem !important; }
        .px-2\.5 { padding-left: 0.625rem !important; padding-right: 0.625rem !important; }
        .py-1\.5 { padding-top: 0.375rem !important; padding-bottom: 0.375rem !important; }
        .py-2\.5 { padding-top: 0.625rem !important; padding-bottom: 0.625rem !important; }
        .mb-2\.5 { margin-bottom: 0.625rem !important; }
        .mt-0\.5 { margin-top: 0.125rem !important; }
        .mt-1\.5 { margin-top: 0.375rem !important; }
        .me-0\.5 { margin-right: 0.125rem !important; }
        @media (min-width: 576px) {
            .w-sm-auto { width: auto !important; }
            .w-sm-100 { width: 100% !important; }
        }
        @media (min-width: 768px) {
            .w-md-auto { width: auto !important; }
            .w-md-100 { width: 100% !important; }
        }
        @media (min-width: 992px) {
            .w-lg-auto { width: auto !important; }
            .w-lg-100 { width: 100% !important; }
        }
        @media (min-width: 1200px) {
            .w-xl-auto { width: auto !important; }
            .w-xl-100 { width: 100% !important; }
        }

        /* Global Mobile Chrome & Layar Kecil Guard (360px - 991px) */
        @media (max-width: 991.98px) {
            html, body, .layout-wrapper, .layout-container, .layout-page, .content-wrapper {
                max-width: 100% !important;
                overflow-x: clip !important;
            }
            /* Search input di toolbar selalu penuh dan rata kiri-kanan di HP/Tablet */
            .card-header .input-group,
            .card-body > .input-group,
            .d-flex > .input-group[style*="max-width"] {
                max-width: 100% !important;
                width: 100% !important;
            }
        }

        @media (max-width: 575.98px) {
            .container-xxl.container-p-y {
                padding-left: 0.85rem !important;
                padding-right: 0.85rem !important;
            }
            .container-xxl h4.fw-bold {
                font-size: 1.12rem !important;
                line-height: 1.35 !important;
                word-break: break-word;
            }
            /* Kartu KPI 2 kolom (col-6): cegah teks terpotong titik-titik & sesuaikan ukuran ikon */
            .req-stat-card,
            .proof-stat-card,
            .wilayah-kpi-card .card-body {
                padding: 0.7rem 0.65rem !important;
                gap: 0.55rem !important;
            }
            .req-stat-card .avatar,
            .proof-stat-card .avatar,
            .wilayah-kpi-card .avatar {
                width: 36px !important;
                height: 36px !important;
                min-width: 36px !important;
            }
            .req-stat-card .avatar img,
            .proof-stat-card .avatar img {
                width: 22px !important;
                height: 22px !important;
            }
            .req-stat-card .avatar i,
            .proof-stat-card .avatar i,
            .wilayah-kpi-card .avatar i {
                font-size: 1.1rem !important;
            }
            .req-stat-card small,
            .proof-stat-card small,
            .wilayah-kpi-card small {
                white-space: normal !important;
                overflow: visible !important;
                text-overflow: clip !important;
                line-height: 1.2 !important;
                font-size: 0.65rem !important;
            }
            .req-stat-card h4,
            .proof-stat-card h4,
            .wilayah-kpi-card h4 {
                font-size: 1.15rem !important;
            }

            /* Kartu KPI 3 kolom (col-4): proporsional & sama tinggi di layar 360px */
            .partner-stat-card,
            .supir-stat-card,
            .loc-stat-card,
            .staff-stat-pill {
                padding: 0.65rem 0.4rem !important;
            }
            .partner-stat-card > span,
            .supir-stat-card > span,
            .loc-stat-card > span,
            .staff-stat-pill > span {
                font-size: 0.66rem !important;
                line-height: 1.2 !important;
            }
            .partner-stat-card h4,
            .supir-stat-card h4,
            .loc-stat-card h4,
            .staff-stat-pill h4 {
                font-size: 1.15rem !important;
                margin-top: 2px;
            }

            /* Proteksi kartu mobile agar email/alamat/teks panjang tidak pernah keluar batas kartu */
            .order-mobile-card,
            .proof-mobile-card,
            .partner-mobile-card,
            .loc-item-card,
            .staff-card,
            .trx-mobile-card,
            .inc-mobile-card,
            .user-card-box {
                overflow: hidden !important;
                overflow-wrap: anywhere !important;
                word-break: break-word !important;
            }
            .inc-unit-card .card-body {
                padding: 0.75rem !important;
            }
            .inc-unit-card h5 {
                font-size: 0.92rem !important;
                word-break: break-word;
            }
            /* Modal nyaman di layar HP */
            .modal-header {
                padding: 0.9rem 1rem !important;
            }
            .modal-body {
                padding: 1rem !important;
            }
            .modal-footer {
                padding: 0.85rem 1rem !important;
            }
            .modal-footer .btn {
                flex: 1 1 auto;
            }
        }

        /* Print optimization: sembunyikan navigasi dan tombol aksi saat mencetak laporan */
        @media print {
            #layout-menu,
            #layout-navbar,
            .content-footer,
            #page-preloader,
            .no-print,
            .layout-overlay {
                display: none !important;
            }
            .layout-page {
                padding: 0 !important;
                margin: 0 !important;
            }
            .content-wrapper,
            .container-xxl {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
                break-inside: avoid;
            }
        }
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
<link rel="stylesheet" href="{{ asset('css/segidaerah-ui.css') }}?v={{ filemtime(public_path('css/segidaerah-ui.css')) }}">
</head>

<body class="segi-admin">
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
                <ul class="menu-inner py-1" style="padding-bottom: 1.5rem !important;">
                    @if(auth()->user()->isStaff())
                    @include('admin.staff.menu')
                    @else

                    <!-- ========================================================================= -->
                    <!-- 1. ZONA UTAMA                                                             -->
                    <!-- ========================================================================= -->
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">Utama</span>
                    </li>
                    <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div data-i18n="Dashboard">Dashboard</div>
                        </a>
                    </li>

                    <!-- ========================================================================= -->
                    <!-- 2. ZONA KHUSUS PEMANTAUAN (Admin Kabupaten dan Admin Kecamatan)            -->
                    <!-- Mode View-Only: Monitoring wilayah, kependudukan, dan penerbitan berita    -->
                    <!-- ========================================================================= -->


                    <!-- ========================================================================= -->
                    <!-- 3. ZONA OPERASIONAL UNIT LAYANAN                                          -->
                    <!-- ========================================================================= -->

                    {{-- 3A. KHUSUS STAF (Hub Mandiri Terisolasi: Hanya Unit yang Ditugaskan) --}}
                    @if(auth()->user()->isStaff())
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">Unit Tugas Anda</span>
                    </li>

                    {{-- Staf: Penyewaan Transportasi --}}
                    @if((in_array('Penyewaan Mobil', $activeServicesMenu ?? []) || in_array('Penyewaan Transportasi', $activeServicesMenu ?? [])) && auth()->user()->hasUnitPermission('sewa_mobil'))
                    <li class="menu-item {{ (request()->is('admin/unit/mobil*') || request()->is('admin/unit/supir*') || (request()->is('admin/aktivitas/permintaan-pengajuan*') && request('tab') == 'mobil') || (request()->is('admin/aktivitas/bukti-transaksi*') && request('category') == 'mobil')) ? 'open active show' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-car text-primary"></i>
                            <div><small class="d-block text-muted" style="font-size:.65rem; line-height:1.1">Layanan Daerah</small>Penyewaan Transportasi</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->is('admin/unit/mobil*') ? 'active' : '' }}">
                                <a href="{{ route('admin.unit.mobil.index') }}" class="menu-link">
                                    <div>Daftar Armada</div>
                                </a>
                            </li>
                            <li class="menu-item {{ (request()->is('admin/aktivitas/permintaan-pengajuan*') && request('tab') == 'mobil') ? 'active' : '' }}">
                                <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index', ['tab' => 'mobil']) }}" class="menu-link">
                                    <div>Pesanan dan Booking</div>
                                </a>
                            </li>
                            <li class="menu-item {{ (request()->is('admin/aktivitas/bukti-transaksi*') && request('category') == 'mobil') ? 'active' : '' }}">
                                <a href="{{ route('admin.aktivitas.bukti-transaksi.index', ['category' => 'mobil']) }}" class="menu-link">
                                    <div>Bukti Transaksi</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->is('admin/unit/supir*') ? 'active' : '' }}">
                                <a href="{{ route('supir.index') }}" class="menu-link">
                                    <div>Data Supir dan Petugas</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('admin.laporan.pendapatan') ? 'active' : '' }}">
                                <a href="{{ route('admin.laporan.pendapatan', ['service' => 'Penyewaan Mobil']) }}" class="menu-link">
                                    <div>Laporan Unit</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    {{-- Staf: Penjualan Gas --}}
                    @if(in_array('Penjualan Gas', $activeServicesMenu ?? []) && auth()->user()->hasUnitPermission('gas'))
                    <li class="menu-item {{ (request()->is('admin/unit/gas*') || (request()->is('admin/aktivitas/permintaan-pengajuan*') && request('tab') == 'gas') || (request()->is('admin/aktivitas/bukti-transaksi*') && request('category') == 'gas')) ? 'open active show' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-gas-pump text-success"></i>
                            <div><small class="d-block text-muted" style="font-size:.65rem; line-height:1.1">Belanja dan Kebutuhan</small>Penjualan Gas</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->is('admin/unit/gas*') ? 'active' : '' }}">
                                <a href="{{ route('admin.unit.penjualan_gas.index') }}" class="menu-link">
                                    <div>Stok dan Tabung Gas</div>
                                </a>
                            </li>
                            <li class="menu-item {{ (request()->is('admin/aktivitas/permintaan-pengajuan*') && request('tab') == 'gas') ? 'active' : '' }}">
                                <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index', ['tab' => 'gas']) }}" class="menu-link">
                                    <div>Pesanan Masuk</div>
                                </a>
                            </li>
                            <li class="menu-item {{ (request()->is('admin/aktivitas/bukti-transaksi*') && request('category') == 'gas') ? 'active' : '' }}">
                                <a href="{{ route('admin.aktivitas.bukti-transaksi.index', ['category' => 'gas']) }}" class="menu-link">
                                    <div>Bukti Transaksi</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('admin.laporan.pendapatan') ? 'active' : '' }}">
                                <a href="{{ route('admin.laporan.pendapatan', ['service' => 'Penjualan Gas']) }}" class="menu-link">
                                    <div>Laporan Unit</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    {{-- Staf: Penyewaan Alat --}}
                    @if(in_array('Penyewaan Alat', $activeServicesMenu ?? []) && auth()->user()->hasUnitPermission('sewa_alat'))
                    <li class="menu-item {{ (request()->is('admin/unit/penyewaan*') || (request()->is('admin/aktivitas/permintaan-pengajuan*') && request('tab') == 'rental') || (request()->is('admin/aktivitas/bukti-transaksi*') && request('category') == 'rental')) ? 'open active show' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-wrench text-warning"></i>
                            <div><small class="d-block text-muted" style="font-size:.65rem; line-height:1.1">Layanan Daerah</small>Penyewaan Alat</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->is('admin/unit/penyewaan*') ? 'active' : '' }}">
                                <a href="{{ route('admin.unit.penyewaan.index') }}" class="menu-link">
                                    <div>Katalog Alat</div>
                                </a>
                            </li>
                            <li class="menu-item {{ (request()->is('admin/aktivitas/permintaan-pengajuan*') && request('tab') == 'rental') ? 'active' : '' }}">
                                <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index', ['tab' => 'rental']) }}" class="menu-link">
                                    <div>Pesanan Sewa</div>
                                </a>
                            </li>
                            <li class="menu-item {{ (request()->is('admin/aktivitas/bukti-transaksi*') && request('category') == 'rental') ? 'active' : '' }}">
                                <a href="{{ route('admin.aktivitas.bukti-transaksi.index', ['category' => 'rental']) }}" class="menu-link">
                                    <div>Bukti Transaksi</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('admin.laporan.pendapatan') ? 'active' : '' }}">
                                <a href="{{ route('admin.laporan.pendapatan', ['service' => 'Penyewaan Alat']) }}" class="menu-link">
                                    <div>Laporan Unit</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    {{-- Staf: Fasilitas Umum --}}
                    @if((in_array('Fasilitas Umum', $activeServicesMenu ?? [])) && auth()->user()->hasUnitPermission('fasilitas_umum'))
                    <li class="menu-item {{ (request()->is('admin/unit/fasilitas_umum*') || request()->is('admin/unit/kendaraan-operasional*') || (request()->is('admin/aktivitas/permintaan-pengajuan*') && request('tab') == 'fasilitas') || (request()->is('admin/aktivitas/bukti-transaksi*') && request('category') == 'fasilitas')) ? 'open active show' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-buildings text-info"></i>
                            <div><small class="d-block text-muted" style="font-size:.65rem; line-height:1.1">Layanan Daerah</small>Fasilitas Umum</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->is('admin/unit/fasilitas_umum*') ? 'active' : '' }}">
                                <a href="{{ route('admin.unit.fasilitas_umum.index') }}" class="menu-link">
                                    <div>Daftar Fasilitas</div>
                                </a>
                            </li>
                            <li class="menu-item {{ (request()->is('admin/aktivitas/permintaan-pengajuan*') && request('tab') == 'fasilitas') ? 'active' : '' }}">
                                <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index', ['tab' => 'fasilitas']) }}" class="menu-link">
                                    <div>Permintaan Booking</div>
                                </a>
                            </li>
                            <li class="menu-item {{ (request()->is('admin/aktivitas/bukti-transaksi*') && request('category') == 'fasilitas') ? 'active' : '' }}">
                                <a href="{{ route('admin.aktivitas.bukti-transaksi.index', ['category' => 'fasilitas']) }}" class="menu-link">
                                    <div>Bukti Transaksi</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('admin.laporan.pendapatan') ? 'active' : '' }}">
                                <a href="{{ route('admin.laporan.pendapatan', ['service' => 'Fasilitas Umum']) }}" class="menu-link">
                                    <div>Laporan Unit</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    

                    {{-- Staf: Kabar dan Pengumuman --}}
                    @if(auth()->user()->hasUnitPermission('kabar_informasi'))
                    <li class="menu-item {{ request()->routeIs('admin.announcements.*') ? 'open active show' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-news text-primary"></i>
                            <div><small class="d-block text-muted" style="font-size:.65rem; line-height:1.1">Kabar dan Informasi Daerah</small>Kabar dan Pengumuman</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.announcements.index') }}" class="menu-link">
                                    <div>Kelola Kabar dan Berita</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                    @endif

                    <!-- ========================================================================= -->
                    <!-- 3. ZONA KHUSUS ADMIN DESA (Susunan Ringkas: Layanan, Tata Kelola, Laporan)-->
                    <!-- ========================================================================= -->
                    @if(auth()->user()->role === 'admin_desa')

                    <!-- PILAR 1: LAYANAN DESA -->
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">Layanan Desa</span>
                    </li>

                    <!-- Dropdown 1: Unit Layanan Desa (Katalog Aktivasi dan Unit Aktif) -->
                    <li class="menu-item {{ (request()->is('admin/unit*') && !request()->is('admin/unit/supir*')) || request()->routeIs('admin.announcements.*') || request()->routeIs('content.*') || request()->routeIs('admin.region-settings.index') ? 'open active show' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center text-nowrap">
                                <i class="menu-icon tf-icons bx bx-grid-alt text-primary"></i>
                                <div>Unit Layanan</div>
                            </div>
                            <span class="badge sidebar-unit-badge">{{ $activeUnitsCount ?? 2 }}/{{ $totalAvailableUnits ?? 5 }}</span>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('admin.region-settings.index') ? 'active' : '' }}">
                                <a href="{{ route('admin.region-settings.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-slider-alt me-2 text-primary" style="font-size: 1.05rem;"></i>
                                    <span class="fw-semibold">Aktivasi &amp; Katalog</span>
                                </a>
                            </li>

                            <li class="menu-item py-1 px-2">
                                <div class="border-top w-100" style="border-color: rgba(67, 89, 113, 0.08) !important;"></div>
                            </li>

                            @if(in_array('Penjualan Gas', $activeServicesMenu ?? []))
                            <li class="menu-item"><span class="sidebar-sub-label">Belanja &amp; Kebutuhan</span></li>
                            <li class="menu-item {{ request()->is('admin/unit/gas*') ? 'active' : '' }}">
                                <a href="{{ route('admin.unit.penjualan_gas.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-gas-pump me-2 text-muted"></i>
                                    <div>Penjualan Gas</div>
                                </a>
                            </li>
                            @endif

                            @if(in_array('Penyewaan Mobil', $activeServicesMenu ?? []) || in_array('Penyewaan Transportasi', $activeServicesMenu ?? []) || in_array('Penyewaan Alat', $activeServicesMenu ?? []) || in_array('Fasilitas Umum', $activeServicesMenu ?? []))
                            <li class="menu-item"><span class="sidebar-sub-label">Layanan Daerah</span></li>
                            @endif
                            @if(in_array('Penyewaan Mobil', $activeServicesMenu ?? []) || in_array('Penyewaan Transportasi', $activeServicesMenu ?? []))
                            <li class="menu-item {{ request()->is('admin/unit/mobil*') ? 'active' : '' }}">
                                <a href="{{ route('admin.unit.mobil.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-car me-2 text-muted"></i>
                                    <div>Penyewaan Transportasi</div>
                                </a>
                            </li>
                            @endif
                            @if(in_array('Penyewaan Alat', $activeServicesMenu ?? []))
                            <li class="menu-item {{ request()->is('admin/unit/penyewaan*') ? 'active' : '' }}">
                                <a href="{{ route('admin.unit.penyewaan.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-wrench me-2 text-muted"></i>
                                    <div>Penyewaan Alat</div>
                                </a>
                            </li>
                            @endif
                            @if(in_array('Fasilitas Umum', $activeServicesMenu ?? []))
                            <li class="menu-item {{ request()->is('admin/unit/fasilitas_umum*') || request()->is('admin/unit/kendaraan-operasional*') ? 'active' : '' }}">
                                <a href="{{ route('admin.unit.fasilitas_umum.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-buildings me-2 text-muted"></i>
                                    <div>Fasilitas Umum</div>
                                </a>
                            </li>
                            @endif

                            <li class="menu-item"><span class="sidebar-sub-label">Informasi Publik</span></li>
                            <li class="menu-item {{ request()->routeIs('admin.announcements.*') || request()->routeIs('content.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.announcements.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-news me-2 text-muted"></i>
                                    <div>Kabar &amp; Pengumuman</div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Dropdown 2: Pesanan dan Transaksi Warga -->
                    <li class="menu-item {{ request()->is('admin/aktivitas*') || request()->is('admin/unit/supir*') ? 'open active show' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-receipt text-warning"></i>
                            <div>Pesanan &amp; Transaksi</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->is('admin/aktivitas/permintaan-pengajuan*') ? 'active' : '' }}">
                                <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-calendar-check me-2 text-muted"></i>
                                    <div>Permintaan &amp; Booking</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->is('admin/aktivitas/bukti-transaksi*') ? 'active' : '' }}">
                                <a href="{{ route('admin.aktivitas.bukti-transaksi.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-check-shield me-2 text-muted"></i>
                                    <div>Bukti Pembayaran</div>
                                </a>
                            </li>
                            @if(in_array('Penyewaan Mobil', $activeServicesMenu ?? []) || in_array('Penyewaan Transportasi', $activeServicesMenu ?? []) || in_array('Fasilitas Umum', $activeServicesMenu ?? []))
                            <li class="menu-item {{ request()->is('admin/unit/supir*') ? 'active' : '' }}">
                                <a href="{{ route('supir.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-user-check me-2 text-muted"></i>
                                    <div>Data Supir &amp; Petugas</div>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>

                    <!-- PILAR 2: TATA KELOLA DESA -->
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">Tata Kelola Desa</span>
                    </li>

                    {{-- Kependudukan & Wilayah (Dropdown) --}}
                    <li class="menu-item {{ request()->routeIs('admin.manajemen-pengguna.*') || request()->routeIs('admin.kyc.*') || request()->routeIs('admin.warga.mutasi.*') || request()->routeIs('admin.wilayah-admins.*') || request()->routeIs('admin.kemitraan.*') ? 'open active show' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-group text-info"></i>
                            <div>Kependudukan</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('admin.manajemen-pengguna.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.manajemen-pengguna.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-user me-2 text-muted"></i>
                                    <div>Data Warga</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.kyc.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-id-card me-2 text-muted"></i>
                                    <div>Verifikasi Identitas</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('admin.warga.mutasi.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.warga.mutasi.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-transfer-alt me-2 text-muted"></i>
                                    <div>Mutasi Penduduk</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('admin.wilayah-admins.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.wilayah-admins.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-map-pin me-2 text-muted"></i>
                                    <div>Wilayah &amp; RT/RW</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('admin.kemitraan.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.kemitraan.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-badge-check me-2 text-muted"></i>
                                    <div>Persetujuan Mitra</div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Staf Layanan Desa --}}
                    <li class="menu-item {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.staff.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-user-pin text-success"></i>
                            <div>Staf Layanan</div>
                        </a>
                    </li>

                    <!-- PILAR 3: LAPORAN & PENGATURAN -->
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">Laporan &amp; Pengaturan</span>
                    </li>

                    {{-- Laporan Rekapitulasi (Dropdown) --}}
                    <li class="menu-item {{ request()->routeIs('admin.laporan.transaksi') || request()->routeIs('admin.laporan.pendapatan*') || request()->routeIs('admin.laporan.wilayah') ? 'open active show' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-bar-chart-alt-2 text-primary"></i>
                            <div>Laporan</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('admin.laporan.transaksi') ? 'active' : '' }}">
                                <a href="{{ route('admin.laporan.transaksi') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-receipt me-2 text-muted"></i>
                                    <div>Laporan Transaksi</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('admin.laporan.pendapatan*') && !request('service') ? 'active' : '' }}">
                                <a href="{{ route('admin.laporan.pendapatan') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-line-chart me-2 text-muted"></i>
                                    <div>Laporan Pendapatan</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('admin.laporan.wilayah') ? 'active' : '' }}">
                                <a href="{{ route('admin.laporan.wilayah') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-pie-chart-alt me-2 text-muted"></i>
                                    <div>Evaluasi Aktivitas</div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Pengaturan Desa (Dropdown Terpadu) --}}
                    <li class="menu-item {{ request()->routeIs('admin.lokasi-layanan.*') || request()->routeIs('admin.region-settings.payment') || request()->routeIs('admin.SiladesBeng.bumdes.*') ? 'open active show' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-cog text-secondary"></i>
                            <div>Pengaturan Desa</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('admin.SiladesBeng.bumdes.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.SiladesBeng.bumdes.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-building-house me-2 text-muted"></i>
                                    <div>Profil Desa &amp; BUMDes</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('admin.lokasi-layanan.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.lokasi-layanan.index') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-map me-2 text-muted"></i>
                                    <div>Lokasi &amp; Peta Layanan</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('admin.region-settings.payment') ? 'active' : '' }}">
                                <a href="{{ route('admin.region-settings.payment') }}" class="menu-link d-flex align-items-center">
                                    <i class="bx bx-credit-card me-2 text-muted"></i>
                                    <div>Pembayaran Wilayah</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    <!-- ========================================================================= -->
                    <!-- 7. SISTEM PLATFORM (Khusus Super Admin Sistem dan Staf Platform)           -->
                    <!-- ========================================================================= -->
                    @if(auth()->user()->bolehAksesPlatform())
                    <!-- PILAR 1: WILAYAH & MONITORING -->
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">Wilayah &amp; Monitoring</span>
                    </li>
                    @if(auth()->user()->role === 'super_admin')
                    <li class="menu-item {{ request()->routeIs('admin.sistem-platform.wilayah.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.sistem-platform.wilayah.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-sitemap text-primary"></i>
                            <div>Peta Wilayah</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('admin.kelola-wilayah.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.kelola-wilayah.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-user-check text-success"></i>
                            <div>Admin Wilayah</div>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasPlatformPermission('platform_monitoring'))
                    <li class="menu-item {{ request()->routeIs('admin.sistem-platform.monitoring') ? 'active' : '' }}">
                        <a href="{{ route('admin.sistem-platform.monitoring') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-line-chart text-info"></i>
                            <div>Monitoring Layanan</div>
                        </a>
                    </li>
                    @endif

                    <!-- PILAR 2: SISTEM & KEAMANAN -->
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">Sistem &amp; Keamanan</span>
                    </li>
                    @if(auth()->user()->hasPlatformPermission('platform_integrasi'))
                    <li class="menu-item {{ request()->routeIs('admin.sistem-platform.gateway') ? 'active' : '' }}">
                        <a href="{{ route('admin.sistem-platform.gateway') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-plug text-warning"></i>
                            <div>Integrasi &amp; API Key</div>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasPlatformPermission('platform_keamanan'))
                    <li class="menu-item {{ request()->routeIs('admin.sistem-platform.security-log') ? 'active' : '' }}">
                        <a href="{{ route('admin.sistem-platform.security-log') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-shield-quarter text-danger"></i>
                            <div>Log Keamanan &amp; Audit</div>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasPlatformPermission('platform_banner'))
                    <li class="menu-item {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.banners.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-image text-primary"></i>
                            <div>Banner Beranda</div>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasPlatformPermission('platform_aktivitas'))
                    <li class="menu-item {{ request()->routeIs('admin.laporan.log') ? 'active' : '' }}">
                        <a href="{{ route('admin.laporan.log') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-history text-secondary"></i>
                            <div>Log Aktivitas</div>
                        </a>
                    </li>
                    @endif
                    @endif

                    <!-- ========================================================================= -->
                    <!-- 8. PROFIL DAN INFORMASI DAERAH (Hanya jika bukan Admin Desa & Super Admin) -->
                    <!-- ========================================================================= -->
                    @if(auth()->user()->role !== 'admin_desa' && !auth()->user()->bolehAksesPlatform())
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">Tentang Aplikasi</span>
                    </li>
                    <li class="menu-item {{ request()->is('admin/SiladesBeng/profile*') || request()->is('admin/SiladesBeng/developer*') || request()->routeIs('admin.SiladesBeng.bumdes.index') || request()->routeIs('admin.SiladesBeng.bumdes.*') ? 'open active show' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-info-circle text-info"></i>
                            <div>Profil dan Info</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('admin.SiladesBeng.profile') || request()->routeIs('admin.SiladesBeng.developer.profile') ? 'active' : '' }}">
                                <a href="{{ route('admin.SiladesBeng.profile') }}" class="menu-link">
                                    <div>SegiDaerah</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                    @endif
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
                                @unless(auth()->user()->isStaff())
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
@endunless
                                @endif
                            </div>
                        </div>
                        {{-- Sama seperti blok Unit Layanan di sidebar: khusus admin wilayah. --}}
                        @if(isset($hasActiveServices) && !$hasActiveServices && !auth()->user()->bolehAksesPlatform() && in_array(auth()->user()->role, ['admin_desa']))
                            @php
                                $settingsRoute = in_array(auth()->user()->role, ['super_admin']) ? route('admin.system-settings.index') : route('admin.region-settings.index');
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
                            <li class="nav-item dropdown me-2 me-sm-3 position-static position-sm-relative">
                                <a class="nav-link dropdown-toggle hide-arrow position-relative rounded-circle d-flex align-items-center justify-content-center" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-display="static" data-bs-auto-close="outside" style="width: 38px; height: 38px;" aria-label="Notifikasi">
                                    <i class="bx bx-bell fs-4 text-body"></i>
                                    @php
                                        // Aturan siapa melihat notifikasi apa ada di
                                        // AdminNotification::scopeUntukPengguna(), dipakai bersama
                                        // halaman "Lihat Semua" supaya keduanya tidak pernah
                                        // berbeda pendapat lagi.
                                        $currentUser = auth()->user();
                                        $canDeleteNotifs = $currentUser && !$currentUser->isStaff();
                                        $hasAdminNotifTable = \Illuminate\Support\Facades\Schema::hasTable('admin_notifications');
                                        $notifQuery = $hasAdminNotifTable ? \App\Models\AdminNotification::untukPengguna($currentUser) : null;
                                        $unreadCount = $notifQuery ? (clone $notifQuery)->where('is_read', false)->count() : 0;
                                        $recentNotifications = $notifQuery ? (clone $notifQuery)->latest()->take(30)->get() : collect();
                                    @endphp
                                    <span id="notif-badge-bell" class="badge bg-danger rounded-pill badge-notifications position-absolute border border-2 border-white {{ $unreadCount > 0 ? '' : 'd-none' }}" style="top: 1px; right: 0px; font-size: 9.5px; min-width: 18px; height: 18px; padding: 0 4px; display: flex; align-items: center; justify-content: center;">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end py-0 notif-dropdown-menu" id="notifDropdownMenu">
                                    <li class="dropdown-menu-header notif-header-bar">
                                        <div class="d-flex align-items-center justify-content-between gap-2 pt-3 pb-2 px-3 flex-wrap">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-primary" style="width: 30px; height: 30px;">
                                                    <i class="bx bx-bell text-primary fs-6"></i>
                                                </div>
                                                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.96rem;">Notifikasi</h6>
                                                <span id="notif-badge-header" class="badge bg-label-primary rounded-pill px-2 py-1 fw-semibold {{ $unreadCount > 0 ? '' : 'd-none' }}" style="font-size: 0.7rem;">{{ $unreadCount }} Baru</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-1.5">
                                                <button type="button" id="btn-mark-all-read" class="notif-action-pill notif-action-primary notif-mark-all {{ $unreadCount > 0 ? '' : 'd-none' }}" data-url="{{ route('admin.notifications.mark-all-read') }}" title="Tandai semua notifikasi sudah dibaca">
                                                    <i class="bx bx-check-double"></i>
                                                    <span class="d-none d-sm-inline">Dibaca</span>
                                                </button>
                                                @if($canDeleteNotifs)
                                                    <button type="button" id="btn-delete-all-notifs" class="notif-action-pill notif-action-danger {{ $recentNotifications->isNotEmpty() ? '' : 'd-none' }}" data-url="{{ route('admin.notifications.deleteAll') }}" title="Bersihkan semua notifikasi">
                                                        <i class="bx bx-trash"></i>
                                                        <span>Bersihkan</span>
                                                    </button>
                                                @endif
                                                <button type="button" id="btn-toggle-notif-expand" class="notif-action-pill" title="Perbesar panel notifikasi" aria-expanded="false">
                                                    <i class="bx bx-expand-alt" id="notif-expand-icon"></i>
                                                    <span id="notif-expand-label">Perbesar</span>
                                                </button>
                                            </div>
                                        </div>

                                        @if($canDeleteNotifs)
                                            <!-- Bar Konfirmasi Hapus Semua (Inline) -->
                                            <div id="notif-delete-all-confirm" class="notif-confirm-bar d-none">
                                                <span class="text-danger fw-semibold" style="font-size: 0.76rem;">
                                                    <i class="bx bx-error-circle me-1"></i>Bersihkan semua notifikasi?
                                                </span>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <button type="button" id="btn-cancel-delete-all" class="btn btn-xs btn-light border rounded-pill px-2.5 fw-semibold">Batal</button>
                                                    <button type="button" id="btn-confirm-delete-all" class="btn btn-xs btn-danger rounded-pill px-2.5 fw-semibold">Ya, Hapus Semua</button>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Pill Filter Layanan -->
                                        <div class="px-3 pb-2.5 pt-1">
                                            <div class="notif-filter-wrapper">
                                                <button type="button" class="notif-scroll-arrow notif-scroll-prev d-none" aria-label="Geser ke kiri">
                                                    <i class="bx bx-chevron-left"></i>
                                                </button>
                                                <div class="notif-filter-scroll">
                                                    <button type="button" class="btn btn-sm notif-filter-btn active" data-filter="all">Semua</button>
                                                    <button type="button" class="btn btn-sm notif-filter-btn" data-filter="rental">Sewa Alat</button>
                                                    <button type="button" class="btn btn-sm notif-filter-btn" data-filter="gas">Gas LPG</button>
                                                    <button type="button" class="btn btn-sm notif-filter-btn" data-filter="mobil">Transportasi</button>
                                                    <button type="button" class="btn btn-sm notif-filter-btn" data-filter="fasilitas">Fasilitas</button>
                                                    <button type="button" class="btn btn-sm notif-filter-btn" data-filter="kyc">Verifikasi KYC</button>
                                                    <button type="button" class="btn btn-sm notif-filter-btn" data-filter="mutasi">Mutasi</button>
                                                </div>
                                                <button type="button" class="notif-scroll-arrow notif-scroll-next" aria-label="Geser ke kanan">
                                                    <i class="bx bx-chevron-right"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="notif-body-li">
                                        <div class="notif-list-container" id="notifListContainer">
                                            @foreach($recentNotifications as $notif)
                                            @php
                                                // Mapping icon, category, label, dan link tujuan
                                                $cat = 'lainnya';
                                                $catLabel = 'Aktivitas';
                                                $icon = 'bx-bell';
                                                $color = 'primary';

                                                if (in_array($notif->type, ['rental_request', 'rental_order', 'rental'])) {
                                                    $cat = 'rental'; $catLabel = 'Sewa Alat'; $icon = 'bx-wrench'; $color = 'warning';
                                                    $targetUrl = route('admin.aktivitas.permintaan-pengajuan.index', ['tab' => 'rental']);
                                                } elseif (in_array($notif->type, ['cancellation_request', 'mobil_order', 'rental_mobil', 'mobil'])) {
                                                    $cat = 'mobil'; $catLabel = 'Transportasi'; $icon = 'bx-car'; $color = 'info';
                                                    $targetUrl = route('admin.aktivitas.permintaan-pengajuan.index', ['tab' => 'mobil']);
                                                } elseif (in_array($notif->type, ['fasilitas_order', 'rental_fasilitas', 'fasilitas'])) {
                                                    $cat = 'fasilitas'; $catLabel = 'Fasilitas'; $icon = 'bx-building-house'; $color = 'success';
                                                    $targetUrl = route('admin.aktivitas.permintaan-pengajuan.index', ['tab' => 'fasilitas']);
                                                } elseif (in_array($notif->type, ['gas_order', 'gas'])) {
                                                    $cat = 'gas'; $catLabel = 'Gas LPG'; $icon = 'bxs-gas-pump'; $color = 'danger';
                                                    $targetUrl = route('admin.aktivitas.permintaan-pengajuan.index', ['tab' => 'gas']);
                                                } elseif (in_array($notif->type, ['pasar_order', 'pasar', 'pasar_daerah'])) {
                                                    $cat = 'pasar'; $catLabel = 'Layanan'; $icon = 'bx-store-alt'; $color = 'primary';
                                                    $targetUrl = route('admin.notifications.index');
                                                } elseif (in_array($notif->type, ['kyc', 'kyc_verification'])) {
                                                    $cat = 'kyc'; $catLabel = 'Verifikasi KYC'; $icon = 'bx-id-card'; $color = 'info';
                                                    $targetUrl = ($notif->reference_id && Route::has('admin.kyc.show')) ? route('admin.kyc.show', $notif->reference_id) : route('admin.kyc.index');
                                                } elseif ($notif->type === 'mutasi') {
                                                    $cat = 'mutasi'; $catLabel = 'Mutasi'; $icon = 'bx-user-pin'; $color = 'secondary';
                                                    $targetUrl = route('admin.warga.mutasi.index');
                                                } elseif (in_array($notif->type, ['laporan', 'pengumuman'])) {
                                                    $cat = 'laporan'; $catLabel = 'Laporan'; $icon = 'bx-message-square-error'; $color = 'danger';
                                                    $targetUrl = route('admin.aktivitas.permintaan-pengajuan.index');
                                                } else {
                                                    $targetUrl = route('admin.aktivitas.permintaan-pengajuan.index');
                                                }
                                            @endphp
                                            <div class="notif-row {{ $canDeleteNotifs ? '' : 'no-delete' }}" data-category="{{ $cat }}" data-id="{{ $notif->id }}" data-unread="{{ !$notif->is_read ? '1' : '0' }}">
                                                <a href="{{ $targetUrl }}" class="dropdown-item notif-item {{ !$notif->is_read ? 'is-unread category-'.$cat : '' }} gap-3" data-category="{{ $cat }}" data-id="{{ $notif->id }}" data-unread="{{ !$notif->is_read ? '1' : '0' }}" data-read-url="{{ route('admin.notifications.mark-as-read', $notif->id) }}">
                                                    <div class="notif-item-icon bg-label-{{ $color }}">
                                                        <i class="bx {{ $icon }} fs-5 text-{{ $color }}"></i>
                                                    </div>
                                                    <div class="flex-grow-1" style="min-width: 0;">
                                                        <div class="d-flex align-items-center justify-content-between gap-2 mb-0.5">
                                                            <h6 class="mb-0 fw-bold notif-item-title text-truncate">{{ $notif->title }}</h6>
                                                            @if(!$notif->is_read)
                                                                <span class="badge badge-dot bg-{{ $color }} flex-shrink-0" style="width: 8px; height: 8px;"></span>
                                                            @endif
                                                        </div>
                                                        <p class="notif-item-desc mb-0">{{ $notif->message }}</p>
                                                        <div class="notif-meta-row">
                                                            <span class="notif-cat-chip">{{ $catLabel }}</span>
                                                            <span class="d-inline-flex align-items-center gap-1">
                                                                <i class="bx bx-time-five"></i> {{ $notif->created_at->diffForHumans() }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                                @if($canDeleteNotifs)
                                                    <button type="button" class="notif-delete-btn" data-delete-url="{{ route('admin.notifications.destroy', $notif->id) }}" title="Hapus notifikasi ini" aria-label="Hapus notifikasi ini">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                            @endforeach

                                            <div class="text-center py-5 px-3 notif-empty-state {{ $recentNotifications->isEmpty() ? '' : 'd-none' }}" id="notifEmptyState">
                                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-3" style="width: 56px; height: 56px;">
                                                    <i class="bx bx-bell-off text-muted fs-2"></i>
                                                </div>
                                                <h6 class="fw-semibold text-dark mb-1">Belum ada notifikasi</h6>
                                                <p class="text-muted small mb-0">Semua pemberitahuan aktivitas terbaru akan muncul di sini.</p>
                                            </div>

                                            <div class="text-center py-5 px-3 d-none notif-filtered-empty" id="notifFilteredEmpty">
                                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-3" style="width: 56px; height: 56px;">
                                                    <i class="bx bx-filter-alt text-muted fs-2"></i>
                                                </div>
                                                <h6 class="fw-semibold text-dark mb-1">Tidak ada notifikasi</h6>
                                                <p class="text-muted small mb-0">Belum ada pemberitahuan pada kategori layanan ini.</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dropdown-menu-footer border-top bg-white">
                                        <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="dropdown-item notif-footer-link">
                                            <span>Lihat Semua Aktivitas</span>
                                            <i class="bx bx-right-arrow-alt fs-5"></i>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Nama Admin -->
                            <li class="nav-item lh-1 me-3 d-none d-sm-block text-end">
                                <span class="fw-semibold d-block text-dark" style="line-height: 1.25; font-size: 0.88rem;">{{ Auth::user()->name ?? 'Admin' }}</span>
                                <small class="text-muted" style="font-size: 11px;">
                                    @php
                                        $roleLabels = [
                                            'super_admin' => 'Super Admin',
                                            'admin' => 'Admin Pusat',
                                            'admin_desa' => 'Admin Desa',
                                            'admin_rw' => 'Admin RW',
                                            'admin_rt' => 'Admin RT',
                                            'staff' => 'Staf Unit',
                                            'user' => 'Pengguna',
                                        ];
                                        $currentRoleLabel = $roleLabels[Auth::user()->role ?? ''] ?? ucfirst(Auth::user()->role ?? 'Admin');
                                    @endphp
                                    {{ $currentRoleLabel }}
                                </small>
                            </li>
                            <!-- Profil Admin -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);"
                                    data-bs-toggle="dropdown">
                                    @if(Auth::user() && Auth::user()->file)
                                        <img src="{{ route('media.avatar', ['filename' => basename(Auth::user()->file->path)]) }}" alt="Avatar"
                                            class="rounded-circle border border-2 border-light shadow-xs" style="width: 40px; height: 40px; object-fit: cover;" />
                                    @else
                                        <div class="rounded-circle d-flex align-items-center justify-content-center border border-2 border-light" style="width: 40px; height: 40px; background-color: #cbd5e1;">
                                            <svg viewBox="0 0 24 24" fill="currentColor" style="width: 24px; height: 24px; color: white;">
                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end admin-user-dropdown">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.profile') }}">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-2.5">
                                                    @if(Auth::user() && Auth::user()->file)
                                                        <img src="{{ route('media.avatar', ['filename' => basename(Auth::user()->file->path)]) }}" alt="Avatar"
                                                            class="rounded-circle" style="width: 38px; height: 38px; object-fit: cover;" />
                                                    @else
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: #cbd5e1;">
                                                            <svg viewBox="0 0 24 24" fill="currentColor" style="width: 22px; height: 22px; color: white;">
                                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1" style="min-width: 0;">
                                                    <span class="fw-semibold d-block text-dark text-truncate" style="max-width: 165px;">{{ Auth::user()->name ?? 'Admin' }}</span>
                                                    <small class="text-muted">{{ Auth::user()->position ?: $currentRoleLabel }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider my-1"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="{{ route('admin.profile') }}">
                                            <i class="bx bx-user-circle me-2 fs-5 text-primary"></i>
                                            <span class="align-middle fw-medium">Profil Saya</span>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider my-1"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center text-danger" href="{{ route('auth.logout') }}"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class="bx bx-power-off me-2 fs-5"></i>
                                            <span class="align-middle fw-medium">Keluar</span>
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

                    {{-- Spanduk wilayah terkunci: khusus admin wilayah, sama seperti
                         blok Unit Layanan di sidebar dan tombol di topbar. --}}
                    @if(isset($hasActiveServices) && !$hasActiveServices && !auth()->user()->bolehAksesPlatform() && in_array(auth()->user()->role, ['admin_desa']) && !request()->routeIs('admin.region-settings.*') && !request()->routeIs('admin.system-settings.*'))
                    <div class="container-xxl pt-3">
                        <div class="alert alert-warning alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center mb-0" role="alert">
                            <i class="bx bx-lock-alt fs-3 me-3 text-warning flex-shrink-0"></i>
                            <div class="flex-grow-1">
                                <strong class="d-block">Unit Layanan Wilayah Sedang Terkunci</strong>
                                <span class="small">Seluruh unit layanan wilayah Anda saat ini nonaktif. Warga tidak dapat melihat atau mengakses layanan di beranda.</span>
                                @php
                                    $settingsRoute = in_array(auth()->user()->role, ['super_admin']) ? route('admin.system-settings.index') : route('admin.region-settings.index');
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
                                &copy; {{ date('Y') }} <strong>Sistem Sinergi Layanan dan Aspirasi Daerah</strong> Kecamatan Bengkalis
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
            <script src="{{ asset('Admin/js/main.js') }}?v={{ time() }}"></script>
            <script src="{{ asset('Admin/js/dashboards-analytics.js') }}"></script>
            {{-- SiladesBeng Global Toast System (Admin) --}}
            <style>
                .sdb-toast-container { position: fixed; top: 70px; right: 16px; z-index: 999999 !important; display: flex; flex-direction: column; gap: 12px; pointer-events: none; max-width: calc(100vw - 32px); overflow: hidden; }
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

                    let activeNotifFilter = 'all';
                    const emptyStateEl = document.getElementById('notifEmptyState');
                    const filteredEmptyEl = document.getElementById('notifFilteredEmpty');
                    const btnDeleteAll = document.getElementById('btn-delete-all-notifs');
                    const confirmDeleteAllBar = document.getElementById('notif-delete-all-confirm');
                    const btnCancelDeleteAll = document.getElementById('btn-cancel-delete-all');
                    const btnConfirmDeleteAll = document.getElementById('btn-confirm-delete-all');
                    const btnToggleExpand = document.getElementById('btn-toggle-notif-expand');
                    const expandIcon = document.getElementById('notif-expand-icon');
                    const expandLabel = document.getElementById('notif-expand-label');

                    function applyNotifFilterAndEmptyState() {
                        const rows = document.querySelectorAll('.notif-row');
                        let visibleCount = 0;

                        rows.forEach(function(row) {
                            const cat = row.getAttribute('data-category');
                            if (activeNotifFilter === 'all' || cat === activeNotifFilter) {
                                row.style.display = '';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        if (rows.length === 0) {
                            if (emptyStateEl) emptyStateEl.classList.remove('d-none');
                            if (filteredEmptyEl) filteredEmptyEl.classList.add('d-none');
                            if (btnDeleteAll) btnDeleteAll.classList.add('d-none');
                            if (confirmDeleteAllBar) confirmDeleteAllBar.classList.add('d-none');
                        } else {
                            if (emptyStateEl) emptyStateEl.classList.add('d-none');
                            if (btnDeleteAll) btnDeleteAll.classList.remove('d-none');
                            if (filteredEmptyEl) {
                                filteredEmptyEl.classList.toggle('d-none', visibleCount > 0);
                            }
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

                            activeNotifFilter = this.getAttribute('data-filter') || 'all';
                            applyNotifFilterAndEmptyState();
                        });
                    });

                    // ---- Perbesar / Kecilkan Panel Notifikasi ----
                    function setNotifExpanded(expanded) {
                        if (!notifDropdownEl) return;
                        notifDropdownEl.classList.toggle('is-expanded', expanded);
                        if (btnToggleExpand) {
                            btnToggleExpand.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                            btnToggleExpand.setAttribute('title', expanded ? 'Kecilkan panel notifikasi' : 'Perbesar panel notifikasi');
                        }
                        if (expandIcon) {
                            expandIcon.classList.toggle('bx-expand-alt', !expanded);
                            expandIcon.classList.toggle('bx-collapse-alt', expanded);
                        }
                        if (expandLabel) {
                            expandLabel.textContent = expanded ? 'Kecilkan' : 'Perbesar';
                        }
                        setTimeout(updateScrollArrows, 240);
                    }

                    if (btnToggleExpand) {
                        btnToggleExpand.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            const isExpanded = notifDropdownEl && notifDropdownEl.classList.contains('is-expanded');
                            setNotifExpanded(!isExpanded);
                        });
                    }

                    // ---- Tandai notifikasi sudah dibaca & hapus notifikasi ----
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const bellBadge = document.getElementById('notif-badge-bell');
                    const headerBadge = document.getElementById('notif-badge-header');
                    const btnMarkAll = document.getElementById('btn-mark-all-read');

                    function bacaPenghitung() {
                        if (!bellBadge || bellBadge.classList.contains('d-none')) return 0;
                        const angka = parseInt(bellBadge.textContent.replace(/\D/g, ''), 10);
                        return isNaN(angka) ? 0 : angka;
                    }

                    function pasangPenghitung(sisa) {
                        if (sisa > 0) {
                            if (bellBadge) {
                                bellBadge.textContent = sisa > 99 ? '99+' : sisa;
                                bellBadge.classList.remove('d-none');
                            }
                            if (headerBadge) {
                                headerBadge.textContent = sisa + ' Baru';
                                headerBadge.classList.remove('d-none');
                            }
                            if (btnMarkAll) btnMarkAll.classList.remove('d-none');
                            return;
                        }
                        if (bellBadge) bellBadge.classList.add('d-none');
                        if (headerBadge) headerBadge.classList.add('d-none');
                        if (btnMarkAll) btnMarkAll.classList.add('d-none');
                    }

                    function tandaiItemSelesai(item) {
                        item.classList.remove('is-unread', 'category-rental', 'category-mobil', 'category-fasilitas', 'category-gas', 'category-pasar', 'category-kyc', 'category-mutasi', 'category-laporan');
                        item.setAttribute('data-unread', '0');
                        const row = item.closest('.notif-row');
                        if (row) row.setAttribute('data-unread', '0');
                        const dot = item.querySelector('.badge-dot');
                        if (dot) dot.remove();
                    }

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
                            parentDrop.addEventListener('hidden.bs.dropdown', function() {
                                if (confirmDeleteAllBar) confirmDeleteAllBar.classList.add('d-none');
                            });
                        }
                    }

                    // Handler tombol "Tandai Dibaca" (semua)
                    if (btnMarkAll) {
                        btnMarkAll.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();

                            btnMarkAll.disabled = true;
                            const originalHtml = btnMarkAll.innerHTML;
                            btnMarkAll.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width: 11px; height: 11px;"></span>';

                            fetch(btnMarkAll.getAttribute('data-url') || '{{ route("admin.notifications.mark-all-read") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ _method: 'PUT' })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.success) {
                                    document.querySelectorAll('.notif-item.is-unread').forEach(tandaiItemSelesai);
                                    pasangPenghitung(0);
                                }
                                btnMarkAll.disabled = false;
                                btnMarkAll.innerHTML = originalHtml;
                            })
                            .catch(err => {
                                console.error('Gagal menandai semua notifikasi:', err);
                                btnMarkAll.disabled = false;
                                btnMarkAll.innerHTML = originalHtml;
                            });
                        });
                    }

                    // Handler hapus notifikasi satu per satu
                    document.querySelectorAll('.notif-delete-btn').forEach(function (btn) {
                        btn.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();

                            const row = this.closest('.notif-row');
                            const deleteUrl = this.getAttribute('data-delete-url');
                            if (!row || !deleteUrl) return;

                            const wasUnread = row.getAttribute('data-unread') === '1';
                            this.disabled = true;
                            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width: 12px; height: 12px;"></span>';

                            fetch(deleteUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ _method: 'DELETE' })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.success) {
                                    row.classList.add('is-removing');
                                    if (wasUnread) {
                                        pasangPenghitung(Math.max(0, bacaPenghitung() - 1));
                                    }
                                    setTimeout(function () {
                                        row.remove();
                                        applyNotifFilterAndEmptyState();
                                    }, 200);
                                } else {
                                    this.disabled = false;
                                    this.innerHTML = '<i class="bx bx-trash"></i>';
                                }
                            })
                            .catch(err => {
                                console.error('Gagal menghapus notifikasi:', err);
                                this.disabled = false;
                                this.innerHTML = '<i class="bx bx-trash"></i>';
                            });
                        });
                    });

                    // Handler tombol "Bersihkan" (Hapus Semua dengan konfirmasi inline)
                    if (btnDeleteAll && confirmDeleteAllBar) {
                        btnDeleteAll.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            confirmDeleteAllBar.classList.toggle('d-none');
                        });
                    }

                    if (btnCancelDeleteAll && confirmDeleteAllBar) {
                        btnCancelDeleteAll.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            confirmDeleteAllBar.classList.add('d-none');
                        });
                    }

                    if (btnConfirmDeleteAll && btnDeleteAll) {
                        btnConfirmDeleteAll.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();

                            const deleteAllUrl = btnDeleteAll.getAttribute('data-url');
                            if (!deleteAllUrl) return;

                            const originalText = this.innerHTML;
                            this.disabled = true;
                            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true" style="width: 11px; height: 11px;"></span>Menghapus...';

                            fetch(deleteAllUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ _method: 'DELETE' })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.success) {
                                    document.querySelectorAll('.notif-row').forEach(function (r) {
                                        r.remove();
                                    });
                                    pasangPenghitung(0);
                                    if (confirmDeleteAllBar) confirmDeleteAllBar.classList.add('d-none');
                                    applyNotifFilterAndEmptyState();
                                }
                            })
                            .catch(err => {
                                console.error('Gagal membersihkan notifikasi:', err);
                            })
                            .finally(() => {
                                this.disabled = false;
                                this.innerHTML = originalText;
                            });
                        });
                    }

                    // Tandai dibaca saat item notifikasi diklik
                    document.querySelectorAll('.notif-item').forEach(function (item) {
                        item.addEventListener('click', function () {
                            if (this.getAttribute('data-unread') !== '1' && !this.classList.contains('is-unread')) return;

                            tandaiItemSelesai(this);
                            pasangPenghitung(Math.max(0, bacaPenghitung() - 1));

                            const markUrl = this.getAttribute('data-read-url');
                            if (markUrl) {
                                const formData = new FormData();
                                formData.append('_token', csrfToken);
                                formData.append('_method', 'PUT');
                                if (navigator.sendBeacon) {
                                    navigator.sendBeacon(markUrl, formData);
                                } else {
                                    fetch(markUrl, {
                                        method: 'POST',
                                        body: formData,
                                        keepalive: true
                                    }).catch(function () {});
                                }
                            }
                        });
                    });
                });
            </script>

            <!-- Sidebar Auto-Scroll & Smart Accordion Engine -->
            <script>
                (function () {
                    // Bersihkan sisa-sisa elemen PerfectScrollbar agar scroll sidebar 100% native
                    function cleanupSidebarScrollbar() {
                        const menuInner = document.querySelector('#layout-menu .menu-inner');
                        if (menuInner) {
                            menuInner.classList.remove('ps');
                            menuInner.querySelectorAll('.ps__rail-x, .ps__rail-y').forEach(function (el) {
                                el.remove();
                            });
                        }
                        if (window.Helpers && window.Helpers.mainMenu && window.Helpers.mainMenu._scrollbar) {
                            try {
                                window.Helpers.mainMenu._scrollbar.destroy();
                            } catch (e) {}
                            window.Helpers.mainMenu._scrollbar = null;
                        }
                    }

                    cleanupSidebarScrollbar();
                    setTimeout(cleanupSidebarScrollbar, 200);
                    setTimeout(cleanupSidebarScrollbar, 600);

                    // Pastikan scrolling dengan mouse wheel / touchpad selalu responsif dan lancar
                    document.addEventListener('wheel', function (e) {
                        const menuInner = e.target.closest('#layout-menu .menu-inner');
                        if (menuInner) {
                            menuInner.scrollTop += e.deltaY;
                        }
                    }, { passive: true });

                    // Fungsi untuk menggeser menu naik ke atas agar dropdown dan semua item di dalamnya tampil utuh
                    function ensureMenuInFullView(menuItem) {
                        const menuInner = document.querySelector('#layout-menu .menu-inner');
                        if (!menuInner || !menuItem) return;

                        const sub = menuItem.querySelector('.menu-sub');
                        if (!sub) return;

                        let bottomEdge = menuItem.getBoundingClientRect().bottom;
                        const lastChild = sub.lastElementChild;
                        if (lastChild) {
                            const lastRect = lastChild.getBoundingClientRect();
                            if (lastRect.bottom > 0) {
                                bottomEdge = Math.max(bottomEdge, lastRect.bottom);
                            }
                        }

                        const viewportHeight = window.innerHeight;
                        // Jarak aman 70px di atas batas bawah layar/taskbar Windows
                        const safetyMargin = 70;
                        const cutoff = bottomEdge - (viewportHeight - safetyMargin);

                        if (cutoff > 0) {
                            menuInner.scrollBy({
                                top: cutoff + 20,
                                behavior: 'smooth'
                            });
                        }
                    }

                    // Callback resmi integrasi dengan event onOpened Menu Sneat
                    window.siladesMenuOnOpened = function (item) {
                        ensureMenuInFullView(item);
                    };

                    // Tangkap event click pada toggle menu langsung pada document
                    document.addEventListener('click', function (e) {
                        const toggle = e.target.closest('#layout-menu .menu-toggle');
                        if (!toggle) return;

                        const menuItem = toggle.closest('.menu-item');
                        if (!menuItem) return;

                        const menuInner = document.querySelector('#layout-menu .menu-inner');
                        if (!menuInner) return;

                        // Evaluasi status saat klik terjadi (fase capture menjamin status terbaca sebelum class toggle berjalan)
                        const isCurrentlyOpen = menuItem.classList.contains('open');

                        if (!isCurrentlyOpen) {
                            // Menu akan dibuka:
                            // 1. Simpan posisi scroll saat ini agar bisa kembali turun saat menu ditutup nanti
                            menuItem.dataset.prevScrollPos = menuInner.scrollTop;

                            // 2. Accordion: Tutup menu saudara yang terbuka di tingkat yang sama
                            const parentUl = menuItem.parentElement;
                            if (parentUl) {
                                const siblings = parentUl.querySelectorAll(':scope > .menu-item.open');
                                siblings.forEach(function (sib) {
                                    if (sib !== menuItem) {
                                        if (window.Helpers && window.Helpers.mainMenu && typeof window.Helpers.mainMenu.close === 'function') {
                                            window.Helpers.mainMenu.close(sib, true);
                                        } else {
                                            sib.classList.remove('open');
                                        }
                                    }
                                });
                            }

                            // 3. Hitung estimasi tinggi dropdown dan geser ke atas sejak awal animasi agar tidak ada jeda
                            const sub = menuItem.querySelector('.menu-sub');
                            if (sub) {
                                const toggleRect = toggle.getBoundingClientRect();
                                const subHeight = sub.scrollHeight || 180;
                                const anticipatedBottom = toggleRect.bottom + subHeight;
                                const viewportLimit = window.innerHeight - 70;
                                const anticipatedCutoff = anticipatedBottom - viewportLimit;

                                if (anticipatedCutoff > 0) {
                                    menuInner.scrollBy({
                                        top: anticipatedCutoff + 20,
                                        behavior: 'smooth'
                                    });
                                }
                            }

                            // 4. Verifikasi dan sempurnakan posisi saat animasi selesai
                            setTimeout(function () {
                                ensureMenuInFullView(menuItem);
                            }, 320);
                            setTimeout(function () {
                                ensureMenuInFullView(menuItem);
                            }, 480);
                        } else {
                            // Menu akan ditutup:
                            // Kembalikan posisi scroll ke tempat semula (turun lagi)
                            const prev = menuItem.dataset.prevScrollPos;
                            if (prev !== undefined) {
                                delete menuItem.dataset.prevScrollPos;
                                setTimeout(function () {
                                    menuInner.scrollTo({
                                        top: parseFloat(prev),
                                        behavior: 'smooth'
                                    });
                                }, 80);
                            }
                        }
                    }, true);
                })();
            </script>
            @yield('modals')
            @stack('modals')
            @yield('scripts')
            @stack('scripts')

    {{-- Dialog konfirmasi bergaya situs, pengganti window.confirm().
         Dipasang di sini karena halaman-halaman admin lain menandai aksi
         berbahayanya dengan atribut data-konfirmasi; tanpa partial ini
         atribut itu tidak berarti apa-apa dan tombol hapus langsung jalan. --}}
    @include('partials.dialog-konfirmasi')
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
    {{-- Panel kotak masuk Gmail. Ditaruh di layout, bukan di halaman dashboard,
         karena super_admin punya berandanya sendiri sehingga panel tidak akan
         pernah terlihat kalau menumpang di sana. Partial-nya mandiri (style &
         script inline), jadi aman di-include paling akhir. --}}
    @auth
        @if(auth()->user()->hasPlatformPermission('platform_inbox'))
            @include('admin.partials.inbox-rail')
        @endif
    @endauth

    <script>
        // Global Scrollable Tab Bar Enhancer:
        // Memastikan semua bar tab horizontal di halaman admin bisa digeser (swipe/drag/klik panah)
        // di layar mobile maupun desktop, serta otomatis memusatkan tab aktif.
        (function () {
            const SELECTOR = 'ul.nav.overflow-x-auto, ul.nav-pills-scrollable, ul.rs-nav-pills, .flex-nowrap.overflow-x-auto';

            function enhanceElement(el) {
                if (!el || el.closest('.admin-scroll-tabs-wrap') || el.closest('.notif-filter-wrapper')) return;
                if (!el.parentNode) return;

                const wrap = document.createElement('div');
                wrap.className = 'admin-scroll-tabs-wrap';

                ['mb-1', 'mb-2', 'mb-3', 'mb-4', 'w-100'].forEach(function (cls) {
                    if (el.classList.contains(cls)) {
                        wrap.classList.add(cls);
                        if (cls.startsWith('mb-')) {
                            el.classList.remove(cls);
                            el.classList.add('mb-0');
                        }
                    }
                });

                const prevBtn = document.createElement('button');
                prevBtn.type = 'button';
                prevBtn.className = 'admin-tabs-arrow prev d-none';
                prevBtn.setAttribute('aria-label', 'Geser menu ke kiri');
                prevBtn.innerHTML = '<i class="bx bx-chevron-left"></i>';

                const nextBtn = document.createElement('button');
                nextBtn.type = 'button';
                nextBtn.className = 'admin-tabs-arrow next d-none';
                nextBtn.setAttribute('aria-label', 'Geser menu ke kanan');
                nextBtn.innerHTML = '<i class="bx bx-chevron-right"></i>';

                el.parentNode.insertBefore(wrap, el);
                wrap.appendChild(prevBtn);
                wrap.appendChild(el);
                wrap.appendChild(nextBtn);

                function updateArrows() {
                    const itemCount = el.querySelectorAll(':scope > li, :scope > a, :scope > button').length;
                    const maxScroll = Math.ceil(el.scrollWidth - el.clientWidth);
                    const hasOverflow = itemCount > 1 && maxScroll > 12;
                    const canLeft = hasOverflow && el.scrollLeft > 6;
                    const canRight = hasOverflow && el.scrollLeft < maxScroll - 6;

                    wrap.classList.toggle('can-scroll-left', canLeft);
                    wrap.classList.toggle('can-scroll-right', canRight);
                    prevBtn.classList.toggle('d-none', !canLeft);
                    nextBtn.classList.toggle('d-none', !canRight);
                }

                prevBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    el.scrollBy({ left: -180, behavior: 'smooth' });
                    setTimeout(updateArrows, 260);
                });

                nextBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    el.scrollBy({ left: 180, behavior: 'smooth' });
                    setTimeout(updateArrows, 260);
                });

                el.addEventListener('scroll', updateArrows, { passive: true });
                window.addEventListener('resize', updateArrows, { passive: true });

                el.addEventListener('click', function (e) {
                    const tab = e.target.closest('.nav-link, .btn-tab-filter, button, a');
                    if (tab && el.contains(tab) && typeof tab.scrollIntoView === 'function') {
                        setTimeout(function () {
                            tab.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                            setTimeout(updateArrows, 260);
                        }, 40);
                    }
                });

                const activeTab = el.querySelector('.nav-link.active, .btn-tab-filter.btn-primary, .btn-tab-filter.btn-success');
                if (activeTab && el.scrollWidth > el.clientWidth + 6) {
                    try {
                        activeTab.scrollIntoView({ behavior: 'auto', inline: 'nearest', block: 'nearest' });
                    } catch (err) {}
                }

                updateArrows();
                setTimeout(updateArrows, 180);
                setTimeout(updateArrows, 600);
            }

            window.initAdminScrollTabs = function () {
                document.querySelectorAll(SELECTOR).forEach(enhanceElement);
            };

            document.addEventListener('DOMContentLoaded', function () {
                window.initAdminScrollTabs();

                const contentWrapper = document.querySelector('.content-wrapper');
                if (contentWrapper && typeof MutationObserver !== 'undefined') {
                    let timer = null;
                    const obs = new MutationObserver(function () {
                        clearTimeout(timer);
                        timer = setTimeout(window.initAdminScrollTabs, 120);
                    });
                    obs.observe(contentWrapper, { childList: true, subtree: true });
                }
            });
        })();
    </script>

    <script src="{{ asset('js/segidaerah-responsive.js') }}?v={{ filemtime(public_path('js/segidaerah-responsive.js')) }}" defer></script>
</body>

</html>
