export const states = {draft:'Draf — belum dikirim',pending:'Menunggu admin',review:'Sedang diperiksa',published:'Terbit',scheduled:'Dijadwalkan',merged:'Digabungkan',rejected:'Tidak diterbitkan',cancelled:'Dibatalkan',expired:'Sudah dibersihkan',archived:'Arsip'};
export function acceptSnapshot(current, incoming) { return Number(incoming.revision) >= Number(current.revision); }
export function acceptPoll(startedEpoch, currentEpoch, startedChanges, currentChanges) {
    return startedEpoch === currentEpoch && startedChanges === currentChanges;
}
export function canReplaceForm({dirty, editing, incomingRevision, currentRevision}) {
    return !dirty && (!editing || incomingRevision === currentRevision);
}
// Keep choices from other pages, but never carry a changed or newly locked source forward.
export function reconcileSourceChoices(choices, items) {
    const next = new Map(choices);
    for (const item of items) {
        const selected = next.get(Number(item.id));
        if (selected && (item.editor || Number(selected.revision) !== Number(item.revision))) next.delete(Number(item.id));
    }
    return next;
}

if (typeof document !== 'undefined') {
    const root = document.getElementById('content-workspace');
    if (root) startWorkspace(root);
    else if (document.querySelector('[data-content-list]')) startList();
}

function startList() {
    let inFlight = false;
    const update = async () => {
        if (document.hidden || inFlight) return;
        inFlight = true;
        try {
            const response = await fetch(location.href, {headers:{Accept:'application/json'}, cache:'no-store'});
            if (!response.ok) throw new Error();
            const data = await response.json();
            let newItems = 0;
            for (const item of data.items) {
                const row = document.querySelector(`[data-item-id="${Number(item.id)}"]`);
                if (!row) { newItems++; continue; }
                row.querySelector('[data-title]').textContent = item.title;
                row.querySelector('[data-state]').textContent = states[item.state] || item.state;
                row.querySelector('[data-editor]').textContent = item.editor ? `Sedang ditangani oleh ${item.editor}` : '';
            }
            const status = document.getElementById('list-sync');
            status.replaceChildren(document.createTextNode(newItems ? `${newItems} kiriman baru tersedia. ` : 'Status diperbarui otomatis.'));
            if (newItems) {
                const button = document.createElement('button'); button.textContent = 'Tampilkan kiriman baru'; button.className = 'text-blue-700 underline';
                button.onclick = () => { for (const item of data.items) {
                    if (document.querySelector(`[data-item-id="${Number(item.id)}"]`)) continue;
                    const link = document.createElement('a'); link.href = item.url; link.dataset.itemId = item.id;
                    link.className = 'block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm';
                    const title = document.createElement('h2'); title.dataset.title = ''; title.textContent = item.title; title.className = 'font-bold';
                    const state = document.createElement('p'); state.dataset.state = ''; state.textContent = states[item.state] || item.state;
                    const editor = document.createElement('p'); editor.dataset.editor = '';
                    link.append(title, state, editor); document.getElementById('content-rows').prepend(link);
                } status.textContent = 'Status diperbarui otomatis.'; };
                status.append(button);
            }
        } catch { document.getElementById('list-sync').textContent = 'Koneksi terganggu. Status akan diperiksa kembali secara otomatis.'; }
        finally { inFlight = false; }
    };
    update(); setInterval(update, 5000); document.addEventListener('visibilitychange', update); window.addEventListener('online', update);
}

