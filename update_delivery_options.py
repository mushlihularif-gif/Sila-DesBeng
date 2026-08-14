import os

file_path = r"D:\laragon\www\SilaDesBeng\resources\views\admin\region_settings\index.blade.php"

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Update Sidebar Mobil Subtitle
content = content.replace(
    '<small class="text-muted" style="font-size: 0.75rem;">BBM & Supir</small>',
    '<small class="text-muted" style="font-size: 0.75rem;">Serah Terima, BBM & Supir</small>'
)

# Update Mobil Content Pane
old_mobil_content = """                                        <!-- Mobil Detail -->
                                        <div class="tab-pane fade show active" id="box_delivery_mobil" role="tabpanel" aria-labelledby="v-pills-mobil-tab">
                                            <div class="d-flex align-items-center mb-4">
                                                <img src="{{ asset('User/img/elemen/mobil.png') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-primary">Pengaturan Penyewaan Mobil</h6>
                                                    <small class="text-muted">Atur kebijakan bahan bakar dan penyediaan supir.</small>
                                                </div>
                                            </div>
                                            <div class="row g-4">"""

new_mobil_content = """                                        <!-- Mobil Detail -->
                                        <div class="tab-pane fade show active" id="box_delivery_mobil" role="tabpanel" aria-labelledby="v-pills-mobil-tab">
                                            <div class="d-flex align-items-center mb-4">
                                                <img src="{{ asset('User/img/elemen/mobil.png') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-primary">Pengaturan Penyewaan Mobil</h6>
                                                    <small class="text-muted">Atur metode penyerahan armada, bahan bakar, dan ketersediaan supir.</small>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column gap-3 mb-4">
                                                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-4 border">
                                                    <div>
                                                        <span class="text-dark fw-semibold d-block">Layanan Antar (Diantar Petugas)</span>
                                                        <span class="text-muted small">Mobil desa diantarkan langsung ke titik lokasi penyewa</span>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input toggle-status" style="transform: scale(1.3); cursor: pointer;" type="checkbox" name="mobil_delivery_antar_active" {{ isset($region->payment_info['mobil_delivery_antar_active']) ? ($region->payment_info['mobil_delivery_antar_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-4 border">
                                                    <div>
                                                        <span class="text-dark fw-semibold d-block">Ambil / Jemput Sendiri</span>
                                                        <span class="text-muted small">Penyewa datang menjemput mobil di kantor atau garasi desa</span>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input toggle-status" style="transform: scale(1.3); cursor: pointer;" type="checkbox" name="mobil_delivery_jemput_active" {{ isset($region->payment_info['mobil_delivery_jemput_active']) ? ($region->payment_info['mobil_delivery_jemput_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row g-4 border-top pt-4">"""

content = content.replace(old_mobil_content, new_mobil_content)

# Update Fasilitas Sidebar Subtitle
content = content.replace(
    '<small class="text-muted" style="font-size: 0.75rem;">Pengambilan Barang</small>',
    '<small class="text-muted" style="font-size: 0.75rem;">Metode Penggunaan / Serah Terima</small>'
)

# Update Fasilitas Content Pane
old_fasilitas_content = """                                        <!-- Fasilitas Umum Detail -->
                                        <div class="tab-pane fade" id="box_delivery_fasilitas" role="tabpanel" aria-labelledby="v-pills-fasilitas-tab">
                                            <div class="d-flex align-items-center mb-4">
                                                <img src="{{ asset('User/img/elemen/fasilitas.png') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">
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
                                            </div>"""

new_fasilitas_content = """                                        <!-- Fasilitas Umum Detail -->
                                        <div class="tab-pane fade" id="box_delivery_fasilitas" role="tabpanel" aria-labelledby="v-pills-fasilitas-tab">
                                            <div class="d-flex align-items-center mb-4">
                                                <img src="{{ asset('User/img/elemen/fasilitas.png') }}" class="me-3" style="width: 32px; height: 32px; object-fit: contain;">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-primary">Pengaturan Fasilitas Umum</h6>
                                                    <small class="text-muted">Metode penggunaan fasilitas (Gedung, Ambulans, Lapangan, dll).</small>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex flex-column gap-3 mb-4">
                                                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-4 border">
                                                    <div>
                                                        <span class="text-dark fw-semibold d-block">Layanan Kunjungan / Antar / Panggilan</span>
                                                        <span class="text-muted small">Fasilitas (seperti ambulans, kursi/tenda) atau petugas datang ke titik lokasi warga</span>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input toggle-status" style="transform: scale(1.3); cursor: pointer;" type="checkbox" name="fasilitas_delivery_antar_active" {{ isset($region->payment_info['fasilitas_delivery_antar_active']) ? ($region->payment_info['fasilitas_delivery_antar_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-4 border">
                                                    <div>
                                                        <span class="text-dark fw-semibold d-block">Gunakan di Tempat / Ambil Sendiri</span>
                                                        <span class="text-muted small">Warga mendatangi lokasi fasilitas (gedung, balai) atau mengambil sendiri barang</span>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input toggle-status" style="transform: scale(1.3); cursor: pointer;" type="checkbox" name="fasilitas_delivery_jemput_active" {{ isset($region->payment_info['fasilitas_delivery_jemput_active']) ? ($region->payment_info['fasilitas_delivery_jemput_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                            </div>"""

content = content.replace(old_fasilitas_content, new_fasilitas_content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updates applied successfully!")
