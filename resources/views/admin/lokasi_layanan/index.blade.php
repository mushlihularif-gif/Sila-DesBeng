@extends('admin.layouts.admin')

@section('title', 'Lokasi Layanan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 py-3 mb-4">
        <h4 class="fw-bold mb-0"><span class="text-muted fw-light">Manajemen /</span> Lokasi Layanan</h4>
        <button class="btn btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalLokasi"
                onclick="siapkanFormulir()">
            <i class="bx bx-plus me-1"></i> Tambah Lokasi
        </button>
    </div>

    <div class="alert alert-info d-flex align-items-start mb-4" role="alert">
        <i class="bx bx-info-circle me-2 fs-5 mt-1"></i>
        <div>
            Titik layanan milik <strong>{{ $wilayah->name }}</strong> — gudang, kantor desa, pangkalan gas, balai.
            Satu daftar ini dipakai <strong>semua unit</strong>: Penjualan Gas, Penyewaan Alat, Penyewaan Mobil,
            Fasilitas Umum, dan Pasar Daerah. Titik peta yang Anda tentukan di sini akan terpakai ulang
            setiap kali lokasinya dipilih, tanpa perlu diketik lagi.
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
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        @forelse($lokasi as $l)
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 {{ $l->is_aktif ? '' : 'opacity-75' }}">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-start mb-3">
                            <span class="ikon-bulat rounded-circle {{ $l->punyaTitik() ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning' }} me-3"
                                  style="width: 42px; height: 42px;">
                                <i class="bx {{ $l->punyaTitik() ? 'bx-map-pin' : 'bx-map' }} fs-5"></i>
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <h6 class="fw-bold mb-1 text-truncate">{{ $l->nama }}</h6>
                                @if(! $l->is_aktif)
                                    <span class="badge bg-label-secondary rounded-pill" style="font-size: .65rem;">Nonaktif</span>
                                @endif
                                @if($l->jumlah_pakai > 0)
                                    <span class="badge bg-label-primary rounded-pill" style="font-size: .65rem;">
                                        Dipakai {{ $l->jumlah_pakai }} produk
                                    </span>
                                @else
                                    <span class="badge bg-label-secondary rounded-pill" style="font-size: .65rem;">Belum dipakai</span>
                                @endif
                            </div>
                        </div>

                        @if($l->alamat)
                            <p class="text-muted small mb-2">{{ $l->alamat }}</p>
                        @endif

                        <div class="small mb-3">
                            @if($l->punyaTitik())
                                <span class="text-success">
                                    <i class="bx bx-check-circle"></i>
                                    {{ number_format($l->latitude, 6) }}, {{ number_format($l->longitude, 6) }}
                                </span>
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $l->latitude }},{{ $l->longitude }}"
                                   target="_blank" rel="noopener" class="ms-2 text-decoration-none">
                                    <i class="bx bx-link-external"></i> Lihat
                                </a>
                            @else
                                {{-- Tanpa titik peta, warga tidak bisa melihat lokasinya
                                     di peta saat memesan, dan tarif borongan berbasis
                                     jarak tidak punya acuan. --}}
                                <span class="text-warning">
                                    <i class="bx bx-error-circle"></i> Titik peta belum ditentukan
                                </span>
                            @endif
                        </div>

                        @if($l->catatan)
                            <p class="text-muted small fst-italic mb-3">{{ $l->catatan }}</p>
                        @endif

                        <div class="d-flex gap-2 mt-auto pt-2 border-top">
                            <button class="btn btn-sm btn-outline-primary rounded-pill flex-grow-1"
                                    data-bs-toggle="modal" data-bs-target="#modalLokasi"
                                    onclick='siapkanFormulir(@json($l))'>
                                <i class="bx bx-edit-alt"></i> Ubah
                            </button>
                            <form action="{{ route('admin.lokasi-layanan.destroy', $l->id) }}" method="POST" class="d-inline"
                                  data-konfirmasi="Hapus lokasi &quot;{{ $l->nama }}&quot;? Tindakan ini tidak bisa dibatalkan."
                                  data-konfirmasi-judul="Hapus Lokasi"
                                  data-konfirmasi-ya="Ya, Hapus">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-5">
                        <span class="ikon-bulat rounded-circle bg-primary-subtle text-primary mb-3"
                              style="width: 64px; height: 64px;">
                            <i class="bx bx-map-alt fs-2"></i>
                        </span>
                        <h6 class="fw-bold mb-1">Belum Ada Lokasi Layanan</h6>
                        <p class="text-muted mb-3">
                            Tambahkan gudang, kantor desa, atau pangkalan gas Anda di sini.
                            Setelah itu lokasinya bisa langsung dipilih di formulir semua unit.
                        </p>
                        <button class="btn btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalLokasi"
                                onclick="siapkanFormulir()">
                            <i class="bx bx-plus me-1"></i> Tambah Lokasi Pertama
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

{{-- Modal tambah/ubah dipakai bersama halaman unit lain, jadi tinggal disertakan. --}}
@include("admin.lokasi_layanan._modal")
