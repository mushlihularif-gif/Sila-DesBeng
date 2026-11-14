<?php
namespace Tests\Feature;

use App\Http\Controllers\{Admin\GasKkAdminController, User\GasKkVerificationController};
use App\Models\{AdminNotification, FamilyCard, FamilyMember, Gas, GasOrder, Region, User, WalletTransaction};
use App\Services\{GasOrdering, GasOrderActions, ReceiptGeneratorService};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\{Request, UploadedFile};
use Illuminate\Support\Facades\{DB, Schema, Storage};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\Support\EditorialDatabase;
use Tests\TestCase;

class GasAuditTest extends TestCase
{
    use EditorialDatabase { setUp as editorialSetUp; }

    protected function setUp(): void
    {
        $this->editorialSetUp();
        Storage::fake('private'); Storage::fake('public');
        $this->mock(ReceiptGeneratorService::class)->shouldReceive('generateGasReceipt')->andReturn('receipts/test.png');
        Schema::table('regions', function (Blueprint $t) { $t->json('settings')->nullable(); $t->json('payment_info')->nullable(); $t->boolean('is_gas_crisis')->default(false); $t->timestamps(); });
        Schema::table('users', fn (Blueprint $t) => $t->string('nik_hash')->nullable());
        DB::table('users')->update(['email'=>'uji@example.test']);
        foreach ([1,6,8] as $id) DB::table('users')->where('id',$id)->update(['nik_hash'=>hash_hmac('sha256', str_repeat((string)$id,16),config('app.key'))]);
        foreach (['2026_06_15_182007_create_services_table.php','2026_06_15_182008_create_region_services_table.php','2026_06_22_151200_add_is_exclusive_to_region_services_table.php','2026_08_24_215518_create_family_cards_table.php','2026_08_24_215518_create_family_members_table.php'] as $file) (require database_path('migrations/'.$file))->up();
        Schema::create('system_settings', fn (Blueprint $t) => $t->id());
        Schema::create('gas', function (Blueprint $t) {
            $t->id(); $t->integer('region_id'); $t->string('jenis_gas'); $t->integer('stok')->default(10); $t->decimal('harga_satuan',10,2)->default(22000); $t->string('status')->default('tersedia');
            foreach (['deskripsi','kategori','lokasi','satuan','foto','foto_2','foto_3','latitude','longitude'] as $col) $t->string($col)->nullable(); $t->timestamps();
        });
        Schema::create('gas_orders', function (Blueprint $t) {
            $t->id(); $t->integer('user_id'); $t->integer('gas_id'); $t->uuid('uuid')->unique(); $t->string('order_number')->unique(); $t->integer('quantity'); $t->decimal('price',10,2); $t->date('order_date');
            foreach (['item_name','delivery_method','payment_method','address','full_name','email','status','proof_of_payment','receipt_path','nomor_kk','nomor_kk_hash','rejection_reason','delivery_proof_image','cancellation_status','cancellation_reason_user','admin_cancellation_response'] as $col) $t->string($col)->nullable();
            foreach (['confirmed_at','delivery_time','arrival_time','completion_time','cancellation_requested_at'] as $col) $t->timestamp($col)->nullable();
            $t->integer('handled_by')->nullable(); $t->decimal('latitude')->nullable(); $t->decimal('longitude')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        (require database_path('migrations/2026_11_12_000001_harden_gas_order_submission.php'))->up();
        Schema::create('transaction_receipts', function (Blueprint $t) {
            $t->id(); foreach (['booking_type','receipt_number','item_name','payment_method'] as $col) $t->string($col); foreach (['booking_id','user_id','region_id','quantity'] as $col) $t->integer($col); $t->decimal('total_amount',12,2); $t->timestamps();
        });
        Schema::create('wallet_transactions', function (Blueprint $t) {
            $t->id(); foreach (['region_id','reference_id'] as $col) $t->integer($col); $t->integer('verified_by')->nullable();
            foreach (['type','source','reference_type','status','proof_path','notes'] as $col) $t->string($col)->nullable(); $t->decimal('amount',12,2); $t->timestamps();
        });
        Schema::create('admin_notifications', function (Blueprint $t) {
            $t->id(); $t->string('type'); $t->integer('reference_id'); $t->integer('region_id'); $t->string('title'); $t->text('message'); $t->boolean('is_read'); $t->timestamps();
        });
        Schema::create('activity_log', function (Blueprint $t) {
            $t->id(); $t->integer('user_id'); $t->string('action'); $t->text('description'); $t->string('ip_address')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('saldo_warga', function (Blueprint $t) { $t->id(); $t->string('type'); $t->string('reference_type'); $t->integer('reference_id'); });
        Gas::create(['id'=>1,'region_id'=>1,'jenis_gas'=>'LPG 3 Kg','stok'=>10,'harga_satuan'=>22000,'status'=>'tersedia']);
        Gas::create(['id'=>2,'region_id'=>5,'jenis_gas'=>'LPG 12 Kg','stok'=>10,'harga_satuan'=>150000,'status'=>'tersedia']);
        $this->actingAs($this->user(1));
    }

    private function req(array $data = [], int $user = 1): Request
    {
        $request = Request::create('/gas/booking', 'POST', $data + ['gas_id'=>1,'quantity'=>1,'buyer_name'=>'Warga Uji','buyer_address'=>'Alamat uji','delivery_method'=>'jemput','payment_method'=>'tunai']);
        $request->setUserResolver(fn () => $this->user($user)); $request->headers->set('Accept','application/json');
        return $request;
    }
    private function order(array $data = [], int $user = 1): GasOrder
    {
        $result = app(GasOrdering::class)->store($this->req($data,$user)); return GasOrder::findOrFail($result['order_id']);
    }
    private function act(GasOrder $order, string $action, int $admin = 4)
    {
        $this->actingAs($this->user($admin)); return app(GasOrderActions::class)->admin($this->req(['reason'=>'Bukti tidak jelas'], $admin), $order->id, $action);
    }
    private function blocked(callable $call, int $status): void
    {
        try { $call(); $this->fail('Request should be blocked'); }
        catch (HttpExceptionInterface $e) { $this->assertSame($status,$e->getStatusCode()); }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) { $this->assertSame(404, $status); }
        catch (ValidationException $e) { $this->assertSame($status,$e->status); }
    }
    private function family(): FamilyCard
    {
        $family=FamilyCard::create(['status'=>'approved','submitted_by'=>1,'no_kk_hash'=>'household']);
        foreach ([1,8] as $id) FamilyMember::create(['family_card_id'=>$family->id,'nik_hash'=>$this->user($id)->nik_hash]);
        DB::table('users')->where('id',8)->update(['verification_status'=>'verified']);
        Region::find(1)->update(['settings'=>['crisis_mode_gas'=>true,'gas_quota_limit'=>1,'gas_quota_days'=>7]]);
        return $family;
    }

    public function test_rt_resident_can_order_from_own_village_and_retry_without_duplicates(): void
    {
        $key=(string)Str::uuid(); $a=$this->order(['idempotency_key'=>$key]); $b=$this->order(['idempotency_key'=>$key]);
        $this->assertSame($a->id,$b->id); $this->assertDatabaseCount('gas_orders',1); $this->assertDatabaseCount('wallet_transactions',1);
        $this->assertDatabaseCount('transaction_receipts',1); $this->assertSame(10,Gas::first()->stok);
        $this->assertEquals(22000,WalletTransaction::first()->amount);
        $this->blocked(fn ()=>$this->order(['idempotency_key'=>$key,'quantity'=>2]),409);
    }
    public function test_web_and_api_reject_foreign_closed_products(): void
    {
        foreach ([\App\Http\Controllers\User\GasBookingController::class,\App\Http\Controllers\Api\GasBookingController::class] as $controller) $this->blocked(fn()=>app($controller)->store($this->req(['gas_id'=>2])),403);
        $this->assertDatabaseCount('gas_orders',0);
    }
    public function test_explicit_service_visibility_respects_exclusive_and_disabled_flags(): void
    {
        DB::table('services')->insert(['id'=>1,'name'=>'Gas','slug'=>'penjualan-gas']);
        DB::table('region_services')->insert(['region_id'=>5,'service_id'=>1,'is_active'=>true,'is_exclusive'=>false]);
        $this->assertTrue(app(GasOrdering::class)->visibleQuery($this->user(1))->whereKey(2)->exists());
        DB::table('region_services')->update(['is_exclusive'=>true]);
        $this->assertFalse(app(GasOrdering::class)->visibleQuery($this->user(1))->whereKey(2)->exists());
        DB::table('region_services')->insert(['region_id'=>1,'service_id'=>1,'is_active'=>false,'is_exclusive'=>false]);
        $this->blocked(fn()=>$this->order(),403);
    }
    public function test_stock_methods_and_delivery_are_checked_at_submission(): void
    {
        $this->blocked(fn()=>$this->order(['quantity'=>11]),422);
        $this->blocked(fn()=>$this->order(['payment_method'=>'transfer']),422);
        Region::find(1)->update(['payment_info'=>['gas_delivery_jemput_active'=>false]]);
        $this->blocked(fn()=>$this->order(),422);
        Gas::find(1)->update(['status'=>'dipesan']); $this->blocked(fn()=>$this->order(['delivery_method'=>'antar']),422);
        $this->assertDatabaseCount('gas_orders',0);
    }
    public function test_kk_quota_includes_pending_orders_by_other_family_members_even_if_archived(): void
    {
        $this->family(); $order=$this->order(); $order->delete();
        $this->blocked(fn()=>$this->order([],8),422);
        DB::table('gas_orders')->update(['status'=>'cancelled']);
        $this->assertSame('pending',$this->order([],8)->status);
    }
    public function test_crisis_requires_verified_identity_and_approved_kk_only_for_three_kg(): void
    {
        DB::table('regions')->where('id',1)->update(['is_gas_crisis'=>true]); $this->blocked(fn()=>$this->order(),422);
        $this->family(); DB::table('users')->where('id',1)->update(['verification_status'=>'pending']);
        $this->blocked(fn()=>$this->order(),422);
        Gas::find(1)->update(['jenis_gas'=>'LPG 12 Kg']); $this->assertSame('pending',$this->order()->status);
    }
    public function test_order_failure_rolls_back_money_receipt_and_uploaded_file(): void
    {
        Region::find(1)->update(['payment_info'=>['bank_name'=>'Bank Uji','account_number'=>'123']]);
        $request=$this->req(['payment_method'=>'transfer']); $request->files->set('payment_proof',UploadedFile::fake()->image('proof.jpg'));
        AdminNotification::creating(fn()=>throw new \RuntimeException('fail notification'));
        try { app(GasOrdering::class)->store($request); $this->fail('Expected rollback'); }
        catch (\RuntimeException $e) { $this->assertSame('fail notification',$e->getMessage()); }
        finally { AdminNotification::flushEventListeners(); }
        foreach (['gas_orders','wallet_transactions','transaction_receipts'] as $table) $this->assertDatabaseCount($table,0);
        $this->assertSame([],Storage::disk('public')->allFiles());
    }
    public function test_cash_delivery_and_payment_are_separate_and_stock_cannot_decrement_twice(): void
    {
        $order=$this->order(); $this->act($order,'confirmed');
        $this->assertSame(9,Gas::find(1)->stok); $this->blocked(fn()=>$this->act($order,'confirmed'),409);
        $this->blocked(fn()=>$this->act($order,'completed'),409);
        $this->act($order,'verify'); $this->assertSame('confirmed',$order->fresh()->status);
        $this->assertSame('verified',WalletTransaction::first()->status);
        $this->act($order,'completed'); $this->blocked(fn()=>$this->act($order,'in_delivery'),409);
        $this->assertSame(9,Gas::find(1)->stok);
    }
    public function test_admin_cannot_manage_orders_or_products_of_another_village(): void
    {
        $order=$this->order(); $this->blocked(fn()=>$this->act($order,'confirmed',7),404);
        $this->blocked(fn()=>app(\App\Http\Controllers\Admin\GasController::class)->destroy(1),404);
        $this->actingAs($this->user(4)); $this->blocked(fn()=>app(\App\Http\Controllers\Admin\GasController::class)->destroy(1),409);
        $this->assertDatabaseCount('gas_orders',1); $this->assertDatabaseCount('gas',2);
    }
    public function test_transfer_must_have_verified_proof_before_approval_and_can_resubmit(): void
    {
        Region::find(1)->update(['payment_info'=>['bank_name'=>'Bank Uji','account_number'=>'123']]);
        $order=$this->order(['payment_method'=>'transfer']);
        $this->blocked(fn()=>$this->act($order,'confirmed'),409); $this->blocked(fn()=>$this->act($order,'verify'),422);
        $upload=$this->req(); $upload->files->set('payment_proof',UploadedFile::fake()->image('proof.jpg'));
        app(\App\Http\Controllers\Api\GasBookingController::class)->uploadPayment($upload,$order->id);
        $this->act($order,'reject-payment'); $this->assertNull($order->fresh()->proof_of_payment);
        $this->assertSame('pending',$order->fresh()->status);
        app(\App\Http\Controllers\Api\GasBookingController::class)->uploadPayment($upload,$order->id);
        $this->act($order,'verify'); $this->act($order,'confirmed');
        $this->assertSame('confirmed',$order->fresh()->status);
    }
    public function test_unpaid_cancellation_closes_ledger_without_increasing_stock(): void
    {
        $order=$this->order(); app(GasOrderActions::class)->cancelUnpaid($this->req(),$order->id);
        $this->assertSame('cancelled',$order->fresh()->status); $this->assertSame(10,Gas::find(1)->stok);
        $this->assertSame('rejected',WalletTransaction::first()->status);
        $this->blocked(fn()=>$this->act($order,'verify'),409);
    }
    public function test_cancellation_request_blocks_concurrent_admin_processing(): void
    {
        $order=$this->order(); app(GasOrderActions::class)->requestCancellation($this->req(['reason'=>'Salah jumlah']),$order->id);
        $this->blocked(fn()=>$this->act($order,'confirmed'),409); $this->blocked(fn()=>$this->act($order,'verify'),409);
        $this->assertSame(10,Gas::find(1)->stok);
    }
    public function test_encrypted_kk_submission_approval_and_cleanup(): void
    {
        $request=$this->req(); $request->files->set('kk_image',UploadedFile::fake()->image('kk.jpg'));
        app(GasKkVerificationController::class)->store($request);
        $family=FamilyCard::first(); $path=$family->kk_image_path;
        $this->assertNull($family->no_kk_hash); $this->assertStringStartsWith('$chacha20$',Storage::disk('private')->get($path));
        $this->blocked(fn()=>app(GasKkVerificationController::class)->store($request),409);
        $this->actingAs($this->user(7)); $this->blocked(fn()=>app(GasKkAdminController::class)->showImage($family->id),404);
        $this->actingAs($this->user(4));
        app(GasKkAdminController::class)->approve($this->req(['no_kk'=>'1234567890123456','no_kk_confirmation'=>'1234567890123456','kepala_keluarga'=>'Nama Uji','niks'=>str_repeat('1',16)],4),$family->id);
        $this->assertSame('approved',$family->fresh()->status); $this->assertNotNull(app(GasOrdering::class)->household($this->user(1)));
        Storage::disk('private')->assertMissing($path); $this->assertNull($family->fresh()->kk_image_path);
        $this->blocked(fn()=>app(GasKkAdminController::class)->showImage($family->id),404);
    }
    public function test_kk_approval_requires_submitter_and_never_steals_an_existing_membership(): void
    {
        $existing=$this->family(); $pending=FamilyCard::create(['submitted_by'=>1,'status'=>'pending']);
        $this->actingAs($this->user(4));
        foreach ([str_repeat('6',16),str_repeat('1',16)] as $nik) $this->blocked(fn()=>app(GasKkAdminController::class)->approve($this->req(['no_kk'=>'1234567890123456','no_kk_confirmation'=>'1234567890123456','kepala_keluarga'=>'Uji','niks'=>$nik],4),$pending->id),422);
        $this->assertSame('pending',$pending->fresh()->status); $this->assertSame(2,$existing->members()->count());
    }

    public function test_delivery_cannot_skip_shipping_or_complete_without_evidence(): void
    {
        $order=$this->order(['delivery_method'=>'antar']); $this->act($order,'confirmed'); $this->act($order,'verify');
        $this->blocked(fn()=>$this->act($order,'completed'),409);
        $this->act($order,'in_delivery'); $this->blocked(fn()=>$this->act($order,'arrived'),422);
        $order->refresh()->update(['delivery_proof_image'=>'delivery_proofs/test.jpg']);
        $this->act($order,'arrived'); $this->act($order,'completed');
        $this->assertSame('completed',$order->fresh()->status); $this->assertSame(9,Gas::find(1)->stok);
    }
    public function test_editor_cannot_overwrite_stock_changed_by_an_order(): void
    {
        $order=$this->order(); $this->act($order,'confirmed');
        $data=['jenis_gas'=>'LPG 3 Kg','deskripsi'=>'Gas uji','harga_satuan'=>'22.000','stok'=>20,'original_stock'=>10,'status'=>'tersedia','kategori'=>'Gas','lokasi'=>'Desa','satuan'=>'tabung'];
        $this->blocked(fn()=>app(\App\Http\Controllers\Admin\GasController::class)->update($this->req($data,4),1),422);
        $this->assertSame(9,Gas::find(1)->stok);
    }
    public function test_admin_action_panel_shows_actual_next_delivery_step(): void
    {
        $order=$this->order(['delivery_method'=>'antar']); $this->act($order,'confirmed');
        $html=view('admin.aktivitas.partials.gas-actions',['request'=>$order->fresh()])->render();
        $this->assertStringContainsString('Mulai Antar Gas',$html);
        $this->assertStringNotContainsString('Selesaikan Pesanan',$html);
        $this->act($order,'in_delivery');
        $html=view('admin.aktivitas.partials.gas-actions',['request'=>$order->fresh()])->render();
        $this->assertStringContainsString('Unggah Bukti',$html);
        $this->assertStringContainsString('Konfirmasi Uang Diterima',$html);
    }
    public function test_kk_rejection_deletes_document_and_allows_new_submission(): void
    {
        $path='kk_images/rejected.enc'; Storage::disk('private')->put($path,'encrypted');
        $kk=FamilyCard::create(['submitted_by'=>1,'status'=>'pending','kk_image_path'=>$path]);
        $this->actingAs($this->user(4)); app(GasKkAdminController::class)->reject($this->req(['reason'=>'Foto tidak terbaca'],4),$kk->id);
        Storage::disk('private')->assertMissing($path); $this->assertSame('rejected',$kk->fresh()->status);
        $request=$this->req(); $request->files->set('kk_image',UploadedFile::fake()->image('kk.jpg'));
        app(GasKkVerificationController::class)->store($request); $this->assertSame(1,FamilyCard::where('status','pending')->count());
    }
    public function test_http_controller_actions_use_the_same_financial_guards(): void
    {
        $order=$this->order(); $this->actingAs($this->user(4));
        app(\App\Http\Controllers\Admin\TransactionController::class)->verify($this->req([],4),$order->id,'gas');
        $this->assertSame('pending',$order->fresh()->status);
        app(\App\Http\Controllers\Admin\RequestController::class)->approve($this->req([],4),$order->id,'gas');
        $this->assertSame('confirmed',$order->fresh()->status);
        $this->assertSame(9,Gas::find(1)->stok);
        $this->blocked(fn()=>app(\App\Http\Controllers\Api\GasBookingController::class)->getReceipt($this->req([],6),$order->id),404);
    }

    public function test_gas_views_compile_and_payment_page_renders_pending_and_cancelled(): void
    {
        foreach (['users.gas-booking','users.gas-detail','users.gas-payment','users.gas-sales','admin.gas.verifikasi_kk','admin.aktivitas.request-detail','admin.aktivitas.partials.gas-actions'] as $name) {
            $compiled=app('blade.compiler')->compileString(file_get_contents(app('view')->getFinder()->find($name)));
            $this->assertNotEmpty(token_get_all($compiled,TOKEN_PARSE));
        }
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        $order=$this->order();
        $this->view('users.gas-payment',['order'=>$order,'setting'=>\App\Support\ProfilPembayaranWilayah::untuk(1),'paid'=>false])->assertSee('Menunggu Konfirmasi')->assertDontSee('Simulasikan konfirmasi');
        $order->update(['status'=>'cancelled']);
        $this->view('users.gas-payment',['order'=>$order,'setting'=>\App\Support\ProfilPembayaranWilayah::untuk(1),'paid'=>false])->assertSee('Pesanan Dibatalkan')->assertDontSee('Kirim Bukti Pembayaran');
    }
    public function test_mistyped_kk_confirmation_cannot_approve_or_attach_any_nik(): void
    {
        $pending = FamilyCard::create(['submitted_by'=>1,'status'=>'pending']);
        $this->actingAs($this->user(4));
        $this->blocked(fn () => app(GasKkAdminController::class)->approve($this->req([
            'no_kk'=>'1234567890123456','no_kk_confirmation'=>'1234567890123457',
            'kepala_keluarga'=>'Uji','niks'=>str_repeat('1',16),
        ],4),$pending->id),422);
        $this->assertSame('pending',$pending->fresh()->status);
        $this->assertSame(0,$pending->members()->count());
    }

}
