# Pemeriksaan PHP2Shell dan Laravel — 26 Oktober 2026

## Kesimpulan

PHP2Shell nyata. Perbaikan keamanan aplikasi pada commit `9161503` tidak memperbarui executable PHP di hosting. Nol temuan Composer hanya menggambarkan paket Composer yang diaudit, bukan keamanan PHP, sistem operasi, atau semua kode aplikasi.

- Laravel lokal dan composer.lock: **12.69.3**. Advisori TokenGuard (GHSA-f9x3-7vq9-cph6) dan Unauthorized Data Destruction (GHSA-5wcm-3v2w-8362/CVE-2026-114508) diperbaiki pada cabang 12 mulai **12.68.0**. XSS Debug Page (GHSA-jh5r-qr3c-85q8/CVE-2026-102279) diperbaiki mulai **12.69.0**. Versi proyek sudah melewati ketiganya. Konfigurasi auth proyek memakai session dan Sanctum, bukan guard driver token lama.
- PHP lokal awal: CLI **8.2.31**, Laragon/Apache dikonfigurasi ke **8.3.25**. Keduanya di bawah rilis PHP2Shell yang diperbaiki. Apache/Laragon tidak sedang berjalan ketika diperiksa.
- Paket Windows resmi **8.2.35-1** dan **8.3.36-1** dipasang berdampingan dengan versi lama di `D:/laragon/bin/php`. SHA256 diverifikasi terhadap metadata PHP resmi sebelum ekstraksi. Rebuild `-1` juga membawa Brotli 1.2.1 untuk CVE-2026-111497. PHP tetap melaporkan versi tanpa akhiran `-1`; versi Brotli diperiksa melalui cURL.
- Konfigurasi php.ini baru menggunakan `session.upload_progress.enabled=Off`. Ini diterapkan pada konfigurasi startup, bukan melalui kode Laravel atau .user.ini.
- Halaman publik online memberi HTTP 200 dan header `Server: LiteSpeed`, tanpa X-Powered-By. **Tidak membuktikan versi/patch PHP produksi.** Belum ada akses atau konfirmasi paket dari hosting; status PHP produksi tetap belum terverifikasi.

## Apa yang diperbaiki PHP

CVE-2026-109618 dan CVE-2026-109619 diperbaiki upstream pada PHP **8.2.35 / 8.3.36 / 8.4.27 / 8.5.12**. Penanganan request/cleanup dapat terjadi sebelum kode aplikasi, sehingga middleware Laravel, login, rate limiter aplikasi, dan `php artisan down` bukan pengganti patch runtime.

Advisori tambahan **CVE-2026-115560** memerlukan kombinasi konfigurasi dan perubahan native session serialize handler saat runtime. Tidak ditemukan penggantian handler tersebut pada kode aplikasi yang diperiksa. PHP tetap menyarankan menonaktifkan session upload progress sebelum request diproses sampai rilis perbaikan berikutnya. Pengaturan harus berlaku di php.ini/server dan worker dimulai ulang; FPM .user.ini/ini_set saja tidak cukup untuk mitigasi PHP2Shell.

## Bukti dari hosting yang masih diperlukan

Berkas deployment memakai `/opt/alt/php82/usr/bin/php`, tetapi itu executable CLI; worker LiteSpeed domain dapat memakai versi lain. Paket CloudLinux dengan nomor lebih rendah dapat sudah menerima backport. Minta penyedia hosting membuktikan:

1. Domain `siladesbeng.inovasia.site` memakai executable/build PHP yang mana, termasuk package release dan changelog yang mencakup kedua CVE PHP2Shell. CloudLinux mencantumkan alt-php82 **8.2.34-7 atau 8.2.35-1** sebagai build perbaikan; jangan menilai hanya angka PHP 8.2.
2. Worker PHP/LiteSpeed sudah direstart setelah pembaruan. Memperbarui paket di disk saja belum membuktikan worker lama berhenti.
3. `session.upload_progress.enabled=0` diterapkan di konfigurasi startup worker web, bukan hanya CLI atau .user.ini. APP_DEBUG produksi harus false.
4. PHP yang digunakan cron/queue/CLI juga ditinjau. Laravel terpasang di vendor produksi harus sesuai composer.lock; pull Git tidak memperbarui vendor yang diabaikan Git.

