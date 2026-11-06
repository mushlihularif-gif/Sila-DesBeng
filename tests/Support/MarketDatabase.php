<?php
namespace Tests\Support;
use App\Models\PasarProduk;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema,Storage};
trait MarketDatabase
{
    use \Tests\Support\EditorialDatabase { setUp as setupEditorial; }

    protected function setUp(): void
    {
        $this->setupEditorial();
        config(['notification_channels.enabled'=>false]);
        Schema::table('regions', function (Blueprint $t) { $t->json('settings')->nullable(); $t->timestamps(); });
        DB::table('regions')->insert(['id'=>10,'name'=>'Kecamatan Bengkalis','type'=>'kecamatan']);
        DB::table('regions')->whereIn('id',[1,5])->update(['parent_id'=>10]);
        DB::table('regions')->where('id',1)->update(['settings'=>json_encode(['ongkir_dalam_desa'=>3000,'ongkir_luar_desa'=>12000])]);
        foreach ([
            '2026_08_04_170015_create_pasar_produks_table.php', '2026_08_04_170031_create_pasar_carts_table.php',
            '2026_08_28_131228_create_pasar_reviews_table.php',
            '2026_08_04_170038_create_pasar_orders_table.php', '2026_08_04_170201_create_pasar_order_items_table.php',
            '2026_08_28_142500_create_pasar_complaints_table.php', '2026_09_04_004539_add_delivered_at_to_pasar_orders_table.php',
            '2026_09_04_004540_add_extra_evidence_to_pasar_complaints_table.php',
            '2026_08_11_120100_create_wallet_transactions_table.php',
            '2026_11_05_120000_add_market_delivery_region.php',
            '2026_11_05_140000_create_citizen_market_stores.php',
        ] as $file) (require database_path('migrations/'.$file))->up();
        Schema::table('pasar_orders', fn (Blueprint $t) => $t->integer('handled_by')->nullable());
        Schema::create('saldo_warga', function (Blueprint $t) {
            $t->id(); foreach (['user_id','region_id','reference_id'] as $f) $t->integer($f);
            foreach (['type','reference_type','status','catatan'] as $f) $t->string($f);
            $t->decimal('amount',12,2); $t->timestamps();
        });
        Schema::create('transaction_receipts', function (Blueprint $t) {
            $t->id(); foreach (['booking_type','receipt_number','item_name','payment_method'] as $f) $t->string($f);
            foreach (['booking_id','user_id','region_id','quantity'] as $f) $t->integer($f);
            $t->decimal('total_amount',12,2); $t->timestamps();
        });
        Schema::create('admin_notifications', function (Blueprint $t) {
            $t->id(); $t->integer('region_id'); $t->integer('reference_id');
            foreach (['type','title','message'] as $f) $t->text($f);
            $t->boolean('is_read'); $t->timestamps();
        });
        PasarProduk::create(['nama_produk'=>'Kerupuk','harga'=>10000,'stok'=>2,'status'=>'tersedia','region_id'=>1]);
        $this->cart(1);
        Storage::fake('public');
    }

    private function cart(int $user, int $qty = 1): void
    {
        DB::table('pasar_carts')->insert(['user_id'=>$user,'pasar_produk_id'=>1,'quantity'=>$qty]);
    }

}
