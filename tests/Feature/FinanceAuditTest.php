<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\KeuanganController;
use App\Http\Controllers\Admin\PenarikanSaldoController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TransactionController;
use App\Models\ManualReport;
use App\Models\PenarikanSaldo;
use App\Models\Region;
use App\Models\WalletTransaction;
use App\Support\FinanceRecords;
use App\Support\SaldoWilayah;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\Support\EditorialDatabase;
use Tests\TestCase;

class FinanceAuditTest extends TestCase
{
    use EditorialDatabase { setUp as editorialSetUp; }

    protected function setUp(): void
    {
        $this->editorialSetUp();
        Storage::fake('private');
        Storage::fake('public');
        Schema::table('users', fn (Blueprint $t) => $t->string('status')->default('aktif'));
        Schema::table('regions', function (Blueprint $t) {
            $t->text('payment_info')->nullable();
            $t->timestamps();
        });
        (require database_path('migrations/2025_11_28_214717_create_manual_reports_table.php'))->up();
        Schema::table('manual_reports', function (Blueprint $t) {
            $t->integer('region_id')->nullable();
            $t->string('proof_image')->nullable();
        });
        (require database_path('migrations/2026_08_11_120100_create_wallet_transactions_table.php'))->up();
        Schema::create('penarikan_saldo', function (Blueprint $t) {
            $t->id();
            foreach (['region_id', 'diajukan_oleh', 'diproses_oleh'] as $c) {
                $t->integer($c)->nullable();
            }
            foreach (['status', 'metode', 'nama_bank', 'no_rekening', 'nama_pemilik', 'catatan_admin'] as $c) {
                $t->text($c)->nullable();
            }
            $t->decimal('jumlah', 15, 2);
            $t->timestamp('diajukan_pada')->nullable();
            $t->timestamp('diselesaikan_pada')->nullable();
            $t->timestamps();
        });
        (require database_path('migrations/2026_11_14_000001_preserve_financial_audit_records.php'))->up();
        Schema::create('activity_log', function (Blueprint $t) {
            $t->id();
            $t->integer('user_id');
            $t->string('action');
            $t->text('description');
            $t->string('ip_address')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('admin_notifications', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id')->nullable();
            $t->integer('reference_id');
            foreach (['type', 'title', 'message'] as $c) {
                $t->text($c);
            } $t->boolean('is_read');
            $t->timestamps();
        });
        Schema::create('gas', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id');
        });
        Schema::create('gas_orders', function (Blueprint $t) {
            $t->id();
            $t->integer('gas_id');
            $t->integer('user_id');
            $t->timestamps();
            $t->softDeletes();
        });
        DB::table('gas')->insert(['id' => 1, 'region_id' => 1]);
        DB::table('gas_orders')->insert(['id' => 1, 'gas_id' => 1, 'user_id' => 1]);
        $this->actingAs($this->user(4));
    }

    private function ledger(array $extra = []): WalletTransaction
    {
        return WalletTransaction::create($extra + ['region_id' => 1, 'reference_type' => 'gas', 'reference_id' => 1, 'source' => 'manual', 'type' => 'masuk', 'status' => 'verified', 'amount' => 22000]);
    }

    private function manual(array $extra = []): ManualReport
    {
        return ManualReport::create($extra + ['category' => 'gas', 'name' => 'Penjualan langsung', 'amount' => 10000, 'quantity' => 2, 'payment_method' => 'tunai', 'transaction_date' => today(), 'created_by' => 4, 'region_id' => 1]);
    }

    private function deny(callable $fn, int $code): void
    {
        try {
            $fn();
            $this->fail('Unexpected access');
        } catch (HttpExceptionInterface $e) {
            $this->assertSame($code, $e->getStatusCode());
        } catch (ModelNotFoundException $e) {
            $this->assertSame(404, $code);
        }
    }

    public function test_income_counts_only_verified_own_payments_and_own_active_manual_records(): void
    {
        $this->ledger();
        $this->ledger(['status' => 'pending', 'amount' => 700000]);
        $this->ledger(['status' => 'rejected', 'amount' => 900000]);
        $this->ledger(['region_id' => 5, 'amount' => 800000]);
        $this->ledger(['type' => 'keluar', 'amount' => 999999]);
        $this->manual();
        $this->manual(['region_id' => 5, 'created_by' => 7, 'amount' => 888888]);
        $this->manual(['amount' => 99999])->delete();
        $data = FinanceRecords::summary(now()->year, now()->month);
        $this->assertEquals(42000, $data['total']['revenue']);
        $this->assertEquals(2, $data['total']['transactions']);
        $this->assertEquals(42000, array_sum(array_column(array_intersect_key($data, array_flip(['gas', 'rental', 'mobil', 'fasilitas', 'pasar', 'lainnya'])), 'revenue')));
        DB::table('users')->where('id', 4)->update(['region_id' => 5]);
        $this->actingAs($this->user(4));
        $this->assertFalse(FinanceRecords::manual()->where('region_id', 1)->exists());
    }

