@php
    $isEdit = isset($mobil) && $mobil && ($mobil->exists || !empty($mobil->id));
    $selectedKategori = old('kategori', $isEdit ? $mobil->kategori : '');
    $selectedSatuan = old('satuan', $isEdit ? ($mobil->satuan ?: 'Unit') : 'Unit');
    $defaultSatuans = ['Unit'];
    if ($selectedSatuan && !in_array($selectedSatuan, $defaultSatuans, true)) {
        $defaultSatuans[] = $selectedSatuan;
    }

    $isHarianActive = old('_submitted')
        ? (bool) old('is_harian_active')
        : (bool) ($isEdit ? ($mobil->is_harian_active ?? true) : true);
    $isBoronganActive = old('_submitted')
        ? (bool) old('is_borongan_active')
        : (bool) ($isEdit ? ($mobil->is_borongan_active ?? true) : true);

    $opsiSupirHarian = old('opsi_supir', $isEdit ? ($mobil->opsi_supir ?? 'Lepas Kunci') : 'Lepas Kunci');
    $isTanpaSupirHarian = in_array($opsiSupirHarian, ['Lepas Kunci', 'Bebas Pilih'], true);
    $isDenganSupirHarian = in_array($opsiSupirHarian, ['Dengan Supir', 'Bebas Pilih'], true);

    $bbmHarian = old('bbm_ditanggung', $isEdit ? ($mobil->bbm_ditanggung ?? 'Penyewa') : 'Penyewa');
    $isBbmDesaHarian = in_array($bbmHarian, ['Pengelola', 'Pemerintah Desa'], true);

    $opsiSupirBorongan = old('opsi_supir_borongan', $isEdit ? ($mobil->opsi_supir_borongan ?? 'Lepas Kunci') : 'Lepas Kunci');
    $isTanpaSupirBorongan = in_array($opsiSupirBorongan, ['Lepas Kunci', 'Bebas Pilih'], true);
    $isDenganSupirBorongan = in_array($opsiSupirBorongan, ['Dengan Supir', 'Bebas Pilih'], true);

    $bbmBorongan = old('bbm_ditanggung_borongan', $isEdit ? ($mobil->bbm_ditanggung_borongan ?? 'Penyewa') : 'Penyewa');
    $isBbmDesaBorongan = in_array($bbmBorongan, ['Pengelola', 'Pemerintah Desa'], true);

    $hargaSewaVal = old('harga_sewa', ($isEdit && $mobil->harga_sewa !== null) ? number_format((float) $mobil->harga_sewa, 0, ',', '.') : '');
    $hargaDalamDesaVal = old('harga_dalam_desa_wilayah', ($isEdit && $mobil->harga_dalam_desa_wilayah) ? number_format((float) $mobil->harga_dalam_desa_wilayah, 0, ',', '.') : '');
    $hargaLuarDesaVal = old('harga_luar_desa_wilayah', ($isEdit && $mobil->harga_luar_desa_wilayah) ? number_format((float) $mobil->harga_luar_desa_wilayah, 0, ',', '.') : '');
@endphp

<input type="hidden" name="_submitted" value="1">
<input type="hidden" name="tipe_tarif_borongan" value="wilayah">

