<!-- GLOBAL CROPPER MODAL (Framework Agnostic) -->
<style>
    #global-cropper-modal {
        display: none;
        position: fixed;
        z-index: 999999 !important;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
        padding: 16px;
        box-sizing: border-box;
    }
    #global-cropper-modal.active {
        display: flex !important;
    }
    .cropper-modal-content {
        background-color: #fff;
        padding: 24px;
        border-radius: 16px;
        width: 100%;
        max-width: 620px;
        max-height: 92vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
        box-sizing: border-box;
        animation: cropperModalFadeIn 0.2s ease;
    }
    @keyframes cropperModalFadeIn {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }
    .cropper-img-container {
        width: 100%;
        height: 380px;
        max-height: 52vh;
        background-color: #0f172a;
        margin-bottom: 20px;
        border-radius: 10px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .cropper-img-container img {
        display: block;
        max-width: 100%;
        max-height: 100%;
    }
    .cropper-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .btn-cropper-cancel {
        padding: 9px 20px;
        background-color: #f1f5f9;
        color: #475569;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-family: inherit;
        font-size: 0.9rem;
        transition: all 0.2s;
    }
    .btn-cropper-save {
        padding: 9px 22px;
        background-color: #3b82f6;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-family: inherit;
        font-size: 0.9rem;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
    }
    .btn-cropper-save:hover { background-color: #2563eb; }
    .btn-cropper-cancel:hover { background-color: #e2e8f0; }
    .cropper-ratio-group {
        display: none;
        justify-content: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 15px;
    }
    .cropper-ratio-group.active {
        display: flex;
    }
    .btn-ratio {
        padding: 6px 14px;
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
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 style="margin: 0; font-family: inherit; font-size: 1.25rem; font-weight: 700; color: #1e293b;">Sesuaikan & Potong Foto</h4>
            <button type="button" class="btn-close" onclick="document.getElementById('btn-cropper-cancel').click()" aria-label="Close" style="cursor: pointer; border: none; background: transparent; font-size: 1.25rem; line-height: 1;">&times;</button>
        </div>
        
        <div class="cropper-ratio-group" id="cropper-ratio-group">
            <button type="button" class="btn-ratio" data-ratio="1">1:1 (Persegi)</button>
            <button type="button" class="btn-ratio active" data-ratio="1.3333333333333333">4:3 (Standar)</button>
            <button type="button" class="btn-ratio" data-ratio="1.7777777777777777">16:9 (Landscape)</button>
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

{{-- Load Cropper CSS & JS dari vendor lokal secara andal --}}
<link rel="stylesheet" href="{{ asset('vendor/cropperjs/cropper.min.css') }}" />
<script src="{{ asset('vendor/cropperjs/cropper.min.js') }}"></script>
<script>
    window.globalCropperInstance = null;
    window.globalCropperInput = null;
    window.globalCropperPreview = null;
    
    function initGlobalCropper(inputElement, previewElementId, aspectRatio = 1, showRatioButtons = false) {
        if (!inputElement || !inputElement.files || !inputElement.files[0]) return false;
        
        const file = inputElement.files[0];
        const fileName = (file.name || '').toLowerCase().trim();
        const isImage = (file.type && file.type.startsWith('image/')) || /\.(jpe?g|png|webp|gif|bmp|svg)$/i.test(fileName);
        if (!isImage) {
            if (typeof showSiladesBengToast === 'function') {
                showSiladesBengToast('warning', 'Perhatian', 'File harus berupa gambar (JPG, PNG, WEBP)');
            } else {
                alert('File harus berupa gambar (JPG, PNG, WEBP)');
            }
            inputElement.value = '';
            return false;
        }

        const modal = document.getElementById('global-cropper-modal');
        if (!modal) {
            console.warn('Modal global-cropper-modal tidak ditemukan');
            return false;
        }

        // Self-healing: pastikan modal berada langsung di document.body agar tidak terkurung kontainer ber-display:none
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        const container = modal.querySelector('.cropper-img-container');
        if (!container) {
            console.warn('Container .cropper-img-container tidak ditemukan');
            return false;
        }

        window.globalCropperInput = inputElement;
        window.globalCropperPreview = typeof previewElementId === 'string' ? document.getElementById(previewElementId) : previewElementId;

        const ratioGroup = document.getElementById('cropper-ratio-group');
        if (ratioGroup) {
            if (showRatioButtons) {
                ratioGroup.classList.add('active');
                document.querySelectorAll('.btn-ratio').forEach(btn => {
                    const r = parseFloat(btn.dataset.ratio);
                    if ((isNaN(aspectRatio) && isNaN(r)) || Math.abs(r - aspectRatio) < 0.01) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            } else {
                ratioGroup.classList.remove('active');
            }
        }

        if (window.globalCropperInstance) {
            try {
                window.globalCropperInstance.destroy();
            } catch (e) {}
            window.globalCropperInstance = null;
        }

        const blobUrl = URL.createObjectURL(file);
        container.innerHTML = '<img id="cropper-image" src="' + blobUrl + '" alt="Picture" style="max-width: 100%; display: block;">';
        const imgEl = document.getElementById('cropper-image');

        modal.classList.add('active');
        modal.style.display = 'flex';

        function startCropper() {
            if (window.globalCropperInstance) return;
            if (typeof Cropper !== 'undefined' && imgEl) {
                try {
                    window.globalCropperInstance = new Cropper(imgEl, {
                        aspectRatio: isNaN(aspectRatio) ? NaN : aspectRatio,
                        viewMode: 1,
                        autoCropArea: 0.95,
                        responsive: true,
                        restore: false,
                        checkCrossOrigin: false,
                    });
                } catch (err) {
                    console.error('Cropper initialization error:', err);
                }
            }
        }

        if (imgEl) {
            if (imgEl.complete && imgEl.naturalWidth > 0) {
                startCropper();
            } else {
                imgEl.onload = startCropper;
                setTimeout(startCropper, 80);
            }
        }

        return true;
    }

    document.querySelectorAll('.btn-ratio').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!window.globalCropperInstance) return;
            
            document.querySelectorAll('.btn-ratio').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const ratio = parseFloat(this.dataset.ratio);
            window.globalCropperInstance.setAspectRatio(isNaN(ratio) ? NaN : ratio);
        });
    });

    document.getElementById('btn-cropper-cancel').addEventListener('click', function() {
        const modal = document.getElementById('global-cropper-modal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
        }
        if (window.globalCropperInstance) {
            window.globalCropperInstance.destroy();
            window.globalCropperInstance = null;
        }
        if (window.globalCropperInput) {
            window.globalCropperInput.value = '';
        }
    });

    const cropperModalEl = document.getElementById('global-cropper-modal');
    if (cropperModalEl) {
        cropperModalEl.addEventListener('click', function(e) {
            if (e.target === this) {
                document.getElementById('btn-cropper-cancel').click();
            }
        });
    }

    document.getElementById('btn-cropper-save').addEventListener('click', function() {
        if (!window.globalCropperInstance) return;
        
        const canvas = window.globalCropperInstance.getCroppedCanvas({
            maxWidth: 1600,
            maxHeight: 1600,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });
        
        if (!canvas) {
            alert('Gagal memotong gambar. Silakan coba lagi.');
            return;
        }

        const fileInput = window.globalCropperInput;
        const originalFile = fileInput && fileInput.files ? fileInput.files[0] : null;
        const fileName = originalFile ? originalFile.name : 'cropped-image.jpg';
        const fileType = originalFile && originalFile.type ? originalFile.type : 'image/jpeg';

        canvas.toBlob(function(blob) {
            if (!blob) return;

            // Ganti isi input file dengan file hasil crop via DataTransfer
            if (fileInput) {
                const croppedFile = new File([blob], fileName, { type: fileType, lastModified: Date.now() });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(croppedFile);
                fileInput.files = dataTransfer.files;
            }
            
            // Perbarui preview foto
            if (window.globalCropperPreview) {
                const url = URL.createObjectURL(blob);
                let targetImg = null;

                if (window.globalCropperPreview.tagName === 'IMG') {
                    targetImg = window.globalCropperPreview;
                } else if (window.globalCropperPreview.querySelector) {
                    targetImg = window.globalCropperPreview.querySelector('img');
                }

                if (targetImg) {
                    targetImg.src = url;
                    targetImg.classList.remove('d-none', 'hidden');
                    targetImg.style.display = 'block';

                    // Tampilkan container pembungkus preview
                    const container = targetImg.closest('.preview-container') || targetImg.parentElement;
                    if (container) {
                        container.style.display = 'block';
                        container.classList.remove('d-none', 'hidden');
                    }

                    // Sembunyikan placeholder di kotak upload yang bersangkutan
                    const uploadBox = targetImg.closest('.upload-box') || (container ? container.parentElement : null);
                    if (uploadBox) {
                        const ph = uploadBox.querySelector('.upload-placeholder');
                        if (ph) ph.style.display = 'none';
                    }
                } else if (window.globalCropperPreview.tagName === 'DIV') {
                    window.globalCropperPreview.style.backgroundImage = 'url(' + url + ')';
                    window.globalCropperPreview.style.backgroundSize = 'cover';
                    window.globalCropperPreview.style.backgroundPosition = 'center';
                    window.globalCropperPreview.style.display = 'block';
                    window.globalCropperPreview.classList.remove('d-none', 'hidden');

                    const uploadBox = window.globalCropperPreview.closest('.upload-box') || window.globalCropperPreview.parentElement;
                    if (uploadBox) {
                        const ph = uploadBox.querySelector('.upload-placeholder');
                        if (ph) ph.style.display = 'none';
                    }
                }
                
                // Sembunyikan placeholder spesifik by ID dan reset delete flag
                if (fileInput && fileInput.id) {
                    const phById = document.getElementById('placeholder_' + fileInput.id);
                    if (phById) phById.style.display = 'none';
                    const delInput = document.getElementById('delete_' + fileInput.id);
                    if (delInput) delInput.value = '0';
                }

                const avatarPlaceholder = document.getElementById('avatar-placeholder');
                if (avatarPlaceholder) avatarPlaceholder.classList.add('hidden', 'd-none');

                const belum = document.getElementById('belum-tersimpan');
                if (belum) belum.classList.remove('hidden');

                const parent = window.globalCropperPreview.parentElement;
                if (parent) {
                    const icon = parent.querySelector('i');
                    if (icon) icon.style.display = 'none';
                }

                if (window.globalCropperPreview.classList.contains('avatar-default')) {
                    window.globalCropperPreview.outerHTML = '<img src="' + url + '" alt="user-avatar" class="avatar-preview rounded-circle" id="' + window.globalCropperPreview.id + '" />';
                }
            }
            
            // Tutup modal & destroy Cropper instance
            const modal = document.getElementById('global-cropper-modal');
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
            if (window.globalCropperInstance) {
                window.globalCropperInstance.destroy();
                window.globalCropperInstance = null;
            }
            
        }, fileType, 0.92);
    });
</script>
