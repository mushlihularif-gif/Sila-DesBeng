@extends('admin.layouts.admin')

@section('title', 'Peta Wilayah')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 py-3 mb-4">
        <h4 class="fw-bold mb-0"><span class="text-muted fw-light">Sistem Platform /</span> Peta Wilayah</h4>
        <button class="btn btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalTambahWilayah">
            <i class="bx bx-plus me-1"></i> Tambah Wilayah
        </button>
    </div>

    <div class="alert alert-info d-flex align-items-start mb-4" role="alert">
        <i class="bx bx-info-circle me-2 fs-5 mt-1"></i>
        <div>
            Susunan wilayah di sini menjadi rujukan hampir semua fitur lain: penempatan warga saat KYC,
            jalur naik-turun laporan, pembukuan saldo tiap daerah, sampai penentuan siapa yang boleh
            melihat layanan bertanda <strong>Eksklusif Warga Lokal</strong>.
            Karena itu halaman ini <strong>hanya untuk Super Admin Diskominfotik</strong> dan tidak
            didelegasikan ke staf mana pun.
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Rekap per tingkat. Angka nol sengaja tetap ditampilkan: tingkat yang
         masih kosong justru itu yang perlu terlihat, bukan disembunyikan. --}}
    <div class="row g-3 mb-4">
        @foreach([
            'kabupaten' => ['Kabupaten', 'bx-buildings', 'primary'],
            'kecamatan' => ['Kecamatan', 'bx-building-house', 'info'],
            'desa'      => ['Desa', 'bx-home-alt', 'success'],
            'kelurahan' => ['Kelurahan', 'bx-home-smile', 'warning'],
            'rw'        => ['RW', 'bx-been-here', 'secondary'],
            'rt'        => ['RT', 'bx-map-pin', 'dark'],
        ] as $tipe => [$label, $ikon, $warna])
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 text-center">
                        <span class="ikon-bulat rounded-circle bg-{{ $warna }}-subtle text-{{ $warna }} mb-2"
                              style="width: 42px; height: 42px;">
                            <i class="bx {{ $ikon }} fs-4"></i>
                        </span>
                        <h4 class="fw-bold mb-0">{{ $rekap[$tipe] ?? 0 }}</h4>
                        <small class="text-muted">{{ $label }}</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($yatim->isNotEmpty())
        <div class="alert alert-warning d-flex align-items-start mb-4" role="alert">
            <i class="bx bx-error me-2 fs-5 mt-1"></i>
            <div>
                <strong>{{ $yatim->count() }} wilayah kehilangan induknya.</strong>
                Induk yang dirujuk sudah tidak ada, sehingga wilayah ini tidak muncul di pohon
                dan tidak akan ikut terbaca oleh penyaringan berbasis wilayah:
                <div class="mt-2 d-flex flex-wrap gap-2">
                    @foreach($yatim as $y)
                        <span class="badge bg-warning-subtle text-warning rounded-pill">
                            {{ $y->name }} <span class="opacity-75">#{{ $y->id }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 border-bottom pb-3 mb-3">
                <div class="d-flex align-items-center">
                    <span class="ikon-bulat rounded-circle bg-primary-subtle text-primary me-3"
                          style="width: 38px; height: 38px;">
                        <i class="bx bx-sitemap fs-5"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold mb-0">Struktur Wilayah</h6>
                        <small class="text-muted">{{ $total }} wilayah terdaftar. Klik panah untuk membuka wilayah bawahannya.</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="width: 240px;">
                        <span class="input-group-text bg-transparent border-end-0"><i class="bx bx-search"></i></span>
                        <input type="text" id="cariWilayah" class="form-control border-start-0"
                               placeholder="Cari nama wilayah...">
                    </div>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill" type="button" id="bukaSemua">
                        <i class="bx bx-expand-vertical me-1"></i> Buka Semua
                    </button>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill" type="button" id="tutupSemua">
                        <i class="bx bx-collapse-vertical me-1"></i> Tutup
                    </button>
                </div>
            </div>

            <div id="pohonWilayah">
                @forelse($pohon as $simpul)
                    @include('admin.super_sistem.partials.simpul_wilayah', ['simpul' => $simpul, 'level' => 0])
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="bx bx-map-alt fs-1 d-block mb-2 opacity-50"></i>
                        <p class="mb-3">Belum ada wilayah sama sekali.</p>
                        <button class="btn btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalTambahWilayah">
                            <i class="bx bx-plus me-1"></i> Tambah Kabupaten
                        </button>
                    </div>
                @endforelse
            </div>

            <div id="pesanKosong" class="text-center text-muted py-4 d-none">
                <i class="bx bx-search-alt fs-1 d-block mb-2 opacity-50"></i>
                <small>Tidak ada wilayah yang cocok dengan pencarian.</small>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- ================== MODAL TAMBAH WILAYAH ==================
     WAJIB lewat stack ini, bukan di dalam @section('content').
     Isi content berada di dalam .layout-page yang diberi
     `animation: pageFadeIn ... forwards` oleh layout. Animasi pada properti
     transform menjadikan elemen itu containing block bagi keturunan
     position:fixed, sehingga .modal ikut terkurung di dalamnya sementara
     .modal-backdrop tetap ditempel Bootstrap ke <body>. Hasilnya backdrop
     menimpa dialognya: tombol tutup dan klik di luar sama-sama tidak
     tertangkap, dan halaman tampak beku sampai di-refresh.
     @stack('modals') dirender di luar layout-wrapper, jadi aman. --}}
