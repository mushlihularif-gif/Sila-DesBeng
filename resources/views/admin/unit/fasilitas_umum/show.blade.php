@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Unit Layanan / Peminjaman Fasilitas Umum /</span> Detail Fasilitas
        </h4>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row gap-4">
                            
                            <!-- Gambar Carousel -->
                            <div class="flex-shrink-0" style="width: 100%; max-width: 400px;">
                                <div id="carouselBarang" class="carousel slide rounded overflow-hidden shadow-sm" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        <div class="carousel-item active">
                                            <img src="{{ asset('storage/' . $fasilitas->foto) }}" class="d-block w-100"
                                                alt="{{ $fasilitas->nama_fasilitas }}"
                                                style="height: 400px; object-fit: cover; object-position: center;">
                                        </div>
                                        @if ($fasilitas->foto_2)
                                            <div class="carousel-item">
                                                <img src="{{ asset('storage/' . $fasilitas->foto_2) }}" class="d-block w-100"
                                                    alt="{{ $fasilitas->nama_fasilitas }}"
                                                    style="height: 400px; object-fit: cover; object-position: center;">
                                            </div>
                                        @endif
                                        @if ($fasilitas->foto_3)
                                            <div class="carousel-item">
                                                <img src="{{ asset('storage/' . $fasilitas->foto_3) }}" class="d-block w-100"
                                                    alt="{{ $fasilitas->nama_fasilitas }}"
                                                    style="height: 400px; object-fit: cover; object-position: center;">
                                            </div>
                                        @endif
                                    </div>
                                    @if ($fasilitas->foto_2 || $fasilitas->foto_3)
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselBarang"
                                            data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselBarang"
                                            data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Informasi Detail -->
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h4 class="fw-bold mb-0 text-dark">Informasi Fasilitas</h4>
                                    <span class="badge {{ $fasilitas->status == 'tersedia' ? 'bg-success' : ($fasilitas->status == 'disewa' ? 'bg-warning' : 'bg-danger') }} fs-6 px-3 py-2 rounded-pill">
                                        {{ Str::upper($fasilitas->status) }}
                                    </span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold text-uppercase text-muted" style="width: 140px;">Nama Fasilitas</td>
                                                <td class="fw-semibold text-dark">{{ $fasilitas->nama_fasilitas }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-uppercase text-muted">Kategori</td>
                                                <td>{{ $fasilitas->kategori }}</td>
                                            </tr>

                                            <tr>
                                                <td class="fw-bold text-uppercase text-muted">Stok</td>
                                                <td>{{ $fasilitas->stok }} {{ Str::upper($fasilitas->satuan) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-uppercase text-muted">Lokasi</td>
                                                <td>{{ $fasilitas->lokasi }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-uppercase text-muted">Deskripsi</td>
                                                <td class="text-muted" style="line-height: 1.6;">{{ $fasilitas->deskripsi }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <hr class="my-4">

                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.unit.fasilitas_umum.edit', $fasilitas->id) }}" class="btn btn-warning px-4">
                                        <i class="bx bx-edit-alt me-1"></i> Ubah
                                    </a>
                                    <form action="{{ route('admin.unit.fasilitas_umum.destroy', $fasilitas->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus fasilitas ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger px-4">
                                            <i class="bx bx-trash me-1"></i> Hapus
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.unit.fasilitas_umum.index') }}" class="btn btn-outline-secondary px-4">
                                        <i class="bx bx-arrow-back me-1"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .table-borderless td {
        padding-top: 12px;
        padding-bottom: 12px;
        vertical-align: middle;
    }
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: rgba(0,0,0,0.3);
        border-radius: 50%;
        padding: 10px;
    }
</style>
@endpush

