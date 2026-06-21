@extends('admin.layouts.admin')

@section('title', 'Manajemen Banner')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem /</span> Manajemen Banner (Beranda)</h4>

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
                <h5 class="mb-1"><i class="bx bx-image me-2"></i> Daftar Banner</h5>
                <small class="text-muted"><i class="bx bx-info-circle"></i> Maksimal ukuran file <b>5MB</b>. Rekomendasi dimensi: <b>1774 x 887 piksel</b>.</small>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBannerModal">
                <i class="bx bx-plus me-1"></i> Tambah Banner
            </button>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Status</th>
                        <th>Urutan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($banners as $banner)
                    <tr>
                        <td>
                            <img src="{{ Storage::url($banner->image_path) }}" alt="Banner" class="rounded" style="max-height: 50px; max-width: 100px; object-fit: cover;">
                        </td>
                        <td>
                            <span class="badge bg-label-{{ $banner->is_active ? 'success' : 'secondary' }}">
                                {{ $banner->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>{{ $banner->sort_order }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editBannerModal{{ $banner->id }}">Edit</button>
                            <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus banner ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editBannerModal{{ $banner->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Banner</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Gambar Saat Ini</label><br>
                                            <img src="{{ Storage::url($banner->image_path) }}" class="img-thumbnail mb-2" style="max-height: 150px;">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Ganti Gambar (Opsional)</label>
                                            <input type="file" name="image" class="form-control" accept="image/*">
                                            <small class="text-muted d-block mt-1">
                                                <i class="bx bx-info-circle"></i> Maksimal ukuran file <b>5MB</b>. Rekomendasi dimensi: <b>1774 x 887 piksel</b>.
                                            </small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Urutan Tampil</label>
                                            <input type="number" name="sort_order" class="form-control" value="{{ $banner->sort_order }}">
                                        </div>
                                        <div class="mb-3 form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive{{ $banner->id }}" {{ $banner->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="isActive{{ $banner->id }}">Aktifkan Banner</label>
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
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada banner iklan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Banner Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Gambar Banner <span class="text-danger">*</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                        <small class="text-muted d-block mt-1">
                            <i class="bx bx-info-circle"></i> Maksimal ukuran file <b>5MB</b>. Rekomendasi dimensi: <b>1774 x 887 piksel</b>.
                        </small>
                    </div>
                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActiveNew" checked>
                        <label class="form-check-label" for="isActiveNew">Aktifkan Banner</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
