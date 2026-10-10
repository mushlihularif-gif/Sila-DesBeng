# PANDUAN DAN CATATAN PERTAHANAN PROPOSAL SKRIPSI
## Rancang Bangun Mekanisme Keamanan Bukti Digital Berbasis QR Code Menggunakan HMAC-SHA256 pada Platform SegiDaerah

**Penyusun:** M. Mushlihul 'Arif  
**NIM:** 6404240051  
**Program Studi:** D4 Keamanan Sistem Informasi (Kelas 5B)  
**Jurusan:** Teknik Informatika, Politeknik Negeri Bengkalis  
**Tahun:** 2026  
**Bidang Peminatan:** Keamanan Aplikasi  
**Sub-Minat:** Secure coding & SDLC aman  
**Tema / Topik Spesifik:** Rancang Bangun Mekanisme Keamanan Bukti Digital Berbasis QR Code Menggunakan HMAC-SHA256 pada Platform SegiDaerah (14 kata)  

---

## 1. Identitas Judul dan Rumusan Masalah

### Judul Skripsi (14 Kata - Memenuhi Syarat Maksimal 15 Kata)
> **"Rancang Bangun Mekanisme Keamanan Bukti Digital Berbasis QR Code Menggunakan HMAC-SHA256 pada Platform SegiDaerah"**

*Nomenklatur Resmi Sistem:*
- **SegiDaerah:** **Platform Sinergi Layanan dan Aspirasi Daerah** (sebelumnya dikembangkan dengan nama awal SiladesBeng oleh Tim Gen Hello World, Politeknik Negeri Bengkalis untuk KMIPN VIII 2026 Kategori E-Government).

### Rumusan Masalah (Rancang, Uji, Implementasikan)
1. **Bagaimana merancang mekanisme keamanan bukti digital berbasis QR Code menggunakan HMAC-SHA256 pada Platform SegiDaerah?**
    - *Fokus:* Menentukan data bukti (payload) yang ditandatangani, cara server membangkitkan dan memverifikasi HMAC, serta struktur token yang dibawa oleh QR Code.
2. **Bagaimana menguji kemampuan HMAC-SHA256 dalam menjaga integritas, otentikasi data, dan mendeteksi upaya manipulasi (anti-tampering) pada bukti digital SegiDaerah?**
    - *Fokus:* Membandingkan baseline tanpa proteksi HMAC dengan mekanisme HMAC, melakukan pengujian manipulasi parameter (tampering test) dengan data dummy, serta membuktikan bahwa perubahan data yang ditandatangani berhasil ditolak oleh server.
3. **Bagaimana mengimplementasikan proses penerbitan dan verifikasi QR Code pada bukti transaksi layanan serta bukti pelaporan warga di Platform SegiDaerah?**
    - *Fokus:* Menerapkan penerbitan QR Code otomatis pada kuitansi/dokumen bukti dan halaman verifikasi publik tanpa login (`hash_equals`) yang mengambil data resmi dari database.

---

## 2. Ruang Lingkup dan Alasan Pemilihan

Platform SegiDaerah (yang dikembangkan dari basis sistem SiladesBeng) sudah dibuat oleh tim dan sebagian besar fiturnya telah berjalan. Penelitian ini **tidak membangun ulang seluruh aplikasi**. Bagian yang menjadi fokus rancang bangun adalah mekanisme keamanan bukti: data apa yang diberi HMAC, bagaimana tanda tangan dibuat dan diverifikasi, bagaimana QR mengarah ke verifikasi, dan bagaimana sistem merespons perubahan.

Batasi jenis bukti yang diteliti bersama dosen pembimbing. Pilihan awal yang paling sederhana adalah satu jenis bukti transaksi. Bukti laporan warga dapat ditambahkan jika waktu dan kebutuhan penelitian mendukungnya. Jangan menyebut semua jenis bukti telah tercakup sebelum implementasi dan pengujiannya benar-benar dilakukan.

HMAC-SHA256 bukan enkripsi dan tidak menyembunyikan data. Fungsinya adalah membuat serta memeriksa kode autentikasi berbasis kunci rahasia. QR hanya menjadi cara praktis untuk membuka halaman verifikasi.

---

## 3. Bank Pertanyaan dan Jawaban Penguji

