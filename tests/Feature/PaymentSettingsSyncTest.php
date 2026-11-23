<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\RegionSettingController;
use App\Http\Controllers\Api\FasilitasBookingApiController;
use App\Http\Controllers\Api\MobilBookingApiController;
use App\Http\Controllers\User\FasilitasUmumBookingController;
use App\Http\Controllers\User\MobilBookingController;
use App\Models\Barang;
use App\Models\FasilitasUmum;
use App\Models\Gas;
use App\Models\Mobil;
use App\Models\Region;
use App\Services\GasOrdering;
use App\Services\RentalOrdering;
use App\Support\ProfilPembayaranWilayah;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\Support\EditorialDatabase;
use Tests\TestCase;

class PaymentSettingsSyncTest extends TestCase
{
    use EditorialDatabase { setUp as editorialSetUp; }

    protected function setUp(): void
    {
        $this->editorialSetUp();
        Storage::fake('public');

        Schema::table('regions', function (Blueprint $t) {
            $t->json('settings')->nullable();
            $t->json('payment_info')->nullable();
            $t->boolean('is_gas_crisis')->default(false);
            $t->timestamps();
        });
        Schema::table('users', function (Blueprint $t) {
            $t->string('nik_hash')->nullable();
        });
        DB::table('users')->update(['email' => 'warga@example.test', 'verification_status' => 'verified']);

        Schema::create('system_settings', fn (Blueprint $t) => $t->id());
        foreach ([
            '2026_06_15_182007_create_services_table.php',
            '2026_06_15_182008_create_region_services_table.php',
            '2026_06_22_151200_add_is_exclusive_to_region_services_table.php',
            '2026_08_24_215518_create_family_cards_table.php',
            '2026_08_24_215518_create_family_members_table.php',
            '2026_08_11_120100_create_wallet_transactions_table.php',
        ] as $file) {
            (require database_path('migrations/' . $file))->up();
        }

        Schema::create('barang', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id');
            $t->string('nama_barang');
            $t->string('kategori')->default('Perlengkapan Acara');
            $t->integer('stok')->default(10);
            $t->decimal('harga_sewa', 12, 2)->default(700000);
            $t->string('status')->default('tersedia');
            $t->string('lokasi')->nullable();
            $t->string('foto')->nullable();
            $t->timestamps();
        });
        (require database_path('migrations/2025_12_04_000000_create_rental_bookings_table.php'))->up();
        Schema::table('rental_bookings', function (Blueprint $t) {
            $t->uuid('uuid')->unique();
            $t->string('order_number')->unique();
            $t->integer('region_id')->nullable();
            $t->text('rental_purpose')->nullable();
            $t->softDeletes();
        });
        (require database_path('migrations/2026_11_12_000002_harden_rental_order_submission.php'))->up();
        (require database_path('migrations/2026_11_13_000001_add_rental_billing_basis.php'))->up();

