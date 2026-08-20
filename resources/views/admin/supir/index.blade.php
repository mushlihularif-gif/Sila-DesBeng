@extends('admin.layouts.admin')

@section('title', 'Data Supir & Petugas')

@section('content')
<style>
    .animate-fade-up {
        animation: fadeUp 0.5s ease-out forwards;
    }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .filter-btn {
        border-radius: 50rem;
        padding: 0.6rem 1.2rem;
        font-weight: 600;
        transition: all 0.2s;
        border: 1px solid transparent;
        text-decoration: none;
        background: transparent;
    }
    
    /* Warna khusus untuk setiap status saat active */
    .filter-btn-primary.active {
        background-color: #0d6efd !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25) !important;
    }
    .filter-btn-info.active {
        background-color: #0dcaf0 !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(13, 202, 240, 0.25) !important;
    }
    
    /* Penyesuaian badge saat active */
    .filter-btn.active .badge {
        background-color: white !important;
    }
    .filter-btn-primary.active .badge { color: #0d6efd !important; }
    .filter-btn-info.active .badge { color: #0dcaf0 !important; }

    .filter-btn:not(.active) {
        color: #697a8d !important;
    }
    .filter-btn:not(.active):hover {
        background-color: rgba(13, 110, 253, 0.08) !important;
    }

    .table-modern {
        border-collapse: separate;
        border-spacing: 0 10px;
    }
    .table-modern tbody tr {
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        border-radius: 8px;
        transition: all 0.2s;
        background: #fff;
    }
    .table-modern tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .table-modern td {
        border: none;
        padding: 1.2rem 1.5rem;
        vertical-align: middle;
    }
    .table-modern td:first-child { border-radius: 8px 0 0 8px; }
    .table-modern td:last-child { border-radius: 0 8px 8px 0; }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">Pengaturan /</span> Data Supir & Petugas
            </h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupirModal">
                <i class="bx bx-plus me-1"></i> Tambah Supir
            </button>
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

    <!-- Panduan -->
    <div class="card bg-label-primary border-0 shadow-none mb-4" style="border-radius: 12px;">
        <div class="card-body d-flex align-items-center p-4">
            <div class="me-3">
                <div class="bg-primary p-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                    <i class="bx bx-info-circle fs-3"></i>
                </div>
            </div>
            <div>
                <h5 class="fw-bold mb-2 text-primary">Panduan Mengelola Supir & Petugas</h5>
                <p class="mb-1 text-primary" style="opacity: 0.85;">
                    Data di bawah ini dikelompokkan berdasarkan unit layanannya. Anda dapat memilih supir yang sudah terdaftar di sini saat mengelola unit armada.
                </p>
                <ul class="mb-0 text-primary" style="opacity: 0.85; padding-left: 1.2rem;">
                    <li><strong>Penyewaan Mobil:</strong> Daftarkan supir untuk mobil rental komersial (seperti Avanza, Innova, Hiace, dll).</li>
                    <li><strong>Fasilitas Umum:</strong> Daftarkan petugas/supir untuk kendaraan layanan masyarakat (seperti Supir Ambulans Daerah, Supir Truk Sampah, Supir Mobil Jenazah, dll).</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- TABS BUTTONS -->
    <ul class="nav nav-pills d-flex flex-wrap gap-2 mb-4" role="tablist" style="border: none;">
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link filter-btn filter-btn-primary active" role="tab" data-bs-toggle="pill" data-bs-target="#navs-mobil">
                <img src="{{ asset('User/img/elemen/mobil.png') }}" alt="Mobil" style="height: 20px; width: auto; object-fit: contain;" class="me-2"> Penyewaan Mobil
                <span class="badge rounded-pill bg-label-primary ms-1">{{ $supirMobil->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link filter-btn filter-btn-info" role="tab" data-bs-toggle="pill" data-bs-target="#navs-fasilitas">
                <img src="{{ asset('User/img/elemen/fasilitas.png') }}" alt="Fasilitas" style="height: 20px; width: auto; object-fit: contain;" class="me-2"> Fasilitas Umum
                <span class="badge rounded-pill bg-label-info ms-1">{{ $supirFasilitas->count() }}</span>
            </button>
        </li>
    </ul>

    <!-- TABS CONTENT -->
    <div class="tab-content p-0 shadow-none bg-transparent">
        
        <!-- Tab: Penyewaan Mobil -->
        <div class="tab-pane fade show active" id="navs-mobil" role="tabpanel">
            <div class="table-responsive text-nowrap">
                <table class="table table-modern border-top-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="rounded-start">No</th>
                            <th>Nama Supir / Petugas</th>
                            <th>Kategori Layanan</th>
                            <th>No. WhatsApp / Kontak</th>
                            <th>Status</th>
                            <th class="rounded-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($supirMobil as $supir)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $supir->nama }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-label-primary"><i class='bx bx-car me-1'></i>{{ $supir->layanan }}</span>
                            </td>
                            <td>
                                @if($supir->kontak)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supir->kontak) }}" target="_blank" class="text-success">
                                    <i class="bx bxl-whatsapp me-1"></i> {{ $supir->kontak }}
                                </a>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($supir->status == 'Tersedia')
                                    <span class="badge bg-label-success">Tersedia</span>
                                @elif($supir->status == 'Sedang Bertugas')
                                    <span class="badge bg-label-warning">Sedang Bertugas</span>
                                @else
                                    <span class="badge bg-label-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editSupirModal{{ $supir->id }}">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                                <form action="{{ route('supir.destroy', $supir->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus supir ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 bg-white rounded">Belum ada data supir/petugas untuk Penyewaan Mobil.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Fasilitas Umum -->
        <div class="tab-pane fade" id="navs-fasilitas" role="tabpanel">
            <div class="table-responsive text-nowrap">
                <table class="table table-modern border-top-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="rounded-start">No</th>
                            <th>Nama Supir / Petugas</th>
                            <th>Kategori Layanan</th>
                            <th>No. WhatsApp / Kontak</th>
                            <th>Status</th>
                            <th class="rounded-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($supirFasilitas as $supir)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $supir->nama }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-label-info"><i class='bx bx-building-house me-1'></i>{{ $supir->layanan }}</span>
                            </td>
                            <td>
                                @if($supir->kontak)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supir->kontak) }}" target="_blank" class="text-success">
                                    <i class="bx bxl-whatsapp me-1"></i> {{ $supir->kontak }}
                                </a>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($supir->status == 'Tersedia')
                                    <span class="badge bg-label-success">Tersedia</span>
                                @elif($supir->status == 'Sedang Bertugas')
                                    <span class="badge bg-label-warning">Sedang Bertugas</span>
                                @else
                                    <span class="badge bg-label-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editSupirModal{{ $supir->id }}">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                                <form action="{{ route('supir.destroy', $supir->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus supir ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 bg-white rounded">Belum ada data supir/petugas untuk Fasilitas Umum.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

