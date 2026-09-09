@foreach($supirs as $supir)
    <!-- Edit Modal -->
    <div class="modal fade" id="editSupirModal{{ $supir->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold d-flex align-items-center">
                        <i class="bx bx-edit-alt text-primary me-2 fs-4"></i> Edit Data {{ $supir->isPengurusGedung() ? 'Pengurus / Pemegang Kunci Gedung' : 'Supir Kendaraan' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('supir.update', $supir->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tipe" value="{{ $supir->tipe ?? 'supir' }}">
                    <div class="modal-body p-4">
                        <div class="alert alert-primary d-flex align-items-center mb-4 rounded-3 border-0" role="alert">
                            <i class="bx bx-info-circle fs-4 me-3"></i>
                            <div class="small">Ubah informasi profil, kontak, serta ketersediaan untuk <strong>{{ $supir->nama }}</strong> ({{ $supir->isPengurusGedung() ? 'Pengurus Gedung' : 'Supir Kendaraan' }}).</div>
                        </div>

                        <!-- Photo Upload UI Premium -->
                        <div class="d-flex align-items-center mb-4 p-4 bg-label-secondary rounded-4 border-0">
                            <div class="me-4 position-relative">
                                @if($supir->foto)
                                    <img id="preview_edit_{{ $supir->id }}" src="{{ asset('storage/' . $supir->foto) }}" class="rounded-circle object-fit-cover shadow-sm border border-2 border-white" style="width: 85px; height: 85px;">
                                @else
                                    <div id="preview_edit_{{ $supir->id }}" class="rounded-circle bg-white d-flex align-items-center justify-content-center shadow-sm border border-2 border-white text-primary" style="width: 85px; height: 85px;">
                                        <i class="bx bx-user fs-1"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Perbarui Foto Profil</h6>
                                <p class="text-muted mb-2" style="font-size: 0.8rem;">Gunakan rasio 1:1 (persegi). Maksimal 8MB.</p>
                                <div class="d-flex align-items-center gap-2">
                                    <label for="foto_edit_{{ $supir->id }}" class="btn btn-sm btn-primary cursor-pointer shadow-sm">
                                        <i class="bx bx-upload me-1"></i> Ganti Foto...
                                    </label>
                                    <input type="file" name="foto" id="foto_edit_{{ $supir->id }}" class="d-none" accept="image/*" onchange="previewImage(this, 'preview_edit_{{ $supir->id }}', 'filename_edit_{{ $supir->id }}')">
                                    <span id="filename_edit_{{ $supir->id }}" class="text-muted small">Tidak ada file baru</span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="bx bx-user"></i></span>
                                    <input type="text" name="nama" class="form-control" value="{{ $supir->nama }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">No. WhatsApp</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="bx bxl-whatsapp"></i></span>
                                    <input type="text" name="kontak" class="form-control" value="{{ $supir->kontak }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 bg-label-info p-3 rounded-4 border-0">
                            <label class="form-label text-uppercase fw-bold text-info mb-1" style="font-size: 0.75rem;"><i class="bx bx-link-alt me-1"></i>Tautkan Akun Aplikasi (Opsional)</label>
                            <p class="text-info mb-2" style="font-size: 0.75rem;">Notifikasi <em>in-app</em> akan dikirimkan ke akun ini saat bertugas.</p>
                            <select name="user_id" class="form-select bg-white border-info text-dark shadow-none py-2" style="cursor: pointer; font-size: 0.88rem;">
                                <option value="">-- Tidak Ditautkan (Hanya Data Profil) --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ $supir->user_id == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-4">
                            @if($supir->isPengurusGedung())
                                <div class="col-md-7">
                                    <div class="border rounded-4 p-3 h-100 border-gray-200">
                                        <label class="form-label text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem;">Peran Tugas</label>
                                        <div class="alert alert-success border-0 mb-0 py-2 px-3 rounded-3">
                                            <div class="fw-bold font-13"><i class="bx bx-building-house me-1"></i> Pengurus & Pemegang Kunci</div>
                                            <small class="text-muted d-block mt-0.5">Dapat ditugaskan pada gedung serbaguna, balai pertemuan, dan ruang publik.</small>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="col-md-7">
                                    <div class="border rounded-4 p-3 h-100 border-gray-200">
                                        <label class="form-label text-uppercase text-muted fw-bold mb-3" style="font-size: 0.75rem;">Layanan Aktif <span class="text-danger">*</span></label>
                                        <div class="d-flex flex-column gap-3">
                                            <div class="form-check form-switch d-flex align-items-center">
                                                <input class="form-check-input mt-0 me-3 cursor-pointer" type="checkbox" name="is_sewa_mobil" value="1" id="is_rental_edit_{{ $supir->id }}" style="width: 2.5em; height: 1.25em;" {{ $supir->is_sewa_mobil ? 'checked' : '' }}>
                                                <label class="form-check-label cursor-pointer fw-bold text-dark" for="is_rental_edit_{{ $supir->id }}">Sewa Mobil (Rental)</label>
                                            </div>
                                            <div class="form-check form-switch d-flex align-items-center">
                                                <input class="form-check-input mt-0 me-3 cursor-pointer bg-danger border-danger" type="checkbox" name="is_fasilitas_umum" value="1" id="is_fasilitas_edit_{{ $supir->id }}" style="width: 2.5em; height: 1.25em;" {{ $supir->is_fasilitas_umum ? 'checked' : '' }}>
                                                <label class="form-check-label cursor-pointer fw-bold text-danger" for="is_fasilitas_edit_{{ $supir->id }}">Ambulans & Kendaraan</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-5">
                                <div class="border rounded-4 p-3 h-100 border-gray-200">
                                    <label class="form-label text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem;">Status Personil <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select mb-2 shadow-none" required>
                                        <option value="Tersedia" {{ $supir->status == 'Tersedia' ? 'selected' : '' }}>Tersedia (Aktif)</option>
                                        <option value="Sedang Bertugas" {{ $supir->status == 'Sedang Bertugas' ? 'selected' : '' }}>Sedang Bertugas</option>
                                        <option value="Tidak Aktif" {{ $supir->status == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif (Cuti/Sakit)</option>
                                    </select>
                                    <small class="text-muted d-block" style="font-size:0.7rem;">Ubah status jika personil sedang berhalangan hadir.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top px-4 py-3 rounded-bottom-4">
                        <button type="button" class="btn btn-label-secondary fw-bold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold shadow-sm"><i class="bx bx-save me-2"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailSupirModal{{ $supir->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 pb-0 position-absolute end-0 top-0 z-3">
                    <button type="button" class="btn-close bg-white p-2 shadow-sm rounded-circle m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="text-center pt-5 pb-4 px-4 bg-label-{{ $supir->isPengurusGedung() ? 'success' : 'primary' }}">
                    <div class="position-relative d-inline-block mb-3">
                        @if($supir->foto)
                            <img src="{{ asset('storage/' . $supir->foto) }}" alt="Avatar" class="rounded-circle object-fit-cover shadow border border-3 border-white" style="width: 100px; height: 100px;">
                        @else
                            <div class="rounded-circle bg-white text-{{ $supir->isPengurusGedung() ? 'success' : 'primary' }} shadow border border-3 border-white d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="bx {{ $supir->isPengurusGedung() ? 'bx-building-house' : 'bx-user' }} fs-1"></i>
                            </div>
                        @endif
                        <span class="position-absolute {{ $supir->status == 'Tersedia' ? 'bg-success' : 'bg-warning' }} border border-2 border-white rounded-circle" style="width: 16px; height: 16px; bottom: 2px; right: 2px;"></span>
                    </div>
                    <h5 class="fw-bold mb-1 text-dark">{{ $supir->nama }}</h5>
                    <span class="badge bg-white text-dark shadow-xs rounded-pill px-3 py-1 font-12 fw-semibold">
                        {{ $supir->isPengurusGedung() ? 'Pengurus / Pemegang Kunci Gedung' : 'Supir Kendaraan' }}
                    </span>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3">
                            <div class="d-flex align-items-center">
                                <i class="bx bxl-whatsapp fs-4 text-success me-3"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Nomor WhatsApp</small>
                                    <span class="fw-bold text-dark">{{ $supir->kontak ?? '-' }}</span>
                                </div>
                            </div>
                            @if($supir->kontak)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supir->kontak) }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3">
                                    <i class="bx bxl-whatsapp me-1"></i> Chat
                                </a>
                            @endif
                        </div>

                        <div class="d-flex align-items-center p-3 bg-light rounded-3">
                            <i class="bx bx-check-shield fs-4 text-primary me-3"></i>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.72rem;">Status Kesiagaan</small>
                                <span class="fw-bold text-dark">{{ $supir->status }}</span>
                            </div>
                        </div>

                        @if($supir->isPengurusGedung())
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block mb-1" style="font-size: 0.72rem;">Gedung & Fasilitas yang Dikelola</small>
                                @if($supir->fasilitas && $supir->fasilitas->count() > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($supir->fasilitas as $f)
                                            <span class="badge bg-label-success px-2.5 py-1">{{ $f->nama_fasilitas }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted fst-italic small">Belum ada gedung yang ditugaskan secara spesifik.</span>
                                @endif
                            </div>
                        @else
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block mb-1" style="font-size: 0.72rem;">Layanan Kendaraan Aktif</small>
                                <div class="d-flex flex-wrap gap-1">
                                    @if($supir->is_sewa_mobil)
                                        <span class="badge bg-label-primary px-2.5 py-1"><i class="bx bx-car me-1"></i> Rental Mobil</span>
                                    @endif
                                    @if($supir->is_fasilitas_umum)
                                        <span class="badge bg-label-danger px-2.5 py-1"><i class="bx bx-plus-medical me-1"></i> Ambulans & Kendaraan</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer bg-light border-top px-4 py-3">
                    <button type="button" class="btn btn-secondary w-100 rounded-pill fw-semibold" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
