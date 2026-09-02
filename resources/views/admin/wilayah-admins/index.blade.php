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
                                    <td class="ps-4">{{ $app->applicant_name }}<br><small>{{ $app->contact_email }}</small></td>
                                    <td><span class="badge bg-label-primary">Admin {{ strtoupper($app->region_type) }}</span></td>
                                    <td>{{ $app->region_name }}</td>
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
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="mb-0 fw-bold text-primary d-flex align-items-center">
                        <i class="bx bx-list-check fs-4 me-2"></i> Daftar Admin RT & RW Aktif
                    </h5>
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
                        <tbody class="table-border-bottom-0">
                            @forelse($admins as $admin)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-dark d-block">{{ $admin->name }}</span>
                                        <small class="text-muted"><i class="bx bx-envelope"></i> {{ $admin->email }}</small>
                                    </td>
                                    <td>
                                        @if($admin->nik)
                                            <span class="badge bg-label-success px-3 py-2 fw-semibold">NIK Terdata</span>
                                        @else
                                            <span class="badge bg-label-warning px-3 py-2 fw-semibold">Akun Dinas (Tanpa NIK)</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-label-primary px-3 py-2 rounded-pill fw-semibold">{{ strtoupper($admin->role) }}</span></td>
                                    <td><span class="fw-semibold text-dark">{{ $admin->region->name }}</span></td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('admin.wilayah-admins.revoke', $admin->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('PERINGATAN: Cabut wewenang admin wilayah ini? Data akun tidak dihapus, hanya role dikembalikan menjadi warga biasa.')">
                                                <i class="bx bx-trash"></i>
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
                                <option value="{{ $warga->email }}" data-name="{{ $warga->name }}" data-phone="{{ $warga->phone }}" data-nik="{{ $warga->nik ? 'Terverifikasi (KTP)' : 'Belum Verifikasi' }}" data-photo="{{ $warga->avatar ? asset('storage/'.$warga->avatar) : asset('Admin/img/avatars/1.png') }}">
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
</script>
@endsection