</div>
@endsection

@push('modals')
<!-- Render semua Edit Modal di luar container untuk mencegah bug z-index Bootstrap -->
@foreach($supirMobil as $supir)
    @include('admin.supir.partials.edit_modal', ['supir' => $supir])
@endforeach

@foreach($supirFasilitas as $supir)
    @include('admin.supir.partials.edit_modal', ['supir' => $supir])
@endforeach

<!-- Add Modal -->
<div class="modal fade" id="addSupirModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Supir Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('supir.store') }}" method="POST">
                @csrf
                @if(Auth::user()->role === 'super_admin')
                <input type="hidden" name="region_id" value="1">
                @endif
                <div class="modal-body">
                    <!-- Icon Preview Container -->
                    <div class="row mb-3 d-none" id="iconPreviewContainerAdd">
                        <div class="col-12 text-center">
                            <img id="iconPreviewAdd" src="" alt="Ikon Layanan" style="width: 80px; height: 80px; object-fit: contain;">
                            <div class="mt-2 text-muted fw-bold" id="iconTextAdd" style="font-size: 0.9rem;"></div>
                        </div>
                    </div>
                      
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Kategori Layanan <span class="text-danger">*</span></label>
                            <select name="layanan" id="kategoriLayananAdd" class="form-select" required>
                                <option value="" disabled selected>Pilih Layanan</option>
                                <option value="Penyewaan Mobil">Penyewaan Mobil</option>
                                <option value="Fasilitas Umum">Fasilitas Umum (Ambulance, dll)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Nama Supir / Petugas <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">No. WhatsApp / Kontak</label>
                            <input type="text" name="kontak" class="form-control" placeholder="Contoh: 08123456789">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Status Awal <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="Tersedia" selected>Tersedia</option>
                                <option value="Tidak Aktif">Tidak Aktif (Cuti/Sakit)</option>
                            </select>
                            <small class="text-muted d-block mt-1"><i class="bx bx-info-circle"></i> <b>Tersedia:</b> Siap ditugaskan. <b>Tidak Aktif:</b> Libur/Sakit. <br><i>(Status "Sedang Bertugas" akan diupdate otomatis oleh sistem saat supir ditugaskan pada suatu pesanan)</i></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const kategoriSelect = document.getElementById('kategoriLayananAdd');
        const iconContainer = document.getElementById('iconPreviewContainerAdd');
        const iconPreview = document.getElementById('iconPreviewAdd');
        const iconText = document.getElementById('iconTextAdd');
        
        // Asset URLs
        const urlMobil = "{{ asset('User/img/elemen/mobil.png') }}";
        const urlFasilitas = "{{ asset('User/img/elemen/fasilitas.png') }}";
        
        kategoriSelect.addEventListener('change', function () {
            const val = this.value;
            if (val === 'Penyewaan Mobil') {
                iconPreview.src = urlMobil;
                iconText.innerText = 'Penyewaan Mobil';
                iconContainer.classList.remove('d-none');
                iconContainer.classList.add('animate__animated', 'animate__fadeIn');
            } else if (val === 'Fasilitas Umum') {
                iconPreview.src = urlFasilitas;
                iconText.innerText = 'Fasilitas Umum';
                iconContainer.classList.remove('d-none');
                iconContainer.classList.add('animate__animated', 'animate__fadeIn');
            } else {
                iconContainer.classList.add('d-none');
            }
            
            // Remove animation class after it plays so it can play again next time
            setTimeout(() => {
                iconContainer.classList.remove('animate__animated', 'animate__fadeIn');
            }, 1000);
        });
    });
</script>
@endpush