Teks untuk tiket hosting (belum dikirim):

> Mohon konfirmasi patch CVE-2026-109618 dan CVE-2026-109619 untuk domain siladesbeng.inovasia.site. Mohon informasikan PHP SAPI/handler, package name-version-release, bukti changelog patch/backport, serta konfirmasi restart seluruh worker PHP/LiteSpeed. Tolong nonaktifkan session.upload_progress.enabled pada konfigurasi startup server untuk mitigasi CVE-2026-115560 dan pastikan berlaku sebelum request parsing. CLI deployment kami menggunakan /opt/alt/php82/usr/bin/php; mohon verifikasi worker web dan CLI secara terpisah.

Halaman admin **Sistem Platform → Log Keamanan** sekarang menampilkan PHP_VERSION, PHP_SAPI, Laravel yang terpasang, mode debug, native session upload progress, dan serialize handler. Ini memakai izin platform_keamanan yang sudah ada. Tidak ada endpoint publik/phpinfo baru dan tidak ada nilai rahasia/path konfigurasi yang ditampilkan. Nilai tersebut adalah observasi satu worker, bukan sertifikat bahwa seluruh worker/backport benar.

Jika hosting belum memiliki patch dan tidak bisa segera memperbarui, isolasi akses publik melalui web server/proxy bersama penyedia. Jangan menganggap maintenance mode Laravel memblokir celah pre-request. Tidak ada percobaan eksploitasi, payload besar/OOM, atau pengujian DDoS pada produksi dalam pemeriksaan ini.

## Verifikasi dan pemulihan lokal

Paket PHP lama disimpan, bukan dihapus. Konfigurasi Laragon/PATH sebelum penggantian disimpan di `D:/laragon/tmp/security-20261026`; jangan kembali memakai versi rentan untuk melayani request publik. Perubahan PHP lokal berada di luar Git dan tidak ikut deployment cPanel.

- PHPUnit pada PHP 8.2.35: **54 tes / 200 assertions lulus**.
- PHPUnit pada PHP 8.3.36: **54 tes / 200 assertions lulus**.
- Composer check-platform-reqs pada PHP 8.3.36: lulus; Composer audit lockfile dan npm audit: nol advisori yang dilaporkan saat pemeriksaan.
- Pemeriksaan sintaks controller, kompilasi Blade, dan Apache `httpd -t`: lulus.
- User PATH kini memilih PHP 8.2.35-1; pengaturan Laragon dan Apache menunjuk PHP 8.3.36-1. Buka ulang terminal/aplikasi yang masih mewarisi PATH lama sebelum memakai perintah `php`. Laragon perlu dibuka ulang untuk membaca pilihan baru. Tidak ada worker lokal yang berjalan saat penggantian.
- Versi PHP lama/backup tidak dihapus dan tidak dipilih dalam konfigurasi baru. Jangan mengaktifkan kembali versi lama, termasuk PHP 8.1 yang masih tersimpan di Laragon.
- Pemeriksaan produksi hanya HEAD halaman publik; tidak membuktikan seluruh fitur online atau ketiadaan kompromi. Akses log hosting dan bukti patch worker belum tersedia.

## Referensi resmi

- [PHP: pengumuman keamanan Oktober 2026](https://www.php.net/archive/2026.php)
- [PHP: rilis Windows 8.3](https://www.php.net/downloads.php?version=8.3)
- [CERT-EU: advisori PHP2Shell dan CVE-2026-115560](https://cert.europa.eu/publications/security-advisories/2026-018)
- [Laravel TokenGuard](https://github.com/laravel/framework/security/advisories/GHSA-f9x3-7vq9-cph6)
- [Laravel Unauthorized Data Destruction](https://github.com/laravel/framework/security/advisories/GHSA-5wcm-3v2w-8362)
- [Laravel XSS Debug Page](https://github.com/laravel/framework/security/advisories/GHSA-jh5r-qr3c-85q8)
- [CloudLinux: build dan verifikasi backport](https://cloudlinux.zendesk.com/hc/en-us/articles/31194180517916-PHP-CVE-2026-109618-CVE-2026-109619-PHP2Shell-fixed-alt-php-and-ea-php-packages-and-how-to-verify)
