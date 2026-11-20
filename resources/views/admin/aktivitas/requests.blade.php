@extends('admin.layouts.admin')

@section('title', 'Permintaan & Booking Layanan')

@section('content')
<style>
    .req-stat-card {
        border-radius: 16px;
        border: 1px solid #eef1f6;
        background: #fff;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .req-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(67, 89, 113, 0.08) !important;
    }
    .filter-btn {
        border-radius: 50rem;
        padding: 0.45rem 0.95rem;
        font-size: 0.82rem;
        font-weight: 600;
        transition: all 0.2s;
        border: 1px solid #e4e7ec;
        text-decoration: none;
        background: #fff;
        white-space: nowrap;
        color: #566a7f;
    }
    .filter-btn:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
    }
    .filter-btn-primary.active {
        background-color: #696cff !important;
        border-color: #696cff !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(105, 108, 255, 0.25) !important;
    }
    .filter-btn-danger.active {
        background-color: #ff3e1d !important;
        border-color: #ff3e1d !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(255, 62, 29, 0.25) !important;
    }
    .filter-btn-success.active {
        background-color: #71dd37 !important;
        border-color: #71dd37 !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(113, 221, 55, 0.25) !important;
    }
    .filter-btn-warning.active {
        background-color: #ffab00 !important;
        border-color: #ffab00 !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(255, 171, 0, 0.25) !important;
    }
    .filter-btn-info.active {
        background-color: #03c3ec !important;
        border-color: #03c3ec !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(3, 195, 236, 0.25) !important;
    }
    .req-table th {
        font-size: 0.73rem;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        font-weight: 700;
        color: #8592a3 !important;
        background-color: #f8fafc;
        border-bottom: 1px solid #eef1f6;
    }
    .req-table td {
        vertical-align: middle;
        padding: 0.95rem 1.25rem;
        border-bottom: 1px solid #f1f4f9;
    }
    .req-table tbody tr:hover {
        background-color: #f8fafc;
    }
    .order-mobile-card {
        border-radius: 16px;
        border: 1px solid #e9edf4;
        background: #ffffff;
        transition: box-shadow 0.18s ease;
    }
    .no-scrollbar {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    @php
        $activeTab = request('tab', 'rental');
    @endphp

    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Layanan Desa /</span> Permintaan &amp; Booking
            </h4>
            <p class="text-muted small mb-0">Kelola pesanan warga mulai dari persetujuan, pengantaran/penugasan supir, hingga pengembalian.</p>
        </div>
        <div class="d-flex align-items-center gap-2 w-100 w-sm-auto">
            <a href="{{ route('admin.aktivitas.bukti-transaksi.index') }}" class="btn btn-outline-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-1 flex-grow-1 flex-sm-grow-0">
                <i class="bx bx-check-shield"></i>
                <span>Bukti Pembayaran</span>
            </a>
            <button type="button" class="btn btn-white border rounded-pill px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-1" onclick="location.reload()">
                <i class="bx bx-refresh"></i>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    <!-- Banner Panduan -->
    <div class="card bg-label-primary border-0 shadow-none rounded-4 mb-4">
        <div class="card-body p-3 p-md-4 d-flex align-items-start align-items-md-center gap-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 46px; height: 46px;">
                <i class="bx bx-calendar-check fs-4"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-primary">Alur Pemrosesan Pesanan &amp; Booking Warga</h6>
                <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.5;">
                    Gunakan filter status untuk memantau pesanan yang <strong>Menunggu</strong> persetujuan, <strong>Sedang Proses</strong> pengantaran/sewa, atau pengajuan <strong>Minta Batal</strong>. Klik ikon mata (<i class="bx bx-show"></i>) atau tombol aksi untuk memperbarui tahap pesanan.
                </p>
            </div>
        </div>
    </div>

    <div id="requests-container">
        @include('admin.aktivitas.partials.requests_content')
    </div>
</div>
@endsection
