@extends('admin.layouts.admin')

@section('title', 'Unit Penyewaan Transportasi')

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
    $mobilCollection = method_exists($mobils, 'getCollection') ? $mobils->getCollection() : collect($mobils);
    $totalMobils = method_exists($mobils, 'total') ? $mobils->total() : $mobilCollection->count();
    $totalMobilUnits = $mobilCollection->sum('stok');
    $unreadChatsCount = (int) ($totalUnreadChats ?? 0);
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="min-w-0">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0"><span class="text-muted fw-light">Unit Layanan /</span> Penyewaan Transportasi</h4>
                @include('admin.unit.partials.unit_officer_badge', ['unitKey' => 'sewa_mobil'])
            </div>
            <p class="text-muted small mb-0">Kelola armada kendaraan sewa desa, tarif harian, ketersediaan unit, serta ketentuan SOP sewa.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-start justify-content-md-end flex-shrink-0">
            <a href="{{ route('admin.unit.mobil.sop') }}" class="btn btn-outline-info rounded-pill px-3 shadow-sm flex-grow-1 flex-sm-grow-0 text-nowrap d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-book-content"></i>
                <span>Atur SOP</span>
            </a>
            <a href="{{ route('admin.unit.mobil.create') }}" class="btn btn-primary rounded-pill px-3 shadow-sm flex-grow-1 flex-sm-grow-0 text-nowrap d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-plus-circle"></i>
                <span>Tambah Armada</span>
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

    <!-- Info Banner + 3 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card bg-label-primary border-0 shadow-none rounded-4 h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                        <i class="bx bx-car fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Katalog Armada &amp; SOP Transportasi</h6>
                        <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.55;">
                            Pastikan ketentuan <strong>SOP Penyewaan</strong> sudah disesuaikan dengan aturan desa Anda melalui tombol <strong>Atur SOP</strong> di atas agar warga memahami hak &amp; kewajiban saat menyewa armada.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="row g-2 h-100">
                <div class="col-4">
                    <div class="unit-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Total Armada</span>
                        <h4 class="fw-bold text-primary mb-0">{{ $totalMobils }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="unit-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Unit Tersedia</span>
                        <h4 class="fw-bold text-success mb-0">{{ $totalMobilUnits }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="unit-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Pesan Baru</span>
                        <h4 class="fw-bold {{ $unreadChatsCount > 0 ? 'text-danger' : 'text-info' }} mb-0">{{ $unreadChatsCount }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs + Quick Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-2.5 p-sm-3 d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2.5">
            <ul class="nav nav-pills gap-2 flex-nowrap overflow-x-auto mb-0" role="tablist" style="scrollbar-width: none;">
                <li class="nav-item flex-shrink-0">
                    <button type="button" class="nav-link rounded-pill px-3 py-2 fw-semibold {{ $tab == 'katalog' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-mobil-katalog" aria-selected="{{ $tab == 'katalog' ? 'true' : 'false' }}">
                        <i class="bx bx-car me-1"></i>Daftar Transportasi
                    </button>
                </li>
                <li class="nav-item flex-shrink-0">
                    <button type="button" class="nav-link rounded-pill px-3 py-2 fw-semibold {{ $tab == 'chat' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-mobil-chat" aria-selected="{{ $tab == 'chat' ? 'true' : 'false' }}">
                        <i class="bx bx-chat me-1"></i>Layanan Pesan
                        <span class="badge rounded-pill bg-danger ms-1 {{ $unreadChatsCount > 0 ? '' : 'd-none' }}" data-unit-chat-tab-badge="mobil">{{ $unreadChatsCount }}</span>
                    </button>
                </li>
            </ul>
            <div class="input-group input-group-merge input-group-sm" style="max-width: 300px;">
                <span class="input-group-text bg-light border-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" id="searchMobilInput" class="form-control bg-light border-0" placeholder="Cari armada kendaraan...">
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content bg-transparent p-0 border-0 shadow-none">
        <!-- TAB 1: DAFTAR MOBIL -->
        <div class="tab-pane fade {{ $tab == 'katalog' ? 'show active' : '' }}" id="navs-mobil-katalog" role="tabpanel">
            @if($mobils->count() > 0)
                <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-2 g-md-3" id="mobilCatalogGrid">
                    @foreach ($mobils as $mobil)
                        <div class="col mobil-product-col" data-search="{{ strtolower($mobil->nama_mobil ?? '') }}">
                            <div class="card unit-product-card shadow-sm h-100 d-flex flex-column">
                                <div class="position-relative">
                                    <div id="carouselExample{{ $mobil->id }}" class="carousel slide" data-bs-ride="carousel">
                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <img src="{{ asset('storage/' . $mobil->foto) }}" class="unit-product-img" alt="{{ $mobil->nama_mobil }}">
                                            </div>
                                            @if ($mobil->foto_2)
                                                <div class="carousel-item">
                                                    <img src="{{ asset('storage/' . $mobil->foto_2) }}" class="unit-product-img" alt="{{ $mobil->nama_mobil }}">
                                                </div>
                                            @endif
                                            @if ($mobil->foto_3)
                                                <div class="carousel-item">
                                                    <img src="{{ asset('storage/' . $mobil->foto_3) }}" class="unit-product-img" alt="{{ $mobil->nama_mobil }}">
                                                </div>
                                            @endif
                                        </div>
                                        @if ($mobil->foto_2 || $mobil->foto_3)
                                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample{{ $mobil->id }}" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExample{{ $mobil->id }}" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            </button>
                                        @endif
                                    </div>
                                    <span class="badge {{ $mobil->stok > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill position-absolute top-0 end-0 m-2 shadow-sm" style="font-size: 0.7rem;">
                                        {{ $mobil->stok }} {{ $mobil->satuan }}
                                    </span>
                                </div>
                                <div class="card-body p-3 d-flex flex-column flex-grow-1">
                                    <h6 class="card-title fw-bold text-dark mb-1 text-truncate" title="{{ $mobil->nama_mobil }}">{{ $mobil->nama_mobil }}</h6>
                                    <div class="mb-3">
                                        @if($mobil->harga_mulai > 0)
                                            <span class="badge bg-label-primary rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.76rem;">
                                                Mulai Rp {{ number_format($mobil->harga_mulai, 0, ',', '.') }}
                                            </span>
                                        @elseif($mobil->harga_sewa > 0)
                                            <span class="badge bg-label-primary rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.76rem;">
                                                Rp {{ number_format($mobil->harga_sewa, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="badge bg-label-success rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.76rem;">Gratis</span>
                                        @endif
                                    </div>
                                    <div class="mt-auto pt-2 border-top d-flex gap-1.5 justify-content-center">
                                        <a href="{{ route('admin.unit.mobil.show', $mobil->id) }}" class="btn btn-sm btn-outline-info rounded-pill flex-grow-1 px-2" title="Lihat Detail">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.unit.mobil.edit', $mobil->id) }}" class="btn btn-sm btn-outline-warning rounded-pill flex-grow-1 px-2" title="Edit Armada">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        <form action="{{ route('admin.unit.mobil.destroy', $mobil->id) }}" method="POST"
                                              data-konfirmasi="Apakah Anda yakin ingin menghapus armada ini?" class="d-flex flex-grow-1 m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill w-100 px-2" title="Hapus Armada">
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
                            <p class="text-muted small mb-3">Tidak ada armada yang cocok dengan pencarian "<strong>{{ $search }}</strong>".</p>
                            <a href="{{ route('admin.unit.mobil.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                                <i class="bx bx-refresh me-1"></i>Tampilkan Semua Armada
                            </a>
                        @else
                            <div class="avatar avatar-lg bg-label-primary text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-car fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Belum Ada Armada Transportasi</h5>
                            <p class="text-muted small mb-3">Tambahkan armada kendaraan desa agar dapat dilihat dan dipesan oleh warga.</p>
                            <a href="{{ route('admin.unit.mobil.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                <i class="bx bx-plus-circle me-1"></i>Tambah Armada Pertama
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @if ($mobils->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $mobils->links() }}
                </div>
            @endif
        </div>

        <!-- TAB 2: CHAT WARGA MOBIL -->
        <div class="tab-pane fade {{ $tab == 'chat' ? 'show active' : '' }}" id="navs-mobil-chat" role="tabpanel">
            @include('admin.unit.partials.unit_chat_panel', [
                'serviceType' => 'mobil',
                'chatServiceTitle' => 'Penyewaan Transportasi',
                'chats' => $chats
            ])
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchMobilInput');
        if (!searchInput) return;
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.mobil-product-col').forEach(col => {
                const hay = (col.getAttribute('data-search') || '').toLowerCase();
                col.style.display = (!q || hay.includes(q)) ? '' : 'none';
            });
        });
    });
</script>
@endpush
@endsection