### Pertanyaan 1: "Lah, QR Code kan sudah ada dan tinggal generate pakai library, terus kamu bikin apanya?"
**Jawaban Mahasiswa:**
> *"Izin menjawab Bapak/Ibu Penguji. Betul sekali, QR Code pada penelitian ini hanya berfungsi sebagai media fisik atau wadah visual pembawa data (data carrier) agar mudah dipindai oleh kamera ponsel.*
> 
> *Yang saya rancang dan uji bukan QR Code-nya, melainkan mekanisme pemeriksaan integritas dan autentikasi data menggunakan HMAC-SHA256. Saya menentukan data yang ditandatangani, menerapkan proses pembuatan serta verifikasi HMAC, lalu menguji apakah perubahan pada data yang ditandatangani terdeteksi. QR menjadi media untuk membuka halaman verifikasi."*

---

### Pertanyaan 2: "Bukannya QR biasa kalau dipindai sudah membuka website dan menampilkan transaksi?"
**Jawaban Mahasiswa:**
> *"Iya, halaman verifikasi dapat menampilkan transaksi resmi dari database. QR biasa berfungsi membuka halaman itu. HMAC menambahkan pemeriksaan apakah data yang dipilih sebagai isi bukti masih cocok dengan data yang ditandatangani server. Pemindaian saja tidak membuktikan bahwa setiap teks pada salinan cetak tidak pernah diubah.*
> 
> *Simulasi lokal dengan data dummy, langkah demi langkah:*
> 1. *Siapkan satu bukti dummy UJI-001 dengan nominal Rp100.000. Buat baseline sederhana yang menerima `id` dan `nominal` dari URL tanpa signature lalu menampilkan nilai tersebut. Ini hanya untuk pengujian lokal.*
> 2. *Buat QR dari URL baseline, misalnya `http://localhost/uji?pesanan=UJI-001&nominal=100000`, lalu pindai. Catat bahwa halaman menampilkan Rp100.000.*
> 3. *Salin URL itu, ubah `nominal=100000` menjadi `nominal=1000000`, buat QR kedua dari URL hasil ubahan, lalu pindai. Baseline yang mempercayai parameter URL akan menampilkan Rp1.000.000.*
> 4. *Pada versi yang dilindungi, server membuat HMAC dari data kanonis yang mencakup ID pesanan dan nominal. Sertakan signature pada QR. Ubah nominal pada URL/QR tetapi biarkan signature lama; saat dipindai, server menghitung ulang HMAC dan menolak data karena signature tidak cocok.*
> 5. *Catat hasil kedua skenario. Jangan gunakan endpoint produksi untuk baseline ini.*
> 
> *Catatan penting: endpoint SegiDaerah yang ada sekarang tidak mempercayai parameter nominal dari URL. HMAC transaksi yang berjalan saat ini dibuat dari ID dan nomor pesanan; halaman validasi mengambil rincian resmi dari database. Jadi contoh perubahan nominal di atas adalah simulasi terkontrol untuk rancangan payload penelitian, bukan klaim bahwa endpoint yang sekarang menerima nominal dari QR. Perubahan pada cetakan tanpa mengubah QR ditangani dengan membandingkan cetakan terhadap data resmi yang ditampilkan saat QR dipindai."*

**Simulasi hanya dilakukan di lingkungan lokal/testing dengan data dummy. Jangan menonaktifkan atau mengubah mekanisme keamanan pada website yang dipakai warga.**

---

### Pertanyaan 3: "Apakah ini tanda tangan basah yang discan lalu dijadikan QR Code?"
**Jawaban Mahasiswa:**
> *"Bukan, Bapak/Ibu. Penelitian ini tidak mengubah gambar tanda tangan menjadi QR. QR membawa tautan atau data verifikasi. HMAC dihitung oleh server menggunakan kunci rahasia untuk memeriksa integritas dan autentikasi data. HMAC bukan tanda tangan elektronik tersertifikasi dan bukan enkripsi."*

---

