export function validatePhoto(file) {
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) return 'Gunakan foto JPG, PNG, atau WEBP.';
    if (file.size > 5 * 1024 * 1024) return 'Ukuran setiap foto maksimal 5 MB.';
    return '';
}

export function mountPhotoPicker(input, container, status, changed) {
    const slots = [null, null, null];
    let urls = [];
    function render() {
        urls.forEach(url => URL.revokeObjectURL(url)); urls = [];
        container.replaceChildren();
        slots.forEach((file, index) => {
            const card = document.createElement('div');
            card.className = 'rounded-xl border border-dashed border-blue-200 bg-white p-3 min-w-0';
            const title = document.createElement('p'); title.textContent = `Foto ${index + 1}`; title.className = 'font-semibold mb-2'; card.append(title);
            if (file) {
                const img = document.createElement('img'); img.src = URL.createObjectURL(file); urls.push(img.src);
                img.alt = `Pratinjau foto ${index + 1}`; img.className = 'w-full aspect-square object-cover rounded-lg mb-2'; card.append(img);
            }
            const picker = document.createElement('input'); picker.type = 'file'; picker.accept = 'image/jpeg,image/png,image/webp'; picker.hidden = true;
            picker.onchange = () => {
                const selected = picker.files[0]; if (!selected) return;
                const error = validatePhoto(selected); if (error) { status.textContent = error; return; }
                slots[index] = selected; sync();
            };
            const choose = document.createElement('button'); choose.type = 'button'; choose.className = 'text-[#115789] underline text-sm';
            choose.textContent = file ? 'Ganti foto' : 'Tambah foto'; choose.onclick = () => picker.click(); card.append(picker,choose);
            if (file) {
                const remove = document.createElement('button'); remove.type = 'button'; remove.textContent = 'Batalkan'; remove.className = 'block text-red-700 underline text-sm mt-2';
                remove.onclick = () => { slots[index] = null; sync(); }; card.append(remove);
            }
            container.append(card);
        });
    }
    function sync() {
        const transfer = new DataTransfer(); slots.filter(Boolean).forEach(file => transfer.items.add(file)); input.files = transfer.files;
        status.textContent = input.files.length ? `${input.files.length} foto dipilih. Tekan Simpan Foto ke Draf sebelum mengirim berita.` : '';
        render(); changed();
    }
    render();
    return {clear() { slots.fill(null); sync(); }, destroy() { urls.forEach(url => URL.revokeObjectURL(url)); }};
}
