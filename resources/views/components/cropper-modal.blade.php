<!-- GLOBAL CROPPER MODAL (Framework Agnostic) -->
<style>
    #global-cropper-modal {
        display: none;
        position: fixed;
        z-index: 99999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.8);
        align-items: center;
        justify-content: center;
    }
    #global-cropper-modal.active {
        display: flex;
    }
    .cropper-modal-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    .cropper-img-container {
        width: 100%;
        max-height: 60vh;
        background-color: #f8f9fa;
        margin-bottom: 20px;
        border-radius: 8px;
        overflow: hidden;
    }
    .cropper-img-container img {
        display: block;
        max-width: 100%;
    }
    .cropper-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .btn-cropper-cancel {
        padding: 8px 16px;
        background-color: #f1f5f9;
        color: #475569;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        font-family: inherit;
    }
    .btn-cropper-save {
        padding: 8px 16px;
        background-color: #3b82f6;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        font-family: inherit;
    }
    .btn-cropper-save:hover { background-color: #2563eb; }
    .btn-cropper-cancel:hover { background-color: #e2e8f0; }
    .cropper-ratio-group {
        display: none; /* Hidden by default */
        justify-content: center;
        gap: 8px;
        margin-bottom: 15px;
    }
    .cropper-ratio-group.active {
        display: flex;
    }
    .btn-ratio {
        padding: 6px 12px;
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 20px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-ratio.active {
        background-color: #3b82f6;
        color: #fff;
        border-color: #3b82f6;
    }
</style>

<div id="global-cropper-modal">
    <div class="cropper-modal-content">
        <h4 style="margin-top: 0; margin-bottom: 15px; font-family: inherit; font-size: 1.25rem; font-weight: 600; color: #1e293b;">Potong Foto</h4>
        
        <div class="cropper-ratio-group" id="cropper-ratio-group">
            <button type="button" class="btn-ratio active" data-ratio="1">1:1 (Persegi)</button>
            <button type="button" class="btn-ratio" data-ratio="1.3333333333333333">4:3 (Melebar)</button>
            <button type="button" class="btn-ratio" data-ratio="NaN">Bebas</button>
        </div>

        <div class="cropper-img-container">
            <img id="cropper-image" src="" alt="Picture">
        </div>
        <div class="cropper-modal-actions">
            <button type="button" class="btn-cropper-cancel" id="btn-cropper-cancel">Batal</button>
            <button type="button" class="btn-cropper-save" id="btn-cropper-save">Simpan Foto</button>
        </div>
    </div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    window.globalCropperInstance = null;
    window.globalCropperInput = null;
    window.globalCropperPreview = null;
    
    function initGlobalCropper(inputElement, previewElementId, aspectRatio = 1, showRatioButtons = false) {
        if (!inputElement.files || !inputElement.files[0]) return;
        
        const file = inputElement.files[0];
        if (!file.type.startsWith('image/')) {
            showSiladesBengToast('warning', 'Perhatian', 'File harus berupa gambar');
            return;
        }

        const modal = document.getElementById('global-cropper-modal');
        const image = document.getElementById('cropper-image');
        
        window.globalCropperInput = inputElement;
        window.globalCropperPreview = typeof previewElementId === 'string' ? document.getElementById(previewElementId) : previewElementId;

        const ratioGroup = document.getElementById('cropper-ratio-group');
        if (showRatioButtons) {
            ratioGroup.classList.add('active');
            // Reset ratio buttons visually based on initial aspectRatio
            document.querySelectorAll('.btn-ratio').forEach(btn => {
                const r = parseFloat(btn.dataset.ratio);
                if ((isNaN(aspectRatio) && isNaN(r)) || r === aspectRatio) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        } else {
            ratioGroup.classList.remove('active');
        }
        
        const reader = new FileReader();
        reader.onload = function (e) {
            image.src = e.target.result;
            modal.classList.add('active');
            
            if (window.globalCropperInstance) {
                window.globalCropperInstance.destroy();
            }
            
            window.globalCropperInstance = new Cropper(image, {
                aspectRatio: aspectRatio,
                viewMode: 1,
                autoCropArea: 1,
            });
        };
        reader.readAsDataURL(file);
    }

    document.querySelectorAll('.btn-ratio').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!window.globalCropperInstance) return;
            
            document.querySelectorAll('.btn-ratio').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const ratio = parseFloat(this.dataset.ratio);
            window.globalCropperInstance.setAspectRatio(ratio);
        });
    });

    document.getElementById('btn-cropper-cancel').addEventListener('click', function() {
        document.getElementById('global-cropper-modal').classList.remove('active');
        if (window.globalCropperInstance) {
            window.globalCropperInstance.destroy();
            window.globalCropperInstance = null;
        }
        if (window.globalCropperInput) {
            window.globalCropperInput.value = ''; // Reset input
        }
    });

    document.getElementById('btn-cropper-save').addEventListener('click', function() {
        if (!window.globalCropperInstance) return;
        
        // Get Cropped canvas
        const canvas = window.globalCropperInstance.getCroppedCanvas({
            width: 800,
            height: 800
        });
        
        canvas.toBlob(function(blob) {
            const fileInput = window.globalCropperInput;
            const originalFile = fileInput.files[0];
            const fileName = originalFile.name;
            const fileType = originalFile.type;
            
            // Create a new File from blob
            const croppedFile = new File([blob], fileName, { type: fileType, lastModified: new Date().getTime() });
            
            // Use DataTransfer to replace files
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(croppedFile);
            fileInput.files = dataTransfer.files;
            
            // Update preview
            if (window.globalCropperPreview) {
                const url = URL.createObjectURL(blob);
                if (window.globalCropperPreview.tagName === 'IMG') {
                    window.globalCropperPreview.src = url;

                    // Dua kelas, bukan satu: pemotong ini dipakai di KEDUA sisi
                    // aplikasi. Sisi admin memakai Bootstrap ('d-none'), sisi
                    // warga memakai Tailwind ('hidden'). Sebelumnya hanya
                    // 'd-none' yang dilepas, sehingga di halaman profil warga
                    // gambarnya tetap tersembunyi setelah dipotong — foto yang
                    // sudah dipilih seolah tidak muncul sama sekali.
                    window.globalCropperPreview.classList.remove('d-none', 'hidden');

                    // Penampung sementara ikut disembunyikan, dengan nama id yang
                    // dipakai halaman profil warga.
                    const placeholder = document.getElementById('avatar-placeholder');
                    if (placeholder) {
                        placeholder.classList.add('hidden', 'd-none');
                    }

                    // Ingatkan bahwa fotonya baru menempel, belum tersimpan.
                    const belum = document.getElementById('belum-tersimpan');
                    if (belum) {
                        belum.classList.remove('hidden');
                    }

                    // Hide icon if exists
                    const parent = window.globalCropperPreview.parentElement;
                    if(parent) {
                        const icon = parent.querySelector('i');
                        if (icon) icon.style.display = 'none';
                    }
                } else if (window.globalCropperPreview.tagName === 'DIV') {
                    window.globalCropperPreview.style.backgroundImage = 'url(' + url + ')';
                    window.globalCropperPreview.style.backgroundSize = 'cover';
                    window.globalCropperPreview.style.backgroundPosition = 'center';
                }
                
                // If it's a default avatar block in Admin, we might need to replace outerHTML
                if (window.globalCropperPreview.classList.contains('avatar-default')) {
                     window.globalCropperPreview.outerHTML = '<img src="' + url + '" alt="user-avatar" class="avatar-preview rounded-circle" id="' + window.globalCropperPreview.id + '" />';
                }
            }
            
            // Close modal
            document.getElementById('global-cropper-modal').classList.remove('active');
            window.globalCropperInstance.destroy();
            window.globalCropperInstance = null;
            
        }, window.globalCropperInput.files[0].type, 0.9);
    });
</script>
