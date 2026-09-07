@extends('admin.layouts.admin')

@section('title', 'Laporan Wilayah')

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
    .unit-stat-card {
        border-radius: 12px;
        transition: all 0.2s ease;
        background: #fff;
    }
    .unit-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
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
    .apexcharts-tooltip-text-y-label {
        font-weight: normal !important;
    }
    @media (max-width: 575.98px) {
        .stat-card .card-body {
            padding: 1rem !important;
        }
    }
</style>

@php
    $activeServices = $activeServices ?? [];
    $isRentalActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'alat'));
    $isGasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'gas'));
    $isMobilActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'mobil'));
    $isFasilitasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'fasilitas'));
    $isPasarActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'pasar'));
@endphp

<div class="container-xxl flex-grow-1 container-p-y animate-fade-up" style="max-width: 100%; overflow-x: hidden;">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Laporan /</span> Kinerja Wilayah
            </h4>
            <p class="text-muted small mb-0">Pemantauan tren aktivitas 5 sektor layanan daerah dan partisipasi warga di seluruh wilayah</p>
        </div>
        <div class="d-flex flex-wrap gap-2 w-100 w-sm-auto justify-content-start justify-content-sm-end">
            <button class="btn btn-primary shadow-sm rounded-pill px-4 flex-grow-1 flex-sm-grow-0" onclick="window.print()">
                <i class="bx bx-printer me-2"></i>Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Panduan Banner -->
    <div class="card bg-label-primary border-0 shadow-none mb-4" style="border-radius: 14px;">
        <div class="card-body d-flex align-items-center p-3 p-md-4">
            <div class="me-3 flex-shrink-0">
                <div class="bg-primary p-2 p-md-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                    <i class="bx bx-info-circle fs-4 fs-md-3"></i>
                </div>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-primary">Panduan Evaluasi Wilayah</h6>
                <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.5;">
                    Halaman ini merangkum indeks aktivitas dari <strong>5 sektor layanan daerah</strong> (Penyewaan Alat, Gas LPG, Mobil, Fasilitas Umum, Pasar Daerah) serta <strong>Pelaporan Warga</strong> dalam bentuk poin tren. Nilai nominal keuangan disamarkan demi menjaga privasi antar wilayah.
                </p>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <div id="filterForm" class="row g-3">
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label text-muted small fw-semibold mb-1">Pilih Tahun</label>
                    <div class="input-group input-group-merge rounded-3 shadow-none border">
                        <span class="input-group-text bg-light text-primary border-0"><i class="bx bx-calendar"></i></span>
                        <select id="filter-year" class="form-select bg-light border-0 ps-0">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                @if(auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin']))
                <div class="col-12 col-md-4 col-lg-4">
                    <label class="form-label text-muted small fw-semibold mb-1">Filter Kecamatan</label>
                    <div class="input-group input-group-merge rounded-3 shadow-none border">
                        <span class="input-group-text bg-light text-primary border-0"><i class="bx bx-map-pin"></i></span>
                        <select id="filter-kecamatan" class="form-select bg-light border-0 ps-0">
                            <option value="all">Semua Kecamatan</option>
                            @foreach($kecamatanList as $kec)
                                <option value="{{ $kec->id }}" {{ $selectedKecamatanId == $kec->id ? 'selected' : '' }}>{{ $kec->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
                
                @if(auth()->check() && in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan']))
                <div class="col-12 col-md-4 col-lg-5">
                    <label class="form-label text-muted small fw-semibold mb-1">Filter Desa / Kelurahan</label>
                    <div class="input-group input-group-merge rounded-3 shadow-none border">
                        <span class="input-group-text bg-light text-primary border-0"><i class="bx bx-buildings"></i></span>
                        <select id="filter-desa" class="form-select bg-light border-0 ps-0">
                            <option value="all">Semua Desa</option>
                            @foreach($desaList as $desa)
                                <option value="{{ $desa->id }}" {{ $selectedDesaId == $desa->id ? 'selected' : '' }}>{{ $desa->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Summary Overview Cards -->
    <div class="row g-3 g-md-4 mb-4">
        <!-- Status Kinerja -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card overflow-hidden">
                <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-md rounded-3 p-2 me-3 d-flex align-items-center justify-content-center shadow-xs" style="background: rgba(105, 108, 255, 0.12); width: 44px; height: 44px;">
                            <i class="bx bx-line-chart fs-3 text-primary"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1 d-block" style="font-size: 0.7rem;">Tren Kinerja</small>
                            <span class="fw-semibold text-dark small">Bulan Berjalan</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1" id="growth-indicator">
                            @if($growth > 0)
                                <span class="text-success"><i class="bx bx-up-arrow-alt"></i> +{{ $growth }}%</span>
                            @elseif($growth < 0)
                                <span class="text-danger"><i class="bx bx-down-arrow-alt"></i> {{ $growth }}%</span>
                            @else
                                <span class="text-secondary"><i class="bx bx-minus"></i> 0%</span>
                            @endif
                        </h3>
                        <small class="text-muted" style="font-size: 0.75rem;">Perubahan volume aktivitas vs bulan lalu</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Aktivitas Bulan Ini -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card overflow-hidden">
                <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-md rounded-3 p-2 me-3 d-flex align-items-center justify-content-center shadow-xs" style="background: rgba(3, 195, 236, 0.12); width: 44px; height: 44px;">
                            <i class="bx bx-pulse fs-3 text-info"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1 d-block" style="font-size: 0.7rem;">Indeks Aktivitas</small>
                            <span class="fw-semibold text-dark small">Bulan Ini</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1 text-dark" id="current-month-total">{{ $currentMonthTotal }}</h3>
                        <small class="text-muted" style="font-size: 0.75rem;">Total transaksi dan laporan tercatat bulan ini</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mode Privasi Wilayah -->
        <div class="col-12 col-md-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative text-white" style="background: linear-gradient(135deg, #ff9800 0%, #f76a00 100%);">
                <!-- Background decorative watermark -->
                <div class="position-absolute top-0 end-0 opacity-25" style="transform: translate(15%, -15%); pointer-events: none;">
                    <i class="bx bx-shield-quarter" style="font-size: 6rem; line-height: 1;"></i>
                </div>
                <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-center position-relative z-1">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm flex-shrink-0" style="width: 38px; height: 38px; color: #f76a00;">
                            <i class="bx bx-shield-check fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-white" style="letter-spacing: 0.3px;">Mode Privasi Terproteksi</h6>
                            <small class="text-white-50" style="font-size: 0.7rem;">Kerahasiaan Anggaran Desa</small>
                        </div>
                    </div>
                    <p class="mb-0 small text-white" style="opacity: 0.92; line-height: 1.5; font-size: 0.82rem;">
                        Data keuangan dikonversi menjadi indeks kuantitatif agar perbandingan wilayah tetap objektif tanpa mengekspos arus kas riil.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Aktivitas 5 Sektor Layanan Daerah & Pelaporan Warga -->
    <div class="row g-2 g-md-3 mb-4">
        <!-- Penyewaan Alat -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 unit-stat-card p-3">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar avatar-sm bg-label-warning rounded-3 p-1 me-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                        <img src="{{ asset('User/img/elemen/F1.png') }}" style="width: 18px; height: 18px; object-fit: contain;" alt="Sewa Alat">
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-secondary fw-semibold d-block text-truncate" style="font-size: 0.75rem;">Sewa Alat</span>
                    </div>
                </div>
                <h5 class="fw-bold mb-0 text-dark" id="count-rental">{{ $serviceTotals['rental'] ?? 0 }}</h5>
                <small class="text-muted" style="font-size: 0.72rem;">Aktivitas</small>
            </div>
        </div>

        <!-- Pangkalan Gas LPG -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 unit-stat-card p-3">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar avatar-sm bg-label-info rounded-3 p-1 me-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                        <img src="{{ asset('User/img/elemen/F2.png') }}" style="width: 18px; height: 18px; object-fit: contain;" alt="Gas LPG">
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-secondary fw-semibold d-block text-truncate" style="font-size: 0.75rem;">Gas LPG</span>
                    </div>
                </div>
                <h5 class="fw-bold mb-0 text-dark" id="count-gas">{{ $serviceTotals['gas'] ?? 0 }}</h5>
                <small class="text-muted" style="font-size: 0.72rem;">Pesanan Tabung</small>
            </div>
        </div>

        <!-- Rental Mobil -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 unit-stat-card p-3">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar avatar-sm bg-label-danger rounded-3 p-1 me-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                        <img src="{{ asset('User/img/elemen/mobil.png') }}" style="width: 18px; height: 18px; object-fit: contain;" alt="Sewa Mobil">
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-secondary fw-semibold d-block text-truncate" style="font-size: 0.75rem;">Sewa Mobil</span>
                    </div>
                </div>
                <h5 class="fw-bold mb-0 text-dark" id="count-mobil">{{ $serviceTotals['mobil'] ?? 0 }}</h5>
                <small class="text-muted" style="font-size: 0.72rem;">Peminjaman</small>
            </div>
        </div>

        <!-- Fasilitas Umum -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 unit-stat-card p-3">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar avatar-sm bg-label-success rounded-3 p-1 me-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                        <img src="{{ asset('User/img/elemen/fasilitas.png') }}" style="width: 18px; height: 18px; object-fit: contain;" alt="Fasilitas">
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-secondary fw-semibold d-block text-truncate" style="font-size: 0.75rem;">Fasilitas</span>
                    </div>
                </div>
                <h5 class="fw-bold mb-0 text-dark" id="count-fasilitas">{{ $serviceTotals['fasilitas'] ?? 0 }}</h5>
                <small class="text-muted" style="font-size: 0.72rem;">Sewa Tempat</small>
            </div>
        </div>

        <!-- Pasar Daerah -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 unit-stat-card p-3">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar avatar-sm bg-label-primary rounded-3 p-1 me-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                        <img src="{{ asset('Admin/img/pasardaerah/PasarDaerah2.png') }}" style="width: 18px; height: 18px; object-fit: contain;" alt="Pasar Daerah">
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-secondary fw-semibold d-block text-truncate" style="font-size: 0.75rem;">Pasar Daerah</span>
                    </div>
                </div>
                <h5 class="fw-bold mb-0 text-dark" id="count-pasar">{{ $serviceTotals['pasar'] ?? 0 }}</h5>
                <small class="text-muted" style="font-size: 0.72rem;">Transaksi Pasar</small>
            </div>
        </div>

        <!-- Pelaporan Warga -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 unit-stat-card p-3">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar avatar-sm bg-label-danger rounded-3 p-1 me-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                        <img src="{{ asset('User/img/elemen/lapor.png') }}" style="width: 18px; height: 18px; object-fit: contain;" alt="Pelaporan Warga">
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-secondary fw-semibold d-block text-truncate" style="font-size: 0.75rem;">Pelaporan Warga</span>
                    </div>
                </div>
                <h5 class="fw-bold mb-0 text-dark" id="count-laporan">{{ $serviceTotals['laporan'] ?? 0 }}</h5>
                <small class="text-muted" style="font-size: 0.72rem;">Laporan Masuk</small>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-3 px-md-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                        <div>
                            <h5 class="card-title fw-bold text-dark mb-1">Tren Kinerja Layanan</h5>
                            <small class="text-muted" id="chart-subtitle">Grafik akumulasi aktivitas 5 sektor layanan daerah & pelaporan warga tahun {{ $year }}</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-label-primary rounded-pill px-3 py-2 fw-medium d-inline-flex align-items-center" style="font-size: 0.8rem; text-transform: none !important; letter-spacing: normal;">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #696cff; display: inline-block; margin-right: 8px;"></span>
                                Total Indeks Aktivitas
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body px-2 px-md-4 pb-4 pt-1">
                    <div id="kinerjaWilayahChart" style="min-height: 340px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let performanceData = {!! json_encode(array_values($monthlyPerformance)) !!};
    let months = {!! json_encode(array_keys($monthlyPerformance)) !!};
    
    // ApexCharts Configuration with full mobile responsiveness
    const chartOptions = {
        series: [{ 
            name: 'Total Aktivitas Layanan', 
            data: performanceData 
        }],
        chart: { 
            type: 'area', 
            height: 340, 
            toolbar: { show: false },
            fontFamily: "'Public Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif",
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 700
            }
        },
        colors: ['#696cff'],
        fill: { 
            type: 'gradient', 
            gradient: { 
                shadeIntensity: 1, 
                opacityFrom: 0.45, 
                opacityTo: 0.05, 
                stops: [0, 85, 100],
                colorStops: [
                    { offset: 0, color: '#696cff', opacity: 0.4 },
                    { offset: 50, color: '#696cff', opacity: 0.15 },
                    { offset: 100, color: '#696cff', opacity: 0.02 }
                ]
            } 
        },
        dataLabels: { enabled: false },
        stroke: { 
            curve: 'smooth', 
            width: 3,
            lineCap: 'round'
        },
        markers: {
            size: 4,
            hover: { size: 6, sizeOffset: 3 },
            colors: ['#696cff'],
            strokeColors: '#fff',
            strokeWidth: 2
        },
        xaxis: { 
            categories: months,
            labels: { 
                style: { colors: '#a1acb8', fontSize: '11px', fontWeight: 500 } 
            },
            axisBorder: { show: false },
            axisTicks: { show: false },
            crosshairs: {
                stroke: { color: '#696cff', width: 1, dashArray: 3 }
            }
        },
        yaxis: {
            show: true,
            min: 0,
            forceNiceScale: true,
            labels: {
                style: { colors: '#a1acb8', fontSize: '11px' },
                formatter: function(val) {
                    return Math.round(val);
                }
            }
        },
        grid: { 
            borderColor: '#f0f2f5', 
            strokeDashArray: 4,
            padding: { left: 10, right: 10 },
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } }
        },
        tooltip: {
            theme: 'light',
            style: { fontSize: '12px', fontFamily: 'inherit' },
            y: {
                formatter: function(val) {
                    return val + ' Indeks Aktivitas';
                }
            },
            marker: { show: true }
        },
        responsive: [
            {
                breakpoint: 768,
                options: {
                    chart: { height: 280 },
                    xaxis: {
                        labels: {
                            rotate: -45,
                            style: { fontSize: '10px' }
                        }
                    }
                }
            },
            {
                breakpoint: 480,
                options: {
                    chart: { height: 250 },
                    markers: { size: 3 }
                }
            }
        ]
    };

    const chartEl = document.querySelector('#kinerjaWilayahChart');
    let chart = null;
    if (chartEl) {
        chart = new ApexCharts(chartEl, chartOptions);
        chart.render();
    }

    // ==========================================
    // Realtime AJAX Filtering Logic
    // ==========================================
    const yearSelect = document.getElementById('filter-year');
    const kecamatanSelect = document.getElementById('filter-kecamatan');
    const desaSelect = document.getElementById('filter-desa');
    
    function fetchFilteredData(resetDesa) {
        if (resetDesa && desaSelect) {
            desaSelect.value = 'all';
        }
        
        const params = new URLSearchParams();
        if (yearSelect) params.set('year', yearSelect.value);
        if (kecamatanSelect) params.set('kecamatan_id', kecamatanSelect.value);
        if (desaSelect) params.set('desa_id', desaSelect.value);

        const ajaxUrl = "{{ route('admin.laporan.wilayah') }}?" + params.toString();

        fetch(ajaxUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            // Update dropdown desa jika ada perubahan kecamatan
            if (resetDesa && desaSelect && data.desaOptionsHtml) {
                desaSelect.innerHTML = data.desaOptionsHtml;
            }

            // Update grafik chart ApexCharts
            if (chart && data.performanceData) {
                chart.updateSeries([{
                    name: 'Total Aktivitas Layanan',
                    data: data.performanceData
                }]);
            }

            // Update indikator pertumbuhan
            const growthEl = document.getElementById('growth-indicator');
            if (growthEl && typeof data.growth !== 'undefined') {
                const g = data.growth;
                if (g > 0) {
                    growthEl.innerHTML = '<span class="text-success"><i class="bx bx-up-arrow-alt"></i> +' + g + '%</span>';
                } else if (g < 0) {
                    growthEl.innerHTML = '<span class="text-danger"><i class="bx bx-down-arrow-alt"></i> ' + g + '%</span>';
                } else {
                    growthEl.innerHTML = '<span class="text-secondary"><i class="bx bx-minus"></i> 0%</span>';
                }
            }

            // Update total aktivitas bulan ini
            const totalEl = document.getElementById('current-month-total');
            if (totalEl && typeof data.currentMonthTotal !== 'undefined') {
                totalEl.textContent = data.currentMonthTotal;
            }

            // Update 5 unit layanan & pelaporan counts
            if (data.serviceTotals) {
                const countRental = document.getElementById('count-rental');
                const countGas = document.getElementById('count-gas');
                const countMobil = document.getElementById('count-mobil');
                const countFasilitas = document.getElementById('count-fasilitas');
                const countPasar = document.getElementById('count-pasar');
                const countLaporan = document.getElementById('count-laporan');

                if (countRental) countRental.textContent = data.serviceTotals.rental || 0;
                if (countGas) countGas.textContent = data.serviceTotals.gas || 0;
                if (countMobil) countMobil.textContent = data.serviceTotals.mobil || 0;
                if (countFasilitas) countFasilitas.textContent = data.serviceTotals.fasilitas || 0;
                if (countPasar) countPasar.textContent = data.serviceTotals.pasar || 0;
                if (countLaporan) countLaporan.textContent = data.serviceTotals.laporan || 0;
            }

            // Update subtitle tahun
            const subtitle = document.getElementById('chart-subtitle');
            if (subtitle && yearSelect) {
                subtitle.textContent = 'Grafik akumulasi aktivitas 5 sektor layanan daerah & pelaporan warga tahun ' + yearSelect.value;
            }
        })
        .catch(function(err) { 
            console.error('Filter error:', err); 
        });
    }

    if (yearSelect) {
        yearSelect.addEventListener('change', function() { fetchFilteredData(false); });
    }
    if (kecamatanSelect) {
        kecamatanSelect.addEventListener('change', function() { fetchFilteredData(true); });
    }
    if (desaSelect) {
        desaSelect.addEventListener('change', function() { fetchFilteredData(false); });
    }
});
</script>
@endsection
