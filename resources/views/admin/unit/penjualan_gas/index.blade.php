@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Unit Layanan /</span> Penjualan Gas</h4>
            <div class="d-flex gap-2">
                @php
                    $admin = auth()->user();
                    $region = \App\Models\Region::find($admin->region_id);
                    $settings = $region ? $region->settings : [];
                    $isCrisisMode = isset($settings['crisis_mode_gas']) ? $settings['crisis_mode_gas'] : false;
                    $quotaLimit = $settings['gas_quota_limit'] ?? 1;
                    $quotaDays = $settings['gas_quota_days'] ?? 7;
                @endphp
                <button type="button" class="btn {{ $isCrisisMode ? 'btn-danger' : 'btn-outline-danger' }}" data-bs-toggle="modal" data-bs-target="#crisisModeModal">
                    <i class='bx {{ $isCrisisMode ? 'bx-error-circle' : 'bx-shield-quarter' }}'></i> 
                    {{ $isCrisisMode ? 'MODE KRISIS AKTIF' : 'Pengaturan Mode Krisis' }}
                </button>
                <a href="{{ route('admin.unit.penjualan_gas.create') }}" class="btn btn-primary">Tambah Gas</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Products Grid -->
        @if($gases->count() > 0)
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                @foreach ($gases as $gas)
                    <div class="col">
                        <div class="card h-100 product-card">
                            <div class="position-relative">
                                <div id="carouselExample{{ $gas->id }}" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        <div class="carousel-item active">
                                            <img src="{{ asset('storage/' . $gas->foto) }}" class="card-img-top"
                                                alt="{{ $gas->jenis_gas }}"
                                                style="height: 300px; object-fit: cover; object-position: center;">
                                        </div>
                                        @if ($gas->foto_2)
                                            <div class="carousel-item">
                                                <img src="{{ asset('storage/' . $gas->foto_2) }}" class="card-img-top"
                                                    alt="{{ $gas->jenis_gas }}"
                                                    style="height: 300px; object-fit: cover; object-position: center;">
                                            </div>
                                        @endif
                                        @if ($gas->foto_3)
                                            <div class="carousel-item">
                                                <img src="{{ asset('storage/' . $gas->foto_3) }}" class="card-img-top"
                                                    alt="{{ $gas->jenis_gas }}"
                                                    style="height: 300px; object-fit: cover; object-position: center;">
                                            </div>
                                        @endif
                                    </div>
                                    @if ($gas->foto_2 || $gas->foto_3)
                                        <button class="carousel-control-prev" type="button"
                                            data-bs-target="#carouselExample{{ $gas->id }}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button"
                                            data-bs-target="#carouselExample{{ $gas->id }}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $gas->jenis_gas }}</h5>
                                <p class="card-text">{{ Str::limit($gas->deskripsi, 100) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary">Rp.
                                        {{ number_format($gas->harga_satuan, 0, ',', '.') }}</span>
                                    <span class="badge bg-success">{{ $gas->stok }} {{ Str::upper($gas->satuan) }}</span>
                                </div>
                                <div class="mt-3 d-flex gap-2">
                                    <a href="{{ route('admin.unit.penjualan_gas.show', $gas->id) }}"
                                        class="btn btn-sm btn-outline-info">Detail</a>
                                    <a href="{{ route('admin.unit.penjualan_gas.edit', $gas->id) }}"
                                        class="btn btn-sm btn-outline-warning">Ubah</a>
                                    <form action="{{ route('admin.unit.penjualan_gas.destroy', $gas->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus gas ini?');">
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
                                    Tidak ada produk gas yang cocok dengan pencarian "<strong>{{ $search }}</strong>". 
                                    Coba gunakan kata kunci lain atau hapus filter pencarian.
                                </p>
                                <a href="{{ route('admin.unit.penjualan_gas.index') }}" class="btn btn-outline-primary btn-lg">
                                    <i class="bx bx-refresh me-2"></i>Tampilkan Semua Gas
                                </a>
                            @else
                                <!-- No Products -->
                                <div class="empty-state-icon mb-4">
                                    <i class="bx bx-gas-pump" style="font-size: 120px; color: #d1d5db;"></i>
                                </div>
                                <h3 class="fw-bold text-muted mb-3">Belum Ada Produk Gas</h3>
                                <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto;">
                                    Anda belum menambahkan produk gas apapun. Mulai tambahkan produk gas LPG 3kg, 5.5kg, 12kg, atau jenis gas lainnya untuk ditampilkan kepada pengguna.
                                </p>
                                <a href="{{ route('admin.unit.penjualan_gas.create') }}" class="btn btn-primary btn-lg">
                                    <i class="bx bx-plus-circle me-2"></i>Tambah Gas Pertama
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Pagination: Bahasa Indonesia -->
        @if ($gases->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                <nav>
                    <ul class="pagination mb-0">
                        {{-- Sebelumnya --}}
                        @if ($gases->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">« Sebelumnya</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $gases->previousPageUrl() }}" rel="prev">« Sebelumnya</a>
                            </li>
                        @endif

                        {{-- Selanjutnya --}}
                        @if ($gases->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $gases->nextPageUrl() }}" rel="next">Selanjutnya »</a>
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
@endsection

@push('modals')
    <!-- Modal Pengaturan Mode Krisis -->
    <div class="modal fade" id="crisisModeModal" tabindex="-1" aria-labelledby="crisisModeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ route('admin.unit.penjualan_gas.crisis_mode') }}" method="POST" class="modal-content border-0 shadow-lg rounded-4">
                @csrf
                <div class="modal-header border-bottom border-light-subtle p-4">
                    <h5 class="modal-title text-danger fw-bold d-flex align-items-center" id="crisisModeModalLabel">
                        <i class='bx bx-shield-quarter fs-3 me-2'></i> Pengaturan Mode Krisis Elpiji
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 p-md-5">
                    <!-- Info Section -->
                    <div class="alert alert-warning d-flex align-items-start mb-4 shadow-sm border-0 rounded-4 p-4 text-dark">
                        <i class="bx bx-info-circle fs-3 me-3 mt-1 text-warning"></i>
                        <div>
                            <strong class="d-block mb-1 fs-6">Apa itu Mode Krisis?</strong>
                            <span style="font-size: 0.95rem; opacity: 0.9;">Fitur ini digunakan <b>hanya saat terjadi kelangkaan gas Elpiji</b> di pasaran. Saat diaktifkan, sistem akan otomatis membatasi jumlah tabung yang bisa dibeli oleh setiap warga dalam kurun waktu tertentu untuk mencegah tindakan penimbunan.</span>
                        </div>
                    </div>

                    <label class="form-label fw-bold mb-3 fs-6">Pilih Status Penjualan Saat Ini</label>
                    <div class="row mb-4 g-3">
                        <!-- Opsi Normal -->
                        <div class="col-md-6">
                            <label class="card h-100 border crisis-card normal-card {{ !$isCrisisMode ? 'border-success shadow-sm' : 'border-light-subtle' }}" style="transition: all 0.2s; cursor: pointer; background-color: {{ !$isCrisisMode ? '#e8fadf' : '#fff' }};">
                                <div class="card-body p-4 text-center">
                                    <div class="form-check d-flex justify-content-center mb-3">
                                        <input class="form-check-input" style="transform: scale(1.2);" type="radio" name="is_crisis_mode" id="mode_normal" value="0" {{ !$isCrisisMode ? 'checked' : '' }} onchange="toggleCrisisInputs()">
                                    </div>
                                    <div class="avatar avatar-md bg-success rounded-circle mx-auto mb-3 d-flex justify-content-center align-items-center shadow-sm">
                                        <i class="bx bx-check text-white fs-3"></i>
                                    </div>
                                    <h5 class="fw-bold text-success mb-2">Stok Aman / Normal</h5>
                                    <span class="text-muted d-block" style="font-size: 0.85rem;">Warga bebas membeli gas tanpa batasan kuota harian.</span>
                                </div>
                            </label>
                        </div>

                        <!-- Opsi Krisis -->
                        <div class="col-md-6">
                            <label class="card h-100 border crisis-card krisis-card {{ $isCrisisMode ? 'border-danger shadow-sm' : 'border-light-subtle' }}" style="transition: all 0.2s; cursor: pointer; background-color: {{ $isCrisisMode ? '#ffe0db' : '#fff' }};">
                                <div class="card-body p-4 text-center">
                                    <div class="form-check d-flex justify-content-center mb-3">
                                        <input class="form-check-input" style="transform: scale(1.2);" type="radio" name="is_crisis_mode" id="mode_krisis" value="1" {{ $isCrisisMode ? 'checked' : '' }} onchange="toggleCrisisInputs()">
                                    </div>
                                    <div class="avatar avatar-md bg-danger rounded-circle mx-auto mb-3 d-flex justify-content-center align-items-center shadow-sm">
                                        <i class="bx bx-error text-white fs-3"></i>
                                    </div>
                                    <h5 class="fw-bold text-danger mb-2">Terjadi Kelangkaan</h5>
                                    <span class="text-muted d-block" style="font-size: 0.85rem;">Sistem akan membatasi pembelian warga secara otomatis.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Pengaturan Batasan (hanya tampil jika krisis) -->
                    <div id="crisis_settings_wrapper" class="{{ !$isCrisisMode ? 'd-none' : '' }}">
                        <div class="p-4 rounded-4 border-0" style="background-color: #fff2f0;">
                            <h6 class="fw-bold text-danger mb-3 d-flex align-items-center"><i class="bx bx-cog fs-4 me-2"></i> Aturan Pembatasan Kuota</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">Maksimal Beli</label>
                                    <div class="input-group input-group-merge shadow-sm rounded-3">
                                        <input type="number" name="quota_limit" class="form-control px-3" min="1" value="{{ $quotaLimit }}" placeholder="Misal: 1">
                                        <span class="input-group-text bg-white fw-bold">Tabung</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">Setiap Rentang Waktu</label>
                                    <div class="input-group input-group-merge shadow-sm rounded-3">
                                        <input type="number" name="quota_days" class="form-control px-3" min="1" value="{{ $quotaDays }}" placeholder="Misal: 7">
                                        <span class="input-group-text bg-white fw-bold">Hari</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-white rounded-3 shadow-sm border border-danger border-opacity-25">
                                <div class="d-flex align-items-start">
                                    <i class="bx bx-info-circle text-danger fs-4 me-2 mt-1"></i>
                                    <p class="mb-0 text-dark" style="font-size: 0.9rem; line-height: 1.5;">
                                        <b>Contoh Simulasi:</b> Jika diatur <b>1 tabung</b> dan <b>7 hari</b>, maka warga yang telah membeli 1 tabung gas hari ini, baru bisa membeli kembali setelah 7 hari kemudian.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-4 border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm"><i class="bx bx-save me-1"></i> Terapkan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@section('scripts')
<script>
    function toggleCrisisInputs() {
        const isKrisis = document.getElementById('mode_krisis').checked;
        const wrapper = document.getElementById('crisis_settings_wrapper');
        
        // Update styling of cards
        document.querySelectorAll('.crisis-card').forEach(card => {
            card.classList.remove('shadow-sm');
            if (card.classList.contains('normal-card')) {
                card.classList.remove('border-success');
                card.classList.add('border-light-subtle');
                card.style.backgroundColor = '#fff';
            } else {
                card.classList.remove('border-danger');
                card.classList.add('border-light-subtle');
                card.style.backgroundColor = '#fff';
            }
        });

        if (isKrisis) {
            wrapper.classList.remove('d-none');
            // Add style to active krisis card
            const krisisCard = document.querySelector('.krisis-card');
            krisisCard.classList.remove('border-light-subtle');
            krisisCard.classList.add('border-danger', 'shadow-sm');
            krisisCard.style.backgroundColor = '#ffe0db';
        } else {
            wrapper.classList.add('d-none');
            // Add style to active normal card
            const normalCard = document.querySelector('.normal-card');
            normalCard.classList.remove('border-light-subtle');
            normalCard.classList.add('border-success', 'shadow-sm');
            normalCard.style.backgroundColor = '#e8fadf';
        }
    }
</script>
@endsection

@section('styles')
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
@endsection
