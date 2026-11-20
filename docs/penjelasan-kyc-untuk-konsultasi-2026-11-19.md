# Catatan kode SegiDaerah untuk konsultasi teknis

Tanggal pemeriksaan: 19–20 November 2026. Ini dokumentasi implementasi dan batas pembuktiannya, bukan proposal atau hasil eksperimen skripsi final. Acuan adalah source code checkout SegiDaerah yang diperiksa, bukan dugaan berdasarkan ilustrasi AI. Kondisi hosting harus dicocokkan dengan commit, migrasi, konfigurasi PHP, dan scheduler yang benar-benar dipasang.

Judul kerja tetap **Implementasi Algoritma ChaCha20-Poly1305 untuk Pengamanan Berkas Verifikasi Identitas pada Platform SegiDaerah**. AAD adalah bagian pendukung penerapan AEAD, bukan algoritma baru atau pengganti peninjauan identitas oleh admin.

## Koreksi atas penjelasan AI sebelumnya

1. **AAD sudah ada pada source code saat ini**, bukan hanya rancangan. Berkas baru ditulis sebagai versi 2. Data lama tidak otomatis diubah menjadi versi 2.
2. **Jalur web dan API berbeda sebelum enkripsi.** Web mengubah KTP menjadi JPEG kualitas 80. API memakai isi berkas unggahan langsung. Jadi tidak tepat menyebut semua jalur mengenkripsi byte JPEG asli dari ponsel.
3. **Pengajuan web berlangsung bertahap:** unggah KTP dan OCR membuat draf, lalu foto wajah dan koreksi data dikirim untuk menjadikan pengajuan pending. Bukan selalu dua foto diunggah bersamaan.
4. **OCR eksternal menerima gambar yang dapat dibaca:** OCR.space lebih dahulu; Google Gemini dapat dipakai sebagai cadangan jika OCR gagal dan kunci Gemini tersedia. Enkripsi penyimpanan aplikasi tidak berarti gambar tidak pernah keluar dari server.
5. **Sodium bukan satu-satunya mesin.** Source mencoba Sodium IETF, lalu OpenSSL ChaCha20-Poly1305. PHP CLI lokal yang diperiksa: 8.2.35, Sodium tidak tersedia, OpenSSL ChaCha20-Poly1305 tersedia. Pengujian lokal ini menggunakan jalur OpenSSL. Kondisi PHP cPanel belum dibuktikan oleh hasil lokal tersebut.
6. **Bukan hanya admin yang boleh melihat:** pemilik pengajuan juga dapat mengakses dokumennya melalui endpoint media ketika pengajuan masih pending. Admin desa berwenang dapat melihat warga di desa dan turunannya. RT/RW/staf tidak mendapat izin meninjau dokumen warga lain dari mekanisme ini.
7. **Watermark diterapkan pada KTP saat ditampilkan**, bukan pada semua foto sebelum enkripsi. Respons media diubah lagi menjadi JPEG kualitas 85; byte respons browser tidak identik dengan plaintext yang dienkripsi.
8. **Zero-Retention tidak berarti semua data pribadi hilang.** Berkas dibersihkan setelah keputusan; identitas tertentu, hasil keputusan, dan jejak pengajuan tetap disimpan. Draf memiliki pembersihan terjadwal. Penghapusan backup, salinan pengguna, atau data pada penyedia OCR tidak dibuktikan oleh kode ini.
9. **Pengambilan wajah di browser bukan bukti biometrik server.** Ada MediaPipe FaceMesh dan pengambilan beberapa pose, tetapi server menerima data pemindaian dari klien dan keputusan akhir berada pada admin. Jangan mengklaim pencocokan wajah dengan KTP, liveness yang tahan pemalsuan, atau keaslian NIK telah dibuktikan oleh ChaCha20-Poly1305.

## Peta komponen aktual

