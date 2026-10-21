<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\{RentalBookingApiController, MobilBookingApiController, FasilitasBookingApiController};
use App\Models\{Notification, User};
use App\Services\ReceiptGeneratorService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookingAuditRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(ReceiptGeneratorService::class)->shouldReceive('generateRentalReceipt')->andReturn('receipts/test.pdf');
        // Isolated SQLite fixtures: no dependency on the local/production MySQL database.
        foreach (['barang' => 'nama_barang', 'mobils' => 'nama_mobil', 'fasilitas_umums' => 'nama_fasilitas'] as $table => $name) {
            Schema::create($table, function (Blueprint $t) use ($name) {
                $t->id(); $t->string($name); $t->integer('stok')->nullable();
                $t->decimal('harga_sewa', 12, 2)->default(10000); $t->string('status_biaya')->default('gratis');
                $t->unsignedBigInteger('region_id')->nullable(); $t->string('status')->default('tersedia'); $t->timestamps();
            });
        }
        foreach (['rental_bookings' => 'barang_id', 'mobil_bookings' => 'mobil_id', 'fasilitas_umum_bookings' => 'fasilitas_id'] as $table => $foreign) {
            Schema::create($table, function (Blueprint $t) use ($foreign) {
                $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger($foreign);
                $t->uuid('uuid')->unique(); $t->string('order_number')->unique();
                $t->date('start_date'); $t->date('end_date'); $t->integer('quantity');
                $t->integer('days_count')->nullable(); $t->unsignedBigInteger('region_id')->nullable();
                foreach (['recipient_name', 'delivery_address', 'delivery_method', 'payment_method', 'rental_purpose', 'jenis_acara', 'receipt_path', 'cancellation_reason'] as $column) $t->string($column)->nullable();
                $t->boolean('dengan_supir')->default(false); $t->boolean('butuh_gudang')->default(false);
                $t->decimal('distance_km')->nullable(); $t->decimal('total_amount', 12, 2);
                $t->string('status'); $t->timestamps(); $t->softDeletes();
            });
        }
        Schema::create('wallet_transactions', function (Blueprint $t) {
            $t->id(); $t->integer('region_id'); $t->string('type'); $t->string('source');
            $t->string('reference_type'); $t->integer('reference_id'); $t->string('status');
            $t->string('proof_path')->nullable(); $t->decimal('amount', 12, 2); $t->timestamps(); $t->text('notes')->nullable();
        });
        Schema::create('saldo_warga', function (Blueprint $t) {
            $t->id(); $t->integer('user_id'); $t->integer('region_id'); $t->string('type');
            $t->decimal('amount', 12, 2); $t->string('reference_type'); $t->integer('reference_id');
            $t->string('status'); $t->text('catatan'); $t->timestamps();
        });
        Schema::create('notifications', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id');
            foreach (['type', 'title', 'message', 'link', 'icon'] as $column) $t->string($column);
            $t->timestamps();
        });
    }

    public static function bookingTypes(): array
    {
        return [
            'alat' => [RentalBookingApiController::class, 'barang', 'nama_barang', 'rental_bookings', 'barang_id', 2],
            'mobil' => [MobilBookingApiController::class, 'mobils', 'nama_mobil', 'mobil_bookings', 'mobil_id', 1],
            'fasilitas' => [FasilitasBookingApiController::class, 'fasilitas_umums', 'nama_fasilitas', 'fasilitas_umum_bookings', 'fasilitas_id', 2],
        ];
    }

    private function requestFor(string $foreign, int $quantity): Request
    {
        $request = Request::create('/api/test', 'POST', [
            $foreign => 1, 'quantity' => $quantity,
            'start_date' => now()->addDay()->toDateString(), 'end_date' => now()->addDays(2)->toDateString(),
            'recipient_name' => 'Warga Uji', 'payment_method' => 'tunai',
        ]);
        $user = new User(); $user->id = 41; $user->region_id = 9;
        $request->setUserResolver(fn () => $user);
        return $request;
    }

    #[DataProvider('bookingTypes')]
    public function test_booking_and_notification_are_saved_together($controller, $table, $name, $bookings, $foreign, $quantity): void
    {
        DB::table($table)->insert(['id' => 1, $name => 'Unit Uji', 'stok' => 3, 'region_id' => 7]);
        $response = app($controller)->store($this->requestFor($foreign, $quantity));
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame(1, DB::table($bookings)->count());
        $this->assertSame(3, DB::table($table)->value('stok')); // Pending belum mengalokasikan stok.
        $this->assertSame(41, DB::table('notifications')->value('user_id'));
        $this->assertStringNotContainsString('/unit-penyewaan/', DB::table('notifications')->value('link'));
    }

    #[DataProvider('bookingTypes')]
    public function test_notification_failure_rolls_back_booking_and_stock($controller, $table, $name, $bookings, $foreign, $quantity): void
    {
        DB::table($table)->insert(['id' => 1, $name => 'Unit Uji', 'stok' => 3, 'region_id' => 7]);
        Notification::creating(function () { throw new \RuntimeException('Simulated notification failure'); });
        try {
            app($controller)->store($this->requestFor($foreign, $quantity));
            $this->fail('Expected notification failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated notification failure', $e->getMessage());
        } finally {
            Notification::flushEventListeners();
        }
        $this->assertSame(0, DB::table($bookings)->count());
        $this->assertSame(3, DB::table($table)->value('stok'));
    }

    #[DataProvider('bookingTypes')]
    public function test_insufficient_stock_does_not_create_a_booking($controller, $table, $name, $bookings, $foreign, $quantity): void
    {
        DB::table($table)->insert(['id' => 1, $name => 'Unit Uji', 'stok' => $quantity - 1, 'region_id' => 7]);
        $response = app($controller)->store($this->requestFor($foreign, $quantity));
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(0, DB::table($bookings)->count());
        $this->assertSame($quantity - 1, DB::table($table)->value('stok'));
    }

    #[DataProvider('bookingTypes')]
    public function test_cancel_pending_never_increases_stock_and_cannot_repeat($controller, $table, $name, $bookings, $foreign, $quantity): void
    {
        DB::table($table)->insert(['id' => 1, $name => 'Unit Uji', 'stok' => 3, 'region_id' => 7]);
        $request = $this->requestFor($foreign, $quantity);
        app($controller)->store($request);
        $id = DB::table($bookings)->value('id');
        $this->assertSame(200, app($controller)->cancel($request, $id)->getStatusCode());
        $this->assertSame(422, app($controller)->cancel($request, $id)->getStatusCode());
        $this->assertSame(3, DB::table($table)->value('stok'));
    }

    public function test_facility_commercial_price_is_calculated_from_catalog(): void
    {
        DB::table('fasilitas_umums')->insert(['id' => 1, 'nama_fasilitas' => 'Gedung Uji', 'stok' => 3, 'region_id' => 7, 'status_biaya' => 'berbayar']);
        $request = $this->requestFor('fasilitas_id', 2);
        $request->merge(['jenis_acara' => 'pribadi', 'total_amount' => 1]);
        $response = app(FasilitasBookingApiController::class)->store($request);
        $this->assertEquals(40000, $response->getData()->data->total_amount);
        $this->assertSame('komersial', DB::table('fasilitas_umum_bookings')->value('jenis_acara'));
        $this->assertEquals(40000, DB::table('wallet_transactions')->value('amount'));
    }

    public function test_package_price_ignores_client_total(): void
    {
        DB::table('barang')->insert(['id' => 1, 'nama_barang' => 'Alat Uji', 'stok' => 3, 'region_id' => 7]);
        $request = $this->requestFor('barang_id', 2);
        $request->merge(['total_amount' => 1, 'items' => [['barang_id' => 1, 'quantity' => 2]]]);
        $response = app(RentalBookingApiController::class)->storePackage($request);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertEquals(40000, $response->getData()->data->total_amount);
    }

    public function test_admin_cannot_return_pending_or_reject_completed_rental(): void
    {
        DB::table('barang')->insert(['id' => 1, 'nama_barang' => 'Alat Uji', 'stok' => 3, 'region_id' => 7]);
        app(RentalBookingApiController::class)->store($this->requestFor('barang_id', 1));
        $admin = new User(); $admin->id = 99; $admin->role = 'admin_desa'; $admin->region_id = 7;
        \Illuminate\Support\Facades\Auth::setUser($admin);
        $controller = app(\App\Http\Controllers\Admin\RequestController::class);
        $request = Request::create('/return', 'POST', ['return_time' => now()->toDateTimeString(), 'reason' => 'test']);
        $this->assertSame(409, $controller->returnRental($request, 1)->getStatusCode());
        $this->assertSame(3, DB::table('barang')->value('stok'));
        DB::table('rental_bookings')->update(['status' => 'completed']);
        try { $controller->reject($request, 1, 'rental'); $this->fail('Completed order rejected'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) { $this->assertSame(409, $e->getStatusCode()); }
        $this->assertSame('completed', DB::table('rental_bookings')->value('status'));
    }

    public function test_history_routes_resolve_to_history_not_product_detail(): void
    {
        foreach (['mobil', 'fasilitas'] as $type) {
            $route = app('router')->getRoutes()->match(Request::create('/api/'.$type.'/my-bookings'));
            $this->assertStringEndsWith('@myBookings', $route->getActionName());
        }
    }

    public function test_web_mobil_booking_returns_receipt_id_without_an_invalid_receipt_insert(): void
    {
        Schema::table('mobil_bookings', function (Blueprint $t) {
            $t->string('jenis_sewa'); $t->decimal('latitude')->nullable(); $t->decimal('longitude')->nullable(); $t->string('payment_proof')->nullable();
        });
        Schema::table('notifications', function (Blueprint $t) {
            $t->boolean('is_read')->default(false); $t->dateTime('sent_at')->nullable();
        });
        Schema::create('admin_notifications', function (Blueprint $t) {
            $t->id(); $t->string('type'); $t->integer('reference_id'); $t->integer('region_id');
            $t->string('title'); $t->text('message'); $t->boolean('is_read'); $t->timestamps();
        });

        DB::table('mobils')->insert(['id' => 1, 'nama_mobil' => 'Mobil Uji', 'stok' => 3, 'region_id' => 7]);
        $request = Request::create('/mobil-rental/booking', 'POST', [
            'mobil_id' => 1, 'jenis_sewa' => 'harian', 'quantity' => 1,
            'start_date' => now()->addDay()->toDateString(), 'delivery_address' => 'Alamat uji',
            'payment_method' => 'tunai', 'rental_purpose' => 'Kegiatan warga',
        ]);
        $request->headers->set('Accept', 'application/json');
        $user = new User(); $user->id = 41; $user->region_id = 9; $user->name = 'Warga Uji';
        \Illuminate\Support\Facades\Auth::setUser($user);
        $response = app(\App\Http\Controllers\User\MobilBookingController::class)->store($request);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(1, $response->getData()->receipt_id);
        $this->assertSame(DB::table('mobil_bookings')->value('order_number'), $response->getData()->receipt_number);
        $this->assertSame(1, DB::table('wallet_transactions')->count());
        $this->assertSame(1, DB::table('admin_notifications')->count());
        $this->assertSame(1, DB::table('notifications')->count());
        $this->assertStringContainsString('/aktivitas', DB::table('notifications')->value('link'));
    }
}
