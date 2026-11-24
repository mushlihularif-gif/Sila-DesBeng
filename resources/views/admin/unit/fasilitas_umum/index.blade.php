@extends('admin.layouts.admin')

@section('title', 'Unit Fasilitas Umum & Aset')

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
    .unit-product-img,
    .unit-product-placeholder {
        aspect-ratio: 4/3;
        width: 100%;
        object-fit: cover;
        object-position: center;
    }
    .sop-option-card {
        border-radius: 16px;
        border: 1.5px solid #e4e7ec;
        background: #fff;
        transition: all 0.2s ease;
    }
    .sop-option-card.active-sop {
        border-color: #ffab00 !important;
        background-color: #fffbf0 !important;
        box-shadow: 0 6px 18px rgba(255, 171, 0, 0.18) !important;
    }
    @media (max-width: 575.98px) {
        .unit-stat-card { padding: 0.65rem 0.4rem !important; }
        .unit-stat-card span { font-size: 0.66rem !important; line-height: 1.2 !important; }
        .unit-stat-card h4 { font-size: 1.12rem !important; margin-top: 2px; }
        .unit-product-img,
        .unit-product-placeholder { aspect-ratio: 1/1; }
        .unit-product-card .card-body { padding: 0.7rem !important; }
        .unit-product-card .card-title { font-size: 0.86rem !important; }
    }
</style>