| Berkas sumber | Tanggung jawab |
| --- | --- |
| `resources/views/kyc/index.blade.php` | Antarmuka unggah KTP, kamera/foto wajah, koreksi hasil OCR, pengiriman pengajuan |
| `app/Http/Controllers/KycController.php` | Jalur web: validasi, JPEG kualitas 80, OCR, draf dan submit milik pengguna |
| `app/Http/Controllers/Api/KycController.php` | Jalur API: berkas unggahan asli, OCR, draf dan submit milik pengguna |
| `app/Services/OcrService.php` | Ekstraksi melalui OCR.space dan cadangan Gemini |
| `app/Services/IdentityDocumentStorage.php` | ID pengajuan, transaksi penyimpanan, nama berkas UUID, versi 2 dan pemanggilan enkripsi |
| `app/Services/IdentityDocumentContext.php` | Menyusun AAD dari record database yang sudah tersimpan |
| `app/Services/FileEncryptionService.php` | Pembungkus enkripsi berkas dan pemeriksaan format/versi saat dekripsi |
| `app/Casts/ChaCha20Encrypted.php` | Memanggil Sodium/OpenSSL, menyiapkan key, nonce, tag dan format penyimpanan; juga dipakai untuk kolom tertentu |
| `app/Http/Controllers/MediaController.php` | Menghubungkan nama berkas dengan record pemilik; hanya dokumen pengajuan pending yang dilayani |
| `app/Services/IdentityMediaService.php` | Pemeriksaan akses, penyusunan ulang AAD, dekripsi, watermark KTP, respons tanpa cache |
| `app/Services/IdentityReviewService.php` | Otorisasi peninjau, persetujuan/penolakan, pembaruan akun dan pembersihan berkas |
| `app/Console/Commands/CleanAbandonedKycDrafts.php` | Pembersihan draf yang tidak diselesaikan |
| `config/filesystems.php` | Disk privat berakar pada `storage/app/private_data` |
| `routes/console.php` | Menjadwalkan pembersihan draf setiap jam |

## Alur web yang benar-benar dijalankan

**Tahap 1 — KTP dan draf.** Warga membuka `/kyc` dan mengirim `ktp_image` ke `POST /kyc/process`. Controller memvalidasi gambar dengan batas 5 MB. Upload sudah berupa byte berkas; tidak perlu membuat representasi biner berbentuk teks untuk mengenkripsinya.

Intervention Image dengan driver GD membaca gambar lalu menghasilkan JPEG kualitas 80. Inilah plaintext yang akan masuk ke enkripsi berkas pada jalur web. Misalnya unggahan awal PNG, hasil sebelum enkripsi tetap JPEG. Pengodean ulang dapat mengubah metadata, ukuran, dan byte, walaupun isi visualnya serupa.

Secara terpisah, `OcrService` menerima path berkas upload asli. Layanan membaca isi berkas tersebut dan mengirimkannya melalui HTTPS ke OCR.space. Bila hasil tidak memiliki NIK atau terjadi kegagalan, kode mencoba Gemini jika dikonfigurasi. Base64 pada permintaan Gemini hanyalah encoding; penyedia tetap dapat membaca gambar. Jangan menyebut tahap ini sebagai pemrosesan seluruhnya lokal. Tidak ada kesimpulan tentang kebijakan retensi penyedia hanya dari source code ini.

Jika NIK tidak berhasil diperoleh, controller tidak membuat draf baru beserta berkas enkripsinya. Jika berhasil, `IdentityDocumentStorage::createDraft()` membuat record KYC milik akun yang sedang login. ID draf diperlukan agar AAD dapat mengikat ciphertext pada pengajuan tertentu.

**Tahap 2 — Enkripsi KTP.** Storage service membuat path acak, misalnya `kyc/ktp/ktp_<UUID>.enc`. Konteks dihitung dari record server, lalu byte JPEG dienkripsi. Berkas ditulis ke disk `private`, sedangkan path dan `ktp_encryption_version = 2` dicatat di database. Kunci tidak ditulis bersama berkas.

