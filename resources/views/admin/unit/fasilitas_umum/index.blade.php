@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h4 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Unit Layanan /</span> Fasilitas Umum & Aset
                </h4>
            </div>
        </div>

        <!-- Panduan -->
        <div class="card bg-label-secondary border-0 shadow-none mb-4" style="border-radius: 12px;">
            <div class="card-body d-flex align-items-center p-4">
                <div class="me-3">
                    <div class="bg-secondary p-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                        <i class="bx bx-building fs-3"></i>
                    </div>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-secondary">Manajemen Fasilitas Umum & Aset</h5>
                    <p class="mb-0 text-secondary" style="opacity: 0.85;">
                        Kelola data kendaraan operasional (Ambulans, Truk Sampah) dan fasilitas publik (Gedung Serbaguna, Lapangan) yang dapat diakses oleh warga.
                    </p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center" role="alert">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <style>
            .nav-pills .nav-link { color: #6c757d; font-weight: 600; padding: 0.6rem 1.2rem; transition: all 0.3s; border-radius: 50rem; }
            .nav-pills .nav-link:hover { background-color: #f8f9fa; color: #566a7f; }
            .nav-pills .nav-link.active { background-color: #696cff; color: #fff; box-shadow: 0 4px 6px rgba(105, 108, 255, 0.2); }
            .product-card { transition: all 0.3s ease; border: none; box-shadow: 0 0.125rem 0.25rem rgba(161, 172, 184, 0.2); border-radius: 1rem; }
            .product-card:hover { transform: translateY(-5px); box-shadow: 0 0.5rem 1rem rgba(161, 172, 184, 0.15); }
        </style>

        <div class="nav-align-top mb-4">
            <ul class="nav nav-pills gap-2 mb-4" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $tab == 'kendaraan' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-kendaraan" aria-controls="navs-top-kendaraan" aria-selected="{{ $tab == 'kendaraan' ? 'true' : 'false' }}">
                        <i class="bx bx-car me-2"></i> Kendaraan Operasional
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $tab == 'gedung' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-gedung" aria-controls="navs-top-gedung" aria-selected="{{ $tab == 'gedung' ? 'true' : 'false' }}">
                        <i class="bx bx-building-house me-2"></i> Gedung & Ruang Publik
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-pengaturan" aria-controls="navs-top-pengaturan" aria-selected="false">
                        <i class="bx bx-cog me-2"></i> Pengaturan & SOP
                    </button>
                </li>
            </ul>
            
            <div class="tab-content">
                <!-- TAB 1: KENDARAAN OPERASIONAL -->
                <div class="tab-pane fade {{ $tab == 'kendaraan' ? 'show active' : '' }}" id="navs-top-kendaraan" role="tabpanel">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 bg-white p-3 rounded-4 shadow-sm gap-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md bg-info-subtle text-info rounded-circle me-3 d-flex justify-content-center align-items-center">
                                <i class="bx bx-car fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Daftar Kendaraan Layanan Masyarakat</h5>
                                <small class="text-muted">Ambulans Darurat, Mobil Siaga, Truk Sampah, dll</small>
                            </div>
                        </div>
                        <div class="w-100 w-md-auto text-end">
                            <a href="{{ route('admin.unit.ambulans.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm w-100"><i class="bx bx-plus me-1"></i> Tambah Kendaraan</a>
                        </div>
                    </div>

                    @if($mobils->count() > 0)
                        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-2 g-md-3">
                            @foreach ($mobils as $mobil)
                                <div class="col">
                                    <div class="card h-100 product-card">
                                        <div class="position-relative">
                                            <div id="carouselMobil{{ $mobil->id }}" class="carousel slide" data-bs-ride="carousel">
                                                <div class="carousel-inner">
                                                    @if ($mobil->foto)
                                                    <div class="carousel-item active">
                                                        <img src="{{ asset('storage/' . $mobil->foto) }}" class="card-img-top"
                                                            alt="{{ $mobil->nama_mobil }}"
                                                            style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                                    </div>
                                                    @endif
                                                    @if ($mobil->foto_2)
                                                        <div class="carousel-item {{ !$mobil->foto ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $mobil->foto_2) }}" class="card-img-top"
                                                                alt="{{ $mobil->nama_mobil }}"
                                                                style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                                        </div>
                                                    @endif
                                                    @if ($mobil->foto_3)
                                                        <div class="carousel-item {{ !$mobil->foto && !$mobil->foto_2 ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $mobil->foto_3) }}" class="card-img-top"
                                                                alt="{{ $mobil->nama_mobil }}"
                                                                style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                                        </div>
                                                    @endif
                                                </div>
                                                <button class="carousel-control-prev" type="button"
                                                    data-bs-target="#carouselMobil{{ $mobil->id }}" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Previous</span>
                                                </button>
                                                <button class="carousel-control-next" type="button"
                                                    data-bs-target="#carouselMobil{{ $mobil->id }}" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Next</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                                <h5 class="card-title fw-bold text-capitalize mb-0">{{ $mobil->nama_mobil }}</h5>
                                                <span class="badge bg-label-info rounded-pill px-3 text-nowrap">{{ $mobil->kategori }}</span>
                                            </div>
                                            
                                            <div class="mt-4 pt-3 border-top d-flex gap-1 flex-nowrap justify-content-center">
                                                <a href="{{ route('admin.unit.ambulans.show', $mobil->id) }}"
                                                    class="btn btn-sm btn-outline-info flex-grow-1"><i class="bx bx-info-circle"></i></a>
                                                <a href="{{ route('admin.unit.ambulans.edit', $mobil->id) }}"
                                                    class="btn btn-sm btn-outline-warning flex-grow-1"><i class="bx bx-edit"></i></a>
                                                <form action="{{ route('admin.unit.ambulans.destroy', $mobil->id) }}" method="POST"
                                                    data-konfirmasi="Apakah Anda yakin ingin menghapus kendaraan ini?" class="d-flex flex-grow-1 m-0 p-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bx bx-trash"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if ($mobils->hasPages())
                            <div class="mt-4 d-flex justify-content-center">
                                {{ $mobils->links() }}
                            </div>
                        @endif
                    @else
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <div class="empty-state-icon mb-4">
                                    <i class="bx bx-car" style="font-size: 120px; color: #d1d5db;"></i>
                                </div>
                                <h3 class="fw-bold text-muted mb-3">Belum Ada Kendaraan</h3>
                                <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto;">
                                    Belum ada data kendaraan operasional (misalnya Ambulans Darurat).
                                </p>
                                <a href="{{ route('admin.unit.ambulans.create') }}" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                                    <i class="bx bx-plus-circle me-2"></i>Tambah Kendaraan
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 2: GEDUNG & RUANG PUBLIK -->
                <div class="tab-pane fade {{ $tab == 'gedung' ? 'show active' : '' }}" id="navs-top-gedung" role="tabpanel">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 bg-white p-3 rounded-4 shadow-sm gap-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md bg-success-subtle text-success rounded-circle me-3 d-flex justify-content-center align-items-center">
                                <i class="bx bx-building-house fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Daftar Gedung & Infrastruktur Publik</h5>
                                <small class="text-muted">Gedung Serbaguna, Balai Pertemuan, Lapangan, dll</small>
                            </div>
                        </div>
                        <div class="w-100 w-md-auto text-end">
                            <a href="{{ route('admin.unit.fasilitas_umum.create') }}" class="btn btn-success rounded-pill px-4 shadow-sm w-100"><i class="bx bx-plus me-1"></i> Tambah Gedung</a>
                        </div>
                    </div>

                    @if($fasilitas->count() > 0)
                        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-2 g-md-3">
                            @foreach ($fasilitas as $item)
                                <div class="col">
                                    <div class="card h-100 product-card">
                                        <div class="position-relative">
                                            <div id="carouselExample{{ $item->id }}" class="carousel slide" data-bs-ride="carousel">
                                                <div class="carousel-inner">
                                                    @if ($item->foto)
                                                    <div class="carousel-item active">
                                                        <img src="{{ asset('storage/' . $item->foto) }}" class="card-img-top"
                                                            alt="{{ $item->nama_fasilitas }}"
                                                            style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                                    </div>
                                                    @endif
                                                    @if ($item->foto_2)
                                                        <div class="carousel-item {{ !$item->foto ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $item->foto_2) }}" class="card-img-top"
                                                                alt="{{ $item->nama_fasilitas }}"
                                                                style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                                        </div>
                                                    @endif
                                                    @if ($item->foto_3)
                                                        <div class="carousel-item {{ !$item->foto && !$item->foto_2 ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $item->foto_3) }}" class="card-img-top"
                                                                alt="{{ $item->nama_fasilitas }}"
                                                                style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                                        </div>
                                                    @endif
                                                </div>
                                                <button class="carousel-control-prev" type="button"
                                                    data-bs-target="#carouselExample{{ $item->id }}" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Previous</span>
                                                </button>
                                                <button class="carousel-control-next" type="button"
                                                    data-bs-target="#carouselExample{{ $item->id }}" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Next</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                                <h5 class="card-title fw-bold text-capitalize mb-0">{{ $item->nama_fasilitas }}</h5>
                                                <span class="badge bg-label-success rounded-pill px-3 text-nowrap">{{ $item->kategori }}</span>
                                            </div>
                                            
                                            <div class="mt-4 pt-3 border-top d-flex gap-1 flex-nowrap justify-content-center">
                                                <a href="{{ route('admin.unit.fasilitas_umum.show', $item->id) }}"
                                                    class="btn btn-sm btn-outline-info flex-grow-1"><i class="bx bx-info-circle"></i></a>
                                                <a href="{{ route('admin.unit.fasilitas_umum.edit', $item->id) }}"
                                                    class="btn btn-sm btn-outline-warning flex-grow-1"><i class="bx bx-edit"></i></a>
                                                <form action="{{ route('admin.unit.fasilitas_umum.destroy', $item->id) }}" method="POST"
                                                    data-konfirmasi="Apakah Anda yakin ingin menghapus gedung/fasilitas ini?" class="d-flex flex-grow-1 m-0 p-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bx bx-trash"></i></button>
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
                        <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                            <div class="card-body">
                                <div class="mb-4">
                                    <i class="bx bx-building text-secondary" style="font-size: 80px; opacity: 0.5;"></i>
                                </div>
                                <h4 class="fw-bold">Belum Ada Fasilitas</h4>
                                <p class="text-muted mb-4">Mulai kelola infrastruktur desa dengan menambahkan fasilitas baru.</p>
                                <a href="{{ route('admin.unit.fasilitas_umum.create') }}" class="btn btn-success rounded-pill px-4">Tambah Gedung</a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 3: PENGATURAN SOP -->
                <div class="tab-pane fade" id="navs-top-pengaturan" role="tabpanel">
                    <form action="{{ route('admin.unit.fasilitas_umum.sop.update') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-xl-8">
                                <!-- Info Card -->
                                <div class="card border-0 shadow-sm rounded-4 mb-4">
                                    <div class="card-header bg-white border-bottom p-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-label-primary rounded-circle me-3 d-flex justify-content-center align-items-center"><i class="bx bx-phone-call"></i></div>
                                            <h5 class="mb-0 fw-bold">Kontak Layanan</h5>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-2">
                                            <label class="form-label text-dark fw-bold">Nomor WhatsApp Pengurus Fasilitas Umum & Ambulans</label>
                                            <div class="input-group input-group-merge border-light-subtle shadow-sm rounded-3">
                                                <span class="input-group-text"><i class="bx bxl-whatsapp text-success"></i></span>
                                                <input type="text" class="form-control" name="kontak_aula" value="{{ $regionSettings['kontak_aula'] ?? '' }}" placeholder="Contoh: 08123456789">
                                            </div>
                                            <small class="text-muted mt-2 d-block">Nomor ini akan dihubungi warga jika ada pertanyaan seputar peminjaman fasilitas.</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- SOP Card -->
                                <div class="card border-0 shadow-sm rounded-4 mb-4">
                                    <div class="card-header bg-white border-bottom p-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-label-info rounded-circle me-3 d-flex justify-content-center align-items-center"><i class="bx bx-book"></i></div>
                                            <h5 class="mb-0 fw-bold">SOP Peminjaman Gedung & Kendaraan</h5>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="alert alert-info d-flex align-items-start mb-4 shadow-sm border-0 rounded-4 p-3 text-dark">
                                            <i class="bx bx-info-circle fs-4 me-3 mt-1"></i>
                                            <div>
                                                <strong class="d-block mb-1">Informasi Penting</strong>
                                                <span>SOP ini akan ditampilkan kepada masyarakat sebagai syarat dan ketentuan sebelum mereka mengajukan peminjaman. Pilih salah satu model kebijakan di bawah ini.</span>
                                            </div>
                                        </div>

                                        <style>
                                            .sop-card {
                                                transition: all 0.2s ease-in-out;
                                                border: 2px solid #ffab00 !important;
                                                background-color: #fff3cd !important;
                                            }
                                            .sop-card.active-sop {
                                                border-width: 2px !important;
                                                box-shadow: 0 0.25rem 1rem rgba(255, 171, 0, 0.4) !important;
                                            }
                                            .sop-icon {
                                                color: #ffab00;
                                                font-size: 1.25rem;
                                                vertical-align: middle;
                                            }
                                        </style>

                                        <div class="row mb-4">
                                            <!-- Opsi A: Ditanggung -->
                                            <div class="col-md-6 mb-3">
                                                <div class="card sop-card {{ $sop_active == 'ditanggung' ? 'active-sop' : '' }} h-100">
                                                    <div class="card-header d-flex justify-content-between align-items-center pb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="sop_active" id="sop_active_ditanggung" value="ditanggung" {{ $sop_active == 'ditanggung' ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bold text-dark" for="sop_active_ditanggung">
                                                                <i class="bx bx-error sop-icon"></i> <span class="align-middle">PENTING: Ditanggung Penyewa</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <p class="text-muted small mb-2">Kerusakan fasilitas/kendaraan sepenuhnya menjadi tanggung jawab penyewa.</p>
                                                        
                                                        <div class="mb-3">
                                                            <textarea class="form-control border-light-subtle shadow-sm rounded-3" name="sop_ditanggung" id="sop_ditanggung_text" rows="8">{{ $sop_ditanggung }}</textarea>
                                                        </div>
                                                        
                                                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 shadow-sm" onclick="resetSop('ditanggung')">
                                                            <i class="bx bx-reset"></i> Reset ke Bawaan
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Opsi B: Tidak Ditanggung -->
                                            <div class="col-md-6 mb-3">
                                                <div class="card sop-card {{ $sop_active == 'tidak_ditanggung' ? 'active-sop' : '' }} h-100">
                                                    <div class="card-header d-flex justify-content-between align-items-center pb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="sop_active" id="sop_active_tidak_ditanggung" value="tidak_ditanggung" {{ $sop_active == 'tidak_ditanggung' ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bold text-dark" for="sop_active_tidak_ditanggung">
                                                                <i class="bx bx-error sop-icon"></i> <span class="align-middle">PENTING: Ditanggung Dana Desa</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <p class="text-muted small mb-2">Kerusakan tidak disengaja ditanggung oleh Dana Operasional (Gratis).</p>
                                                        
                                                        <div class="mb-3">
                                                            <textarea class="form-control border-light-subtle shadow-sm rounded-3" name="sop_tidak_ditanggung" id="sop_tidak_ditanggung_text" rows="8">{{ $sop_tidak_ditanggung }}</textarea>
                                                        </div>
                                                        
                                                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 shadow-sm" onclick="resetSop('tidak_ditanggung')">
                                                            <i class="bx bx-reset"></i> Reset ke Bawaan
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end mt-4">
                                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="bx bx-save me-1"></i> Simpan Pengaturan</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="card bg-primary text-white border-0 shadow-sm rounded-4 mb-4">
                                    <div class="card-body p-4">
                                        <h5 class="fw-bold text-white mb-3"><i class="bx bx-bulb me-2"></i>Tips Menulis SOP</h5>
                                        <ul class="list-unstyled mb-0" style="opacity: 0.9">
                                            <li class="mb-2"><i class="bx bx-check-circle me-2"></i>Sertakan biaya (jika ada) atau infokan jika gratis</li>
                                            <li class="mb-2"><i class="bx bx-check-circle me-2"></i>Jelaskan prosedur pengembalian aset/kendaraan</li>
                                            <li class="mb-2"><i class="bx bx-check-circle me-2"></i>Aturan denda jika terjadi kerusakan/keterlambatan</li>
                                            <li class="mb-2"><i class="bx bx-check-circle me-2"></i>Syarat dokumen yang harus dibawa warga</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // SOP Teks Bawaan
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

    // Interactive Card selection
    document.querySelectorAll('input[name="sop_active"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            // Reset border dari semua card
            document.querySelectorAll('.sop-card').forEach(function(card) {
                card.classList.remove('active-sop');
            });
            // Tambahkan border orange menyala ke card yang dipilih
            if(this.checked) {
                this.closest('.sop-card').classList.add('active-sop');
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    .card {
        transition: transform 0.2s ease;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.03);
    }
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.08);
    }
    .pagination .page-link {
        color: #495057;
        border: 1px solid #dee2e6;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }
    .pagination .page-link:hover {
        background-color: #f8f9fa;
        color: #0d6efd;
    }
    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white;
    }
    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
    }
</style>
@endpush
