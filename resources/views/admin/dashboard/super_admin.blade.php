@extends('admin.layouts.admin')

@section('title', 'Dashboard Super Admin')

@section('content')
@php
    $totalPesanan = collect($unit)->sum('total');
    $totalSelesai = collect($unit)->sum('selesai');
    $totalMenunggu = collect($unit)->sum('menunggu');
    $totalGagal = collect($unit)->sum('gagal');
    $rateGlobal = $totalPesanan > 0 ? round(($totalSelesai / $totalPesanan) * 100, 1) : 0;

    $totalIntegrasi = count($integrasi);
    $integrasiAktif = collect($integrasi)->where('aktif', true)->count();

    $unitMeta = [
        'Penjualan Gas'  => ['icon' => 'bxs-gas-pump', 'color' => 'danger'],
        'Sewa Alat'      => ['icon' => 'bx-wrench', 'color' => 'warning'],
        'Sewa Mobil'     => ['icon' => 'bx-car', 'color' => 'info'],
        'Fasilitas Umum' => ['icon' => 'bx-building-house', 'color' => 'success'],
    ];

    $roleLabels = [
        'super_admin' => ['label' => 'Super Admin', 'color' => 'primary'],
        'admin_desa'  => ['label' => 'Admin Desa', 'color' => 'info'],
        'admin_rw'    => ['label' => 'Admin RW', 'color' => 'warning'],
        'admin_rt'    => ['label' => 'Admin RT', 'color' => 'secondary'],
        'staff'       => ['label' => 'Staf Layanan', 'color' => 'danger'],
        'user'        => ['label' => 'Warga', 'color' => 'success'],
    ];
@endphp

