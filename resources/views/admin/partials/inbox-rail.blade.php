{{--
    Panel kotak masuk Gmail di sisi kanan, tampil di semua halaman admin (baca-saja).

    Hanya dirender untuk super_admin, sejalan dengan pembatasan route
    admin.inbox.* — kotak surat yang dibaca adalah milik instansi, bukan pribadi.

    Isi email adalah data dari luar yang tidak dipercaya, jadi seluruh
    penulisan ke DOM di bawah memakai textContent, tidak pernah innerHTML.
--}}
@php $inboxAlamat = config('services.gmail_inbox.email'); @endphp

<button type="button" id="inbox-toggle" class="inbox-toggle" title="Kotak Masuk" aria-controls="inbox-rail" aria-expanded="false">
    <i class="bx bx-envelope"></i>
    <span class="inbox-toggle-badge d-none" id="inbox-badge">0</span>
</button>

<aside id="inbox-rail" class="inbox-rail" aria-label="Kotak masuk email">
    <div class="inbox-head">
        <div class="flex-grow-1 min-w-0">
            <h6 class="mb-0 fw-bold text-white d-flex align-items-center gap-2">
                <i class="bx bx-envelope"></i> Kotak Masuk
            </h6>
            <small class="inbox-akun text-truncate d-block">{{ $inboxAlamat ?: 'belum ditautkan' }}</small>
        </div>
        <button type="button" class="inbox-ikon" id="inbox-refresh" title="Muat ulang"><i class="bx bx-refresh"></i></button>
        <button type="button" class="inbox-ikon" id="inbox-close" title="Tutup"><i class="bx bx-x"></i></button>
    </div>

    {{-- Daftar email --}}
    <div class="inbox-body" id="inbox-list">
        <div class="inbox-info" id="inbox-status">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <span>Memuat…</span>
        </div>
    </div>

    {{-- Tampilan isi satu email, menutupi daftar saat dibuka --}}
    <div class="inbox-detail" id="inbox-detail" hidden>
        <div class="inbox-detail-head">
            <button type="button" class="inbox-ikon" id="inbox-back" title="Kembali"><i class="bx bx-arrow-back"></i></button>
            <span class="fw-semibold small text-truncate" id="inbox-detail-subjek"></span>
        </div>
        <div class="inbox-detail-meta">
            <div class="fw-semibold small" id="inbox-detail-nama"></div>
            <div class="text-muted" style="font-size:.72rem" id="inbox-detail-email"></div>
            <div class="text-muted" style="font-size:.72rem" id="inbox-detail-tanggal"></div>
            <div class="mt-2 d-none" id="inbox-detail-lampiran"></div>
        </div>
        <pre class="inbox-detail-isi" id="inbox-detail-isi"></pre>
    </div>

    <div class="inbox-foot">
        <span id="inbox-diperbarui">&nbsp;</span>
        <a href="https://mail.google.com/" target="_blank" rel="noopener noreferrer">Buka Gmail <i class="bx bx-link-external"></i></a>
    </div>
</aside>

