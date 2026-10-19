# Audit SegiDaerah — 19 Oktober 2026

## Status perbaikan — 19 Oktober 2026

Sepuluh temuan prioritas di bawah telah ditangani dalam kode. Daftar temuan asli dipertahankan sebagai catatan kondisi sebelum perbaikan.

- Booking API sekarang memakai pengguna request yang benar, transaksi database, dan penguncian stok; jumlah fasilitas divalidasi terhadap stok yang diminta.
- Route riwayat mobil/fasilitas tidak lagi tertangkap sebagai ID produk.
- CSP peta, handler modal fallback, respons geocoding terlambat, pin peta pasar, dan field koordinat enam form unit diperbaiki.
- Tombol Lihat laporan terhubung. Edit/hapus berita dan pengumuman tersedia bagi penulis konten, dengan validasi jangkauan wilayah dan gambar. Daftar berita/pengumuman dipisah sesuai kategori.
- Pembatalan pasar memakai endpoint khusus, izin unit dan wilayah, transaksi database, pengembalian stok, serta mekanisme pencatatan refund yang sudah ada. Aksi berulang ditolak. Refund otomatis mengikuti aturan saldo yang ada; pembayaran manual tetap perlu dikembalikan pengelola.
- Booking mobil web memakai nomor booking sebagai nomor bukti; gambar bukti tetap dibuat oleh ReceiptController yang sudah tersedia. Insert transaction_receipts yang tidak sesuai skema dihapus. Tidak memerlukan migrasi database.
- Tautan notifikasi pesanan, menu footer, fallback gambar, ikon Boxicons, ilustrasi delegasi, watermark KTP, dan tampilan bukti pembayaran pasar diperbaiki.
- Endpoint daftar chat pasar diarahkan ke tab chat yang tersedia.
- Kontrol contoh pada halaman pengaturan lama diganti dengan navigasi pusat notifikasi dan status koneksi Google yang nyata. Form lama tidak lagi mengklaim preferensi tersimpan.
- Email kontak portal disamakan dengan footer. Tautan sosial kosong disembunyikan sampai SOCIAL_TIKTOK_URL, SOCIAL_FACEBOOK_URL, dan SOCIAL_YOUTUBE_URL dikonfigurasi. Instagram yang sudah ada dipertahankan karena akun pengganti belum ditentukan.

Validasi perbaikan: PHPUnit 29 tes / 108 assertion lulus; tiga tes JavaScript peta lulus. Tes meliputi rollback transaksi, stok kurang, pemetaan route, receipt mobil, hak akses konten, pembatalan/refund pasar satu kali, serta respons geocoding terlambat. Database pengujian menggunakan SQLite terisolasi. Pengujian seluruh interaksi berlogin pada produksi tetap belum dilakukan.

Untuk cPanel: Update from Remote pada branch main, kemudian Deploy HEAD Commit agar aset public dan cache route/view ikut diperbarui. Build frontend disertakan dalam commit; tidak perlu npm install atau migrasi baru untuk perubahan ini. Alternatif melalui terminal di folder repository: bash deploy.sh.

Konten historis di database dan gambar banner yang memuat identitas lama belum diganti. Kandidat masalah pada endpoint lama yang tidak dipakai UI tetap memerlukan audit terpisah; perubahan ini tidak mengklaim seluruh fitur produksi bebas bug.

## Cakupan dan batas

Audit kode lokal mencakup route, controller, view, aset, alur booking, tombol admin/RT/RW, dan peta. Pemeriksaan online sebagai tamu dilakukan pada beranda, kategori Belanja dan Kebutuhan, pemilihan wilayah, dan katalog gas Pematang Duku Timur di https://siladesbeng.inovasia.site. Halaman tersebut berhasil dibuka. Belum dilakukan pengujian semua peran berlogin, pembayaran, atau transaksi pada produksi. Kesamaan seluruh kode lokal dengan versi hosting belum diverifikasi.

