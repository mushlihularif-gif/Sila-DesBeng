@extends('admin.layouts.admin')

@section('title', 'Manajemen Kategori Produk')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Manajemen /</span> Kategori Produk</h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <h6 class="alert-heading d-flex align-items-center fw-bold mb-1"><i class="bx bx-error-circle me-2"></i>Terjadi Kesalahan!</h6>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1"><i class="bx bx-category me-2"></i> Daftar Kategori</h5>
                <small class="text-muted"><i class="bx bx-info-circle"></i> Kategori yang dihapus tidak akan menghapus produk lama (hanya disembunyikan dari pilihan produk baru).</small>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bx bx-plus me-1"></i> Tambah Kategori
            </button>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Peruntukan (Modul)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($categories as $index => $category)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-semibold text-primary">{{ $category->name }}</td>
                        <td>
                            @if($category->type == 'barang')
                                <span class="badge bg-label-info"><i class="bx bx-package me-1"></i>Penyewaan Alat</span>
                            @elseif($category->type == 'mobil')
                                <span class="badge bg-label-warning"><i class="bx bx-car me-1"></i>Penyewaan Mobil</span>
                            @elseif($category->type == 'gas')
                                <span class="badge bg-label-success"><i class="bx bxs-gas-pump me-1"></i>Unit Gas</span>
                            @elseif($category->type == 'fasilitas')
                                <span class="badge bg-label-primary"><i class="bx bx-building-house me-1"></i>Fasilitas Umum</span>
                            @else
                                <span class="badge bg-label-secondary">Umum / Semua</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}">Edit</button>
                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Produk yang sudah ada tidak akan terhapus.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>

                    @push('modals')
                    <!-- Edit Modal -->
                    <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Kategori</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Peruntukan Modul <span class="text-muted">(Opsional)</span></label>
                                            <select class="form-select" name="type">
                                                <option value="" {{ empty($category->type) ? 'selected' : '' }}>Umum (Tampil di Semua)</option>
                                                <option value="barang" {{ $category->type == 'barang' ? 'selected' : '' }}>Penyewaan Alat</option>
                                                <option value="mobil" {{ $category->type == 'mobil' ? 'selected' : '' }}>Penyewaan Mobil</option>
                                                <option value="gas" {{ $category->type == 'gas' ? 'selected' : '' }}>Penjualan Gas</option>
                                                <option value="fasilitas" {{ $category->type == 'fasilitas' ? 'selected' : '' }}>Fasilitas Umum</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endpush

                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4">Belum ada kategori yang ditambahkan. Silakan tambah kategori baru.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('modals')
<!-- Add Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Perlengkapan Acara" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Peruntukan Modul <span class="text-muted">(Opsional)</span></label>
                        <select class="form-select" name="type">
                            <option value="">Umum (Tampil di Semua)</option>
                            <option value="barang">Penyewaan Alat</option>
                            <option value="mobil">Penyewaan Mobil</option>
                            <option value="gas">Penjualan Gas</option>
                            <option value="fasilitas">Fasilitas Umum</option>
                        </select>
                        <small class="text-muted d-block mt-1">Jika dikosongkan, kategori ini akan muncul di seluruh fitur penyewaan/penjualan.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endpush
@endsection