# Brain SiladesBeng (Catatan Memori Proyek)

Dokumen ini berfungsi sebagai pusat memori dan dokumentasi (pengganti Obsidian Vault) untuk memastikan rekam jejak pengembangan sistem tetap terjaga secara persisten.

## 1. Identitas Proyek
- **Nama Sistem:** SiladesBeng (Sistem Sinergi Layanan dan Aspirasi Desa di Kabupaten Bengkalis)
- **Kompetisi:** KMIPN VIII Tahun 2026 (Kategori E-Government)
- **Tim Pengembang:** Tim Gen Hello World (Politeknik Negeri Bengkalis)
- **Skala Implementasi:** Skala Kabupaten (Mencakup Kabupaten, 8 Kecamatan, 155 Desa, dan 47 Kelurahan di Bengkalis).
- **Aturan Ketat Lomba:** TIDAK BOLEH MENGGUNAKAN EMOJI DALAM SISTEM MAUPUN DOKUMENTASI.

## 2. Arsitektur dan Inovasi Utama
- **Multi-Tenant Architecture:** Satu sistem (codebase) dan satu database yang melayani seluruh hierarki pemerintahan dari tingkat Kabupaten hingga RT/RW dengan otonomi ruang kerja (workspace) masing-masing instansi.
- **Enam Modul Terintegrasi:**
  1. Sewa Kendaraan (ambulans, mobil pick-up, dll)
  2. Sewa Alat (tenda, kursi, sound system)
  3. Penjualan Gas LPG (Subsidi 3kg, opsi 5.5kg & 12kg)
  4. Fasilitas Umum (peminjaman lapangan, balai desa)
  5. Pengumuman dan Event (Kabar Daerah)
  6. Lapor Warga
- **Matriks Eskalasi Pelaporan (Zero-Bottleneck):** Laporan warga masuk ke RT, jika tidak tertangani akan otomatis/manual naik ke RW, lalu ke Desa, hingga Kecamatan/Kabupaten. Pimpinan daerah memiliki Hak Pantau Real-Time.
- **SiladesBeng Assistant (Kecerdasan Buatan):** Asisten virtual interaktif bermaskot robot bertanjak corak songket Melayu untuk memandu warga.
- **Keamanan Berlapis (Defense in Depth):** Autentikasi, RBAC (Role-Based Access Control), dan Enkripsi tingkat lanjut (AES-256 dan ChaCha20-Poly1305).
- **Omnichannel Payment:** Mendukung pembayaran digital (Midtrans), transfer manual, dan tunai (Cash on Delivery).

## 3. Riwayat Pekerjaan yang Telah Diselesaikan
- **Konsolidasi Modul dan UI:**
  - Menyatukan menu "Pengumuman" dan "Event" menjadi satu halaman terpadu bernama **Kabar Daerah**.
  - Merapikan tata letak (layout) kartu layanan dan navigasi, memastikan tinggi kartu seragam (simetris) dan teks tidak terpotong.
- **Penanganan Otentikasi dan Sesi (Session):**
  - Mengatasi masalah *Force Logout* (Sesi terputus secara acak) saat berpindah halaman.
  - Memecahkan kasus "Login selalu terlempar ke Guest": 
    - **Masalah:** Browser mengalami konflik dan menolak pengiriman cookie baru yang digenerate oleh fitur keamanan *Session Regeneration* milik Laravel setelah sukses melakukan POST request. Browser ngotot mengirimkan cookie sesi `laravel-session` lama yang sudah usang dan dianggap kosong oleh server.
    - **Solusi Final:** Mengubah nama cookie di `config/session.php` menjadi `siladesbeng_auth_session` untuk secara paksa me-reset memori browser tanpa harus menonaktifkan fitur keamanan *Session Regeneration*.
  - Memperbaiki *Middleware CheckRole* untuk meredirect admin yang mencoba mengakses halaman warga kembali ke Dashboard Admin tanpa memutus sesi.
  - Mengubah konfigurasi `CACHE_STORE` ke `database` dan menambahkan `DB_CACHE_CONNECTION=mysql` agar fitur *DDoS Protection* dapat bekerja secara optimal tanpa menimbulkan *crash* di lingkungan lokal (Laragon) maupun *Production* (Hosting cPanel).
- **Penyesuaian Aturan Lomba:**
  - Menghapus penggunaan emoji di seluruh antarmuka yang telah dikembangkan dan menggantinya dengan aset gambar vektor kustom yang telah disediakan.
- **Penyempurnaan UI/UX dan Stabilitas Navigasi:**
  - **[BfCache Fix]** Menambahkan `Cache-Control: no-store` di `SecurityHeaders` untuk memaksa browser melakukan *hard refresh* saat menekan tombol *Back*, guna mencegah *white screen* atau tampilan usang.
  - **[Layout Shift Fix]** Mengganti `overflow-x-clip` menjadi `overflow-hidden` pada elemen `<main>` di `beranda/index.blade.php`. Hal ini memecahkan bug di mana halaman beranda tiba-tiba bergeser ke kiri dan memunculkan ruang putih di kanan akibat elemen dekorasi (*background ovals*) yang keluar dari batas *viewport* saat memulihkan halaman dari *history*.
  - **[Konsistensi Data Produk]** Memperbaiki variabel nama produk di halaman UI pengguna (`nama_barang` menjadi `nama_mobil` & `nama_fasilitas`) agar judul produk tidak kosong.
  - **[Konsistensi UI & State Persistence Filter]** Menyeragamkan warna tombol kategori aktif dengan tombol aksi utama (biru solid) demi kepatuhan desain KMIPN, dan memfaktorkan ulang (*refactor*) Javascript filter menggunakan `sessionStorage`. Hal ini memastikan pilihan pengguna tidak ter-*reset* saat menekan tombol *Back* dari halaman detail, serta mencegah *freeze* akibat tumpukan antrean animasi CSS.
  - **[AlpineJS UI Conflict Fix]** Memperbaiki *bug* hilangnya garis biru penanda tab aktif di halaman "Kabar dan Informasi Daerah". Masalah ini disebabkan oleh *inline style* statis (`border-color: transparent`) yang memblokir *binding* `:style` milik AlpineJS. Solusinya adalah memindahkan pengaturan warna ke atribut `:class` menggunakan utilitas Tailwind (`border-blue-500` vs `border-transparent`).
  - **[Hierarki Nomenklatur]** Menegaskan pembagian hierarki nama menu agar tidak tumpang tindih. Menu utamanya adalah **"Kabar dan Informasi Daerah"**, yang di dalamnya terbagi menjadi dua sub-tab: **"Berita Daerah"** dan **"Pengumuman"** (sebelumnya sempat keliru/dobel penamaan akibat *find & replace* massal).
  - **[KYC UI & Validation Enhancements]** Memoles halaman verifikasi KYC dengan berbagai penyempurnaan UI:
    1. Mengimplementasikan transisi animasi *fade-in* (`animate-section` & `IntersectionObserver`) agar pemuatan elemen halaman tidak terlihat kaku (sebelumnya halaman termuat polos).
    2. Menghapus atribut `required` pada input *file* KTP tersembunyi (*hidden input*) untuk mencegah *browser silent validation error* yang membuat form gagal submit tanpa respon.
    3. Mengganti model notifikasi validasi KTP dari *modal dialog* besar SweetAlert bawaan yang terasa intrusif menjadi sistem **Toast Notification** mengambang di kanan atas layar agar lebih elegan.
    4. Mengoreksi *bug* warna latar transparan akibat ketidakcocokan kelas utilitas fraksi opasitas Tailwind (`bg-white/95` & `bg-white/60`). Seluruh latar kotak KYC dan *modal* peringatannya (di seluruh halaman *detail* layanan) kini dipastikan menggunakan putih solid (`bg-white`).
    5. Mengganti ikon panduan animasi generik menjadi **Karakter Tanjak AI Assistant** berdesain robot orisinal menggunakan manipulasi kode SVG murni secara kompleks (*pure code, no images*). Animasi maskot ini bereaksi sesuai status pendeteksi wajah (*scanning* depan, kedip, lirik arah, dan senyum sukses).

## 4. Rencana Kerja Selanjutnya (In Progress)
- **Desentralisasi Lokasi Map Produk (Fitur Lokasi Tersimpan):**
  - **[SELESAI]** Awalnya sistem menggunakan 1 titik Map pusat (Kantor BUMDes) untuk semua produk. Pengguna meminta agar setiap produk/fasilitas bisa memiliki titik Map sendiri (Desentralisasi) jika lokasinya tersebar (misal: Gedung Olahraga di RT 01, Kolam Renang di RT 05).
  - **Solusi UX Pintar:** Untuk mencegah Admin kelelahan menginput koordinat berulang kali, kita menerapkan konsep "Gunakan Lokasi Tersimpan".
  - **Teknis Selesai:** Telah ditambahkan kolom `latitude` dan `longitude` ke tabel `barang`, `mobils`, `gas`, dan `fasilitas_umums`. Di halaman Admin Tambah/Edit Produk, terdapat *dropdown* lokasi tersimpan dan opsi "Tentukan Baru". Di sisi Pengguna (User), jika Admin tidak melampirkan titik Latitude/Longitude, maka lokasi hanya akan muncul sebagai teks mati (tidak bisa diklik), sesuai dengan privasi BUMDes.