Tidak ada perubahan kode aplikasi atau data bisnis. Temuan kode di bawah tidak otomatis berarti telah direproduksi pada produksi.

## Pemeriksaan yang lulus

- Build Vite berhasil: 58 modul; hasil audit ditempatkan di storage/app/audit-build-20261018, bukan mengganti build publik.
- PHPUnit: 10 tes, 31 assertion lulus. Cakupan tes yang ada belum meliputi mayoritas alur bisnis di bawah.
- Pemeriksaan sintaks PHP pada app, routes, config, database, dan tests lulus.
- Inventaris route lokal: 502 route.
- Logo utama yang diperiksa sudah menggunakan identitas SD/SegiDaerah. Nama file dan route lama tidak otomatis merupakan bug.

## Temuan prioritas

### 1. API booking dapat gagal setelah pesanan dibuat (tinggi)

RentalBookingApiController.php:135, MobilBookingApiController.php:151, FasilitasBookingApiController.php:150, di app/Http/Controllers/Api, membaca `$user->id` dalam method store tanpa mendefinisikan `$user` di method tersebut. Variabel pada method index tidak berlaku di store. Blok tersebut dijalankan setelah pembuatan booking/perubahan stok. Permintaan dapat berakhir error meskipun sebagian perubahan sudah tersimpan; pengulangan oleh klien berisiko menghasilkan pesanan ganda. Perbaiki pengambilan pengguna dan atomicity transaksi. Bukti: penelusuran kode; tidak membuat pesanan di produksi.

### 2. Riwayat booking mobil/fasilitas salah masuk route detail (sedang)

routes/api.php:136–145 mendaftarkan `/mobil/{id}` dan `/fasilitas/{id}` sebelum `/mobil/my-bookings` dan `/fasilitas/my-bookings`, tanpa pembatas angka. Pencocokan route terisolasi menunjukkan `my-bookings` diterima sebagai id oleh method show. Akibatnya klien tidak mendapat riwayat yang diminta. Daftarkan route statis lebih dulu atau batasi parameter id.

### 3. Peta detail laporan bertentangan dengan CSP (sedang)

app/Http/Middleware/SecurityHeaders.php:55 hanya mengizinkan frame dari accounts.google.com. resources/views/user/laporan/show.blade.php:371 dan resources/views/admin/pelaporan/show.blade.php:188 memakai iframe maps.google.com. Browser yang menerima header ini akan memblokir peta detail. Sesuaikan allowlist secara spesifik dengan sumber embed yang digunakan.

### 4. Tombol konfirmasi/batal lokasi tidak dipasang pada fallback Leaflet (sedang)

resources/views/user/laporan/create.blade.php:557–558 memasang handler hanya pada inisialisasi Google Maps. Jalur initLeafletPicker pada baris 749 tidak memasangnya. Saat peta cadangan berjalan langsung, modal lokasi tidak memiliki handler konfirmasi/batal. Probe JavaScript terhadap jalur fallback menghasilkan nol handler untuk kedua tombol. Pindahkan binding modal ke inisialisasi bersama.

### 5. Pin peta penjual memakai aset hilang (sedang)

resources/views/admin/unit/pasar_daerah/create.blade.php:174 dan edit.blade.php:188 meminta User/img/marker-icon.png serta marker-shadow.png yang tidak ada di public. Koordinat dapat dipilih tetapi gambar pin gagal dimuat. Gunakan aset marker yang tersedia dan periksa kembali pada hosting.

### 6. Tombol Lihat laporan RT/RW masih placeholder (sedang)

resources/views/user/wilayah/laporan.blade.php:224 hanya menampilkan pesan “Fitur detail sedang disiapkan”. Route wilayah.laporan.show dan controller detail sudah tersedia. Pengguna tidak dapat mencapai proses tindak lanjut melalui tombol daftar ini. Hubungkan ke halaman detail.

### 7. Setuju/tolak pembatalan pesanan pasar tidak mengirim perubahan (sedang)

