{{--
    Dialog konfirmasi SiLaDesBeng — pengganti window.confirm().

    Desainnya MENGIKUTI modal yang sudah ada di halaman Kemitraan
    (pages/kemitraan/create.blade.php), satu-satunya dialog terpusat bergaya
    situs di aplikasi ini:

        kartu   rounded-3xl (24px) + shadow-2xl, lebar maks 448px, terpusat
        ikon    lingkaran 80px berlatar tint, ikon 40px di dalamnya
        judul   text-2xl (1.5rem) font-bold #111827
        pesan   text-sm #6b7280 leading-relaxed
        footer  bar terpisah bg-gray-50 dengan garis atas #f3f4f6,
                tombol rounded-xl px-10 py-3 font-semibold

    Nilai Tailwind-nya ditulis ulang sebagai CSS biasa, bukan memakai kelas
    Tailwind, karena komponen ini juga dipasang di sisi admin yang memakai
    Sneat/Bootstrap dan tidak memuat Tailwind.

    Simpulnya ditempelkan ke document.body lewat JavaScript, bukan ditaruh di
    tengah halaman. Isi konten admin berada di dalam .layout-page yang diberi
    animasi transform, dan itu menjadikannya containing block bagi keturunan
    position:fixed — dialog yang ditaruh di sana akan tertimpa lapisan gelapnya
    sendiri dan halaman tampak beku.

    Dua cara pakai:

    1. Deklaratif (dipakai hampir semua tempat) — cukup atribut, tanpa JS:

         <form ... data-konfirmasi="Yakin hapus staf ini?">
         <a href="..." data-konfirmasi="Yakin keluar?">

       Atribut pelengkap yang bersifat opsional:
         data-konfirmasi-judul="Hapus Staf"
         data-konfirmasi-jenis="bahaya|peringatan|info"
         data-konfirmasi-ya="Ya, Hapus"

    2. Terprogram, untuk alur yang bercabang di dalam JavaScript:

         if (await konfirmasi({ pesan: '...', jenis: 'bahaya' })) { ... }
--}}
<script>
(function () {
    if (window.konfirmasi) return;   // layout bersarang: cukup sekali pasang

    // Latar ikon memakai tint -50 seperti bg-green-50 pada modal Kemitraan.
    // #115789 adalah biru utama situs, warna yang sama dipakai cincin fokus
    // formulir di halaman itu.
    var GAYA = {
        bahaya: {
            latarIkon: '#fef2f2', warnaIkon: '#ef4444',
            tombol: '#dc2626', tombolHover: '#b91c1c',
            judul: 'Konfirmasi', ya: 'Ya, Lanjutkan',
            ikon: 'M12 9v2m0 4h.01M10.29 3.86l-8.4 14.56a1.35 1.35 0 001.16 2.02h16.88a1.35 1.35 0 001.16-2.02L12.7 3.86a1.35 1.35 0 00-2.42 0z'
        },
        peringatan: {
            latarIkon: '#fffbeb', warnaIkon: '#f59e0b',
            tombol: '#d97706', tombolHover: '#b45309',
            judul: 'Perhatian', ya: 'Ya, Lanjutkan',
            ikon: 'M12 9v2m0 4h.01M10.29 3.86l-8.4 14.56a1.35 1.35 0 001.16 2.02h16.88a1.35 1.35 0 001.16-2.02L12.7 3.86a1.35 1.35 0 00-2.42 0z'
        },
        info: {
            latarIkon: '#eff6ff', warnaIkon: '#115789',
            tombol: '#115789', tombolHover: '#0d456d',
            judul: 'Konfirmasi', ya: 'Ya, Lanjutkan',
            ikon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
        }
    };

    var css = document.createElement('style');
    css.textContent = [
        // bg-gray-900 bg-opacity-50, sama dengan modal Kemitraan.
        '.sdb-konf-tirai{position:fixed;inset:0;z-index:2147483647;display:flex;align-items:center;justify-content:center;',
        'padding:16px;background:rgba(17,24,39,.5);opacity:0;transition:opacity .25s ease}',
        '.sdb-konf-tirai.tampil{opacity:1}',

        // bg-white rounded-3xl shadow-2xl sm:max-w-md w-full + animate-fade-in-up
        '.sdb-konf-kartu{background:#fff;border-radius:24px;overflow:hidden;',
        'box-shadow:0 25px 50px -12px rgba(0,0,0,.25);max-width:448px;width:100%;box-sizing:border-box;',
        // font-family: inherit, BUKAN tumpukan sendiri. Dialog ini dipasang di
        // kedua sisi aplikasi yang fontnya berbeda — Inter di sisi warga,
        // Public Sans di sisi admin. Memaku satu tumpukan di sini membuatnya
        // selalu salah di salah satu sisi.
        'font-family:inherit;',
        'opacity:0;transform:translateY(24px);transition:opacity .3s ease,transform .3s ease}',
        '.sdb-konf-tirai.tampil .sdb-konf-kartu{opacity:1;transform:none}',

        // px-6 pt-10 pb-8 text-center
        '.sdb-konf-badan{padding:40px 24px 32px;text-align:center}',

        // h-20 w-20 rounded-full mb-6
        '.sdb-konf-ikon{width:80px;height:80px;border-radius:9999px;display:flex;align-items:center;',
        'justify-content:center;margin:0 auto 24px}',

        // text-2xl font-bold text-gray-900 mb-4
        '.sdb-konf-judul{font-size:1.5rem;line-height:1.6rem;font-weight:700;color:#111827;margin:0 0 16px}',

        // text-sm text-gray-500 leading-relaxed
        '.sdb-konf-pesan{font-size:.875rem;color:#6b7280;line-height:1.625;margin:0;white-space:pre-line}',

        // px-6 py-5 bg-gray-50 flex justify-center border-t border-gray-100
        '.sdb-konf-kaki{padding:20px 24px;background:#f9fafb;border-top:1px solid #f3f4f6;',
        'display:flex;justify-content:center;gap:12px;flex-wrap:wrap}',

        // rounded-xl px-10 py-3 text-base font-semibold + shadow-sm
        '.sdb-konf-kaki button{border-radius:12px;padding:12px 40px;font-size:1rem;font-weight:600;',
        'cursor:pointer;font-family:inherit;box-shadow:0 1px 2px rgba(0,0,0,.05);transition:all .15s}',
        '.sdb-konf-batal{background:#fff;border:1px solid #d1d5db;color:#374151}',
        '.sdb-konf-batal:hover{background:#f9fafb}',
        '.sdb-konf-ya{border:1px solid transparent;color:#fff}',
        '.sdb-konf-kaki button:focus-visible{outline:2px solid #115789;outline-offset:2px}',

        // Layar sempit: tombol menumpuk penuh, seperti tata letak sm: di Tailwind.
        '@media (max-width:400px){.sdb-konf-kaki{flex-direction:column-reverse}',
        '.sdb-konf-kaki button{width:100%;padding-left:0;padding-right:0}}'
    ].join('');
    document.head.appendChild(css);

    /**
     * @returns {Promise<boolean>} true kalau pengguna menekan tombol setuju
     */
    window.konfirmasi = function (opsi) {
        opsi = opsi || {};
        var gaya = GAYA[opsi.jenis] || GAYA.info;

        return new Promise(function (selesai) {
            var tirai = document.createElement('div');
            tirai.className = 'sdb-konf-tirai';
            tirai.setAttribute('role', 'dialog');
            tirai.setAttribute('aria-modal', 'true');

            var kartu = document.createElement('div');
            kartu.className = 'sdb-konf-kartu';

            var badan = document.createElement('div');
            badan.className = 'sdb-konf-badan';

            var bulat = document.createElement('div');
            bulat.className = 'sdb-konf-ikon';
            bulat.style.background = gaya.latarIkon;
            bulat.style.color = gaya.warnaIkon;
            bulat.innerHTML = '<svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">'
                + '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' + gaya.ikon + '"></path></svg>';

            var judul = document.createElement('h3');
            judul.className = 'sdb-konf-judul';
            judul.textContent = opsi.judul || gaya.judul;

            var pesan = document.createElement('p');
            pesan.className = 'sdb-konf-pesan';
            // textContent, bukan innerHTML: pesannya sering memuat nama warga
            // atau nama wilayah yang datang dari basis data.
            pesan.textContent = opsi.pesan || 'Apakah Anda yakin ingin melanjutkan?';

            var kaki = document.createElement('div');
            kaki.className = 'sdb-konf-kaki';

            var batal = document.createElement('button');
            batal.type = 'button';
            batal.className = 'sdb-konf-batal';
            batal.textContent = opsi.tombolBatal || 'Batal';

            var ya = document.createElement('button');
            ya.type = 'button';
            ya.className = 'sdb-konf-ya';
            ya.style.background = gaya.tombol;
            ya.textContent = opsi.tombolYa || gaya.ya;
            ya.addEventListener('mouseenter', function () { ya.style.background = gaya.tombolHover; });
            ya.addEventListener('mouseleave', function () { ya.style.background = gaya.tombol; });

            badan.appendChild(bulat);
            badan.appendChild(judul);
            badan.appendChild(pesan);
            kaki.appendChild(batal);
            kaki.appendChild(ya);
            kartu.appendChild(badan);
            kartu.appendChild(kaki);
            tirai.appendChild(kartu);
            document.body.appendChild(tirai);

            var fokusSebelumnya = document.activeElement;
            requestAnimationFrame(function () {
                tirai.classList.add('tampil');
                ya.focus();
            });

            function tutup(jawaban) {
                document.removeEventListener('keydown', padaTombol, true);
                tirai.classList.remove('tampil');
                setTimeout(function () {
                    tirai.remove();
                    if (fokusSebelumnya && fokusSebelumnya.focus) fokusSebelumnya.focus();
                }, 250);
                selesai(jawaban);
            }

            function padaTombol(e) {
                if (e.key === 'Escape') { e.preventDefault(); tutup(false); }
                // Fokus dikurung di dalam dialog supaya Tab tidak lolos ke
                // halaman di belakangnya yang sedang tidak bisa dipakai.
                if (e.key === 'Tab') {
                    e.preventDefault();
                    (document.activeElement === ya ? batal : ya).focus();
                }
            }

            batal.addEventListener('click', function () { tutup(false); });
            ya.addEventListener('click', function () { tutup(true); });
            tirai.addEventListener('click', function (e) { if (e.target === tirai) tutup(false); });
            document.addEventListener('keydown', padaTombol, true);
        });
    };

    // ---- Jalur deklaratif: satu penyadap untuk seluruh halaman ----
    //
    // Dipasang di fase penangkapan (capture) supaya berjalan sebelum pendengar
    // milik halaman, dan pengiriman formulir benar-benar tertahan sampai
    // pengguna menjawab.

    function bacaOpsi(el) {
        var pesan = el.getAttribute('data-konfirmasi') || '';
        var jenis = el.getAttribute('data-konfirmasi-jenis');

        if (!jenis) {
            // Tebakan otomatis supaya tindakan merusak tidak tampil sedatar
            // tindakan biasa, tanpa perlu menandai satu per satu.
            var metode = (el.querySelector && el.querySelector('input[name="_method"]')) || null;
            var hapus = /hapus|batalkan|cabut|tolak|permanen|reset/i.test(pesan)
                || (metode && /delete/i.test(metode.value));
            jenis = hapus ? 'bahaya' : 'peringatan';
        }

        return {
            pesan: pesan,
            jenis: jenis,
            judul: el.getAttribute('data-konfirmasi-judul') || undefined,
            tombolYa: el.getAttribute('data-konfirmasi-ya') || undefined
        };
    }

    document.addEventListener('submit', function (e) {
        var form = e.target.closest ? e.target.closest('form[data-konfirmasi]') : null;
        if (!form || form.dataset.konfirmasiLolos === '1') return;

        e.preventDefault();
        e.stopPropagation();

        // Tombol yang ditekan ikut dicatat: banyak formulir memakai
        // <button name="aksi" value="..."> untuk membedakan tindakan, dan
        // form.submit() tidak menyertakan nilai itu.
        var penekan = form.querySelector('button[type="submit"]:focus, input[type="submit"]:focus');

        konfirmasi(bacaOpsi(form)).then(function (setuju) {
            if (!setuju) return;
            form.dataset.konfirmasiLolos = '1';

            if (penekan && penekan.name) {
                var titipan = document.createElement('input');
                titipan.type = 'hidden';
                titipan.name = penekan.name;
                titipan.value = penekan.value;
                form.appendChild(titipan);
            }

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        });
    }, true);

    document.addEventListener('click', function (e) {
        var pemicu = e.target.closest ? e.target.closest('a[data-konfirmasi], button[data-konfirmasi]') : null;
        if (!pemicu || pemicu.dataset.konfirmasiLolos === '1') return;

        // Tombol di dalam form[data-konfirmasi] sudah ditangani penyadap submit.
        if (pemicu.tagName === 'BUTTON' && pemicu.closest('form[data-konfirmasi]')) return;

        e.preventDefault();
        e.stopPropagation();

        konfirmasi(bacaOpsi(pemicu)).then(function (setuju) {
            if (!setuju) return;
            pemicu.dataset.konfirmasiLolos = '1';
            pemicu.click();
        });
    }, true);
})();
</script>
