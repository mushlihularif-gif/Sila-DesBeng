@extends('admin.layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Breadcrumb & Header Halaman -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">
                    <a href="{{ route('admin.unit.fasilitas_umum.index', ['tab' => 'kendaraan']) }}" class="text-decoration-none text-muted">
                        <i class="bx bx-left-arrow-alt me-1"></i>Kendaraan Operasional
                    </a> /
                </span> Tambah Armada
            </h4>
            <p class="text-muted mb-0 small">Konfigurasikan armada baru, nomor plat polisi, serta penugasan supir darurat siaga.</p>
        </div>
        <div class="mt-2 mt-sm-0">
            <a href="{{ route('admin.unit.fasilitas_umum.index', ['tab' => 'kendaraan']) }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi Validasi Error -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show modern-alert mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-start">
                <i class='bx bx-error-circle me-2 fs-4 text-danger mt-1'></i>
                <div class="flex-grow-1">
                    <strong class="d-block text-danger">Terdapat beberapa data yang belum sesuai:</strong>
                    <ul class="mb-0 mt-1 small ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- ============================================== -->
        <!-- KOLOM KIRI: FORMULIR INPUT UTAMA (7 KOLOM)     -->
        <!-- ============================================== -->
        <div class="col-xl-7 col-lg-7">
            <div class="card modern-card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper me-3">
                            <i class='bx bx-plus-medical text-primary fs-4'></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">Formulir Informasi Kendaraan</h5>
                            <small class="text-muted">Masukkan detail identitas kendaraan beserta foto dan penugasan supir.</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Banner Panduan Kategori Armada -->
                    <div class="alert alert-primary bg-primary-subtle border-0 rounded-3 p-3 mb-4 d-flex align-items-start gap-3">
                        <i class="bx bx-info-circle text-primary fs-3 flex-shrink-0 mt-0.5"></i>
                        <div class="small leading-relaxed text-dark">
                            <strong>Panduan Klasifikasi Armada:</strong>
                            <div class="mt-1">
                                <span class="badge bg-danger me-1">Ambulans Siaga</span> Prioritas kegawatdaruratan medis warga 24/7. Wajib menugaskan supir siaga dengan kontak darurat/WhatsApp aktif.<br>
                                <span class="badge bg-secondary me-1 mt-1">Kendaraan Operasional</span> Armada mobilitas umum desa (Bus dinas warga, mobil patroli, truk sampah). Bebas dari kewajiban penugasan supir darurat.
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.unit.ambulans.store') }}" method="POST" enctype="multipart/form-data" id="ambulansForm">
                        @csrf

                        <!-- SECTION 1: IDENTITAS & KATEGORI -->
                        <div class="form-section mb-4">
                            <h6 class="section-title mb-3">
                                <i class='bx bx-car me-2 text-primary'></i>Identitas Kendaraan
                            </h6>

                            <!-- Nama Kendaraan -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">
                                    Nama Armada / Kendaraan <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="bx bx-car text-muted"></i></span>
                                    <input type="text" 
                                           class="form-control modern-input" 
                                           name="nama_mobil" 
                                           id="inputNamaMobil"
                                           value="{{ old('nama_mobil') }}"
                                           placeholder="Contoh: Ambulans Siaga Desa 01, Bus Warga, Truk Sampah" 
                                           required>
                                </div>
                                <div class="form-text text-muted small">Berikan nama armada yang spesifik dan mudah dikenali warga.</div>
                            </div>

                            <!-- Kategori Kendaraan -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">
                                    Kategori Armada <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="bx bx-category text-muted"></i></span>
                                    <select class="form-select modern-input" 
                                            id="kategori_kendaraan" 
                                            name="kategori" 
                                            required 
                                            onchange="handleKategoriChange()">
                                        <option value="ambulans" {{ old('kategori', 'ambulans') == 'ambulans' ? 'selected' : '' }}>Ambulans (Tersedia Supir Siaga Darurat 24 Jam)</option>
                                        <option value="kendaraan_operasional" {{ old('kategori') == 'kendaraan_operasional' ? 'selected' : '' }}>Kendaraan Operasional Umum (Bus, Truk, Pick Up)</option>
                                    </select>
                                </div>
                                <div class="form-text text-info small" id="kategoriHelpText">
                                    <i class="bx bx-info-circle me-1"></i>Pilihan <strong>Ambulans</strong> mengaktifkan penugasan supir darurat siaga 24 jam.
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: FOTO KENDARAAN (MAKSIMAL 3 FOTO) -->
                        <div class="form-section mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="section-title mb-0">
                                    <i class='bx bx-image me-2 text-primary'></i>Foto Resmi Kendaraan
                                </h6>
                                <span class="badge bg-label-info font-11">Bisa 1 s/d 3 Foto</span>
                            </div>
                            
                            <div class="row g-2 g-sm-3">
                                <!-- Foto Utama -->
                                <div class="col-6 col-md-4">
                                    <label class="form-label fw-semibold text-dark" for="foto">
                                        Foto Utama <span class="text-danger">*</span>
                                    </label>
                                    <div class="upload-box" onclick="document.getElementById('foto').click()">
                                        <div id="preview_foto" class="preview-container" style="display:none;">
                                            <img src="#" alt="Preview Foto Utama" class="preview-image" />
                                            <button type="button" class="btn-remove-image" title="Hapus Foto" onclick="event.stopPropagation(); clearFile('foto', 'preview_foto')">
                                                <span style="font-size: 20px; font-weight: bold; line-height: 1; color: white;">&times;</span>
                                            </button>
                                        </div>
                                        <div id="placeholder_foto" class="upload-placeholder py-2">
                                            <i class='bx bx-cloud-upload text-primary' style="font-size: 38px;"></i>
                                            <p class="mb-0 mt-1 fw-semibold text-dark font-13">Foto Utama</p>
                                            <small class="text-muted d-block font-11">Klik untuk upload</small>
                                        </div>
                                    </div>
                                    <input type="file" 
                                           class="d-none" 
                                           id="foto" 
                                           name="foto" 
                                           accept="image/jpeg,image/png,image/jpg,image/webp" 
                                           onchange="previewFile(this, 'preview_foto', 'placeholder_foto')" />
                                </div>

                                <!-- Foto Tambahan 1 -->
                                <div class="col-6 col-md-4">
                                    <label class="form-label fw-semibold text-dark" for="foto_2">
                                        Foto Tambahan 1 <small class="text-muted fw-normal">(Opsional)</small>
                                    </label>
                                    <div class="upload-box" onclick="document.getElementById('foto_2').click()">
                                        <div id="preview_foto_2" class="preview-container" style="display:none;">
                                            <img src="#" alt="Preview Foto Tambahan 1" class="preview-image" />
                                            <button type="button" class="btn-remove-image" title="Hapus Foto" onclick="event.stopPropagation(); clearFile('foto_2', 'preview_foto_2')">
                                                <span style="font-size: 20px; font-weight: bold; line-height: 1; color: white;">&times;</span>
                                            </button>
                                        </div>
                                        <div id="placeholder_foto_2" class="upload-placeholder py-2">
                                            <i class='bx bx-cloud-upload text-primary' style="font-size: 38px;"></i>
                                            <p class="mb-0 mt-1 fw-semibold text-dark font-13">Foto Tambahan 1</p>
                                            <small class="text-muted d-block font-11">Klik untuk upload</small>
                                        </div>
                                    </div>
                                    <input type="file" 
                                           class="d-none" 
                                           id="foto_2" 
                                           name="foto_2" 
                                           accept="image/jpeg,image/png,image/jpg,image/webp" 
                                           onchange="previewFile(this, 'preview_foto_2', 'placeholder_foto_2')" />
                                </div>

                                <!-- Foto Tambahan 2 -->
                                <div class="col-6 col-md-4">
                                    <label class="form-label fw-semibold text-dark" for="foto_3">
                                        Foto Tambahan 2 <small class="text-muted fw-normal">(Opsional)</small>
                                    </label>
                                    <div class="upload-box" onclick="document.getElementById('foto_3').click()">
                                        <div id="preview_foto_3" class="preview-container" style="display:none;">
                                            <img src="#" alt="Preview Foto Tambahan 2" class="preview-image" />
                                            <button type="button" class="btn-remove-image" title="Hapus Foto" onclick="event.stopPropagation(); clearFile('foto_3', 'preview_foto_3')">
                                                <span style="font-size: 20px; font-weight: bold; line-height: 1; color: white;">&times;</span>
                                            </button>
                                        </div>
                                        <div id="placeholder_foto_3" class="upload-placeholder py-2">
                                            <i class='bx bx-cloud-upload text-primary' style="font-size: 38px;"></i>
                                            <p class="mb-0 mt-1 fw-semibold text-dark font-13">Foto Tambahan 2</p>
                                            <small class="text-muted d-block font-11">Klik untuk upload</small>
                                        </div>
                                    </div>
                                    <input type="file" 
                                           class="d-none" 
                                           id="foto_3" 
                                           name="foto_3" 
                                           accept="image/jpeg,image/png,image/jpg,image/webp" 
                                           onchange="previewFile(this, 'preview_foto_3', 'placeholder_foto_3')" />
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 mt-3 text-muted small">
                                <i class="bx bx-bulb text-warning fs-5"></i>
                                <span><strong>Tips:</strong> Anda dapat mengunggah 1 foto saja (foto utama), 2 foto, atau maksimal 3 foto untuk sudut depan, samping, dan interior armada.</span>
                            </div>
                        </div>

                        <!-- SECTION 3: PLAT NOMOR POLISI -->
                        <div class="form-section mb-4">
                            <h6 class="section-title mb-3">
                                <i class='bx bx-id-card me-2 text-primary'></i>Registrasi Plat Nomor Polisi
                            </h6>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">
                                    Nomor Registrasi Plat Nomor <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="bx bx-barcode text-muted"></i></span>
                                    <input type="text" 
                                           class="form-control modern-input text-uppercase font-monospace fw-bold fs-6" 
                                           name="nomor_plat" 
                                           id="inputPlat" 
                                           value="{{ old('nomor_plat') }}"
                                           placeholder="Contoh: BM 1234 DV" 
                                           required 
                                           maxlength="14"
                                           autocomplete="off">
                                </div>
                                <div class="form-text text-muted small">
                                    Ketik nomor plat polisi dengan spasi standar. Karakter akan otomatis tersinkronisasi ke visualisasi plat di panel samping.
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4: DATA SUPIR DENGAN KARTU PROFIL LENGKAP -->
                        <div class="form-section mb-4" id="supir_section">
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div>
                                    <h6 class="section-title mb-1 text-primary">
                                        <i class='bx bx-user-pin me-2'></i>Petugas Supir Penanggung Jawab
                                    </h6>
                                    <small class="text-muted">Pilih satu atau beberapa supir siaga yang bertanggung jawab atas armada ambulans ini.</small>
                                </div>
                                <div class="mt-2 mt-sm-0 d-flex gap-2 align-items-center">
                                    <span class="badge bg-label-primary px-2.5 py-1.5" id="selectedDriverCount">0 Supir Dipilih</span>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addSupirModal">
                                        <i class="bx bx-plus me-1"></i> Tambah Supir Baru
                                    </button>
                                </div>
                            </div>

                            <!-- Banner Informasi & Panduan Penugasan Supir Siaga -->
                            <div class="card border border-primary-subtle bg-white shadow-xs rounded-3 mb-3 overflow-hidden">
                                <!-- Header Banner: Rapi, Lega, & Seimbang -->
                                <div class="card-header bg-primary-subtle py-3 px-3 border-bottom border-primary-subtle d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 bg-primary text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 me-3" style="width: 36px; height: 36px;">
                                            <i class="bx bx-info-circle fs-5 text-white"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-primary font-14">
                                                Panduan & Fungsi Penugasan Supir Siaga
                                            </h6>
                                            <small class="text-muted font-11">Informasi kesiapsiagaan layanan darurat medis armada desa</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-white text-primary border border-primary-subtle font-11 fw-semibold px-2.5 py-1 rounded-pill d-inline-flex align-items-center gap-1">
                                        <i class="bx bx-check-shield text-primary font-13"></i> Standar Medis
                                    </span>
                                </div>

                                <!-- 3 Poin Panduan dengan Desain Bersih & Elegan -->
                                <div class="p-3 bg-white">
                                    <div class="d-flex flex-column" style="gap: 10px;">
                                        
                                        <!-- Poin 1: Fleksibilitas Penugasan (Biru) -->
                                        <div class="d-flex align-items-start rounded-3" style="padding: 10px 14px; background-color: #f8fafc; border-left: 3.5px solid #0284c7; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5 me-3" 
                                                 style="width: 28px; height: 28px; background-color: #e0f2fe; color: #0284c7;">
                                                <i class="bx bx-user-check" style="font-size: 16px;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                    <span class="fw-bold font-13" style="color: #0369a1;">Bebas Memilih Jumlah Supir</span>
                                                    <span class="badge rounded-pill font-10 px-2 py-0.5" style="background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">1 atau >2 Supir</span>
                                                </div>
                                                <p class="mb-0 font-12" style="color: #334155; line-height: 1.5;">
                                                    Anda dapat memilih <strong>1 supir atau lebih dari 2 supir sekaligus</strong> untuk satu unit armada ambulans yang sama sesuai ketersediaan pengemudi desa.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Poin 2: Siaga 24 Jam & Shift (Kuning-Amber - PENTING) -->
                                        <div class="d-flex align-items-start rounded-3" style="padding: 10px 14px; background-color: #fffdf5; border-left: 3.5px solid #d97706; border-top: 1px solid #fef3c7; border-right: 1px solid #fef3c7; border-bottom: 1px solid #fef3c7;">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5 me-3" 
                                                 style="width: 28px; height: 28px; background-color: #fef3c7; color: #d97706;">
                                                <i class="bx bx-time-five" style="font-size: 16px;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                    <span class="fw-bold font-13" style="color: #b45309;">Kesiapsiagaan Unit Gawat Darurat (Shift 24 Jam)</span>
                                                    <span class="badge rounded-pill font-10 px-2 py-0.5" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a;">Sangat Penting</span>
                                                </div>
                                                <p class="mb-0 font-12" style="color: #451a03; line-height: 1.5;">
                                                    Penugasan beberapa supir berfungsi sebagai sistem <strong>rotasi jaga (shift bergantian)</strong> dan <strong>supir cadangan (backup)</strong>. Saat warga memerlukan rujukan darurat, melahirkan, atau kecelakaan mendadak, selalu ada supir siaga yang siap berangkat tanpa hambatan.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Poin 3: Akses Cepat Kontak Warga (Hijau) -->
                                        <div class="d-flex align-items-start rounded-3" style="padding: 10px 14px; background-color: #f6fef9; border-left: 3.5px solid #16a34a; border-top: 1px solid #dcfce7; border-right: 1px solid #dcfce7; border-bottom: 1px solid #dcfce7;">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5 me-3" 
                                                 style="width: 28px; height: 28px; background-color: #dcfce7; color: #16a34a;">
                                                <i class="bx bxl-whatsapp" style="font-size: 16px;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                    <span class="fw-bold font-13" style="color: #15803d;">Akses Langsung Kontak Darurat</span>
                                                    <span class="badge rounded-pill font-10 px-2 py-0.5" style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">WhatsApp Aktif</span>
                                                </div>
                                                <p class="mb-0 font-12" style="color: #14532d; line-height: 1.5;">
                                                    Nomor WhatsApp dan telepon seluruh supir yang ditugaskan akan otomatis tampil pada menu unit darurat aplikasi agar warga dapat menghubungi secara instan untuk penjemputan cepat.
                                                </p>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Search Filter Supir -->
                            <div class="mb-3">
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text bg-white border-end-0"><i class="bx bx-search text-muted"></i></span>
                                    <input type="text" 
                                           id="searchDriverInput" 
                                           class="form-control border-start-0 ps-0 modern-input" 
                                           placeholder="Cari supir berdasarkan nama atau nomor WhatsApp...">
                                </div>
                            </div>

                            <!-- Driver Cards Grid Container -->
                            <div class="row g-3" id="driverCardsContainer">
                                @forelse($supirs as $supir)
                                    @php
                                        $avatar = $supir->foto ? asset('storage/' . $supir->foto) : asset('Admin/img/avatars/pria.png');
                                        $isTersedia = ($supir->status == 'Tersedia');
                                        $isSelected = is_array(old('supir_ids')) && in_array($supir->id, old('supir_ids'));
                                    @endphp
                                    <div class="col-md-6 driver-card-item" data-name="{{ strtolower($supir->nama) }}" data-phone="{{ $supir->kontak }}">
                                        <div class="card driver-profile-card h-100 border transition-all {{ $isSelected ? 'selected' : '' }}" 
                                             id="card-supir-{{ $supir->id }}"
                                             onclick="toggleDriverSelection({{ $supir->id }})">
                                            
                                            <!-- Hidden Checkbox Input -->
                                            <input type="checkbox" 
                                                   name="supir_ids[]" 
                                                   value="{{ $supir->id }}" 
                                                   id="check-supir-{{ $supir->id }}"
                                                   class="d-none driver-checkbox"
                                                   data-id="{{ $supir->id }}"
                                                   data-name="{{ $supir->nama }}"
                                                   data-avatar="{{ $avatar }}"
                                                   data-kontak="{{ $supir->kontak ?? '-' }}"
                                                   data-status="{{ $supir->status }}"
                                                   {{ $isSelected ? 'checked' : '' }}
                                                   onchange="syncDriverSelection({{ $supir->id }})">

                                            <!-- Card Header: Avatar & Info -->
                                            <div class="d-flex align-items-center gap-3 mb-3">
                                                <!-- Avatar Foto Profil -->
                                                <div class="position-relative flex-shrink-0" style="width: 48px; height: 48px;">
                                                    <img src="{{ $avatar }}" 
                                                         alt="{{ $supir->nama }}" 
                                                         class="rounded-circle border shadow-sm object-fit-cover w-100 h-100" 
                                                         onerror="this.src='{{ asset('Admin/img/avatars/pria.png') }}'">
                                                    <span class="position-absolute {{ $isTersedia ? 'bg-success' : 'bg-warning' }} border border-2 border-white rounded-circle" style="width: 12px; height: 12px; bottom: 1px; right: 1px;"></span>
                                                </div>

                                                <!-- Identitas & Status Supir -->
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                                        <h6 class="mb-0 fw-bold text-dark text-truncate font-14" title="{{ $supir->nama }}">
                                                            {{ $supir->nama }}
                                                        </h6>
                                                        <span class="badge {{ $isTersedia ? 'bg-label-success' : 'bg-label-warning' }} rounded-pill px-2.5 py-1 font-10 fw-semibold">
                                                            {{ $supir->status }}
                                                        </span>
                                                    </div>

                                                    <div class="d-flex align-items-center gap-1.5 text-muted small">
                                                        <i class="bx bxl-whatsapp text-success fs-6"></i>
                                                        <span class="text-truncate font-12 fw-medium">{{ $supir->kontak ?? 'Tanpa Kontak' }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Card Footer: Aksi & Tombol Pilihan Elegan -->
                                            <div class="d-flex align-items-center justify-content-between pt-3 border-top border-light">
                                                <button type="button" 
                                                        class="btn btn-sm btn-light border text-dark rounded-pill px-3 py-1 font-12 fw-semibold d-inline-flex align-items-center gap-1.5 hover-shadow"
                                                        onclick="event.stopPropagation(); showDriverDetailModal({{ $supir->id }})">
                                                    <i class="bx bx-user text-primary fs-6"></i> Detail Profil
                                                </button>
                                                <span class="selection-btn btn btn-sm {{ $isSelected ? 'btn-primary shadow-sm' : 'btn-outline-primary' }} rounded-pill px-3 py-1 font-12 fw-bold d-inline-flex align-items-center gap-1.5">
                                                    @if($isSelected)
                                                        <i class="bx bx-check-circle fs-6"></i> Ditugaskan
                                                    @else
                                                        <i class="bx bx-plus fs-6"></i> Pilih Supir
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-4" id="emptyDriverAlert">
                                        <div class="p-4 bg-light rounded-3 border text-center">
                                            <i class="bx bx-user-x text-muted" style="font-size: 40px;"></i>
                                            <p class="mt-2 mb-1 fw-bold text-dark">Belum Ada Supir Terdaftar</p>
                                            <small class="text-muted d-block mb-3">Tambahkan data supir baru agar armada ambulans dapat ditugaskan dengan baik.</small>
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addSupirModal">
                                                <i class="bx bx-plus me-1"></i> Tambah Supir Sekarang
                                            </button>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- TOMBOL SUBMIT FORM -->
                        <div class="d-flex flex-column-reverse flex-sm-row align-items-stretch align-items-sm-center justify-content-between gap-2 pt-3 border-top mt-4">
                            <a href="{{ route('admin.unit.fasilitas_umum.index', ['tab' => 'kendaraan']) }}" class="btn btn-outline-secondary px-4 text-center">
                                <i class="bx bx-x me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm" id="btnSubmitForm">
                                <i class="bx bx-save me-1"></i> Simpan Armada Kendaraan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- KOLOM KANAN: VISUALISASI PLAT & RINGKASAN      -->
        <!-- ============================================== -->
        <div class="col-xl-5 col-lg-5">
            <div class="sticky-top" style="top: 24px; z-index: 10;">
                
                <!-- KARTU 1: VISUALISASI PLAT NOMOR & MOBIL -->
                <div class="card modern-card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="p-2 bg-primary-subtle text-primary rounded-3 me-2">
                                    <i class="bx bx-show fs-5"></i>
                                </div>
                                <h6 class="mb-0 fw-bold text-dark">Visualisasi Kendaraan</h6>
                            </div>
                            <span class="badge bg-label-primary font-11" id="previewKategoriBadge">Ambulans Siaga</span>
                        </div>
                    </div>

                    <div class="card-body text-center p-4">
                        <!-- Nama Preview -->
                        <h5 class="fw-extrabold text-dark mb-1" id="previewNamaMobil">Nama Kendaraan Baru</h5>
                        <p class="text-muted small mb-4" id="previewSubtext">Layanan Transportasi & Medis Warga</p>

                        <!-- Container Grafis Mobil Plat Asli -->
                        <div class="position-relative d-inline-block mx-auto mb-4" style="max-width: 320px;">
                            <img src="{{ asset('Admin/img/platkendaraan/MOBILPLAT.png') }}" 
                                 class="img-fluid rounded-3 shadow-sm" 
                                 alt="Visualisasi Kendaraan">
                            
                            <!-- Overlay Plat Nomor di Bumper Mobil -->
                            <div class="position-absolute w-100 d-flex justify-content-center align-items-center" style="bottom: 31%; left: 0;">
                                <span id="previewPlatText" 
                                      style="font-family: 'Arial Black', Impact, sans-serif; font-size: 0.65rem; font-weight: 900; color: #111; letter-spacing: 0.5px; line-height: 1;">
                                    BM XXXX XX
                                </span>
                            </div>
                        </div>

                        <!-- Kotak Plat Tercatat (Tampilan Reflektif) -->
                        <div class="p-3 bg-dark rounded-3 text-center border shadow-sm position-relative overflow-hidden">
                            <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10 bg-gradient"></div>
                            <span class="d-block text-secondary text-uppercase font-10 fw-semibold tracking-wider mb-1">
                                Plat Registrasi Tercatat Resmi:
                            </span>
                            <h3 id="previewPlatTextStatic" class="text-white fw-bolder mb-0 font-monospace" style="letter-spacing: 3px;">
                                BM XXXX XX
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- KARTU 2: DAFTAR SUPIR YANG SEDANG DITUGASKAN (LIVE PREVIEW) -->
                <div class="card modern-card shadow-sm border-0 mb-4" id="assignedDriversCard">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="p-2 bg-success-subtle text-success rounded-3">
                                    <i class="bx bx-user-check fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">Petugas Supir Ditugaskan</h6>
                                    <small class="text-muted font-11">Supir siaga aktif armada ini</small>
                                </div>
                            </div>
                            <span class="badge bg-success rounded-pill px-2.5 py-1 font-11 fw-bold" id="assignedCountBadge">0</span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div id="assignedDriversList">
                            <div class="text-center py-4 text-muted small" id="noDriversSelectedMsg">
                                <div class="d-inline-flex p-3 bg-light rounded-circle text-muted mb-2">
                                    <i class="bx bx-user-x fs-2 text-secondary opacity-60"></i>
                                </div>
                                <div class="fw-bold text-dark font-13">Belum Ada Supir Ditugaskan</div>
                                <small class="text-muted d-block mt-0.5">Pilih supir dari daftar di sebelah kiri untuk menugaskannya ke armada ini.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KARTU 3: PANDUAN KESIAPAN OPERASIONAL DARURAT -->
                <div class="card modern-card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-warning-subtle text-warning rounded-3 me-2">
                                <i class="bx bx-shield-quarter fs-5"></i>
                            </div>
                            <h6 class="mb-0 fw-bold text-dark">Standar Kesiapan Operasional</h6>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-unstyled mb-0 small space-y-2.5">
                            <li class="d-flex align-items-start gap-2">
                                <i class="bx bx-check-circle text-success fs-5 flex-shrink-0 mt-0.5"></i>
                                <span class="text-muted">Nomor kontak WhatsApp supir ambulans wajib aktif 24 jam untuk panggilan darurat warga.</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bx bx-check-circle text-success fs-5 flex-shrink-0 mt-0.5"></i>
                                <span class="text-muted">Disarankan menugaskan 1 hingga lebih dari 2 supir sebagai sistem rotasi shift dan supir cadangan darurat.</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bx bx-check-circle text-success fs-5 flex-shrink-0 mt-0.5"></i>
                                <span class="text-muted">Kendaraan wajib siap jalan di pos siaga desa dengan tangki bahan bakar terisi minimal 50%.</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bx bx-check-circle text-success fs-5 flex-shrink-0 mt-0.5"></i>
                                <span class="text-muted">Lakukan inspeksi rutin pada rem, aki kelistrikan, sirine medis, dan perlengkapan P3K.</span>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
@push('modals')
<!-- ============================================== -->
<!-- MODAL 1: TAMBAH SUPIR BARU (AJAX)              -->
<!-- ============================================== -->
<div class="modal fade" id="addSupirModal" tabindex="-1" aria-labelledby="addSupirModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-primary-subtle text-primary rounded-3">
                        <i class="bx bx-user-plus fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-dark fw-bold mb-0" id="addSupirModalLabel">Tambah Petugas Supir Baru</h5>
                        <small class="text-muted">Daftarkan petugas pengemudi resmi desa</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="addSupirForm" action="{{ route('supir.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="is_fasilitas_umum" value="1">
                <input type="hidden" name="is_sewa_mobil" value="1">

                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <div class="position-relative d-inline-block">
                            <img src="{{ asset('Admin/img/avatars/pria.png') }}" 
                                 id="previewAvatarNewSupir" 
                                 class="rounded-circle border shadow-sm object-fit-cover" 
                                 style="width: 80px; height: 80px;" 
                                 alt="Avatar">
                            <button type="button" 
                                    class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 p-1" 
                                    style="width: 28px; height: 28px;"
                                    onclick="document.getElementById('inputFotoSupir').click()">
                                <i class="bx bx-camera font-12"></i>
                            </button>
                        </div>
                        <input type="file" id="inputFotoSupir" name="foto" class="d-none" accept="image/*" onchange="previewNewSupirAvatar(this)">
                        <small class="d-block text-muted mt-1">Upload foto profil supir (opsional)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Nama Lengkap Supir <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-user"></i></span>
                            <input type="text" name="nama" class="form-control" placeholder="Nama lengkap supir" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Nomor WhatsApp / HP Aktif <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bxl-whatsapp text-success"></i></span>
                            <input type="text" name="kontak" class="form-control" placeholder="Contoh: 081234567890" required>
                        </div>
                        <div class="form-text small">Nomor kontak aktif yang dapat dihubungi warga saat panggilan ambulans darurat.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Status Kesiapan Penugasan</label>
                        <select name="status" class="form-select">
                            <option value="Tersedia" selected>Tersedia (Siap Tugas)</option>
                            <option value="Sedang Bertugas">Sedang Bertugas</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-3 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveSupir">
                        <i class="bx bx-save me-1"></i> Simpan & Gunakan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL 2: DETAIL PROFIL SUPIR                   -->
<!-- ============================================== -->
<div class="modal fade" id="detailSupirModal" tabindex="-1" aria-labelledby="detailSupirModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-primary-subtle text-primary rounded-3">
                        <i class="bx bx-user-pin fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="detailSupirModalLabel">Detail Profil Petugas Supir</h5>
                        <small class="text-muted">Informasi data pengemudi resmi desa</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 text-center">
                <div class="mb-3 position-relative d-inline-block">
                    <img src="{{ asset('Admin/img/avatars/pria.png') }}" 
                         id="detailSupirAvatar" 
                         alt="Foto Profil Supir" 
                         class="rounded-circle border border-3 border-primary shadow-sm object-fit-cover" 
                         style="width: 90px; height: 90px;">
                </div>

                <h4 class="fw-bold text-dark mb-1" id="detailSupirNama">Nama Supir</h4>
                <div class="mb-3">
                    <span class="badge bg-success px-3 py-1.5 rounded-pill font-11" id="detailSupirStatus">Tersedia</span>
                </div>

                <div class="bg-light p-3 rounded-3 text-start border mb-3">
                    <div class="row g-2">
                        <div class="col-5 text-muted small fw-semibold">No. WhatsApp / HP:</div>
                        <div class="col-7 text-dark small fw-bold" id="detailSupirKontak">-</div>

                        <div class="col-5 text-muted small fw-semibold">Status Kesiapan:</div>
                        <div class="col-7 small fw-semibold" id="detailSupirStatusText">-</div>

                        <div class="col-5 text-muted small fw-semibold">Tugas Layanan:</div>
                        <div class="col-7 text-dark small">Ambulans Medis Siaga Desa</div>

                        <div class="col-5 text-muted small fw-semibold">Penugasan Unit:</div>
                        <div class="col-7 small" id="detailSupirArmada">-</div>
                    </div>
                </div>

                <div class="d-grid">
                    <a href="#" id="detailSupirWaBtn" target="_blank" class="btn btn-success d-flex align-items-center justify-content-center gap-2 shadow-sm py-2">
                        <i class="bx bxl-whatsapp fs-4"></i> Hubungi Langsung via WhatsApp
                    </a>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 border-top text-end">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('styles')
<style>
    /* Styling Card Modern & Form Section */
    .modern-card {
        border-radius: 16px;
        background: #ffffff;
        transition: all 0.3s ease;
    }
    .icon-wrapper {
        width: 44px;
        height: 44px;
        background: #e0f2fe;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .form-section {
        padding: 22px;
        background: #f8fafc;
        border-radius: 14px;
        border-left: 4px solid #3b82f6;
        border-top: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    .form-section:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        border-left-width: 6px;
    }
    .section-title {
        font-weight: 700;
        font-size: 15px;
        display: flex;
        align-items: center;
    }
    .modern-input {
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 14px;
        transition: all 0.25s ease;
    }
    .modern-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    /* Upload Box 16:9 persis seperti di mobil */
    .upload-box {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 0.75rem;
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
        border-color: #3b82f6;
        background: #eff6ff;
    }
    .upload-box:hover .upload-placeholder i {
        color: #2563eb;
        transform: translateY(-2px) scale(1.05);
    }
    .upload-placeholder i {
        transition: all 0.3s ease;
    }
    .preview-container {
        width: 100%;
        height: 100%;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .preview-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .btn-remove-image {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #dc3545;
        border: 2px solid white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.35);
        transition: all 0.2s ease;
        z-index: 5;
    }
    .btn-remove-image:hover {
        background: #bb2d3b;
        transform: scale(1.1);
    }

    /* Modern Driver Profile Card Styling dengan Padding yang Pas & Lega */
    .driver-profile-card {
        cursor: pointer;
        background: #ffffff;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 16px !important;
        padding: 16px 18px !important;
        transition: all 0.2s ease-in-out;
    }
    .driver-profile-card:hover {
        border-color: #93c5fd !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(59, 130, 246, 0.08);
    }
    .driver-profile-card.selected {
        border-color: #2563eb !important;
        background-color: #f4f8ff !important;
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.12);
    }

    .hover-shadow:hover {
        background-color: #f1f5f9 !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    }

    /* High-End Assigned Driver Card Styling (Panel Kanan) */
    .assigned-driver-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 10px;
        transition: all 0.2s ease-in-out;
    }
    .assigned-driver-card:last-child {
        margin-bottom: 0;
    }
    .assigned-driver-card:hover {
        background: #ffffff;
        border-color: #cbd5e1;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
    }

    /* Modern Soft Red Delete Button */
    .btn-remove-driver {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid #fee2e2;
        background: #fef2f2;
        color: #ef4444;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }
    .btn-remove-driver:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
        transform: scale(1.06);
        box-shadow: 0 3px 10px rgba(220, 38, 38, 0.25);
    }

    /* Modal Close Button Fix - Sneat override */
    .modal-header .btn-close {
        margin: 0 !important;
        transform: none !important;
        box-shadow: none !important;
        opacity: 0.65;
    }
    .modal-header .btn-close:hover {
        opacity: 1;
        transform: none !important;
    }

    /* Utility */
    .font-10 { font-size: 10px; }
    .font-11 { font-size: 11px; }
    .font-12 { font-size: 12px; }
    .font-13 { font-size: 13px; }
    .font-14 { font-size: 14px; }
</style>
@endpush

@push('scripts')
<script>
    'use strict';

    // Global in-memory cache of driver records
    const supirsMaster = @json($supirs->keyBy('id'));

    // 1. Sinkronisasi Live Input Nama & Plat Nomor ke Visualisasi Kanan
    const inputNama = document.getElementById('inputNamaMobil');
    const previewNama = document.getElementById('previewNamaMobil');
    
    const inputPlat = document.getElementById('inputPlat');
    const previewPlatText = document.getElementById('previewPlatText');
    const previewPlatTextStatic = document.getElementById('previewPlatTextStatic');

    if (inputNama && previewNama) {
        inputNama.addEventListener('input', function() {
            previewNama.textContent = this.value.trim() ? this.value : 'Nama Kendaraan Baru';
        });
    }

    if (inputPlat) {
        inputPlat.addEventListener('input', function() {
            let val = this.value.toUpperCase();
            this.value = val;

            if (val.trim() === '') {
                if (previewPlatText) previewPlatText.textContent = 'BM XXXX XX';
                if (previewPlatTextStatic) previewPlatTextStatic.textContent = 'BM XXXX XX';
            } else {
                if (previewPlatText) previewPlatText.textContent = val;
                if (previewPlatTextStatic) previewPlatTextStatic.textContent = val;
            }
        });
    }

    // 2. Kategori Change Handler: Tampilkan/Sembunyikan Supir Secara Dinamis
    function handleKategoriChange(isInitial = false) {
        const select = document.getElementById('kategori_kendaraan');
        const supirSection = document.getElementById('supir_section');
        const assignedCard = document.getElementById('assignedDriversCard');
        const previewBadge = document.getElementById('previewKategoriBadge');
        const previewSubtext = document.getElementById('previewSubtext');
        const helpText = document.getElementById('kategoriHelpText');

        if (!select) return;
        const val = select.value;

        if (val === 'ambulans') {
            // Tampilkan bagian supir jika Ambulans
            if (isInitial) {
                if (supirSection) supirSection.style.display = 'block';
                if (assignedCard) assignedCard.style.display = 'block';
            } else {
                if (supirSection) $(supirSection).slideDown(250);
                if (assignedCard) $(assignedCard).slideDown(250);
            }

            if (previewBadge) {
                previewBadge.textContent = 'Ambulans Siaga';
                previewBadge.className = 'badge bg-label-danger font-11';
            }
            if (previewSubtext) previewSubtext.textContent = 'Layanan Tanggap Darurat Medis Warga 24 Jam';
            if (helpText) {
                helpText.innerHTML = '<i class="bx bx-info-circle me-1"></i>Pilihan <strong>Ambulans</strong> mengaktifkan penugasan supir darurat siaga 24 jam.';
            }

            updateAssignedDriversList();
        } else {
            // Sembunyikan bagian supir jika Kendaraan Operasional Umum
            if (isInitial) {
                if (supirSection) supirSection.style.display = 'none';
                if (assignedCard) assignedCard.style.display = 'none';
            } else {
                if (supirSection) $(supirSection).slideUp(250);
                if (assignedCard) $(assignedCard).slideUp(250);
            }

            if (previewBadge) {
                previewBadge.textContent = 'Kendaraan Operasional';
                previewBadge.className = 'badge bg-label-secondary font-11';
            }
            if (previewSubtext) previewSubtext.textContent = 'Armada Logistik & Mobilitas Kegiatan Desa';
            if (helpText) {
                helpText.innerHTML = '<i class="bx bx-info-circle me-1"></i>Armada <strong>Operasional Umum</strong> digunakan untuk dinas, pengangkutan umum, atau truk sampah tanpa penugasan supir darurat.';
            }

            // Jika admin sengaja mengubah ke operasional umum, reset centang supir
            if (!isInitial) {
                document.querySelectorAll('.driver-checkbox').forEach(cb => {
                    cb.checked = false;
                    syncDriverSelection(cb.dataset.id);
                });
            }
        }
    }

    // 3. Selection & Sync Driver Cards dengan Tombol Interaktif Modern
    function toggleDriverSelection(driverId) {
        const checkbox = document.getElementById('check-supir-' + driverId);
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            syncDriverSelection(driverId);
        }
    }

    function syncDriverSelection(driverId) {
        const card = document.getElementById('card-supir-' + driverId);
        const checkbox = document.getElementById('check-supir-' + driverId);
        if (!card || !checkbox) return;

        const selBtn = card.querySelector('.selection-btn');

        if (checkbox.checked) {
            card.classList.add('selected');
            if (selBtn) {
                selBtn.className = 'selection-btn btn btn-sm btn-primary shadow-sm rounded-pill px-3 py-1 font-12 fw-bold d-inline-flex align-items-center gap-1.5';
                selBtn.innerHTML = '<i class="bx bx-check-circle fs-6"></i> Ditugaskan';
            }
        } else {
            card.classList.remove('selected');
            if (selBtn) {
                selBtn.className = 'selection-btn btn btn-sm btn-outline-primary rounded-pill px-3 py-1 font-12 fw-bold d-inline-flex align-items-center gap-1.5';
                selBtn.innerHTML = '<i class="bx bx-plus fs-6"></i> Pilih Supir';
            }
        }

        updateAssignedDriversList();
    }

    // Hapus supir langsung dari daftar ringkasan sebelah kanan
    function removeAssignedDriver(driverId) {
        const checkbox = document.getElementById('check-supir-' + driverId);
        if (checkbox) {
            checkbox.checked = false;
            syncDriverSelection(driverId);
        }
    }

    function updateAssignedDriversList() {
        const checkboxes = document.querySelectorAll('.driver-checkbox:checked');
        const countBadge = document.getElementById('selectedDriverCount');
        const assignedBadge = document.getElementById('assignedCountBadge');
        const listContainer = document.getElementById('assignedDriversList');

        const total = checkboxes.length;
        if (countBadge) countBadge.textContent = total + ' Supir Dipilih';
        if (assignedBadge) assignedBadge.textContent = total;

        if (!listContainer) return;

        if (total === 0) {
            listContainer.innerHTML = `
                <div class="text-center py-4 text-muted small" id="noDriversSelectedMsg">
                    <div class="d-inline-flex p-3 bg-light rounded-circle text-muted mb-2">
                        <i class="bx bx-user-x fs-2 text-secondary opacity-60"></i>
                    </div>
                    <div class="fw-bold text-dark font-13">Belum Ada Supir Ditugaskan</div>
                    <small class="text-muted d-block mt-0.5">Pilih supir dari daftar di sebelah kiri untuk menugaskannya ke armada ini.</small>
                </div>
            `;
        } else {
            let html = '';
            checkboxes.forEach(cb => {
                const id = cb.dataset.id;
                const name = cb.dataset.name || 'Supir';
                const avatar = cb.dataset.avatar || '';
                const kontak = cb.dataset.kontak || '-';
                const status = cb.dataset.status || 'Tersedia';
                const isTersedia = (status === 'Tersedia');

                html += `
                    <div class="assigned-driver-card d-flex align-items-center justify-content-between" id="assigned-item-${id}">
                        <div class="d-flex align-items-center gap-3 min-w-0">
                            <!-- Avatar Foto dengan Indikator Status Inset Rapi -->
                            <div class="position-relative flex-shrink-0" style="width: 44px; height: 44px;">
                                <img src="${avatar}" class="rounded-circle border shadow-xs object-fit-cover w-100 h-100" alt="${name}">
                                <span class="position-absolute ${isTersedia ? 'bg-success' : 'bg-warning'} border border-2 border-white rounded-circle" style="width: 12px; height: 12px; bottom: 1px; right: 1px;"></span>
                            </div>

                            <!-- Detail Identitas & Status -->
                            <div class="min-w-0">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h6 class="mb-0 fw-bold text-dark font-13 text-truncate" title="${name}">${name}</h6>
                                    <span class="badge ${isTersedia ? 'bg-label-success' : 'bg-label-warning'} rounded-pill px-2 py-0.5 font-10 fw-semibold">${status}</span>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 text-muted font-11">
                                    <i class="bx bxl-whatsapp text-success fs-6"></i>
                                    <span class="fw-medium">${kontak}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Icon Hapus Soft Modern -->
                        <div class="flex-shrink-0 ms-2">
                            <button type="button" 
                                    class="btn-remove-driver" 
                                    title="Lepas Penugasan Supir"
                                    onclick="removeAssignedDriver(${id})">
                                <i class="bx bx-trash font-14"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            listContainer.innerHTML = html;
        }
    }

    // 4. Live Search Filter Supir
    const searchInput = document.getElementById('searchDriverInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.driver-card-item');

            items.forEach(item => {
                const name = item.dataset.name || '';
                const phone = item.dataset.phone || '';
                if (name.includes(query) || phone.includes(query)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // 5. View Detail Supir Modal (Informasi Realistis & Akurat)
    function showDriverDetailModal(supirId) {
        const supir = supirsMaster[supirId];
        if (!supir) return;

        const avatarUrl = supir.foto ? `/storage/${supir.foto}` : `{{ asset('Admin/img/avatars/pria.png') }}`;

        document.getElementById('detailSupirAvatar').src = avatarUrl;
        document.getElementById('detailSupirNama').textContent = supir.nama || '-';
        document.getElementById('detailSupirKontak').textContent = supir.kontak || 'Belum diisi';
        
        const statusEl = document.getElementById('detailSupirStatus');
        const statusTextEl = document.getElementById('detailSupirStatusText');
        const isTersedia = (supir.status === 'Tersedia');

        if (statusEl) {
            statusEl.textContent = supir.status || 'Tersedia';
            statusEl.className = isTersedia 
                ? 'badge bg-success px-3 py-1.5 rounded-pill font-11' 
                : 'badge bg-warning px-3 py-1.5 rounded-pill font-11';
        }

        if (statusTextEl) {
            statusTextEl.innerHTML = isTersedia 
                ? '<span class="text-success fw-bold"><i class="bx bx-check-circle me-1"></i>Tersedia (Siap Ditugaskan)</span>' 
                : '<span class="text-warning fw-bold"><i class="bx bx-time-five me-1"></i>' + supir.status + '</span>';
        }

        // Tampilkan penugasan armada saat ini secara akurat (tanpa teks fiktif "Armada Baru")
        const armadaEl = document.getElementById('detailSupirArmada');
        if (armadaEl) {
            if (supir.ambulans && supir.ambulans.length > 0) {
                const names = supir.ambulans.map(m => m.nama_mobil).join(', ');
                armadaEl.innerHTML = `<span class="badge bg-label-info font-11">${names}</span>`;
            } else {
                armadaEl.innerHTML = `<span class="badge bg-label-success font-11"><i class="bx bx-check me-1"></i>Bebas Tugas (Siap Ditugaskan)</span>`;
            }
        }

        const waBtn = document.getElementById('detailSupirWaBtn');
        if (waBtn) {
            let phone = (supir.kontak || '').replace(/[^0-9]/g, '');
            if (phone.startsWith('0')) phone = '62' + phone.substring(1);
            waBtn.href = phone ? `https://wa.me/${phone}` : 'javascript:void(0);';
        }

        const modalEl = document.getElementById('detailSupirModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    // 6. Preview & Cropper File Upload
    function previewFile(input, previewId, placeholderId) {
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);
        const img = preview ? preview.querySelector('img') : null;

        if (input.files && input.files[0]) {
            if (typeof initGlobalCropper === 'function') {
                initGlobalCropper(input, img || previewId, 16 / 9, true);
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

    // 7. Preview Avatar Modal Tambah Supir Baru
    function previewNewSupirAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewAvatarNewSupir').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // 8. Submit AJAX Tambah Supir Baru
    const addSupirForm = document.getElementById('addSupirForm');
    if (addSupirForm) {
        addSupirForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const btn = document.getElementById('btnSaveSupir');
            const originalText = btn.innerHTML;

            btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Menyimpan...';
            btn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success || data.supir) {
                    const newSupir = data.supir || data.data;
                    supirsMaster[newSupir.id] = newSupir;

                    const avatarUrl = newSupir.foto ? `/storage/${newSupir.foto}` : `{{ asset('Admin/img/avatars/pria.png') }}`;
                    const isTersedia = (newSupir.status === 'Tersedia');

                    // Sembunyikan empty state jika ada
                    const emptyAlert = document.getElementById('emptyDriverAlert');
                    if (emptyAlert) emptyAlert.style.display = 'none';

                    // Buat Card Baru dengan format rapi dan tombol modern
                    const cardHtml = `
                        <div class="col-md-6 driver-card-item" data-name="${newSupir.nama.toLowerCase()}" data-phone="${newSupir.kontak || ''}">
                            <div class="card driver-profile-card h-100 border transition-all selected" 
                                 id="card-supir-${newSupir.id}"
                                 onclick="toggleDriverSelection(${newSupir.id})">
                                
                                <input type="checkbox" 
                                       name="supir_ids[]" 
                                       value="${newSupir.id}" 
                                       id="check-supir-${newSupir.id}" 
                                       class="d-none driver-checkbox"
                                       data-id="${newSupir.id}"
                                       data-name="${newSupir.nama}"
                                       data-avatar="${avatarUrl}"
                                       data-kontak="${newSupir.kontak || '-'}"
                                       data-status="${newSupir.status}"
                                       checked
                                       onchange="syncDriverSelection(${newSupir.id})">

                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="position-relative flex-shrink-0" style="width: 48px; height: 48px;">
                                        <img src="${avatarUrl}" 
                                             alt="${newSupir.nama}" 
                                             class="rounded-circle border shadow-sm object-fit-cover w-100 h-100" 
                                             onerror="this.src='{{ asset('Admin/img/avatars/pria.png') }}'">
                                        <span class="position-absolute ${isTersedia ? 'bg-success' : 'bg-warning'} border border-2 border-white rounded-circle" style="width: 12px; height: 12px; bottom: 1px; right: 1px;"></span>
                                    </div>

                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                            <h6 class="mb-0 fw-bold text-dark text-truncate font-14" title="${newSupir.nama}">
                                                ${newSupir.nama}
                                            </h6>
                                            <span class="badge ${isTersedia ? 'bg-label-success' : 'bg-label-warning'} rounded-pill px-2.5 py-1 font-10 fw-semibold">
                                                ${newSupir.status}
                                            </span>
                                        </div>

                                        <div class="d-flex align-items-center gap-1.5 text-muted small">
                                            <i class="bx bxl-whatsapp text-success fs-6"></i>
                                            <span class="text-truncate font-12 fw-medium">${newSupir.kontak || 'Tanpa Kontak'}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between pt-3 border-top border-light">
                                    <button type="button" 
                                            class="btn btn-sm btn-light border text-dark rounded-pill px-3 py-1 font-12 fw-semibold d-inline-flex align-items-center gap-1.5 hover-shadow"
                                            onclick="event.stopPropagation(); showDriverDetailModal(${newSupir.id})">
                                        <i class="bx bx-user text-primary fs-6"></i> Detail Profil
                                    </button>
                                    <span class="selection-btn btn btn-sm btn-primary shadow-sm rounded-pill px-3 py-1 font-12 fw-bold d-inline-flex align-items-center gap-1.5">
                                        <i class="bx bx-check-circle fs-6"></i> Ditugaskan
                                    </span>
                                </div>
                            </div>
                        </div>
                    `;

                    // Tambahkan ke container
                    document.getElementById('driverCardsContainer').insertAdjacentHTML('beforeend', cardHtml);

                    // Update ringkasan sebelah kanan
                    updateAssignedDriversList();

                    // Tutup modal & reset form
                    bootstrap.Modal.getInstance(document.getElementById('addSupirModal')).hide();
                    form.reset();
                    document.getElementById('previewAvatarNewSupir').src = "{{ asset('Admin/img/avatars/pria.png') }}";

                    // Tampilkan notifikasi
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Supir Berhasil Ditambahkan',
                            text: `${newSupir.nama} berhasil ditambahkan dan otomatis ditugaskan ke armada ini!`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } else {
                    alert('Gagal menyimpan data supir baru.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan koneksi server saat menyimpan supir.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    }

    // Init on Page Ready
    document.addEventListener('DOMContentLoaded', function() {
        handleKategoriChange(true);
        updateAssignedDriversList();
    });
</script>
@endpush