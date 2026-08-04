# Brain SiladesBeng (Catatan Memori Proyek)

Dokumen ini berfungsi sebagai pusat memori dan dokumentasi (pengganti Obsidian Vault) untuk memastikan rekam jejak pengembangan sistem tetap terjaga secara persisten.

## 1. Identitas Proyek
- **Nama Sistem:** SiladesBeng (Sistem Sinergi Layanan dan Aspirasi Daerah di Kabupaten Bengkalis)
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

  - **[Rencana Mendatang - Peta & Smart Camera Pengaduan] (DITUNDA):**
    - Rencana memecah opsi unggah bukti laporan menjadi "Foto Langsung (Smart Camera dengan deteksi GPS otomatis)" dan "Pilih dari Galeri (Tanpa tarikan GPS otomatis)".
    - Status: **DITUNDA** karena menunggu validasi API Key Google Maps dari anggota tim lain agar tidak merusak fungsi peta yang sudah ada.
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
