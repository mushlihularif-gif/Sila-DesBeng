@extends('admin.layouts.admin')

@section('title', 'Monitoring Layanan')

@section('content')
@php
    $totals = $totals ?? [
        'total'    => collect($stats)->sum('total'),
        'selesai'  => collect($stats)->sum('selesai'),
        'menunggu' => collect($stats)->sum('menunggu'),
        'gagal'    => collect($stats)->sum('gagal'),
    ];
    $desaAktifLayanan = $desaAktifLayanan ?? ($walletHealth['jumlah_region_aktif'] ?? 0);
    $globalRate = $totals['total'] > 0 ? round(($totals['selesai'] / $totals['total']) * 100, 1) : 0;
@endphp

<style>
    .mon-kpi-card {
        border: 1px solid #eef1f6;
        border-radius: 16px;
        background: #ffffff;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .mon-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06) !important;
    }
    .mon-unit-card {
        border: 1px solid #eef1f6;
        border-radius: 16px;
        background: #ffffff;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }
    .mon-unit-card:hover {
        border-color: #dbeafe;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06) !important;
    }
    .mon-mini-box {
        background: #f8fafc;
        border: 1px solid #edf1f7;
        border-radius: 12px;
        padding: 0.65rem 0.5rem;
        text-align: center;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">Sistem Platform /</span> Monitoring Layanan
            </h4>
            <p class="text-muted mb-0 small">
                Pantau performa, antrean, dan tingkat penyelesaian transaksi seluruh unit layanan desa secara agregat.
            </p>
        </div>
        <button type="button" class="btn btn-outline-primary rounded-pill px-4 shadow-sm w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1.5" onclick="location.reload()">
            <i class="bx bx-refresh fs-5"></i>
            <span>Muat Ulang Data</span>
        </button>
    </div>

    {{-- Info Banner --}}
    <div class="card bg-label-primary border-0 shadow-none rounded-4 mb-4">
        <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 42px; height: 42px;">
                <i class="bx bx-line-chart fs-4"></i>
            </div>
            <div>
                <h6 class="fw-bold text-primary mb-1">Pemantauan Operasional Lintas Desa</h6>
                <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.55;">
                    Data di bawah adalah ringkasan agregat transaksi dari seluruh desa di Kecamatan Bengkalis (tanpa menampilkan nominal kas internal desa). Pembayaran layanan dikelola langsung oleh masing-masing Admin Desa secara tunai maupun transfer rekening desa.
                </p>
            </div>
        </div>
    </div>

    {{-- 4 KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card mon-kpi-card shadow-sm h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Total Transaksi</span>
                        <h4 class="fw-bold text-dark mb-1">{{ number_format($totals['total'], 0, ',', '.') }}</h4>
                        <small class="text-muted" style="font-size: 0.74rem;">Dari 4 unit layanan aktif</small>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-primary flex-shrink-0" style="width: 42px; height: 42px;">
                        <i class="bx bx-receipt fs-4 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card mon-kpi-card shadow-sm h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Transaksi Selesai</span>
                        <h4 class="fw-bold text-success mb-1">{{ number_format($totals['selesai'], 0, ',', '.') }}</h4>
                        <small class="text-success fw-semibold" style="font-size: 0.74rem;">{{ $globalRate }}% tingkat sukses</small>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-success flex-shrink-0" style="width: 42px; height: 42px;">
                        <i class="bx bx-check-circle fs-4 text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card mon-kpi-card shadow-sm h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Menunggu Diproses</span>
                        <h4 class="fw-bold text-warning mb-1">{{ number_format($totals['menunggu'], 0, ',', '.') }}</h4>
                        <small class="text-muted" style="font-size: 0.74rem;">Antrean aktif lintas desa</small>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-warning flex-shrink-0" style="width: 42px; height: 42px;">
                        <i class="bx bx-time-five fs-4 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card mon-kpi-card shadow-sm h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Desa Aktif Layanan</span>
                        <h4 class="fw-bold text-info mb-1">{{ number_format($desaAktifLayanan, 0, ',', '.') }}</h4>
                        <small class="text-muted" style="font-size: 0.74rem;">Wilayah dengan unit aktif</small>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-info flex-shrink-0" style="width: 42px; height: 42px;">
                        <i class="bx bx-map-pin fs-4 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4 Service Performance Cards --}}
    <div class="row g-3 mb-4">
        @foreach($stats as $label => $row)
            @php
                $total = (int) ($row['total'] ?? 0);
                $selesai = (int) ($row['selesai'] ?? 0);
                $menunggu = (int) ($row['menunggu'] ?? 0);
                $gagal = (int) ($row['gagal'] ?? 0);
                $icon = $row['icon'] ?? 'bx-cube';
                $color = $row['color'] ?? 'primary';

                $successRate = $total > 0 ? round(($selesai / $total) * 100, 1) : 0;
                $pendingRate = $total > 0 ? round(($menunggu / $total) * 100, 1) : 0;
                $failRate = $total > 0 ? round(($gagal / $total) * 100, 1) : 0;

                if ($total === 0) {
                    $healthBadge = ['secondary', 'Belum Ada Transaksi'];
                } elseif ($successRate >= 75) {
                    $healthBadge = ['success', 'Sangat Baik'];
                } elseif ($successRate >= 45) {
                    $healthBadge = ['warning', 'Cukup Baik'];
                } else {
                    $healthBadge = ['danger', 'Perlu Perhatian'];
                }
            @endphp
            <div class="col-12 col-md-6">
                <div class="card mon-unit-card shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-{{ $color }} flex-shrink-0" style="width: 44px; height: 44px;">
                                    <i class="bx {{ $icon }} fs-4 text-{{ $color }}"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">{{ $label }}</h6>
                                    <small class="text-muted">{{ number_format($total, 0, ',', '.') }} total pesanan</small>
                                </div>
                            </div>
                            <span class="badge bg-label-{{ $healthBadge[0] }} rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.72rem;">
                                {{ $healthBadge[1] }}
                            </span>
                        </div>

                        {{-- Segmented Progress Bar --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="text-muted">Tingkat Penyelesaian</span>
                                <span class="fw-bold text-dark">{{ $successRate }}%</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 8px; background: #edf1f7;">
                                @if($total > 0)
                                    <div class="progress-bar bg-success" style="width: {{ $successRate }}%" title="Selesai: {{ $selesai }}"></div>
                                    <div class="progress-bar bg-warning" style="width: {{ $pendingRate }}%" title="Menunggu: {{ $menunggu }}"></div>
                                    <div class="progress-bar bg-danger" style="width: {{ $failRate }}%" title="Batal/Ditolak: {{ $gagal }}"></div>
                                @endif
                            </div>
                        </div>

                        {{-- 3 Mini Counters --}}
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="mon-mini-box">
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Selesai</small>
                                    <span class="fw-bold text-success fs-6">{{ number_format($selesai, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mon-mini-box">
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Menunggu</small>
                                    <span class="fw-bold text-warning fs-6">{{ number_format($menunggu, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mon-mini-box">
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Batal / Tolak</small>
                                    <span class="fw-bold text-danger fs-6">{{ number_format($gagal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Comparative Table Card (Desktop) --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden d-none d-md-block">
        <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-table text-primary fs-5"></i>
                <h6 class="fw-bold text-dark mb-0">Tabel Perbandingan Unit Layanan</h6>
            </div>
            <span class="badge bg-label-primary rounded-pill">Tahun {{ date('Y') }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted text-uppercase" style="font-size: 0.72rem;">
                        <th class="ps-4 py-3">Unit Layanan</th>
                        <th class="text-center py-3">Total Pesanan</th>
                        <th class="text-center py-3">Selesai</th>
                        <th class="text-center py-3">Menunggu</th>
                        <th class="text-center py-3">Gagal / Batal</th>
                        <th class="pe-4 text-end py-3">Tingkat Sukses</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats as $label => $row)
                        @php
                            $successRate = $row['total'] > 0 ? round(($row['selesai'] / $row['total']) * 100, 1) : 0;
                            $badgeColor = $row['total'] === 0 ? 'secondary' : ($successRate >= 75 ? 'success' : ($successRate >= 45 ? 'warning' : 'danger'));
                        @endphp
                        <tr>
                            <td class="ps-4 py-3 fw-semibold text-dark">{{ $label }}</td>
                            <td class="text-center fw-bold text-dark">{{ number_format($row['total'], 0, ',', '.') }}</td>
                            <td class="text-center text-success fw-semibold">{{ number_format($row['selesai'], 0, ',', '.') }}</td>
                            <td class="text-center text-warning fw-semibold">{{ number_format($row['menunggu'], 0, ',', '.') }}</td>
                            <td class="text-center text-danger">{{ number_format($row['gagal'], 0, ',', '.') }}</td>
                            <td class="pe-4 text-end">
                                <span class="badge bg-label-{{ $badgeColor }} rounded-pill px-3">{{ $successRate }}%</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