        Schema::create('gas', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id');
            $t->string('jenis_gas');
            $t->string('kategori')->default('LPG 3 Kg');
            $t->integer('stok')->default(10);
            $t->decimal('harga_satuan', 12, 2)->default(22000);
            $t->string('status')->default('tersedia');
            $t->string('lokasi')->nullable();
            $t->string('foto')->nullable();
            $t->timestamps();
        });
        Schema::create('gas_orders', function (Blueprint $t) {
            $t->id();
            $t->string('order_number')->unique();
            $t->integer('user_id');
            $t->integer('gas_id');
            $t->string('item_name');
            $t->integer('quantity');
            $t->decimal('price', 12, 2);
            $t->timestamp('order_date')->nullable();
            $t->string('delivery_method');
            $t->string('payment_method');
            $t->string('address')->nullable();
            $t->string('full_name')->nullable();
            $t->string('email')->nullable();
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->string('status')->default('pending');
            $t->string('proof_of_payment')->nullable();
            $t->string('idempotency_key')->nullable();
            $t->string('submission_hash')->nullable();
            $t->string('nomor_kk_hash')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('mobils', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id');
            $t->string('nama_mobil');
            $t->string('kategori')->default('Minibus');
            $t->integer('stok')->default(3);
            $t->decimal('harga_sewa', 12, 2)->default(300000);
            $t->string('status')->default('tersedia');
            $t->string('lokasi')->nullable();
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
            $t->string('jenis_sewa')->default('harian');
            $t->boolean('dengan_supir')->default(false);
            $t->string('delivery_method')->default('jemput');
            $t->text('rental_purpose')->nullable();
            $t->integer('quantity')->default(1);
            $t->date('start_date');
            $t->date('end_date');
            $t->decimal('distance_km', 8, 2)->nullable();
            $t->string('tujuan_wilayah')->nullable();
            $t->string('recipient_name')->nullable();
            $t->text('delivery_address')->nullable();
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->string('payment_method');
            $t->string('payment_proof')->nullable();
            $t->decimal('total_amount', 12, 2);
            $t->string('status')->default('pending');
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('fasilitas_umums', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id');
            $t->string('nama_fasilitas');
            $t->string('kategori')->default('Gedung');
            $t->integer('stok')->default(2);
            $t->decimal('harga_sewa', 12, 2)->default(500000);
            $t->string('status_biaya')->default('berbayar');
            $t->string('status')->default('tersedia');
            $t->string('lokasi')->nullable();
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
            $t->string('delivery_method')->default('lokasi');
            $t->string('payment_method')->nullable();
            $t->string('payment_proof')->nullable();
            $t->string('recipient_name')->nullable();
            $t->text('delivery_address')->nullable();
            $t->integer('quantity')->default(1);
            $t->date('start_date');
            $t->date('end_date');
            $t->text('rental_purpose')->nullable();
            $t->string('jenis_acara')->default('komersial');
            $t->string('surat_pengantar')->nullable();
            $t->boolean('butuh_gudang')->default(false);
            $t->boolean('dengan_supir')->default(false);
            $t->decimal('total_amount', 12, 2)->default(0);
            $t->string('status')->default('pending');
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('transaction_receipts', function (Blueprint $t) {
            $t->id();
            $t->string('booking_type');
            $t->integer('booking_id');
            $t->string('receipt_number');
            $t->integer('user_id');
            $t->integer('region_id')->nullable();
            $t->string('item_name');
            $t->integer('quantity');
            $t->decimal('total_amount', 12, 2);
            $t->string('payment_method');
            $t->timestamps();
        });
        Schema::create('supirs', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('kontak')->nullable();
            $t->string('foto')->nullable();
            $t->string('status')->default('Tersedia');
            $t->timestamps();
        });
        Schema::create('fasilitas_pengurus', function (Blueprint $t) {
            $t->id();
            $t->integer('fasilitas_id');
            $t->integer('pengurus_id');
            $t->timestamps();
        });
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

        Barang::create(['id' => 1, 'region_id' => 1, 'nama_barang' => 'Tenda Komplit', 'stok' => 5, 'harga_sewa' => 700000, 'billing_basis' => 'per_activity', 'status' => 'tersedia', 'lokasi' => 'Kantor Desa A']);
        Gas::create(['id' => 1, 'region_id' => 1, 'jenis_gas' => 'LPG 3 Kg', 'stok' => 10, 'harga_satuan' => 22000, 'status' => 'tersedia', 'lokasi' => 'Kantor Desa A']);
        Mobil::create(['id' => 1, 'region_id' => 1, 'nama_mobil' => 'Minibus Desa', 'stok' => 2, 'harga_sewa' => 300000, 'status' => 'tersedia', 'lokasi' => 'Kantor Desa A']);
        FasilitasUmum::create(['id' => 1, 'region_id' => 1, 'nama_fasilitas' => 'Gedung Serbaguna', 'stok' => 1, 'harga_sewa' => 500000, 'status_biaya' => 'berbayar', 'status' => 'tersedia', 'lokasi' => 'Kantor Desa A']);
    }

    public function test_disabling_cash_hides_tunai_and_blocks_cash_orders_across_all_services(): void
    {
        Region::find(1)->update([
            'payment_info' => [
                'bank_active' => true,
                'bank_name' => 'BSI',
                'account_number' => '7266899658',
                'account_name' => 'Pemerintah Desa Pematang Duku Timur',
                'cash_only_active' => false,
                'ewallet_active' => false,
            ],
        ]);

        $profile = ProfilPembayaranWilayah::untuk(1);
        $this->assertSame(['transfer'], $profile->metodeTersedia());
        $publik = $profile->publik();
        $this->assertSame(['transfer'], $publik['payment_methods']);
        $this->assertSame('BSI', $publik['bank_name']);
        $this->assertNull($publik['ewallet_number']);

        $citizen = $this->user(1);
        Auth::setUser($citizen);
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());

        // 0. All 4 booking views render ONLY Transfer Bank (no Tunai / COD)
        $rentalHtml = view('users.rental-booking', [
            'item' => Barang::with('region')->find(1),
            'setting' => $profile,
            'quantity' => 1,
            'sop_penyewaan_alat' => 'Jaga alat.',
            'alamatTersimpan' => collect(),
            'adaGateway' => false,
        ])->render();
        $this->assertStringContainsString('btn-sewa-transfer', $rentalHtml);
        $this->assertStringContainsString('7266899658', $rentalHtml);
        $this->assertStringNotContainsString('btn-sewa-tunai', $rentalHtml);
        $this->assertStringNotContainsString('Bayar Di Tempat (COD)', $rentalHtml);

        $gasHtml = view('users.gas-booking', [
            'item' => Gas::with('region')->find(1),
            'setting' => $profile,
            'quantity' => 1,
            'alamatTersimpan' => collect(),
        ])->render();
        $this->assertStringContainsString('btn-transfer', $gasHtml);
        $this->assertStringNotContainsString('btn-tunai', $gasHtml);
        $this->assertStringNotContainsString('Bayar Di Tempat (COD)', $gasHtml);

        $mobilHtml = view('users.mobil-rental-booking', [
            'item' => Mobil::with('region')->find(1),
            'setting' => $profile,
            'quantity' => 1,
            'sop_mobil' => 'Jaga mobil.',
            'isWilayah' => false,
            'tarifWilayah' => [],
            'adaGateway' => false,
            'alamatTersimpan' => collect(),
        ])->render();
        $this->assertStringContainsString('btn-mobil-transfer', $mobilHtml);
        $this->assertStringNotContainsString('btn-mobil-tunai', $mobilHtml);
        $this->assertStringNotContainsString('Bayar Di Tempat (COD)', $mobilHtml);

        $fasilitasHtml = view('users.fasilitas-umum-booking', [
            'item' => FasilitasUmum::with('region')->find(1),
            'setting' => $profile,
            'quantity' => 1,
            'sop_fasilitas_umum' => 'Jaga gedung.',
            'region' => Region::find(1),
            'regionSettings' => [],
            'alamatTersimpan' => collect(),
            'adaGateway' => false,
        ])->render();
        $this->assertStringContainsString('id="btn-fasilitas-transfer"', $fasilitasHtml);
        $this->assertStringNotContainsString('id="btn-fasilitas-tunai"', $fasilitasHtml);
        $this->assertStringNotContainsString('Bayar di Tempat', $fasilitasHtml);

        // 1. Rental jemput rejects tunai, accepts transfer with proof
        $rentalTunai = Request::create('/rental/booking', 'POST', [
            'barang_id' => 1,
            'quantity' => 1,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'delivery_method' => 'jemput',
            'recipient_name' => 'Warga Uji',
            'rental_purpose' => 'Acara keluarga',
            'payment_method' => 'tunai',
            'sop_accepted' => 1,
        ]);
        $rentalTunai->setUserResolver(fn () => $citizen);
        try {
            app(RentalOrdering::class)->store($rentalTunai);
            $this->fail('Rental tunai should be rejected when cash is disabled');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('payment_method', $e->errors());
        }

        $rentalTransfer = Request::create('/rental/booking', 'POST', [
            'barang_id' => 1,
            'quantity' => 1,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'delivery_method' => 'jemput',
            'recipient_name' => 'Warga Uji',
            'rental_purpose' => 'Acara keluarga',
            'payment_method' => 'transfer',
            'sop_accepted' => 1,
        ]);
        $rentalTransfer->files->set('payment_proof', UploadedFile::fake()->image('bukti.jpg'));
        $rentalTransfer->setUserResolver(fn () => $citizen);
        $orders = app(RentalOrdering::class)->store($rentalTransfer);
        $this->assertSame('jemput', $orders[0]->delivery_method);
        $this->assertSame('transfer', $orders[0]->payment_method);

        // 2. Gas rejects tunai
        $gasTunai = Request::create('/gas/booking', 'POST', [
            'gas_id' => 1,
            'quantity' => 1,
            'delivery_method' => 'jemput',
            'buyer_name' => 'Warga Uji',
            'payment_method' => 'tunai',
        ]);
        $gasTunai->setUserResolver(fn () => $citizen);
        try {
            app(GasOrdering::class)->store($gasTunai);
            $this->fail('Gas tunai should be rejected when cash is disabled');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('payment_method', $e->errors());
        }

        // 3. Mobil web & API reject tunai
        $mobilWebTunai = Request::create('/mobil/booking', 'POST', [
            'mobil_id' => 1,
            'jenis_sewa' => 'harian',
            'quantity' => 1,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'delivery_method' => 'jemput',
            'rental_purpose' => 'Perjalanan',
            'payment_method' => 'tunai',
        ]);
        $mobilWebTunai->setUserResolver(fn () => $citizen);
        try {
            app(MobilBookingController::class)->store($mobilWebTunai);
            $this->fail('Mobil web tunai should be rejected when cash is disabled');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('payment_method', $e->errors());
        }

        $mobilApiTunai = Request::create('/api/mobil/bookings', 'POST', [
            'mobil_id' => 1,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'recipient_name' => 'Warga Uji',
            'payment_method' => 'tunai',
        ]);
        $mobilApiTunai->setUserResolver(fn () => $citizen);
        try {
            app(MobilBookingApiController::class)->store($mobilApiTunai);
            $this->fail('Mobil API tunai should be rejected when cash is disabled');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('payment_method', $e->errors());
        }

        // 4. Fasilitas komersial web & API reject tunai
        $fasilitasWebTunai = Request::create('/fasilitas/booking', 'POST', [
            'fasilitas_id' => 1,
            'quantity' => 1,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'rental_purpose' => 'Pameran',
            'jenis_acara' => 'komersial',
            'payment_method' => 'tunai',
        ]);
        $fasilitasWebTunai->setUserResolver(fn () => $citizen);
        try {
            app(FasilitasUmumBookingController::class)->store($fasilitasWebTunai);
            $this->fail('Fasilitas web tunai should be rejected when cash is disabled');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('payment_method', $e->errors());
        }

        $fasilitasApiTunai = Request::create('/api/fasilitas/bookings', 'POST', [
            'fasilitas_id' => 1,
            'quantity' => 1,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'rental_purpose' => 'Pameran',
            'jenis_acara' => 'komersial',
            'payment_method' => 'tunai',
        ]);
        $fasilitasApiTunai->setUserResolver(fn () => $citizen);
        try {
            app(FasilitasBookingApiController::class)->store($fasilitasApiTunai);
            $this->fail('Fasilitas API tunai should be rejected when cash is disabled');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('payment_method', $e->errors());
        }
    }

    public function test_ewallet_only_configuration_renders_and_accepts_ewallet_everywhere(): void
    {
        Region::find(1)->update([
            'payment_info' => [
                'bank_active' => false,
                'bank_name' => 'BSI',
                'account_number' => '7266899658',
                'cash_only_active' => false,
                'ewallet_active' => true,
                'ewallet_name' => 'DANA',
                'ewallet_number' => '081234567890',
                'ewallet_account_name' => 'BUMDes Pematang Duku Timur',
            ],
        ]);

        $profile = ProfilPembayaranWilayah::untuk(1);
        $this->assertSame(['ewallet'], $profile->metodeTersedia());
        $publik = $profile->publik();
        $this->assertNull($publik['bank_name']);
        $this->assertNull($publik['account_number']);
        $this->assertSame('DANA', $publik['ewallet_name']);
        $this->assertSame('081234567890', $publik['ewallet_number']);

        $citizen = $this->user(1);
        Auth::setUser($citizen);
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());

        // Verify all 4 booking Blade views render ONLY DANA (no Transfer Bank, no Bayar Tunai)
        $rentalHtml = view('users.rental-booking', [
            'item' => Barang::with('region')->find(1),
            'setting' => $profile,
            'quantity' => 1,
            'sop_penyewaan_alat' => 'Jaga alat dengan baik.',
            'alamatTersimpan' => collect(),
            'adaGateway' => false,
        ])->render();
        $this->assertStringContainsString('081234567890', $rentalHtml);
        $this->assertStringContainsString('value="ewallet"', $rentalHtml);
        $this->assertStringNotContainsString('btn-sewa-transfer', $rentalHtml);
        $this->assertStringNotContainsString('btn-sewa-tunai', $rentalHtml);
        $this->assertStringNotContainsString('Bayar Di Tempat (COD)', $rentalHtml);

        $gasHtml = view('users.gas-booking', [
            'item' => Gas::with('region')->find(1),
            'setting' => $profile,
            'quantity' => 1,
            'alamatTersimpan' => collect(),
        ])->render();
        $this->assertStringContainsString('btn-ewallet', $gasHtml);
        $this->assertStringContainsString('081234567890', $gasHtml);
        $this->assertStringContainsString('value="ewallet"', $gasHtml);
        $this->assertStringNotContainsString('btn-transfer', $gasHtml);
        $this->assertStringNotContainsString('btn-tunai', $gasHtml);

        $mobilHtml = view('users.mobil-rental-booking', [
            'item' => Mobil::with('region')->find(1),
            'setting' => $profile,
            'quantity' => 1,
            'sop_mobil' => 'Jaga mobil.',
            'isWilayah' => false,
            'tarifWilayah' => [],
            'adaGateway' => false,
            'alamatTersimpan' => collect(),
        ])->render();
        $this->assertStringContainsString('btn-mobil-ewallet', $mobilHtml);
        $this->assertStringContainsString('081234567890', $mobilHtml);
        $this->assertStringContainsString('value="ewallet"', $mobilHtml);
        $this->assertStringNotContainsString('btn-mobil-transfer', $mobilHtml);
        $this->assertStringNotContainsString('btn-mobil-tunai', $mobilHtml);

        $fasilitasHtml = view('users.fasilitas-umum-booking', [
            'item' => FasilitasUmum::with('region')->find(1),
            'setting' => $profile,
            'quantity' => 1,
            'sop_fasilitas_umum' => 'Jaga gedung.',
            'region' => Region::find(1),
            'regionSettings' => [],
            'alamatTersimpan' => collect(),
            'adaGateway' => false,
        ])->render();
        $this->assertStringContainsString('id="btn-fasilitas-ewallet"', $fasilitasHtml);
        $this->assertStringContainsString('081234567890', $fasilitasHtml);
        $this->assertStringContainsString('value="ewallet"', $fasilitasHtml);
        $this->assertStringNotContainsString('id="btn-fasilitas-transfer"', $fasilitasHtml);
        $this->assertStringNotContainsString('id="btn-fasilitas-tunai"', $fasilitasHtml);

        // Verify Mobil API & Fasilitas API accept ewallet with proof
        $mobilApi = Request::create('/api/mobil/bookings', 'POST', [
            'mobil_id' => 1,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'recipient_name' => 'Warga Uji',
            'payment_method' => 'ewallet',
        ]);
        $mobilApi->files->set('payment_proof', UploadedFile::fake()->image('dana.jpg'));
        $mobilApi->setUserResolver(fn () => $citizen);
        $this->assertSame(201, app(MobilBookingApiController::class)->store($mobilApi)->getStatusCode());

        $fasilitasApi = Request::create('/api/fasilitas/bookings', 'POST', [
            'fasilitas_id' => 1,
            'quantity' => 1,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'rental_purpose' => 'Pameran',
            'jenis_acara' => 'komersial',
            'payment_method' => 'ewallet',
        ]);
        $fasilitasApi->files->set('payment_proof', UploadedFile::fake()->image('dana2.jpg'));
        $fasilitasApi->setUserResolver(fn () => $citizen);
        $this->assertSame(201, app(FasilitasBookingApiController::class)->store($fasilitasApi)->getStatusCode());
    }

    public function test_admin_payment_update_validates_incomplete_or_empty_configurations(): void
    {
        $admin = $this->user(4);
        $this->actingAs($admin);

        // 1. All toggles off -> error
        $resAllOff = app(RegionSettingController::class)->paymentUpdate(Request::create('/admin/pengaturan-pembayaran-wilayah', 'PUT', []));
        $this->assertSame('Gagal: Minimal satu metode pembayaran (Transfer Bank, Tunai, atau E-Wallet) harus diaktifkan.', session('error'));

        // 2. Bank active without bank name / account number -> error
        $resBadBank = app(RegionSettingController::class)->paymentUpdate(Request::create('/admin/pengaturan-pembayaran-wilayah', 'PUT', [
            'bank_active' => '1',
            'bank_name' => 'BSI',
            'account_number' => '',
        ]));
        $this->assertStringContainsString('Pilih Bank dan isi Nomor Rekening', (string) session('error'));

        // 3. E-wallet active without e-wallet number -> error
        $resBadEwallet = app(RegionSettingController::class)->paymentUpdate(Request::create('/admin/pengaturan-pembayaran-wilayah', 'PUT', [
            'ewallet_active' => '1',
            'ewallet_name' => 'DANA',
            'ewallet_number' => '',
        ]));
        $this->assertStringContainsString('Pilih layanan E-Wallet dan isi Nomor HP', (string) session('error'));

        // 4. Valid save (Bank + E-Wallet, Cash OFF) -> persists & syncs immediately
        session()->forget('error');
        app(RegionSettingController::class)->paymentUpdate(Request::create('/admin/pengaturan-pembayaran-wilayah', 'PUT', [
            'bank_active' => '1',
            'bank_name' => 'BSI',
            'account_number' => '7266899658',
            'account_name' => 'Desa A',
            'ewallet_active' => '1',
            'ewallet_name' => 'DANA',
            'ewallet_number' => '081234567890',
            'ewallet_account_name' => 'BUMDes A',
            'card_theme' => 'dark',
        ]));

        $this->assertSame(['transfer', 'ewallet'], ProfilPembayaranWilayah::untuk(1)->metodeTersedia());
        $this->assertFalse((bool) Region::find(1)->payment_info['cash_only_active']);
    }

    public function test_unconfigured_region_falls_back_to_tunai(): void
    {
        Region::find(1)->update(['payment_info' => null]);
        $this->assertSame(['tunai'], ProfilPembayaranWilayah::untuk(1)->metodeTersedia());
    }
}
