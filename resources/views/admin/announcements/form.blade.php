@extends('admin.layouts.admin')

@section('title', isset($announcement) ? 'Edit ' . $category : 'Buat ' . $category . ' Baru')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Sistem / {{ $category }} /</span> {{ isset($announcement) ? 'Edit' : 'Buat Baru' }}
    </h4>

    @if(isset($laporan))
        <div class="alert alert-info d-flex align-items-center mb-4 shadow-sm" style="border-left: 5px solid #03c3ec; border-radius: 10px;" role="alert">
            <span class="alert-icon text-info me-3">
                <i class="bx bx-info-circle bx-md"></i>
            </span>
            <div>
                <strong class="fs-5">Menindaklanjuti Laporan Warga!</strong><br>
                Anda sedang membuat event/berita berdasarkan laporan: <em>"{{ $laporan->nama }}"</em> dari {{ $laporan->user->name ?? 'Warga' }}.
            </div>
        </div>
    @endif

    <!-- Panduan Cerdas -->
    <div class="alert alert-primary d-flex align-items-center mb-4 shadow-sm" style="border-left: 5px solid #696cff; border-radius: 10px; background-color: #f4f5ff;" role="alert">
        <span class="alert-icon text-primary me-3">
            <i class="bx bx-bulb bx-md"></i>
        </span>
        <div>
            <strong class="fs-5 mb-1 d-block">Panduan Pembuatan {{ $category }}</strong>
            @if($category === 'Berita')
                <ul class="mb-0 ps-3">
                    <li>Unggah minimal 1 foto kegiatan/dokumentasi untuk menarik perhatian warga. Anda bisa klik area putus-putus untuk menambah banyak foto.</li>
                    <li>Pastikan judul dibuat singkat namun jelas dan informatif.</li>
                    <li>Deskripsi berita disarankan mencakup elemen dasar (Apa, Siapa, Kapan, Di mana, dan Bagaimana).</li>
                </ul>
            @else
                <ul class="mb-0 ps-3">
                    <li>Gambar/Poster bersifat opsional, namun sangat disarankan agar pengumuman lebih menarik.</li>
                    <li>Gunakan fitur <strong>Target Audiens</strong> dengan bijak agar pengumuman ini hanya muncul di menu <em>Kabar & Informasi Daerah</em> bagi warga di wilayah yang tepat sasaran.</li>
                    <li>Jangan lupa tentukan tipe kategori yang sesuai agar warga mudah memfilter informasi.</li>
                </ul>
            @endif
        </div>
    </div>

    <form action="{{ isset($announcement) ? route('admin.announcements.update', $announcement->id) : route('admin.announcements.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($announcement))
            @method('PUT')
        @endif

        <input type="hidden" name="post_category" value="{{ $category }}">

        @if(isset($laporan))
            <input type="hidden" name="laporan_id" value="{{ $laporan->id }}">
        @endif

        <div class="row">
            <!-- Left Column -->
            <div class="col-xl-8">
                <!-- Card 1: Media & Dokumentasi -->
                <div class="card shadow-sm mb-4 bg-white border-0" style="border-radius: 16px; overflow: hidden;">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="bx bx-image-add me-2"></i>Media & Dokumentasi</h5>
                    </div>
                    <div class="card-body mt-4">
                        @if($category === 'Berita')
                            <!-- Upload Banyak Gambar untuk Berita -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">Foto Dokumentasi (Bisa pilih lebih dari satu)</label>
                                <div class="border border-2 border-primary rounded p-4 text-center" style="border-style: dashed !important; background-color: rgba(105, 108, 255, 0.04);">
                                    <div id="multi-preview-grid" class="d-flex flex-wrap gap-3 mb-3 justify-content-center" style="display: none !important;">
                                        <!-- Previews akan muncul di sini -->
                                    </div>
                                    
                                    <div id="hidden-inputs-container"></div>
                                    
                                    <div class="cursor-pointer d-inline-block" onclick="addPhotoField()">
                                        <div class="avatar avatar-xl bg-white text-primary rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center shadow-sm border" style="width: 60px; height: 60px;">
                                            <i class="bx bx-cloud-upload" style="font-size: 2rem;"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1 text-dark" style="font-size: 1.1rem;">Pilih dari Penyimpanan</h6>
                                        <small class="text-muted">Klik untuk menambah foto (Bisa di-crop)</small>
                                    </div>
                                </div>
                                
                                @if(isset($announcement) && $announcement->images->count() > 0)
                                    <div class="mt-4 p-3 border rounded bg-white shadow-sm">
                                        <p class="mb-2 fw-semibold">Foto Saat Ini:</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($announcement->images as $img)
                                                <div class="position-relative border rounded p-1">
                                                    <img src="{{ Storage::url($img->image_path) }}" class="rounded" style="width: 120px; height: 120px; object-fit: cover;">
                                                    <div class="form-check mt-2 text-center d-flex justify-content-center">
                                                        <input class="form-check-input me-1" type="checkbox" name="delete_images[]" value="{{ $img->id }}" id="del_{{ $img->id }}">
                                                        <label class="form-check-label text-danger" style="font-size: 13px;" for="del_{{ $img->id }}">
                                                            Hapus
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <!-- Upload Single Image untuk Pengumuman -->
                            <div class="mb-3 position-relative">
                                <label class="form-label fw-semibold text-dark">Gambar / Poster (Opsional)</label>
                                <div class="text-center w-100 position-relative">
                                    <div class="position-relative d-inline-block rounded-3 border border-2 border-primary border-dashed" 
                                         style="width: 100%; border-style: dashed !important; cursor: pointer; overflow: hidden; transition: all 0.3s ease; background-color: rgba(105, 108, 255, 0.04);" 
                                         onclick="document.getElementById('imageInput').click()"
                                         onmouseover="this.style.backgroundColor='rgba(105, 108, 255, 0.1)';"
                                         onmouseout="this.style.backgroundColor='rgba(105, 108, 255, 0.04)';">
                                        
                                        <img id="imagePreview" 
                                            src="{{ isset($announcement) && $announcement->image_path ? Storage::url($announcement->image_path) : '' }}" 
                                            alt="Preview Gambar" 
                                            class="img-fluid w-100" 
                                            style="object-fit: cover; max-height: 350px; {{ (isset($announcement) && $announcement->image_path) ? '' : 'display: none;' }}">
                                        
                                        <div id="uploadPlaceholder" class="p-5" style="{{ (isset($announcement) && $announcement->image_path) ? 'display: none;' : '' }}">
                                            <div class="avatar avatar-xl bg-white text-primary rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center shadow-sm border" style="width: 60px; height: 60px;">
                                                <i class="bx bx-cloud-upload" style="font-size: 2rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1 text-dark" style="font-size: 1.1rem;">Pilih dari Penyimpanan</h6>
                                            <small class="text-muted">PNG, JPG, JPEG up to 5MB</small>
                                        </div>
                                    </div>
                                    <input type="file" name="image" id="imageInput" class="d-none" accept="image/*" onchange="previewImage(this)">
                                    
                                    <!-- Remove Button -->
                                    <button type="button" id="removeImageBtn" class="btn btn-danger btn-sm position-absolute fw-bold shadow-sm" style="top: 10px; right: 10px; padding: 0px 8px; font-size: 20px; line-height: 1.2; border-radius: 8px; z-index: 10; {{ (isset($announcement) && $announcement->image_path) ? '' : 'display: none;' }}" onclick="clearSingleImage(event)">
                                        &times;
                                    </button>
                                </div>
                                <input type="hidden" name="delete_single_image" id="delete_single_image" value="0">
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Card 2: Detail Utama -->
                <div class="card shadow-sm mb-4 bg-white border-0" style="border-radius: 16px; overflow: hidden;">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="bx bx-detail me-2"></i>Detail Utama</h5>
                    </div>
                    <div class="card-body mt-4">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-lg" placeholder="Masukkan judul..." value="{{ isset($announcement) ? $announcement->title : (isset($laporan) ? 'Tindak Lanjut Laporan: ' . $laporan->nama : '') }}" required>
                            <div class="form-text text-muted">Buat judul yang menarik dan padat (maksimal 100 karakter).</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi / Isi <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="8" placeholder="Tuliskan isi selengkapnya di sini... (Gunakan bahasa yang jelas dan mudah dipahami)" required>{{ isset($announcement) ? $announcement->description : '' }}</textarea>
                            <div class="form-text text-muted">Jelaskan secara detail informasi yang ingin disampaikan ke warga.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-xl-4">
                <!-- Card 3: Pengaturan & Kategori -->
                <div class="card shadow-sm mb-4 bg-white border-0" style="border-radius: 16px; overflow: hidden;">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="bx bx-cog me-2"></i>Pengaturan & Kategori</h5>
                    </div>
                    <div class="card-body mt-4">
                        <div class="mb-4">
                            <label class="form-label fw-semibold"><i class="bx bx-category me-1"></i>Tipe Kategori <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="Pengumuman" {{ (isset($announcement) && $announcement->type == 'Pengumuman') ? 'selected' : '' }}>Pengumuman Biasa / Berita</option>
                                <option value="Event" {{ (isset($announcement) && $announcement->type == 'Event') ? 'selected' : '' }}>Acara / Event</option>
                                <option value="Gotong Royong" {{ (isset($announcement) && $announcement->type == 'Gotong Royong') || isset($laporan) ? 'selected' : '' }}>Gotong Royong</option>
                            </select>
                            <div class="form-text text-muted">Membantu warga memfilter jenis informasi.</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold"><i class="bx bx-calendar-event me-1"></i>Tanggal & Waktu (Opsional)</label>
                            
                            <div class="row g-2">
                                <div class="col-7">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                        <input type="date" id="ui_date" class="form-control" onchange="updateEventDate()">
                                    </div>
                                </div>
                                <div class="col-5">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="bx bx-time-five"></i></span>
                                        <input type="time" id="ui_time" class="form-control" onchange="updateEventDate()">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden input to submit the actual combined format -->
                            <input type="hidden" name="event_date" id="event_date" value="{{ isset($announcement) && $announcement->event_date ? $announcement->event_date->format('Y-m-d\TH:i') : '' }}">
                            
                            <div class="form-text text-muted mt-2">Kapan acara ini dilaksanakan? Kosongkan jika bukan *event*.</div>
                            
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const hiddenDate = document.getElementById('event_date').value;
                                    if(hiddenDate) {
                                        const parts = hiddenDate.split('T');
                                        if(parts.length === 2) {
                                            document.getElementById('ui_date').value = parts[0];
                                            document.getElementById('ui_time').value = parts[1];
                                        }
                                    }
                                });

                                function updateEventDate() {
                                    const dateVal = document.getElementById('ui_date').value;
                                    const timeVal = document.getElementById('ui_time').value;
                                    const hiddenInput = document.getElementById('event_date');
                                    
                                    if (dateVal && timeVal) {
                                        hiddenInput.value = dateVal + 'T' + timeVal;
                                    } else if (dateVal) {
                                        hiddenInput.value = dateVal + 'T09:00';
                                        document.getElementById('ui_time').value = '09:00';
                                    } else {
                                        hiddenInput.value = '';
                                        document.getElementById('ui_time').value = '';
                                    }
                                }
                            </script>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><i class="bx bx-map-pin me-1"></i>Lokasi (Opsional)</label>
                            <input type="text" name="location" class="form-control" placeholder="Contoh: Balai Desa..." value="{{ isset($announcement) ? $announcement->location : '' }}">
                            <div class="form-text text-muted">Dimana titik kumpul atau pelaksanaannya?</div>
                        </div>
                    </div>
                </div>

                @if($category === 'Pengumuman')
                <!-- Card 4: Target Audiens -->
                <div class="card shadow-sm mb-4 bg-white border-0" style="border-radius: 16px; overflow: hidden;">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="bx bx-target-lock me-2"></i>Target Audiens</h5>
                    </div>
                    <div class="card-body mt-4">
                        <input type="hidden" name="target_region_id" id="final_target_region_id" value="{{ isset($announcement) ? $announcement->target_audience_id : '' }}" required>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><i class="bx bx-map me-1"></i>Target Kabupaten <span class="text-danger">*</span></label>
                            <select id="select_kabupaten" class="form-select" required>
                                <option value="">Memuat...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><i class="bx bx-map-alt me-1"></i>Target Kecamatan</label>
                            <select id="select_kecamatan" class="form-select" disabled>
                                <option value="">-- Pilih --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><i class="bx bx-home-heart me-1"></i>Target Desa</label>
                            <select id="select_desa" class="form-select" disabled>
                                <option value="">-- Pilih --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><i class="bx bx-buildings me-1"></i>Target RW</label>
                            <select id="select_rw" class="form-select" disabled>
                                <option value="">-- Pilih --</option>
                            </select>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Card 5: Aksi Publikasi -->
                <div class="card shadow-sm mb-4 bg-white border-0" style="border-radius: 16px; overflow: hidden;">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="bx bx-paper-plane me-2"></i>Aksi Publikasi</h5>
                    </div>
                    <div class="card-body mt-3">
                        
                        <style>
                        @keyframes flyAway {
                            0% { transform: translate(0, 0) scale(1); opacity: 1; }
                            40% { transform: translate(30px, -30px) scale(1.2); opacity: 0; }
                            41% { transform: translate(-30px, 30px) scale(0.5); opacity: 0; }
                            100% { transform: translate(0, 0) scale(1); opacity: 1; }
                        }
                        .anim-fly { animation: flyAway 0.8s ease-in-out forwards; }

                        @keyframes dropInBox {
                            0% { transform: translateY(0); opacity: 1; }
                            40% { transform: translateY(20px) scale(0.8); opacity: 0; }
                            41% { transform: translateY(-20px) scale(0.8); opacity: 0; }
                            100% { transform: translateY(0); opacity: 1; }
                        }
                        .anim-drop { animation: dropInBox 0.8s ease-in-out forwards; }
                        
                        .btn-animated { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
                        .btn-animated:active { transform: scale(0.95); }
                        
                        .neon-blue {
                            box-shadow: 0 0 20px 8px rgba(105, 108, 255, 0.6) !important;
                            border-color: #696cff !important;
                            transform: scale(0.98);
                        }
                        
                        .neon-gray {
                            box-shadow: 0 0 20px 8px rgba(133, 146, 163, 0.5) !important;
                            border-color: #8592a3 !important;
                            background-color: #f8f9fa !important;
                            transform: scale(0.98);
                        }
                        </style>

                        <!-- Hidden actual input to satisfy backend ->has('is_active') -->
                        <input type="checkbox" name="is_active" id="is_active" value="1" class="d-none" checked>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <button type="button" id="btn-publish" onclick="submitWithAnimation('publish', event)" class="btn btn-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 text-center btn-animated shadow-sm" style="border-radius: 12px;">
                                    <i id="icon-publish" class='bx bx-send fs-1 mb-2'></i>
                                    <span class="fw-bold fs-5">Terbitkan</span>
                                    <small class="d-block text-wrap mt-1 opacity-75" style="font-size: 0.75rem; line-height: 1.2;">Kirim & tampil di aplikasi</small>
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" id="btn-draft" onclick="submitWithAnimation('draft', event)" class="btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 text-center btn-animated" style="border-radius: 12px; border-width: 2px;">
                                    <i id="icon-draft" class='bx bx-archive-in fs-1 mb-2'></i>
                                    <span class="fw-bold fs-5">Simpan Draft</span>
                                    <small class="d-block text-wrap mt-1 opacity-75" style="font-size: 0.75rem; line-height: 1.2;">Simpan tertutup</small>
                                </button>
                            </div>
                        </div>

                        <a href="{{ route('admin.announcements.index', ['category' => $category]) }}" class="btn btn-light btn-lg w-100 fw-semibold text-secondary mt-2" style="border-radius: 12px;">
                            <i class="bx bx-arrow-back me-2"></i> Batal / Kembali
                        </a>

                        <script>
                        function submitWithAnimation(type, event) {
                            event.preventDefault();
                            const form = event.target.closest('form');
                            
                            // Check form validity before animating
                            if (!form.checkValidity()) {
                                form.reportValidity();
                                return;
                            }

                            if (type === 'publish') {
                                document.getElementById('is_active').checked = true;
                                const btn = document.getElementById('btn-publish');
                                const icon = document.getElementById('icon-publish');
                                
                                btn.classList.add('neon-blue');
                                icon.classList.add('anim-fly');
                                
                                setTimeout(() => {
                                    icon.className = 'bx bx-check-circle fs-1 mb-2 text-white';
                                    setTimeout(() => form.submit(), 300);
                                }, 400);
                                
                            } else {
                                document.getElementById('is_active').checked = false;
                                const btn = document.getElementById('btn-draft');
                                const icon = document.getElementById('icon-draft');
                                
                                btn.classList.add('neon-gray');
                                icon.classList.add('anim-drop');
                                
                                setTimeout(() => {
                                    icon.className = 'bx bx-check-circle fs-1 mb-2 text-success';
                                    setTimeout(() => form.submit(), 300);
                                }, 400);
                            }
                        }
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@if($category === 'Berita')
<script>
    let photoCounter = 0;
    
    function addPhotoField() {
        photoCounter++;
        const id = 'img_upload_' + photoCounter;
        const previewId = 'preview_img_' + photoCounter;
        
        const input = document.createElement('input');
        input.type = 'file';
        input.name = 'images[]';
        input.accept = 'image/*';
        input.className = 'd-none';
        input.id = id;
        
        input.onchange = function() {
            if (this.files && this.files[0]) {
                document.getElementById('multi-preview-grid').style.setProperty('display', 'flex', 'important');
                
                const grid = document.getElementById('multi-preview-grid');
                const box = document.createElement('div');
                box.className = 'position-relative border rounded overflow-hidden shadow-sm';
                box.style.width = '150px';
                box.style.height = '150px';
                box.id = 'box_' + photoCounter;
                
                box.innerHTML = `
                    <img id="${previewId}" class="w-100 h-100" style="object-fit: cover;" src="" alt="Preview">
                    <button type="button" onclick="removePhoto(${photoCounter})" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 fw-bold" style="padding: 0px 6px; font-size: 16px; line-height: 1.2;">
                        &times;
                    </button>
                `;
                grid.appendChild(box);
                
                if (typeof initGlobalCropper === 'function') {
                    initGlobalCropper(this, previewId, NaN, true);
                }
            } else {
                this.remove();
            }
        };
        
        document.getElementById('hidden-inputs-container').appendChild(input);
        input.click();
    }
    
    function removePhoto(idNum) {
        const input = document.getElementById('img_upload_' + idNum);
        const box = document.getElementById('box_' + idNum);
        if (input) input.remove();
        if (box) box.remove();
        
        const grid = document.getElementById('multi-preview-grid');
        if (grid.children.length === 0) {
            grid.style.setProperty('display', 'none', 'important');
        }
    }
</script>
@else
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            if (typeof initGlobalCropper === 'function') {
                initGlobalCropper(input, 'imagePreview', 16/9, true);
            }
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
                document.getElementById('uploadPlaceholder').style.display = 'none';
                document.getElementById('removeImageBtn').style.display = 'block';
                document.getElementById('delete_single_image').value = '0';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    function clearSingleImage(event) {
        event.stopPropagation(); // prevent triggering the click on the upload box
        document.getElementById('imageInput').value = '';
        document.getElementById('imagePreview').src = '';
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('uploadPlaceholder').style.display = 'block';
        document.getElementById('removeImageBtn').style.display = 'none';
        document.getElementById('delete_single_image').value = '1';
    }
</script>
@endif

@if($category === 'Pengumuman')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const regions = @json($regions);
        const userRole = "{{ $userRole }}";
        
        let initialTargetRegionId = document.getElementById('final_target_region_id').value;
        let initialKab = '';
        let initialKec = '';
        let initialDesa = '';
        let initialRw = '';

        if(initialTargetRegionId) {
            let selectedRegion = regions.find(r => r.id == initialTargetRegionId);
            if(selectedRegion) {
                if(selectedRegion.type === 'rw') {
                    initialRw = selectedRegion.id;
                    initialDesa = selectedRegion.parent_id;
                    let desa = regions.find(r => r.id == initialDesa);
                    if(desa) {
                        initialKec = desa.parent_id;
                        let kec = regions.find(r => r.id == initialKec);
                        if(kec) initialKab = kec.parent_id;
                    }
                } else if(selectedRegion.type === 'desa') {
                    initialDesa = selectedRegion.id;
                    initialKec = selectedRegion.parent_id;
                    let kec = regions.find(r => r.id == initialKec);
                    if(kec) initialKab = kec.parent_id;
                } else if(selectedRegion.type === 'kecamatan') {
                    initialKec = selectedRegion.id;
                    initialKab = selectedRegion.parent_id;
                } else if(selectedRegion.type === 'kabupaten') {
                    initialKab = selectedRegion.id;
                }
            }
        }

        const kabSelect = document.getElementById('select_kabupaten');
        const kecSelect = document.getElementById('select_kecamatan');
        const desaSelect = document.getElementById('select_desa');
        const rwSelect = document.getElementById('select_rw');
        const finalInput = document.getElementById('final_target_region_id');

        const kabupatenData = regions.filter(r => r.type === 'kabupaten');
        kabupatenData.forEach(kab => {
            let option = new Option(kab.name, kab.id);
            kabSelect.add(option);
        });

        if(kabupatenData.length > 0) {
            if(!initialKab) {
                kabSelect.value = kabupatenData[0].id;
            } else {
                kabSelect.value = initialKab;
            }
        }

        function updateKecamatan() {
            kecSelect.innerHTML = '<option value="">-- Semua Kecamatan --</option>';
            desaSelect.innerHTML = '<option value="">-- Pilih --</option>';
            if(rwSelect) rwSelect.innerHTML = '<option value="">-- Pilih --</option>';
            desaSelect.disabled = true;
            if(rwSelect) rwSelect.disabled = true;

            const kabId = kabSelect.value;
            finalInput.value = kabId; 

            if (!kabId) {
                kecSelect.disabled = true;
                return;
            }

            const kecData = regions.filter(r => r.type === 'kecamatan' && r.parent_id == kabId);
            kecData.forEach(kec => {
                let option = new Option(kec.name, kec.id);
                kecSelect.add(option);
            });

            if (kecData.length > 0) {
                kecSelect.disabled = false;
                if(initialKec) {
                    kecSelect.value = initialKec;
                    updateDesa(); 
                }
            } else {
                kecSelect.disabled = true;
            }
        }

        function updateDesa() {
            desaSelect.innerHTML = '<option value="">-- Semua Desa --</option>';
            if(rwSelect) rwSelect.innerHTML = '<option value="">-- Pilih --</option>';
            if(rwSelect) rwSelect.disabled = true;
            
            const kecId = kecSelect.value;
            
            if (!kecId) {
                desaSelect.disabled = true;
                finalInput.value = kabSelect.value; 
                return;
            }
            
            finalInput.value = kecId; 

            const desaData = regions.filter(r => r.type === 'desa' && r.parent_id == kecId);
            desaData.forEach(desa => {
                let option = new Option(desa.name, desa.id);
                desaSelect.add(option);
            });

            if (desaData.length > 0) {
                desaSelect.disabled = false;
                if(initialDesa) {
                    desaSelect.value = initialDesa;
                    finalInput.value = initialDesa;
                    if(typeof updateRw === 'function') updateRw();
                }
            } else {
                desaSelect.disabled = true;
            }
        }

        function updateRw() {
            if(!rwSelect) return;
            rwSelect.innerHTML = '<option value="">-- Semua RW --</option>';
            const desaId = desaSelect.value;
            
            if (!desaId) {
                rwSelect.disabled = true;
                finalInput.value = kecSelect.value; 
                return;
            }
            
            finalInput.value = desaId; 

            const rwData = regions.filter(r => r.type === 'rw' && r.parent_id == desaId);
            rwData.forEach(rw => {
                let option = new Option(rw.name, rw.id);
                rwSelect.add(option);
            });

            if (rwData.length > 0) {
                rwSelect.disabled = false;
                if(initialRw) {
                    rwSelect.value = initialRw;
                    finalInput.value = initialRw;
                }
            } else {
                rwSelect.disabled = true;
            }
        }

        kabSelect.addEventListener('change', updateKecamatan);
        kecSelect.addEventListener('change', updateDesa);
        desaSelect.addEventListener('change', updateRw);
        if(rwSelect) {
            rwSelect.addEventListener('change', function() {
                if(this.value) {
                    finalInput.value = this.value;
                } else {
                    finalInput.value = desaSelect.value;
                }
            });
        }

        updateKecamatan();
        
        @if(auth()->user()->role === 'admin_kecamatan')
            kabSelect.value = "{{ auth()->user()->region->parent_id ?? '' }}";
            kabSelect.dispatchEvent(new Event('change'));
            kecSelect.value = "{{ auth()->user()->region_id }}";
            kecSelect.dispatchEvent(new Event('change'));
            kabSelect.disabled = true;
            kecSelect.disabled = true;
        @elseif(auth()->user()->role === 'admin_desa')
            let desaId = "{{ auth()->user()->region_id }}";
            let myDesa = regions.find(r => r.id == desaId);
            if(myDesa) {
                kabSelect.value = myDesa.parent_id; 
                let myKec = regions.find(r => r.id == myDesa.parent_id);
                if(myKec) {
                    kabSelect.value = myKec.parent_id;
                    kabSelect.dispatchEvent(new Event('change'));
                    kecSelect.value = myKec.id;
                    kecSelect.dispatchEvent(new Event('change'));
                    desaSelect.value = myDesa.id;
                    desaSelect.dispatchEvent(new Event('change'));
                }
                kabSelect.disabled = true;
                kecSelect.disabled = true;
                desaSelect.disabled = true;
            }
        @endif    });
</script>
@endif

@endsection
