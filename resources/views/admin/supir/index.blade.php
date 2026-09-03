@extends('admin.layouts.admin')

@section('title', 'Manajemen Supir & Petugas')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><span class="text-muted fw-light">Pengaturan /</span> Data Supir & Petugas</h4>
            <p class="text-muted mb-0">Kelola data supir yang tersedia untuk ditugaskan pada pesanan rental maupun ambulans darurat.</p>
        </div>
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addSupirModal">
            <i class="bx bx-plus-circle me-1"></i> Tambah Supir Baru
        </button>
    </div>

    <!-- Alert Penjelasan Sistem -->
    <div class="alert alert-info d-flex align-items-center border-0 shadow-sm rounded-3 mb-4" role="alert">
        <span class="badge bg-white text-info rounded-circle p-2 me-3 shadow-sm flex-shrink-0">
            <i class="bx bx-bulb fs-4"></i>
        </span>
        <div>
            <h6 class="alert-heading fw-bold mb-1">Penting: Sistem Penugasan Baru</h6>
            <p class="mb-0" style="font-size: 0.85rem;">
                Supir tidak lagi diikat secara permanen pada satu mobil. Cukup tentukan <strong>Kategori Layanan</strong> supir (Rental Mobil atau Ambulans/Fasilitas). Anda akan memilih supir secara spesifik <strong>pada saat mengonfirmasi pesanan dari warga</strong>.
            </p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Profil Supir</th>
                        <th>Kontak & Akun</th>
                        <th>Kategori Layanan</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($supirs as $supir)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md me-3">
                                    @if($supir->foto)
                                        <img src="{{ asset('storage/' . $supir->foto) }}" alt="Avatar" class="rounded-circle object-fit-cover shadow-sm border" style="width: 40px; height: 40px;">
                                    @else
                                        <span class="avatar-initial rounded-circle bg-label-primary shadow-sm border fw-bold">{{ substr($supir->nama, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $supir->nama }}</h6>
                                    <small class="text-muted">ID: #{{ str_pad($supir->id, 4, '0', STR_PAD_LEFT) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center mb-1">
                                <i class="bx bxl-whatsapp text-success me-1"></i> 
                                <span class="fw-semibold">{{ $supir->kontak ?? '-' }}</span>
                            </div>
                            @if($supir->user_id)
                                <span class="badge bg-label-info d-inline-flex align-items-center" style="font-size: 0.7rem;"><i class="bx bx-link me-1"></i> Tertaut: {{ $supir->user->name }}</span>
                            @else
                                <span class="badge bg-label-secondary d-inline-flex align-items-center" style="font-size: 0.7rem;" title="Tautkan ke akun warga agar supir bisa menerima notifikasi in-app"><i class="bx bx-unlink me-1"></i> Belum tertaut akun</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                @if($supir->is_sewa_mobil)
                                    <span class="badge bg-label-primary w-100 text-start"><i class="bx bx-car me-1"></i> Rental Mobil</span>
                                @endif
                                @if($supir->is_fasilitas_umum)
                                    <span class="badge bg-label-danger w-100 text-start"><i class="bx bx-plus-medical me-1"></i> Ambulans & Fasilitas</span>
                                @endif
                                
                                @if(!$supir->is_sewa_mobil && !$supir->is_fasilitas_umum)
                                    <span class="text-muted fst-italic" style="font-size: 0.8rem;">Belum ada kategori</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($supir->status == 'Tersedia')
                                <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i> Tersedia</span>
                            @elseif($supir->status == 'Sedang Bertugas')
                                <span class="badge bg-label-warning"><i class="bx bx-run me-1"></i> Bertugas</span>
                            @else
                                <span class="badge bg-label-secondary"><i class="bx bx-minus-circle me-1"></i> Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-icon btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#detailSupirModal{{ $supir->id }}" title="Lihat Detail Profil">
                                <i class="bx bx-show"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editSupirModal{{ $supir->id }}" title="Edit Data Supir">
                                <i class="bx bx-edit-alt"></i>
                            </button>
                            <form action="{{ route('supir.destroy', $supir->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data supir ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus Supir">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="empty-state">
                                <i class="bx bx-user-x fs-1 text-muted mb-3 d-block"></i>
                                <h6>Belum ada data supir/petugas.</h6>
                                <p class="text-muted mb-0">Klik tombol "Tambah Supir Baru" di sudut kanan atas untuk mulai.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('modals')
@foreach($supirs as $supir)
    <!-- Edit Modal -->
    <div class="modal fade" id="editSupirModal{{ $supir->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold d-flex align-items-center">
                        <i class="bx bx-edit-alt text-primary me-2 fs-4"></i> Edit Data Supir & Petugas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('supir.update', $supir->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="alert alert-primary d-flex align-items-center mb-4 rounded-3 border-0" role="alert">
                            <i class="bx bx-info-circle fs-4 me-3"></i>
                            <div class="small">Ubah informasi profil, kontak, serta ketersediaan layanan untuk supir <strong>{{ $supir->nama }}</strong>.</div>
                        </div>

                        <!-- Photo Upload UI Premium -->
                        <div class="d-flex align-items-center mb-4 p-4 bg-label-secondary rounded-4 border-0">
                            <div class="me-4 position-relative">
                                @if($supir->foto)
                                    <img id="preview_edit_{{ $supir->id }}" src="{{ asset('storage/' . $supir->foto) }}" class="rounded-circle object-fit-cover shadow-sm border border-2 border-white" style="width: 85px; height: 85px;">
                                @else
                                    <div id="preview_edit_{{ $supir->id }}" class="rounded-circle bg-white d-flex align-items-center justify-content-center shadow-sm border border-2 border-white text-primary" style="width: 85px; height: 85px;">
                                        <i class="bx bx-user fs-1"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Foto Profil Petugas</h6>
                                <p class="text-muted mb-2" style="font-size: 0.8rem;">Gunakan rasio 1:1 (persegi). Maksimal ukuran file 2MB.</p>
                                <div class="d-flex align-items-center gap-2">
                                    <label for="foto_upload_e_{{ $supir->id }}" class="btn btn-sm btn-primary cursor-pointer shadow-sm">
                                        <i class="bx bx-upload me-1"></i> Ganti Foto
                                    </label>
                                    <input type="file" name="foto" id="foto_upload_e_{{ $supir->id }}" class="d-none" accept="image/*" onchange="previewImage(this, 'preview_edit_{{ $supir->id }}', 'filename_e_{{ $supir->id }}')">
                                    <span id="filename_e_{{ $supir->id }}" class="text-muted small">Tidak ada file baru</span>
                                </div>
                                @if($supir->foto)
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="delete_foto" value="1" id="delFoto{{ $supir->id }}">
                                    <label class="form-check-label text-danger small fw-semibold" for="delFoto{{ $supir->id }}"><i class="bx bx-trash"></i> Hapus foto saat ini</label>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="bx bx-user"></i></span>
                                    <input type="text" name="nama" class="form-control" value="{{ $supir->nama }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">No. WhatsApp</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="bx bxl-whatsapp"></i></span>
                                    <input type="text" name="kontak" class="form-control" value="{{ $supir->kontak }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4 bg-label-info p-3 rounded-4 border-0">
                            <label class="form-label text-uppercase fw-bold text-info mb-1" style="font-size: 0.75rem;"><i class="bx bx-link-alt me-1"></i>Tautkan Akun Aplikasi (Opsional)</label>
                            <p class="text-info mb-2" style="font-size: 0.75rem;">Notifikasi *in-app* akan dikirimkan ke akun ini saat bertugas.</p>
                            <select name="user_id" class="form-select border-info shadow-none select2-users">
                                <option value="">-- Tidak Ditautkan --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ $supir->user_id == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-7">
                                <div class="border rounded-4 p-3 h-100 border-gray-200">
                                    <label class="form-label text-uppercase text-muted fw-bold mb-3" style="font-size: 0.75rem;">Layanan Aktif <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-column gap-3">
                                        <div class="form-check form-switch d-flex align-items-center">
                                            <input class="form-check-input mt-0 me-3 cursor-pointer" type="checkbox" name="is_sewa_mobil" value="1" id="is_rental_e_{{ $supir->id }}" {{ $supir->is_sewa_mobil ? 'checked' : '' }} style="width: 2.5em; height: 1.25em;">
                                            <label class="form-check-label cursor-pointer fw-bold text-dark" for="is_rental_e_{{ $supir->id }}">Sewa Mobil (Rental)</label>
                                        </div>
                                        <div class="form-check form-switch d-flex align-items-center">
                                            <input class="form-check-input mt-0 me-3 cursor-pointer bg-danger border-danger" type="checkbox" name="is_fasilitas_umum" value="1" id="is_fasilitas_e_{{ $supir->id }}" {{ $supir->is_fasilitas_umum ? 'checked' : '' }} style="width: 2.5em; height: 1.25em;">
                                            <label class="form-check-label cursor-pointer fw-bold text-danger" for="is_fasilitas_e_{{ $supir->id }}">Ambulans & Fasilitas</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="border rounded-4 p-3 h-100 border-gray-200">
                                    <label class="form-label text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem;">Status Operasional <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select mb-2 shadow-none" required>
                                        <option value="Tersedia" {{ $supir->status == 'Tersedia' ? 'selected' : '' }}>Tersedia (Aktif)</option>
                                        <option value="Sedang Bertugas" {{ $supir->status == 'Sedang Bertugas' ? 'selected' : '' }}>Sedang Bertugas</option>
                                        <option value="Tidak Aktif" {{ $supir->status == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif (Cuti/Sakit)</option>
                                    </select>
                                    <small class="text-muted d-block" style="font-size:0.7rem;">Ubah status jika supir sedang berhalangan hadir.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top px-4 py-3 rounded-bottom-4">
                        <button type="button" class="btn btn-label-secondary fw-bold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold shadow-sm"><i class="bx bx-save me-2"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detail Supir Modal -->
    <div class="modal fade" id="detailSupirModal{{ $supir->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0 justify-content-end">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0 px-4 pb-4">
                    <div class="text-center mb-4">
                        <div class="avatar avatar-xl mx-auto mb-3" style="width: 110px; height: 110px;">
                            @if($supir->foto)
                                <img src="{{ asset('storage/' . $supir->foto) }}" class="rounded-circle object-fit-cover shadow border border-3 border-white w-100 h-100">
                            @else
                                <span class="avatar-initial rounded-circle bg-label-primary shadow border border-3 border-white fs-1 w-100 h-100 d-flex align-items-center justify-content-center fw-bold">{{ substr($supir->nama, 0, 1) }}</span>
                            @endif
                        </div>
                        <h4 class="mb-1 fw-bold text-dark">{{ $supir->nama }}</h4>
                        <p class="text-muted mb-2"><i class="bx bxl-whatsapp text-success me-1"></i>{{ $supir->kontak ?: 'Tidak ada kontak' }}</p>
                        
                        <div class="mt-2">
                            @if($supir->status == 'Tersedia')
                                <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i> Tersedia</span>
                            @elseif($supir->status == 'Sedang Bertugas')
                                <span class="badge bg-label-warning"><i class="bx bx-run me-1"></i> Sedang Bertugas</span>
                            @else
                                <span class="badge bg-label-secondary"><i class="bx bx-minus-circle me-1"></i> Tidak Aktif</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-label-secondary rounded-3 p-3 mb-3 text-center">
                        <small class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size:0.7rem;">Akun Tertaut</small>
                        @if($supir->user_id)
                            <div class="d-inline-flex align-items-center text-dark fw-semibold">
                                <i class="bx bx-user-circle me-2 fs-5 text-primary"></i> {{ $supir->user->name }}
                            </div>
                        @else
                            <div class="text-muted small italic">Belum ditautkan</div>
                        @endif
                    </div>

                    <div class="text-center">
                        <small class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size:0.7rem;">Kategori Layanan</small>
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            @if($supir->is_sewa_mobil)
                                <span class="badge bg-label-primary"><i class="bx bx-car me-1"></i> Sewa Mobil</span>
                            @endif
                            @if($supir->is_fasilitas_umum)
                                <span class="badge bg-label-danger"><i class="bx bx-plus-medical me-1"></i> Ambulans & Fasilitas</span>
                            @endif
                            @if(!$supir->is_sewa_mobil && !$supir->is_fasilitas_umum)
                                <span class="text-muted small italic">Belum diatur</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- Add Modal -->
<div class="modal fade" id="addSupirModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center">
                    <i class="bx bx-user-plus text-primary me-2 fs-4"></i> Tambah Supir / Petugas Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('supir.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(Auth::user()->role === 'super_admin')
                <input type="hidden" name="region_id" value="1">
                @endif
                <div class="modal-body p-4">
                    <!-- Panduan -->
                    <div class="alert alert-primary d-flex mb-4 rounded-3 border-0" role="alert">
                        <i class="bx bx-bulb fs-4 me-3 mt-1"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1">Panduan Pengisian</h6>
                            <p class="mb-0 small">Masukkan biodata supir/petugas yang akan ditugaskan di desa. Profil yang diunggah akan ditampilkan kepada warga khusus untuk layanan darurat (Ambulans).</p>
                        </div>
                    </div>

                    <!-- Photo Upload UI Premium -->
                    <div class="d-flex align-items-center mb-4 p-4 bg-label-secondary rounded-4 border-0">
                        <div class="me-4 position-relative">
                            <div id="preview_add" class="rounded-circle bg-white d-flex align-items-center justify-content-center shadow-sm border border-2 border-white text-primary" style="width: 85px; height: 85px;">
                                <i class="bx bx-camera fs-1"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1">Foto Profil Petugas (Opsional)</h6>
                            <p class="text-muted mb-2" style="font-size: 0.8rem;">Gunakan rasio 1:1 (persegi). Maksimal ukuran file 2MB.</p>
                            <div class="d-flex align-items-center gap-2">
                                <label for="foto_upload_add" class="btn btn-sm btn-primary cursor-pointer shadow-sm">
                                    <i class="bx bx-upload me-1"></i> Pilih Foto...
                                </label>
                                <input type="file" name="foto" id="foto_upload_add" class="d-none" accept="image/*" onchange="previewImage(this, 'preview_add', 'filename_add')">
                                <span id="filename_add" class="text-muted small">Belum ada file dipilih</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" name="nama" class="form-control" placeholder="Cth: Pak Budi" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">No. WhatsApp</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bxl-whatsapp"></i></span>
                                <input type="text" name="kontak" class="form-control" placeholder="Cth: 08123456789">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4 bg-label-info p-3 rounded-4 border-0">
                        <label class="form-label text-uppercase fw-bold text-info mb-1" style="font-size: 0.75rem;"><i class="bx bx-link-alt me-1"></i>Tautkan Akun Aplikasi (Opsional)</label>
                        <p class="text-info mb-2" style="font-size: 0.75rem;">Notifikasi *in-app* akan dikirimkan ke akun ini saat bertugas.</p>
                        <select name="user_id" class="form-select border-info shadow-none select2-users">
                            <option value="" selected>-- Tidak Ditautkan (Hanya Data Profil) --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-7">
                            <div class="border rounded-4 p-3 h-100 border-gray-200">
                                <label class="form-label text-uppercase text-muted fw-bold mb-3" style="font-size: 0.75rem;">Layanan Aktif <span class="text-danger">*</span></label>
                                <div class="d-flex flex-column gap-3">
                                    <div class="form-check form-switch d-flex align-items-center">
                                        <input class="form-check-input mt-0 me-3 cursor-pointer" type="checkbox" name="is_sewa_mobil" value="1" id="is_rental_add" style="width: 2.5em; height: 1.25em;" checked>
                                        <label class="form-check-label cursor-pointer fw-bold text-dark" for="is_rental_add">Sewa Mobil (Rental)</label>
                                    </div>
                                    <div class="form-check form-switch d-flex align-items-center">
                                        <input class="form-check-input mt-0 me-3 cursor-pointer bg-danger border-danger" type="checkbox" name="is_fasilitas_umum" value="1" id="is_fasilitas_add" style="width: 2.5em; height: 1.25em;">
                                        <label class="form-check-label cursor-pointer fw-bold text-danger" for="is_fasilitas_add">Ambulans & Fasilitas</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="border rounded-4 p-3 h-100 border-gray-200">
                                <label class="form-label text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem;">Status Awal <span class="text-danger">*</span></label>
                                <select name="status" class="form-select mb-2 shadow-none" required>
                                    <option value="Tersedia" selected>Tersedia (Aktif)</option>
                                    <option value="Tidak Aktif">Tidak Aktif (Cuti/Sakit)</option>
                                </select>
                                <small class="text-muted d-block" style="font-size:0.7rem;">Ubah status jika supir sedang berhalangan hadir.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top px-4 py-3 rounded-bottom-4">
                    <button type="button" class="btn btn-label-secondary fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold shadow-sm"><i class="bx bx-save me-2"></i>Simpan Supir Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(input, previewId, filenameId) {
    var preview = document.getElementById(previewId);
    var filename = document.getElementById(filenameId);
    
    if (input.files && input.files[0]) {
        filename.textContent = input.files[0].name;
        
        // Ensure preview is an img element so Cropper can assign .src to it
        if(preview.tagName.toLowerCase() !== 'img') {
            var img = document.createElement('img');
            img.id = previewId;
            img.className = "rounded-circle object-fit-cover shadow-sm border border-2 border-white bg-white";
            img.style.width = "85px";
            img.style.height = "85px";
            preview.parentNode.replaceChild(img, preview);
            preview = img;
        }
        
        // Integrasi Cropper.js (Rasio 1:1)
        if (typeof initGlobalCropper === 'function') {
            initGlobalCropper(input, previewId, 1, true);
        } else {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    } else {
        filename.textContent = "Tidak ada file dipilih";
    }
}
</script>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Override Select2 Styling to match Sneat Template */
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #00cfe8 !important; /* using border-info color */
        border-radius: 0.375rem !important;
        background-color: #fff !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 14px !important;
        color: #697a8d !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }
    .select2-container {
        width: 100% !important;
    }
</style>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-users').each(function() {
            var modalId = $(this).closest('.modal').attr('id');
            $(this).select2({
                dropdownParent: $('#' + modalId),
                width: '100%'
            });
        });
    });
</script>
@endpush