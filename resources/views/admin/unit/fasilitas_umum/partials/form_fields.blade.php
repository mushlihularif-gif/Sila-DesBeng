@php
    $isEdit = isset($fasilitas) && $fasilitas && ($fasilitas->exists || !empty($fasilitas->id));
    $selectedKategori = old('kategori', $isEdit ? $fasilitas->kategori : '');
    $selectedSatuan = old('satuan', $isEdit ? ($fasilitas->satuan ?: 'Unit') : 'Unit');
    $defaultSatuans = ['Unit', 'Gedung', 'Ruang', 'Lapangan', 'Paket'];
    if ($selectedSatuan && !in_array($selectedSatuan, $defaultSatuans, true)) {
        $defaultSatuans[] = $selectedSatuan;
    }

    $statusBiaya = old('status_biaya', $isEdit ? ($fasilitas->status_biaya ?? 'gratis') : 'gratis');
    $hargaSewaVal = old('harga_sewa', ($isEdit && !empty($fasilitas->harga_sewa)) ? number_format((float) $fasilitas->harga_sewa, 0, ',', '.') : '');
    $currentStatus = old('status', $isEdit ? ($fasilitas->status ?: 'Tersedia') : 'Tersedia');
    $assignedPengurusIds = old(
        'pengurus_ids',
        ($isEdit && $fasilitas->pengurus) ? $fasilitas->pengurus->pluck('id')->toArray() : []
    );
@endphp

