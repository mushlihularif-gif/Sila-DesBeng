<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\UnitPasarDaerahController;
use App\Http\Controllers\Api\PasarDaerahApiController;
use App\Models\{PasarOrder, PasarProduk, Region};
use App\Services\{MarketCheckoutService, MarketAfterSalesService};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Schema, Storage};
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MarketAuditTest extends TestCase
{
    use \Tests\Support\MarketDatabase;

    private function data(): array
    {
        return ['delivery_method'=>'antar','payment_method'=>'COD','full_name'=>'Warga Uji','phone'=>'08123456789','delivery_address'=>'Alamat uji'];
    }

    private function place(int $user = 1): PasarOrder
    {
        return app(MarketCheckoutService::class)->place($this->user($user),$this->data());
    }

    private function denied(callable $action): void
    {
        try { $action(); $this->fail('Mutation unexpectedly allowed'); }
        catch (HttpException $e) { $this->assertContains($e->getStatusCode(),[403,409,422]); }
        catch (ValidationException $e) { $this->assertNotEmpty($e->errors()); }
    }

    public function test_rt_resident_shipping_and_order_ledger_receipt_are_consistent(): void
    {
        $order=$this->place();
        $this->assertSame('3000.00',$order->shipping_cost);
        $this->assertSame('13000.00',$order->grand_total);
        $this->assertSame('pending',$order->status);
        $this->assertStringStartsWith('PSR-',$order->order_number);
        $this->assertDatabaseHas('pasar_order_items',['pasar_order_id'=>$order->id,'product_name'=>'Kerupuk','product_price'=>10000]);
        $this->assertDatabaseHas('wallet_transactions',['reference_id'=>$order->id,'source'=>'manual','status'=>'pending','amount'=>13000]);
        $this->assertDatabaseHas('transaction_receipts',['booking_id'=>$order->id,'region_id'=>1,'total_amount'=>13000]);
        $this->assertSame(12000.0,app(MarketCheckoutService::class)->shipping($this->user(6),Region::find(1)));
    }

    public function test_mobile_checkout_uses_same_stock_status_shipping_and_receipt_contract(): void
    {
        $request=Request::create('/','POST',['delivery_method'=>'Diantar','payment_method'=>'bank_transfer','delivery_address'=>'Alamat uji']);
        $request->setUserResolver(fn ()=>$this->user(1));
        $response=app(PasarDaerahApiController::class)->checkout($request);
        $this->assertSame('success',$response->getData()->status);
        $this->assertSame('pending',$response->getData()->order->status);
        $this->assertSame('antar',$response->getData()->order->delivery_method);
        $this->assertSame('13000.00',$response->getData()->order->grand_total);
        $this->assertDatabaseHas('wallet_transactions',['source'=>'manual','amount'=>13000]);
        $this->assertSame(1,DB::table('transaction_receipts')->count());
    }

    public function test_stale_cart_repeat_checkout_and_nonactive_products_cannot_consume_stock(): void
    {
        DB::table('pasar_carts')->update(['quantity'=>2]); $this->cart(6,2);
        $this->place();
        $this->denied(fn ()=>$this->place());
        $this->denied(fn ()=>$this->place(6));
        $this->assertSame(0,PasarProduk::find(1)->stok);
        $this->assertSame(1,PasarOrder::count());
        PasarProduk::find(1)->update(['stok'=>10,'status'=>'nonaktif']);
        $this->denied(fn ()=>$this->place(6));
        $this->assertDatabaseHas('pasar_carts',['user_id'=>6]);
    }

    public function test_failed_checkout_rolls_back_stock_order_and_uploaded_proof(): void
    {
        Schema::drop('transaction_receipts');
        try {
            app(MarketCheckoutService::class)->place($this->user(1),$this->data(),UploadedFile::fake()->image('proof.jpg'));
            $this->fail('Missing receipt table must roll back the order');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertSame(0,PasarOrder::count());
            $this->assertSame(2,PasarProduk::find(1)->stok);
            $this->assertSame(1,DB::table('pasar_carts')->count());
            $this->assertSame([],Storage::disk('public')->allFiles());
        }
    }

    public function test_unverified_customer_and_cancelled_order_cannot_bypass_workflow(): void
    {
        $user=$this->user(1); $user->verification_status='unverified';
        $this->denied(fn ()=>app(MarketCheckoutService::class)->place($user,$this->data()));
        $order=$this->place();
        foreach (['pending','cancelled','rejected','completed'] as $state) {
            $order->update(['status'=>$state]);
            $this->denied(fn ()=>app(MarketCheckoutService::class)->received($this->user(1),$order->id));
        }
    }

    public function test_open_complaint_blocks_auto_completion_and_duplicate_submission(): void
    {
        $order=$this->place(); $order->update(['status'=>'delivered','delivered_at'=>now()]);
        $request=Request::create('/','POST',['reason'=>'Barang rusak','solution_requested'=>'replacement']);
        app(MarketAfterSalesService::class)->submit($this->user(1),$order->id,$request);
        $this->denied(fn ()=>app(MarketAfterSalesService::class)->submit($this->user(1),$order->id,$request));
        $this->denied(fn ()=>app(MarketCheckoutService::class)->received($this->user(1),$order->id));
        $this->travel(3)->hours(); $this->artisan('pasar:auto-complete')->assertSuccessful();
        $this->assertSame('delivered',$order->fresh()->status);
        DB::table('pasar_complaints')->update(['status'=>'rejected']);
        $this->artisan('pasar:auto-complete')->assertSuccessful();
        $this->assertSame('completed',$order->fresh()->status);
    }

    public function test_product_order_history_is_preserved_and_legacy_tabs_have_destinations(): void
    {
        $order=$this->place(); $order->delete();
        $this->actingAs($this->user(4));
        $controller=app(UnitPasarDaerahController::class);
        $controller->destroy(1);
        $this->assertNotNull(PasarProduk::find(1));
        $this->assertSame(1,DB::table('pasar_order_items')->count());
        foreach (['pesanan'=>'pesanan','laporan'=>'laporan','profile'=>'profil','reviews'=>'ulasan'] as $method=>$tab) {
            $response=in_array($method,['pesanan','laporan']) ? $controller->$method(Request::create('/')) : $controller->$method();
            $this->assertSame(route('admin.unit.pasar_daerah.index',['tab'=>$tab]),$response->getTargetUrl());
        }
    }

    public function test_public_catalog_does_not_serialize_region_settings_or_inactive_products(): void
    {
        $controller=app(PasarDaerahApiController::class);
        foreach ([$controller->getProducts(Request::create('/')), $controller->getProductDetail(1)] as $response) {
            $this->assertStringNotContainsString('ongkir_dalam_desa',$response->getContent());
            $this->assertStringNotContainsString('"settings"',$response->getContent());
        }
        PasarProduk::find(1)->update(['status'=>'nonaktif']);
        $this->assertSame(404,$controller->getProductDetail(1)->getStatusCode());
    }

    public function test_admin_transitions_verify_payment_and_restore_cancelled_stock_only_once(): void
    {
        $order=$this->place(); $this->actingAs($this->user(4));
        $controller=app(UnitPasarDaerahController::class);
        $this->denied(fn ()=>$controller->pesananUpdate(Request::create('/','PUT',['status'=>'completed']),$order->id));
        $controller->pesananUpdate(Request::create('/','PUT',['status'=>'paid']),$order->id);
        $this->assertDatabaseHas('wallet_transactions',['reference_id'=>$order->id,'status'=>'verified','verified_by'=>4]);
        $controller->pesananUpdate(Request::create('/','PUT',['status'=>'cancelled']),$order->id);
        $controller->pesananUpdate(Request::create('/','PUT',['status'=>'cancelled']),$order->id);
        $this->assertSame(2,PasarProduk::find(1)->stok);
        $this->assertDatabaseHas('wallet_transactions',['reference_id'=>$order->id,'status'=>'rejected']);
        $this->denied(fn ()=>$controller->pesananUpdate(Request::create('/','PUT',['status'=>'paid']),$order->id));
    }

    public function test_complaint_refund_is_accounted_once_and_expired_complaints_are_denied(): void
    {
        $order=$this->place(); $order->update(['status'=>'delivered','delivered_at'=>now()->subHours(3)]);
        $service=app(MarketAfterSalesService::class);
        $request=Request::create('/','POST',['reason'=>'Barang rusak','solution_requested'=>'refund']);
        $this->denied(fn ()=>$service->submit($this->user(1),$order->id,$request));
        $order->update(['delivered_at'=>now()]);
        $complaint=$service->submit($this->user(1),$order->id,$request);
        // Historic gateway payments still use the existing wallet refund flow.
        DB::table('wallet_transactions')->update(['source'=>'gateway','status'=>'verified']);
        $decision=['status'=>'approved_refund','admin_response'=>'Barang rusak, dana dikembalikan.'];
        $service->decide($this->user(4),$complaint->id,$decision);
        $this->denied(fn ()=>$service->decide($this->user(4),$complaint->id,$decision));
        $this->assertSame('cancelled',$order->fresh()->status);
        $this->assertSame(1,DB::table('saldo_warga')->count());
        $this->assertEquals(13000,DB::table('saldo_warga')->value('amount'));
    }

    public function test_only_completed_buyer_can_review_product_once(): void
    {
        $service=app(MarketAfterSalesService::class); $data=['rating'=>5,'comment'=>'Produk baik'];
        $this->denied(fn ()=>$service->review($this->user(1),1,$data));
        $order=$this->place(); $order->update(['status'=>'completed']);
        $service->review($this->user(1),1,$data);
        $this->denied(fn ()=>$service->review($this->user(1),1,$data));
        $this->denied(fn ()=>$service->review($this->user(6),1,$data));
        $this->assertSame(1,DB::table('pasar_reviews')->count());
    }

    public function test_disabled_payment_methods_cannot_be_submitted_including_legacy_aliases(): void
    {
        DB::table('regions')->where('id',1)->update(['settings'=>json_encode(['enable_cod'=>false,'enable_bank_transfer'=>false,'enable_qris'=>false])]);
        foreach (['COD','tunai','bank_transfer','transfer_manual','qris'] as $method) {
            $this->denied(fn ()=>app(MarketCheckoutService::class)->place($this->user(1),array_replace($this->data(),['payment_method'=>$method])));
        }
        $this->assertSame(0,PasarOrder::count());
        $this->assertSame(2,PasarProduk::find(1)->stok);
        $this->assertSame(1,DB::table('pasar_carts')->count());
    }

    public function test_failed_direct_buy_preserves_existing_cart_and_api_cannot_change_another_users_cart(): void
    {
        $product=PasarProduk::create(['nama_produk'=>'Jamu','harga'=>5000,'stok'=>0,'status'=>'habis','region_id'=>5]);
        $this->actingAs($this->user(1));
        $response=app(\App\Http\Controllers\User\PasarDaerahController::class)->addToCart(
            Request::create('/','POST',['pasar_produk_id'=>$product->id,'quantity'=>1,'is_direct_buy'=>true]));
        $this->assertSame(400,$response->getStatusCode());
        $this->assertDatabaseHas('pasar_carts',['user_id'=>1,'pasar_produk_id'=>1,'quantity'=>1]);
        $cartId=DB::table('pasar_carts')->value('id');
        $request=Request::create('/','POST',['cart_item_id'=>$cartId,'quantity'=>2]);
        $request->setUserResolver(fn ()=>$this->user(6));
        $api=app(PasarDaerahApiController::class);
        $this->assertSame(404,$api->updateCart($request)->getStatusCode());
        $api->removeFromCart($request);
        $this->assertDatabaseHas('pasar_carts',['id'=>$cartId,'user_id'=>1,'quantity'=>1]);
    }

    public function test_cod_can_be_fulfilled_without_falsely_verifying_cash_and_payment_is_recorded_once(): void
    {
        $order=$this->place(); $this->actingAs($this->user(4));
        $controller=app(UnitPasarDaerahController::class);
        $this->denied(fn ()=>$controller->pesananUpdate(Request::create('/','PUT',['status'=>'payment_received']),$order->id));
        foreach (['confirmed','delivered','completed'] as $status) {
            $controller->pesananUpdate(Request::create('/','PUT',compact('status')),$order->id);
            $this->assertFalse($order->paymentIsVerified());
        }
        $request=Request::create('/','PUT',['status'=>'payment_received']);
        $controller->pesananUpdate($request,$order->id);
        $notificationCount=DB::table('notifications')->count();
        $controller->pesananUpdate($request,$order->id);
        $this->assertSame($notificationCount,DB::table('notifications')->count());
        $this->assertSame('completed',$order->fresh()->status);
        $this->assertTrue($order->paymentIsVerified());
        $this->assertDatabaseHas('wallet_transactions',['reference_id'=>$order->id,'verified_by'=>4]);

        $this->cart(1);
        $bankOrder=app(MarketCheckoutService::class)->place($this->user(1),array_replace($this->data(),['payment_method'=>'bank_transfer']));
        $this->denied(fn ()=>$controller->pesananUpdate(Request::create('/','PUT',['status'=>'confirmed']),$bankOrder->id));
        $this->denied(fn ()=>$controller->pesananUpdate($request,$bankOrder->id));
    }

    public function test_market_receipt_uses_order_snapshot_prices_and_delivery_fields(): void
    {
        $order=$this->place();
        PasarProduk::find(1)->update(['nama_produk'=>'Nama baru','harga'=>99999]);
        $generator=new class extends \App\Services\ReceiptGeneratorService {
            public array $texts=[];
            protected function addText($image,$text,$x,$y,$size,$color,$fontPath,$bold=false) { $this->texts[]=$text; }
            protected function addFooterTtd($image,$y,$url,$fontPath,$normalSize,$black) {}
        };
        $path=$generator->generatePasarReceipt($order);
        Storage::disk('public')->assertExists($path);
        foreach (['Kerupuk','Rp. 10.000',': Warga Uji',': 08123456789',': Alamat uji',': Diantar (Kurir)'] as $text) {
            $this->assertContains($text,$generator->texts);
        }
        $this->assertNotContains('Nama baru',$generator->texts);
        $this->assertNotContains('Rp. 0',$generator->texts);
    }

    public function test_payment_page_is_owned_or_scoped_to_the_store_admin(): void
    {
        $order=$this->place();
        $controller=app(\App\Http\Controllers\User\PasarDaerahController::class);
        foreach ([6,7] as $id) {
            $user=$this->user($id); $user->setAttribute('is_admin',true);
            $this->actingAs($user);
            $this->denied(fn ()=>$controller->payment($order->id));
        }
        foreach ([1,4] as $id) {
            $this->actingAs($this->user($id));
            $this->assertSame($order->id,$controller->payment($order->id)->getData()['order']->id);
        }
    }

    private function shippingFixture(): array
    {
        DB::table('regions')->insert([
            ['id'=>11,'name'=>'Kecamatan Bantan','type'=>'kecamatan','parent_id'=>null],
            ['id'=>12,'name'=>'Desa Tujuan','type'=>'desa','parent_id'=>11],
            ['id'=>13,'name'=>'Desa Tetangga','type'=>'desa','parent_id'=>11],
        ]);
        return app(\App\Services\MarketShippingService::class)->configuration(Region::find(1));
    }

    public function test_shipping_defaults_keep_old_rates_and_never_treat_unserved_region_as_free(): void
    {
        $this->shippingFixture(); $service=app(\App\Services\MarketShippingService::class); $seller=Region::find(1);
        $this->assertSame(3000,$service->quote($seller,Region::find(1),'antar')['cost']);
        $this->assertSame(12000,$service->quote($seller,Region::find(5),'antar')['cost']);
        $this->assertFalse($service->quote($seller,Region::find(12),'antar')['available']);
        $this->assertNull($service->quote($seller,Region::find(12),'antar')['cost']);
        $this->assertSame(0,$service->quote($seller,Region::find(12),'jemput')['cost']);
        $this->assertFalse($service->quote($seller,Region::find(3),'antar')['available']);
    }

    public function test_specific_village_tariff_wins_and_disabled_delivery_cannot_be_bypassed(): void
    {
        $config=$this->shippingFixture();
        $config['overrides']=[['region_id'=>11,'fee'=>15000],['region_id'=>12,'fee'=>0]];
        $seller=Region::find(1); $seller->settings=['market_shipping'=>$config];
        $service=app(\App\Services\MarketShippingService::class);
        $this->assertSame(0,$service->quote($seller,Region::find(12),'antar')['cost']);
        $this->assertSame(15000,$service->quote($seller,Region::find(13),'antar')['cost']);
        $config['delivery']=false; $seller->settings=['market_shipping'=>$config];
        $this->assertFalse($service->quote($seller,Region::find(12),'antar')['available']);
        $config['pickup']=false; $seller->settings=['market_shipping'=>$config];
        $this->assertFalse($service->quote($seller,Region::find(12),'jemput')['available']);
    }

    public function test_checkout_prices_selected_destination_and_rejects_stale_quote_without_charging(): void
    {
        $config=$this->shippingFixture(); $config['outside']=['enabled'=>true,'fee'=>25000];
        Region::find(1)->update(['settings'=>['market_shipping'=>$config]]);
        $data=$this->data()+['delivery_region_id'=>12,'quoted_shipping_cost'=>3000];
        $this->denied(fn ()=>app(MarketCheckoutService::class)->place($this->user(1),$data));
        $this->assertSame(0,PasarOrder::count()); $this->assertSame(2,PasarProduk::find(1)->stok);
        $data['quoted_shipping_cost']=25000;
        $order=app(MarketCheckoutService::class)->place($this->user(1),$data);
        $this->assertSame('25000.00',$order->shipping_cost);
        $this->assertEquals(12,$order->delivery_region_id);
        $this->assertSame('35000.00',$order->grand_total);
        $config['outside']['fee']=9000; Region::find(1)->update(['settings'=>['market_shipping'=>$config]]);
        $this->assertSame('25000.00',$order->fresh()->shipping_cost);
    }

    public function test_shipping_settings_reject_invalid_prices_duplicates_and_empty_service(): void
    {
        $config=$this->shippingFixture(); $service=app(\App\Services\MarketShippingService::class);
        foreach ([
            array_replace($config,['pickup'=>false,'delivery'=>false]),
            array_replace($config,['village'=>['enabled'=>true,'fee'=>-1]]),
            array_replace($config,['overrides'=>[['region_id'=>3,'fee'=>5000]]]),
            array_replace($config,['overrides'=>[['region_id'=>12,'fee'=>5000],['region_id'=>12,'fee'=>0]]]),
        ] as $invalid) $this->denied(fn ()=>$service->validateSettings($invalid));
        $config['overrides']=[['region_id'=>12,'fee'=>0]];
        $this->assertSame($config,$service->validateSettings($config));
    }

    public function test_store_settings_save_shipping_and_quote_api_uses_destination(): void
    {
        $config=$this->shippingFixture(); $config['outside']=['enabled'=>true,'fee'=>9000];
        $this->actingAs($this->user(4));
        app(UnitPasarDaerahController::class)->updateSop(Request::create('/','POST',['shipping'=>$config,'enable_cod'=>1]));
        $this->assertSame($config,Region::find(1)->settings['market_shipping']);
        $this->assertArrayNotHasKey('market_shipping',Region::find(5)->settings ?? []);
        $this->actingAs($this->user(1),'sanctum')->getJson('/api/pasar-daerah/shipping-quote?seller_id=1&delivery_region_id=12&delivery_method=antar')
            ->assertOk()->assertJsonPath('cost',9000)->assertJsonPath('available',true)->assertDontSee('settings');
        $this->getJson('/api/pasar-daerah/shipping-quote?seller_id=1&delivery_region_id=3&delivery_method=antar')->assertUnprocessable();
        $config['overrides']=[['region_id'=>12,'fee'=>9000]];
        Region::find(1)->update(['settings'=>['market_shipping'=>$config]]);
        $config['delivery']=false; unset($config['overrides']);
        $this->actingAs($this->user(4));
        app(UnitPasarDaerahController::class)->updateSop(Request::create('/','POST',['shipping'=>$config,'enable_cod'=>1]));
        $this->assertSame([['region_id'=>12,'fee'=>9000]],Region::find(1)->settings['market_shipping']['overrides']);
        $this->actingAs($this->user(1));
        $this->denied(fn ()=>app(UnitPasarDaerahController::class)->updateSop(Request::create('/','POST',['shipping'=>$config])));
    }

    public function test_shipping_editor_renders_simple_labels_and_optional_advanced_section(): void
    {
        $config=$this->shippingFixture();
        $html=view('admin.unit.pasar_daerah.shipping-settings',[
            'shippingConfig'=>$config,'shippingRegions'=>Region::with('parent')->get(),
            'errors'=>new \Illuminate\Support\ViewErrorBag,
        ])->render();
        $this->assertStringContainsString('Cara pembeli menerima barang',$html);
        $this->assertStringContainsString('Diantar oleh toko',$html);
        $this->assertStringContainsString('data-delivery-options',$html);
        $this->assertStringContainsString('Atur ongkir berbeda untuk wilayah tertentu',$html);
        $this->assertStringContainsString('Pengiriman toko Anda',$html);
        $this->assertStringNotContainsString('tipe_ongkir_luar_kecamatan',$html);
    }
}
