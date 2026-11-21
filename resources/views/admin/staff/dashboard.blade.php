@extends('admin.layouts.admin')

@section('title', 'Tugas Layanan Saya')

@section('content')
@php
    $staffUser = auth()->user();
    $unitConfigs = [
        'gas' => [
            'route' => 'admin.unit.penjualan_gas.index',
            'tab'   => 'gas',
            'icon'  => 'bxs-gas-pump',
            'color' => 'danger',
            'desc'  => 'Kelola stok tabung gas, harga jual, pesanan warga, dan balas chat pembeli.',
        ],
        'sewa_alat' => [
            'route' => 'admin.unit.penyewaan.index',
            'tab'   => 'rental',
            'icon'  => 'bx-wrench',
            'color' => 'warning',
            'desc'  => 'Kelola katalog alat sewa, ketersediaan unit, pengantaran, dan pengembalian.',
        ],
        'sewa_mobil' => [
            'route' => 'admin.unit.mobil.index',
            'tab'   => 'mobil',
            'icon'  => 'bx-car',
            'color' => 'info',
            'desc'  => 'Kelola armada kendaraan, jadwal supir, persetujuan booking, dan chat warga.',
        ],
        'fasilitas_umum' => [
            'route' => 'admin.unit.fasilitas_umum.index',
            'tab'   => 'fasilitas',
            'icon'  => 'bx-building-house',
            'color' => 'success',
            'desc'  => 'Kelola daftar gedung/fasilitas desa, jadwal pemakaian, pengurus, dan booking.',
        ],
    ];

    $assignedUnits = collect(\App\Support\StaffAccess::UNITS)
        ->filter(fn ($label, $key) => $staffUser && $staffUser->hasUnitPermission($key));

    $unitStats = $staffUnitStats ?? [];
    $totalPendingStaff = (int) ($staffTotals['pending'] ?? 0);
    $totalOrdersStaff = (int) ($staffTotals['total'] ?? 0);
    $canManageSupir = $staffUser && ($staffUser->hasUnitPermission('sewa_mobil') || $staffUser->hasUnitPermission('fasilitas_umum'));
@endphp

