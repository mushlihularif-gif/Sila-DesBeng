<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Route, Schema, Storage};
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FinalRouteRegressionTest extends TestCase
{
    use \Tests\Support\EditorialDatabase;

    public function test_vehicle_detail_and_region_admin_views_compile_to_valid_php(): void
    {
        foreach (['admin.unit.mobil.show', 'admin.layouts.admin', 'admin.region_management.index',
            'admin.region_management.partials.modals', 'admin.super_sistem.partials.simpul_wilayah',
            'admin.unit.pasar_daerah.pesanan_show', 'users.pasar-checkout', 'users.pasar-detail', 'users.partials.activity-pasar',
            'market.dispute', 'market.review', 'market.manage', 'market.product', 'market.cart', 'market.order', 'market.chat'] as $name) {
            $source = file_get_contents(app('view')->getFinder()->find($name));
            $compiled = app('blade.compiler')->compileString($source);
            try { $tokens = token_get_all($compiled, TOKEN_PARSE); }
            catch (\ParseError $e) { $this->fail($name.': '.$e->getMessage()); }
            $this->assertNotEmpty($tokens, $name);
        }
    }

    public function test_registered_routes_resolve_existing_controller_actions(): void
    {
        foreach (Route::getRoutes() as $route) {
            if ($route->getActionName() === 'Closure') continue;
            [$class,$method]=array_pad(explode('@',$route->getActionName(),2),2,'__invoke');
            $this->assertTrue(method_exists($class,$method),$route->uri().' points to a missing action');
        }
    }

    public function test_google_registration_resumes_only_with_callback_session(): void
    {
        $this->get('/auth/register-google')->assertRedirect(route('beranda'))->assertSessionHas('open_login_modal',true);
        $this->withSession(['google_register_data'=>['id'=>'oauth-test','email'=>'test@example.test','name'=>'Warga']])
            ->get('/auth/register-google')->assertRedirect(route('beranda'))->assertSessionHas('show_google_register',true);
    }

    public function test_gas_receipt_and_payment_reject_another_accounts_order(): void
    {
        Schema::create('gas_orders',function (Blueprint $t) {
            $t->id(); $t->integer('user_id'); $t->string('status'); $t->string('payment_method');
            $t->string('order_number'); $t->string('item_name'); $t->integer('quantity'); $t->decimal('price');
            $t->string('proof_of_payment')->nullable(); $t->string('cancellation_status')->nullable(); $t->softDeletes(); $t->timestamps();
        });
        Schema::create('transaction_receipts',function (Blueprint $t) {
            $t->id(); $t->integer('user_id'); $t->integer('booking_id'); $t->string('booking_type');
            $t->string('receipt_number')->nullable(); $t->decimal('total_amount')->nullable();
        });
        DB::table('gas_orders')->insert(['id'=>1,'user_id'=>1,'status'=>'completed','payment_method'=>'Transfer','order_number'=>'GAS-TEST','item_name'=>'Gas','quantity'=>2,'price'=>10000]);
        $this->actingAs($this->user(6),'sanctum')->getJson('/api/gas/receipt/1')->assertNotFound();
        $this->postJson('/api/gas/payment/1')->assertNotFound();
        $this->actingAs($this->user(1),'sanctum')->getJson('/api/gas/receipt/1')->assertOk()->assertJsonPath('data.order_number','GAS-TEST');
        Storage::fake('public');
        $this->postJson('/api/gas/payment/1',['payment_proof'=>UploadedFile::fake()->image('proof.jpg')])->assertStatus(409);
        $this->assertSame([],Storage::disk('public')->allFiles());
        $this->assertNull(DB::table('gas_orders')->value('proof_of_payment'));
    }

    public function test_market_payment_is_owned_and_does_not_expose_all_region_settings(): void
    {
        Schema::table('regions',fn (Blueprint $t)=>$t->text('settings')->nullable());
        Schema::create('pasar_orders',function (Blueprint $t) {
            $t->id(); $t->integer('user_id'); $t->integer('region_id'); $t->string('status');
            $t->string('order_number'); $t->string('payment_method'); $t->decimal('total_amount');
            $t->decimal('grand_total'); $t->decimal('shipping_cost'); $t->string('proof_of_payment')->nullable(); $t->softDeletes();
        });
        DB::table('regions')->where('id',1)->update(['settings'=>json_encode(['rekening_bank'=>'Bank Uji','rekening_nomor'=>'1234','internal_setting'=>'private'])]);
        DB::table('pasar_orders')->insert(['id'=>1,'user_id'=>1,'region_id'=>1,'status'=>'pending','order_number'=>'PASAR-TEST','payment_method'=>'Transfer','total_amount'=>10000,'grand_total'=>10000,'shipping_cost'=>0]);
        $this->actingAs($this->user(6),'sanctum')->getJson('/api/pasar-daerah/orders/1/payment')->assertNotFound();
        $this->actingAs($this->user(1),'sanctum')->getJson('/api/pasar-daerah/orders/1/payment')->assertOk()
            ->assertJsonPath('data.payment_instructions.account_number','1234')->assertDontSee('internal_setting');
        DB::table('pasar_orders')->where('id',1)->update(['status'=>'completed']);
        $this->getJson('/api/pasar-daerah/orders/1/payment')->assertOk()->assertJsonPath('data.payment_instructions',null);
    }

    public function test_mutasi_identity_image_is_private_and_scoped(): void
    {
        Schema::create('mutasi_penduduks',function (Blueprint $t) {
            $t->id(); $t->integer('from_region_id'); $t->integer('to_region_id'); $t->string('ktp_image_path');
        });
        Storage::fake('private');
        Storage::disk('private')->put('mutasi_ktp/test.png',base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aG9kAAAAASUVORK5CYII='));
        DB::table('mutasi_penduduks')->insert(['id'=>1,'from_region_id'=>3,'to_region_id'=>4,'ktp_image_path'=>'mutasi_ktp/test.png']);
        $this->actingAs($this->user(7))->get('/admin/warga/mutasi/1/ktp')->assertNotFound();
        $response=$this->actingAs($this->user(4))->get('/admin/warga/mutasi/1/ktp')->assertOk();
        $this->assertStringContainsString('no-store',$response->headers->get('Cache-Control'));
        DB::table('mutasi_penduduks')->where('id',1)->update(['ktp_image_path'=>'../test.png']);
        $this->get('/admin/warga/mutasi/1/ktp')->assertNotFound();
    }
}
