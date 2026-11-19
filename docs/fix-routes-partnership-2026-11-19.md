# Pemulihan halaman kabar dan pilihan kepengurusan

## Temuan

- Log hosting pada commit `5c469a5` mencatat `Route [content.destroy] not defined`. Definisi rute sudah ada di sumber; instruksi pull UI sebelumnya tidak membangun ulang cache rute. Instruksi deployment sekarang menjalankan `route:clear` dan `route:cache` sebelum `view:cache`.
- Halaman detail kabar masih eager-load relasi `laporan` yang sudah dihapus. Relasi tersebut tidak digunakan tampilan; pemanggilannya dihapus. Rekomendasi kabar sekarang memakai pembatasan publikasi dan wilayah yang sama dengan daftar kabar.
- Formulir kemitraan membatasi pilihan menjadi Desa ATAU RT/RW berdasarkan keberadaan admin desa aktif. Pilihan sekarang selalu terlihat, dengan kelayakan masing-masing: pengajuan Desa mengikuti aturan yang sudah ada; RT/RW mengikuti domisili akun di Kecamatan Bengkalis. Tanpa domisili yang sesuai, pilihan RT/RW dinonaktifkan disertai petunjuk Pengaturan. Pengajuan tidak memberikan hak admin sebelum persetujuan.
- Saat jenis pengajuan berganti, isian wilayah yang tidak dipakai dinonaktifkan dan nilai tersembunyi diselaraskan. Tidak ada alur pendaftaran atau jenis akun baru.

## Validasi lokal

- Suite setelah perbaikan kemitraan: 279 tes, 4.509 assertion lulus.
- Setelah temuan detail kabar diperbaiki: dua tes tambahan (11 assertion) lulus, menguji render HTTP dan penolakan draf/pengumuman wilayah lain dalam detail maupun rekomendasi.
- Cache rute dibangun pada berkas uji terpisah; `route:list --name=content.destroy` menampilkan metode DELETE yang benar. Berkas cache uji dibersihkan.
- Browser dengan HTML/data sintetis: pergantian Desa/RW/RT, nilai wilayah terkirim, field tersembunyi yang dinonaktifkan, dan lebar modal pada 320, 390, 1360 piksel lulus. Skenario dengan admin desa aktif dan tanpa admin aktif diuji. Layanan eksternal dimock; tidak ada pengajuan produksi dikirim.

Pemulihan hosting tetap harus diperiksa setelah pull dan penyegaran cache. Log yang diterima tidak cukup untuk menyatakan semua halaman produksi bebas error. Perubahan ini tidak menambah migrasi database.
