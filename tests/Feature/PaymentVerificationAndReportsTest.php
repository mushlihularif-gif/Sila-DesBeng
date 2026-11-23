<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\User\RentalBookingController;
use App\Models\{Barang, Gas, GasOrder, ManualReport, Mobil, MobilBooking, Region, RentalBooking, WalletTransaction};
use App\Services\{GasOrdering, GasOrderActions, PaymentProofAnalyzerService, ReceiptGeneratorService, RentalOrdering, RentalOrderActions};
use App\Support\FinanceRecords;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\{Request, UploadedFile};
use Illuminate\Support\Facades\{DB, Schema, Storage};
use Illuminate\Validation\ValidationException;
use Tests\Support\EditorialDatabase;
use Tests\TestCase;

class PaymentVerificationAndReportsTest extends TestCase
{
    use EditorialDatabase { setUp as editorialSetUp; }

    protected function setUp(): void
    {
        $this->editorialSetUp();
        Storage::fake('public');
        Storage::fake('private');

        Schema::table('regions', function (Blueprint $t) {
            $t->json('settings')->nullable();
            $t->json('payment_info')->nullable();
            $t->boolean('is_gas_crisis')->default(false);
            $t->timestamps();
        });
        Schema::table('users', function (Blueprint $t) {
            $t->string('nik_hash')->nullable();
            $t->string('status')->default('aktif');
            $t->boolean('staff_enabled')->default(false);
            $t->integer('created_by')->nullable();
        });
        DB::table('users')->update(['email' => 'warga@example.test']);
        Region::find(1)->update([
            'payment_info' => [
                'bank_name' => 'Bank BRI',
                'account_number' => '1234567890',
                'account_name' => 'BUMDes Desa A',
                'bank_active' => true,
                'cash_only_active' => true,
            ],
        ]);

        foreach ([
            '2026_06_15_182007_create_services_table.php',
            '2026_06_15_182008_create_region_services_table.php',
            '2026_06_22_151200_add_is_exclusive_to_region_services_table.php',
            '2026_08_24_215518_create_family_cards_table.php',
            '2026_08_24_215518_create_family_members_table.php',
            '2025_11_28_214717_create_manual_reports_table.php',
        ] as $file) {
            (require database_path('migrations/' . $file))->up();
        }
        Schema::table('manual_reports', function (Blueprint $t) {
            $t->integer('region_id')->nullable();
            $t->string('proof_image')->nullable();
        });

        Schema::create('system_settings', fn (Blueprint $t) => $t->id());
        Schema::create('staff_permissions', function (Blueprint $t) {
            $t->id();
            $t->integer('user_id');
            $t->string('unit_key');
            $t->timestamps();
        });

        Schema::create('barang', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id');
            $t->string('nama_barang');
            $t->integer('stok')->default(10);
            $t->decimal('harga_sewa', 12, 2)->default(200000);
            $t->string('status')->default('tersedia');
            foreach (['deskripsi', 'kategori', 'lokasi', 'satuan', 'foto', 'foto_2', 'foto_3', 'latitude', 'longitude'] as $col) {
                $t->string($col)->nullable();
            }
            $t->timestamps();
        });
        (require database_path('migrations/2025_12_04_000000_create_rental_bookings_table.php'))->up();
        Schema::table('rental_bookings', function (Blueprint $t) {
            $t->uuid('uuid')->unique();
            $t->string('order_number')->unique();
            $t->integer('region_id')->nullable();
            $t->integer('handled_by')->nullable();
            foreach (['rental_purpose', 'receipt_path', 'delivery_proof_image', 'cancellation_reason', 'cancellation_status', 'admin_cancellation_response'] as $col) {
                $t->text($col)->nullable();
            }
            foreach (['delivery_time', 'arrival_time', 'return_time', 'completion_time', 'cancellation_requested_at'] as $col) {
                $t->timestamp($col)->nullable();
            }
            $t->softDeletes();
        });
        Schema::table('rental_bookings', fn (Blueprint $t) => $t->string('status')->default('pending')->change());
        (require database_path('migrations/2026_11_12_000002_harden_rental_order_submission.php'))->up();
        (require database_path('migrations/2026_11_13_000001_add_rental_billing_basis.php'))->up();

