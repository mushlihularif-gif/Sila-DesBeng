<!-- Modal Tambah Pengurus Baru -->
<div class="modal fade" id="addPengurusModal" tabindex="-1" aria-labelledby="addPengurusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-white border-bottom px-4 py-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="up-icon-box bg-label-success">
                        <i class="bx bx-user-plus fs-5 text-success"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="addPengurusModalLabel">Tambah Pengurus Gedung</h6>
                        <small class="text-muted">Langsung ditugaskan ke fasilitas setelah disimpan</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="formAddPengurusModal" enctype="multipart/form-data" class="up-form">
                @csrf
                <input type="hidden" name="tipe" value="pengurus_gedung">
                <input type="hidden" name="is_fasilitas_umum" value="1">
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <div class="position-relative d-inline-block">
                            <img src="{{ asset('Admin/img/avatars/pria.png') }}"
                                 id="previewAvatarNewPengurus"
                                 class="rounded-circle border shadow-sm object-fit-cover"
                                 style="width: 72px; height: 72px;"
                                 alt="Avatar Pengurus">
                            <button type="button"
                                    class="btn btn-xs btn-success rounded-circle position-absolute bottom-0 end-0 p-1 d-inline-flex align-items-center justify-content-center"
                                    style="width: 26px; height: 26px;"
                                    onclick="document.getElementById('inputFotoPengurus').click()"
                                    title="Pilih foto profil">
                                <i class="bx bx-camera"></i>
                            </button>
                        </div>
                        <input type="file" id="inputFotoPengurus" name="foto" class="d-none" accept="image/*" onchange="previewNewPengurusAvatar(this)">
                        <small class="d-block text-muted mt-1" style="font-size: 0.74rem;">Foto profil (opsional)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="new_pengurus_nama">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="new_pengurus_nama" class="form-control" placeholder="Nama petugas / pemegang kunci" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="new_pengurus_kontak">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                        <input type="text" name="kontak" id="new_pengurus_kontak" class="form-control" placeholder="Contoh: 081234567890" required>
                    </div>

                    <div>
                        <label class="form-label" for="new_pengurus_status">Status Kesiapan</label>
                        <select name="status" id="new_pengurus_status" class="form-select">
                            <option value="Tersedia" selected>Tersedia (Aktif)</option>
                            <option value="Sedang Bertugas">Sedang Bertugas</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold" id="btnSavePengurus">
                        <i class="bx bx-save me-1"></i>Simpan &amp; Tugaskan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Pengurus -->
<div class="modal fade" id="detailPengurusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-white border-bottom px-4 py-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="up-icon-box bg-label-success">
                        <i class="bx bx-key fs-5 text-success"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0">Profil Pengurus Gedung</h6>
                        <small class="text-muted">Penanggung jawab serah terima kunci fasilitas</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <img src="{{ asset('Admin/img/avatars/pria.png') }}"
                     id="detailPengurusAvatar"
                     alt="Foto Pengurus"
                     class="rounded-circle border border-2 border-success shadow-sm object-fit-cover mb-2.5"
                     style="width: 78px; height: 78px;">
                <h6 class="fw-bold text-dark mb-1" id="detailPengurusNama">Nama Pengurus</h6>
                <div class="mb-3">
                    <span class="badge bg-label-success rounded-pill px-3 py-1" id="detailPengurusStatus">Tersedia</span>
                </div>
                <div class="p-3 rounded-3 bg-light border text-start small mb-3">
                    <div class="d-flex justify-content-between gap-2 mb-1.5">
                        <span class="text-muted">No. WhatsApp / HP</span>
                        <span class="fw-bold text-dark" id="detailPengurusKontak">-</span>
                    </div>
                    <div class="d-flex justify-content-between gap-2">
                        <span class="text-muted">Peran</span>
                        <span class="fw-semibold text-dark">Pengurus &amp; Pemegang Kunci</span>
                    </div>
                </div>
                <a href="#" id="detailPengurusWaBtn" target="_blank" rel="noopener"
                   class="btn btn-success rounded-pill w-100 d-inline-flex align-items-center justify-content-center gap-1">
                    <i class="bx bxl-whatsapp fs-5"></i>Hubungi via WhatsApp
                </a>
            </div>
            <div class="modal-footer bg-light border-top px-4 py-2.5">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
