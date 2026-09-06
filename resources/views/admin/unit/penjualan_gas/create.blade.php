@extends('admin.layouts.admin')

@php
    $userRegion = \App\Models\Region::find(auth()->user()->region_id);
    $paymentInfo = $userRegion->payment_info ?? [];
@endphp

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <div class="mb-4">
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Unit Layanan / Penjualan Gas /</span> Tambah Gas
            </h4>
            <p class="text-muted mb-0">Lengkapi formulir di bawah untuk menambahkan produk gas baru</p>
        </div>

        <!-- Form Card -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card modern-card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper me-3">
                                <i class='bx bxs-gas-pump text-primary' style="font-size: 24px;"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Form Tambah Produk Gas</h5>
                                <small class="text-muted">Masukkan detail produk gas yang akan dijual</small>
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

                        <form action="{{ route('admin.unit.penjualan_gas.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            
                            <!-- STEPS NAVIGATION -->
                            <ul class="nav nav-pills nav-justified flex-nowrap mb-4 wizard-steps gap-1 gap-sm-2" id="formWizard" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="step1-tab" data-bs-toggle="pill" data-bs-target="#step1" type="button" role="tab" aria-controls="step1" aria-selected="true">
                                        <span class="step-icon"><i class='bx bx-info-circle'></i></span>
                                        <span class="step-text d-none d-sm-inline ms-1">Info & Media</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="step2-tab" data-bs-toggle="pill" data-bs-target="#step2" type="button" role="tab" aria-controls="step2" aria-selected="false">
                                        <span class="step-icon"><i class='bx bx-money'></i></span>
                                        <span class="step-text d-none d-sm-inline ms-1">Harga & Stok</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="step3-tab" data-bs-toggle="pill" data-bs-target="#step3" type="button" role="tab" aria-controls="step3" aria-selected="false">
                                        <span class="step-icon"><i class='bx bx-cog'></i></span>
                                        <span class="step-text d-none d-sm-inline ms-1">Pengaturan & Simpan</span>
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="formWizardContent">
                                <!-- STEP 1: INFO & MEDIA -->
                                <div class="tab-pane fade show active" id="step1" role="tabpanel" aria-labelledby="step1-tab">
<!-- Section: Foto Produk -->
                            <div class="form-section mb-4">
                                <h6 class="section-title mb-3">
                                    <i class='bx bx-image me-2'></i>Foto Produk
                                </h6>
                                <div class="row g-2 g-sm-3">
                                    <!-- Foto Utama -->
                                    <div class="col-6 col-md-4">
                                        <label class="form-label fw-semibold" for="foto">Foto Utama</label>
                                        <div class="upload-box" onclick="document.getElementById('foto').click()">
                                            <div id="preview_foto" class="preview-container" style="display:none;">
                                                <img src="#" alt="Preview" class="preview-image" />
                                                <button type="button" class="btn-remove-image" onclick="event.stopPropagation(); clearFile('foto', 'preview_foto')">
                                                    <span style="font-size: 20px; font-weight: bold; line-height: 1; color: white;">&times;</span>
                                                </button>
                                            </div>
                                            <div id="placeholder_foto" class="upload-placeholder">
                                                <i class='bx bx-cloud-upload' style="font-size: 40px;"></i>
                                                <p class="mb-0 mt-2">Klik untuk upload</p>
                                                <small class="text-muted">JPG, PNG (Max 8MB)</small>
                                            </div>
                                        </div>
                                        <input type="file" class="d-none" id="foto" name="foto" 
                                               accept="image/*" onchange="previewFile(this, 'preview_foto', 'placeholder_foto')" />
                                    </div>

                                    <!-- Foto Tambahan 1 -->
                                    <div class="col-6 col-md-4">
                                        <label class="form-label fw-semibold" for="foto_2">Foto Tambahan 1</label>
                                        <div class="upload-box" onclick="document.getElementById('foto_2').click()">
                                            <div id="preview_foto_2" class="preview-container" style="display:none;">
                                                <img src="#" alt="Preview" class="preview-image" />
                                                <button type="button" class="btn-remove-image" onclick="event.stopPropagation(); clearFile('foto_2', 'preview_foto_2')">
                                                    <span style="font-size: 20px; font-weight: bold; line-height: 1; color: white;">&times;</span>
                                                </button>
                                            </div>
                                            <div id="placeholder_foto_2" class="upload-placeholder">
                                                <i class='bx bx-cloud-upload' style="font-size: 40px;"></i>
                                                <p class="mb-0 mt-2">Klik untuk upload</p>
                                                <small class="text-muted">JPG, PNG (Max 8MB)</small>
                                            </div>
                                        </div>
                                        <input type="file" class="d-none" id="foto_2" name="foto_2" 
                                               accept="image/*" onchange="previewFile(this, 'preview_foto_2', 'placeholder_foto_2')" />
                                    </div>

                                    <!-- Foto Tambahan 2 -->
                                    <div class="col-6 col-md-4">
                                        <label class="form-label fw-semibold" for="foto_3">Foto Tambahan 2</label>
                                        <div class="upload-box" onclick="document.getElementById('foto_3').click()">
                                            <div id="preview_foto_3" class="preview-container" style="display:none;">
                                                <img src="#" alt="Preview" class="preview-image" />
                                                <button type="button" class="btn-remove-image" onclick="event.stopPropagation(); clearFile('foto_3', 'preview_foto_3')">
                                                    <span style="font-size: 20px; font-weight: bold; line-height: 1; color: white;">&times;</span>
                                                </button>
                                            </div>
                                            <div id="placeholder_foto_3" class="upload-placeholder">
                                                <i class='bx bx-cloud-upload' style="font-size: 40px;"></i>
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
                                        <label class="form-label fw-semibold" for="jenis_gas">
                                            Jenis Gas <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control modern-input" id="jenis_gas" 
                                               name="jenis_gas" placeholder="Contoh: LPG 3 Kg" required />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="kategori">
                                            Kategori <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <select class="form-select modern-input" id="kategori" name="kategori" required>
                                                <option value="" disabled selected>Pilih Kategori</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->name }}">{{ $category->name }}</option>
                                                @endforeach
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
                                                  rows="4" placeholder="Jelaskan detail produk gas, spesifikasi, dan kegunaan..." required></textarea>
                                    </div>
                                </div>
                            </div>

                            
                                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                                        <button type="button" class="btn btn-primary flex-grow-1 flex-sm-grow-0" onclick="nextStep('step2-tab')">Selanjutnya <i class='bx bx-right-arrow-alt ms-1'></i></button>
                                    </div>
                                </div>
                                
                                <!-- STEP 2: HARGA & STOK -->
                                <div class="tab-pane fade" id="step2" role="tabpanel" aria-labelledby="step2-tab">
