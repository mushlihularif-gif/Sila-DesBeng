@extends('admin.layouts.admin')

@section('title', 'Tambah Pemerintah Daerah')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <span class="text-muted fw-light">Profil SiladesBeng / Pemerintah Daerah /</span> Tambah Anggota
                    </h4>
                    <p class="text-muted mb-0">Tambahkan anggota baru untuk ditampilkan di halaman Profil SiladesBeng</p>
                </div>
                <a href="{{ route('admin.siladesbeng.bumdes.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <!-- Main Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.siladesbeng.bumdes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <!-- Left Column - Photo Preview -->
                            <div class="col-md-3">
                                <div class="text-center">
                                    <!-- Photo Preview Circle -->
                                    <div class="position-relative d-inline-block mb-3">
                                        <div id="preview-container" class="rounded-circle overflow-hidden border border-3 border-light shadow-lg d-flex justify-content-center align-items-center" 
                                             style="width: 180px; height: 180px; background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); cursor: pointer;"
                                             onclick="document.getElementById('photo-input').click()">
                                            <i id="preview-icon" class="bx bxs-user" style="font-size: 80px; color: #90caf9;"></i>
                                            <img id="preview-image" 
                                                 src="#" 
                                                 alt="" 
                                                 class="w-100 h-100 d-none"
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
                                            onclick="clearPhoto()"
                                            style="display: none;">
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
                                               class="form-control modern-input @error('name') is-invalid @enderror" 
                                               placeholder="Contoh: Muhammad Mawardi" 
                                               value="{{ old('name') }}"
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
                                               class="form-control modern-input @error('position') is-invalid @enderror" 
                                               placeholder="Contoh: Sekretaris Desa" 
                                               value="{{ old('position') }}"
                                               required>
                                        @error('position')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Info Box -->
                                    <div class="col-12">
                                        <div class="alert alert-info border-0 modern-alert d-flex align-items-start" role="alert">
                                            <i class="bx bx-info-circle fs-4 me-2 mt-1"></i>
                                            <div class="small">
                                                <strong>Informasi:</strong><br>
                                                Data anggota yang ditambahkan akan ditampilkan di halaman <strong>Profil SiladesBeng</strong> 
                                                pada bagian <strong>Struktur Pengembang SiladesBeng</strong>.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="col-12">
                                        <div class="d-flex gap-2 justify-content-end pt-3 border-top">
                                            <a href="{{ route('admin.siladesbeng.bumdes.index') }}" 
                                               class="btn btn-light modern-btn-secondary px-4">
                                                <i class="bx bx-x me-1"></i> Batal
                                            </a>
                                            <button type="submit" class="btn btn-primary modern-btn-primary px-4">
                                                <i class="bx bx-check me-1"></i> Simpan Anggota
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
        box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;
        border: none !important;
    }

    /* Modern Input Enhancements */
    .modern-input {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.75rem 1.25rem;
        font-size: 0.95rem;
        color: #334155;
        transition: all 0.3s ease;
    }

    .modern-input:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        transform: translateY(-1px);
    }

    /* Modern Button Enhancements */
    .modern-btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        transition: all 0.3s ease;
        border-radius: 0.75rem;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
    }

    .modern-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3);
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }

    .modern-btn-secondary {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #475569;
        transition: all 0.3s ease;
        border-radius: 0.75rem;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
    }

    .modern-btn-secondary:hover {
        background: #e2e8f0;
        color: #1e293b;
        transform: translateY(-2px);
    }

    /* Photo Preview Enhancements */
    #preview-container {
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15) !important;
    }

    #preview-container:hover {
        transform: scale(1.03);
        box-shadow: 0 12px 30px rgba(59, 130, 246, 0.25) !important;
    }

    #preview-image {
        transition: all 0.3s ease;
    }

    /* Alert Enhancements */
    .modern-alert {
        border-radius: 0.75rem;
        background-color: #eff6ff;
        border: 1px solid #bfdbfe !important;
        color: #1e3a8a;
    }

    .modern-alert i {
        color: #3b82f6;
    }

    /* Label Enhancements */
    .form-label {
        color: #475569;
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Animation for photo upload */
    @keyframes photoUpload {
        0% { opacity: 0; transform: scale(0.8); }
        100% { opacity: 1; transform: scale(1); }
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
    const clearBtn = document.getElementById('clear-photo-btn');

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
            preview.classList.remove('d-none');
            preview.classList.add('photo-uploaded');
            document.getElementById('preview-icon').style.display = 'none';
            container.style.background = 'transparent';
            clearBtn.style.display = 'inline-block';
            
            // Remove animation class after animation completes
            setTimeout(() => {
                preview.classList.remove('photo-uploaded');
            }, 300);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function clearPhoto() {
    const input = document.getElementById('photo-input');
    const preview = document.getElementById('preview-image');
    const container = document.getElementById('preview-container');
    const clearBtn = document.getElementById('clear-photo-btn');

    input.value = '';
    preview.src = '#';
    preview.classList.add('d-none');
    document.getElementById('preview-icon').style.display = 'block';
    container.style.background = 'linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%)';
    clearBtn.style.display = 'none';
}

// Show clear button if there's already a photo on page load
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('photo-input');
    const clearBtn = document.getElementById('clear-photo-btn');
    
    if (input.value) {
        clearBtn.style.display = 'inline-block';
    }
});
</script>
@endsection