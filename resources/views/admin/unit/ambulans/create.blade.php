@extends('admin.layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light"><a href="{{ route('admin.unit.ambulans.index') }}">Kendaraan Operasional</a> /</span> Tambah Kendaraan
    </h4>

    <div class="row">
        <!-- Form Input -->
        <div class="col-xl-7 col-lg-7">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white border-bottom pt-4 pb-3">
                    <h5 class="mb-0 text-primary fw-bold"><i class="bx bx-plus-circle me-2"></i>Informasi Kendaraan</h5>
                    <small class="text-muted">Masukkan detail kendaraan operasional beserta data penanggung jawab (supir).</small>
                </div>
                <div class="card-body pt-4">
                    <form action="{{ route('admin.unit.ambulans.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Nama Kendaraan -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Nama Kendaraan</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-car"></i></span>
                                <input type="text" class="form-control" name="nama_mobil" placeholder="Misal: Ambulans Siaga, Truk Sampah, Bus Desa" required>
                            </div>
                            <div class="form-text">Berikan nama armada yang mudah dikenali.</div>
                        </div>

                        <!-- Foto Kendaraan -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark" for="foto">Foto Kendaraan</label>
                            <div class="upload-box border rounded p-3 text-center" style="cursor: pointer; border-style: dashed !important;" onclick="document.getElementById('foto').click()">
                                <div id="preview_foto" class="preview-container position-relative" style="display:none;">
                                    <img src="#" alt="Preview" class="preview-image img-fluid rounded" style="max-height: 200px; object-fit: cover;" />
                                    <button type="button" class="btn btn-danger position-absolute" style="top: -10px; right: -10px; width: 32px; height: 32px; border-radius: 50%; padding: 0; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3); color: white; z-index: 10;" onclick="event.stopPropagation(); clearFile('foto', 'preview_foto')">
                                        <span style="font-size: 24px; font-weight: bold; line-height: 1;">&times;</span>
                                    </button>
                                </div>
                                <div id="placeholder_foto" class="upload-placeholder py-4">
                                    <i class='bx bx-cloud-upload text-primary' style="font-size: 48px;"></i>
                                    <p class="mb-0 mt-2 fw-semibold">Klik untuk upload foto</p>
                                    <small class="text-muted">Format: JPG, PNG, WEBP (Maksimal 2MB)</small>
                                </div>
                                <input type="file" id="foto" name="foto" class="d-none" accept="image/jpeg, image/png, image/webp" onchange="previewFile(this, 'preview_foto', 'placeholder_foto')">
                            </div>
                        </div>

                        <!-- Plat Nomor -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Plat Nomor</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                <input type="text" class="form-control text-uppercase" name="nomor_plat" id="inputPlat" placeholder="Misal: BM 1234 DV" required maxlength="12">
                            </div>
                            <div class="form-text">Masukkan plat dengan spasi yang benar. Visualisasi akan muncul di samping.</div>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-semibold text-dark mb-3"><i class="bx bx-user-pin me-2"></i>Data Supir / Penanggung Jawab</h6>

                        <!-- Supir Dropdown -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Pilih Supir <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <select name="supir_ids[]" class="form-select" multiple required style="height: 120px;">
                                    <option value="" disabled>-- Tahan CTRL untuk memilih lebih dari 1 supir --</option>
                                    @foreach($supirs as $supir)
                                        <option value="{{ $supir->id }}">{{ $supir->nama }} ({{ $supir->kontak ?? 'Tanpa Kontak' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-text mt-2">
                                Supir belum didaftarkan? 
                                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#addSupirModal" class="fw-bold text-primary">
                                    <i class="bx bx-plus-circle"></i> Tambah Supir Baru
                                </a>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                            <a href="{{ route('admin.unit.ambulans.index') }}" class="btn btn-outline-secondary me-2"><i class="bx bx-x me-1"></i> Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Visualisasi Animasi Plat -->
        <div class="col-xl-5 col-lg-5">
            <div class="card mb-4 shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-header bg-white border-bottom pt-4 pb-3">
                    <h5 class="mb-0 text-primary fw-bold"><i class="bx bx-show me-2"></i>Visualisasi Kendaraan</h5>
                    <small class="text-muted">Pratinjau otomatis nomor registrasi polisi.</small>
                </div>
                <div class="card-body text-center pt-5 pb-5">
                    
                    <!-- Container Gambar & Overlay Text -->
                    <div class="position-relative d-inline-block mx-auto" style="max-width: 300px;">
                        <!-- Gambar Mobil Asli -->
                        <img src="{{ asset('Admin/img/platkendaraan/MOBILPLAT.png') }}" class="img-fluid" alt="Visualisasi Mobil">
                        
                        <!-- Teks Plat di Atas Gambar -->
                        <div class="position-absolute w-100 d-flex justify-content-center align-items-center" style="bottom: 32%; left: 0;">
                            <span id="previewPlatText" style="font-family: 'Arial Black', Impact, sans-serif; font-size: 0.55rem; font-weight: 900; color: #111; letter-spacing: 0px; line-height: 1;">BM XXXX XX</span>
                        </div>
                    </div>

                    <!-- Keterangan Statis (Fokus Bacaan) -->
                    <div class="mt-5 p-3 bg-light rounded text-center border">
                        <span class="d-block text-muted small mb-1 fw-semibold text-uppercase">Plat Kendaraan Tercatat:</span>
                        <h3 id="previewPlatTextStatic" class="text-dark fw-bolder mb-0" style="letter-spacing: 2px;">BM XXXX XX</h3>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputPlat = document.getElementById('inputPlat');
        const previewPlatText = document.getElementById('previewPlatText');
        const previewPlatTextStatic = document.getElementById('previewPlatTextStatic');

        inputPlat.addEventListener('input', function(e) {
            // Ubah huruf kecil jadi besar (uppercase) secara otomatis saat mengetik
            let val = this.value.toUpperCase();
            
            if(val.trim() === '') {
                previewPlatText.textContent = 'BM XXXX XX';
                previewPlatTextStatic.textContent = 'BM XXXX XX';
            } else {
                previewPlatText.textContent = val;
                previewPlatTextStatic.textContent = val;
            }
        });
    });

    // Fungsi untuk preview file gambar
    function previewFile(input, previewId, placeholderId) {
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);
        const img = preview ? preview.querySelector('img') : null;

        if (input.files && input.files[0]) {
            if (typeof initGlobalCropper === 'function') {
                initGlobalCropper(input, img || previewId, NaN, true);
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                if (img) img.src = e.target.result;
                if (preview) preview.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Fungsi untuk menghapus file dan reset input
    function clearFile(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById('placeholder_' + inputId);
        const img = preview.querySelector('img');

        input.value = '';
        img.src = '#';
        preview.style.display = 'none';
        placeholder.style.display = 'block';
    }

    document.getElementById('addSupirForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const btn = document.getElementById('btnSaveSupir');
        btn.innerHTML = 'Menyimpan...';
        btn.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Tambahkan option baru ke select
                const select = document.querySelector('select[name="supir_id"]');
                const option = new Option(data.supir.nama + ' (' + (data.supir.kontak || 'Tanpa Kontak') + ')', data.supir.id, true, true);
                select.add(option);
                
                // Tutup modal
                bootstrap.Modal.getInstance(document.getElementById('addSupirModal')).hide();
                form.reset();
            } else {
                alert('Gagal menyimpan supir.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan server.');
        })
        .finally(() => {
            btn.innerHTML = 'Simpan & Gunakan';
            btn.disabled = false;
        });
    });
</script>
@endsection

@push('modals')
<!-- Add Supir Modal -->
<div class="modal fade" id="addSupirModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Supir Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addSupirForm" action="{{ route('supir.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Supir / Petugas <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. WhatsApp / Kontak</label>
                        <input type="text" name="kontak" class="form-control">
                    </div>
                    <input type="hidden" name="status" value="Tersedia">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveSupir">Simpan & Gunakan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
