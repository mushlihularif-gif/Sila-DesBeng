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


  - **[Fitur Baru - Image Cropper / Potong Foto Interaktif] (SELESAI - 15 Agustus 2026):**
    - **Latar Belakang:** Saat mengunggah foto profil atau foto produk, pengguna seringkali memilih foto berformat memanjang (*landscape/portrait*) yang tidak ideal. Tanpa fitur pemotongan, sistem hanya menampilkan bagian tengah foto secara otomatis, sehingga wajah atau objek penting bisa terpotong. Fitur ini memungkinkan pengguna memilih area foto yang ingin ditampilkan sebelum diunggah.
    - **Komponen Universal:** Dibuat sebagai komponen Blade terpisah di `resources/views/components/cropper-modal.blade.php` menggunakan pustaka **Cropper.js** (CDN). Komponen ini bersifat *framework-agnostic* (bekerja baik di sisi Admin yang menggunakan Bootstrap maupun di sisi User yang menggunakan Tailwind CSS) karena menggunakan CSS murni sendiri.
    - **Teknik Zero-Backend (DataTransfer API):** Hasil pemotongan gambar TIDAK dikirim sebagai Base64 berukuran raksasa ke server. Melainkan, hasil *crop* dikemas ulang menjadi objek `Blob` lalu disuntikkan kembali ke `<input type="file">` menggunakan teknik `DataTransfer`. Server menerima file seolah-olah pengguna mengunggah foto yang memang sudah terpotong rapi dari perangkatnya. **Seluruh controller backend tetap utuh 100% tanpa modifikasi.**
    - **Fungsi Pemanggil Global:** `initGlobalCropper(inputElement, previewElementId, aspectRatio, showRatioButtons)` -- parameter ke-4 (`showRatioButtons`) mengontrol apakah tombol pemilihan rasio ditampilkan atau tidak.
    - **Area Penerapan (Foto Profil - Rasio Terkunci 1:1):**
      1. Profil Admin Desa/Kecamatan/Kabupaten (`admin/profile/scripts.blade.php`)
      2. Profil Warga/User (`users/profile.blade.php`)
      3. Struktur Pemerintah Desa - Tambah & Edit Anggota (`admin/isewa/bumdes/create.blade.php` & `edit.blade.php`)
    - **Area Penerapan (Foto Produk - Rasio Dinamis):**
      1. Penyewaan Alat - Tambah & Edit (`admin/unit/penyewaan/`)
      2. Penjualan Gas - Tambah & Edit (`admin/unit/penjualan_gas/`)
      3. Penyewaan Mobil - Tambah & Edit (`admin/unit/mobil/`)
      4. Fasilitas Umum - Tambah & Edit (`admin/unit/fasilitas_umum/`)
    - **Pilihan Rasio untuk Foto Produk:** Saat mengunggah foto produk, modal *cropper* menampilkan 3 tombol *pill* di bagian atas: **1:1 (Persegi)**, **4:3 (Lanskap)**, dan **Bebas (Free Crop)**. Rasio 16:9 dan 9:16 sengaja TIDAK disertakan karena terlalu lebar/pipih untuk tampilan katalog produk. Pengguna bisa mengganti rasio secara langsung saat memotong tanpa menutup modal.
    - **Include di Layout:** Komponen `@include('components.cropper-modal')` telah ditambahkan sebelum tag `</body>` pada kedua layout utama: `admin/layouts/admin.blade.php` dan `layouts/app.blade.php`.

  - **[Aturan Privasi Keuangan Antar Desa - Indeks Poin] (DIKONFIRMASI - 16 Agustus 2026):**
    - **Prinsip Dasar:** Admin Desa **hanya bisa melihat data keuangan (nominal Rupiah) milik desanya sendiri**. Ini ditegakkan oleh fungsi `applyRegionFilter()` di `Controller.php` dengan mode `$strict = true` pada query Laporan Transaksi dan Pendapatan.
    - **Transparansi Lintas Desa (Tanpa Bocor Nominal):** Untuk mendorong kompetisi sehat antar desa, sistem sudah menyediakan grafik **Indeks Poin** di beberapa halaman strategis:
      - Dashboard Admin (`admin/dashboard/index.blade.php`)
      - Laporan Wilayah Admin (`admin/laporan/wilayah.blade.php`)
      - Beranda User / Halaman Publik (`beranda/index.blade.php`)
      - Laporan BUMDes User (`users/bumdes-laporan.blade.php`)
    - **Cara Kerja Indeks Poin:** Grafik ini menampilkan skor aktivitas/pertumbuhan setiap desa dalam bentuk **poin relatif**, bukan nominal Rupiah absolut. Dengan demikian, Admin Kabupaten, Kecamatan, Desa lain, maupun Warga biasa bisa melihat **siapa yang paling aktif dan berkembang**, tanpa mengetahui berapa nominal pendapatan pasti masing-masing desa. Ini menjaga kerahasiaan APBDes sekaligus menstimulasi semangat kerja.
    - **Keputusan Final:** Sistem ini sudah BENAR dan TIDAK PERLU diubah. Tidak diperlukan fitur tambahan "Papan Peringkat Pertumbuhan Desa" karena grafik Indeks Poin yang sudah ada telah menjalankan fungsi tersebut secara sempurna.

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

---

#### *** 8.8 KEPUTUSAN KRITIS: ARSITEKTUR SPA (SINGLE PAGE APPLICATION) & HYBRID ONGKIR PASAR DAERAH ***

**Status:** SELESAI DIEKSEKUSI OLEH TIM (13 Agustus 2026)

