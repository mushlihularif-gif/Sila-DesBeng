@extends('admin.layouts.admin')

@section('title', 'Kelola Kabar & Pengumuman Daerah')

@section('content')
<style>
    .kabar-stat-card {
        border-radius: 16px;
        border: 1px solid #eef1f6;
        background: #fff;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .kabar-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(67, 89, 113, 0.08) !important;
    }
    .kabar-item-card {
        border-radius: 16px;
        border: 1px solid #e9edf4;
        background: #fff;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        overflow: hidden;
    }
    .kabar-item-card:hover {
        transform: translateY(-2px);
        border-color: #c7cbfd;
        box-shadow: 0 8px 20px rgba(67, 89, 113, 0.08) !important;
    }
</style>

@php
    $canCreate = $manager || (auth()->user()->role === 'user' && auth()->user()->verification_status === 'verified');
    $states = [
        'draft' => 'Draf — belum dikirim',
        'pending' => 'Menunggu admin',
        'review' => 'Sedang diperiksa',
        'published' => 'Terbit',
        'scheduled' => 'Dijadwalkan',
        'merged' => 'Digabungkan',
        'rejected' => 'Tidak diterbitkan',
        'cancelled' => 'Dibatalkan',
        'archived' => 'Arsip',
        'expired' => 'Sudah dibersihkan',
    ];
    $stateColors = [
        'draft' => 'secondary',
        'pending' => 'warning',
        'review' => 'info',
        'published' => 'success',
        'scheduled' => 'primary',
        'merged' => 'info',
        'rejected' => 'danger',
        'cancelled' => 'danger',
        'archived' => 'secondary',
        'expired' => 'secondary',
    ];
    $collection = method_exists($items, 'getCollection') ? $items->getCollection() : collect($items);
    $totalItems = method_exists($items, 'total') ? $items->total() : $collection->count();
    $pendingItems = $collection->whereIn('workflow_state', ['pending', 'review'])->count();
    $publishedItems = $collection->where('workflow_state', 'published')->count();
@endphp

