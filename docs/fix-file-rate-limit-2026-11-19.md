# Pemulihan pembatas permintaan pada cache file

Hosting dikonfirmasi memakai cache `file`, batas 30/detik, 600/menit, 5 strike, dan blokir 10 menit. Screenshot `/kelola-kabar` menampilkan HTTP 429 dengan waktu tunggu 1 detik. Nilai penghitung hosting belum dibaca, sehingga screenshot bukan bukti bahwa semua 429 berasal dari bug ini.

## Reproduksi dan perbaikan

Kode lama melakukan `Cache::add(key, 0, TTL)` lalu `Cache::increment(key)`. Implementasi FileStore membaca nilai dan sisa TTL sebelum menulisnya kembali. Jika key kedaluwarsa di antara operasi, increment dapat membuat penghitung tanpa TTL. Penghitung satu detik kemudian bisa terus membatasi permintaan pada detik-detik berikutnya. FileStore juga tidak menyediakan increment atomik seperti store tertentu lainnya.

Tes regresi menggunakan FileStore nyata di direktori sementara, dengan waktu digeser melewati TTL saat increment. Sebelum perbaikan, permintaan setelah jeda menghasilkan 429, bukan 200; tes gagal. Setelah perbaikan, tes lulus.

Untuk cache file, pembaruan counter kini memakai file lock, menyimpan batas waktu di dalam payload, dan mereset jendela yang sudah lewat. Namespace counter baru tidak membaca counter lama yang mungkin tanpa TTL. Batas per detik/per menit dan mekanisme strike tetap berlaku. Ban dengan timestamp yang sudah lewat tidak lagi memblokir meskipun payload cache masih ada. Ban yang belum berakhir tetap dihormati. Tidak ada penghapusan cache aplikasi secara menyeluruh, perubahan .env, atau migrasi database.

Tes mencakup pemulihan setelah burst, batas per menit, strike berulang sampai ban, dan masa ban berakhir, pada cache array dan file. Hosting harus diperiksa kembali setelah pull; apabila ban sebelumnya masih aktif, tunggu sampai waktu yang ditampilkan habis. Error 429 yang masih berulang setelahnya perlu pemeriksaan penghitung dan log hosting, bukan mematikan pembatas permintaan.
