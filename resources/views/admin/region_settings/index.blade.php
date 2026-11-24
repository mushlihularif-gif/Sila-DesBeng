@extends('admin.layouts.admin')

@section('title', 'Aktivasi & Katalog Unit Layanan')

@section('page-title', 'Aktivasi & Katalog Unit Layanan')

@section('content')
<style>
    .rs-stat-card {
        border-radius: 16px;
        border: 1px solid #eef1f6;
        background: #fff;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .rs-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(67, 89, 113, 0.08) !important;
    }
    .rs-service-card {
        border-radius: 16px;
        border: 1px solid #e9edf4;
        background: #ffffff;
        transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.2s ease, border-color 0.2s ease;
        overflow: hidden;
    }
    .rs-service-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(67, 89, 113, 0.08) !important;
    }
    .rs-service-card.is-off {
        opacity: 0.72;
        background: #fafbfd;
    }
    .rs-access-box {
        background: #f8fafc;
        border: 1px solid #edf1f7;
        border-radius: 12px;
        padding: 0.75rem 0.9rem;
    }
    .rs-delivery-option {
        background: #f8fafc;
        border: 1px solid #e9edf4;
        border-radius: 14px;
        padding: 0.95rem 1rem;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }
    .rs-delivery-option.is-active {
        background: #f5f7ff;
        border-color: rgba(105, 108, 255, 0.35);
    }
    .form-check-input.public-access-toggle:checked {
        background-color: #03c3ec !important;
        border-color: #03c3ec !important;
    }
    .rs-nav-pills {
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch !important;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .rs-nav-pills::-webkit-scrollbar {
        display: none;
    }
    @media (max-width: 575.98px) {
        .rs-stat-card {
            padding: 0.65rem 0.4rem !important;
        }
        .rs-stat-card > span {
            font-size: 0.66rem !important;
            line-height: 1.2 !important;
        }
        .rs-stat-card h4 {
            font-size: 1.15rem !important;
            margin-top: 2px;
        }
        .rs-nav-pills .nav-link {
            padding: 0.45rem 0.85rem !important;
            font-size: 0.8rem !important;
        }
    }
</style>

@php
    $operationalServices = $allServices->filter(fn($s) => strpos(strtolower($s->name), 'pengumuman') === false);
    $publicServices = $allServices->filter(fn($s) => strpos(strtolower($s->name), 'pengumuman') !== false);

    $totalOperational = $operationalServices->count();
    $publicAccessServices = $publicAccessServices ?? [];
    $activeOperationalCount = $operationalServices->filter(fn($s) => in_array($s->id, $activeServices))->count();
    $exclusiveOperationalCount = $operationalServices->filter(fn($s) => in_array($s->id, $activeServices) && !in_array($s->id, $publicAccessServices))->count();
    $isWaActive = !empty($region->payment_info['whatsapp_active']);

    $alatAntarActive = isset($region->payment_info['alat_delivery_antar_active']) ? (bool) $region->payment_info['alat_delivery_antar_active'] : true;
    $alatJemputActive = isset($region->payment_info['alat_delivery_jemput_active']) ? (bool) $region->payment_info['alat_delivery_jemput_active'] : true;
    $gasAntarActive = isset($region->payment_info['gas_delivery_antar_active']) ? (bool) $region->payment_info['gas_delivery_antar_active'] : true;
    $gasJemputActive = isset($region->payment_info['gas_delivery_jemput_active']) ? (bool) $region->payment_info['gas_delivery_jemput_active'] : true;
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div class="min-w-0">
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Unit Layanan /</span> Aktivasi &amp; Katalog Unit
            </h4>
            <div class="d-flex flex-wrap align-items-center gap-1.5 text-muted small">
                <span class="d-inline-flex align-items-center fw-semibold text-dark">
                    <i class="bx bx-map-pin text-primary me-1"></i>{{ $region->name }}
                </span>
                @if($region->parent)
                    <span>&middot;</span>
                    <span>{{ $region->parent->name }}</span>
                @endif
                @if($region->parent && $region->parent->parent)
                    <span>&middot;</span>
                    <span>{{ $region->parent->parent->name }}</span>
                @endif
            </div>
        </div>
        <button type="submit" form="region-settings-form" class="btn btn-primary rounded-pill px-4 shadow-sm w-100 w-sm-auto flex-shrink-0 d-inline-flex align-items-center justify-content-center gap-1.5">
            <i class="bx bx-save fs-5"></i>
            <span>Simpan Pengaturan</span>
        </button>
    </div>

    <!-- Flash Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-check-circle fs-4 me-2 flex-shrink-0"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-error-circle fs-4 me-2 flex-shrink-0"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Info Banner + Live KPI Strip -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card bg-label-primary border-0 shadow-none rounded-4 h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                        <i class="bx bx-slider-alt fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Kelola Unit Usaha &amp; Pengaturan Desa</h6>
                        <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.55;">
                            Secara bawaan, unit layanan aktif hanya diperuntukkan bagi <strong>warga desa Anda sendiri</strong>. Geser sakelar <strong>Izinkan Desa Lain</strong> bila ingin membuka layanan ke desa lain, atur opsi pengantaran, lalu klik <strong>Simpan Pengaturan</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="row g-2 h-100">
                <div class="col-4">
                    <div class="rs-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Unit Aktif</span>
                        <h4 class="fw-bold text-primary mb-0">
                            <span id="liveActiveUnitsCount">{{ $activeOperationalCount }}</span><span class="text-muted fw-normal fs-6">/{{ $totalOperational }}</span>
                        </h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="rs-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Eksklusif Desa</span>
                        <h4 class="fw-bold text-warning mb-0" id="liveExclusiveUnitsCount">{{ $exclusiveOperationalCount }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="rs-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Chat WhatsApp</span>
                        <h4 class="fw-bold mb-0 {{ $isWaActive ? 'text-success' : 'text-secondary' }}" id="liveWaStatusText" style="font-size: 1.1rem;">
                            {{ $isWaActive ? 'Aktif' : 'Nonaktif' }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.region-settings.update') }}" method="POST" id="region-settings-form">
        @csrf
        <input type="hidden" name="access_toggle_mode" value="public">
        {{-- Hidden input untuk kompatibilitas validasi controller Transportasi --}}
        <input type="hidden" name="mobil_rental_delivery_antar_active" value="1">
        <input type="hidden" name="mobil_rental_delivery_jemput_active" value="1">

        <!-- Navigation Tabs (3 Tab Jelas & Tidak Membingungkan) -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-2.5 p-sm-3">
                <ul class="nav nav-pills rs-nav-pills gap-2 flex-nowrap overflow-x-auto mb-0" role="tablist" style="-webkit-overflow-scrolling: touch;">
                    <li class="nav-item flex-shrink-0" role="presentation">
                        <button type="button" class="nav-link active rounded-pill px-3 px-sm-4 py-2 fw-semibold d-inline-flex align-items-center text-nowrap" role="tab" data-bs-toggle="tab" data-bs-target="#tab-unit" aria-selected="true" style="font-size: 0.85rem;">
                            <i class="bx bx-grid-alt me-1.5"></i>
                            <span>Aktivasi Unit Layanan</span>
                        </button>
                    </li>
                    <li class="nav-item flex-shrink-0" role="presentation">
                        <button type="button" class="nav-link rounded-pill px-3 px-sm-4 py-2 fw-semibold d-inline-flex align-items-center text-nowrap" role="tab" data-bs-toggle="tab" data-bs-target="#tab-pengiriman" aria-selected="false" style="font-size: 0.85rem;">
                            <i class="bx bx-truck me-1.5"></i>
                            <span>Metode Antar &amp; Ambil</span>
                        </button>
                    </li>
                    <li class="nav-item flex-shrink-0" role="presentation">
                        <button type="button" class="nav-link rounded-pill px-3 px-sm-4 py-2 fw-semibold d-inline-flex align-items-center text-nowrap" role="tab" data-bs-toggle="tab" data-bs-target="#tab-kontak" aria-selected="false" style="font-size: 0.85rem;">
                            <i class="bx bxl-whatsapp me-1.5"></i>
                            <span>Kontak WhatsApp</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content p-0 shadow-none bg-transparent">
            <!-- ========================================================= -->
            <!-- TAB 1: AKTIVASI UNIT LAYANAN                              -->
            <!-- ========================================================= -->
            <div class="tab-pane fade show active" id="tab-unit" role="tabpanel">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bx bx-store-alt text-primary fs-5"></i>
                            <span>Unit Layanan Mandiri Desa</span>
                        </h6>
                        <small class="text-muted">Geser sakelar untuk menampilkan/menyembunyikan layanan di aplikasi warga.</small>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    @foreach($operationalServices as $service)
                        @php
                            $iconPath = 'User/img/elemen/fasilitas.png';
                            $descText = 'yang dapat mengakses layanan ini.';
                            $accentColor = '#71dd37';
                            $sName = strtolower($service->name);
                            $displayName = $service->name === 'Penyewaan Mobil' ? 'Penyewaan Transportasi' : $service->name;
                            $serviceTypeKey = 'other';

                            if (strpos($sName, 'mobil') !== false || strpos($sName, 'transportasi') !== false) {
                                $iconPath = 'User/img/elemen/mobil.png';
                                $descText = 'yang dapat menyewa armada transportasi desa ini.';
                                $accentColor = '#03c3ec';
                                $serviceTypeKey = 'mobil';
                            } elseif (strpos($sName, 'alat') !== false) {
                                $iconPath = 'User/img/elemen/F1.png';
                                $descText = 'yang dapat meminjam atau menyewa alat desa ini.';
                                $accentColor = '#ffab00';
                                $serviceTypeKey = 'alat';
                            } elseif (strpos($sName, 'gas') !== false) {
                                $iconPath = 'User/img/elemen/F2.png';
                                $descText = 'yang dapat memesan tabung gas LPG desa ini.';
                                $accentColor = '#ff3e1d';
                                $serviceTypeKey = 'gas';
                            } elseif (strpos($sName, 'fasilitas') !== false) {
                                $iconPath = 'User/img/elemen/fasilitas.png';
                                $descText = 'yang dapat meminjam atau menyewa fasilitas umum ini.';
                                $accentColor = '#71dd37';
                                $serviceTypeKey = 'fasilitas';
                            }

                            $isActive = in_array($service->id, $activeServices);
                            $isPublic = in_array($service->id, $publicAccessServices);
                            $isExclusive = ! $isPublic;
                        @endphp
                        <div class="col-12 col-md-6">
                            <div class="card rs-service-card shadow-sm h-100 card-service-item {{ $isActive ? '' : 'is-off' }}"
                                data-action="{{ $descText }}"
                                data-service-type="{{ $serviceTypeKey }}"
                                style="border-left: 4px solid {{ $accentColor }} !important;">
                                <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                                    <!-- Top Row: Icon, Name, Badge & Master Switch -->
                                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                        <div class="d-flex align-items-center gap-3 min-w-0">
                                            <div class="rounded-3 p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #f8fafc; border: 1px solid #eef1f6;">
                                                <img src="{{ asset($iconPath) }}" alt="{{ $displayName }}" style="width: 32px; height: 32px; object-fit: contain;">
                                            </div>
                                            <div class="min-w-0">
                                                <h6 class="fw-bold text-dark mb-1 text-truncate">{{ $displayName }}</h6>
                                                <span class="badge {{ $isActive ? 'bg-label-success text-success' : 'bg-label-secondary text-secondary' }} rounded-pill px-2.5 py-1 status-badge-pill" style="font-size: 0.72rem;">
                                                    <i class="bx {{ $isActive ? 'bx-check-circle' : 'bx-hide' }} me-1"></i><span class="status-badge-text">{{ $isActive ? 'Tampil di Warga' : 'Disembunyikan' }}</span>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="form-check form-switch mb-0 flex-shrink-0">
                                            <input type="checkbox"
                                                name="services[]"
                                                value="{{ $service->id }}"
                                                class="form-check-input service-main-toggle"
                                                data-service-type="{{ $serviceTypeKey }}"
                                                style="cursor: pointer; transform: scale(1.25);"
                                                {{ $isActive ? 'checked' : '' }}
                                                title="Aktifkan atau Nonaktifkan {{ $displayName }}">
                                        </div>
                                    </div>

                                    <!-- Bottom Box: Jangkauan Akses (Default Khusus Warga Desa, ON = Izinkan Desa Lain) -->
                                    <div class="rs-access-box mt-auto">
                                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1.5">
                                            <div class="d-flex align-items-center gap-1.5 min-w-0">
                                                <i class="bx {{ $isPublic ? 'bx-world text-info' : 'bx-shield-quarter text-warning' }} fs-5 flex-shrink-0 access-status-icon"></i>
                                                <span class="fw-semibold text-dark small text-truncate">Izinkan Desa Lain</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                                <span class="badge {{ $isPublic ? 'bg-label-info text-info' : 'bg-label-warning text-warning' }} rounded-pill px-2 py-1 fw-semibold exclusive-badge-pill" style="font-size: 0.7rem;">
                                                    {{ $isPublic ? 'Terbuka Desa Lain' : 'Khusus Warga Desa' }}
                                                </span>
                                                <div class="form-check form-switch mb-0">
                                                    <input type="checkbox"
                                                        name="public_services[]"
                                                        value="{{ $service->id }}"
                                                        class="form-check-input public-access-toggle"
                                                        style="cursor: pointer;"
                                                        {{ $isPublic ? 'checked' : '' }}
                                                        title="OFF = Khusus Warga Desa (bawaan), ON = Izinkan Desa Lain / Terbuka Publik">
                                                </div>
                                            </div>
                                        </div>
                                        <small class="exclusive-desc-text text-muted d-block" style="font-size: 0.75rem; line-height: 1.45;">
                                            {{ $isPublic ? 'Terbuka publik — warga dari luar desa juga diizinkan mengakses dan memesan layanan ini.' : 'Hanya warga berdomisili ' . $region->name . ' ' . $descText }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Layanan Publik Bawaan (Kabar & Informasi — Ringkas 1 Baris Penuh) -->
                @foreach($publicServices as $service)
                    @if(strpos(strtolower($service->name), 'pengumuman') !== false)
                        <input type="hidden" name="services[]" value="{{ $service->id }}">
                        <div class="card border-0 shadow-sm rounded-4 mb-4" style="border-left: 4px solid #696cff !important;">
                            <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-3 min-w-0">
                                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(105, 108, 255, 0.08);">
                                        <img src="{{ asset('User/img/elemen/KabardanInformasiDaerah.png') }}" alt="Kabar dan Informasi" style="width: 32px; height: 32px; object-fit: contain;">
                                    </div>
                                    <div class="min-w-0">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <h6 class="fw-bold text-dark mb-0">Kabar &amp; Informasi Daerah</h6>
                                            <span class="badge bg-label-success rounded-pill" style="font-size: 0.7rem;">
                                                <i class="bx bx-check-circle me-1"></i>Selalu Aktif
                                            </span>
                                        </div>
                                        <small class="text-muted d-block" style="line-height: 1.45;">
                                            Layanan informasi publik terpadu Kecamatan Bengkalis. Berita dan pengumuman selalu tersedia untuk warga; target penerima diatur saat memposting pengumuman.
                                        </small>
                                    </div>
                                </div>
                                <span class="badge bg-label-primary rounded-pill px-3 py-2 flex-shrink-0">
                                    <i class="bx bx-lock-alt me-1"></i>Layanan Bawaan
                                </span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- ========================================================= -->
            <!-- TAB 2: METODE ANTAR & AMBIL (TANPA SUB-TAB BERLAPIS)      -->
            <!-- ========================================================= -->
            <div class="tab-pane fade" id="tab-pengiriman" role="tabpanel">
                <div class="alert bg-label-info border-0 rounded-4 d-flex align-items-start gap-2.5 p-3 mb-4">
                    <i class="bx bx-info-circle fs-4 text-info flex-shrink-0 mt-0.5"></i>
                    <div class="small text-dark" style="line-height: 1.5;">
                        Opsi antar/ambil berlaku untuk <strong>Penyewaan Alat</strong> dan <strong>Penjualan Gas</strong>. Untuk <strong>Transportasi</strong>, metode serah-terima mengikuti opsi supir pada tiap kendaraan, sedangkan <strong>Fasilitas Umum</strong> digunakan langsung di lokasi.
                    </div>
                </div>

                <div class="row g-3 mb-4" id="main_delivery_section">
                    <!-- Kartu Pengiriman: Penyewaan Alat -->
                    <div class="col-12 col-lg-6" id="delivery_card_alat">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center gap-2.5">
                                <div class="rounded-3 p-1.5 bg-label-warning d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <img src="{{ asset('User/img/elemen/F1.png') }}" alt="Alat" style="width: 26px; height: 26px; object-fit: contain;">
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Penyewaan Alat</h6>
                                    <small class="text-muted">Minimal 1 metode harus aktif</small>
                                </div>
                            </div>
                            <div class="card-body p-3 p-md-4 d-flex flex-column gap-3">
                                <div class="rs-delivery-option {{ $alatAntarActive ? 'is-active' : '' }}">
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-3 min-w-0">
                                            <img src="{{ asset('Admin/img/elements/antar.png') }}" alt="Antar" class="flex-shrink-0" style="width: 38px; height: 38px; object-fit: contain;">
                                            <div class="min-w-0">
                                                <div class="fw-bold text-dark small">Antar ke Lokasi Warga</div>
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Petugas desa mengantarkan alat ke alamat pemesan.</small>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column align-items-center flex-shrink-0">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input toggle-status m-0" style="transform: scale(1.2); cursor: pointer;" type="checkbox" name="alat_delivery_antar_active" {{ $alatAntarActive ? 'checked' : '' }}>
                                            </div>
                                            <span class="status-label mt-1 fw-bold" style="font-size: 0.68rem;"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="rs-delivery-option {{ $alatJemputActive ? 'is-active' : '' }}">
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-3 min-w-0">
                                            <img src="{{ asset('Admin/img/elements/jemput.png') }}" alt="Ambil" class="flex-shrink-0" style="width: 38px; height: 38px; object-fit: contain;">
                                            <div class="min-w-0">
                                                <div class="fw-bold text-dark small">Ambil Sendiri di Gudang / Kantor</div>
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Warga mengambil alat langsung ke lokasi layanan desa.</small>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column align-items-center flex-shrink-0">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input toggle-status m-0" style="transform: scale(1.2); cursor: pointer;" type="checkbox" name="alat_delivery_jemput_active" {{ $alatJemputActive ? 'checked' : '' }}>
                                            </div>
                                            <span class="status-label mt-1 fw-bold" style="font-size: 0.68rem;"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Pengiriman: Penjualan Gas -->
                    <div class="col-12 col-lg-6" id="delivery_card_gas">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center gap-2.5">
                                <div class="rounded-3 p-1.5 bg-label-danger d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <img src="{{ asset('User/img/elemen/F2.png') }}" alt="Gas" style="width: 26px; height: 26px; object-fit: contain;">
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Penjualan Gas LPG</h6>
                                    <small class="text-muted">Minimal 1 metode harus aktif</small>
                                </div>
                            </div>
                            <div class="card-body p-3 p-md-4 d-flex flex-column gap-3">
                                <div class="rs-delivery-option {{ $gasAntarActive ? 'is-active' : '' }}">
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-3 min-w-0">
                                            <img src="{{ asset('Admin/img/elements/antar.png') }}" alt="Kurir Desa" class="flex-shrink-0" style="width: 38px; height: 38px; object-fit: contain;">
                                            <div class="min-w-0">
                                                <div class="fw-bold text-dark small">Diantar Kurir Desa</div>
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Tabung gas diantarkan langsung ke rumah warga.</small>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column align-items-center flex-shrink-0">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input toggle-status m-0" style="transform: scale(1.2); cursor: pointer;" type="checkbox" name="gas_delivery_antar_active" {{ $gasAntarActive ? 'checked' : '' }}>
                                            </div>
                                            <span class="status-label mt-1 fw-bold" style="font-size: 0.68rem;"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="rs-delivery-option {{ $gasJemputActive ? 'is-active' : '' }}">
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-3 min-w-0">
                                            <img src="{{ asset('Admin/img/elements/jemput.png') }}" alt="Ambil di Pangkalan" class="flex-shrink-0" style="width: 38px; height: 38px; object-fit: contain;">
                                            <div class="min-w-0">
                                                <div class="fw-bold text-dark small">Ambil Sendiri di Pangkalan</div>
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Warga datang menukar tabung langsung ke pangkalan desa.</small>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column align-items-center flex-shrink-0">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input toggle-status m-0" style="transform: scale(1.2); cursor: pointer;" type="checkbox" name="gas_delivery_jemput_active" {{ $gasJemputActive ? 'checked' : '' }}>
                                            </div>
                                            <span class="status-label mt-1 fw-bold" style="font-size: 0.68rem;"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State (Jika Alat dan Gas keduanya nonaktif) -->
                <div id="empty_delivery_state" class="card border-0 shadow-sm rounded-4 mb-4" style="display: none;">
                    <div class="card-body text-center py-5 px-3">
                        <div class="avatar avatar-lg bg-label-secondary text-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                            <i class="bx bx-box fs-1"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Belum Ada Unit dengan Opsi Pengiriman</h6>
                        <p class="text-muted small mb-0">Aktifkan unit <strong>Penyewaan Alat</strong> atau <strong>Penjualan Gas</strong> pada tab <strong>Aktivasi Unit Layanan</strong> untuk mengatur metode antar/ambil.</p>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- TAB 3: KONTAK WHATSAPP                                    -->
            <!-- ========================================================= -->
            <div class="tab-pane fade" id="tab-kontak" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2.5 min-w-0">
                            <div class="avatar avatar-sm rounded-circle {{ $isWaActive ? 'bg-success text-white' : 'bg-secondary text-white' }} d-flex align-items-center justify-content-center flex-shrink-0" id="wa_avatar" style="width: 38px; height: 38px;">
                                <i class="bx bxl-whatsapp fs-4"></i>
                            </div>
                            <div class="min-w-0">
                                <h6 class="fw-bold mb-0 {{ $isWaActive ? 'text-success' : 'text-dark' }}" id="wa_title">Tombol Chat WhatsApp Warga</h6>
                                <small class="text-muted d-block">Memudahkan warga menghubungi admin desa secara langsung.</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <span class="badge rounded-pill {{ $isWaActive ? 'bg-label-success' : 'bg-label-secondary' }}" id="wa_status_badge" style="font-size: 0.72rem;">
                                {{ $isWaActive ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input shadow-sm" style="cursor:pointer; transform: scale(1.2);" type="checkbox" name="whatsapp_active" id="whatsapp_active" onchange="toggleWaStatus(this)" {{ $isWaActive ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        <div id="wa_desc_active" class="alert bg-label-success border-0 rounded-3 small mb-3" style="display: {{ $isWaActive ? 'block' : 'none' }};">
                            <i class="bx bx-check-circle me-1"></i><strong>Aktif:</strong> Tombol chat WhatsApp tampil di halaman layanan warga agar warga bisa bertanya langsung ke admin desa.
                        </div>
                        <div id="wa_desc_inactive" class="alert bg-label-secondary border-0 rounded-3 small mb-0" style="display: {{ !$isWaActive ? 'block' : 'none' }};">
                            <i class="bx bx-info-circle me-1"></i><strong>Nonaktif:</strong> Tombol chat WhatsApp saat ini disembunyikan dari aplikasi warga. Aktifkan sakelar di kanan atas untuk mengisi nomor kontak.
                        </div>

                        <div id="wa_fields" style="{{ !$isWaActive ? 'display: none;' : '' }}">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small text-dark" for="wa_input_nama">Nama Kontak / Petugas WA</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text bg-light"><i class="bx bx-user text-muted"></i></span>
                                        <input type="text" name="whatsapp_name" id="wa_input_nama" value="{{ old('whatsapp_name', $region->payment_info['whatsapp_name'] ?? '') }}" class="form-control" placeholder="Contoh: Admin {{ $region->name }}">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small text-dark" for="contact_phone">Nomor WhatsApp Aktif</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text bg-light"><i class="bx bxl-whatsapp text-success"></i></span>
                                        <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $region->contact_phone) }}" class="form-control" placeholder="Contoh: 081234567890">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Action Bar -->
        <div class="card border-0 shadow-sm rounded-4 mt-2 mb-3">
            <div class="card-body p-3 d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-between gap-2">
                <small class="text-muted d-flex align-items-center gap-1.5">
                    <i class="bx bx-info-circle text-primary fs-5 flex-shrink-0"></i>
                    <span>Perubahan akan langsung berlaku di aplikasi warga setelah Anda menekan tombol simpan.</span>
                </small>
                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm w-100 w-sm-auto flex-shrink-0 d-inline-flex align-items-center justify-content-center gap-1.5">
                    <i class="bx bx-save fs-5"></i>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
(function() {
    const mainToggles = document.querySelectorAll('.service-main-toggle');
    const publicAccessToggles = document.querySelectorAll('.public-access-toggle');

    function updateSummaryAndDelivery() {
        let activeCount = 0;
        let exclusiveCount = 0;
        let showAlat = false;
        let showGas = false;

        mainToggles.forEach(cb => {
            const card = cb.closest('.card-service-item');
            const type = cb.getAttribute('data-service-type');
            const pubToggle = card ? card.querySelector('.public-access-toggle') : null;

            if (cb.checked) {
                activeCount++;
                if (!pubToggle || !pubToggle.checked) exclusiveCount++;
                if (type === 'alat') showAlat = true;
                if (type === 'gas') showGas = true;
            }
        });

        const liveActive = document.getElementById('liveActiveUnitsCount');
        const liveExclusive = document.getElementById('liveExclusiveUnitsCount');
        if (liveActive) liveActive.textContent = activeCount;
        if (liveExclusive) liveExclusive.textContent = exclusiveCount;

        const cardAlat = document.getElementById('delivery_card_alat');
        const cardGas = document.getElementById('delivery_card_gas');
        const mainSection = document.getElementById('main_delivery_section');
        const emptyState = document.getElementById('empty_delivery_state');

        if (cardAlat) cardAlat.style.display = showAlat ? '' : 'none';
        if (cardGas) cardGas.style.display = showGas ? '' : 'none';

        const hasAnyDelivery = showAlat || showGas;
        if (mainSection) mainSection.style.display = hasAnyDelivery ? '' : 'none';
        if (emptyState) emptyState.style.display = hasAnyDelivery ? 'none' : 'block';
    }

    // Update UI Kartu Unit Layanan
    mainToggles.forEach(toggle => {
        const syncCard = (el) => {
            const card = el.closest('.card-service-item');
            if (!card) return;
            const badgePill = card.querySelector('.status-badge-pill');

            if (el.checked) {
                card.classList.remove('is-off');
                if (badgePill) {
                    badgePill.className = 'badge bg-label-success text-success rounded-pill px-2.5 py-1 status-badge-pill';
                    badgePill.innerHTML = '<i class="bx bx-check-circle me-1"></i><span class="status-badge-text">Tampil di Warga</span>';
                }
            } else {
                card.classList.add('is-off');
                if (badgePill) {
                    badgePill.className = 'badge bg-label-secondary text-secondary rounded-pill px-2.5 py-1 status-badge-pill';
                    badgePill.innerHTML = '<i class="bx bx-hide me-1"></i><span class="status-badge-text">Disembunyikan</span>';
                }
            }
            updateSummaryAndDelivery();
        };

        toggle.addEventListener('change', function() { syncCard(this); });
    });

    // Update UI Akses Layanan (OFF = Khusus Warga Desa, ON = Terbuka Desa Lain)
    publicAccessToggles.forEach(toggle => {
        const syncPublicAccess = (el) => {
            const card = el.closest('.card-service-item');
            if (!card) return;
            const badgePill = card.querySelector('.exclusive-badge-pill');
            const descContainer = card.querySelector('.exclusive-desc-text');
            const statusIcon = card.querySelector('.access-status-icon');
            const regionName = @json($region->name);
            const actionText = card.getAttribute('data-action') || 'yang dapat mengakses layanan ini.';

            if (el.checked) {
                if (badgePill) {
                    badgePill.className = 'badge bg-label-info text-info rounded-pill px-2 py-1 fw-semibold exclusive-badge-pill';
                    badgePill.textContent = 'Terbuka Desa Lain';
                }
                if (statusIcon) {
                    statusIcon.className = 'bx bx-world text-info fs-5 flex-shrink-0 access-status-icon';
                }
                if (descContainer) {
                    descContainer.textContent = 'Terbuka publik — warga dari luar desa juga diizinkan mengakses dan memesan layanan ini.';
                }
            } else {
                if (badgePill) {
                    badgePill.className = 'badge bg-label-warning text-warning rounded-pill px-2 py-1 fw-semibold exclusive-badge-pill';
                    badgePill.textContent = 'Khusus Warga Desa';
                }
                if (statusIcon) {
                    statusIcon.className = 'bx bx-shield-quarter text-warning fs-5 flex-shrink-0 access-status-icon';
                }
                if (descContainer) {
                    descContainer.textContent = 'Hanya warga berdomisili ' + regionName + ' ' + actionText;
                }
            }
            updateSummaryAndDelivery();
        };

        toggle.addEventListener('change', function() { syncPublicAccess(this); });
    });

    // Update UI Delivery Switches
    document.querySelectorAll('.toggle-status').forEach(toggle => {
        const syncDelivery = (el) => {
            const box = el.closest('.rs-delivery-option');
            const label = box ? box.querySelector('.status-label') : null;
            if (box) box.classList.toggle('is-active', el.checked);
            if (label) {
                label.textContent = el.checked ? 'Aktif' : 'Off';
                label.className = 'status-label mt-1 fw-bold ' + (el.checked ? 'text-primary' : 'text-secondary');
            }
        };
        syncDelivery(toggle);
        toggle.addEventListener('change', function() { syncDelivery(this); });
    });

    updateSummaryAndDelivery();
})();

function toggleWaStatus(checkbox) {
    const isChecked = checkbox.checked;
    const title = document.getElementById('wa_title');
    const avatar = document.getElementById('wa_avatar');
    const descActive = document.getElementById('wa_desc_active');
    const descInactive = document.getElementById('wa_desc_inactive');
    const badge = document.getElementById('wa_status_badge');
    const fieldsWrapper = document.getElementById('wa_fields');
    const liveWa = document.getElementById('liveWaStatusText');

    if (isChecked) {
        if (title) { title.classList.remove('text-dark'); title.classList.add('text-success'); }
        if (avatar) { avatar.classList.remove('bg-secondary'); avatar.classList.add('bg-success'); }
        if (badge) { badge.className = 'badge rounded-pill bg-label-success'; badge.textContent = 'Aktif'; }
        if (descActive) descActive.style.display = 'block';
        if (descInactive) descInactive.style.display = 'none';
        if (fieldsWrapper) fieldsWrapper.style.display = 'block';
        if (liveWa) { liveWa.className = 'fw-bold mb-0 text-success'; liveWa.textContent = 'Aktif'; }
    } else {
        if (title) { title.classList.remove('text-success'); title.classList.add('text-dark'); }
        if (avatar) { avatar.classList.remove('bg-success'); avatar.classList.add('bg-secondary'); }
        if (badge) { badge.className = 'badge rounded-pill bg-label-secondary'; badge.textContent = 'Nonaktif'; }
        if (descActive) descActive.style.display = 'none';
        if (descInactive) descInactive.style.display = 'block';
        if (fieldsWrapper) fieldsWrapper.style.display = 'none';
        if (liveWa) { liveWa.className = 'fw-bold mb-0 text-secondary'; liveWa.textContent = 'Nonaktif'; }
    }
}
</script>
@endsection