@push('modals')
<div class="modal fade" id="modalTambahWilayah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.sistem-platform.wilayah.store') }}" method="POST" class="modal-content rounded-4 border-0">
            @csrf
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center">
                    <span class="ikon-bulat rounded-circle bg-primary-subtle text-primary me-3"
                          style="width: 40px; height: 40px;">
                        <i class="bx bx-map-alt fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Tambah Wilayah</h5>
                        <small class="text-muted">Struktur baru akan langsung dipakai seluruh sistem.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body pt-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="indukWilayah">Wilayah Induk</label>
                    <select name="parent_id" id="indukWilayah" class="form-select rounded-3">
                        <option value="">— Tanpa induk (Kabupaten baru) —</option>
                        @foreach($pilihanInduk as $p)
                            <option value="{{ $p['id'] }}" data-tipe="{{ $p['tipe'] }}"
                                {{ old('parent_id') == $p['id'] ? 'selected' : '' }}>
                                {{ $p['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Pilihan tingkat di bawah menyesuaikan induk yang dipilih.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="tipeWilayah">Tingkat</label>
                    <select name="type" id="tipeWilayah" class="form-select rounded-3" required>
                        <option value="kabupaten">Kabupaten</option>
                    </select>
                    @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-1">
                    <label class="form-label fw-semibold" for="namaWilayah">Nama Wilayah</label>
                    <input type="text" name="name" id="namaWilayah" class="form-control rounded-3"
                           value="{{ old('name') }}" placeholder="Contoh: Rupat Utara" required>
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    <small class="text-muted">
                        Awalan tingkat ditambahkan otomatis — ketik <em>Rupat Utara</em>,
                        tersimpan sebagai <em>Kecamatan Rupat Utara</em>.
                    </small>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-label-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-3">
                    <i class="bx bx-check me-1"></i> Simpan Wilayah
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('styles')
<style>
    .baris-wilayah { transition: background-color .15s ease; }
    .baris-wilayah:hover { background-color: rgba(105, 108, 255, 0.06); }
    /* Panah mengikuti keadaan collapse Bootstrap: menunjuk ke bawah saat terbuka. */
    .tombol-lipat .panah-lipat { transition: transform .2s ease; transform: rotate(90deg); }
    .tombol-lipat.collapsed .panah-lipat { transform: rotate(0deg); }
    .min-w-0 { min-width: 0; }

    /* .avatar-initial milik tema hanya memusatkan isinya kalau berada di dalam
       .avatar — dipakai sendirian, ikonnya melenceng ke kiri-bawah. */
    .ikon-bulat {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        line-height: 1;
        flex-shrink: 0;
    }
    .ikon-bulat i { line-height: 1; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tingkat apa saja yang boleh dibuat di bawah tiap tingkat induk. Dikirim
    // dari controller supaya aturan di formulir tidak bisa menyimpang dari
    // aturan yang divalidasi di server.
    const turunan = @json($turunan);
    const label = {
        kabupaten: 'Kabupaten', kecamatan: 'Kecamatan', desa: 'Desa',
        kelurahan: 'Kelurahan', rw: 'RW', rt: 'RT',
    };

    const induk = document.getElementById('indukWilayah');
    const tipe  = document.getElementById('tipeWilayah');

    function segarkanTipe() {
        const opsiInduk = induk.options[induk.selectedIndex];
        const tipeInduk = opsiInduk ? opsiInduk.dataset.tipe : null;
        const boleh = tipeInduk ? (turunan[tipeInduk] || []) : ['kabupaten'];

        const sebelumnya = tipe.value;
        tipe.innerHTML = '';
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

    // --- Buka / tutup semua ---
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

    // --- Pencarian ---
    // Simpul yang cocok ditampilkan bersama seluruh induknya, supaya hasilnya
    // tetap terbaca sebagai posisi dalam hierarki, bukan daftar lepas.
    const kolomCari = document.getElementById('cariWilayah');
    const pesanKosong = document.getElementById('pesanKosong');

    kolomCari.addEventListener('input', function () {
        const kata = this.value.trim().toLowerCase();
        const semua = document.querySelectorAll('#pohonWilayah .simpul-wilayah');

        if (!kata) {
            semua.forEach(function (el) { el.classList.remove('d-none'); });
            pesanKosong.classList.add('d-none');
            return;
        }

        semua.forEach(function (el) { el.classList.add('d-none'); });

        let ketemu = 0;
        semua.forEach(function (el) {
            if (!el.dataset.nama.includes(kata)) return;
            ketemu++;
            el.classList.remove('d-none');

            // Naikkan visibilitas ke seluruh leluhurnya, sekaligus buka lipatannya.
            let naik = el.parentElement;
            while (naik && naik.id !== 'pohonWilayah') {
                if (naik.classList.contains('simpul-wilayah')) naik.classList.remove('d-none');
                if (naik.classList.contains('collapse')) {
                    bootstrap.Collapse.getOrCreateInstance(naik, { toggle: false }).show();
                }
                naik = naik.parentElement;
            }

            // Turunannya ikut tampil agar cabangnya utuh.
            el.querySelectorAll('.simpul-wilayah').forEach(function (anak) {
                anak.classList.remove('d-none');
            });
        });

        pesanKosong.classList.toggle('d-none', ketemu > 0);
    });
});
</script>
@endpush