<div class="row g-4">
    {{-- Kolom Kiri --}}
    <div class="col-12 col-lg-8">
        {{-- 1. Informasi Dasar Fasilitas --}}
        <div class="card up-card mb-4">
            <div class="up-card-header d-flex align-items-center gap-2.5">
                <span class="up-step-num bg-label-primary text-primary">1</span>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Informasi Dasar Fasilitas</h6>
                    <small class="text-muted">Nama gedung atau aset publik, kategori, kapasitas, dan deskripsi</small>
                </div>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-12 col-md-7">
                        <label class="form-label" for="nama_fasilitas">
                            Nama Fasilitas <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('nama_fasilitas') is-invalid @enderror"
                               id="nama_fasilitas"
                               name="nama_fasilitas"
                               value="{{ old('nama_fasilitas', $isEdit ? $fasilitas->nama_fasilitas : '') }}"
                               placeholder="Contoh: Balai Pertemuan Desa / Lapangan Futsal"
                               required />
                        @error('nama_fasilitas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-5">
                        <label class="form-label" for="kategori">
                            Kategori <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <select class="form-select @error('kategori') is-invalid @enderror" id="kategori" name="kategori" required>
                                <option value="" disabled @selected(!$selectedKategori)>Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->name }}" @selected($selectedKategori == $category->name)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                                @if($selectedKategori && !$categories->contains('name', $selectedKategori))
                                    <option value="{{ $selectedKategori }}" selected>{{ $selectedKategori }}</option>
                                @endif
                            </select>
                            <button type="button" class="btn btn-outline-primary px-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal" title="Tambah kategori baru">
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                        @error('kategori')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label" for="stok">
                            Jumlah / Kapasitas Unit <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               class="form-control @error('stok') is-invalid @enderror"
                               id="stok"
                               name="stok"
                               value="{{ old('stok', $isEdit ? $fasilitas->stok : 1) }}"
                               placeholder="1"
                               min="0"
                               required />
                        @error('stok')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label" for="satuan">
                            Satuan <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <select class="form-select" id="satuan" name="satuan" required>
                                @foreach($defaultSatuans as $sat)
                                    <option value="{{ $sat }}" @selected($selectedSatuan == $sat)>{{ $sat }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary px-3" data-bs-toggle="modal" data-bs-target="#addSatuanModal" title="Tambah satuan baru">
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="deskripsi">
                            Deskripsi &amp; Daya Tampung <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror"
                                  id="deskripsi"
                                  name="deskripsi"
                                  rows="4"
                                  placeholder="Jelaskan daya tampung, perlengkapan ruangan (AC/kursi/sound), dan aturan pemakaian..."
                                  required>{{ old('deskripsi', $isEdit ? $fasilitas->deskripsi : '') }}</textarea>
                        @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Skema Pembiayaan --}}
        @php
            $isPaidSelection = in_array($statusBiaya, ['berbayar', 'berbayar_semua'], true);
        @endphp
        <div class="card up-card mb-4">
            <div class="up-card-header d-flex align-items-center gap-2.5">
                <span class="up-step-num bg-label-success text-success">2</span>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Skema Pembiayaan Peminjaman</h6>
                    <small class="text-muted">Tentukan apakah semua acara gratis, hanya acara pribadi yang berbayar, atau semua acara berbayar</small>
                </div>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="status_biaya">
                            Status Pembiayaan <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('status_biaya') is-invalid @enderror"
                                id="status_biaya"
                                name="status_biaya"
                                required
                                onchange="toggleHargaSewa()">
                            <option value="gratis" @selected($statusBiaya === 'gratis')>Gratis Sepenuhnya (Acara Sosial &amp; Pribadi Gratis)</option>
                            <option value="berbayar" @selected($statusBiaya === 'berbayar')>Sosial Gratis, Komersial / Pribadi Berbayar</option>
                            <option value="berbayar_semua" @selected($statusBiaya === 'berbayar_semua')>Semua Acara Berbayar (Acara Sosial &amp; Pribadi Berbayar)</option>
                        </select>
                        @error('status_biaya')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div id="status_biaya_hint" class="small text-muted mt-2"></div>
                    </div>

                    <div class="col-12 col-md-6" id="harga_sewa_container" style="{{ $isPaidSelection ? '' : 'display: none;' }}">
                        <label class="form-label" for="harga_sewa" id="harga_sewa_label">
                            {{ $statusBiaya === 'berbayar_semua' ? 'Tarif Sewa Semua Acara (Per Hari)' : 'Tarif Acara Komersial / Pribadi (Per Hari)' }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text"
                                   class="form-control fw-semibold format-rupiah @error('harga_sewa') is-invalid @enderror"
                                   id="harga_sewa"
                                   name="harga_sewa"
                                   value="{{ $hargaSewaVal }}"
                                   placeholder="500.000"
                                   inputmode="numeric"
                                   @required($isPaidSelection)
                                   oninput="formatRupiah(this); clearHargaSewaError();" />
                        </div>
                        <small id="harga_sewa_helper" class="text-muted d-block mt-1">
                            {{ $statusBiaya === 'berbayar_semua'
                                ? 'Wajib diisi (> Rp 0). Ditagihkan untuk acara Sosial/Masyarakat maupun Komersial/Pribadi.'
                                : 'Wajib diisi (> Rp 0). Hanya ditagihkan jika warga memilih kategori Acara Komersial / Pribadi.' }}
                        </small>
                        <div id="harga_sewa_client_error" class="text-danger small fw-semibold mt-1" style="display: none;">
                            Tarif sewa per hari wajib diisi (lebih dari Rp 0) jika memilih skema berbayar.
                        </div>
                        @error('harga_sewa')<small class="text-danger d-block mt-1 fw-semibold">{{ $message }}</small>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Foto Fasilitas --}}
        <div class="card up-card mb-4">
            <div class="up-card-header d-flex align-items-center gap-2.5">
                <span class="up-step-num bg-label-info text-info">3</span>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Foto Fasilitas</h6>
                    <small class="text-muted">Unggah tampak depan dan bagian dalam gedung atau fasilitas (opsional, maks. 8MB)</small>
                </div>
            </div>
            <div class="card-body p-3 p-md-4">
                @include('admin.unit.partials.photo_upload_slots', [
                    'item' => $isEdit ? $fasilitas : null,
                    'primaryName' => 'foto_utama',
                    'primaryRequired' => false,
                    'cropRatioExpr' => '4 / 3',
                    'altPrefix' => $isEdit ? $fasilitas->nama_fasilitas : 'Foto Fasilitas Umum',
                ])
            </div>
        </div>

        {{-- 4. Lokasi Fasilitas --}}
        <div class="card up-card mb-4">
            <div class="up-card-header d-flex align-items-center gap-2.5">
                <span class="up-step-num bg-label-warning text-warning">4</span>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Lokasi Fasilitas</h6>
                    <small class="text-muted">Pilih lokasi wilayah tersimpan atau tulis alamat fasilitas</small>
                </div>
            </div>
            <div class="card-body p-3 p-md-4">
                @include('admin.unit.partials.location_selector', [
                    'savedLocations' => $savedLocations,
                    'lokasiValue' => $isEdit ? $fasilitas->lokasi : '',
                    'latValue' => $isEdit ? $fasilitas->latitude : '',
                    'lngValue' => $isEdit ? $fasilitas->longitude : '',
                    'placeholderText' => 'Contoh: Jl. Utama Desa, Sebelah Kantor Desa',
                ])
            </div>
        </div>

        {{-- 5. Pengurus & Pemegang Kunci --}}
        <div class="card up-card">
            <div class="up-card-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="up-step-num bg-label-success text-success">5</span>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Pengurus &amp; Pemegang Kunci <span class="text-muted fw-normal small">(Opsional)</span></h6>
                        <small class="text-muted">Pilih petugas yang dihubungi warga untuk serah terima kunci fasilitas</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-label-success rounded-pill px-2.5 py-1.5 fw-semibold" id="selectedPengurusCount">0 Dipilih</span>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addPengurusModal">
                        <i class="bx bx-plus me-1"></i>Tambah Pengurus
                    </button>
                </div>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="p-2.5 rounded-3 bg-light border text-muted small mb-3">
                    <i class="bx bx-info-circle text-success me-1"></i>Nomor WhatsApp pengurus yang dipilih akan ditampilkan kepada pemohon. Kosongkan jika kunci dikelola langsung oleh kantor desa.
                </div>

                @if($pengurusList->count() > 0)
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text bg-light"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" id="searchPengurusInput" class="form-control" placeholder="Cari pengurus berdasarkan nama atau nomor WhatsApp...">
                    </div>
                @endif

                <div class="row g-3" id="pengurusCardsContainer">
                    @forelse($pengurusList as $p)
                        @php
                            $avatar = $p->foto ? asset('storage/' . $p->foto) : asset('Admin/img/avatars/pria.png');
                            $isTersedia = ($p->status === 'Tersedia');
                            $isSelected = in_array($p->id, $assignedPengurusIds);
                        @endphp
                        <div class="col-12 col-md-6 pengurus-card-item" data-name="{{ strtolower($p->nama) }}" data-phone="{{ $p->kontak }}">
                            <div class="card pengurus-profile-card h-100 {{ $isSelected ? 'selected' : '' }}"
                                 id="card-pengurus-{{ $p->id }}"
                                 onclick="togglePengurusSelection({{ $p->id }})">
                                <input type="checkbox"
                                       name="pengurus_ids[]"
                                       value="{{ $p->id }}"
                                       id="check-pengurus-{{ $p->id }}"
                                       class="d-none pengurus-checkbox"
                                       @checked($isSelected)
                                       onchange="syncPengurusSelection({{ $p->id }})">

                                <div class="d-flex align-items-center gap-2.5 p-3 pb-2">
                                    <div class="position-relative flex-shrink-0" style="width: 42px; height: 42px;">
                                        <img src="{{ $avatar }}"
                                             alt="{{ $p->nama }}"
                                             class="rounded-circle border object-fit-cover w-100 h-100"
                                             onerror="this.src='{{ asset('Admin/img/avatars/pria.png') }}'">
                                        <span class="position-absolute {{ $isTersedia ? 'bg-success' : 'bg-warning' }} border border-2 border-white rounded-circle" style="width: 10px; height: 10px; bottom: 0; right: 0;"></span>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center justify-content-between gap-1 mb-0.5">
                                            <span class="fw-bold text-dark small text-truncate" title="{{ $p->nama }}">{{ $p->nama }}</span>
                                            <span class="badge {{ $isTersedia ? 'bg-label-success' : 'bg-label-warning' }} rounded-pill px-2 py-0.5 flex-shrink-0" style="font-size: 0.65rem;">
                                                {{ $p->status }}
                                            </span>
                                        </div>
                                        <small class="text-muted d-block text-truncate">
                                            <i class="bx bxl-whatsapp text-success me-0.5"></i>{{ $p->kontak ?: 'Tanpa kontak' }}
                                        </small>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top bg-light bg-opacity-50 rounded-bottom-3">
                                    <button type="button"
                                            class="btn btn-xs btn-outline-secondary rounded-pill px-2.5"
                                            onclick="event.stopPropagation(); showPengurusDetailModal({{ $p->id }}, {{ Js::from($p->nama) }}, {{ Js::from($avatar) }}, {{ Js::from($p->kontak ?? '-') }}, {{ Js::from($p->status) }})">
                                        <i class="bx bx-user me-0.5"></i>Detail
                                    </button>
                                    <span class="selection-btn-pengurus btn btn-xs {{ $isSelected ? 'btn-success' : 'btn-outline-success' }} rounded-pill px-3 fw-semibold">
                                        @if($isSelected)
                                            <i class="bx bx-check-circle me-0.5"></i>Ditugaskan
                                        @else
                                            <i class="bx bx-plus me-0.5"></i>Pilih
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12" id="emptyPengurusAlert">
                            <div class="p-4 bg-light rounded-3 border text-center">
                                <i class="bx bx-user-x text-muted fs-1 d-block mb-1"></i>
                                <p class="fw-bold text-dark small mb-1">Belum Ada Pengurus Gedung Terdaftar</p>
                                <small class="text-muted d-block mb-3">Tambahkan pengurus kunci jika fasilitas memiliki penanggung jawab khusus.</small>
                                <button type="button" class="btn btn-sm btn-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addPengurusModal">
                                    <i class="bx bx-plus me-1"></i>Tambah Pengurus Baru
                                </button>
                            </div>
                        </div>
                    @endforelse
                    <div class="col-12" id="noMatchPengurusAlert" style="display: none;">
                        <div class="p-3 bg-light rounded-3 border text-center text-muted small">
                            Tidak ditemukan pengurus yang cocok dengan kata kunci pencarian.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan Sticky --}}
    <div class="col-12 col-lg-4">
        <div class="up-sticky">
            <div class="card up-card mb-4">
                <div class="up-card-header d-flex align-items-center gap-2.5">
                    <div class="up-icon-box bg-label-primary">
                        <i class="bx bx-slider-alt fs-5 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Status Katalog</h6>
                        <small class="text-muted">Atur ketersediaan peminjaman fasilitas</small>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <label class="form-label" for="status">Status Fasilitas <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="Tersedia" @selected(strtolower($currentStatus) === 'tersedia')>Tersedia — Dapat Dipinjam</option>
                        <option value="Disewa" @selected(strtolower($currentStatus) === 'disewa')>Sedang Dipakai / Disewa</option>
                        <option value="Tidak Tersedia" @selected(strtolower($currentStatus) === 'tidak tersedia')>Tidak Tersedia / Perbaikan</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card up-card mb-4">
                <div class="card-body p-3 p-md-4">
                    <button type="submit" id="btnSubmitFasilitas" class="btn btn-primary rounded-pill w-100 py-2.5 fw-semibold shadow-sm mb-2">
                        <i class="bx bx-save me-1"></i>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Fasilitas' }}
                    </button>
                    <a href="{{ $isEdit ? route('admin.unit.fasilitas_umum.show', $fasilitas->id) : route('admin.unit.fasilitas_umum.index', ['tab' => 'gedung']) }}"
                       class="btn btn-outline-secondary rounded-pill w-100">
                        Batal
                    </a>
                </div>
            </div>

            <div class="card border-0 bg-label-success rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bx bx-key fs-5 text-success mt-0.5 flex-shrink-0"></i>
                        <div class="small text-success" style="line-height: 1.55;">
                            <strong class="d-block mb-1">Koordinasi Kunci Mudah</strong>
                            Pengurus yang ditugaskan otomatis tampil pada halaman detail agar warga tahu siapa yang dihubungi saat serah terima kunci.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function clearHargaSewaError() {
        const input = document.getElementById('harga_sewa');
        const errEl = document.getElementById('harga_sewa_client_error');
        if (input) input.classList.remove('is-invalid');
        if (errEl) errEl.style.display = 'none';
    }

    function toggleHargaSewa() {
        const statusBiaya = document.getElementById('status_biaya')?.value;
        const container = document.getElementById('harga_sewa_container');
        const input = document.getElementById('harga_sewa');
        const label = document.getElementById('harga_sewa_label');
        const helper = document.getElementById('harga_sewa_helper');
        const hint = document.getElementById('status_biaya_hint');
        if (!container || !input) return;

        clearHargaSewaError();

        if (statusBiaya === 'berbayar' || statusBiaya === 'berbayar_semua') {
            container.style.display = '';
            input.required = true;
            if (statusBiaya === 'berbayar_semua') {
                if (label) label.innerHTML = 'Tarif Sewa Semua Acara (Per Hari) <span class="text-danger">*</span>';
                if (helper) helper.textContent = 'Wajib diisi (> Rp 0). Ditagihkan untuk acara Sosial/Masyarakat maupun Komersial/Pribadi.';
                if (hint) hint.textContent = 'Semua kategori acara (sosial dan pribadi) akan dikenakan biaya sesuai tarif harian.';
            } else {
                if (label) label.innerHTML = 'Tarif Acara Komersial / Pribadi (Per Hari) <span class="text-danger">*</span>';
                if (helper) helper.textContent = 'Wajib diisi (> Rp 0). Hanya ditagihkan jika warga memilih kategori Acara Komersial / Pribadi.';
                if (hint) hint.textContent = 'Kegiatan sosial/masyarakat gratis; hanya acara komersial/pribadi yang dikenakan tarif harian.';
            }
        } else {
            container.style.display = 'none';
            input.required = false;
            input.value = '';
            if (hint) hint.textContent = 'Seluruh peminjaman (acara sosial maupun pribadi) tidak dipungut biaya (Gratis).';
        }
    }

    function togglePengurusSelection(pengurusId) {
        const checkbox = document.getElementById('check-pengurus-' + pengurusId);
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            syncPengurusSelection(pengurusId);
        }
    }

    function syncPengurusSelection(pengurusId) {
        const card = document.getElementById('card-pengurus-' + pengurusId);
        const checkbox = document.getElementById('check-pengurus-' + pengurusId);
        if (!card || !checkbox) return;

        const selBtn = card.querySelector('.selection-btn-pengurus');
        card.classList.toggle('selected', checkbox.checked);
        if (selBtn) {
            if (checkbox.checked) {
                selBtn.className = 'selection-btn-pengurus btn btn-xs btn-success rounded-pill px-3 fw-semibold';
                selBtn.innerHTML = '<i class="bx bx-check-circle me-0.5"></i>Ditugaskan';
            } else {
                selBtn.className = 'selection-btn-pengurus btn btn-xs btn-outline-success rounded-pill px-3 fw-semibold';
                selBtn.innerHTML = '<i class="bx bx-plus me-0.5"></i>Pilih';
            }
        }
        updateAssignedPengurusCount();
    }

    function updateAssignedPengurusCount() {
        const checked = document.querySelectorAll('.pengurus-checkbox:checked').length;
        const badge = document.getElementById('selectedPengurusCount');
        if (badge) badge.textContent = checked + ' Dipilih';
    }

    function previewNewPengurusAvatar(input) {
        if (!input || !input.files || !input.files[0]) return;
        if (typeof initGlobalCropper === 'function') {
            const started = initGlobalCropper(input, 'previewAvatarNewPengurus', 1, true);
            if (started !== false) return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.getElementById('previewAvatarNewPengurus');
            if (img) img.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }

    function showPengurusDetailModal(id, nama, avatar, kontak, status) {
        const avatarEl = document.getElementById('detailPengurusAvatar');
        const namaEl = document.getElementById('detailPengurusNama');
        const statusEl = document.getElementById('detailPengurusStatus');
        const kontakEl = document.getElementById('detailPengurusKontak');
        const waBtn = document.getElementById('detailPengurusWaBtn');

        if (avatarEl) avatarEl.src = avatar;
        if (namaEl) namaEl.textContent = nama;
        if (statusEl) {
            statusEl.textContent = status;
            statusEl.className = 'badge rounded-pill px-3 py-1 ' + (status === 'Tersedia' ? 'bg-label-success' : 'bg-label-warning');
        }
        if (kontakEl) kontakEl.textContent = kontak || '-';

        const cleanWa = String(kontak || '').replace(/[^0-9]/g, '');
        if (waBtn) {
            if (cleanWa) {
                waBtn.href = 'https://wa.me/' + (cleanWa.startsWith('0') ? '62' + cleanWa.substring(1) : cleanWa);
                waBtn.classList.remove('disabled');
            } else {
                waBtn.href = '#';
                waBtn.classList.add('disabled');
            }
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('detailPengurusModal')).show();
    }

    function escapeHtmlAttr(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateAssignedPengurusCount();
        toggleHargaSewa();

        const formFasilitas = document.getElementById('formFasilitasUmum');
        if (formFasilitas) {
            formFasilitas.addEventListener('submit', function (e) {
                const statusBiaya = document.getElementById('status_biaya')?.value;
                const inputHarga = document.getElementById('harga_sewa');
                const errEl = document.getElementById('harga_sewa_client_error');
                if (statusBiaya === 'berbayar' || statusBiaya === 'berbayar_semua') {
                    const numericVal = parseInt(String(inputHarga?.value || '').replace(/[^0-9]/g, ''), 10) || 0;
                    if (numericVal <= 0) {
                        e.preventDefault();
                        if (inputHarga) {
                            inputHarga.classList.add('is-invalid');
                            inputHarga.focus();
                            inputHarga.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        if (errEl) errEl.style.display = 'block';
                    }
                }
            });
        }

        const searchInput = document.getElementById('searchPengurusInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const q = this.value.toLowerCase().trim();
                const items = document.querySelectorAll('.pengurus-card-item');
                let visible = 0;
                items.forEach(function (item) {
                    const match = (item.dataset.name || '').includes(q) || (item.dataset.phone || '').includes(q);
                    item.style.display = match ? '' : 'none';
                    if (match) visible++;
                });
                const noMatch = document.getElementById('noMatchPengurusAlert');
                if (noMatch) noMatch.style.display = (visible === 0 && items.length > 0) ? '' : 'none';
            });
        }

        const formAddPengurus = document.getElementById('formAddPengurusModal');
        if (formAddPengurus) {
            formAddPengurus.addEventListener('submit', function (e) {
                e.preventDefault();
                const btn = document.getElementById('btnSavePengurus');
                const originalHtml = btn ? btn.innerHTML : '';
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
                }

                fetch("{{ route('supir.store') }}", {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: new FormData(this)
                })
                .then(res => res.json())
                .then(data => {
                    if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }

                    if (data.success && data.supir) {
                        const supir = data.supir;
                        const avatar = data.avatar_url || '{{ asset("Admin/img/avatars/pria.png") }}';
                        const isTersedia = supir.status === 'Tersedia';
                        const emptyAlert = document.getElementById('emptyPengurusAlert');
                        if (emptyAlert) emptyAlert.style.display = 'none';

                        const container = document.getElementById('pengurusCardsContainer');
                        const col = document.createElement('div');
                        col.className = 'col-12 col-md-6 pengurus-card-item';
                        col.dataset.name = String(supir.nama || '').toLowerCase();
                        col.dataset.phone = String(supir.kontak || '');

                        const safeNama = escapeHtmlAttr(supir.nama);
                        const safeKontak = escapeHtmlAttr(supir.kontak || 'Tanpa kontak');
                        const safeStatus = escapeHtmlAttr(supir.status);
                        const safeAvatar = escapeHtmlAttr(avatar);

                        col.innerHTML = `
                            <div class="card pengurus-profile-card h-100 selected" id="card-pengurus-${supir.id}" onclick="togglePengurusSelection(${supir.id})">
                                <input type="checkbox" name="pengurus_ids[]" value="${supir.id}" id="check-pengurus-${supir.id}" class="d-none pengurus-checkbox" checked onchange="syncPengurusSelection(${supir.id})">
                                <div class="d-flex align-items-center gap-2.5 p-3 pb-2">
                                    <div class="position-relative flex-shrink-0" style="width: 42px; height: 42px;">
                                        <img src="${safeAvatar}" alt="${safeNama}" class="rounded-circle border object-fit-cover w-100 h-100" onerror="this.src='{{ asset('Admin/img/avatars/pria.png') }}'">
                                        <span class="position-absolute ${isTersedia ? 'bg-success' : 'bg-warning'} border border-2 border-white rounded-circle" style="width: 10px; height: 10px; bottom: 0; right: 0;"></span>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center justify-content-between gap-1 mb-0.5">
                                            <span class="fw-bold text-dark small text-truncate">${safeNama}</span>
                                            <span class="badge ${isTersedia ? 'bg-label-success' : 'bg-label-warning'} rounded-pill px-2 py-0.5 flex-shrink-0" style="font-size: 0.65rem;">${safeStatus}</span>
                                        </div>
                                        <small class="text-muted d-block text-truncate"><i class="bx bxl-whatsapp text-success me-0.5"></i>${safeKontak}</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top bg-light bg-opacity-50 rounded-bottom-3">
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 js-detail-new-pengurus">
                                        <i class="bx bx-user me-0.5"></i>Detail
                                    </button>
                                    <span class="selection-btn-pengurus btn btn-xs btn-success rounded-pill px-3 fw-semibold">
                                        <i class="bx bx-check-circle me-0.5"></i>Ditugaskan
                                    </span>
                                </div>
                            </div>
                        `;

                        col.querySelector('.js-detail-new-pengurus')?.addEventListener('click', function (ev) {
                            ev.stopPropagation();
                            showPengurusDetailModal(supir.id, supir.nama, avatar, supir.kontak || '-', supir.status);
                        });

                        container?.appendChild(col);
                        updateAssignedPengurusCount();

                        bootstrap.Modal.getOrCreateInstance(document.getElementById('addPengurusModal'))?.hide();
                        formAddPengurus.reset();
                        const previewEl = document.getElementById('previewAvatarNewPengurus');
                        if (previewEl) previewEl.src = '{{ asset("Admin/img/avatars/pria.png") }}';

                        if (typeof showSiladesBengToast === 'function') {
                            showSiladesBengToast('success', 'Pengurus Ditambahkan', 'Pengurus baru langsung ditugaskan ke fasilitas ini.');
                        }
                    } else {
                        alert(data.message || 'Gagal menambahkan pengurus.');
                    }
                })
                .catch(() => {
                    if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
                    alert('Terjadi kesalahan jaringan. Coba lagi.');
                });
            });
        }
    });
</script>
@endpush