<div class="container-xxl flex-grow-1 container-p-y" data-content-list>
    <!-- Page Header -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
        <div class="min-w-0">
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Unit Layanan /</span> Kabar &amp; Pengumuman
            </h4>
            <p class="text-muted small mb-0">
                Kelola berita desa, tinjau kiriman warga, serta publikasikan pengumuman resmi wilayah Anda.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2 w-100 w-lg-auto justify-content-start justify-content-lg-end flex-shrink-0">
            <a href="{{ route('announcements.index') }}" class="segi-back btn btn-outline-secondary rounded-pill px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-1" target="_blank">
                <i class="bx bx-globe"></i>
                <span>Lihat Portal Publik</span>
            </a>
            @if($canCreate)
                <form action="{{ route('content.store') }}" method="post" class="m-0">
                    @csrf
                    <input type="hidden" name="category" value="Berita">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-1 text-nowrap">
                        <i class="bx bx-edit-alt"></i>
                        <span>Tulis Kabar Baru</span>
                    </button>
                </form>
            @endif
            @if($manager)
                <form action="{{ route('content.store') }}" method="post" class="m-0">
                    @csrf
                    <input type="hidden" name="category" value="Pengumuman">
                    <button type="submit" class="btn btn-outline-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-1 text-nowrap">
                        <i class="bx bx-megaphone"></i>
                        <span>Buat Pengumuman</span>
                    </button>
                </form>
            @endif
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

    <!-- Info Banner + KPI Summary -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card bg-label-primary border-0 shadow-none rounded-4 h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                        <i class="bx bx-news fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Ruang Kelola Kabar &amp; Pengumuman</h6>
                        <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.55;">
                            Pilih kiriman untuk dibaca. Membaca saja tidak mengunci kiriman—klik <strong>Periksa dan Edit</strong> jika ingin merapikan tulisan (dibantu AI), menggabungkan dokumentasi warga, atau menerbitkannya.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="row g-2 h-100">
                <div class="col-4">
                    <div class="kabar-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Total Kiriman</span>
                        <h4 class="fw-bold text-primary mb-0">{{ $totalItems }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="kabar-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Perlu Tinjau</span>
                        <h4 class="fw-bold text-warning mb-0">{{ $pendingItems }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="kabar-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Sudah Terbit</span>
                        <h4 class="fw-bold text-success mb-0">{{ $publishedItems }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="get" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text bg-light border-0"><i class="bx bx-search text-muted"></i></span>
                        <input name="search" value="{{ request('search') }}" placeholder="Cari judul kabar atau pengumuman..." aria-label="Cari judul" class="form-control bg-light border-0">
                    </div>
                </div>
                <div class="col-12 col-sm-7 col-md-4">
                    <select name="state" aria-label="Status kiriman" class="form-select bg-light border-0">
                        <option value="">Semua Status</option>
                        @foreach($states as $value => $label)
                            <option value="{{ $value }}" @selected(request('state') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-5 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill flex-grow-1 shadow-sm">
                        <i class="bx bx-filter-alt me-1"></i>Filter
                    </button>
                    @if(request('search') || request('state'))
                        <a href="{{ route('content.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Reset Filter">
                            <i class="bx bx-x"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Live Sync Status -->
    <div class="d-flex align-items-center justify-content-between mb-3 px-1">
        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <i class="bx bx-list-ul text-primary"></i>
            <span>Daftar Kabar &amp; Pengumuman</span>
        </h6>
        <small id="list-sync" class="text-muted" role="status">Status diperbarui otomatis.</small>
    </div>

    <!-- List of Items -->
    <div class="d-flex flex-column gap-3" id="content-rows">
        @forelse($items as $item)
            @php
                $stColor = $stateColors[$item->workflow_state] ?? 'secondary';
                $isBerita = ($item->post_category ?? 'Berita') === 'Berita';
            @endphp
            <article data-item-id="{{ $item->id }}" class="card kabar-item-card shadow-sm">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-start gap-3 min-w-0 flex-grow-1">
                            <div class="avatar avatar-md rounded-3 {{ $isBerita ? 'bg-label-primary text-primary' : 'bg-label-warning text-warning' }} d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="bx {{ $isBerita ? 'bx-news' : 'bx-megaphone' }} fs-4"></i>
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <span class="badge {{ $isBerita ? 'bg-label-primary' : 'bg-label-warning' }} rounded-pill" style="font-size: 0.7rem;">
                                        {{ $item->post_category }}
                                    </span>
                                    <span class="badge bg-label-{{ $stColor }} rounded-pill" style="font-size: 0.7rem;" data-state>
                                        {{ $states[$item->workflow_state] ?? $item->workflow_state }}
                                    </span>
                                    @if($item->updated_at)
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            <i class="bx bx-time-five me-0.5"></i>{{ $item->updated_at->format('d M Y, H:i') }}
                                        </small>
                                    @endif
                                </div>
                                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.98rem;" data-title>
                                    {{ $item->draft_data['title'] ?? $item->title ?: 'Draf tanpa judul' }}
                                </h6>
                                <div class="small text-muted">
                                    <i class="bx bx-user me-1"></i>Pengirim: <strong class="text-dark">{{ $item->admin->name ?? 'Warga' }}</strong>
                                </div>
                                <p data-editor class="small text-warning fw-semibold mb-0 mt-1"></p>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-md-auto justify-content-end flex-shrink-0 pt-2 pt-md-0 border-top border-md-0">
                            <a href="{{ route('content.show', $item) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1 flex-grow-1 flex-md-grow-0 justify-content-center">
                                <i class="bx bx-show"></i>
                                <span>{{ $manager ? 'Buka & Periksa' : 'Lihat Kiriman' }}</span>
                            </a>
                            @if($item->workflow_state === 'draft' && (int) $item->admin_id === (int) auth()->id())
                                <form method="post" action="{{ route('content.destroy', $item) }}" class="m-0" data-konfirmasi="Hapus draf ini beserta foto yang belum dikirim? Draf tidak dapat dikembalikan.">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="revision" value="{{ $item->revision }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 d-inline-flex align-items-center gap-1">
                                        <i class="bx bx-trash"></i>
                                        <span>Hapus Draf</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5 px-3">
                    <div class="avatar avatar-lg bg-label-primary text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i class="bx bx-news fs-1"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Belum Ada Kabar atau Pengumuman</h6>
                    <p class="text-muted small mb-0">Gunakan tombol <strong>Tulis Kabar Baru</strong> atau <strong>Buat Pengumuman</strong> di atas untuk memulai publikasi desa.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($items->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $items->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script type="module" src="{{ asset('js/content-workspace.js') }}?v={{ filemtime(public_path('js/content-workspace.js')) }}"></script>
@endpush
@endsection