### Pertanyaan 4: "Sistem SegiDaerah ini kan dipakai di lingkungan daerah/desa, bagaimana membuktikan dokumen diterbitkan oleh pihak/sistem yang sah?"
**Jawaban Mahasiswa:**
> *"Izin menjawab. Implementasi saat ini perlu diperiksa cakupannya. Pada rute validasi transaksi, HMAC saat ini dihitung dari ID dan nomor pesanan. Karena itu saya tidak akan mengklaim nominal, wilayah, atau akun petugas ikut dilindungi sebelum payload HMAC tersebut dirancang dan diuji untuk mengikat data-data itu.*
>
> *Data yang dipilih untuk payload final akan ditetapkan dalam desain penelitian. Verifikasi mengambil rekaman resmi di server, menghitung ulang HMAC, lalu membandingkan signature dengan fungsi pembandingan aman. Informasi yang tidak masuk payload HMAC tidak boleh disebut terlindungi oleh signature tersebut."*

---

### Pertanyaan 5: "Di jurnal rujukan pakainya RSA, AES, atau SHA-256 murni. Kenapa kamu pakai HMAC-SHA256? Apakah tidak masalah berbeda?"
**Jawaban Mahasiswa (Poin Kebaruan / State of the Art):**
> *"Izin menjawab Bapak/Ibu. Pemilihan HMAC-SHA256 perlu dijelaskan berdasarkan kebutuhan dan hasil kajian pustaka, bukan hanya karena berbeda dari algoritma pada penelitian lain. SHA-256 biasa tidak menggunakan kunci rahasia sehingga siapa pun yang mengetahui datanya dapat menghitung hash baru. HMAC menggunakan kunci rahasia bersama untuk memeriksa integritas data dan autentikasi pihak yang mengetahui kunci. RSA memiliki model kunci dan tujuan yang berbeda. Saya akan membandingkan penelitian terdahulu berdasarkan konteks, tujuan, metode, dan hasil yang benar-benar dilaporkan.*
> 
> *Hasil penelitian ini akan diukur pada implementasi SegiDaerah. Saya tidak menyatakan HMAC sebagai solusi yang selalu paling baik, dan tidak menyebut angka performa sebelum pengukuran dilakukan. Keterbatasan pentingnya adalah kunci rahasia harus dijaga server; siapa pun yang memperoleh kunci tersebut dapat membuat HMAC yang valid."*

---

### Analogi Sederhana untuk Menjelaskan ke Penguji:
- **QR Code** itu hanyalah **kertas amplop surat**.
- **HMAC-SHA256** adalah **Cap Lilin Segel dengan Cincin Khusus Raja**.
- Siapapun bisa membeli amplop (membuat QR Code), tetapi hanya pemegang cincin resmi (Secret Key server) yang bisa membuat segel lilin yang sah. Jika amplop dibuka dan isinya diubah di jalan, segel lilin rusak dan akan langsung ditolak saat diperiksa di gerbang pos pemeriksaan.

---

## 4. Kandidat Jurnal Rujukan untuk Diverifikasi

Daftar berikut adalah kandidat awal, bukan klaim bahwa semua jurnal telah diverifikasi status akreditasi, metadata, dan relevansinya. Periksa setiap artikel melalui laman jurnal atau DOI dan cocokkan dengan topik HMAC, autentikasi dokumen, QR, serta pengujian keamanan sebelum dimasukkan ke proposal.

