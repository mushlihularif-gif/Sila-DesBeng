@extends('admin.layouts.admin')

@section('title', 'Laporan Pendapatan')

@section('content')
<style>
    .inc-hero-card {
        border-radius: 16px;
        background: linear-gradient(135deg, #4f46e5 0%, #696cff 60%, #818cf8 100%);
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .inc-unit-card {
        border-radius: 14px;
        border: 1px solid #eef1f6;
        background: #fff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .inc-unit-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(67, 89, 113, 0.08) !important;
    }
    .inc-detail-row {
        border: 1px solid #eef1f6;
        border-radius: 12px;
        padding: 0.9rem 1rem;
        transition: all 0.2s ease;
    }
    .inc-detail-row:hover {
        border-color: rgba(105, 108, 255, 0.35);
        background-color: #f9faff;
    }
    .inc-manual-card {
        border: 1px solid #eef1f6;
        border-radius: 14px;
        padding: 1rem;
        background: #fff;
        margin-bottom: 0.75rem;
    }
    .upload-zone {
        border: 2px dashed #d5dce6;
        border-radius: 12px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .upload-zone:hover {
        border-color: #696cff;
        background-color: rgba(105, 108, 255, 0.04) !important;
    }
    .apexcharts-tooltip {
        background: #ffffff !important;
        border: 1px solid #e0e0e0 !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        border-radius: 8px !important;
        pointer-events: none !important;
    }
    .apexcharts-tooltip * {
        color: #333333 !important;
        font-family: inherit !important;
    }
    .apexcharts-tooltip-title {
        background: #f8f9fa !important;
        border-bottom: 1px solid #eceef1 !important;
        font-weight: bold !important;
        padding: 6px 10px !important;
    }
    .apexcharts-tooltip-text-y-value {
        font-weight: bold !important;
        color: #696cff !important;
    }
    @media (max-width: 991.98px) {
        #manualTransactionModal .border-end-lg {
            border-right: none !important;
            border-bottom: 1px solid #eceef1;
        }
    }
    @media (min-width: 992px) {
        #manualTransactionModal .border-end-lg {
            border-right: 1px solid #eceef1;
        }
    }
</style>

@php
    $activeServices = $activeServices ?? [];
    $isRentalActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'alat'));
    $isGasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'gas'));
    $isMobilActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'mobil') || str_contains(strtolower($name), 'transportasi'));
    $isFasilitasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'fasilitas'));
    $totalActive = collect([$isRentalActive, $isGasActive, $isMobilActive, $isFasilitasActive])->filter()->count();

    $totalTrxYear = ($rentalCount ?? 0) + ($gasCount ?? 0) + ($mobilCount ?? 0) + ($fasilitasCount ?? 0) + $manualReports->count();
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Laporan /</span> Laporan Pendapatan
            </h4>
            <p class="text-muted small mb-0">Ringkasan uang masuk terverifikasi dan catatan pemasukan manual tahun {{ $year }}.</p>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-center w-100 w-lg-auto justify-content-start justify-content-lg-end no-print">
            <!-- Tahun Dropdown -->
            <div class="dropdown">
                <button class="btn btn-white border shadow-sm rounded-pill px-3 d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
                    <i class="bx bx-calendar text-primary"></i>
                    <span>Tahun {{ $year }}</span>
                    <i class="bx bx-chevron-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                    @foreach($availableYears as $optYear)
                        <li>
                            <a class="dropdown-item {{ $optYear == $year ? 'active' : '' }}" href="{{ route('admin.laporan.pendapatan', ['year' => $optYear]) }}">
                                Tahun {{ $optYear }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if($totalActive > 0)
            <button type="button" class="btn btn-success shadow-sm rounded-pill px-3 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#manualTransactionModal">
                <i class="bx bx-plus"></i>
                <span>Catat Manual</span>
            </button>
            @endif

            <a href="{{ route('admin.laporan.pendapatan.riwayat') }}" class="btn btn-outline-primary shadow-sm rounded-pill px-3 d-inline-flex align-items-center gap-1">
                <i class="bx bx-history"></i>
                <span>Riwayat</span>
            </a>

            @if(auth()->user()->role === 'admin_desa')
            <a href="{{ route('admin.refunds.index') }}" class="btn btn-outline-warning shadow-sm rounded-pill px-3 d-inline-flex align-items-center gap-1">
                <i class="bx bx-undo"></i>
                <span>Pengembalian Dana</span>
            </a>
            @endif

            <button type="button" class="btn btn-primary shadow-sm rounded-pill px-3 d-inline-flex align-items-center gap-1" onclick="window.print()">
                <i class="bx bx-printer"></i>
                <span>Cetak</span>
            </button>
        </div>
    </div>

    <!-- Single Concise Info Note -->
    <div class="card bg-label-primary border-0 shadow-none rounded-4 mb-4">
        <div class="card-body py-3 px-3 px-md-4 d-flex align-items-start gap-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 38px; height: 38px;">
                <i class="bx bx-info-circle fs-5"></i>
            </div>
            <div class="small text-primary" style="line-height: 1.5; opacity: 0.92;">
                <strong>Catatan Perhitungan:</strong> Pendapatan dihitung dari pembayaran terverifikasi (transfer/tunai) dan catatan pemasukan manual di luar pesanan aplikasi. Pesanan belum dibayar atau dibatalkan tidak dimasukkan.
            </div>
        </div>
    </div>

    @if($totalActive === 0)
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center">
            <div class="avatar avatar-lg bg-label-warning text-warning rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                <i class="bx bx-info-circle fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">Saat Ini Layanan Belum Diaktifkan</h5>
            <p class="text-muted mb-3">Silakan aktifkan minimal satu layanan pada menu Aktivasi &amp; Katalog Unit.</p>
            @if(auth()->user()->role === 'admin_desa')
                <div>
                    <a href="{{ route('admin.region-settings.index') }}" class="btn btn-primary rounded-pill px-4">
                        <i class="bx bx-slider-alt me-1"></i>Aktivasi Unit Layanan
                    </a>
                </div>
            @endif
        </div>
    @else

    <!-- Hero Total Pendapatan Card -->
    <div class="card inc-hero-card border-0 shadow-sm mb-4">
        <div class="position-absolute rounded-circle" style="width: 240px; height: 240px; right: -70px; top: -90px; background: rgba(255,255,255,0.1); pointer-events: none;"></div>
        <div class="position-absolute rounded-circle" style="width: 160px; height: 160px; right: 60px; bottom: -80px; background: rgba(255,255,255,0.08); pointer-events: none;"></div>
        <div class="card-body p-3 p-md-4 position-relative">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(255,255,255,0.2); font-size: 0.75rem;">
                            <i class="bx bx-wallet me-1"></i>Total Pendapatan Tahun {{ $year }}
                        </span>
                        @if($growth['total'] > 0)
                            <span class="badge bg-success rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                <i class="bx bx-trending-up me-1"></i>+{{ number_format($growth['total'], 1) }}% vs {{ $year - 1 }}
                            </span>
                        @elseif($growth['total'] < 0)
                            <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                <i class="bx bx-trending-down me-1"></i>{{ number_format($growth['total'], 1) }}% vs {{ $year - 1 }}
                            </span>
                        @else
                            <span class="badge rounded-pill px-2 py-1" style="background: rgba(255,255,255,0.18); font-size: 0.72rem;">
                                0% vs {{ $year - 1 }}
                            </span>
                        @endif
                    </div>
                    <h2 class="fw-bold text-white mb-1" style="letter-spacing: -0.5px;">
                        Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatan }}">{{ number_format($totalPendapatan, 0, ',', '.') }}</span>
                    </h2>
                    <small style="opacity: 0.85;">
                        Akumulasi dari {{ $totalActive }} unit layanan aktif &amp; {{ $manualReports->count() }} catatan manual ({{ $totalTrxYear }} transaksi)
                    </small>
                </div>
                <div class="col-12 col-md-4 text-start text-md-end no-print">
                    <a href="{{ route('admin.laporan.pendapatan.riwayat') }}" class="btn btn-light rounded-pill px-4 fw-semibold text-primary shadow-sm">
                        <i class="bx bx-list-ul me-1"></i>Lihat Rincian Masuk
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Per-Unit Summary Cards (Spacious, Tidak Terpotong) -->
    <div class="row g-3 mb-4">
        @if($isRentalActive)
        <div class="col-6 col-lg-3">
            <div class="card inc-unit-card shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.7rem;">Penyewaan Alat</span>
                        <div class="avatar avatar-sm bg-label-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <img src="{{ asset('User/img/elemen/F1.png') }}" style="width: 20px; height: 20px; object-fit: contain;" alt="">
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark mb-1" style="font-size: 1.05rem;">
                        Rp <span class="count-up-rupiah" data-value="{{ $totalPenyewaan }}">{{ number_format($totalPenyewaan, 0, ',', '.') }}</span>
                    </h5>
                    <div class="small text-muted d-flex align-items-center gap-1" style="font-size: 0.74rem;">
                        @if($growth['rental'] > 0)
                            <span class="text-success fw-semibold"><i class="bx bx-trending-up"></i> +{{ number_format($growth['rental'], 1) }}%</span>
                        @elseif($growth['rental'] < 0)
                            <span class="text-danger fw-semibold"><i class="bx bx-trending-down"></i> {{ number_format($growth['rental'], 1) }}%</span>
                        @else
                            <span class="text-secondary fw-semibold">0%</span>
                        @endif
                        <span>&middot; {{ $rentalCount ?? 0 }} trx</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($isGasActive)
        <div class="col-6 col-lg-3">
            <div class="card inc-unit-card shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.7rem;">Penjualan Gas</span>
                        <div class="avatar avatar-sm bg-label-info rounded-3 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <img src="{{ asset('User/img/elemen/F2.png') }}" style="width: 20px; height: 20px; object-fit: contain;" alt="">
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark mb-1" style="font-size: 1.05rem;">
                        Rp <span class="count-up-rupiah" data-value="{{ $totalGas }}">{{ number_format($totalGas, 0, ',', '.') }}</span>
                    </h5>
                    <div class="small text-muted d-flex align-items-center gap-1" style="font-size: 0.74rem;">
                        @if($growth['gas'] > 0)
                            <span class="text-success fw-semibold"><i class="bx bx-trending-up"></i> +{{ number_format($growth['gas'], 1) }}%</span>
                        @elseif($growth['gas'] < 0)
                            <span class="text-danger fw-semibold"><i class="bx bx-trending-down"></i> {{ number_format($growth['gas'], 1) }}%</span>
                        @else
                            <span class="text-secondary fw-semibold">0%</span>
                        @endif
                        <span>&middot; {{ $gasCount ?? 0 }} trx</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($isMobilActive)
        <div class="col-6 col-lg-3">
            <div class="card inc-unit-card shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.7rem;">Transportasi</span>
                        <div class="avatar avatar-sm bg-label-danger rounded-3 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <img src="{{ asset('User/img/elemen/mobil.png') }}" style="width: 20px; height: 20px; object-fit: contain;" alt="">
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark mb-1" style="font-size: 1.05rem;">
                        Rp <span class="count-up-rupiah" data-value="{{ $totalMobil }}">{{ number_format($totalMobil, 0, ',', '.') }}</span>
                    </h5>
                    <div class="small text-muted d-flex align-items-center gap-1" style="font-size: 0.74rem;">
                        @if($growth['mobil'] > 0)
                            <span class="text-success fw-semibold"><i class="bx bx-trending-up"></i> +{{ number_format($growth['mobil'], 1) }}%</span>
                        @elseif($growth['mobil'] < 0)
                            <span class="text-danger fw-semibold"><i class="bx bx-trending-down"></i> {{ number_format($growth['mobil'], 1) }}%</span>
                        @else
                            <span class="text-secondary fw-semibold">0%</span>
                        @endif
                        <span>&middot; {{ $mobilCount ?? 0 }} trx</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($isFasilitasActive)
        <div class="col-6 col-lg-3">
            <div class="card inc-unit-card shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.7rem;">Fasilitas Umum</span>
                        <div class="avatar avatar-sm bg-label-success rounded-3 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <img src="{{ asset('User/img/elemen/fasilitas.png') }}" style="width: 20px; height: 20px; object-fit: contain;" alt="">
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark mb-1" style="font-size: 1.05rem;">
                        Rp <span class="count-up-rupiah" data-value="{{ $totalFasilitas ?? 0 }}">{{ number_format($totalFasilitas ?? 0, 0, ',', '.') }}</span>
                    </h5>
                    <div class="small text-muted d-flex align-items-center gap-1" style="font-size: 0.74rem;">
                        @if(($growth['fasilitas'] ?? 0) > 0)
                            <span class="text-success fw-semibold"><i class="bx bx-trending-up"></i> +{{ number_format($growth['fasilitas'] ?? 0, 1) }}%</span>
                        @elseif(($growth['fasilitas'] ?? 0) < 0)
                            <span class="text-danger fw-semibold"><i class="bx bx-trending-down"></i> {{ number_format($growth['fasilitas'] ?? 0, 1) }}%</span>
                        @else
                            <span class="text-secondary fw-semibold">0%</span>
                        @endif
                        <span>&middot; {{ $fasilitasCount ?? 0 }} trx</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Grafik Tren Pendapatan Bulanan -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="fw-bold mb-0 fs-6">Tren Pendapatan Bulanan</h5>
                        <small class="text-muted">Perbandingan omzet masuk setiap bulan sepanjang tahun {{ $year }}</small>
                    </div>
                    <span class="badge bg-label-primary rounded-pill px-3 py-2">Tahun {{ $year }}</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div id="kinerjaChart" style="min-height: 310px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rincian Per Unit (Bulan Terpilih) + Proporsi Donut -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="fw-bold mb-0 fs-6">Detail Pendapatan per Unit</h5>
                        <small class="text-muted">Kontribusi tiap unit layanan pada bulan terpilih</small>
                    </div>
                    <select id="pendapatan-month" class="form-select form-select-sm bg-light border fw-medium no-print" style="width: auto;">
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $month)
                            <option value="{{ $index + 1 }}" {{ ($index + 1) == ($totalPendapatanData['month'] ?? date('m')) ? 'selected' : '' }}>
                                {{ $month }} {{ $totalPendapatanData['year'] ?? $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="card-body p-3 p-md-4 d-flex flex-column gap-3">
                    @if($isRentalActive)
                    <div class="inc-detail-row d-flex align-items-center gap-3">
                        <div class="avatar bg-label-warning rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                            <img src="{{ asset('User/img/elemen/F1.png') }}" style="width: 24px; height: 24px; object-fit: contain;" alt="">
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-1">
                                <span class="fw-bold text-dark">Unit Penyewaan Alat</span>
                                <span class="fw-bold text-dark">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['rental']['revenue'] ?? 0 }}">{{ number_format($totalPendapatanData['rental']['revenue'] ?? 0, 0, ',', '.') }}</span></span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $totalPendapatanData['rental']['percentage'] ?? 0 }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span><span class="count-up" data-value="{{ $totalPendapatanData['rental']['transactions'] ?? 0 }}">{{ $totalPendapatanData['rental']['transactions'] ?? 0 }}</span> Transaksi</span>
                                <span>{{ $totalPendapatanData['rental']['percentage'] ?? 0 }}% dari Total</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($isGasActive)
                    <div class="inc-detail-row d-flex align-items-center gap-3">
                        <div class="avatar bg-label-info rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                            <img src="{{ asset('User/img/elemen/F2.png') }}" style="width: 24px; height: 24px; object-fit: contain;" alt="">
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-1">
                                <span class="fw-bold text-dark">Unit Penjualan Gas</span>
                                <span class="fw-bold text-dark">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['gas']['revenue'] ?? 0 }}">{{ number_format($totalPendapatanData['gas']['revenue'] ?? 0, 0, ',', '.') }}</span></span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $totalPendapatanData['gas']['percentage'] ?? 0 }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span><span class="count-up" data-value="{{ $totalPendapatanData['gas']['transactions'] ?? 0 }}">{{ $totalPendapatanData['gas']['transactions'] ?? 0 }}</span> Transaksi</span>
                                <span>{{ $totalPendapatanData['gas']['percentage'] ?? 0 }}% dari Total</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($isMobilActive)
                    <div class="inc-detail-row d-flex align-items-center gap-3">
                        <div class="avatar bg-label-danger rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                            <img src="{{ asset('User/img/elemen/mobil.png') }}" style="width: 24px; height: 24px; object-fit: contain;" alt="">
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-1">
                                <span class="fw-bold text-dark">Unit Sewa Transportasi</span>
                                <span class="fw-bold text-dark">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['mobil']['revenue'] ?? 0 }}">{{ number_format($totalPendapatanData['mobil']['revenue'] ?? 0, 0, ',', '.') }}</span></span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $totalPendapatanData['mobil']['percentage'] ?? 0 }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span><span class="count-up" data-value="{{ $totalPendapatanData['mobil']['transactions'] ?? 0 }}">{{ $totalPendapatanData['mobil']['transactions'] ?? 0 }}</span> Transaksi</span>
                                <span>{{ $totalPendapatanData['mobil']['percentage'] ?? 0 }}% dari Total</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($isFasilitasActive)
                    <div class="inc-detail-row d-flex align-items-center gap-3">
                        <div class="avatar bg-label-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                            <img src="{{ asset('User/img/elemen/fasilitas.png') }}" style="width: 24px; height: 24px; object-fit: contain;" alt="">
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-1">
                                <span class="fw-bold text-dark">Unit Fasilitas Umum</span>
                                <span class="fw-bold text-dark">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['fasilitas']['revenue'] ?? 0 }}">{{ number_format($totalPendapatanData['fasilitas']['revenue'] ?? 0, 0, ',', '.') }}</span></span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $totalPendapatanData['fasilitas']['percentage'] ?? 0 }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span><span class="count-up" data-value="{{ $totalPendapatanData['fasilitas']['transactions'] ?? 0 }}">{{ $totalPendapatanData['fasilitas']['transactions'] ?? 0 }}</span> Transaksi</span>
                                <span>{{ $totalPendapatanData['fasilitas']['percentage'] ?? 0 }}% dari Total</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(($totalPendapatanData['lainnya']['revenue'] ?? 0) > 0)
                    <div class="inc-detail-row d-flex align-items-center gap-3">
                        <div class="avatar bg-label-secondary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                            <i class="bx bx-money fs-4 text-secondary"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-1">
                                <span class="fw-bold text-dark">Pendapatan Lainnya (Manual)</span>
                                <span class="fw-bold text-dark">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['lainnya']['revenue'] ?? 0 }}">{{ number_format($totalPendapatanData['lainnya']['revenue'] ?? 0, 0, ',', '.') }}</span></span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $totalPendapatanData['lainnya']['percentage'] ?? 0 }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span><span class="count-up" data-value="{{ $totalPendapatanData['lainnya']['transactions'] ?? 0 }}">{{ $totalPendapatanData['lainnya']['transactions'] ?? 0 }}</span> Transaksi</span>
                                <span>{{ $totalPendapatanData['lainnya']['percentage'] ?? 0 }}% dari Total</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
                    <h5 class="fw-bold mb-0 fs-6">Proporsi Pendapatan</h5>
                    <small class="text-muted">Distribusi bulan terpilih</small>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3 p-md-4">
                    <div id="pendapatanPieChart" style="width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Catatan Transaksi Manual (Di Luar Aplikasi) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold mb-0 fs-6">Catatan Pemasukan Manual</h5>
                <small class="text-muted">Pemasukan tunai/offline di luar pesanan aplikasi tahun {{ $year }}</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-label-primary rounded-pill px-3 py-2 fw-bold">{{ $manualReports->count() }} Data</span>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 no-print" data-bs-toggle="modal" data-bs-target="#manualTransactionModal">
                    <i class="bx bx-plus me-1"></i>Tambah
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            @if($manualReports->isEmpty())
                <div class="text-center py-5 px-3">
                    <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bx bx-notepad fs-2 opacity-50"></i>
                    </div>
                    <h6 class="text-dark fw-bold mb-1">Belum Ada Catatan Manual</h6>
                    <p class="text-muted small mb-3">Catat hanya pemasukan di luar pesanan aplikasi agar pendapatan tidak terhitung ganda.</p>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 no-print" data-bs-toggle="modal" data-bs-target="#manualTransactionModal">
                        <i class="bx bx-plus me-1"></i>Catat Pemasukan Manual
                    </button>
                </div>
            @else
                <!-- Desktop Table -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Tanggal</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Item &amp; Kategori</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Nominal</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Bukti</th>
                                <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold no-print">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($manualReports as $report)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold text-dark">{{ $report->transaction_date->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $report->transaction_date->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar avatar-sm bg-light rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                                            @if($report->category == 'penyewaan') <i class="bx bx-wrench text-warning"></i>
                                            @elseif($report->category == 'gas') <i class="bx bxs-gas-pump text-info"></i>
                                            @elseif($report->category == 'mobil') <i class="bx bx-car text-danger"></i>
                                            @elseif($report->category == 'fasilitas') <i class="bx bx-buildings text-success"></i>
                                            @else <i class="bx bx-money text-secondary"></i> @endif
                                        </span>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $report->name }}</div>
                                            <small class="text-muted">{{ $report->category_label }} &bull; {{ $report->quantity }} Unit</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">Rp {{ number_format($report->amount * $report->quantity, 0, ',', '.') }}</div>
                                    <small class="text-muted">{{ ucfirst($report->payment_method) }}</small>
                                </td>
                                <td>
                                    @if($report->proof_image)
                                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3"
                                                data-proof="{{ route('admin.laporan.manual.proof', $report->id) }}"
                                                data-title="{{ $report->name }}"
                                                onclick="viewProof(this.dataset.proof, this.dataset.title)">
                                            <i class="bx bx-image-alt me-1"></i>Lihat
                                        </button>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4 no-print">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2"
                                                title="Edit"
                                                data-id="{{ $report->id }}"
                                                data-name="{{ $report->name }}"
                                                data-category="{{ $report->category }}"
                                                data-description="{{ $report->description }}"
                                                data-amount="{{ $report->amount }}"
                                                data-quantity="{{ $report->quantity }}"
                                                data-date="{{ $report->transaction_date->format('Y-m-d') }}"
                                                onclick="editManualTransaction(this)">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2"
                                                title="Hapus"
                                                onclick="deleteManualTransaction({{ $report->id }})">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="d-md-none p-3">
                    @foreach($manualReports as $report)
                    <div class="inc-manual-card shadow-xs">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <div class="fw-bold text-dark">{{ $report->name }}</div>
                                <small class="text-muted">{{ $report->category_label }} &bull; {{ $report->quantity }} Unit &bull; {{ $report->transaction_date->format('d M Y') }}</small>
                            </div>
                            <span class="badge bg-label-success rounded-pill">{{ ucfirst($report->payment_method) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div class="fw-bold text-primary">Rp {{ number_format($report->amount * $report->quantity, 0, ',', '.') }}</div>
                            <div class="d-flex gap-1 no-print">
                                @if($report->proof_image)
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-2"
                                            data-proof="{{ route('admin.laporan.manual.proof', $report->id) }}"
                                            data-title="{{ $report->name }}"
                                            onclick="viewProof(this.dataset.proof, this.dataset.title)">
                                        <i class="bx bx-image-alt"></i>
                                    </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2"
                                        data-id="{{ $report->id }}"
                                        data-name="{{ $report->name }}"
                                        data-category="{{ $report->category }}"
                                        data-description="{{ $report->description }}"
                                        data-amount="{{ $report->amount }}"
                                        data-quantity="{{ $report->quantity }}"
                                        data-date="{{ $report->transaction_date->format('Y-m-d') }}"
                                        onclick="editManualTransaction(this)">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2"
                                        onclick="deleteManualTransaction({{ $report->id }})">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @endif
</div>

@push('modals')
<!-- Manual Transaction Modal -->
<div class="modal fade" id="manualTransactionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar bg-label-primary text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                        <i class="bx bx-receipt fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0 fs-6" id="modalTitle">Catat Transaksi Manual</h5>
                        <small class="text-muted">Pemasukan tunai/offline di luar pesanan aplikasi</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="manualTransactionForm" enctype="multipart/form-data">
                <div class="modal-body p-0">
                    <input type="hidden" id="transactionId" name="id">

                    <div class="row g-0">
                        <!-- Panel Kiri: Info Layanan -->
                        <div class="col-12 col-lg-7 border-end-lg p-3 p-md-4">
                            <div class="fw-bold text-dark text-uppercase small mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                <i class="bx bx-info-circle text-primary"></i>
                                <span>Informasi Layanan &amp; Waktu</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">Tanggal Transaksi <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="transaction_date" name="transaction_date" max="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">Sektor Layanan <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Pilih Unit Layanan...</option>
                                        @if($isRentalActive || $totalActive === 0)
                                            <option value="penyewaan">Penyewaan Alat</option>
                                        @endif
                                        @if($isGasActive || $totalActive === 0)
                                            <option value="gas">Penjualan Gas LPG</option>
                                        @endif
                                        @if($isMobilActive || $totalActive === 0)
                                            <option value="mobil">Penyewaan Transportasi</option>
                                        @endif
                                        @if($isFasilitasActive || $totalActive === 0)
                                            <option value="fasilitas">Fasilitas Umum</option>
                                        @endif
                                        <option value="lainnya">Pendapatan Lain-lain</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark small mb-1">Nama Barang / Layanan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Contoh: Sewa Tenda Pernikahan, Gas 3kg, dll." required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark small mb-1">Catatan Tambahan <span class="text-muted fw-normal">(Opsional)</span></label>
                                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Contoh: Pelanggan Bpk. Budi RT 02, lunas dibayar tunai..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Panel Kanan: Perhitungan -->
                        <div class="col-12 col-lg-5 bg-light p-3 p-md-4">
                            <div class="fw-bold text-dark text-uppercase small mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                <i class="bx bx-calculator text-primary"></i>
                                <span>Perhitungan Nominal</span>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark small mb-1">Jumlah / Qty <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <button class="btn btn-white border px-3 text-primary" type="button" onclick="changeQty(-1)"><i class="bx bx-minus"></i></button>
                                    <input type="number" class="form-control text-center fw-bold bg-white" id="quantity" name="quantity" min="1" value="1" oninput="calculateTotal()" required>
                                    <button class="btn btn-white border px-3 text-primary" type="button" onclick="changeQty(1)"><i class="bx bx-plus"></i></button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark small mb-1">Harga Satuan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white fw-bold">Rp</span>
                                    <input type="number" class="form-control bg-white fw-semibold" id="amount" name="amount" min="0" placeholder="0" oninput="calculateTotal()" required>
                                </div>
                            </div>

                            <div class="card bg-label-primary border-0 rounded-3 p-3 mb-3 text-center shadow-none">
                                <small class="text-uppercase fw-bold text-primary d-block mb-1" style="font-size: 0.68rem;">Total Pendapatan</small>
                                <h4 class="fw-bold text-primary mb-1" id="displayTotal">Rp 0</h4>
                                <small class="text-muted" id="calcFormula">1 Unit x Rp 0</small>
                            </div>

                            <div>
                                <label class="form-label fw-semibold text-dark small mb-1">Bukti Foto / Nota <span class="text-muted fw-normal">(Opsional)</span></label>
                                <div class="upload-zone p-3 text-center bg-white" onclick="document.getElementById('proof_image').click()">
                                    <input type="file" class="d-none" id="proof_image" name="proof_image" accept="image/*" onchange="previewUploadFile(this)">
                                    <div id="uploadPrompt">
                                        <i class="bx bx-cloud-upload fs-2 text-primary"></i>
                                        <div class="fw-semibold text-dark small">Pilih Foto Struk / Nota</div>
                                        <small class="text-muted" style="font-size: 0.72rem;">JPG, PNG, WebP (Maks 2 MB)</small>
                                    </div>
                                    <div id="uploadSuccess" class="d-none align-items-center justify-content-center gap-2">
                                        <i class="bx bx-check-circle fs-3 text-success"></i>
                                        <div class="text-start overflow-hidden">
                                            <div class="fw-semibold text-dark text-truncate small" id="uploadFileName" style="max-width: 160px;"></div>
                                            <small class="text-success" style="font-size: 0.7rem;">Klik untuk ganti file</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="payment_method" value="tunai">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-white py-3 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="submitBtn">
                        <i class="bx bx-save me-1"></i> Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom bg-white py-3 px-4">
                <h6 class="modal-title fw-bold mb-0" id="previewImageTitle">Bukti Transaksi</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-3 bg-light text-center">
                <img src="" id="previewImage" class="img-fluid rounded-3 shadow-sm" style="max-height: 75vh;" alt="Bukti Transaksi">
            </div>
        </div>
    </div>
</div>
@endpush

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>
<script>
    function calculateTotal() {
        const qty = parseInt(document.getElementById('quantity').value) || 0;
        const price = parseFloat(document.getElementById('amount').value) || 0;
        const total = qty * price;
        document.getElementById('displayTotal').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        const subCalc = document.getElementById('calcFormula');
        if (subCalc) {
            subCalc.innerText = qty + ' Unit x Rp ' + new Intl.NumberFormat('id-ID').format(price);
        }
    }

    function changeQty(delta) {
        const input = document.getElementById('quantity');
        let val = parseInt(input.value) || 1;
        val = Math.max(1, val + delta);
        input.value = val;
        calculateTotal();
    }

    function previewUploadFile(input) {
        if (input.files && input.files[0]) {
            document.getElementById('uploadFileName').innerText = input.files[0].name;
            document.getElementById('uploadPrompt').classList.add('d-none');
            document.getElementById('uploadSuccess').classList.remove('d-none');
            document.getElementById('uploadSuccess').classList.add('d-flex');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const monthlyVals = {!! json_encode(array_values($monthlyIncome)) !!};
        const monthlyLabels = {!! json_encode(array_keys($monthlyIncome)) !!};
        const maxMonthly = Math.max(0, ...monthlyVals.map(Number));

        const kinerjaEl = document.querySelector("#kinerjaChart");
        if (kinerjaEl) {
            new ApexCharts(kinerjaEl, {
                series: [{ name: 'Pendapatan', data: monthlyVals }],
                chart: { type: 'area', height: 310, toolbar: { show: false } },
                colors: ['#696cff'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90, 100] } },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                xaxis: {
                    categories: monthlyLabels,
                    labels: { style: { colors: '#8592a3', fontSize: '11px' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    min: 0,
                    max: maxMonthly > 0 ? undefined : 100000,
                    labels: {
                        style: { colors: '#8592a3', fontSize: '11px' },
                        formatter: function(val) {
                            if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1).replace('.0', '') + ' jt';
                            if (val >= 1000) return 'Rp ' + Math.round(val / 1000) + ' rb';
                            return 'Rp ' + Math.round(val);
                        }
                    }
                },
                grid: { borderColor: '#eceef1', strokeDashArray: 4 },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: function (val) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(val);
                        }
                    }
                },
                responsive: [{
                    breakpoint: 576,
                    options: {
                        chart: { height: 250 },
                        xaxis: { labels: { rotate: -45, style: { fontSize: '10px' } } }
                    }
                }]
            }).render();
        }

        const pieEl = document.querySelector("#pendapatanPieChart");
        if (pieEl) {
            const rawItems = [
                { label: 'Penyewaan Alat', value: {{ $totalPendapatanData['rental']['revenue'] ?? 0 }}, color: '#ffab00', active: {{ $isRentalActive ? 'true' : 'false' }} },
                { label: 'Gas LPG', value: {{ $totalPendapatanData['gas']['revenue'] ?? 0 }}, color: '#03c3ec', active: {{ $isGasActive ? 'true' : 'false' }} },
                { label: 'Transportasi', value: {{ $totalPendapatanData['mobil']['revenue'] ?? 0 }}, color: '#ff3e1d', active: {{ $isMobilActive ? 'true' : 'false' }} },
                { label: 'Fasilitas Umum', value: {{ $totalPendapatanData['fasilitas']['revenue'] ?? 0 }}, color: '#71dd37', active: {{ $isFasilitasActive ? 'true' : 'false' }} },
                { label: 'Arsip Terdahulu', value: {{ $totalPendapatanData['pasar']['revenue'] ?? 0 }}, color: '#696cff', active: false },
                { label: 'Lainnya (Manual)', value: {{ $totalPendapatanData['lainnya']['revenue'] ?? 0 }}, color: '#8592a3', active: false }
            ].filter(item => item.value > 0);

            const totalPieRevenue = rawItems.reduce((acc, item) => acc + item.value, 0);

            if (totalPieRevenue === 0) {
                pieEl.innerHTML = '<div class="text-center py-4"><i class="bx bx-pie-chart-alt fs-1 text-muted opacity-25 d-block mb-2"></i><span class="text-muted small">Belum ada pendapatan tercatat pada bulan ini</span></div>';
            } else {
                new ApexCharts(pieEl, {
                    series: rawItems.map(i => i.value),
                    chart: { type: 'donut', height: 270 },
                    labels: rawItems.map(i => i.label),
                    colors: rawItems.map(i => i.color),
                    dataLabels: { enabled: false },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        showAlways: true,
                                        label: 'Total',
                                        fontSize: '13px',
                                        color: '#8592a3',
                                        formatter: function (w) {
                                            return "Rp " + new Intl.NumberFormat('id-ID').format(w.globals.seriesTotals.reduce((a, b) => a + b, 0));
                                        }
                                    }
                                }
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return "Rp " + new Intl.NumberFormat('id-ID').format(val);
                            }
                        }
                    },
                    legend: { position: 'bottom', fontSize: '12px' }
                }).render();
            }
        }

        const manualForm = document.getElementById('manualTransactionForm');
        const modalEl = document.getElementById('manualTransactionModal');
        const modal = modalEl ? new bootstrap.Modal(modalEl) : null;

        if (manualForm) {
            manualForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const id = document.getElementById('transactionId').value;
                const url = id
                    ? `{{ route('admin.laporan.manual.update', ':id') }}`.replace(':id', id)
                    : `{{ route('admin.laporan.manual.store') }}`;

                if (id) formData.append('_method', 'PUT');

                const btn = document.getElementById('submitBtn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
                btn.disabled = true;

                fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (modal) modal.hide();
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1500, showConfirmButton: false }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', data.message || 'Periksa kembali data yang dimasukkan.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            });
        }

        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function () {
                manualForm?.reset();
                document.getElementById('transactionId').value = '';
                document.getElementById('modalTitle').innerText = 'Catat Transaksi Manual';
                document.getElementById('submitBtn').innerHTML = '<i class="bx bx-save me-1"></i> Simpan Transaksi';
                document.getElementById('displayTotal').innerText = 'Rp 0';
                const subCalc = document.getElementById('calcFormula');
                if (subCalc) subCalc.innerText = '1 Unit x Rp 0';
                document.getElementById('uploadPrompt')?.classList.remove('d-none');
                document.getElementById('uploadSuccess')?.classList.add('d-none');
                document.getElementById('uploadSuccess')?.classList.remove('d-flex');
                const fn = document.getElementById('uploadFileName');
                if (fn) fn.innerText = '';
            });
        }

        document.getElementById('pendapatan-month')?.addEventListener('change', function() {
            window.location.href = `{{ route('admin.laporan.pendapatan') }}?month=${this.value}&year={{ $year }}`;
        });
    });

    window.editManualTransaction = function(btn) {
        document.getElementById('transactionId').value = btn.getAttribute('data-id');
        document.getElementById('transaction_date').value = btn.getAttribute('data-date');
        document.getElementById('category').value = btn.getAttribute('data-category');
        document.getElementById('name').value = btn.getAttribute('data-name');
        document.getElementById('description').value = btn.getAttribute('data-description') || '';
        document.getElementById('amount').value = btn.getAttribute('data-amount');
        document.getElementById('quantity').value = btn.getAttribute('data-quantity');
        document.getElementById('modalTitle').innerText = 'Edit Transaksi Manual';
        document.getElementById('submitBtn').innerHTML = '<i class="bx bx-save me-1"></i> Simpan Perubahan';
        calculateTotal();
        const modalEl = document.getElementById('manualTransactionModal');
        (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    };

    window.deleteManualTransaction = function(id) {
        Swal.fire({
            title: 'Batalkan Catatan Manual?',
            text: "Catatan tidak akan dihitung lagi di pendapatan, namun arsip audit tetap tersimpan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff3e1d',
            cancelButtonColor: '#8592a3',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ route('admin.laporan.manual.destroy', ':id') }}`.replace(':id', id), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                });
            }
        });
    };

    window.viewProof = function(url, title) {
        document.getElementById('previewImage').src = url;
        const titleEl = document.getElementById('previewImageTitle');
        if (titleEl) titleEl.textContent = title ? ('Bukti: ' + title) : 'Bukti Transaksi';
        const modalEl = document.getElementById('imagePreviewModal');
        (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    };
</script>
@endsection
