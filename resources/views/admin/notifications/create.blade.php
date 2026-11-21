@extends('admin.layouts.admin')

@section('title', 'Kirim Notifikasi')

@include('admin.unit.partials.unit_product_styles')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div class="min-w-0">
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">
                    <a href="{{ route('admin.notifications.index') }}" class="text-muted text-decoration-none">Notifikasi</a> /
                </span>
                Kirim Pesan Baru
            </h4>
            <p class="text-muted small mb-0">Kirim pesan notifikasi langsung ke akun warga tertentu.</p>
        </div>
        <a href="{{ route('admin.notifications.index') }}"
           class="segi-back btn btn-outline-secondary rounded-pill px-4 w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1">
            <i class="bx bx-arrow-back"></i>
            <span>Kembali</span>
        </a>
    </div>

    <form action="{{ route('admin.notifications.store') }}" method="POST" enctype="multipart/form-data" class="up-form">
        @csrf
        <div class="row g-4">
            {{-- Kolom Kiri --}}
            <div class="col-12 col-lg-8">
                <div class="card up-card">
                    <div class="up-card-header d-flex align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="up-icon-box bg-label-primary">
                                <i class="bx bx-bell-plus fs-5 text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Isi Pesan Notifikasi</h6>
                                <small class="text-muted">Pilih penerima, tuliskan judul singkat dan isi pesan yang jelas</small>
                            </div>
                        </div>
                        <span class="badge bg-label-secondary rounded-pill px-2.5 py-1 small">Wajib (*)</span>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="mb-4">
                            <label for="user_id" class="form-label">Penerima Pesan <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('user_id') is-invalid @enderror" id="user_id" name="user_id" style="width: 100%;">
                                <option value="">Pilih warga penerima pesan</option>
                                <optgroup label="Pengguna Spesifik">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(old('user_id') == $user->id) data-icon="bx-user" data-subtext="{{ $user->email }}">
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <small class="text-muted d-block mt-1.5">
                                <i class="bx bx-info-circle me-1"></i>Untuk informasi ke seluruh warga sekaligus, gunakan menu <strong>Pengumuman</strong>.
                            </small>
                            @error('user_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="title" class="form-label">Judul Notifikasi <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   placeholder="Contoh: Jadwal Pengambilan Dokumen Layanan"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="message" class="form-label">Isi Pesan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('message') is-invalid @enderror"
                                      id="message"
                                      name="message"
                                      rows="6"
                                      placeholder="Tuliskan pesan lengkap yang ingin disampaikan..."
                                      required>{{ old('message') }}</textarea>
                            <div class="d-flex justify-content-between mt-1.5">
                                <small class="text-muted">Minimal 10 karakter.</small>
                                <small class="text-muted fw-medium" id="charCount">{{ strlen((string) old('message', '')) }} karakter</small>
                            </div>
                            @error('message')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan --}}
            <div class="col-12 col-lg-4">
                <div class="up-sticky">
                    <div class="card up-card mb-4">
                        <div class="up-card-header d-flex align-items-center gap-2.5">
                            <div class="up-icon-box bg-label-info">
                                <i class="bx bx-image-add fs-5 text-info"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Gambar Pendukung</h6>
                                <small class="text-muted">Opsional · JPG, PNG maks. 2MB</small>
                            </div>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <div class="upload-box m-0" id="uploadZone" onclick="document.getElementById('image').click()">
                                <input type="file" class="d-none" id="image" name="image" accept="image/*" onchange="previewImage(event)">

                                <div id="placeholder" class="upload-placeholder">
                                    <i class="bx bx-cloud-upload d-block"></i>
                                    <p class="mb-0 mt-1 fw-semibold text-dark small">Pilih Gambar</p>
                                    <small class="text-muted" style="font-size: 0.72rem;">Klik untuk mengunggah lampiran</small>
                                </div>

                                <div id="previewContainer" class="preview-container d-none">
                                    <img id="preview" src="" class="preview-image" alt="Preview">
                                    <button type="button" class="btn-remove-image" onclick="removeImage(event)" title="Hapus Gambar">
                                        <i class="bx bx-x fs-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card up-card mb-4">
                        <div class="card-body p-3 p-md-4">
                            <button type="submit" class="btn btn-primary rounded-pill w-100 py-2.5 fw-semibold shadow-sm mb-2">
                                <i class="bx bx-paper-plane me-1"></i>Kirim Notifikasi
                            </button>
                            <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary rounded-pill w-100">
                                Batal
                            </a>
                        </div>
                    </div>

                    <div class="card border-0 bg-label-primary rounded-4">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bx bx-bulb fs-5 text-primary mt-0.5 flex-shrink-0"></i>
                                <div class="small text-primary" style="line-height: 1.55;">
                                    <strong class="d-block mb-1">Tips Pesan Efektif</strong>
                                    Gunakan judul singkat (5–7 kata) dan sertakan instruksi tindak lanjut yang jelas bagi warga penerima.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid #d9e1ec !important;
        border-radius: 10px !important;
        padding: 0.42rem 0.85rem !important;
        background-color: #ffffff !important;
    }
    .select2-container--open .select2-selection--single {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 3.5px rgba(13, 110, 253, 0.11) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 26px !important;
        color: #1e293b !important;
        padding-left: 0 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #eff6ff !important;
        color: #0d6efd !important;
    }
    .select2-dropdown {
        border: 1px solid #d9e1ec !important;
        border-radius: 12px !important;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.1) !important;
        padding: 0.35rem 0;
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('#user_id').select2({ width: '100%' });
    });

    const messageInput = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    if (messageInput && charCount) {
        messageInput.addEventListener('input', function () {
            charCount.textContent = this.value.length + ' karakter';
        });
    }

    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('preview').src = e.target.result;
                document.getElementById('previewContainer').classList.remove('d-none');
                document.getElementById('placeholder').classList.add('d-none');
            };
            reader.readAsDataURL(file);
        }
    }

    function removeImage(event) {
        event.stopPropagation();
        document.getElementById('image').value = '';
        document.getElementById('previewContainer').classList.add('d-none');
        document.getElementById('placeholder').classList.remove('d-none');
    }
</script>
@endsection
