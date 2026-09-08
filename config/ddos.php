<?php

/*
|--------------------------------------------------------------------------
| Ambang Perlindungan DDoS
|--------------------------------------------------------------------------
|
| Dipakai App\Http\Middleware\DDoSProtection. Ditaruh di config supaya bisa
| disetel lewat .env tanpa mengubah kode — penting karena angka yang pas
| hanya ketahuan setelah dipakai warga sungguhan.
|
| Nilai lama (10/detik, 100/menit, ban 30 menit) terbukti terlalu ketat:
| satu halaman aplikasi ini bisa memicu belasan request sekaligus, browser
| membuka sampai enam koneksi paralel, dan yang terpenting SATU KANTOR DESA
| berbagi satu IP publik — jatahnya habis dipakai beramai-ramai, bukan oleh
| satu penyerang.
|
*/

return [

    // Batas request per detik per IP. Melewati ini menghasilkan satu strike.
    'per_detik' => (int) env('DDOS_PER_DETIK', 30),

    // Batas request per menit per IP.
    'per_menit' => (int) env('DDOS_PER_MENIT', 600),

    // Jumlah strike sebelum IP benar-benar diblokir.
    'strike' => (int) env('DDOS_STRIKE', 5),

    // Lama blokir dalam menit. Sengaja diperpendek dari 30 menit: serangan
    // sungguhan akan kena blokir lagi dalam hitungan detik, sedangkan warga
    // yang kena salah tangkap tidak perlu menunggu setengah jam.
    'ban_menit' => (int) env('DDOS_BAN_MENIT', 10),

    // Ambang pencatatan "mencurigakan" di log — peringatan dini, tidak memblokir.
    'mencurigakan' => (int) env('DDOS_MENCURIGAKAN', 400),

    /*
     | IP yang dikecualikan sepenuhnya, dipisah koma di .env. Isi dengan IP
     | publik kantor pengelola supaya pekerjaan admin tidak pernah terputus:
     |
     |   DDOS_WHITELIST=103.10.20.30,103.10.20.31
     |
     | Jangan diisi IP dinamis rumahan tanpa dicek berkala — kalau berpindah
     | ke pelanggan lain, pengecualiannya ikut berpindah.
     */
    'whitelist' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('DDOS_WHITELIST', ''))
    ))),

];