- **Implementasi Fitur Mitra Gas Daerah (Pengecer):**
  - **[SELESAI]** Berdasarkan diskusi dengan pengembang aplikasi mobile dan penanggung jawab gas daerah, diputuskan untuk **TIDAK** membuat sistem registrasi Pengecer secara otomatis di dalam aplikasi. Alasannya:
    - Menghindari kebingungan bagi warga awam (salah menu/salah pesan).
    - Menghindari kerumitan birokrasi dan persetujuan (approval) yang panjang.
  - **Solusi UX Praktis:** Menambahkan *Banner Alert* (Pop-up Info) di halaman Profil Warga dan Halaman Pemesanan Gas. Banner ini secara jelas mengarahkan warga yang memiliki warung (pengecer) untuk menghubungi nomor *Halo Layanan BUMDes* (WhatsApp) secara langsung untuk proses pendaftaran B2B (Business-to-Business) di luar sistem digital warga awam.

- **Sistem Manajemen Kategori Dinamis (Lintas Modul):**
  - **[SELESAI]** Sistem kategori yang sebelumnya *hardcoded* (statis) kini telah dirombak menjadi dinamis untuk modul **Penyewaan Alat**, **Sewa Mobil**, **Gas**, dan **Fasilitas Umum**.
  - **Isolasi Data (Multitenancy Kategori):** Tabel `categories` menggunakan parameter `type` untuk memastikan kategori Alat tidak bercampur dengan kategori Mobil atau Gas.
  - **Solusi UX Cerdas (AJAX on-the-fly):** Admin dapat menambahkan kategori baru langsung dari form "Tambah Produk" menggunakan modal *pop-up*. Kategori disimpan via AJAX di *background*, dan langsung muncul terpilih secara otomatis tanpa me-*refresh* halaman, memberikan pengalaman *seamless* dan tidak *nge-freeze*.
  - **Konsistensi Data (Teks Mati):** Apabila kategori dihapus dari master data, produk yang sudah dibuat menggunakan kategori tersebut TIDAK akan terhapus atau kehilangan kategorinya (mempertahankan kategori lama sebagai teks mati di database produk). Hal ini mencegah kerusakan riwayat transaksi masa lalu.

- **Standardisasi Nomenklatur Skala Kabupaten & Optimasi UI:**
  - **[SELESAI]** Menyelaraskan seluruh *copywriting* di aplikasi agar sesuai dengan visi *Cerita Kami* (Profil SiladesBeng) yang berskala Kabupaten.
  - Mengubah istilah berkonteks lokal (contoh: "Gas Desa", "Dana Desa", "Aspirasi Desa") menjadi **"Gas Daerah"**, **"Dana Daerah"**, dan **"Aspirasi Daerah"** agar konsisten secara makro.
  - Memperjelas nama menu **"Kabar Daerah"** menjadi **"Kabar dan Informasi Daerah"** untuk menghindari bias persepsi (memastikan pengguna tahu bahwa halaman tersebut berisi berita, artikel liputan, sekaligus pengumuman resmi).
  - Melakukan kompresi *lossless* pada aset gambar UI baru (misal: penggantian maskot *event.png* ke *KabardanInformasiDaerah.png*, serta *Berita.png* dan *Pengumuman1.png* untuk tab informasi) via skrip PHP *backend* demi menjaga *loading speed* web tetap optimal tanpa mengorbankan kualitas HD aset visual perlombaan.
  - Memperbaiki proporsi ukuran ikon tab (*Berita* dan *Pengumuman*) di halaman informasi agar lebih besar dan sesuai secara estetika antarmuka.
  - Menghapus penggunaan *jargon* teknis seperti kata "filter" pada *Empty State* pencarian dan menggantinya dengan bahasa yang lebih membumi (mudah dipahami orang awam).
  - Menyelaraskan (standardisasi) desain kotak pencarian (Search Bar) di halaman *Kabar dan Informasi Daerah* dengan halaman Beranda (menggunakan *gradient border* dan ikon kaca pembesar) serta memperbaiki *vertical alignment* yang sebelumnya miring (senget) agar sejajar sempurna dengan tombol kategori.
  - Meningkatkan privasi usaha daerah dengan menyembunyikan nominal pasti (Rupiah) pada halaman Laporan Keuangan publik (`layanandaerah/laporan`) dan menggantinya dengan **persentase kontribusi pendapatan**. Hal ini menjaga kerahasiaan data finansial namun tetap memberikan visualisasi kinerja unit usaha yang bermanfaat bagi publik.

---
*Catatan: Dokumen ini harus dibaca setiap kali memulai sesi baru untuk memulihkan konteks pekerjaan dan memastikan pengembangan tetap selaras dengan proposal KMIPN 2026.*

- **Perbaikan Kritis Tambahan (Sesi Lanjutan):**
  - **[Fix Browser Freeze Kabar Daerah]** Merombak total sistem navigasi pada halaman Kabar Daerah dengan menghapus sistem AJAX berbasis AlpineJS yang memicu *infinite loop* (DOM conflict). Navigasi (Tab, Filter Kategori, dan Form Pencarian) kini dikembalikan ke metode bawaan Laravel yang 100% stabil dan anti-*freeze*.
  - **[UI Fix - Laporan Keuangan]** Memperbaiki inkonsistensi efek gradasi (gradient) teks pada judul 'Persentase Pendapatan Unit Pelayanan Daerah' dan 'Total Pendapatan Unit Pelayanan Daerah' di halaman laporan keuangan. Padding bawah ditambahkan agar descenders huruf (seperti 'y' dan 'p') tidak terpotong dan gradasi tampil sempurna.


  - **[Aturan Ketat Copywriting Skala Makro]** Dilarang keras melakukan *hardcode* kata 'BUMDes', 'Kantor Desa', atau terminologi tingkat desa lainnya pada UI (seperti form pemesanan, banner informasi, dan struk/laporan). Sistem harus selalu menggunakan terminologi generik seperti 'Layanan Daerah', 'Pengelola', 'Pusat Layanan', 'Gas Daerah', dll. Hal ini memastikan konsistensi *Multi-Tenant Architecture* di mana sistem ini bisa dioperasikan secara penuh oleh level Kabupaten maupun Kecamatan tanpa perlu mengubah satupun baris kode.

# Brain SiladesBeng (Catatan Memori Proyek)

Dokumen ini berfungsi sebagai pusat memori dan dokumentasi (pengganti Obsidian Vault) untuk memastikan rekam jejak pengembangan sistem tetap terjaga secara persisten.

## 1. Identitas Proyek
- **Nama Sistem:** SiladesBeng (Sistem Sinergi Layanan dan Aspirasi Desa di Kabupaten Bengkalis)
- **Kompetisi:** KMIPN VIII Tahun 2026 (Kategori E-Government)
- **Tim Pengembang:** Tim Gen Hello World (Politeknik Negeri Bengkalis)
- **Skala Implementasi:** Skala Kabupaten (Mencakup Kabupaten, 8 Kecamatan, 155 Desa, dan 47 Kelurahan di Bengkalis).
- **Aturan Ketat Lomba:** TIDAK BOLEH MENGGUNAKAN EMOJI DALAM SISTEM MAUPUN DOKUMENTASI.

## 2. Arsitektur dan Inovasi Utama
- **Multi-Tenant Architecture:** Satu sistem (codebase) dan satu database yang melayani seluruh hierarki pemerintahan dari tingkat Kabupaten hingga RT/RW dengan otonomi ruang kerja (workspace) masing-masing instansi.
- **Enam Modul Terintegrasi:**
  1. Sewa Kendaraan (ambulans, mobil pick-up, dll)
  2. Sewa Alat (tenda, kursi, sound system)
  3. Penjualan Gas LPG (Subsidi 3kg, opsi 5.5kg & 12kg)
  4. Fasilitas Umum (peminjaman lapangan, balai desa)
  5. Pengumuman dan Event (Kabar Daerah)
  6. Lapor Warga
- **Matriks Eskalasi Pelaporan (Zero-Bottleneck):** Laporan warga masuk ke RT, jika tidak tertangani akan otomatis/manual naik ke RW, lalu ke Desa, hingga Kecamatan/Kabupaten. Pimpinan daerah memiliki Hak Pantau Real-Time.
- **SiladesBeng Assistant (Kecerdasan Buatan):** Asisten virtual interaktif bermaskot robot bertanjak corak songket Melayu untuk memandu warga.
- **Keamanan Berlapis (Defense in Depth):** Autentikasi, RBAC (Role-Based Access Control), dan Enkripsi tingkat lanjut (AES-256 dan ChaCha20-Poly1305).
- **Omnichannel Payment:** Mendukung pembayaran digital (Midtrans), transfer manual, dan tunai (Cash on Delivery).

## 3. Riwayat Pekerjaan yang Telah Diselesaikan
- **Konsolidasi Modul dan UI:**
  - Menyatukan menu "Pengumuman" dan "Event" menjadi satu halaman terpadu bernama **Kabar Daerah**.
  - Merapikan tata letak (layout) kartu layanan dan navigasi, memastikan tinggi kartu seragam (simetris) dan teks tidak terpotong.
- **Penanganan Otentikasi dan Sesi (Session):**
  - Mengatasi masalah *Force Logout* (Sesi terputus secara acak) saat berpindah halaman.
  - Memecahkan kasus "Login selalu terlempar ke Guest": 
    - **Masalah:** Browser mengalami konflik dan menolak pengiriman cookie baru yang digenerate oleh fitur keamanan *Session Regeneration* milik Laravel setelah sukses melakukan POST request. Browser ngotot mengirimkan cookie sesi `laravel-session` lama yang sudah usang dan dianggap kosong oleh server.
    - **Solusi Final:** Mengubah nama cookie di `config/session.php` menjadi `siladesbeng_auth_session` untuk secara paksa me-reset memori browser tanpa harus menonaktifkan fitur keamanan *Session Regeneration*.
  - Memperbaiki *Middleware CheckRole* untuk meredirect admin yang mencoba mengakses halaman warga kembali ke Dashboard Admin tanpa memutus sesi.
  - Mengubah konfigurasi `CACHE_STORE` ke `database` dan menambahkan `DB_CACHE_CONNECTION=mysql` agar fitur *DDoS Protection* dapat bekerja secara optimal tanpa menimbulkan *crash* di lingkungan lokal (Laragon) maupun *Production* (Hosting cPanel).
