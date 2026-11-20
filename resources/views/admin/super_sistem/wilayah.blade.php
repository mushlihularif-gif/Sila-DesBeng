@extends('admin.layouts.admin')

@section('title', 'Peta Wilayah')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="min-w-0">
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">Sistem Platform /</span> Peta Wilayah
            </h4>
            <p class="text-muted mb-0 small">
                Kelola hierarki resmi Kecamatan Bengkalis hingga tingkat Desa/Kelurahan, RW, dan RT.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto">
            <a href="{{ route('admin.kelola-wilayah.index') }}" class="btn btn-outline-primary rounded-pill px-3 w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-user-check"></i>
                <span>Kelola Admin Wilayah</span>
            </a>
            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1" id="btnBukaModalWilayah" data-bs-toggle="modal" data-bs-target="#modalTambahWilayah">
                <i class="bx bx-plus fs-5"></i>
                <span>Tambah Wilayah</span>
            </button>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="card bg-label-primary border-0 shadow-none rounded-4 mb-4">
        <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 42px; height: 42px;">
                <i class="bx bx-map-alt fs-4"></i>
            </div>
            <div>
                <h6 class="fw-bold text-primary mb-1">Cakupan Operasional Kecamatan Bengkalis</h6>
                <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.55;">
                    Susunan wilayah ini menjadi acuan utama penempatan warga saat verifikasi identitas (KYC), struktur pengurus desa/RW/RT, serta batasan akses layanan bertanda <strong>Khusus Warga Desa</strong>. Hanya dapat dikelola oleh Super Admin.
                </p>
            </div>
        </div>
    </div>

    {{-- Flash Alerts --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bx bx-check-circle fs-4 flex-shrink-0"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bx bx-error-circle fs-4 flex-shrink-0"></i>
            <div class="flex-grow-1">{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    {{-- 5 Equal-Width KPI Cards --}}
    <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-5 g-3 mb-4">
        @foreach([
            'kecamatan' => ['Kecamatan', 'bx-building-house', 'info'],
            'desa'      => ['Desa', 'bx-home-alt', 'success'],
            'kelurahan' => ['Kelurahan', 'bx-home-smile', 'warning'],
            'rw'        => ['RW', 'bx-been-here', 'primary'],
            'rt'        => ['RT', 'bx-map-pin', 'secondary'],
        ] as $tipe => [$label, $ikon, $warna])
            <div class="col">
                <div class="card border-0 shadow-sm rounded-4 h-100 wilayah-kpi-tile">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-{{ $warna }} flex-shrink-0" style="width: 42px; height: 42px;">
                            <i class="bx {{ $ikon }} fs-4 text-{{ $warna }}"></i>
                        </div>
                        <div class="min-w-0">
                            <h4 class="fw-bold text-dark mb-0">{{ number_format($rekap[$tipe] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-muted fw-semibold d-block text-truncate">{{ $label }}</small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Orphan Warning (if any) --}}
    @if($yatim->isNotEmpty())
        <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex align-items-start gap-3 mb-4" role="alert">
            <i class="bx bx-error fs-4 mt-0.5 flex-shrink-0"></i>
            <div>
                <strong class="d-block mb-1">{{ $yatim->count() }} wilayah kehilangan induknya</strong>
                <p class="small mb-2">Induk yang dirujuk sudah tidak ditemukan sehingga wilayah ini tidak tampil pada pohon hierarki:</p>
                <div class="d-flex flex-wrap gap-1.5">
                    @foreach($yatim as $y)
                        <span class="badge bg-label-warning rounded-pill px-2.5">
                            {{ $y->name }} <span class="opacity-75">#{{ $y->id }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Main Tree Card --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom px-4 py-3.5">
            <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-primary flex-shrink-0" style="width: 40px; height: 40px;">
                        <i class="bx bx-sitemap fs-5 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Struktur Pohon Wilayah</h6>
                        <small class="text-muted">{{ number_format($total, 0, ',', '.') }} wilayah terdaftar &bull; Klik panah untuk membuka atau menutup cabang</small>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="input-group input-group-sm" style="max-width: 240px;">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bx bx-search"></i></span>
                        <input type="text" id="cariWilayah" class="form-control bg-light border-start-0" placeholder="Cari nama wilayah...">
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-secondary rounded-start-pill px-3" type="button" id="bukaSemua">
                            <i class="bx bx-expand-alt me-1"></i>Buka Semua
                        </button>
                        <button class="btn btn-outline-secondary rounded-end-pill px-3" type="button" id="tutupSemua">
                            <i class="bx bx-collapse-alt me-1"></i>Tutup
                        </button>
                    </div>
                </div>
            </div>

            {{-- Quick Level Filter Pills --}}
            <div class="mt-3">
                <ul class="nav nav-pills gap-1.5 flex-nowrap overflow-x-auto mb-0" id="filterTingkatWilayah">
                    <li class="nav-item flex-shrink-0">
                        <button type="button" class="nav-link active rounded-pill px-3 py-1 fw-semibold wilayah-type-filter" data-filter="all" style="font-size: 0.78rem;">Semua</button>
                    </li>
                    <li class="nav-item flex-shrink-0">
                        <button type="button" class="nav-link rounded-pill px-3 py-1 fw-semibold wilayah-type-filter" data-filter="desa" style="font-size: 0.78rem;">Desa ({{ $rekap['desa'] ?? 0 }})</button>
                    </li>
                    <li class="nav-item flex-shrink-0">
                        <button type="button" class="nav-link rounded-pill px-3 py-1 fw-semibold wilayah-type-filter" data-filter="kelurahan" style="font-size: 0.78rem;">Kelurahan ({{ $rekap['kelurahan'] ?? 0 }})</button>
                    </li>
                    <li class="nav-item flex-shrink-0">
                        <button type="button" class="nav-link rounded-pill px-3 py-1 fw-semibold wilayah-type-filter" data-filter="rw" style="font-size: 0.78rem;">RW ({{ $rekap['rw'] ?? 0 }})</button>
                    </li>
                    <li class="nav-item flex-shrink-0">
                        <button type="button" class="nav-link rounded-pill px-3 py-1 fw-semibold wilayah-type-filter" data-filter="rt" style="font-size: 0.78rem;">RT ({{ $rekap['rt'] ?? 0 }})</button>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card-body p-3 p-md-4">
            <div id="pohonWilayah">
                @forelse($pohon as $simpul)
                    @include('admin.super_sistem.partials.simpul_wilayah', ['simpul' => $simpul, 'level' => 0])
                @empty
                    <div class="text-center text-muted py-5">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-3" style="width: 64px; height: 64px;">
                            <i class="bx bx-map-alt fs-1 text-muted"></i>
                        </div>
                        <h6 class="fw-semibold text-dark mb-1">Belum ada wilayah terdaftar</h6>
                        <p class="small mb-3">Tambahkan wilayah pertama untuk memulai struktur hierarki.</p>
                        <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambahWilayah">
                            <i class="bx bx-plus me-1"></i>Tambah Wilayah
                        </button>
                    </div>
                @endforelse
            </div>

            <div id="pesanKosong" class="text-center text-muted py-5 d-none">
                <i class="bx bx-search-alt fs-1 d-block mb-2 opacity-50"></i>
                <h6 class="fw-semibold text-dark mb-1">Wilayah tidak ditemukan</h6>
                <small>Coba gunakan kata kunci lain atau pilih filter "Semua".</small>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Modal wajib di @push('modals') agar tidak terkurung animation transform .layout-page --}}
@push('modals')
<div class="modal fade" id="modalTambahWilayah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.sistem-platform.wilayah.store') }}" method="POST" class="modal-content rounded-4 border-0 shadow-lg">
            @csrf
            <div class="modal-header border-bottom px-4 py-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-primary" style="width: 38px; height: 38px;">
                        <i class="bx bx-map-alt fs-5 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" style="font-size: 1rem;">Tambah Wilayah</h5>
                        <small class="text-muted">Struktur baru langsung aktif di seluruh sistem.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-dark" for="indukWilayah">Wilayah Induk <span class="text-danger">*</span></label>
                    <select name="parent_id" id="indukWilayah" class="form-select rounded-3" required>
                        <option value="">— Pilih wilayah induk —</option>
                        @foreach($pilihanInduk as $p)
                            <option value="{{ $p['id'] }}" data-tipe="{{ $p['tipe'] }}"
                                {{ old('parent_id') == $p['id'] ? 'selected' : '' }}>
                                {{ $p['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">Tingkat wilayah turunan akan menyesuaikan induk yang dipilih.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-dark" for="tipeWilayah">Tingkat Wilayah <span class="text-danger">*</span></label>
                    <select name="type" id="tipeWilayah" class="form-select rounded-3" required>
                        <option value="">Pilih wilayah induk dahulu</option>
                    </select>
                    @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-1">
                    <label class="form-label fw-semibold small text-dark" for="namaWilayah">Nama Wilayah <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="namaWilayah" class="form-control rounded-3"
                           value="{{ old('name') }}" placeholder="Contoh: Pematang Duku atau 01" required>
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">
                        Awalan tingkat (Desa / Kelurahan / RW / RT) ditambahkan otomatis oleh sistem.
                    </small>
                </div>
            </div>

            <div class="modal-footer border-top px-4 py-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                    <i class="bx bx-check me-1"></i>Simpan Wilayah
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('styles')
<style>
    .wilayah-kpi-tile {
        border: 1px solid #eef1f6 !important;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .wilayah-kpi-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06) !important;
    }
    .baris-wilayah {
        border: 1px solid transparent;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }
    .baris-wilayah:hover {
        background-color: #f8fbff;
        border-color: #e2e8f0;
    }
    .wilayah-children-branch {
        border-left: 2px solid #e2e8f0;
        margin-left: 16px;
        padding-left: 12px;
    }
    @media (max-width: 575.98px) {
        .wilayah-children-branch {
            margin-left: 10px;
            padding-left: 8px;
        }
    }
    .tombol-lipat .panah-lipat {
        transition: transform 0.2s ease;
        transform: rotate(90deg);
    }
    .tombol-lipat.collapsed .panah-lipat {
        transform: rotate(0deg);
    }
    .wilayah-quick-add {
        opacity: 0.65;
        transition: opacity 0.15s ease, background-color 0.15s ease;
    }
    .baris-wilayah:hover .wilayah-quick-add {
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const turunan = @json($turunan);
    const label = {
        kecamatan: 'Kecamatan', desa: 'Desa',
        kelurahan: 'Kelurahan', rw: 'RW', rt: 'RT',
    };

    const induk = document.getElementById('indukWilayah');
    const tipe  = document.getElementById('tipeWilayah');

    function segarkanTipe() {
        const opsiInduk = induk.options[induk.selectedIndex];
        const tipeInduk = opsiInduk ? opsiInduk.dataset.tipe : null;
        const boleh = tipeInduk ? (turunan[tipeInduk] || []) : [];

        const sebelumnya = tipe.value;
        tipe.innerHTML = '';
        if (!boleh.length) {
            const o = document.createElement('option');
            o.value = '';
            o.textContent = 'Pilih wilayah induk dahulu';
            tipe.appendChild(o);
            return;
        }
        boleh.forEach(function (t) {
            const o = document.createElement('option');
            o.value = t;
            o.textContent = label[t] || t;
            tipe.appendChild(o);
        });
        if (boleh.includes(sebelumnya)) tipe.value = sebelumnya;
    }

    induk.addEventListener('change', segarkanTipe);
    segarkanTipe();

    // Quick "+ Tambah Bawahan" pada simpul pohon
    document.querySelectorAll('.btn-quick-add-child').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const parentId = this.getAttribute('data-parent-id');
            if (parentId && induk) {
                induk.value = parentId;
                segarkanTipe();
            }
            const modalEl = document.getElementById('modalTambahWilayah');
            if (modalEl) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        });
    });

    // Buka / tutup semua
    document.getElementById('bukaSemua').addEventListener('click', function () {
        document.querySelectorAll('#pohonWilayah .collapse').forEach(function (el) {
            bootstrap.Collapse.getOrCreateInstance(el, { toggle: false }).show();
        });
    });
    document.getElementById('tutupSemua').addEventListener('click', function () {
        document.querySelectorAll('#pohonWilayah .collapse').forEach(function (el) {
            bootstrap.Collapse.getOrCreateInstance(el, { toggle: false }).hide();
        });
    });

    // Filter pencarian + tingkat wilayah
    const kolomCari = document.getElementById('cariWilayah');
    const pesanKosong = document.getElementById('pesanKosong');
    let activeTypeFilter = 'all';

    function jalankanFilter() {
        const kata = (kolomCari.value || '').trim().toLowerCase();
        const semua = document.querySelectorAll('#pohonWilayah .simpul-wilayah');

        if (!kata && activeTypeFilter === 'all') {
            semua.forEach(function (el) { el.classList.remove('d-none'); });
            pesanKosong.classList.add('d-none');
            return;
        }

        semua.forEach(function (el) { el.classList.add('d-none'); });

        let ketemu = 0;
        semua.forEach(function (el) {
            const cocokNama = !kata || (el.dataset.nama || '').includes(kata);
            const cocokTipe = activeTypeFilter === 'all' || el.dataset.tipe === activeTypeFilter;
            if (!cocokNama || !cocokTipe) return;

            ketemu++;
            el.classList.remove('d-none');

            let naik = el.parentElement;
            while (naik && naik.id !== 'pohonWilayah') {
                if (naik.classList.contains('simpul-wilayah')) naik.classList.remove('d-none');
                if (naik.classList.contains('collapse')) {
                    bootstrap.Collapse.getOrCreateInstance(naik, { toggle: false }).show();
                }
                naik = naik.parentElement;
            }

            if (activeTypeFilter === 'all') {
                el.querySelectorAll('.simpul-wilayah').forEach(function (anak) {
                    anak.classList.remove('d-none');
                });
            }
        });

        pesanKosong.classList.toggle('d-none', ketemu > 0);
    }

    kolomCari.addEventListener('input', jalankanFilter);
    document.querySelectorAll('.wilayah-type-filter').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.wilayah-type-filter').forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            activeTypeFilter = this.getAttribute('data-filter') || 'all';
            jalankanFilter();
        });
    });
});
</script>
@endpush
