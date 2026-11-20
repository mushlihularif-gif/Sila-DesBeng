@extends('admin.layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<style>
    .dash-welcome-card {
        border-radius: 16px;
        border: 1px solid #eef1f6;
        background: #fff;
    }
    .dash-shortcut-tile {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.7rem 0.85rem;
        border-radius: 12px;
        border: 1px solid #eef1f6;
        background: #f9fafc;
        color: #384554;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .dash-shortcut-tile:hover {
        background: #f0f2ff;
        border-color: rgba(105, 108, 255, 0.35);
        color: #696cff;
        transform: translateY(-2px);
    }
    .dash-shortcut-icon {
        width: 34px;
        height: 34px;
        min-width: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
    .dash-unit-card {
        border-radius: 14px;
        border: 1px solid #eef1f6;
        background: #fff;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        cursor: pointer;
    }
    .dash-unit-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(67, 89, 113, 0.1) !important;
    }
    .dash-unit-avatar {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
    }
    .dash-unit-avatar img {
        width: 32px;
        height: 32px;
        object-fit: contain;
    }
    .notif-request-card {
        border-radius: 14px;
        border: 1px solid #eef2f6;
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(67, 89, 113, 0.04);
        transition: all 0.2s ease-in-out;
    }
    .notif-request-card:hover {
        border-color: #d2d8e0;
        box-shadow: 0 6px 18px rgba(67, 89, 113, 0.08);
    }
    .notif-request-avatar {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .notif-actions {
        width: 100%;
        padding-top: 10px;
        border-top: 1px solid #f1f4f8;
    }
    @media (min-width: 768px) {
        .notif-actions {
            width: auto;
            padding-top: 0;
            border-top: none;
        }
    }
    .product-card {
        border-radius: 16px;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px rgba(67, 89, 113, 0.12) !important;
    }
    .product-card .product-image {
        transition: transform 0.4s ease;
    }
    .product-card:hover .product-image {
        transform: scale(1.06);
    }
</style>

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
    if (in_array($role, ['super_admin'])) {
        $regionName = 'Pemerintah Kabupaten Bengkalis';
        $kinerjaTitle = 'Kinerja Tingkat Kabupaten';
        $pendapatanTitle = 'Pendapatan Layanan Kabupaten';
        $perbandinganTitle = 'Perbandingan Transaksi';
    } else {
        $name = \App\Models\Region::find(auth()->user()->region_id)->name ?? 'Desa';
        $regionName = 'Pemerintah ' . $name;
        $kinerjaTitle = 'Tren Aktivitas Layanan Desa';
        $pendapatanTitle = 'Pendapatan Unit Pelayanan';
        $perbandinganTitle = 'Perbandingan Transaksi';
    }

    $activeServicesCount = \App\Models\RegionService::where('region_id', auth()->user()->region_id)->where('is_active', true)->count();
    $totalServicesCount = \App\Models\Service::available()->count();
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Baris 1: Sambutan & Akses Cepat + Grafik Kinerja -->
    <div class="row g-4 mb-4 align-items-stretch">
        <div class="col-12 col-lg-5">
            <div class="card dash-welcome-card shadow-sm h-100">
                <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between gap-3">
                    <!-- Sambutan -->
                    <div>
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                            <div>
                                <h5 class="fw-bold text-primary mb-1">{{ $greeting }}, {{ explode(' ', Auth::user()->name ?? 'Administrator')[0] }} {!! $icon !!}</h5>
                                <p class="text-muted small mb-0">Sistem Pelayanan Digital <strong class="text-dark">{{ $regionName }}</strong></p>
                            </div>
                            <div class="avatar flex-shrink-0" style="width: 42px; height: 42px;">
                                <span class="avatar-initial rounded-3 bg-label-primary p-1 w-100 h-100">
                                    <img src="{{ asset('Admin/img/illustrations/logokab.webp') }}" alt="Logo Kab" style="width: 100%; height: 100%; object-fit: contain;">
                                </span>
                            </div>
                        </div>

                        <!-- Indikator Modul Layanan Aktif -->
                        <div class="d-flex align-items-center justify-content-between bg-label-primary rounded-3 px-3 py-2 mt-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bx bx-grid-alt text-primary fs-5"></i>
                                <span class="small fw-semibold text-primary">Modul Layanan Aktif</span>
                            </div>
                            <div class="fw-bold text-primary">
                                {{ $activeServicesCount }} <span class="fw-normal opacity-75">/ {{ $totalServicesCount }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Akses Cepat (Pengganti Banner Carousel yang Tidak Diperlukan) -->
                    @if(in_array($role, ['admin_desa']))
                    <div>
                        <small class="text-uppercase fw-bold text-muted d-block mb-2" style="font-size: 0.68rem; letter-spacing: 0.6px;">Akses Cepat Pengelolaan</small>
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="dash-shortcut-tile">
                                    <span class="dash-shortcut-icon bg-label-warning text-warning"><i class="bx bx-calendar-check"></i></span>
                                    <span class="small fw-semibold lh-sm">Permintaan Masuk</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('admin.aktivitas.bukti-transaksi.index') }}" class="dash-shortcut-tile">
                                    <span class="dash-shortcut-icon bg-label-success text-success"><i class="bx bx-check-shield"></i></span>
                                    <span class="small fw-semibold lh-sm">Bukti Bayar</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('admin.laporan.pendapatan') }}" class="dash-shortcut-tile">
                                    <span class="dash-shortcut-icon bg-label-primary text-primary"><i class="bx bx-line-chart"></i></span>
                                    <span class="small fw-semibold lh-sm">Pendapatan</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('admin.region-settings.payment') }}" class="dash-shortcut-tile">
                                    <span class="dash-shortcut-icon bg-label-info text-info"><i class="bx bx-credit-card"></i></span>
                                    <span class="small fw-semibold lh-sm">Pembayaran</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                        <a href="{{ route('admin.SiladesBeng.bumdes.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 flex-grow-1">
                            <i class="bx bx-building-house me-1"></i>Profil Desa
                        </a>
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 flex-grow-1">
                            <i class="bx bx-group me-1"></i>Staf Layanan
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Grafik Kinerja -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4 d-flex flex-column">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                        <div>
                            <h5 class="card-title fw-bold mb-1 fs-6">{{ $kinerjaTitle }}</h5>
                            <span class="badge bg-label-warning rounded-pill">Tahun {{ $selectedYear }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge bg-label-primary py-2 px-3 rounded-pill">Kecamatan Bengkalis</span>

                            @if(in_array($role, ['super_admin']))
                            <select class="form-select form-select-sm" id="desaSelect" style="width: auto; min-width: 150px;">
                                <option value="all" {{ empty($selectedDesaId) || $selectedDesaId == 'all' ? 'selected' : '' }}>Semua Desa</option>
                                @foreach($desaList ?? [] as $desa)
                                    <option value="{{ $desa->id }}" {{ $selectedDesaId == $desa->id ? 'selected' : '' }}>{{ $desa->name }}</option>
                                @endforeach
                            </select>
                            @endif

                            <select class="form-select form-select-sm" id="tahunSelect" style="width: auto; min-width: 90px;">
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex-grow-1 d-flex flex-column justify-content-center">
                        <div id="kinerjaChart" style="min-height: 250px; width: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateFilters() {
            let year = document.getElementById('tahunSelect').value;
            let desaSelect = document.getElementById('desaSelect');
            let url = "{{ route('admin.dashboard') }}?year=" + year;
            if (desaSelect && desaSelect.value !== 'all') {
                url += "&desa_id=" + desaSelect.value;
            }
            window.location.href = url;
        }
        document.getElementById('tahunSelect')?.addEventListener('change', updateFilters);
        document.getElementById('desaSelect')?.addEventListener('change', updateFilters);
    </script>

    <!-- Baris 2: Unit Layanan Aktif (Satu Grid Terpadu, Tanpa Celah Kosong) -->
    @php
        $unitConfigs = [
            'Penjualan Gas' => [
                'title' => 'Penjualan Gas',
                'category' => 'Belanja',
                'count' => ($unitGas ?? \App\Models\Gas::count() ?? 0),
                'label' => 'Jenis Tabung',
                'route' => route('admin.unit.penjualan_gas.index'),
                'image' => asset('User/img/elemen/F2.png'),
                'color' => 'danger'
            ],
            'Penyewaan Alat' => [
                'title' => 'Penyewaan Alat',
                'category' => 'Layanan Daerah',
                'count' => ($unitPenyewaan ?? \App\Models\Barang::count() ?? 0),
                'label' => 'Item',
                'route' => route('admin.unit.penyewaan.index'),
                'image' => asset('User/img/elemen/F1.png'),
                'color' => 'warning'
            ],
            'Penyewaan Mobil' => [
                'title' => 'Transportasi',
                'category' => 'Layanan Daerah',
                'count' => ($unitMobil ?? (\App\Models\Mobil::inCatalog()->forRental()->when(auth()->user() && auth()->user()->region_id, fn($q) => $q->where('region_id', auth()->user()->region_id))->count() ?? 0)),
                'label' => 'Kendaraan',
                'route' => route('admin.unit.mobil.index'),
                'image' => asset('User/img/elemen/mobil.png'),
                'color' => 'info'
            ],
            'Fasilitas Umum' => [
                'title' => 'Fasilitas Umum',
                'category' => 'Layanan Daerah',
                'count' => ($unitFasilitas ?? ((\App\Models\FasilitasUmum::inCatalog()->when(auth()->user() && auth()->user()->region_id, fn($q) => $q->where(fn($sub) => $sub->where('region_id', auth()->user()->region_id)->orWhereNull('region_id')))->count() ?? 0))),
                'label' => 'Fasilitas',
                'route' => route('admin.unit.fasilitas_umum.index'),
                'image' => asset('User/img/elemen/fasilitas.png'),
                'color' => 'success'
            ],
            'Pengumuman' => [
                'title' => 'Kabar & Info',
                'category' => 'Informasi Publik',
                'count' => (\App\Models\Announcement::count() ?? 0),
                'label' => 'Info',
                'route' => route('admin.announcements.index'),
                'image' => asset('User/img/elemen/KabardanInformasiDaerah.png'),
                'color' => 'primary'
            ],
        ];

        $activeServicesList = isset($activeServices) ? $activeServices : [];
        if ($role === 'staff') {
            if (auth()->user()->hasUnitPermission('kabar_informasi')) $activeServicesList[] = 'Pengumuman';
        } else {
            $activeServicesList = array_merge($activeServicesList, ['Pengumuman']);
        }
        $activeServicesList = array_values(array_unique($activeServicesList));

        $orderedActiveUnits = array_values(array_filter(array_keys($unitConfigs), fn($k) => in_array($k, $activeServicesList, true)));
    @endphp

    @if(in_array($role, ['admin_desa', 'admin_rt', 'admin_rw']) && count($orderedActiveUnits) > 0)
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="avatar avatar-sm bg-label-primary text-primary rounded-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-grid-alt fs-5"></i>
                </span>
                <div>
                    <h5 class="mb-0 fw-bold text-dark fs-6">Unit Layanan Desa</h5>
                    <small class="text-muted">Klik kartu untuk mengelola katalog masing-masing unit</small>
                </div>
            </div>
            @if($role === 'admin_desa')
            <a href="{{ route('admin.region-settings.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="bx bx-slider-alt me-1"></i>Kelola Aktivasi
            </a>
            @endif
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="row g-3 row-cols-2 row-cols-md-3 row-cols-xl-{{ min(count($orderedActiveUnits), 5) }}">
                @foreach($orderedActiveUnits as $serviceName)
                    @php $config = $unitConfigs[$serviceName]; @endphp
                    <div class="col">
                        <div class="dash-unit-card h-100 p-3 d-flex flex-column justify-content-between" style="border-top: 3px solid var(--bs-{{ $config['color'] }});"
                             onclick="window.location='{{ $config['route'] }}'">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                <div class="dash-unit-avatar bg-label-{{ $config['color'] }}">
                                    <img src="{{ $config['image'] }}" alt="{{ $config['title'] }}">
                                </div>
                                <span class="badge bg-label-{{ $config['color'] }} rounded-pill" style="font-size: 0.65rem;">{{ $config['category'] }}</span>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark small mb-1">{{ $config['title'] }}</div>
                                <div class="d-flex align-items-baseline gap-1">
                                    <h4 class="fw-bold text-{{ $config['color'] }} mb-0">
                                        <span class="count-up" data-value="{{ $config['count'] }}">{{ $config['count'] }}</span>
                                    </h4>
                                    <small class="text-muted">{{ $config['label'] }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Baris 3: Notifikasi Permintaan Terbaru -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-label-primary text-primary rounded-3 d-flex align-items-center justify-content-center">
                            <i class="bx bx-bell fs-5"></i>
                        </span>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark fs-6">Permintaan Terbaru</h5>
                            <small class="text-muted">Pesanan &amp; pengajuan warga yang perlu ditindaklanjuti</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                        Lihat Semua <i class="bx bx-right-arrow-alt ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-3 p-md-4" style="background-color: #fbfcfd;">
                    @forelse($latestRequests as $request)
                        @php
                            $rIcon = 'bx-bell';
                            $bgClass = 'bg-label-primary text-primary';
                            $badgeClass = 'bg-label-primary';
                            $serviceName = 'Umum';
                            $detailLink = route('admin.aktivitas.permintaan-pengajuan.show', [$request->id, $request->type ?? 'rental']);
                            $canQuickAction = in_array($request->type, ['rental', 'gas', 'mobil', 'fasilitas_umum']);

                            if ($request->type == 'rental') {
                                $rIcon = 'bx-wrench'; $bgClass = 'bg-label-warning text-warning'; $badgeClass = 'bg-label-warning'; $serviceName = 'Penyewaan Alat';
                            } elseif ($request->type == 'gas') {
                                $rIcon = 'bxs-gas-pump'; $bgClass = 'bg-label-danger text-danger'; $badgeClass = 'bg-label-danger'; $serviceName = 'Penjualan Gas';
                            } elseif ($request->type == 'mobil') {
                                $rIcon = 'bx-car'; $bgClass = 'bg-label-info text-info'; $badgeClass = 'bg-label-info'; $serviceName = 'Transportasi';
                            } elseif ($request->type == 'fasilitas_umum') {
                                $rIcon = 'bx-building-house'; $bgClass = 'bg-label-success text-success'; $badgeClass = 'bg-label-success'; $serviceName = 'Fasilitas Umum';
                            } elseif ($request->type == 'kyc') {
                                $rIcon = 'bx-id-card'; $bgClass = 'bg-label-info text-info'; $badgeClass = 'bg-label-info'; $serviceName = 'Verifikasi Identitas';
                                $detailLink = route('admin.kyc.show', $request->id);
                            } elseif ($request->type == 'mutasi') {
                                $rIcon = 'bx-user-pin'; $bgClass = 'bg-label-secondary text-secondary'; $badgeClass = 'bg-label-secondary'; $serviceName = 'Mutasi Penduduk';
                                $detailLink = route('admin.warga.mutasi.index');
                            }

                            $requestName = $request->full_name ?? $request->recipient_name ?? $request->name_from_ocr ?? $request->user->name ?? 'Warga';
                        @endphp

                        <div class="notif-request-card p-3 {{ !$loop->last ? 'mb-3' : '' }}">
                            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                                <div class="d-flex align-items-start gap-3 min-w-0 flex-grow-1">
                                    <div class="notif-request-avatar {{ $bgClass }}">
                                        <i class="bx {{ $rIcon }} fs-4"></i>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                            <h6 class="mb-0 fw-bold text-dark text-break" style="font-size: 0.92rem;">{{ $request->item_name }}</h6>
                                            <span class="badge {{ $badgeClass }} rounded-pill px-2 py-1" style="font-size: 0.68rem;">{{ strtoupper($serviceName) }}</span>
                                            @if(isset($request->cancellation_status) && $request->cancellation_status == 'pending')
                                                <span class="badge bg-label-danger rounded-pill px-2 py-1" style="font-size: 0.68rem;">
                                                    <i class="bx bx-error-circle me-1"></i>MINTA BATAL
                                                </span>
                                            @endif
                                        </div>
                                        <div class="d-flex flex-wrap gap-3 text-muted small">
                                            <span><i class="bx bx-user me-1"></i>{{ $requestName }}</span>
                                            <span><i class="bx bx-time-five me-1"></i>{{ $request->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="notif-actions d-flex flex-wrap align-items-center justify-content-end gap-2 flex-shrink-0">
                                    <a href="{{ $detailLink }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                        Detail <i class="bx bx-right-arrow-alt ms-1"></i>
                                    </a>
                                    @if($canQuickAction)
                                        @if(isset($request->cancellation_status) && $request->cancellation_status == 'pending')
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" onclick="handleCancellation({{ $request->id }}, '{{ $request->type }}', 'approve')">
                                                <i class="bx bx-check me-1"></i>Setujui Batal
                                            </button>
                                        @elseif($request->status == 'pending' || $request->status == 'waiting')
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold" onclick="approveRequest({{ $request->id }}, '{{ $request->type }}')">
                                                <i class="bx bx-check me-1"></i>Proses
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-5 px-3 text-center bg-white rounded-3 border">
                            <div class="bg-label-primary rounded-circle d-inline-flex p-3 mb-3">
                                <i class="bx bx-bell-off fs-2 text-primary"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Belum Ada Permintaan Baru</h6>
                            <p class="text-muted mb-0 small">Saat ini tidak ada permintaan layanan yang menunggu diproses.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Baris 4: Statistik Pendapatan & Perbandingan Transaksi -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="card-title fw-bold mb-0 fs-6 text-dark">{{ $pendapatanTitle }}</h5>
                    <select id="pendapatan-month" class="form-select form-select-sm bg-light border" style="width: auto;">
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $month)
                            <option value="{{ $index + 1 }}" {{ ($index + 1) == ($totalPendapatanData['month'] ?? date('m')) ? 'selected' : '' }}>
                                {{ $month }} {{ $totalPendapatanData['year'] ?? date('Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="card-body p-3 p-md-4">
                    @php
                        $revenueServices = ['Penyewaan Alat', 'Penjualan Gas', 'Penyewaan Mobil', 'Fasilitas Umum'];
                        $activeRevenueServices = array_intersect($activeServicesList, $revenueServices);
                    @endphp
                    @if(count($activeRevenueServices) > 0)
                        <div class="row g-4 h-100 align-items-center">
                            <div class="col-12 col-md-7">
                                @foreach($activeRevenueServices as $serviceName)
                                    @php
                                        $dataItem = $totalPendapatanData[$serviceName] ?? ['revenue' => 0, 'transactions' => 0, 'percentage' => 0, 'color' => 'secondary'];
                                    @endphp
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1 small">
                                            <span class="fw-semibold text-dark">{{ $serviceName === 'Penyewaan Mobil' ? 'Penyewaan Transportasi' : $serviceName }}</span>
                                            <span class="fw-bold text-dark">Rp <span class="count-up-rupiah" data-value="{{ $dataItem['revenue'] }}">{{ number_format($dataItem['revenue'], 0, ',', '.') }}</span></span>
                                        </div>
                                        <div class="progress mb-1" style="height: 7px;">
                                            <div class="progress-bar bg-{{ $dataItem['color'] }}" role="progressbar" style="width: {{ $dataItem['percentage'] }}%"></div>
                                        </div>
                                        <small class="text-muted"><span class="count-up" data-value="{{ $dataItem['transactions'] }}">{{ $dataItem['transactions'] }}</span> Transaksi</small>
                                    </div>
                                @endforeach

                                <div class="pt-3 border-top mt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold">Total Bulan Ini</h6>
                                        <h6 class="mb-0 fw-bold text-primary">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['total']['revenue'] ?? 0 }}">{{ number_format($totalPendapatanData['total']['revenue'] ?? 0, 0, ',', '.') }}</span></h6>
                                    </div>
                                    <small class="text-muted"><span class="count-up" data-value="{{ $totalPendapatanData['total']['transactions'] ?? 0 }}">{{ $totalPendapatanData['total']['transactions'] ?? 0 }}</span> Transaksi</small>
                                </div>
                            </div>

                            <div class="col-12 col-md-5 d-flex align-items-center justify-content-center">
                                <div id="pendapatanPieChart" style="width: 100%;"></div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-store-alt text-muted fs-1 mb-2"></i>
                            <h6 class="fw-bold mb-1">Data Belum Tersedia</h6>
                            <p class="text-muted mb-0 small">Aktifkan unit layanan terlebih dahulu di menu Aktivasi &amp; Katalog Unit.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
                    <h5 class="card-title fw-bold mb-0 fs-6">{{ $perbandinganTitle }}</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center p-3" style="min-height: 280px;">
                    @if(count($activeRevenueServices) > 0)
                        <div id="transactionDonutChart" style="width: 100%;"></div>
                    @else
                        <div class="text-center">
                            <i class="bx bx-pie-chart-alt text-muted fs-1 mb-2"></i>
                            <h6 class="fw-bold mb-1">Grafik Belum Tersedia</h6>
                            <p class="text-muted mb-0 small">Aktifkan unit layanan terlebih dahulu.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Baris 5: Produk & Layanan Populer -->
    @if(in_array($role, ['admin_desa', 'admin_rt', 'admin_rw']))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex align-items-center gap-2">
                    <span class="avatar avatar-sm bg-label-warning text-warning rounded-3 d-flex align-items-center justify-content-center">
                        <i class="bx bx-star fs-5"></i>
                    </span>
                    <div>
                        <h5 class="mb-0 fw-bold fs-6">Layanan Paling Diminati</h5>
                        <small class="text-muted">Item terpopuler tahun {{ $selectedYear }}</small>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3 g-md-4">
                        @forelse($popularProducts as $item)
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card product-card h-100 border shadow-sm overflow-hidden" onclick="window.location.href='{{ $item->link }}'" style="cursor: pointer;">
                                <div class="position-relative overflow-hidden" style="height: 190px;">
                                    <img src="{{ Str::startsWith($item->image, ['http', 'https', 'User', 'Admin']) ? asset($item->image) : asset('storage/' . $item->image) }}"
                                         alt="{{ $item->name }}" class="product-image w-100 h-100 object-fit-cover">
                                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0.08) 0%, rgba(0,0,0,0) 45%, rgba(0,0,0,0.45) 100%); pointer-events: none;"></div>
                                    @if($loop->iteration <= 2)
                                    <span class="badge bg-danger rounded-pill px-2 py-1 position-absolute top-0 end-0 m-2 shadow-sm" style="font-size: 0.7rem;">
                                        <i class="bx bxs-hot me-1"></i>POPULER
                                    </span>
                                    @endif
                                    <span class="badge bg-white text-dark shadow-sm px-2 py-1 rounded-pill position-absolute bottom-0 start-0 m-2" style="font-size: 0.7rem;">
                                        {{ strtoupper($item->category) }}
                                    </span>
                                </div>
                                <div class="p-3 d-flex flex-column flex-grow-1 bg-white">
                                    <h6 class="mb-2 fw-bold text-dark text-truncate" title="{{ $item->name }}">{{ $item->name }}</h6>
                                    <div class="mb-3">
                                        <span class="text-primary fw-bold fs-6">{{ $item->price_formatted }}</span>
                                        <span class="text-muted small">
                                            @if($item->type == 'rental') / 24 jam
                                            @elseif($item->type == 'mobil') / Harian
                                            @else / Tabung @endif
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top small text-muted">
                                        <span><i class="bx bx-box me-1"></i>Stok: {{ $item->stock }}</span>
                                        <span class="fw-semibold text-primary"><i class="bx bx-check-shield me-1"></i>{{ $item->sold }} {{ $item->type == 'gas' ? 'Terjual' : 'Booking' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center py-5">
                            <i class="bx bx-data text-muted fs-1 mb-2"></i>
                            <p class="text-muted mb-0 small">Belum ada data produk populer untuk tahun ini.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal Tolak -->
@push('modals')
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold fs-6">Tolak Permintaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-0">
                        <label class="form-label fw-semibold small text-dark">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" placeholder="Jelaskan alasan penolakan permintaan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Tolak Permintaan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

<script>
    function handleCancellation(id, type, action) {
        if (action !== 'approve') {
            Swal.fire({
                title: 'Tolak Pembatalan',
                input: 'textarea',
                inputLabel: 'Alasan Penolakan',
                inputPlaceholder: 'Jelaskan kenapa pembatalan ditolak...',
                showCancelButton: true,
                confirmButtonText: 'Tolak Pembatalan',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) return 'Anda harus menuliskan alasan penolakan!';
                }
            }).then((result) => {
                if (result.isConfirmed) submitCancellationResponse(id, type, action, result.value);
            });
            return;
        }

        Swal.fire({
            title: 'Setujui Pembatalan?',
            text: "Pesanan akan dibatalkan sesuai permintaan pengguna.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Setujui Pembatalan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) submitCancellationResponse(id, type, action, null);
        });
    }

    function submitCancellationResponse(id, type, action, reason) {
        Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });
        let url = `{{ url('admin/aktivitas/permintaan-pengajuan') }}/${type}/${id}/cancellation/${action}`;
        let body = { _token: '{{ csrf_token() }}' };
        if (reason) body.admin_response = reason;

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(body)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
            else Swal.fire('Gagal', data.message, 'error');
        })
        .catch(() => Swal.fire('Error', 'Terjadi kesalahan sistem', 'error'));
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
                Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });
                fetch(`{{ url('admin/aktivitas/permintaan-pengajuan') }}/${id}/${type}/approve`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                    else Swal.fire('Gagal', data.message, 'error');
                })
                .catch(() => Swal.fire('Error', 'Terjadi kesalahan sistem', 'error'));
            }
        });
    }

    function rejectRequest(id, type) {
        const modalEl = document.getElementById('rejectModal');
        if (modalEl) {
            document.getElementById('rejectForm').action = `{{ url('admin/aktivitas/permintaan-pengajuan') }}/${id}/${type}/reject`;
            (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
        }
    }

    window.addEventListener('load', function() {
        setTimeout(function() {
            // 1. GRAFIK KINERJA
            const chartElement = document.querySelector("#kinerjaChart");
            if (chartElement && typeof ApexCharts !== 'undefined') {
                const perfData = {!! json_encode($monthlyPerformance ?? [0,0,0,0,0,0,0,0,0,0,0,0]) !!}.map(Number);
                const maxPerf = Math.max(0, ...perfData);

                new ApexCharts(chartElement, {
                    series: [{ name: 'Aktivitas', data: perfData }],
                    chart: { height: 270, type: 'area', parentHeightOffset: 0, toolbar: { show: false } },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 },
                    colors: ['#696cff'],
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] } },
                    xaxis: {
                        categories: {!! json_encode($monthNames ?? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']) !!},
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: { style: { colors: '#8592a3', fontSize: '11px' } }
                    },
                    yaxis: {
                        min: 0,
                        max: maxPerf > 0 ? undefined : 5,
                        tickAmount: 5,
                        forceNiceScale: maxPerf > 0,
                        labels: {
                            style: { colors: '#8592a3', fontSize: '11px' },
                            formatter: (val) => Number.isFinite(val) ? Math.round(val) : '0'
                        }
                    },
                    grid: { borderColor: '#eceef1', strokeDashArray: 4 },
                    tooltip: { y: { formatter: (val) => val + ' Aktivitas' } }
                }).render();
            }

            // 2. PIE CHART PENDAPATAN
            const pieContainer = document.querySelector("#pendapatanPieChart");
            if (pieContainer && typeof ApexCharts !== 'undefined') {
                @php
                    $pieSeries = [];
                    $pieLabels = [];
                    $pieColors = [];
                    $hexColors = [
                        'warning' => '#ffab00', 'primary' => '#696cff', 'info' => '#03c3ec',
                        'success' => '#71dd37', 'danger' => '#ff3e1d', 'secondary' => '#8592a3'
                    ];
                    foreach ($activeRevenueServices ?? [] as $serviceName) {
                        $dataItem = $totalPendapatanData[$serviceName] ?? ['percentage' => 0, 'color' => 'secondary'];
                        $pieSeries[] = (float) ($dataItem['percentage'] ?? 0);
                        $pieLabels[] = $serviceName === 'Penyewaan Mobil' ? 'Transportasi' : $serviceName;
                        $pieColors[] = $hexColors[$dataItem['color']] ?? '#8592a3';
                    }
                @endphp
                const pieSeries = {!! json_encode($pieSeries) !!};
                const pieTotal = pieSeries.reduce((a, b) => a + b, 0);

                if (pieTotal <= 0) {
                    pieContainer.innerHTML = '<div class="text-center py-4"><i class="bx bx-pie-chart-alt fs-1 text-muted opacity-25 d-block mb-1"></i><small class="text-muted">Belum ada pendapatan bulan ini</small></div>';
                } else {
                    new ApexCharts(pieContainer, {
                        series: pieSeries,
                        chart: { type: 'donut', height: 230 },
                        labels: {!! json_encode($pieLabels) !!},
                        colors: {!! json_encode($pieColors) !!},
                        legend: { show: false },
                        dataLabels: { enabled: true, formatter: (val) => val.toFixed(0) + '%' },
                        tooltip: { y: { formatter: (val) => val.toFixed(1) + '%' } }
                    }).render();
                }
            }

            // Month selector
            document.getElementById('pendapatan-month')?.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('month', this.value);
                url.searchParams.set('year', '{{ $totalPendapatanData['year'] ?? date('Y') }}');
                window.location.href = url.toString();
            });

            // 3. DONUT CHART TRANSAKSI
            const orderChartElement = document.querySelector("#transactionDonutChart");
            if (orderChartElement && typeof ApexCharts !== 'undefined') {
                @php
                    $donutSeries = [];
                    $donutLabels = [];
                    $donutColors = [];
                    $totalDonut = 0;
                    $countMap = [
                        'Penyewaan Alat' => ['count' => $rentalCount ?? 0, 'color' => '#ffab00'],
                        'Penjualan Gas' => ['count' => $gasCount ?? 0, 'color' => '#ff3e1d'],
                        'Penyewaan Mobil' => ['count' => $mobilCount ?? 0, 'color' => '#03c3ec'],
                        'Fasilitas Umum' => ['count' => $fasilitasCount ?? 0, 'color' => '#71dd37'],
                    ];
                    foreach ($activeRevenueServices ?? [] as $serviceName) {
                        $c = (int) ($countMap[$serviceName]['count'] ?? 0);
                        $donutSeries[] = $c;
                        $displayLabel = $serviceName === 'Penyewaan Mobil' ? 'Transportasi' : $serviceName;
                        $donutLabels[] = $displayLabel . ' (' . $c . ')';
                        $donutColors[] = $countMap[$serviceName]['color'] ?? '#8592a3';
                        $totalDonut += $c;
                    }
                @endphp
                const totalDonut = {{ $totalDonut }};
                if (totalDonut <= 0) {
                    orderChartElement.innerHTML = '<div class="text-center py-4"><i class="bx bx-doughnut-chart fs-1 text-muted opacity-25 d-block mb-1"></i><small class="text-muted">Belum ada transaksi tahun {{ $selectedYear }}</small></div>';
                } else {
                    new ApexCharts(orderChartElement, {
                        series: {!! json_encode($donutSeries) !!},
                        chart: { type: "donut", width: "100%", height: 300 },
                        labels: {!! json_encode($donutLabels) !!},
                        colors: {!! json_encode($donutColors) !!},
                        legend: { show: true, position: 'bottom', fontSize: '12px' },
                        dataLabels: { enabled: false },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: "68%",
                                    labels: {
                                        show: true,
                                        total: {
                                            show: true,
                                            label: "Tahun {{ $selectedYear }}",
                                            formatter: () => "{{ $totalDonut }}"
                                        }
                                    }
                                }
                            }
                        }
                    }).render();
                }
            }
        }, 250);
    });
</script>
@endsection