**1. Refactoring UI Admin Pasar Daerah ke SPA (Tab Navigasi Tanpa Reload):**
- **Masalah Awal:** Sebelumnya, pengelolaan "Daftar Produk", "Daftar Pesanan", "Laporan", dan "Pengaturan Toko" di menu Pasar Daerah dipisah ke halaman (view) yang berbeda. Hal ini menyebabkan loading yang tidak perlu dan memperlambat alur kerja Admin Desa.
- **Solusi Eksekusi:** Menyatukan seluruh halaman operasional Pasar Daerah ke dalam satu berkas `index.blade.php` menggunakan arsitektur Tab Bootstrap (nav-pills). Sistem kini bekerja seperti Single Page Application (SPA). Semua data (Produk, Pesanan, Laporan, Pengaturan) di-render di satu halaman, dengan navigasi instan antar tab.
- **Keuntungan:** UX menjadi sangat responsif, mengurangi beban server (navigasi lokal DOM), dan mempermudah Admin Desa melihat gambaran utuh tokonya.

**2. Kebijakan "Hybrid Ongkir" (Luar Kecamatan):**
- **Kondisi Lapangan:** Kurir lokal desa mungkin sanggup memukul rata ongkir ke kecamatan tetangga (misal Rp20.000 ke manapun asalkan masih 1 kecamatan sebelah), tapi bisa jadi rugi jika ada kecamatan yang terpisah selat/pulau (Bengkalis adalah daerah kepulauan).
- **Eksekusi:** Menambahkan 2 Opsi Pengiriman Luar Kecamatan:
  1. **Pukul Rata:** Satu nominal harga pasti yang berlaku ke seluruh pembeli dari luar kecamatan.
  2. **Per Kecamatan:** Admin Desa dapat mencentang (mengaktifkan) kecamatan mana saja yang sanggup dilayani, dan menentukan ongkir spesifik per kecamatan tersebut.
- **Keputusan UI:** Menerapkan desain interaktif berupa "2 Kartu Besar" (Pukul Rata vs Per Kecamatan).

**3. Bug Fix Kritis - Hilangnya Script Javascript di View (@push vs @section):**
- **Kasus Laporan Bug:** Fungsi klik/interaktif di halaman Admin Pasar Daerah sering *freeze* (beku/mati) atau mengalami *500 Server Error*.
- **Penyebab (Root Cause):** Terdapat kesalahan sintaks *rendering* bawaan Laravel. Kami sempat menggunakan perintah `@push('scripts')` untuk memuat Javascript khusus halaman tersebut. Namun, karena file induk *layout* (`admin.blade.php`) menggunakan `@yield('scripts')` (bukan `@stack('scripts')`), maka seluruh *Javascript* tersebut secara otomatis dibuang/ditolak oleh Laravel saat me-render HTML ke pengguna, sehingga tombol-tombol kehilangan "otak" (*handler* kliknya hilang).
- **Penyelesaian Final:** Mengubah secara permanen pendekatan blok khusus (*inline script* di view) menjadi `@section('scripts')` untuk skrip, dan `@section('styles')` untuk CSS agar sesuai dengan *yield* pada *layout* utama, sekaligus menuliskan kode fungsi JS secara murni dan global (`window.setOngkirType = function(...)`) demi menjamin kekebalan kode dari intervensi CSS/Label klik ganda browser.

---

#### *** 8.9 KEPUTUSAN KRITIS: OTOMATISASI STATUS SUPIR (FLEET MANAGEMENT) ***

**Status:** SELESAI DIEKSEKUSI OLEH TIM (17 Agustus 2026)

**1. Logika Penugasan Supir (Otomasi Penuh):**
- **Masalah Awal:** Sebelumnya, Admin harus memilih "Sedang Bertugas" saat membuat data supir baru, padahal secara logika supir yang baru dimasukkan belum bertugas. Ini juga bisa memicu *human error* di mana Admin lupa mengganti status supir setelah pesanan selesai, membuat supir seolah-olah "menghilang" dari daftar tugas berikutnya.
- **Solusi Eksekusi:** Menerapkan sistem *Smart Dispatch* di dalam `RequestController`:
  - **Saat Tambah Data:** Pilihan "Sedang Bertugas" dhilangkan sepenuhnya dari *form* pembuatan supir baru. Supir baru hanya bisa diset sebagai "Tersedia" atau "Tidak Aktif".
  - **Saat Ditugaskan (Assign):** Saat Admin menyetujui pesanan (Penyewaan Mobil/Fasilitas) dan memilih supir dari daftar "Tersedia", sistem secara **otomatis** mengunci status supir tersebut menjadi `Sedang Bertugas`.
  - **Saat Selesai/Batal:** Begitu Admin menandai pesanan sebagai "Selesai", atau pesanan tersebut dibatalkan/ditolak, sistem secara **otomatis** membebaskan supir tersebut (mengembalikan statusnya menjadi `Tersedia`) sehingga ia bisa langsung muncul kembali di daftar pilihan untuk tugas warga yang lain.
- **Keuntungan:** *Zero human error* dalam pengaturan armada. Admin tidak perlu lagi bolak-balik ke menu Manajemen Supir hanya untuk mengubah status supir secara manual.


#### *** 8.10 STRATEGI INFRASTRUKTUR DAN RENCANA ANGGARAN BIAYA (RAB) KMIPN ***

**Status:** DISEPAKATI TIM (19 Agustus 2026)

Dalam persiapan pengajuan pendanaan lomba KMIPN dan strategi implementasi sistem berskala Kabupaten, disepakati arsitektur infrastruktur dan skema biaya sebagai berikut:

**1. Strategi Domain (Smart Portfolio):**
- Sistem tidak menggunakan domain pemerintah saat lomba, tetapi membeli domain .com tunggal atas nama tim, yaitu `untukkita.com`.
- Sistem SilaDesBeng di-deploy sebagai subdomain (`siladesbeng.untukkita.com`). Hal ini memungkinkan domain utama dipakai sebagai portofolio tim jangka panjang, dan siap digunakan untuk lomba-lomba berikutnya tanpa perlu membeli domain baru.
- **Keputusan Hosting:** Untuk efisiensi dana lomba, diputuskan menggunakan paket **Shared Hosting (Unlimited L)** yang sudah memiliki fitur **Akses SSH** (bukan VPS murni) dengan sewa langsung 1 tahun penuh. Akses SSH ini krusial agar perintah artisan Laravel tetap bisa dijalankan.

