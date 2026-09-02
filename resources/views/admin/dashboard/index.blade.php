@extends('admin.layouts.admin')
@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <!-- Kartu Selamat Datang & Grafik Kinerja -->
            <div class="row mb-2 align-items-stretch">
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <div class="card h-100">
                        <div class="d-flex flex-column h-100">
                            <div class="col-12">
                                <div class="card-body p-4 pb-0">
                                    @php
                                        $hour = date('H');
                                        if ($hour >= 5 && $hour < 11) {
                                            $greeting = 'Selamat Pagi';
                                            $icon = '<i class="bx bx-sun text-warning fs-4 ms-1" style="vertical-align: middle;"></i>';
                                        } elseif ($hour >= 11 && $hour < 15) {
                                            $greeting = 'Selamat Siang';
                                            $icon = '<i class="bx bxs-sun text-warning fs-4 ms-1" style="vertical-align: middle;"></i>';
                                        } elseif ($hour >= 15 && $hour < 18) {
                                            $greeting = 'Selamat Sore';
                                            $icon = '<i class="bx bx-cloud text-secondary fs-4 ms-1" style="vertical-align: middle;"></i>';
                                        } else {
                                            $greeting = 'Selamat Malam';
                                            $icon = '<i class="bx bx-moon text-info fs-4 ms-1" style="vertical-align: middle;"></i>';
                                        }

                                        $role = auth()->user()->role;
                                        if (in_array($role, ['super_admin', 'admin'])) {
                                            $regionName = 'Pemerintah Kabupaten Bengkalis';
                                            $kinerjaTitle = 'Kinerja Tingkat Kabupaten';
                                            $pendapatanTitle = 'Jumlah Pendapatan Layanan Kabupaten';
                                            $perbandinganTitle = 'Perbandingan Transaksi Kabupaten';
                                        } elseif ($role == 'admin_kecamatan') {
                                            $name = \App\Models\Region::find(auth()->user()->region_id)->name ?? 'Kecamatan';
                                            $regionName = 'Pemerintah ' . $name;
                                            $kinerjaTitle = 'Kinerja Tingkat Kecamatan';
                                            $pendapatanTitle = 'Jumlah Pendapatan Layanan Kecamatan';
                                            $perbandinganTitle = 'Perbandingan Transaksi Kecamatan';
                                        } else {
                                            $name = \App\Models\Region::find(auth()->user()->region_id)->name ?? 'Desa';
                                            $regionName = 'Pemerintah ' . $name;
                                            $kinerjaTitle = 'Kinerja Pemerintah Desa';
                                            $pendapatanTitle = 'Jumlah Pendapatan Unit Pelayanan Usaha';
                                            $perbandinganTitle = 'Perbandingan Transaksi';
                                        }
                                    @endphp
                                    <h5 class="card-title text-primary fw-bold mb-3">{{ $greeting }}, {{ explode(' ', Auth::user()->name ?? 'Administrator')[0] }} {!! $icon !!}</h5>
                                    <p class="mb-2 text-muted">Sistem Pelayanan Terpadu berbasis Digital <br><span class="fw-bold text-dark">{{ $regionName }}</span></p>
                                    @if(in_array(auth()->user()->role, ['admin_desa']))
                                    <a href="{{ route('admin.SiladesBeng.bumdes.index') }}" class="btn btn-sm btn-outline-primary">Profil Pemerintah Desa</a>
                                    @endif
                                </div>
                            </div>
                            <!-- Menambahkan indikator layanan aktif di atas banner -->
                            <div class="col-12 mt-4 px-4">
                                @php
                                    $activeServicesCount = \App\Models\RegionService::where('region_id', auth()->user()->region_id)->where('is_active', true)->count();
                                    $totalServicesCount = \App\Models\Service::count();
                                @endphp
                                <div class="d-flex align-items-center bg-light rounded-3 p-3 shadow-sm">
                                    <div class="avatar flex-shrink-0 me-3" style="width: 50px; height: 50px;">
                                        <span class="avatar-initial rounded bg-label-primary w-100 h-100 p-1">
                                            <img src="{{ asset('Admin/img/illustrations/logokab.webp') }}" alt="Logo Kab" style="width: 100%; height: 100%; object-fit: contain;">
                                        </span>
                                    </div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0 fw-bold text-dark">Modul Layanan Aktif</h6>
                                            <small class="text-muted">Untuk {{ $regionName }}</small>
                                        </div>
                                        <div class="user-progress text-end">
                                            <small class="fw-bold text-primary fs-5">{{ $activeServicesCount }}</small> <small class="text-muted fs-6">/ {{ $totalServicesCount }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 mt-auto">
                                <div class="px-3 pb-3 pt-4">
                                    <div id="dashboardBannerCarousel" class="carousel slide" data-bs-ride="carousel">
                                        @php
                                            $banners = \App\Models\Banner::where('is_active', true)->latest()->get();
                                        @endphp
                                        
                                        <!-- Indicators -->
                                        <div class="carousel-indicators">
                                            @if($banners->count() > 0)
                                                @foreach($banners as $index => $banner)
                                                    <button type="button" data-bs-target="#dashboardBannerCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}" aria-current="{{ $index == 0 ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}"></button>
                                                @endforeach
                                            @else
                                                <button type="button" data-bs-target="#dashboardBannerCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                                <button type="button" data-bs-target="#dashboardBannerCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                            @endif
                                        </div>

                                        <div class="carousel-inner rounded-3 shadow-sm">
                                            @if($banners->count() > 0)
                                                @foreach($banners as $index => $banner)
                                                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}" data-bs-interval="3000">
                                                        <img src="{{ Storage::url($banner->image_path) }}" class="d-block w-100 rounded-3" alt="Banner {{ $index + 1 }}">
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="carousel-item active" data-bs-interval="3000">
                                                    <img src="{{ asset('User/img/elemen/kuncislide1r.png') }}" class="d-block w-100 rounded-3" alt="Slide 1">
                                                </div>
                                                <div class="carousel-item" data-bs-interval="3000">
                                                    <img src="{{ asset('User/img/elemen/kuncislide2r.png') }}" class="d-block w-100 rounded-3" alt="Slide 2">
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Controls -->
                                        <button class="carousel-control-prev" type="button" data-bs-target="#dashboardBannerCarousel" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#dashboardBannerCarousel" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <div
                                class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4">
                                <div>
                                    <h5 class="card-title fw-bold mb-2">{{ $kinerjaTitle }}</h5>
                                    <span class="badge bg-label-warning rounded-pill">Tahun {{ $selectedYear }}</span>
                                </div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mt-3 mt-sm-0">
                                    @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                                    <select class="form-select form-select-sm" id="kecamatanSelect" style="min-width: 150px;">
                                        <option value="all" {{ empty($selectedKecamatanId) || $selectedKecamatanId == 'all' ? 'selected' : '' }}>Semua Kecamatan</option>
                                        @foreach($kecamatanList ?? [] as $kecamatan)
                                            <option value="{{ $kecamatan->id }}" {{ $selectedKecamatanId == $kecamatan->id ? 'selected' : '' }}>{{ $kecamatan->name }}</option>
                                        @endforeach
                                    </select>
                                    @endif

                                    @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'admin_kecamatan']))
                                    <select class="form-select form-select-sm" id="desaSelect" style="min-width: 150px;">
                                        <option value="all" {{ empty($selectedDesaId) || $selectedDesaId == 'all' ? 'selected' : '' }}>Semua Desa</option>
                                        @foreach($desaList ?? [] as $desa)
                                            <option value="{{ $desa->id }}" {{ $selectedDesaId == $desa->id ? 'selected' : '' }}>{{ $desa->name }}</option>
                                        @endforeach
                                    </select>
                                    @endif

                                    <select class="form-select form-select-sm" id="tahunSelect" style="min-width: 100px;">
                                        @foreach($availableYears as $year)
                                            <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                    <script>
                                        function updateFilters(isKecamatanChange = false) {
                                            let year = document.getElementById('tahunSelect').value;
                                            let kecamatanSelect = document.getElementById('kecamatanSelect');
                                            let desaSelect = document.getElementById('desaSelect');
                                            
                                            let url = "{{ route('admin.dashboard') }}?year=" + year;
                                            
                                            if (kecamatanSelect && kecamatanSelect.value !== 'all') {
                                                url += "&kecamatan_id=" + kecamatanSelect.value;
                                            }
                                            
                                            // Only append desa_id if we didn't just change the kecamatan (resetting desa filter)
                                            if (desaSelect && desaSelect.value !== 'all' && !isKecamatanChange) {
                                                url += "&desa_id=" + desaSelect.value;
                                            }
                                            
                                            window.location.href = url;
                                        }

                                        document.getElementById('tahunSelect').addEventListener('change', () => updateFilters(false));
                                        
                                        let kecSel = document.getElementById('kecamatanSelect');
                                        if (kecSel) {
                                            kecSel.addEventListener('change', () => updateFilters(true));
                                        }

                                        let desaSel = document.getElementById('desaSelect');
                                        if (desaSel) {
                                            desaSel.addEventListener('change', () => updateFilters(false));
                                        }
                                    </script>
                                </div>
                            </div>
                            <div class="flex-grow-1 d-flex flex-column justify-content-center">
                                <div id="kinerjaChart" style="min-height: 240px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-3"></div>

            <!-- Kartu Unit - Lebar Penuh -->
            @php
                    $laporanPendingCount = 0;
                    if(class_exists('\App\Models\Laporan')) {
                        $laporanPendingCount = \App\Models\Laporan::where('status', 'Pending')->count() ?? 0;
                    }
                    $unitConfigs = [
                        'Penyewaan Alat' => [
                            'title' => 'Unit Penyewaan Alat',
                            'count' => ($unitPenyewaan ?? \App\Models\Barang::count() ?? 0),
                            'label' => 'Item',
                            'route' => route('admin.unit.penyewaan.index'),
                            'image' => asset('User/img/elemen/F1.png'),
                            'color' => 'warning'
                        ],
                        'Penjualan Gas' => [
                            'title' => 'Unit Penjualan Gas',
                            'count' => ($unitGas ?? \App\Models\Gas::count() ?? 0),
                            'label' => 'Jenis Tabung',
                            'route' => route('admin.unit.penjualan_gas.index'),
                            'image' => asset('User/img/elemen/F2.png'),
                            'color' => 'danger'
                        ],
                        'Penyewaan Mobil' => [
                            'title' => 'Unit Penyewaan Mobil',
                            'count' => (\App\Models\Mobil::count() ?? 0),
                            'label' => 'Kendaraan',
                            'route' => route('admin.unit.mobil.index'),
                            'image' => asset('User/img/elemen/mobil.png'),
                            'color' => 'info'
                        ],
                        'Fasilitas Umum' => [
                            'title' => 'Unit Peminjaman Fasilitas Umum',
                            'count' => (\App\Models\FasilitasUmum::count() ?? 0),
                            'label' => 'Fasilitas',
                            'route' => route('admin.unit.fasilitas_umum.index'),
                            'image' => asset('User/img/elemen/fasilitas.png'),
                            'color' => 'success'
                        ],
                        'Pelaporan Warga' => [
                            'title' => 'Pelaporan Warga',
                            'count' => $laporanPendingCount,
                            'label' => 'Pending',
                            'route' => Route::has('admin.laporan.index') ? route('admin.laporan.index') : '#',
                            'image' => asset('User/img/elemen/lapor.png'),
                            'color' => 'primary'
                        ],
                        'Pengumuman' => [
                            'title' => 'Kabar dan Informasi Daerah',
                            'count' => (\App\Models\Announcement::count() ?? 0),
                            'label' => 'Info',
                            'route' => route('admin.announcements.index'),
                            'image' => asset('User/img/elemen/KabardanInformasiDaerah.png'),
                            'color' => 'secondary'
                        ],
                        'Pasar Daerah' => [
                            'title' => 'Unit Pasar Daerah',
                            'count' => (\App\Models\PasarProduk::count() ?? 0),
                            'label' => 'Produk',
                            'route' => route('admin.unit.pasar_daerah.index'),
                            'image' => asset('Admin/img/pasardaerah/PasarDaerah2.png'),
                            'color' => 'warning'
                        ]
                    ];

                    $activeServicesList = isset($activeServices) ? $activeServices : [];
                    
                    $validServiceCount = 0;
                    foreach($activeServicesList as $s) {
                        if(isset($unitConfigs[$s])) $validServiceCount++;
                    }
                    
                    $colClass = 'col-md-6';
                    $isSquare = false;
                    
                    if ($validServiceCount == 3) {
                        $colClass = 'col-lg-4 col-md-6';
                        $isSquare = true;
                    } elseif ($validServiceCount >= 4) {
                        $colClass = 'col-lg-3 col-md-4 col-sm-6';
                        $isSquare = true;
                    }
                @endphp
            @if(in_array(auth()->user()->role, ['admin_desa', 'admin_rt', 'admin_rw']))
            <div class="row mb-4">
                @foreach($activeServicesList as $serviceName)
                    @if(isset($unitConfigs[$serviceName]))
                        @php $config = $unitConfigs[$serviceName]; @endphp
                        <div class="{{ $colClass }} mb-4">
                            <div class="card unit-card h-100 border-{{ $config['color'] }} hover-lift" style="border-top: 3px solid; cursor: pointer;"
                                onclick="window.location='{{ $config['route'] }}'">
                                
                                @if($isSquare)
                                    <!-- Layout Kotak (Vertical) -->
                                    <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center text-center">
                                        <div class="avatar mb-3" style="width: 70px; height: 70px;">
                                            <img src="{{ $config['image'] }}" alt="{{ $config['title'] }}" class="rounded w-100" />
                                        </div>
                                        <div class="mt-2">
                                            <span class="fw-semibold d-block mb-2 text-muted" style="font-size: 0.85rem; line-height: 1.2; min-height: 2em;">{{ $config['title'] }}</span>
                                            <h4 class="card-title mb-0 text-{{ $config['color'] }}"><span class="count-up fw-bold" data-value="{{ $config['count'] }}">0</span> <span class="fs-6 text-body">{{ $config['label'] }}</span></h4>
                                        </div>
                                    </div>
                                @else
                                    <!-- Layout Memanjang (Horizontal) -->
                                    <div class="card-body p-4 d-flex align-items-center">
                                        <div class="avatar flex-shrink-0 me-3" style="width: 65px; height: 65px;">
                                            <img src="{{ $config['image'] }}" alt="{{ $config['title'] }}" class="rounded w-100" />
                                        </div>
                                        <div class="flex-grow-1">
                                            <span class="fw-semibold d-block mb-1 text-muted">{{ $config['title'] }}</span>
                                            <h4 class="card-title mb-0"><span class="count-up" data-value="{{ $config['count'] }}">0</span> {{ $config['label'] }}</h4>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-center bg-label-{{ $config['color'] }} rounded ms-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                            <i class="bx bx-chevron-right text-{{ $config['color'] }}"></i>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            @endif

                <!-- Bagian Notifikasi -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-4">
                            <div class="card-header bg-white py-3 border-bottom px-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1 fw-bold d-flex align-items-center text-primary">
                                            <span class="badge badge-center rounded-pill bg-primary-subtle text-primary me-2"
                                                style="width: 32px; height: 32px;">
                                                <i class="bx bx-bell fs-5"></i>
                                            </span>
                                            Notifikasi Permintaan
                                        </h5>
                                    </div>
                                    <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        Lihat Semua <i class="bx bx-right-arrow-alt ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush border-0">
                                    @forelse($latestRequests as $request)
                                        @php
                                            $icon = 'bx-bell';
                                            $bgClass = 'bg-primary-subtle text-primary';
                                            $badgeClass = 'bg-primary text-white';
                                            $serviceName = 'Umum';
                                            $detailLink = route('admin.aktivitas.permintaan-pengajuan.show', [$request->id, $request->type ?? 'rental']);
                                            $canQuickAction = in_array($request->type, ['rental', 'gas', 'mobil', 'fasilitas_umum']);
                                            
                                            if ($request->type == 'rental') {
                                                $icon = 'bx-wrench'; $bgClass = 'bg-warning-subtle text-warning'; $badgeClass = 'bg-warning text-white'; $serviceName = 'Penyewaan Alat';
                                            } elseif ($request->type == 'gas') {
                                                $icon = 'bxs-gas-pump'; $bgClass = 'bg-danger-subtle text-danger'; $badgeClass = 'bg-danger text-white'; $serviceName = 'Penjualan Gas';
                                            } elseif ($request->type == 'mobil') {
                                                $icon = 'bx-car'; $bgClass = 'bg-info-subtle text-info'; $badgeClass = 'bg-info text-white'; $serviceName = 'Penyewaan Mobil';
                                            } elseif ($request->type == 'fasilitas_umum') {
                                                $icon = 'bx-building-house'; $bgClass = 'bg-success-subtle text-success'; $badgeClass = 'bg-success text-white'; $serviceName = 'Fasilitas Umum';
                                            } elseif ($request->type == 'pasar_daerah') {
                                                $icon = 'bx-store-alt'; $bgClass = 'bg-primary-subtle text-primary'; $badgeClass = 'bg-primary text-white'; $serviceName = 'Pasar Daerah';
                                                $detailLink = Route::has('admin.unit.pasar_daerah.pesanan.show') ? route('admin.unit.pasar_daerah.pesanan.show', $request->id) : '#';
                                            } elseif ($request->type == 'laporan') {
                                                $icon = 'bx-message-error'; $bgClass = 'bg-dark-subtle text-dark'; $badgeClass = 'bg-dark text-white'; $serviceName = 'Pelaporan Warga';
                                                $detailLink = Route::has('admin.laporan.show') ? route('admin.laporan.show', $request->id) : '#';
                                            }
                                            
                                            $requestName = $request->full_name ?? $request->recipient_name ?? $request->user->name ?? 'User';
                                        @endphp
                                        
                                        <div class="list-group-item list-group-item-action d-flex align-items-center p-4 border-bottom-0 border-top" style="gap: 1.25rem; transition: all 0.2s ease;">
                                            <!-- Icon / Avatar -->
                                            <div class="avatar flex-shrink-0" style="width: 48px; height: 48px;">
                                                <span class="avatar-initial rounded-circle {{ $bgClass }} shadow-sm">
                                                    <i class="bx {{ $icon }} fs-4"></i>
                                                </span>
                                            </div>
                                            
                                            <!-- Info -->
                                            <div class="flex-grow-1" style="min-width: 0;">
                                                <div class="d-flex align-items-center mb-1 gap-2 flex-wrap">
                                                    <h6 class="mb-0 fw-bold text-dark text-truncate">{{ $request->item_name }}</h6>
                                                    <span class="badge {{ $badgeClass }} rounded-pill px-2 shadow-sm" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                                        {{ strtoupper($serviceName) }}
                                                    </span>
                                                    @if(isset($request->cancellation_status) && $request->cancellation_status == 'pending')
                                                        <span class="badge bg-danger rounded-pill px-2 shadow-sm" style="font-size: 0.65rem; letter-spacing: 0.5px;"><i class="bx bx-error-circle me-1"></i>MINTA BATAL</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center text-muted small gap-3 flex-wrap">
                                                    <span class="d-flex align-items-center"><i class="bx bx-user me-1 text-secondary"></i> <span class="fw-medium">{{ $requestName }}</span></span>
                                                    <span class="d-flex align-items-center"><i class="bx bx-time-five me-1 text-secondary"></i> {{ $request->created_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="d-flex gap-2 flex-shrink-0">
                                                <a href="{{ $detailLink }}" class="btn btn-sm btn-white rounded-pill px-3 shadow-sm text-primary fw-bold border">
                                                    Lihat Detail <i class="bx bx-right-arrow-alt ms-1"></i>
                                                </a>
                                                
                                                @if($canQuickAction)
                                                    @if(isset($request->cancellation_status) && $request->cancellation_status == 'pending')
                                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm fw-bold" onclick="handleCancellation({{ $request->id }}, '{{ $request->type }}', 'approve')">
                                                            <i class="bx bx-check me-1"></i> Setujui Batal
                                                        </button>
                                                    @elseif($request->status == 'pending' || $request->status == 'waiting')
                                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-bold" onclick="approveRequest({{ $request->id }}, '{{ $request->type }}')">
                                                            <i class="bx bx-check me-1"></i> Proses
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-5 text-center">
                                            <div class="bg-label-primary rounded-circle d-inline-flex p-4 mb-3 shadow-sm">
                                                <i class="bx bx-bell-off fs-1 text-primary"></i>
                                            </div>
                                            <h5 class="fw-bold text-dark mb-1">Belum Ada Notifikasi</h5>
                                            <p class="text-muted mb-0">Saat ini tidak ada permintaan layanan yang perlu diproses.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Tolak -->
                <div class="modal fade" id="rejectModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0">
                            <div class="modal-header border-bottom-0 pb-0">
                                <h5 class="modal-title fw-bold">Tolak Permintaan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="rejectForm" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Alasan Penolakan <span class="text-danger">*</span></label>
                                        <textarea name="reason" class="form-control bg-light border-0 py-3" rows="4" placeholder="Jelaskan alasan penolakan permintaan..." required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-top-0 pt-0">
                                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                                        Tolak Permintaan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    function handleCancellation(id, type, action) {
                        let title, text, confirmBtn, icon;
                        
                        if (action === 'approve') {
                            title = 'Setujui Pembatalan?';
                            text = "Pesanan akan dibatalkan sesuai permintaan pengguna.";
                            confirmBtn = 'Ya, Setujui Pembatalan';
                            icon = 'warning';
                        } else {
                            // Untuk penolakan pembatalan, kita butuh input alasan
                            // Gunakan SweetAlert dengan input
                            Swal.fire({
                                title: 'Tolak Pembatalan',
                                input: 'textarea',
                                inputLabel: 'Alasan Penolakan',
                                inputPlaceholder: 'Jelaskan kenapa pembatalan ditolak...',
                                inputAttributes: {
                                    'aria-label': 'Jelaskan kenapa pembatalan ditolak'
                                },
                                showCancelButton: true,
                                confirmButtonText: 'Tolak Pembatalan',
                                cancelButtonText: 'Batal',
                                inputValidator: (value) => {
                                    if (!value) {
                                        return 'Anda harus menuliskan alasan penolakan!'
                                    }
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    submitCancellationResponse(id, type, action, result.value);
                                }
                            });
                            return; // Hentikan eksekusi di sini, lanjut di submitCancellationResponse
                        }

                        Swal.fire({
                            title: title,
                            text: text,
                            icon: icon,
                            showCancelButton: true,
                            confirmButtonColor: action === 'approve' ? '#198754' : '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: confirmBtn,
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                submitCancellationResponse(id, type, action, null);
                            }
                        });
                    }

                    function submitCancellationResponse(id, type, action, reason) {
                        Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });
                        
                        let url = `{{ url('admin/aktivitas/permintaan-pengajuan') }}/${type}/${id}/cancellation/${action}`;
                        let body = { 
                            _token: '{{ csrf_token() }}' 
                        };
                        
                        if (reason) {
                            body.admin_response = reason;
                        }

                        fetch(url, {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json',
                                'Accept': 'application/json' 
                            },
                            body: JSON.stringify(body)
                        })
                        .then(res => res.json())
                        .then(data => {
                            if(data.success) {
                                Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Gagal', data.message, 'error');
                            }
                        })
                        .catch(err => Swal.fire('Error', 'Terjadi kesalahan sistem', 'error'));
                    }

                    function approveRequest(id, type) {
                        Swal.fire({
                            title: 'Setujui Pesanan?',
                            text: "Pastikan stok tersedia. Pesanan akan diproses.",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#198754',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, Lanjutkan',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Show loader
                                Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });
                                
                                fetch(`{{ url('admin/aktivitas/permintaan-pengajuan') }}/${id}/${type}/approve`, {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                })
                                .then(res => res.json())
                                .then(data => {
                                    if(data.success) {
                                        Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                                    } else {
                                        Swal.fire('Gagal', data.message, 'error');
                                    }
                                })
                                .catch(err => Swal.fire('Error', 'Terjadi kesalahan sistem', 'error'));
                            }
                        });
                    }

                    function rejectRequest(id, type) {
                        const modalEl = document.getElementById('rejectModal');
                        if(modalEl) {
                            // Pindahkan modal ke body untuk menghindari masalah z-index/backdrop
                            if(modalEl.parentNode !== document.body) {
                                document.body.appendChild(modalEl);
                            }
                            const modal = new bootstrap.Modal(modalEl);
                            document.getElementById('rejectForm').action = `{{ url('admin/aktivitas/permintaan-pengajuan') }}/${id}/${type}/reject`;
                            modal.show();
                        } else {
                            console.error('Reject Modal not found');
                        }
                    }
                </script>

                <!-- Tambahkan Animate.css untuk animasi halus -->
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

                <!-- Tata Letak Tiga Kolom untuk Statistik Keuangan -->
                <div class="row mb-4">
                    <!-- Left Column: Total Pendapatan Unit Pelayanan Usaha -->
                    <div class="col-lg-8 mb-4 mb-lg-0">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 text-dark">
                                    {{ $pendapatanTitle }}
                                </h5>
                                <select id="pendapatan-month" class="form-select form-select-sm" style="width: auto;">
                                    @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $month)
                                        <option value="{{ $index + 1 }}" {{ ($index + 1) == ($totalPendapatanData['month'] ?? date('m')) ? 'selected' : '' }}>
                                            {{ $month }} {{ $totalPendapatanData['year'] ?? date('Y') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="card-body">
                                @php
                                    $revenueServices = ['Penyewaan Alat', 'Penjualan Gas', 'Penyewaan Mobil', 'Fasilitas Umum', 'Pasar Daerah'];
                                    $activeRevenueServices = array_intersect($activeServicesList, $revenueServices);
                                @endphp
                                @if(count($activeRevenueServices) > 0)
                                    <div class="row h-100 align-items-center">
                                        <!-- Data List -->
                                        <div class="col-md-7">
                                            @foreach($activeRevenueServices as $serviceName)
                                                @php
                                                    $dataItem = $totalPendapatanData[$serviceName] ?? ['revenue' => 0, 'transactions' => 0, 'percentage' => 0, 'color' => 'secondary'];
                                                @endphp
                                                <div class="mb-4">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="fw-medium">Unit {{ $serviceName }}</span>
                                                        <span class="fw-bold">Rp <span class="count-up-rupiah" data-value="{{ $dataItem['revenue'] }}">0</span></span>
                                                    </div>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar bg-{{ $dataItem['color'] }}" role="progressbar" style="width: {{ $dataItem['percentage'] }}%"></div>
                                                    </div>
                                                    <small class="text-muted"><span class="count-up" data-value="{{ $dataItem['transactions'] }}">0</span> Transaksi</small>
                                                </div>
                                            @endforeach

                                            <!-- Total -->
                                            <div class="pt-3 border-top">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">Total Keseluruhan</h6>
                                                    <h6 class="mb-0 fw-bold">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['total']['revenue'] ?? 0 }}">0</span></h6>
                                                </div>
                                                <small class="text-muted"><span class="count-up" data-value="{{ $totalPendapatanData['total']['transactions'] ?? 0 }}">0</span> Transaksi</small>
                                            </div>
                                        </div>

                                        <!-- Pie Chart -->
                                        <div class="col-md-5 d-flex align-items-center justify-content-center">
                                            <div id="pendapatanPieChart" style="width: 100%;"></div>
                                        </div>
                                    </div>
                                @else
                                    <div class="d-flex h-100 justify-content-center align-items-center">
                                        <div class="text-center py-5">
                                            <i class="bx bx-store-alt text-muted fs-1 mb-3"></i>
                                            <h6 class="mb-1">Data Belum Tersedia</h6>
                                            <p class="text-muted mb-0 small">Belum ada unit layanan yang diaktifkan. Silakan aktifkan unit layanan terlebih dahulu di menu Pengaturan Layanan.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Grafik Transaksi -->
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header pb-0">
                                <h5 class="card-title mb-0">{{ $perbandinganTitle }}</h5>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center align-items-center"
                                style="min-height: 280px; padding: 1rem;">
                                @if(count($activeRevenueServices) > 0)
                                    <div id="transactionDonutChart" style="width: 100%;"></div>
                                @else
                                    <div class="text-center">
                                        <i class="bx bx-pie-chart-alt text-muted fs-1 mb-3"></i>
                                        <h6 class="mb-1">Grafik Belum Tersedia</h6>
                                        <p class="text-muted mb-0 small">Aktifkan unit layanan terlebih dahulu.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Produk Populer -->
                @if(in_array(auth()->user()->role, ['admin_desa', 'admin_rt', 'admin_rw']))
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1 fw-semibold d-flex align-items-center">
                                            <span class="badge badge-center rounded-pill bg-label-warning me-2"
                                                style="width: 32px; height: 32px;">
                                                <i class="bx bx-star fs-5"></i>
                                            </span>
                                            Populer
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4">

                                    <!-- Product 2 - Sound System -->
                                    @forelse($popularProducts as $item)
                                    <div class="col-lg-3 col-md-6">
                                        <div class="card product-card h-100 border-0 shadow-sm" onclick="window.location.href='{{ $item->link }}'" style="cursor: pointer; border-radius: 16px;">
                                            <div class="card-body p-0 d-flex flex-column h-100">
                                                <!-- Product Image -->
                                                <div class="product-img-wrapper position-relative overflow-hidden" style="height: 220px; border-radius: 16px 16px 0 0;">
                                                    <img src="{{ Str::startsWith($item->image, ['http', 'https', 'User', 'Admin']) ? asset($item->image) : asset('storage/' . $item->image) }}"
                                                        alt="{{ $item->name }}" class="product-image w-100 h-100 object-fit-cover">
                                                    
                                                    <!-- Gradient Overlay -->
                                                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0) 40%, rgba(0,0,0,0.4) 100%); pointer-events: none;"></div>

                                                    @if($loop->iteration <= 2)
                                                    <div class="position-absolute top-0 end-0 m-3 shadow-sm" style="z-index: 2;">
                                                        <span class="badge bg-danger rounded-pill px-3 py-2 d-flex align-items-center" style="font-weight: 600; letter-spacing: 0.5px;">
                                                            <i class="bx bxs-hot fs-6 me-1 text-white"></i> HOT
                                                        </span>
                                                    </div>
                                                    @endif

                                                    <!-- Category Badge overlay -->
                                                    <div class="position-absolute bottom-0 start-0 m-3" style="z-index: 2;">
                                                        <span class="badge bg-white text-dark shadow-sm border px-3 py-2 rounded-pill" style="font-size: 0.75rem; font-weight: 700;">
                                                            <i class="bx bx-purchase-tag-alt text-primary me-1"></i> {{ strtoupper($item->category) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <!-- Product Info -->
                                                <div class="p-4 d-flex flex-column flex-grow-1 bg-white" style="border-radius: 0 0 16px 16px;">
                                                    <h5 class="mb-2 fw-bold text-dark text-truncate" title="{{ $item->name }}" style="line-height: 1.4;">{{ $item->name }}</h5>
                                                    
                                                    <div class="mt-auto mb-3 pt-2">
                                                        <span class="text-primary fw-bolder fs-5">{{ $item->price_formatted }}</span>
                                                        <span class="text-muted small fw-medium">
                                                            @if($item->type == 'rental')
                                                                / 24 jam
                                                            @elseif($item->type == 'mobil')
                                                                / Harian
                                                            @else
                                                                / Tabung
                                                            @endif
                                                        </span>
                                                    </div>

                                                    <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top border-light">
                                                        <div class="d-flex align-items-center text-muted">
                                                            <div class="d-flex align-items-center justify-content-center bg-light rounded-circle me-2" style="width: 28px; height: 28px;">
                                                                <i class="bx bx-box text-secondary" style="font-size: 14px;"></i>
                                                            </div>
                                                            <span class="small fw-medium">Stok: {{ $item->stock }}</span>
                                                        </div>
                                                        <div class="d-flex align-items-center text-muted">
                                                            <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle me-2" style="width: 28px; height: 28px;">
                                                                <i class="bx bx-check-shield" style="font-size: 14px;"></i>
                                                            </div>
                                                            <span class="small fw-medium text-primary">{{ $item->sold }} <span class="d-none d-xxl-inline">{{ $item->type == 'gas' ? 'Terjual' : 'Booking' }}</span></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="col-12 text-center py-5">
                                        <i class="bx bx-data text-muted fs-1 mb-3"></i>
                                        <p class="text-muted">Belum ada data produk populer untuk tahun ini.</p>
                                    </div>
                                    @endforelse

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Custom CSS untuk Product Cards -->
                <style>
                    .product-card {
                        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    }

                    .product-card:hover {
                        transform: translateY(-8px);
                        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.1) !important;
                    }

                    .product-img-wrapper {
                        transition: all 0.3s ease;
                    }

                    .product-card:hover .product-image {
                        transform: scale(1.08);
                    }

                    .product-image {
                        transition: transform 0.5s ease;
                    }

                    .badge-center {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }

                    .badge.bg-danger {
                        background-color: #dc3545 !important;
                    }

                    /* Responsive adjustments */
                    @media (max-width: 991px) {
                        .product-img-wrapper {
                            height: 180px !important;
                        }
                    }

                    @media (max-width: 767px) {
                        .product-img-wrapper {
                            height: 220px !important;
                        }
                    }
                </style>



        <!-- SCRIPT LANGSUNG DI SINI -->
        <script>
            // Tunggu sampai halaman selesai load
            window.addEventListener('load', function() {
                // Add delay to ensure layout is stable (prevent glitch when toast appears)
                setTimeout(function() {
                    console.log('Page loaded, initializing charts...');

                    // Cek apakah element ada
                    const chartElement = document.querySelector("#kinerjaChart");
                    console.log('Chart element:', chartElement);

                    if (!chartElement) {
                        console.error('Chart element not found!');
                        return;
                    }

                // ========================================
                // GRAFIK KINERJA BUMDES (AREA CHART) - REAL DATA
                // ========================================
                const kinerjaOptions = {
                    series: [{
                        name: 'Indeks Poin',
                        data: {!! json_encode($monthlyPerformance ?? [0,0,0,0,0,0,0,0,0,0,0,0]) !!}
                    }],
                    chart: {
                        height: 350,
                        type: 'area',
                        parentHeightOffset: 0,
                        toolbar: {
                            show: false
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    colors: ['#ffab00'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.45,
                            opacityTo: 0.05,
                            stops: [0, 90, 100]
                        }
                    },
                    xaxis: {
                        categories: {!! json_encode($monthNames ?? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']) !!},
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        },
                        labels: {
                            style: {
                                colors: '#a1acb8',
                                fontSize: '13px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#a1acb8',
                                fontSize: '13px'
                            }
                        }
                    },
                    grid: {
                        borderColor: '#eceef1',
                        strokeDashArray: 4,
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        padding: {
                            top: 0,
                            right: 5,
                            bottom: 0,
                            left: 5
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + ' Indeks Poin';
                            }
                        }
                    }
                };

                try {
                    const kinerjaChart = new ApexCharts(chartElement, kinerjaOptions);
                    kinerjaChart.render();
                    console.log('Kinerja chart rendered successfully!');
                } catch (error) {
                    console.error('Error rendering kinerja chart:', error);
                }

                // ========================================
                // PIE CHART TOTAL PENDAPATAN - REAL DATA
                // ========================================
                const pieContainer = document.querySelector("#pendapatanPieChart");
                if (pieContainer) {
                    @php
                        $pieSeries = [];
                        $pieLabels = [];
                        $pieColors = [];
                        $hexColors = [
                            'warning' => '#ffc107',
                            'primary' => '#696cff',
                            'info' => '#0dcaf0',
                            'success' => '#198754',
                            'danger' => '#dc3545',
                            'secondary' => '#8592a3'
                        ];
                        foreach($activeRevenueServices as $serviceName) {
                            $dataItem = $totalPendapatanData[$serviceName] ?? ['percentage' => 0, 'color' => 'secondary'];
                            $pieSeries[] = $dataItem['percentage'];
                            $pieLabels[] = $serviceName;
                            $pieColors[] = $hexColors[$dataItem['color']] ?? '#8592a3';
                        }
                    @endphp

                    const pieOptions = {
                        series: {!! json_encode($pieSeries) !!},
                        chart: {
                            type: 'pie',
                            height: 250 // slightly smaller to fit
                        },
                        labels: {!! json_encode($pieLabels) !!},
                        colors: {!! json_encode($pieColors) !!},

                        legend: {
                            show: false
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function(val) {
                                return val.toFixed(1) + '%';
                            },
                            style: {
                                fontSize: '12px',
                                fontWeight: 'bold',
                                colors: ['#fff']
                            },
                             dropShadow: { enabled: true }
                        },
                        tooltip: {
                             y: {
                                formatter: function(val) {
                                    return val.toFixed(1) + '%';
                                }
                            }
                        }
                    };
                    
                    try {
                        const pieChart = new ApexCharts(pieContainer, pieOptions);
                        pieChart.render();
                        console.log('Pie chart rendered successfully!');
                    } catch (error) {
                        console.error('Error rendering pie chart:', error);
                    }
                }

                // Handle select change
                const monthSelect = document.getElementById('pendapatan-month');
                if (monthSelect) {
                    monthSelect.addEventListener('change', function() {
                        const selectedMonth = this.value;
                        const url = new URL(window.location.href);
                        url.searchParams.set('month', selectedMonth);
                        url.searchParams.set('year', '{{ $totalPendapatanData['year'] ?? date('Y') }}');
                        window.location.href = url.toString();
                    });
                }


                // Donut Chart untuk Transaksi (Large centered chart)
                const orderChartElement = document.querySelector("#transactionDonutChart");
                if (orderChartElement) {
                    @php
                        $donutSeries = [];
                        $donutLabels = [];
                        $donutColors = [];
                        $totalDonut = 0;
                        
                        $countMap = [
                            'Penyewaan Alat' => ['count' => $rentalCount ?? 0, 'color' => '#ffc107'],
                            'Penjualan Gas' => ['count' => $gasCount ?? 0, 'color' => '#dc3545'],
                            'Penyewaan Mobil' => ['count' => $mobilCount ?? 0, 'color' => '#0dcaf0'],
                            'Fasilitas Umum' => ['count' => $fasilitasCount ?? 0, 'color' => '#198754'],
                            'Pasar Daerah' => ['count' => $pasarCount ?? 0, 'color' => '#696cff']
                        ];
                        
                        foreach($activeRevenueServices as $serviceName) {
                            $c = $countMap[$serviceName]['count'] ?? 0;
                            $donutSeries[] = $c;
                            $donutLabels[] = $serviceName . " (" . $c . ")";
                            $donutColors[] = $countMap[$serviceName]['color'] ?? '#8592a3';
                            $totalDonut += $c;
                        }
                    @endphp
                    var optionsOrder = {
                        series: {!! json_encode($donutSeries) !!},
                        chart: {
                            type: "donut",
                            width: "100%",
                            height: 350,
                            events: {
                                dataPointSelection: function(event, chartContext, config) {
                                    event.preventDefault();
                                }
                            }
                        },
                        labels: {!! json_encode($donutLabels) !!},
                        colors: {!! json_encode($donutColors) !!},
                        legend: {
                            show: true,
                            position: 'bottom',
                            horizontalAlign: 'center',
                            fontSize: '13px',
                            fontWeight: 500,
                            markers: {
                                width: 10,
                                height: 10,
                                radius: 12
                            },
                            itemMargin: {
                                horizontal: 10,
                                vertical: 6
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: "70%",
                                    labels: {
                                        show: true,
                                        name: {
                                            show: false
                                        },
                                        value: {
                                            show: true,
                                            fontSize: "30px",
                                            fontWeight: 600,
                                            color: "#5e5873",
                                            offsetY: 5,
                                            formatter: function() {
                                                return "{{ $totalDonut }}";
                                            },
                                        },
                                        total: {
                                            show: true,
                                            label: "{{ $selectedYear }}",
                                            fontSize: "16px",
                                            color: "#6e6b7b",
                                            offsetY: 25,
                                        },
                                    },
                                },
                            },
                        },
                        tooltip: {
                            enabled: true,
                            y: {
                                formatter: function(value, { seriesIndex, dataPointIndex, w }) {
                                    // Return empty string to hide the value, only show label
                                    return '';
                                },
                                title: {
                                    formatter: function(seriesName) {
                                        // Return only the label name without any value
                                        return seriesName;
                                    }
                                }
                            },
                            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                                // Custom tooltip to show only the label name
                                return '<div class="apexcharts-tooltip-custom" style="padding: 8px 12px; background: #fff; border: 1px solid #e3e3e3; border-radius: 4px;">' +
                                    '<span style="font-weight: 500; color: #333;">' + w.config.labels[seriesIndex] + '</span>' +
                                    '</div>';
                            }
                        },
                        states: {
                            active: {
                                filter: {
                                    type: 'none'
                                }
                            }
                        }
                    };

                    try {
                        var chartOrder = new ApexCharts(orderChartElement, optionsOrder);
                        chartOrder.render();
                        console.log('Order chart rendered successfully!');
                    } catch (error) {
                        console.error('Error rendering order chart:', error);
                    }
                } // End if orderChartElement
            }, 500); // End Timeout
            });
        </script>
    @endsection


