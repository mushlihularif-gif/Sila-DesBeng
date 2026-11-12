<?php
namespace Tests\Feature;

use App\Models\{AdminNotification, Barang, RentalBooking, Region, User, WalletTransaction};
use App\Services\{RentalOrdering, RentalOrderActions, ReceiptGeneratorService};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\{Request, UploadedFile};
use Illuminate\Support\Facades\{DB, Schema, Storage};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\Support\EditorialDatabase;
use Tests\TestCase;

class RentalAuditTest extends TestCase
{
    use EditorialDatabase { setUp as editorialSetUp; }
    protected function setUp(): void
    {
        $this->editorialSetUp();
        Storage::fake('public');
        $this->mock(ReceiptGeneratorService::class)->shouldReceive('generateRentalReceipt')->andReturn('receipts/test.png');
        Schema::table('regions', function (Blueprint $t) { $t->json('payment_info')->nullable(); $t->timestamps(); });
        DB::table('users')->update(['email'=>'uji@example.test']);
        foreach (['2026_06_15_182007_create_services_table.php','2026_06_15_182008_create_region_services_table.php','2026_06_22_151200_add_is_exclusive_to_region_services_table.php'] as $file) (require database_path('migrations/'.$file))->up();
        Schema::create('system_settings', fn (Blueprint $t) => $t->id());
        Schema::create('barang', function (Blueprint $t) {
            $t->id(); $t->integer('region_id'); $t->string('nama_barang'); $t->integer('stok')->default(10); $t->decimal('harga_sewa',12,2)->default(700000); $t->string('status')->default('tersedia');
            foreach (['deskripsi','kategori','lokasi','satuan','foto','foto_2','foto_3','latitude','longitude'] as $col) $t->string($col)->nullable(); $t->timestamps();
        });
        (require database_path('migrations/2025_12_04_000000_create_rental_bookings_table.php'))->up();
        Schema::table('rental_bookings', function (Blueprint $t) {
            $t->uuid('uuid')->unique(); $t->string('order_number')->unique(); $t->integer('region_id')->nullable(); $t->integer('handled_by')->nullable();
            foreach (['rental_purpose','receipt_path','delivery_proof_image','cancellation_reason','cancellation_status','admin_cancellation_response'] as $col) $t->text($col)->nullable();
            foreach (['delivery_time','arrival_time','return_time','completion_time','cancellation_requested_at'] as $col) $t->timestamp($col)->nullable();
            $t->softDeletes();
        });
        // Production migrations expand the enum; SQLite fixtures use an unrestricted status string.
        Schema::table('rental_bookings', fn (Blueprint $t) => $t->string('status')->default('pending')->change());
        (require database_path('migrations/2026_11_12_000002_harden_rental_order_submission.php'))->up();
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
        Barang::create(['id'=>1,'region_id'=>1,'nama_barang'=>'Sound System','stok'=>10,'harga_sewa'=>700000,'status'=>'tersedia']);
        Barang::create(['id'=>2,'region_id'=>5,'nama_barang'=>'Alat Wilayah B','stok'=>10,'harga_sewa'=>100000,'status'=>'tersedia']);
        $this->actingAs($this->user(1));
    }

