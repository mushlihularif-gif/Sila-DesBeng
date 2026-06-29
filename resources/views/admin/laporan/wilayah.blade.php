@extends('admin.layouts.admin')

@section('title', 'Laporan Wilayah')

@section('content')
<div class="container-fluid py-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold fs-3 mb-1 text-primary">
                <i class="bx bx-map-alt me-2"></i>Laporan Wilayah
            </h4>
            <p class="text-muted mb-0">Pemantauan tren kinerja layanan di seluruh wilayah</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary shadow-sm rounded-pill px-4" onclick="window.print()">
                <i class="bx bx-printer me-2"></i>Cetak
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <select id="filter-year" class="form-select">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                
                @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                <div class="col-md-4">
                    <select id="filter-kecamatan" class="form-select">
                        <option value="all">-- Semua Kecamatan --</option>
                        @foreach($kecamatanList as $kec)
                            <option value="{{ $kec->id }}" {{ $selectedKecamatanId == $kec->id ? 'selected' : '' }}>{{ $kec->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan']))
                <div class="col-md-4">
                    <select id="filter-desa" class="form-select">
                        <option value="all">-- Semua Desa --</option>
                        @foreach($desaList as $desa)
                            <option value="{{ $desa->id }}" {{ $selectedDesaId == $desa->id ? 'selected' : '' }}>{{ $desa->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <!-- Status Kinerja -->
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-md rounded-3 p-2 me-3" style="background: rgba(105, 108, 255, 0.12);">
                            <i class="bx bx-line-chart fs-3 text-primary"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Kinerja Bulan Ini</small>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1" id="growth-indicator">
                        @if($growth > 0)
                            <span class="text-success"><i class="bx bx-up-arrow-alt"></i> +{{ $growth }}%</span>
                        @elseif($growth < 0)
                            <span class="text-danger"><i class="bx bx-down-arrow-alt"></i> {{ $growth }}%</span>
                        @else
                            <span class="text-secondary"><i class="bx bx-minus"></i> 0%</span>
                        @endif
                    </h3>
                    <small class="text-muted">vs bulan sebelumnya</small>
                </div>
            </div>
        </div>

        <!-- Total Aktivitas Bulan Ini -->
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-md rounded-3 p-2 me-3" style="background: rgba(3, 195, 236, 0.12);">
                            <i class="bx bx-pulse fs-3 text-info"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Indeks Aktivitas</small>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-dark" id="current-month-total">{{ $currentMonthTotal }}</h3>
                    <small class="text-muted">interaksi layanan bulan ini</small>
                </div>
            </div>
        </div>

        <!-- Info Privasi -->
        <div class="col-lg-4 col-md-12">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #ff9800 0%, #f76a00 100%);">
                <!-- Decorative background elements -->
                <div class="position-absolute top-0 end-0 opacity-25 text-white" style="transform: translate(20%, -20%); pointer-events: none;">
                    <i class="bx bx-shield-quarter" style="font-size: 10rem;"></i>
                </div>
                <div class="card-body p-4 text-white d-flex flex-column justify-content-center position-relative z-1">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-white text-warning rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 38px; height: 38px;">
                            <i class="bx bx-shield-check fs-4"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-white" style="letter-spacing: 0.5px; font-size: 1.1rem;">Mode Privasi Aktif</h6>
                    </div>
                    <p class="mb-0 small" style="opacity: 0.95; line-height: 1.6;">Grafik ini hanya menampilkan tren aktivitas layanan. Tidak ada data nominal keuangan yang ditampilkan demi kerahasiaan daerah.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title fw-bold text-dark mb-1">Tren Kinerja Layanan</h5>
                            <small class="text-muted" id="chart-subtitle">Grafik aktivitas layanan sepanjang tahun {{ $year }}</small>
                        </div>
                        <div class="d-flex gap-2" id="chart-legend">
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                                <i class="bx bx-circle me-1" style="font-size: 8px;"></i> Penyewaan & Pemesanan
                            </span>
                            <span class="badge bg-info-subtle text-info rounded-pill px-3 py-2">
                                <i class="bx bx-circle me-1" style="font-size: 8px;"></i> Pelaporan Warga
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <div id="kinerjaWilayahChart" style="min-height: 380px;"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initial data
    let performanceData = {!! json_encode(array_values($monthlyPerformance)) !!};
    let months = {!! json_encode(array_keys($monthlyPerformance)) !!};
    
    // Chart Configuration
    const chartOptions = {
        series: [{ 
            name: 'Aktivitas Layanan', 
            data: performanceData 
        }],
        chart: { 
            type: 'area', 
            height: 380, 
            toolbar: { show: false },
            fontFamily: "'Inter', 'Helvetica', sans-serif",
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800,
                animateGradually: { enabled: true, delay: 150 },
                dynamicAnimation: { enabled: true, speed: 350 }
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
            size: 0,
            hover: { size: 6, sizeOffset: 3 },
            colors: ['#696cff'],
            strokeColors: '#fff',
            strokeWidth: 2
        },
        xaxis: { 
            categories: months,
            labels: { 
                style: { colors: '#a1acb8', fontSize: '12px', fontWeight: 500 } 
            },
            axisBorder: { show: false },
            axisTicks: { show: false },
            crosshairs: {
                stroke: { color: '#696cff', width: 1, dashArray: 3 }
            }
        },
        yaxis: {
            show: false
        },
        grid: { 
            borderColor: '#f0f2f5', 
            strokeDashArray: 5,
            padding: { left: 10, right: 10 },
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } }
        },
        tooltip: {
            theme: 'light',
            style: { fontSize: '13px', fontFamily: 'inherit' },
            y: {
                formatter: function(val) {
                    return val + ' Indeks Poin';
                }
            },
            marker: { show: true }
        }
    };

    const chartEl = document.querySelector('#kinerjaWilayahChart');
    let chart = null;
    if (chartEl) {
        chart = new ApexCharts(chartEl, chartOptions);
        chart.render();
    }

    // ==========================================
    // AJAX Filtering Logic
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
            // Update desa dropdown
            if (resetDesa && desaSelect && data.desaOptionsHtml) {
                desaSelect.innerHTML = data.desaOptionsHtml;
            }

            // Update chart
            if (chart && data.performanceData) {
                chart.updateSeries([{
                    name: 'Aktivitas Layanan',
                    data: data.performanceData
                }]);
            }

            // Update growth indicator
            var el = document.getElementById('growth-indicator');
            if (el) {
                var g = data.growth;
                if (g > 0) {
                    el.innerHTML = '<span class="text-success"><i class="bx bx-up-arrow-alt"></i> +' + g + '%</span>';
                } else if (g < 0) {
                    el.innerHTML = '<span class="text-danger"><i class="bx bx-down-arrow-alt"></i> ' + g + '%</span>';
                } else {
                    el.innerHTML = '<span class="text-secondary"><i class="bx bx-minus"></i> 0%</span>';
                }
            }

            // Update subtitle
            var subtitle = document.getElementById('chart-subtitle');
            if (subtitle && yearSelect) {
                subtitle.textContent = 'Grafik aktivitas layanan sepanjang tahun ' + yearSelect.value;
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

<style>
    /* ApexCharts Tooltip Fix */
    .apexcharts-tooltip {
        background: #ffffff !important;
        border: 1px solid #e0e0e0 !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        opacity: 1 !important;
    }
    .apexcharts-tooltip * {
        color: #333333 !important;
        font-family: inherit !important;
        opacity: 1 !important;
        visibility: visible !important;
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
</style>
@endsection
