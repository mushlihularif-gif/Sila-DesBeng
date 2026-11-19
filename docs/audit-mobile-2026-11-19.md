# Audit mobile, kemitraan, dan batas akun — 19 November 2026

## Perubahan

- Antar/Jemput pada sewa alat dan gas tetap dua kolom di ponsel; kartu aktif tidak membesar sendiri. Ringkasan alat/gas menempatkan subtotal di baris sendiri dan menjaga kontrol jumlah tetap terbaca.
- CSS/JS bersama dimuat oleh layout warga (termasuk RT/RW) dan layout admin (Desa, Super Admin, staf). Form, modal, avatar, ruang antarkomponen, dan tabel menyesuaikan layar kecil. Tabel yang belum memiliki wadah gulir mendapat wadah gulir sendiri; kolom tidak dibuang.
- Tombol Chat dan Pesan/Sewa pada empat halaman detail layanan menjadi dua kolom setelah pemilih jumlah di layar kecil.
- Latar gelombang yang sudah ada menjadi latar dasar halaman warga. Detail gas, kendaraan, dan edit konten RT/RW menggunakan partial gelombang yang sama. Kartu/form tetap memiliki latar putih agar teks terbaca.
- Status direktori desa berasal dari admin desa **aktif atau layanan desa aktif**. Layanan yang sudah dipensiunkan tidak dihitung. Desa bergabung di atas, kemudian abjad dalam masing-masing kelompok. Status direktori **tidak memberi hak akses** kepada akun.
- HTML publik sebelum perbaikan menampilkan Pematang Duku Timur sebagai belum bergabung sekaligus menampilkan Penyewaan Alat. Uji lokal mereproduksi kondisi layanan aktif tanpa admin desa aktif. Perbaikan menggunakan data layanan, bukan pengecualian nama desa.
- Form kemitraan menampilkan pilihan jabatan yang memang tersedia: pengelola desa jika belum ada admin desa aktif; RW/RT untuk domisili yang sudah memiliki admin desa. Dropdown lama tetap tersinkron. Pendaftaran staf tidak ditambahkan ke jalur publik.
- Pengajuan RT/RW tanpa domisili atau lintas desa ditolak. Nama dan email pengajuan diambil dari akun yang masuk. Pengajuan invalid tidak lagi membuat RW baru. Persetujuan tidak boleh mengubah akun dengan email berbeda dari pemohon atau memproses ulang pengajuan yang sudah diputuskan. Sandi akun baru dari persetujuan menggunakan generator acak.
- Dokumen kemitraan baru disimpan pada disk lokal privat, dibuka melalui pemeriksaan wewenang admin dengan `Cache-Control: private, no-store`. Perintah `kemitraan:protect-documents` menyalin dokumen lama, memeriksa SHA-256, memperbarui referensi, lalu menghapus salinan publik. Aman dijalankan ulang. Tidak menyentuh berkas KYC/AAD.

- Pusat Bantuan memakai tema/kontak SegiDaerah. Alur tiket lama belum mempunyai tabel migrasi, halaman tiket, maupun panel admin; URL lama diarahkan ke kontak bantuan tanpa mengklaim tiket tersimpan. Tidak ada data tiket yang dihapus. Tautan profil pengembang admin yang menunjuk tampilan hilang diarahkan ke bagian tim di profil sistem yang sudah ada.

## Akun dan kewenangan

| Akun | Cara membuat/mengelola | Batas |
|---|---|---|
| Super Admin | SQL awal yang disiapkan terpisah di komputer pemilik | Platform; SQL tidak menimpa email/username yang sudah ada |
| Admin Desa / BUMDes | Super Admin melalui pengelolaan wilayah; atau pengajuan kemitraan desa yang ditinjau | Operasional desa yang ditugaskan; BUMDes bukan role terpisah |
| Admin RW / RT | Admin Desa melalui **Wilayah dan RT/RW**, membuat akun atau mengangkat warga; tersedia juga pengajuan kemitraan | RW/RT di bawah desa sendiri; domisili dan hierarki dicek di server |
| Staf | Admin Desa melalui **Staf Layanan**, memilih unit layanan | Unit yang dicentang dalam desa tersebut; bukan verifikasi identitas, pengelolaan admin, atau pengaturan platform |
| Warga | Registrasi biasa | Akun/data/pesanan miliknya; tidak memperoleh kewenangan karena status desa di direktori |