**2. Google Maps API & Gemini AI (SilaDesBeng Assistant):**
- **Model Pembayaran:** Keduanya berada di bawah Google Cloud Console dengan sistem *Pay-as-you-go* (bayar sesuai penggunaan).
- **Status Lomba:** Dengan *Pay-as-you-go*, sebagian besar traffic ter-cover oleh *Free Credit* bulanan. Namun, untuk keperluan RAB (jaga-jaga lonjakan request saat demo), dialokasikan dana *buffer* sebesar **Rp 50.000** untuk Google Maps API dan **Rp 50.000** untuk Gemini AI API.

**3. Layanan & Fitur Pendukung Lainnya:**
- **Midtrans (Payment Gateway):** Rp 0 (Gratis Pendaftaran). Hanya ada biaya admin dari pihak Midtrans saat terjadi transaksi (biaya pendaftaran dan integrasi sistem tetap Rp 0).
- **WhatsApp Gateway (Fonnte):** Langganan 1 bulan khusus saat final lomba berlangsung (Rp 140.000) agar notifikasi dan OTP WhatsApp warga bisa didemokan ke juri secara real-time.
- **E-KYC (KTP & Wajah):** Menggunakan API OCR.space gratis (API Key: helloworld) untuk keperluan demo. Jika diimplementasikan penuh oleh pemerintah, disarankan menggunakan Vendor KYC resmi berbayar.
- **Firebase Cloud Messaging (FCM):** Rp 0. Digunakan untuk mengirimkan notifikasi *Push Notification* (pop-up HP) secara *real-time* ke aplikasi Android warga. Layanan ini disediakan gratis 100% oleh Google Firebase.
- **Generate PDF & QR Code:** Rp 0. Fitur untuk cetak laporan, struk sewa, dan bukti keaslian dokumen dibangun secara mandiri (*Native*) menggunakan *library* internal Laravel yang berjalan 100% gratis di dalam *Shared Hosting*, tanpa ketergantungan API luar.

**4. Ringkasan RAB Demo Lomba (Untuk Pengajuan Kampus):**
- **Paket Domain (`untukkita.com`) & Hosting Unlimited L (1 Tahun): Rp 750.000** *(Tagihan asli ~Rp 491.000 + Uang jaga-jaga operasional)*
- Langganan WhatsApp API Fonnte (1 Bulan): Rp 140.000
- Dana Buffer Google Cloud Console (Mencakup Kuota Maps API & Gemini AI API): Rp 300.000. *(Catatan: Maps API digunakan untuk fitur Peta Interaktif dan Titik Lokasi Pelaporan Warga. Gemini AI digunakan untuk SilaDesBeng Assistant sebagai AI cerdas yang menjawab pertanyaan warga).*
- Akun Google Play Console (Satu kali bayar untuk publish APK): Rp 400.000
- **Total Estimasi:** **Rp 1.590.000**


## CATATAN PENTING - VERIFIKASI KYC (DITANGGUHKAN)
Saat ini (tahap development), fitur wajib verifikasi KTP (KYC) untuk akses Pelaporan Warga dan halaman Kelola Layanan Wilayah (Admin RT/RW) **DIMATIKAN SEMENTARA**. Hal ini dilakukan agar proses pengembangan UI/UX tidak terhambat oleh proses scan KTP. Setelah tampilan selesai, fitur ini HARUS DIAKTIFKAN KEMBALI di 
outes/web.php dan LaporanController.php.


## CATATAN PENYELESAIAN BUG & LOGIKA SISTEM TERBARU

**1. Bug "Layanan Belum Tersedia" di Panel Admin RT/RW**
Penyebabnya: Ada kesalahan logika (bug) di Middleware CheckRegionService. Saat sistem mengecek "Apakah layanan ini aktif di daerah ini?", sistem hanya melihat ID Region milik RT/RW. Padahal, yang mengaktifkan layanannya adalah Super Admin Desa. Karena sistem tidak mengecek "induk" (Desa) dari RT tersebut, maka akses Admin RT/RW terblokir seolah-olah layanannya tidak ada. Perbaikannya: Memperbarui CheckRegionService agar turut memeriksa status layanan pada "induk/leluhur" (Desa) dari wilayah user. Sekarang, Admin RT/RW dapat mengakses layanannya dengan lancar.

**2. Tampilan Menu di Halaman Beranda (Homepage)**
Penyebabnya: Ada pemetaan nama unit layanan yang tidak lengkap di halaman beranda.index.blade.php (seperti Unit Penyewaan Mobil dan Fasilitas Umum terlewatkan dari daftar periksa) sehingga sistem menyembunyikannya secara tidak sengaja untuk user yang login. 
Perbaikannya:
- Memperbaiki pemetaan nama layanannya.
- Halaman Beranda sekarang otomatis menyesuaikan jumlah ikon layanan yang tampil berdasarkan apa yang diaktifkan oleh Super Admin di daerah/desa user tersebut. Jika desa mematikan 1, maka hanya tampil sisa layanannya.
- Pengecualian Khusus: Layanan Pasar Daerah, Kabar dan Informasi Daerah (Berita), dan Pengumuman akan selalu tampil untuk publik, terlepas dari daerah manapun, tanpa perlu pilih-pilih desa terlebih dahulu.
- Untuk Guest: Karena lokasinya belum jelas, semua layanan (lengkap) akan tampil, namun mereka akan diminta memilih daerah saat mencoba masuk.