<style>
    .st-kpi-card {
        border: 1px solid #eef1f6;
        border-radius: 16px;
        background: #ffffff;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .st-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06) !important;
    }
    .st-unit-card {
        border: 1px solid #eef1f6;
        border-radius: 16px;
        background: #ffffff;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }
    .st-unit-card:hover {
        transform: translateY(-2px);
        border-color: #dbeafe;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.07) !important;
    }
    .st-shortcut-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px solid #eef1f6;
        background: #ffffff;
        text-decoration: none;
        color: #1e293b;
        transition: all 0.18s ease;
        height: 100%;
    }
    .st-shortcut-card:hover {
        border-color: #bfdbfe;
        background: #f8fbff;
        color: #0d6efd;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(13, 110, 253, 0.08);
    }
    @media (max-width: 575.98px) {
        .st-kpi-card .card-body {
            padding: 0.7rem 0.45rem !important;
        }
        .st-kpi-card .st-kpi-label {
            font-size: 0.66rem !important;
            line-height: 1.2;
        }
        .st-kpi-card h4 {
            font-size: 1.15rem !important;
        }
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div class="min-w-0">
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">Panel Staf /</span> Tugas Layanan Saya
            </h4>
            <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                @if($staffUser?->region)
                    <span class="d-inline-flex align-items-center fw-semibold text-dark">
                        <i class="bx bx-map-pin text-primary me-1"></i>{{ $staffUser->region->name }}
                    </span>
                    <span>&middot;</span>
                @endif
                <span>Kelola operasional unit layanan yang ditugaskan kepada Anda.</span>
            </div>
        </div>
        <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="btn btn-primary rounded-pill px-4 shadow-sm w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1.5 flex-shrink-0">
            <i class="bx bx-calendar-check fs-5"></i>
            <span>Pesanan Masuk</span>
            @if($totalPendingStaff > 0)
                <span class="badge bg-white text-primary rounded-pill ms-1">{{ $totalPendingStaff }}</span>
            @endif
        </a>
    </div>

    {{-- Welcome Banner + 3 KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card bg-label-primary border-0 shadow-none rounded-4 h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                        <i class="bx bx-briefcase-alt-2 fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Halo, {{ $staffUser->name ?? 'Staf Layanan' }}!</h6>
                        <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.55;">
                            Anda memiliki akses khusus sebagai <strong>Staf Layanan</strong> untuk mengelola katalog produk, memproses pesanan warga, memverifikasi bukti pembayaran, serta membalas chat warga pada unit yang ditugaskan.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="row g-2 h-100">
                <div class="col-4">
                    <div class="card st-kpi-card shadow-sm h-100">
                        <div class="card-body p-3 d-flex flex-column justify-content-center text-center">
                            <span class="text-muted small fw-semibold st-kpi-label mb-1">Unit Tugas</span>
                            <h4 class="fw-bold text-primary mb-0">{{ $assignedUnits->count() }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card st-kpi-card shadow-sm h-100">
                        <div class="card-body p-3 d-flex flex-column justify-content-center text-center">
                            <span class="text-muted small fw-semibold st-kpi-label mb-1">Perlu Diproses</span>
                            <h4 class="fw-bold {{ $totalPendingStaff > 0 ? 'text-warning' : 'text-success' }} mb-0">{{ number_format($totalPendingStaff, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card st-kpi-card shadow-sm h-100">
                        <div class="card-body p-3 d-flex flex-column justify-content-center text-center">
                            <span class="text-muted small fw-semibold st-kpi-label mb-1">Total Pesanan</span>
                            <h4 class="fw-bold text-dark mb-0">{{ number_format($totalOrdersStaff, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Assigned Service Units --}}
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-grid-alt text-primary fs-5"></i>
                <h6 class="fw-bold text-dark mb-0">Unit Layanan yang Anda Kelola</h6>
                <span class="badge bg-label-primary rounded-pill">{{ $assignedUnits->count() }}</span>
            </div>
        </div>

        @if($assignedUnits->isEmpty())
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5 px-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-3" style="width: 64px; height: 64px;">
                        <i class="bx bx-lock-alt fs-1 text-muted"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Belum Ada Unit yang Ditugaskan</h6>
                    <p class="text-muted small mb-0">Silakan hubungi Admin Desa Anda untuk mendapatkan penugasan unit layanan.</p>
                </div>
            </div>
        @else
            <div class="row g-3">
                @foreach($assignedUnits as $key => $label)
                    @php
                        $meta = $unitConfigs[$key];
                        $uStat = $unitStats[$key] ?? ['catalog' => null, 'pending' => 0, 'total' => 0];
                    @endphp
                    <div class="col-12 col-md-6">
                        <div class="card st-unit-card shadow-sm h-100">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-{{ $meta['color'] }} flex-shrink-0" style="width: 48px; height: 48px;">
                                            <i class="bx {{ $meta['icon'] }} fs-3 text-{{ $meta['color'] }}"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0.5">{{ $label }}</h6>
                                            <span class="badge bg-label-success rounded-pill" style="font-size: 0.68rem;">
                                                <i class="bx bx-check-circle me-0.5"></i>Akses Aktif
                                            </span>
                                        </div>
                                    </div>
                                    @if(($uStat['pending'] ?? 0) > 0)
                                        <span class="badge bg-label-warning rounded-pill px-2.5 py-1 fw-semibold flex-shrink-0" style="font-size: 0.72rem;">
                                            {{ $uStat['pending'] }} menunggu
                                        </span>
                                    @endif
                                </div>

                                <p class="text-muted small mb-3 flex-grow-1" style="line-height: 1.55;">
                                    {{ $meta['desc'] }}
                                </p>

                                @if(isset($uStat['catalog']))
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <span class="badge bg-light text-secondary border fw-medium" style="font-size: 0.72rem;">
                                            <i class="bx bx-package me-1 text-primary"></i>{{ number_format($uStat['catalog'], 0, ',', '.') }} item katalog
                                        </span>
                                        <span class="badge bg-light text-secondary border fw-medium" style="font-size: 0.72rem;">
                                            <i class="bx bx-receipt me-1 text-success"></i>{{ number_format($uStat['total'] ?? 0, 0, ',', '.') }} pesanan tercatat
                                        </span>
                                    </div>
                                @endif

                                <div class="d-flex flex-column flex-sm-row gap-2 pt-2 border-top">
                                    <a href="{{ route($meta['route']) }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold flex-fill d-inline-flex align-items-center justify-content-center gap-1">
                                        <i class="bx bx-folder-open"></i>
                                        <span>Buka {{ $label }}</span>
                                    </a>
                                    <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index', ['tab' => $meta['tab']]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold flex-fill d-inline-flex align-items-center justify-content-center gap-1">
                                        <i class="bx bx-calendar-check"></i>
                                        <span>Pesanan Unit</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Operational Quick Shortcuts --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bx bx-bolt-circle text-primary fs-5"></i>
                <h6 class="fw-bold text-dark mb-0">Akses Cepat Operasional</h6>
            </div>

            <div class="row g-2.5">
                <div class="col-12 col-sm-6 {{ $canManageSupir ? 'col-lg-3' : 'col-lg-4' }}">
                    <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="st-shortcut-card">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-warning flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="bx bx-calendar-check fs-5 text-warning"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-bold small text-truncate">Pesanan Layanan Saya</div>
                            <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">Setujui &amp; proses pesanan</small>
                        </div>
                        <i class="bx bx-chevron-right text-muted flex-shrink-0"></i>
                    </a>
                </div>

                <div class="col-12 col-sm-6 {{ $canManageSupir ? 'col-lg-3' : 'col-lg-4' }}">
                    <a href="{{ route('admin.aktivitas.bukti-transaksi.index') }}" class="st-shortcut-card">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-success flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="bx bx-check-shield fs-5 text-success"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-bold small text-truncate">Periksa Pembayaran</div>
                            <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">Verifikasi bukti transfer/tunai</small>
                        </div>
                        <i class="bx bx-chevron-right text-muted flex-shrink-0"></i>
                    </a>
                </div>

                @if($canManageSupir)
                    <div class="col-12 col-sm-6 col-lg-3">
                        <a href="{{ route('supir.index') }}" class="st-shortcut-card">
                            <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-info flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="bx bx-user-check fs-5 text-info"></i>
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <div class="fw-bold small text-truncate">Sopir &amp; Pengurus</div>
                                <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">Kelola petugas lapangan</small>
                            </div>
                            <i class="bx bx-chevron-right text-muted flex-shrink-0"></i>
                        </a>
                    </div>
                @endif

                <div class="col-12 col-sm-6 {{ $canManageSupir ? 'col-lg-3' : 'col-lg-4' }}">
                    <a href="{{ route('admin.profile') }}" class="st-shortcut-card">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-primary flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="bx bx-user-circle fs-5 text-primary"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-bold small text-truncate">Profil &amp; Keamanan</div>
                            <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">Foto, kontak, &amp; kata sandi</small>
                        </div>
                        <i class="bx bx-chevron-right text-muted flex-shrink-0"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
