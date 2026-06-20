@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-40 pb-16">
        <!-- Animated Background Wrapper -->
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
            <div id="animated-bg" class="absolute inset-0 bg-cover bg-top bg-no-repeat opacity-0 scale-105 transition-all duration-1000 ease-out" 
                 style="background-image: url('{{ asset('Admin/img/elements/background1.png') }}');">
            </div>
            <!-- White Overlay -->
            <div class="absolute inset-0 bg-white/25"></div>
        </div>

        <div id="main-content" class="max-w-6xl mx-auto px-6 relative z-20">
            
            <!-- Header Section -->
            <div class="text-center mb-16 animate-section">
                <h1 class="text-4xl md:text-5xl font-bold mb-4 bg-gradient-to-r from-[#1a1a1a] via-[#0099ff] to-[#33b5ff] bg-clip-text text-transparent">
                    Laporan Layanan Daerah
                </h1>
                <p class="text-lg text-gray-700">
                    Laporan kinerja dan aktivitas Layanan Daerah
                </p>
            </div>

            <!-- Desa Section Header -->
            <div class="mb-12 animate-section">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 text-center mb-2">
                    Kabupaten Bengkalis
                </h2>
                <h3 class="text-xl md:text-2xl font-bold text-center bg-gradient-to-r from-[#1a1a1a] via-[#0099ff] to-[#33b5ff] bg-clip-text text-transparent mb-8">
                    Grafik Umum
                </h3>

                <!-- Global Filters -->
                <div class="max-w-5xl mx-auto flex flex-col md:flex-row justify-center items-center gap-4 bg-white/40 backdrop-blur-md p-4 rounded-2xl border border-gray-200 shadow-sm">
                    <div class="px-4 py-3 text-sm border border-gray-300 rounded-xl bg-white/80 backdrop-blur-md text-gray-800 font-bold flex items-center justify-center cursor-not-allowed shadow-sm" style="min-width: 200px;">
                        Kabupaten Bengkalis
                    </div>
                    
                    <div class="relative inline-block" style="min-width: 250px;">
                        <select id="kecamatanSelect" class="w-full appearance-none px-4 py-3 pr-10 text-sm border border-gray-300 rounded-xl bg-white/80 backdrop-blur-md text-gray-800 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 hover:bg-white transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                            <option value="all">Semua Kecamatan</option>
                            @foreach($kecamatans as $kec)
                                <option value="{{ $kec->id }}" {{ $kecamatanId == $kec->id ? 'selected' : '' }}>{{ $kec->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>

                    <div class="relative inline-block" style="min-width: 240px;">
                        <select id="desaSelect" class="w-full appearance-none px-4 py-3 pr-10 text-sm border border-gray-300 rounded-xl bg-white/80 backdrop-blur-md text-gray-800 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 hover:bg-white transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm" {{ $kecamatanId == 'all' ? 'disabled' : '' }}>
                            <option value="all">Semua Kelurahan/Desa</option>
                            @foreach($desas as $desa)
                                <option value="{{ $desa->id }}" {{ $desaId == $desa->id ? 'selected' : '' }}>{{ $desa->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>

                    <div class="relative inline-block" style="min-width: 120px;">
                        <select id="globalYearSelect" translate="no" class="w-full appearance-none px-4 py-3 pr-10 text-sm border border-gray-300 rounded-xl bg-white/80 backdrop-blur-md text-gray-800 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 hover:bg-white transition-all shadow-sm">
                            @foreach($availableYears as $optYear)
                                <option value="{{ $optYear }}" {{ $optYear == $year ? 'selected' : '' }}>{{ $optYear }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kinerja BUMDes Chart -->
            <div class="mb-16 animate-section">
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-8 border border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                        <h3 class="text-xl font-bold mb-3 md:mb-0">
                            <span class="text-gray-900">Kinerja </span>
                            <span class="bg-gradient-to-r from-[#1a1a1a] via-[#0099ff] to-[#33b5ff] bg-clip-text text-transparent">Layanan</span>
                        </h3>
                    </div>
                    <div class="bg-white/50 backdrop-blur-sm rounded-2xl p-5 border border-gray-100">
                        <div id="kinerjaChart" data-chart='@json($kinerjaData)'></div>
                    </div>
                    <p class="text-sm text-gray-600 mt-4 leading-relaxed">
                        Grafik menunjukkan perkembangan tingkat aktivitas secara unit Layanan Daerah Kabupaten Bengkalis. Data diambil dari total pendapatan per bulan. Informasi ini membantu dalam memahami tren kinerja dan mengidentifikasi area yang perlu ditingkatkan.
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
                    </div>
                    <div class="bg-white/50 backdrop-blur-sm rounded-2xl p-5 mb-5 border border-gray-100">
                        <div id="unitChart" data-chart='@json($unitPopulerData)'></div>
                    </div>
                    <!-- Legend - 6 Units -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-4 gap-x-8 text-sm w-fit mx-auto mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-sm" style="background-color: #f59e0b;"></div>
                            <span class="text-gray-700 font-medium">Unit Penyewaan Alat</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-sm" style="background-color: #3b82f6;"></div>
                            <span class="text-gray-700 font-medium">Unit Penjualan Gas</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-sm" style="background-color: #10b981;"></div>
                            <span class="text-gray-700 font-medium">Unit Peminjaman Mobil</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-sm" style="background-color: #8b5cf6;"></div>
                            <span class="text-gray-700 font-medium">Unit Fasilitas Umum</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-sm" style="background-color: #ef4444;"></div>
                            <span class="text-gray-700 font-medium">Pelaporan Warga</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-sm" style="background-color: #06b6d4;"></div>
                            <span class="text-gray-700 font-medium">Pengumuman & Event</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed mt-2">
                        Grafik menunjukkan tingkat aktivitas dari seluruh unit Layanan Daerah. Informasi ini membantu dalam memahami performa dan antusiasme warga terhadap masing-masing layanan sehingga dapat ditingkatkan dan dikembangkan lebih lanjut.
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
                            <span class="text-gray-900"> Unit Pelayanan Daerah</span>
                        </h3>
                        <select id="pendapatan-month" class="px-4 py-2 text-sm border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="all" {{ $totalPendapatanData['month'] === 'all' ? 'selected' : '' }}>Sepanjang Tahun {{ $totalPendapatanData['year'] }}</option>
                            @php
                                $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                $currentMonth = $totalPendapatanData['month'];
                            @endphp
                            @foreach($months as $index => $month)
                                <option value="{{ $index + 1 }}" {{ (string)($index + 1) === (string)$currentMonth ? 'selected' : '' }}>
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

                            <!-- Unit Peminjaman Mobil -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Unit Peminjaman Mobil</span>
                                    <span class="text-sm font-bold text-gray-900">Rp {{ number_format($totalPendapatanData['mobil']['revenue'], 0, ',', '.') }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                                    <div class="bg-gradient-to-r from-[#10b981] to-[#34d399] h-4 rounded-full transition-all duration-500" 
                                         style="width: {{ $totalPendapatanData['mobil']['percentage'] }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $totalPendapatanData['mobil']['transactions'] }} Transaksi</p>
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
                            <div id="pendapatanPieChart" class="w-full max-w-sm" data-chart='@json($totalPendapatanData)'></div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="flex flex-wrap justify-center gap-6 text-sm mt-6 pt-6 border-t border-gray-200">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-sm" style="background-color: #f59e0b;"></div>
                            <span class="text-gray-700 font-medium">Unit Penyewaan Alat</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-sm" style="background-color: #3b82f6;"></div>
                            <span class="text-gray-700 font-medium">Unit Penjualan Gas</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-sm" style="background-color: #10b981;"></div>
                            <span class="text-gray-700 font-medium">Unit Peminjaman Mobil</span>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 mt-6 leading-relaxed">
                        Diagram menunjukkan perbandingan total pendapatan dari transaksi per unit Layanan Daerah per bulan. Informasi ini membantu dalam memahami kontribusi setiap unit terhadap total pendapatan Layanan Daerah dan dapat digunakan untuk perencanaan strategi ke depan.
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
    (() => {
        const initLaporan = function() {
        // Animate background
        const bg = document.getElementById('animated-bg');
        if (bg) {
            setTimeout(() => {
                bg.classList.remove('opacity-0', 'scale-110');
                bg.classList.add('opacity-100', 'scale-100');
            }, 50);
        }

        // Animate sections
        (() => {
            const sections = document.querySelectorAll('.animate-section');
            sections.forEach((section, index) => {
                setTimeout(() => {
                    section.classList.add('show');
                }, index * 100 + 300); // Wait for background animation
            });
        })();

        // Initialize charts
        initKinerjaChart();
        initUnitChart();
        initPendapatanPieChart();

        // Elements
        const globalYearSelect = document.getElementById('globalYearSelect');
        const monthSelect = document.getElementById('pendapatan-month');
        const kecamatanSelect = document.getElementById('kecamatanSelect');
        const desaSelect = document.getElementById('desaSelect');

        // AJAX Update function
        const redirectWithFilters = async () => {
            const url = new URL(window.location.href);
            // Get current active selects since DOM might have changed
            const currentKecamatan = document.getElementById('kecamatanSelect');
            const currentDesa = document.getElementById('desaSelect');
            const currentYear = document.getElementById('globalYearSelect');
            const currentMonth = document.getElementById('pendapatan-month');

            if (currentKecamatan) url.searchParams.set('kecamatan_id', currentKecamatan.value || 'all');
            if (currentDesa) url.searchParams.set('desa_id', currentDesa.value || 'all');
            if (currentYear) url.searchParams.set('year', currentYear.value || new Date().getFullYear());
            if (currentMonth) url.searchParams.set('month', currentMonth.value || new Date().getMonth() + 1);

            const mainContent = document.getElementById('main-content');
            if (mainContent) {
                mainContent.style.transition = 'opacity 0.3s ease';
                mainContent.style.opacity = '0.5';
                mainContent.style.pointerEvents = 'none';
            }

            try {
                window.history.pushState({}, '', url.toString());

                const response = await fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                if (!response.ok) throw new Error('Network response was not ok');
                
                const htmlString = await response.text();
                const parser = new DOMParser();
                const newDoc = parser.parseFromString(htmlString, 'text/html');
                const newMainContent = newDoc.getElementById('main-content');
                
                if (newMainContent && mainContent) {
                    mainContent.innerHTML = newMainContent.innerHTML;
                    
                    // Re-bind events to new DOM elements
                    const newKecamatan = document.getElementById('kecamatanSelect');
                    const newDesa = document.getElementById('desaSelect');
                    const newYear = document.getElementById('globalYearSelect');
                    const newMonth = document.getElementById('pendapatan-month');

                    if (newYear) newYear.addEventListener('change', redirectWithFilters);
                    if (newMonth) {
                        newMonth.value = new URLSearchParams(window.location.search).get('month') || '{{ date("m") }}';
                        newMonth.addEventListener('change', redirectWithFilters);
                    }
                    if (newKecamatan) {
                        newKecamatan.addEventListener('change', function() {
                            if (newDesa) newDesa.value = 'all';
                            redirectWithFilters();
                        });
                    }
                    if (newDesa) newDesa.addEventListener('change', redirectWithFilters);

                    // Re-initialize charts
                    initKinerjaChart();
                    initUnitChart();
                    initPendapatanPieChart();

                    // Re-animate sections
                    const newSections = mainContent.querySelectorAll('.animate-section');
                    newSections.forEach((section, index) => {
                        setTimeout(() => {
                            section.classList.add('show');
                        }, index * 100 + 100);
                    });

                    // Re-animate background
                    const bg = document.getElementById('animated-bg');
                    if (bg) {
                        setTimeout(() => {
                            bg.classList.remove('opacity-0', 'scale-105', 'scale-110');
                            bg.classList.add('opacity-100', 'scale-100');
                        }, 50);
                    }
                } else {
                    window.location.reload();
                }
            } catch (error) {
                console.error('AJAX failed, falling back to reload:', error);
                window.location.reload();
            } finally {
                if (mainContent) {
                    mainContent.style.opacity = '1';
                    mainContent.style.pointerEvents = 'auto';
                }
            }
        };

        // Initial Bindings
        if (globalYearSelect) globalYearSelect.addEventListener('change', redirectWithFilters);
        if (monthSelect) monthSelect.addEventListener('change', redirectWithFilters);
        if (kecamatanSelect) {
            kecamatanSelect.addEventListener('change', function() {
                if (desaSelect) desaSelect.value = 'all';
                redirectWithFilters();
            });
        }
        if (desaSelect) desaSelect.addEventListener('change', redirectWithFilters);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLaporan);
        } else {
            initLaporan();
        }
    })();

    // Kinerja BUMDes Chart
    function initKinerjaChart() {
        const container = document.querySelector("#kinerjaChart");
        if (!container) return;

        container.innerHTML = '';
        let chartData;
        try {
            chartData = JSON.parse(container.getAttribute('data-chart'));
        } catch(e) {
            console.error("Failed to parse kinerja data", e);
            return;
        }

        const options = {
            series: [{
                name: 'Kinerja (Juta Rupiah)',
                data: chartData.data
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
                categories: chartData.categories,
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

        container.innerHTML = '';
        let chartData;
        try {
            chartData = JSON.parse(container.getAttribute('data-chart'));
        } catch(e) {
            console.error("Failed to parse unit data", e);
            return;
        }

        const options = {
            series: [
                {
                    name: 'Unit Penyewaan Alat',
                    data: chartData.rental
                },
                {
                    name: 'Unit Penjualan Gas',
                    data: chartData.gas
                },
                {
                    name: 'Unit Peminjaman Mobil',
                    data: chartData.mobil
                },
                {
                    name: 'Unit Fasilitas Umum',
                    data: chartData.fasilitas
                },
                {
                    name: 'Pelaporan Warga',
                    data: chartData.laporan
                },
                {
                    name: 'Pengumuman & Event',
                    data: chartData.pengumuman
                }
            ],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false },
                stacked: false,
                background: 'transparent'
            },
            colors: ['#f59e0b', '#3b82f6', '#10b981', '#8b5cf6', '#ef4444', '#06b6d4'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '50%',
                    borderRadius: 4
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: chartData.categories,
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

        container.innerHTML = '';
        let chartData;
        try {
            chartData = JSON.parse(container.getAttribute('data-chart'));
        } catch(e) {
            console.error("Failed to parse pie data", e);
            return;
        }

        const rentalPercentage = chartData.rental.percentage;
        const gasPercentage = chartData.gas.percentage;
        const mobilPercentage = chartData.mobil.percentage;

        const options = {
            series: [rentalPercentage, gasPercentage, mobilPercentage],
            chart: {
                type: 'pie',
                height: 280
            },
            labels: ['Penyewaan Alat', 'Penjualan Gas', 'Peminjaman Mobil'],
            colors: ['#f59e0b', '#3b82f6', '#10b981'],
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