        Schema::create('gas', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id');
            $t->string('jenis_gas');
            $t->integer('stok')->default(10);
            $t->decimal('harga_satuan', 10, 2)->default(22000);
            $t->string('status')->default('tersedia');
            foreach (['deskripsi', 'kategori', 'lokasi', 'satuan', 'foto', 'foto_2', 'foto_3', 'latitude', 'longitude'] as $col) {
                $t->string($col)->nullable();
            }
            $t->timestamps();
        });
        Schema::create('gas_orders', function (Blueprint $t) {
            $t->id();
            $t->integer('user_id');
            $t->integer('gas_id');
            $t->uuid('uuid')->unique();
            $t->string('order_number')->unique();
            $t->integer('quantity');
            $t->decimal('price', 10, 2);
            $t->date('order_date');
            foreach (['item_name', 'delivery_method', 'payment_method', 'address', 'full_name', 'email', 'status', 'proof_of_payment', 'receipt_path', 'nomor_kk', 'nomor_kk_hash', 'rejection_reason', 'delivery_proof_image', 'cancellation_status', 'cancellation_reason_user', 'admin_cancellation_response'] as $col) {
                $t->string($col)->nullable();
            }
            foreach (['confirmed_at', 'delivery_time', 'arrival_time', 'completion_time', 'cancellation_requested_at'] as $col) {
                $t->timestamp($col)->nullable();
            }
            $t->integer('handled_by')->nullable();
            $t->decimal('latitude')->nullable();
            $t->decimal('longitude')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        (require database_path('migrations/2026_11_12_000001_harden_gas_order_submission.php'))->up();

        Schema::create('mobils', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id');
            $t->string('nama_mobil');
            $t->decimal('harga_sewa', 12, 2)->default(150000);
            $t->string('status')->default('tersedia');
            $t->string('foto')->nullable();
            $t->timestamps();
        });
        Schema::create('mobil_bookings', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->string('order_number')->unique();
            $t->integer('user_id');
            $t->integer('mobil_id');
            $t->integer('region_id')->nullable();
            $t->string('recipient_name')->nullable();
            $t->string('delivery_method')->default('jemput');
            $t->boolean('dengan_supir')->default(false);
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->string('payment_method')->default('transfer');
            $t->string('payment_proof')->nullable();
            $t->string('receipt_path')->nullable();
            $t->decimal('total_amount', 15, 2)->default(0);
            $t->string('status')->default('pending');
            $t->string('cancellation_status')->nullable();
            $t->timestamp('confirmed_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('fasilitas_umums', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id');
            $t->string('nama_fasilitas');
            $t->decimal('harga_sewa', 12, 2)->default(0);
            $t->string('status')->default('tersedia');
            $t->integer('stok')->default(1);
            $t->string('foto')->nullable();
            $t->timestamps();
        });
        Schema::create('fasilitas_umum_bookings', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->string('order_number')->unique();
            $t->integer('user_id');
            $t->integer('fasilitas_id');
            $t->integer('region_id')->nullable();
            $t->string('recipient_name')->nullable();
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->integer('quantity')->default(1);
            $t->string('payment_method')->nullable();
            $t->string('payment_proof')->nullable();
            $t->decimal('total_amount', 15, 2)->default(0);
            $t->string('status')->default('pending');
            $t->string('cancellation_status')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('transaction_receipts', function (Blueprint $t) {
            $t->id();
            foreach (['booking_type', 'receipt_number', 'item_name', 'payment_method'] as $col) {
                $t->string($col);
            }
            foreach (['booking_id', 'user_id', 'region_id', 'quantity'] as $col) {
                $t->integer($col);
            }
            $t->decimal('total_amount', 12, 2);
            $t->timestamps();
        });

        Schema::create('penarikan_saldo', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id')->nullable();
            $t->decimal('jumlah', 15, 2)->default(0);
            $t->string('status')->default('pending');
            $t->timestamps();
        });
        (require database_path('migrations/2026_08_11_120100_create_wallet_transactions_table.php'))->up();
        (require database_path('migrations/2026_11_14_000001_preserve_financial_audit_records.php'))->up();
        (require database_path('migrations/2026_11_21_000001_add_payment_verification_details_to_wallet_transactions.php'))->up();

        Schema::create('admin_notifications', function (Blueprint $t) {
            $t->id();
            $t->string('type');
            $t->integer('reference_id');
            $t->integer('region_id')->nullable();
            $t->string('title');
            $t->text('message');
            $t->boolean('is_read')->default(false);
            $t->timestamps();
        });
        Schema::create('activity_log', function (Blueprint $t) {
            $t->id();
            $t->integer('user_id');
            $t->string('action');
            $t->text('description');
            $t->string('ip_address')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('saldo_warga', function (Blueprint $t) {
            $t->id();
            $t->string('type');
            $t->string('reference_type');
            $t->integer('reference_id');
        });

        Barang::create([
            'id' => 1,
            'region_id' => 1,
            'nama_barang' => 'Tenda Komplit Pernikahan',
            'stok' => 5,
            'harga_sewa' => 200000,
            'billing_basis' => 'per_activity',
            'status' => 'tersedia',
        ]);
        Gas::create([
            'id' => 1,
            'region_id' => 1,
            'jenis_gas' => 'LPG 3 Kg',
            'stok' => 10,
            'harga_satuan' => 22000,
            'status' => 'tersedia',
        ]);
        Mobil::create([
            'id' => 1,
            'region_id' => 1,
            'nama_mobil' => 'Pickup Desa',
            'harga_sewa' => 150000,
            'status' => 'tersedia',
        ]);

        // Activate services for region 1
        DB::table('services')->insert([
            ['id' => 1, 'name' => 'Penyewaan Alat', 'slug' => 'penyewaan-alat'],
            ['id' => 2, 'name' => 'Penjualan Gas', 'slug' => 'penjualan-gas'],
            ['id' => 3, 'name' => 'Penyewaan Mobil', 'slug' => 'penyewaan-mobil'],
            ['id' => 4, 'name' => 'Fasilitas Umum', 'slug' => 'fasilitas-umum'],
        ]);
        foreach ([1, 2, 3, 4] as $svcId) {
            DB::table('region_services')->insert([
                'region_id' => 1,
                'service_id' => $svcId,
                'is_active' => true,
                'is_exclusive' => false,
            ]);
        }

        $this->actingAs($this->user(1));
    }

    private function createTransferTentOrder(): RentalBooking
    {
        $req = Request::create('/rental/booking', 'POST', [
            'barang_id' => 1,
            'quantity' => 1,
            'recipient_name' => 'Warga Uji',
            'delivery_address' => 'Jl. Desa A RT 01',
            'delivery_method' => 'jemput',
            'payment_method' => 'transfer',
            'rental_purpose' => 'Hajatan Keluarga',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'sop_accepted' => '1',
        ]);
        $req->files->set('payment_proof', UploadedFile::fake()->image('transfer_20k.jpg'));
        $req->setUserResolver(fn () => $this->user(1));
        $req->headers->set('Accept', 'application/json');

        return app(RentalOrdering::class)->store($req)[0];
    }

    public function test_underpaid_manual_transfer_is_blocked_from_full_confirmation_and_never_enters_revenue(): void
    {
        $order = $this->createTransferTentOrder();
        $this->assertEquals(200000, $order->subtotalAmount());
        $this->assertEquals(0, $order->paidAmount());
        $this->assertEquals(200000, $order->remainingAmount());

        // Admin Desa tries to confirm payment while entering actual received Rp 20.000
        $this->actingAs($this->user(4));
        $verifyReq = Request::create('/admin/aktivitas/bukti-transaksi/' . $order->id . '/rental/verify', 'POST', [
            'confirmed_amount' => '20.000',
        ]);
        $verifyReq->setUserResolver(fn () => $this->user(4));
        $verifyReq->headers->set('Accept', 'application/json');

        try {
            app(RentalOrderActions::class)->admin($verifyReq, $order->id, 'verify');
            $this->fail('Underpaid manual transfer should be blocked from full confirmation');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('confirmed_amount', $e->errors());
            $this->assertStringContainsString('kurang Rp 180.000', $e->errors()['confirmed_amount'][0]);
        }

        // Order stays unpaid & revenue stays Rp 0 (NOT Rp 200.000!)
        $order->refresh();
        $this->assertFalse($order->paymentReceived());
        $this->assertSame('pending', WalletTransaction::first()->status);
        $this->assertEquals(0, FinanceRecords::summary(now()->year)['total']['revenue']);
    }

    public function test_shortfall_recording_tracks_partial_paid_amount_requests_settlement_and_completes_when_settled(): void
    {
        $order = $this->createTransferTentOrder();
        $this->actingAs($this->user(4));

        // 1. Admin records Rp 20.000 received (shortfall Rp 180.000) and requests settlement proof
        $rejectReq = Request::create('/reject', 'POST', [
            'received_amount' => '20000',
            'reason' => 'Nominal transfer baru Rp 20.000 dari subtotal Rp 200.000.',
        ]);
        $rejectReq->setUserResolver(fn () => $this->user(4));
        $rejectReq->headers->set('Accept', 'application/json');

        app(RentalOrderActions::class)->admin($rejectReq, $order->id, 'reject-payment');

        $order->refresh();
        $this->assertNull($order->payment_proof);
        $this->assertFalse($order->paymentReceived());
        $this->assertEquals(200000, $order->subtotalAmount());
        $this->assertEquals(20000, $order->paidAmount());
        $this->assertEquals(180000, $order->remainingAmount());
        $this->assertSame('Kurang Bayar', $order->paymentStatusLabel());
        $this->assertEquals(0, FinanceRecords::summary(now()->year)['total']['revenue']);

        // Receipt PNG reflects exact Subtotal Rp 200.000, Uang Diterima Rp 20.000, Sisa Tagihan Rp 180.000
        $generator = new class extends ReceiptGeneratorService {
            public array $text = [];
            protected function addText($image, $text, $x, $y, $size, $color, $fontPath, $bold = false) { $this->text[] = $text; }
            protected function addFooterTtd($image, $y, $url, $fontPath, $normalSize, $black) {}
        };
        $generator->generateRentalReceipt($order->fresh());
        $this->assertContains('Subtotal Pesanan', $generator->text);
        $this->assertContains('Rp. 200.000', $generator->text);
        $receivedIdx = array_search('Uang Diterima', $generator->text, true);
        $this->assertNotFalse($receivedIdx);
        $this->assertSame('Rp. 20.000', $generator->text[$receivedIdx + 1]);
        $this->assertContains('Rp. 180.000 (Kurang Bayar)', $generator->text);

        // 2. Citizen uploads settlement proof for the remaining Rp 180.000
        $this->actingAs($this->user(1));
        $reuploadReq = Request::create('/rental/payment/' . $order->id . '/proof', 'POST');
        $reuploadReq->files->set('payment_proof', UploadedFile::fake()->image('pelunasan_180k.jpg'));
        $reuploadReq->setUserResolver(fn () => $this->user(1));
        $reuploadReq->headers->set('Accept', 'application/json');
        app(RentalBookingController::class)->uploadPayment($reuploadReq, $order->id);

        // 3. Admin confirms remaining Rp 180.000 (delta mode -> total Rp 200.000)
        $this->actingAs($this->user(4));
        $settleReq = Request::create('/verify', 'POST', [
            'confirmed_amount' => '180.000',
            'amount_mode' => 'delta',
        ]);
        $settleReq->setUserResolver(fn () => $this->user(4));
        $settleReq->headers->set('Accept', 'application/json');
        app(RentalOrderActions::class)->admin($settleReq, $order->id, 'verify');

        $order->refresh();
        $this->assertTrue($order->paymentReceived());
        $this->assertEquals(200000, $order->paidAmount());
        $this->assertEquals(0, $order->remainingAmount());
        $this->assertSame('Lunas', $order->paymentStatusLabel());
        $this->assertEquals(200000, FinanceRecords::summary(now()->year)['total']['revenue']);

        $generator->text = [];
        $generator->generateRentalReceipt($order->fresh());
        $receivedIdx = array_search('Uang Diterima', $generator->text, true);
        $this->assertSame('Rp. 200.000', $generator->text[$receivedIdx + 1]);
        $this->assertContains('LUNAS', $generator->text);
    }

    public function test_gas_and_mobil_receipts_never_print_fake_paid_amount_when_unpaid(): void
    {
        // Gas: 3 tabungs x Rp 22.000 = Rp 66.000 (unpaid)
        $gasReq = Request::create('/gas/booking', 'POST', [
            'gas_id' => 1,
            'quantity' => 3,
            'buyer_name' => 'Warga Gas',
            'buyer_address' => 'Desa A',
            'delivery_method' => 'jemput',
            'payment_method' => 'tunai',
        ]);
        $gasReq->setUserResolver(fn () => $this->user(1));
        $gasReq->headers->set('Accept', 'application/json');
        $gasResult = app(GasOrdering::class)->store($gasReq);
        $gasOrder = GasOrder::findOrFail($gasResult['order_id']);

        $generator = new class extends ReceiptGeneratorService {
            public array $text = [];
            protected function addText($image, $text, $x, $y, $size, $color, $fontPath, $bold = false) { $this->text[] = $text; }
            protected function addFooterTtd($image, $y, $url, $fontPath, $normalSize, $black) {}
        };

        $generator->generateGasReceipt($gasOrder->fresh());
        $this->assertContains('Subtotal Pesanan', $generator->text);
        $this->assertContains('Rp. 66.000', $generator->text);
        $receivedIdx = array_search('Uang Diterima', $generator->text, true);
        $this->assertNotFalse($receivedIdx);
        $this->assertSame('Rp. 0', $generator->text[$receivedIdx + 1]);
        $this->assertContains('Rp. 66.000 (Belum Lunas)', $generator->text);

        // Verify gas payment Rp 66.000
        $this->actingAs($this->user(4));
        $vReq = Request::create('/verify', 'POST', ['confirmed_amount' => '66000']);
        $vReq->setUserResolver(fn () => $this->user(4));
        $vReq->headers->set('Accept', 'application/json');
        app(GasOrderActions::class)->admin($vReq, $gasOrder->id, 'verify');

        $generator->text = [];
        $generator->generateGasReceipt($gasOrder->fresh());
        $receivedIdx = array_search('Uang Diterima', $generator->text, true);
        $this->assertSame('Rp. 66.000', $generator->text[$receivedIdx + 1]);
        $this->assertContains('LUNAS', $generator->text);

        // Mobil: 3 days, total_amount = Rp 450.000 (previously printed Rp 0 because of total_harga typo!)
        $mobilOrder = MobilBooking::create([
            'user_id' => 1,
            'mobil_id' => 1,
            'region_id' => 1,
            'recipient_name' => 'Warga Mobil',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'payment_method' => 'transfer',
            'total_amount' => 450000,
            'status' => 'pending',
        ]);
        WalletTransaction::catatPemasukan(1, 'mobil', $mobilOrder->id, 450000, 'transfer', null);

        $generator->text = [];
        $generator->generateMobilReceipt($mobilOrder->fresh());
        $this->assertContains('3 Hari', $generator->text);
        $this->assertContains('Rp. 450.000', $generator->text);
        $receivedIdx = array_search('Uang Diterima', $generator->text, true);
        $this->assertSame('Rp. 0', $generator->text[$receivedIdx + 1]);
        $this->assertContains('Rp. 450.000 (Belum Lunas)', $generator->text);
    }

    public function test_proof_ocr_parser_detects_indonesian_amounts_and_flags_underpayment(): void
    {
        $analyzer = app(PaymentProofAnalyzerService::class);

        $brimoText = "Transaksi Berhasil\nBRI BRImo\nNo. Ref 8829102938\nNominal Transfer\nRp 20.000\nBiaya Admin Rp 2.500\nTanggal 21/11/2026";
        $this->assertEquals(20000.0, PaymentProofAnalyzerService::parseIndonesianAmount($brimoText, 200000));

        $bcaText = "m-Transfer : BERHASIL\nTotal Bayar : IDR 200,000.00\nKe Rek 1234567890";
        $this->assertEquals(200000.0, PaymentProofAnalyzerService::parseIndonesianAmount($bcaText, 200000));

        // Short payment evaluation (Rp 20.000 vs Rp 200.000)
        $evalShort = $analyzer->evaluateAmount(20000.0, 200000.0, 0.0, 'BRI');
        $this->assertSame('mismatch', $evalShort['status']);
        $this->assertEquals(180000.0, $evalShort['shortfall']);
        $this->assertStringContainsString('KURANG BAYAR', $evalShort['message']);

        // Settlement evaluation (Rp 180.000 after Rp 20.000 already paid)
        $evalSettle = $analyzer->evaluateAmount(180000.0, 200000.0, 20000.0, 'BRI');
        $this->assertSame('matched', $evalSettle['status']);
        $this->assertEquals(0.0, $evalSettle['shortfall']);
    }

    public function test_reports_do_not_double_count_manual_entries_and_show_accurate_subtotals_for_village_admin(): void
    {
        $order = $this->createTransferTentOrder();
        $this->actingAs($this->user(4));
        $verifyReq = Request::create('/verify', 'POST', ['confirmed_amount' => '200000']);
        $verifyReq->setUserResolver(fn () => $this->user(4));
        app(RentalOrderActions::class)->admin($verifyReq, $order->id, 'verify');

        // 1 manual report in 'penyewaan' category
        ManualReport::create([
            'category' => 'penyewaan',
            'name' => 'Sewa Kursi Offline',
            'amount' => 50000,
            'quantity' => 2,
            'payment_method' => 'tunai',
            'transaction_date' => today(),
            'created_by' => 4,
            'region_id' => 1,
        ]);

        // Income report must count EXACTLY 2 transactions (1 rental verified + 1 manual), NEVER 3!
        $incomeView = app(ReportController::class)->income(Request::create('/admin/laporan/pendapatan', 'GET', ['year' => now()->year]));
        $data = $incomeView->getData();
        $this->assertEquals(300000, $data['totalPendapatan']);
        $this->assertSame(2, $data['totalTrxYear']);

        // Gas order (3 tabungs x 22.000 = 66.000) placed by RT resident (user 1, region_id=3) on Village 1 service
        $this->actingAs($this->user(1));
        $gasReq = Request::create('/gas/booking', 'POST', [
            'gas_id' => 1,
            'quantity' => 3,
            'buyer_name' => 'Warga RT',
            'buyer_address' => 'RT 01',
            'delivery_method' => 'jemput',
            'payment_method' => 'tunai',
        ]);
        $gasReq->setUserResolver(fn () => $this->user(1));
        $gasReq->headers->set('Accept', 'application/json');
        app(GasOrdering::class)->store($gasReq);

        // Admin Desa (user 4, region_id=1) views Laporan Transaksi: must see real Gas subtotal Rp 66.000 & Tenda name (NOT "Privasi", NOT unit price Rp 22.000, NOT "Alat")
        $this->actingAs($this->user(4));
        $trxView = app(ReportController::class)->transactions(Request::create('/admin/laporan/transaksi', 'GET'));
        $html = view('admin.laporan.partials.transactions_content', $trxView->getData())->render();
        $this->assertStringContainsString('Tenda Komplit Pernikahan', $html);
        $this->assertStringContainsString('Rp 200.000', $html);
        $this->assertStringContainsString('Rp 66.000', $html);
        $this->assertStringNotContainsString('Privasi', $html);

        // QR validation page renders Subtotal vs Nominal Dibayarkan accurately
        $token = hash_hmac('sha256', $order->id . $order->order_number, config('app.key'));
        $res = $this->get('/validasi/transaksi/rental/' . $order->id . '?token=' . $token);
        $res->assertStatus(200)
            ->assertSee('Subtotal Pesanan')
            ->assertSee('Nominal Dibayarkan')
            ->assertSee('Rp 200.000')
            ->assertSee('LUNAS');
    }
}