- **Penyesuaian Aturan Lomba:**
  - Menghapus penggunaan emoji di seluruh antarmuka yang telah dikembangkan dan menggantinya dengan aset gambar vektor kustom yang telah disediakan.
- **Penyempurnaan UI/UX dan Stabilitas Navigasi:**
  - **[BfCache Fix]** Menambahkan `Cache-Control: no-store` di `SecurityHeaders` untuk memaksa browser melakukan *hard refresh* saat menekan tombol *Back*, guna mencegah *white screen* atau tampilan usang.
  - **[Layout Shift Fix]** Mengganti `overflow-x-clip` menjadi `overflow-hidden` pada elemen `<main>` di `beranda/index.blade.php`. Hal ini memecahkan bug di mana halaman beranda tiba-tiba bergeser ke kiri dan memunculkan ruang putih di kanan akibat elemen dekorasi (*background ovals*) yang keluar dari batas *viewport* saat memulihkan halaman dari *history*.
  - **[Konsistensi Data Produk]** Memperbaiki variabel nama produk di halaman UI pengguna (`nama_barang` menjadi `nama_mobil` & `nama_fasilitas`) agar judul produk tidak kosong.
  - **[Konsistensi UI & State Persistence Filter]** Menyeragamkan warna tombol kategori aktif dengan tombol aksi utama (biru solid) demi kepatuhan desain KMIPN, dan memfaktorkan ulang (*refactor*) Javascript filter menggunakan `sessionStorage`. Hal ini memastikan pilihan pengguna tidak ter-*reset* saat menekan tombol *Back* dari halaman detail, serta mencegah *freeze* akibat tumpukan antrean animasi CSS.
  - **[AlpineJS UI Conflict Fix]** Memperbaiki *bug* hilangnya garis biru penanda tab aktif di halaman "Kabar dan Informasi Daerah". Masalah ini disebabkan oleh *inline style* statis (`border-color: transparent`) yang memblokir *binding* `:style` milik AlpineJS. Solusinya adalah memindahkan pengaturan warna ke atribut `:class` menggunakan utilitas Tailwind (`border-blue-500` vs `border-transparent`).
  - **[Hierarki Nomenklatur]** Menegaskan pembagian hierarki nama menu agar tidak tumpang tindih. Menu utamanya adalah **"Kabar dan Informasi Daerah"**, yang di dalamnya terbagi menjadi dua sub-tab: **"Berita Daerah"** dan **"Pengumuman"** (sebelumnya sempat keliru/dobel penamaan akibat *find & replace* massal).
  - **[KYC UI & Validation Enhancements]** Memoles halaman verifikasi KYC dengan berbagai penyempurnaan UI:
    1. Mengimplementasikan transisi animasi *fade-in* (`animate-section` & `IntersectionObserver`) agar pemuatan elemen halaman tidak terlihat kaku (sebelumnya halaman termuat polos).
    2. Menghapus atribut `required` pada input *file* KTP tersembunyi (*hidden input*) untuk mencegah *browser silent validation error* yang membuat form gagal submit tanpa respon.
    3. Mengganti model notifikasi validasi KTP dari *modal dialog* besar SweetAlert bawaan yang terasa intrusif menjadi sistem **Toast Notification** mengambang di kanan atas layar agar lebih elegan.
    4. Mengoreksi *bug* warna latar transparan akibat ketidakcocokan kelas utilitas fraksi opasitas Tailwind (`bg-white/95` & `bg-white/60`). Seluruh latar kotak KYC dan *modal* peringatannya (di seluruh halaman *detail* layanan) kini dipastikan menggunakan putih solid (`bg-white`).
    5. Mengganti ikon panduan animasi generik menjadi **Karakter Tanjak AI Assistant** berdesain robot orisinal menggunakan manipulasi kode SVG murni secara kompleks (*pure code, no images*). Animasi maskot ini bereaksi sesuai status pendeteksi wajah (*scanning* depan, kedip, lirik arah, dan senyum sukses).

## 4. Rencana Kerja Selanjutnya (In Progress)
- **Desentralisasi Lokasi Map Produk (Fitur Lokasi Tersimpan):**
  - **[SELESAI]** Awalnya sistem menggunakan 1 titik Map pusat (Kantor BUMDes) untuk semua produk. Pengguna meminta agar setiap produk/fasilitas bisa memiliki titik Map sendiri (Desentralisasi) jika lokasinya tersebar (misal: Gedung Olahraga di RT 01, Kolam Renang di RT 05).
  - **Solusi UX Pintar:** Untuk mencegah Admin kelelahan menginput koordinat berulang kali, kita menerapkan konsep "Gunakan Lokasi Tersimpan".
  - **Teknis Selesai:** Telah ditambahkan kolom `latitude` dan `longitude` ke tabel `barang`, `mobils`, `gas`, dan `fasilitas_umums`. Di halaman Admin Tambah/Edit Produk, terdapat *dropdown* lokasi tersimpan dan opsi "Tentukan Baru". Di sisi Pengguna (User), jika Admin tidak melampirkan titik Latitude/Longitude, maka lokasi hanya akan muncul sebagai teks mati (tidak bisa diklik), sesuai dengan privasi BUMDes.

- **Implementasi Fitur Mitra Gas Daerah (Pengecer):**
  - **[SELESAI]** Berdasarkan diskusi dengan pengembang aplikasi mobile dan penanggung jawab gas daerah, diputuskan untuk **TIDAK** membuat sistem registrasi Pengecer secara otomatis di dalam aplikasi. Alasannya:
    - Menghindari kebingungan bagi warga awam (salah menu/salah pesan).
    - Menghindari kerumitan birokrasi dan persetujuan (approval) yang panjang.
  - **Solusi UX Praktis:** Menambahkan *Banner Alert* (Pop-up Info) di halaman Profil Warga dan Halaman Pemesanan Gas. Banner ini secara jelas mengarahkan warga yang memiliki warung (pengecer) untuk menghubungi nomor *Halo Layanan BUMDes* (WhatsApp) secara langsung untuk proses pendaftaran B2B (Business-to-Business) di luar sistem digital warga awam.

- **Sistem Manajemen Kategori Dinamis (Lintas Modul):**
  - **[SELESAI]** Sistem kategori yang sebelumnya *hardcoded* (statis) kini telah dirombak menjadi dinamis untuk modul **Penyewaan Alat**, **Sewa Mobil**, **Gas**, dan **Fasilitas Umum**.
  - **Isolasi Data (Multitenancy Kategori):** Tabel `categories` menggunakan parameter `type` untuk memastikan kategori Alat tidak bercampur dengan kategori Mobil atau Gas.
  - **Solusi UX Cerdas (AJAX on-the-fly):** Admin dapat menambahkan kategori baru langsung dari form "Tambah Produk" menggunakan modal *pop-up*. Kategori disimpan via AJAX di *background*, dan langsung muncul terpilih secara otomatis tanpa me-*refresh* halaman, memberikan pengalaman *seamless* dan tidak *nge-freeze*.
  - **Konsistensi Data (Teks Mati):** Apabila kategori dihapus dari master data, produk yang sudah dibuat menggunakan kategori tersebut TIDAK akan terhapus atau kehilangan kategorinya (mempertahankan kategori lama sebagai teks mati di database produk). Hal ini mencegah kerusakan riwayat transaksi masa lalu.

- **Standardisasi Nomenklatur Skala Kabupaten & Optimasi UI:**
  - **[SELESAI]** Menyelaraskan seluruh *copywriting* di aplikasi agar sesuai dengan visi *Cerita Kami* (Profil SiladesBeng) yang berskala Kabupaten.
  - Mengubah istilah berkonteks lokal (contoh: "Gas Desa", "Dana Desa", "Aspirasi Desa") menjadi **"Gas Daerah"**, **"Dana Daerah"**, dan **"Aspirasi Daerah"** agar konsisten secara makro.
  - Memperjelas nama menu **"Kabar Daerah"** menjadi **"Kabar dan Informasi Daerah"** untuk menghindari bias persepsi (memastikan pengguna tahu bahwa halaman tersebut berisi berita, artikel liputan, sekaligus pengumuman resmi).
  - Melakukan kompresi *lossless* pada aset gambar UI baru (misal: penggantian maskot *event.png* ke *KabardanInformasiDaerah.png*, serta *Berita.png* dan *Pengumuman1.png* untuk tab informasi) via skrip PHP *backend* demi menjaga *loading speed* web tetap optimal tanpa mengorbankan kualitas HD aset visual perlombaan.
  - Memperbaiki proporsi ukuran ikon tab (*Berita* dan *Pengumuman*) di halaman informasi agar lebih besar dan sesuai secara estetika antarmuka.
  - Menghapus penggunaan *jargon* teknis seperti kata "filter" pada *Empty State* pencarian dan menggantinya dengan bahasa yang lebih membumi (mudah dipahami orang awam).
  - Menyelaraskan (standardisasi) desain kotak pencarian (Search Bar) di halaman *Kabar dan Informasi Daerah* dengan halaman Beranda (menggunakan *gradient border* dan ikon kaca pembesar) serta memperbaiki *vertical alignment* yang sebelumnya miring (senget) agar sejajar sempurna dengan tombol kategori.
  - Meningkatkan privasi usaha daerah dengan menyembunyikan nominal pasti (Rupiah) pada halaman Laporan Keuangan publik (`layanandaerah/laporan`) dan menggantinya dengan **persentase kontribusi pendapatan**. Hal ini menjaga kerahasiaan data finansial namun tetap memberikan visualisasi kinerja unit usaha yang bermanfaat bagi publik.