| No | Penulis & Tahun | Judul Artikel | Nama Jurnal & Akreditasi | Relevansi Terhadap Skripsi Anda |
|---|---|---|---|---|
| 1 | **Fitri Nuraeni, Dede Kurniadi, Diva Nuratnika Rahayu (2024)** | Implementation of RSA and AES-128 Super Encryption on QR-Code Based Digital Signature Schemes for Document Legalization | *Jurnal Teknik Informatika (JUTIF)*, Vol. 5, No. 3, hal. 675-684. DOI: `10.52436/1.jutif.2024.5.3.1426` | Bukti ilmiah penerapan QR Code sebagai wadah kriptografi untuk legalitas dokumen digital. |
| 2 | **Fikri Fahru Roji, Ridwan Setiawan, dkk. (2023)** | Implementasi Tanda Tangan Digital pada Pembuatan Surat Keterangan dengan Metodologi Scrum | *Jurnal Algoritma*, Vol. 20, No. 1. | Rujukan alur perancangan software engineering dalam siklus penerbitan surat dan verifikasi QR Code. |
| 3 | **Yusuf Anshori, Erwin Dodu, Dewa Made (2019)** | Implementasi Algoritma Kriptografi Rivest Shamir Adleman (RSA) pada Tanda Tangan Digital | *Techno.COM*, Vol. 18, No. 4. | Landasan teori mengenai pengujian otentikasi dokumen dan pembuktian keaslian berkas digital. |
| 4 | **Juniar Hutagalung, Puji Sari, Sarah Juliana (2023)** | Keamanan Data Menggunakan Secure Hashing Algorithm (SHA)-256 dan Rivest Shamir Adleman (RSA) pada Digital Signature | *Jurnal Teknologi Informasi dan Ilmu Komputer (JTIIK)*, SINTA 2, Vol. 10. | Landasan teori fungsi hash SHA-256 dalam mendeteksi perubahan/manipulasi isi berkas dokumen. |
| 5 | **Antika Lorien, Theophilus Wellem (2021)** | Implementasi Sistem Otentikasi Dokumen Berbasis Quick Response (QR) Code dan Digital Signature | *Jurnal RESTI (Rekayasa Sistem dan Teknologi Informasi)*, SINTA 2, Vol. 5, No. 4, hal. 663-671. DOI: `10.29207/resti.v5i4.3316` | Rujukan utama rancang bangun sistem otentikasi dokumen publik menggunakan pemindaian QR Code. |

---

## 5. Data Referensi Siap Salin untuk Mendeley

Jika membutuhkan pengisian manual di Mendeley:

```text
Type: Journal Article
Title: Implementasi Sistem Otentikasi Dokumen Berbasis Quick Response (QR) Code dan Digital Signature
Authors: 
Lorien, Antika
Wellem, Theophilus
Journal: Jurnal RESTI (Rekayasa Sistem dan Teknologi Informasi)
Year: 2021
Volume: 5
Issue: 4
Pages: 663-671
DOI: 10.29207/resti.v5i4.3316
```

---

## 6. Bukti Kesiapan Teknis pada Kode Sumber SegiDaerah (SiladesBeng)

Sebagian mekanisme terkait sudah ada pada proyek SegiDaerah (direktori lokal: `D:\laragon\www\SiladesBeng`). Kondisi berikut adalah catatan awal dan harus diperiksa lagi pada kode terbaru sebelum dijadikan klaim proposal:

1. **Pembangkitan Segel Kriptografi (Penerbitan Token HMAC):**
    - Bukti Laporan Warga: `app/Http/Controllers/LaporanController.php`; payload saat ini menggunakan ID dan waktu pembuatan.
    - Kuitansi Transaksi Layanan: `app/Services/ReceiptGeneratorService.php`; payload saat ini menggunakan ID dan nomor pesanan.
    - Kunci yang digunakan: `config('app.key')`. Untuk rancangan penelitian, pertimbangkan kunci khusus HMAC yang disimpan sebagai secret konfigurasi terpisah dan jangan pernah menaruh secret di QR atau kode klien.
2. **Rute Verifikasi Publik Tanpa Login:**
   - Validasi Laporan: `routes/web.php` -> rute `/validasi/laporan/{id}`
   - Validasi Transaksi: `routes/web.php` -> rute `/validasi/transaksi/{type}/{id}`
3. **Pencegahan Timing Attack:**
   - Pencocokan token menggunakan fungsi `hash_equals($expectedToken, $token)`
4. **Antarmuka Verifikasi Visual (Hasil Scan):**
   - Halaman Validasi Laporan: `resources/views/user/laporan/validasi.blade.php` (atau `laporan.blade.php`)
   - Halaman Validasi Transaksi: `resources/views/user/transaksi/validasi.blade.php` (atau `transaksi.blade.php`)
    - Periksa label status dan perilaku halaman aktual sebelum mendeskripsikan hasil verifikasi sebagai "termodifikasi" atau "palsu". Jika server hanya membandingkan data dari database, jelaskan bahwa halaman menampilkan data resmi untuk dibandingkan dengan salinan bukti.

---

*Dokumen ini disusun sebagai catatan persiapan Seminar Proposal dan Sidang Skripsi M. Mushlihul 'Arif.*