<div class="row g-4">
    {{-- Kolom Kiri --}}
    <div class="col-12 col-lg-8">
        {{-- 1. Informasi Kendaraan --}}
        <div class="card up-card mb-4">
            <div class="up-card-header d-flex align-items-center gap-2.5">
                <span class="up-step-num bg-label-primary text-primary">1</span>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Informasi Dasar Kendaraan</h6>
                    <small class="text-muted">Nama armada, kategori, nomor polisi, stok unit, dan spesifikasi</small>
                </div>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-12 col-md-7">
                        <label class="form-label" for="nama_mobil">
                            Nama Kendaraan / Transportasi <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('nama_mobil') is-invalid @enderror"
                               id="nama_mobil"
                               name="nama_mobil"
                               value="{{ old('nama_mobil', $isEdit ? $mobil->nama_mobil : '') }}"
                               placeholder="Contoh: Toyota Avanza / Mitsubishi L300"
                               required />
                        @error('nama_mobil')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

                    <div class="col-12 col-md-5">
                        <label class="form-label" for="plat_nomor">
                            Plat Nomor <span class="text-muted fw-normal">(Opsional)</span>
                        </label>
                        <input type="text"
                               class="form-control @error('plat_nomor') is-invalid @enderror"
                               id="plat_nomor"
                               name="plat_nomor"
                               value="{{ old('plat_nomor', $isEdit ? $mobil->plat_nomor : '') }}"
                               placeholder="Contoh: BM 1234 XY" />
                        @error('plat_nomor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label" for="stok">
                            Stok Unit <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               class="form-control @error('stok') is-invalid @enderror"
                               id="stok"
                               name="stok"
                               value="{{ old('stok', $isEdit ? $mobil->stok : 1) }}"
                               placeholder="1"
                               min="0"
                               required />
                        @error('stok')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label" for="satuan">
                            Satuan <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('satuan') is-invalid @enderror" id="satuan" name="satuan" required>
                            @foreach($defaultSatuans as $sat)
                                <option value="{{ $sat }}" @selected($selectedSatuan == $sat)>{{ $sat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="deskripsi">
                            Deskripsi &amp; Spesifikasi <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror"
                                  id="deskripsi"
                                  name="deskripsi"
                                  rows="3"
                                  placeholder="Jelaskan kapasitas penumpang/muatan, kondisi AC, tahun kendaraan, dan ketentuan penggunaan..."
                                  required>{{ old('deskripsi', $isEdit ? $mobil->deskripsi : '') }}</textarea>
                        @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Foto Kendaraan --}}
        <div class="card up-card mb-4">
            <div class="up-card-header d-flex align-items-center gap-2.5">
                <span class="up-step-num bg-label-info text-info">2</span>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Foto Armada</h6>
                    <small class="text-muted">Unggah foto tampak depan &amp; interior kendaraan (rasio 16:9, maks. 8MB)</small>
                </div>
            </div>
            <div class="card-body p-3 p-md-4">
                @include('admin.unit.partials.photo_upload_slots', [
                    'item' => $isEdit ? $mobil : null,
                    'primaryName' => 'foto_utama',
                    'primaryRequired' => !$isEdit,
                    'cropRatioExpr' => '16 / 9',
                    'aspectClass' => 'ratio-16x9',
                    'altPrefix' => $isEdit ? $mobil->nama_mobil : 'Foto Transportasi',
                ])
            </div>
        </div>

        {{-- 3. Skema 1: Sewa Harian --}}
        <div class="card up-card mb-4">
            <div class="up-card-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="up-step-num bg-label-primary text-primary">3</span>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Skema Sewa Harian (Per 24 Jam)</h6>
                        <small class="text-muted">Cocok untuk rental fleksibel 1 hari atau lebih, otomatis dikalikan jumlah hari</small>
                    </div>
                </div>
                <div class="form-check form-switch mb-0 d-flex align-items-center gap-2 bg-light border rounded-pill px-3 py-1.5">
                    <input class="form-check-input m-0" type="checkbox" name="is_harian_active" id="is_harian_active"
                           @checked($isHarianActive) onchange="toggleLayanan('harian')">
                    <label class="form-check-label fw-semibold small text-primary m-0" for="is_harian_active">Aktifkan</label>
                </div>
            </div>

            <div class="card-body p-3 p-md-4" id="harian_content_wrapper" style="{{ $isHarianActive ? '' : 'display:none;' }}">
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="harga_sewa">
                            Tarif Dasar per Hari <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text"
                                   class="form-control fw-semibold @error('harga_sewa') is-invalid @enderror"
                                   id="harga_sewa"
                                   name="harga_sewa"
                                   value="{{ $hargaSewaVal }}"
                                   placeholder="250.000"
                                   inputmode="numeric"
                                   @required($isHarianActive)
                                   oninput="formatRupiah(this)" />
                        </div>
                        @error('harga_sewa')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                    </div>
                </div>

                {{-- Pilihan Supir Harian --}}
                <div class="mb-4">
                    <label class="form-label d-block mb-1">Opsi Layanan Supir (Harian) <span class="text-danger">*</span></label>
                    <small class="text-muted d-block mb-2.5">Aktifkan salah satu, atau keduanya agar warga bebas memilih saat memesan.</small>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="up-choice-card {{ $isTanpaSupirHarian ? 'is-active-primary' : '' }}" id="card_supir_tanpa_harian" onclick="toggleSupirCard('harian', 'tanpa', event)">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-1.5">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <img src="{{ asset('User/img/iconbaru/supirsendiri.png') }}" alt="" style="width: 32px; height: 32px; object-fit: contain;" onerror="this.style.display='none'">
                                        <div class="min-w-0">
                                            <span class="fw-bold text-dark small d-block">Lepas Kunci (Bawa Sendiri)</span>
                                            <span class="badge {{ $isTanpaSupirHarian ? 'bg-success' : 'bg-label-secondary' }} rounded-pill" id="status_tanpa_supir" style="font-size: 0.66rem;">{{ $isTanpaSupirHarian ? 'Aktif' : 'Nonaktif' }}</span>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="switch_tanpa_supir" @checked($isTanpaSupirHarian) onchange="updateSupirStatus('harian')">
                                    </div>
                                </div>
                                <small class="text-muted d-block" style="font-size: 0.78rem; line-height: 1.4;">Penyewa mengemudi sendiri tanpa pendampingan supir pengelola.</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="up-choice-card {{ $isDenganSupirHarian ? 'is-active-primary' : '' }}" id="card_supir_dengan_harian" onclick="toggleSupirCard('harian', 'dengan', event)">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-1.5">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <img src="{{ asset('User/img/iconbaru/disediakansupir.png') }}" alt="" style="width: 32px; height: 32px; object-fit: contain;" onerror="this.style.display='none'">
                                        <div class="min-w-0">
                                            <span class="fw-bold text-dark small d-block">Dengan Supir Pengelola</span>
                                            <span class="badge {{ $isDenganSupirHarian ? 'bg-success' : 'bg-label-secondary' }} rounded-pill" id="status_dengan_supir" style="font-size: 0.66rem;">{{ $isDenganSupirHarian ? 'Aktif' : 'Nonaktif' }}</span>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="switch_dengan_supir" @checked($isDenganSupirHarian) onchange="updateSupirStatus('harian')">
                                    </div>
                                </div>
                                <small class="text-muted d-block" style="font-size: 0.78rem; line-height: 1.4;">Admin menugaskan supir resmi desa saat pesanan disetujui.</small>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="opsi_supir" id="opsi_supir" value="{{ $opsiSupirHarian }}">
                </div>

                {{-- Pilihan BBM Harian --}}
                <div>
                    <label class="form-label d-block mb-1">Ketentuan Bahan Bakar / BBM (Harian) <span class="text-danger">*</span></label>
                    <small class="text-muted d-block mb-2.5">Pilih salah satu untuk transparansi biaya kepada warga.</small>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="up-choice-card {{ $isBbmDesaHarian ? 'is-active-primary' : '' }}" id="card_bbm_desa" onclick="selectBbm('harian', 'Pengelola')">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-1.5">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <img src="{{ asset('User/img/iconbaru/bbmdisediakan.png') }}" alt="" style="width: 32px; height: 32px; object-fit: contain;" onerror="this.style.display='none'">
                                        <div class="min-w-0">
                                            <span class="fw-bold text-dark small d-block">BBM Disediakan Pengelola</span>
                                            <span class="badge {{ $isBbmDesaHarian ? 'bg-success' : 'bg-label-secondary' }} rounded-pill" id="status_bbm_desa" style="font-size: 0.66rem;">{{ $isBbmDesaHarian ? 'Dipilih' : 'Tidak Dipilih' }}</span>
                                        </div>
                                    </div>
                                    <input class="form-check-input m-0" type="radio" name="bbm_switch" id="switch_bbm_desa" value="Pengelola" @checked($isBbmDesaHarian) onchange="updateBbmStatus('harian')">
                                </div>
                                <small class="text-muted d-block" style="font-size: 0.78rem; line-height: 1.4;">Tarif sewa harian sudah termasuk bahan bakar.</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="up-choice-card {{ !$isBbmDesaHarian ? 'is-active-primary' : '' }}" id="card_bbm_penyewa" onclick="selectBbm('harian', 'Penyewa')">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-1.5">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <img src="{{ asset('User/img/iconbaru/bbmditanggungpengguna.png') }}" alt="" style="width: 32px; height: 32px; object-fit: contain;" onerror="this.style.display='none'">
                                        <div class="min-w-0">
                                            <span class="fw-bold text-dark small d-block">BBM Ditanggung Penyewa</span>
                                            <span class="badge {{ !$isBbmDesaHarian ? 'bg-success' : 'bg-label-secondary' }} rounded-pill" id="status_bbm_penyewa" style="font-size: 0.66rem;">{{ !$isBbmDesaHarian ? 'Dipilih' : 'Tidak Dipilih' }}</span>
                                        </div>
                                    </div>
                                    <input class="form-check-input m-0" type="radio" name="bbm_switch" id="switch_bbm_penyewa" value="Penyewa" @checked(!$isBbmDesaHarian) onchange="updateBbmStatus('harian')">
                                </div>
                                <small class="text-muted d-block" style="font-size: 0.78rem; line-height: 1.4;">Penyewa mengisi bahan bakar mandiri sesuai pemakaian.</small>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="bbm_ditanggung" id="bbm_ditanggung" value="{{ $isBbmDesaHarian ? 'Pengelola' : 'Penyewa' }}" required>
                </div>
            </div>
            <div class="card-body p-3 text-center text-muted small bg-light rounded-bottom-4" id="harian_disabled_notice" style="{{ $isHarianActive ? 'display:none;' : '' }}">
                Sewa Harian nonaktif. Aktifkan sakelar di kanan atas jika kendaraan ini juga bisa disewa per hari.
            </div>
        </div>

        {{-- 4. Skema 2: Sewa Borongan --}}
        <div class="card up-card mb-4">
            <div class="up-card-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="up-step-num bg-label-success text-success">4</span>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Skema Sewa Borongan (Carter / Drop-Off)</h6>
                        <small class="text-muted">Tarif sekali jalan / antar berdasarkan wilayah tujuan di Kecamatan Bengkalis</small>
                    </div>
                </div>
                <div class="form-check form-switch mb-0 d-flex align-items-center gap-2 bg-light border rounded-pill px-3 py-1.5">
                    <input class="form-check-input m-0" type="checkbox" name="is_borongan_active" id="is_borongan_active"
                           @checked($isBoronganActive) onchange="toggleLayanan('borongan')">
                    <label class="form-check-label fw-semibold small text-success m-0" for="is_borongan_active">Aktifkan</label>
                </div>
            </div>

            <div class="card-body p-3 p-md-4" id="borongan_content_wrapper" style="{{ $isBoronganActive ? '' : 'display:none;' }}">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="harga_dalam_desa_wilayah">
                            Tarif Dalam Desa (Satu Desa)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text"
                                   class="form-control fw-semibold"
                                   id="harga_dalam_desa_wilayah"
                                   name="harga_dalam_desa_wilayah"
                                   value="{{ $hargaDalamDesaVal }}"
                                   placeholder="100.000"
                                   inputmode="numeric"
                                   oninput="formatRupiah(this)" />
                        </div>
                        <small class="text-muted d-block mt-1">Tujuan di dalam batas desa asal.</small>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="harga_luar_desa_wilayah">
                            Tarif Luar Desa (1 Kecamatan)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text"
                                   class="form-control fw-semibold"
                                   id="harga_luar_desa_wilayah"
                                   name="harga_luar_desa_wilayah"
                                   value="{{ $hargaLuarDesaVal }}"
                                   placeholder="150.000"
                                   inputmode="numeric"
                                   oninput="formatRupiah(this)" />
                        </div>
                        <small class="text-muted d-block mt-1">Desa/kelurahan lain di Kecamatan Bengkalis.</small>
                    </div>
                </div>

                <div class="p-2.5 rounded-3 bg-light border text-muted small mb-4">
                    <i class="bx bx-info-circle text-primary me-1"></i>Rute sewa borongan hanya berlaku untuk desa/kelurahan dalam wilayah Kecamatan Bengkalis.
                </div>

                {{-- Pilihan Supir Borongan --}}
                <div class="mb-4">
                    <label class="form-label d-block mb-1">Opsi Layanan Supir (Borongan) <span class="text-danger">*</span></label>
                    <small class="text-muted d-block mb-2.5">Aktifkan salah satu, atau keduanya agar warga bebas memilih.</small>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="up-choice-card {{ $isTanpaSupirBorongan ? 'is-active-success' : '' }}" id="card_supir_tanpa_borongan" onclick="toggleSupirCard('borongan', 'tanpa', event)">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-1.5">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <img src="{{ asset('User/img/iconbaru/supirsendiri.png') }}" alt="" style="width: 32px; height: 32px; object-fit: contain;" onerror="this.style.display='none'">
                                        <div class="min-w-0">
                                            <span class="fw-bold text-dark small d-block">Tanpa Supir (Bawa Sendiri)</span>
                                            <span class="badge {{ $isTanpaSupirBorongan ? 'bg-success' : 'bg-label-secondary' }} rounded-pill" id="status_tanpa_supir_borongan" style="font-size: 0.66rem;">{{ $isTanpaSupirBorongan ? 'Aktif' : 'Nonaktif' }}</span>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="switch_tanpa_supir_borongan" @checked($isTanpaSupirBorongan) onchange="updateSupirStatus('borongan')">
                                    </div>
                                </div>
                                <small class="text-muted d-block" style="font-size: 0.78rem; line-height: 1.4;">Penyewa membawa sendiri kendaraan untuk rute borongan.</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="up-choice-card {{ $isDenganSupirBorongan ? 'is-active-success' : '' }}" id="card_supir_dengan_borongan" onclick="toggleSupirCard('borongan', 'dengan', event)">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-1.5">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <img src="{{ asset('User/img/iconbaru/disediakansupir.png') }}" alt="" style="width: 32px; height: 32px; object-fit: contain;" onerror="this.style.display='none'">
                                        <div class="min-w-0">
                                            <span class="fw-bold text-dark small d-block">Dengan Supir Pengelola</span>
                                            <span class="badge {{ $isDenganSupirBorongan ? 'bg-success' : 'bg-label-secondary' }} rounded-pill" id="status_dengan_supir_borongan" style="font-size: 0.66rem;">{{ $isDenganSupirBorongan ? 'Aktif' : 'Nonaktif' }}</span>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="switch_dengan_supir_borongan" @checked($isDenganSupirBorongan) onchange="updateSupirStatus('borongan')">
                                    </div>
                                </div>
                                <small class="text-muted d-block" style="font-size: 0.78rem; line-height: 1.4;">Pengelola menugaskan supir resmi untuk mengantar rombongan.</small>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="opsi_supir_borongan" id="opsi_supir_borongan" value="{{ $opsiSupirBorongan }}">
                </div>

                {{-- Pilihan BBM Borongan --}}
                <div>
                    <label class="form-label d-block mb-1">Ketentuan Bahan Bakar / BBM (Borongan) <span class="text-danger">*</span></label>
                    <small class="text-muted d-block mb-2.5">Pilih salah satu untuk kejelasan tarif borongan.</small>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="up-choice-card {{ $isBbmDesaBorongan ? 'is-active-success' : '' }}" id="card_bbm_desa_borongan" onclick="selectBbm('borongan', 'Pengelola')">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-1.5">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <img src="{{ asset('User/img/iconbaru/bbmdisediakan.png') }}" alt="" style="width: 32px; height: 32px; object-fit: contain;" onerror="this.style.display='none'">
                                        <div class="min-w-0">
                                            <span class="fw-bold text-dark small d-block">BBM Disediakan Pengelola</span>
                                            <span class="badge {{ $isBbmDesaBorongan ? 'bg-success' : 'bg-label-secondary' }} rounded-pill" id="status_bbm_desa_borongan" style="font-size: 0.66rem;">{{ $isBbmDesaBorongan ? 'Dipilih' : 'Tidak Dipilih' }}</span>
                                        </div>
                                    </div>
                                    <input class="form-check-input m-0" type="radio" name="bbm_switch_borongan" id="switch_bbm_desa_borongan" value="Pengelola" @checked($isBbmDesaBorongan) onchange="updateBbmStatus('borongan')">
                                </div>
                                <small class="text-muted d-block" style="font-size: 0.78rem; line-height: 1.4;">Tarif borongan sudah all-in termasuk bahan bakar sampai tujuan.</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="up-choice-card {{ !$isBbmDesaBorongan ? 'is-active-success' : '' }}" id="card_bbm_penyewa_borongan" onclick="selectBbm('borongan', 'Penyewa')">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-1.5">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <img src="{{ asset('User/img/iconbaru/bbmditanggungpengguna.png') }}" alt="" style="width: 32px; height: 32px; object-fit: contain;" onerror="this.style.display='none'">
                                        <div class="min-w-0">
                                            <span class="fw-bold text-dark small d-block">BBM Ditanggung Penyewa</span>
                                            <span class="badge {{ !$isBbmDesaBorongan ? 'bg-success' : 'bg-label-secondary' }} rounded-pill" id="status_bbm_penyewa_borongan" style="font-size: 0.66rem;">{{ !$isBbmDesaBorongan ? 'Dipilih' : 'Tidak Dipilih' }}</span>
                                        </div>
                                    </div>
                                    <input class="form-check-input m-0" type="radio" name="bbm_switch_borongan" id="switch_bbm_penyewa_borongan" value="Penyewa" @checked(!$isBbmDesaBorongan) onchange="updateBbmStatus('borongan')">
                                </div>
                                <small class="text-muted d-block" style="font-size: 0.78rem; line-height: 1.4;">Biaya bahan bakar selama perjalanan ditanggung penyewa.</small>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="bbm_ditanggung_borongan" id="bbm_ditanggung_borongan" value="{{ $isBbmDesaBorongan ? 'Pengelola' : 'Penyewa' }}" required>
                </div>
            </div>
            <div class="card-body p-3 text-center text-muted small bg-light rounded-bottom-4" id="borongan_disabled_notice" style="{{ $isBoronganActive ? 'display:none;' : '' }}">
                Sewa Borongan nonaktif. Aktifkan sakelar di kanan atas jika kendaraan ini melayani carter atau drop-off.
            </div>
        </div>

        {{-- 5. Lokasi Pangkalan --}}
        <div class="card up-card">
            <div class="up-card-header d-flex align-items-center gap-2.5">
                <span class="up-step-num bg-label-warning text-warning">5</span>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Lokasi Pangkalan / Pengambilan</h6>
                    <small class="text-muted">Pilih lokasi wilayah tersimpan atau tulis alamat pangkalan armada</small>
                </div>
            </div>
            <div class="card-body p-3 p-md-4">
                @include('admin.unit.partials.location_selector', [
                    'savedLocations' => $savedLocations,
                    'lokasiValue' => $isEdit ? $mobil->lokasi : '',
                    'latValue' => $isEdit ? $mobil->latitude : '',
                    'lngValue' => $isEdit ? $mobil->longitude : '',
                    'placeholderText' => 'Contoh: Garasi Kantor Desa / Pool BUMDes',
                ])
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
                        <small class="text-muted">Atur ketersediaan kendaraan bagi warga</small>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <label class="form-label" for="status">Status Armada <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="tersedia" @selected(old('status', $isEdit ? $mobil->status : 'tersedia') === 'tersedia')>Tersedia — Siap Disewa</option>
                        <option value="disewa" @selected(old('status', $isEdit ? $mobil->status : '') === 'disewa')>Sedang Disewa</option>
                        <option value="rusak" @selected(old('status', $isEdit ? $mobil->status : '') === 'rusak')>Rusak / Perbaikan</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card up-card mb-4">
                <div class="card-body p-3 p-md-4">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 py-2.5 fw-semibold shadow-sm mb-2">
                        <i class="bx bx-save me-1"></i>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Kendaraan' }}
                    </button>
                    <a href="{{ $isEdit ? route('admin.unit.mobil.show', $mobil->id) : route('admin.unit.mobil.index') }}" class="btn btn-outline-secondary rounded-pill w-100">
                        Batal
                    </a>
                </div>
            </div>

            <div class="card border-0 bg-label-primary rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bx bx-bulb fs-5 text-primary mt-0.5 flex-shrink-0"></i>
                        <div class="small text-primary" style="line-height: 1.55;">
                            <strong class="d-block mb-1">Minimal 1 Skema Aktif</strong>
                            Anda dapat mengaktifkan <strong>Sewa Harian</strong>, <strong>Sewa Borongan</strong>, atau keduanya sekaligus pada satu kendaraan.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleLayanan(type) {
        const harianEl = document.getElementById('is_harian_active');
        const boronganEl = document.getElementById('is_borongan_active');
        if (!harianEl || !boronganEl) return;

        if (!harianEl.checked && !boronganEl.checked) {
            if (typeof showSiladesBengToast === 'function') {
                showSiladesBengToast('warning', 'Minimal 1 Layanan', 'Aktifkan minimal satu skema (Sewa Harian atau Sewa Borongan).');
            } else {
                alert('Minimal harus ada satu skema (Sewa Harian atau Sewa Borongan) yang diaktifkan.');
            }
            if (type === 'harian') harianEl.checked = true;
            else boronganEl.checked = true;
            return;
        }

        const harianWrap = document.getElementById('harian_content_wrapper');
        const harianNotice = document.getElementById('harian_disabled_notice');
        const hargaSewaInput = document.getElementById('harga_sewa');
        if (harianWrap) harianWrap.style.display = harianEl.checked ? '' : 'none';
        if (harianNotice) harianNotice.style.display = harianEl.checked ? 'none' : '';
        if (hargaSewaInput) {
            hargaSewaInput.required = harianEl.checked;
            if (!harianEl.checked && !hargaSewaInput.value.trim()) {
                hargaSewaInput.value = '0';
            }
        }

        const boronganWrap = document.getElementById('borongan_content_wrapper');
        const boronganNotice = document.getElementById('borongan_disabled_notice');
        if (boronganWrap) boronganWrap.style.display = boronganEl.checked ? '' : 'none';
        if (boronganNotice) boronganNotice.style.display = boronganEl.checked ? 'none' : '';
    }

    function toggleSupirCard(scope, which, event) {
        const suffix = scope === 'borongan' ? '_borongan' : '';
        const swId = 'switch_' + which + '_supir' + suffix;
        if (event && event.target && event.target.id === swId) return;
        const sw = document.getElementById(swId);
        if (sw) {
            sw.checked = !sw.checked;
            updateSupirStatus(scope);
        }
    }

    function updateSupirStatus(scope) {
        const suffix = scope === 'borongan' ? '_borongan' : '';
        const activeClass = scope === 'borongan' ? 'is-active-success' : 'is-active-primary';
        const swTanpa = document.getElementById('switch_tanpa_supir' + suffix);
        const swDengan = document.getElementById('switch_dengan_supir' + suffix);
        if (!swTanpa || !swDengan) return;

        if (!swTanpa.checked && !swDengan.checked) {
            swTanpa.checked = true;
            if (typeof showSiladesBengToast === 'function') {
                showSiladesBengToast('warning', 'Minimal 1 Opsi', 'Minimal satu pilihan layanan supir harus aktif.');
            }
        }

        const stTanpa = document.getElementById('status_tanpa_supir' + suffix);
        const cardTanpa = document.getElementById('card_supir_tanpa_' + scope);
        if (stTanpa) {
            stTanpa.textContent = swTanpa.checked ? 'Aktif' : 'Nonaktif';
            stTanpa.className = 'badge rounded-pill ' + (swTanpa.checked ? 'bg-success' : 'bg-label-secondary');
        }
        if (cardTanpa) cardTanpa.classList.toggle(activeClass, swTanpa.checked);

        const stDengan = document.getElementById('status_dengan_supir' + suffix);
        const cardDengan = document.getElementById('card_supir_dengan_' + scope);
        if (stDengan) {
            stDengan.textContent = swDengan.checked ? 'Aktif' : 'Nonaktif';
            stDengan.className = 'badge rounded-pill ' + (swDengan.checked ? 'bg-success' : 'bg-label-secondary');
        }
        if (cardDengan) cardDengan.classList.toggle(activeClass, swDengan.checked);

        const hidden = document.getElementById('opsi_supir' + suffix);
        if (hidden) {
            if (swTanpa.checked && swDengan.checked) hidden.value = 'Bebas Pilih';
            else if (swTanpa.checked) hidden.value = 'Lepas Kunci';
            else hidden.value = 'Dengan Supir';
        }
    }

    function selectBbm(scope, type) {
        const suffix = scope === 'borongan' ? '_borongan' : '';
        const swDesa = document.getElementById('switch_bbm_desa' + suffix);
        const swPenyewa = document.getElementById('switch_bbm_penyewa' + suffix);
        if (type === 'Pengelola' && swDesa) swDesa.checked = true;
        else if (swPenyewa) swPenyewa.checked = true;
        updateBbmStatus(scope);
    }

    function updateBbmStatus(scope) {
        const suffix = scope === 'borongan' ? '_borongan' : '';
        const activeClass = scope === 'borongan' ? 'is-active-success' : 'is-active-primary';
        const swDesa = document.getElementById('switch_bbm_desa' + suffix);
        const swPenyewa = document.getElementById('switch_bbm_penyewa' + suffix);
        if (!swDesa || !swPenyewa) return;

        const isDesa = swDesa.checked;
        const stDesa = document.getElementById('status_bbm_desa' + suffix);
        const cardDesa = document.getElementById('card_bbm_desa' + suffix);
        if (stDesa) {
            stDesa.textContent = isDesa ? 'Dipilih' : 'Tidak Dipilih';
            stDesa.className = 'badge rounded-pill ' + (isDesa ? 'bg-success' : 'bg-label-secondary');
        }
        if (cardDesa) cardDesa.classList.toggle(activeClass, isDesa);

        const stPenyewa = document.getElementById('status_bbm_penyewa' + suffix);
        const cardPenyewa = document.getElementById('card_bbm_penyewa' + suffix);
        if (stPenyewa) {
            stPenyewa.textContent = !isDesa ? 'Dipilih' : 'Tidak Dipilih';
            stPenyewa.className = 'badge rounded-pill ' + (!isDesa ? 'bg-success' : 'bg-label-secondary');
        }
        if (cardPenyewa) cardPenyewa.classList.toggle(activeClass, !isDesa);

        const hidden = document.getElementById('bbm_ditanggung' + suffix);
        if (hidden) hidden.value = isDesa ? 'Pengelola' : 'Penyewa';
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateSupirStatus('harian');
        updateBbmStatus('harian');
        updateSupirStatus('borongan');
        updateBbmStatus('borongan');
        toggleLayanan('harian');
        toggleLayanan('borongan');
    });
</script>
@endpush
