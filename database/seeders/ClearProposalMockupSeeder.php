<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Laporan;
use App\Models\RentalBooking;
use App\Models\GasOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClearProposalMockupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Matikan constraint sementara agar tidak error saat delete
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Hapus transaksi dan laporan yang memiliki tanda DUMMY_PROPOSAL
        Laporan::where('catatan_admin', 'DUMMY_PROPOSAL')->delete();
        RentalBooking::where('admin_notes', 'DUMMY_PROPOSAL')->delete();
        GasOrder::where('notes', 'DUMMY_PROPOSAL')->delete();

        // Hapus user dummy
        User::where('email', 'dummy_proposal@siladesbeng.com')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "🧹 BERSIH: Semua data simulasi proposal telah berhasil dihapus secara permanen dari database Anda.\n";
    }
}