**3. Logika Kewajiban Verifikasi KTP (KYC)**
Menerima laporan anonim berisiko tinggi (bisa berisi ujaran kebencian tanpa tanggung jawab). Sistem telah dipasang gerbang verifikasi (KYC) tanpa membuat fitur baru, menggunakan modal popup KYC yang sudah ada:
- Pelaporan Warga: Warga wajib melakukan verifikasi KTP sebelum bisa mengakses form Pelaporan Warga.
- Admin RT & RW: Karena bertugas memproses laporan warga, mereka juga wajib melakukan verifikasi KTP (sebelum bisa masuk ke menu Kelola Layanan Wilayah). Jika belum, muncul popup peringatan.
- Berita & Pengumuman (Membaca): Publik bebas membaca tanpa perlu verifikasi.
- Memesan Layanan: Tetap wajib verifikasi.
- Popup modal peringatan verifikasi dipasang secara global di layout utama.


### 8.8 Keputusan UI Publik: Berita vs Pengumuman
**Latar Belakang:**
Secara fungsional, **Berita** (dokumentasi publik, jangkauan luas) dan **Pengumuman** (undangan, edaran, instruksi seperti 17 Agustus atau Goro) adalah dua entitas yang berbeda tujuan. Namun, dalam sistem SilaDesBeng, ketika warga mengklik dan membaca detailnya, mereka dirender menggunakan **satu template desain yang sama** (untuk efisiensi dan keseragaman UI).

**Masalah:**
Jika menggunakan satu template statis, membaca sebuah pengumuman resmi akan terasa janggal jika di bagian bawahnya terdapat label 'Berita Terkait' atau 'Rekomendasi untuk Anda'.

**Keputusan Desain (Detik.com Style):**
1. **Tata Letak Elegan:** Desain detail artikel dirombak mengikuti portal profesional (seperti Detik.com). Judul besar di atas, diikuti meta info penulis (`Pemerintah Desa X`) dan waktu rilis (`Hari, Tanggal Bulan Tahun HH:MM WIB`), baru kemudian teks isi.
2. **Kosakata Dinamis (Smart Rendering):** Meskipun menggunakan satu file Blade template yang sama (`show.blade.php`), sistem ditambahkan kecerdasan untuk mendeteksi `post_category`.
   - Jika membaca Berita -> Menampilkan label **'Berita Terkait'** dan **'Rekomendasi untuk Anda'**.
   - Jika membaca Pengumuman -> Label otomatis berubah menjadi **'Pengumuman Terkait'** dan **'Pengumuman Lainnya'** agar konteks formalnya terjaga.

**Nilai Jual untuk Juri KMIPN:**
Hal ini menunjukkan kedetailan tim dalam merancang UX (User Experience). Sistem tidak hanya fungsional, tetapi juga memiliki logika linguistik yang menyesuaikan diri dengan konteks konten (Context-Aware UI), membuat portal desa terasa sangat profesional.


## Catatan Fitur Tambahan (Backlog)
- **E-KYC Krisis Gas**: Fitur unggah dan pemindaian OCR untuk Kartu Keluarga (KK) saat ini belum diaktifkan di halaman utama KYC, dan akan dirancang/diaktifkan khusus pada saat alur pembelian/distribusi subsidi krisis gas.



## CATATAN PANDUAN UI/UX (STANDARISASI KMIPN)

**1. Sistem Notifikasi (Toast / Alert):**
Untuk menjaga konsistensi UI/UX selama penjurian KMIPN, **DILARANG** menggunakan library popup eksternal (seperti SweetAlert2) kecuali memang sudah terpasang secara global di admin panel.
Untuk antarmuka warga (User/Public UI), sistem WAJIB menggunakan fungsi notifikasi native bawaan sistem yaitu `showToast(message, type)` (yang terpasang di `auth.scripts`) atau notifikasi AlpineJS bawaan. 
- Format penggunaan: `showToast('Pesan Anda', 'success|error|warning|info')`.
- Notifikasi ini akan muncul melayang di pojok kanan atas layar secara konsisten.

**2. Penulisan Nama Sistem:**
Penulisan nama aplikasi di sisi publik dan teks pesan harus konsisten menggunakan **"Siladesbeng"** (bukan SilaDesBeng atau siladesbeng), kecuali untuk keperluan penulisan variabel kode atau logo.


## CATATAN PENGUJIAN FITUR (TESTING)

**1. Liveness Detection / Kamera KTP (MediaPipe):**
- Saat tahap *development* di Laragon (Localhost), browser modern (Chrome, Edge, dll) akan memblokir akses ke WebCam jika URL menggunakan protokol `http://` selain nama domain `localhost`.
- Jika mengakses lewat domain virtual seperti `http://siladesbeng.test`, kamera akan *error* dan sistem akan otomatis mengarahkan warga (fallback) ke halaman Verifikasi Manual.
- **Tindak Lanjut & Solusi Uji Coba Lokal:** 
  Untuk membuktikan dan menguji coba fitur Liveness AI ini di komputer lokal tanpa harus menunggu *hosting*, gunakan trik `localhost`:
  1. Jalankan perintah `php artisan serve` di terminal proyek.
  2. Akses aplikasi melalui browser dengan URL: `http://127.0.0.1:8000` (atau `http://localhost:8000`).
  3. Browser (Chrome/Edge) akan secara otomatis **mengizinkan akses kamera** pada domain `127.0.0.1` meskipun tanpa HTTPS.

**2. Depresiasi Halaman Verifikasi Lama:**
- Halaman verifikasi identitas manual yang lama (`/profile/verifikasi`) telah sepenuhnya dinonaktifkan dan digantikan oleh halaman E-KYC AI terpadu (`/kyc`).
- Semua rute atau menu navigasi yang sebelumnya mengarah ke halaman lama wajib dialihkan (di-redirect) ke `/kyc` untuk memastikan warga tidak tersesat ke form manual tanpa OCR.



## ?? Arsitektur Kriptografi Lapis 3 (Defense in Depth)

Sistem SilaDesBeng menggunakan 3 lapis algoritma kriptografi untuk melindungi data warga dan memastikan fungsionalitas pencarian data tanpa mengorbankan privasi:

