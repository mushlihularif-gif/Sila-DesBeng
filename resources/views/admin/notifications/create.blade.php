@extends('admin.layouts.admin')

@section('title', 'Kirim Notifikasi')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">Notifikasi /</span> Buat Baru
            </h4>
            <p class="text-muted small mb-0">Kirim pesan notifikasi ke pengguna aplikasi</p>
        </div>
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    <form action="{{ route('admin.notifications.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <!-- Left Column: Main Form -->
            <div class="col-lg-8">
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header d-flex align-items-center justify-content-between border-bottom">
                        <h5 class="mb-0 fw-semibold"><i class='bx bx-edit me-2 text-primary'></i>Isi Pesan Notification</h5>
                        <small class="text-muted">Wajib diisi (*)</small>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Recipient -->
                        <div class="mb-4">
                            <label for="user_id" class="form-label fw-semibold">Penerima Pesan <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('user_id') is-invalid @enderror" id="user_id" name="user_id" style="width: 100%;">
                                <option value="" selected class="fw-bold" data-icon="bx-broadcast">Kirim ke Semua Pengguna (Broadcast)</option>
                                <optgroup label="Pengguna Spesifik">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }} data-icon="bx-user" data-subtext="{{ $user->email }}">
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <div class="form-text text-muted small mt-1">
                                <i class='bx bx-info-circle me-1'></i>Pilih "Semua Pengguna" untuk mengirim pengumuman umum.
                            </div>
                            @error('user_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-semibold">Judul Notifikasi <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   placeholder="Contoh: Promo Spesial Hari Ini!"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div class="mb-0">
                            <label for="message" class="form-label fw-semibold">Isi Pesan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" 
                                      name="message" 
                                      rows="6" 
                                      placeholder="Tuliskan pesan lengkap yang ingin disampaikan..."
                                      style="resize: none;"
                                      required>{{ old('message') }}</textarea>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted">Minimal 10 karakter.</small>
                                <small class="text-muted" id="charCount">0 karakter</small>
                            </div>
                            @error('message')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            <!-- Right Column: Media & Actions -->
            <div class="col-lg-4">
                
                <!-- Media Upload Card -->
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header border-bottom">
                        <h5 class="mb-0 fw-semibold fs-6">Gambar Pendukung (Opsional)</h5>
                    </div>
                    <div class="card-body text-center p-4">
                        <div class="upload-zone p-4 mb-3" id="uploadZone" onclick="document.getElementById('image').click()">
                            <input type="file" class="d-none" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                            
                            <div id="placeholder" class="py-3">
                                <div class="mb-3">
                                    <div class="avatar avatar-xl mx-auto bg-label-primary rounded-circle mb-2 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                        <i class="bx bx-image-add fs-2"></i>
                                    </div>
                                </div>
                                <h6 class="mb-1 text-dark">Upload Gambar</h6>
                                <small class="text-muted d-block">Klik untuk memilih file</small>
                                <small class="text-xs text-muted">(JPG, PNG max 2MB)</small>
                            </div>

                            <div id="previewContainer" class="d-none position-relative">
                                <img id="preview" src="" class="img-fluid rounded shadow-sm border" alt="Preview">
                                <button type="button" class="btn btn-icon btn-sm btn-danger rounded-pill position-absolute top-0 end-0 mt-n2 me-n2 shadow" onclick="removeImage(event)" title="Hapus Gambar">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="card mb-4 bg-label-secondary border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <i class='bx bx-bulb text-warning fs-3 me-3'></i>
                            <div>
                                <h6 class="fw-bold mb-1">Tips Efektif</h6>
                                <p class="small mb-0 text-muted">
                                    Gunakan judul yang singkat dan menarik (max 5-7 kata). Sertakan gambar untuk meningkatkan keterlibatan pengguna.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                        <i class="bx bx-paper-plane me-2"></i> Kirim Notifikasi
                    </button>
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-label-secondary">
                        Batal
                    </a>
                </div>

            </div>
        </div>
    </form>
</div>

@endsection

@section('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Custom clean styling */
    .form-control:focus, .form-select:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1);
    }
    
    .upload-zone {
        border: 2px dashed #d9dee3;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        background-color: #f8f9fa;
    }
    
    .upload-zone:hover {
        border-color: #696cff;
        background-color: #f3f4ff;
    }

    .bg-label-primary {
        background-color: #e7e7ff !important;
        color: #696cff !important;
    }
    
    .bg-label-secondary {
        background-color: #ebeef0 !important;
        color: #8592a3 !important;
    }

    .btn-label-secondary {
        color: #8592a3;
        background-color: #ebeef0;
        border-color: transparent;
    }
    .btn-label-secondary:hover {
        color: #8592a3;
        background-color: #dde1e5;
    }

    /* Override Select2 Styling to match Sneat Template */
    .select2-container .select2-selection--single {
        height: 40px !important;
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        padding: 0.375rem 0.75rem !important;
        background-color: #fff !important;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .select2-container--open .select2-selection--single {
        border-color: #696cff !important;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 24px !important;
        color: #697a8d !important;
        padding-left: 0 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
        right: 8px !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: rgba(105, 108, 255, 0.08) !important;
        color: #696cff !important;
    }
    .select2-dropdown {
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45) !important;
        padding: 0.5rem 0;
    }
    .select2-search__field {
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        padding: 0.4rem 0.8rem !important;
    }
    .select2-search__field:focus {
        border-color: #696cff !important;
        outline: none !important;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1) !important;
    }
    .select2-results__group {
        font-weight: 700 !important;
        color: #a1acb8 !important;
        background-color: transparent !important;
        padding: 0.5rem 1rem !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
    }
    .select2-results__option {
        padding: 0.5rem 1rem !important;
    }
    /* Hide the search icon if needed */
    .input-group > .select2-container {
        flex: 1 1 auto;
        width: 1% !important;
    }