**Tahap 3 — Wajah.** Halaman web mempunyai alur kamera menggunakan MediaPipe FaceMesh, rekaman indikator mata/arah wajah, serta beberapa frame yang dapat digabung menjadi kolase. Ada pula jalur foto alternatif. Kolase kamera berukuran 600 × 200 pada fungsi `buildCollage`, dengan tiga panel 200 × 200, dan keluaran JPEG dari canvas. Jika pembuatan kolase gagal, tersedia fallback ke frame utama.

Penting untuk batas penelitian: beberapa perpindahan tahap pose juga dapat terjadi karena timeout, bukan selalu gerakan yang telah terbukti. `face_data` berasal dari klien; server tidak menghitung ulang kecocokan wajah-KTP atau memvalidasi seluruh rangkaian gerakan sebagai bukti liveness. Oleh karena itu label selesai pada antarmuka berarti **pengambilan foto selesai**, bukan identitas telah disetujui.

Saat `POST /kyc/submit`, server mencari `kyc_id` bersama `user_id` akun login. Pengguna tidak bisa menyerahkan ID pengajuan orang lain lalu dianggap pemiliknya. Data URL wajah didekode, dibaca GD, dan diubah lagi menjadi JPEG kualitas 80. Wajah disimpan dengan mekanisme versi 2, tetapi AAD bertipe `face` dan path berbeda dari KTP. Server mewajibkan foto wajah tersedia sebelum pengajuan menjadi pending. Status akun menjadi pending dan notifikasi pengajuan dibuat untuk peninjauan.

**Perbedaan jalur API.** `/api/kyc/process` mengenkripsi `$ktpFile->get()`, yaitu byte berkas unggahan asli. `/api/kyc/submit` menerima berkas `face_image` dan menggunakan isi aslinya juga. API tetap memakai storage service dan AAD yang sama. Selain itu masih ada controller verifikasi kompatibilitas berbasis kolom foto di `users`; implementasi penyimpanannya juga melewati storage service, dengan scope AAD `user-verification`. Jangan menyebut controller kompatibilitas sebagai halaman utama web: rute halaman verifikasi lama mengarah ke `/kyc`.

## Bentuk key, nonce, ciphertext, tag dan AAD

Key diambil dari `config('app.key')`. Jika berformat `base64:...`, bagian setelah prefix didekode. Pada APP_KEY normal sepanjang 32 byte hasil decode, byte tersebut langsung dipakai. Ini bukan key acak baru untuk setiap KTP. Jangan mengganti APP_KEY sembarangan pada instalasi berisi data terenkripsi.

Jika panjang key tidak 32 byte, kode lama melakukan hashing untuk mendapat 32 byte: Sodium generichash jika tersedia, SHA-256 jika tidak. Jalur ini berpotensi menghasilkan key berbeda saat lingkungan berpindah antarbackend. Pengujian interoperabilitas yang memakai key 32 byte tidak membuktikan kompatibilitas semua konfigurasi key nonstandar. Belum ada rotasi key per dokumen atau key ID dalam format ini.

Nonce dibuat dengan `random_bytes(12)` untuk setiap operasi enkripsi. Nonce bukan password, bukan ID warga, dan tidak perlu dirahasiakan. Key yang sama dapat dipakai pada banyak dokumen dengan kebutuhan nonce tidak digunakan ulang. Kode menghasilkan nonce secara acak; tidak ada registry global pemeriksa tabrakan nonce. Ini juga bukan mekanisme pencegahan replay.

Contoh AAD sintetis yang sesuai bentuk kode:

```json
{"domain":"segidaerah.identity","version":2,"scope":"kyc","record_id":"25","owner_id":"103","type":"ktp","path":"kyc/ktp/ktp_UUID-UJI.enc"}
```

Urutan field JSON tetap. Record ID dan owner ID ditulis sebagai string; version berupa angka. Nilai tersebut bukan input bebas dari browser. Saat dekripsi, server menyusun ulang AAD dari database. Perubahan path, pemilik, ID pengajuan, scope, atau jenis dokumen menghasilkan konteks berbeda. AAD diautentikasi, tetapi bukan data rahasia dan tidak ikut dienkripsi sebagai plaintext foto.

