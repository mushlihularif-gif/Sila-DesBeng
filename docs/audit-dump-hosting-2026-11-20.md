# Hasil pemeriksaan dump hosting dan penyelesaian audit 20 November 2026

Sumber data: ekspor SQL hosting yang diberikan pemilik, dibaca lokal tanpa mengimpor identitas, kredensial, sesi, atau transaksi nyata ke database pengujian. Dump dan kredensial tidak dimasukkan ke Git. Pemeriksaan ini tidak mengubah database hosting.

## Penyebab pilihan role phpMyAdmin masih banyak

Dropdown phpMyAdmin mengikuti tipe ENUM kolom `users.role`, bukan pilihan menu aplikasi. ENUM lama memuat delapan nilai. Ini bukan perbedaan server lokal dengan hosting; skemanya belum dipersempit walaupun aplikasi sudah memakai enam role.

Dump memuat 24 akun: 15 warga, dua `admin`, dua `admin_kecamatan`, satu `admin_desa`, dua `super_admin`, dan dua staf. Akun ID 9 sudah benar menjadi `admin_desa` pada desa ID 12. Belum ada akun RT/RW pada dump tersebut; fitur dan role keduanya tetap tersedia.

Migrasi `2026_11_20_000001_retire_legacy_account_roles`:

- Menyimpan penugasan lama pada `retired_account_assignments`, tanpa menyalin password, token atau dokumen identitas.
- Menonaktifkan empat akun ber-role lama dan memindahkan role-nya ke `user`. Akun tidak dihapus atau dipromosikan. Sesi, token API pengguna dan remember token dicabut.
- Menonaktifkan dua staf yang tidak memiliki penempatan desa/pembuat admin desa yang sah; role staf dipertahankan.
- Mempertahankan Admin Desa ID 9, kedua Super Admin dan warga lain. Staf valid yang dibuat admin desa tidak ikut dinonaktifkan.
- Menyusutkan ENUM MySQL menjadi `super_admin`, `admin_desa`, `user`, `admin_rt`, `admin_rw`, `staff`. Nilai tak dikenal menghentikan konversi akun secara atomik, bukan dipetakan sembarangan.
- Pengulangan migrasi aman. Rollback tidak mengaktifkan kembali hak akses yang sudah dipensiunkan dan tidak menghapus catatan asalnya.

Sesudah migrasi pada data yang sama: 15 warga aktif + 4 akun user nonaktif, 1 admin desa aktif, 2 super admin aktif, 2 staf nonaktif. Total tetap 24 akun. Tidak perlu SQL manual untuk mengubah ENUM lagi.

## Daftar permintaan dan hasil

| Permintaan | Hasil pemeriksaan/perbaikan |
| --- | --- |
| Hapus presentasi admin kecamatan | Presentasi struktur kecamatan dan aparatur contoh API sudah dipensiunkan pada paket sebelumnya. Kecamatan Bengkalis tetap menjadi wilayah induk, bukan role administrator. |
| Batasi wilayah operasional | Pengelolaan pohon, profil publik dan pilihan wilayah dibatasi Bengkalis. Dashboard Super Admin kini menghitung wilayah pada pohon yang sama, termasuk kelurahan; tidak menghitung seluruh 167 record lama. Data sejarah wilayah tidak dihapus. |
| Banyak desa setelah bergabung | Direktori memakai data desa/kelurahan, bukan hardcode PDT. Desa aktif tampil dahulu dan tiap kartu mengarah ke layanan/profil desanya. Kategori layanan warga RT/RW memakai desa induk. |
| Hitung desa, bukan akun | Tes dua admin pada desa sama memastikan tetap dihitung satu desa. Status bergabung berasal dari admin desa aktif atau layanan aktif yang tersedia; bukan berarti desa memiliki semua jenis layanan. |
| Pisahkan kewenangan desa | Tes perubahan lokasi/produk dengan nama sama di desa berbeda, pengurus, fasilitas, pesanan dan staf menolak operasi lintas desa. |
| Admin RT/RW | Tetap role sah; alur kemitraan/penugasan, warga turunannya, moderasi kabar dan pembatasan akses tercakup tes. Tidak diberikan kendali layanan atau struktur desa lain. |
| Staf | Dibuat oleh Admin Desa melalui Staf Layanan, dengan izin per unit layanan dan desa. Bukan pendaftaran bebas dan bukan izin otomatis ke seluruh menu. Pembatasan per gedung/produk individual belum menjadi model izin staf; pengurus/sopir pesanan merupakan mekanisme berbeda. |
| Lokasi produk | Komponen publik membaca lokasi sesuai desa dan nama lokasi produk, atau koordinat langsung produk. Tautan arah tersedia bila titik ada. Dump menunjukkan semua 14 produk belum memiliki koordinat atau kecocokan ke lokasi tersimpan: pengelola perlu mengisi lokasi nyata. |
| Foto KTP/wajah dan AAD | Implementasi tetap ChaCha20-Poly1305 dengan AAD pada berkas baru. Empat record KYC dump adalah `approved`, versi 1; ini tidak membuktikan isi file atau enkripsi ulang arsip. Penjelasan source lengkap tersedia pada dokumen konsultasi KYC. |
| Penghapusan dokumen gagal | Diperbaiki dengan antrean database, percobaan ulang sepuluh menit, pemulihan referensi pengajuan selesai, penguncian draf dan pencatatan penghapusan dalam transaksi yang sama. Gangguan notifikasi tidak membatalkan dokumen yang sudah tersimpan atau menghentikan cleanup. |
| Periksa hosting setelah pull | Ditambahkan `php artisan segidaerah:audit`, hanya baca: rekap role/status, ENUM, wilayah produk, titik yang belum lengkap, mesin kriptografi, rute hapus draf dan antrean cleanup. Tidak mencetak kunci, NIK, nama warga atau password. |