---
*Catatan: Dokumen ini harus dibaca setiap kali memulai sesi baru untuk memulihkan konteks pekerjaan dan memastikan pengembangan tetap selaras dengan proposal KMIPN 2026.*

- **Perbaikan Kritis Tambahan (Sesi Lanjutan):**
  - **[Fix Browser Freeze Kabar Daerah]** Merombak total sistem navigasi pada halaman Kabar Daerah dengan menghapus sistem AJAX berbasis AlpineJS yang memicu *infinite loop* (DOM conflict). Navigasi (Tab, Filter Kategori, dan Form Pencarian) kini dikembalikan ke metode bawaan Laravel yang 100% stabil dan anti-*freeze*.
  - **[UI Fix - Laporan Keuangan]** Memperbaiki inkonsistensi efek gradasi (gradient) teks pada judul 'Persentase Pendapatan Unit Pelayanan Daerah' dan 'Total Pendapatan Unit Pelayanan Daerah' di halaman laporan keuangan. Padding bawah ditambahkan agar descenders huruf (seperti 'y' dan 'p') tidak terpotong dan gradasi tampil sempurna.


  - **[Aturan Ketat Copywriting Skala Makro]** Dilarang keras melakukan *hardcode* kata 'BUMDes', 'Kantor Desa', atau terminologi tingkat desa lainnya pada UI (seperti form pemesanan, banner informasi, dan struk/laporan). Sistem harus selalu menggunakan terminologi generik seperti 'Layanan Daerah', 'Pengelola', 'Pusat Layanan', 'Gas Daerah', dll. Hal ini memastikan konsistensi *Multi-Tenant Architecture* di mana sistem ini bisa dioperasikan secara penuh oleh level Kabupaten maupun Kecamatan tanpa perlu mengubah satupun baris kode.

  - **[UI Fix - Formulir Pengaduan] (SELESAI):**
    - Mengganti teks judul formulir sederhana dengan format **Kop Surat Resmi Pemerintahan** (lengkap dengan Logo Kabupaten Bengkalis dan Logo SiladesBeng, serta garis pembatas ganda) agar tampil eksklusif untuk presentasi KMIPN.
    - Menyeragamkan warna teks label formulir yang sebelumnya berwarna *navy* (#1e3a5f) menjadi 	ext-gray-800 agar konsisten dengan halaman formulir kemitraan.
    - Meningkatkan keamanan privasi NIK pada cetak PDF Bukti Laporan dengan mengubah penyensoran menjadi 12 digit (hanya menyisakan 2 digit awal dan 2 digit akhir).
    - Memperbaiki ejaan *subtitle* di Statistik Laporan Warga dari 'Sekabupaten' menjadi 'se-Kabupaten' sesuai standar PUEBI.

  - **[Peta & Smart Camera Pengaduan] (SELESAI):**
    - **Eksekusi:** Telah ditambahkan kolom `latitude` dan `longitude` ke tabel `laporans`. Form pelaporan kini otomatis meminta akses *Geolocation* (GPS) ketika pengguna mengklik "Ambil Foto (Kamera)". Titik koordinat akan dikunci dan dikirim bersama laporan.
    - **Tampilan Peta Interaktif:** Pada halaman Detail Laporan (`show.blade.php`), telah ditambahkan integrasi Google Maps (mode satelit) yang menampilkan *marker* presisi pada koordinat yang terekam, tidak hanya teks alamat statis. Fitur ini aman dan tidak bentrok dengan fungsionalitas peta lainnya.
    - **[UX Fix - Modern Notifications] (SELESAI):** Menghapus penggunaan window.alert() bawaan browser (kotak dialog hitam jadul yang tidak profesional) di halaman formulir pengaduan. Menggantinya dengan **SweetAlert2** (melalui teknik *function overriding* pada Javascript), sehingga seluruh notifikasi peringatan/error kini tampil sebagai Toast Notification (di pojok kanan atas, tidak menghalangi layar), elegan, dan estetik sesuai standar UI KMIPN.
    - **[UX Decision - Header Lokasi Kejadian]:** Sempat diubah agar sama dengan desain header "Informasi Wilayah" (di Kemitraan), namun akhirnya dikembalikan (*revert*) ke desain label teks sederhana seperti semula atas permintaan user karena dirasa lebih mengalir dan bersih.
  - **[Arsitektur Sistem - Pop-up Profil Wilayah (CRITICAL)] (SELESAI):** Ditemukan celah arsitektur terkait Pop-up manual "Lengkapi Profil Wilayah" (Pilih RT/RW) di form Laporan. Pop-up ini adalah sistem *legacy* sebelum adanya fitur KYC. Karena sekarang domisili warga (RT/RW) dikunci mutlak berdasarkan data KTP (KYC), pop-up manual ini statusnya **usang dan berbahaya** (bisa dimanipulasi).
      - **Eksekusi:** Pop-up `rtrw-modal` (HTML + JavaScript) telah **dihapus total** dari `create.blade.php`.
      - **Pengganti:** Bagian "Tujuan Pelaporan" di formulir kini dilengkapi **Searchable Dropdown** dinamis. Saat warga memilih radio "Pengurus RT" atau "Pengurus RW", muncul dropdown dengan fitur pencarian yang hanya menampilkan RT/RW yang sudah punya admin terdaftar di sistem. Setiap opsi RT dilengkapi badge nama RW-nya agar tidak rancu (karena nomor RT bisa sama lintas RW). Empty state dropdown: *"Tidak ditemukan. Pengurus RT/RW ini belum bergabung."*
      - **Backend (`LaporanController`):**
        - `create()` kini mengambil koleksi `$activeRTs` dan `$activeRWs` (Region yang punya admin) dan mengirimnya ke view via `@json()`.
        - `store()` menerima field baru `target_region_id` dari hidden input. Nilai ini menentukan ke region mana laporan dikirim (bukan lagi hard-coded dari `user->region_id`).
        - Smart Routing Notifikasi diperbarui: pencarian admin menggunakan `targetRegionId` pilihan user, dengan mekanisme eskalasi otomatis (RT -> RW -> Desa) tetap berjalan jika admin di level tujuan tidak ditemukan.
      - **Prinsip Kunci:** Penentuan domisili warga = KYC KTP (mutlak). Penentuan tujuan laporan = pilihan warga via form (fleksibel, tapi hanya bisa memilih yang aktif).


    - **[UX & Security Fix - Identitas Pelapor Dinamis (KYC)] (SELESAI):**
      - Mengunci kolom 'Nama Lengkap' secara mutlak (readonly) bagi warga yang status KYC KTP-nya telah pproved. Hal ini menjamin validitas hukum pelapor.
      - Mengizinkan warga yang belum diverifikasi KTP untuk mengedit nama pada form, TAPI sistem akan secara eksplisit mengekspos 'Nama Akun' asli mereka di bawah 'Nama Pelapor' pada halaman Bukti Laporan. Langkah ini menutup celah pengguna anonim (contoh: akun 'Ucup Gaming' melapor atas nama 'Bapak RT') dengan menelanjangi identitas asli akun tanpa mematikan fleksibilitas form.
      - Mengganti representasi visual status KTP dengan icon FontAwesome dan SVG murni (menggantikan karakter emoji) agar tetap profesional dan mematuhi aturan kompetisi KMIPN.
    - **[Bug Fix - RW Searchable Dropdown] (SELESAI):** Memperbaiki kendala dropdown pencarian RW yang gagal memuat (blank) akibat data $activeRWs dirender sebagai JSON Object Javascript oleh Laravel (karena ID Region tidak berurutan). Solusinya, dilakukan re-indexing array menggunakan ->values()->all() pada LaporanController sehingga Frontend membaca data murni sebagai Array.
    - **[UI Fix - Kop Surat Form Pelaporan] (SELESAI):** Mengoptimalkan proporsi ukuran Logo Kabupaten dan Logo Sistem pada Kop Surat formulir agar lebih simetris dan mengurangi ruang kosong (padding) berlebih di bagian atas card.
    - **[Localization Fix] (SELESAI):** Menambahkan atribut 	ranslate=
o` pada judul 'Form Pelaporan' untuk mencegah browser secara sepihak menerjemahkannya secara absurd (menjadi 'Buah Pelaporan').

    - **[Arsitektur Sistem - Layanan Ambulans Darurat] (SELESAI):** Memperbaiki inkonsistensi akses menu Layanan Ambulans Darurat. Sebelumnya menu ini bersifat publik/selalu terbuka untuk pengguna yang login. Kini rute /layanan-ambulans telah diproteksi menggunakan middleware `region.service:layanan-ambulans`, sehingga menu ini akan otomatis hilang dari HP warga jika Admin Desa menonaktifkan fitur tersebut (misal karena desa tidak memiliki armada ambulans), konsisten dengan logika unit layanan daerah lainnya.

    - **[Fitur Baru - Pasar Daerah (Marketplace)] (IN PROGRESS):**
      - **Latar Belakang:** Berdasarkan survei lapangan ulang oleh anggota tim, ditemukan bahwa beberapa desa memiliki usaha berbentuk marketplace (jual perabotan, pipa, pupuk, genteng, semen, hingga hasil olahan warga seperti keripik dan ubi dari lahan pertanian pemerintah daerah). Fitur ini ditambahkan sebagai penyempurnaan sistem (menambah, bukan mengurangi dari proposal).
      - **Nama Resmi:** Pasar Daerah
      - **Justifikasi KMIPN:** Penambahan fitur ke-7 berdasarkan temuan lapangan nyata. Bukan pengurangan fitur proposal, melainkan penyempurnaan sistem agar lebih relevan dengan kondisi daerah.
      - **Modul Lengkap Sistem (7 Layanan):**
        1. Penyewaan Alat Berat
        2. Penjualan Gas LPG
        3. Pelaporan Warga
        4. Peminjaman Fasilitas Umum
        5. Kabar dan Informasi Daerah
        6. Penyewaan Kendaraan (Mobil)
        7. **Pasar Daerah (BARU)**
      - **Aset Ikon:** Ikon kustom tas belanja kuning-oranye dengan logo SilaDesBeng (SB) di badan tas telah ditempatkan di `public/Admin/img/pasardaerah/PasarDaerah.png`. Resolusi asli: 4500x4500px. Kompresi lossless PNG Level 9 berhasil dilakukan: 2.13 MB -> 1.4 MB (hemat 33.9%), kualitas HD terjaga.
      - **Arsitektur Final (Disetujui):**
        - *Cakupan:* Lintas desa se-Kabupaten Bengkalis. Warga Desa A bisa membeli produk Desa Z. Filter pencarian berdasarkan Kecamatan, Desa, dan Kategori.
        - *Kategori Fixed (5):* Hasil Tani & Bumi, Pangan & Olahan, Material & Bangunan, Kerajinan & Kesenian, Lainnya. Dikunci oleh sistem, Admin Desa tidak bisa bikin sendiri.
        - *Metode Pembayaran (3):* Tunai/COD, Transfer Manual (upload bukti), Midtrans Otomatis (VA/QRIS/GoPay). Replikasi dari modul Gas.
        - *Metode Pengiriman (2):* Ambil Sendiri (gratis) dan Diantar (ongkir otomatis).
        - *Ongkir Otomatis:* Algoritma Haversine di backend PHP (gratis, tanpa API berbayar). Admin set tarif per Km dan koordinat toko. Warga kirim GPS dari HP. Sistem hitung jarak dan total ongkir secara otomatis.
        - *Siklus Status Pesanan:* Pending -> Confirmed -> Processing (Diproses/Dikemas) -> Ready/In Delivery (Siap Diambil/Sedang Dikirim) -> Completed (Selesai) -> Cancelled/Rejected (Batal/Ditolak).
        - *Database (4 tabel baru):* `pasar_produks`, `pasar_carts` (keranjang), `pasar_orders`, `pasar_order_items` (1 order bisa multi-produk).
        - *Fitur Keranjang Belanja:* Fitur baru yang belum ada di modul manapun. Warga bisa menambah beberapa produk sekaligus sebelum checkout.
      - **Rencana Fitur Inti:**
        - *Sisi Admin:* **(SELESAI)** CRUD Produk, Kategori Fixed (5 pilihan), Kelola Pesanan Masuk, Pengaturan On/Off, SOP Toko, Tarif Ongkir per Km. Controller (`UnitPasarDaerahController`) dan seluruh View Admin terkait telah diimplementasikan sepenuhnya dengan integrasi titik peta LeafletJS untuk ongkir.
        - *Sisi User:* **(SELESAI)** Katalog Produk (lintas desa), Detail Produk, Keranjang Belanja, Checkout (Ambil/Antar + Tunai/Transfer/Midtrans), Riwayat Pesanan di tab Aktivitas, Bukti Transaksi + QR Code. Tampilan disempurnakan menggunakan SweetAlert2 untuk notifikasi dan penyesuaian copywriting yang mematuhi standar desain "Layanan Daerah".
        - *Fitur yang TIDAK dibuat (terlalu kompleks):* Chat Penjual-Pembeli, Rating/Review, Wishlist, Retur, Multi-Seller C2C.
      
      - **Penyempurnaan Akses Global & UI/UX (SELESAI):**
        - **[Routing Global (Bypass Filter Wilayah)] (SELESAI):** Layanan Pasar Daerah dan Kabar & Informasi Daerah adalah layanan yang bersifat lintas desa (skala Kabupaten). Sebelumnya pengguna/tamu tertahan oleh *middleware* yang memaksa mereka memilih desa terlebih dahulu. Kini *middleware* `region.service:pasar-daerah` telah dihapus dari rute katalog, dan *redirect* di Beranda dimodifikasi sehingga akses ke 2 layanan ini 100% langsung masuk ke katalog tanpa terhalang gerbang "Pilih Wilayah". Layanan unit spesifik lainnya tetap dibatasi oleh filter wilayah.
        - **[Penyelarasan UI/UX Pasar Daerah] (SELESAI):** Perombakan total *interface* Pasar Daerah (Katalog, Checkout, Payment) menggunakan templat global `@extends('layouts.user')`. Sinkronisasi visual 100% dengan Kabar Daerah, mencakup implementasi latar belakang partikel animasi `<canvas>` dinamis, warna *font* tipografi biru bergradasi (`bg-gradient-to-r from-[#115789] to-[#60a5fa]`), serta layout grid produk bergaya *e-commerce* padat (`grid-cols-2` hingga `grid-cols-4`). Fitur interaktif *Keranjang Belanja* (Cart Sidebar) juga dipoles dengan animasi *slide-over* modern dan notifikasi *toast*.


### 5. Konsep Arsitektur Finansial & Hak Akses (Untuk KMIPN)

### 5.1 Desentralisasi Finansial (Rekening BUMDes)
- Sistem pembayaran dirancang **tidak menahan uang** di rekening pusat aplikasi (Kabupaten). Warga langsung transfer ke rekening BUMDes desa masing-masing atau bayar tunai (Walk-In). Hal ini menghindari masalah birokrasi penahanan APBDes.
- **Sistem Pembayaran Hibrida:** Mendukung transaksi *online* (via Midtrans) dan transaksi manual/langsung (Admin BUMDes memasukkan pesanan warga yang tidak memiliki HP/Gaptek via POS sederhana). Laporan keuangan membedakan tag *sistem* dan *manual* namun tetap menjumlahkan subtotalnya demi transparansi auditor.
- **Warga Tanpa HP (Walk-In):** Warga datang langsung ke kantor BUMDes, pesan secara lisan, bayar tunai. Admin menggunakan fitur "Tambah Transaksi Manual" (sudah ada di `ReportController@storeManualTransaction`) untuk mencatatnya ke sistem.
- **Hierarki Data & Privasi APBDes:** Pimpinan (Camat/Bupati) hanya disajikan data dalam bentuk Persentase Pertumbuhan (%), bukan nominal Rupiah mentah. Ini melindungi APBDes agar tidak dipotong secara sepihak dan memposisikan pimpinan sebagai 'Pengawas/Pembina' (Politik), bukan pengurus bisnis.

### 5.2 Pemisahan Sistem Admin: Super Admin + Staf Unit (Dynamic RBAC)

**Latar Belakang Masalah:**
Saat ini setiap desa/kecamatan/kabupaten hanya punya 1 akun admin yang mengakses semua fitur sekaligus. Di lapangan, yang mengelola setiap unit layanan adalah orang berbeda-beda. Dashboard campur aduk menyebabkan risiko *human error* tinggi, terlalu banyak menu, dan tidak ada jejak audit (siapa melakukan apa).

**Solusi: Dua Tier Akun Admin**

**A. Super Admin (Tier 1 - Pimpinan)**
- Berlaku di **semua level** hierarki: Desa, Kecamatan, DAN Kabupaten.
- Per desa boleh ada **2 akun Super Admin** dengan email berbeda (misal `pemdes@desaX.id` dan `bumdes@desaX.id`). Role/hak aksesnya identik, hanya beda email login agar pengguna tidak bingung.
- Jika Kecamatan/Kabupaten juga mengaktifkan layanan unit (misal buka pangkalan Gas tingkat kecamatan), maka Super Admin di level tersebut juga bisa membuat staf.
- **Wewenang Super Admin:**
  - Membuat, mengedit, dan menghapus akun Staf
  - Mengatur hak akses Staf (centang unit mana saja yang boleh dikelola via *checkbox* / Dynamic Permissions)
  - Mengaktifkan/menonaktifkan unit layanan
  - Melihat **seluruh grafik dan subtotal keuangan** dari semua unit
  - **Memantau (read-only)** detail operasional semua unit: stok produk, pesanan yang sedang diproses, status sewa, bukti transaksi, dll. Bisa melihat secara detail, tapi TIDAK bisa mengubah/mengonfirmasi (itu wewenang Staf).
  - Mengelola fitur administratif/pemerintahan: Kelola Pengguna (Warga), Mutasi Akun, Kelola Wilayah, Profil BUMDes/Pemdes, Pengaturan Sistem
  - Untuk level Kecamatan/Kabupaten: memverifikasi surat tugas dan mengonfirmasi pendaftaran desa baru

**B. Staf Unit (Tier 2 - Operator Lapangan)**
- Akun dibuat oleh Super Admin di level manapun (Desa, Kecamatan, atau Kabupaten).
- Setiap staf hanya melihat menu sesuai unit yang dicentang oleh Super Admin.
- **Wewenang Staf:**
  - Mengelola operasional harian unitnya (CRUD produk, konfirmasi pesanan, atur stok, dll)
  - Melihat dan memverifikasi **Bukti Transaksi** yang terkait dengan unitnya saja
  - Melihat dan memproses **Permintaan & Pengajuan (Pesanan Masuk)** yang terkait dengan unitnya saja
  - Melihat grafik keuangan **unitnya sendiri** secara detail
  - Melihat grafik **unit lain** dalam mode baca saja (read-only, untuk motivasi/kompetisi sehat internal)
- **Yang TIDAK bisa dilakukan Staf:**
  - Membuat/menghapus akun pengguna atau staf lain
  - Mengaktifkan/menonaktifkan unit layanan
  - Mengakses Kelola Wilayah, Mutasi Akun, Pengaturan Sistem, atau Profil BUMDes

### 5.3 Pembagian 7 Unit Layanan ke Staf

| No | Unit Layanan | Staf Mengurus |
|----|-------------|---------------|
| 1 | Gas LPG | Produk gas, harga, stok, konfirmasi pesanan, bukti transaksi gas |
| 2 | Sewa Alat | Katalog alat, harga sewa, ketersediaan, pesanan sewa alat |
| 3 | Sewa Kendaraan | Katalog mobil, harga sewa, ketersediaan, pesanan sewa mobil |
| 4 | Fasilitas Umum | Daftar fasilitas, jadwal peminjaman, konfirmasi booking |
| 5 | Pasar Daerah | Katalog produk UMKM, stok, pesanan, pengiriman |
| 6 | Kabar & Informasi Daerah | Berita, pengumuman, event, banner slide |
| 7 | Pelaporan Warga | Menerima & merespons laporan/aduan warga |

- Unit 6 dan 7 secara konseptual cocok dipegang oleh **Staf Humas** (operator/tangan kanan pemerintah dan BUMDes).
- Satu staf bisa dicentang untuk mengelola **banyak unit sekaligus** (fleksibel, tergantung jumlah SDM desa).
- Hak akses bisa diubah kapan saja oleh Super Admin tanpa perlu mengubah kode program.

### 5.4 Keputusan Desain Penting
- **Konsep ini adalah UPGRADE, bukan downgrade.** Semua 7 fitur tetap ada dan berfungsi. Kita hanya menambahkan lapisan keamanan dan efisiensi di atasnya (Segregation of Duties).
- **Backward Compatible:** Super Admin tetap bisa melakukan semua hal yang sebelumnya bisa dilakukan oleh Admin Desa lama. Jika belum ada staf yang dibuat, sistem tetap berjalan normal.
- **Skalabilitas Nasional:** Sistem siap dipakai oleh desa kecil (1-2 staf, dicentang banyak unit) maupun desa besar (7 staf, masing-masing 1 unit) tanpa perlu ubah kode.
- **Verifikasi Identitas (KYC KTP/KK):** Tetap menjadi wewenang **Super Admin saja**. Ini urusan tata kelola kependudukan, bukan operasional unit layanan. Staf tidak perlu dan tidak boleh mengakses data KTP/KK warga.
- **Status Implementasi:** Menunggu persetujuan final dari seluruh anggota tim sebelum eksekusi koding.

### 5.5 Pemetaan Sidebar Admin (Super Admin vs Staf)

Berdasarkan struktur sidebar admin yang ada saat ini, berikut pemetaan lengkap hak akses per sub-menu:

**Dashboard**
- Super Admin: Grafik keseluruhan (semua unit) + subtotal gabungan
- Staf: Grafik unitnya sendiri (detail) + grafik unit lain (read-only, untuk motivasi)

**Unit Layanan** (Penyewaan Alat, Penjualan Gas, Penyewaan Mobil, Fasilitas Umum, Kabar dan Informasi Daerah, Pasar Daerah)
- Super Admin: Bisa masuk ke SEMUA unit, tapi dalam **mode monitoring** (lihat detail produk, stok, pesanan - tanpa tombol Tambah/Edit/Hapus/Konfirmasi)
- Staf: Hanya melihat unit yang dicentang, dalam **mode operasional** penuh (bisa CRUD, konfirmasi, dll)

**Manajemen**
- Pengguna: Super Admin saja
- Verifikasi Identitas (KYC): Super Admin saja
- Mutasi Penduduk: Super Admin saja
- Kategori Produk: Staf bisa akses, tapi hanya melihat/mengelola kategori untuk unitnya saja
- Kelola Wilayah: Super Admin saja
- Kelola Staf (MENU BARU): Super Admin saja

**Permintaan & Aktivitas**
- Permintaan & Pengajuan: Super Admin melihat semua (read-only). Staf melihat & memproses hanya pesanan unitnya.
- Bukti Transaksi: Super Admin melihat semua (read-only). Staf melihat & memverifikasi hanya bukti unitnya.
- Pelaporan Warga: Super Admin melihat semua (read-only). Hanya muncul di sidebar Staf jika dicentang "Pelaporan Warga".

**Data & Laporan**
- Bukti Pelaporan Warga: Super Admin melihat semua. Staf hanya jika dicentang "Pelaporan Warga".
- Laporan Transaksi: Super Admin melihat semua unit. Staf hanya melihat transaksi unitnya.
- Laporan Pendapatan: Super Admin melihat semua unit. Staf hanya melihat pendapatan unitnya.
- Laporan Wilayah: Super Admin saja.

**Pengaturan**
- Layanan Wilayah: Super Admin saja.
- Pembayaran Wilayah: Super Admin saja.
- Seluruh menu Pengaturan **tidak muncul** di sidebar Staf.

**Profil & Info**
- SiladesBeng (Profil Sistem): Super Admin saja.
- Pemerintah Desa (Profil Pemdes): Super Admin saja.
- Seluruh menu Profil & Info **tidak muncul** di sidebar Staf.

### 5.6 Halaman Monitoring Super Admin (Tampilan Khusus Per Unit)

Super Admin **TIDAK** melihat halaman operasional yang sama dengan Staf (yang hanya tombolnya dihilangkan). Super Admin mendapatkan **halaman monitoring khusus** yang dirancang untuk mengawasi, bukan mengoperasikan.

**Struktur Halaman Monitoring Per Unit:**
1. **Bagian Atas - Kartu Ringkasan:** Total Produk, Total Stok, Pesanan Sedang Diproses, Belum Dibayar, Selesai Bulan Ini, Pendapatan Bulan Ini.
2. **Bagian Tengah - Grafik:** Grafik penjualan/aktivitas bulanan unit tersebut.
3. **Bagian Bawah - Galeri Produk (Read-Only):** Menampilkan kartu produk lengkap dengan gambar, nama, deskripsi, harga, stok, dan kategori. Tanpa tombol Tambah/Edit/Hapus di manapun.
4. **Tabel Pesanan Terbaru:** Daftar pesanan terbaru beserta status (Diproses/Belum Bayar/Selesai), nama pemesan, dan tanggal.

**Contoh - Monitoring Penjualan Gas:**
- Kartu: [Total Produk: 5] [Stok: 230] [Diproses: 3] [Belum Bayar: 2] [Selesai: 45]
- Grafik penjualan Gas bulan ini
- Galeri: Kartu Gas 3kg (gambar, Rp20.000, stok 180), Kartu Gas 12kg (gambar, Rp152.000, stok 50), dst.
- Tabel: Pak Ahmad - Gas 3kg - Diproses - 7 Agu 2026, dst.

**Contoh - Monitoring Sewa Alat:**
- Kartu: [Total Alat: 12] [Sedang Disewa: 3] [Tersedia: 9]
- Tabel: Traktor A - Disewa Pak Ahmad - Kembali Besok, Mesin Potong - Dalam Perbaikan, dst.

**Tujuan:** Super Admin bisa melakukan inspeksi visual (cek foto produk, baca deskripsi, pantau stok real-time), lalu menegur atau menginstruksikan Staf jika ada yang perlu diperbaiki. Semua koreksi dilakukan melalui Staf, bukan oleh Super Admin langsung.

### 5.7 Perluasan Hak Akses Kabar & Informasi Daerah (Admin RT/RW Bisa Bikin Berita)

**Latar Belakang:**
Saat ini, pembuatan berita/pengumuman di modul Kabar & Informasi Daerah hanya bisa dilakukan oleh Admin Desa ke atas (Super Admin). Padahal di lapangan, acara-acara seperti gotong royong, pesta rakyat, kerja bakti, dan kegiatan sosial terjadi di tingkat RT/RW. Admin RT/RW yang hadir langsung di acara itulah yang punya foto dan detail kegiatan.

**Keputusan Desain:**
- Admin RT dan Admin RW **diizinkan membuat berita** di modul Kabar & Informasi Daerah.
- Berita yang dibuat oleh RT/RW **langsung terbit** tanpa perlu persetujuan Super Admin (menghindari bottleneck birokrasi).
- Setiap berita diberi **label asal** yang jelas (contoh: "Diterbitkan oleh: RT 03 / RW 02 - Desa Bantan") agar pembaca mengetahui sumbernya.
- Super Admin dan Staf Humas tetap bisa mengedit atau menghapus berita dari RT/RW jika diperlukan (fungsi moderasi).

**Siapa Saja yang Bisa Membuat Berita (Ringkasan):**
1. Super Admin (Desa/Kecamatan/Kabupaten)
2. Staf Humas (yang dicentang unit "Kabar & Informasi Daerah")
3. Admin RT (untuk kegiatan di lingkup RT-nya)
4. Admin RW (untuk kegiatan di lingkup RW-nya)

**Nilai Tambah untuk KMIPN:**
- Konsep *Citizen Journalism* tingkat desa: berita tidak hanya top-down (perintah dari atas), tapi juga bottom-up (partisipasi dari bawah).
- Feed Kabar Daerah menjadi lebih hidup dan aktif karena kontributor lebih banyak.
- Dokumentasi kegiatan masyarakat menjadi lebih lengkap dan autentik.
- **Status Implementasi:** **SELESAI / TELAH DIIMPLEMENTASIKAN**. Admin RT dan RW sekarang sudah dapat secara independen mengunggah berita kegiatan di lingkungan masing-masing, memperkaya keragaman dan aktualitas portal "Kabar dan Informasi Daerah".

**Alasan Mengapa Super Admin & Staf Humas Punya Hak Moderasi (Edit/Hapus Berita RT/RW):**
1. **Kesalahan Penulisan:** Admin RT/RW bukan jurnalis profesional. Bisa salah tulis nama pejabat, salah tanggal, atau typo memalukan. Humas bisa langsung koreksi.
2. **Konten Tidak Pantas:** Jika ada berita dengan foto tidak layak atau kalimat menyinggung SARA, harus bisa dihapus segera.
3. **Duplikasi Berita:** Dua admin RT di RW yang sama bisa menulis berita tentang acara yang sama. Humas bisa menghapus duplikat.
4. **Berita Kadaluarsa:** Pengumuman acara yang sudah lewat berbulan-bulan masih tayang di feed perlu dibersihkan.
5. **Konsistensi Nomenklatur KMIPN:** Admin RT tidak tahu aturan ketat (misal larangan tulis "BUMDes", harus "Layanan Daerah"). Humas bisa mengedit agar sesuai standar.
- **Prinsip:** Yang bikin berita boleh banyak orang, tapi yang menjaga kualitas dan kebersihan konten tetap harus ada penanggung jawabnya (Humas).

### 5.8 Keputusan AI Assistant SiladesBeng (Fokus Tanya Jawab Sistem)

**Keputusan:** AI Assistant SiladesBeng tetap **hanya melayani tanya jawab tentang sistem** (panduan penggunaan fitur, cara pesan, cara lapor, status pesanan, dll). **TIDAK** diperluas menjadi *content generator* (pembuat judul berita, deskripsi produk, dll).

**Alasan:**
1. **Efisiensi Token API:** Content generation membutuhkan prompt panjang dan respons panjang (3-5x token dibanding tanya jawab). Dengan 155 desa aktif, token Gemini API akan cepat habis jika dipakai untuk generate konten.
2. **Potensi Penyalahgunaan:** Begitu pengguna tahu AI bisa bikin teks, mereka akan minta macam-macam di luar fungsi sistem (pidato, surat, puisi, dll). Ini bukan fungsi SiladesBeng.
3. **Bukan Nilai Jual KMIPN:** Juri menilai inovasi E-Government, bukan kemampuan AI menulis artikel. Yang unik dari asisten SiladesBeng adalah pemahamannya terhadap konteks sistem pemerintahan desa.
4. **Autentisitas Konten:** Berita yang ditulis sendiri oleh Admin RT/RW dengan bahasa sederhana jauh lebih asli dan dipercaya warga daripada teks yang di-generate AI. Sesuai semangat *Citizen Journalism*.

**Fungsi AI Assistant yang Dipertahankan:**
- Panduan penggunaan fitur ("Cara pesan gas gimana?")
- Navigasi sistem ("Saya mau lapor jalan rusak, lewat mana?")
- Informasi status ("Status pesanan saya gimana?")
- Bantuan pendaftaran ("Cara daftar KTP di sistem ini?")

## 6. KMIPN VIII 2026 - Babak Final (LOLOS!)

### 6.1 Status Kelulusan
- **Tim Gen Hello World - Politeknik Negeri Bengkalis** dinyatakan **LOLOS FINAL** kategori **E-Government** (nomor urut 6 dari 21 finalis se-Indonesia).
- Pengumuman resmi: 8 Agustus 2026, berdasarkan Berita Acara Pleno Dewan Juri tanggal 7 Agustus 2026.
- Pelaksanaan lomba final: **DARING (Online)** via Zoom. Tidak perlu ke Makassar.

### 6.2 Timeline Kritis

| Tanggal | Agenda | Keterangan |
|---------|--------|------------|
| **12 Agustus 2026** | Technical Meeting Tahap 1 | Zoom, 09:00 WIB. Penjelasan Panduan Final. WAJIB HADIR. |
| **15 Agustus 2026** | Batas Konfirmasi Kesediaan | Google Form oleh Ketua Tim + Upload Scan Surat Pernyataan Kesiapan (PDF/gambar, maks 10MB). Jika tidak diisi = dianggap mundur. |
| **31 Agustus 2026** | Deadline Upload Dokumen Karya | Upload di website https://kmipn.poliupg.ac.id/ oleh Ketua Tim. |
| **7 September 2026** | Technical Meeting Tahap 2 | Teknis persiapan lomba per kategori. |
| **9 September 2026** | Pembukaan KMIPN | Check-in & Pembukaan (di Makassar untuk Luring). |
| **10 September 2026** | Lomba Hari 1 | Pelaksanaan Lomba Luring dan Daring 1. |
| **11 September 2026** | Lomba Hari 2 & Penutupan | Pelaksanaan Lomba Daring 2 dan Penutupan. |
| **12 September 2026** | Social & Communities | Branding dan networking. |

### 6.3 Dokumen Karya yang Wajib Di-Upload (Deadline 31 Agustus)

| No | Dokumen | Format | Keterangan |
|----|---------|--------|------------|
| 1 | **Presentasi Karya** | PPT / PPTX / PDF | Slide presentasi lengkap (seperti sidang, 15-20 slide) |
| 2 | **Poster Karya** | JPG / JPEG / PNG | 1 halaman infografis/ringkasan visual proyek (ukuran A1/A0, desain menarik). Berisi: nama tim, masalah, solusi, fitur utama, screenshot, arsitektur, teknologi, dampak. |
| 3 | **Video YouTube** | Link YouTube | Video presentasi karya selama **10 menit** (presentasi + demo aplikasi) |

- Semua di-upload di website KMIPN, BUKAN diantar fisik.
- Detail teknis (format poster, durasi presentasi live, dll) akan diperjelas di Technical Meeting 12 Agustus.

### 6.4 Data Formulir Konfirmasi Kesediaan (Google Form)
- Kategori: E-Government
- Nama Tim: Gen Hello World
- Asal Kampus: Politeknik Negeri Bengkalis
- Perlu diisi: Data Ketua + Anggota 1 + Anggota 2 (Nama, No. WA, Ukuran Baju) + Data Dosen Pembimbing (Nama, NIP/NIDN/NIDK, No. WA)
- Upload: Scan Surat Pernyataan Kesiapan (template dari panitia)

## 7. Strategi Presentasi: Proposal vs Produk Final

### 7.1 Perbedaan Proposal dan Produk Final
Beberapa fitur di produk final **tidak ada di proposal awal**, dan ini BUKAN masalah. Ini adalah **upgrade berbasis temuan lapangan**, bukan penyimpangan.

| Aspek | Proposal Awal | Produk Final | Alasan Pengembangan |
|-------|--------------|-------------|-------------------|
| Jumlah Modul | 6 Unit Layanan | **7 Unit Layanan** (+Pasar Daerah) | Survei lapangan: desa memiliki usaha marketplace (pupuk, keripik, genteng, dll) |
| Struktur Admin | 1 Admin Tunggal per level | **Super Admin + Staf Unit (Dynamic RBAC)** | Menyesuaikan struktur organisasi BUMDes nyata |
| Kontributor Berita | Admin Desa ke atas saja | **Admin RT/RW juga bisa bikin berita** | Mendukung Citizen Journalism tingkat desa |

### 7.2 Cara Menyampaikan ke Juri
- Tambahkan **1-2 slide** berjudul "Pengembangan Berdasarkan Temuan Lapangan" di PPT.
- **Kalimat kunci:** "Pada proposal awal kami merancang 6 modul layanan. Namun setelah survei lapangan ulang, kami menemukan kebutuhan baru dan menambahkan modul ke-7 (Pasar Daerah) sebagai penyempurnaan sistem yang lebih relevan dengan kondisi nyata di Kabupaten Bengkalis."
- **Prinsip:** Semua fitur di proposal TETAP ADA (tidak dikurangi). Fitur baru adalah TAMBAHAN berbasis data lapangan.

### 7.3 Kenapa Juri Justru Menghargai Ini
1. **Riset lapangan nyata:** Tim tidak hanya duduk di depan laptop, tapi turun ke desa.
2. **Upgrade, bukan downgrade:** Menambahkan fitur, bukan menghilangkan.
3. **Responsif terhadap kebutuhan pengguna:** Prinsip utama Software Engineering.
4. **Iterasi produk:** Menunjukkan metodologi pengembangan yang matang (Agile).

### 8. Arsitektur Operasional & Security by Design (Penjelasan Lanjutan)

#### 8.1 ~~Pembuatan Akun Eksekutif (Lurah/Kades)~~ [DIBATALKAN - LIHAT 8.5]
~~Dalam sistem SilaDesBeng, tidak ada tombol antarmuka (UI) bagi operator/Admin Desa untuk membuat akun `lurah`.~~
**STATUS: DIHAPUS.** Bab ini sudah tidak berlaku. Lihat **Bab 8.5 (KEPUTUSAN KRITIS)** di bawah.

#### 8.2 Wewenang Admin Kabupaten & Kecamatan
Admin Kabupaten (`super_admin`) dan Admin Kecamatan (`admin_kecamatan`) bukan sekadar pemantau, melainkan **Regulator**.
Berdasarkan fungsi `isSuperAdmin()` di model `User`, pimpinan dari level Kabupaten dan Kecamatan **memiliki hak akses penuh** untuk masuk ke **Manajemen Staf** dan membuat akun Staf untuk instansi mereka sendiri.
*(Contoh: Jika Kecamatan memiliki armada Ambulans sendiri, Admin Kecamatan dapat membuat akun staf dengan wewenang mengelola ambulans khusus untuk wilayah kecamatan tersebut).*

#### 8.3 Aktivasi Layanan Jalur Kemitraan (Auto-Activate)
Saat sebuah wilayah (contoh: BUMDes di level Desa) pertama kali diregistrasikan, layanan belum otomatis menyala.
BUMDes harus mengajukan diri melalui **Menu Kemitraan**.
Ketika pengajuan tersebut **disetujui (Approve)** oleh atasan (Kecamatan atau Kabupaten), sistem akan mengeksekusi perintah yang secara cerdas **mengaktifkan seluruh 7 modul layanan** untuk desa tersebut secara otomatis. Desa langsung siap beroperasi merekrut staf dan mengelola unit usaha.

#### 8.4 Penanganan SLA Pelaporan Warga (Zero-Bottleneck)
Pelaporan Warga dilengkapi dengan mekanisme **Eskalasi Otomatis (SLA)** yang digerakkan oleh *Cron Job* otomatis (`laporan:auto-escalate`).
- Jika sebuah laporan dikirim ke **RT** namun diabaikan selama batas waktu (24 jam), sistem menarik paksa laporan tersebut dan menampilkannya di *Dashboard* **RW**.
- Jika diabaikan lagi, laporan naik otomatis ke tingkat **Desa**, lalu ke **Kecamatan**.
- Siapa yang merespons? Admin tingkat bersangkutan (atau **Staf Humas** yang diberi hak akses "Layanan Pelaporan" oleh Admin) diwajibkan untuk membalas, memperbarui status, atau menuntaskan solusi atas laporan eskalasi tersebut, menjamin tidak ada keluhan warga yang mengendap ("Zero-Bottleneck").

---

#### *** 8.5 KEPUTUSAN KRITIS: PENGHAPUSAN TOTAL ROLE `lurah` (Kepala Desa / Sekdes) ***

**Tanggal Keputusan:** 10 Agustus 2026
**Status:** SELESAI DIEKSEKUSI OLEH TIM (10 Agustus 2026)

**Latar Belakang Masalah (Kenapa Role `lurah` Dihapus):**
Setelah analisis mendalam terhadap realita birokrasi pemerintahan desa di Indonesia, ditemukan beberapa masalah fundamental yang membuat keberadaan role `lurah` sebagai akun terpisah menjadi *overengineering* (rekayasa berlebihan) dan justru menimbulkan masalah baru:

1. **Dinamika Politik Desa:** Kepala Desa menjabat 5 tahun. Masa jabatan antar desa TIDAK serentak. Ada yang baru dilantik, ada yang sudah habis masa jabatan, ada yang PLT (Pelaksana Tugas Sementara). Jika sistem harus mengurus rotasi akun Kades secara manual, Admin Kabupaten akan mati kelelahan mengurus ratusan desa (155 Desa + 47 Kelurahan = 202 wilayah).
2. **Kades Bukan Operator IT:** Di dunia nyata, Kepala Desa hampir TIDAK PERNAH membuka dashboard sistem secara mandiri. Mereka terbiasa menerima laporan bersih dari stafnya (Sekdes / Operator BUMDes).
3. **Konflik Akun Ganda (UX Issue):** Jika Kades ingin memesan gas atau meminjam fasilitas di BUMDes desanya sendiri, dia butuh akun Warga biasa. Sangat membingungkan jika di HP-nya harus gonta-ganti login antara "Akun Kades" dan "Akun Warga".
4. **Fitur Ekspor PDF Sudah Ada:** Operator (Admin Desa) sudah memiliki fitur cetak laporan keuangan dan statistik. Cukup dikirim via Grup WhatsApp Pemdes. Kepala Desa tidak perlu login ke dashboard.
5. **Transparansi Publik Sudah Tersedia:** Grafik persentase pendapatan unit layanan sudah bisa diakses oleh SIAPA SAJA (termasuk Kades) melalui halaman publik tanpa login.

**Keputusan Final:**
- Role `lurah` **DIHAPUS TOTAL** dari seluruh sistem (Middleware, Routing, View, Controller, Model).
- File `LurahController.php` beserta seluruh View di `resources/views/lurah/` **DIHAPUS**.
- Seluruh referensi string `'lurah'` di array pengecekan akses (CheckRole, DashboardController, RegionManagement, MutasiAdmin, UserManagement, navbar, kemitraan) **DIBERSIHKAN**.

**Arsitektur Pengganti:**
- **Operator (Admin Desa / Staf BUMDes)** menjadi satu-satunya pengendali operasional di tingkat desa.
- **Kepala Desa** berperan sebagai *End-User* (penerima informasi akhir) yang mendapatkan laporan PDF dari Operator via WhatsApp, atau mengakses grafik publik melalui akun Warga biasa jika ingin melihat sendiri.
- **Tidak ada fitur "Daftarkan Kepala Desa"** di sistem. Tidak diperlukan.

**Jawaban untuk Juri KMIPN (Jika Ditanya):**
*"Kami sengaja tidak membuat akun khusus Kepala Desa karena jabatan Kades bersifat politis dan periodik (5 tahun, tidak serentak antar desa). Membuat akun khusus pejabat akan menimbulkan beban administratif rotasi yang tidak realistis untuk skala 202 desa. Sebagai gantinya, Operator BUMDes menyediakan laporan berkala dalam format PDF yang dikirim langsung ke Kepala Desa melalui saluran komunikasi resmi pemerintah desa. Data publik seperti persentase kinerja unit layanan juga bisa diakses siapa saja tanpa login khusus. Pendekatan ini lebih pragmatis, realistis, dan scalable."*

---

#### *** 8.6 KEPUTUSAN KRITIS (PENTING!): DATA OWNERSHIP & DELEGASI STAF ***

**Latar Belakang:**
Dalam SilaDesBeng, satu Admin Operator (Admin Desa) bisa mendelegasikan wewenang tertentu ke Staf Khusus (contoh: Staf Humas untuk Pelaporan & Berita, atau Staf Gas untuk layanan LPG). Timbul pertanyaan operasional: Bagaimana nasib data jika staf dihapus? Apakah menu di Admin hilang?

**Aturan Arsitektur Sistem (Rules of Engine):**

1. **Menu Admin Operator TIDAK PERNAH HILANG (Absolute Supervisor)**
   Meskipun Admin Desa telah membuat "Staf Humas" khusus untuk mengurus Berita dan Pelaporan, menu Berita dan Pelaporan di *Dashboard* Admin Desa **TIDAK AKAN HILANG**. Admin Desa bertindak sebagai *Super Admin/Supervisor* di desanya. Mereka tetap bisa memantau, bahkan ikut campur merespons laporan jika Staf Humas sedang kewalahan/sakit.

2. **Data Dimiliki oleh "Desa", Bukan oleh "Staf" (Region-Bound Data)**
   Semua data operasional (Produk Gas, Riwayat Sewa, Berita Daerah, Balasan Pelaporan) tidak diikat secara mutlak ke ID Staf yang membuatnya. Data tersebut diikat ke `region_id` (Entitas Desa/BUMDes).
   
3. **Data TIDAK HILANG Jika Staf Dihapus**
   Jika Staf Humas (misal: Budi) dipecat atau akunnya dihapus dari sistem, **semua berita dan balasan laporan yang pernah dibuat oleh Budi TETAP AMAN dan TIDAK HILANG**. Data tersebut akan tetap muncul di sistem publik dan di dashboard Admin Desa. Admin Desa bisa mengambil alih tugas tersebut secara otomatis, atau mendelegasikan (membuat) akun staf baru untuk melanjutkannya.

**Kesimpulan untuk KMIPN:**
*"Sistem ini menggunakan arsitektur keamanan multitenancy berbasis wilayah (region-bound), bukan user-bound. Artinya, pergantian, pemecatan, atau penambahan staf operasional di lapangan tidak akan merusak integritas sejarah data pemerintahan desa tersebut."*

---

#### *** 8.7 KEPUTUSAN KRITIS (PENTING!): EKOSISTEM PASAR DAERAH & STANDAR KODE KMIPN ***

**Aturan Ekosistem Pasar Daerah (BUMDes Sentris):**
- **Pasar Daerah BUKAN C2C (Masyarakat ke Masyarakat):** Sistem SilaDesBeng menetapkan bahwa produk yang dijual di Pasar Daerah secara eksklusif dikelola dan didaftarkan oleh **Unit Usaha BUMDes**, bukan oleh masyarakat individu secara mandiri.
- **Alasan Operasional:** Jika masyarakat umum diizinkan menjadi penjual independen (membuat akun seller sendiri), sistem logistik (pengiriman/kurir) tidak akan berjalan karena desa tidak memiliki infrastruktur kurir independen yang mumpuni, serta akan menimbulkan kerumitan administrasi dan kontrol kualitas produk.
- **Peran BUMDes:** Masyarakat yang memiliki produk karya asli desa (seperti Lempuk, Anyaman) dapat menjual produknya *melalui* BUMDes (BUMDes membeli atau membantu memasarkan). BUMDes bertindak sebagai sentra distributor lokal yang terverifikasi dan mengelola transaksinya.

**Aturan Penamaan Aset & Kode (Standar Kompetisi KMIPN):**
- **DILARANG Keras Meninggalkan Jejak Template Eksternal:** Karena ini adalah proyek untuk kompetisi tingkat nasional (KMIPN), juri akan menilai orisinalitas, profesionalisme, dan kerapian. Sangat dilarang keras menyimpan, membiarkan, atau membuat penamaan file/variabel/class CSS yang membawa jejak nama *template* luar atau orang lain (misalnya penamaan ulfa-pasar.css).
- **Standardisasi Penamaan:** Semua *file* tambahan, skrip, dan penulisan *class* HTML harus ditulis ulang menggunakan *naming convention* yang relevan dengan modul aplikasi itu sendiri (misalnya: pasar-daerah.css).