1. **AES-256-GCM (Standar Laravel):**
   Digunakan untuk mengamankan jalur lalu lintas dasar, token CSRF, Sesi Cookie, dan hal-hal yang bersifat sementara di sisi jaringan.

2. **ChaCha20-Poly1305 (Enkripsi PII & File):**
   Algoritma yang dikembangkan khusus untuk aplikasi ini (App\Casts\ChaCha20Encrypted & FileEncryptionService). ChaCha20 dipilih karena sangat cepat di perangkat *mobile* (ARM) dan sangat tangguh. Digunakan untuk:
   - Membungkus teks Data Sensitif (Alamat, Nomor HP, Nama).
   - Mengenkripsi file fisik (Foto KTP, Foto Selfie Wajah).
   Sifat dari ChaCha20 adalah *Non-Deterministic* (Kodenya selalu berubah-ubah setiap kali dienkripsi ulang berkat bantuan *Nonce/IV*).

3. **HMAC-SHA256 (Blind Indexing / Pencarian Buta):**
   Karena ChaCha20 selalu merubah wujud datanya setiap detik, kita tidak bisa melakukan pencarian WHERE nik = '...'. Oleh karena itu, kita menanamkan HMAC-SHA256 pada saat proses penyimpanan (User::saving).
   - Sifat HMAC-SHA256 adalah *Deterministic* (Pasti). Input yang sama akan selalu menghasilkan Hash 64 karakter yang sama persis kapan pun dieksekusi.
   - Digunakan untuk: 
ik_hash, phone_hash, dan 
ame_hash.
   - Hal ini memungkinkan sistem mendeteksi keidentikan data warga tanpa pernah mengetahui (atau mendekripsi) angka NIK/Nomor HP aslinya.

---

## ??????????? Logika Klasifikasi Kartu Keluarga (Krisis Gas) & Privacy by Design

Dalam rangka memfasilitasi "Mode Krisis Gas" tanpa mengorbankan privasi warga, sistem menerapkan konsep **Zero Data Retention** (Tidak ada data sensitif yang ditahan/disimpan utuh).

### 1. Alur Verifikasi Kartu Keluarga (KK):
- Warga mengunggah foto KK.
- Mesin OCR mengekstrak No. KK dan daftar NIK Anggota Keluarga.
- **TIDAK ADA HAK EDIT ADMIN:** Untuk menjaga *Non-Repudiation*, Admin hanya bertugas sebagai Hakim (Setujui atau Tolak). Admin mencocokkan teks OCR dengan foto. Jika salah *typo*, Admin harus menolak agar warga mengulangnya.
- Setelah Admin mengklik **Setujui**, sistem akan:
  1. Menghitung Hash (HMAC-SHA256) dari No. KK asli dan seluruh NIK Anggota asli.
  2. Menyimpan No. KK dan Nama Kepala Keluarga dalam bentuk tersensor permanen (misal: 1472********0001 dan A***D) hanya untuk tampilan kosmetik di UI Admin.
  3. **MENGHANCURKAN FOTO KK SECARA PERMANEN** dari *storage* dan *database* (Storage::disk('private')->delete(...)). Foto hanya sekali pakai.

### 2. Skema Tabel Database Keluarga Berbasis Hash:
Kita menggunakan pemisahan tabel untuk menjaga privasi:
- **Tabel amily_cards (Brankas Induk):** Berisi 
o_kk_hash sebagai Kunci Induk.
- **Tabel amily_members (Daftar Pemegang Kunci):** Berisi daftar 
ik_hash dari seluruh anggota keluarga yang terhubung ke Brankas Induk di atas.

### 3. Logika Antrian Pemesanan Gas:
- Saat seorang warga (*login*) menekan "Pesan Gas", sistem mengambil 
ik_hash dari profil warga tersebut.
- Sistem mengecek di amily_members: *"Di Brankas KK (Hash) mana warga ini tergabung?"*.
- Sistem mengecek riwayat transaksi Brankas tersebut di tabel pemesanan gas.
- Jika ada *Hash* keluarga (anggota lain) yang sudah memesan dalam batas waktu yang ditentukan (misal 1 minggu), pesanan akan **Ditolak otomatis**.
- Metode ini memastikan penjatahan gas tepat sasaran per Kepala Keluarga (KK), namun *Hacker* yang membobol *database* hanya akan menemukan kumpulan kode Hash acak tanpa tahu identitas asli maupun hubungan darah warga tersebut.



## ??? Alasan & Fungsi dari Setiap Lapis Keamanan (Privacy Framework)

Berikut adalah landasan teori dan fungsi dari masing-masing kebijakan keamanan ketat yang diterapkan di SilaDesBeng:

### 1. Fungsi Penghapusan Permanen Foto KTP & KK (Burn After Reading)
* **Fungsi:** Mengosongkan memori penyimpanan dari gambar yang mengandung identitas visual warga sesaat setelah Admin melakukan verifikasi (Setujui/Tolak).
* **Alasan:** Foto KTP/KK adalah komoditas utama di pasar gelap dunia maya (sering disalahgunakan untuk pendaftaran Pinjol Ilegal atau penipuan). Dengan menghancurkan foto fisik, jika suatu saat server SilaDesBeng diserang peretas (*hacker*), peretas tersebut **TIDAK AKAN** menemukan satu lembar pun foto KTP warga. Warga terbebas dari ancaman nyata pencurian identitas. Selain itu, langkah ini menghemat ratusan Gigabyte kapasitas Server secara jangka panjang.

### 2. Fungsi Sensor Kosmetik Bintang (Misal: 1472********0001 & R***Y***N)
* **Fungsi:** Mengubah tampilan huruf/angka asli menjadi deretan bintang, dan menyimpannya di *database* secara permanen dalam bentuk seperti itu.
* **Alasan:** Meskipun foto sudah dihapus, teks NIK dan Nama Lengkap yang disimpan di dalam *database* masih rawan diretas. Dengan menyensornya, aplikasi tetap bisa menampilkan informasi dasar ke warga atau Admin (misal: "Halo, R***Y***N"), namun peretas yang mencuri isi tabel *database* tidak akan pernah bisa membaca nama dan NIK aslinya.

