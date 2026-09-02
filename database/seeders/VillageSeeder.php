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
                'desa' => ['Air Putih', 'Damai', 'Kelapapati', 'Kelebuk', 'Kelemantan', 'Kelemantan Barat', 'Ketam Putih', 'Kuala Alam', 'Meskom', 'Palkun', 'Pangkalan Batang', 'Pangkalan Batang Barat', 'Pedekik', 'Pematang Duku', 'Pematang Duku Timur', 'Penampi', 'Penebal', 'Prapat Tunggal', 'Sebauk', 'Sei Alam', 'Sekodi', 'Senderak', 'Senggoro', 'Simpang Ayam', 'Sungai Batang', 'Teluk Latak', 'Tameran', 'Wonosari'],
                'kelurahan' => ['Bengkalis Kota', 'Damon', 'Rimba Sekampung']
            ],
            'Bantan' => [
                'desa' => ['Bantan Tengah', 'Bantan Air', 'Bantan Tua', 'Teluk Pambang', 'Selat Baru', 'Teluk Lancar', 'Kembung Luar', 'Jangkang', 'Muntai', 'Resam Lapis', 'Berancah', 'Ulu Pulau', 'Mentayan', 'Pambang Pesisir', 'Sukamaju', 'Pambang Baru', 'Kembung Baru', 'Pasiran', 'Bantan Sari', 'Bantan Timur', 'Teluk Papal', 'Muntai Barat', 'Deluk'],
                'kelurahan' => []
            ],
            'Bukit Batu' => [
                'desa' => ['Sejangat', 'Dompas', 'Pangkalan Jambi', 'Sungai Selari', 'Buruk Bakul', 'Bukit Batu', 'Sukajadi', 'Batang Duku', 'Pakning Asal'],
                'kelurahan' => ['Sungai Pakning']
            ],
            'Mandau' => [
                'desa' => ['Bathin Betuah', 'Harapan Baru'],
                'kelurahan' => ['Air Jamban', 'Babussalam', 'Balik Alam', 'Batang Serosa', 'Duri Barat', 'Duri Timur', 'Gajah Sakti', 'Pematang Pudu', 'Talang Mandi']
            ],
            'Rupat' => [
                'desa' => ['Darul Aman', 'Dungun Baru', 'Hutan Panjang', 'Makeruh', 'Pancur Jaya', 'Pangkalan Nyirih', 'Pangkalan Pinang', 'Parit Kebumen', 'Sri Tanjung', 'Sukarjo Mesim', 'Sungai Cingam', 'Teluk Lecah'],
                'kelurahan' => ['Batu Panjang', 'Pergam', 'Tanjung Kapal', 'Terkul']
            ],
            'Rupat Utara' => [
                'desa' => ['Tanjung Medang', 'Teluk Rhu', 'Tanjung Punak', 'Titi Akar', 'Kadur', 'Hutan Ayu', 'Suka Damai', 'Puteri Sembilan'],
                'kelurahan' => []
            ],
            'Siak Kecil' => [
                'desa' => ['Lubuk Muda', 'Tanjung Belit', 'Sumber Jaya', 'Sungai Siput', 'Sepotong', 'Lubuk Garam', 'Lubuk Gaung', 'Tanjung Damai', 'Langkat', 'Sadar Jaya', 'Sungai Linau', 'Muara Dua', 'Bandar Jaya', 'Tanjung Datuk', 'Liang Banir', 'Koto Raja', 'Sungai Nibung'],
                'kelurahan' => []
            ],
            'Pinggir' => [
                'desa' => ['Balai Pungut', 'Muara Basung', 'Pinggir', 'Semunai', 'Sungai Meranti', 'Tengganau', 'Buluh Apo', 'Pangkalan Libut'],
                'kelurahan' => ['Balai Raja', 'Titian Antui']
            ],
            'Bandar Laksamana' => [
                'desa' => ['Parit I Api Api', 'Temiang', 'Api Api', 'Tenggayun', 'Sepahat', 'Bukit Kerikil', 'Tanjung Leban'],
                'kelurahan' => []
            ],
            'Talang Muandau' => [
                'desa' => ['Beringin', 'Koto Pait Beringin', 'Kuala Penaso', 'Melibur', 'Serai Wangi', 'Tasik Serai', 'Tasik Serai Barat', 'Tasik Serai Timur', 'Tasik Tebing Serai'],
                'kelurahan' => []
            ],
            'Bathin Solapan' => [
                'desa' => ['Air Kulim', 'Balai Makam', 'Bathin Sobanga', 'Boncah Mahang', 'Buluh Manis', 'Bumbung', 'Kesumboampai', 'Pamesi', 'Pematang Obo', 'Petani', 'Sebangar', 'Simpang Padang', 'Tambusai Batang Dui'],
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
