import os
import json

file_path = r"D:\laragon\www\SilaDesBeng\resources\views\admin\region_settings\index.blade.php"

# We will read the current file and do replacements to recreate the Vertical Tabs layout
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace Tab 2 content
import re

start_marker = "<!-- TAB 2: Pengaturan Pengiriman (Global) -->"
end_marker = "<!-- Save Button -->"

# If the old markers exist
tab2_replacement = """<!-- TAB 2: Pengaturan Pengiriman (Global) -->
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
                                            <i class="bx bx-car fs-4 me-3"></i>
                                            <div>
                                                <span class="fw-bold d-block">Mobil Desa</span>
                                                <small class="text-muted" style="font-size: 0.75rem;">BBM & Supir</small>
                                            </div>
                                        </button>
                                        
                                        <button class="nav-link d-flex align-items-center text-start p-3 rounded-4" id="v-pills-alat-tab" data-bs-toggle="pill" data-bs-target="#box_delivery_alat" type="button" role="tab" aria-selected="false" style="display: none; transition: all 0.2s;">
                                            <i class="bx bx-wrench fs-4 me-3"></i>
                                            <div>
                                                <span class="fw-bold d-block">Alat Berat</span>
                                                <small class="text-muted" style="font-size: 0.75rem;">Metode Pengiriman</small>
                                            </div>
                                        </button>
                                        
                                        <button class="nav-link d-flex align-items-center text-start p-3 rounded-4" id="v-pills-gas-tab" data-bs-toggle="pill" data-bs-target="#box_delivery_gas" type="button" role="tab" aria-selected="false" style="display: none; transition: all 0.2s;">
                                            <i class="bx bx-gas-pump fs-4 me-3"></i>
                                            <div>
                                                <span class="fw-bold d-block">Pangkalan Gas</span>
                                                <small class="text-muted" style="font-size: 0.75rem;">Ambil Sendiri / Antar</small>
                                            </div>
                                        </button>
                                        
                                        <button class="nav-link d-flex align-items-center text-start p-3 rounded-4" id="v-pills-fasilitas-tab" data-bs-toggle="pill" data-bs-target="#box_delivery_fasilitas" type="button" role="tab" aria-selected="false" style="display: none; transition: all 0.2s;">
                                            <i class="bx bx-buildings fs-4 me-3"></i>
                                            <div>
                                                <span class="fw-bold d-block">Fasilitas Umum</span>
                                                <small class="text-muted" style="font-size: 0.75rem;">Pengambilan Barang</small>
                                            </div>
                                        </button>

                                        <button class="nav-link d-flex align-items-center text-start p-3 rounded-4" id="v-pills-pasar-tab" data-bs-toggle="pill" data-bs-target="#box_delivery_pasar" type="button" role="tab" aria-selected="false" style="display: none; transition: all 0.2s;">
                                            <i class="bx bx-store fs-4 me-3"></i>
                                            <div>
                                                <span class="fw-bold d-block">Pasar Daerah</span>
                                                <small class="text-muted" style="font-size: 0.75rem;">Penyewaan Kios</small>
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
                                                <i class="bx bx-car fs-2 text-primary me-3"></i>
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-primary">Pengaturan Mobil Desa</h6>
                                                    <small class="text-muted">Atur kebijakan bahan bakar dan penyediaan supir.</small>
                                                </div>
                                            </div>
                                            <div class="row g-4">
                                                <div class="col-12">
                                                    <label class="form-label text-dark fw-bold mb-2">BBM Kendaraan Default</label>
                                                    <select name="mobil_bbm" class="form-select text-dark shadow-sm rounded-3 py-2">
                                                        <option value="Penyewa" {{ ($region->payment_info['mobil_bbm_default'] ?? 'Penyewa') == 'Penyewa' ? 'selected' : '' }}>Ditanggung Penyewa</option>
                                                        <option value="Pemerintah Desa" {{ ($region->payment_info['mobil_bbm_default'] ?? '') == 'Pemerintah Desa' ? 'selected' : '' }}>Gratis (Ditanggung Desa)</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label text-dark fw-bold mb-2">Supir Kendaraan Default</label>
                                                    <select name="mobil_supir" class="form-select text-dark shadow-sm rounded-3 py-2">
                                                        <option value="Tanpa Supir (Bawa Sendiri)" {{ ($region->payment_info['mobil_supir_default'] ?? 'Tanpa Supir (Bawa Sendiri)') == 'Tanpa Supir (Bawa Sendiri)' || ($region->payment_info['mobil_supir_default'] ?? '') == 'Lepas Kunci' ? 'selected' : '' }}>Tanpa Supir (Lepas Kunci)</option>
                                                        <option value="Dengan Supir" {{ ($region->payment_info['mobil_supir_default'] ?? '') == 'Dengan Supir' ? 'selected' : '' }}>Termasuk Supir Desa</option>
                                                        <option value="Bebas Pilih" {{ ($region->payment_info['mobil_supir_default'] ?? '') == 'Bebas Pilih' ? 'selected' : '' }}>Penyewa Bebas Memilih</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Alat Berat Detail -->
                                        <div class="tab-pane fade" id="box_delivery_alat" role="tabpanel" aria-labelledby="v-pills-alat-tab">
                                            <div class="d-flex align-items-center mb-4">
                                                <i class="bx bx-wrench fs-2 text-primary me-3"></i>
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-primary">Pengaturan Alat Berat</h6>
                                                    <small class="text-muted">Atur cara alat berat diserahkan ke warga.</small>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column gap-3">
                                                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-4 border">
                                                    <div>
                                                        <span class="text-dark fw-semibold d-block">Layanan Antar ke Lokasi</span>
                                                        <span class="text-muted small">Mobil desa / petugas mengantar ke lokasi warga</span>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input toggle-status" style="transform: scale(1.3); cursor: pointer;" type="checkbox" name="alat_delivery_antar_active" {{ isset($region->payment_info['alat_delivery_antar_active']) ? ($region->payment_info['alat_delivery_antar_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-4 border">
                                                    <div>
                                                        <span class="text-dark fw-semibold d-block">Ambil Sendiri</span>
                                                        <span class="text-muted small">Warga mengambil alat langsung ke kantor/gudang</span>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input toggle-status" style="transform: scale(1.3); cursor: pointer;" type="checkbox" name="alat_delivery_jemput_active" {{ isset($region->payment_info['alat_delivery_jemput_active']) ? ($region->payment_info['alat_delivery_jemput_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Gas Detail -->
                                        <div class="tab-pane fade" id="box_delivery_gas" role="tabpanel" aria-labelledby="v-pills-gas-tab">
                                            <div class="d-flex align-items-center mb-4">
                                                <i class="bx bx-gas-pump fs-2 text-primary me-3"></i>
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-primary">Pengaturan Pangkalan Gas</h6>
                                                    <small class="text-muted">Metode pengambilan tabung gas oleh warga.</small>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column gap-3">
                                                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-4 border">
                                                    <div>
                                                        <span class="text-dark fw-semibold d-block">Layanan Antar (Kurir Desa)</span>
                                                        <span class="text-muted small">Gas diantar ke rumah warga</span>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input toggle-status" style="transform: scale(1.3); cursor: pointer;" type="checkbox" name="gas_delivery_antar_active" {{ isset($region->payment_info['gas_delivery_antar_active']) ? ($region->payment_info['gas_delivery_antar_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-4 border">
                                                    <div>
                                                        <span class="text-dark fw-semibold d-block">Beli di Pangkalan (Ambil Sendiri)</span>
                                                        <span class="text-muted small">Warga datang menukar tabung ke pangkalan</span>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input toggle-status" style="transform: scale(1.3); cursor: pointer;" type="checkbox" name="gas_delivery_jemput_active" {{ isset($region->payment_info['gas_delivery_jemput_active']) ? ($region->payment_info['gas_delivery_jemput_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pasar Detail -->
                                        <div class="tab-pane fade" id="box_delivery_pasar" role="tabpanel" aria-labelledby="v-pills-pasar-tab">
                                            <div class="d-flex align-items-center mb-4">
                                                <i class="bx bx-store fs-2 text-primary me-3"></i>
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-primary">Pengaturan Pasar Daerah</h6>
                                                    <small class="text-muted">Manajemen penyewaan kios/lapak.</small>
                                                </div>
                                            </div>
                                            <div class="alert alert-warning mb-0 border-0 rounded-4">
                                                <i class="bx bx-info-circle me-1"></i> Saat ini Layanan Pasar Daerah tidak membutuhkan pengaturan pengiriman/armada tambahan.
                                            </div>
                                        </div>
                                        
                                        <!-- Fasilitas Umum Detail -->
                                        <div class="tab-pane fade" id="box_delivery_fasilitas" role="tabpanel" aria-labelledby="v-pills-fasilitas-tab">
                                            <div class="d-flex align-items-center mb-4">
                                                <i class="bx bx-buildings fs-2 text-primary me-3"></i>
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-primary">Pengaturan Fasilitas Umum</h6>
                                                    <small class="text-muted">Metode serah terima fasilitas portabel / kendaraan umum.</small>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex flex-column gap-3 mb-4">
                                                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-4 border">
                                                    <div>
                                                        <span class="text-dark fw-semibold d-block">Layanan Antar</span>
                                                        <span class="text-muted small">Fasilitas diantar ke lokasi penyewa</span>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input toggle-status" style="transform: scale(1.3); cursor: pointer;" type="checkbox" name="fasilitas_delivery_antar_active" {{ isset($region->payment_info['fasilitas_delivery_antar_active']) ? ($region->payment_info['fasilitas_delivery_antar_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-4 border">
                                                    <div>
                                                        <span class="text-dark fw-semibold d-block">Ambil / Datang Sendiri</span>
                                                        <span class="text-muted small">Warga datang ke lokasi fasilitas</span>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input toggle-status" style="transform: scale(1.3); cursor: pointer;" type="checkbox" name="fasilitas_delivery_jemput_active" {{ isset($region->payment_info['fasilitas_delivery_jemput_active']) ? ($region->payment_info['fasilitas_delivery_jemput_active'] ? 'checked' : '') : 'checked' }}>
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
"""