### 3. Fungsi Blind Indexing / Kode Hash HMAC-SHA256 (Misal: 8d9b1x7z...)
* **Fungsi:** Menciptakan "Sidik Jari Digital" sepanjang 64 karakter acak dari sebuah NIK atau Nomor HP tanpa bisa diubah kembali ke bentuk angka aslinya.
* **Alasan:** Karena NIK asli sudah disensor bintang, sistem "buta" dan tidak tahu cara mengelompokkan keluarga atau mencocokkan data. HMAC-SHA256 menutupi kelemahan ini. Karena Hash bersifat absolut (angka NIK yang sama selalu menghasilkan Hash yang sama), sistem bisa menyatukan Warga A dan Warga B ke dalam satu Kartu Keluarga murni berdasarkan pencocokan Hash. Hacker yang meretas tabel ini hanya akan melihat lautan kode acak tanpa makna.

### 4. Fungsi Pencabutan Hak Edit dari Admin (Non-Repudiation)
* **Fungsi:** Admin tidak disediakan formulir (kolom) untuk mengetik atau memperbaiki data NIK warga yang *typo*. Admin hanya memiliki wewenang menekan **Setujui** atau **Tolak**.
* **Alasan:** Ini menutup celah *Rogue Admin* (Admin Nakal). Jika Admin bebas mengubah NIK, Admin bisa saja diam-diam mengubah NIK warga dengan NIK fiktif untuk menyedot jatah krisis gas mereka. Dengan mencabut hak edit, tanggung jawab kebenaran data mutlak ada di tangan Warga (Non-Repudiation). Warga yang salah ketik harus mengulang dari awal. Sistem menjadi sangat transparan dan minim penipuan.



---

## ?? STATUS IMPLEMENTASI (AGUSTUS 2026)

**Tahap 1: Persiapan Database Krisis Gas & Mutasi** (SELESAI)
- **Migrasi domicile_transfers:** Telah ditambahkan kolom t, w, dan ktp_image_path.
  - *Fungsi:* Agar saat warga/Admin melakukan mutasi domisili, data wilayah tetap akurat hingga tingkat RT/RW, dan foto KTP fisik wajib diunggah (sebagai pengganti OCR).
- **Tabel amily_cards (Brankas Induk):** Telah dibuat di *database* beserta Model Eloquent-nya.
  - *Fungsi:* Menyimpan 
o_kk_hash sebagai Kunci Induk tanpa wujud KK asli. Berperan sebagai acuan antrian gas (mencegah *double-claim*).
- **Tabel amily_members (Pemegang Kunci):** Telah dibuat di *database* beserta relasinya.
  - *Fungsi:* Menyimpan daftar 
ik_hash anggota keluarga. Berfungsi sebagai pelacak saat warga *login* untuk mengetahui keluarga mana yang ia miliki.
  - *Sistem Auto-Cabut:* Jika ada warga pindah KK, data NIK lamanya di tabel ini akan dihapus dan dipindah ke Brankas KK baru secara otomatis.


### Aturan Privasi Fitur Mutasi Akun (Pindah Domisili)
Sama halnya dengan verifikasi KTP awal (KYC), fitur Mutasi Akun mewajibkan warga mengunggah foto fisik KTP baru. Aturan penghancuran foto (*Burn After Reading*) **berlaku mutlak secara merata**. Setelah Admin Desa Setuju/Tolak pengajuan Mutasi, sistem wajib menggunakan perintah Storage::disk('private')->delete() untuk menghanguskan foto KTP dari memori dan mengubah kolom ktp_image_path menjadi 
ull.

---

## 🤖 KECERDASAN BUATAN (SILADESBENG ASSISTANT)

Pada Agustus 2026, otak utama **SiladesBeng Assistant** (terletak di ChatbotController.php) telah ditraining secara masif dan komprehensif agar memahami arsitektur, filosofi, dan aturan privasi aplikasi secara mutlak. Berikut adalah kerangka pengetahuan (System Prompt) yang telah ditanamkan ke dalam AI:

### 1. Identitas & Ejaan Mutlak
- **Penulisan Resmi:** Wajib dieja sebagai **SiladesBeng** (S besar, B besar, dan d kecil). Singkatan dari *Sistem Sinergi Layanan dan Aspirasi Desa di Kabupaten Bengkalis*.
- **Skala Ekosistem:** Berskala Kabupaten, meliputi 155 Desa dan 47 Kelurahan. (AI DILARANG menyebutnya terbatas pada "BUMDes", melainkan dikelola oleh Pemerintah Daerah / Kabupaten).
- **Filosofi Maskot:** Robot bertanjak bermotif kain songket. Warna biru laut (Maritim Bengkalis) dan Kuning Keemasan (Kesejahteraan Ekonomi Tanah Melayu).
- **Pencipta:** Tim Gen Hello World (Rizqy Hamadi Ken - Full Stack, Mushlihul Arif - UI/UX & Frontend, Dicki Wahyudi - Mobile Dev), dibimbing oleh Nurmi Hidayasari, ST., M.Kom.

### 2. Penguasaan 7 Unit Layanan (How-To)
AI telah diajari langkah-langkah (*Tutorial*) presisi untuk menggunakan aplikasi, meliputi:
- **Scan KTP & Selfie (KYC/Mutasi):** Untuk verifikasi data otomatis (OCR) dan pemindai manusia asli (*Liveness Detection* / Kedipan Mata).
- **Scan KK (Krisis Gas):** Aturan wajib unggah KK saat Mode Krisis menyala agar subsidi tepat sasaran per-Keluarga. Memiliki sistem *Auto-Cabut* jika warga pecah KK / menikah.
- **Penyewaan / Pembelian:** Tersedia fitur Kalender Pemesanan dan opsi Metode Pengiriman (Diantar/Delivery atau Dijemput/Pick-up).
- **Pasar Daerah:** Memiliki algoritma *Ongkir Hybrid* lintas desa secara otomatis.
- **Pelaporan Warga:** Matriks Eskalasi berjenjang (Zero-Bottleneck) dari RT -> RW -> Desa -> Kecamatan.