Format berkas versi 2 aktual:

```text
$chacha20:v2$ + Base64(nonce 12 byte || ciphertext || tag 16 byte)
```

Format lama memakai prefix `$chacha20$`. Encoding Base64 bukan lapisan keamanan; tujuannya merepresentasikan rangkaian byte. UUID nama berkas juga bukan pengganti pemeriksaan akses.

Ilustrasi `FF D8` pada JPEG adalah penanda awal format, bukan warna piksel dan bukan data NIK. Tidak semua urutan header contoh dari AI harus identik dengan hasil GD. Berkas versi 2 yang disimpan dimulai dengan prefix aplikasi di atas, bukan header JPEG yang siap dibuka penampil foto.

## Apa yang dikerjakan ChaCha20 dan Poly1305?

Pada varian IETF, ChaCha20 memakai key 256 bit, nonce 96 bit, counter blok 32 bit, dan empat word konstanta. State terdiri dari 16 word 32 bit. Operasi penjumlahan modulo 2^32, rotasi bit dan XOR dijalankan dalam 20 round. Hasil blok menghasilkan keystream yang di-XOR dengan plaintext. Dalam konstruksi AEAD, counter 0 menurunkan key sekali pakai Poly1305; enkripsi pesan dimulai pada counter 1. Poly1305 mengautentikasi susunan AAD, padding, ciphertext, dan panjang keduanya, menghasilkan tag 128 bit. Itu detail pustaka algoritma, bukan loop ARX buatan controller PHP. Rujukan: [RFC 8439](https://www.rfc-editor.org/rfc/rfc8439).

SegiDaerah menyerahkan plaintext, key, nonce dan AAD kepada `sodium_crypto_aead_chacha20poly1305_ietf_encrypt()` atau `openssl_encrypt(..., 'chacha20-poly1305', ..., OPENSSL_RAW_DATA, ..., $tag, $aad, 16)`. Pada OpenSSL, tag diterima lewat parameter terpisah lalu digabungkan dalam format berkas. Ketika membuka, kode memisahkan nonce dan tag serta memanggil fungsi dekripsi AEAD. Parameter OpenSSL dijelaskan dalam [manual PHP](https://www.php.net/manual/en/function.openssl-encrypt.php).

## Ketika admin membuka dan memutuskan pengajuan

1. Browser meminta endpoint `/media/secure/ktp/{filename}` atau `/media/secure/face/{filename}` dengan sesi login. Nama berkas divalidasi; record dicari berdasarkan path yang disimpan server.
2. Pengajuan harus pending. Pemilik boleh melihat dokumennya; admin desa berwenang harus berada pada desa/kelurahan yang mencakup domisili pemilik. Akun nonaktif ditolak. Role admin desa yang salah ditempatkan pada kecamatan juga ditolak. Pada jalur kompatibilitas lama berbasis `users`, Super Admin masih mempunyai izin tambahan; ini harus dibedakan dari KYC baru yang ditinjau admin desa.
3. Server mengambil expected version dari metadata database. Versi 2 harus memiliki prefix dan AAD yang sesuai. Bila dibuka sebagai format lama, atau konteks/isi/kunci/tag tidak cocok, pembukaan ditolak.
4. Bila valid, gambar didekode di memori. KTP diberi watermark statis `WatermarkPrivasiFIX.png`, lalu respons dihasilkan sebagai JPEG kualitas 85. Wajah juga menjadi respons JPEG kualitas 85, tetapi tidak diberi watermark oleh service ini. Header respons melarang cache dan memakai `nosniff`.
5. Jika dekripsi atau watermark gagal, endpoint menolak menampilkan gambar; tidak mengirim berkas asli sebagai cadangan. Kegagalan dicatat di log dengan pesan umum. **Poly1305 tidak mengirim notifikasi personal “ada hacker” kepada admin.** Admin melihat dokumen tidak dapat dibuka; penyebabnya bisa manipulasi, kerusakan file atau masalah kunci/konfigurasi.
6. Persetujuan/penolakan memakai transaksi dan pemeriksaan ulang pemilik, status serta wilayah. Persetujuan membutuhkan kedua dokumen terbaca, NIK 16 digit, dan tidak dipakai akun terverifikasi lain berdasarkan indeks HMAC. Itu bukan pemeriksaan ke database Dukcapil. Nama/NIK/gender akun dapat diperbarui dari pengajuan; domisili akun tidak otomatis dipindahkan mengikuti OCR.
7. Hasil keputusan, peninjau, waktu dan catatan dicatat; `face_scan_data` dibersihkan; warga diberi notifikasi. Berkas KTP/wajah kemudian dihapus melalui cleanup.

## Skenario ancaman dan batas perlindungan

| Skenario | Perilaku yang dituju dan batasnya |
| --- | --- |
| Penyerang hanya memperoleh berkas versi 2 | Mendapat ciphertext, nonce dan tag; isi foto tidak terbuka tanpa key. Ini tidak berarti seluruh metadata database tersembunyi. |
| Satu byte ciphertext/tag/nonce diubah | Dekripsi AEAD gagal, gambar tidak ditampilkan. Tag memeriksa autentikasi data, bukan keaslian KTP secara administratif. |
| Ciphertext KTP Andi dipasang pada path pengajuan Budi | AAD pengajuan Budi tidak cocok, sehingga pembukaan ditolak. Ini skenario uji terkontrol, bukan bukti pernah ada peretasan produksi. |
| KTP diganti ciphertext foto wajah | Field `type` dan path berbeda; autentikasi gagal. |
| Browser mengganti ID/filename milik orang lain | Controller dan pemeriksaan wilayah/pemilik harus menolak sebelum dekripsi. AAD tidak menggantikan kontrol IDOR. |
| Akun admin sah dicuri | Permintaan bisa terlihat berwenang; AEAD tidak dapat mengetahui siapa manusia yang mengendalikan sesi. Perlindungan login/sesi dan pembatasan peran tetap penting. |
| APP_KEY dan berkas ikut dicuri | Dengan konteks yang diketahui, penyerang berpotensi mendekripsi dan membuat ciphertext/tag valid. Enkripsi tidak melindungi dari pengambilalihan server beserta kunci. |
| Seluruh record, versi, konteks dan berkas bisa diubah | Asumsi database sebagai sumber konteks terpercaya sudah rusak. AAD tidak memberi jaminan menyeluruh pada skenario ini. |
| Admin mengambil tangkapan layar | Watermark dan header tanpa cache tidak mencegah kamera, tangkapan layar, atau salinan di perangkat penerima. |

## Retensi dan data yang masih tersimpan

Versi 2 dipakai untuk berkas baru. Reader tetap mendukung data versi 1, termasuk jalur kompatibilitas gambar lama tanpa prefix. Karena itu klaim “semua dokumen lama sudah memakai AAD” atau “semua foto lama pasti terenkripsi” memerlukan inventaris data nyata, bukan hanya membaca fungsi save terbaru.

Cleanup setelah peninjauan mencatat niat penghapusan pada `identity_cleanup_queue` dalam transaksi keputusan, lalu mencoba menghapus berkas dan mengosongkan path bila berhasil. Jika penghapusan gagal, path tetap terlacak; keputusan verifikasi sudah tersimpan. Perintah `kyc:clean-files` dijadwalkan setiap sepuluh menit untuk mencoba ulang antrean dan memulihkan referensi pengajuan selesai yang tertinggal dari versi lama. Scheduler hosting harus aktif. Ini tetap bukan jaminan penghapusan fisik permanen tanpa kemungkinan gagal.

`kyc:clean-drafts` berjalan setiap jam bila scheduler hosting aktif, dengan usia minimum default dua jam sejak pembaruan terakhir draf. Perintah mengunci dan memeriksa kembali status sebelum menghapus record. Niat penghapusan berkas dicatat dalam transaksi yang sama, sehingga `delete()` gagal tidak menghilangkan path yang perlu dicoba ulang. Penggantian berkas dan pensiun pengajuan sebelumnya memakai antrean yang sama. Pengajuan pending tetap menunggu peninjauan. Berkas yatim yang sudah kehilangan semua referensi sebelum perbaikan ini tidak otomatis ditemukan oleh antrean baru.

Source ini tidak membuktikan pembersihan secure-erase pada media penyimpanan, backup hosting, log eksternal, file sementara PHP/OS, OCR.space/Gemini, atau perangkat warga/admin. Kebijakan aplikasi lebih tepat dijelaskan sebagai **pengurangan retensi berkas verifikasi setelah tujuan pemeriksaan selesai**, dengan batas tersebut.

Kolom `users.name`, `users.nik`, `users.phone`, dan `users.address` menggunakan encrypted cast. Pada KYC, `nik_from_ocr` dan `name_from_ocr` menggunakan cast; beberapa kolom OCR lainnya dan `face_scan_data` tidak memakai cast yang sama. Indeks HMAC membantu pencarian/keunikan data tertentu, bukan cara membuka kembali foto. Kolom terenkripsi tetap dapat didekripsi saat aplikasi membutuhkannya. Jangan menyebut seluruh identitas hanya disimpan sebagai hash atau “tidak perlu pernah didekripsi”.

## Cara membuktikannya untuk penelitian

- **Ketepatan:** bandingkan byte plaintext sebelum enkripsi dengan hasil dekripsi. Web memakai JPEG hasil konversi kualitas 80 sebagai pembanding; API memakai byte unggahan asli. Jangan menggunakan respons JPEG85 berwatermark sebagai pembanding byte.
- **Integritas/autentikasi:** modifikasi ciphertext, nonce, tag, gunakan key salah; catat penolakan. Uji tidak hanya fungsi pembantu, tetapi juga endpoint media.
- **AAD:** pertukarkan dokumen antar-pengajuan/pemilik/jenis/path; uji konteks benar tetap bisa dibuka. Uji metadata versi 2 menolak downgrade format.
- **Hak akses:** pemilik sendiri, warga lain, admin desa sesuai/lain, RT/RW, staf, Super Admin, akun nonaktif, dan akun salah penempatan wilayah. Pisahkan KYC baru dari jalur kompatibilitas.
- **Retensi pendukung:** setujui, tolak, tinggalkan draf, dan simulasi kegagalan penghapusan. Catat apa yang hilang serta metadata yang memang tetap ada.
- **Kinerja:** tetapkan ukuran/format gambar sintetis, perangkat, backend, jumlah pengulangan, waktu enkripsi dan dekripsi, penggunaan memori, ukuran plaintext/ciphertext. Pisahkan waktu OCR, konversi JPEG, jaringan dan watermark dari waktu algoritma.
- **Kompatibilitas:** uji berkas lama tanpa AAD, berkas baru, serta perpindahan backend dengan key standar 32 byte. Jangan mengklaim semua varian key lama aman tanpa pengujian.

Tes pengembangan terkait berada pada `tests/Unit/IdentityDocumentEncryptionTest.php`, `tests/Feature/IdentityDocumentAadTest.php`, dan `tests/Feature/IdentityReviewAuditTest.php`. OCR pada tes menggunakan respons tiruan; pengujian tersebut tidak mengirim identitas asli ke penyedia. Tes web/API juga memeriksa byte KTP hasil dekripsi terhadap input yang benar untuk masing-masing jalur.

Jumlah keseluruhan tes aplikasi bukan jumlah eksperimen khusus skripsi. Simpan baseline, commit, konfigurasi lingkungan yang tidak berisi rahasia, protokol pengujian, dan hasil mentah secara jujur. Belum ada dasar untuk menyatakan benchmark formal, audit keamanan menyeluruh, atau efektivitas biometrik telah selesai hanya karena tes regresi aplikasi lulus.
