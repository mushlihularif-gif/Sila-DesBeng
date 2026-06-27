@extends('admin.layouts.admin')

@section('title', isset($announcement) ? 'Edit Pengumuman' : 'Buat Pengumuman Baru')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Pengumuman & Event /</span> {{ isset($announcement) ? 'Edit' : 'Buat Baru' }}
    </h4>

    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bx bx-edit-alt me-2"></i>Formulir {{ isset($announcement) ? 'Edit' : 'Buat' }}</h5>
                </div>
                <div class="card-body">
                    
                    @if(isset($laporan))
                        <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                            <span class="alert-icon text-info me-2">
                                <i class="bx bx-info-circle bx-md"></i>
                            </span>
                            <div>
                                <strong>Menindaklanjuti Laporan Warga!</strong><br>
                                Anda sedang membuat event berdasarkan laporan: <em>"{{ $laporan->nama }}"</em> dari {{ $laporan->user->name ?? 'Warga' }}.
                            </div>
                        </div>
                    @endif

                    <form action="{{ isset($announcement) ? route('admin.announcements.update', $announcement->id) : route('admin.announcements.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($announcement))
                            @method('PUT')
                        @endif

                        @if(isset($laporan))
                            <input type="hidden" name="laporan_id" value="{{ $laporan->id }}">
                        @endif

                        <!-- Upload Gambar di Atas -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Gambar / Poster (Opsional)</label>
                            <div class="text-center w-100">
                                <div class="position-relative d-inline-block rounded-3 border border-2 border-primary border-dashed bg-light" 
                                     style="width: 100%; border-style: dashed !important; cursor: pointer; overflow: hidden; transition: all 0.3s ease;" 
                                     onclick="document.getElementById('imageInput').click()"
                                     onmouseover="this.style.borderColor='#696cff'; this.style.backgroundColor='#e7e7ff';"
                                     onmouseout="this.style.borderColor='#696cff'; this.style.backgroundColor='#f8f9fa';">
                                    
                                    <img id="imagePreview" 
                                        src="{{ isset($announcement) && $announcement->image_path ? Storage::url($announcement->image_path) : '' }}" 
                                        alt="Preview Gambar" 
                                        class="img-fluid w-100" 
                                        style="object-fit: cover; max-height: 350px; {{ (isset($announcement) && $announcement->image_path) ? '' : 'display: none;' }}">
                                    
                                    <div id="uploadPlaceholder" class="p-5" style="{{ (isset($announcement) && $announcement->image_path) ? 'display: none;' : '' }}">
                                        <div class="avatar avatar-xl bg-primary-subtle text-primary rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center">
                                            <i class="bx bx-cloud-upload" style="font-size: 2.5rem;"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Klik untuk memilih gambar poster</h6>
                                        <small class="text-muted">Format: JPG, PNG, GIF. Ukuran maksimal 2MB.</small>
                                    </div>

                                    <!-- Hover Overlay -->
                                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 opacity-0 transition-all" 
                                         style="opacity: 0; transition: 0.3s;" 
                                         onmouseover="this.style.opacity=1" 
                                         onmouseout="this.style.opacity=0">
                                        <span class="text-white fw-bold fs-5"><i class="bx bx-edit me-2"></i>Ubah Gambar</span>
                                    </div>
                                </div>
                                <input type="file" name="image" id="imageInput" class="d-none" accept="image/*" onchange="previewImage(this)">
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold"><i class="bx bx-category me-1"></i>Tipe Pengumuman <span class="text-danger">*</span></label>
                                <select name="type" class="form-select border-primary" required>
                                    <option value="Pengumuman" {{ (isset($announcement) && $announcement->type == 'Pengumuman') ? 'selected' : '' }}>Pengumuman Biasa</option>
                                    <option value="Event" {{ (isset($announcement) && $announcement->type == 'Event') ? 'selected' : '' }}>Acara / Event</option>
                                    <option value="Gotong Royong" {{ (isset($announcement) && $announcement->type == 'Gotong Royong') || isset($laporan) ? 'selected' : '' }}>Gotong Royong</option>
                                </select>
                            </div>

                            <input type="hidden" name="target_region_id" id="final_target_region_id" required>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold"><i class="bx bx-map me-1"></i>Kabupaten <span class="text-danger">*</span></label>
                                <select id="select_kabupaten" class="form-select border-primary" required>
                                    <option value="">Memuat...</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold"><i class="bx bx-map-alt me-1"></i>Kecamatan</label>
                                <select id="select_kecamatan" class="form-select border-primary" disabled>
                                    <option value="">Pilih Kecamatan (Opsional)</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold"><i class="bx bx-home me-1"></i>Desa/Kelurahan</label>
                                <select id="select_desa" class="form-select border-primary" disabled>
                                    <option value="">Pilih Desa (Opsional)</option>
                                </select>
                                <div class="form-text small">Biarkan kosong untuk target lebih luas.</div>
                            </div>
                        </div>



                        <div class="mb-3 mt-3">
                            <label class="form-label fw-semibold">Judul Pengumuman <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-lg border-primary" required 
                                value="{{ old('title', $announcement->title ?? (isset($laporan) ? 'Gotong Royong: Menindaklanjuti ' . $laporan->nama : '')) }}"
                                placeholder="Contoh: Gotong Royong Pembersihan Selokan">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi Lengkap <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control border-primary" rows="6" required placeholder="Tuliskan detail pengumuman, agenda acara, atau deskripsi kegiatan di sini...">{!! old('description', $announcement->description ?? (isset($laporan) ? "Mari bersama-sama kita melakukan gotong royong untuk mengatasi masalah:\n\n" . $laporan->deskripsi : '')) !!}</textarea>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal & Waktu Acara (Opsional)</label>
                                <input type="datetime-local" name="event_date" class="form-control border-primary" 
                                    value="{{ old('event_date', isset($announcement->event_date) ? $announcement->event_date->format('Y-m-d\TH:i') : '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Lokasi Acara (Opsional)</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text border-primary"><i class="bx bx-map"></i></span>
                                    <input type="text" name="location" class="form-control border-primary" 
                                        value="{{ old('location', $announcement->location ?? (isset($laporan) ? $laporan->lokasi : '')) }}" placeholder="Contoh: Balai Desa">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" {{ old('is_active', $announcement->is_active ?? true) ? 'checked' : '' }} style="width: 2.5em; height: 1.25em; cursor: pointer;">
                                <label class="form-check-label fw-bold text-dark ms-2 mt-1" for="isActive" style="cursor: pointer;">Publikasikan Langsung</label>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">Batal</a>
                                <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm"><i class="bx bx-save me-1"></i> Simpan Pengumuman</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <script>
            function previewImage(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    
                    reader.onload = function(e) {
                        document.getElementById('imagePreview').src = e.target.result;
                        document.getElementById('imagePreview').style.display = 'block';
                        document.getElementById('uploadPlaceholder').style.display = 'none';
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>
        
        <div class="col-xl-4">
            <div class="card bg-warning-subtle border-0 mb-4 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="mb-0 fw-bold text-warning-emphasis"><i class="bx bx-info-circle me-2"></i>Panduan Modul</h5>
                </div>
                <div class="card-body p-4 text-warning-emphasis">
                    <p class="mb-4">Modul ini memfasilitasi Anda untuk menyebarkan informasi penting kepada warga secara efektif.</p>
                    
                    <div class="d-flex align-items-start mb-3">
                        <div class="avatar avatar-sm bg-warning text-white rounded-circle me-3 flex-shrink-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px;">
                            <i class="bx bx-news"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold text-warning-emphasis">Pengumuman Biasa</h6>
                            <p class="mb-0 small opacity-75">Informasi umum atau imbauan satu arah untuk seluruh warga.</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-3">
                        <div class="avatar avatar-sm bg-warning text-white rounded-circle me-3 flex-shrink-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px;">
                            <i class="bx bx-calendar-event"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold text-warning-emphasis">Acara / Event</h6>
                            <p class="mb-0 small opacity-75">Kegiatan desa atau wilayah yang memiliki tanggal dan lokasi pelaksanaan.</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-4">
                        <div class="avatar avatar-sm bg-warning text-white rounded-circle me-3 flex-shrink-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px;">
                            <i class="bx bx-group"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold text-warning-emphasis">Gotong Royong</h6>
                            <p class="mb-0 small opacity-75">Ajakan aksi kebersihan bersama (bisa digunakan untuk menindaklanjuti laporan masalah dari warga).</p>
                        </div>
                    </div>

                    <div class="bg-white bg-opacity-50 text-warning-emphasis d-flex align-items-center p-3 mb-0 shadow-sm rounded-3">
                        <i class="bx bx-broadcast fs-3 me-3 text-warning"></i>
                        <small class="fw-semibold lh-sm">Pengumuman hanya akan masuk ke notifikasi warga di lingkup wilayah target yang Anda pilih.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const allRegions = @json($regions);
    const kabSelect = document.getElementById('select_kabupaten');
    const kecSelect = document.getElementById('select_kecamatan');
    const desaSelect = document.getElementById('select_desa');
    const finalTarget = document.getElementById('final_target_region_id');

    let initialId = {{ isset($announcement) ? $announcement->region_id : 'null' }};
    if (initialId === null) {
        initialId = {{ auth()->user()->region_id ?? 'null' }};
    }

    let initKab = null, initKec = null, initDesa = null;
    
    if (initialId) {
        let current = allRegions.find(r => r.id == initialId);
        if (current) {
            if (current.type === 'desa' || current.type === 'kelurahan') {
                initDesa = current.id;
                initKec = current.parent_id;
                let parentKec = allRegions.find(r => r.id == initKec);
                if (parentKec) initKab = parentKec.parent_id;
            } else if (current.type === 'kecamatan') {
                initKec = current.id;
                initKab = current.parent_id;
            } else if (current.type === 'kabupaten') {
                initKab = current.id;
            }
        }
    }

    const kabupatens = allRegions.filter(r => r.type === 'kabupaten');
    kabSelect.innerHTML = '';
    kabupatens.forEach(k => {
        kabSelect.innerHTML += `<option value="${k.id}">${k.name}</option>`;
    });
    if (initKab) { kabSelect.value = initKab; }

    function updateFinalTarget() {
        if (desaSelect.value) {
            finalTarget.value = desaSelect.value;
        } else if (kecSelect.value) {
            finalTarget.value = kecSelect.value;
        } else {
            finalTarget.value = kabSelect.value;
        }
    }

    function populateKecamatan() {
        const kabId = parseInt(kabSelect.value);
        kecSelect.innerHTML = '<option value="">Semua Kecamatan (Opsional)</option>';
        desaSelect.innerHTML = '<option value="">Semua Desa (Opsional)</option>';
        desaSelect.disabled = true;

        if (kabId) {
            const kecs = allRegions.filter(r => r.type === 'kecamatan' && r.parent_id === kabId);
            kecs.sort((a,b) => a.name.localeCompare(b.name)).forEach(k => {
                kecSelect.innerHTML += `<option value="${k.id}">${k.name}</option>`;
            });
            kecSelect.disabled = false;
        } else {
            kecSelect.disabled = true;
        }
        updateFinalTarget();
    }

    function populateDesa() {
        const kecId = parseInt(kecSelect.value);
        desaSelect.innerHTML = '<option value="">Semua Desa (Opsional)</option>';
        
        if (kecId) {
            const desas = allRegions.filter(r => (r.type === 'desa' || r.type === 'kelurahan') && r.parent_id === kecId);
            desas.sort((a,b) => a.name.localeCompare(b.name)).forEach(d => {
                desaSelect.innerHTML += `<option value="${d.id}">${d.name}</option>`;
            });
            desaSelect.disabled = false;
        } else {
            desaSelect.disabled = true;
        }
        updateFinalTarget();
    }

    kabSelect.addEventListener('change', populateKecamatan);
    kecSelect.addEventListener('change', populateDesa);
    desaSelect.addEventListener('change', updateFinalTarget);

    populateKecamatan();
    if (initKec) {
        kecSelect.value = initKec;
        populateDesa();
        if (initDesa) {
            desaSelect.value = initDesa;
        }
    }
    updateFinalTarget();
});
</script>

@endsection
