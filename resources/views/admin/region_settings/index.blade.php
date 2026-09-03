@extends('admin.layouts.admin')

@section('title', 'Pengaturan Admin Wilayah')

@section('page-title', 'Pengaturan Admin Wilayah')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Pengaturan Admin Wilayah: {{ $region->name }}</h4>

    <!-- Header / Panduan -->
    <div class="card bg-label-primary border-0 shadow-none mb-4" style="border-radius: 12px;">
        <div class="card-body d-flex align-items-center p-4">
            <div class="me-3">
                <div class="bg-primary p-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                    <i class="bx bx-map-alt fs-3"></i>
                </div>
            </div>
            <div>
                <h5 class="fw-bold mb-1 text-primary">Detail & Layanan Wilayah</h5>
                <p class="mb-0 text-primary" style="opacity: 0.85;">
                    Kelola informasi kontak, profil, dan tentukan modul layanan apa saja yang diaktifkan untuk wilayah Anda.
                </p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center" role="alert">
            <i class="bx bx-check-circle fs-4 me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center" role="alert">
            <i class="bx bx-error-circle fs-4 me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="alert alert-info d-flex align-items-start mb-4 shadow-sm rounded-4 border-0 p-4" role="alert">
        <span class="alert-icon text-info me-3 mt-1">
            <i class="bx bx-info-circle fs-3"></i>
        </span>
        <div>
            <h6 class="alert-heading fw-bold mb-1 text-info">Profil Wilayah Administratif Anda</h6>
            <ul class="mb-0 ps-3">
                <li><strong>Desa/Kelurahan:</strong> {{ $region->type == 'desa' ? $region->name : '-' }}</li>
                <li><strong>Kecamatan:</strong> {{ $region->parent ? $region->parent->name : '-' }}</li>
                <li><strong>Kabupaten:</strong> {{ $region->parent && $region->parent->parent ? $region->parent->parent->name : '-' }}</li>
            </ul>
        </div>
    </div>

    <form action="{{ route('admin.region-settings.update') }}" method="POST" id="region-settings-form">
        @csrf
        
        <div class="nav-align-top mb-4">
            <ul class="nav nav-pills mb-4 gap-2" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link active rounded-pill shadow-sm px-4" role="tab" data-bs-toggle="tab" data-bs-target="#navs-kontak" aria-controls="navs-kontak" aria-selected="true">
                        <i class="bx bx-phone-call me-2"></i> Layanan Wilayah
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link rounded-pill shadow-sm px-4" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pengiriman" aria-controls="navs-pengiriman" aria-selected="false">
                        <i class="bx bx-truck me-2"></i> Pengaturan Pengiriman
                    </button>
                </li>
            </ul>

            <div class="tab-content p-0 shadow-none bg-transparent">
                <!-- TAB 1: Kontak & Layanan -->
                <div class="tab-pane fade show active" id="navs-kontak" role="tabpanel">
                    
                    <!-- Informasi Kontak Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3 border-bottom pb-3">
                                <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-circle me-3 d-flex justify-content-center align-items-center">
                                    <i class="bx bx-phone-call fs-5"></i>
                                </div>
                                <h6 class="fw-bold mb-0">Informasi Kontak Wilayah</h6>
                            </div>
                            
                            <div class="card bg-label-success border-0 shadow-none rounded-3 mt-4">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-success mb-0 d-flex align-items-center">
                                            <i class="bx bxl-whatsapp me-2 fs-4"></i> WhatsApp Layanan
                                        </h6>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" style="cursor:pointer;" type="checkbox" name="whatsapp_active" id="whatsapp_active" onchange="document.getElementById('wa_fields').style.display = this.checked ? 'block' : 'none'" {{ !empty($region->payment_info['whatsapp_active']) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <p class="text-success small mb-3 opacity-75">Kontak WA ini akan dihubungkan ke tombol chat otomatis di aplikasi untuk melayani warga.</p>
                                    
                                    <div id="wa_fields" style="{{ empty($region->payment_info['whatsapp_active']) ? 'display: none;' : '' }}">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold text-success small">Nama Kontak WA</label>
                                                <input type="text" name="whatsapp_name" value="{{ old('whatsapp_name', $region->payment_info['whatsapp_name'] ?? '') }}" class="form-control border-success text-success bg-white" placeholder="Cth: Admin Desa">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold text-success small">Nomor WhatsApp</label>
                                                <input type="text" name="contact_phone" value="{{ old('contact_phone', $region->contact_phone) }}" class="form-control border-success text-success bg-white" placeholder="Cth: 081234567890">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Opt-in Layanan Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-sm bg-warning-subtle text-warning rounded-circle me-3 d-flex justify-content-center align-items-center">
                                    <i class="bx bx-layer fs-5"></i>
                                </div>
                                <h6 class="fw-bold mb-0">2. HAK AKSES UNIT LAYANAN</h6>
                            </div>
                            
                            <!-- Panduan UI -->
                            <div class="alert bg-label-warning border-0 rounded-3 mb-4 d-flex align-items-start p-3">
                                <i class="bx bx-error-circle fs-4 me-3 text-warning mt-1"></i>
                                <div>
                                    <strong class="d-block mb-1 text-dark">PENTING: Panduan Pengaturan Layanan!</strong>
                                    <p class="mb-0 text-dark" style="font-size: 0.85rem;">
                                        Silakan aktifkan unit layanan yang ingin dibuka untuk warga. 
                                        Anda juga dapat mengatur <strong>Hak Akses Eksklusif</strong> agar layanan tersebut hanya bisa dinikmati oleh warga lokal desa Anda. Layanan publik seperti <em>Pasar Daerah</em> dan <em>Berita Daerah</em> secara otomatis terbuka untuk umum dan tidak dapat dieksklusifkan.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                            @foreach($allServices as $service)
                                <div class="col-md-6">
                                    <div class="card border {{ in_array($service->id, $activeServices) ? 'border-primary shadow-sm bg-label-primary' : 'border-secondary shadow-none bg-light' }} h-100 rounded-3 card-service-item" style="transition: all 0.2s;">
                                        <div class="card-body p-3">
                                            
                                            <!-- Main Service Toggle -->
                                            @php
                                                $iconPath = 'User/img/elemen/fasilitas.png';
                                                $descText = 'yang dapat mengakses layanan ini.';
                                                $sName = strtolower($service->name);
                                                $isPasar = strpos($sName, 'pasar') !== false;
                                                $isPengumuman = strpos($sName, 'pengumuman') !== false;
                                                $displayName = $service->name;
                                                
                                                if (strpos($sName, 'mobil') !== false) {
                                                    $iconPath = 'User/img/elemen/mobil.png';
                                                    $descText = 'yang dapat melihat dan menyewa armada ini.';
                                                }
                                                elseif (strpos($sName, 'alat') !== false) {
                                                    $iconPath = 'User/img/elemen/F1.png';
                                                    $descText = 'yang dapat meminjam atau menyewa alat.';
                                                }
                                                elseif (strpos($sName, 'gas') !== false) {
                                                    $iconPath = 'User/img/elemen/F2.png';
                                                    $descText = 'yang dapat memesan tabung gas.';
                                                }
                                                elseif ($isPasar) {
                                                    $iconPath = 'Admin/img/pasardaerah/PasarDaerah2.png';
                                                    $descText = 'yang dapat mendaftar sewa kios.';
                                                }
                                                elseif (strpos($sName, 'lapor') !== false) {
                                                    $iconPath = 'User/img/elemen/lapor.png';
                                                    $descText = 'yang dapat membuat laporan.';
                                                }
                                                elseif ($isPengumuman) {
                                                    $iconPath = 'User/img/elemen/KabardanInformasiDaerah.png';
                                                    $descText = 'yang dapat melihat pengumuman daerah ini.';
                                                    $displayName = "Kabar dan Informasi Daerah";
                                                }
                                            @endphp

                                            <div class="d-flex align-items-center mb-2">
                                                <div class="bg-white rounded p-2 me-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                    <img src="{{ asset($iconPath) }}" alt="{{ $displayName }}" class="w-100 h-100 object-contain" style="object-fit: contain;">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <span class="fw-bold d-block text-dark" style="font-size: 0.95rem;">{{ $displayName }}</span>
                                                    <div class="form-check form-switch mb-0 mt-1">
                                                        <input type="checkbox" name="services[]" value="{{ $service->id }}" class="form-check-input service-main-toggle" style="cursor: pointer; transform: scale(1.2);" {{ in_array($service->id, $activeServices) ? 'checked' : '' }}>
                                                        <label class="form-check-label small fw-bold mt-1 ms-1 status-label-main {{ in_array($service->id, $activeServices) ? 'text-primary' : 'text-secondary' }}">{{ in_array($service->id, $activeServices) ? 'Layanan Aktif' : 'Layanan Nonaktif' }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3 ps-5 ms-3">
                                                <small class="status-desc-text {{ in_array($service->id, $activeServices) ? 'text-success' : 'text-danger' }}" style="font-size: 0.75rem;">
                                                    {{ in_array($service->id, $activeServices) ? 'Layanan ini muncul di beranda warga.' : 'Layanan ini disembunyikan sepenuhnya.' }}
                                                </small>
                                            </div>

                                            <div class="border-top pt-2 mt-2">
                                                <label class="form-label text-dark fw-bold small mb-2 d-flex align-items-center">
                                                    <i class="bx bx-shield-quarter text-warning me-1"></i> Hak Akses Eksklusif
                                                </label>
                                                
                                                @if($isPengumuman)
                                                    <div class="bg-white border rounded-3 p-3 mt-2">
                                                        <!-- Berita Block (Locked) -->
                                                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                                                            <div class="bg-info-subtle text-info rounded p-2 me-3 d-flex justify-content-center align-items-center">
                                                                <i class="bx bx-news fs-4"></i>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <span class="fw-bold d-block text-dark" style="font-size: 0.85rem;">Berita Daerah</span>
                                                                <small class="text-primary fw-semibold" style="font-size: 0.75rem;"><i class="bx bx-world me-1"></i>Publik (Terbuka Umum)</small>
                                                                <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Berita otomatis dapat dilihat oleh seluruh warga desa lain.</small>
                                                            </div>
                                                            <i class="bx bxs-lock-alt text-secondary mt-1" title="Tidak dapat diubah"></i>
                                                        </div>
                                                        
                                                        <!-- Pengumuman Block (Toggleable) -->
                                                        <div class="d-flex align-items-start">
                                                            <div class="bg-warning-subtle text-warning rounded p-2 me-3 d-flex justify-content-center align-items-center">
                                                                <i class="bx bx-broadcast fs-4"></i>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                                    <span class="fw-bold d-block text-dark" style="font-size: 0.85rem;">Pengumuman Desa</span>
                                                                    <div class="form-check form-switch mb-0">
                                                                        <input type="checkbox" name="exclusive_services[]" value="{{ $service->id }}" class="form-check-input exclusive-toggle" style="cursor: pointer; border-color: #ffab00;" {{ in_array($service->id, $exclusiveServices) ? 'checked' : '' }}>
                                                                    </div>
                                                                </div>
                                                                <label class="form-check-label small fw-bold text-dark exclusive-label-main d-block mb-1" style="font-size: 0.75rem;">
                                                                    {{ in_array($service->id, $exclusiveServices) ? 'Eksklusif Warga Lokal' : 'Publik (Terbuka Umum)' }}
                                                                </label>
                                                                <small class="exclusive-desc-text {{ in_array($service->id, $exclusiveServices) ? 'text-warning' : 'text-primary' }} d-block lh-sm" style="font-size: 0.7rem;">
                                                                    {{ in_array($service->id, $exclusiveServices) ? 'Pengumuman HANYA tampil untuk warga domisili '.$region->name.'.' : 'Pengumuman dapat dilihat oleh semua warga termasuk dari desa lain.' }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="form-check form-switch mb-1">
                                                        <input type="checkbox" name="exclusive_services[]" value="{{ $service->id }}" class="form-check-input exclusive-toggle" style="cursor: pointer; border-color: #ffab00;" {{ in_array($service->id, $exclusiveServices) ? 'checked' : '' }}>
                                                        <label class="form-check-label small fw-bold text-dark exclusive-label-main">{{ in_array($service->id, $exclusiveServices) ? 'Eksklusif Warga Lokal' : 'Publik (Terbuka Umum)' }}</label>
                                                    </div>
                                                    <small class="exclusive-desc-text {{ in_array($service->id, $exclusiveServices) ? 'text-warning' : 'text-primary' }} d-block" style="font-size: 0.75rem;">
                                                        {{ in_array($service->id, $exclusiveServices) ? 'Hanya warga domisili '.$region->name.' '.$descText : 'Semua warga termasuk dari luar desa dapat mengakses layanan ini.' }}
                                                    </small>
                                                @endif
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- TAB 2: Pengaturan Pengiriman (Global) -->
                <div class="tab-pane fade" id="navs-pengiriman" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                                <div class="avatar avatar-sm bg-info-subtle text-info rounded-circle me-3 d-flex justify-content-center align-items-center">
                                    <i class="bx bx-slider-alt fs-5"></i>
                                </div>
                                <h6 class="fw-bold mb-0">Pengaturan Pengiriman & Armada (Master-Detail)</h6>
                            </div>
                            
                            <div class="row g-4" id="main_delivery_section">
                                <!-- Sidebar Navigation (Master) -->
                                <div class="col-md-4 border-end pe-md-4">
                                    <div class="nav flex-column nav-pills gap-2" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                        <button class="nav-link d-flex align-items-center text-start p-3 rounded-4 active" id="v-pills-mobil-tab" data-bs-toggle="pill" data-bs-target="#box_delivery_mobil" type="button" role="tab" aria-selected="true" style="display: none; transition: all 0.2s;">
                                            <img src="{{ asset('User/img/elemen/mobil.png') }}" class="me-3" style="width: 24px; height: 24px; object-fit: contain;">
                                            <div>
                                                <span class="fw-bold d-block">Penyewaan Mobil</span>
                                                <small class="text-muted" style="font-size: 0.75rem;">Serah Terima, BBM & Supir</small>
                                            </div>
                                        </button>
                                        
                                        <button class="nav-link d-flex align-items-center text-start p-3 rounded-4" id="v-pills-alat-tab" data-bs-toggle="pill" data-bs-target="#box_delivery_alat" type="button" role="tab" aria-selected="false" style="display: none; transition: all 0.2s;">
                                            <img src="{{ asset('User/img/elemen/F1.png') }}" class="me-3" style="width: 24px; height: 24px; object-fit: contain;">
                                            <div>
                                                <span class="fw-bold d-block">Penyewaan Alat</span>
                                                <small class="text-muted" style="font-size: 0.75rem;">Metode Pengiriman</small>
                                            </div>
                                        </button>
                                        
                                        <button class="nav-link d-flex align-items-center text-start p-3 rounded-4" id="v-pills-gas-tab" data-bs-toggle="pill" data-bs-target="#box_delivery_gas" type="button" role="tab" aria-selected="false" style="display: none; transition: all 0.2s;">
                                            <img src="{{ asset('User/img/elemen/F2.png') }}" class="me-3" style="width: 24px; height: 24px; object-fit: contain;">
                                            <div>
                                                <span class="fw-bold d-block">Penjualan Gas</span>
                                                <small class="text-muted" style="font-size: 0.75rem;">Ambil Sendiri / Antar</small>
                                            </div>
                                        </button>
                                        
                                        <button class="nav-link d-flex align-items-center text-start p-3 rounded-4" id="v-pills-fasilitas-tab" data-bs-toggle="pill" data-bs-target="#box_delivery_fasilitas" type="button" role="tab" aria-selected="false" style="display: none; transition: all 0.2s;">
                                            <img src="{{ asset('User/img/elemen/fasilitas.png') }}" class="me-3" style="width: 24px; height: 24px; object-fit: contain;">
                                            <div>
                                                <span class="fw-bold d-block">Fasilitas Umum</span>
                                                <small class="text-muted" style="font-size: 0.75rem;">Metode Penggunaan / Serah Terima</small>
                                            </div>
                                        </button>

                                        <button class="nav-link d-flex align-items-center text-start p-3 rounded-4" id="v-pills-pasar-tab" data-bs-toggle="pill" data-bs-target="#box_delivery_pasar" type="button" role="tab" aria-selected="false" style="display: none; transition: all 0.2s;">
                                            <img src="{{ asset('Admin/img/pasardaerah/PasarDaerah2.png') }}" class="me-3" style="width: 24px; height: 24px; object-fit: contain;">
                                            <div>
                                                <span class="fw-bold d-block">Pasar Daerah</span>
                                                <small class="text-muted" style="font-size: 0.75rem;">Ambil Sendiri / Antar</small>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Content Area (Detail) -->
                                <div class="col-md-8 ps-md-4">
                                    <div class="tab-content p-0 shadow-none bg-transparent" id="v-pills-tabContent">
                                        
                                        <!-- Mobil Detail -->
                                        <div class="tab-pane fade show active" id="box_delivery_mobil" role="tabpanel" aria-labelledby="v-pills-mobil-tab">
                                            <div class="d-flex align-items-center mb-4">
                                                <img src="{{ asset('User/img/elemen/mobil.png') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-primary">Pengaturan Penyewaan Mobil</h6>
                                                    <small class="text-muted">Atur metode penyerahan armada, bahan bakar, dan ketersediaan supir.</small>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column gap-3 mb-4">
                                                <div class="bg-light p-3 rounded-4 border">
    <div class="d-flex justify-content-between align-items-center">
        <div class="flex-grow-1 pe-3">
            <div class="d-flex align-items-center mb-2">
                <img src="{{ asset('Admin/img/elements/antar.png') }}" alt="Layanan Antar (Diantar Petugas)" style="width: 45px; height: 45px; object-fit: contain;" class="me-3">
                <div>
                    <span class="text-dark fw-bold d-block" style="font-size: 1.05rem;">Layanan Antar (Diantar Petugas)</span>
                    <span class="text-muted small d-block mb-0">Mobil desa diantarkan langsung ke titik lokasi penyewa</span>
                </div>
            </div>
            <div class="dynamic-keterangan mt-2" style="display: none;">
                <div class="d-flex align-items-start rounded-3 p-2 border" style="background-color: #e7f1ff; border-color: #b8daff; color: #004085;">
                    <i class="bx bx-check-circle me-2 mt-1"></i>
                    <span class="small fw-semibold">Aktif: Warga dapat memilih opsi ini saat melakukan pemesanan.</span>
                </div>
            </div>
        </div>
        <div class="form-check form-switch mb-0 d-flex flex-column align-items-center justify-content-center" style="min-width: 80px;">
            <input class="form-check-input toggle-status m-0" style="transform: scale(1.3); cursor: pointer; float: none;" type="checkbox" name="mobil_delivery_antar_active" {{ isset($region->payment_info['mobil_delivery_antar_active']) ? ($region->payment_info['mobil_delivery_antar_active'] ? 'checked' : '') : 'checked' }}>
            <span class="status-label mt-2 small fw-bold text-center"></span>
        </div>
    </div>
</div>
                                                <div class="bg-light p-3 rounded-4 border">
    <div class="d-flex justify-content-between align-items-center">
        <div class="flex-grow-1 pe-3">
            <div class="d-flex align-items-center mb-2">
                <img src="{{ asset('Admin/img/elements/jemput.png') }}" alt="Ambil / Jemput Sendiri" style="width: 45px; height: 45px; object-fit: contain;" class="me-3">
                <div>
                    <span class="text-dark fw-bold d-block" style="font-size: 1.05rem;">Ambil / Jemput Sendiri</span>
                    <span class="text-muted small d-block mb-0">Penyewa datang menjemput mobil di kantor atau garasi desa</span>
                </div>
            </div>
            <div class="dynamic-keterangan mt-2" style="display: none;">
                <div class="d-flex align-items-start rounded-3 p-2 border" style="background-color: #e7f1ff; border-color: #b8daff; color: #004085;">
                    <i class="bx bx-check-circle me-2 mt-1"></i>
                    <span class="small fw-semibold">Aktif: Warga dapat memilih opsi ini saat melakukan pemesanan.</span>
                </div>
            </div>
        </div>
        <div class="form-check form-switch mb-0 d-flex flex-column align-items-center justify-content-center" style="min-width: 80px;">
            <input class="form-check-input toggle-status m-0" style="transform: scale(1.3); cursor: pointer; float: none;" type="checkbox" name="mobil_delivery_jemput_active" {{ isset($region->payment_info['mobil_delivery_jemput_active']) ? ($region->payment_info['mobil_delivery_jemput_active'] ? 'checked' : '') : 'checked' }}>
            <span class="status-label mt-2 small fw-bold text-center"></span>
        </div>
    </div>
</div>
                                            </div>

                                        </div>
                                        
                                        <!-- Alat Berat Detail -->
                                        <div class="tab-pane fade" id="box_delivery_alat" role="tabpanel" aria-labelledby="v-pills-alat-tab">
                                            <div class="d-flex align-items-center mb-4">
                                                <img src="{{ asset('User/img/elemen/F1.png') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-primary">Pengaturan Penyewaan Alat</h6>
                                                    <small class="text-muted">Atur cara alat berat diserahkan ke warga.</small>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column gap-3">
                                                <div class="bg-light p-3 rounded-4 border">
    <div class="d-flex justify-content-between align-items-center">
        <div class="flex-grow-1 pe-3">
            <div class="d-flex align-items-center mb-2">
                <img src="{{ asset('Admin/img/elements/antar.png') }}" alt="Layanan Antar ke Lokasi" style="width: 45px; height: 45px; object-fit: contain;" class="me-3">
                <div>
                    <span class="text-dark fw-bold d-block" style="font-size: 1.05rem;">Layanan Antar ke Lokasi</span>
                    <span class="text-muted small d-block mb-0">Mobil desa / petugas mengantar ke lokasi warga</span>
                </div>
            </div>
            <div class="dynamic-keterangan mt-2" style="display: none;">
                <div class="d-flex align-items-start rounded-3 p-2 border" style="background-color: #e7f1ff; border-color: #b8daff; color: #004085;">
                    <i class="bx bx-check-circle me-2 mt-1"></i>
                    <span class="small fw-semibold">Aktif: Warga dapat memilih opsi ini saat melakukan pemesanan.</span>
                </div>
            </div>
        </div>
        <div class="form-check form-switch mb-0 d-flex flex-column align-items-center justify-content-center" style="min-width: 80px;">
            <input class="form-check-input toggle-status m-0" style="transform: scale(1.3); cursor: pointer; float: none;" type="checkbox" name="alat_delivery_antar_active" {{ isset($region->payment_info['alat_delivery_antar_active']) ? ($region->payment_info['alat_delivery_antar_active'] ? 'checked' : '') : 'checked' }}>
            <span class="status-label mt-2 small fw-bold text-center"></span>
        </div>
    </div>
</div>
                                                <div class="bg-light p-3 rounded-4 border">
    <div class="d-flex justify-content-between align-items-center">
        <div class="flex-grow-1 pe-3">
            <div class="d-flex align-items-center mb-2">
                <img src="{{ asset('Admin/img/elements/jemput.png') }}" alt="Ambil Sendiri" style="width: 45px; height: 45px; object-fit: contain;" class="me-3">
                <div>
                    <span class="text-dark fw-bold d-block" style="font-size: 1.05rem;">Ambil Sendiri</span>
                    <span class="text-muted small d-block mb-0">Warga mengambil alat langsung ke kantor/gudang</span>
                </div>
            </div>
            <div class="dynamic-keterangan mt-2" style="display: none;">
                <div class="d-flex align-items-start rounded-3 p-2 border" style="background-color: #e7f1ff; border-color: #b8daff; color: #004085;">
                    <i class="bx bx-check-circle me-2 mt-1"></i>
                    <span class="small fw-semibold">Aktif: Warga dapat memilih opsi ini saat melakukan pemesanan.</span>
                </div>
            </div>
        </div>
        <div class="form-check form-switch mb-0 d-flex flex-column align-items-center justify-content-center" style="min-width: 80px;">
            <input class="form-check-input toggle-status m-0" style="transform: scale(1.3); cursor: pointer; float: none;" type="checkbox" name="alat_delivery_jemput_active" {{ isset($region->payment_info['alat_delivery_jemput_active']) ? ($region->payment_info['alat_delivery_jemput_active'] ? 'checked' : '') : 'checked' }}>
            <span class="status-label mt-2 small fw-bold text-center"></span>
        </div>
    </div>
</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Gas Detail -->
                                        <div class="tab-pane fade" id="box_delivery_gas" role="tabpanel" aria-labelledby="v-pills-gas-tab">
                                            <div class="d-flex align-items-center mb-4">
                                                <img src="{{ asset('User/img/elemen/F2.png') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-primary">Pengaturan Penjualan Gas</h6>
                                                    <small class="text-muted">Metode pengambilan tabung gas oleh warga.</small>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column gap-3">
                                                <div class="bg-light p-3 rounded-4 border">
    <div class="d-flex justify-content-between align-items-center">
        <div class="flex-grow-1 pe-3">
            <div class="d-flex align-items-center mb-2">
                <img src="{{ asset('Admin/img/elements/antar.png') }}" alt="Layanan Antar (Kurir Desa)" style="width: 45px; height: 45px; object-fit: contain;" class="me-3">
                <div>
                    <span class="text-dark fw-bold d-block" style="font-size: 1.05rem;">Layanan Antar (Kurir Desa)</span>
                    <span class="text-muted small d-block mb-0">Gas diantar ke rumah warga</span>
                </div>
            </div>
            <div class="dynamic-keterangan mt-2" style="display: none;">
                <div class="d-flex align-items-start rounded-3 p-2 border" style="background-color: #e7f1ff; border-color: #b8daff; color: #004085;">
                    <i class="bx bx-check-circle me-2 mt-1"></i>
                    <span class="small fw-semibold">Aktif: Warga dapat memilih opsi ini saat melakukan pemesanan.</span>
                </div>
            </div>
        </div>
        <div class="form-check form-switch mb-0 d-flex flex-column align-items-center justify-content-center" style="min-width: 80px;">
            <input class="form-check-input toggle-status m-0" style="transform: scale(1.3); cursor: pointer; float: none;" type="checkbox" name="gas_delivery_antar_active" {{ isset($region->payment_info['gas_delivery_antar_active']) ? ($region->payment_info['gas_delivery_antar_active'] ? 'checked' : '') : 'checked' }}>
            <span class="status-label mt-2 small fw-bold text-center"></span>
        </div>
    </div>
</div>
                                                <div class="bg-light p-3 rounded-4 border">
    <div class="d-flex justify-content-between align-items-center">
        <div class="flex-grow-1 pe-3">
            <div class="d-flex align-items-center mb-2">
                <img src="{{ asset('Admin/img/elements/jemput.png') }}" alt="Beli di Pangkalan (Ambil Sendiri)" style="width: 45px; height: 45px; object-fit: contain;" class="me-3">
                <div>
                    <span class="text-dark fw-bold d-block" style="font-size: 1.05rem;">Beli di Pangkalan (Ambil Sendiri)</span>
                    <span class="text-muted small d-block mb-0">Warga datang menukar tabung ke pangkalan</span>
                </div>
            </div>
            <div class="dynamic-keterangan mt-2" style="display: none;">
                <div class="d-flex align-items-start rounded-3 p-2 border" style="background-color: #e7f1ff; border-color: #b8daff; color: #004085;">
                    <i class="bx bx-check-circle me-2 mt-1"></i>
                    <span class="small fw-semibold">Aktif: Warga dapat memilih opsi ini saat melakukan pemesanan.</span>
                </div>
            </div>
        </div>
        <div class="form-check form-switch mb-0 d-flex flex-column align-items-center justify-content-center" style="min-width: 80px;">
            <input class="form-check-input toggle-status m-0" style="transform: scale(1.3); cursor: pointer; float: none;" type="checkbox" name="gas_delivery_jemput_active" {{ isset($region->payment_info['gas_delivery_jemput_active']) ? ($region->payment_info['gas_delivery_jemput_active'] ? 'checked' : '') : 'checked' }}>
            <span class="status-label mt-2 small fw-bold text-center"></span>
        </div>
    </div>
</div>
                                            </div>
                                        </div>

                                        <!-- Pasar Detail -->
                                        <div class="tab-pane fade" id="box_delivery_pasar" role="tabpanel" aria-labelledby="v-pills-pasar-tab">
                                            <div class="d-flex align-items-center mb-4">
                                                <img src="{{ asset('Admin/img/pasardaerah/PasarDaerah2.png') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-primary">Pengaturan Pasar Daerah</h6>
                                                    <small class="text-muted">Metode pengiriman produk dari toko/penjual ke pembeli.</small>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column gap-3">
                                                <div class="bg-light p-3 rounded-4 border">
    <div class="d-flex justify-content-between align-items-center">
        <div class="flex-grow-1 pe-3">
            <div class="d-flex align-items-center mb-2">
                <img src="{{ asset('Admin/img/elements/antar.png') }}" alt="Layanan Antar (Kurir/Armada)" style="width: 45px; height: 45px; object-fit: contain;" class="me-3">
                <div>
                    <span class="text-dark fw-bold d-block" style="font-size: 1.05rem;">Layanan Antar (Kurir/Armada)</span>
                    <span class="text-muted small d-block mb-0">Produk diantar ke rumah warga (dengan ongkos kirim otomatis)</span>
                </div>
            </div>
            <div class="dynamic-keterangan mt-2" style="display: none;">
                <div class="d-flex align-items-start rounded-3 p-2 border" style="background-color: #e7f1ff; border-color: #b8daff; color: #004085;">
                    <i class="bx bx-check-circle me-2 mt-1"></i>
                    <span class="small fw-semibold">Aktif: Warga dapat memilih opsi ini saat melakukan pemesanan.</span>
                </div>
            </div>
        </div>
        <div class="form-check form-switch mb-0 d-flex flex-column align-items-center justify-content-center" style="min-width: 80px;">
            <input class="form-check-input toggle-status m-0" style="transform: scale(1.3); cursor: pointer; float: none;" type="checkbox" name="pasar_delivery_antar_active" {{ isset($region->payment_info['pasar_delivery_antar_active']) ? ($region->payment_info['pasar_delivery_antar_active'] ? 'checked' : '') : 'checked' }}>
            <span class="status-label mt-2 small fw-bold text-center"></span>
        </div>
    </div>
</div>
                                                <div class="bg-light p-3 rounded-4 border">
    <div class="d-flex justify-content-between align-items-center">
        <div class="flex-grow-1 pe-3">
            <div class="d-flex align-items-center mb-2">
                <img src="{{ asset('Admin/img/elements/jemput.png') }}" alt="Jemput Sendiri / Pick-up (Gratis)" style="width: 45px; height: 45px; object-fit: contain;" class="me-3">
                <div>
                    <span class="text-dark fw-bold d-block" style="font-size: 1.05rem;">Jemput Sendiri / Pick-up (Gratis)</span>
                    <span class="text-muted small d-block mb-0">Warga dapat memilih untuk menjemput produk langsung di toko</span>
                </div>
            </div>
            <div class="dynamic-keterangan mt-2" style="display: none;">
                <div class="d-flex align-items-start rounded-3 p-2 border" style="background-color: #e7f1ff; border-color: #b8daff; color: #004085;">
                    <i class="bx bx-check-circle me-2 mt-1"></i>
                    <span class="small fw-semibold">Aktif: Warga dapat memilih opsi ini saat melakukan pemesanan.</span>
                </div>
            </div>
        </div>
        <div class="form-check form-switch mb-0 d-flex flex-column align-items-center justify-content-center" style="min-width: 80px;">
            <input class="form-check-input toggle-status m-0" style="transform: scale(1.3); cursor: pointer; float: none;" type="checkbox" name="pasar_delivery_jemput_active" {{ isset($region->payment_info['pasar_delivery_jemput_active']) ? ($region->payment_info['pasar_delivery_jemput_active'] ? 'checked' : '') : 'checked' }}>
            <span class="status-label mt-2 small fw-bold text-center"></span>
        </div>
    </div>
</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Fasilitas Umum Detail -->
                                        <div class="tab-pane fade" id="box_delivery_fasilitas" role="tabpanel" aria-labelledby="v-pills-fasilitas-tab">
                                            <div class="d-flex align-items-center mb-4">
                                                <img src="{{ asset('User/img/elemen/fasilitas.png') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-primary">Pengaturan Fasilitas Umum</h6>
                                                    <small class="text-muted">Metode penggunaan fasilitas (Gedung, Ambulans, Lapangan, dll).</small>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex flex-column gap-3 mb-4">
                                                <div class="bg-light p-3 rounded-4 border">
    <div class="d-flex justify-content-between align-items-center">
        <div class="flex-grow-1 pe-3">
            <div class="d-flex align-items-center mb-2">
                <img src="{{ asset('Admin/img/elements/antar.png') }}" alt="Layanan Kunjungan / Antar / Panggilan" style="width: 45px; height: 45px; object-fit: contain;" class="me-3">
                <div>
                    <span class="text-dark fw-bold d-block" style="font-size: 1.05rem;">Layanan Kunjungan / Antar / Panggilan</span>
                    <span class="text-muted small d-block mb-0">Fasilitas (seperti ambulans, dan transportasi lain) atau petugas datang ke titik lokasi warga</span>
                </div>
            </div>
            <div class="dynamic-keterangan mt-2" style="display: none;">
                <div class="d-flex align-items-start rounded-3 p-2 border" style="background-color: #e7f1ff; border-color: #b8daff; color: #004085;">
                    <i class="bx bx-check-circle me-2 mt-1"></i>
                    <span class="small fw-semibold">Aktif: Warga dapat memilih opsi ini saat melakukan pemesanan.</span>
                </div>
            </div>
        </div>
        <div class="form-check form-switch mb-0 d-flex flex-column align-items-center justify-content-center" style="min-width: 80px;">
            <input class="form-check-input toggle-status m-0" style="transform: scale(1.3); cursor: pointer; float: none;" type="checkbox" name="fasilitas_delivery_antar_active" {{ isset($region->payment_info['fasilitas_delivery_antar_active']) ? ($region->payment_info['fasilitas_delivery_antar_active'] ? 'checked' : '') : 'checked' }}>
            <span class="status-label mt-2 small fw-bold text-center"></span>
        </div>
    </div>
</div>
                                                <div class="bg-light p-3 rounded-4 border">
    <div class="d-flex justify-content-between align-items-center">
        <div class="flex-grow-1 pe-3">
            <div class="d-flex align-items-center mb-2">
                <img src="{{ asset('Admin/img/elements/jemput.png') }}" alt="Gunakan di Tempat / Ambil Sendiri" style="width: 45px; height: 45px; object-fit: contain;" class="me-3">
                <div>
                    <span class="text-dark fw-bold d-block" style="font-size: 1.05rem;">Gunakan di Tempat / Ambil Sendiri</span>
                    <span class="text-muted small d-block mb-0">Warga mendatangi lokasi fasilitas (gedung, balai) atau mengambil sendiri barang</span>
                </div>
            </div>
            <div class="dynamic-keterangan mt-2" style="display: none;">
                <div class="d-flex align-items-start rounded-3 p-2 border" style="background-color: #e7f1ff; border-color: #b8daff; color: #004085;">
                    <i class="bx bx-check-circle me-2 mt-1"></i>
                    <span class="small fw-semibold">Aktif: Warga dapat memilih opsi ini saat melakukan pemesanan.</span>
                </div>
            </div>
        </div>
        <div class="form-check form-switch mb-0 d-flex flex-column align-items-center justify-content-center" style="min-width: 80px;">
            <input class="form-check-input toggle-status m-0" style="transform: scale(1.3); cursor: pointer; float: none;" type="checkbox" name="fasilitas_delivery_jemput_active" {{ isset($region->payment_info['fasilitas_delivery_jemput_active']) ? ($region->payment_info['fasilitas_delivery_jemput_active'] ? 'checked' : '') : 'checked' }}>
            <span class="status-label mt-2 small fw-bold text-center"></span>
        </div>
    </div>
</div>
                                            </div>

                                            @if($hasFasilitasKendaraan)
                                            <div class="row g-4 border-top pt-4">
                                                <div class="col-12">
                                                    <label class="form-label text-dark fw-bold mb-2">BBM Kendaraan Default</label>
                                                    <select name="fasilitas_bbm" class="form-select text-dark shadow-sm rounded-3 py-2">
                                                        <option value="Penyewa" {{ ($region->payment_info['fasilitas_bbm_default'] ?? 'Penyewa') == 'Penyewa' ? 'selected' : '' }}>Ditanggung Penyewa</option>
                                                        <option value="Pemerintah Desa" {{ ($region->payment_info['fasilitas_bbm_default'] ?? '') == 'Pemerintah Desa' ? 'selected' : '' }}>Gratis (Ditanggung Desa)</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label text-dark fw-bold mb-2">Supir Kendaraan Default</label>
                                                    <select name="fasilitas_supir" class="form-select text-dark shadow-sm rounded-3 py-2">
                                                        <option value="Tanpa Supir (Bawa Sendiri)" {{ ($region->payment_info['fasilitas_supir_default'] ?? 'Tanpa Supir (Bawa Sendiri)') == 'Tanpa Supir (Bawa Sendiri)' || ($region->payment_info['fasilitas_supir_default'] ?? '') == 'Lepas Kunci' ? 'selected' : '' }}>Tanpa Supir</option>
                                                        <option value="Dengan Supir" {{ ($region->payment_info['fasilitas_supir_default'] ?? '') == 'Dengan Supir' ? 'selected' : '' }}>Dengan Supir Desa</option>
                                                        <option value="Bebas Pilih" {{ ($region->payment_info['fasilitas_supir_default'] ?? '') == 'Bebas Pilih' ? 'selected' : '' }}>Bebas Pilih</option>
                                                    </select>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Empty State -->
                            <div id="empty_delivery_state" class="text-center py-5" style="display: none;">
                                <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                                    <i class="bx bx-box fs-1 text-muted"></i>
                                </div>
                                <h6 class="fw-bold text-muted mb-1">Belum Ada Layanan Terkait</h6>
                                <p class="text-muted small mb-0 px-md-5">Silakan aktifkan layanan (seperti Mobil, Alat Berat, atau Pangkalan Gas) di tab <b>Layanan Wilayah</b> terlebih dahulu agar pengaturannya muncul di sini.</p>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="mt-4 border-top pt-4 pb-4 mb-2 text-end">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                        <i class="bx bx-save me-2"></i> Simpan Pengaturan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Floating Banner for Unsaved Changes -->
<div id="unsaved-changes-banner" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 bg-dark text-white rounded-3 px-3 py-2 shadow-lg d-flex align-items-center" style="z-index: 1050; display: none; width: 90%; max-width: 400px; transform: translateY(100px); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
    <i class="bx bx-info-circle fs-4 me-2 text-warning"></i>
    <span class="fw-semibold" style="font-size: 0.85rem; line-height: 1.2;">Jangan lupa klik "Simpan Pengaturan" setelah mengubah data.</span>
</div>

<style>
/* Custom style for exclusive toggle to differentiate from primary toggle */
.form-check-input.exclusive-toggle:checked {
    background-color: #ffab00 !important; /* Warning / Orange color */
    border-color: #ffab00 !important;
}
</style>

<script>
(function() {
    const checkboxes = document.querySelectorAll('input[name="services[]"]');
    
    function updateDeliveryBoxes() {
        let showMobil = false, showAlat = false, showGas = false, showFasilitas = false, showPasar = false;
        
        checkboxes.forEach(cb => {
            if(cb.checked) {
                const nameElem = cb.closest('.card-body').querySelector('span.fw-bold');
                if (nameElem) {
                    const name = nameElem.innerText;
                    if(name.includes('Mobil')) showMobil = true;
                    if(name.includes('Alat')) showAlat = true;
                    if(name.includes('Gas')) showGas = true;
                    if(name.includes('Fasilitas Umum')) showFasilitas = true;
                    if(name.includes('Pasar Daerah')) showPasar = true;
                }
            }
        });
        
        const tabMobil = document.getElementById('v-pills-mobil-tab');
        const tabAlat = document.getElementById('v-pills-alat-tab');
        const tabGas = document.getElementById('v-pills-gas-tab');
        const tabFasilitas = document.getElementById('v-pills-fasilitas-tab');
        const tabPasar = document.getElementById('v-pills-pasar-tab');
        const mainBox = document.getElementById('main_delivery_section');
        const emptyState = document.getElementById('empty_delivery_state');
        
        if(tabMobil) tabMobil.style.display = showMobil ? 'flex' : 'none';
        if(tabAlat) tabAlat.style.display = showAlat ? 'flex' : 'none';
        if(tabGas) tabGas.style.display = showGas ? 'flex' : 'none';
        if(tabFasilitas) tabFasilitas.style.display = showFasilitas ? 'flex' : 'none';
        if(tabPasar) tabPasar.style.display = showPasar ? 'flex' : 'none';
        
        // Auto-select first visible tab
        let firstVisible = null;
        if(showMobil) firstVisible = tabMobil;
        else if(showAlat) firstVisible = tabAlat;
        else if(showGas) firstVisible = tabGas;
        else if(showPasar) firstVisible = tabPasar;
        else if(showFasilitas) firstVisible = tabFasilitas;
        
        const hasAnyDelivery = (showMobil || showAlat || showGas || showFasilitas || showPasar);
        
        if(mainBox) mainBox.style.display = hasAnyDelivery ? 'flex' : 'none';
        if(emptyState) emptyState.style.display = hasAnyDelivery ? 'none' : 'block';
        
        if (firstVisible) {
            // Activate it using Bootstrap Tab API if not already active
            if (!firstVisible.classList.contains('active')) {
                document.querySelectorAll('#v-pills-tab .nav-link').forEach(t => {
                    t.classList.remove('active');
                    t.setAttribute('aria-selected', 'false');
                });
                document.querySelectorAll('#v-pills-tabContent .tab-pane').forEach(p => {
                    p.classList.remove('show', 'active');
                });
                
                firstVisible.classList.add('active');
                firstVisible.setAttribute('aria-selected', 'true');
                const targetId = firstVisible.getAttribute('data-bs-target');
                document.querySelector(targetId).classList.add('show', 'active');
            }
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateDeliveryBoxes);
    });
    
    updateDeliveryBoxes();

    // ULTRA-SIMPLE Unsaved Changes Banner Logic
    const form = document.getElementById('region-settings-form');
    const banner = document.getElementById('unsaved-changes-banner');
    
    if (form && banner) {
        // Pindahkan banner ke root body agar position-fixed tidak terganggu oleh parent transform
        document.body.appendChild(banner);
        
        banner.style.display = 'none';
        banner.style.transform = 'translateY(100px)';

        const showBanner = (e) => {
            if (e && e.isTrusted) {
                banner.style.display = 'flex';
                void banner.offsetWidth;
                banner.style.transform = 'translateY(0)';
            }
        };
        
        form.addEventListener('input', showBanner);
        form.addEventListener('change', showBanner);
    }
    
    // Logika Text Label Toggle Status (Aktif/Nonaktif) untuk Delivery
    const statusToggles = document.querySelectorAll('.toggle-status');
    statusToggles.forEach(toggle => {
        const updateLabel = (el) => {
            const label = el.nextElementSibling;
            if(label && label.classList.contains('status-label')) {
                label.innerText = el.checked ? 'Aktif' : 'Tidak Aktif';
                
                // Toggle keterangan
                const parent = el.closest('.bg-light');
                if (parent) {
                    const ket = parent.querySelector('.dynamic-keterangan');
                    if (ket) {
                        ket.style.display = el.checked ? 'block' : 'none';
                    }
                }
                
                if(el.checked) {
                    label.classList.remove('text-secondary');
                    label.classList.add('text-primary');
                } else {
                    label.classList.remove('text-primary');
                    label.classList.add('text-secondary');
                }
            }
        };
        updateLabel(toggle);
        toggle.addEventListener('change', function() { updateLabel(this); });
    });

    // Logika Main Service Card Activation
    const serviceToggles = document.querySelectorAll('.service-main-toggle');
    serviceToggles.forEach(toggle => {
        const updateServiceCard = (el) => {
            const label = el.nextElementSibling;
            const card = el.closest('.card-service-item');
            const descContainer = card.querySelector('.status-desc-text');
            
            if(label && label.classList.contains('status-label-main')) {
                if(el.checked) {
                    label.innerText = 'Layanan Aktif';
                    label.classList.remove('text-secondary');
                    label.classList.add('text-primary');
                    card.classList.remove('border-secondary', 'bg-light');
                    card.classList.add('border-primary', 'shadow-sm', 'bg-label-primary');
                    if(descContainer) {
                        descContainer.innerText = 'Layanan ini muncul di beranda warga.';
                        descContainer.classList.remove('text-danger');
                        descContainer.classList.add('text-success');
                    }
                } else {
                    label.innerText = 'Layanan Nonaktif';
                    label.classList.remove('text-primary');
                    label.classList.add('text-secondary');
                    card.classList.remove('border-primary', 'shadow-sm', 'bg-label-primary');
                    card.classList.add('border-secondary', 'bg-light');
                    if(descContainer) {
                        descContainer.innerText = 'Layanan ini disembunyikan sepenuhnya.';
                        descContainer.classList.remove('text-success');
                        descContainer.classList.add('text-danger');
                    }
                }
            }
        };
        updateServiceCard(toggle);
        toggle.addEventListener('change', function() { updateServiceCard(this); });
    });

    // Logika Hak Akses Eksklusif Toggle
    const exclusiveToggles = document.querySelectorAll('.exclusive-toggle');
    exclusiveToggles.forEach(toggle => {
        const updateExclusiveCard = (el) => {
            const label = el.closest('.flex-grow-1') ? el.closest('.flex-grow-1').querySelector('.exclusive-label-main') : el.nextElementSibling;
            const card = el.closest('.card-service-item');
            const descContainer = el.closest('.flex-grow-1') ? el.closest('.flex-grow-1').querySelector('.exclusive-desc-text') : card.querySelector('.exclusive-desc-text');
            const isPengumuman = card.querySelector('span.fw-bold') && card.querySelector('span.fw-bold').innerText.includes('Kabar dan Informasi');
            const descTextRaw = descContainer ? descContainer.getAttribute('data-desc') || '' : '';
            const regionName = "{{ $region->name }}";
            
            if(label && label.classList.contains('exclusive-label-main')) {
                if(el.checked) {
                    label.innerText = 'Eksklusif Warga Lokal';
                    if(descContainer) {
                        descContainer.classList.remove('text-primary');
                        descContainer.classList.add('text-warning');
                        if (isPengumuman) {
                            descContainer.innerText = 'Pengumuman HANYA tampil untuk warga domisili ' + regionName + '.';
                        } else {
                            // Extract action text from original text if needed, or fallback
                            const action = descContainer.innerText.split(regionName)[1] || ' mengakses layanan ini.';
                            descContainer.innerText = 'Hanya warga domisili ' + regionName + action;
                        }
                    }
                } else {
                    label.innerText = 'Publik (Terbuka Umum)';
                    if(descContainer) {
                        descContainer.classList.remove('text-warning');
                        descContainer.classList.add('text-primary');
                        if (isPengumuman) {
                            descContainer.innerText = 'Pengumuman dapat dilihat oleh semua warga termasuk dari desa lain.';
                        } else {
                            descContainer.innerText = 'Semua warga termasuk dari luar desa dapat mengakses layanan ini.';
                        }
                    }
                }
            }
        };
        
        // Save original action text to data attribute on first run
        const descC = toggle.closest('.card-service-item').querySelector('.exclusive-desc-text');
        if (descC && !descC.hasAttribute('data-desc')) {
            descC.setAttribute('data-desc', descC.innerText);
        }
        
        updateExclusiveCard(toggle);
        toggle.addEventListener('change', function() { updateExclusiveCard(this); });
    });
})();
</script>
@endsection
