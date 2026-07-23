@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Unit Layanan /</span> Peminjaman Fasilitas Umum</h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="nav-align-top mb-4">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-data" aria-controls="navs-top-data" aria-selected="true">Daftar Fasilitas</button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-pengaturan" aria-controls="navs-top-pengaturan" aria-selected="false">Pengaturan & SOP</button>
                </li>
            </ul>
            
            <div class="tab-content">
                <!-- TAB: Data Fasilitas -->
                <div class="tab-pane fade show active" id="navs-top-data" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <div class="alert alert-warning d-flex align-items-center p-2 mb-0 text-dark" style="font-size: 0.85rem; border-left: 4px solid #ffab00;">
                                <i class="bx bx-error me-2 fs-5"></i>
                                <div><strong>PENTING:</strong> Pastikan Anda telah mengatur Kontak Layanan dan SOP di tab sebelah.</div>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('admin.unit.fasilitas_umum.create') }}" class="btn btn-primary"><i class="bx bx-plus"></i> Tambah Fasilitas</a>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    @if($fasilitas->count() > 0)
                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                            @foreach ($fasilitas as $item)
                                <div class="col">
                                    <div class="card h-100 product-card">
                                        <div class="position-relative">
                                            <div id="carouselExample{{ $item->id }}" class="carousel slide" data-bs-ride="carousel">
                                                <div class="carousel-inner">
                                                    <div class="carousel-item active">
                                                        <img src="{{ asset('storage/' . $item->foto) }}" class="card-img-top"
                                                            alt="{{ $item->nama_fasilitas }}"
                                                            style="height: 300px; object-fit: cover; object-position: center;">
                                                    </div>
                                                    @if ($item->foto_2)
                                                        <div class="carousel-item">
                                                            <img src="{{ asset('storage/' . $item->foto_2) }}" class="card-img-top"
                                                                alt="{{ $item->nama_fasilitas }}"
                                                                style="height: 300px; object-fit: cover; object-position: center;">
                                                        </div>
                                                    @endif
                                                    @if ($item->foto_3)
                                                        <div class="carousel-item">
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
                    @else
                        <!-- Empty State -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body text-center py-5">
                                        @if($search)
                                            <!-- Search Not Found -->
                                            <div class="empty-state-icon mb-4">
                                                <i class="bx bx-search-alt" style="font-size: 120px; color: #d1d5db;"></i>
                                            </div>
                                            <h3 class="fw-bold text-muted mb-3">Tidak Ditemukan</h3>
                                            <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto;">
                                                Tidak ada fasilitas yang cocok dengan pencarian "<strong>{{ $search }}</strong>". 
                                            </p>
                                            <div class="mt-3">
                                                <a href="{{ route('admin.unit.fasilitas_umum.index') }}" class="btn btn-outline-secondary">
                                                <i class="bx bx-refresh me-2"></i>Tampilkan Semua Fasilitas
                                                </a>
                                            </div>
                                        @else
                                            <!-- No Products -->
                                            <div class="empty-state-icon mb-4">
                                                <i class="bx bx-package" style="font-size: 120px; color: #d1d5db;"></i>
                                            </div>
                                            <h3 class="fw-bold text-muted mb-3">Belum Ada Fasilitas Umum</h3>
                                            <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto;">
                                                Anda belum menambahkan fasilitas umum apapun. Mulai tambahkan fasilitas seperti balai desa, lapangan olahraga, atau gedung serbaguna untuk ditampilkan kepada pengguna.
                                            </p>
                                            <a href="{{ route('admin.unit.fasilitas_umum.create') }}" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                                                <i class="bx bx-plus-circle me-2"></i>Tambah Fasilitas Pertama
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Pagination -->
                    @if ($fasilitas->hasPages())
                        <div class="mt-4 d-flex justify-content-center">
                            {{ $fasilitas->links() }}
                        </div>
                    @endif
                </div>

                <!-- TAB: Pengaturan SOP & Kontak -->
                <div class="tab-pane fade" id="navs-top-pengaturan" role="tabpanel">
                    <form action="{{ route('admin.unit.fasilitas_umum.sop.update') }}" method="POST">
                        @csrf
                        <h6 class="mb-3">Kontak Layanan Peminjaman</h6>
                        <div class="mb-4">
                            <label class="form-label text-danger fw-bold">Nomor WhatsApp Pengurus Fasilitas (Halo Layanan)</label>
                            <input type="text" class="form-control" name="kontak_aula" value="{{ $regionSettings['kontak_aula'] ?? '' }}" placeholder="Contoh: 08123456789">
                            <small class="text-muted">Nomor ini akan dihubungi warga jika ada pertanyaan seputar peminjaman fasilitas (Tampil sebagai tombol Halo Layanan di HP warga).</small>
                        </div>
                        <hr>
                        
                        <h6 class="mb-3 mt-4">SOP Peminjaman & Kerusakan</h6>
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


