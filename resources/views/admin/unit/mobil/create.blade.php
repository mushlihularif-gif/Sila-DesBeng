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
                <span class="text-muted fw-light">Unit Layanan / Penyewaan Mobil /</span> Tambah Mobil
            </h4>
            <p class="text-muted mb-0">Lengkapi formulir di bawah untuk menambahkan mobil baru</p>
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
                            <ul class="nav nav-pills nav-fill mb-4 wizard-steps flex-column flex-sm-row gap-2" id="formWizard" role="tablist">
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
                                
                                <div class="alert alert-primary bg-primary-subtle border-primary text-primary d-flex align-items-start shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                                    <i class="bx bx-info-circle fs-3 me-3 mt-1"></i>
                                    <div class="w-100">
                                        <h6 class="fw-bold mb-2 text-primary fs-6 border-bottom border-primary pb-2" style="border-opacity: 0.3;">Apa itu Sewa Harian?</h6>
                                        <ul class="mb-0 small ps-3" style="line-height: 1.6;">
                                            <li class="mb-1"><span class="fw-bold text-primary-emphasis">Hitungan Waktu:</span> Tarif ditetapkan per 24 jam.</li>
                                            <li class="mb-1"><span class="fw-bold text-primary-emphasis">Durasi Fleksibel:</span> Bisa dirental selama 1 hari, 3 hari, seminggu, atau lebih. Sistem otomatis mengalikan harga x hari.</li>
                                            <li><span class="fw-bold text-primary-emphasis">Kebebasan Rute:</span> Penyewa bebas berkeliling membawa mobil ke tujuan mana pun selama batas waktu sewa aktif (Lepas Kunci / Pakai Supir).</li>
                                        </ul>
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

                            <!-- Section: Layanan Tambahan Harian (Supir & BBM) -->
                            <div class="form-section mb-4 mt-4">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="section-title mb-0">
                                        <i class='bx bx-plus-circle me-2'></i>Pengaturan Layanan Tambahan Harian (Supir & BBM)
                                    </h6>
                                </div>
                                
                                <!-- Info Petunjuk Supir Harian -->
                                <div class="alert alert-light border border-info-subtle bg-info-subtle text-dark d-flex align-items-start py-2.5 px-3 mb-3 rounded-3 shadow-none">
                                    <i class="bx bx-info-circle text-info fs-5 me-2 mt-1"></i>
                                    <div class="small">
                                        <strong>Petunjuk Pilihan Layanan Supir:</strong> Anda dapat mengaktifkan salah satu opsi atau <strong>keduanya sekaligus</strong>.
                                        <ul class="mb-0 ps-3 mt-1" style="line-height: 1.5;">
                                            <li><strong>Jika Keduanya Aktif:</strong> Di website pemesanan warga, akan muncul <em>2 pilihan kartu</em> sehingga warga bebas memilih antara sewa lepas kunci (bawa sendiri) atau memakai supir pengelola.</li>
                                            <li><strong>Jika Hanya Salah Satu Aktif:</strong> Halaman pemesanan warga akan <em>otomatis terkunci</em> pada opsi tersebut saja (warga tidak diberikan pilihan lain).</li>
                                            <li><em>Catatan: Minimal salah satu opsi harus selalu aktif.</em></li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Opsi Supir Harian -->
                                <div class="row g-3 mb-4">
                                    <!-- Opsi Tanpa Supir (Bawa Sendiri) -->
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-white h-100 cursor-pointer shadow-sm position-relative transition-all" id="card_supir_tanpa_harian" onclick="toggleSupirTanpaCardHarian(event)">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ asset('User/img/iconbaru/supirsendiri.png') }}" style="width: 38px; height: 38px; object-fit: contain;">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark">Sewa Tanpa Supir (Lepas Kunci)</h6>
                                                        <span class="badge bg-success small" id="status_tanpa_supir">Aktif</span>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch fs-5 mb-0">
                                                    <input class="form-check-input cursor-pointer" type="checkbox" id="switch_tanpa_supir" onchange="updateSupirStatus()" checked>
                                                </div>
                                            </div>
                                            <p class="text-muted small mb-0 mt-2" style="font-size: 0.825rem; line-height: 1.4;">
                                                <strong>Jika Aktif:</strong> Penyewa diperbolehkan mengemudikan kendaraan sendiri secara mandiri tanpa pendampingan supir dari pihak pengelola.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Opsi Dengan Supir Pengelola -->
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-white h-100 cursor-pointer shadow-sm position-relative transition-all" id="card_supir_dengan_harian" onclick="toggleSupirDenganCardHarian(event)">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ asset('User/img/iconbaru/disediakansupir.png') }}" style="width: 38px; height: 38px; object-fit: contain;">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark">Sewa Dengan Supir Pengelola</h6>
                                                        <span class="badge bg-label-secondary small" id="status_dengan_supir">Tidak Aktif</span>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch fs-5 mb-0">
                                                    <input class="form-check-input cursor-pointer" type="checkbox" id="switch_dengan_supir" onchange="updateSupirStatus()">
                                                </div>
                                            </div>
                                            <p class="text-muted small mb-0 mt-2" style="font-size: 0.825rem; line-height: 1.4;">
                                                <strong>Jika Aktif:</strong> Pengelola menyediakan supir untuk melayani penyewa. Supir akan ditugaskan oleh admin saat pesanan disetujui.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <input type="hidden" name="opsi_supir" id="opsi_supir" value="Lepas Kunci">

                                <hr class="my-4 text-muted opacity-25">

                                <!-- Opsi BBM Harian -->
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label fw-bold text-dark mb-0">Ketentuan Bahan Bakar (BBM) Sewa Harian <span class="text-danger">*</span></label>
                                </div>
                                
                                <!-- Info Petunjuk BBM Harian -->
                                <div class="alert alert-light border border-warning-subtle bg-warning-subtle text-dark d-flex align-items-start py-2.5 px-3 mb-3 rounded-3 shadow-none">
                                    <i class="bx bx-gas-pump text-warning fs-5 me-2 mt-1"></i>
                                    <div class="small">
                                        <strong>Petunjuk Ketentuan BBM (Wajib Pilih Salah Satu):</strong>
                                        <p class="mb-1 mt-1" style="line-height: 1.4;">
                                            Kebijakan BBM hanya dapat dipilih <strong>salah satu saja</strong> karena menentukan dasar transparansi tarif sewa:
                                        </p>
                                        <ul class="mb-0 ps-3" style="line-height: 1.5;">
                                            <li><strong>BBM Disediakan Pengelola:</strong> Pilih opsi ini jika harga sewa yang Anda tetapkan sudah <em>termasuk bensin</em> (diisi/ditanggung penuh pengelola).</li>
                                            <li><strong>BBM Ditanggung Penyewa:</strong> Pilih opsi ini jika bensin <em>tidak termasuk</em>, sehingga penyewa wajib mengisi bensin secara mandiri.</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="row g-3 mb-2">
                                    <!-- BBM Disediakan Pengelola -->
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-white h-100 cursor-pointer shadow-sm position-relative transition-all" id="card_bbm_desa" onclick="selectBbmHarian('Pengelola')">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ asset('User/img/iconbaru/bbmdisediakan.png') }}" style="width: 38px; height: 38px; object-fit: contain;">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark">BBM Disediakan Pengelola</h6>
                                                        <span class="badge bg-label-secondary small" id="status_bbm_desa">Tidak Aktif</span>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch fs-5 mb-0">
                                                    <input class="form-check-input cursor-pointer" type="radio" name="bbm_switch" id="switch_bbm_desa" value="Pengelola" onchange="updateBbmStatus()">
                                                </div>
                                            </div>
                                            <p class="text-muted small mb-0 mt-2" style="font-size: 0.825rem; line-height: 1.4;">
                                                <strong>Ketentuan:</strong> Harga sewa harian sudah termasuk bahan bakar (BBM diisi/ditanggung penuh oleh pengelola).
                                            </p>
                                        </div>
                                    </div>

                                    <!-- BBM Ditanggung Penyewa -->
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-white h-100 cursor-pointer shadow-sm position-relative transition-all" id="card_bbm_penyewa" onclick="selectBbmHarian('Penyewa')">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ asset('User/img/iconbaru/bbmditanggungpengguna.png') }}" style="width: 38px; height: 38px; object-fit: contain;">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark">BBM Ditanggung Penyewa</h6>
                                                        <span class="badge bg-success small" id="status_bbm_penyewa">Aktif</span>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch fs-5 mb-0">
                                                    <input class="form-check-input cursor-pointer" type="radio" name="bbm_switch" id="switch_bbm_penyewa" value="Penyewa" onchange="updateBbmStatus()" checked>
                                                </div>
                                            </div>
                                            <p class="text-muted small mb-0 mt-2" style="font-size: 0.825rem; line-height: 1.4;">
                                                <strong>Ketentuan:</strong> Penyewa bertanggung jawab mengisi bahan bakar mandiri sesuai pemakaian selama masa sewa.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="bbm_ditanggung" id="bbm_ditanggung" value="Penyewa" required>
                            </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep('step1-tab')"><i class='bx bx-left-arrow-alt'></i> Sebelumnya</button>
                                        <button type="button" class="btn btn-primary" onclick="nextStep('step3-tab')">Selanjutnya <i class='bx bx-right-arrow-alt'></i></button>
                                    </div>
                                </div>
                                </div> <!-- End Step 2: Sewa Harian -->
                                
                                                                <!-- STEP 3: SEWA BORONGAN -->
                                <div class="tab-pane fade" id="step3" role="tabpanel" aria-labelledby="step3-tab">

                                    <div class="form-section mb-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                                            <h6 class="section-title mb-0 mt-1">
                                                <i class='bx bx-money me-2'></i><span class="badge bg-success me-2">SEWA BORONGAN (DROP OFF)</span> Pengaturan Tarif Wilayah
                                            </h6>
                                            <div class="bg-white p-2 px-3 rounded shadow-sm border d-flex align-items-center mb-0">
                                                <div class="form-check form-switch fs-4 mb-0">
                                                    <input class="form-check-input cursor-pointer mt-1" style="margin-left: -2em;" type="checkbox" name="is_borongan_active" id="is_borongan_active" checked onchange="toggleLayanan('borongan')">
                                                    <label class="form-check-label fs-6 mt-1 ms-3 fw-bold text-success" for="is_borongan_active">Aktifkan Layanan Ini</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="alert alert-success bg-success-subtle border-success text-success d-flex align-items-start shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                                            <i class="bx bx-map-alt fs-3 me-3 mt-1"></i>
                                            <div class="w-100">
                                                <h6 class="fw-bold mb-2 text-success fs-6 border-bottom border-success pb-2" style="border-opacity: 0.3;">Apa itu Sewa Borongan (Drop-Off/Carter)?</h6>
                                                <ul class="mb-0 small ps-3" style="line-height: 1.6;">
                                                    <li class="mb-1"><span class="fw-bold text-success-emphasis">Hitungan Rute:</span> Tarif ditetapkan berdasarkan wilayah tujuan (Beda Desa / Luar Kecamatan / Luar Kota).</li>
                                                    <li class="mb-1"><span class="fw-bold text-success-emphasis">Tanpa Patokan Hari:</span> Tidak dihitung per 24 jam. Selesai mengantar atau acara selesai, maka penyewaan otomatis berakhir.</li>
                                                    <li><span class="fw-bold text-success-emphasis">Penggunaan:</span> Sangat cocok untuk mengantar rombongan warga ke acara tertentu, jemputan bandara, atau drop-off luar kota 1 kali jalan.</li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div id="borongan_content_wrapper">
                                            <div id="borongan_wilayah_wrapper">
                                                <input type="hidden" name="tipe_tarif_borongan" value="wilayah">
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
                                                                                        <!-- Section: Layanan Tambahan Borongan (Supir & BBM) -->
                                            <div class="form-section mb-4 mt-4">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <h6 class="section-title mb-0">
                                                        <i class='bx bx-plus-circle me-2'></i>Pengaturan Layanan Tambahan Borongan (Supir & BBM)
                                                    </h6>
                                                </div>

                                                <!-- Info Petunjuk Supir Borongan -->
                                                <div class="alert alert-light border border-success-subtle bg-success-subtle text-dark d-flex align-items-start py-2.5 px-3 mb-3 rounded-3 shadow-none">
                                                    <i class="bx bx-info-circle text-success fs-5 me-2 mt-1"></i>
                                                    <div class="small">
                                                        <strong>Petunjuk Pilihan Layanan Supir Borongan:</strong> Anda dapat mengaktifkan salah satu opsi atau <strong>keduanya sekaligus</strong>.
                                                        <ul class="mb-0 ps-3 mt-1" style="line-height: 1.5;">
                                                            <li><strong>Jika Keduanya Aktif:</strong> Di website pemesanan, warga bebas memilih apakah ingin membawa unit borongan sendiri atau didampingi supir resmi pengelola.</li>
                                                            <li><strong>Jika Hanya Salah Satu Aktif:</strong> Pemesanan borongan otomatis terkunci pada opsi tersebut saja.</li>
                                                            <li><em>Catatan: Minimal salah satu opsi harus selalu aktif.</em></li>
                                                        </ul>
                                                    </div>
                                                </div>

                                                <!-- Opsi Supir Borongan -->
                                                <div class="row g-3 mb-4">
                                                    <!-- Opsi Tanpa Supir Borongan -->
                                                    <div class="col-md-6">
                                                        <div class="p-3 border rounded-3 bg-white h-100 cursor-pointer shadow-sm position-relative transition-all" id="card_supir_tanpa_borongan" onclick="toggleSupirTanpaCardBorongan(event)">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <img src="{{ asset('User/img/iconbaru/supirsendiri.png') }}" style="width: 38px; height: 38px; object-fit: contain;">
                                                                    <div>
                                                                        <h6 class="mb-0 fw-bold text-dark">Sewa Tanpa Supir (Bawa Sendiri)</h6>
                                                                        <span class="badge bg-success small" id="status_tanpa_supir_borongan">Aktif</span>
                                                                    </div>
                                                                </div>
                                                                <div class="form-check form-switch fs-5 mb-0">
                                                                    <input class="form-check-input cursor-pointer" type="checkbox" id="switch_tanpa_supir_borongan" onchange="updateSupirStatusBorongan()" checked>
                                                                </div>
                                                            </div>
                                                            <p class="text-muted small mb-0 mt-2" style="font-size: 0.825rem; line-height: 1.4;">
                                                                <strong>Jika Aktif:</strong> Penyewa diperbolehkan mengemudikan sendiri mobil borongan tanpa menggunakan supir dari pihak pengelola.
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <!-- Opsi Dengan Supir Borongan -->
                                                    <div class="col-md-6">
                                                        <div class="p-3 border rounded-3 bg-white h-100 cursor-pointer shadow-sm position-relative transition-all" id="card_supir_dengan_borongan" onclick="toggleSupirDenganCardBorongan(event)">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <img src="{{ asset('User/img/iconbaru/disediakansupir.png') }}" style="width: 38px; height: 38px; object-fit: contain;">
                                                                    <div>
                                                                        <h6 class="mb-0 fw-bold text-dark">Sewa Dengan Supir Pengelola</h6>
                                                                        <span class="badge bg-label-secondary small" id="status_dengan_supir_borongan">Tidak Aktif</span>
                                                                    </div>
                                                                </div>
                                                                <div class="form-check form-switch fs-5 mb-0">
                                                                    <input class="form-check-input cursor-pointer" type="checkbox" id="switch_dengan_supir_borongan" onchange="updateSupirStatusBorongan()">
                                                                </div>
                                                            </div>
                                                            <p class="text-muted small mb-0 mt-2" style="font-size: 0.825rem; line-height: 1.4;">
                                                                <strong>Jika Aktif:</strong> Pengelola menugaskan supir resmi dari Data Supir & Petugas untuk mengantar rombongan warga.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <input type="hidden" name="opsi_supir_borongan" id="opsi_supir_borongan" value="Lepas Kunci">

                                                <hr class="my-4 text-muted opacity-25">
                                                
                                                <!-- Opsi BBM Borongan -->
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <label class="form-label fw-bold text-dark mb-0">Ketentuan Bahan Bakar (BBM) Sewa Borongan <span class="text-danger">*</span></label>
                                                </div>
                                                
                                                <!-- Info Petunjuk BBM Borongan -->
                                                <div class="alert alert-light border border-warning-subtle bg-warning-subtle text-dark d-flex align-items-start py-2.5 px-3 mb-3 rounded-3 shadow-none">
                                                    <i class="bx bx-gas-pump text-warning fs-5 me-2 mt-1"></i>
                                                    <div class="small">
                                                        <strong>Petunjuk Ketentuan BBM Borongan (Wajib Pilih Salah Satu):</strong>
                                                        <p class="mb-1 mt-1" style="line-height: 1.4;">
                                                            Ketentuan BBM borongan hanya dapat dipilih <strong>salah satu saja</strong> untuk kejelasan transparansi tarif carter/drop-off:
                                                        </p>
                                                        <ul class="mb-0 ps-3" style="line-height: 1.5;">
                                                            <li><strong>BBM Disediakan Pengelola:</strong> Pilih opsi ini jika tarif borongan yang ditetapkan sudah <em>all-in mencakup bensin</em> penuh sampai tujuan.</li>
                                                            <li><strong>BBM Ditanggung Penyewa:</strong> Pilih opsi ini jika tarif borongan <em>belum termasuk bensin</em>, sehingga bensin ditanggung oleh penyewa.</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="row g-3 mb-4">
                                                    <!-- BBM Disediakan Pengelola Borongan -->
                                                    <div class="col-md-6">
                                                        <div class="p-3 border rounded-3 bg-white h-100 cursor-pointer shadow-sm position-relative transition-all" id="card_bbm_desa_borongan" onclick="selectBbmBorongan('Pemerintah Desa')">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <img src="{{ asset('User/img/iconbaru/bbmdisediakan.png') }}" style="width: 38px; height: 38px; object-fit: contain;">
                                                                    <div>
                                                                        <h6 class="mb-0 fw-bold text-dark">BBM Disediakan Pengelola</h6>
                                                                        <span class="badge bg-label-secondary small" id="status_bbm_desa_borongan">Tidak Aktif</span>
                                                                    </div>
                                                                </div>
                                                                <div class="form-check form-switch fs-5 mb-0">
                                                                    <input class="form-check-input cursor-pointer" type="radio" name="bbm_switch_borongan" id="switch_bbm_desa_borongan" value="Pemerintah Desa" onchange="updateBbmStatusBorongan()">
                                                                </div>
                                                            </div>
                                                            <p class="text-muted small mb-0 mt-2" style="font-size: 0.825rem; line-height: 1.4;">
                                                                <strong>Ketentuan:</strong> Tarif borongan sudah mencakup bahan bakar (BBM terisi dan ditanggung pengelola).
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <!-- BBM Ditanggung Penyewa Borongan -->
                                                    <div class="col-md-6">
                                                        <div class="p-3 border rounded-3 bg-white h-100 cursor-pointer shadow-sm position-relative transition-all" id="card_bbm_penyewa_borongan" onclick="selectBbmBorongan('Penyewa')">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <img src="{{ asset('User/img/iconbaru/bbmditanggungpengguna.png') }}" style="width: 38px; height: 38px; object-fit: contain;">
                                                                    <div>
                                                                        <h6 class="mb-0 fw-bold text-dark">BBM Ditanggung Penyewa</h6>
                                                                        <span class="badge bg-success small" id="status_bbm_penyewa_borongan">Aktif</span>
                                                                    </div>
                                                                </div>
                                                                <div class="form-check form-switch fs-5 mb-0">
                                                                    <input class="form-check-input cursor-pointer" type="radio" name="bbm_switch_borongan" id="switch_bbm_penyewa_borongan" value="Penyewa" onchange="updateBbmStatusBorongan()" checked>
                                                                </div>
                                                            </div>
                                                            <p class="text-muted small mb-0 mt-2" style="font-size: 0.825rem; line-height: 1.4;">
                                                                <strong>Ketentuan:</strong> Biaya bahan bakar selama perjalanan borongan/carter ditanggung mandiri oleh penyewa.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="bbm_ditanggung_borongan" id="bbm_ditanggung_borongan" value="Penyewa" required>
                                            </div> <!-- End form-section supir & bbm --> <!-- End form-section supir & bbm -->
                                        </div> <!-- End borongan_content_wrapper -->

                                        <div class="d-flex justify-content-between mt-4">
                                            <button type="button" class="btn btn-secondary" onclick="prevStep('step2-tab')"><i class='bx bx-left-arrow-alt'></i> Sebelumnya</button>
                                            <button type="button" class="btn btn-primary" onclick="nextStep('step4-tab')">Selanjutnya <i class='bx bx-right-arrow-alt'></i></button>
                                        </div>
                                    </div> <!-- End form-section mb-4 -->
                                </div> <!-- End Step 3: Sewa Borongan -->

                                <!-- STEP 4: PENGATURAN & SIMPAN -->
                                <div class="tab-pane fade" id="step4" role="tabpanel" aria-labelledby="step4-tab">
                                    
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
                                                        <option value="{{ $loc->lokasi }}">
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
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #ffffff;
            width: 100%;
            aspect-ratio: 16/9;
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
            aspect-ratio: 16/9;
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
@endsection

