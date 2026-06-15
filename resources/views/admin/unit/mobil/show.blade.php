@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Unit Layanan / Penyewaan Mobil /</span> Detail Alat
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
                                            <img src="{{ asset('storage/' . $mobil->foto) }}" class="d-block w-100"
                                                alt="{{ $mobil->nama_mobil }}"
                                                style="height: 400px; object-fit: cover; object-position: center;">
                                        </div>
                                        @if ($mobil->foto_2)
                                            <div class="carousel-item">
                                                <img src="{{ asset('storage/' . $mobil->foto_2) }}" class="d-block w-100"
                                                    alt="{{ $mobil->nama_mobil }}"
                                                    style="height: 400px; object-fit: cover; object-position: center;">
                                            </div>
                                        @endif
                                        @if ($mobil->foto_3)
                                            <div class="carousel-item">
                                                <img src="{{ asset('storage/' . $mobil->foto_3) }}" class="d-block w-100"
                                                    alt="{{ $mobil->nama_mobil }}"
                                                    style="height: 400px; object-fit: cover; object-position: center;">
                                            </div>
                                        @endif
                                    </div>
                                    @if ($mobil->foto_2 || $mobil->foto_3)
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
                                    <h4 class="fw-bold mb-0 text-dark">Informasi Alat</h4>
                                    <span class="badge {{ $mobil->status == 'tersedia' ? 'bg-success' : ($mobil->status == 'disewa' ? 'bg-warning' : 'bg-danger') }} fs-6 px-3 py-2 rounded-pill">
                                        {{ Str::upper($mobil->status) }}
                                    </span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold text-uppercase text-muted" style="width: 140px;">Nama Alat</td>
                                                <td class="fw-semibold text-dark">{{ $mobil->nama_mobil }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-uppercase text-muted">Kategori</td>
                                                <td>{{ $mobil->kategori }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-uppercase text-muted">Harga Sewa</td>
                                                <td>
                                                    <h3 class="text-primary fw-bold mb-0">Rp. {{ number_format($mobil->harga_sewa, 0, ',', '.') }} <span class="fs-6 text-muted fw-normal">/ {{ $mobil->satuan }}</span></h3>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-uppercase text-muted">Stok</td>
                                                <td>{{ $mobil->stok }} {{ Str::upper($mobil->satuan) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-uppercase text-muted">Lokasi</td>
                                                <td>{{ $mobil->lokasi }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-uppercase text-muted">Deskripsi</td>
                                                <td class="text-muted" style="line-height: 1.6;">{{ $mobil->deskripsi }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <hr class="my-4">

                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.unit.mobil.edit', $mobil->id) }}" class="btn btn-warning px-4">
                                        <i class="bx bx-edit-alt me-1"></i> Ubah
                                    </a>
                                    <form action="{{ route('admin.unit.mobil.destroy', $mobil->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus alat ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger px-4">
                                            <i class="bx bx-trash me-1"></i> Hapus
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.unit.mobil.index') }}" class="btn btn-outline-secondary px-4">
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
