@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div class="flex-grow-1">
                <h4 class="fw-bold m-0 mb-2"><span class="text-muted fw-light">Unit Layanan /</span> Penyewaan Mobil</h4>
                <div class="alert alert-warning d-inline-flex align-items-center p-2 mb-0 text-dark" style="font-size: 0.85rem; border-left: 4px solid #ffab00;">
                    <i class="bx bx-error me-2 fs-5"></i>
                    <div><strong>PENTING:</strong> Tentukan dan Pastikan Ketentuan SOP sesuai dengan ketentuan daerah anda.</div>
                </div>
            </div>
            <div class="d-flex flex-wrap flex-sm-nowrap gap-2 justify-content-md-end flex-shrink-0">
                <a href="{{ route('admin.unit.mobil.sop') }}" class="btn btn-outline-info flex-grow-1 flex-sm-grow-0 text-nowrap"><i class="bx bx-cog me-1"></i> SOP</a>
                <a href="{{ route('admin.unit.mobil.create') }}" class="btn btn-primary flex-grow-1 flex-sm-grow-0 text-nowrap"><i class="bx bx-plus me-1"></i> Tambah</a>
            </div>
        </div>

        <div class="nav-align-top mb-4">
            <ul class="nav nav-pills gap-2 mb-4" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $tab == 'katalog' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-mobil-katalog" aria-controls="navs-mobil-katalog" aria-selected="{{ $tab == 'katalog' ? 'true' : 'false' }}">
                        <i class="bx bx-car me-1"></i> Daftar Mobil
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $tab == 'chat' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-mobil-chat" aria-controls="navs-mobil-chat" aria-selected="{{ $tab == 'chat' ? 'true' : 'false' }}">
                        <i class="bx bx-chat me-1"></i> Layanan Pesan
                        @if(isset($totalUnreadChats) && $totalUnreadChats > 0)
                            <span class="badge rounded-pill bg-danger ms-1 px-2 py-0" style="font-size: 0.75rem;">{{ $totalUnreadChats }}</span>
                        @endif
                    </button>
                </li>
            </ul>

            <div class="tab-content bg-transparent p-0 border-0 shadow-none">
                <!-- TAB 1: DAFTAR MOBIL -->
                <div class="tab-pane fade {{ $tab == 'katalog' ? 'show active' : '' }}" id="navs-mobil-katalog" role="tabpanel">
                    <!-- Products Grid -->
                    @if($mobils->count() > 0)
                        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-2 g-md-3">
                            @foreach ($mobils as $mobil)
                    <div class="col">
                        <div class="card h-100 product-card">
                            <div class="position-relative">
                                <div id="carouselExample{{ $mobil->id }}" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        <div class="carousel-item active">
                                            <img src="{{ asset('storage/' . $mobil->foto) }}" class="card-img-top"
                                                alt="{{ $mobil->nama_mobil }}"
                                                style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                        </div>
                                        @if ($mobil->foto_2)
                                            <div class="carousel-item">
                                                <img src="{{ asset('storage/' . $mobil->foto_2) }}" class="card-img-top"
                                                    alt="{{ $mobil->nama_mobil }}"
                                                    style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                            </div>
                                        @endif
                                        @if ($mobil->foto_3)
                                            <div class="carousel-item">
                                                <img src="{{ asset('storage/' . $mobil->foto_3) }}" class="card-img-top"
                                                    alt="{{ $mobil->nama_mobil }}"
                                                    style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                            </div>
                                        @endif
                                    </div>
                                    <button class="carousel-control-prev" type="button"
                                        data-bs-target="#carouselExample{{ $mobil->id }}" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button"
                                        data-bs-target="#carouselExample{{ $mobil->id }}" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $mobil->nama_mobil }}</h5>
                                
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                                    <span class="badge bg-primary">Mulai Rp.
                                        {{ number_format($mobil->harga_dalam_desa ?? 0, 0, ',', '.') }}</span>
                                    <span class="badge bg-success">{{ $mobil->stok }} {{ $mobil->satuan }}</span>
                                </div>
                                <div class="mt-3 d-flex gap-1 flex-nowrap justify-content-center">
                                    <a href="{{ route('admin.unit.mobil.show', $mobil->id) }}"
                                        class="btn btn-sm btn-outline-info flex-grow-1"><i class="bx bx-info-circle"></i></a>
                                    <a href="{{ route('admin.unit.mobil.edit', $mobil->id) }}"
                                        class="btn btn-sm btn-outline-warning flex-grow-1"><i class="bx bx-edit"></i></a>
                                    <form action="{{ route('admin.unit.mobil.destroy', $mobil->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus alat ini?');" class="d-flex flex-grow-1 m-0 p-0">
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
                                    Tidak ada alat yang cocok dengan pencarian "<strong>{{ $search }}</strong>". 
                                    Coba gunakan kata kunci lain atau hapus filter pencarian.
                                </p>
                                <a href="{{ route('admin.unit.mobil.index') }}" class="btn btn-outline-primary btn-lg">
                                    <i class="bx bx-refresh me-2"></i>Tampilkan Semua Alat
                                </a>
                            @else
                                <!-- No Products -->
                                <div class="empty-state-icon mb-4">
                                    <i class="bx bx-package" style="font-size: 120px; color: #d1d5db;"></i>
                                </div>
                                <h3 class="fw-bold text-muted mb-3">Belum Ada Alat Penyewaan</h3>
                                <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto;">
                                    Anda belum menambahkan alat penyewaan apapun. Mulai tambahkan alat seperti tenda, sound system, atau perlengkapan acara lainnya untuk ditampilkan kepada pengguna.
                                </p>
                                <a href="{{ route('admin.unit.mobil.create') }}" class="btn btn-primary btn-lg">
                                    <i class="bx bx-plus-circle me-2"></i>Tambah Alat Pertama
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Pagination -->
        @if ($mobils->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                <nav>
                    <ul class="pagination mb-0">
                        {{-- Sebelumnya --}}
                        @if ($mobils->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">« Sebelumnya</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $mobils->previousPageUrl() }}" rel="prev">« Sebelumnya</a>
                            </li>
                        @endif

                        {{-- Selanjutnya --}}
                        @if ($mobils->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $mobils->nextPageUrl() }}" rel="next">Selanjutnya »</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">Selanjutnya »</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif
                </div>

                <!-- TAB 2: CHAT WARGA MOBIL -->
                <div class="tab-pane fade {{ $tab == 'chat' ? 'show active' : '' }}" id="navs-mobil-chat" role="tabpanel">
                    @include('admin.unit.partials.unit_chat_panel', [
                        'serviceType' => 'mobil',
                        'chatServiceTitle' => 'Penyewaan Mobil / Kendaraan',
                        'chats' => $chats
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .nav-pills .nav-link { color: #6c757d; font-weight: 600; padding: 0.6rem 1.2rem; transition: all 0.3s; border-radius: 50rem; }
    .nav-pills .nav-link:hover { background-color: #f8f9fa; color: #566a7f; }
    .nav-pills .nav-link.active { background-color: #696cff; color: #fff; box-shadow: 0 4px 6px rgba(105, 108, 255, 0.2); }
    
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

