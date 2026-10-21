# Audit keamanan SegiDaerah — 21 Oktober 2026

Perbaikan ini melanjutkan audit fitur/peta/UI pada commit `e8ed855`. Pemeriksaan dilakukan pada kode sumber dan pengujian lokal dengan data sintetis SQLite. Tidak ada pengujian DDoS, malware, atau percobaan membaca data pribadi warga di produksi. Hasil ini bukan sertifikat bahwa seluruh aplikasi dan hosting kebal serangan.

## Temuan yang diperbaiki

- **Autentikasi Google API:** email/google_id kiriman klien sebelumnya cukup untuk login. Kini wajib `id_token` Google dengan verifikasi tanda tangan, audience, issuer, dan email terverifikasi. Tautan akun yang berbeda ditolak. Profil admin tidak boleh berpindah wilayah melalui parameter login. Akun nonaktif ditolak; reset password API mencabut token API/remember-me.
- **Chat unit dan pasar:** token tamu tidak bisa membuka chat akun lain; pengguna login hanya mengakses sesi miliknya. Token tamu baru dihasilkan server dengan acak kriptografis, 64 karakter. Klien web menyimpan token dari respons. Data pengirim dibatasi ke id/nama/avatar. Akses petugas dibatasi wilayah dan izin unit.
- **Identitas dan warga:** foto KTP/wajah pending hanya dapat diakses pemilik atau peninjau yang berwenang di wilayahnya. Salah tipe berkas, dokumen selesai, dan kegagalan dekripsi/watermark tidak mengembalikan dokumen asli. API daftar/detail warga RT/RW dibatasi wilayah dan nomor RT/RW, NIK disensor. RT/RW tidak lagi dapat menyetujui/menolak KYC lewat API; peninjauan dilakukan melalui dashboard admin desa/kecamatan.
- **Laporan warga:** API daftar admin/forward, detail, media, dan ekspor memakai pembatasan pemilik/role/wilayah. Akun warga biasa tidak dapat menjadi admin lewat endpoint forward. Staf tanpa izin pelaporan dan petugas tanpa penempatan wilayah ditolak.
- **Berkas sensitif:** receipt, bukti pembayaran/pengiriman, surat pengantar, lampiran laporan, keluhan pasar, tiket, dokumen kemitraan, dan laporan manual diberi pemeriksaan akses. Aturan Apache mengarahkan URL storage sensitif ke Laravel walaupun berkas ada melalui symlink. Traversal, double decoding, fallback sumber skrip, berkas tersembunyi, dan backup dibatasi. Fallback tidak lagi menyalin berkas otomatis ke document root.
- **Unggahan/XSS:** ekstensi kiriman klien tidak lagi dijadikan bukti tipe berkas; validator image menolak SVG/HTML tersamar dan gambar dengan dimensi berlebihan. Escaping atribut chat diperbaiki; tautan detail produk chat admin hanya menerima HTTP(S) pada origin aplikasi untuk menghindari JavaScript URL dan tautan eksternal dari kiriman chat.
- **Pemesanan:** harga paket alat dihitung server per barang; fasilitas komersial berbayar tidak lagi selalu nol. Pemasukan API alat/mobil/fasilitas dicatat di ledger. Pesanan pending belum mengalokasikan stok, konsisten dengan web; admin mengalokasikan saat menyetujui. Pembatalan pending tidak menambah stok. Perubahan status dikunci dalam transaksi; penolakan pesanan selesai, pengembalian pending, dan pemrosesan berulang dibatasi. Fasilitas mengurangi/mengembalikan sesuai quantity. Pengembalian stok menggunakan increment atomik.
- **Pembatasan permintaan:** throttle login/OTP/reset, API, pengiriman chat/chatbot ditambahkan. Ini hanya lapisan aplikasi, bukan penangkal DDoS volumetrik.
- **Dependensi:** Composer audit tidak menemukan advisori. Npm audit awal menemukan lima paket terdampak; lockfile diperbarui dalam rentang versi yang diizinkan (termasuk Vite 7.3.7). Npm audit ulang melaporkan nol kerentanan.

## Kebersihan repository dan sesi

Skrip sementara fix_audit/fix_polish/fix_workflows sudah dikeluarkan dari workspace; tidak dilacak Git. Berkas build sementara yang diabaikan Git tidak ikut push. Tes regresi dan laporan ini sengaja disimpan agar temuan dapat diperiksa ulang.

Delapan berkas `.bak`/`.backup` dan 17 berkas sesi yang sebelumnya terlacak dikeluarkan dari indeks Git. Aturan ignore ditambahkan. Salinan lokal cadangan tetap ada. **Ini tidak menghapus data dari riwayat Git lama.** Pengelola perlu memastikan akses repository sesuai kebijakan data; pembersihan riwayat tidak dilakukan dalam perubahan ini.

