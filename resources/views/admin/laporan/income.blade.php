@extends('admin.layouts.admin')

@section('title', 'Laporan Pendapatan')

@section('content')
<style>
    .animate-fade-up {
        animation: fadeUp 0.5s ease-out forwards;
    }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .stat-card {
        border-radius: 14px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
    }
    .hover-bg-light:hover {
        background-color: rgba(67, 89, 113, 0.04);
    }
    /* Fix Sneat core.css modal close button displacement */
    #manualTransactionModal .modal-header .btn-close {
        position: static !important;
        transform: none !important;
        margin: 0 !important;
        padding: 0.55rem !important;
        box-shadow: none !important;
        border-radius: 50% !important;
        background-color: #f1f3f5 !important;
        opacity: 0.75 !important;
        transition: all 0.2s ease !important;
    }
    #manualTransactionModal .modal-header .btn-close:hover,
    #manualTransactionModal .modal-header .btn-close:focus {
        transform: none !important;
        opacity: 1 !important;
        background-color: #e2e6ea !important;
        box-shadow: none !important;
    }
    #imagePreviewModal .btn-close {
        transform: none !important;
    }
    #imagePreviewModal .btn-close:hover,
    #imagePreviewModal .btn-close:focus {
        transform: none !important;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y animate-fade-up">
    @php
    $activeServices = $activeServices ?? [];
    $isRentalActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'alat'));
    $isGasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'gas'));
    $isMobilActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'mobil'));
    $isFasilitasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'fasilitas'));
    $isPasarActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'pasar'));
    $totalActive = collect([$isRentalActive, $isGasActive, $isMobilActive, $isFasilitasActive, $isPasarActive])->filter()->count();
    @endphp

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Laporan /</span> Pendapatan Keuangan
            </h4>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center w-100 w-md-auto justify-content-start justify-content-md-end">
            <div class="dropdown">
                <button class="btn btn-white border shadow-sm rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                    <i class="bx bx-calendar me-1"></i>Tahun {{ $year }}
                </button>
                <ul class="dropdown-menu shadow border-0 rounded-3">
                    @foreach($availableYears as $optYear)
                        <li><a class="dropdown-item {{ $optYear == $year ? 'active' : '' }}" href="{{ route('admin.laporan.pendapatan', ['year' => $optYear]) }}">{{ $optYear }}</a></li>
                    @endforeach
                </ul>
            </div>
            
            @if($totalActive > 0)
            <button class="btn btn-success shadow-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#manualTransactionModal">
                <i class="bx bx-plus me-1"></i>Catat Manual
            </button>
            @endif
            
            <a href="{{ route('admin.laporan.pendapatan.riwayat') }}" class="btn btn-info text-white shadow-sm rounded-pill px-3">
                <i class="bx bx-history me-1"></i>Riwayat
            </a>
            
            <button class="btn btn-primary shadow-sm rounded-pill px-3" onclick="window.print()">
                <i class="bx bx-printer me-1"></i>Cetak
            </button>
        </div>
    </div>

    <!-- Panduan Banner -->
    <div class="card bg-label-primary border-0 shadow-none mb-4" style="border-radius: 12px;">
        <div class="card-body d-flex align-items-center p-3 p-md-4">
            <div class="me-3">
                <div class="bg-primary p-2 p-md-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                    <i class="bx bx-info-circle fs-4 fs-md-3"></i>
                </div>
            </div>
            <div>
                <h5 class="fw-bold mb-1 text-primary fs-6 fs-md-5">Panduan Laporan Pendapatan</h5>
                <p class="mb-0 text-primary small" style="opacity: 0.85;">
                    Menu ini khusus memvisualisasikan ringkasan performa finansial dan total uang masuk dari unit layanan Anda sendiri. Gunakan grafik di bawah ini untuk mengevaluasi omzet bulanan dan mengukur persentase kontribusi dari tiap sektor usaha.
                </p>
            </div>
        </div>
    </div>
        
    @if($totalActive === 0)
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 text-center">
            <div class="avatar avatar-lg bg-warning-subtle text-warning rounded-circle mx-auto mb-3">
                <i class="bx bx-info-circle fs-2"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">Saat ini Layanan Belum Di Aktifkan</h5>
            <p class="text-muted mb-0">Silakan aktifkan setidaknya satu layanan pada menu Pengaturan Wilayah.</p>
        </div>
    @else

    <!-- Summary Statistics -->
    <div class="row g-2 g-md-3 mb-4">
        <!-- Total Pendapatan -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card overflow-hidden">
                <div class="card-body p-2 p-md-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-3 p-1 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px;">
                            <i class="bx bx-wallet fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <small class="text-muted text-uppercase fw-bold ls-1 d-block text-truncate" style="font-size: 0.65rem;">Total Pendapatan</small>
                        </div>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 0.92rem;">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatan }}">0</span></h6>
                        <div class="small text-muted d-flex align-items-center text-truncate" style="font-size: 0.65rem;">
                            @if($growth['total'] > 0)
                                <i class="bx bx-trending-up text-success me-1"></i>
                                <span class="text-success fw-semibold me-1">+{{ number_format($growth['total'], 1) }}%</span>
                            @elseif($growth['total'] < 0)
                                <i class="bx bx-trending-down text-danger me-1"></i>
                                <span class="text-danger fw-semibold me-1">{{ number_format($growth['total'], 1) }}%</span>
                            @else
                                <i class="bx bx-minus text-secondary me-1"></i>
                                <span class="text-secondary fw-semibold me-1">0%</span>
                            @endif
                            <span class="text-truncate">thn lalu</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if($isRentalActive)
        <!-- Unit Penyewaan -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card overflow-hidden">
                <div class="card-body p-2 p-md-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar avatar-sm bg-warning-subtle text-warning rounded-3 p-1 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px;">
                            <img src="{{ asset('User/img/elemen/F1.png') }}" style="width: 20px; height: 20px; object-fit: contain;">
                        </div>
                        <div class="overflow-hidden">
                            <small class="text-muted text-uppercase fw-bold ls-1 d-block text-truncate" style="font-size: 0.65rem;">Penyewaan</small>
                        </div>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 0.92rem;">Rp <span class="count-up-rupiah" data-value="{{ $totalPenyewaan }}">0</span></h6>
                        <div class="small text-muted d-flex align-items-center text-truncate" style="font-size: 0.65rem;">
                            @if($growth['rental'] > 0)
                                <i class="bx bx-trending-up text-success me-1"></i>
                                <span class="text-success fw-semibold me-1">+{{ number_format($growth['rental'], 1) }}%</span>
                            @elseif($growth['rental'] < 0)
                                <i class="bx bx-trending-down text-danger me-1"></i>
                                <span class="text-danger fw-semibold me-1">{{ number_format($growth['rental'], 1) }}%</span>
                            @else
                                <i class="bx bx-minus text-secondary me-1"></i>
                                <span class="text-secondary fw-semibold me-1">0%</span>
                            @endif
                            <span class="text-truncate">thn lalu</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        @if($isGasActive)
        <!-- Unit Gas -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card overflow-hidden">
                <div class="card-body p-2 p-md-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar avatar-sm bg-info-subtle text-info rounded-3 p-1 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px;">
                            <img src="{{ asset('User/img/elemen/F2.png') }}" style="width: 20px; height: 20px; object-fit: contain;">
                        </div>
                        <div class="overflow-hidden">
                            <small class="text-muted text-uppercase fw-bold ls-1 d-block text-truncate" style="font-size: 0.65rem;">Gas LPG</small>
                        </div>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 0.92rem;">Rp <span class="count-up-rupiah" data-value="{{ $totalGas }}">0</span></h6>
                        <div class="small text-muted d-flex align-items-center text-truncate" style="font-size: 0.65rem;">
                            @if($growth['gas'] > 0)
                                <i class="bx bx-trending-up text-success me-1"></i>
                                <span class="text-success fw-semibold me-1">+{{ number_format($growth['gas'], 1) }}%</span>
                            @elseif($growth['gas'] < 0)
                                <i class="bx bx-trending-down text-danger me-1"></i>
                                <span class="text-danger fw-semibold me-1">{{ number_format($growth['gas'], 1) }}%</span>
                            @else
                                <i class="bx bx-minus text-secondary me-1"></i>
                                <span class="text-secondary fw-semibold me-1">0%</span>
                            @endif
                            <span class="text-truncate">thn lalu</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        @if($isMobilActive)
        <!-- Sewa Mobil -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card overflow-hidden">
                <div class="card-body p-2 p-md-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar avatar-sm bg-danger-subtle text-danger rounded-3 p-1 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px;">
                            <img src="{{ asset('User/img/elemen/mobil.png') }}" style="width: 20px; height: 20px; object-fit: contain;">
                        </div>
                        <div class="overflow-hidden">
                            <small class="text-muted text-uppercase fw-bold ls-1 d-block text-truncate" style="font-size: 0.65rem;">Sewa Mobil</small>
                        </div>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 0.92rem;">Rp <span class="count-up-rupiah" data-value="{{ $totalMobil }}">0</span></h6>
                        <div class="small text-muted d-flex align-items-center text-truncate" style="font-size: 0.65rem;">
                            @if($growth['mobil'] > 0)
                                <i class="bx bx-trending-up text-success me-1"></i>
                                <span class="text-success fw-semibold me-1">+{{ number_format($growth['mobil'], 1) }}%</span>
                            @elseif($growth['mobil'] < 0)
                                <i class="bx bx-trending-down text-danger me-1"></i>
                                <span class="text-danger fw-semibold me-1">{{ number_format($growth['mobil'], 1) }}%</span>
                            @else
                                <i class="bx bx-minus text-secondary me-1"></i>
                                <span class="text-secondary fw-semibold me-1">0%</span>
                            @endif
                            <span class="text-truncate">thn lalu</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        @if($isFasilitasActive ?? false)
        <!-- Fasilitas Umum -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card overflow-hidden">
                <div class="card-body p-2 p-md-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar avatar-sm bg-success-subtle text-success rounded-3 p-1 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px;">
                            <img src="{{ asset('User/img/elemen/fasilitas.png') }}" style="width: 20px; height: 20px; object-fit: contain;">
                        </div>
                        <div class="overflow-hidden">
                            <small class="text-muted text-uppercase fw-bold ls-1 d-block text-truncate" style="font-size: 0.65rem;">Fasilitas Umum</small>
                        </div>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 0.92rem;">Rp <span class="count-up-rupiah" data-value="{{ $totalFasilitas ?? 0 }}">0</span></h6>
                        <div class="small text-muted d-flex align-items-center text-truncate" style="font-size: 0.65rem;">
                            @if(($growth['fasilitas'] ?? 0) > 0)
                                <i class="bx bx-trending-up text-success me-1"></i>
                                <span class="text-success fw-semibold me-1">+{{ number_format($growth['fasilitas'] ?? 0, 1) }}%</span>
                            @elseif(($growth['fasilitas'] ?? 0) < 0)
                                <i class="bx bx-trending-down text-danger me-1"></i>
                                <span class="text-danger fw-semibold me-1">{{ number_format($growth['fasilitas'] ?? 0, 1) }}%</span>
                            @else
                                <i class="bx bx-minus text-secondary me-1"></i>
                                <span class="text-secondary fw-semibold me-1">0%</span>
                            @endif
                            <span class="text-truncate">thn lalu</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        @if($isPasarActive ?? false)
        <!-- Pasar Daerah -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card overflow-hidden">
                <div class="card-body p-2 p-md-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar avatar-sm bg-secondary-subtle text-secondary rounded-3 p-1 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px;">
                            <img src="{{ asset('Admin/img/pasardaerah/PasarDaerah2.png') }}" style="width: 20px; height: 20px; object-fit: contain;">
                        </div>
                        <div class="overflow-hidden">
                            <small class="text-muted text-uppercase fw-bold ls-1 d-block text-truncate" style="font-size: 0.65rem;">Pasar Daerah</small>
                        </div>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 0.92rem;">Rp <span class="count-up-rupiah" data-value="{{ $totalPasar ?? 0 }}">0</span></h6>
                        <div class="small text-muted d-flex align-items-center text-truncate" style="font-size: 0.65rem;">
                            @if(($growth['pasar'] ?? 0) > 0)
                                <i class="bx bx-trending-up text-success me-1"></i>
                                <span class="text-success fw-semibold me-1">+{{ number_format($growth['pasar'] ?? 0, 1) }}%</span>
                            @elseif(($growth['pasar'] ?? 0) < 0)
                                <i class="bx bx-trending-down text-danger me-1"></i>
                                <span class="text-danger fw-semibold me-1">{{ number_format($growth['pasar'] ?? 0, 1) }}%</span>
                            @else
                                <i class="bx bx-minus text-secondary me-1"></i>
                                <span class="text-secondary fw-semibold me-1">0%</span>
                            @endif
                            <span class="text-truncate">thn lalu</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Kinerja Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Tren Kinerja Keuangan</h5>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm border-0 bg-light fw-medium">
                            <option>Tahun Ini</option>
                            <option>Tahun Lalu</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div id="kinerjaChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Detail Pendapatan Per Unit -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                 <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Detail Pendapatan Unit</h5>
                    <select id="pendapatan-month" class="form-select form-select-sm border-0 bg-light fw-medium" style="width: auto;">
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $month)
                            <option value="{{ $index + 1 }}" {{ ($index + 1) == ($totalPendapatanData['month'] ?? date('m')) ? 'selected' : '' }}>
                                {{ $month }} {{ $totalPendapatanData['year'] ?? date('Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="card-body p-4">
                    @if($isRentalActive)
                    <!-- Rental Item -->
                    <div class="d-flex align-items-center mb-4 p-3 rounded-3 hover-bg-light transition-all border border-dashed-hover">
                         <div class="avatar avatar-md bg-warning-subtle rounded-3 p-2 me-3 d-flex align-items-center justify-content-center">
                            <img src="{{ asset('User/img/elemen/F1.png') }}" style="width: 24px; height: 24px; object-fit: contain;">
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold text-dark mb-0">Unit Penyewaan Alat</h6>
                                <span class="fw-bold text-dark">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['rental']['revenue'] ?? 0 }}">0</span></span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $totalPendapatanData['rental']['percentage'] ?? 0 }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span><span class="count-up" data-value="{{ $totalPendapatanData['rental']['transactions'] ?? 0 }}">0</span> Transaksi</span>
                                <span><span class="count-up" data-value="{{ $totalPendapatanData['rental']['percentage'] ?? 0 }}">0</span>% dari Total</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($isGasActive)
                    <!-- Gas Item -->
                    <div class="d-flex align-items-center mb-4 p-3 rounded-3 hover-bg-light transition-all border border-dashed-hover">
                         <div class="avatar avatar-md bg-info-subtle rounded-3 p-2 me-3 d-flex align-items-center justify-content-center">
                            <img src="{{ asset('User/img/elemen/F2.png') }}" style="width: 24px; height: 24px; object-fit: contain;">
                        </div>
                         <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold text-dark mb-0">Unit Penjualan Gas</h6>
                                <span class="fw-bold text-dark">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['gas']['revenue'] ?? 0 }}">0</span></span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $totalPendapatanData['gas']['percentage'] ?? 0 }}%"></div>
                            </div>
                             <div class="d-flex justify-content-between text-muted small">
                                <span><span class="count-up" data-value="{{ $totalPendapatanData['gas']['transactions'] ?? 0 }}">0</span> Transaksi</span>
                                <span><span class="count-up" data-value="{{ $totalPendapatanData['gas']['percentage'] ?? 0 }}">0</span>% dari Total</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($isMobilActive)
                    <!-- Mobil Item -->
                    <div class="d-flex align-items-center mb-4 p-3 rounded-3 hover-bg-light transition-all border border-dashed-hover">
                          <div class="avatar avatar-md bg-danger-subtle rounded-3 p-2 me-3 d-flex align-items-center justify-content-center">
                              <img src="{{ asset('User/img/elemen/mobil.png') }}" style="width: 24px; height: 24px; object-fit: contain;">
                          </div>
                          <div class="flex-grow-1">
                              <div class="d-flex justify-content-between align-items-center mb-1">
                                  <h6 class="fw-bold text-dark mb-0">Unit Sewa Mobil</h6>
                                  <span class="fw-bold text-dark">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['mobil']['revenue'] ?? 0 }}">0</span></span>
                              </div>
                              <div class="progress mb-2" style="height: 6px;">
                                  <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $totalPendapatanData['mobil']['percentage'] ?? 0 }}%"></div>
                              </div>
                              <div class="d-flex justify-content-between text-muted small">
                                  <span><span class="count-up" data-value="{{ $totalPendapatanData['mobil']['transactions'] ?? 0 }}">0</span> Transaksi</span>
                                  <span><span class="count-up" data-value="{{ $totalPendapatanData['mobil']['percentage'] ?? 0 }}">0</span>% dari Total</span>
                              </div>
                          </div>
                    </div>
                    @endif
                    
                    @if($isFasilitasActive ?? false)
                    <!-- Fasilitas Item -->
                    <div class="d-flex align-items-center mb-4 p-3 rounded-3 hover-bg-light transition-all border border-dashed-hover">
                          <div class="avatar avatar-md bg-success-subtle rounded-3 p-2 me-3 d-flex align-items-center justify-content-center">
                              <img src="{{ asset('User/img/elemen/fasilitas.png') }}" style="width: 24px; height: 24px; object-fit: contain;">
                          </div>
                          <div class="flex-grow-1">
                              <div class="d-flex justify-content-between align-items-center mb-1">
                                  <h6 class="fw-bold text-dark mb-0">Unit Fasilitas Umum</h6>
                                  <span class="fw-bold text-dark">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['fasilitas']['revenue'] ?? 0 }}">0</span></span>
                              </div>
                              <div class="progress mb-2" style="height: 6px;">
                                  <div class="progress-bar bg-success" role="progressbar" style="width: {{ $totalPendapatanData['fasilitas']['percentage'] ?? 0 }}%"></div>
                              </div>
                              <div class="d-flex justify-content-between text-muted small">
                                  <span><span class="count-up" data-value="{{ $totalPendapatanData['fasilitas']['transactions'] ?? 0 }}">0</span> Transaksi</span>
                                  <span><span class="count-up" data-value="{{ $totalPendapatanData['fasilitas']['percentage'] ?? 0 }}">0</span>% dari Total</span>
                              </div>
                          </div>
                    </div>
                    @endif
                    
                    @if($isPasarActive ?? false)
                    <!-- Pasar Item -->
                    <div class="d-flex align-items-center mb-4 p-3 rounded-3 hover-bg-light transition-all border border-dashed-hover">
                          <div class="avatar avatar-md bg-secondary-subtle rounded-3 p-2 me-3 d-flex align-items-center justify-content-center">
                              <img src="{{ asset('Admin/img/pasardaerah/PasarDaerah2.png') }}" style="width: 24px; height: 24px; object-fit: contain;">
                          </div>
                          <div class="flex-grow-1">
                              <div class="d-flex justify-content-between align-items-center mb-1">
                                  <h6 class="fw-bold text-dark mb-0">Pasar Daerah</h6>
                                  <span class="fw-bold text-dark">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['pasar']['revenue'] ?? 0 }}">0</span></span>
                              </div>
                              <div class="progress mb-2" style="height: 6px;">
                                  <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $totalPendapatanData['pasar']['percentage'] ?? 0 }}%"></div>
                              </div>
                              <div class="d-flex justify-content-between text-muted small">
                                  <span><span class="count-up" data-value="{{ $totalPendapatanData['pasar']['transactions'] ?? 0 }}">0</span> Transaksi</span>
                                  <span><span class="count-up" data-value="{{ $totalPendapatanData['pasar']['percentage'] ?? 0 }}">0</span>% dari Total</span>
                              </div>
                          </div>
                    </div>
                    @endif
                    
                    @if(($totalPendapatanData['lainnya']['revenue'] ?? 0) > 0)
                    <!-- Lainnya Item -->
                    <div class="d-flex align-items-center mb-4 p-3 rounded-3 hover-bg-light transition-all border border-dashed-hover">
                          <div class="avatar avatar-md bg-secondary-subtle rounded-3 p-2 me-3 d-flex align-items-center justify-content-center">
                              <i class="bx bx-money fs-4 text-secondary"></i>
                          </div>
                          <div class="flex-grow-1">
                              <div class="d-flex justify-content-between align-items-center mb-1">
                                  <h6 class="fw-bold text-dark mb-0">Pendapatan Lainnya (Manual)</h6>
                                  <span class="fw-bold text-dark">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['lainnya']['revenue'] ?? 0 }}">0</span></span>
                              </div>
                              <div class="progress mb-2" style="height: 6px;">
                                  <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $totalPendapatanData['lainnya']['percentage'] ?? 0 }}%"></div>
                              </div>
                              <div class="d-flex justify-content-between text-muted small">
                                  <span><span class="count-up" data-value="{{ $totalPendapatanData['lainnya']['transactions'] ?? 0 }}">0</span> Transaksi</span>
                                  <span><span class="count-up" data-value="{{ $totalPendapatanData['lainnya']['percentage'] ?? 0 }}">0</span>% dari Total</span>
                              </div>
                          </div>
                    </div>
                    @endif
                  </div>
              </div>
          </div>

          <!-- Donut Chart -->
         <div class="col-lg-4">
             <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0">Proporsi Pendapatan</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-4">
                     <div id="pendapatanPieChart" style="width: 100%;"></div>
                </div>
             </div>
         </div>
    </div>

    <!-- Manual Transactions Section -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Laporan Transaksi Manual</h5>
            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill">{{ $manualReports->count() }} Data</span>
        </div>
        
        <div class="card-body p-0">
             @if($manualReports->isEmpty())
                <div class="text-center py-5">
                    <div class="mb-3"><i class="bx bx-notepad fs-1 text-muted opacity-25"></i></div>
                    <h6 class="text-muted fw-bold mb-1">Belum ada transaksi manual</h6>
                    <p class="text-muted small mb-3">Catat pendapatan di luar sistem secara manual di sini</p>
                    <button class="btn btn-sm btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#manualTransactionModal">
                        Tambah Data
                    </button>
                </div>
             @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                         <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Tanggal</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Item & Kategori</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Nominal</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Bukti</th>
                                <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                            </tr>
                        </thead>
                         <tbody>
                            @foreach($manualReports as $report)
                            <tr>
                                <td class="ps-4">
                                     <div class="d-flex flex-column">
                                        <span class="fw-semibold text-dark">{{ $report->transaction_date->format('d M Y') }}</span>
                                        <small class="text-muted">{{ $report->transaction_date->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                         <div class="avatar avatar-sm bg-light rounded-circle p-1 me-2 d-flex align-items-center justify-content-center">
                                            @if($report->category == 'penyewaan') <i class="bx bx-wrench text-warning"></i>
                                            @elseif($report->category == 'gas') <i class="bx bxs-gas-pump text-info"></i>
                                            @elseif($report->category == 'mobil') <i class="bx bx-car text-danger"></i>
                                            @elseif($report->category == 'fasilitas') <i class="bx bx-buildings text-primary"></i>
                                            @elseif($report->category == 'pasar') <i class="bx bx-store-alt text-success"></i>
                                            @else <i class="bx bx-money text-secondary"></i> @endif
                                        </div>
                                        <div>
                                            <div class="fw-medium text-dark">{{ $report->name }}</div>
                                            <small class="text-muted">{{ $report->category_label }} • {{ $report->quantity }} Unit</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">Rp {{ number_format($report->amount * $report->quantity, 0, ',', '.') }}</div>
                                    <small class="text-muted">{{ ucfirst($report->payment_method) }}</small>
                                </td>
                                <td>
                                     @if($report->proof_image)
                                        <button class="btn btn-sm btn-light border rounded-pill px-3" onclick="viewProof('{{ asset('storage/' . $report->proof_image) }}', '{{ $report->name }}')">
                                            <i class="bx bx-image-alt me-1"></i>Lihat
                                        </button>
                                     @else
                                        <span class="text-muted small italic">- Tidak ada -</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="dropdown" data-bs-boundary="window">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" 
                                                   data-id="{{ $report->id }}"
                                                   data-name="{{ $report->name }}"
                                                   data-category="{{ $report->category }}"
                                                   data-description="{{ $report->description }}"
                                                   data-amount="{{ $report->amount }}"
                                                   data-quantity="{{ $report->quantity }}"
                                                   data-date="{{ $report->transaction_date->format('Y-m-d') }}"
                                                   onclick="editManualTransaction(this)">
                                                    <i class="bx bx-edit me-2 text-warning"></i>Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteManualTransaction({{ $report->id }})">
                                                    <i class="bx bx-trash me-2"></i>Hapus
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                         </tbody>
                    </table>
                </div>
             @endif
        </div>
    </div>
    @endif
</div>

@push('modals')
<!-- Manual Transaction Modal -->
<div class="modal fade" id="manualTransactionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4 bg-white d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 46px; height: 46px;">
                        <i class="bx bx-receipt fs-3"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalTitle">Catat Transaksi Manual</h5>
                        <p class="text-muted small mb-0" style="font-size: 0.8rem;">Pencatatan pendapatan tunai atau offline unit layanan daerah</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" style="transform: none !important; margin: 0 !important;"></button>
            </div>
            <form id="manualTransactionForm" enctype="multipart/form-data">
                <div class="modal-body p-0">
                    <input type="hidden" id="transactionId" name="id">
                    
                    <div class="row g-0">
                        <!-- Left Panel: Transaction Info -->
                        <div class="col-lg-7 border-end p-4 p-md-4">
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                <i class="bx bx-info-circle fs-5 text-primary me-2"></i>
                                <span class="fw-bold text-dark text-uppercase small ls-1">Informasi Layanan & Waktu</span>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">Tanggal Transaksi <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-merge rounded-3 shadow-xs">
                                        <span class="input-group-text bg-light border-end-0 text-primary"><i class="bx bx-calendar"></i></span>
                                        <input type="date" class="form-control bg-light border-start-0 ps-0" id="transaction_date" name="transaction_date" required>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Tanggal uang diterima / dibayar</small>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">Sektor Layanan Daerah <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-merge rounded-3 shadow-xs">
                                        <span class="input-group-text bg-light border-end-0 text-primary"><i class="bx bx-store-alt"></i></span>
                                        <select class="form-select bg-light border-start-0 ps-0 fw-medium" id="category" name="category" required>
                                            <option value="">Pilih Unit Layanan...</option>
                                            @if($isRentalActive || $totalActive === 0)
                                                <option value="penyewaan">Penyewaan Alat</option>
                                            @endif
                                            @if($isGasActive || $totalActive === 0)
                                                <option value="gas">Penjualan Gas LPG</option>
                                            @endif
                                            @if($isMobilActive || $totalActive === 0)
                                                <option value="mobil">Penyewaan Mobil</option>
                                            @endif
                                            @if($isFasilitasActive || $totalActive === 0)
                                                <option value="fasilitas">Fasilitas Umum</option>
                                            @endif
                                            @if($isPasarActive || $totalActive === 0)
                                                <option value="pasar">Pasar Daerah</option>
                                            @endif
                                            <option value="lainnya">Pendapatan Lain-lain</option>
                                        </select>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Sektor usaha terkait transaksi ini</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark small mb-1">Nama Barang / Layanan <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-merge rounded-3 shadow-xs">
                                        <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bx bx-package"></i></span>
                                        <input type="text" class="form-control bg-light border-start-0 ps-0" id="name" name="name" placeholder="Contoh: Sewa Tenda Pernikahan, Gas 3kg, dll." required>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Tuliskan barang atau jasa yang dibayar warga/pelanggan</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark small mb-1">Catatan Tambahan <span class="text-muted fw-normal">(Opsional)</span></label>
                                    <textarea class="form-control bg-light rounded-3 p-2 px-3" id="description" name="description" rows="3" placeholder="Contoh: Pelanggan Bpk. Budi RT 02, lunas dibayar tunai..."></textarea>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Catatan khusus atau identitas pembeli jika ada</small>
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel: Financials -->
                        <div class="col-lg-5 bg-light p-4 p-md-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                    <i class="bx bx-calculator fs-5 text-primary me-2"></i>
                                    <span class="fw-bold text-dark text-uppercase small ls-1">Perhitungan Pendapatan</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark small mb-1">Banyaknya Barang / Pesanan <span class="text-danger">*</span></label>
                                    <div class="input-group rounded-3 shadow-xs">
                                        <button class="btn btn-white border border-end-0 px-3 text-primary" type="button" onclick="changeQty(-1)">
                                            <i class="bx bx-minus"></i>
                                        </button>
                                        <input type="number" class="form-control text-center fw-bold bg-white border" id="quantity" name="quantity" min="1" value="1" oninput="calculateTotal()" required>
                                        <button class="btn btn-white border border-start-0 px-3 text-primary" type="button" onclick="changeQty(1)">
                                            <i class="bx bx-plus"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Berapa unit barang atau frekuensi sewa (misal: 1, 2, dst)</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark small mb-1">Harga Satuan <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-merge rounded-3 shadow-xs">
                                        <span class="input-group-text bg-white text-dark fw-bold border-end-0">Rp</span>
                                        <input type="number" class="form-control bg-white border-start-0 ps-0 fw-semibold" id="amount" name="amount" placeholder="0" oninput="calculateTotal()" required>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Tarif atau harga per 1 barang / layanan</small>
                                </div>

                                <!-- Box Total Ringkasan -->
                                <div class="card bg-primary-subtle border border-primary-subtle rounded-3 p-3 mb-3 text-center">
                                    <div class="text-uppercase small fw-bold text-primary mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Pendapatan Diterima</div>
                                    <h3 class="fw-bolder text-primary mb-1" id="displayTotal">Rp 0</h3>
                                    <div class="text-muted small fw-medium" id="calcFormula" style="font-size: 0.75rem;">1 Unit x Rp 0</div>
                                </div>

                                <!-- File Upload Dropzone -->
                                <div class="mb-2">
                                    <label class="form-label fw-semibold text-dark small mb-1">Bukti Foto / Nota <span class="text-muted fw-normal">(Opsional)</span></label>
                                    <div class="upload-zone rounded-3 p-3 text-center border border-dashed bg-white cursor-pointer" onclick="document.getElementById('proof_image').click()" style="border: 2px dashed #d9dee3; transition: all 0.2s ease;">
                                        <input type="file" class="d-none" id="proof_image" name="proof_image" accept="image/*" onchange="previewUploadFile(this)">
                                        <div id="uploadPrompt">
                                            <i class="bx bx-cloud-upload fs-2 text-primary mb-1"></i>
                                            <div class="fw-semibold text-dark small">Pilih Foto Struk / Nota</div>
                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Format JPG, PNG (Maks 2 MB)</small>
                                        </div>
                                        <div id="uploadSuccess" class="d-none align-items-center justify-content-center gap-2">
                                            <i class="bx bx-check-circle fs-3 text-success"></i>
                                            <div class="text-start overflow-hidden">
                                                <div class="fw-semibold text-dark text-truncate small" id="uploadFileName" style="max-width: 180px;">file.jpg</div>
                                                <small class="text-success d-block" style="font-size: 0.7rem;">File dipilih - Klik untuk ganti</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" name="payment_method" value="tunai">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-white py-3 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm" id="submitBtn">
                        <i class="bx bx-save me-1"></i> Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent shadow-none border-0">
            <div class="modal-body p-0 text-center position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal" style="transform: none !important;"></button>
                <img src="" id="previewImage" class="img-fluid rounded-4 shadow" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>
@endpush

<style>
    .transition-all { transition: all 0.3s ease; }
    .hover-bg-light:hover { background-color: #f8f9fa; }
    .border-dashed-hover:hover { border-style: dashed !important; border-color: #696cff !important; }
    .input-group-merge .form-control:focus, .input-group-merge .form-select:focus { border-color: #696cff; box-shadow: none; }
    .input-group-merge .input-group-text { border-color: #d9dee3; color: #697a8d; }
    
    /* Fix for dropdown getting clipped in table-responsive */
    .table-responsive {
        min-height: 150px;
    }
    @media (min-width: 768px) {
        .table-responsive {
            overflow: visible;
        }
    }
    
    /* ApexCharts Tooltip Fix */
    .apexcharts-tooltip {
        background: #ffffff !important;
        border: 1px solid #e0e0e0 !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        border-radius: 8px !important;
        pointer-events: none !important;
    }
    .apexcharts-tooltip.apexcharts-active {
        opacity: 1 !important;
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
    .upload-zone:hover {
        border-color: #696cff !important;
        background-color: rgba(105, 108, 255, 0.04) !important;
    }
    .shadow-xs {
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>

<!-- Scripts for Charts & Logic -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        // --- CHARTS CONFIGURATION (Optimized) ---
         // Kinerja Chart (Area)
        const kinerjaEl = document.querySelector("#kinerjaChart");
        if (kinerjaEl) {
            new ApexCharts(kinerjaEl, {
                series: [{ name: 'Pendapatan', data: {!! json_encode(array_values($monthlyIncome)) !!} }],
                chart: { type: 'area', height: 350, toolbar: { show: false } },
                colors: ['#696cff'], // Primary
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.05, stops: [0, 90, 100] } },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                xaxis: { 
                    categories: {!! json_encode(array_keys($monthlyIncome)) !!},
                    labels: { style: { colors: '#a1acb8' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                grid: { borderColor: '#eceef1', strokeDashArray: 4 },
                tooltip: {
                    theme: 'light',
                    style: {
                        fontSize: '13px',
                        fontFamily: 'inherit'
                    },
                    marker: { show: true },
                    y: {
                        formatter: function (val) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(val)
                        }
                    }
                }
            }).render();
        }

        // Pie Chart (Donut)
        const pieEl = document.querySelector("#pendapatanPieChart");
        if (pieEl) {
            const seriesData = [
                {{ $totalPendapatanData['rental']['revenue'] ?? 0 }}, 
                {{ $totalPendapatanData['gas']['revenue'] ?? 0 }}, 
                {{ $totalPendapatanData['mobil']['revenue'] ?? 0 }}, 
                {{ $totalPendapatanData['fasilitas']['revenue'] ?? 0 }}, 
                {{ $totalPendapatanData['pasar']['revenue'] ?? 0 }},
                {{ $totalPendapatanData['lainnya']['revenue'] ?? 0 }}
            ];
            const seriesLabels = ['Penyewaan', 'Gas', 'Sewa Mobil', 'Fasilitas Umum', 'Pasar Daerah', 'Lainnya'];
            const seriesColors = ['#ffab00', '#03c3ec', '#ff3e1d', '#696cff', '#71dd37', '#8592a3'];

            const totalPieRevenue = seriesData.reduce((a, b) => a + b, 0);

            if (totalPieRevenue === 0) {
                pieEl.innerHTML = '<div class="text-center py-4"><i class="bx bx-pie-chart-alt fs-1 text-muted opacity-25 d-block mb-2"></i><span class="text-muted small">Belum ada data pendapatan bulan ini</span></div>';
            } else {
                new ApexCharts(pieEl, {
                    series: seriesData,
                    chart: { type: 'donut', height: 260 },
                    labels: seriesLabels,
                    colors: seriesColors,
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
                                        fontSize: '14px', 
                                        color: '#a1acb8',
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
                    legend: { position: 'bottom' }
                }).render();
            }
        }

        // --- MANUAL TRANSACTION LOGIC ---
        const manualForm = document.getElementById('manualTransactionForm');
        const modalEl = document.getElementById('manualTransactionModal');
        const modal = new bootstrap.Modal(modalEl);

        manualForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const id = document.getElementById('transactionId').value;
            const url = id 
                ? `{{ route('admin.laporan.manual.update', ':id') }}`.replace(':id', id)
                : `{{ route('admin.laporan.manual.store') }}`;
            
            if(id) formData.append('_method', 'PUT');

            // Disable button
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
                if(data.success) {
                    modal.hide();
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1500, showConfirmButton: false }).then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Terjadi kesalahan validasi', 'error');
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

        // Reset form on modal close
        modalEl.addEventListener('hidden.bs.modal', function () {
            manualForm.reset();
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

        // Month filter logic
        document.getElementById('pendapatan-month')?.addEventListener('change', function() {
            window.location.href = `{{ route('admin.laporan.pendapatan') }}?month=${this.value}&year={{ $year }}`;
        });
    });

    // Global Functions
    window.editManualTransaction = function(btn) {
        const id = btn.getAttribute('data-id');
        document.getElementById('transactionId').value = id;
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
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    };

    window.deleteManualTransaction = function(id) {
         Swal.fire({
            title: 'Hapus Laporan?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
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
                    if(data.success) {
                        Swal.fire('Terhapus!', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                });
            }
        });
    }

    window.viewProof = function(url, title) {
        document.getElementById('previewImage').src = url;
        new bootstrap.Modal(document.getElementById('imagePreviewModal')).show();
    }
</script>
@endsection
