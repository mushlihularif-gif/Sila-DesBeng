@extends('admin.layouts.admin')

@section('title', 'Edit Anggota BUMDes')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <span class="text-muted fw-light">Profil iSewa / Anggota BUMDes /</span> Edit Anggota
                    </h4>
                    <p class="text-muted mb-0">Perbarui informasi anggota yang ditampilkan di halaman Profil iSewa</p>
                </div>
                <a href="{{ route('admin.isewa.bumdes.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <!-- Main Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.isewa.bumdes.update', $member->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Left Column - Photo Preview -->
                            <div class="col-md-3">
                                <div class="text-center">
                                    <!-- Photo Preview Circle -->
                                    <div class="position-relative d-inline-block mb-3">
                                        <div id="preview-container" class="rounded-circle overflow-hidden border border-3 border-light shadow-lg" 
                                             style="width: 180px; height: 180px; background: transparent; cursor: pointer;"
                                             onclick="document.getElementById('photo-input').click()">
                                            <img id="preview-image" 
                                                 src="{{ $member->photo_url }}" 
                                                 alt="Preview" 
                                                 class="w-100 h-100"
                                                 style="object-fit: cover; object-position: center;">
                                        </div>
                                        <!-- Camera Icon Overlay -->
                                        <div class="position-absolute bottom-0 end-0 mb-2 me-2">
                                            <label for="photo-input" class="btn btn-primary btn-sm rounded-circle p-2" style="width: 36px; height: 36px; cursor: pointer;">
                                                <i class="bx bx-camera fs-5"></i>
                                            </label>
                                        </div>
                                    </div>

                                    <label for="photo-input" class="form-label fw-bold d-block mb-3" style="cursor: pointer;">FOTO PROFIL</label>

                                    <!-- File Input (Hidden) -->
                                    <input type="file" 
                                           name="photo" 
                                           id="photo-input" 
                                           class="d-none" 
                                           accept="image/*" 
                                           onchange="previewImage(event)">
                                    <input type="hidden" name="delete_photo" id="delete_photo" value="0">

                                    <!-- Info Text -->
                                    <div class="text-muted small mb-3">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Ukuran maks: 8MB<br>
                                        Format: JPG, PNG, GIF
                                    </div>

                                    <!-- Clear Button -->
                                    <button type="button" 
                                            id="clear-photo-btn" 
                                            class="btn btn-outline-danger btn-sm" 
                                            onclick="clearPhoto()">
                                        <i class="bx bx-trash me-1"></i> Hapus Foto
                                    </button>
                                </div>
                            </div>

                            <!-- Right Column - Form Fields -->
                            <div class="col-md-9">
                                <div class="row g-3">
                                    <!-- Nama Lengkap -->
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">
                                            Nama Lengkap <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               name="name" 
                                               class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                               placeholder="Contoh: Muhammad Mawardi" 
                                               value="{{ old('name', $member->name) }}"
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Jabatan -->
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">
                                            Jabatan <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               name="position" 
                                               class="form-control form-control-lg @error('position') is-invalid @enderror" 
                                               placeholder="Contoh: Sekretaris Desa" 
                                               value="{{ old('position', $member->position) }}"
                                               required>
                                        @error('position')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Info Box -->
                                    <div class="col-12">
                                        <div class="alert alert-info border-0 d-flex align-items-start" role="alert">
                                            <i class="bx bx-info-circle fs-4 me-2"></i>
                                            <div class="small">
                                                <strong>Informasi:</strong><br>
                                                Data anggota yang ditambahkan akan ditampilkan di halaman <strong>Profil iSewa</strong> 
                                                pada bagian <strong>Struktur Pengembang iSewa</strong>.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="col-12">
                                        <div class="d-flex gap-2 justify-content-end pt-3 border-top">
                                            <a href="{{ route('admin.isewa.bumdes.index') }}" 
                                               class="btn btn-outline-secondary px-4">
                                                <i class="bx bx-x me-1"></i> Batal
                                            </a>
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="bx bx-check me-1"></i> Simpan Perubahan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Card Enhancements */
    .card {
        transition: all 0.3s ease;
        border-radius: 1rem;
    }

    /* Form Control Enhancements */
    .form-control:focus {
        border-color: #0099ff;
        box-shadow: 0 0 0 0.2rem rgba(0, 153, 255, 0.25);
    }

    .form-control-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border-radius: 0.5rem;
    }

    /* Button Enhancements */
    .btn {
        transition: all 0.3s ease;
        border-radius: 0.5rem;
        font-weight: 500;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        background: linear-gradient(135deg, #0099ff 0%, #0077cc 100%);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #0088ee 0%, #0066bb 100%);
    }

    /* Photo Preview Enhancements */
    #preview-container {
        transition: all 0.3s ease;
    }

    #preview-container:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(0, 153, 255, 0.3) !important;
    }

    #preview-image {
        transition: all 0.3s ease;
    }

    /* Alert Enhancements */
    .alert {
        border-radius: 0.75rem;
    }

    .alert-info {
        background-color: #e3f2fd;
        color: #0277bd;
    }

    /* Label Enhancements */
    .form-label {
        color: #495057;
        margin-bottom: 0.5rem;
    }

    /* Animation for photo upload */
    @keyframes photoUpload {
        0% {
            opacity: 0;
            transform: scale(0.8);
        }
        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    .photo-uploaded {
        animation: photoUpload 0.3s ease;
    }
</style>

<script>
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('preview-image');
    const container = document.getElementById('preview-container');
    const deleteInput = document.getElementById('delete_photo');

    if (input.files && input.files[0]) {
        // Validate file size (2MB)
        if (input.files[0].size > 8 * 1024 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 8MB.');
            input.value = '';
            return;
        }

        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(input.files[0].type)) {
            alert('Format file tidak didukung! Gunakan JPG, PNG, atau GIF.');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.add('photo-uploaded');
            container.style.background = 'transparent';
            
            // Remove animation class after animation completes
            setTimeout(() => {
                preview.classList.remove('photo-uploaded');
            }, 300);

            // Reset delete flag
            if (deleteInput) {
                deleteInput.value = '0';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function clearPhoto() {
    const input = document.getElementById('photo-input');
    const preview = document.getElementById('preview-image');
    const container = document.getElementById('preview-container');
    const deleteInput = document.getElementById('delete_photo');

    input.value = '';
    preview.src = '{{ $member->photo_url }}'; // Kembali ke foto asli atau default jika null
    // Catatan: Jika ingin preview hilang completely saat clear, ganti src jadi '#' dan sembunyikan container.
    // Tapi di sini behaviornya sepertinya reset ke awal. 
    // TAPI user ingin MENGHAPUS. Jadi harusnya kita buat jadi blank/placeholder.
    
    // Perbaikan logika: Jika tombol "Hapus Foto" ditekan, berarti user tidak ingin ada foto.
    preview.src = 'https://via.placeholder.com/200x200?text=No+Photo'; // Placeholder
    container.style.background = '#f8f9fa';
    
    if (deleteInput) {
        deleteInput.value = '1';
    }
}
</script>
@endsection