<style>
    .inbox-rail {
        position: fixed; top: 0; right: 0; z-index: 1046;
        width: 320px; max-width: 100vw; height: 100vh;
        background: #fff; border-left: 1px solid #e4e6e8;
        box-shadow: -6px 0 24px rgba(20, 20, 43, .10);
        display: flex; flex-direction: column;
        transform: translateX(100%); transition: transform .25s ease;
    }
    .inbox-rail.show { transform: none; }

    .inbox-head {
        display: flex; align-items: center; gap: .5rem;
        padding: .85rem 1rem; background: #5a67d8; color: #fff; flex-shrink: 0;
    }
    .inbox-akun { color: rgba(255,255,255,.75); font-size: .72rem; }
    .inbox-ikon {
        border: 0; background: rgba(255,255,255,.15); color: #fff;
        width: 28px; height: 28px; border-radius: 6px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .inbox-ikon:hover { background: rgba(255,255,255,.3); }

    .inbox-body { flex: 1 1 auto; overflow-y: auto; }
    .inbox-info { padding: 1.25rem 1rem; text-align: center; color: #697a8d; font-size: .82rem; }
    .inbox-info .bx { font-size: 1.75rem; display: block; margin-bottom: .35rem; }

    .inbox-item {
        display: block; width: 100%; text-align: left; border: 0; background: none;
        padding: .7rem 1rem; border-bottom: 1px solid #f1f2f4; cursor: pointer;
    }
    .inbox-item:hover { background: #f6f7fb; }
    .inbox-item .baris { display: flex; align-items: baseline; gap: .5rem; }
    .inbox-item .nama { font-size: .8rem; font-weight: 600; color: #384551; flex: 1 1 auto; min-width: 0;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .inbox-item .waktu { font-size: .68rem; color: #a1acb8; flex-shrink: 0; }
    .inbox-item .subjek { font-size: .78rem; color: #566a7f; margin-top: .15rem;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .inbox-item.belum { background: #f2f4ff; }
    .inbox-item.belum .nama, .inbox-item.belum .subjek { color: #2f3a8f; font-weight: 700; }

    .inbox-detail { position: absolute; inset: 0; background: #fff; display: flex; flex-direction: column; z-index: 2; }
    .inbox-detail-head { display: flex; align-items: center; gap: .5rem; padding: .7rem 1rem;
        background: #5a67d8; color: #fff; flex-shrink: 0; }
    .inbox-detail-meta { padding: .75rem 1rem; border-bottom: 1px solid #eef0f2; flex-shrink: 0; }
    .inbox-detail-isi {
        flex: 1 1 auto; overflow: auto; margin: 0; padding: 1rem;
        font-family: inherit; font-size: .8rem; line-height: 1.6; color: #566a7f;
        white-space: pre-wrap; word-break: break-word;
    }

    .inbox-foot {
        flex-shrink: 0; padding: .5rem 1rem; border-top: 1px solid #eef0f2;
        display: flex; justify-content: space-between; align-items: center;
        font-size: .7rem; color: #a1acb8; background: #fbfbfc;
    }

    .inbox-toggle {
        position: fixed; right: 18px; bottom: 18px; z-index: 1045;
        width: 46px; height: 46px; border-radius: 50%; border: 0;
        background: #5a67d8; color: #fff; box-shadow: 0 4px 14px rgba(90,103,216,.45);
    }
    .inbox-toggle .bx { font-size: 1.35rem; }
    .inbox-toggle-badge {
        position: absolute; top: -3px; right: -3px; min-width: 18px; height: 18px;
        padding: 0 4px; border-radius: 9px; background: #ff3e1d; color: #fff;
        font-size: .65rem; line-height: 18px; font-weight: 700;
    }

    /* Panel bersifat OVERLAY: menimpa halaman, tidak menggeser kontennya.
       Sebelumnya konten digeser dengan padding-right, tapi itu menambah lebar
       di atas elemen yang sudah 100% sehingga tata letak dashboard gepeng dan
       muncul scrollbar horizontal. Yang digeser sekarang hanya tombolnya,
       karena tombol itu position:fixed dan tidak memengaruhi aliran konten. */
    @media (min-width: 1200px) {
        body.inbox-terbuka .inbox-toggle { right: 338px; }
    }
</style>

<script>
(function () {
    const rail     = document.getElementById('inbox-rail');
    const toggle   = document.getElementById('inbox-toggle');
    const list     = document.getElementById('inbox-list');
    const detail   = document.getElementById('inbox-detail');
    const badge    = document.getElementById('inbox-badge');
    const KUNCI    = 'inbox-rail-terbuka';

    const URL_DAFTAR = @json(route('admin.inbox.index'));
    const URL_ISI    = @json(url('admin/kotak-masuk'));

    // ---------- buka / tutup ----------
    function setTerbuka(terbuka) {
        rail.classList.toggle('show', terbuka);
        document.body.classList.toggle('inbox-terbuka', terbuka);
        toggle.setAttribute('aria-expanded', terbuka ? 'true' : 'false');
        localStorage.setItem(KUNCI, terbuka ? '1' : '0');
        if (terbuka && !sudahDimuat) muatDaftar();
    }

    let sudahDimuat = false;

    toggle.addEventListener('click', () => setTerbuka(!rail.classList.contains('show')));
    document.getElementById('inbox-close').addEventListener('click', () => setTerbuka(false));
    document.getElementById('inbox-refresh').addEventListener('click', () => muatDaftar(true));
    document.getElementById('inbox-back').addEventListener('click', () => { detail.hidden = true; });

    // ---------- util ----------
    function kosongkan(el) { while (el.firstChild) el.removeChild(el.firstChild); }

    function pesanInfo(ikon, teks) {
        kosongkan(list);
        const bungkus = document.createElement('div');
        bungkus.className = 'inbox-info';
        const i = document.createElement('i');
        i.className = 'bx ' + ikon;
        const span = document.createElement('span');
        span.textContent = teks;                 // textContent, bukan innerHTML
        bungkus.append(i, span);
        list.appendChild(bungkus);
    }

    function waktuSingkat(iso) {
        if (!iso) return '';
        const d = new Date(iso);
        if (isNaN(d)) return '';
        const sekarang = new Date();
        const samaHari = d.toDateString() === sekarang.toDateString();
        return samaHari
            ? d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
            : d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    }

    // ---------- daftar ----------
    async function muatDaftar(segarkan = false) {
        sudahDimuat = true;
        pesanInfo('bx-loader-alt bx-spin', 'Memuat…');

        try {
            const r = await fetch(URL_DAFTAR + (segarkan ? '?segarkan=1' : ''), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            });
            const data = await r.json();

            if (data.status === 'belum_diatur') {
                return pesanInfo('bx-plug', 'Kotak masuk belum ditautkan. Atur di Sistem Platform → Integrasi & API Key.');
            }
            if (data.status !== 'ok') {
                return pesanInfo('bx-error-circle', data.pesan || 'Gagal memuat kotak masuk.');
            }
            if (!data.messages.length) {
                return pesanInfo('bx-inbox', 'Tidak ada email.');
            }

            gambarDaftar(data.messages);

            const belum = data.messages.filter(m => !m.sudah_baca).length;
            badge.textContent = belum > 99 ? '99+' : belum;
            badge.classList.toggle('d-none', belum === 0);

            document.getElementById('inbox-diperbarui').textContent =
                'Diperbarui ' + new Date(data.diambil_pada).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        } catch (e) {
            pesanInfo('bx-error-circle', 'Tidak bisa menghubungi server.');
        }
    }

    function gambarDaftar(pesanPesan) {
        kosongkan(list);

        pesanPesan.forEach(m => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'inbox-item' + (m.sudah_baca ? '' : ' belum');

            const baris = document.createElement('div');
            baris.className = 'baris';

            const nama = document.createElement('span');
            nama.className = 'nama';
            nama.textContent = m.nama;

            const waktu = document.createElement('span');
            waktu.className = 'waktu';
            waktu.textContent = waktuSingkat(m.tanggal);

            baris.append(nama, waktu);

            const subjek = document.createElement('div');
            subjek.className = 'subjek';
            subjek.textContent = m.subjek;

            item.append(baris, subjek);
            item.addEventListener('click', () => bukaEmail(m.uid));
            list.appendChild(item);
        });
    }

    // ---------- isi satu email ----------
    async function bukaEmail(uid) {
        detail.hidden = false;
        document.getElementById('inbox-detail-subjek').textContent = 'Memuat…';
        document.getElementById('inbox-detail-isi').textContent = '';
        document.getElementById('inbox-detail-nama').textContent = '';
        document.getElementById('inbox-detail-email').textContent = '';
        document.getElementById('inbox-detail-tanggal').textContent = '';
        document.getElementById('inbox-detail-lampiran').classList.add('d-none');

        try {
            const r = await fetch(URL_ISI + '/' + uid, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            });
            const data = await r.json();

            if (data.status !== 'ok') {
                document.getElementById('inbox-detail-subjek').textContent = 'Gagal dibuka';
                document.getElementById('inbox-detail-isi').textContent = data.pesan || 'Email tidak bisa dibuka.';
                return;
            }

            const m = data.message;
            document.getElementById('inbox-detail-subjek').textContent  = m.subjek;
            document.getElementById('inbox-detail-nama').textContent    = m.nama;
            document.getElementById('inbox-detail-email').textContent   = m.email;
            document.getElementById('inbox-detail-tanggal').textContent =
                m.tanggal ? new Date(m.tanggal).toLocaleString('id-ID') : '';
            document.getElementById('inbox-detail-isi').textContent     = m.body || '(email tanpa isi teks)';

            if (m.lampiran && m.lampiran.length) {
                const kotak = document.getElementById('inbox-detail-lampiran');
                kosongkan(kotak);
                m.lampiran.forEach(l => {
                    const tag = document.createElement('span');
                    tag.className = 'badge bg-label-secondary me-1';
                    tag.textContent = l.nama;      // nama file pun tidak dipercaya
                    kotak.appendChild(tag);
                });
                kotak.classList.remove('d-none');
            }
        } catch (e) {
            document.getElementById('inbox-detail-subjek').textContent = 'Gagal dibuka';
            document.getElementById('inbox-detail-isi').textContent = 'Tidak bisa menghubungi server.';
        }
    }

    // ---------- keadaan awal ----------
    const tersimpan = localStorage.getItem(KUNCI);
    const bawaanTerbuka = tersimpan === null ? window.innerWidth >= 1200 : tersimpan === '1';
    setTerbuka(bawaanTerbuka);
})();
</script>
