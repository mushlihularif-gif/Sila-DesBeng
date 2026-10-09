# Portal SiladesBeng

Halaman portal statis (HTML + CSS + JS murni, tanpa Laravel) sebagai pintu masuk
sebelum warga menuju aplikasi. Terpisah dari aplikasi utama supaya bisa diunggah
ke hosting mana pun — termasuk shared hosting biasa, GitHub Pages, atau Netlify.

## Mengisi dua link tombol

Buka [index.html](index.html), cari blok `KONFIG_PORTAL` di dalam `<head>`
(sekitar baris 25). Hanya bagian ini yang perlu diubah:

```js
const KONFIG_PORTAL = {
    linkWeb:   'https://app.siladesbeng.id',                  // tombol "Buka Layanan Web"
    linkDrive: 'https://drive.google.com/file/d/xxxx/view',   // tombol "Unduh Aplikasi Mobile"

    email:    'siladesbeng@bengkaliskab.go.id',
    telepon:  '(0766) 21001',
    alamat:   'Kompleks Perkantoran Pemkab Bengkalis, Jl. Antara, Bengkalis, Riau'
};
```

- `linkWeb` — alamat aplikasi Laravel SiladesBeng yang sudah di-hosting.
- `linkDrive` — link Google Drive berisi berkas APK aplikasi Android.
- Selama nilainya masih `'BELUM_DIISI'`, tombol otomatis berubah jadi
  "Segera Hadir" dan tidak bisa diklik, jadi tidak ada link mati di halaman.
- `email`, `telepon`, `alamat` ikut mengisi bilah atas dan footer.

Setiap tombol di halaman ini ditandai `data-tautan="web"` atau `data-tautan="drive"`,
dan seluruhnya diisi otomatis dari satu blok konfigurasi di atas — tidak perlu
mengubah `href` satu per satu.

## Cara mengunggah

Salin seluruh isi folder `portal/` ke direktori root domain (`public_html/`):

```
index.html
style.css
script.js
assets/img/brand/...
assets/img/unit/...
```

Tidak perlu PHP, database, atau proses build.

## Menambah tangkapan layar aplikasi

Bingkai ponsel di bagian *Aplikasi* memutar tangkapan layar dari
`assets/img/app/` secara otomatis setiap 4,5 detik.

Untuk menambah layar baru: taruh berkasnya di `assets/img/app/`, lalu tambahkan
satu baris `<img>` di dalam `<div class="jalur-layar">` pada `index.html`.
Jumlah titik penanda dan urutan pergantiannya menyesuaikan sendiri — tidak ada
angka yang perlu diubah di `script.js`.

Gunakan gambar tegak berbanding 1:2 (misalnya 800x1600) supaya pas di bingkai
tanpa terpotong. Slide berhenti berputar saat kursor menunjuknya, saat tab
disembunyikan, dan saat perangkat menyalakan mode hemat gerak.

## Susunan halaman

| Bagian | Isi |
| --- | --- |
| Hero | Judul, ringkasan, **dua tombol utama** |
| Statistik | Jumlah unit layanan, kecamatan, desa |
| Tentang | Penjelasan SiladesBeng + papan unit layanan |
| Layanan | Tujuh kartu unit layanan |
| Aplikasi | Fitur versi Android + tombol unduh |
| Cara Memulai | Empat langkah pemakaian |
| Akses | Pengulangan dua tombol utama dalam kartu terpisah |
| Footer | Navigasi, layanan, kontak |

## Tema

Warna dan tipografi mengikuti aplikasi SiladesBeng: biru institusional `#115789`,
aksen emas `#ffab00`, font `Inter`. Warna tiap kartu unit layanan disamakan dengan
legenda grafik unit di halaman beranda aplikasi.
