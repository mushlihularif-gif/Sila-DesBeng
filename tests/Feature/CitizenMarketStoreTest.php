<?php
namespace Tests\Feature;

use App\Http\Controllers\{MarketStoreController, MarketShippingController};
use App\Http\Controllers\Api\PasarDaerahApiController;
use App\Models\{MarketStore,PasarProduk,PasarOrder};
use App\Services\{MarketCheckoutService,MarketStoreOrders,MarketAfterSalesService,BookingAccess};
use Illuminate\Http\{Request,UploadedFile};
use Illuminate\Support\Facades\{DB,Schema,Storage};
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class CitizenMarketStoreTest extends TestCase
{
    use \Tests\Support\MarketDatabase;

    private function request(int $user,array $data=[],string $method='POST'): Request
    {
        $r=Request::create('/',$method,$data,[],[],['HTTP_ACCEPT'=>'application/json']);
        $r->setUserResolver(fn()=>$this->user($user)); return $r;
    }
    private function profile(array $extra=[]): array
    {
        return array_replace(['name'=>'Kerupuk Bu Siti','category'=>'Makanan dan Minuman','description'=>'Kerupuk rumahan dibuat setiap pagi.',
            'region_id'=>1,'address'=>'Jalan Kebun RT 01','avatar_source'=>'keep','cover_position'=>50],$extra);
    }
    private function store(int $owner=6,int $region=1): MarketStore
    {
        $s=new MarketStore(['name'=>'Toko '.$owner,'description'=>'Produk warga','category'=>'Makanan','address'=>'Jalan Kebun','is_open'=>true,
            'settings'=>['enable_cod'=>true,'enable_bank_transfer'=>true,'enable_qris'=>false,'rekening_bank'=>'Bank Toko','rekening_nomor'=>'123456','rekening_nama'=>'Pemilik Toko',
                'market_shipping'=>['pickup'=>true,'delivery'=>true,'village'=>['enabled'=>true,'fee'=>2000],'district'=>['enabled'=>true,'fee'=>7000],'outside'=>['enabled'=>false,'fee'=>0],'overrides'=>[]]]]);
        $s->user_id=$owner;$s->region_id=$region;$s->status='approved';$s->save();return $s;
    }
    private function productFor(MarketStore $store,int $buyer=1): PasarProduk
    {
        $p=PasarProduk::create(['nama_produk'=>'Kerupuk Warga','harga'=>15000,'stok'=>5,'status'=>'tersedia','region_id'=>$store->region_id,'store_id'=>$store->id]);
        DB::table('pasar_carts')->insert(['user_id'=>$buyer,'pasar_produk_id'=>$p->id,'quantity'=>1]);return $p;
    }
    private function place(MarketStore $store,string $method='COD'): PasarOrder
    {
        return app(MarketCheckoutService::class)->place($this->user(1),['store_key'=>'warga:'.$store->id,'delivery_method'=>'antar','delivery_region_id'=>1,
            'quoted_shipping_cost'=>2000,'payment_method'=>$method,'full_name'=>'Pembeli','phone'=>'08123456789','delivery_address'=>'Jalan Uji']);
    }
    private function denied(callable $action,int $code): void
    {
        try {$action();$this->fail('Akses/tindakan seharusnya ditolak');}
        catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){$this->assertSame(404,$code);}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertSame(422,$code);}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame($code,$e->getStatusCode());}
    }
    public function test_store_application_binds_verified_owner_and_notifies_only_its_village(): void
    {
        $controller=app(MarketStoreController::class);
        $this->denied(fn()=>$controller->saveProfile($this->request(8,$this->profile())),403);
        $this->denied(fn()=>$controller->saveProfile($this->request(2,$this->profile())),403);
        $controller->saveProfile($this->request(1,$this->profile(['user_id'=>6,'status'=>'approved'])));
        $store=MarketStore::firstOrFail();$this->assertSame(1,$store->user_id);$this->assertSame('pending',$store->status);
        $this->assertSame('user',$this->user(1)->role);
        $this->assertSame([4],DB::table('notifications')->pluck('user_id')->all());
        $this->denied(fn()=>$controller->review($this->request(7,['decision'=>'approved']),$store->id),404);
        $controller->review($this->request(4,['decision'=>'approved']),$store->id);
        $this->assertSame('approved',$store->fresh()->status);
        $this->denied(fn()=>$controller->review($this->request(4,['decision'=>'approved']),$store->id),409);
        $controller->saveProfile($this->request(1,$this->profile(['name'=>'Nama Baru'])));
        $this->assertSame('pending',$store->fresh()->status);$this->assertSame(1,MarketStore::count());
    }
    public function test_rejected_store_can_resubmit_and_location_changes_hide_and_reassign_products(): void
    {
        $store=$this->store(1);$product=$this->productFor($store,6);$store->status='pending';$store->save();
        $c=app(MarketStoreController::class);
        $c->review($this->request(4,['decision'=>'rejected','reason'=>'Alamat tidak jelas']),$store->id);
        $this->assertSame('Alamat tidak jelas',$store->fresh()->review_reason);
        $c->saveProfile($this->request(1,$this->profile(['region_id'=>5])));
        $this->assertSame('pending',$store->fresh()->status);$this->assertSame(5,$product->fresh()->region_id);
        $this->assertFalse(PasarProduk::visibleStore()->whereKey($product->id)->exists());
        $this->assertDatabaseHas('notifications',['user_id'=>7,'title'=>'Pendaftaran Toko Warga']);
    }
    public function test_one_store_checkout_keeps_other_carts_and_never_credits_village_wallet(): void
    {
        $store=$this->store();$product=$this->productFor($store);$second=$this->store(8);$other=$this->productFor($second);
        $order=$this->place($store,'bank_transfer');
        $this->assertSame($store->id,$order->store_id);$this->assertSame('17000.00',$order->grand_total);
        $this->assertSame('123456',$order->payment_settings_snapshot['rekening_nomor']);
        $this->assertDatabaseHas('market_payments',['pasar_order_id'=>$order->id,'status'=>'pending','amount'=>17000]);
        $this->assertSame(0,DB::table('wallet_transactions')->count());
        $this->assertSame(2,DB::table('pasar_carts')->count());
        $this->assertDatabaseHas('pasar_carts',['pasar_produk_id'=>$other->id]);
        $this->assertSame(4,$product->fresh()->stok);
        $this->denied(fn()=>$this->place($store),422);
        $this->assertSame(4,$product->fresh()->stok);
    }
    public function test_mixed_checkout_closed_unapproved_and_self_purchase_are_blocked(): void
    {
        $store=$this->store();$this->productFor($store);
        $data=['delivery_method'=>'jemput','payment_method'=>'COD','full_name'=>'Test','phone'=>'08123456789'];
        $this->denied(fn()=>app(MarketCheckoutService::class)->place($this->user(1),$data),422);
        foreach(['pending','suspended','rejected'] as $status){$store->status=$status;$store->save();$this->denied(fn()=>$this->place($store),422);}
        $store->status='approved';$store->is_open=false;$store->save();$this->denied(fn()=>$this->place($store),422);
        $store->is_open=true;$store->save();$this->productFor($store,6);
        $this->denied(fn()=>app(MarketCheckoutService::class)->place($this->user(6),$data+['store_key'=>'warga:'.$store->id]),422);
        $this->assertSame(0,PasarOrder::count());
    }
    public function test_owner_order_scope_payment_and_refund_are_idempotent_and_survive_buyer_archive(): void
    {
        $store=$this->store();$product=$this->productFor($store);$this->store(8);$order=$this->place($store,'bank_transfer');$svc=app(MarketStoreOrders::class);
        $this->denied(fn()=>$svc->change($this->user(8),$order->id,['action'=>'payment_received']),404);
        $svc->change($this->user(6),$order->id,['action'=>'payment_received']);
        $svc->change($this->user(6),$order->id,['action'=>'payment_received']);
        $this->assertSame('paid',$order->fresh()->status);
        $svc->requestCancellation($this->user(1),$order->id,'Tidak jadi membeli');
        $svc->change($this->user(6),$order->id,['action'=>'cancel','reason'=>'Permintaan pembeli']);
        $this->assertSame(5,$product->fresh()->stok);
        $this->assertDatabaseHas('market_payments',['status'=>'refund_pending']);
        $this->denied(fn()=>$svc->change($this->user(6),$order->id,['action'=>'cancel','reason'=>'Ulang']),409);
        $order->refresh()->delete();
        $svc->change($this->user(6),$order->id,['action'=>'refund_done','reason'=>'Transfer kembali 5 Nov, ref ABC']);
        $this->assertDatabaseHas('market_payments',['status'=>'refunded']);
        $this->denied(fn()=>$svc->change($this->user(6),$order->id,['action'=>'refund_done','reason'=>'Ulang']),409);
        $this->assertSame(0,DB::table('saldo_warga')->count());$this->assertSame(0,DB::table('wallet_transactions')->count());
    }
    public function test_cod_completion_does_not_claim_payment_received_and_proof_has_owner_scope(): void
    {
        $store=$this->store();$this->productFor($store);$order=$this->place($store);$svc=app(MarketStoreOrders::class);
        foreach(['accept','send','arrive'] as $action)$svc->change($this->user(6),$order->id,compact('action'));
        app(MarketCheckoutService::class)->received($this->user(1),$order->id);
        $this->assertFalse($order->fresh()->paymentIsVerified());
        $svc->change($this->user(6),$order->id,['action'=>'payment_received']);$this->assertTrue($order->fresh()->paymentIsVerified());
        $access=app(BookingAccess::class);$this->assertTrue($access->canView($order,'pasar',$this->user(6)));$this->assertFalse($access->canView($order,'pasar',$this->user(8)));
    }
    public function test_merchant_complaint_uses_own_village_review_and_manual_refund(): void
    {
        $store=$this->store();$this->productFor($store);$order=$this->place($store);
        $svc=app(MarketStoreOrders::class);$svc->change($this->user(6),$order->id,['action'=>'payment_received']);$svc->change($this->user(6),$order->id,['action'=>'accept']);
        $after=app(MarketAfterSalesService::class);$complaint=$after->submit($this->user(1),$order->id,$this->request(1,['reason'=>'Barang rusak','solution_requested'=>'refund']));
        $this->denied(fn()=>$svc->change($this->user(6),$order->id,['action'=>'send']),409);
        $data=['status'=>'approved_refund','admin_response'=>'Pengembalian disetujui'];
        $this->denied(fn()=>$after->decide($this->user(7),$complaint->id,$data),404);
        $after->decide($this->user(4),$complaint->id,$data);
        $this->assertSame('cancelled',$order->fresh()->status);$this->assertDatabaseHas('market_payments',['status'=>'refund_pending']);
        $this->assertSame(0,DB::table('saldo_warga')->count());
    }
    public function test_chat_separates_buyers_and_prevents_owner_contacting_arbitrary_accounts(): void
    {
        $store=$this->store();$c=app(MarketStoreController::class);
        $this->denied(fn()=>$c->chat($this->request(6,['message'=>'Spam']),$store->id,8),404);
        $c->chat($this->request(1,['message'=>'<script>alert(1)</script>']),$store->id);
        $this->denied(fn()=>$c->chat($this->request(8,[],'GET'),$store->id,1),403);
        $c->chat($this->request(6,['message'=>'Tersedia']),$store->id,1);
        $this->assertCount(2,$c->chat($this->request(1,[],'GET'),$store->id)->getData()->messages);
        $this->assertCount(0,$c->chat($this->request(8,[],'GET'),$store->id)->getData()->messages);
        $store->status='suspended';$store->save();
        $c->chat($this->request(1,['message'=>'Pesanan lama saya']),$store->id);
        $this->denied(fn()=>$c->chat($this->request(8,['message'=>'Baru']),$store->id),403);
    }
    public function test_public_store_and_product_api_never_expose_payment_settings_or_wrong_owner(): void
    {
        $store=$this->store();$product=$this->productFor($store);
        $json=app(MarketStoreController::class)->show($this->request(1,[],'GET'),$store->id)->getContent();
        $this->assertStringNotContainsString('123456',$json);$this->assertStringNotContainsString('settings',$json);
        $detail=app(PasarDaerahApiController::class)->getProductDetail($product->id)->getData();
        $this->assertSame(6,$detail->data->seller->id);$this->assertSame($store->name,$detail->data->seller->store_name);
        $store->status='pending';$store->save();
        $this->assertSame(404,app(PasarDaerahApiController::class)->getProductDetail($product->id)->getStatusCode());
        $this->denied(fn()=>app(PasarDaerahApiController::class)->getProductReviews($product->id),404);
    }
    public function test_profile_images_are_copied_and_foreign_product_cannot_be_edited(): void
    {
        Schema::table('users',fn(Blueprint $t)=>$t->string('avatar')->nullable());
        Storage::disk('public')->put('avatars/photo.png','test image');DB::table('users')->where('id',1)->update(['avatar'=>'avatars/photo.png']);
        $c=app(MarketStoreController::class);$c->saveProfile($this->request(1,$this->profile(['avatar_source'=>'account'])));
        $store=MarketStore::first();$this->assertNotSame('avatars/photo.png',$store->avatar);Storage::disk('public')->assertExists($store->avatar);
        $store->status='approved';$store->save();$other=$this->store();$foreign=$this->productFor($other);
        $this->denied(fn()=>$c->product($this->request(1,[],'GET'),$foreign->id),404);
        $r=$this->request(1,['nama_produk'=>'Kerupuk','deskripsi'=>'Kerupuk pedas','harga'=>5000,'stok'=>'0','satuan'=>'Bungkus','kategori'=>'Makanan','status'=>'tersedia','store_id'=>$other->id]);
        $r->files->set('foto',UploadedFile::fake()->image('produk.jpg'));
        $c->product($r);$this->assertDatabaseHas('pasar_produks',['store_id'=>$store->id,'nama_produk'=>'Kerupuk','status'=>'habis','stok'=>0]);
    }

    public function test_payment_proof_is_buyer_only_and_does_not_confirm_money(): void
    {
        $store=$this->store();$this->productFor($store);$order=$this->place($store,'bank_transfer');$c=app(MarketStoreController::class);
        $r=$this->request(8);$r->files->set('proof_of_payment',UploadedFile::fake()->image('transfer.jpg'));
        $this->denied(fn()=>$c->paymentProof($r,$order->id),404);
        $r=$this->request(1);$r->files->set('proof_of_payment',UploadedFile::fake()->image('transfer.jpg'));$c->paymentProof($r,$order->id);
        Storage::disk('public')->assertExists($order->fresh()->proof_of_payment);$this->assertFalse($order->fresh()->paymentIsVerified());
        $old=$order->fresh()->proof_of_payment;$r=$this->request(1);$r->files->set('proof_of_payment',UploadedFile::fake()->image('transfer-baru.jpg'));$c->paymentProof($r,$order->id);
        $this->assertNotSame($old,$order->fresh()->proof_of_payment);Storage::disk('public')->assertMissing($old);
        app(MarketStoreOrders::class)->change($this->user(6),$order->id,['action'=>'payment_received']);
        $this->denied(fn()=>$c->paymentProof($r,$order->id),409);
    }
    public function test_merchant_pages_render_with_shared_navigation_and_store_checkout(): void
    {
        (require database_path('migrations/2026_09_05_170000_create_alamat_warga_table.php'))->up();
        $store=$this->store();$product=$this->productFor($store);$product->update(['foto'=>'market_products/one.jpg','foto_2'=>'market_products/two.jpg']);
        $this->get(route('market.store',$store->id))->assertOk()->assertSee('Toko 6')->assertSee('sd-navbar',false)->assertSee('<footer',false);
        $this->get(route('pasar.show',$product->id))->assertOk()->assertSee('Foto berikutnya');
        $this->actingAs($this->user(6))->get(route('market.seller.home'))->assertOk()->assertSee('Simpan Pengiriman dan Pembayaran');
        $this->get(route('market.seller.product',$product->id))->assertOk();
        $this->actingAs($this->user(1))->get(route('pasar.cart'))->assertOk()->assertSee('Pesan dari Toko 6');
        $this->get(route('pasar.checkout',['store_key'=>'warga:'.$store->id]))->assertOk()->assertSee('Toko 6');
        $order=$this->place($store,'bank_transfer');
        $this->get(route('pasar.payment',$order->id))->assertOk()->assertSee('Kirim bukti pembayaran');
        $this->actingAs($this->user(6))->get(route('market.seller.order',$order->id))->assertOk()->assertSee('Catat Pembayaran Sudah Diterima');
    }
}
