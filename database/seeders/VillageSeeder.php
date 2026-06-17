<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;

class VillageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $villagesData = [
            'Bengkalis' => [
                'desa' => ['Air Putih', 'Damai', 'Kelapapati', 'Kelebuk', 'Kelemantan', 'Kelemantan Barat', 'Ketam Putih', 'Kuala Alam', 'Meskom', 'Palkun', 'Pangkalan Batang', 'Pangkalan Batang Barat', 'Pedekik', 'Pematang Duku', 'Pematang Duku Timur', 'Penampi', 'Penebal', 'Prapat Tunggal', 'Sebauk', 'Sei Alam', 'Sekodi', 'Senderek', 'Senggoro', 'Simpang Ayam', 'Sungai batang', 'Teluk Latak', 'Temeran', 'Wonosari'],
                'kelurahan' => ['Bengkalis Kota', 'Damon', 'Rimba Sekampung']
            ],
            'Bantan' => [
                'desa' => ['Bantan Air', 'Bantan Sari', 'Bantan Tengah', 'Bantan Timur', 'Bantan Tua', 'Berancah', 'Deluk', 'Jangkang', 'Kembung Baru', 'Kembung Luar', 'Mentayan', 'Muntai', 'Muntai Barat', 'Pampang Baru', 'Pampang Pesisir', 'Pasiran', 'Resam Lapis', 'Selat Baru', 'Sukamaju', 'Teluklancar', 'Telukpambang', 'Telukpapal', 'Ulu Pulau'],
                'kelurahan' => []
            ],
            'Bukit Batu' => [
                'desa' => ['Batang Duku', 'Bukit Batu', 'Buruk Bakul', 'Dompas', 'Pangkalan Jambi', 'Pakning Asal', 'Sejangat', 'Sukajadi', 'Sungai Selari'],
                'kelurahan' => ['Sungai Pakning']
            ],
            'Mandau' => [
                'desa' => ['Bathin Betuah', 'Harapan Baru'],
                'kelurahan' => ['Air Jamban', 'Babussalam', 'Balik Alam', 'Batang Serosa', 'Duri Barat', 'Duri Timur', 'Gajah Sakti', 'Pematang Pudu', 'Makeruh (Talang Mandi)']
            ],
            'Rupat' => [
                'desa' => ['Darul Aman', 'Dungun Baru', 'Hutan Panjang', 'Makeruh', 'Pancur Jaya', 'Pangkalan Nyirih', 'Pangkalan Pinang', 'Parit Kebumen', 'Sri Tanjung', 'Sukarjo Mesin', 'Sungai Cingam', 'Teluk Lecah'],
                'kelurahan' => ['Batu Panjang', 'Pergam', 'Tanjung Kapal', 'Terkul']
            ],
            'Rupat Utara' => [
                'desa' => ['Hutan Ayu', 'Kadur', 'Suka Damai', 'Puteri Sembilan', 'Tanjung Medang', 'Tanjung Punak', 'Teluk Rhu', 'Titi Akar'],
                'kelurahan' => []
            ],
            'Siak Kecil' => [
                'desa' => ['Bandar Jaya', 'Koto Raja', 'Langkat', 'Liang Banir', 'Lubuk Garam', 'Lubuk Gaung', 'Lubuk Muda', 'Muara Dua', 'Sadar Jaya', 'Sepotong', 'Sumber Jaya', 'Sungainibung', 'Sungai Limau', 'Sungai Siput', 'Tanjung Belit', 'Tanjung Damai', 'Tanjungdatuk'],
                'kelurahan' => []
            ],
            'Pinggir' => [
                'desa' => ['Balai Pungut', 'Buluh Apo', 'Muara Basung', 'Pangkalan Libut', 'Pinggir', 'Semunai', 'Sungaimeranti', 'Tengganau'],
                'kelurahan' => ['Balai Raja', 'Titian Antui']
            ],
            'Bandar Laksamana' => [
                'desa' => ['Api-Api', 'Bukitkerikil', 'Paritsatuapi-api', 'Sepahat', 'Tanjungleban', 'Temiang', 'Tenggayun'],
                'kelurahan' => []
            ],
            'Talang Muandau' => [
                'desa' => ['Beringin', 'Koto Pait Beringin', 'Kuala Penaso', 'Melibur', 'Serai Wangi', 'Tasik Serai', 'Tasik Serai Barat', 'Tasik Serai Timur', 'Tasik Tebing Serai'],
                'kelurahan' => []
            ],
            'Bathin Solapan' => [
                'desa' => ['Air Kulim', 'Balai Makam', 'Bathin Sobanga', 'Boncah Mahang', 'Buluh Manis', 'Bumbung', 'Kesumbo Ampai', 'Pamesi', 'Pematang Obo', 'Petani', 'Sebangar', 'Simpang Padang', 'Tambusai Batang Dui'],
                'kelurahan' => []
            ]
        ];

        $kabupaten = Region::where('type', 'kabupaten')->where('name', 'Kabupaten Bengkalis')->first();

        if (!$kabupaten) {
            $kabupaten = Region::create([
                'type' => 'kabupaten',
                'name' => 'Kabupaten Bengkalis',
                'profile_text' => 'Pemerintah Kabupaten Bengkalis'
            ]);
        }

        foreach ($villagesData as $kecamatanName => $data) {
            $kecamatan = Region::firstOrCreate(
                ['type' => 'kecamatan', 'name' => 'Kecamatan ' . $kecamatanName, 'parent_id' => $kabupaten->id],
                ['profile_text' => 'Pemerintah Kecamatan ' . $kecamatanName]
            );

            foreach ($data['desa'] as $desaName) {
                Region::firstOrCreate(
                    ['type' => 'desa', 'name' => 'Desa ' . $desaName, 'parent_id' => $kecamatan->id],
                    ['profile_text' => 'Pemerintah Desa ' . $desaName]
                );
            }

            foreach ($data['kelurahan'] as $kelurahanName) {
                Region::firstOrCreate(
                    ['type' => 'desa', 'name' => 'Kelurahan ' . $kelurahanName, 'parent_id' => $kecamatan->id],
                    ['profile_text' => 'Pemerintah Kelurahan ' . $kelurahanName]
                );
            }
        }
    }
}
