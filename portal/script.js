/* ============================================================
   PORTAL SiladesBeng — perilaku halaman

   CATATAN: link tombol TIDAK diatur di berkas ini.
   Ubah KONFIG_PORTAL di bagian <head> index.html.
   ============================================================ */

(function () {
    'use strict';

    // Terima dua cara penulisan: window.KONFIG_PORTAL (dianjurkan) maupun
    // deklarasi const/let di <head>, yang tidak pernah menempel di window.
    var konfig = window.KONFIG_PORTAL
        || (typeof KONFIG_PORTAL !== 'undefined' ? KONFIG_PORTAL : {});
    var BELUM = 'BELUM_DIISI';

    /* ---------- 1. Pasang dua tautan utama ----------
       Setiap elemen bertanda data-tautan="web" / "drive" akan
       diarahkan ke alamat yang ada di KONFIG_PORTAL. Selama masih
       'BELUM_DIISI', tombolnya berubah jadi "Segera hadir" dan
       tidak bisa diklik — bukan link mati. */
    function pasangTautan(nama, alamat, labelTunggu) {
        var terisi = typeof alamat === 'string' && alamat.trim() !== '' && alamat !== BELUM;
        var daftar = document.querySelectorAll('[data-tautan="' + nama + '"]');

        Array.prototype.forEach.call(daftar, function (el) {
            if (terisi) {
                el.setAttribute('href', alamat);
                el.setAttribute('target', '_blank');
                el.setAttribute('rel', 'noopener noreferrer');
                el.removeAttribute('aria-disabled');
                el.style.removeProperty('opacity');
                el.style.removeProperty('cursor');
                return;
            }

            el.setAttribute('href', '#akses');
            el.setAttribute('aria-disabled', 'true');
            el.setAttribute('title', 'Tautan belum diisi oleh pengelola');
            el.style.opacity = '.62';
            el.style.cursor = 'not-allowed';

            var label = el.querySelector('[data-label="' + nama + '"]');
            if (label) label.textContent = labelTunggu;

            el.addEventListener('click', function (e) { e.preventDefault(); });
        });
    }

    pasangTautan('web', konfig.linkWeb, 'Web Segera Hadir');
    pasangTautan('drive', konfig.linkDrive, 'Aplikasi Segera Hadir');

    /* ---------- 2. Kontak dari konfigurasi ---------- */
    function pasangKontak(nama, nilai, awalan) {
        if (!nilai) return;
        var daftar = document.querySelectorAll('[data-teks="' + nama + '"]');
        Array.prototype.forEach.call(daftar, function (el) {
            el.textContent = nilai;
            if (awalan && el.tagName === 'A') {
                el.setAttribute('href', awalan + nilai.replace(/[\s()-]/g, ''));
            }
        });
    }

    pasangKontak('email', konfig.email, 'mailto:');
    pasangKontak('telepon', konfig.telepon, 'tel:');
    pasangKontak('alamat', konfig.alamat, null);

    /* ---------- 3. Tahun berjalan di footer ---------- */
    var tahun = document.getElementById('tahun');
    if (tahun) tahun.textContent = new Date().getFullYear();

    /* ---------- 4. Menu mobile ---------- */
    var hamburger = document.getElementById('hamburger');
    var menuMobile = document.getElementById('menu-mobile');

    if (hamburger && menuMobile) {
        hamburger.addEventListener('click', function () {
            var terbuka = menuMobile.classList.toggle('terbuka');
            hamburger.setAttribute('aria-expanded', terbuka ? 'true' : 'false');
        });

        menuMobile.addEventListener('click', function (e) {
            if (e.target.closest('a')) {
                menuMobile.classList.remove('terbuka');
                hamburger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* ---------- 5. Navbar mengecil + tombol ke atas ---------- */
    var header = document.getElementById('header');
    var keAtas = document.getElementById('ke-atas');

    function saatGulir() {
        var y = window.scrollY || document.documentElement.scrollTop;
        if (header) header.classList.toggle('mengecil', y > 20);
        if (keAtas) keAtas.classList.toggle('tampil', y > 500);
    }

    window.addEventListener('scroll', saatGulir, { passive: true });
    saatGulir();

    if (keAtas) {
        keAtas.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ---------- 6. Animasi muncul saat digulir ---------- */
    var elemenAnimasi = document.querySelectorAll('.muncul, .beruntun');

    if ('IntersectionObserver' in window) {
        var pengamat = new IntersectionObserver(function (entri) {
            entri.forEach(function (bagian) {
                if (!bagian.isIntersecting) return;
                bagian.target.classList.add('tampil');
                pengamat.unobserve(bagian.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

        Array.prototype.forEach.call(elemenAnimasi, function (el) { pengamat.observe(el); });
    } else {
        Array.prototype.forEach.call(elemenAnimasi, function (el) { el.classList.add('tampil'); });
    }

    /* ---------- 7. Tangkapan layar aplikasi yang berganti sendiri ----------
       Jumlah slide dibaca dari isi HTML, jadi menambah tangkapan layar
       cukup dengan menambah satu <img> di dalam #jalur-layar. */
    var jalur = document.getElementById('jalur-layar');
    var wadahTitik = document.getElementById('titik-layar');

    if (jalur && jalur.children.length > 1) {
        var jumlah = jalur.children.length;
        var indeks = 0;
        var pewaktu = null;
        var JEDA = 4500;
        var hematGerak = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        var titik = [];
        for (var i = 0; i < jumlah; i++) {
            (function (ke) {
                var t = document.createElement('button');
                t.type = 'button';
                t.setAttribute('aria-label', 'Tampilkan layar ' + (ke + 1));
                t.addEventListener('click', function () { tampilkan(ke); mulai(); });
                if (wadahTitik) wadahTitik.appendChild(t);
                titik.push(t);
            })(i);
        }

        function tampilkan(ke) {
            indeks = (ke + jumlah) % jumlah;
            jalur.style.transform = 'translateX(-' + (indeks * 100) + '%)';
            titik.forEach(function (t, n) { t.classList.toggle('aktif', n === indeks); });
        }

        function mulai() {
            berhenti();
            if (hematGerak) return;
            pewaktu = setInterval(function () { tampilkan(indeks + 1); }, JEDA);
        }

        function berhenti() {
            if (pewaktu) { clearInterval(pewaktu); pewaktu = null; }
        }

        /* Berhenti saat ditunjuk supaya pengunjung sempat mengamati,
           dan saat tab disembunyikan supaya tidak berputar sia-sia. */
        jalur.addEventListener('mouseenter', berhenti);
        jalur.addEventListener('mouseleave', mulai);
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) berhenti(); else mulai();
        });

        tampilkan(0);
        mulai();
    }

    /* ---------- 8. Penanda menu aktif ---------- */
    var bagianHalaman = document.querySelectorAll('section[id]');
    var tautanNav = document.querySelectorAll('#nav a[href^="#"]');

    if (bagianHalaman.length && tautanNav.length && 'IntersectionObserver' in window) {
        var pengamatNav = new IntersectionObserver(function (entri) {
            entri.forEach(function (bagian) {
                if (!bagian.isIntersecting) return;
                var id = bagian.target.id;
                Array.prototype.forEach.call(tautanNav, function (a) {
                    a.classList.toggle('aktif', a.getAttribute('href') === '#' + id);
                });
            });
        }, { threshold: 0.35, rootMargin: '-80px 0px -45% 0px' });

        Array.prototype.forEach.call(bagianHalaman, function (s) { pengamatNav.observe(s); });
    }
})();