## Bukti data hosting

- 186 migrasi pada dump semuanya dikenal source; dua migrasi baru paket ini belum ada pada dump.
- 167 wilayah historis; 32 berada pada pohon Kecamatan Bengkalis, terdiri dari akar kecamatan dan 31 desa/kelurahan. Tidak ditemukan parent wilayah yang hilang.
- Semua sembilan alat, dua gas, dua kendaraan dan satu fasilitas berada di desa ID 12. Tidak ditemukan layanan aktif di luar Kecamatan Bengkalis.
- Dua lokasi layanan tersimpan; satu memiliki koordinat. Belum ada kecocokan lokasi tersebut dengan 14 produk. Tidak dilakukan penyamaan nama atau pengisian titik secara tebakan.

## Pengujian dan batas hasil

- PHPUnit: **303 tes lulus, 4.673 assertion** setelah menggabungkan perubahan login Google terbaru dari `main`; termasuk kompilasi dan pemeriksaan sintaks seluruh 194 template Blade, kontrol peran, isolasi desa, transaksi, alamat dan KYC.
- JavaScript: **25 tes lulus**, termasuk pemilihan titik, alamat tersimpan, penolakan GPS, berbagi tautan lokasi dan respons pencarian yang datang terlambat.
- Sembilan tes tambahan dibanding paket sebelumnya mencakup migrasi role, percobaan ulang cleanup, dashboard wilayah dan perintah pemeriksaan deployment. Ini tes pengembangan, bukan hasil eksperimen formal skripsi.

Migrasi role diuji juga pada MySQL 8.0.30 terpisah dengan data penugasan sintetis yang mengikuti bentuk dump. Uji memeriksa ENUM enam role, enam arsip penugasan, admin sah tetap utuh, pencabutan sesi/token, penolakan role lama, pengulangan migrasi, serta perlindungan rollback antrean yang belum kosong. Server pengujian sudah dihentikan setelah uji.

Pengujian Google Maps langsung dengan konfigurasi lokal berhasil memuat SDK, peta dan pemilihan koordinat. GPS simulasi mengisi titik. Geocoder mengembalikan `REQUEST_DENIED`. Kunci hosting tersimpan terenkripsi dan tidak dapat dibaca dengan kunci lokal; hasil tersebut tidak boleh diklaim sebagai hasil kunci hosting. Halaman detail gas publik hosting merespons HTTP 200, tetapi tidak memuat peta karena produk tidak mempunyai titik. Pemeriksaan browser akun hosting terhenti karena alat tidak dapat memastikan URL Chrome Windows. Billing, izin API, pencarian alamat, GPS dan share sheet perangkat produksi masih perlu verifikasi langsung.

Inventaris pemeriksaan tampilan per halaman tetap ada di `audit-mobile-inventory-2026-11-19.csv`. Kolom yang menyatakan belum diuji browser penuh bukan dianggap lulus. Kompilasi template, tes layout bersama dan tes peran tidak sama dengan mencoba setiap tombol pada seluruh akun produksi.

## Penerapan

Simpan ekspor database yang sudah dibuat. Jalankan di Terminal cPanel:

```bash
(
  set -e
  cd /home/inon1796/repositories/Sila-DesBeng
  git pull --ff-only origin main
  /opt/alt/php82/usr/bin/php artisan migrate --force
  tujuan=/home/inon1796/public_html/siladesbeng.inovasia.site
  mkdir -p "$tujuan/build" "$tujuan/css" "$tujuan/js"
  cp -R public/build/. "$tujuan/build/"
  cp public/css/*.css "$tujuan/css/"
  cp public/js/*.js "$tujuan/js/"
  /opt/alt/php82/usr/bin/php artisan config:cache
  /opt/alt/php82/usr/bin/php artisan route:clear
  /opt/alt/php82/usr/bin/php artisan route:cache
  /opt/alt/php82/usr/bin/php artisan view:cache
  /opt/alt/php82/usr/bin/php artisan segidaerah:audit
  git log -1 --oneline
)
```

Sesudah migrasi, scheduler yang sudah menjalankan `schedule:run` akan mengambil jadwal cleanup baru. `kyc:clean-files` dapat dijalankan manual untuk memeriksa percobaan pertama. Antrean kosong saja tidak membuktikan cron berjalan; periksa log cron hosting yang sudah digunakan. Tidak ada pergantian APP_KEY atau impor ulang dump dalam deployment ini.
