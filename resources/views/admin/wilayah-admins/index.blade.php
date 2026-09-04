@extends('admin.layouts.admin')

@section('title', 'Manajemen Admin RT & RW')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 38px;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        color: #697a8d;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
</style>
<style>
    . { animation: fadeUp 0.5s ease-out forwards; }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .table-modern th {
        font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; color: #a1acb8 !important; border-bottom: 2px solid #f0f2f4;
    }
    .table-modern td { vertical-align: middle; padding: 1rem 1.25rem; border-bottom: 1px solid #f0f2f4; transition: all 0.2s; }
    .table-modern tbody tr:hover { background-color: #f8f9fa; transform: scale(1.001); }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y ">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div class="flex-grow-1">
                <h4 class="fw-bold m-0"><span class="text-muted fw-light">Manajemen /</span> Admin RT & RW</h4>
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-md-end flex-shrink-0" style="position: relative; z-index: 10;">
                <button type="button" class="btn btn-outline-primary shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalTambahAdmin">
                    <i class="bx bx-plus me-1"></i> Buat Akun Dinas
                </button>
                <button type="button" class="btn btn-primary shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalPromosiAdmin">
                    <i class="bx bx-user-check me-1"></i> Jadikan Warga RT/RW
                </button>
            </div>
        </div>
    </div>

    <!-- Panduan -->
    <div class="card bg-label-primary border-0 shadow-none mb-4" style="border-radius: 12px;">
        <div class="card-body d-flex align-items-center p-4">
            <div class="me-3">
                <div class="bg-primary p-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                    <i class="bx bx-building-house fs-3"></i>
                </div>
            </div>
            <div>
                <h5 class="fw-bold mb-1 text-primary">Manajemen Pejabat Kewilayahan</h5>
                <p class="mb-0 text-primary" style="opacity: 0.85;">
                    Kelola akun khusus untuk para ketua/pengurus RT dan RW. Sangat direkomendasikan untuk <b>Menjadikan Warga sebagai RT atau RW</b> (memilih langsung dari daftar warga yang sudah ada di sistem). Opsi <b>Akun Dinas</b> hanya digunakan jika pejabat terkait belum memiliki akun sama sekali (gaptek).
                </p>
            </div>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center" role="alert">
            <i class="bx bx-check-circle fs-4 me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('duplicate_nik') || session('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center" role="alert">
            <i class="bx bx-error-circle fs-4 me-2"></i>
            {{ session('duplicate_nik') ?? session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Pengajuan Warga -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                <div class="card-header bg-warning py-3">
                    <h5 class="mb-0 text-white fw-bold d-flex align-items-center">
                        <i class="bx bx-time-five fs-4 me-2"></i> Menunggu Persetujuan (Pengajuan Kemitraan)
                    </h5>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-modern table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="py-3 ps-4">NAMA WARGA</th>
                                <th class="py-3">PENGAJUAN ROLE</th>
                                <th class="py-3">TARGET WILAYAH</th>
                                <th class="py-3 text-end pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($applications as $app)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-md me-3 flex-shrink-0">
                                                @if ($app->user && $app->user->file)
                                                    <img src="{{ $app->user->file->file_stream }}" alt="{{ $app->applicant_name }}" class="rounded-circle shadow-sm" style="width: 44px; height: 44px; object-fit: cover;">
                                                @elseif ($app->user && $app->user->avatar)
                                                    <img src="{{ asset('storage/' . $app->user->avatar) }}" alt="{{ $app->applicant_name }}" class="rounded-circle shadow-sm" style="width: 44px; height: 44px; object-fit: cover;">
                                                @else
                                                    <span class="avatar-initial rounded-circle bg-label-warning shadow-sm fw-bold" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                                        {{ strtoupper(substr($app->applicant_name, 0, 2)) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block fs-6">{{ $app->applicant_name }}</span>
                                                <small class="text-muted"><i class="bx bx-envelope me-1"></i>{{ $app->contact_email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-label-primary px-3 py-2 rounded-pill fw-semibold">Admin {{ strtoupper($app->region_type) }}</span></td>
                                    <td>
                                        <span class="fw-bold text-dark d-block">{{ $app->region_name }}</span>
                                        @if($app->region_type === 'rt' && $app->parentRegion)
                                            <small class="text-primary fw-semibold"><i class="bx bx-subdirectory-right me-1"></i>Di bawah {{ $app->parentRegion->name }}</small>
                                        @elseif($app->region_type === 'rw')
                                            <small class="text-muted"><i class="bx bx-buildings me-1"></i>Tingkat RW</small>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('admin.wilayah-admins.approve', $app->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Setujui pengajuan ini?')">Setujui</button>
                                        </form>
                                        <form action="{{ route('admin.wilayah-admins.reject', $app->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tolak pengajuan ini?')">Tolak</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Tidak ada pengajuan baru.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Daftar Admin RT/RW Aktif -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div>
                        <h5 class="mb-0 fw-bold text-primary d-flex align-items-center">
                            <i class="bx bx-list-check fs-4 me-2"></i> Daftar Admin RT & RW Aktif
                        </h5>
                        <small class="text-muted">Data terkelompok berdasarkan Rukun Warga (RW) dan Rukun Tetangga (RT) binaan.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2 w-100 w-md-auto flex-wrap">
                        <div class="input-group input-group-merge input-group-sm shadow-none" style="min-width: 210px;">
                            <span class="input-group-text bg-light border-0"><i class="bx bx-search text-muted"></i></span>
                            <input type="text" id="searchAdminTable" class="form-control form-control-sm bg-light border-0" placeholder="Cari nama, email, RT/RW...">
                        </div>
                        <select id="filterRwTable" class="form-select form-select-sm bg-light border-0" style="min-width: 140px;">
                            <option value="">Semua RW</option>
                            @foreach($rws as $rw)
                                <option value="{{ $rw->name }}">{{ $rw->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-modern table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="py-3 ps-4">NAMA PEJABAT</th>
                                <th class="py-3">STATUS KYC / NIK</th>
                                <th class="py-3">ROLE</th>
                                <th class="py-3">WILAYAH KERJA</th>
                                <th class="py-3 text-end pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0" id="adminTableBody">
                            @php
                                $currentRwGroup = null;
                            @endphp
                            @forelse($admins as $admin)
                                @php
                                    $isRw = $admin->role === 'admin_rw';
                                    $rwParentName = $isRw 
                                        ? ($admin->region->name ?? 'RW') 
                                        : ($admin->region && $admin->region->parent ? $admin->region->parent->name : 'RW Lainnya');
                                @endphp

                                {{-- Baris Pemisah Pengelompokan RW --}}
                                @if($currentRwGroup !== $rwParentName)
                                    @php $currentRwGroup = $rwParentName; @endphp
                                    <tr class="rw-group-header bg-light bg-opacity-75" data-group-rw="{{ $rwParentName }}">
                                        <td colspan="5" class="py-2 ps-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center text-primary fw-bold" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                                                    <i class="bx bx-buildings fs-5 me-2"></i>
                                                    <span>WILAYAH BINAAN: {{ strtoupper($rwParentName) }}</span>
                                                </div>
                                                <span class="badge bg-label-primary rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                                    Lingkup {{ $rwParentName }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endif

                                <tr class="admin-data-row" data-rw-name="{{ $rwParentName }}" data-search="{{ strtolower($admin->name . ' ' . $admin->email . ' ' . ($admin->region->name ?? '') . ' ' . $rwParentName) }}">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-md me-3 flex-shrink-0">
                                                @if ($admin->file)
                                                    <img src="{{ $admin->file->file_stream }}" alt="{{ $admin->name }}" class="rounded-circle shadow-sm" style="width: 44px; height: 44px; object-fit: cover;">
                                                @elseif ($admin->avatar)
                                                    <img src="{{ asset('storage/' . $admin->avatar) }}" alt="{{ $admin->name }}" class="rounded-circle shadow-sm" style="width: 44px; height: 44px; object-fit: cover;">
                                                @else
                                                    <span class="avatar-initial rounded-circle {{ $isRw ? 'bg-label-primary' : 'bg-label-success' }} shadow-sm fw-bold" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                                        {{ strtoupper(substr($admin->name, 0, 2)) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block fs-6">{{ $admin->name }}</span>
                                                <small class="text-muted"><i class="bx bx-envelope me-1"></i>{{ $admin->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($admin->nik)
                                            <span class="badge bg-label-success px-3 py-2 fw-semibold"><i class="bx bx-check-shield me-1"></i> NIK Terdata</span>
                                        @else
                                            <span class="badge bg-label-warning px-3 py-2 fw-semibold"><i class="bx bx-info-circle me-1"></i> Akun Dinas (Tanpa NIK)</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isRw)
                                            <span class="badge bg-label-primary px-3 py-2 rounded-pill fw-semibold"><i class="bx bx-shield-quarter me-1"></i>ADMIN RW</span>
                                        @else
                                            <span class="badge bg-label-success px-3 py-2 rounded-pill fw-semibold"><i class="bx bx-shield me-1"></i>ADMIN RT</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isRw)
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark fs-6">{{ $admin->region->name ?? '-' }}</span>
                                                <small class="text-muted mt-1"><i class="bx bx-home-alt me-1"></i>Wilayah RW Induk</small>
                                            </div>
                                        @else
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark fs-6">{{ $admin->region->name ?? '-' }}</span>
                                                <div class="mt-1">
                                                    <span class="badge bg-label-info rounded-pill px-2 py-1" style="font-size: 0.78rem;">
                                                        <i class="bx bx-subdirectory-right me-1"></i>Bagian dari {{ $rwParentName }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('admin.wilayah-admins.revoke', $admin->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('PERINGATAN: Cabut wewenang admin wilayah ini? Data akun tidak dihapus, hanya role dikembalikan menjadi warga biasa.')" title="Cabut Wewenang">
                                                <i class="bx bx-user-x me-1"></i> Cabut Akses
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada Admin RT/RW yang aktif.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
<!-- Modal Tambah Akun Dinas (Bypass) -->
<div class="modal fade" id="modalTambahAdmin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <form action="{{ route('admin.wilayah-admins.store') }}" method="POST" class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold text-primary" id="modalTambahAdminTitle">
                    <i class="bx bx-user-plus me-2 fs-4"></i>Buat Akun Dinas Baru (RT/RW)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @csrf
            <div class="modal-body pt-4">
                <div class="alert alert-info d-flex align-items-start mb-4 bg-label-info border-0" role="alert">
                    <i class="bx bx-info-circle fs-4 me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading mb-1 fw-bold">Jalur Khusus (VIP)</h6>
                        <span style="font-size: 0.85rem; line-height: 1.4; display: block;">Gunakan form ini untuk membuat <strong>Akun Dinas</strong> khusus bagi pejabat RT/RW (misalnya yang gaptek atau belum punya akun). Sistem akan meng-generate password sementara.</span>
                    </div>
                </div>
                
                <div class="row g-3">
                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-dark">Nama Lengkap Pejabat</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-user"></i></span>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso" required>
                        </div>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-dark">Email Resmi / Pribadi</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="rt01@SiladesBeng.com" required>
                        </div>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-dark">Nomor WhatsApp</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bxl-whatsapp"></i></span>
                            <input type="text" name="phone" class="form-control" placeholder="081234567890" required>
                        </div>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-dark">Tingkat Jabatan (Role)</label>
                        <select name="role" id="roleTambah" class="form-select form-select-lg" required onchange="updateWilayahTambah()">
                            <option value="">-- Pilih Role --</option>
                            <option value="admin_rw">Pengurus RW</option>
                            <option value="admin_rt">Pengurus RT</option>
                        </select>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-dark">Wilayah Kerja</label>
                        <select name="region_id" id="regionTambah" class="form-select form-select-lg" required>
                            <option value="">-- Pilih Wilayah --</option>
                        </select>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-dark">NIK (Nomor Induk Kependudukan)</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                            <input type="text" name="nik" id="nikTambah" class="form-control" required placeholder="16 Digit NIK">
                        </div>
                        <small class="text-muted mt-1 d-block" id="nikHelper"><i class="bx bx-shield-alt-2 text-success"></i> Jika NIK sudah terdaftar, sistem akan memberikan peringatan keamanan pintar.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top pt-3">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary shadow-sm"><i class="bx bx-save me-1"></i> Buat Akun Dinas</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Promosi Akun Warga -->
<div class="modal fade" id="modalPromosiAdmin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <form action="{{ route('admin.wilayah-admins.promote') }}" method="POST" class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold text-primary" id="modalPromosiAdminTitle">
                    <i class="bx bx-user-plus me-2 fs-4"></i>Beri Akses Admin RT/RW ke Warga
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @csrf
            <div class="modal-body pt-4">
                <div class="alert alert-primary d-flex align-items-start mb-4 bg-label-primary border-0" role="alert">
                    <i class="bx bx-info-circle fs-4 me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading mb-1 fw-bold">Panduan Penunjukan Admin</h6>
                        <span style="font-size: 0.85rem; line-height: 1.4; display: block;">Gunakan formulir ini untuk mencari akun warga yang telah terdaftar di <strong>SiladesBeng</strong>, lalu ubah statusnya menjadi pengurus RT atau RW agar mereka dapat mengelola data kependudukan wilayahnya.</span>
                    </div>
                </div>
                
                <div class="row g-3">
                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-dark"><i class="bx bx-search-alt text-primary me-1"></i> 1. Cari & Pilih Akun Warga</label>
                        <select name="user_email" id="selectWarga" class="form-select" required onchange="showWargaDetails()">
                            <option value="">Pilih Warga...</option>
                            @foreach($wargaList as $warga)
                                @php
                                    $photo = $warga->file ? $warga->file->file_stream : ($warga->avatar ? asset('storage/'.$warga->avatar) : asset('Admin/img/avatars/1.png'));
                                @endphp
                                <option value="{{ $warga->email }}" data-name="{{ $warga->name }}" data-phone="{{ $warga->phone }}" data-nik="{{ $warga->nik ? 'Terverifikasi (KTP)' : 'Belum Verifikasi' }}" data-photo="{{ $photo }}">
                                    {{ $warga->name }} ({{ $warga->email }} | {{ $warga->phone ?? 'Tanpa No. HP' }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted mt-1 d-block"><i class="bx bx-help-circle"></i> Ketik nama, email, atau nomor WhatsApp warga yang bersangkutan.</small>
                    </div>

                    <!-- Profil Singkat Warga (Muncul setelah dipilih) -->
                    <div class="col-12 mb-2 d-none" id="wargaDetailPreview">
                        <div class="card bg-label-secondary border-0 shadow-none rounded-3">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="avatar avatar-xl me-3">
                                    <img src="" id="previewPhoto" class="rounded-circle border border-2 border-white shadow-sm" style="object-fit:cover; width: 64px; height: 64px;">
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold text-dark" id="previewName">Nama Warga</h6>
                                    <div class="d-flex flex-column gap-1">
                                        <span class="text-muted" style="font-size:0.85rem;"><i class="bx bx-envelope text-primary"></i> <span id="previewEmail">email</span></span>
                                        <span class="text-muted" style="font-size:0.85rem;"><i class="bx bxl-whatsapp text-success"></i> <span id="previewPhone">phone</span></span>
                                    </div>
                                    <span class="badge bg-label-info mt-2" id="previewNik">NIK</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-dark"><i class="bx bx-briefcase text-primary me-1"></i> 2. Tetapkan Jabatan Baru</label>
                        <select name="role" id="rolePromosi" class="form-select form-select-lg" required onchange="updateWilayahPromosi()">
                            <option value="">-- Pilih Tingkat Jabatan --</option>
                            <option value="admin_rw">Pengurus RW (Ketua/Wakil)</option>
                            <option value="admin_rt">Pengurus RT (Ketua/Wakil)</option>
                        </select>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-dark"><i class="bx bx-map text-primary me-1"></i> 3. Pilih Wilayah Kerja</label>
                        <select name="region_id" id="regionPromosi" class="form-select form-select-lg" required>
                            <option value="">-- Pilih Wilayah --</option>
                        </select>
                        <small class="text-muted mt-1 d-block"><i class="bx bx-info-circle"></i> Daftar wilayah akan muncul secara otomatis setelah Anda memilih Tingkat Jabatan di atas.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top pt-3">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary shadow-sm"><i class="bx bx-check-circle me-1"></i> Tetapkan Jadi Admin</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const rws = @json($rws);
    const rts = @json($rts);

    function populateRegionSelect(selectId, roleId) {
        const selRegion = document.getElementById(selectId);
        const role = document.getElementById(roleId).value;
        
        selRegion.innerHTML = '<option value="">Pilih Wilayah...</option>';
        
        if (role === 'admin_rw') {
            rws.forEach(rw => {
                selRegion.innerHTML += `<option value="${rw.id}">${rw.name}</option>`;
            });
        } else if (role === 'admin_rt') {
            rts.forEach(rt => {
                const parentName = rt.parent ? rt.parent.name : '';
                selRegion.innerHTML += `<option value="${rt.id}">${parentName} - ${rt.name}</option>`;
            });
        }
    }

    function updateWilayahTambah() { populateRegionSelect('regionTambah', 'roleTambah'); }
    function updateWilayahPromosi() { populateRegionSelect('regionPromosi', 'rolePromosi'); }

    function showWargaDetails() {
        const select = document.getElementById('selectWarga');
        const preview = document.getElementById('wargaDetailPreview');
        if(!select.value) {
            preview.classList.add('d-none');
            return;
        }
        
        const selectedOption = select.options[select.selectedIndex];
        document.getElementById('previewName').innerText = selectedOption.getAttribute('data-name');
        document.getElementById('previewEmail').innerText = select.value;
        document.getElementById('previewPhone').innerText = selectedOption.getAttribute('data-phone') || '-';
        document.getElementById('previewNik').innerText = selectedOption.getAttribute('data-nik');
        document.getElementById('previewPhoto').src = selectedOption.getAttribute('data-photo');
        
        preview.classList.remove('d-none');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Init select2 if available
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
            $('#selectWarga').select2({
                dropdownParent: $('#modalPromosiAdmin'),
                placeholder: "Ketik Nama, Email, atau No. WhatsApp...",
                allowClear: true,
                width: '100%'
            }).on('change', function() {
                showWargaDetails();
            });
        }
    });

    // Jika ada session duplicate NIK, buka kembali modal tambah dan ubah NIK menjadi opsional
    @if(session('duplicate_nik'))
    document.addEventListener('DOMContentLoaded', function() {
        var modalTambah = new bootstrap.Modal(document.getElementById('modalTambahAdmin'));
        modalTambah.show();
        
        // Ubah NIK menjadi opsional
        const nikInput = document.getElementById('nikTambah');
        nikInput.required = false;
        nikInput.value = ''; // Kosongkan agar bisa dilanjut submit sebagai Akun Dinas
        
        const nikHelper = document.getElementById('nikHelper');
        nikHelper.innerHTML = "<span class='text-danger fw-bold'>* Peringatan NIK Ganda terpicu. Kolom NIK ini sekarang berstatus Opsional. Kosongkan untuk melanjutkan membuat Akun Dinas terpisah.</span>";
    });
    @endif

    // Filter dan Search Tabel Admin RT/RW
    const searchInput = document.getElementById('searchAdminTable');
    const filterRwSelect = document.getElementById('filterRwTable');
    
    function filterAdminTable() {
        if (!searchInput || !filterRwSelect) return;
        const query = searchInput.value.toLowerCase().trim();
        const selectedRw = filterRwSelect.value.trim();
        
        const rows = document.querySelectorAll('.admin-data-row');
        const headers = document.querySelectorAll('.rw-group-header');
        
        const visibleGroups = new Set();

        rows.forEach(row => {
            const searchData = (row.getAttribute('data-search') || '').toLowerCase();
            const rowRw = row.getAttribute('data-rw-name') || '';
            
            const matchesQuery = !query || searchData.includes(query);
            const matchesRw = !selectedRw || rowRw === selectedRw;
            
            if (matchesQuery && matchesRw) {
                row.style.display = '';
                visibleGroups.add(rowRw);
            } else {
                row.style.display = 'none';
            }
        });
        
        headers.forEach(header => {
            const groupRw = header.getAttribute('data-group-rw');
            if (visibleGroups.has(groupRw)) {
                header.style.display = '';
            } else {
                header.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterAdminTable);
    if (filterRwSelect) filterRwSelect.addEventListener('change', filterAdminTable);
</script>
@endsection


