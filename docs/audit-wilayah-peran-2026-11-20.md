# Audit wilayah, peran dan alur layanan — 20 November 2026

> Catatan ini merekam paket `456c2a7`. Tindak lanjut dengan dump SQL nyata, penyelarasan ENUM, antrean cleanup dan status verifikasi terbaru ada di [audit-dump-hosting-2026-11-20.md](audit-dump-hosting-2026-11-20.md). Daftar tindak lanjut di bawah merupakan kondisi sebelum paket lanjutan tersebut.

Audit ini memeriksa source code lokal dan tes sintetis. Hasil SQL hosting yang diberikan pengguna dipakai untuk memahami data akun; database hosting tidak diubah langsung oleh Codex. Ini bukan pernyataan bahwa seluruh aplikasi dan semua integrasi eksternal sudah bebas cacat.

## Perbaikan pada paket ini

| Area | Perubahan dan pembuktian |
| --- | --- |
| Struktur kecamatan yang sudah dipensiunkan | Commit sebelumnya menghapus presentasi aparatur kecamatan dari direktori web. Paket ini juga menghapus aparatur contoh otomatis pada API dan tidak lagi menampilkan struktur kecamatan di respons API. |
| Wilayah Super Admin | Peta dan pengelolaan wilayah dimulai pada Kecamatan Bengkalis. Wilayah di kecamatan lain tidak masuk pilihan. Request manipulasi di luar batas ditolak; akar Kecamatan Bengkalis tidak dapat diubah/dihapus lewat pengelola struktur. |
| Desa/kelurahan | Keduanya tetap tipe sah di bawah Kecamatan Bengkalis. Label kelurahan tidak berarti memperluas cakupan ke kecamatan lain. |
| Pengelola struktur pemerintahan | Struktur desa/kelurahan hanya dikelola admin desa pemilik wilayah. Menu struktur pemerintah kabupaten untuk Super Admin dihapus; URL perubahan juga dibatasi. Data lama tidak dihapus. |
| Direktori layanan publik | Menampilkan kartu desa yang sudah bergabung dan tautan profil penyedia. Desa yang baru bergabung muncul dari data, tanpa hardcode Pematang Duku Timur. Profil desa di luar kecamatan ditolak. |
| Akun warga pada RT/RW | Kategori layanan menelusuri desa induknya; tidak lagi mencari unit hanya pada RT/RW sehingga kategori kosong. Pilihan desa eksplisit tetap diutamakan. |
| Jumlah desa bergabung | Tetap satu record desa, bukan jumlah akun. Tes menambahkan dua admin dan layanan pada desa yang sama: desa tetap dihitung sekali. |
| Lokasi layanan | Nama, alamat dan koordinat disimpan per wilayah. Uji perubahan nama lokasi Desa A membuktikan produk Desa B dengan nama lokasi sama tidak ikut berubah; edit/hapus lokasi desa lain ditolak untuk admin desa. Validasi lokasi oleh Super Admin memakai wilayah record tujuan. Penambahan lokasi tanpa desa pengelola ditolak. |
| Media KYC | Akun nonaktif dan admin desa dengan penempatan tingkat kecamatan ditolak. Peninjauan juga memeriksa tipe wilayah. Label kamera menyatakan foto selesai diambil, bukan identitas terverifikasi. |
| Penjelasan kriptografi | Dokumentasi terpisah mencatat web JPEG80 versus byte API, AAD versi 2, OCR.space/Gemini, format berkas, otorisasi dan batas retensi. Tidak mengganti algoritma atau format enkripsi. |

## Aturan desa bergabung dan tampilan layanan

`VillageParticipation` memberi status bergabung bila sebuah desa/kelurahan memiliki setidaknya satu admin desa aktif **atau** setidaknya satu layanan aktif yang masih tersedia. `withExists` menghasilkan boolean per wilayah, bukan penggandaan baris berdasarkan akun. Peta kemitraan mengurutkan yang bergabung lebih dahulu, kemudian nama.

Karena layanan aktif juga dihitung, mengubah role admin tidak selalu membuat desa hilang dari daftar. Jika status berubah menjadi nol pada hosting, periksa pula pivot layanan aktif dan apakah akun/layanan mengacu ke ID desa yang sama. Status direktori tidak digunakan untuk memberikan izin akun. Dua record wilayah duplikat tetap merupakan masalah data tersendiri; tes dua admin tidak membuktikan database produksi bebas record desa duplikat.

Beranda menghitung layanan dari wilayah warga dan garis induknya, serta beberapa pemeriksaan produk yang tersedia. Carousel adalah pintu kategori, bukan daftar produk yang membuktikan semua barang berasal dari domisili pengguna. Katalog layanan juga memiliki aturan layanan terbuka versus eksklusif warga lokal. Melihat layanan terbuka desa lain tidak sama dengan memperoleh izin mengelolanya.

Di `/layanandaerah/profil-layanan`, kartu penyedia yang sudah bergabung membantu warga memilih desa. Kartu membawa pengguna ke profil desa beserta kategori layanan dengan `region_id`. Direktori tetap menyediakan tautan daftar seluruh desa untuk status kemitraan. Warga tidak diwajibkan membuka semua desa yang belum bergabung.

## Peran dan data hosting

Peran yang didukung `User::ROLES` adalah `user`, `admin_rt`, `admin_rw`, `admin_desa`, `super_admin`, `staff`.