    private function req(array $data = [], int $user = 1): Request
    {
        $request = Request::create('/rental/booking', 'POST', $data + ['barang_id'=>1,'quantity'=>1,'recipient_name'=>'Warga Uji','delivery_address'=>'Alamat uji','delivery_method'=>'jemput','payment_method'=>'tunai',
            'rental_purpose'=>'Kegiatan warga', 'start_date'=>now()->toDateString(),'end_date'=>now()->addDays(2)->toDateString(),'sop_accepted'=>'1']);
        $request->setUserResolver(fn () => $this->user($user)); $request->headers->set('Accept','application/json');
        return $request;
    }
    private function order(array $data = [], int $user = 1): RentalBooking
    {
        return app(RentalOrdering::class)->store($this->req($data,$user))[0];
    }
    private function act(RentalBooking $order, string $action, array $data = [], int $admin = 4)
    {
        $this->actingAs($this->user($admin)); return app(RentalOrderActions::class)->admin($this->req($data + ['reason'=>'Bukti tidak jelas','return_time'=>now()->toDateTimeString(),'returned_quantity'=>$order->quantity], $admin), $order->id, $action);
    }
    private function blocked(callable $call, int $status): void
    {
        try { $call(); $this->fail('Request should be blocked'); }
        catch (HttpExceptionInterface $e) { $this->assertSame($status,$e->getStatusCode()); }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) { $this->assertSame(404,$status); }
        catch (ValidationException $e) { $this->assertSame($status,$e->status); }
    }

    public function test_single_use_price_and_atomic_receipt_ledger_survive_price_changes_and_retries(): void
    {
        $key=(string)Str::uuid(); $a=$this->order(['idempotency_key'=>$key,'quantity'=>2,'total_amount'=>1]);
        $this->assertEquals(1400000,$a->total_amount); $this->assertSame(3,$a->days_count);
        $this->assertEquals(1400000,WalletTransaction::first()->amount);
        $this->assertEquals(1400000,DB::table('transaction_receipts')->value('total_amount'));
        $this->assertSame($a->order_number,DB::table('transaction_receipts')->value('receipt_number'));
        $this->assertSame(1,DB::table('transaction_receipts')->value('region_id'));
        Barang::find(1)->update(['harga_sewa'=>900000]);
        $this->assertEquals(700000,$a->fresh()->booked_unit_price);
        $b=$this->order(['idempotency_key'=>$key,'quantity'=>2,'total_amount'=>1]); $this->assertSame($a->id,$b->id);
        foreach (['rental_bookings','wallet_transactions','transaction_receipts','admin_notifications','notifications'] as $table) $this->assertDatabaseCount($table,1);
        $this->assertSame(10,Barang::first()->stok);
        $this->blocked(fn()=>$this->order(['idempotency_key'=>$key,'quantity'=>1]),409);
    }
    public function test_web_and_api_require_identity_scope_stock_and_sop(): void
    {
        foreach ([\App\Http\Controllers\User\RentalBookingController::class,\App\Http\Controllers\Api\RentalBookingApiController::class] as $controller) {
            $this->blocked(fn()=>app($controller)->store($this->req([],8)),403);
            $this->blocked(fn()=>app($controller)->store($this->req(['barang_id'=>2])),403);
            $this->blocked(fn()=>app($controller)->store($this->req(['quantity'=>11])),422);
            $this->blocked(fn()=>app($controller)->store($this->req(['sop_accepted'=>0])),422);
        }
        $this->assertDatabaseCount('rental_bookings',0);
    }
    public function test_invalid_dates_payment_and_delivery_cannot_be_submitted(): void
    {
        foreach ([['start_date'=>now()->toDateTimeString()],['end_date'=>now()->subDay()->toDateString()],['quantity'=>51],['payment_method'=>'gateway'],['payment_method'=>'transfer'],['rental_purpose'=>'']] as $data) $this->blocked(fn()=>$this->order($data),422);
        Region::find(1)->update(['payment_info'=>['alat_delivery_jemput_active'=>false]]); $this->blocked(fn()=>$this->order(),422);
        Barang::find(1)->update(['status'=>'disewa']); $this->blocked(fn()=>$this->order(['delivery_method'=>'antar']),422);
        $this->assertDatabaseCount('rental_bookings',0);
    }
    public function test_public_service_visibility_respects_disabled_and_exclusive(): void
    {
        DB::table('services')->insert(['id'=>1,'name'=>'Alat','slug'=>'penyewaan-alat']);
        DB::table('region_services')->insert(['region_id'=>5,'service_id'=>1,'is_active'=>true,'is_exclusive'=>false]);
        $this->assertTrue(app(RentalOrdering::class)->visibleQuery($this->user(1))->whereKey(2)->exists());
        DB::table('region_services')->update(['is_exclusive'=>true]);
        $this->assertFalse(app(RentalOrdering::class)->visibleQuery($this->user(1))->whereKey(2)->exists());
        DB::table('region_services')->insert(['region_id'=>1,'service_id'=>1,'is_active'=>false,'is_exclusive'=>false]);
        $this->blocked(fn()=>$this->order(),403);
    }
    public function test_package_is_atomic_priced_once_and_idempotent(): void
    {
        Barang::find(2)->update(['region_id'=>1]);
        $req=$this->req(['items'=>[['barang_id'=>2,'quantity'=>2],['barang_id'=>1,'quantity'=>1]],'idempotency_key'=>(string)Str::uuid()]);
        $response=app(\App\Http\Controllers\Api\RentalBookingApiController::class)->storePackage($req);
        $this->assertSame(201,$response->getStatusCode()); $this->assertEquals(900000,$response->getData()->data->total_amount);
        app(\App\Http\Controllers\Api\RentalBookingApiController::class)->storePackage($req);
        foreach (['rental_bookings','wallet_transactions','transaction_receipts'] as $table) $this->assertDatabaseCount($table,2);
    }
    public function test_package_failure_cleans_up_orders_ledger_and_proof_files(): void
    {
        Barang::find(2)->update(['region_id'=>1,'stok'=>0]);
        Region::find(1)->update(['payment_info'=>['bank_name'=>'Bank Uji','account_number'=>'123']]);
        $req=$this->req(['items'=>[['barang_id'=>1,'quantity'=>1],['barang_id'=>2,'quantity'=>1]],'payment_method'=>'transfer']);
        $req->files->set('payment_proof',UploadedFile::fake()->image('proof.jpg'));
        $this->blocked(fn()=>app(RentalOrdering::class)->store($req,true),422);
        foreach (['rental_bookings','wallet_transactions','transaction_receipts','notifications'] as $table) $this->assertDatabaseCount($table,0);
        $this->assertSame([],Storage::disk('public')->allFiles());
    }
    public function test_failure_to_record_admin_notification_rolls_back_everything(): void
    {
        AdminNotification::creating(fn()=>throw new \RuntimeException('notification failure'));
        try { $this->order(); $this->fail('Expected failure'); }
        catch (\RuntimeException $e) { $this->assertSame('notification failure',$e->getMessage()); }
        finally { AdminNotification::flushEventListeners(); }
        foreach (['rental_bookings','wallet_transactions','transaction_receipts'] as $table) $this->assertDatabaseCount($table,0);
    }
    public function test_payment_never_completes_or_restocks_and_return_only_happens_once(): void
    {
        $order=$this->order(['quantity'=>2]); $this->act($order,'verify');
        $this->assertSame('pending',$order->fresh()->status); $this->assertSame(10,Barang::first()->stok);
        $this->act($order,'confirmed'); $this->assertSame(8,Barang::first()->stok);
        $this->blocked(fn()=>$this->act($order,'confirmed'),409); $this->blocked(fn()=>$this->act($order,'returned'),409);
        $this->blocked(fn()=>$this->act($order,'completed'),409);
        $this->act($order,'arrived'); $this->act($order,'returned');
        $this->assertSame(10,Barang::first()->stok); $this->assertSame('completed',$order->fresh()->status);
        $this->blocked(fn()=>$this->act($order,'returned'),409); $this->blocked(fn()=>$this->act($order,'reject'),409);
        $this->assertSame('verified',WalletTransaction::first()->status);
    }
    public function test_return_requires_received_payment_valid_time_and_damage_notes(): void
    {
        $order=$this->order(['quantity'=>2]); $this->act($order,'confirmed'); $this->act($order,'arrived');
        $this->blocked(fn()=>$this->act($order,'returned'),409); $this->act($order,'verify');
        $this->blocked(fn()=>$this->act($order,'returned',['return_time'=>now()->addDay()->toDateTimeString()]),422);
        $this->blocked(fn()=>$this->act($order,'returned',['return_time'=>now()->subDay()->toDateTimeString()]),422);
        $this->blocked(fn()=>$this->act($order,'returned',['returned_quantity'=>3]),422);
        $this->blocked(fn()=>$this->act($order,'returned',['returned_quantity'=>1]),422);
        $this->act($order,'returned',['returned_quantity'=>1,'return_notes'=>'Satu alat rusak, ditangani pengelola.']);
        $this->assertSame(9,Barang::first()->stok); $this->assertSame(1,$order->fresh()->returned_quantity);
    }
    public function test_foreign_admin_and_rt_cannot_change_orders_or_products(): void
    {
        $order=$this->order(); $this->blocked(fn()=>$this->act($order,'verify',[],7),404);
        $this->blocked(fn()=>$this->act($order,'confirmed',[],2),403);
        $this->actingAs($this->user(7)); $controller=app(\App\Http\Controllers\Admin\UnitPenyewaanController::class);
        foreach (['show','edit','destroy'] as $method) $this->blocked(fn()=>$controller->$method(1),404);
        $this->actingAs($this->user(4)); $this->blocked(fn()=>$controller->destroy(1),409);
        $this->assertDatabaseCount('barang',2); $this->assertDatabaseCount('rental_bookings',1);
    }
    public function test_admin_controller_paths_cannot_bypass_state_rules(): void
    {
        $order=$this->order(); $this->actingAs($this->user(4)); $req=$this->req(['status'=>'completed'],4);
        $controller=app(\App\Http\Controllers\Admin\TransactionController::class);
        $controller->verify($req,$order->id,'rental'); $this->assertSame('pending',$order->fresh()->status);
        $this->blocked(fn()=>$controller->updateStatus($req,$order->id,'rental','completed'),409);
        $this->blocked(fn()=>app(\App\Http\Controllers\Admin\RequestController::class)->updateStatus($req,'rental',$order->id),409);
    }
    public function test_transfer_proof_matches_ledger_and_can_be_resubmitted_only_when_returned(): void
    {
        Region::find(1)->update(['payment_info'=>['bank_name'=>'Bank Uji','account_number'=>'123']]);
        $order=$this->order(['payment_method'=>'transfer']); $this->blocked(fn()=>$this->act($order,'confirmed'),409); $this->blocked(fn()=>$this->act($order,'verify'),422);
        $req=$this->req(); $req->files->set('payment_proof',UploadedFile::fake()->image('proof.jpg'));
        $controller=app(\App\Http\Controllers\User\RentalBookingController::class);
        $controller->uploadPayment($req,$order->id); $path=$order->fresh()->payment_proof;
        $this->assertSame($path,WalletTransaction::first()->proof_path); $this->assertSame('pending',WalletTransaction::first()->status);
        $this->blocked(fn()=>$controller->uploadPayment($req,$order->id),409);
        $this->act($order,'reject-payment'); Storage::disk('public')->assertMissing($path);
        $controller->uploadPayment($req,$order->id); $this->act($order,'verify'); $this->act($order,'confirmed');
        $this->assertSame('confirmed',$order->fresh()->status); $this->assertSame(9,Barang::first()->stok);
    }
    public function test_pending_cancellation_freezes_processing_and_unpaid_api_cancel_never_restocks(): void
    {
        $order=$this->order(); app(RentalOrderActions::class)->cancelUnpaid($this->req(),$order->id);
        $this->assertSame(10,Barang::first()->stok); $this->blocked(fn()=>app(RentalOrderActions::class)->cancelUnpaid($this->req(),$order->id),409);
        $other=$this->order(); app(RentalOrderActions::class)->requestCancellation($this->req(['reason'=>'Acara batal']),$other->id);
        $this->blocked(fn()=>$this->act($other,'confirmed'),409);
        $this->assertSame('pending',$other->fresh()->status);
    }

    public function test_user_notification_failure_rolls_back_booking(): void
    {
        \App\Models\Notification::creating(fn()=>throw new \RuntimeException('notice failed'));
        try { $this->order(); $this->fail('Expected notice failure'); }
        catch (\RuntimeException $e) { $this->assertSame('notice failed',$e->getMessage()); }
        finally { \App\Models\Notification::flushEventListeners(); }
        foreach (['rental_bookings','wallet_transactions','transaction_receipts','admin_notifications'] as $table) $this->assertDatabaseCount($table,0);
    }
    public function test_proof_ownership_is_checked_and_paid_pending_orders_require_review_to_cancel(): void
    {
        $order=$this->order(); $req=$this->req([],6); $req->files->set('payment_proof',UploadedFile::fake()->image('proof.jpg'));
        $this->blocked(fn()=>app(\App\Http\Controllers\User\RentalBookingController::class)->uploadPayment($req,$order->id),404);
        $this->act($order,'verify');
        $this->blocked(fn()=>app(RentalOrderActions::class)->cancelUnpaid($this->req(),$order->id),409);
        $this->assertSame('verified',WalletTransaction::first()->status);
        $this->assertSame([],Storage::disk('public')->allFiles());
    }
    public function test_delivery_cannot_skip_handover_proof(): void
    {
        $order=$this->order(['delivery_method'=>'antar']); $this->act($order,'confirmed');
        $this->blocked(fn()=>$this->act($order,'arrived'),409); $this->act($order,'in_delivery');
        $this->blocked(fn()=>$this->act($order,'arrived'),422);
        $this->assertSame(9,Barang::first()->stok);
    }
    public function test_admin_stock_edit_conflict_preserves_current_inventory(): void
    {
        $order=$this->order(); $this->act($order,'confirmed');
        $request=$this->req(['nama_barang'=>'Alat','deskripsi'=>'Uji','harga_sewa'=>'700.000','stok'=>20,'original_stock'=>10,
            'status'=>'tersedia','kategori'=>'Alat','lokasi'=>'Desa','satuan'=>'unit'],4);
        $this->blocked(fn()=>app(\App\Http\Controllers\Admin\UnitPenyewaanController::class)->update($request,1),422);
        $this->assertSame(9,Barang::first()->stok);
    }
    public function test_template_controls_follow_payment_and_handover_state(): void
    {
        foreach (['users.rental-booking','users.rental-payment','users.rental-detail','admin.aktivitas.request-detail','admin.aktivitas.partials.rental-actions','admin.unit.penyewaan.edit'] as $name) {
            $compiled=app('blade.compiler')->compileString(file_get_contents(app('view')->getFinder()->find($name)));
            $this->assertNotEmpty(token_get_all($compiled,TOKEN_PARSE));
        }
        $order=$this->order();
        $render=fn()=>view('admin.aktivitas.partials.rental-actions',['request'=>$order->fresh()])->render();
        $this->assertStringContainsString('Konfirmasi Uang Diterima',$render());
        $this->assertStringNotContainsString('Catat Pengembalian Alat',$render());
        $this->act($order,'confirmed'); $this->assertStringContainsString('Alat Sudah Diserahkan',$render());
        $this->act($order,'arrived'); $this->assertStringNotContainsString('Catat Pengembalian Alat',$render());
        $this->act($order,'verify'); $this->assertStringContainsString('Catat Pengembalian Alat',$render());
        $this->act($order,'returned'); $this->assertStringNotContainsString('Catat Pengembalian Alat',$render());
    }
    public function test_receipt_uses_historical_price_and_only_confirms_received_money(): void
    {
        $order=$this->order(); Barang::find(1)->update(['harga_sewa'=>999999]);
        $generator = new class extends ReceiptGeneratorService {
            public array $text = [];
            protected function addText($image,$text,$x,$y,$size,$color,$fontPath,$bold=false) { $this->text[]=$text; }
            protected function addFooterTtd($image,$y,$url,$fontPath,$normalSize,$black) {}
        };
        $path=$generator->generateRentalReceipt($order->fresh());
        $this->assertContains('Rp. 700.000',$generator->text); $this->assertNotContains('Rp. 999.999',$generator->text);
        $receivedIndex=array_search('Uang Diterima',$generator->text,true);
        $this->assertSame('Rp. 0',$generator->text[$receivedIndex+1]);
        $this->act($order,'verify'); $generator->text=[];
        $this->assertSame($path,$generator->generateRentalReceipt($order->fresh()));
        $receivedIndex=array_search('Uang Diterima',$generator->text,true);
        $this->assertSame('Rp. 700.000',$generator->text[$receivedIndex+1]);
        $this->assertCount(1,Storage::disk('public')->allFiles('receipts/rental'));
    }

    public function test_payment_detail_page_and_admin_notification_keep_the_correct_rental_link(): void
    {
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        Region::find(1)->update(['payment_info'=>['bank_name'=>'Bank Uji','account_number'=>'123']]);
        $order=$this->order(['payment_method'=>'transfer']);
        $this->view('users.rental-payment',['order'=>$order,'setting'=>\App\Support\ProfilPembayaranWilayah::untuk(1),'paid'=>false])
            ->assertSee('Kirim Bukti Pembayaran')->assertSee('700.000')->assertSee('Bank Uji');
        $notice=AdminNotification::first();
        $this->assertSame('rental_request',$notice->type);
        $this->assertSame(route('admin.aktivitas.permintaan-pengajuan.show',[$order->id,'rental']),app(\App\Services\NotificationDeliveryService::class)->destination($notice));
        $order->update(['status'=>'cancelled']);
        $this->view('users.rental-payment',['order'=>$order,'setting'=>\App\Support\ProfilPembayaranWilayah::untuk(1),'paid'=>false])
            ->assertSee('Pesanan Dibatalkan')->assertDontSee('Kirim Bukti Pembayaran');
    }
    public function test_mismatched_ledger_cannot_be_verified_or_marked_paid(): void
    {
        $order=$this->order(); WalletTransaction::query()->update(['amount'=>1]);
        $this->blocked(fn()=>$this->act($order,'verify'),409);
        WalletTransaction::query()->update(['status'=>'verified']);
        $this->assertFalse($order->fresh()->paymentReceived());
        $this->assertSame('pending',$order->fresh()->status);
    }
}