    public function test_manual_records_cannot_be_changed_deleted_or_read_by_other_village_or_staff(): void
    {
        $report = $this->manual(['proof_image' => 'manual-reports/evidence.png']);
        Storage::disk('private')->put($report->proof_image, 'evidence');
        $this->actingAs($this->user(7));
        $this->deny(fn () => app(ReportController::class)->updateManualTransaction(Request::create('/', 'PUT'), $report->id), 404);
        $this->deny(fn () => app(ReportController::class)->destroyManualTransaction($report->id), 404);
        $this->deny(fn () => app(ReportController::class)->manualProof($report->id), 404);
        $staff = $this->user(4);
        $staff->role = 'staff';
        $this->actingAs($staff);
        $this->deny(fn () => FinanceRecords::summary(now()->year), 403);
        $this->assertNotNull($report->fresh());
        Storage::disk('private')->assertExists($report->proof_image);
    }

    public function test_manual_create_is_private_and_cancel_retains_evidence_and_audit(): void
    {
        $request = Request::create('/', 'POST', ['category' => 'gas', 'name' => 'Penjualan tunai', 'amount' => 10000, 'quantity' => 1, 'payment_method' => 'tunai', 'transaction_date' => today()->toDateString(), 'region_id' => 5, 'created_by' => 7]);
        $request->files->set('proof_image', UploadedFile::fake()->image('receipt.png'));
        app(ReportController::class)->storeManualTransaction($request);
        $report = ManualReport::firstOrFail();
        $this->assertEquals(1, $report->region_id);
        $this->assertEquals(4, $report->created_by);
        Storage::disk('private')->assertExists($report->proof_image);
        Storage::disk('public')->assertMissing($report->proof_image);
        $response = app(ReportController::class)->manualProof($report->id);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        app(ReportController::class)->destroyManualTransaction($report->id);
        $this->assertSame(0, ManualReport::count());
        $this->assertSame(1, ManualReport::withTrashed()->count());
        Storage::disk('private')->assertExists($report->proof_image);
        $this->assertSame(2, DB::table('activity_log')->count());
    }

    public function test_manual_refund_requires_actual_recorded_evidence_and_retries_do_not_duplicate(): void
    {
        $entry = $this->ledger();
        WalletTransaction::batalkanDanRefund('gas', 1, 'Batal');
        WalletTransaction::batalkanDanRefund('gas', 1, 'Batal lagi');
        $this->assertSame('pending', $entry->fresh()->refund_status);
        $this->assertSame('rejected', $entry->fresh()->status);
        $this->assertSame(1, DB::table('notifications')->where('title', 'Pengembalian Dana Menunggu Pengelola')->count());
        $this->assertEquals(0, FinanceRecords::summary(now()->year)['total']['revenue']);
        $request = Request::create('/', 'POST', ['confirmed' => 1]);
        $request->files->set('proof', UploadedFile::fake()->image('refund.png'));
        $this->actingAs($this->user(7));
        $this->deny(fn () => app(RefundController::class)->complete($request, $entry->id), 404);
        $this->actingAs($this->user(4));
        app(RefundController::class)->complete($request, $entry->id);
        $this->assertSame('completed', $entry->fresh()->refund_status);
        Storage::disk('private')->assertExists($entry->fresh()->refund_proof);
        $this->deny(fn () => app(RefundController::class)->complete($request, $entry->id), 409);
        $this->assertCount(1, Storage::disk('private')->allFiles('refund-evidence'));
        $pending = $this->ledger(['reference_id' => 2, 'status' => 'pending']);
        WalletTransaction::batalkanDanRefund('gas', 2, 'Belum bayar');
        $this->assertNull($pending->fresh()->refund_status);
    }

