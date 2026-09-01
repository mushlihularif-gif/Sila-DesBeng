import re

filepath = r'D:\laragon\www\SilaDesBeng\resources\views\admin\announcements\form.blade.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

start_marker = "    document.addEventListener('DOMContentLoaded', function() {\n        const regions = @json($regions);"
end_marker = "    });\n</script>"

script_content = """    document.addEventListener('DOMContentLoaded', function() {
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
            kecSelect.innerHTML = '<option value="">-- Pilih Semua (Se-Kabupaten) --</option>';
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
            desaSelect.innerHTML = '<option value="">-- Pilih Semua (Se-Kecamatan) --</option>';
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
            rwSelect.innerHTML = '<option value="">-- Pilih Semua (Se-Desa) --</option>';
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
        @endif"""

start_idx = content.find(start_marker)
end_idx = content.find(end_marker)

if start_idx != -1 and end_idx != -1:
    content = content[:start_idx] + script_content + content[end_idx:]
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Script updated successfully")
else:
    print("Could not find markers")
