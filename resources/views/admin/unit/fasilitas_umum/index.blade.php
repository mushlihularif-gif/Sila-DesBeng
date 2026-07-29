@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Unit Layanan /</span> Fasilitas Umum & Aset</h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="nav-align-top mb-4">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $tab == 'kendaraan' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-kendaraan" aria-controls="navs-top-kendaraan" aria-selected="{{ $tab == 'kendaraan' ? 'true' : 'false' }}"><i class="bx bx-car me-1"></i> Kendaraan Operasional</button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $tab == 'gedung' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-gedung" aria-controls="navs-top-gedung" aria-selected="{{ $tab == 'gedung' ? 'true' : 'false' }}"><i class="bx bx-building-house me-1"></i> Gedung & Ruang Publik</button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-pengaturan" aria-controls="navs-top-pengaturan" aria-selected="false"><i class="bx bx-cog me-1"></i> Pengaturan & SOP</button>
                </li>
            </ul>
            
            <div class="tab-content">
                <!-- TAB 1: KENDARAAN OPERASIONAL -->
                <div class="tab-pane fade {{ $tab == 'kendaraan' ? 'show active' : '' }}" id="navs-top-kendaraan" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-0">Daftar Kendaraan Layanan Masyarakat</h5>
                            <small class="text-muted">Ambulans Darurat, Mobil Siaga, Truk Sampah, dll</small>
                        </div>
                        <div>
                            <a href="{{ route('admin.unit.ambulans.create') }}" class="btn btn-primary"><i class="bx bx-plus"></i> Tambah Kendaraan</a>
                        </div>
                    </div>

                    @if($mobils->count() > 0)
                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
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
                                                            style="height: 300px; object-fit: cover; object-position: center;">
                                                    </div>
                                                    @endif
                                                    @if ($mobil->foto_2)
                                                        <div class="carousel-item {{ !$mobil->foto ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $mobil->foto_2) }}" class="card-img-top"
                                                                alt="{{ $mobil->nama_mobil }}"
                                                                style="height: 300px; object-fit: cover; object-position: center;">
                                                        </div>
                                                    @endif
                                                    @if ($mobil->foto_3)
                                                        <div class="carousel-item {{ !$mobil->foto && !$mobil->foto_2 ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $mobil->foto_3) }}" class="card-img-top"
                                                                alt="{{ $mobil->nama_mobil }}"
                                                                style="height: 300px; object-fit: cover; object-position: center;">
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
                                        <div class="card-body">
                                            <h5 class="card-title text-capitalize">{{ $mobil->kategori }}: {{ $mobil->nama_mobil }}</h5>
                                            <p class="card-text">{{ Str::limit($mobil->deskripsi, 100) }}</p>
                                            
                                            <div class="mt-3 d-flex gap-2">
                                                <a href="{{ route('admin.unit.ambulans.edit', $mobil->id) }}"
                                                    class="btn btn-sm btn-outline-warning">Ubah</a>
                                                <form action="{{ route('admin.unit.ambulans.destroy', $mobil->id) }}" method="POST"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus kendaraan ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-0">Daftar Gedung & Infrastruktur Publik</h5>
                            <small class="text-muted">Gedung Serbaguna, Balai Pertemuan, Lapangan, dll</small>
                        </div>
                        <div>
                            <a href="{{ route('admin.unit.fasilitas_umum.create') }}" class="btn btn-primary"><i class="bx bx-plus"></i> Tambah Gedung</a>
                        </div>
                    </div>

                    @if($fasilitas->count() > 0)
                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
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
                                                            style="height: 300px; object-fit: cover; object-position: center;">
                                                    </div>
                                                    @endif
                                                    @if ($item->foto_2)
                                                        <div class="carousel-item {{ !$item->foto ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $item->foto_2) }}" class="card-img-top"
                                                                alt="{{ $item->nama_fasilitas }}"
                                                                style="height: 300px; object-fit: cover; object-position: center;">
                                                        </div>
                                                    @endif
                                                    @if ($item->foto_3)
                                                        <div class="carousel-item {{ !$item->foto && !$item->foto_2 ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $item->foto_3) }}" class="card-img-top"
                                                                alt="{{ $item->nama_fasilitas }}"
                                                                style="height: 300px; object-fit: cover; object-position: center;">
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
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $item->nama_fasilitas }}</h5>
                                            <p class="card-text">{{ Str::limit($item->deskripsi, 100) }}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-success">{{ $item->stok }} {{ $item->satuan }}</span>
                                            </div>
                                            <div class="mt-3 d-flex gap-2">
                                                <a href="{{ route('admin.unit.fasilitas_umum.show', $item->id) }}"
                                                    class="btn btn-sm btn-outline-info">Detail</a>
                                                <a href="{{ route('admin.unit.fasilitas_umum.edit', $item->id) }}"
                                                    class="btn btn-sm btn-outline-warning">Ubah</a>
                                                <form action="{{ route('admin.unit.fasilitas_umum.destroy', $item->id) }}" method="POST"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus fasilitas ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
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
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <div class="empty-state-icon mb-4">
                                    <i class="bx bx-building" style="font-size: 120px; color: #d1d5db;"></i>
                                </div>
                                <h3 class="fw-bold text-muted mb-3">Belum Ada Fasilitas Umum</h3>
                                <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto;">
                                    Mulai tambahkan infrastruktur seperti balai desa, lapangan, atau gedung serbaguna.
                                </p>
                                <a href="{{ route('admin.unit.fasilitas_umum.create') }}" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                                    <i class="bx bx-plus-circle me-2"></i>Tambah Gedung Pertama
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 3: PENGATURAN SOP -->
                <div class="tab-pane fade" id="navs-top-pengaturan" role="tabpanel">
                    <form action="{{ route('admin.unit.fasilitas_umum.sop.update') }}" method="POST">
                        @csrf
                        <h6 class="mb-3">Kontak Layanan</h6>
                        <div class="mb-4">
                            <label class="form-label text-danger fw-bold">Nomor WhatsApp Pengurus Fasilitas Umum & Ambulans</label>
                            <input type="text" class="form-control" name="kontak_aula" value="{{ $regionSettings['kontak_aula'] ?? '' }}" placeholder="Contoh: 08123456789">
                            <small class="text-muted">Nomor ini akan dihubungi warga jika ada pertanyaan seputar peminjaman fasilitas.</small>
                        </div>
                        <hr>
                        
                        <h6 class="mb-3 mt-4">SOP Peminjaman Gedung & Kendaraan</h6>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Model Kebijakan Penanganan Kerusakan</label>
                            <select name="sop_active" class="form-select">
                                <option value="ditanggung" {{ $sop_active == 'ditanggung' ? 'selected' : '' }}>Ditanggung oleh Penyewa (Mengganti Rugi)</option>
                                <option value="tidak_ditanggung" {{ $sop_active == 'tidak_ditanggung' ? 'selected' : '' }}>Ditanggung Dana Desa (Gratis)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">SOP Jika Kerusakan Ditanggung Penyewa</label>
                            <textarea class="form-control" name="sop_ditanggung" rows="5">{{ $sop_ditanggung }}</textarea>
                            <button type="button" class="btn btn-sm btn-link p-0 mt-1" onclick="document.querySelector('textarea[name=sop_ditanggung]').value = `{{ $default_ditanggung }}`">Gunakan Teks Bawaan Sistem</button>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">SOP Jika Kerusakan Ditanggung Dana Desa</label>
                            <textarea class="form-control" name="sop_tidak_ditanggung" rows="5">{{ $sop_tidak_ditanggung }}</textarea>
                            <button type="button" class="btn btn-sm btn-link p-0 mt-1" onclick="document.querySelector('textarea[name=sop_tidak_ditanggung]').value = `{{ $default_tidak_ditanggung }}`">Gunakan Teks Bawaan Sistem</button>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

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
