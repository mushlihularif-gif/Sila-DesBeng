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
                <span class="text-muted fw-light">Unit Layanan / Penyewaan Mobil /</span> Tambah Alat
            </h4>
            <p class="text-muted mb-0">Lengkapi formulir di bawah untuk menambahkan alat baru</p>
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
                                <h5 class="mb-0 fw-bold">Form Tambah Mobil Sewa</h5>
                                <small class="text-muted">Masukkan detail kendaraan yang akan disewakan</small>
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

                        <form action="{{ route('admin.unit.mobil.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            
                            <!-- STEPS NAVIGATION -->
                            <ul class="nav nav-pills nav-justified mb-4 wizard-steps" id="formWizard" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="step1-tab" data-bs-toggle="pill" data-bs-target="#step1" type="button" role="tab" aria-controls="step1" aria-selected="true">
                                        <span class="step-icon"><i class='bx bx-info-circle'></i></span>
                                        <span class="step-text d-none d-sm-inline ms-1">Info & Media</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="step2-tab" data-bs-toggle="pill" data-bs-target="#step2" type="button" role="tab" aria-controls="step2" aria-selected="false">
                                        <span class="step-icon"><i class='bx bx-car'></i></span>
                                        <span class="step-text d-none d-sm-inline ms-1">Sewa Harian</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="step3-tab" data-bs-toggle="pill" data-bs-target="#step3" type="button" role="tab" aria-controls="step3" aria-selected="false">
                                        <span class="step-icon"><i class='bx bx-money'></i></span>
                                        <span class="step-text d-none d-sm-inline ms-1">Sewa Borongan</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="step4-tab" data-bs-toggle="pill" data-bs-target="#step4" type="button" role="tab" aria-controls="step4" aria-selected="false">
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
                                <div class="row g-3">
                                    <!-- Foto Utama -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold" for="foto">Foto Utama</label>
                                        <div class="upload-box" onclick="document.getElementById('foto').click()">
                                            <div id="preview_foto" class="preview-container" style="display:none;">
                                                <img src="#" alt="Preview" class="preview-image" />
                                                <button type="button" class="btn-remove-image" onclick="event.stopPropagation(); clearFile('foto', 'preview_foto')">
                                                    <span style="font-size: 24px; font-weight: bold; line-height: 1; color: white;">&times;</span>
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
                                                    <span style="font-size: 24px; font-weight: bold; line-height: 1; color: white;">&times;</span>
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
                                                    <span style="font-size: 24px; font-weight: bold; line-height: 1; color: white;">&times;</span>
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
                                        <label class="form-label fw-semibold" for="nama_mobil">
                                            Nama Barang <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control modern-input" id="nama_mobil" 
                                               name="nama_mobil" placeholder="Contoh: Tenda Pesta 5x5m" required />
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
                                    <div class="col-md-12 mt-3">
                                        <label class="form-label fw-semibold" for="plat_nomor">
                                            Plat Nomor Kendaraan (Opsional)
                                        </label>
                                        <input type="text" class="form-control modern-input" id="plat_nomor" 
                                               name="plat_nomor" placeholder="Contoh: BM 1234 XY" value="{{ isset($mobil) ? $mobil->plat_nomor : (isset($fasilitas) ? $fasilitas->plat_nomor : old('plat_nomor')) }}" />
                                    </div>
                                </div>

                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="stok">
                                            Stok Mobil Tersedia <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control modern-input" id="stok" name="stok" placeholder="1" min="0" required />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="satuan">
                                            Satuan <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <select class="form-select modern-input" id="satuan" name="satuan" required>
                                                <option value="Unit" selected>Unit</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12 mt-3">
                                        <label class="form-label fw-semibold" for="deskripsi">
                                            Deskripsi <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control modern-input" id="deskripsi" name="deskripsi" 
                                                  rows="4" placeholder="Jelaskan detail alat, kondisi, dan spesifikasi..." required></textarea>
                                    </div>
                                </div>
                            </div>

                            
                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="button" class="btn btn-primary" onclick="nextStep('step2-tab')">Selanjutnya <i class='bx bx-right-arrow-alt'></i></button>
                                    </div>
                                </div>
                                
                                <!-- STEP 2: SEWA HARIAN -->
                                <div class="tab-pane fade" id="step2" role="tabpanel" aria-labelledby="step2-tab">

                            <!-- Section: Pengaturan Sewa Harian -->
                            <div class="form-section mb-4">
                                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                                    <h6 class="section-title mb-0 mt-1">
                                        <i class='bx bx-car me-2'></i><span class="badge bg-primary me-2">SEWA HARIAN</span> Pengaturan Sewa Harian
                                    </h6>
                                    <div class="bg-white p-2 px-3 rounded shadow-sm border d-flex align-items-center mb-0">
                                        <div class="form-check form-switch fs-4 mb-0">
                                            <input class="form-check-input cursor-pointer mt-1" style="margin-left: -2em;" type="checkbox" name="is_harian_active" id="is_harian_active" checked onchange="toggleLayanan('harian')">
                                            <label class="form-check-label fs-6 mt-1 ms-3 fw-bold text-primary" for="is_harian_active">Aktifkan Layanan Ini</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-primary bg-primary-subtle border-primary text-primary d-flex align-items-start" role="alert" style="border-radius: 12px;">
                                    <i class="bx bx-info-circle fs-4 me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-primary">Apa itu Sewa Harian?</h6>
                                        <p class="mb-0 small">Sistem penyewaan dihitung per 24 jam dengan tarif tetap per hari. Penyewa dapat memesan untuk 1 hari, 2 hari, seminggu (7 hari), atau lebih, dengan total harga yang dikalikan sesuai jumlah hari. Penyewa bebas menggunakan kendaraan untuk berbagai tujuan selama masa waktu sewa berjalan.</p>
                                    </div>
                                </div>

                                <div id="harian_content_wrapper">
                                    <p class="text-muted small mb-3">Tentukan harga dasar per hari dan opsi layanan pendukung untuk penyewaan sistem harian.</p>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label text-muted small fw-semibold text-uppercase letter-spacing-1 mb-2" for="harga_sewa">
                                            Harga Sewa Dasar (Per Hari) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">Rp</span>
                                            <input type="text" class="form-control fw-semibold" id="harga_sewa" 
                                                   name="harga_sewa" placeholder="250.000" 
                                                   value="{{ old('harga_sewa', isset($mobil) ? number_format($mobil->harga_sewa, 0, ',', '.') : '') }}"
                                                   required oninput="formatRupiah(this)" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Layanan Tambahan -->
                                <div class="form-section mb-4 mt-4">
                                    <h6 class="section-title mb-3">
                                        <i class='bx bx-plus-circle me-2'></i>Pengaturan Layanan Tambahan (Supir & BBM)
                                    </h6>
                                    <p class="text-muted small mb-3">Aktifkan layanan yang ingin Anda sediakan untuk unit penyewaan ini (Berlaku untuk Sewa Harian).</p>
                                    
                                    <div class="row g-3">
                                        <div style="display:none;"><!-- Opsi Tanpa Supir -->
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded bg-white">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="{{ asset('User/img/iconbaru/supirsendiri.png') }}" style="width: 40px; height: 40px; object-fit: contain;">
                                                        <h6 class="mb-0 fw-bold">Sewa Tanpa Supir (Bawa Sendiri)</h6>
                                                    </div>
                                                    <div class="form-check form-switch fs-5 mb-0">
                                                        <input class="form-check-input cursor-pointer" type="checkbox" id="switch_tanpa_supir" onchange="updateSupirStatus()" checked>
                                                    </div>
                                                </div>
                                                <div class="text-muted small mb-0 fw-semibold" id="status_tanpa_supir">Tidak Aktif</div>
                                            </div>
                                        </div>

                                        <!-- Opsi Dengan Supir -->
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded bg-white">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="{{ asset('User/img/iconbaru/disediakansupir.png') }}" style="width: 40px; height: 40px; object-fit: contain;">
                                                        <h6 class="mb-0 fw-bold">Sewa Dengan Supir Pengelola</h6>
                                                    </div>
                                                    <div class="form-check form-switch fs-5 mb-0">
                                                        <input class="form-check-input cursor-pointer" type="checkbox" id="switch_dengan_supir" onchange="updateSupirStatus()">
                                                    </div>
                                                </div>
                                                <div class="text-muted small mb-0 fw-semibold" id="status_dengan_supir">Tidak Aktif</div>
                                                
                                                
                                                  <!-- Form Detail Supir -->
                                                  <div id="form_supir_details" class="mt-3 p-3 bg-light border rounded" style="display: none;">
                                                      <div class="mb-3">
                                                          <label class="form-label small fw-bold">Nama Supir (Opsional)</label>
                                                          <input type="text" class="form-control" name="nama_supir" id="nama_supir" placeholder="Contoh: Pak Budi" value="{{ old('nama_supir') }}">
                                                      </div>
                                                      <div class="mb-0">
                                                          <label class="form-label small fw-bold">Kontak Supir (WhatsApp)</label>
                                                          <input type="text" class="form-control" name="kontak_supir" id="kontak_supir" placeholder="Contoh: 08123456789" value="{{ old('kontak_supir') }}">
                                                      </div>
                                                  </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="opsi_supir" id="opsi_supir" value="Lepas Kunci"></div>

                                    <hr class="my-4 text-muted">
                                    
                                    <div class="row g-3">
                                        <!-- BBM Disediakan Pengelola -->
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded bg-white">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="{{ asset('User/img/iconbaru/bbmdisediakan.png') }}" style="width: 40px; height: 40px; object-fit: contain;">
                                                        <h6 class="mb-0 fw-bold">BBM Disediakan Pengelola</h6>
                                                    </div>
                                                    <div class="form-check form-switch fs-5 mb-0">
                                                        <input class="form-check-input cursor-pointer" type="radio" name="bbm_switch" id="switch_bbm_desa" value="Pengelola" onchange="updateBbmStatus()">
                                                    </div>
                                                </div>
                                                <div class="text-muted small mb-0 fw-semibold" id="status_bbm_desa">Tidak Aktif</div>
                                            </div>
                                        </div>

                                        <!-- BBM Ditanggung Penyewa -->
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded bg-white">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="{{ asset('User/img/iconbaru/bbmditanggungpengguna.png') }}" style="width: 40px; height: 40px; object-fit: contain;">
                                                        <h6 class="mb-0 fw-bold">BBM Ditanggung Penyewa</h6>
                                                    </div>
                                                    <div class="form-check form-switch fs-5 mb-0">
                                                        <input class="form-check-input cursor-pointer" type="radio" name="bbm_switch" id="switch_bbm_penyewa" value="Penyewa" onchange="updateBbmStatus()" checked>
                                                    </div>
                                                </div>
                                                <div class="text-muted small mb-0 fw-semibold" id="status_bbm_penyewa">Tidak Aktif</div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="bbm_ditanggung" id="bbm_ditanggung" required>
                                </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep('step1-tab')"><i class='bx bx-left-arrow-alt'></i> Sebelumnya</button>
                                        <button type="button" class="btn btn-primary" onclick="nextStep('step3-tab')">Selanjutnya <i class='bx bx-right-arrow-alt'></i></button>
                                    </div>
                                </div>
                                </div> <!-- End Step 2: Sewa Harian -->
                                
                                <!-- STEP 3: SEWA BORONGAN -->
                                <div class="tab-pane fade" id="step3" role="tabpanel" aria-labelledby="step3-tab">

                            <!-- Section: Harga & Jarak \(Sistem Zona Borongan\) -->
                            <div class="form-section mb-4">
                                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                                    <h6 class="section-title mb-0 mt-1">
                                        <i class='bx bx-money me-2'></i><span class="badge bg-success me-2">SEWA BORONGAN (DROP OFF)</span> Pengaturan Zona Jarak
                                    </h6>
                                    <div class="bg-white p-2 px-3 rounded shadow-sm border d-flex align-items-center mb-0">
                                        <div class="form-check form-switch fs-4 mb-0">
                                            <input class="form-check-input cursor-pointer mt-1" style="margin-left: -2em;" type="checkbox" name="is_borongan_active" id="is_borongan_active" checked onchange="toggleLayanan('borongan')">
                                            <label class="form-check-label fs-6 mt-1 ms-3 fw-bold text-success" for="is_borongan_active">Aktifkan Layanan Ini</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-success bg-success-subtle border-success text-success d-flex align-items-start" role="alert" style="border-radius: 12px;">
                                    <i class="bx bx-info-circle fs-4 me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-success">Apa itu Sewa Borongan?</h6>
                                        <p class="mb-0 small">Sistem penyewaan sekali jalan (Drop-off) atau paket insidental per rute tujuan tanpa melihat durasi harian. Sangat cocok untuk keperluan mengantar penumpang, jemputan dari bandara/pelabuhan, atau keperluan insidental satu hari penuh ke satu titik tujuan spesifik.</p>
                                    </div>
                                </div>

                                <div id="borongan_content_wrapper">
                                <div class="mb-4">
                                    <label class="form-label text-dark fw-bold mb-3">Pilih Metode Perhitungan Tarif Borongan <span class="text-danger">*</span></label>
                                    
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center gap-3 p-3 border rounded-3 w-100" style="cursor: pointer; transition: all 0.2s;" id="label_tarif_jarak" onclick="setTarifBoronganType('jarak')">
                                                <input type="radio" name="tipe_tarif_borongan" id="tipe_tarif_jarak" value="jarak" class="form-check-input mt-0" style="transform: scale(1.2);" checked onchange="setTarifBoronganType('jarak')">
                                                <div>
                                                    <div class="fw-bold text-dark mb-1">Berdasarkan Jarak (Km)</div>
                                                    <div class="small text-muted" style="line-height: 1.2;">Hitung berdasarkan zona jarak kilometer</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center gap-3 p-3 border rounded-3 w-100" style="cursor: pointer; transition: all 0.2s;" id="label_tarif_wilayah" onclick="setTarifBoronganType('wilayah')">
                                                <input type="radio" name="tipe_tarif_borongan" id="tipe_tarif_wilayah" value="wilayah" class="form-check-input mt-0" style="transform: scale(1.2);" onchange="setTarifBoronganType('wilayah')">
                                                <div>
                                                    <div class="fw-bold text-dark mb-1">Berdasarkan Wilayah</div>
                                                    <div class="small text-muted" style="line-height: 1.2;">Pilih tujuan berdasarkan desa atau kecamatan</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="borongan_jarak_wrapper">
                                    <p class="text-muted small mb-3">Tentukan harga borongan (paket sewa) berdasarkan jarak tempuh tujuan penyewa. Biarkan batas Km tetap 0 jika tidak ada batasan.</p>

                                
                                <div class="row g-4 mb-4">
                                    <!-- Dalam Desa -->
                                    <div class="col-md-4">
                                        <div class="card h-100 shadow-sm border-0">
                                            <div class="card-header bg-transparent border-bottom border-primary border-2 pb-2">
                                                <h6 class="mb-0 fw-bold text-primary"><i class="bx bx-home-circle me-2 fs-5 align-middle"></i>Zona 1: Dalam Desa</h6>
                                            </div>
                                            <div class="card-body pt-3">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small fw-semibold">Maksimal Jarak (Km)</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control" name="batas_km_dalam_desa" value="5" required>
                                                        <span class="input-group-text bg-light">Km</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="form-label text-muted small fw-semibold">Harga Sewa</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light">Rp</span>
                                                        <input type="text" class="form-control fw-semibold" name="harga_dalam_desa" placeholder="100.000" required oninput="formatRupiah(this)">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Luar Desa -->
                                    <div class="col-md-4">
                                        <div class="card h-100 shadow-sm border-0">
                                            <div class="card-header bg-transparent border-bottom border-warning border-2 pb-2">
                                                <h6 class="mb-0 fw-bold text-warning"><i class="bx bx-map me-2 fs-5 align-middle"></i>Zona 2: Luar Desa</h6>
                                            </div>
                                            <div class="card-body pt-3">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small fw-semibold">Maksimal Jarak (Km)</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control" name="batas_km_luar_desa" value="50" required>
                                                        <span class="input-group-text bg-light">Km</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="form-label text-muted small fw-semibold">Harga Sewa</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light">Rp</span>
                                                        <input type="text" class="form-control fw-semibold" name="harga_luar_desa" placeholder="200.000" required oninput="formatRupiah(this)">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Luar Kota -->
                                    <div class="col-md-4">
                                        <div class="card h-100 shadow-sm border-0">
                                            <div class="card-header bg-transparent border-bottom border-danger border-2 pb-2">
                                                <h6 class="mb-0 fw-bold text-danger"><i class="bx bx-building-house me-2 fs-5 align-middle"></i>Zona 3: Luar Kota</h6>
                                            </div>
                                            <div class="card-body pt-3">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small fw-semibold">Syarat Jarak</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" class="form-control bg-lighter text-muted" value="> Batas Luar Desa" readonly disabled>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="form-label text-muted small fw-semibold">Harga Sewa</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light">Rp</span>
                                                        <input type="text" class="form-control fw-semibold" name="harga_luar_kota" placeholder="400.000" required oninput="formatRupiah(this)">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                


                            </div>
                            
                            
                                </div> <!-- Close borongan_jarak_wrapper -->
                                <div id="borongan_wilayah_wrapper" style="display: none;">
                                    <p class="text-muted small mb-3">Tentukan harga borongan berdasarkan tujuan wilayah administrasi penyewa.</p>
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-dark fw-bold" for="harga_dalam_desa_wilayah">Tarif Dalam Desa (Satu Desa) <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-merge border-light-subtle shadow-sm rounded-3">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control fw-semibold" id="harga_dalam_desa_wilayah" name="harga_dalam_desa_wilayah" 
                                                value="" oninput="formatRupiah(this)" placeholder="100.000">
                                        </div>
                                        <small class="text-muted mt-1 d-block">Tarif untuk tujuan di desa yang sama.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-dark fw-bold" for="harga_luar_desa_wilayah">Tarif Luar Desa (Beda Desa, Satu Kecamatan) <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-merge border-light-subtle shadow-sm rounded-3">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control fw-semibold" id="harga_luar_desa_wilayah" name="harga_luar_desa_wilayah" 
                                                value="" oninput="formatRupiah(this)" placeholder="150.000">
                                        </div>
                                        <small class="text-muted mt-1 d-block">Tarif untuk tujuan ke desa tetangga di dalam 1 kecamatan.</small>
                                    </div>

                                    <hr class="my-4 text-light">

                                    <div class="mb-4">
                                        <label class="form-label text-dark fw-bold mb-3">Pilih Metode Tarif Luar Kecamatan <span class="text-danger">*</span></label>
                                        
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center gap-3 p-3 border rounded-3 w-100" style="cursor: pointer; transition: all 0.2s;" id="label_pukul_rata" onclick="setOngkirType('pukul_rata')">
                                                    <input type="radio" name="tipe_luar_kecamatan_wilayah" id="tipe_pukul_rata" value="pukul_rata" class="form-check-input mt-0" style="transform: scale(1.2);" checked onchange="setOngkirType('pukul_rata')">
                                                    <div>
                                                        <div class="fw-bold text-dark mb-1">Pukul Rata</div>
                                                        <div class="small text-muted" style="line-height: 1.2;">Satu harga untuk semua luar kecamatan</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center gap-3 p-3 border rounded-3 w-100" style="cursor: pointer; transition: all 0.2s;" id="label_per_kecamatan" onclick="setOngkirType('per_kecamatan')">
                                                    <input type="radio" name="tipe_luar_kecamatan_wilayah" id="tipe_per_kecamatan" value="per_kecamatan" class="form-check-input mt-0" style="transform: scale(1.2);" onchange="setOngkirType('per_kecamatan')">
                                                    <div>
                                                        <div class="fw-bold text-dark mb-1">Per Kecamatan</div>
                                                        <div class="small text-muted" style="line-height: 1.2;">Tentukan harga untuk masing-masing daerah</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Opsi Pukul Rata -->
                                        <div id="div_pukul_rata" class="mt-3 p-3 bg-light rounded-4 border" style="display: block;">
                                            <label class="form-label fw-medium" for="harga_luar_kecamatan_wilayah">Harga Pukul Rata Luar Kecamatan</label>
                                            <div class="input-group input-group-merge border-light-subtle shadow-sm rounded-3">
                                                <span class="input-group-text bg-white">Rp</span>
                                                <input type="text" class="form-control fw-semibold border-start-0" id="harga_luar_kecamatan_wilayah" name="harga_luar_kecamatan_wilayah" 
                                                    value="" oninput="formatRupiah(this)" placeholder="250.000">
                                            </div>
                                            <small class="text-muted mt-2 d-block">Tarif otomatis berlaku untuk semua tujuan kecamatan lain.</small>
                                        </div>

                                        <!-- Opsi Per Kecamatan -->
                                        <div id="div_per_kecamatan" class="mt-3" style="display: none;">
                                            <div class="table-responsive text-nowrap border rounded-4 shadow-sm">
                                                <table class="table table-hover mb-0">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th class="py-3 text-secondary text-uppercase small fw-bold" style="width: 50px;">Layanan</th>
                                                            <th class="py-3 text-secondary text-uppercase small fw-bold">Nama Kecamatan</th>
                                                            <th class="py-3 text-secondary text-uppercase small fw-bold text-end pe-4">Tarif (Rp)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="table-border-bottom-0">
                                                        @foreach($semuaKecamatan as $kec)
                                                            <tr>
                                                                <td class="text-center">
                                                                    <div class="form-check form-switch d-flex justify-content-center mb-0">
                                                                        <input class="form-check-input" type="checkbox" role="switch" id="switch_kec_{{ $kec->id }}" 
                                                                            onchange="toggleKecamatan({{ $kec->id }})">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <label class="form-check-label fw-medium text-muted" id="label_kec_{{ $kec->id }}" for="switch_kec_{{ $kec->id }}">
                                                                        {{ $kec->name }}
                                                                    </label>
                                                                </td>
                                                                <td class="pe-3">
                                                                    <div class="input-group input-group-sm">
                                                                        <span class="input-group-text bg-light text-muted">Rp</span>
                                                                        <input type="text" class="form-control text-end fw-semibold" 
                                                                            name="harga_kecamatan_khusus[{{ $kec->id }}]" id="input_kec_{{ $kec->id }}" 
                                                                            value="" placeholder="0" disabled oninput="formatRupiah(this)">
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <small class="text-danger mt-2 d-block"><i class="bx bx-info-circle me-1"></i>Kecamatan yang dimatikan (switch abu-abu) berarti Anda TIDAK melayani penyewaan borongan ke daerah tersebut.</small>
                                        </div>
                                    </div>
                                </div>

                            <!-- Section: Layanan Tambahan Borongan -->
                            <div class="form-section mb-4 mt-4">
                                <h6 class="section-title mb-3">
                                    <i class='bx bx-plus-circle me-2'></i>Pengaturan Layanan Tambahan Borongan (Supir & BBM)
                                </h6>
                                <p class="text-muted small mb-3">Aktifkan layanan yang ingin Anda sediakan untuk unit penyewaan ini (Berlaku untuk Sewa Borongan).</p>
                                
                                <div class="row g-3">
                                    <div style="display:none;"><!-- Opsi Tanpa Supir Borongan -->
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded bg-white">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ asset('User/img/iconbaru/supirsendiri.png') }}" style="width: 40px; height: 40px; object-fit: contain;">
                                                    <h6 class="mb-0 fw-bold">Sewa Tanpa Supir (Bawa Sendiri)</h6>
                                                </div>
                                                <div class="form-check form-switch fs-5 mb-0">
                                                    <input class="form-check-input cursor-pointer" type="checkbox" id="switch_tanpa_supir_borongan" onchange="updateSupirStatusBorongan()" checked>
                                                </div>
                                            </div>
                                            <div class="text-muted small mb-0 fw-semibold" id="status_tanpa_supir_borongan">Tidak Aktif</div>
                                        </div>
                                    </div>

                                    <!-- Opsi Dengan Supir Borongan -->
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded bg-white">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ asset('User/img/iconbaru/disediakansupir.png') }}" style="width: 40px; height: 40px; object-fit: contain;">
                                                    <h6 class="mb-0 fw-bold">Sewa Dengan Supir Pengelola</h6>
                                                </div>
                                                <div class="form-check form-switch fs-5 mb-0">
                                                    <input class="form-check-input cursor-pointer" type="checkbox" id="switch_dengan_supir_borongan" onchange="updateSupirStatusBorongan()">
                                                </div>
                                            </div>
                                            <div class="text-muted small mb-0 fw-semibold" id="status_dengan_supir_borongan">Tidak Aktif</div>
                                            
                                            
                                              <!-- Form Detail Supir Borongan -->
                                              <div id="form_supir_details_borongan" class="mt-3 p-3 bg-light border rounded" style="display: none;">
                                                  <div class="mb-3">
                                                      <label class="form-label small fw-bold">Nama Supir Borongan (Opsional)</label>
                                                      <input type="text" class="form-control" name="nama_supir_borongan" id="nama_supir_borongan" placeholder="Contoh: Pak Budi" value="{{ old('nama_supir_borongan') }}">
                                                  </div>
                                                  <div class="mb-0">
                                                      <label class="form-label small fw-bold">Kontak Supir Borongan (WhatsApp)</label>
                                                      <input type="text" class="form-control" name="kontak_supir_borongan" id="kontak_supir_borongan" placeholder="Contoh: 08123456789" value="{{ old('kontak_supir_borongan') }}">
                                                  </div>
                                              </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                
                                <input type="hidden" name="opsi_supir_borongan" id="opsi_supir_borongan" value="Lepas Kunci"></div>

                                <hr class="my-4 text-muted">
                                
                                <div class="row g-3 mb-4">
                                    <!-- BBM Disediakan Pengelola Borongan -->
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded bg-white">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ asset('User/img/iconbaru/bbmdisediakan.png') }}" style="width: 40px; height: 40px; object-fit: contain;">
                                                    <h6 class="mb-0 fw-bold">BBM Disediakan Pengelola</h6>
                                                </div>
                                                <div class="form-check form-switch fs-5 mb-0">
                                                    <input class="form-check-input cursor-pointer" type="radio" name="bbm_switch_borongan" id="switch_bbm_desa_borongan" value="Pemerintah Desa" onchange="updateBbmStatusBorongan()">
                                                </div>
                                            </div>
                                            <div class="text-muted small mb-0 fw-semibold" id="status_bbm_desa_borongan">Tidak Aktif</div>
                                        </div>
                                    </div>

                                    <!-- BBM Ditanggung Penyewa Borongan -->
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded bg-white">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ asset('User/img/iconbaru/bbmditanggungpengguna.png') }}" style="width: 40px; height: 40px; object-fit: contain;">
                                                    <h6 class="mb-0 fw-bold">BBM Ditanggung Penyewa</h6>
                                                </div>
                                                <div class="form-check form-switch fs-5 mb-0">
                                                    <input class="form-check-input cursor-pointer" type="radio" name="bbm_switch_borongan" id="switch_bbm_penyewa_borongan" value="Penyewa" onchange="updateBbmStatusBorongan()" checked>
                                                </div>
                                            </div>
                                            <div class="text-muted small mb-0 fw-semibold" id="status_bbm_penyewa_borongan">Tidak Aktif</div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="bbm_ditanggung_borongan" id="bbm_ditanggung_borongan" required>
                            </div>

                            
                                </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep('step2-tab')"><i class='bx bx-left-arrow-alt'></i> Sebelumnya</button>
                                        <button type="button" class="btn btn-primary" onclick="nextStep('step4-tab')">Selanjutnya <i class='bx bx-right-arrow-alt'></i></button>
                                    </div>
                                </div>
                                
                                <!-- STEP 4: PENGATURAN & SIMPAN -->
                                <div class="tab-pane fade" id="step4" role="tabpanel" aria-labelledby="step4-tab">
                                
                                    <div class="alert alert-info bg-info-subtle border-info text-info d-flex align-items-start mb-4" role="alert" style="border-radius: 12px;">
                                        <i class="bx bx-cog fs-4 me-3 mt-1"></i>
                                        <div>
                                            <h6 class="fw-bold mb-1 text-info">Tahap Akhir: Pengaturan Titik Serah Terima Kendaraan</h6>
                                            <p class="mb-0 small">Pada tahap ini, Anda menentukan <strong>bagaimana cara penyewa menerima mobil tersebut</strong>. Anda dapat mengaktifkan opsi "Diantar ke alamat penyewa" (opsi ini memerlukan kurir/supir), atau "Jemput di Pool/Kantor" agar penyewa mengambil kendaraannya sendiri.</p>
                                        </div>
                                    </div>
                                    
                                    <!-- PENGATURAN PENGIRIMAN GLOBAL SEWA & RENTAL -->
                                    <div class="col-12 mb-4">
                                        <div class="row g-4">
                                            <!-- Sewa Borongan -->
                                            <div class="col-md-6">
                                                <div class="card h-100 shadow-sm border-0">
                                                    <div class="card-header bg-transparent border-bottom border-success border-2 pb-2">
                                                        <h6 class="mb-0 fw-bold text-success"><i class="bx bx-package me-2 fs-5 align-middle"></i>Sewa Borongan (Drop-off)</h6>
                                                    </div>
                                                    <div class="card-body p-4 pt-3">
                                                        <p class="text-muted small mb-3">Aktifkan opsi pengiriman yang tersedia khusus untuk layanan Sewa Borongan.</p>
                                                        
                                                        <ul class="list-group list-group-flush">
                                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent py-3">
                                                                <div>
                                                                    <h6 class="mb-0 fw-semibold text-dark">Sediakan Jasa Diantar</h6>
                                                                    <small class="text-muted">Unit diantar ke lokasi penyewa</small>
                                                                </div>
                                                                <div class="form-check form-switch fs-4 mb-0">
                                                                    <input class="form-check-input delivery-toggle cursor-pointer" type="checkbox" id="delivery_antar_sewa" data-field="mobil_sewa_delivery_antar_active" {{ !empty($paymentInfo['mobil_sewa_delivery_antar_active']) || !isset($paymentInfo['mobil_sewa_delivery_antar_active']) ? 'checked' : '' }}>
                                                                </div>
                                                            </li>
                                                            
                                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent border-bottom-0 pb-0 pt-3">
                                                                <div>
                                                                    <h6 class="mb-0 fw-semibold text-dark">Bisa Jemput Sendiri</h6>
                                                                    <small class="text-muted">Penyewa ambil unit di garasi</small>
                                                                </div>
                                                                <div class="form-check form-switch fs-4 mb-0">
                                                                    <input class="form-check-input delivery-toggle cursor-pointer" type="checkbox" id="delivery_jemput_sewa" data-field="mobil_sewa_delivery_jemput_active" {{ !empty($paymentInfo['mobil_sewa_delivery_jemput_active']) || !isset($paymentInfo['mobil_sewa_delivery_jemput_active']) ? 'checked' : '' }}>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Rental Harian -->
                                            <div class="col-md-6">
                                                <div class="card h-100 shadow-sm border-0">
                                                    <div class="card-header bg-transparent border-bottom border-primary border-2 pb-2">
                                                        <h6 class="mb-0 fw-bold text-primary"><i class="bx bx-calendar me-2 fs-5 align-middle"></i>Rental Harian</h6>
                                                    </div>
                                                    <div class="card-body p-4 pt-3">
                                                        <p class="text-muted small mb-3">Aktifkan opsi pengiriman yang tersedia khusus untuk layanan Rental Harian.</p>
                                                        
                                                        <ul class="list-group list-group-flush">
                                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent py-3">
                                                                <div>
                                                                    <h6 class="mb-0 fw-semibold text-dark">Sediakan Jasa Diantar</h6>
                                                                    <small class="text-muted">Unit diantar ke lokasi penyewa</small>
                                                                </div>
                                                                <div class="form-check form-switch fs-4 mb-0">
                                                                    <input class="form-check-input delivery-toggle cursor-pointer" type="checkbox" id="delivery_antar_rental" data-field="mobil_rental_delivery_antar_active" {{ !empty($paymentInfo['mobil_rental_delivery_antar_active']) || !isset($paymentInfo['mobil_rental_delivery_antar_active']) ? 'checked' : '' }}>
                                                                </div>
                                                            </li>
                                                            
                                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent border-bottom-0 pb-0 pt-3">
                                                                <div>
                                                                    <h6 class="mb-0 fw-semibold text-dark">Bisa Jemput Sendiri</h6>
                                                                    <small class="text-muted">Penyewa ambil unit di garasi</small>
                                                                </div>
                                                                <div class="form-check form-switch fs-4 mb-0">
                                                                    <input class="form-check-input delivery-toggle cursor-pointer" type="checkbox" id="delivery_jemput_rental" data-field="mobil_rental_delivery_jemput_active" {{ !empty($paymentInfo['mobil_rental_delivery_jemput_active']) || !isset($paymentInfo['mobil_rental_delivery_jemput_active']) ? 'checked' : '' }}>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
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
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold" for="status">
                                            Status <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select modern-input" id="status" name="status" required>
                                            <option value="tersedia" selected>Tersedia</option>
                                            <option value="disewa">Disewa</option>
                                            <option value="rusak">Rusak</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mt-3">
                                        <label class="form-label fw-semibold">Pilih Metode Lokasi <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3 mb-3">
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
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold" for="latitude">Latitude (Opsional)</label>
                                                <input type="text" class="form-control modern-input" id="latitude" name="latitude" placeholder="-6.200000" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold" for="longitude">Longitude (Opsional)</label>
                                                <input type="text" class="form-control modern-input" id="longitude" name="longitude" placeholder="106.816666" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep('step3-tab')"><i class='bx bx-left-arrow-alt'></i> Sebelumnya</button>
                                        <div>
                                            <a href="{{ route('admin.unit.mobil.index') }}" class="btn btn-light me-2 border">Batal</a>
                                            <button type="submit" class="btn btn-success"><i class='bx bx-save'></i> Simpan Data</button>
                                        </div>
                                    </div>
                                </div> <!-- End Step 4 -->
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

        /* Option Cards (Toggle UI) */
        .option-card {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            background: white;
        }
        .option-card:hover {
            border-color: var(--primary-light);
            background: var(--bg-soft);
            transform: translateY(-2px);
        }
        .option-card.active {
            border-color: var(--primary-color);
            background: var(--primary-soft);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }
        .option-card img {
            max-height: 80px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        .option-card p {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 0;
            color: #475569;
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
    
<style>
    .wizard-steps .nav-link {
        border-radius: 0.5rem;
        color: #6c757d;
        font-weight: 500;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
    }
    .wizard-steps .nav-link.active {
        background-color: #0d6efd;
        color: white;
        box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
    }
    .step-icon {
        font-size: 1.25rem;
        vertical-align: middle;
    }
</style>

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

    // JS Logic for Supir & BBM Toggle
    function updateSupirStatus() {
        const switchTanpa = document.getElementById('switch_tanpa_supir').checked;
        const switchDengan = document.getElementById('switch_dengan_supir').checked;
        
        document.getElementById('status_tanpa_supir').textContent = switchTanpa ? 'Aktif' : 'Tidak Aktif';
        document.getElementById('status_tanpa_supir').className = switchTanpa ? 'small mb-0 fw-bold text-success' : 'small mb-0 fw-semibold text-muted';
        
        document.getElementById('status_dengan_supir').textContent = switchDengan ? 'Aktif' : 'Tidak Aktif';
        document.getElementById('status_dengan_supir').className = switchDengan ? 'small mb-0 fw-bold text-success' : 'small mb-0 fw-semibold text-muted';

        const input = document.getElementById('opsi_supir');
        const details = document.getElementById('form_supir_details');
        if (details) details.style.display = switchDengan ? 'block' : 'none';
        
        const detailsBorongan = document.getElementById('form_supir_details_borongan');
        if (detailsBorongan) detailsBorongan.style.display = switchDengan ? 'block' : 'none';
        
        if (switchTanpa && switchDengan) {
            input.value = 'Bebas Pilih';
        } else if (switchTanpa) {
            input.value = 'Lepas Kunci';
        } else if (switchDengan) {
            input.value = 'Dengan Supir';
        } else {
            input.value = '';
        }
        
        if (switchDengan) {
            details.style.display = 'block';
        } else {
            details.style.display = 'none';
            const ns = document.getElementById('nama_supir'); if(ns) ns.value = '';
            const ks = document.getElementById('kontak_supir'); if(ks) ks.value = '';
        }
    }

    function updateBbmStatus() {
        const switchDesa = document.getElementById('switch_bbm_desa').checked;
        const switchPenyewa = document.getElementById('switch_bbm_penyewa').checked;
        
        document.getElementById('status_bbm_desa').textContent = switchDesa ? 'Aktif' : 'Tidak Aktif';
        document.getElementById('status_bbm_desa').className = switchDesa ? 'small mb-0 fw-bold text-success' : 'small mb-0 fw-semibold text-muted';

        document.getElementById('status_bbm_penyewa').textContent = switchPenyewa ? 'Aktif' : 'Tidak Aktif';
        document.getElementById('status_bbm_penyewa').className = switchPenyewa ? 'small mb-0 fw-bold text-success' : 'small mb-0 fw-semibold text-muted';

        if (switchDesa) {
            document.getElementById('bbm_ditanggung').value = 'Pengelola';
        } else if (switchPenyewa) {
            document.getElementById('bbm_ditanggung').value = 'Penyewa';
        }
    }

    // JS Logic for Supir & BBM Toggle (Borongan)
    function updateSupirStatusBorongan() {
        const switchTanpa = document.getElementById('switch_tanpa_supir_borongan').checked;
        const switchDengan = document.getElementById('switch_dengan_supir_borongan').checked;
        
        document.getElementById('status_tanpa_supir_borongan').textContent = switchTanpa ? 'Aktif' : 'Tidak Aktif';
        document.getElementById('status_tanpa_supir_borongan').className = switchTanpa ? 'small mb-0 fw-bold text-success' : 'small mb-0 fw-semibold text-muted';
        
        document.getElementById('status_dengan_supir_borongan').textContent = switchDengan ? 'Aktif' : 'Tidak Aktif';
        document.getElementById('status_dengan_supir_borongan').className = switchDengan ? 'small mb-0 fw-bold text-success' : 'small mb-0 fw-semibold text-muted';

        const input = document.getElementById('opsi_supir_borongan');
        const details = document.getElementById('form_supir_details_borongan');
        
        if (switchTanpa && switchDengan) {
            input.value = 'Bebas Pilih';
        } else if (switchTanpa) {
            input.value = 'Lepas Kunci';
        } else if (switchDengan) {
            input.value = 'Dengan Supir';
        } else {
            input.value = '';
        }
        
        if (switchDengan) {
            details.style.display = 'block';
        } else {
            details.style.display = 'none';
            const nsb = document.getElementById('nama_supir_borongan'); if(nsb) nsb.value = '';
            const ksb = document.getElementById('kontak_supir_borongan'); if(ksb) ksb.value = '';
        }
    }

    function updateBbmStatusBorongan() {
        const switchDesa = document.getElementById('switch_bbm_desa_borongan').checked;
        const switchPenyewa = document.getElementById('switch_bbm_penyewa_borongan').checked;
        
        document.getElementById('status_bbm_desa_borongan').textContent = switchDesa ? 'Aktif' : 'Tidak Aktif';
        document.getElementById('status_bbm_desa_borongan').className = switchDesa ? 'small mb-0 fw-bold text-success' : 'small mb-0 fw-semibold text-muted';

        document.getElementById('status_bbm_penyewa_borongan').textContent = switchPenyewa ? 'Aktif' : 'Tidak Aktif';
        document.getElementById('status_bbm_penyewa_borongan').className = switchPenyewa ? 'small mb-0 fw-bold text-success' : 'small mb-0 fw-semibold text-muted';

        if (switchDesa) {
            document.getElementById('bbm_ditanggung_borongan').value = 'Pengelola';
        } else if (switchPenyewa) {
            document.getElementById('bbm_ditanggung_borongan').value = 'Penyewa';
        }
    }

    // Inisialisasi status di awal
    updateSupirStatus();
    updateBbmStatus();
    updateSupirStatusBorongan();
    updateBbmStatusBorongan();

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
                body: JSON.stringify({ name: newKategori, type: 'mobil' })
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
                inputLat.readOnly = true;
                inputLng.readOnly = true;
                fillLocationData(select);
            }
        } else {
            if(savedContainer) savedContainer.style.display = 'none';
            newContainer.style.display = 'block';
            inputLokasi.readOnly = false;
            inputLat.readOnly = false;
            inputLng.readOnly = false;
            inputLokasi.value = '';
            inputLat.value = '';
            inputLng.value = '';
        }
    }

    function fillLocationData(select) {
        const option = select.options[select.selectedIndex];
        if (option && option.value !== '') {
            document.getElementById('lokasi').value = option.value;
            document.getElementById('latitude').value = option.dataset.lat || '';
            document.getElementById('longitude').value = option.dataset.lng || '';
        } else {
            document.getElementById('lokasi').value = '';
            document.getElementById('latitude').value = '';
            document.getElementById('longitude').value = '';
        }
    }

    function toggleLayanan(type) {
        const isHarian = document.getElementById('is_harian_active').checked;
        const isBorongan = document.getElementById('is_borongan_active').checked;
        
        // Cek agar tidak dua-duanya nonaktif
        if (!isHarian && !isBorongan) {
            alert('Minimal harus ada satu layanan (Harian atau Borongan) yang diaktifkan.');
            if (type === 'harian') {
                document.getElementById('is_harian_active').checked = true;
            } else {
                document.getElementById('is_borongan_active').checked = true;
            }
            return;
        }

        // Toggle visibility wrapper
        if (type === 'harian') {
            document.getElementById('harian_content_wrapper').style.display = isHarian ? 'block' : 'none';
        } else {
            document.getElementById('borongan_content_wrapper').style.display = isBorongan ? 'block' : 'none';
        }
    }

    // Panggil saat page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleLayanan('harian');
        toggleLayanan('borongan');
    });


    // Script for Tipe Borongan
    function setTarifBoronganType(type) {
        const divJarak = document.getElementById('borongan_jarak_wrapper');
        const divWilayah = document.getElementById('borongan_wilayah_wrapper');
        const labelJarak = document.getElementById('label_tarif_jarak');
        const labelWilayah = document.getElementById('label_tarif_wilayah');
        
        // Remove required from all first
        const jarakInputs = divJarak.querySelectorAll('input[type="number"], input[type="text"]');
        const wilayahInputs = document.querySelectorAll('#harga_dalam_desa_wilayah, #harga_luar_desa_wilayah');
        
        jarakInputs.forEach(el => el.removeAttribute('required'));
        wilayahInputs.forEach(el => el.removeAttribute('required'));

        if (type === 'jarak') {
            divJarak.style.display = 'block';
            divWilayah.style.display = 'none';
            labelJarak.classList.replace('border', 'border-primary');
            labelJarak.style.backgroundColor = 'var(--bs-primary-bg-subtle)';
            labelWilayah.classList.replace('border-primary', 'border');
            labelWilayah.style.backgroundColor = 'transparent';
            
            document.getElementById('tipe_tarif_jarak').checked = true;
            
            // Add required back to visible inputs if borongan is active
            if(document.getElementById('is_borongan_active').checked) {
                jarakInputs.forEach(el => el.setAttribute('required', 'required'));
            }
        } else {
            divJarak.style.display = 'none';
            divWilayah.style.display = 'block';
            labelWilayah.classList.replace('border', 'border-primary');
            labelWilayah.style.backgroundColor = 'var(--bs-primary-bg-subtle)';
            labelJarak.classList.replace('border-primary', 'border');
            labelJarak.style.backgroundColor = 'transparent';
            
            document.getElementById('tipe_tarif_wilayah').checked = true;
            
            if(document.getElementById('is_borongan_active').checked) {
                wilayahInputs.forEach(el => el.setAttribute('required', 'required'));
            }
        }
    }

    function setOngkirType(type) {
        const divPukulRata = document.getElementById('div_pukul_rata');
        const divPerKecamatan = document.getElementById('div_per_kecamatan');
        const labelPukulRata = document.getElementById('label_pukul_rata');
        const labelPerKecamatan = document.getElementById('label_per_kecamatan');

        if (type === 'pukul_rata') {
            divPukulRata.style.display = 'block';
            divPerKecamatan.style.display = 'none';
            labelPukulRata.classList.replace('border', 'border-primary');
            labelPukulRata.style.backgroundColor = 'var(--bs-primary-bg-subtle)';
            labelPerKecamatan.classList.replace('border-primary', 'border');
            labelPerKecamatan.style.backgroundColor = 'transparent';
            document.getElementById('tipe_pukul_rata').checked = true;
        } else {
            divPukulRata.style.display = 'none';
            divPerKecamatan.style.display = 'block';
            labelPerKecamatan.classList.replace('border', 'border-primary');
            labelPerKecamatan.style.backgroundColor = 'var(--bs-primary-bg-subtle)';
            labelPukulRata.classList.replace('border-primary', 'border');
            labelPukulRata.style.backgroundColor = 'transparent';
            document.getElementById('tipe_per_kecamatan').checked = true;
        }
    }

    function toggleKecamatan(id) {
        const switchEl = document.getElementById('switch_kec_' + id);
        const inputEl = document.getElementById('input_kec_' + id);
        const labelEl = document.getElementById('label_kec_' + id);

        if (switchEl.checked) {
            inputEl.disabled = false;
            inputEl.setAttribute('required', 'required');
            labelEl.classList.remove('text-muted');
            labelEl.classList.add('text-dark');
        } else {
            inputEl.disabled = true;
            inputEl.removeAttribute('required');
            inputEl.value = '';
            labelEl.classList.remove('text-dark');
            labelEl.classList.add('text-muted');
        }
    }

    // Initialize styling
    document.addEventListener('DOMContentLoaded', function() {
        setTarifBoronganType('jarak');
        setOngkirType('pukul_rata');
    });

</script>
@endsection
