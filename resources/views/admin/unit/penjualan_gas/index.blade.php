@extends('admin.layouts.admin')

@section('title', 'Unit Penjualan Gas')

@section('content')
<style>
    .unit-stat-card {
        border-radius: 16px;
        border: 1px solid #eef1f6;
        background: #fff;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .unit-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(67, 89, 113, 0.08) !important;
    }
    .unit-product-card {
        border-radius: 16px;
        border: 1px solid #e9edf4;
        background: #fff;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .unit-product-card:hover {
        transform: translateY(-3px);
        border-color: #c7cbfd;
        box-shadow: 0 10px 24px rgba(67, 89, 113, 0.1) !important;
    }
    .unit-product-img {
        aspect-ratio: 4/3;
        width: 100%;
        object-fit: cover;
        object-position: center;
    }
    @media (max-width: 575.98px) {
        .unit-stat-card { padding: 0.65rem 0.4rem !important; }
        .unit-stat-card span { font-size: 0.66rem !important; line-height: 1.2 !important; }
        .unit-stat-card h4 { font-size: 1.12rem !important; margin-top: 2px; }
        .unit-product-img { aspect-ratio: 1/1; }
        .unit-product-card .card-body { padding: 0.7rem !important; }
        .unit-product-card .card-title { font-size: 0.86rem !important; }
    }
</style>