@php
    $fasilitasCollection = method_exists($fasilitas, 'getCollection') ? $fasilitas->getCollection() : collect($fasilitas);
    $totalFasilitas = method_exists($fasilitas, 'total') ? $fasilitas->total() : $fasilitasCollection->count();
    $tersediaCount = $fasilitasCollection->filter(fn($f) => ($f->status ?? 'Tersedia') === 'Tersedia')->count();
    $unreadChatsCount = (int) ($totalUnreadChats ?? 0);
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="min-w-0">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0"><span class="text-muted fw-light">Unit Layanan /</span> Fasilitas Umum &amp; Aset</h4>
                @include('admin.unit.partials.unit_officer_badge', ['unitKey' => 'fasilitas_umum'])
            </div>
            <p class="text-muted small mb-0">Kelola gedung serbaguna, balai pertemuan, lapangan olahraga, pengurus kunci, serta SOP peminjaman.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-start justify-content-md-end flex-shrink-0">
            <a href="{{ route('admin.unit.fasilitas_umum.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm w-100 w-sm-auto text-nowrap d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-plus-circle"></i>
                <span>Tambah Gedung / Fasilitas</span>
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
                        <i class="bx bx-buildings fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Manajemen Fasilitas Publik &amp; Pemegang Kunci</h6>
                        <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.55;">
                            Tampilkan gedung, aula, dan ruang publik desa beserta informasi biaya (gratis/berbayar), pengurus pemegang kunci, serta aturan SOP peminjaman di tab <strong>Pengaturan &amp; SOP</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="row g-2 h-100">
                <div class="col-4">
                    <div class="unit-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Total Aset</span>
                        <h4 class="fw-bold text-primary mb-0">{{ $totalFasilitas }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="unit-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Tersedia</span>
                        <h4 class="fw-bold text-success mb-0">{{ $tersediaCount }}</h4>
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
        <div class="card-body p-2.5 p-sm-3 d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-2.5">
            <ul class="nav nav-pills gap-2 flex-nowrap overflow-x-auto mb-0" role="tablist" style="scrollbar-width: none;">
                <li class="nav-item flex-shrink-0">
                    <button type="button" class="nav-link rounded-pill px-3 py-2 fw-semibold text-nowrap {{ ($tab == 'gedung' || $tab == 'kendaraan') ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-gedung" aria-selected="{{ ($tab == 'gedung' || $tab == 'kendaraan') ? 'true' : 'false' }}">
                        <i class="bx bx-building-house me-1"></i>Gedung &amp; Ruang Publik
                    </button>
                </li>
                @unless(auth()->user()->isStaff())
                <li class="nav-item flex-shrink-0">
                    <button type="button" class="nav-link rounded-pill px-3 py-2 fw-semibold text-nowrap" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-pengaturan" aria-selected="false">
                        <i class="bx bx-book-content me-1"></i>Pengaturan &amp; SOP
                    </button>
                </li>
                @endunless
                <li class="nav-item flex-shrink-0">
                    <button type="button" class="nav-link rounded-pill px-3 py-2 fw-semibold text-nowrap {{ $tab == 'chat' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-chat" aria-selected="{{ $tab == 'chat' ? 'true' : 'false' }}">
                        <i class="bx bx-chat me-1"></i>Layanan Pesan
                        <span class="badge rounded-pill bg-danger ms-1 {{ $unreadChatsCount > 0 ? '' : 'd-none' }}" data-unit-chat-tab-badge="fasilitas_umum">{{ $unreadChatsCount }}</span>
                    </button>
                </li>
            </ul>
            <div class="input-group input-group-merge input-group-sm" style="max-width: 300px;">
                <span class="input-group-text bg-light border-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" id="searchFasilitasInput" class="form-control bg-light border-0" placeholder="Cari gedung atau fasilitas...">
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content bg-transparent p-0 border-0 shadow-none">
        <!-- TAB 1: GEDUNG & RUANG PUBLIK -->
        <div class="tab-pane fade {{ ($tab == 'gedung' || $tab == 'kendaraan') ? 'show active' : '' }}" id="navs-top-gedung" role="tabpanel">
            @if($fasilitas->count() > 0)
                <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-2 g-md-3" id="fasilitasCatalogGrid">
                    @foreach ($fasilitas as $item)
                        @php
                            $validPhotos = [];
                            foreach (['foto', 'foto_2', 'foto_3'] as $fCol) {
                                if (!empty($item->$fCol) && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->$fCol)) {
                                    $validPhotos[] = $item->$fCol;
                                }
                            }
                            $fotoCount = count($validPhotos);
                        @endphp
                        <div class="col fasilitas-product-col" data-search="{{ strtolower(($item->nama_fasilitas ?? '') . ' ' . ($item->kategori ?? '')) }}">
                            <div class="card unit-product-card shadow-sm h-100 d-flex flex-column">
                                <div class="position-relative">
                                    <div id="carouselExample{{ $item->id }}" class="carousel slide" data-bs-ride="carousel">
                                        <div class="carousel-inner">
                                            @if ($fotoCount > 0)
                                                @foreach ($validPhotos as $idx => $photo)
                                                    <div class="carousel-item {{ $idx === 0 ? 'active' : '' }}">
                                                        <img src="{{ asset('storage/' . $photo) }}" class="unit-product-img" alt="{{ $item->nama_fasilitas }}"
                                                            onerror="this.parentElement.innerHTML='<div class=\'d-flex align-items-center justify-content-center bg-light text-muted unit-product-placeholder\'><i class=\'bx bx-building-house\' style=\'font-size: 44px; color: #cbd5e1;\'></i></div>';">
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="carousel-item active">
                                                    <div class="d-flex align-items-center justify-content-center bg-light text-muted unit-product-placeholder">
                                                        <i class="bx bx-building-house" style="font-size: 44px; color: #cbd5e1;"></i>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        @if($fotoCount > 1)
                                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample{{ $item->id }}" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExample{{ $item->id }}" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            </button>
                                        @endif
                                    </div>
                                    <span class="badge {{ ($item->status ?? 'Tersedia') === 'Tersedia' ? 'bg-success' : 'bg-secondary' }} rounded-pill position-absolute top-0 end-0 m-2 shadow-sm" style="font-size: 0.68rem;">
                                        {{ $item->status ?? 'Tersedia' }}
                                    </span>
                                </div>
                                <div class="card-body p-3 d-flex flex-column flex-grow-1">
                                    <h6 class="card-title fw-bold text-dark text-capitalize mb-1 text-truncate" title="{{ $item->nama_fasilitas }}">{{ $item->nama_fasilitas }}</h6>
                                    <div class="mb-2">
                                        <span class="badge bg-label-success rounded-pill px-2 py-0.5 text-truncate" style="font-size: 0.68rem; max-width: 100%;">
                                            {{ str_replace('&', 'dan', $item->kategori) }}
                                        </span>
                                    </div>
                                    <div class="mb-2 d-flex flex-wrap gap-1">
                                        @php
                                            $skemaBiaya = $item->effectivePricingScheme();
                                        @endphp
                                        @if($skemaBiaya === 'berbayar_semua')
                                            <span class="badge bg-label-primary rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.74rem;">
                                                Semua Acara: Rp {{ number_format($item->harga_sewa, 0, ',', '.') }} / hari
                                            </span>
                                        @elseif($skemaBiaya === 'berbayar')
                                            <span class="badge bg-label-info rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.74rem;">
                                                Pribadi: Rp {{ number_format($item->harga_sewa, 0, ',', '.') }} / hari
                                            </span>
                                            <span class="badge bg-label-success rounded-pill px-2 py-1 fw-semibold" style="font-size: 0.68rem;">
                                                Sosial Gratis
                                            </span>
                                        @else
                                            <span class="badge bg-label-success rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.74rem;">Gratis Sepenuhnya</span>
                                        @endif
                                    </div>

                                    @if($item->pengurus && $item->pengurus->count() > 0)
                                        <div class="mb-2">
                                            <small class="text-muted d-block mb-0.5 fw-semibold" style="font-size: 0.7rem;"><i class="bx bx-key me-1"></i>Pengurus:</small>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($item->pengurus as $p)
                                                    <span class="badge bg-label-secondary text-truncate" style="font-size: 0.66rem; max-width: 100%;" title="{{ $p->kontak }}">{{ $p->nama }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-auto pt-2 border-top d-flex gap-1.5 justify-content-center">
                                        <a href="{{ route('admin.unit.fasilitas_umum.show', $item->id) }}" class="btn btn-sm btn-outline-info rounded-pill flex-grow-1 px-2" title="Lihat Detail">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.unit.fasilitas_umum.edit', $item->id) }}" class="btn btn-sm btn-outline-warning rounded-pill flex-grow-1 px-2" title="Edit Fasilitas">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        <form action="{{ route('admin.unit.fasilitas_umum.destroy', $item->id) }}" method="POST"
                                              data-konfirmasi="Apakah Anda yakin ingin menghapus gedung/fasilitas ini?" class="d-flex flex-grow-1 m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill w-100 px-2" title="Hapus Fasilitas">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($fasilitas->hasPages())
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $fasilitas->links() }}
                    </div>
                @endif
            @else
                <div class="card border-0 shadow-sm rounded-4 text-center py-5 px-3">
                    <div class="card-body">
                        <div class="avatar avatar-lg bg-label-primary text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                            <i class="bx bx-buildings fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Belum Ada Fasilitas Umum</h5>
                        <p class="text-muted small mb-3">Tambahkan gedung serbaguna, balai desa, atau lapangan olahraga agar warga dapat mengajukan peminjaman.</p>
                        <a href="{{ route('admin.unit.fasilitas_umum.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="bx bx-plus-circle me-1"></i>Tambah Gedung Pertama
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- TAB 2: PENGATURAN SOP -->
        <div class="tab-pane fade" id="navs-top-pengaturan" role="tabpanel">
            <form action="{{ route('admin.unit.fasilitas_umum.sop.update') }}" method="POST">
                @if(auth()->user()->isStaff())
                    <div class="alert bg-label-info border-0 rounded-4 mb-3 small">
                        <i class="bx bx-info-circle me-1"></i>Ketentuan layanan ditetapkan oleh Admin Desa. Staf hanya dapat membacanya di sini.
                    </div>
                @endif
                <fieldset @disabled(auth()->user()->isStaff())>
                    @csrf
                    <div class="row g-3">
                        <div class="col-12 col-xl-8">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-header bg-white border-bottom px-3 px-md-4 py-3 d-flex align-items-center gap-2.5">
                                    <div class="avatar avatar-sm bg-label-info rounded-circle d-flex justify-content-center align-items-center">
                                        <i class="bx bx-book-content fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">SOP Peminjaman Gedung &amp; Fasilitas Publik</h6>
                                        <small class="text-muted">Ditampilkan kepada warga sebelum mengajukan peminjaman.</small>
                                    </div>
                                </div>
                                <div class="card-body p-3 p-md-4">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <div class="card sop-option-card sop-card {{ $sop_active == 'ditanggung' ? 'active-sop' : '' }} h-100">
                                                <div class="card-body p-3">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="radio" name="sop_active" id="sop_active_ditanggung" value="ditanggung" {{ $sop_active == 'ditanggung' ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-bold text-dark small" for="sop_active_ditanggung">
                                                            Ditanggung Penyewa
                                                        </label>
                                                    </div>
                                                    <p class="text-muted small mb-2" style="font-size: 0.76rem;">Kerusakan fasilitas publik menjadi tanggung jawab penyewa.</p>
                                                    <textarea class="form-control form-control-sm rounded-3 mb-2" name="sop_ditanggung" id="sop_ditanggung_text" rows="7">{{ $sop_ditanggung }}</textarea>
                                                    <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-2.5" onclick="resetSop('ditanggung')">
                                                        <i class="bx bx-reset me-1"></i>Reset Bawaan
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card sop-option-card sop-card {{ $sop_active == 'tidak_ditanggung' ? 'active-sop' : '' }} h-100">
                                                <div class="card-body p-3">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="radio" name="sop_active" id="sop_active_tidak_ditanggung" value="tidak_ditanggung" {{ $sop_active == 'tidak_ditanggung' ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-bold text-dark small" for="sop_active_tidak_ditanggung">
                                                            Ditanggung Dana Desa
                                                        </label>
                                                    </div>
                                                    <p class="text-muted small mb-2" style="font-size: 0.76rem;">Kerusakan tidak disengaja ditanggung dana operasional desa.</p>
                                                    <textarea class="form-control form-control-sm rounded-3 mb-2" name="sop_tidak_ditanggung" id="sop_tidak_ditanggung_text" rows="7">{{ $sop_tidak_ditanggung }}</textarea>
                                                    <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-2.5" onclick="resetSop('tidak_ditanggung')">
                                                        <i class="bx bx-reset me-1"></i>Reset Bawaan
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end pt-2 border-top">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm w-100 w-sm-auto">
                                            <i class="bx bx-save me-1"></i>Simpan Pengaturan SOP
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-4">
                            <div class="card bg-label-primary border-0 shadow-none rounded-4">
                                <div class="card-body p-3 p-md-4">
                                    <h6 class="fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                                        <i class="bx bx-bulb fs-5"></i>
                                        <span>Tips Menulis SOP Fasilitas</span>
                                    </h6>
                                    <ul class="list-unstyled mb-0 small text-primary" style="opacity: 0.92;">
                                        <li class="mb-2 d-flex align-items-start gap-2"><i class="bx bx-check-circle mt-0.5 flex-shrink-0"></i><span>Sertakan biaya sewa (jika ada) atau informasikan jika gratis.</span></li>
                                        <li class="mb-2 d-flex align-items-start gap-2"><i class="bx bx-check-circle mt-0.5 flex-shrink-0"></i><span>Jelaskan prosedur serah-terima dan pengembalian kunci fasilitas.</span></li>
                                        <li class="mb-2 d-flex align-items-start gap-2"><i class="bx bx-check-circle mt-0.5 flex-shrink-0"></i><span>Cantumkan aturan kebersihan dan denda keterlambatan/kerusakan.</span></li>
                                        <li class="d-flex align-items-start gap-2"><i class="bx bx-check-circle mt-0.5 flex-shrink-0"></i><span>Sebutkan dokumen identitas yang perlu ditunjukkan warga.</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>

        <!-- TAB 3: CHAT WARGA FASILITAS -->
        <div class="tab-pane fade {{ $tab == 'chat' ? 'show active' : '' }}" id="navs-top-chat" role="tabpanel">
            @include('admin.unit.partials.unit_chat_panel', [
                'serviceType' => 'fasilitas_umum',
                'chatServiceTitle' => 'Fasilitas Umum & Ruang Publik',
                'chats' => $chats
            ])
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const defaultSops = {
        'ditanggung': @json($default_ditanggung ?? ''),
        'tidak_ditanggung': @json($default_tidak_ditanggung ?? '')
    };

    function resetSop(type) {
        konfirmasi({
            judul: 'Reset Teks SOP',
            pesan: 'Teks SOP akan dikembalikan ke versi bawaan. Perubahan yang belum disimpan akan hilang.',
            jenis: 'bahaya',
            tombolYa: 'Ya, Reset'
        }).then(function (setuju) {
            if (setuju) document.getElementById('sop_' + type + '_text').value = defaultSops[type];
        });
    }

    document.querySelectorAll('input[name="sop_active"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.sop-card').forEach(card => card.classList.remove('active-sop'));
            if (this.checked) {
                this.closest('.sop-card')?.classList.add('active-sop');
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchFasilitasInput');
        if (!searchInput) return;
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.fasilitas-product-col').forEach(col => {
                const hay = (col.getAttribute('data-search') || '').toLowerCase();
                col.style.display = (!q || hay.includes(q)) ? '' : 'none';
            });
        });
    });
</script>
@endpush