# Replace the block
content = re.sub(r'<!-- TAB 2: Pengaturan Pengiriman \(Global\) -->.*?<!-- Save Button -->', tab2_replacement + '\n\t\t\t\t<!-- Save Button -->', content, flags=re.DOTALL)


js_script = """
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
    const form = document.querySelector('form');
    const banner = document.getElementById('unsaved-changes-banner');
    
    if (form && banner) {
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
                label.innerText = el.checked ? 'Tersedia' : 'Tidak Tersedia';
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
            if(label && label.classList.contains('status-label-main')) {
                if(el.checked) {
                    label.innerText = 'Layanan Aktif';
                    label.classList.remove('text-secondary');
                    label.classList.add('text-primary');
                    card.classList.remove('border-secondary', 'bg-light');
                    card.classList.add('border-primary', 'shadow-sm', 'bg-label-primary');
                } else {
                    label.innerText = 'Layanan Nonaktif';
                    label.classList.remove('text-primary');
                    label.classList.add('text-secondary');
                    card.classList.remove('border-primary', 'shadow-sm', 'bg-label-primary');
                    card.classList.add('border-secondary', 'bg-light');
                }
            }
        };
        updateServiceCard(toggle);
        toggle.addEventListener('change', function() { updateServiceCard(this); });
    });
})();
</script>
@endsection
"""

content = re.sub(r'<script>.*?</script>\s*@endsection', js_script, content, flags=re.DOTALL)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Restored successfully")