<!-- Section: Harga & Stok -->
                            <div class="form-section mb-4">
                                <h6 class="section-title mb-3">
                                    <i class='bx bx-money me-2'></i>Harga & Stok
                                </h6>
                                <div class="row g-2 g-sm-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label fw-semibold" for="harga_satuan">
                                            Harga Satuan <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text modern-input-addon">Rp</span>
                                            <input type="text" class="form-control modern-input" id="harga_satuan" 
                                                   name="harga_satuan" placeholder="21.000" required oninput="formatRupiah(this)" />
                                        </div>
                                        <small class="form-text text-muted">Masukkan angka tanpa titik atau koma</small>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <label class="form-label fw-semibold" for="stok">
                                            Stok Tersedia <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control modern-input" id="stok" 
                                               name="stok" placeholder="50" min="0" required />
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <label class="form-label fw-semibold" for="satuan">
                                            Satuan <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <select class="form-select modern-input" id="satuan" name="satuan" required>
                                                <option value="" disabled selected>Pilih Satuan</option>
                                                <option value="Unit">Unit</option>
                                                <option value="Paket">Paket</option>

                                            </select>
                                            <button type="button" class="btn btn-outline-primary modern-btn-outline" 
                                                    data-bs-toggle="modal" data-bs-target="#addSatuanModal">
                                                <i class="bx bx-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                                    <div class="d-flex justify-content-between gap-2 mt-4 pt-3 border-top">
                                        <button type="button" class="btn btn-secondary flex-grow-1 flex-sm-grow-0" onclick="prevStep('step1-tab')"><i class='bx bx-left-arrow-alt me-1'></i> Sebelumnya</button>
                                        <button type="button" class="btn btn-primary flex-grow-1 flex-sm-grow-0" onclick="nextStep('step3-tab')">Selanjutnya <i class='bx bx-right-arrow-alt ms-1'></i></button>
                                    </div>
                                </div>
                                
                                <!-- STEP 3: PENGATURAN & SIMPAN -->
                                <div class="tab-pane fade" id="step3" role="tabpanel" aria-labelledby="step3-tab">
                                
                                    <!-- Section: Status & Lokasi -->
                            <div class="form-section mb-4">
                                <h6 class="section-title mb-3">
                                    <i class='bx bx-map me-2'></i>Status & Lokasi
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold" for="status">
                                            Status <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select modern-input" id="status" name="status" required>
                                            <option value="tersedia" selected>Tersedia</option>
                                            <option value="dipesan">Dipesan</option>
                                            <option value="rusak">Rusak</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mt-3">
                                        <label class="form-label fw-semibold">Pilih Metode Lokasi <span class="text-danger">*</span></label>
                                        <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-3 mb-3">
                                            @if($savedLocations->count() > 0)
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="lokasi_mode" id="mode_saved" value="saved" onchange="toggleLokasiMode(this.value)">
                                                <label class="form-check-label" for="mode_saved">
                                                    Gunakan Lokasi Tersimpan
                                                </label>
                                            </div>
                                            @endif
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="lokasi_mode" id="mode_new" value="new" checked onchange="toggleLokasiMode(this.value)">
                                                <label class="form-check-label" for="mode_new">
                                                    Tentukan Lokasi Baru
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    @if($savedLocations->count() > 0)
                                    <div class="col-md-12 mb-3" id="saved_location_container" style="display: none;">
                                        <label class="form-label fw-semibold" for="saved_lokasi">Pilih Lokasi</label>
                                        <select class="form-select modern-input" id="saved_lokasi" onchange="fillLocationData(this)">
                                            <option value="">-- Pilih Lokasi --</option>
                                            @foreach($savedLocations as $loc)
                                                <option value="{{ $loc->lokasi }}" data-lat="{{ $loc->latitude }}" data-lng="{{ $loc->longitude }}">
                                                    {{ $loc->lokasi }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    <div class="col-md-12" id="new_location_container">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold" for="lokasi">
                                                    Lokasi <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control modern-input" id="lokasi" 
                                                       name="lokasi" value="Desa Pematang Duku Timur" required />
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>



                            
                                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mt-4 pt-3 border-top">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep('step2-tab')"><i class='bx bx-left-arrow-alt me-1'></i> Sebelumnya</button>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.unit.penjualan_gas.index') }}" class="btn btn-light border flex-grow-1 flex-sm-grow-0 text-center">Batal</a>
                                            <button type="submit" class="btn btn-success flex-grow-1 flex-sm-grow-0"><i class='bx bx-save me-1'></i> Simpan Data</button>
                                        </div>
                                    </div>
                                </div> <!-- End step 3 -->
                            </div> <!-- End tab-content -->

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modals')
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
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #ffffff;
            aspect-ratio: 4/3;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .upload-box:hover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }

        .upload-placeholder {
            color: #6c757d;
            max-width: 100%;
        }

        .upload-placeholder i {
            color: #94a3b8;
            transition: all 0.3s ease;
        }

        .upload-box:hover .upload-placeholder i {
            color: var(--primary-color);
            transform: translateY(-3px) scale(1.05);
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
            top: 6px;
            right: 6px;
            width: 28px;
            height: 28px;
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
            z-index: 5;
        }

        .btn-remove-image:hover {
            background: #bb2d3b;
            transform: scale(1.1);
        }

        /* Mobile Optimization */
        @media (max-width: 767.98px) {
            .form-section {
                padding: 16px 12px !important;
                margin-bottom: 1rem !important;
            }
            .upload-box {
                padding: 10px 4px !important;
                aspect-ratio: 4/3 !important;
                border-radius: 8px !important;
            }
            .upload-placeholder i {
                font-size: 26px !important;
            }
            .upload-placeholder p {
                font-size: 11px !important;
                margin-top: 4px !important;
                line-height: 1.2 !important;
            }
            .upload-placeholder small {
                font-size: 9px !important;
                display: block !important;
            }
            .btn-remove-image {
                width: 24px !important;
                height: 24px !important;
                top: 4px !important;
                right: 4px !important;
            }
            .btn-remove-image span {
                font-size: 16px !important;
            }
        }

        @media (max-width: 575.98px) {
            .card-body.p-4 {
                padding: 1rem !important;
            }
            .wizard-steps .nav-link {
                padding: 0.5rem 0.25rem !important;
            }
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
    
    .wizard-steps .nav-link {
        border-radius: 0.5rem;
        color: #6c757d;
        font-weight: 500;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
    }
    .wizard-steps .nav-link:hover {
        background-color: #e9ecef;
        color: #495057;
    }
    .wizard-steps .nav-link.active {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
        box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
    }
    .step-icon {
        font-size: 1.25rem;
        vertical-align: middle;
    }
</style>
@endsection

@section('scripts')
<script>
    // Fungsi untuk format angka menjadi Rupiah
    function formatRupiah(input) {
        let value = input.value.replace(/\D/g, '');
        if (value) {
            value = new Intl.NumberFormat('id-ID').format(value);
            input.value = value;
        }
    }

    // Fungsi untuk preview file gambar
    function previewFile(input, previewId, placeholderId) {
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);
        const img = preview ? preview.querySelector('img') : null;

        if (input.files && input.files[0]) {
            if (typeof initGlobalCropper === 'function') {
                initGlobalCropper(input, img || previewId, NaN, true);
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                if (img) img.src = e.target.result;
                if (preview) preview.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
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

    // Fungsi untuk menambah kategori via AJAX
    document.getElementById('saveCategoryBtn').addEventListener('click', function() {
        const newKategori = document.getElementById('new_kategori').value.trim();
        if (newKategori) {
            const saveBtn = this;
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
            saveBtn.disabled = true;

            fetch('{{ route("admin.categories.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: newKategori, type: 'gas' })
            })
            .then(response => response.json())
            .then(data => {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
                
                if(data.success) {
                    const select = document.getElementById('kategori');
                    const option = document.createElement('option');
                    option.value = data.category.name;
                    option.textContent = data.category.name;
                    select.appendChild(option);
                    select.value = data.category.name;
                    $('#addCategoryModal').modal('hide');
                    document.getElementById('new_kategori').value = '';
                } else {
                    alert('Gagal menyimpan kategori.');
                }
            })
            .catch(err => {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
                alert('Terjadi kesalahan jaringan.');
            });
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
            $('#addSatuanModal').modal('hide');
            document.getElementById('new_satuan').value = '';
        } else {
            alert('Silakan masukkan nama satuan.');
        }
    });
    
    function nextStep(tabId) {
        var activeTabPane = document.querySelector('.tab-pane.active');
        if (activeTabPane) {
            var inputs = activeTabPane.querySelectorAll('input[required], select[required], textarea[required]');
            for (var i = 0; i < inputs.length; i++) {
                if (!inputs[i].checkValidity()) {
                    inputs[i].reportValidity();
                    return; 
                }
            }
        }
        var tabEl = document.querySelector('#' + tabId);
        var tab = new bootstrap.Tab(tabEl);
        tab.show();
        window.scrollTo(0, 0);
    }
    function prevStep(tabId) {
        var tabEl = document.querySelector('#' + tabId);
        var tab = new bootstrap.Tab(tabEl);
        tab.show();
        window.scrollTo(0, 0);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const toggles = document.querySelectorAll('.delivery-toggle');
        toggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const field = this.dataset.field;
                const value = this.checked ? 1 : 0;
                this.disabled = true;

                fetch('{{ route("admin.region-settings.toggle-delivery") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ field: field, value: value })
                })
                .then(response => response.json())
                .then(data => {
                    this.disabled = false;
                    if(data.success) {
                        const toast = document.createElement('div');
                        toast.className = 'alert alert-success position-fixed top-0 end-0 m-3 shadow-sm';
                        toast.style.zIndex = '9999';
                        toast.innerHTML = '<i class="bx bx-check-circle me-2"></i>' + data.message;
                        document.body.appendChild(toast);
                        setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 500); }, 3000);
                    } else {
                        alert(data.message);
                        this.checked = !this.checked; 
                    }
                })
                .catch(err => {
                    this.disabled = false;
                    alert('Terjadi kesalahan jaringan.');
                    this.checked = !this.checked; 
                });
            });
        });
    });

    function toggleLokasiMode(mode) {
        const savedContainer = document.getElementById('saved_location_container');
        const newContainer = document.getElementById('new_location_container');
        const select = document.getElementById('saved_lokasi');
        const inputLokasi = document.getElementById('lokasi');
        const inputLat = document.getElementById('latitude');
        const inputLng = document.getElementById('longitude');

        if (mode === 'saved') {
            if(savedContainer) savedContainer.style.display = 'block';
            newContainer.style.display = 'none';
            if(select) {
                inputLokasi.readOnly = true;
                if(inputLat) inputLat.readOnly = true;
                if(inputLng) inputLng.readOnly = true;
                fillLocationData(select);
            }
        } else {
            if(savedContainer) savedContainer.style.display = 'none';
            newContainer.style.display = 'block';
            inputLokasi.readOnly = false;
            if(inputLat) inputLat.readOnly = false;
            if(inputLng) inputLng.readOnly = false;
            inputLokasi.value = '';
            inputLat.value = '';
            inputLng.value = '';
        }
    }

    function fillLocationData(select) {
        const option = select.options[select.selectedIndex];
        if (option && option.value !== '') {
            document.getElementById('lokasi').value = option.value;
            let latEl = document.getElementById('latitude'); if(latEl) latEl.value = option.dataset.lat || '';
            let lngEl = document.getElementById('longitude'); if(lngEl) lngEl.value = option.dataset.lng || '';
        } else {
            document.getElementById('lokasi').value = '';
            let latEl2 = document.getElementById('latitude'); if(latEl2) latEl2.value = '';
            let lngEl2 = document.getElementById('longitude'); if(lngEl2) lngEl2.value = '';
        }
    }

</script>
@endsection