### 3. Pemahaman Pembayaran Inklusif (Omnichannel)
AI mengetahui 3 metode bayar yang tersedia bagi warga:
1. **Digital (Midtrans):** QRIS/Virtual Account (Otomatis Lunas).
2. **Transfer Manual:** Wajib unggah struk foto.
3. **Tunai (Cash / COD):** Membayar uang kertas ke petugas di lokasi.

### 4. Sistem Pertahanan & Bahasa Psikologis (Customer Service Empathy)
- **Anti-Malware & XSS:** Sistem menggunakan Karantina *Private Storage* dan Validasi MIME Type. AI diinstruksikan untuk MENGABAIKAN semua tautan URL/Link yang dikirim warga untuk mencegah *Phishing*.
- **Anti-Jailbreak / SQLi:** AI akan menolak mentah-mentah jika disuruh *"Abaikan instruksi"*, *"Tampilkan password"*, atau diberikan kode SQL Injection.
- **Komunikasi Privasi (Copywriting Kritis):** AI dilarang menggunakan bahasa menakutkan seperti *"Dihanguskan"* atau *"Brankas"*. AI telah diinstruksikan untuk menggunakan bahasa empati tinggi:
  > *"Foto KTP/KK Anda hanya dipakai sementara untuk pencocokan oleh Admin. Setelah disetujui, foto akan langsung terhapus otomatis dari memori server agar privasi Anda 100% aman dan tidak jatuh ke tangan yang salah."*

### 5. Arsitektur Model AI
- Model Primer yang digunakan beralih ke **gemini-3.5-flash-lite** menyesuaikan dengan perubahan *Deprecation* pada Google API v1beta di tahun 2026.


### 8.9 Arsitektur Hybrid Akun Dinas (RT/RW) & Mode Krisis Gas (PENTING)
- **Latar Belakang:** Admin Desa dapat membuatkan akun Admin RT/RW secara manual untuk pejabat yang gaptek. Namun, muncul konflik: Bagaimana dengan aturan 1 NIK = 1 Akun? Dan bagaimana jika akun pejabat (tanpa NIK) mencoba membeli gas saat Mode Krisis (yang mewajibkan scan KK)?
- **Solusi Arsitektur (Hybrid Logic):**
  1. **Logika Transaksi Gas (Dinamis):**
     - Kondisi Normal: Akun RT/RW bebas membeli gas.
     - Kondisi Mode Krisis (Dibatasi per KK): Sistem mendeteksi keberadaan NIK pada akun yang login. Jika ada NIK (hasil promosi akun warga), transaksi dilanjutkan ke Scan KK. Jika **TIDAK ADA NIK** (karena akun tersebut adalah murni *Akun Dinas*), transaksi diblokir dengan peringatan: *"Ini adalah akun khusus pemerintahan. Silakan login menggunakan akun warga pribadi Anda yang memiliki NIK untuk membeli gas subsidi."*
  2. **Logika Pembuatan Akun di Admin Desa (Validasi NIK Pintar):**
     - Admin Desa mengisi form pembuatan RT/RW (Kolom NIK berstatus **Wajib**).
     - Saat *Submit*, sistem mengecek NIK. Jika NIK **Belum Terdaftar**, akun dibuat lengkap dengan NIK (Terverifikasi penuh, menolong pejabat gaptek agar punya 1 akun serbaguna).
     - Jika NIK **Sudah Terdaftar**, sistem memblokir form dan memunculkan *alert*: *"NIK sudah terdaftar di akun warga! Silakan cari akun tersebut dan ubah rolenya (Promosi), ATAU kosongkan kolom NIK ini jika ingin tetap membuat Akun Dinas terpisah."* (Status NIK berubah menjadi Opsional).
- **Kesimpulan:** Logika ini menyelesaikan 3 masalah besar sekaligus (Warga Gaptek, Pencegahan NIK Ganda, dan Kebocoran Kuota Gas Subsidi) tanpa mengurangi fleksibilitas operasional pemerintahan desa.

### 8.10 Logika Dinamis Form Kemitraan Pejabat Wilayah (RT/RW)
Formulir Kemitraan memiliki logika *smart-rendering* berbasis status domisili user untuk mencegah tumpang tindih pendaftaran, dengan aturan:
- **Jika Desa BELUM Terdaftar (Belum ada Admin Desa):**
  Sistem menampilkan ajakan pendaftaran desa. Di dalam formulir, pilihan tingkat jabatan mencakup 3 opsi lengkap: **Pemerintah Desa**, **Pengurus RW**, dan **Pengurus RT**.
- **Jika Desa SUDAH Terdaftar (Sudah ada Admin Desa):**
  Tombol Kemitraan *tetap dimunculkan*, namun teks otomatis berubah menjadi instruksi khusus bagi Pejabat RT/RW ("Klaim hak akses wilayah"). Di dalam formulir, opsi "Pemerintah Desa" **dihilangkan secara dinamis**, sehingga warga hanya bisa memilih mendaftar sebagai **Pengurus RW** atau **Pengurus RT**.
Logika ini memastikan alur pendaftaran hierarki kewilayahan berjalan tanpa celah, menggantikan logika lama di mana tombol pendaftaran hilang total jika desa sudah bergabung.

## 9. Rencana Proyek KMIPN & Proposal Skripsi Tim

### 9.1 Fokus Keamanan: M. MUSHLIHUL ARIF (Implementasi Keamanan)
**Peran dalam Tim:** Implementasi Keamanan Sistem (Security Implementation)

