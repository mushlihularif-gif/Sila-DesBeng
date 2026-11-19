(() => {
    function start() {
        document.querySelectorAll('textarea[data-terms-editor]').forEach(source => {
            if (source.dataset.enhanced) return; source.dataset.enhanced = '1';
            const panel = document.createElement('div'); source.after(panel); source.hidden = true;
            let rows = [];
            function load() {
                rows = []; let list = false;
                for (const raw of source.value.split(/\r?\n/)) {
                    const line = raw.trim(); if (!line) { list = false; continue; }
                    const match = line.match(/^\d+[.)]\s+(.+)$/);
                    if (match) { rows.push({type:'item',text:match[1]}); list = true; }
                    else if (list && rows.length) rows.at(-1).text += ' ' + line;
                    else rows.push({type:'note',text:line});
                }
                if (!rows.length) rows.push({type:'item',text:''});
                render();
            }
            function updateLabels() {
                let number = 0;
                panel.querySelectorAll('[data-terms-label]').forEach((label,index) => {
                    label.textContent = rows[index].type === 'item' ? 'Aturan ' + (++number) : 'Keterangan';
                    if (rows[index].type === 'note') number = 0;
                });
            }
            function sync() {
                let number = 0, previous = null;
                source.value = rows.map(row => {
                    const prefix = previous && (previous !== row.type || row.type === 'note') ? '\n' : '';
                    if (row.type === 'note') number = 0;
                    previous = row.type;
                    return prefix + (row.type === 'item' ? (++number) + '. ' : '') + row.text.trim();
                }).join('\n');
                updateLabels();
            }
            function render() {
                panel.replaceChildren();
                const help = document.createElement('p'); help.className = 'small text-muted';
                help.textContent = 'Pisahkan setiap aturan menjadi satu butir. Gunakan Keterangan untuk penjelasan tanpa nomor.'; panel.append(help);
                rows.forEach((row,index) => {
                    const box = document.createElement('div'); box.className = 'border rounded p-3 mb-2';
                    const select = document.createElement('select'); select.className = 'form-select mb-2'; select.setAttribute('aria-label','Jenis bagian '+(index+1));
                    for (const [value,label] of [['item','Butir bernomor'],['note','Keterangan tanpa nomor']]) { const option = document.createElement('option'); option.value=value; option.textContent=label; select.append(option); }
                    select.value=row.type; select.onchange=()=>{row.type=select.value;sync();};
                    const input=document.createElement('textarea'); input.className='form-control'; input.rows=3; input.value=row.text; input.setAttribute('aria-label','Isi bagian '+(index+1)); input.oninput=()=>{row.text=input.value;sync();};
                    const remove=document.createElement('button'); remove.type='button'; remove.className='btn btn-sm btn-outline-danger mt-2'; remove.textContent='Hapus bagian'; remove.onclick=()=>{rows.splice(index,1);render();sync();};
                    const label=document.createElement('strong'); label.dataset.termsLabel=''; label.className='d-block mb-2';
                    box.append(label,select,input,remove); panel.append(box);
                });
                const add=document.createElement('button'); add.type='button'; add.className='segi-outline'; add.textContent='Tambah Butir / Keterangan'; add.onclick=()=>{rows.push({type:'item',text:''});render();}; panel.append(add); updateLabels();
            }
            source.addEventListener('change',load);
            load();
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded',start); else start();
})();
