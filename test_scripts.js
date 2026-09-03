
    function handleImagePreview(input, previewId, wrapperId, ratio, imgClasses, width, height) {
        if (!input.files || !input.files[0]) return;
        
        let wrapper = document.getElementById(wrapperId);
        let preview = document.getElementById(previewId);
        
        // Ensure there is an img tag to preview and pass to cropper
        if (!preview) {
            // Remove placeholder if it exists
            let placeholder = document.getElementById(previewId + '_placeholder');
            if (placeholder) {
                placeholder.remove();
            }
            // Create new img element
            preview = document.createElement('img');
            preview.id = previewId;
            preview.className = 'd-block ' + imgClasses;
            preview.style.width = width;
            preview.style.height = height;
            preview.style.objectFit = 'cover';
            wrapper.appendChild(preview);
        }
        
        // Pass to Global Cropper
        if (typeof initGlobalCropper === 'function') {
            initGlobalCropper(input, previewId, ratio, true);
        } else {
            // Fallback preview
            let reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }


/* --- NEXT SCRIPT --- */


    window.setOngkirType = function(type) {
        try {
            document.getElementById('tipe_' + type).checked = true;
            
            document.getElementById('div_pukul_rata').style.display = type === 'pukul_rata' ? 'block' : 'none';
            document.getElementById('div_per_kecamatan').style.display = type === 'per_kecamatan' ? 'block' : 'none';
            
            const lblPukulRata = document.getElementById('label_pukul_rata');
            const lblPerKecamatan = document.getElementById('label_per_kecamatan');
            
            if (lblPukulRata && lblPerKecamatan) {
                if (type === 'pukul_rata') {
                    lblPukulRata.classList.add('border-primary', 'bg-label-primary');
                    lblPerKecamatan.classList.remove('border-primary', 'bg-label-primary');
                } else {
                    lblPerKecamatan.classList.add('border-primary', 'bg-label-primary');
                    lblPukulRata.classList.remove('border-primary', 'bg-label-primary');
                }
            }
        } catch (e) {
            console.error("Error toggling ongkir:", e);
        }
    };

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        const initType = document.getElementById('tipe_per_kecamatan').checked ? 'per_kecamatan' : 'pukul_rata';
        window.setOngkirType(initType);

        // Cek apakah ada hash di URL (misal: #navs-top-pengaturan)
        var hash = window.location.hash;
        if (hash) {
            // Cari tombol tab yang menargetkan hash tersebut
            var tabTarget = document.querySelector('button[data-bs-target="' + hash + '"]');
            if (tabTarget) {
                // Aktifkan tab menggunakan Bootstrap Tab API
                var tab = new bootstrap.Tab(tabTarget);
                tab.show();
            }
        }
    });

    function toggleKecamatan(id) {
        const isChecked = document.getElementById('switch_kec_' + id).checked;
        const input = document.getElementById('input_kec_' + id);
        const label = document.getElementById('label_kec_' + id);
        
        if (isChecked) {
            input.disabled = false;
            label.classList.remove('text-muted');
            label.classList.add('text-dark');
        } else {
            input.disabled = true;
            input.value = '';
            label.classList.add('text-muted');
            label.classList.remove('text-dark');
        }
    }


/* --- NEXT SCRIPT --- */


    function handleImagePreview(input, previewId, wrapperId, ratio, imgClasses, width, height) {
        if (!input.files || !input.files[0]) return;
        
        let wrapper = document.getElementById(wrapperId);
        let preview = document.getElementById(previewId);
        
        // Ensure there is an img tag to preview and pass to cropper
        if (!preview) {
            // Remove placeholder if it exists
            let placeholder = document.getElementById(previewId + '_placeholder');
            if (placeholder) {
                placeholder.remove();
            }
            // Create new img element
            preview = document.createElement('img');
            preview.id = previewId;
            preview.className = 'd-block ' + imgClasses;
            preview.style.width = width;
            preview.style.height = height;
            preview.style.objectFit = 'cover';
            wrapper.appendChild(preview);
        }
        
        // Pass to Global Cropper
        if (typeof initGlobalCropper === 'function') {
            initGlobalCropper(input, previewId, ratio, true);
        } else {
            // Fallback preview
            let reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }


/* --- NEXT SCRIPT --- */


    function handleImagePreview(input, previewId, wrapperId, ratio, imgClasses, width, height) {
        if (!input.files || !input.files[0]) return;
        
        let wrapper = document.getElementById(wrapperId);
        let preview = document.getElementById(previewId);
        
        // Ensure there is an img tag to preview and pass to cropper
        if (!preview) {
            // Remove placeholder if it exists
            let placeholder = document.getElementById(previewId + '_placeholder');
            if (placeholder) {
                placeholder.remove();
            }
            // Create new img element
            preview = document.createElement('img');
            preview.id = previewId;
            preview.className = 'd-block ' + imgClasses;
            preview.style.width = width;
            preview.style.height = height;
            preview.style.objectFit = 'cover';
            wrapper.appendChild(preview);
        }
        
        // Pass to Global Cropper
        if (typeof initGlobalCropper === 'function') {
            initGlobalCropper(input, previewId, ratio, true);
        } else {
            // Fallback preview
            let reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