**Status Infrastruktur Keamanan SilaDesBeng Saat Ini:**
- Telah menerapkan algoritma **AES-256** untuk kerahasiaan data berat (Confidentiality).
- Telah menerapkan algoritma **ChaCha20** untuk enkripsi arus data (Stream Cipher).
- Telah menerapkan prinsip privasi *Burn After Reading* (penghancuran otomatis file sensitif/KTP pasca verifikasi).
- *QR Code Eksisting:* Sistem telah dapat mencetak QR Code verifikasi pada Bukti Transaksi & Pelaporan, namun masih menggunakan mesin *HMAC (Symmetric Hash)* bawaan framework yang rentan dipalsukan jika `app.key` pada server bocor.

**Rencana Puncak Trisula Keamanan (CIA Triad - Integrity & Non-Repudiation):**
Untuk menyempurnakan keamanan sistem agar berstandar *Enterprise*, M. Mushlihul Arif akan mencabut mesin HMAC lama dan mengimplementasikan **Tanda Tangan Digital (Digital Signature)** murni menggunakan **Kriptografi Asimetris (Algoritma RSA)**.

**Konsep & Alur Implementasi (Invisible Security):**
* Tampilan UI/UX pemindaian QR Code dipertahankan agar tidak merepotkan pengguna.
* Di belakang layar, **Private Key** (Kunci Rahasia) milik sistem digunakan secara otomatis untuk membubuhkan Tanda Tangan Digital secara matematis saat mencetak struk *Bukti Transaksi (Penyewaan/Gas)* dan *Bukti Pelaporan*.
* Masyarakat atau Pengecer Gas mengecek keaslian struk menggunakan kamera HP. Sistem memverifikasi QR Code tersebut menggunakan **Public Key**.
* **Keuntungan Mutlak:** Sekalipun *database* atau server diretas (file .env bocor), peretas secara matematis tetap mustahil bisa memalsukan struk bukti transaksi karena mereka tidak memegang *Private Key* asli.

**Ide Judul Skripsi / Topik KMIPN Final:**
> *"Penerapan Tanda Tangan Digital (Algoritma RSA) Berbasis QR Code untuk Verifikasi Keaslian Bukti Transaksi dan Pelaporan pada Sistem Layanan Daerah Kabupaten Bengkalis"*

**⚠️ STRATEGI EKSEKUSI (PENTING: KMIPN vs SEMPRO/SKRIPSI) ⚠️**
* **Fase KMIPN (Kompetisi):** Implementasi Kriptografi RSA **TIDAK BOLEH** dikerjakan sekarang. QR Code pada sistem dibiarkan menggunakan algoritma bawaan Laravel (*HMAC Symmetric*). Di depan juri KMIPN, sistem sudah terlihat canggih karena QR Code sudah bisa di-scan. Jika ditanya juri, jawab: *"Saat ini menggunakan Symmetric Hashing, untuk Future Work akan di-upgrade ke Asymmetric RSA"*.
* **Fase Sempro & Skripsi (Penelitian):** Mesin HMAC baru dibongkar dan diganti dengan algoritma RSA murni pada fase ini. Hal ini dilakukan untuk **melindungi kebaruan (novelty) skripsi**, agar saat Sempro nanti dosen penguji tidak menolak judul dengan alasan *"Aplikasinya kan sudah jadi dan aman, lalu apa yang mau diteliti lagi?"*.

---
*(Catatan Distribusi Tim KMIPN: 1 Anggota fokus Penetration Testing, 1 Anggota fokus Liveness Detection & OCR KYC, 1 Anggota fokus Integrasi Payment Gateway BUMDes).*

### 9.2 Ide Integrasi AI Keamanan (Tugas Mata Kuliah Kecerdasan Buatan)
Sebagai bagian dari tugas akademik dan wawasan untuk *Future Work*, sistem SilaDesBeng juga dapat mengintegrasikan lapisan **Kecerdasan Buatan (AI) untuk Keamanan Infrastruktur Skala Kabupaten** dengan konsep sebagai berikut:

1. **Perlindungan Jalur Masuk via QR Code (Metode: *Payload Analysis*)**
   - **Alur:** Saat kamera memindai QR Code surat/dokumen desa, AI mencegat isi QR tersebut sebelum diproses sistem kriptografi. AI bertugas mendeteksi sisipan tautan penipuan (*Quishing*) atau skrip perusak (*SQL Injection/XSS*).
   - **Algoritma:** Menggunakan *Machine Learning* seperti **Random Forest** atau **Naive Bayes** untuk mengklasifikasikan (membedakan) teks *payload* yang aman vs. teks serangan siber.

2. **Pencegahan Pencurian Data Pribadi (Metode: *User Behavior Analytics* / UBA)**
   - **Alur:** AI memantau pola dan kebiasaan wajar seluruh Admin Desa. Jika ada Admin yang tiba-tiba mengunduh (*download*) ribuan data KTP/NIK secara masif pada tengah malam, AI mendeteksinya sebagai pencurian data oleh orang dalam (*insider threat*) dan langsung mengunci akun tersebut.
   - **Algoritma:** Menggunakan **Isolation Forest**, yaitu algoritma cerdas pendeteksi anomali (aktivitas yang menyimpang tajam dari kebiasaan normal).

3. **Perlindungan Infrastruktur dan File Konfigurasi (Metode: *AI-based WAF / IPS*)**
   - **Alur:** AI membaca lalu lintas jaringan menuju server kabupaten secara *real-time*. Jika mendeteksi ada pengunjung yang berulang kali mencoba menebak rute folder (*directory traversal*) untuk mencuri *file* .env, AI otomatis memblokir IP peretas secara permanen.
   - **Algoritma:** Menggunakan *Deep Learning* seperti **LSTM (Long Short-Term Memory)** atau **CNN** yang pintar menganalisis pola rentetan jejak aktivitas (*log server*) untuk mengenali serangan otomatis (*bots/brute-force*).