SQL dan sandi awal sengaja berada di luar repository. SQL memakai hash bcrypt, bukan sandi polos di kolom password. Verifikasi identitas warga tidak dipalsukan oleh SQL ini.

## Verifikasi dan batasnya

- PHPUnit: **277 tes, 4.485 assertion**, lulus. Termasuk kompilasi seluruh Blade, matriks peran yang sudah ada, cakupan wilayah/staf, manipulasi email pengajuan, dokumen privat, dan kompatibilitas dokumen lama.
- JavaScript: **25 tes**, lulus. Vite production build berhasil.
- Seluruh 194 template Blade diperiksa sintaksnya. Sebanyak 117 rujukan view literal pada aksi rute aktif dipastikan tersedia. Inventaris 127 halaman ada di `audit-mobile-inventory-2026-11-19.csv`. Ini membedakan pemeriksaan sumber/layout dari pengujian browser halaman penuh; tidak menyamakan keduanya.
- Uji browser mencakup 35 halaman/varian peran (27 template unik, 175 kombinasi viewport). Uji browser menggunakan HTML aplikasi yang dirender dengan fixture sintetis, Chrome headless pada lebar 320, 360, 390, 768, dan 1360 px. Tidak menggunakan identitas atau transaksi produksi. Hasil browser dan tangkapan layar disimpan terpisah dari repository.
- Pengujian visual ini tidak mengirim pesanan, transfer, email, atau WhatsApp sungguhan. SDK Google Maps diblokir pada pengujian tata letak; pengujian ini tidak membuktikan bahwa pembatasan Geocoding API di Google Cloud sudah diperbaiki.
- Belum melakukan uji manual seluruh halaman dan tombol dengan enam akun di hosting. Halaman yang hanya diperiksa sumber/layout ditandai pada inventaris. Perubahan di Git baru berlaku di hosting setelah pull dan penyalinan aset.
- Pemindahan dokumen lama hanya menangani berkas yang direferensikan pengajuan pada `storage/app/public/partner_applications`. Salinan terpisah di document root, backup publik, dan berkas tanpa referensi perlu diperiksa pada hosting. Jangan menyatakan semuanya sudah privat sebelum hasil perintah dan lokasi salinan diperiksa.

## Pemasangan di cPanel

Kode ini tidak menambah kolom database. Simpan cadangan database serta folder dokumen sebelum menjalankan pemindahan dokumen lama. Document root hosting terpisah dari repository, sehingga CSS/JS/build harus ikut disalin.

```bash
(
  set -e
  cd /home/inon1796/repositories/Sila-DesBeng
  git pull --ff-only origin main
  tujuan=/home/inon1796/public_html/siladesbeng.inovasia.site
  mkdir -p "$tujuan/build" "$tujuan/css" "$tujuan/js"
  cp -R public/build/. "$tujuan/build/"
  cp public/css/*.css "$tujuan/css/"
  cp public/js/*.js "$tujuan/js/"
  /opt/alt/php82/usr/bin/php artisan config:cache
  /opt/alt/php82/usr/bin/php artisan view:cache
)
```

Setelah cadangan tersedia, jalankan di repository:

```bash
/opt/alt/php82/usr/bin/php artisan kemitraan:protect-documents
```

Periksa bahwa jumlah dokumen yang perlu ditindaklanjuti adalah 0. Jika document root mempunyai salinan folder storage sendiri, jangan anggap salinan itu ikut dipindahkan. Jangan kembali ke kode lama yang hanya memahami folder publik setelah referensi dokumen menjadi privat tanpa memulihkan pasangan database/berkasnya.
