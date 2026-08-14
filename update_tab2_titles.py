import os

file_path = r"D:\laragon\www\SilaDesBeng\resources\views\admin\region_settings\index.blade.php"

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Sidebar Renames
content = content.replace(
    '<span class="fw-bold d-block">Mobil Desa</span>',
    '<span class="fw-bold d-block">Penyewaan Mobil</span>'
)
content = content.replace(
    '<span class="fw-bold d-block">Alat Berat</span>',
    '<span class="fw-bold d-block">Penyewaan Alat</span>'
)
content = content.replace(
    '<span class="fw-bold d-block">Pangkalan Gas</span>',
    '<span class="fw-bold d-block">Penjualan Gas</span>'
)

# Detail View Header Renames
content = content.replace(
    '<h6 class="fw-bold mb-0 text-primary">Pengaturan Mobil Desa</h6>',
    '<h6 class="fw-bold mb-0 text-primary">Pengaturan Penyewaan Mobil</h6>'
)
content = content.replace(
    '<h6 class="fw-bold mb-0 text-primary">Pengaturan Alat Berat</h6>',
    '<h6 class="fw-bold mb-0 text-primary">Pengaturan Penyewaan Alat</h6>'
)
content = content.replace(
    '<h6 class="fw-bold mb-0 text-primary">Pengaturan Pangkalan Gas</h6>',
    '<h6 class="fw-bold mb-0 text-primary">Pengaturan Penjualan Gas</h6>'
)

# Update Pasar Daerah Delivery View
old_pasar_content = """                                        <!-- Pasar Detail -->
                                        <div class="tab-pane fade" id="box_delivery_pasar" role="tabpanel" aria-labelledby="v-pills-pasar-tab">
                                            <div class="d-flex align-items-center mb-4">
                                                <img src="{{ asset('Admin/img/pasardaerah/PasarDaerah2.png') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-primary">Pengaturan Pasar Daerah</h6>
                                                    <small class="text-muted">Manajemen penyewaan kios/lapak.</small>
                                                </div>
                                            </div>
                                            <div class="alert alert-warning mb-0 border-0 rounded-4">
                                                <i class="bx bx-info-circle me-1"></i> Saat ini Layanan Pasar Daerah tidak membutuhkan pengaturan pengiriman/armada tambahan.
                                            </div>
                                        </div>"""

new_pasar_content = """                                        <!-- Pasar Detail -->
                                        <div class="tab-pane fade" id="box_delivery_pasar" role="tabpanel" aria-labelledby="v-pills-pasar-tab">
                                            <div class="d-flex align-items-center mb-4">
                                                <img src="{{ asset('Admin/img/pasardaerah/PasarDaerah2.png') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-primary">Pengaturan Pasar Daerah</h6>
                                                    <small class="text-muted">Metode pengiriman produk dari toko/penjual ke pembeli.</small>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column gap-3">
                                                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-4 border">
                                                    <div>
                                                        <span class="text-dark fw-semibold d-block">Layanan Antar (Kurir/Armada)</span>
                                                        <span class="text-muted small">Produk diantar ke rumah warga (dengan ongkos kirim otomatis)</span>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input toggle-status" style="transform: scale(1.3); cursor: pointer;" type="checkbox" name="pasar_delivery_antar_active" {{ isset($region->payment_info['pasar_delivery_antar_active']) ? ($region->payment_info['pasar_delivery_antar_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-4 border">
                                                    <div>
                                                        <span class="text-dark fw-semibold d-block">Jemput Sendiri / Pick-up (Gratis)</span>
                                                        <span class="text-muted small">Warga dapat memilih untuk menjemput produk langsung di toko</span>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input toggle-status" style="transform: scale(1.3); cursor: pointer;" type="checkbox" name="pasar_delivery_jemput_active" {{ isset($region->payment_info['pasar_delivery_jemput_active']) ? ($region->payment_info['pasar_delivery_jemput_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>"""

content = content.replace(old_pasar_content, new_pasar_content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updates applied successfully!")
