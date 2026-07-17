<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Region;
use App\Models\Laporan;
use App\Models\RentalBooking;
use App\Models\GasOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProposalMockupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Dapatkan Desa (atau buat jika kosong)
        $desa = Region::where('type', 'desa')->first();
        if (!$desa) {
            $desa = Region::create(['name' => 'Desa Dummy', 'type' => 'desa']);
        }

        // Dapatkan RT/RW untuk Laporan
        $rw = Region::where('type', 'rw')->where('parent_id', $desa->id)->first() ?? Region::create(['name' => 'RW 01', 'type' => 'rw', 'parent_id' => $desa->id]);
        $rt = Region::where('type', 'rt')->where('parent_id', $rw->id)->first() ?? Region::create(['name' => 'RT 01', 'type' => 'rt', 'parent_id' => $rw->id]);

        // 2. Buat Dummy User Khusus Proposal
        $dummyUser = User::where('email', 'dummy_proposal@siladesbeng.com')->first();
        if(!$dummyUser) {
            $dummyUser = User::create([
                'name' => 'Warga Simulasi Proposal',
                'username' => 'warga_simulasi_99',
                'email' => 'dummy_proposal@siladesbeng.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'region_id' => $desa->id,
                'status' => 'aktif',
                'phone' => '081200000000'
            ]);
        }

        // Matikan event/global scopes sementara untuk seeder agar cepat
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Dapatkan semua region_id milik admin_rw dan admin_rt agar laporannya bisa dilihat oleh mereka
        $adminRegionIds = User::whereIn('role', ['admin_rw', 'admin_rt', 'admin_desa'])->pluck('region_id')->filter()->toArray();
        $targetRegionId = !empty($adminRegionIds) ? $adminRegionIds[array_rand($adminRegionIds)] : $desa->id;

        // 3. Buat Laporan Palsu (Untuk Panel RT/RW Gambar 2.6)
        $kategoriList = ['Infrastruktur', 'Fasilitas Umum', 'Pelayanan', 'Kebersihan', 'Lainnya'];
        $statusList = ['Pending', 'Proses', 'Selesai', 'Ditolak'];
        
        $laporans = [];
        for ($i = 1; $i <= 25; $i++) {
            $laporans[] = [
                'user_id' => $dummyUser->id,
                'region_id' => !empty($adminRegionIds) ? $adminRegionIds[array_rand($adminRegionIds)] : $desa->id,
                'nama' => 'Warga ' . $i,
                'kategori' => $kategoriList[array_rand($kategoriList)],
                'lokasi' => 'Jalan Kenangan RT 01 RW 01',
                'rw' => $rw->name,
                'rt' => $rt->name,
                'rw_number' => '01',
                'rt_number' => '01',
                'deskripsi' => 'Laporan simulasi untuk keperluan screenshot proposal nomor ' . $i,
                'status' => $statusList[array_rand($statusList)],
                'catatan_admin' => 'DUMMY_PROPOSAL',
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()
            ];
        }
        Laporan::insert($laporans);

        // 4. Buat Transaksi Rental & Gas (Untuk Grafik Admin Kabupaten Gambar 2.4)
        for ($i = 1; $i <= 100; $i++) {
            $randomDate = Carbon::now()->subDays(rand(1, 150));
            
            // Sewa Alat
            RentalBooking::insert([
                'uuid' => Str::uuid()->toString(),
                'user_id' => $dummyUser->id,
                'region_id' => $targetRegionId,
                'barang_id' => 1, 
                'order_number' => 'RNT-' . Str::random(6),
                'delivery_method' => 'jemput',
                'quantity' => rand(1, 5),
                'days_count' => 2,
                'start_date' => $randomDate->toDateString(),
                'end_date' => $randomDate->copy()->addDays(2)->toDateString(),
                'payment_method' => 'tunai',
                'total_amount' => rand(5, 50) * 10000,
                'status' => 'completed',
                'payment_proof' => 'dummy.jpg',
                'admin_notes' => 'DUMMY_PROPOSAL',
                'created_at' => $randomDate,
                'updated_at' => $randomDate
            ]);

            // Beli Gas
            GasOrder::insert([
                'uuid' => Str::uuid()->toString(),
                'order_number' => 'GAS-' . Str::random(6),
                'user_id' => $dummyUser->id,
                'region_id' => $targetRegionId,
                'gas_id' => 1,
                'item_name' => 'Gas LPG 3Kg',
                'quantity' => rand(1, 3),
                'price' => 20000,
                'order_date' => $randomDate->toDateString(),
                'delivery_method' => 'jemput',
                'payment_method' => 'tunai',
                'address' => 'Alamat RT 01',
                'full_name' => 'Warga Simulasi',
                'email' => 'dummy@siladesbeng.com',
                'status' => 'completed',
                'proof_of_payment' => 'dummy.jpg',
                'notes' => 'DUMMY_PROPOSAL',
                'created_at' => $randomDate,
                'updated_at' => $randomDate
            ]);
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        echo "✅ BERHASIL: 200+ Data Simulasi Proposal Berhasil Dibuat!\n";
    }
}