    public function test_withdrawal_reservation_prevents_second_request_and_stale_decisions(): void
    {
        $this->ledger(['source' => 'gateway', 'amount' => 100000]);
        Region::find(1)->update(['payment_info' => ['bank_name' => 'Bank Uji', 'account_number' => '123', 'account_name' => 'Desa A']]);
        $request = Request::create('/', 'POST', ['metode' => 'bank', 'jumlah' => 60000]);
        app(KeuanganController::class)->tarikSaldo($request);
        app(KeuanganController::class)->tarikSaldo(Request::create('/', 'POST', ['metode' => 'bank', 'jumlah' => 30000]));
        $this->assertSame(1, PenarikanSaldo::count());
        $this->assertEquals(40000, SaldoWilayah::tersedia(1));
        $entry = PenarikanSaldo::firstOrFail();
        $stale = clone $entry;
        app(KeuanganController::class)->batalkanPenarikan($entry);
        $admin = $this->user(4);
        $admin->role = 'super_admin';
        $this->actingAs($admin);
        $approve = Request::create('/', 'POST');
        $approve->files->set('proof', UploadedFile::fake()->image('transfer.png'));
        $this->deny(fn () => app(PenarikanSaldoController::class)->approve($approve, $stale), 409);
        $this->assertSame(PenarikanSaldo::DIBATALKAN, $entry->fresh()->status);
        $this->assertEquals(100000, SaldoWilayah::tersedia(1));
        $this->assertCount(0, Storage::disk('private')->allFiles('withdrawal-evidence'));
    }

    public function test_transport_payment_verification_preserves_service_status_and_checks_ledger(): void
    {
        Schema::create('mobils', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id');
            $t->string('status');
        });
        Schema::create('mobil_bookings', function (Blueprint $t) {
            $t->id();
            $t->integer('mobil_id');
            $t->integer('user_id');
            $t->string('status');
            $t->string('payment_method');
            $t->string('payment_proof')->nullable();
            $t->decimal('total_amount', 15, 2);
            $t->timestamps();
            $t->softDeletes();
        });
        DB::table('mobils')->insert(['id' => 1, 'region_id' => 1, 'status' => 'disewa']);
        DB::table('mobil_bookings')->insert(['id' => 1, 'mobil_id' => 1, 'user_id' => 1, 'status' => 'confirmed', 'payment_method' => 'tunai', 'total_amount' => 500000]);
        $entry = $this->ledger(['reference_type' => 'mobil', 'status' => 'pending', 'amount' => 400000]);
        $controller = app(TransactionController::class);
        $this->deny(fn () => $controller->verify(Request::create('/', 'POST'), 1, 'mobil'), 409);
        $entry->update(['amount' => 500000]);
        $controller->verify(Request::create('/', 'POST'), 1, 'mobil');
        $controller->verify(Request::create('/', 'POST'), 1, 'mobil');
        $this->assertSame('verified', $entry->fresh()->status);
        $this->assertSame('confirmed', DB::table('mobil_bookings')->value('status'));
        $this->assertSame('disewa', DB::table('mobils')->value('status'));
        $this->assertSame(1, DB::table('activity_log')->count());
    }

    public function test_withdrawal_completion_requires_evidence_and_cannot_be_repeated(): void
    {
        $this->ledger(['source' => 'gateway', 'amount' => 100000]);
        $entry = PenarikanSaldo::create(['region_id' => 1, 'diajukan_oleh' => 4, 'jumlah' => 50000, 'metode' => 'bank', 'nama_bank' => 'Bank Uji', 'no_rekening' => '123', 'nama_pemilik' => 'Desa A', 'status' => 'pending']);
        $stale = clone $entry;
        $admin = $this->user(4);
        $admin->role = 'super_admin';
        $this->actingAs($admin);
        try {
            app(PenarikanSaldoController::class)->approve(Request::create('/', 'POST'), $entry);
            $this->fail('Missing proof accepted');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('proof', $e->errors());
        }
        $request = Request::create('/', 'POST');
        $request->files->set('proof', UploadedFile::fake()->image('paid.png'));
        app(PenarikanSaldoController::class)->approve($request, $entry);
        $this->assertSame('selesai', $entry->fresh()->status);
        $this->deny(fn () => app(PenarikanSaldoController::class)->approve($request,$stale), 409);
        Storage::disk('private')->assertExists($entry->fresh()->proof_path);
        $this->assertCount(1,Storage::disk('private')->allFiles('withdrawal-evidence'));
        $this->assertEquals(50000,SaldoWilayah::tersedia(1));
    }
}