resources/views/admin/unit/pasar_daerah/pesanan_show.blade.php:449–463 hanya menampilkan konfirmasi/toast. Pada pesanan dengan cancellation_status pending, kedua tombol tidak mengirim permintaan server. Permintaan pembatalan tetap tertunda. Ada implementasi di jalur Aktivitas yang dapat dijadikan acuan.

### 8. Edit/hapus berita dan pengumuman RT/RW belum diimplementasikan (sedang)

resources/views/user/wilayah/berita.blade.php:169 dan pengumuman.blade.php:146 hanya menampilkan toast “sedang disiapkan”; route wilayah terkait juga hanya index/store. Tombol memberi kesan tersedia, tetapi pengelola tidak bisa memperbaiki atau menghapus konten melalui jalur ini.

### 9. Menu footer tertukar (rendah; terlihat online)

resources/views/partials/footer.blade.php:33–39 mengarahkan “Profil dan Layanan” ke /pelayanan dan “Tentang Layanan” ke /layanandaerah/profil-layanan. Ini berkebalikan dengan menu navigasi utama. Kedua pasangan label/URL terlihat langsung pada situs online. Samakan pemetaan menu.

### 10. Fallback gambar aktivitas pasar juga hilang (rendah)

resources/views/users/partials/activity-pasar.blade.php:14 mengalihkan gambar gagal ke User/img/elemen/pasar.png yang tidak ada; aset yang tersedia bernama PasarDaerah.png. Handler onerror juga tidak dinonaktifkan. app/Http/Controllers/Api/HistoryController.php:128 memakai fallback hilang yang sama.

## Konsistensi tampilan dan branding

- Beberapa class ikon tidak tersedia dalam Boxicons lokal, misalnya bx-money-withdraw di admin/keuangan/index.blade.php:143,252 dan bx-inbox di admin/partials/inbox-rail.blade.php:214. Perlu penyamaan ikon dengan versi pustaka yang benar-benar dimuat.
- Footer sosial TikTok/Facebook/YouTube masih href="#"; Instagram masih mengarah ke isewa_id. Tautan ini juga terlihat di situs online. Konfirmasi akun tujuan sebelum mengganti.
- Beranda online masih menampilkan judul berita lama tentang SiladesBeng. Ini bisa merupakan arsip historis; jangan mengganti otomatis tanpa keputusan editorial.
- Gambar banner online tampak masih membawa identitas lama; perlu pemeriksaan ulang aset banner pada resolusi asli sebelum menyimpulkan seluruh rebranding selesai.
- Nama domain, database, file logo, cookie, dan identifier route lama dapat tetap dipakai untuk kompatibilitas; bukan alasan melakukan penggantian massal.

## Pemeriksaan lanjutan yang diperlukan

- Uji lengkap peran user, admin desa/kecamatan, RT/RW, staf, dan super admin menggunakan akun uji.
- Uji transaksi di staging: sukses/gagal booking, rollback stok, bukti transaksi, pembatalan, dan saldo.
- Verifikasi skema transaction_receipts terhadap pembuatan receipt mobil: controller mengirim amount/issued_at, sedangkan model memakai total_amount dan field wajib lain. Ini kandidat masalah tambahan; database aktual belum diperiksa.
- Verifikasi form koordinat gas/alat/fasilitas, pembatalan geocoding yang masih berjalan, dan sumber tile/marker Leaflet terhadap CSP.
- Periksa route yang mengarah ke method belum tersedia, termasuk KritikSaranController@store; pastikan apakah masih dipakai oleh UI aktif.
- Pengaturan notifikasi lama memiliki handler sukses tanpa penyimpanan; pastikan halaman masih digunakan sebelum memasukkannya ke perbaikan prioritas.

Kesimpulan: build dan tes dasar sehat, tetapi ada kekurangan fungsional yang nyata. Prioritaskan konsistensi data booking, peta, dan tombol alur operasional sebelum pemolesan visual. Audit ini belum merupakan sertifikasi bahwa semua fitur produksi telah diuji end-to-end.