</style>
@endsection

@section('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        function formatUser (option) {
            if (!option.id && !option.element) {
                return option.text;
            }
            
            var el = option.element;
            if (!el) return option.text;

            var icon = el.getAttribute('data-icon');
            var subtext = el.getAttribute('data-subtext');
            var color = icon === 'bx-broadcast' ? 'text-primary' : 'text-secondary';
            
            if (icon) {
                return '<div class="d-flex align-items-center">' +
                       '<i class="bx ' + icon + ' ' + color + ' me-2 fs-5"></i>' +
                       '<div>' +
                       '<div class="fw-medium text-dark">' + option.text + '</div>' +
                       (subtext ? '<small class="text-muted">' + subtext + '</small>' : '') +
                       '</div></div>';
            }
            return option.text;
        }

        function formatUserSelection (option) {
            if (!option.id && !option.element) {
                return option.text;
            }
            
            var el = option.element;
            if (!el) return option.text;

            var icon = el.getAttribute('data-icon');
            var color = icon === 'bx-broadcast' ? 'text-primary' : 'text-secondary';
            
            if (icon) {
                return '<i class="bx ' + icon + ' ' + color + ' me-2"></i><span class="fw-medium">' + option.text + '</span>';
            }
            return option.text;
        }

        $('#user_id').select2({
            templateResult: formatUser,
            templateSelection: formatUserSelection,
            escapeMarkup: function(m) { return m; },
            width: '100%'
        });
    });

    // Character Counter
    const messageInput = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    
    if(messageInput) {
        messageInput.addEventListener('input', function() {
            charCount.textContent = this.value.length + " karakter";
        });
    }

    // Image Preview
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
                document.getElementById('previewContainer').classList.remove('d-none');
                document.getElementById('placeholder').classList.add('d-none');
            }
            reader.readAsDataURL(file);
        }
    }

    function removeImage(event) {
        event.stopPropagation(); // Mencegah trigger klik pada parent div
        document.getElementById('image').value = "";
        document.getElementById('previewContainer').classList.add('d-none');
        document.getElementById('placeholder').classList.remove('d-none');
    }
</script>
@endsection