@section('scripts')
<script>
    // Format Rupiah
    function formatRupiah(input) {
        if (!input) return;
        let value = input.value.replace(/\D/g, '');
        if (value) {
            value = new Intl.NumberFormat('id-ID').format(value);
            input.value = value;
        }
    }

    // Preview File Image
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

    // Clear File Image
    function clearFile(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById('placeholder_' + inputId);
        const img = preview ? preview.querySelector('img') : null;

        if (input) input.value = '';
        if (img) img.src = '#';
        if (preview) preview.style.display = 'none';
        if (placeholder) placeholder.style.display = 'block';
    }

    // --- HARIAN: SUPIR CLICK HANDLERS ---
    function toggleSupirTanpaCardHarian(event) {
        if (event && event.target && event.target.id === 'switch_tanpa_supir') return;
        const sw = document.getElementById('switch_tanpa_supir');
        if (sw) {
            sw.checked = !sw.checked;
            updateSupirStatus();
        }
    }

    function toggleSupirDenganCardHarian(event) {
        if (event && event.target && event.target.id === 'switch_dengan_supir') return;
        const sw = document.getElementById('switch_dengan_supir');
        if (sw) {
            sw.checked = !sw.checked;
            updateSupirStatus();
        }
    }

    function updateSupirStatus() {
        const switchTanpaEl = document.getElementById('switch_tanpa_supir');
        const switchDenganEl = document.getElementById('switch_dengan_supir');
        if (!switchTanpaEl || !switchDenganEl) return;

        let switchTanpa = switchTanpaEl.checked;
        let switchDengan = switchDenganEl.checked;

        // Validasi: Minimal 1 opsi supir harus aktif
        if (!switchTanpa && !switchDengan) {
            alert('Minimal harus ada 1 opsi supir (Lepas Kunci atau Dengan Supir) yang diaktifkan.');
            switchTanpaEl.checked = true;
            switchTanpa = true;
        }

        const statusTanpa = document.getElementById('status_tanpa_supir');
        const cardTanpa = document.getElementById('card_supir_tanpa_harian');
        if (statusTanpa) {
            statusTanpa.textContent = switchTanpa ? 'Aktif' : 'Tidak Aktif';
            statusTanpa.className = switchTanpa ? 'badge bg-success small' : 'badge bg-label-secondary small';
        }
        if (cardTanpa) {
            if (switchTanpa) {
                cardTanpa.classList.add('border-primary', 'bg-primary-subtle');
                cardTanpa.classList.remove('border');
            } else {
                cardTanpa.classList.remove('border-primary', 'bg-primary-subtle');
                cardTanpa.classList.add('border');
            }
        }

        const statusDengan = document.getElementById('status_dengan_supir');
        const cardDengan = document.getElementById('card_supir_dengan_harian');
        if (statusDengan) {
            statusDengan.textContent = switchDengan ? 'Aktif' : 'Tidak Aktif';
            statusDengan.className = switchDengan ? 'badge bg-success small' : 'badge bg-label-secondary small';
        }
        if (cardDengan) {
            if (switchDengan) {
                cardDengan.classList.add('border-primary', 'bg-primary-subtle');
                cardDengan.classList.remove('border');
            } else {
                cardDengan.classList.remove('border-primary', 'bg-primary-subtle');
                cardDengan.classList.add('border');
            }
        }

        const input = document.getElementById('opsi_supir');
        if (switchTanpa && switchDengan) {
            if (input) input.value = 'Bebas Pilih';
        } else if (switchTanpa) {
            if (input) input.value = 'Lepas Kunci';
        } else if (switchDengan) {
            if (input) input.value = 'Dengan Supir';
        }
    }

    // --- HARIAN: BBM CLICK HANDLERS ---
    function selectBbmHarian(type) {
        const swDesa = document.getElementById('switch_bbm_desa');
        const swPenyewa = document.getElementById('switch_bbm_penyewa');
        if (type === 'Pengelola' && swDesa) {
            swDesa.checked = true;
        } else if (swPenyewa) {
            swPenyewa.checked = true;
        }
        updateBbmStatus();
    }

    function updateBbmStatus() {
        const switchDesaEl = document.getElementById('switch_bbm_desa');
        const switchPenyewaEl = document.getElementById('switch_bbm_penyewa');
        if (!switchDesaEl || !switchPenyewaEl) return;

        const switchDesa = switchDesaEl.checked;
        const switchPenyewa = switchPenyewaEl.checked;

        const statusDesa = document.getElementById('status_bbm_desa');
        const cardDesa = document.getElementById('card_bbm_desa');
        if (statusDesa) {
            statusDesa.textContent = switchDesa ? 'Aktif' : 'Tidak Aktif';
            statusDesa.className = switchDesa ? 'badge bg-success small' : 'badge bg-label-secondary small';
        }
        if (cardDesa) {
            if (switchDesa) {
                cardDesa.classList.add('border-primary', 'bg-primary-subtle');
                cardDesa.classList.remove('border');
            } else {
                cardDesa.classList.remove('border-primary', 'bg-primary-subtle');
                cardDesa.classList.add('border');
            }
        }

        const statusPenyewa = document.getElementById('status_bbm_penyewa');
        const cardPenyewa = document.getElementById('card_bbm_penyewa');
        if (statusPenyewa) {
            statusPenyewa.textContent = switchPenyewa ? 'Aktif' : 'Tidak Aktif';
            statusPenyewa.className = switchPenyewa ? 'badge bg-success small' : 'badge bg-label-secondary small';
        }
        if (cardPenyewa) {
            if (switchPenyewa) {
                cardPenyewa.classList.add('border-primary', 'bg-primary-subtle');
                cardPenyewa.classList.remove('border');
            } else {
                cardPenyewa.classList.remove('border-primary', 'bg-primary-subtle');
                cardPenyewa.classList.add('border');
            }
        }

        const bbmInput = document.getElementById('bbm_ditanggung');
        if (bbmInput) {
            bbmInput.value = switchDesa ? 'Pengelola' : 'Penyewa';
        }
    }

    // --- BORONGAN: SUPIR CLICK HANDLERS ---
    function toggleSupirTanpaCardBorongan(event) {
        if (event && event.target && event.target.id === 'switch_tanpa_supir_borongan') return;
        const sw = document.getElementById('switch_tanpa_supir_borongan');
        if (sw) {
            sw.checked = !sw.checked;
            updateSupirStatusBorongan();
        }
    }

    function toggleSupirDenganCardBorongan(event) {
        if (event && event.target && event.target.id === 'switch_dengan_supir_borongan') return;
        const sw = document.getElementById('switch_dengan_supir_borongan');
        if (sw) {
            sw.checked = !sw.checked;
            updateSupirStatusBorongan();
        }
    }

    function updateSupirStatusBorongan() {
        const switchTanpaEl = document.getElementById('switch_tanpa_supir_borongan');
        const switchDenganEl = document.getElementById('switch_dengan_supir_borongan');
        if (!switchTanpaEl || !switchDenganEl) return;

        let switchTanpa = switchTanpaEl.checked;
        let switchDengan = switchDenganEl.checked;

        // Validasi: Minimal 1 opsi supir harus aktif
        if (!switchTanpa && !switchDengan) {
            alert('Minimal harus ada 1 opsi supir (Lepas Kunci atau Dengan Supir) yang diaktifkan.');
            switchTanpaEl.checked = true;
            switchTanpa = true;
        }

        const statusTanpa = document.getElementById('status_tanpa_supir_borongan');
        const cardTanpa = document.getElementById('card_supir_tanpa_borongan');
        if (statusTanpa) {
            statusTanpa.textContent = switchTanpa ? 'Aktif' : 'Tidak Aktif';
            statusTanpa.className = switchTanpa ? 'badge bg-success small' : 'badge bg-label-secondary small';
        }
        if (cardTanpa) {
            if (switchTanpa) {
                cardTanpa.classList.add('border-success', 'bg-success-subtle');
                cardTanpa.classList.remove('border');
            } else {
                cardTanpa.classList.remove('border-success', 'bg-success-subtle');
                cardTanpa.classList.add('border');
            }
        }

        const statusDengan = document.getElementById('status_dengan_supir_borongan');
        const cardDengan = document.getElementById('card_supir_dengan_borongan');
        if (statusDengan) {
            statusDengan.textContent = switchDengan ? 'Aktif' : 'Tidak Aktif';
            statusDengan.className = switchDengan ? 'badge bg-success small' : 'badge bg-label-secondary small';
        }
        if (cardDengan) {
            if (switchDengan) {
                cardDengan.classList.add('border-success', 'bg-success-subtle');
                cardDengan.classList.remove('border');
            } else {
                cardDengan.classList.remove('border-success', 'bg-success-subtle');
                cardDengan.classList.add('border');
            }
        }

        const input = document.getElementById('opsi_supir_borongan');
        if (switchTanpa && switchDengan) {
            if (input) input.value = 'Bebas Pilih';
        } else if (switchTanpa) {
            if (input) input.value = 'Lepas Kunci';
        } else if (switchDengan) {
            if (input) input.value = 'Dengan Supir';
        }
    }

    // --- BORONGAN: BBM CLICK HANDLERS ---
    function selectBbmBorongan(type) {
        const swDesa = document.getElementById('switch_bbm_desa_borongan');
        const swPenyewa = document.getElementById('switch_bbm_penyewa_borongan');
        if (type === 'Pemerintah Desa' && swDesa) {
            swDesa.checked = true;
        } else if (swPenyewa) {
            swPenyewa.checked = true;
        }
        updateBbmStatusBorongan();
    }

    function updateBbmStatusBorongan() {
        const switchDesaEl = document.getElementById('switch_bbm_desa_borongan');
        const switchPenyewaEl = document.getElementById('switch_bbm_penyewa_borongan');
        if (!switchDesaEl || !switchPenyewaEl) return;

        const switchDesa = switchDesaEl.checked;
        const switchPenyewa = switchPenyewaEl.checked;

        const statusDesa = document.getElementById('status_bbm_desa_borongan');
        const cardDesa = document.getElementById('card_bbm_desa_borongan');
        if (statusDesa) {
            statusDesa.textContent = switchDesa ? 'Aktif' : 'Tidak Aktif';
            statusDesa.className = switchDesa ? 'badge bg-success small' : 'badge bg-label-secondary small';
        }
        if (cardDesa) {
            if (switchDesa) {
                cardDesa.classList.add('border-success', 'bg-success-subtle');
                cardDesa.classList.remove('border');
            } else {
                cardDesa.classList.remove('border-success', 'bg-success-subtle');
                cardDesa.classList.add('border');
            }
        }

        const statusPenyewa = document.getElementById('status_bbm_penyewa_borongan');
        const cardPenyewa = document.getElementById('card_bbm_penyewa_borongan');
        if (statusPenyewa) {
            statusPenyewa.textContent = switchPenyewa ? 'Aktif' : 'Tidak Aktif';
            statusPenyewa.className = switchPenyewa ? 'badge bg-success small' : 'badge bg-label-secondary small';
        }
        if (cardPenyewa) {
            if (switchPenyewa) {
                cardPenyewa.classList.add('border-success', 'bg-success-subtle');
                cardPenyewa.classList.remove('border');
            } else {
                cardPenyewa.classList.remove('border-success', 'bg-success-subtle');
                cardPenyewa.classList.add('border');
            }
        }

        const bbmInput = document.getElementById('bbm_ditanggung_borongan');
        if (bbmInput) {
            bbmInput.value = switchDesa ? 'Pengelola' : 'Penyewa';
        }
    }

    // Step Navigation
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
        if (tabEl) {
            var tab = new bootstrap.Tab(tabEl);
            tab.show();
            window.scrollTo(0, 0);
        }
    }

    function prevStep(tabId) {
        var tabEl = document.querySelector('#' + tabId);
        if (tabEl) {
            var tab = new bootstrap.Tab(tabEl);
            tab.show();
            window.scrollTo(0, 0);
        }
    }

    // Toggle Lokasi
    function toggleLokasiMode(mode) {
        const savedContainer = document.getElementById('saved_location_container');
        const newContainer = document.getElementById('new_location_container');
        const select = document.getElementById('saved_lokasi');
        const inputLokasi = document.getElementById('lokasi');

        if (mode === 'saved') {
            if (savedContainer) savedContainer.style.display = 'block';
            if (newContainer) newContainer.style.display = 'none';
            if (select) {
                if (inputLokasi) inputLokasi.readOnly = true;
                fillLocationData(select);
            }
        } else {
            if (savedContainer) savedContainer.style.display = 'none';
            if (newContainer) newContainer.style.display = 'block';
            if (inputLokasi) {
                inputLokasi.readOnly = false;
                inputLokasi.value = '';
            }
        }
    }

    function fillLocationData(select) {
        const option = select.options[select.selectedIndex];
        const inputLokasi = document.getElementById('lokasi');
        if (inputLokasi) {
            inputLokasi.value = (option && option.value !== '') ? option.value : '';
        }
    }

    // Toggle Layanan Harian & Borongan
    function toggleLayanan(type) {
        const harianEl = document.getElementById('is_harian_active');
        const boronganEl = document.getElementById('is_borongan_active');
        if (!harianEl || !boronganEl) return;

        const isHarian = harianEl.checked;
        const isBorongan = boronganEl.checked;
        
        if (!isHarian && !isBorongan) {
            alert('Minimal harus ada satu layanan (Harian atau Borongan) yang diaktifkan.');
            if (type === 'harian') {
                harianEl.checked = true;
            } else {
                boronganEl.checked = true;
            }
            return;
        }

        const harianWrapper = document.getElementById('harian_content_wrapper');
        const boronganWrapper = document.getElementById('borongan_content_wrapper');
        if (type === 'harian' && harianWrapper) {
            harianWrapper.style.display = isHarian ? 'block' : 'none';
        } else if (type === 'borongan' && boronganWrapper) {
            boronganWrapper.style.display = isBorongan ? 'block' : 'none';
        }
    }

    // Ongkir Type (Pukul Rata / Per Kecamatan)
    function setOngkirType(type) {
        const divPukulRata = document.getElementById('div_pukul_rata');
        const divPerKecamatan = document.getElementById('div_per_kecamatan');
        const labelPukulRata = document.getElementById('label_pukul_rata');
        const labelPerKecamatan = document.getElementById('label_per_kecamatan');

        if (type === 'pukul_rata') {
            if (divPukulRata) divPukulRata.style.display = 'block';
            if (divPerKecamatan) divPerKecamatan.style.display = 'none';
            if (labelPukulRata) {
                labelPukulRata.classList.replace('border', 'border-primary');
                labelPukulRata.style.backgroundColor = 'var(--bs-primary-bg-subtle)';
            }
            if (labelPerKecamatan) {
                labelPerKecamatan.classList.replace('border-primary', 'border');
                labelPerKecamatan.style.backgroundColor = 'transparent';
            }
            const radio = document.getElementById('tipe_pukul_rata');
            if (radio) radio.checked = true;
        } else {
            if (divPukulRata) divPukulRata.style.display = 'none';
            if (divPerKecamatan) divPerKecamatan.style.display = 'block';
            if (labelPerKecamatan) {
                labelPerKecamatan.classList.replace('border', 'border-primary');
                labelPerKecamatan.style.backgroundColor = 'var(--bs-primary-bg-subtle)';
            }
            if (labelPukulRata) {
                labelPukulRata.classList.replace('border-primary', 'border');
                labelPukulRata.style.backgroundColor = 'transparent';
            }
            const radio = document.getElementById('tipe_per_kecamatan');
            if (radio) radio.checked = true;
        }
    }

    function toggleKecamatan(id) {
        const switchEl = document.getElementById('switch_kec_' + id);
        const inputEl = document.getElementById('input_kec_' + id);
        const labelEl = document.getElementById('label_kec_' + id);
        if (!switchEl || !inputEl || !labelEl) return;

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

    document.addEventListener('DOMContentLoaded', function() {
        updateSupirStatus();
        updateBbmStatus();
        updateSupirStatusBorongan();
        updateBbmStatusBorongan();
        toggleLayanan('harian');
        toggleLayanan('borongan');
        setOngkirType('pukul_rata');

        const saveCatBtn = document.getElementById('saveCategoryBtn');
        if (saveCatBtn) {
            saveCatBtn.addEventListener('click', function() {
                const newKategoriEl = document.getElementById('new_kategori');
                const newKategori = newKategoriEl ? newKategoriEl.value.trim() : '';
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
                            if (select) {
                                const option = document.createElement('option');
                                option.value = data.category.name;
                                option.textContent = data.category.name;
                                select.appendChild(option);
                                select.value = data.category.name;
                            }
                            $('#addCategoryModal').modal('hide');
                            if (newKategoriEl) newKategoriEl.value = '';
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
        }

        const saveSatuanBtn = document.getElementById('saveSatuanBtn');
        if (saveSatuanBtn) {
            saveSatuanBtn.addEventListener('click', function() {
                const newSatuanEl = document.getElementById('new_satuan');
                const newSatuan = newSatuanEl ? newSatuanEl.value.trim() : '';
                if (newSatuan) {
                    const select = document.getElementById('satuan');
                    if (select) {
                        const option = document.createElement('option');
                        option.value = newSatuan;
                        option.textContent = newSatuan;
                        select.appendChild(option);
                        select.value = newSatuan;
                    }
                    $('#addSatuanModal').modal('hide');
                    if (newSatuanEl) newSatuanEl.value = '';
                } else {
                    alert('Silakan masukkan nama satuan.');
                }
            });
        }
    });
</script>
@endsection