Command `php artisan security:invalidate-auth --once` ditambahkan ke deployment. Pada deploy pertama, semua sesi, remember-me, dan token API lama dicabut; semua pengguna perlu login ulang. Penanda pada disk lokal mencegah logout berulang pada deploy berikutnya. Driver sesi file/database didukung; driver lain perlu penanganan hosting. APP_KEY tidak diubah karena dibutuhkan untuk membaca data terenkripsi lama.

## Validasi

- PHPUnit: **54 tes, 200 assertions, lulus**, menggunakan SQLite dan storage palsu; tidak memakai database produksi.
- JavaScript: **6 tes lulus**, mencakup peta, escaping chat, tautan berbahaya, dan penyimpanan token tamu.
- Pemeriksaan sintaks PHP seluruh file PHP yang berubah: lulus.
- Cache route dan kompilasi Blade: lulus.
- Build produksi Vite: lulus. Output build disertakan apabila berubah.
- Composer dan npm audit: nol advisori yang dilaporkan saat pemeriksaan; tidak menjamin bebas kerentanan yang belum diketahui.

## Deployment dan kompatibilitas

1. cPanel Git Version Control: **Update from Remote**, lalu **Deploy HEAD Commit**. Pull saja belum menyalin `.htaccess` dan aset ke document root. Tidak ada migrasi baru pada perubahan ini; database harus sudah memakai migrasi proyek yang ada.
2. Perhatikan keberhasilan command pencabutan sesi dan cache di log deployment. Jangan mengubah APP_KEY. Pengguna web/mobile akan login ulang.
3. **Klien Flutter tidak ada di repository ini.** Login Google mobile harus mengirim `id_token` dengan server client ID sesuai konfigurasi Google server. Format lama email/google_id sengaja ditolak. Login password tetap tersedia.
4. Gambar dokumen/bukti privat dari mobile harus menyertakan bearer token pada permintaan gambar. Paket alat API kini menerima `items: [{barang_id, quantity}]` dan mengembalikan `data.bookings` serta total server. `jenis_acara=pribadi` fasilitas dipetakan ke `komersial` agar kompatibel dengan pilihan lama.
5. Token chat tamu lama yang lemah tidak lagi berlaku; tamu akan mendapat sesi baru. Riwayat akun tetap dibatasi pemiliknya.
6. **Rekonsiliasi stok pesanan API pending lama:** versi lama sudah mengurangi stok saat pembuatan, sedangkan web tidak. Sistem tidak mempunyai penanda asal reservasi yang dapat dipercaya. Periksa stok fisik dan pesanan aktif sebelum menyetujui antrean lama; jangan mengoreksi seluruh pending secara otomatis.
7. Setelah deploy, uji login, satu transaksi percobaan, chat, peta, serta akses media menggunakan dua akun uji di wilayah berbeda. URL bukti privat harus ditolak ketika logout. Perubahan belum diverifikasi pada hosting setelah deploy pengguna.

## Batas dan pekerjaan operasional

- Perlindungan static storage mengandalkan `.htaccess` Apache/LiteSpeed yang berlaku pada document root. Nginx, CDN, alias storage, dan salinan dokumen pada URL lain harus dikonfigurasi agar tidak melewati Laravel; tidak diverifikasi dari lingkungan ini.
- Masking NIK di UI tidak sama dengan menghapus data database. KYC utama menghapus foto ketika keputusan selesai; alur verifikasi legacy masih dapat menyimpan berkas terenkripsi yang aksesnya sudah ditutup setelah selesai. Retensi draft/pending/backup dan penghapusan historis perlu kebijakan serta inventarisasi data hosting.
- SQLi: pembatasan query/parameter pada jalur yang ditinjau diperiksa, tetapi belum ada pentest penuh setiap endpoint. CSP lama masih memakai unsafe-inline/unsafe-eval untuk kompatibilitas antarmuka; migrasi nonce dan pengurangan skrip inline perlu pekerjaan lanjutan.
- DDoS besar, malware/ransomware pada server, dan phishing akun memerlukan kontrol hosting/WAF, patch server, pembatasan akses admin, serta backup yang dapat dipulihkan. Aplikasi saja tidak dapat menjamin perlindungan tersebut. Tidak ada pemindaian antivirus dokumen atau uji pemulihan backup hosting pada audit ini.
- Integrasi eksternal Google/OCR/WhatsApp, aliran bank nyata, pengujian konkurensi MySQL, dan seluruh tampilan per role belum diuji end-to-end dengan kredensial produksi.

Referensi: [Google backend authentication](https://developers.google.com/identity/sign-in/web/backend-auth), [OWASP IDOR](https://cheatsheetseries.owasp.org/cheatsheets/Insecure_Direct_Object_Reference_Prevention_Cheat_Sheet.html), [OWASP file upload](https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html).
