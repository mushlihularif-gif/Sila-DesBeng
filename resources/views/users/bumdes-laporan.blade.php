@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-40 pb-16 bg-cover bg-center bg-no-repeat" 
             style="background-image: url('{{ asset('Admin/img/elements/background1.png') }}');">
        
        <!-- White Overlay -->
        <div class="absolute inset-0 bg-white/25 pointer-events-none"></div>

        <div class="max-w-6xl mx-auto px-6 relative z-20">
            
            <!-- Header Section -->
            <div class="text-center mb-16 animate-section">
                <h1 class="text-4xl md:text-5xl font-bold mb-4 bg-gradient-to-r from-[#1a1a1a] via-[#0099ff] to-[#33b5ff] bg-clip-text text-transparent">
                    Laporan BUMDes
                </h1>
                <p class="text-lg text-gray-700">
                    Laporan kinerja dan aktivitas BUMDes
                </p>
            </div>

            <!-- Desa Section Header -->
            <div class="mb-12 animate-section">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 text-center mb-2">
                    Desa Pematang Duku Timur
                </h2>
                <h3 class="text-xl md:text-2xl font-bold text-center bg-gradient-to-r from-[#1a1a1a] via-[#0099ff] to-[#33b5ff] bg-clip-text text-transparent">
                    Grafik Umum
                </h3>
            </div>

            <!-- Kinerja BUMDes Chart -->
            <div class="mb-16 animate-section">
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-8 border border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                        <h3 class="text-xl font-bold mb-3 md:mb-0">
                            <span class="text-gray-900">Kinerja </span>
                            <span class="bg-gradient-to-r from-[#1a1a1a] via-[#0099ff] to-[#33b5ff] bg-clip-text text-transparent">BUMDes</span>
                        </h3>
                        <select id="kinerja-year" class="px-4 py-2 text-sm border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            @foreach($availableYears as $optYear)
                                <option value="{{ $optYear }}" {{ $optYear == $year ? 'selected' : '' }}>{{ $optYear }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bg-white/50 backdrop-blur-sm rounded-2xl p-5 border border-gray-100">
                        <div id="kinerjaChart"></div>
                    </div>
                    <p class="text-sm text-gray-600 mt-4 leading-relaxed">
                        Grafik menunjukkan perkembangan tingkat aktivitas secara unit usaha BUMDes Pematang Duku Timur. Data diambil dari total pendapatan per bulan. Informasi ini membantu dalam memahami tren kinerja dan mengidentifikasi area yang perlu ditingkatkan.
                    </p>
                </div>
            </div>

            <!-- Unit Populer Chart -->
            <div class="mb-16 animate-section">
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-8 border border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                        <h3 class="text-xl font-bold mb-3 md:mb-0">
                            <span class="text-gray-900">Unit </span>
                            <span class="bg-gradient-to-r from-[#1a1a1a] via-[#0099ff] to-[#33b5ff] bg-clip-text text-transparent">Populer</span>
                        </h3>
                        <select id="unit-year" class="px-4 py-2 text-sm border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            @foreach($availableYears as $optYear)
                                <option value="{{ $optYear }}" {{ $optYear == $year ? 'selected' : '' }}>{{ $optYear }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bg-white/50 backdrop-blur-sm rounded-2xl p-5 mb-5 border border-gray-100">
                        <div id="unitChart"></div>
                    </div>
                    <!-- Legend -->
                    <div class="flex flex-wrap justify-center gap-6 text-sm mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-[#f59e0b] rounded"></div>
                            <span class="text-gray-700 font-medium">Unit Penyewaan Alat</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-[#3b82f6] rounded"></div>
                            <span class="text-gray-700 font-medium">Unit Penjualan Gas</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Grafik menunjukkan perbandingan tingkat aktivitas antara unit usaha BUMDes. Unit "penyewaan alat" dan "penjualan gas" menunjukkan kinerja yang baik. Informasi ini membantu dalam memahami unit terbaik sehingga dapat ditingkatkan dan dikembangkan lebih lanjut.
                    </p>
                </div>
            </div>

            <!-- Total Pendapatan Section -->
            <div class="mb-16 animate-section">
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-8 border border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                        <h3 class="text-xl font-bold mb-3 md:mb-0">
                            <span class="text-gray-900">Total </span>
                            <span class="bg-gradient-to-r from-[#1a1a1a] via-[#0099ff] to-[#33b5ff] bg-clip-text text-transparent">Pendapatan</span>
                            <span class="text-gray-900"> Unit Pelayanan Usaha</span>
                        </h3>
                        <select id="pendapatan-month" class="px-4 py-2 text-sm border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            @php
                                $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                $currentMonth = $totalPendapatanData['month'];
                            @endphp
                            @foreach($months as $index => $month)
                                <option value="{{ $index + 1 }}" {{ ($index + 1) == $currentMonth ? 'selected' : '' }}>
                                    {{ $month }} {{ $totalPendapatanData['year'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Left: Revenue Bars -->
                        <div class="space-y-6">
                            <!-- Unit Penyewaan Alat -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Unit Penyewaan Alat</span>
                                    <span class="text-sm font-bold text-gray-900">Rp {{ number_format($totalPendapatanData['rental']['revenue'], 0, ',', '.') }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                                    <div class="bg-gradient-to-r from-[#f59e0b] to-[#fbbf24] h-4 rounded-full transition-all duration-500" 
                                         style="width: {{ $totalPendapatanData['rental']['percentage'] }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $totalPendapatanData['rental']['transactions'] }} Transaksi</p>
                            </div>

                            <!-- Unit Penjualan Gas -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Unit Penjualan Gas</span>
                                    <span class="text-sm font-bold text-gray-900">Rp {{ number_format($totalPendapatanData['gas']['revenue'], 0, ',', '.') }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                                    <div class="bg-gradient-to-r from-[#3b82f6] to-[#60a5fa] h-4 rounded-full transition-all duration-500" 
                                         style="width: {{ $totalPendapatanData['gas']['percentage'] }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $totalPendapatanData['gas']['transactions'] }} Transaksi</p>
                            </div>

                            <!-- Total -->
                            <div class="pt-4 border-t border-gray-300">
                                <div class="flex justify-between items-center">
                                    <span class="text-base font-bold text-gray-900">Total Keseluruhan</span>
                                    <span class="text-base font-bold text-gray-900">Rp {{ number_format($totalPendapatanData['total']['revenue'], 0, ',', '.') }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $totalPendapatanData['total']['transactions'] }} Transaksi</p>
                            </div>
                        </div>

                        <!-- Right: Pie Chart -->
                        <div class="flex items-center justify-center">
                            <div id="pendapatanPieChart" class="w-full max-w-sm"></div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="flex flex-wrap justify-center gap-6 text-sm mt-6 pt-6 border-t border-gray-200">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-[#f59e0b] rounded"></div>
                            <span class="text-gray-700 font-medium">Unit Penyewaan Alat</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-[#3b82f6] rounded"></div>
                            <span class="text-gray-700 font-medium">Unit Penjualan Gas</span>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 mt-6 leading-relaxed">
                        Diagram menunjukkan perbandingan total pendapatan dari transaksi per unit usaha BUMDes per bulan. Informasi ini membantu dalam memahami kontribusi setiap unit terhadap pendapatan total BUMDes dan dapat digunakan untuk perencanaan strategis ke depan.
                    </p>
                </div>
            </div>

        </div>
    </section>
</main>
@endsection

@push('styles')
<style>
    * {
        font-family: 'Inter', sans-serif;
    }
    
    /* Animation keyframes */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Initial hidden state */
    .animate-section {
        opacity: 0;
        transform: translateY(30px);
    }
    
    /* Animated state */
    .animate-section.show {
        animation: fadeInUp 0.8s ease-out forwards;
    }
    
    /* Staggered delays for each section */
    .animate-section:nth-child(1) { animation-delay: 0.1s; }
    .animate-section:nth-child(2) { animation-delay: 0.2s; }
    .animate-section:nth-child(3) { animation-delay: 0.3s; }
    .animate-section:nth-child(4) { animation-delay: 0.4s; }
    .animate-section:nth-child(5) { animation-delay: 0.5s; }
</style>
@endpush

@push('scripts')
{{-- ApexCharts Library - Minified Version --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate sections on load
        const sections = document.querySelectorAll('.animate-section');
        sections.forEach((section, index) => {
            setTimeout(() => {
                section.classList.add('show');
            }, index * 100);
        });

        // Initialize charts
        initKinerjaChart();
        initUnitChart();
        initPendapatanPieChart();

        // Helper function for URL updates
        const updateUrlParam = (key, value) => {
            const url = new URL(window.location.href);
            url.searchParams.set(key, value);
            window.location.href = url.toString();
        };

        // Handle Year Change for Kinerja
        const kinerjaYearSelect = document.getElementById('kinerja-year');
        if (kinerjaYearSelect) {
            kinerjaYearSelect.addEventListener('change', function() {
                updateUrlParam('year', this.value);
            });
        }

        // Handle Year Change for Unit
        const unitYearSelect = document.getElementById('unit-year');
        if (unitYearSelect) {
            unitYearSelect.addEventListener('change', function() {
                updateUrlParam('year', this.value);
            });
        }

        // Handle Month Change for Total Pendapatan
        const monthSelect = document.getElementById('pendapatan-month');
        if (monthSelect) {
            monthSelect.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('month', this.value);
                // Preserve current year if exists, otherwise dont force 2025
                if (!url.searchParams.has('year')) {
                     url.searchParams.set('year', '{{ $year }}');
                }
                window.location.href = url.toString();
            });
        }
    });

    // Kinerja BUMDes Chart
    function initKinerjaChart() {
        const container = document.querySelector("#kinerjaChart");
        if (!container) return;

        const options = {
            series: [{
                name: 'Kinerja (Juta Rupiah)',
                data: @json($kinerjaData['data'])
            }],
            chart: {
                type: 'area',
                height: 300,
                toolbar: { show: false },
                zoom: { enabled: false },
                background: 'transparent'
            },
            colors: ['#f59e0b'],
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            markers: {
                size: 0,
                hover: { size: 6 }
            },
            xaxis: {
                categories: @json($kinerjaData['categories']),
                labels: {
                    style: {
                        colors: '#374151',
                        fontSize: '12px',
                        fontWeight: 500
                    }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    formatter: (val) => val.toFixed(1),
                    style: {
                        colors: '#6b7280',
                        fontSize: '11px'
                    }
                }
            },
            grid: {
                borderColor: '#e5e7eb',
                strokeDashArray: 3,
                padding: {
                    top: 0,
                    right: 5,
                    bottom: 0,
                    left: 5
                }
            },
            tooltip: {
                y: {
                    formatter: (val) => 'Rp ' + val.toFixed(1) + ' Juta'
                }
            }
        };

        const chart = new ApexCharts(container, options);
        chart.render();
    }

    // Unit Populer Chart
    function initUnitChart() {
        const container = document.querySelector("#unitChart");
        if (!container) return;

        const options = {
            series: [
                {
                    name: 'Unit Penyewaan Alat',
                    data: @json($unitPopulerData['rental'])
                },
                {
                    name: 'Unit Penjualan Gas',
                    data: @json($unitPopulerData['gas'])
                }
            ],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false },
                stacked: false,
                background: 'transparent'
            },
            colors: ['#f59e0b', '#3b82f6'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '50%',
                    borderRadius: 4
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: @json($unitPopulerData['categories']),
                labels: {
                    style: {
                        colors: '#374151',
                        fontSize: '12px',
                        fontWeight: 500
                    }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#6b7280',
                        fontSize: '11px'
                    }
                }
            },
            grid: {
                borderColor: '#e5e7eb',
                strokeDashArray: 3,
                padding: {
                    top: 0,
                    right: 10,
                    bottom: 0,
                    left: 5
                }
            },
            legend: { show: false },
            tooltip: {
                shared: true,
                intersect: false
            }
        };

        const chart = new ApexCharts(container, options);
        chart.render();
    }

    // Pendapatan Pie Chart
    function initPendapatanPieChart() {
        const container = document.querySelector("#pendapatanPieChart");
        if (!container) return;

        const rentalPercentage = {{ $totalPendapatanData['rental']['percentage'] }};
        const gasPercentage = {{ $totalPendapatanData['gas']['percentage'] }};

        const options = {
            series: [rentalPercentage, gasPercentage],
            chart: {
                type: 'pie',
                height: 280
            },
            labels: ['Penyewaan', 'Penjualan'],
            colors: ['#f59e0b', '#3b82f6'],
            legend: {
                show: false
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val.toFixed(1) + '%';
                },
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold',
                    colors: ['#fff']
                },
                dropShadow: {
                    enabled: true,
                    top: 1,
                    left: 1,
                    blur: 1,
                    opacity: 0.5
                }
            },
            plotOptions: {
                pie: {
                    expandOnClick: false
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val.toFixed(1) + '%';
                    }
                }
            }
        };

        const chart = new ApexCharts(container, options);
        chart.render();
    }
</script>
@endpush