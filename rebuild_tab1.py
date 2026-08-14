import os
import re

file_path = r"D:\laragon\www\SilaDesBeng\resources\views\admin\region_settings\index.blade.php"

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

tab1_replacement = """        <div class="nav-align-top mb-4">
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
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold text-dark">Deskripsi Singkat / Profil Wilayah</label>
                                <textarea name="profile_text" rows="3" class="form-control border-secondary rounded-3" placeholder="Tuliskan deskripsi singkat profil wilayah ini...">{{ old('profile_text', $region->profile_text ?? '') }}</textarea>
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
                            <div class="d-flex align-items-center mb-3 border-bottom pb-3">
                                <div class="avatar avatar-sm bg-warning-subtle text-warning rounded-circle me-3 d-flex justify-content-center align-items-center">
                                    <i class="bx bx-layer fs-5"></i>
                                </div>
                                <h6 class="fw-bold mb-0">Layanan yang Tersedia</h6>
                            </div>
                            <p class="text-muted small mb-4">Centang layanan yang ingin Anda aktifkan. Warga hanya dapat mengakses layanan yang dicentang di bawah ini.</p>
                            
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
                                                if (strpos($sName, 'mobil') !== false) {
                                                    $iconPath = 'User/img/elemen/mobil.png';
                                                    $descText = 'yang dapat melihat dan menyewa armada ini.';
                                                }
                                                elseif (strpos($sName, 'alat') !== false) {
                                                    $iconPath = 'User/img/elemen/F0.png';
                                                    $descText = 'yang dapat meminjam atau menyewa alat.';
                                                }
                                                elseif (strpos($sName, 'gas') !== false) {
                                                    $iconPath = 'User/img/elemen/gas.png';
                                                    $descText = 'yang dapat memesan tabung gas.';
                                                }
                                                elseif (strpos($sName, 'pasar') !== false) {
                                                    $iconPath = 'User/img/elemen/pasar.png';
                                                    $descText = 'yang dapat mendaftar sewa kios.';
                                                }
                                            @endphp

                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-white rounded p-2 me-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                    <img src="{{ asset($iconPath) }}" alt="{{ $service->name }}" class="w-100 h-100 object-contain" style="object-fit: contain;">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <span class="fw-bold d-block text-dark">{{ $service->name }}</span>
                                                    <div class="form-check form-switch mb-0 mt-1">
                                                        @if(isset($isNews) && $isNews)
                                                            <input type="hidden" name="services[]" value="{{ $service->id }}">
                                                            <input type="checkbox" class="form-check-input" checked disabled style="cursor: not-allowed; transform: scale(1.2);">
                                                            <label class="form-check-label small fw-bold mt-1 text-primary">Wajib (Default)</label>
                                                        @else
                                                            <input type="checkbox" name="services[]" value="{{ $service->id }}" class="form-check-input service-main-toggle" style="cursor: pointer; transform: scale(1.2);" {{ in_array($service->id, $activeServices) ? 'checked' : '' }}>
                                                            <label class="form-check-label small fw-bold mt-1 status-label-main {{ in_array($service->id, $activeServices) ? 'text-primary' : 'text-secondary' }}">{{ in_array($service->id, $activeServices) ? 'Layanan Aktif' : 'Layanan Nonaktif' }}</label>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="border-top pt-2 mt-2">
                                                <label class="form-label text-dark fw-bold small mb-2 d-flex align-items-center">
                                                    <i class="bx bx-shield-quarter text-warning me-1"></i> Hak Akses Eksklusif
                                                </label>
                                                <div class="form-check form-switch mb-1">
                                                    <input type="checkbox" name="exclusive_services[]" value="{{ $service->id }}" class="form-check-input exclusive-toggle" style="cursor: pointer; border-color: #ffab00;" {{ in_array($service->id, $exclusiveServices) ? 'checked' : '' }}>
                                                    <label class="form-check-label small text-dark">Eksklusif Warga Lokal</label>
                                                </div>
                                                <small class="text-muted" style="font-size: 0.75rem;">Hanya warga domisili {{ $region->name }} {{ $descText }}</small>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        </div>
                    </div>
                </div>"""

# Remove everything between <form ...> and <!-- TAB 2 -->
content = re.sub(r'(<form action="{{ route\(\'admin\.region-settings\.update\'\) }}" method="POST">\s*@csrf).*?(<!-- TAB 2: Pengaturan Pengiriman \(Global\) -->)', r'\1\n' + tab1_replacement + '\n\n\2', content, flags=re.DOTALL)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Tab 1 rebuilt")
