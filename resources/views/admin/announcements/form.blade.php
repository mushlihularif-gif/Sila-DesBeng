@extends('admin.layouts.admin')

@section('title', isset($announcement) ? 'Edit ' . $category : 'Buat ' . $category . ' Baru')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Sistem / {{ $category }} /</span> {{ isset($announcement) ? 'Edit' : 'Buat Baru' }}
    </h4>

    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bx bx-edit-alt me-2"></i>Formulir {{ isset($announcement) ? 'Edit' : 'Buat' }} {{ $category }}</h5>
                </div>
                <div class="card-body mt-4">
                    
                    @if(isset($laporan))
                        <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                            <span class="alert-icon text-info me-2">
                                <i class="bx bx-info-circle bx-md"></i>
                            </span>
                            <div>
                                <strong>Menindaklanjuti Laporan Warga!</strong><br>
                                Anda sedang membuat event/berita berdasarkan laporan: <em>"{{ $laporan->nama }}"</em> dari {{ $laporan->user->name ?? 'Warga' }}.
                            </div>
                        </div>
                    @endif

                    <form action="{{ isset($announcement) ? route('admin.announcements.update', $announcement->id) : route('admin.announcements.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($announcement))
                            @method('PUT')
                        @endif

                        <input type="hidden" name="post_category" value="{{ $category }}">

                        @if(isset($laporan))
                            <input type="hidden" name="laporan_id" value="{{ $laporan->id }}">
                        @endif

                        @if($category === 'Berita')
                            <!-- Upload Banyak Gambar untuk Berita (Cropper) -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold text-dark">Foto Dokumentasi (Bisa pilih lebih dari satu)</label>
                                
                                <div class="border border-2 border-dashed border-primary rounded p-4 bg-light">
                                    <div id="multi-preview-grid" class="d-flex flex-wrap gap-3 mb-3" style="display: none !important;">
                                        <!-- Previews akan muncul di sini -->
                                    </div>
                                    
                                    <div id="hidden-inputs-container"></div>
                                    
                                    <div class="text-center">
                                        <button type="button" onclick="addPhotoField()" class="btn btn-outline-primary">
                                            <i class="bx bx-image me-1"></i> Pilih dari Penyimpanan
                                        </button>
                                        <div class="form-text mt-2">Anda bisa mengatur posisi / crop untuk setiap foto yang ditambahkan.</div>
                                    </div>
                                </div>
                                
                                @if(isset($announcement) && $announcement->images->count() > 0)
                                    <div class="mt-4 p-3 border rounded bg-white">
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
                                                <button type="button" onclick="removePhoto(${photoCounter})" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" style="padding: 2px 6px;">
                                                    <i class="bx bx-x"></i>
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
                            pload Single Image untuk Pengumuman -->
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
                                            <div class="avatar avatar-xl bg-white text-secondary rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center shadow-sm border" style="width: 60px; height: 60px;">
                                                <i class="bx bx-image-alt" style="font-size: 2rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1 text-dark" style="font-size: 1.1rem;">Pilih dari Penyimpanan</h6>
                                            <small class="text-muted">PNG, JPG, JPEG up to 5MB</small>
                                        </div>
                                    </div>
                                    <input type="file" name="image" id="imageInput" class="d-none" accept="image/*" onchange="previewImage(this)">
                                </div>
                            </div>
                        @endif

                        <hr class="my-4">

                        <div class="row g-3">
                            <div class="col-md-{{ $category === 'Berita' ? '12' : '3' }}">
                                <label class="form-label fw-semibold"><i class="bx bx-category me-1"></i>Tipe Kategori <span class="text-danger">*</span></label>
                                <select name="type" class="form-select border-primary" required>
                                    <option value="Pengumuman" {{ (isset($announcement) && $announcement->type == 'Pengumuman') ? 'selected' : '' }}>Pengumuman Biasa / Berita</option>
                                    <option value="Event" {{ (isset($announcement) && $announcement->type == 'Event') ? 'selected' : '' }}>Acara / Event</option>
                                    <option value="Gotong Royong" {{ (isset($announcement) && $announcement->type == 'Gotong Royong') || isset($laporan) ? 'selected' : '' }}>Gotong Royong</option>
                                </select>
                            </div>

                            @if($category === 'Pengumuman')
                                <input type="hidden" name="target_region_id" id="final_target_region_id" value="{{ isset($announcement) ? $announcement->target_audience_id : '' }}" required>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold"><i class="bx bx-map me-1"></i>Target Kabupaten <span class="text-danger">*</span></label>
                                    <select id="select_kabupaten" class="form-select border-primary" required>
                                        <option value="">Memuat...</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold"><i class="bx bx-map-alt me-1"></i>Target Kecamatan</label>
                                    <select id="select_kecamatan" class="form-select border-primary" disabled>
                                        <option value="">-- Pilih --</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold"><i class="bx bx-home-heart me-1"></i>Target Desa</label>
                                    <select id="select_desa" class="form-select border-primary" disabled>
                                        <option value="">-- Pilih --</option>
                                    </select>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4">
                            <label class="form-label fw-semibold">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-lg border-primary" placeholder="Masukkan judul..." value="{{ isset($announcement) ? $announcement->title : (isset($laporan) ? 'Tindak Lanjut Laporan: ' . $laporan->nama : '') }}" required>
                        </div>

                        <div class="mt-4">
                            <label class="form-label fw-semibold">Deskripsi / Isi <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control border-primary" rows="6" placeholder="Tuliskan isi selengkapnya di sini..." required>{{ isset($announcement) ? $announcement->description : '' }}</textarea>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Event (Opsional)</label>
                                <input type="datetime-local" name="event_date" class="form-control" value="{{ isset($announcement) && $announcement->event_date ? $announcement->event_date->format('Y-m-d\TH:i') : '' }}">
                                <div class="form-text">Isi jika ini adalah acara yang memiliki waktu pelaksanaan.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Lokasi (Opsional)</label>
                                <input type="text" name="location" class="form-control" placeholder="Contoh: Balai Desa, Lapangan..." value="{{ isset($announcement) ? $announcement->location : '' }}">
                            </div>
                        </div>

                        <div class="mt-4 form-check form-switch form-switch-lg mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ isset($announcement) && !$announcement->is_active ? '' : 'checked' }}>
                            <label class="form-check-label ms-2" for="is_active">Langsung Publikasikan (Aktif)</label>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="{{ route('admin.announcements.index', ['category' => $category]) }}" class="btn btn-label-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="bx bx-save me-1"></i> Simpan & Publikasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            if (typeof initGlobalCropper === 'function') {
                initGlobalCropper(input, 'imagePreview', 16/9, true);
                document.getElementById('imagePreview').style.display = 'block';
                document.getElementById('uploadPlaceholder').style.display = 'none';
            } else {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                    document.getElementById('uploadPlaceholder').style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    }