@php
    $admin = auth()->user();
    $region = \App\Models\Region::find($admin->region_id);
    $settings = $region ? $region->settings : [];
    $isCrisisMode = isset($settings['crisis_mode_gas']) ? $settings['crisis_mode_gas'] : false;
    $quotaLimit = $settings['gas_quota_limit'] ?? 1;
    $quotaDays = $settings['gas_quota_days'] ?? 7;

    $gasCollection = method_exists($gases, 'getCollection') ? $gases->getCollection() : collect($gases);
    $totalGasProducts = method_exists($gases, 'total') ? $gases->total() : $gasCollection->count();
    $totalGasStock = $gasCollection->sum('stok');
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
        <div class="min-w-0">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0"><span class="text-muted fw-light">Unit Layanan /</span> Penjualan Gas</h4>
                @include('admin.unit.partials.unit_officer_badge', ['unitKey' => 'gas'])
            </div>
            <p class="text-muted small mb-0">Kelola stok tabung gas LPG, harga satuan, mode krisis kuota, serta lokasi pangkalan desa.</p>
        </div>

        <div class="d-flex flex-wrap gap-2 w-100 w-lg-auto justify-content-start justify-content-lg-end flex-shrink-0">
            @unless(auth()->user()->isStaff())
                <button type="button" class="btn {{ $isCrisisMode ? 'btn-danger' : 'btn-outline-danger' }} rounded-pill px-3 shadow-sm text-nowrap d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#crisisModeModal">
                    <i class="bx {{ $isCrisisMode ? 'bx-error-circle' : 'bx-shield-quarter' }}"></i>
                    <span>Mode Krisis</span>
                </button>
            @endunless
            @if(in_array(auth()->user()->role, ['admin_desa', 'super_admin']))
                <a href="{{ route('admin.gas.kk.index') }}" class="btn btn-outline-primary rounded-pill px-3 shadow-sm text-nowrap d-inline-flex align-items-center gap-1">
                    <i class="bx bx-id-card"></i>
                    <span>Periksa KK</span>
                </a>
            @endif
            @unless(auth()->user()->isStaff())
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm text-nowrap d-inline-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#modalLokasi" onclick="siapkanFormulir()">
                    <i class="bx bx-map-pin"></i>
                    <span>Lokasi</span>
                    @if(isset($lokasiLayanan) && $lokasiLayanan->count())
                        <span class="badge bg-primary rounded-pill ms-1">{{ $lokasiLayanan->count() }}</span>
                    @endif
                </button>
            @endunless
            <a href="{{ route('admin.unit.penjualan_gas.create') }}" class="btn btn-primary rounded-pill px-3 shadow-sm text-nowrap d-inline-flex align-items-center gap-1">
                <i class="bx bx-plus-circle"></i>
                <span>Tambah Gas</span>
            </a>
        </div>
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

    <!-- Info Banner + 3 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card bg-label-primary border-0 shadow-none rounded-4 h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                        <i class="bx bx-gas-pump fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Katalog &amp; Distribusi Gas LPG Desa</h6>
                        <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.55;">
                            Pastikan stok tabung dan harga satuan selalu terbarui. Bila terjadi kelangkaan gas, aktifkan <strong>Mode Krisis</strong> agar pembelian warga dibatasi otomatis per KK/akun.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="row g-2 h-100">
                <div class="col-4">
                    <div class="unit-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Jenis Produk</span>
                        <h4 class="fw-bold text-primary mb-0">{{ $totalGasProducts }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="unit-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Total Stok</span>
                        <h4 class="fw-bold text-success mb-0">{{ $totalGasStock }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="unit-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Status Kuota</span>
                        <h4 class="fw-bold mb-0 {{ $isCrisisMode ? 'text-danger' : 'text-info' }}" style="font-size: 1.05rem;">
                            {{ $isCrisisMode ? 'Krisis' : 'Normal' }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lokasi Layanan Strip -->
    @isset($lokasiLayanan)
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 d-flex align-items-center flex-wrap gap-3">
            <span class="avatar avatar-sm rounded-circle bg-label-primary text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                <i class="bx bx-map-pin fs-5"></i>
            </span>
            <div class="flex-grow-1 min-w-0">
                <div class="fw-bold text-dark small mb-1">Lokasi Pangkalan / Layanan Wilayah</div>
                @if($lokasiLayanan->isEmpty())
                    <div class="text-muted small">
                        Belum ada titik lokasi terdaftar. Tambahkan dulu agar mudah dipilih saat menambah produk gas.
                    </div>
                @else
                    <div class="d-flex flex-wrap gap-1.5">
                        @foreach($lokasiLayanan as $l)
                            <span class="badge {{ $l->punyaTitik() ? 'bg-label-primary' : 'bg-label-warning' }} rounded-pill"
                                  title="{{ $l->punyaTitik() ? 'Titik peta sudah ditentukan' : 'Titik peta belum ditentukan' }}">
                                <i class="bx {{ $l->punyaTitik() ? 'bx-map-pin' : 'bx-map' }} me-0.5"></i>{{ $l->nama }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
            @unless(auth()->user()->isStaff())
                <a href="{{ route('admin.lokasi-layanan.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 text-nowrap">
                    Kelola Lokasi
                </a>
            @endunless
        </div>
    </div>
    @endisset

    <!-- Tabs + Quick Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-2.5 p-sm-3 d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2.5">
            <ul class="nav nav-pills gap-2 flex-nowrap overflow-x-auto mb-0" role="tablist" style="scrollbar-width: none;">
                <li class="nav-item flex-shrink-0">
                    <button type="button" class="nav-link rounded-pill px-3 py-2 fw-semibold {{ $tab == 'katalog' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-gas-katalog" aria-selected="{{ $tab == 'katalog' ? 'true' : 'false' }}">
                        <i class="bx bx-grid-alt me-1"></i>Katalog Gas
                    </button>
                </li>
                <li class="nav-item flex-shrink-0">
                    <button type="button" class="nav-link rounded-pill px-3 py-2 fw-semibold {{ $tab == 'chat' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-gas-chat" aria-selected="{{ $tab == 'chat' ? 'true' : 'false' }}">
                        <i class="bx bx-chat me-1"></i>Layanan Pesan
                        <span class="badge rounded-pill bg-danger ms-1 {{ ($totalUnreadChats ?? 0) > 0 ? '' : 'd-none' }}" data-unit-chat-tab-badge="gas">{{ (int) ($totalUnreadChats ?? 0) }}</span>
                    </button>
                </li>
            </ul>
            <div class="input-group input-group-merge input-group-sm" style="max-width: 300px;">
                <span class="input-group-text bg-light border-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" id="searchGasInput" class="form-control bg-light border-0" placeholder="Cari produk gas...">
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content bg-transparent p-0 border-0 shadow-none">
        <!-- TAB 1: KATALOG GAS -->
        <div class="tab-pane fade {{ $tab == 'katalog' ? 'show active' : '' }}" id="navs-gas-katalog" role="tabpanel">
            @if($gases->count() > 0)
                <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-2 g-md-3" id="gasCatalogGrid">
                    @foreach ($gases as $gas)
                        <div class="col gas-product-col" data-search="{{ strtolower($gas->jenis_gas ?? '') }}">
                            <div class="card unit-product-card shadow-sm h-100 d-flex flex-column">
                                <div class="position-relative">
                                    <div id="carouselExample{{ $gas->id }}" class="carousel slide" data-bs-ride="carousel">
                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <img src="{{ asset('storage/' . $gas->foto) }}" class="unit-product-img" alt="{{ $gas->jenis_gas }}">
                                            </div>
                                            @if ($gas->foto_2)
                                                <div class="carousel-item">
                                                    <img src="{{ asset('storage/' . $gas->foto_2) }}" class="unit-product-img" alt="{{ $gas->jenis_gas }}">
                                                </div>
                                            @endif
                                            @if ($gas->foto_3)
                                                <div class="carousel-item">
                                                    <img src="{{ asset('storage/' . $gas->foto_3) }}" class="unit-product-img" alt="{{ $gas->jenis_gas }}">
                                                </div>
                                            @endif
                                        </div>
                                        @if ($gas->foto_2 || $gas->foto_3)
                                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample{{ $gas->id }}" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExample{{ $gas->id }}" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            </button>
                                        @endif
                                    </div>
                                    <span class="badge {{ $gas->stok > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill position-absolute top-0 end-0 m-2 shadow-sm" style="font-size: 0.7rem;">
                                        {{ $gas->stok }} {{ Str::upper($gas->satuan) }}
                                    </span>
                                </div>
                                <div class="card-body p-3 d-flex flex-column flex-grow-1">
                                    <h6 class="card-title fw-bold text-dark mb-1 text-truncate" title="{{ $gas->jenis_gas }}">{{ $gas->jenis_gas }}</h6>
                                    <div class="mb-3">
                                        <span class="badge bg-label-primary rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.78rem;">
                                            Rp {{ number_format($gas->harga_satuan, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="mt-auto pt-2 border-top d-flex gap-1.5 justify-content-center">
                                        <a href="{{ route('admin.unit.penjualan_gas.show', $gas->id) }}" class="btn btn-sm btn-outline-info rounded-pill flex-grow-1 px-2" title="Lihat Detail">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.unit.penjualan_gas.edit', $gas->id) }}" class="btn btn-sm btn-outline-warning rounded-pill flex-grow-1 px-2" title="Edit Produk">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        <form action="{{ route('admin.unit.penjualan_gas.destroy', $gas->id) }}" method="POST"
                                              data-konfirmasi="Apakah Anda yakin ingin menghapus gas ini?" class="d-flex flex-grow-1 m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill w-100 px-2" title="Hapus Produk">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-5 px-3">
                        @if($search)
                            <div class="avatar avatar-lg bg-label-secondary text-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-search-alt fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Tidak Ditemukan</h5>
                            <p class="text-muted small mb-3">Tidak ada produk gas yang cocok dengan pencarian "<strong>{{ $search }}</strong>".</p>
                            <a href="{{ route('admin.unit.penjualan_gas.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                                <i class="bx bx-refresh me-1"></i>Tampilkan Semua Gas
                            </a>
                        @else
                            <div class="avatar avatar-lg bg-label-primary text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-gas-pump fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Belum Ada Produk Gas</h5>
                            <p class="text-muted small mb-3">Tambahkan produk gas LPG 3kg, 5.5kg, 12kg, atau jenis lainnya agar warga dapat memesan.</p>
                            <a href="{{ route('admin.unit.penjualan_gas.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                <i class="bx bx-plus-circle me-1"></i>Tambah Gas Pertama
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @if ($gases->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $gases->links() }}
                </div>
            @endif
        </div>

        <!-- TAB 2: CHAT WARGA GAS -->
        <div class="tab-pane fade {{ $tab == 'chat' ? 'show active' : '' }}" id="navs-gas-chat" role="tabpanel">
            @include('admin.unit.partials.unit_chat_panel', [
                'serviceType' => 'gas',
                'chatServiceTitle' => 'Penjualan Gas LPG',
                'chats' => $chats
            ])
        </div>
    </div>
</div>
@endsection

@push('modals')
    <!-- Modal Pengaturan Mode Krisis -->
    <div class="modal fade" id="crisisModeModal" tabindex="-1" aria-labelledby="crisisModeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ route('admin.unit.penjualan_gas.crisis_mode') }}" method="POST" class="modal-content border-0 shadow-lg rounded-4">
                @csrf
                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title text-danger fw-bold d-flex align-items-center" id="crisisModeModalLabel">
                        <i class="bx bx-shield-quarter fs-4 me-2"></i>Pengaturan Mode Krisis Elpiji
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert bg-label-warning border-0 rounded-4 d-flex align-items-start gap-2.5 p-3 mb-4 text-dark small">
                        <i class="bx bx-info-circle fs-4 text-warning flex-shrink-0"></i>
                        <div>
                            <strong class="d-block mb-1">Apa itu Mode Krisis?</strong>
                            Fitur ini digunakan <strong>hanya saat terjadi kelangkaan gas Elpiji</strong>. Saat aktif, sistem membatasi jumlah tabung yang dapat dibeli tiap warga dalam rentang waktu tertentu.
                        </div>
                    </div>

                    <label class="form-label fw-bold mb-3 small text-uppercase text-muted">Pilih Status Penjualan Saat Ini</label>
                    <div class="row mb-4 g-3">
                        <div class="col-md-6">
                            <label class="card h-100 border rounded-4 crisis-card normal-card {{ !$isCrisisMode ? 'border-success shadow-sm' : 'border-light-subtle' }}" style="transition: all 0.2s; cursor: pointer; background-color: {{ !$isCrisisMode ? '#e8fadf' : '#fff' }};">
                                <div class="card-body p-3 text-center">
                                    <div class="form-check d-flex justify-content-center mb-2">
                                        <input class="form-check-input" style="transform: scale(1.2);" type="radio" name="is_crisis_mode" id="mode_normal" value="0" {{ !$isCrisisMode ? 'checked' : '' }} onchange="toggleCrisisInputs()">
                                    </div>
                                    <div class="avatar avatar-md bg-success rounded-circle mx-auto mb-2 d-flex justify-content-center align-items-center shadow-sm">
                                        <i class="bx bx-check text-white fs-4"></i>
                                    </div>
                                    <h6 class="fw-bold text-success mb-1">Stok Aman / Normal</h6>
                                    <small class="text-muted d-block">Warga bebas membeli gas tanpa batasan kuota harian.</small>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="card h-100 border rounded-4 crisis-card krisis-card {{ $isCrisisMode ? 'border-danger shadow-sm' : 'border-light-subtle' }}" style="transition: all 0.2s; cursor: pointer; background-color: {{ $isCrisisMode ? '#ffe0db' : '#fff' }};">
                                <div class="card-body p-3 text-center">
                                    <div class="form-check d-flex justify-content-center mb-2">
                                        <input class="form-check-input" style="transform: scale(1.2);" type="radio" name="is_crisis_mode" id="mode_krisis" value="1" {{ $isCrisisMode ? 'checked' : '' }} onchange="toggleCrisisInputs()">
                                    </div>
                                    <div class="avatar avatar-md bg-danger rounded-circle mx-auto mb-2 d-flex justify-content-center align-items-center shadow-sm">
                                        <i class="bx bx-error text-white fs-4"></i>
                                    </div>
                                    <h6 class="fw-bold text-danger mb-1">Terjadi Kelangkaan</h6>
                                    <small class="text-muted d-block">Sistem membatasi pembelian warga secara otomatis.</small>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div id="crisis_settings_wrapper" class="{{ !$isCrisisMode ? 'd-none' : '' }}">
                        <div class="p-3 rounded-4" style="background-color: #fff2f0;">
                            <h6 class="fw-bold text-danger mb-3 d-flex align-items-center"><i class="bx bx-cog me-2"></i>Aturan Pembatasan Kuota</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Maksimal Beli</label>
                                    <div class="input-group input-group-merge">
                                        <input type="number" name="quota_limit" class="form-control" min="1" value="{{ $quotaLimit }}" placeholder="Misal: 1">
                                        <span class="input-group-text bg-white fw-semibold">Tabung</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Setiap Rentang Waktu</label>
                                    <div class="input-group input-group-merge">
                                        <input type="number" name="quota_days" class="form-control" min="1" value="{{ $quotaDays }}" placeholder="Misal: 7">
                                        <span class="input-group-text bg-white fw-semibold">Hari</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3 border-top rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm"><i class="bx bx-save me-1"></i>Terapkan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
<script>
    function toggleCrisisInputs() {
        const isKrisis = document.getElementById('mode_krisis').checked;
        const wrapper = document.getElementById('crisis_settings_wrapper');

        document.querySelectorAll('.crisis-card').forEach(card => {
            card.classList.remove('shadow-sm', 'border-success', 'border-danger');
            card.classList.add('border-light-subtle');
            card.style.backgroundColor = '#fff';
        });

        if (isKrisis) {
            wrapper.classList.remove('d-none');
            const krisisCard = document.querySelector('.krisis-card');
            krisisCard.classList.remove('border-light-subtle');
            krisisCard.classList.add('border-danger', 'shadow-sm');
            krisisCard.style.backgroundColor = '#ffe0db';
        } else {
            wrapper.classList.add('d-none');
            const normalCard = document.querySelector('.normal-card');
            normalCard.classList.remove('border-light-subtle');
            normalCard.classList.add('border-success', 'shadow-sm');
            normalCard.style.backgroundColor = '#e8fadf';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchGasInput');
        if (!searchInput) return;
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.gas-product-col').forEach(col => {
                const hay = (col.getAttribute('data-search') || '').toLowerCase();
                col.style.display = (!q || hay.includes(q)) ? '' : 'none';
            });
        });
    });
</script>
@endpush

@include("admin.lokasi_layanan._modal")
