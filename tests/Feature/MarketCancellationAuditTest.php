<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\UnitPasarDaerahController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MarketCancellationAuditTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('pasar_orders', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('region_id');
            $t->string('order_number'); $t->string('status'); $t->string('cancellation_status');
            $t->text('admin_cancellation_response')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('pasar_produks', function (Blueprint $t) {
            $t->id(); $t->integer('stok'); $t->string('status'); $t->timestamps();
        });
        Schema::create('pasar_order_items', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('pasar_order_id'); $t->unsignedBigInteger('pasar_produk_id'); $t->integer('quantity');
        });
        Schema::create('wallet_transactions', function (Blueprint $t) {
            $t->id(); $t->string('reference_type'); $t->unsignedBigInteger('reference_id'); $t->string('source');
            $t->string('status'); $t->decimal('amount', 12, 2); $t->text('notes')->nullable(); $t->timestamps();
        });
        Schema::create('saldo_warga', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('region_id');
            $t->string('type'); $t->decimal('amount', 12, 2); $t->string('reference_type'); $t->unsignedBigInteger('reference_id');
            $t->string('status'); $t->text('catatan'); $t->timestamps();
        });
        Schema::create('notifications', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('admin_id');
            foreach (['type', 'title', 'message', 'link'] as $column) $t->string($column);
            $t->timestamps();
        });
        Schema::create('activity_log', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->string('action'); $t->string('description'); $t->string('ip_address'); $t->timestamps(); $t->softDeletes();
        });
        $user = new User(); $user->id = 1; $user->role = 'admin_desa'; $user->region_id = 7;
        Auth::setUser($user);
        DB::table('pasar_orders')->insert(['id' => 1, 'user_id' => 41, 'region_id' => 7, 'order_number' => 'PSR-TEST', 'status' => 'paid', 'cancellation_status' => 'pending']);
        DB::table('pasar_produks')->insert(['id' => 1, 'stok' => 0, 'status' => 'habis']);
        DB::table('pasar_order_items')->insert(['pasar_order_id' => 1, 'pasar_produk_id' => 1, 'quantity' => 2]);
        DB::table('wallet_transactions')->insert(['reference_type' => 'pasar', 'reference_id' => 1, 'source' => 'gateway', 'status' => 'verified', 'amount' => 30000]);
    }

    private function act(string $action)
    {
        $request = Request::create('/cancel', 'POST', ['admin_response' => 'Alasan uji']);
        $request->setUserResolver(fn () => Auth::user());
        return app(UnitPasarDaerahController::class)->handleCancellation($request, 1, $action);
    }

    public function test_approval_restores_stock_and_refunds_verified_money_exactly_once(): void
    {
        $this->assertSame(200, $this->act('approve')->getStatusCode());
        $this->assertSame('cancelled', DB::table('pasar_orders')->value('status'));
        $this->assertSame(2, DB::table('pasar_produks')->value('stok'));
        $this->assertSame('tersedia', DB::table('pasar_produks')->value('status'));
        $this->assertSame(30000.0, (float) DB::table('saldo_warga')->sum('amount'));
        $this->assertSame('rejected', DB::table('wallet_transactions')->value('status'));
        $this->assertSame(409, $this->act('approve')->getStatusCode());
        $this->assertSame(2, DB::table('pasar_produks')->value('stok'));
        $this->assertSame(1, DB::table('saldo_warga')->count());
    }

    public function test_rejection_keeps_stock_payment_and_order_status(): void
    {
        $this->assertSame(200, $this->act('reject')->getStatusCode());
        $this->assertSame('paid', DB::table('pasar_orders')->value('status'));
        $this->assertSame('rejected', DB::table('pasar_orders')->value('cancellation_status'));
        $this->assertSame(0, DB::table('pasar_produks')->value('stok'));
        $this->assertSame(0, DB::table('saldo_warga')->count());
        $this->assertSame('verified', DB::table('wallet_transactions')->value('status'));
    }

    public function test_other_region_cannot_process_the_order(): void
    {
        Auth::user()->region_id = 8;
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->act('approve');
    }

    public function test_a_regular_user_cannot_process_the_order(): void
    {
        Auth::user()->role = 'user';
        $this->expectException(HttpException::class);
        $this->act('approve');
    }

    public function test_failure_rolls_back_stock_order_and_refund(): void
    {
        \App\Models\Notification::creating(function () { throw new \RuntimeException('notification failure'); });
        try {
            $this->act('approve');
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('notification failure', $e->getMessage());
        } finally {
            \App\Models\Notification::flushEventListeners();
        }
        $this->assertSame('pending', DB::table('pasar_orders')->value('cancellation_status'));
        $this->assertSame('paid', DB::table('pasar_orders')->value('status'));
        $this->assertSame(0, DB::table('pasar_produks')->value('stok'));
        $this->assertSame(0, DB::table('saldo_warga')->count());
        $this->assertSame('verified', DB::table('wallet_transactions')->value('status'));
    }
}