function startWorkspace(root) {
    const $ = id => document.getElementById(id);
    let snapshot = JSON.parse($('content-initial').textContent), token = null, revision = snapshot.revision;
    let dirty = false, changes = 0, busy = false, polling = false, connected = true, saveTimer, proposal, aiVersion, pendingIntent = null, reviewedText = null;
    let lastActivity = Date.now(), lastRenewed = 0, galleryKey = '', activityKey = '', sourcesKey = '', epoch = 0;
    const manager = root.dataset.manager === '1';
    let sourceChoices = new Map(), sourcePage = 1, sourceLastPage = 1, sourceSearch = '';
    const fields = ['title','description','type','location','event_date','target_audience_id','scheduled_at','ends_at'];
    const text = (id,value) => { if ($(id)) $(id).textContent = value ?? ''; };
    const error = message => {
        for (const id of ['error','source-error','review-error']) if ($(id)) { text(id,message); $(id).hidden = !message; }
    };
    const dateInput = value => {
        if (!value) return '';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return value.slice(0,16);
        const pad = n => String(n).padStart(2,'0');
        return `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    };
    function populate(data) {
        for (const field of fields) if ($(field)) {
            const input = $(field), scrollTop = input.scrollTop, scrollLeft = input.scrollLeft;
            input.value = ['event_date','scheduled_at','ends_at'].includes(field) ? dateInput(data[field]) : (data[field] ?? '');
            input.scrollTop = scrollTop; input.scrollLeft = scrollLeft;
        }
    }
    function formData() {
        const data = {}; for (const field of fields) if ($(field)) data[field] = $(field).value || null;
        return data;
    }
    async function request(path, data, form = false) {
        const controller = new AbortController(), timer = setTimeout(() => controller.abort(), path === 'ai' ? 40000 : 20000);
        try {
            const headers = {Accept:'application/json', 'X-CSRF-TOKEN':root.dataset.csrf};
            if (token) headers['X-Editorial-Token'] = token;
            if (data && !form) headers['Content-Type'] = 'application/json';
            const result = await fetch(path === 'ai' ? root.dataset.ai : `${root.dataset.base}/${path}`, {
                method:data ? 'POST':'GET', headers, body:data ? (form ? data : JSON.stringify(data)) : undefined,
                cache:'no-store', signal:controller.signal,
            });
            let payload; try { payload = await result.json(); } catch { payload = {}; }
            if (!result.ok) {
                const failure = new Error(Object.values(payload.errors || {}).flat().join(' ') || payload.message || 'Permintaan belum berhasil. Silakan coba lagi.');
                failure.status = result.status; throw failure;
            }
            connected = true; return payload;
        } catch (failure) { if (!failure.status) connected = false; throw failure; }
        finally { clearTimeout(timer); }
    }
    function controls() {
        const editing = Boolean(token) && connected;
        const writable = snapshot.can_write && !['rejected','merged','cancelled','expired','archived'].includes(snapshot.state);
        $('recovery').hidden = Boolean(token) || !dirty;
        $('editor-fields').disabled = !editing;
        $('save').disabled = !editing || busy;
        $('claim').hidden = Boolean(token) || !writable;
        $('claim').disabled = busy || !connected || Boolean(snapshot.editor);
        $('release').hidden = !token; $('release').disabled = busy || !connected;
        $('submit').hidden = !editing || !snapshot.is_owner || snapshot.state !== 'draft' || (manager && snapshot.can_publish);
        text('submit', manager ? 'Ajukan ke RW / Desa' : 'Kirim ke Admin');
        $('publish').hidden = !editing || !snapshot.can_publish;
        for (const id of ['submit','publish','compose','correct','confirm-publish','continue-manual','use-ai','keep-original']) if ($(id)) $(id).disabled = !editing || busy;
        $('upload').disabled = !editing || busy || !$('photos').files.length;
        for (const id of ['reason-choice','reason-text']) if ($(id)) $(id).disabled = !editing || busy;
        if ($('reject')) $('reject').disabled = !editing || busy || !snapshot.can_publish || ['published','scheduled'].includes(snapshot.state);
        if ($('cancel')) { $('cancel').hidden = !editing || !['published','scheduled'].includes(snapshot.state); $('cancel').disabled = busy; }
        if ($('load-sources')) $('load-sources').disabled = !editing || busy || snapshot.category !== 'Berita';
        for (const id of ['merge','confirm-merge']) if ($(id)) $(id).disabled = !editing || busy || !sourceChoices.size;
        for (const id of ['source-search','source-search-button','change-selection']) if ($(id)) $(id).disabled = !editing || busy;
        if ($('sources-prev')) $('sources-prev').disabled = !editing || busy || sourcePage <= 1;
        if ($('sources-next')) $('sources-next').disabled = !editing || busy || sourcePage >= sourceLastPage;
        document.querySelectorAll('[data-source-choice]').forEach(input => input.disabled = !editing || busy || input.dataset.locked === '1');
        document.querySelectorAll('[data-edit-control]').forEach(button => button.disabled = !editing || busy);
        text('sync-status', !connected ? `Koneksi terputus — ${dirty ? 'ada perubahan belum tersimpan.' : 'status terbaru belum dapat diperiksa.'}` :
            token ? 'Anda sedang mengedit. Perubahan tulisan disimpan sebagai draf; belum diterbitkan. Pengelola lain dapat membaca.' :
            snapshot.editor ? `Sedang diedit oleh ${snapshot.editor}. Anda tetap dapat membaca. Tombol edit tersedia kembali setelah ia selesai.` :
            writable ? `Untuk mengisi atau mengubah tulisan, pilih “${manager ? 'Periksa dan Edit' : 'Mulai Menulis'}” di atas.` : 'Anda sedang membaca kiriman. Lihat Status Kiriman untuk mengetahui hasil penanganannya.');
        const guidance = {draft:manager ? 'Periksa isi dan foto, lalu terbitkan atau ajukan ke pengelola wilayah yang dituju.' : 'Lengkapi kegiatan, lalu pilih Kirim ke Admin. Menyimpan draf saja belum mengirim kiriman.',pending:'Kiriman menunggu admin wilayah. Hasil peninjauan akan diberitahukan kepada pengirim.',review:'Admin sedang memeriksa kiriman. Perubahan status akan muncul otomatis.',published:'Kabar sudah diterbitkan. Gunakan tautan di bawah untuk melihat hasilnya.',scheduled:'Kiriman akan terbit sesuai jadwal yang dipilih.',merged:'Foto atau cerita kiriman ini telah digabungkan ke kabar lain. Nama penyumbangnya tetap dicatat.',rejected:'Kiriman tidak diterbitkan. Alasan keputusan dapat dilihat di bawah.',cancelled:'Penerbitan kiriman ini dibatalkan.',archived:'Kiriman sudah diarsipkan.',expired:'Masa simpan kiriman sudah berakhir.'};
        text('next-step',guidance[snapshot.state] || 'Lihat status kiriman di atas.');
    }
    function render(incoming, replace = false) {
        if (!acceptSnapshot(snapshot,incoming)) return;
        snapshot = incoming;
        if (replace) populate(incoming.data);
        text('workflow-state',states[incoming.state] || incoming.state);
        text('published-at',incoming.published_at ? `Terbit: ${new Date(incoming.published_at).toLocaleString('id-ID')}` : 'Waktu terbit: belum diterbitkan (diisi sistem).');
        text('reason',incoming.rejection_reason);
        text('presence',incoming.presence?.length ? `Sedang melihat: ${incoming.presence.join(', ')}` : '');
        $('public-link').hidden = !incoming.public_url; if (incoming.public_url) $('public-link').href = incoming.public_url;
        const nextGallery = JSON.stringify([incoming.images,Boolean(token)]);
        if (nextGallery !== galleryKey) {
            galleryKey = nextGallery; $('gallery').replaceChildren();
            for (const photo of incoming.images) {
                const figure = document.createElement('figure'), img = document.createElement('img'), caption = document.createElement('figcaption');
                img.src = photo.url; img.alt = 'Dokumentasi kegiatan'; img.className = 'rounded-xl w-full aspect-square object-cover'; img.loading = 'lazy';
                caption.textContent = photo.credit || ''; caption.className = 'text-sm text-gray-500 mt-1';
                figure.append(img,caption); $('gallery').append(figure);
                if (token) {
                    const remove = document.createElement('button'); remove.type = 'button'; remove.dataset.editControl = '';
                    remove.textContent = photo.staged ? 'Hapus foto draf' : 'Hapus dari revisi'; remove.className = 'text-red-700 text-sm underline';
                    remove.onclick = () => run(async () => { await save(); const result = await mutate('remove_image',{image_id:photo.id}); revision = result.revision; render(result,false); }); figure.append(remove);
                }
            }
        }
        const nextActivity = JSON.stringify(incoming.activities);
        if ($('activity') && nextActivity !== activityKey) {
            activityKey = nextActivity; $('activity').replaceChildren();
            const verbs = {created:'membuat draf',opened:'membuka kiriman',claimed:'mulai menangani',saved:'menyimpan revisi',released:'melepas penanganan',submitted:'mengirim untuk ditinjau',published:'menerbitkan',scheduled:'menjadwalkan',rejected:'tidak menerbitkan',cancelled:'membatalkan',merged:'menggabungkan',sources_added:'mengambil bahan',images_added:'menambahkan foto'};
            for (const row of incoming.activities) { const li = document.createElement('li'); li.textContent = `${row.actor_name} ${verbs[row.action] || row.action} · ${row.created_at}`; $('activity').append(li); }
        }
        const nextSources = JSON.stringify([incoming.sources,Boolean(token)]);
        if ($('selected-sources') && nextSources !== sourcesKey) {
            sourcesKey = nextSources;
            $('selected-sources').replaceChildren();
            for (const source of incoming.sources) {
                const block = document.createElement('div'), name = document.createElement('p'); name.className = 'font-semibold'; name.textContent = source.contributor_name;
                block.append(name);
                const used = source.draft_uses_text ?? source.uses_text;
                if (source.excerpt && token) {
                    const label = document.createElement('label'), check = document.createElement('input'); check.type = 'checkbox'; check.checked = Boolean(used); check.dataset.editControl = '';
                    check.onchange = () => run(async () => { await save(); const result = await mutate('source_credit',{source_id:source.id,uses_text:check.checked}); revision = result.revision; render(result,false); });
                    label.append(check,document.createTextNode(' Cantumkan sebagai penyumbang cerita')); block.append(label);
                }
                if (used) { const paragraph = document.createElement('p'); paragraph.textContent = source.excerpt; paragraph.className = 'text-sm whitespace-pre-wrap'; block.append(paragraph);
                    const append = document.createElement('button'); append.type = 'button'; append.dataset.editControl = ''; append.textContent = 'Tambahkan ke Tulisan'; append.className = 'text-blue-700 text-sm underline block mt-2';
                    append.onclick = () => {
                        if (!token || busy) return;
                        if (!$('description').value.includes(source.excerpt)) { $('description').value = [$('description').value,source.excerpt].filter(Boolean).join('\n\n'); dirty = true; changes++; queueSave(); }
                        $('description').focus(); $('description').scrollIntoView({behavior:'smooth',block:'center'});
                    }; block.append(append);
                    const button = document.createElement('button'); button.type = 'button'; button.dataset.editControl = ''; button.textContent = 'Gunakan untuk Bantuan AI'; button.className = 'text-blue-700 text-sm underline block mt-2';
                    button.onclick = () => { if (!token || busy) return; $('write-mode').value = 'assisted'; $('write-mode').onchange(); if (!$('facts').value.includes(source.excerpt)) $('facts').value += `\nSumber ${source.contributor_name}: ${source.excerpt}`; $('facts').focus(); $('facts-panel').scrollIntoView({behavior:'smooth',block:'center'}); }; block.append(button);
                }
                $('selected-sources').append(block);
            }
        }
        controls();
    }
    async function mutate(action, extra = {}) {
        if (!token) throw new Error(`Pilih ${manager ? 'Periksa dan Edit' : 'Mulai Menulis'} terlebih dahulu.`);
        return request('action',{action,token,revision,...extra});
    }
    async function save() {
        if (!dirty) return;
        const submittedChanges = changes, data = formData();
        const result = await mutate('save', data);
        revision = result.revision; dirty = changes !== submittedChanges;
        render(result,false); text('saved', `Terakhir tersimpan: ${new Date().toLocaleTimeString('id-ID')}${dirty ? ' · masih ada perubahan baru.' : ''}`);
        if (dirty) queueSave();
    }
    async function run(work) {
        if (busy) return;
        busy = true; epoch++; error(''); controls();
        try { await work(); }
        catch (failure) {
            error(failure.message || 'Koneksi terganggu. Perubahan belum dikonfirmasi tersimpan.');
            if (!$('source-picker')?.open && !$('publish-review').open) $('error').scrollIntoView({behavior:'smooth',block:'center'});
            if ([403,409,419].includes(failure.status)) { token = null; text('saved','Tulisan pada formulir dipertahankan. Periksa versi terbaru sebelum melanjutkan.'); }
        } finally { busy = false; controls(); }
    }
    function queueSave() { clearTimeout(saveTimer); saveTimer = setTimeout(() => { if (dirty && token && connected) { if (busy) queueSave(); else run(save); } }, 1800); }
    $('editor-form').addEventListener('input', event => { lastActivity = Date.now(); if (!fields.includes(event.target.id)) return; dirty = true; changes++; text('saved','Perubahan belum tersimpan.'); queueSave(); });
    $('editor-form').onsubmit = event => { event.preventDefault(); run(save); };
    $('download-draft').onclick = () => {
        const data = formData(), body = `${data.title || ''}\n${data.location || ''}\n${data.event_date || ''}\n\n${data.description || ''}`;
        const url = URL.createObjectURL(new Blob([body],{type:'text/plain;charset=utf-8'}));
        const link = document.createElement('a'); link.href = url; link.download = 'salinan-kabar-belum-tersimpan.txt'; link.click(); setTimeout(() => URL.revokeObjectURL(url),1000);
    };
    $('load-latest').onclick = () => run(async () => { const result = await request('state'); dirty = false; changes++; revision = result.revision; render(result,true); text('saved','Tulisan terbaru sudah dimuat. Gunakan tombol edit di atas untuk melanjutkan.'); });
    $('claim').onclick = () => run(async () => {
        if (dirty) { error('Ada salinan tulisan yang belum tersimpan. Salin terlebih dahulu sebelum memuat naskah terbaru.'); return; }
        const result = await request('claim',{}); token = result.token; revision = result.revision; lastActivity = lastRenewed = Date.now();
        render(await request('state'),true);
    });
    function checkPendingPhotos() {
        if (!$('photos').files.length) return;
        $('photos').scrollIntoView({behavior:'smooth',block:'center'});
        throw new Error('Foto yang dipilih belum ditambahkan. Tekan Tambahkan Foto terlebih dahulu, atau kosongkan pilihan foto.');
    }
    $('release').onclick = () => run(async () => { checkPendingPhotos(); await save(); const result = await mutate('release'); token = null; revision = result.revision; render(result,true); });
    const currentText = () => JSON.stringify([$('title').value,$('description').value]);
    async function finishReview() {
        if (!pendingIntent) return;
        const intent = pendingIntent; pendingIntent = null; await save();
        if (intent === 'submit') {
            const result = await mutate('submit'); token = null; revision = result.revision; render(result,true);
            text('updated','Terima kasih. Kiriman sudah masuk ke admin wilayah. Anda akan mendapat notifikasi setelah ditinjau.'); $('updated').scrollIntoView({behavior:'smooth',block:'center'}); return;
        }
        text('review-title',$('title').value); text('review-body',$('description').value);
        text('review-target',snapshot.category === 'Berita' ? 'Kabar akan dapat dibaca publik.' : `Penerima: ${$('target_audience_id')?.selectedOptions[0]?.textContent || 'Belum dipilih'}`);
        const scheduled = $('scheduled_at')?.value;
        text('review-meta',`Lokasi: ${$('location').value || 'Belum diisi'} · Waktu kegiatan: ${$('event_date').value ? new Date($('event_date').value).toLocaleString('id-ID') : 'Belum diisi'}${scheduled ? ` · Jadwal terbit: ${new Date(scheduled).toLocaleString('id-ID')}` : ''}`);
        $('review-photos').replaceChildren();
        for (const photo of snapshot.images) { const figure = document.createElement('figure'), img = document.createElement('img'), caption = document.createElement('figcaption'); img.src = photo.url; img.alt = 'Foto yang akan diterbitkan'; img.className = 'w-full aspect-square object-cover rounded-xl'; caption.textContent = photo.credit || ''; caption.className = 'text-sm'; figure.append(img,caption); $('review-photos').append(figure); }
        text('confirm-publish', scheduled && new Date(scheduled) > new Date() ? 'Ya, Jadwalkan' : 'Ya, Terbitkan');
        $('publish-review').showModal();
    }
    async function reviewBefore(intent) {
        checkPendingPhotos();
        if (!$('title').value.trim() || !$('description').value.trim()) throw new Error('Isi judul dan cerita terlebih dahulu. Anda juga dapat memilih bantuan AI untuk menyusunnya.');
        pendingIntent = intent;
        if (reviewedText === currentText()) return finishReview();
        try { await fetchHelp('correct'); $('ai-preview').scrollIntoView({behavior:'smooth',block:'center'}); }
        catch (failure) {
            if (failure.status && ![429,503].includes(failure.status)) throw failure;
            $('ai-unavailable').hidden = false; error('Pemeriksaan AI belum berhasil. Tulisan belum dikirim atau diterbitkan.');
            $('ai-unavailable').scrollIntoView({behavior:'smooth',block:'center'});
        }
    }
    $('submit').onclick = () => run(() => reviewBefore('submit'));
    $('publish').onclick = () => run(() => reviewBefore('publish'));
    $('continue-manual').onclick = () => run(async () => { reviewedText = currentText(); $('ai-unavailable').hidden = true; await finishReview(); });
    $('close-review').onclick = () => $('publish-review').close();
    $('confirm-publish').onclick = () => run(async () => {
        const result = await mutate('publish'); token = null; revision = result.revision; render(result,true); $('publish-review').close();
        text('updated',result.state === 'scheduled' ? 'Berhasil dijadwalkan. Kiriman akan terbit pada waktu yang dipilih.' : 'Berhasil diterbitkan. Anda dapat melihat hasilnya melalui tautan Lihat Berita yang Terbit.');
    });
    const decision = action => run(async () => {
        const reason = [$('reason-choice')?.value,$('reason-text')?.value].filter(Boolean).join('. ');
        const result = await mutate(action,{reason}); token = null; revision = result.revision; render(result,!dirty);
    });
    if ($('reject')) $('reject').onclick = () => decision('reject');
    if ($('cancel')) $('cancel').onclick = () => decision('cancel');
    $('upload').onclick = () => run(async () => {
        await save(); const body = new FormData(); body.set('token',token); body.set('revision',revision);
        for (const file of $('photos').files) body.append('images[]',file);
        const result = await request('images',body,true); revision = result.revision; render(result,false); $('photos').value = '';
        text('photo-status','Foto berhasil ditambahkan ke draf.');
    });
    $('photos').onchange = () => { text('photo-status',$('photos').files.length ? `${$('photos').files.length} foto dipilih, belum disimpan. Tekan Tambahkan Foto.` : ''); controls(); };
    $('write-mode').onchange = () => { const assisted = $('write-mode').value === 'assisted'; $('facts-panel').hidden = !assisted; text('write-help',assisted ? 'Tulis poin pentingnya saja. AI menyusun judul dan cerita, lalu Anda memilih hasil yang ingin digunakan.' : 'Tulis dengan bahasa Anda sendiri. Sebelum dikirim, Anda bisa membandingkannya dengan hasil koreksi AI.'); };
    async function fetchHelp(mode) {
        aiVersion = changes;
        text('original-title',$('title').value); text('original-body',$('description').value);
        proposal = await request('ai',{mode,category:snapshot.category,title:$('title').value,description:$('description').value,facts:$('facts').value,location:$('location').value,event_date:$('event_date').value || null});
        text('suggested-title',proposal.title); text('suggested-body',proposal.description);
        $('ai-questions').replaceChildren(); for (const question of proposal.questions) { const li = document.createElement('li'); li.textContent = question; $('ai-questions').append(li); }
        $('ai-preview').hidden = false;
    }
    const help = mode => run(async () => { pendingIntent = null; await fetchHelp(mode); $('ai-preview').scrollIntoView({behavior:'smooth',block:'center'}); });
    $('compose').onclick = () => help('compose'); $('correct').onclick = () => help('correct');
    $('use-ai').onclick = () => {
        if (!token || !proposal) return;
        if (changes !== aiVersion) { error('Tulisan sudah berubah sejak AI mulai bekerja. Jalankan pemeriksaan lagi agar tulisan terbaru tidak tertimpa.'); return; }
        if (proposal.title) $('title').value = proposal.title;
        if (proposal.description) $('description').value = proposal.description;
        dirty = true; changes++; queueSave(); $('ai-preview').hidden = true;
        reviewedText = currentText(); if (pendingIntent) run(finishReview);
    };
    $('keep-original').onclick = () => {
        if (changes !== aiVersion) { error('Tulisan sudah berubah. Periksa kembali naskah terbaru.'); return; }
        reviewedText = currentText(); $('ai-preview').hidden = true; if (pendingIntent) run(finishReview);
    };
    function sourceSelectionChanged() {
        $('merge-review').hidden = true;
        const choices = [...sourceChoices.values()];
        text('source-selection',choices.length ? `${choices.length} kiriman dipilih · ${choices.reduce((n,item) => n+item.images.length,0)} foto · ${choices.filter(item => item.text).length} cerita` : 'Belum ada yang dipilih. Centang foto atau cerita yang ingin digunakan.');
        controls();
    }
    function chooseSource(item, field, value, checked) {
        const id = Number(item.id), choice = sourceChoices.get(id) || {id,revision:Number(item.revision),title:item.title,author:item.author,text:false,images:[]};
        if (field === 'text') choice.text = checked;
        else choice.images = checked ? [...new Set([...choice.images,Number(value)])] : choice.images.filter(photo => photo !== Number(value));
        if (choice.text || choice.images.length) sourceChoices.set(id,choice); else sourceChoices.delete(id);
        sourceSelectionChanged();
    }
    async function loadSources(page = 1) {
        const query = new URLSearchParams({page:String(page),search:sourceSearch});
        const result = await request(`sources?${query}`);
        const previousCount = sourceChoices.size;
        sourceChoices = reconcileSourceChoices(sourceChoices,result.items);
        sourcePage = result.current_page; sourceLastPage = result.last_page;
        $('source-options').replaceChildren();
        for (const item of result.items) {
            const selected = sourceChoices.get(Number(item.id));
            const block = document.createElement('fieldset'); block.className = 'border border-slate-200 rounded-xl p-4 min-w-0';
            const legend = document.createElement('legend'); legend.textContent = item.title; legend.className = 'font-bold px-1 break-words'; block.append(legend);
            const name = document.createElement('p'); name.textContent = `Dikirim oleh ${item.author}`; name.className = 'font-semibold text-sm'; block.append(name);
            const summary = document.createElement('p'); summary.textContent = [item.location,item.event_date ? new Date(`${item.event_date}T12:00:00`).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}) : ''].filter(Boolean).join(' · '); summary.className = 'text-sm text-slate-600 mb-3'; block.append(summary);
            if (item.editor) { const locked = document.createElement('p'); locked.textContent = `Sedang diedit oleh ${item.editor}. Pilih kiriman lain atau coba lagi setelah ia selesai.`; locked.className = 'text-sm text-amber-800 mb-3'; block.append(locked); }
            const infoLabel = document.createElement('label'), info = document.createElement('input'); info.type = 'checkbox'; info.dataset.sourceChoice = ''; info.dataset.locked = item.editor ? '1' : '0'; info.checked = Boolean(selected?.text);
            info.onchange = () => chooseSource(item,'text',null,info.checked); infoLabel.append(info,document.createTextNode(' Gunakan cerita ini')); block.append(infoLabel);
            const details = document.createElement('p'); details.textContent = item.description; details.className = 'text-sm max-h-32 overflow-auto my-2 whitespace-pre-wrap break-words'; block.append(details);
            const photos = document.createElement('div'); photos.className = 'flex flex-wrap gap-3';
            for (const [index,photo] of item.images.entries()) {
                const label = document.createElement('label'), check = document.createElement('input'), image = document.createElement('img'), caption = document.createElement('span');
                check.type = 'checkbox'; check.dataset.sourceChoice = ''; check.dataset.locked = item.editor ? '1' : '0'; check.checked = Boolean(selected?.images.includes(Number(photo.id)));
                check.onchange = () => chooseSource(item,'image',photo.id,check.checked);
                check.setAttribute('aria-label',`Gunakan foto ${index+1} dari ${item.author}`);
                image.src = photo.url; image.alt = `Foto ${index+1} kiriman ${item.author}`; image.className = 'w-24 h-24 rounded object-cover block my-1'; image.loading = 'lazy';
                caption.textContent = ` Foto ${index+1}`; caption.className = 'text-sm'; label.append(check,caption,image); photos.append(label);
            }
            if (!item.images.length) photos.textContent = 'Kiriman ini tidak memiliki foto.';
            block.append(photos); $('source-options').append(block);
        }
        text('source-results',result.total ? `${result.total} kiriman dapat diperiksa. Pilih yang membahas kegiatan yang sama.` : 'Tidak ada kiriman yang cocok. Coba kata lain atau kembali ke daftar kiriman.');
        text('source-page',`Halaman ${sourcePage} dari ${sourceLastPage}`);
        sourceSelectionChanged();
        if (sourceChoices.size < previousCount) error('Ada pilihan yang berubah atau sedang diedit admin lain. Pilihan tersebut dilepas; periksa kembali sisanya.');
    }
    if ($('load-sources')) {
        $('load-sources').onclick = () => run(async () => { $('source-picker').showModal(); await loadSources(sourcePage); });
        $('close-sources').onclick = () => $('source-picker').close();
        $('source-search-form').onsubmit = event => { event.preventDefault(); sourceSearch = $('source-search').value.trim(); run(() => loadSources(1)); };
        $('sources-prev').onclick = () => run(() => loadSources(sourcePage-1));
        $('sources-next').onclick = () => run(() => loadSources(sourcePage+1));
        $('merge').onclick = () => {
            if (!token || busy || !sourceChoices.size) return;
            $('merge-summary').replaceChildren();
            for (const choice of sourceChoices.values()) {
                const parts = [choice.images.length ? `${choice.images.length} foto` : '',choice.text ? 'cerita' : ''].filter(Boolean);
                const row = document.createElement('li'); row.textContent = `${choice.author} — ${choice.title}: ${parts.join(' dan ')}`; $('merge-summary').append(row);
            }
            $('merge-review').hidden = false; $('merge-review').scrollIntoView({behavior:'smooth',block:'nearest'}); $('confirm-merge').focus();
        };
        $('change-selection').onclick = () => { $('merge-review').hidden = true; $('merge').focus(); };
        $('confirm-merge').onclick = () => run(async () => {
            await save();
            const sources = [...sourceChoices.values()].map(({id,revision,text,images}) => ({id,revision,text,images}));
            const result = await mutate('merge',{sources}); revision = result.revision; render(result,false);
            sourceChoices.clear(); sourcePage = 1; sourceSelectionChanged(); $('source-picker').close();
            text('updated','Pilihan berhasil digabungkan. Foto masuk ke draf; cerita tersedia di bagian Gabungkan Kiriman Warga untuk ditambahkan atau dirapikan. Perubahan belum diterbitkan.');
            $('selected-sources').scrollIntoView({behavior:'smooth',block:'center'});
        });
    }
    async function poll() {
        if (polling || busy || document.hidden) return;
        polling = true;
        try {
            const capturedChanges = changes, capturedEpoch = epoch, result = await request('state');
            if (busy || !acceptPoll(capturedEpoch,epoch,capturedChanges,changes) || !acceptSnapshot(snapshot,result)) return;
            if (token && !result.lease_owned) { token = null; error('Hak penanganan sudah berakhir. Salinan tulisan Anda dipertahankan; pengelola lain mungkin sedang menangani.'); }
            const replace = canReplaceForm({dirty,editing:Boolean(token),incomingRevision:result.revision,currentRevision:revision});
            if (result.revision > revision) text('updated',`Naskah diperbarui oleh pengelola · ${new Date(result.updated_at).toLocaleTimeString('id-ID')}`);
            if (!dirty) revision = result.revision;
            const anchor = document.elementFromPoint(20, Math.min(250,window.innerHeight/2)), offset = anchor?.getBoundingClientRect().top;
            render(result,replace);
            if (anchor?.isConnected && offset !== undefined) window.scrollBy(0,anchor.getBoundingClientRect().top-offset);
            if (token && Date.now()-lastRenewed > 25000 && Date.now()-lastActivity < 90000) {
                await run(async () => { const renewed = await mutate('heartbeat'); revision = renewed.revision; lastRenewed = Date.now(); render(renewed,false); });
            }
        } catch (failure) { controls(); if ([401,403,404,419].includes(failure.status)) { token = null; error('Akses halaman telah berakhir. Masuk kembali atau buka daftar kiriman.'); } }
        finally { polling = false; }
    }
    root.addEventListener('pointerdown',() => { if (token) lastActivity = Date.now(); });
    root.addEventListener('keydown',() => { if (token) lastActivity = Date.now(); });
    window.addEventListener('beforeunload',event => { if (dirty || $('photos').files.length) { event.preventDefault(); event.returnValue = ''; } });
    window.addEventListener('offline',() => { connected = false; controls(); });
    window.addEventListener('online',poll); document.addEventListener('visibilitychange',poll);
    populate(snapshot.data); render(snapshot); poll(); setInterval(poll,4000);
}