| Peran | Lingkup utama |
| --- | --- |
| Warga (`user`) | Profil/alamat, pengajuan, pesanan dan kiriman sendiri; katalog sesuai aturan akses layanan |
| Admin RT | Pengelolaan lingkungan RT yang ditugaskan; antarmuka warga tetap tersedia |
| Admin RW | Wilayah RW dan RT di bawahnya; tidak menjadi pengelola semua layanan desa |
| Admin desa | Warga dan struktur desa/RW/RT terkait, pengaturan layanan/pembayaran, lokasi, konten dan peninjauan KYC dalam wilayah, serta pembuatan staf |
| Staf | Unit layanan yang secara eksplisit diberikan oleh admin desa pembuatnya, di desa yang sama; bukan pengelola akun, rekening/pengaturan kebijakan desa, atau verifikator KYC |
| Super Admin | Administrasi platform dan kemitraan/struktur dalam cakupan aplikasi; beberapa jalur operasional/kompatibilitas memiliki akses khusus yang diuji tersendiri |

Izin staf saat ini **per unit layanan**, misalnya gas/sewa alat/fasilitas/transportasi. Ini belum berarti izin terpisah untuk setiap gedung atau setiap barang di dalam satu unit. Dua staf fasilitas dalam desa yang sama tidak otomatis dibatasi ke dua gedung berbeda. Penugasan pengurus/sopir pada pesanan adalah mekanisme lain.

String `admin` pada middleware merupakan alias kelompok peran modern. Itu tidak berarti nilai role database lama `admin` masih merupakan akun operasional yang sah. `admin_kecamatan` dan `admin` masih ada pada ENUM lama dan masih dipakai empat akun hosting berdasarkan rekap pengguna. Menghapus nilai ENUM tanpa memetakan akun-akun tersebut tidak aman.

Pengguna memilih pemulihan akun desa ID 9 ke `admin_desa`, tetap pada desa ID 12. SQL dibatasi pada ID/email/region/role lama yang cocok dan mengosongkan remember token. Akun Super Admin pribadi tidak diubah. Eksekusi di phpMyAdmin serta hasil login perlu dikonfirmasi pengguna. Akun lama lain dan dua staf dengan penempatan yang tidak sesuai tidak otomatis dikonversi, dihapus, atau diaktifkan oleh paket ini.

## Batas pemeriksaan dan tindak lanjut

1. Pastikan pemulihan akun ID 9 benar-benar dieksekusi dan login admin desa berhasil. Tentukan pemilik/penugasan empat akun ber-role lama sebelum menyusutkan ENUM. Staf lama yang tidak memiliki desa/pembuat/izin sah sebaiknya ditata melalui alur Staf Layanan, bukan hanya mengganti role di phpMyAdmin.
2. Tes kode menggunakan database SQLite sementara dan lokasi sintetis. Ini tidak membuktikan seluruh isi MySQL hosting, seluruh akun nyata, atau semua tautan produksi sudah diuji manual.
3. Komponen peta dan penyimpanan koordinat tercakup tes rendering/alamat serta tes isolasi lokasi. Pengujian browser lokal menggunakan SDK peta tiruan pada pemeriksaan layout; hasil itu tidak membuktikan billing, API restriction, pencarian alamat, izin GPS atau share sheet perangkat di hosting. Perlu uji langsung dengan konfigurasi Google aktif dan akun tiap peran.
4. AAD baru tidak otomatis mengenkripsi ulang arsip lama. Cleanup draf/penggantian yang gagal menghapus file masih membutuhkan mekanisme pemulihan yang lebih kuat; jangan mengklaim Zero-Retention absolut.
5. Pemindaian wajah browser bukan pencocokan biometrik server yang tervalidasi. Keputusan identitas tetap manual oleh admin. Benchmark formal skripsi belum dilakukan pada audit ini.

Tes relevan: `BengkalisScopeTest`, `PartnershipAuditTest`, `ServiceCategoryPageTest`, `SupportedRolesTest`, `ServiceStaffAuditTest`, `RoleOperationalAuditTest`, `ServiceRegionAccessTest`, `GoogleMapsAddressTest`, `GoogleMapsPagesTest`, `IdentityDocumentAadTest`, `IdentityReviewAuditTest`, dan `IdentityDocumentEncryptionTest`.

## Hasil verifikasi paket ini

- PHPUnit: **294 tes lulus, 4.612 assertion**, PHP CLI 8.2.35, database uji SQLite sementara.
- JavaScript: **25 tes lulus**, termasuk komponen peta dengan SDK tiruan dan alur berbagi titik.
- **194 template Blade** berhasil dikompilasi dan diperiksa sintaks PHP-nya.
- Build produksi Vite berhasil; tidak menghasilkan perubahan aset build yang perlu di-commit.
- Direktori layanan dan peta wilayah Super Admin diperiksa melalui Chrome headless pada lebar **320, 360, 390, 768, dan 1360 piksel**: tidak ditemukan overflow kontrol atau error JavaScript pada skenario tersebut. Screenshot mobile kedua halaman diperiksa secara visual.
- Tidak ada migrasi database baru atau pergantian APP_KEY dalam paket ini.

Angka tersebut adalah tes regresi/pengembangan aplikasi. Tidak menyatakan semua halaman produksi, semua peran pada data nyata, atau seluruh keamanan aplikasi sudah lolos audit menyeluruh.
