@php
    $savedLocations = $savedLocations ?? collect();
    $currentLokasi = old('lokasi', $lokasiValue ?? '');
    $currentLat = old('latitude', $latValue ?? '');
    $currentLng = old('longitude', $lngValue ?? '');
    $hasSaved = $savedLocations->count() > 0;
    $matchedSaved = $hasSaved && $currentLokasi !== '' && $savedLocations->contains(fn ($l) => $l->nama === $currentLokasi);
    $initialMode = $matchedSaved ? 'saved' : 'new';
    $placeholderText = $placeholderText ?? 'Contoh: Balai Desa / Gudang Layanan Desa';
@endphp

<input type="hidden" id="latitude" name="latitude" value="{{ $currentLat }}">
<input type="hidden" id="longitude" name="longitude" value="{{ $currentLng }}">

@if($hasSaved)
    <div class="mb-3">
        <label class="form-label d-block">Sumber Lokasi <span class="text-danger">*</span></label>
        <div class="up-mode-group" role="group" aria-label="Pilih metode lokasi">
            <input type="radio" class="btn-check" name="lokasi_mode" id="mode_saved" value="saved"
                   autocomplete="off" @checked($initialMode === 'saved') onchange="toggleLokasiMode('saved')">
            <label class="up-mode-label" for="mode_saved">
                <i class="bx bx-bookmark-alt"></i>Lokasi Tersimpan
            </label>

            <input type="radio" class="btn-check" name="lokasi_mode" id="mode_new" value="new"
                   autocomplete="off" @checked($initialMode === 'new') onchange="toggleLokasiMode('new')">
            <label class="up-mode-label" for="mode_new">
                <i class="bx bx-edit-alt"></i>Ketik Alamat Manual
            </label>
        </div>
    </div>

    <div class="mb-3" id="saved_location_container" style="{{ $initialMode === 'saved' ? '' : 'display: none;' }}">
        <label class="form-label" for="saved_lokasi">Pilih dari Lokasi Wilayah <span class="text-danger">*</span></label>
        <select class="form-select" id="saved_lokasi" @required($initialMode === 'saved') onchange="fillLocationData(this)">
            <option value="">-- Pilih Lokasi Tersimpan --</option>
            @foreach($savedLocations as $loc)
                <option value="{{ $loc->nama }}"
                        data-lat="{{ $loc->latitude }}"
                        data-lng="{{ $loc->longitude }}"
                        @selected($currentLokasi === $loc->nama)>
                    {{ $loc->nama }}
                </option>
            @endforeach
        </select>
        <small class="text-muted d-block mt-1">
            Titik peta (koordinat) akan otomatis terisi sesuai lokasi desa yang dipilih.
        </small>
    </div>
@endif

<div id="new_location_container" style="{{ $initialMode === 'saved' ? 'display: none;' : '' }}">
    <label class="form-label" for="lokasi">
        Alamat / Nama Lokasi Layanan <span class="text-danger">*</span>
    </label>
    <input type="text"
           class="form-control @error('lokasi') is-invalid @enderror"
           id="lokasi"
           name="lokasi"
           value="{{ $currentLokasi }}"
           placeholder="{{ $placeholderText }}"
           @required($initialMode !== 'saved') />
    @error('lokasi')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted d-block mt-1">
        Masukkan nama tempat atau alamat pengambilan/penggunaan layanan agar warga mudah menemukannya.
    </small>
</div>

<div id="coord_preview_badge" class="mt-2.5 {{ ($currentLat && $currentLng) ? '' : 'd-none' }}">
    <span class="badge bg-label-success rounded-pill px-2.5 py-1 fw-medium" style="font-size: 0.74rem;">
        <i class="bx bx-map-pin me-1"></i>Koordinat peta tersambung (<span id="coord_preview_text">{{ $currentLat }}, {{ $currentLng }}</span>)
    </span>
</div>

@once
@push('scripts')
<script>
    function updateCoordPreview() {
        const lat = document.getElementById('latitude')?.value || '';
        const lng = document.getElementById('longitude')?.value || '';
        const badge = document.getElementById('coord_preview_badge');
        const text = document.getElementById('coord_preview_text');
        if (!badge || !text) return;
        if (lat && lng) {
            text.textContent = lat + ', ' + lng;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    function toggleLokasiMode(mode) {
        const savedContainer = document.getElementById('saved_location_container');
        const newContainer = document.getElementById('new_location_container');
        const select = document.getElementById('saved_lokasi');
        const inputLokasi = document.getElementById('lokasi');
        const inputLat = document.getElementById('latitude');
        const inputLng = document.getElementById('longitude');

        if (mode === 'saved') {
            if (savedContainer) savedContainer.style.display = 'block';
            if (newContainer) newContainer.style.display = 'none';
            if (inputLokasi) {
                inputLokasi.readOnly = true;
                inputLokasi.required = false;
            }
            if (select) {
                select.required = true;
                fillLocationData(select);
            }
        } else {
            if (savedContainer) savedContainer.style.display = 'none';
            if (newContainer) newContainer.style.display = 'block';
            if (select) select.required = false;
            if (inputLokasi) {
                inputLokasi.readOnly = false;
                inputLokasi.required = true;
            }
            if (!(select && select.value && inputLokasi && inputLokasi.value === select.value)) {
                if (inputLat && !inputLat.dataset.keep) inputLat.value = '';
                if (inputLng && !inputLng.dataset.keep) inputLng.value = '';
            }
            updateCoordPreview();
        }
    }

    function fillLocationData(select) {
        const option = select?.options[select.selectedIndex];
        const inputLokasi = document.getElementById('lokasi');
        const inputLat = document.getElementById('latitude');
        const inputLng = document.getElementById('longitude');

        if (option && option.value !== '') {
            if (inputLokasi) inputLokasi.value = option.value;
            if (inputLat) inputLat.value = option.dataset.lat || '';
            if (inputLng) inputLng.value = option.dataset.lng || '';
        } else {
            if (inputLokasi) inputLokasi.value = '';
            if (inputLat) inputLat.value = '';
            if (inputLng) inputLng.value = '';
        }
        updateCoordPreview();
    }
</script>
@endpush
@endonce