<style>
    .sa-kpi-card {
        border: 1px solid #eef1f6;
        border-radius: 16px;
        background: #ffffff;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }
    .sa-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.07) !important;
        border-color: #dbeafe;
    }
    .sa-card {
        border: 1px solid #eef1f6;
        border-radius: 16px;
        background: #ffffff;
    }
    .sa-shortcut-tile {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 14px;
        border-radius: 14px;
        border: 1px solid #eef1f6;
        background: #ffffff;
        text-decoration: none;
        color: #1e293b;
        transition: all 0.18s ease;
        height: 100%;
    }
    .sa-shortcut-tile:hover {
        border-color: #bfdbfe;
        background: #f8fbff;
        color: #0d6efd;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(13, 110, 253, 0.08);
    }
    .sa-activity-row {
        border-bottom: 1px solid #f1f5f9;
        padding: 11px 0;
    }
    .sa-activity-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .sa-activity-row:first-child {
        padding-top: 0;
    }
    @media (max-width: 575.98px) {
        .sa-kpi-card .card-body {
            padding: 0.85rem !important;
        }
        .sa-kpi-card h4 {
            font-size: 1.25rem !important;
        }
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="min-w-0">
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">Sistem Platform /</span> Dashboard
            </h4>
            <p class="text-muted mb-0 small">
                Ringkasan menyeluruh platform SegiDaerah — cakupan wilayah Kecamatan Bengkalis, aktivitas layanan desa, dan integrasi sistem.
            </p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-md-auto">
            <span class="badge bg-label-primary rounded-pill px-3 py-2 fw-semibold">
                <i class="bx bx-shield-quarter me-1"></i>{{ auth()->user()->name }}
            </span>
            @if(auth()->user()->hasPlatformPermission('platform_monitoring'))
                <a href="{{ route('admin.sistem-platform.monitoring') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
                    <i class="bx bx-line-chart"></i>
                    <span>Monitoring Layanan</span>
                </a>
            @endif
        </div>
    </div>

    {{-- 4 KPI Utama --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <a href="{{ auth()->user()->role === 'super_admin' ? route('admin.sistem-platform.wilayah.index') : '#' }}" class="text-decoration-none d-block h-100">
                <div class="card sa-kpi-card shadow-sm h-100">
                    <div class="card-body p-3 p-md-4 d-flex align-items-start justify-content-between gap-2">
                        <div class="min-w-0">
                            <span class="text-muted small fw-semibold d-block mb-1">Wilayah Bengkalis</span>
                            <h4 class="fw-bold text-dark mb-1">{{ number_format($wilayah['total'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-muted d-block" style="font-size: 0.74rem; line-height: 1.3;">
                                {{ $wilayah['desa'] ?? 0 }} desa/kelurahan &bull; {{ $desa_layanan_aktif ?? 0 }} aktif
                            </small>
                        </div>
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-primary flex-shrink-0" style="width: 42px; height: 42px;">
                            <i class="bx bx-map-alt fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-xl-3">
            <a href="{{ auth()->user()->role === 'super_admin' ? route('admin.kelola-wilayah.index') : '#' }}" class="text-decoration-none d-block h-100">
                <div class="card sa-kpi-card shadow-sm h-100">
                    <div class="card-body p-3 p-md-4 d-flex align-items-start justify-content-between gap-2">
                        <div class="min-w-0">
                            <span class="text-muted small fw-semibold d-block mb-1">Total Pengguna</span>
                            <h4 class="fw-bold text-dark mb-1">{{ number_format($pengguna['total'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-success fw-medium d-block" style="font-size: 0.74rem; line-height: 1.3;">
                                +{{ $pengguna['baru_7hari'] ?? 0 }} baru (7 hari)
                            </small>
                        </div>
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-success flex-shrink-0" style="width: 42px; height: 42px;">
                            <i class="bx bx-group fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-xl-3">
            <a href="{{ auth()->user()->hasPlatformPermission('platform_monitoring') ? route('admin.sistem-platform.monitoring') : '#' }}" class="text-decoration-none d-block h-100">
                <div class="card sa-kpi-card shadow-sm h-100">
                    <div class="card-body p-3 p-md-4 d-flex align-items-start justify-content-between gap-2">
                        <div class="min-w-0">
                            <span class="text-muted small fw-semibold d-block mb-1">Pesanan Layanan</span>
                            <h4 class="fw-bold text-dark mb-1">{{ number_format($totalPesanan, 0, ',', '.') }}</h4>
                            <small class="text-muted d-block" style="font-size: 0.74rem; line-height: 1.3;">
                                {{ $totalSelesai }} selesai &bull; {{ $totalMenunggu }} menunggu
                            </small>
                        </div>
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-info flex-shrink-0" style="width: 42px; height: 42px;">
                            <i class="bx bx-shopping-bag fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-xl-3">
            <a href="{{ auth()->user()->hasPlatformPermission('platform_integrasi') ? route('admin.sistem-platform.gateway') : '#' }}" class="text-decoration-none d-block h-100">
                <div class="card sa-kpi-card shadow-sm h-100">
                    <div class="card-body p-3 p-md-4 d-flex align-items-start justify-content-between gap-2">
                        <div class="min-w-0">
                            <span class="text-muted small fw-semibold d-block mb-1">Integrasi API</span>
                            <h4 class="fw-bold text-dark mb-1">
                                {{ $integrasiAktif }}<span class="text-muted fw-normal fs-6">/{{ $totalIntegrasi }}</span>
                            </h4>
                            <small class="text-muted d-block" style="font-size: 0.74rem; line-height: 1.3;">
                                Dikelola aktif dari panel
                            </small>
                        </div>
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-warning flex-shrink-0" style="width: 42px; height: 42px;">
                            <i class="bx bx-plug fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Row 2: Ringkasan Unit Layanan & Status Integrasi --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-7">
            <div class="card sa-card shadow-sm h-100 overflow-hidden">
                <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-primary" style="width: 36px; height: 36px;">
                            <i class="bx bx-bar-chart-alt-2 fs-5 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Ringkasan Unit Layanan Usaha</h6>
                            <small class="text-muted">Agregat lintas desa di Kecamatan Bengkalis</small>
                        </div>
                    </div>
                    @if(auth()->user()->hasPlatformPermission('platform_monitoring'))
                        <a href="{{ route('admin.sistem-platform.monitoring') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold flex-shrink-0">
                            Detail <i class="bx bx-right-arrow-alt ms-0.5"></i>
                        </a>
                    @endif
                </div>

                {{-- Desktop Table --}}
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted text-uppercase" style="font-size: 0.7rem;">
                                <th class="ps-4 py-2.5">Unit Layanan</th>
                                <th class="text-end py-2.5">Total</th>
                                <th class="text-end py-2.5">Selesai</th>
                                <th class="text-end py-2.5">Menunggu</th>
                                <th class="text-end py-2.5">Batal</th>
                                <th class="pe-4 text-end py-2.5">Sukses</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unit as $u)
                                @php
                                    $meta = $unitMeta[$u['label']] ?? ['icon' => 'bx-cube', 'color' => 'primary'];
                                    $rate = $u['total'] > 0 ? round(($u['selesai'] / $u['total']) * 100) : 0;
                                    $rateColor = $u['total'] === 0 ? 'secondary' : ($rate >= 75 ? 'success' : ($rate >= 45 ? 'warning' : 'danger'));
                                @endphp
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-{{ $meta['color'] }}" style="width: 32px; height: 32px;">
                                                <i class="bx {{ $meta['icon'] }} text-{{ $meta['color'] }}"></i>
                                            </div>
                                            <span class="fw-semibold text-dark small">{{ $u['label'] }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold text-dark small">{{ number_format($u['total'], 0, ',', '.') }}</td>
                                    <td class="text-end text-success fw-semibold small">{{ number_format($u['selesai'], 0, ',', '.') }}</td>
                                    <td class="text-end text-warning fw-semibold small">{{ number_format($u['menunggu'], 0, ',', '.') }}</td>
                                    <td class="text-end text-danger small">{{ number_format($u['gagal'], 0, ',', '.') }}</td>
                                    <td class="pe-4 text-end">
                                        <span class="badge bg-label-{{ $rateColor }} rounded-pill px-2.5">{{ $rate }}%</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Compact Cards --}}
                <div class="d-md-none p-3 d-flex flex-column gap-2.5">
                    @foreach($unit as $u)
                        @php
                            $meta = $unitMeta[$u['label']] ?? ['icon' => 'bx-cube', 'color' => 'primary'];
                            $rate = $u['total'] > 0 ? round(($u['selesai'] / $u['total']) * 100) : 0;
                            $rateColor = $u['total'] === 0 ? 'secondary' : ($rate >= 75 ? 'success' : ($rate >= 45 ? 'warning' : 'danger'));
                        @endphp
                        <div class="p-3 rounded-3 border bg-light bg-opacity-50">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-label-{{ $meta['color'] }}" style="width: 28px; height: 28px;">
                                        <i class="bx {{ $meta['icon'] }} text-{{ $meta['color'] }}"></i>
                                    </span>
                                    <span class="fw-bold text-dark small">{{ $u['label'] }}</span>
                                </div>
                                <span class="badge bg-label-{{ $rateColor }} rounded-pill">{{ $rate }}% sukses</span>
                            </div>
                            <div class="row g-2 text-center small">
                                <div class="col-3">
                                    <small class="text-muted d-block" style="font-size: 0.68rem;">Total</small>
                                    <strong class="text-dark">{{ $u['total'] }}</strong>
                                </div>
                                <div class="col-3">
                                    <small class="text-muted d-block" style="font-size: 0.68rem;">Selesai</small>
                                    <strong class="text-success">{{ $u['selesai'] }}</strong>
                                </div>
                                <div class="col-3">
                                    <small class="text-muted d-block" style="font-size: 0.68rem;">Tunggu</small>
                                    <strong class="text-warning">{{ $u['menunggu'] }}</strong>
                                </div>
                                <div class="col-3">
                                    <small class="text-muted d-block" style="font-size: 0.68rem;">Batal</small>
                                    <strong class="text-danger">{{ $u['gagal'] }}</strong>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="card-footer bg-light bg-opacity-50 border-top px-4 py-2.5 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">Tingkat penyelesaian global</small>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress" style="width: 110px; height: 6px;">
                            <div class="progress-bar bg-primary rounded-pill" style="width: {{ $rateGlobal }}%"></div>
                        </div>
                        <span class="fw-bold text-primary small">{{ $rateGlobal }}%</span>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->hasPlatformPermission('platform_integrasi'))
        <div class="col-12 col-lg-5">
            <div class="card sa-card shadow-sm h-100 d-flex flex-column">
                <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-info" style="width: 36px; height: 36px;">
                            <i class="bx bx-plug fs-5 text-info"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Integrasi Pihak Ketiga</h6>
                            <small class="text-muted">Layanan API aktif &amp; fallback <code>.env</code></small>
                        </div>
                    </div>
                </div>
                <div class="card-body px-4 py-3 flex-grow-1">
                    @foreach($integrasi as $i)
                        <div class="d-flex align-items-center justify-content-between gap-2 py-2.5 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex align-items-center gap-2.5 min-w-0">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-light border flex-shrink-0" style="width: 32px; height: 32px;">
                                    <i class="bx {{ $i['ikon'] }} text-primary"></i>
                                </span>
                                <span class="small fw-semibold text-dark text-truncate">{{ $i['label'] }}</span>
                            </div>
                            @if($i['aktif'])
                                <span class="badge bg-label-success rounded-pill flex-shrink-0" style="font-size: 0.7rem;">
                                    <i class="bx bx-check me-0.5"></i>Aktif
                                </span>
                            @else
                                <span class="badge bg-label-secondary rounded-pill flex-shrink-0" style="font-size: 0.7rem;">
                                    Default .env
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="card-footer bg-white border-top px-4 py-3">
                    <a href="{{ route('admin.sistem-platform.gateway') }}" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-semibold">
                        <i class="bx bx-cog me-1"></i>Kelola Integrasi &amp; API Key
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Row 3: Aktivitas Terbaru & Komposisi Pengguna --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-7">
            <div class="card sa-card shadow-sm h-100">
                <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-secondary" style="width: 36px; height: 36px;">
                            <i class="bx bx-history fs-5 text-secondary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Aktivitas Terbaru Platform</h6>
                            <small class="text-muted">8 catatan terakhir dari log aktivitas sistem</small>
                        </div>
                    </div>
                    @if(auth()->user()->hasPlatformPermission('platform_keamanan'))
                        <a href="{{ route('admin.sistem-platform.security-log') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold flex-shrink-0">
                            Log Keamanan
                        </a>
                    @endif
                </div>
                <div class="card-body px-4 py-3">
                    @forelse($aktivitas as $log)
                        <div class="sa-activity-row d-flex align-items-start gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-label-primary flex-shrink-0 mt-0.5" style="width: 34px; height: 34px;">
                                <i class="bx bx-user text-primary"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center flex-wrap gap-1.5 mb-0.5">
                                    <span class="fw-semibold text-dark small">{{ $log['nama'] ?? 'Sistem' }}</span>
                                    <span class="badge bg-label-primary rounded-pill" style="font-size: 0.66rem;">{{ $log['action'] }}</span>
                                </div>
                                <p class="text-muted mb-0" style="font-size: 0.78rem; line-height: 1.4; word-break: break-word;">
                                    {{ $log['description'] }}
                                </p>
                            </div>
                            <small class="text-muted flex-shrink-0" style="font-size: 0.72rem;">
                                {{ $log['created_at'] ? \Carbon\Carbon::parse($log['created_at'])->diffForHumans(short: true) : '' }}
                            </small>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bx bx-history fs-1 text-muted opacity-50 d-block mb-2"></i>
                            <p class="text-muted small mb-0">Belum ada aktivitas tercatat.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card sa-card shadow-sm h-100 d-flex flex-column">
                <div class="card-header bg-white border-bottom px-4 py-3 d-flex align-items-center gap-2.5">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-success" style="width: 36px; height: 36px;">
                        <i class="bx bx-group fs-5 text-success"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Komposisi Pengguna</h6>
                        @if(($pengguna['belum_aktif'] ?? 0) > 0)
                            <small class="text-warning fw-medium">{{ $pengguna['belum_aktif'] }} akun belum aktif</small>
                        @else
                            <small class="text-muted">Seluruh akun terdaftar berstatus aktif</small>
                        @endif
                    </div>
                </div>
                <div class="card-body px-4 py-3 flex-grow-1">
                    @forelse($pengguna['per_role'] as $role => $n)
                        @php
                            $persen = ($pengguna['total'] ?? 0) > 0 ? round(($n / $pengguna['total']) * 100) : 0;
                            $rInfo = $roleLabels[$role] ?? ['label' => ucfirst(str_replace('_', ' ', $role)), 'color' => 'primary'];
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="fw-semibold text-dark">{{ $rInfo['label'] }}</span>
                                <span class="text-muted fw-semibold">{{ number_format($n, 0, ',', '.') }} <span class="fw-normal">({{ $persen }}%)</span></span>
                            </div>
                            <div class="progress rounded-pill" style="height: 7px;">
                                <div class="progress-bar bg-{{ $rInfo['color'] }} rounded-pill" style="width: {{ $persen }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0 text-center py-4">Belum ada pengguna terdaftar.</p>
                    @endforelse
                </div>
                @if(auth()->user()->role === 'super_admin')
                <div class="card-footer bg-white border-top px-4 py-3">
                    <a href="{{ route('admin.kelola-wilayah.index') }}" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-semibold">
                        <i class="bx bx-user-check me-1"></i>Kelola Wilayah &amp; Admin
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Row 4: Pintasan Cepat Modul --}}
    <div class="card sa-card shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bx bx-grid-alt text-primary fs-5"></i>
                <h6 class="fw-bold text-dark mb-0">Pintasan Modul Platform</h6>
            </div>
            @php
                $pintasan = [
                    ['Peta Wilayah', 'Struktur Kecamatan hingga RT', 'admin.sistem-platform.wilayah.index', 'bx-sitemap', 'primary', null, true],
                    ['Admin Wilayah', 'Kelola pengelola desa & RW/RT', 'admin.kelola-wilayah.index', 'bx-user-check', 'success', null, true],
                    ['Monitoring Layanan', 'Performa unit lintas desa', 'admin.sistem-platform.monitoring', 'bx-line-chart', 'info', 'platform_monitoring', false],
                    ['Integrasi & API Key', 'Google, Maps, WA OTP, Gemini', 'admin.sistem-platform.gateway', 'bx-plug', 'warning', 'platform_integrasi', false],
                    ['Log Keamanan', 'Audit staf & runtime server', 'admin.sistem-platform.security-log', 'bx-shield-quarter', 'danger', 'platform_keamanan', false],
                    ['Banner Beranda', 'Spanduk informasi aplikasi', 'admin.banners.index', 'bx-image', 'primary', 'platform_banner', false],
                ];
            @endphp
            <div class="row g-2.5">
                @foreach($pintasan as [$label, $desc, $rute, $ikon, $warna, $izin, $hanyaSuper])
                    @php
                        $boleh = $hanyaSuper
                            ? auth()->user()->role === 'super_admin'
                            : (! $izin || auth()->user()->hasPlatformPermission($izin));
                    @endphp
                    @if($boleh && Route::has($rute))
                        <div class="col-12 col-sm-6 col-lg-4">
                            <a href="{{ route($rute) }}" class="sa-shortcut-tile">
                                <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-{{ $warna }} flex-shrink-0" style="width: 40px; height: 40px;">
                                    <i class="bx {{ $ikon }} fs-5 text-{{ $warna }}"></i>
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="fw-bold small text-truncate">{{ $label }}</div>
                                    <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">{{ $desc }}</small>
                                </div>
                                <i class="bx bx-chevron-right text-muted flex-shrink-0"></i>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