</script>

@if($category === 'Pengumuman')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const regions = @json($regions);
        const userRole = "{{ $userRole }}";
        
        let initialTargetRegionId = document.getElementById('final_target_region_id').value;
        let initialKab = '';
        let initialKec = '';
        let initialDesa = '';

        if(initialTargetRegionId) {
            let selectedRegion = regions.find(r => r.id == initialTargetRegionId);
            if(selectedRegion) {
                if(selectedRegion.type === 'desa') {
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
            kecSelect.innerHTML = '<option value="">-- Pilih Semua (Se-Kabupaten) --</option>';
            desaSelect.innerHTML = '<option value="">-- Pilih --</option>';
            desaSelect.disabled = true;

            const kabId = kabSelect.value;
            finalInput.value = kabId; // Default to kab if no kec selected

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
            desaSelect.innerHTML = '<option value="">-- Pilih Semua (Se-Kecamatan) --</option>';
            const kecId = kecSelect.value;
            
            if (!kecId) {
                desaSelect.disabled = true;
                finalInput.value = kabSelect.value; // Revert to kab
                return;
            }
            
            finalInput.value = kecId; // Update to kec

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
                }
            } else {
                desaSelect.disabled = true;
            }
        }

        kabSelect.addEventListener('change', updateKecamatan);
        kecSelect.addEventListener('change', updateDesa);
        desaSelect.addEventListener('change', function() {
            if(this.value) {
                finalInput.value = this.value;
            } else {
                finalInput.value = kecSelect.value;
            }
        });

        // Initialize cascades
        updateKecamatan();
        
        // Auto select defaults based on user role to restrict sending announcement to other region
        // The controller validates it securely, but UI should guide them
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
                kabSelect.value = myDesa.parent_id; // actually this is kec_id. Need to find kab_id
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
        @endif
    });
</script>
@endif

@endsection
