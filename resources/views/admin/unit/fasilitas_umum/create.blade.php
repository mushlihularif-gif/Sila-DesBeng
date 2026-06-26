@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <div class="mb-4">
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Unit Layanan / Peminjaman Fasilitas Umum /</span> Tambah Fasilitas
            </h4>
            <p class="text-muted mb-0">Lengkapi formulir di bawah untuk menambahkan fasilitas baru</p>
        </div>

        <!-- Form Card -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card modern-card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper me-3">
                                <i class='bx bx-package text-primary' style="font-size: 24px;"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Form Tambah Fasilitas Umum</h5>
                                <small class="text-muted">Masukkan detail fasilitas yang dapat dipinjam</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        {{-- Tampilan error validasi --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show modern-alert" role="alert">
                                <div class="d-flex align-items-start">
                                    <i class='bx bx-error-circle me-2' style="font-size: 20px;"></i>
                                    <div class="flex-grow-1">
                                        <strong>Perhatian!</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.unit.fasilitas_umum.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Section: Foto Fasilitas -->
                            <div class="form-section mb-4">
                                <h6 class="section-title mb-3">
                                    <i class='bx bx-image me-2'></i>Foto Fasilitas
                                </h6>
                                <div class="row g-3">
                                    <!-- Foto Utama -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold" for="foto">Foto Utama</label>
                                        <div class="upload-box" onclick="document.getElementById('foto').click()">
                                            <div id="preview_foto" class="preview-container" style="display:none;">
                                                <img src="#" alt="Preview" class="preview-image" />
                                                <button type="button" class="btn-remove-image" onclick="event.stopPropagation(); clearFile('foto', 'preview_foto')">
                                                    <i class='bx bx-x'></i>
                                                </button>
                                            </div>
                                            <div id="placeholder_foto" class="upload-placeholder">
                                                <i class='bx bx-cloud-upload' style="font-size: 48px;"></i>
                                                <p class="mb-0 mt-2">Klik untuk upload</p>
                                                <small class="text-muted">JPG, PNG (Max 8MB)</small>
                                            </div>
                                        </div>
                                        <input type="file" class="d-none" id="foto" name="foto_utama" 
                                               accept="image/*" onchange="previewFile(this, 'preview_foto', 'placeholder_foto')" />
                                    </div>

                                    <!-- Foto Tambahan 1 -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold" for="foto_2">Foto Tambahan 1</label>
                                        <div class="upload-box" onclick="document.getElementById('foto_2').click()">
                                            <div id="preview_foto_2" class="preview-container" style="display:none;">
                                                <img src="#" alt="Preview" class="preview-image" />
                                                <button type="button" class="btn-remove-image" onclick="event.stopPropagation(); clearFile('foto_2', 'preview_foto_2')">
                                                    <i class='bx bx-x'></i>
                                                </button>
                                            </div>
                                            <div id="placeholder_foto_2" class="upload-placeholder">
                                                <i class='bx bx-cloud-upload' style="font-size: 48px;"></i>
                                                <p class="mb-0 mt-2">Klik untuk upload</p>
                                                <small class="text-muted">JPG, PNG (Max 8MB)</small>
                                            </div>
                                        </div>
                                        <input type="file" class="d-none" id="foto_2" name="foto_2" 
                                               accept="image/*" onchange="previewFile(this, 'preview_foto_2', 'placeholder_foto_2')" />
                                    </div>

                                    <!-- Foto Tambahan 2 -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold" for="foto_3">Foto Tambahan 2</label>
                                        <div class="upload-box" onclick="document.getElementById('foto_3').click()">
                                            <div id="preview_foto_3" class="preview-container" style="display:none;">
                                                <img src="#" alt="Preview" class="preview-image" />
                                                <button type="button" class="btn-remove-image" onclick="event.stopPropagation(); clearFile('foto_3', 'preview_foto_3')">
                                                    <i class='bx bx-x'></i>
                                                </button>
                                            </div>
                                            <div id="placeholder_foto_3" class="upload-placeholder">
                                                <i class='bx bx-cloud-upload' style="font-size: 48px;"></i>
                                                <p class="mb-0 mt-2">Klik untuk upload</p>
                                                <small class="text-muted">JPG, PNG (Max 8MB)</small>
                                            </div>
                                        </div>
                                        <input type="file" class="d-none" id="foto_3" name="foto_3" 
                                               accept="image/*" onchange="previewFile(this, 'preview_foto_3', 'placeholder_foto_3')" />
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section: Informasi Dasar -->
                            <div class="form-section mb-4">
                                <h6 class="section-title mb-3">
                                    <i class='bx bx-info-circle me-2'></i>Informasi Dasar
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="nama_fasilitas">
                                            Nama Barang <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control modern-input" id="nama_fasilitas" 
                                               name="nama_fasilitas" placeholder="Contoh: Tenda Pesta 5x5m" required />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="kategori">
                                            Kategori <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <select class="form-select modern-input" id="kategori" name="kategori" required>
                                                <option value="" disabled selected>Pilih Kategori</option>
                                                <option value="Kendaraan">Kendaraan (Mobil, Ambulan, dll)</option>
                                                <option value="Gedung">Gedung / Ruangan</option>
                                                <option value="Lapangan">Lapangan Olahraga</option>
                                                <option value="Perlengkapan Acara">Perlengkapan Acara</option>
                                                <option value="Tenda Acara">Tenda Acara</option>
                                                <option value="Dekorasi">Dekorasi</option>
                                            </select>
                                            <button type="button" class="btn btn-outline-primary modern-btn-outline" 
                                                    data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                                <i class="bx bx-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold" for="deskripsi">
                                            Deskripsi <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control modern-input" id="deskripsi" name="deskripsi" 
                                                  rows="4" placeholder="Jelaskan detail fasilitas, lokasi, dan daya tampung..." required></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Kapasitas & Satuan -->
                            <div class="form-section mb-4">
                                <h6 class="section-title mb-3">
                                    <i class='bx bx-list-ol me-2'></i>Kapasitas & Satuan
                                </h6>
                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="stok">
                                            Stok Tersedia <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control modern-input" id="stok" 
                                               name="stok" placeholder="10" min="0" required />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="satuan">
                                            Satuan <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <select class="form-select modern-input" id="satuan" name="satuan" required>
                                                <option value="" disabled selected>Pilih Satuan</option>
                                                <option value="Unit">Unit</option>
                                                <option value="Paket">Paket</option>
                                                <option value="Set">Satu Set</option>
                                            </select>
                                            <button type="button" class="btn btn-outline-primary modern-btn-outline" 
                                                    data-bs-toggle="modal" data-bs-target="#addSatuanModal">
                                                <i class="bx bx-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Status & Lokasi -->
                            <div class="form-section mb-4">
                                <h6 class="section-title mb-3">
                                    <i class='bx bx-map me-2'></i>Status & Lokasi
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="status">
                                            Status <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select modern-input" id="status" name="status" required>
                                            <option value="tersedia" selected>Tersedia</option>
                                            <option value="disewa">Disewa</option>
                                            <option value="rusak">Rusak</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="lokasi">
                                            Lokasi <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control modern-input" id="lokasi" 
                                               name="lokasi" value="Desa Pematang Duku Timur" required />
                                    </div>
                                </div>
                            </div>


                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                                <a href="{{ route('admin.unit.fasilitas_umum.index') }}" class="btn btn-light modern-btn-secondary px-4">
                                    <i class='bx bx-x me-1'></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary modern-btn-primary px-4">
                                    <i class='bx bx-save me-1'></i> Simpan Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Kategori -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modern-modal">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="addCategoryModalLabel">
                        <i class='bx bx-category me-2'></i>Tambah Kategori Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="new_kategori" class="form-label fw-semibold">Nama Kategori</label>
                        <input type="text" class="form-control modern-input" id="new_kategori" 
                               placeholder="Contoh: Perlengkapan Pesta">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light modern-btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary modern-btn-primary" id="saveCategoryBtn">
                        <i class='bx bx-check me-1'></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Satuan -->
    <div class="modal fade" id="addSatuanModal" tabindex="-1" aria-labelledby="addSatuanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modern-modal">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="addSatuanModalLabel">
                        <i class='bx bx-ruler me-2'></i>Tambah Satuan Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="new_satuan" class="form-label fw-semibold">Nama Satuan</label>
                        <input type="text" class="form-control modern-input" id="new_satuan" 
                               placeholder="Contoh: Paket">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light modern-btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary modern-btn-primary" id="saveSatuanBtn">
                        <i class='bx bx-check me-1'></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        :root {
            --primary-color: #3b82f6; /* Smooth Blue */
            --primary-dark: #2563eb;
            --primary-light: #eff6ff;
            --primary-soft: #e0f2fe;
            --border-color: #e2e8f0;
            --bg-soft: #f8fafc;
        }

        /* Card Styling */
        .modern-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
            background: #ffffff;
        }

        .modern-card:hover {
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
        }

        /* Icon Wrapper */
        .icon-wrapper {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background: #e3f2fd;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Form Sections */
        .form-section {
            padding: 24px;
            background: var(--bg-soft);
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            border-top: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-section:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            border-left-width: 6px;
        }

        .section-title {
            color: #2c3e50;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
        }

        /* Modern Inputs */
        .modern-input {
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #ffffff;
            color: #334155;
        }

        .modern-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            background: #ffffff;
            outline: none;
        }

        .modern-input-addon {
            background: #f8f9fa;
            border: 1.5px solid #e0e6ed;
            border-right: none;
            border-radius: 8px 0 0 8px;
            color: #6c757d;
            font-weight: 500;
        }

        /* Modern Buttons */
        .modern-btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 12px 28px;
            font-weight: 600;
            color: #ffffff;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .modern-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35);
            background: var(--primary-dark);
            color: #ffffff;
        }

        .modern-btn-secondary {
            background: #f8f9fa;
            border: 1.5px solid #e0e6ed;
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 500;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .modern-btn-secondary:hover {
            background: #e9ecef;
            border-color: #ced4da;
            color: #495057;
        }

        .modern-btn-outline {
            border: 1.5px solid #0d6efd;
            border-radius: 0 8px 8px 0;
            color: #0d6efd;
            transition: all 0.3s ease;
            background: white;
        }

        .modern-btn-outline:hover {
            background: #0d6efd;
            color: white;
        }

        /* Upload Box */
        .upload-box {
            border: 2px dashed var(--border-color);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #ffffff;
            aspect-ratio: 4/3;
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .upload-box:hover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }

        .upload-placeholder {
            color: #6c757d;
        }

        .upload-placeholder i {
            color: #94a3b8;
            transition: all 0.3s ease;
        }

        .upload-box:hover .upload-placeholder i {
            color: var(--primary-color);
            transform: translateY(-5px) scale(1.05);
        }

        .preview-container {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .preview-image {
            width: 100%;
            height: 100%;
            aspect-ratio: 4/3;
            object-fit: cover;
            border-radius: 8px;
        }

        .btn-remove-image {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #dc3545;
            color: white;
            border: 2px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
        }

        .btn-remove-image:hover {
            background: #bb2d3b;
            transform: scale(1.1);
        }

        /* Modern Alert */
        .modern-alert {
            border-radius: 10px;
            border: none;
            border-left: 4px solid #dc3545;
        }

        /* Modern Modal */
        .modern-modal {
            border-radius: 12px;
            border: none;
        }

        .modern-modal .modal-header {
            background: #f8f9fa;
        }

        /* Smooth Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modern-card {
            animation: fadeInUp 0.5s ease;
        }
    </style>

    <script>


    // Fungsi untuk preview file gambar
    function previewFile(input, previewId, placeholderId) {
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);
        const img = preview.querySelector('img');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Fungsi untuk menghapus file dan reset input
    function clearFile(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById('placeholder_' + inputId);
        const img = preview.querySelector('img');

        input.value = '';
        img.src = '#';
        preview.style.display = 'none';
        placeholder.style.display = 'block';
    }

    // Fungsi untuk menambah kategori
    document.getElementById('saveCategoryBtn').addEventListener('click', function() {
        const newKategori = document.getElementById('new_kategori').value.trim();
        if (newKategori) {
            const select = document.getElementById('kategori');
            const option = document.createElement('option');
            option.value = newKategori;
            option.textContent = newKategori;
            select.appendChild(option);
            select.value = newKategori;
            bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')).hide();
            document.getElementById('new_kategori').value = '';
        } else {
            alert('Silakan masukkan nama kategori.');
        }
    });

    // Fungsi untuk menambah satuan
    document.getElementById('saveSatuanBtn').addEventListener('click', function() {
        const newSatuan = document.getElementById('new_satuan').value.trim();
        if (newSatuan) {
            const select = document.getElementById('satuan');
            const option = document.createElement('option');
            option.value = newSatuan;
            option.textContent = newSatuan;
            select.appendChild(option);
            select.value = newSatuan;
            bootstrap.Modal.getInstance(document.getElementById('addSatuanModal')).hide();
            document.getElementById('new_satuan').value = '';
        } else {
            alert('Silakan masukkan nama satuan.');
        }
    });

    // Script untuk memunculkan Opsi Supir & BBM hanya jika kategori = Kendaraan
    function toggleKendaraanOptions() {
        const kategori = document.getElementById('kategori').value;
        const vehicleOptions = document.querySelectorAll('.kendaraan-options');
        
        vehicleOptions.forEach(function(el) {
            if(kategori && kategori.toLowerCase().includes('kendaraan')) {
                el.style.display = 'block';
            } else {
                el.style.display = 'none';
                // Reset value saat disembunyikan
                const select = el.querySelector('select');
                if(select) select.value = '';
            }
        });
    }

    document.getElementById('kategori').addEventListener('change', toggleKendaraanOptions);
    // Jalankan saat pertama dimuat
    document.addEventListener('DOMContentLoaded', toggleKendaraanOptions);

    </script>
@endsection

