<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\UnitFasilitasUmumController;
use App\Http\Controllers\Api\FasilitasBookingApiController;
use App\Http\Controllers\User\FasilitasUmumBookingController;
use App\Models\FasilitasUmum;
use App\Models\FasilitasUmumBooking;
use App\Models\Gas;
use App\Models\Region;
use App\Models\User;
use App\Support\ProfilPembayaranWilayah;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Tests\Support\EditorialDatabase;
use Tests\TestCase;

class FasilitasPricingAndGasLayoutTest extends TestCase
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
            $t->string('status')->default('aktif');
            $t->boolean('staff_enabled')->default(false);
            $t->unsignedBigInteger('created_by')->nullable();
        });
        DB::table('users')->update([
            'email' => 'warga@example.test',
            'verification_status' => 'verified',
            'status' => 'aktif',
        ]);

        Schema::create('staff_permissions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('unit_key');
            $t->timestamps();
        });

        Schema::create('system_settings', fn (Blueprint $t) => $t->id());
        foreach ([
            '2026_06_15_182007_create_services_table.php',
            '2026_06_15_182008_create_region_services_table.php',
            '2026_06_22_151200_add_is_exclusive_to_region_services_table.php',
            '2026_08_11_120100_create_wallet_transactions_table.php',
        ] as $file) {
            (require database_path('migrations/' . $file))->up();
        }

        Schema::create('fasilitas_umums', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id');
            $t->string('nama_fasilitas');
            $t->text('deskripsi')->nullable();
            $t->string('kategori')->default('Gedung Serbaguna');
            $t->integer('stok')->default(2);
            $t->string('satuan')->default('Unit');
            $t->string('status')->default('Tersedia');
            $t->string('lokasi')->nullable();
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->string('foto')->nullable();
            $t->string('foto_2')->nullable();
            $t->string('foto_3')->nullable();
            $t->string('opsi_supir')->nullable();
            $t->string('bbm_ditanggung')->nullable();
            $t->timestamps();
        });

        // Jalankan migrasi enum lama lalu migrasi perluasan status_biaya
        (require database_path('migrations/2026_07_21_200951_add_rental_fields_to_fasilitas_umums_table.php'))->up();
        (require database_path('migrations/2026_11_24_000001_expand_fasilitas_status_biaya_column.php'))->up();

        Schema::create('fasilitas_umum_bookings', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->nullable();
            $t->string('order_number')->nullable();
            $t->integer('user_id');
            $t->integer('fasilitas_id');
            $t->integer('region_id')->nullable();
            $t->string('delivery_method')->nullable();
            $t->string('payment_method')->nullable();
            $t->string('payment_proof')->nullable();
            $t->string('recipient_name')->nullable();
            $t->text('delivery_address')->nullable();
            $t->integer('quantity')->default(1);
            $t->date('start_date');
            $t->date('end_date');
            $t->text('rental_purpose')->nullable();
            $t->string('jenis_acara')->default('sosial');
            $t->string('surat_pengantar')->nullable();
            $t->boolean('butuh_gudang')->default(false);
            $t->boolean('dengan_supir')->default(false);
            $t->decimal('total_amount', 12, 2)->default(0);
            $t->string('status')->default('pending');
            $t->text('cancellation_reason')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('gas', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id');
            $t->string('jenis_gas');
            $t->string('kategori')->default('LPG 3 Kg');
            $t->integer('stok')->default(10);
            $t->decimal('harga_satuan', 12, 2)->default(21000);
            $t->string('status')->default('tersedia');
            $t->string('lokasi')->nullable();
            $t->string('nama_pengecer')->nullable();
            $t->string('kontak_pengecer')->nullable();
            $t->string('foto')->nullable();
            $t->timestamps();
        });

        Schema::create('supirs', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id')->nullable();
            $t->string('nama');
            $t->string('kontak')->nullable();
            $t->string('foto')->nullable();
            $t->string('status')->default('Tersedia');
            $t->string('tipe')->default('pengurus_gedung');
            $t->timestamps();
        });
        Schema::create('fasilitas_pengurus', function (Blueprint $t) {
            $t->id();
            $t->integer('fasilitas_id');
            $t->integer('pengurus_id');
            $t->timestamps();
        });
        if (! Schema::hasTable('admin_notifications')) {
            Schema::create('admin_notifications', function (Blueprint $t) {
                $t->id();
                $t->string('type');
                $t->integer('reference_id')->nullable();
                $t->integer('region_id')->nullable();
                $t->string('title');
                $t->text('message');
                $t->boolean('is_read')->default(false);
                $t->timestamps();
            });
        }
        Schema::create('alamat_warga', function (Blueprint $t) {
            $t->id();
            $t->integer('user_id');
            $t->integer('region_id')->nullable();
            $t->string('label')->nullable();
            $t->string('nama_penerima')->nullable();
            $t->text('alamat_lengkap')->nullable();
            $t->boolean('is_utama')->default(false);
            $t->timestamps();
        });

        // Aktifkan layanan Fasilitas Umum pada Desa A (region 1)
        $serviceId = DB::table('services')->insertGetId([
            'name' => 'Fasilitas Umum',
            'slug' => 'fasilitas-umum',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('region_services')->insert([
            'region_id' => 1,
            'service_id' => $serviceId,
            'is_active' => true,
            'is_exclusive' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Region::whereKey(1)->update([
            'payment_info' => [
                'bank_active' => true,
                'cash_only_active' => true,
                'ewallet_active' => true,
                'bank_name' => 'BSI',
                'account_number' => '7266899658',
                'account_holder' => 'Pemerintah Desa Pematang Duku Timur',
                'ewallet_name' => 'DANA',
                'ewallet_number' => '081234567890',
                'ewallet_holder' => 'Bendahara Desa',
            ],
        ]);
    }

    private function makeStaffUser(): User
    {
        $id = DB::table('users')->insertGetId([
            'name' => 'Staf Fasilitas',
            'email' => 'staf-fasilitas@example.test',
            'role' => 'staff',
            'region_id' => 1,
            'verification_status' => 'verified',
            'status' => 'aktif',
            'staff_enabled' => true,
            'created_by' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('staff_permissions')->insert([
            'user_id' => $id,
            'unit_key' => 'fasilitas_umum',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::findOrFail($id);
    }

    private function facilityPayload(string $statusBiaya, ?string $hargaSewa): array
    {
        return [
            'nama_fasilitas' => 'Gedung Serbaguna Desa',
            'deskripsi' => 'Gedung pertemuan kapasitas 300 orang.',
            'stok' => 1,
            'status' => 'Tersedia',
            'kategori' => 'Gedung Serbaguna',
            'lokasi' => 'Jl. Makmur Desa',
            'status_biaya' => $statusBiaya,
            'harga_sewa' => $hargaSewa,
        ];
    }

    public function test_admin_and_staff_cannot_save_paid_facility_without_positive_nominal(): void
    {
        $actors = [
            'admin_desa' => User::findOrFail(4),
            'staff' => $this->makeStaffUser(),
        ];

        $controller = new UnitFasilitasUmumController();

        foreach ($actors as $roleLabel => $actor) {
            Auth::login($actor);

            foreach (['berbayar', 'berbayar_semua'] as $scheme) {
                foreach ([null, '', '0', 'Rp 0'] as $invalidPrice) {
                    $request = Request::create('/admin/unit/fasilitas_umum', 'POST', $this->facilityPayload($scheme, $invalidPrice));
                    $request->setUserResolver(fn () => $actor);

                    try {
                        $controller->store($request);
                        $this->fail("Expected ValidationException for {$roleLabel} with scheme={$scheme} and harga_sewa=" . var_export($invalidPrice, true));
                    } catch (ValidationException $e) {
                        $this->assertArrayHasKey('harga_sewa', $e->errors(), "Missing harga_sewa error for {$roleLabel} ({$scheme})");
                    }
                }
            }
        }
    }

    public function test_admin_and_staff_can_create_and_update_all_three_pricing_schemes(): void
    {
        $actors = [
            'admin_desa' => User::findOrFail(4),
            'staff' => $this->makeStaffUser(),
        ];

        $controller = new UnitFasilitasUmumController();

        foreach ($actors as $roleLabel => $actor) {
            Auth::login($actor);

            // 1. Create berbayar_semua
            $storeReq = Request::create('/admin/unit/fasilitas_umum', 'POST', $this->facilityPayload('berbayar_semua', '350.000'));
            $storeReq->setUserResolver(fn () => $actor);
            $controller->store($storeReq);

            $created = FasilitasUmum::latest('id')->firstOrFail();
            $this->assertSame('berbayar_semua', $created->status_biaya, "{$roleLabel} failed saving berbayar_semua");
            $this->assertEquals(350000, (int) $created->harga_sewa);

            // 2. Update to berbayar with empty/zero price MUST fail
            $badUpdateReq = Request::create("/admin/unit/fasilitas_umum/{$created->id}", 'PUT', $this->facilityPayload('berbayar', '0'));
            $badUpdateReq->setUserResolver(fn () => $actor);
            try {
                $controller->update($badUpdateReq, $created->id);
                $this->fail("{$roleLabel} should not be allowed to update to berbayar with zero price.");
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('harga_sewa', $e->errors());
            }

            // 3. Update to gratis MUST clear harga_sewa to null
            $freeUpdateReq = Request::create("/admin/unit/fasilitas_umum/{$created->id}", 'PUT', $this->facilityPayload('gratis', '999.000'));
            $freeUpdateReq->setUserResolver(fn () => $actor);
            $controller->update($freeUpdateReq, $created->id);

            $created->refresh();
            $this->assertSame('gratis', $created->status_biaya);
            $this->assertNull($created->harga_sewa);
        }
    }

    public function test_web_and_api_booking_totals_match_all_three_pricing_schemes(): void
    {
        // Warga 1 tinggal di RT 01 (region 3) -> child dari RW 02 (2) -> child dari Desa A (1)
        $citizen = User::findOrFail(1);
        Auth::login($citizen);

        $gratisItem = FasilitasUmum::create([
            'region_id' => 1,
            'nama_fasilitas' => 'Balai Gratis',
            'deskripsi' => 'Gratis semua',
            'stok' => 5,
            'status' => 'Tersedia',
            'status_biaya' => 'gratis',
            'harga_sewa' => null,
        ]);

        $mixedItem = FasilitasUmum::create([
            'region_id' => 1,
            'nama_fasilitas' => 'Aula Multifungsi',
            'deskripsi' => 'Sosial gratis pribadi bayar',
            'stok' => 5,
            'status' => 'Tersedia',
            'status_biaya' => 'berbayar',
            'harga_sewa' => 500000,
        ]);

        $allPaidItem = FasilitasUmum::create([
            'region_id' => 1,
            'nama_fasilitas' => 'Gedung Komersial Penuh',
            'deskripsi' => 'Semua acara bayar',
            'stok' => 5,
            'status' => 'Tersedia',
            'status_biaya' => 'berbayar_semua',
            'harga_sewa' => 400000,
        ]);

        $start = now()->addDays(2)->toDateString();
        $end = now()->addDays(3)->toDateString(); // 2 hari

        $webController = new FasilitasUmumBookingController();
        $apiController = new FasilitasBookingApiController();

        // A. GRATIS: komersial pun harus Rp 0 & payment_method null
        $reqFreeWeb = Request::create('/fasilitas/book', 'POST', [
            'fasilitas_id' => $gratisItem->id,
            'quantity' => 1,
            'start_date' => $start,
            'end_date' => $end,
            'rental_purpose' => 'Acara keluarga',
            'jenis_acara' => 'komersial',
        ]);
        $reqFreeWeb->setUserResolver(fn () => $citizen);
        $resFreeWeb = $webController->store($reqFreeWeb);
        $bookingFreeWeb = FasilitasUmumBooking::findOrFail($resFreeWeb->getData(true)['booking_id']);
        $this->assertEquals(0, (float) $bookingFreeWeb->total_amount);
        $this->assertNull($bookingFreeWeb->payment_method);

        // B. BERBAYAR (Multifungsi): sosial gratis, komersial = 2 hari * 500.000 * 1 = 1.000.000
        $reqMixedSosial = Request::create('/fasilitas/book', 'POST', [
            'fasilitas_id' => $mixedItem->id,
            'quantity' => 1,
            'start_date' => $start,
            'end_date' => $end,
            'rental_purpose' => 'Rapat RT',
            'jenis_acara' => 'sosial',
        ]);
        $reqMixedSosial->setUserResolver(fn () => $citizen);
        $bMixedSosial = FasilitasUmumBooking::findOrFail($webController->store($reqMixedSosial)->getData(true)['booking_id']);
        $this->assertEquals(0, (float) $bMixedSosial->total_amount);

        $reqMixedKom = Request::create('/fasilitas/book', 'POST', [
            'fasilitas_id' => $mixedItem->id,
            'quantity' => 1,
            'start_date' => $start,
            'end_date' => $end,
            'rental_purpose' => 'Resepsi',
            'jenis_acara' => 'komersial',
            'payment_method' => 'transfer',
        ], [], [
            'payment_proof' => UploadedFile::fake()->image('bukti-kom.jpg'),
        ]);
        $reqMixedKom->setUserResolver(fn () => $citizen);
        $bMixedKom = FasilitasUmumBooking::findOrFail($webController->store($reqMixedKom)->getData(true)['booking_id']);
        $this->assertEquals(1000000, (float) $bMixedKom->total_amount);
        $this->assertSame('transfer', $bMixedKom->payment_method);

        // C. BERBAYAR SEMUA (Web): sosial HARUS ditagih 2 hari * 400.000 * 2 unit = 1.600.000!
        $reqAllPaidSosial = Request::create('/fasilitas/book', 'POST', [
            'fasilitas_id' => $allPaidItem->id,
            'quantity' => 2,
            'start_date' => $start,
            'end_date' => $end,
            'rental_purpose' => 'Turnamen warga',
            'jenis_acara' => 'sosial',
            'payment_method' => 'transfer',
        ], [], [
            'payment_proof' => UploadedFile::fake()->image('bukti-sosial.jpg'),
        ]);
        $reqAllPaidSosial->setUserResolver(fn () => $citizen);
        $bAllPaidSosial = FasilitasUmumBooking::findOrFail($webController->store($reqAllPaidSosial)->getData(true)['booking_id']);
        $this->assertEquals(1600000, (float) $bAllPaidSosial->total_amount);
        $this->assertSame('transfer', $bAllPaidSosial->payment_method);

        // D. BERBAYAR SEMUA (API): sosial juga HARUS ditagih 2 hari * 400.000 * 1 = 800.000!
        $reqAllPaidApi = Request::create('/api/fasilitas/bookings', 'POST', [
            'fasilitas_id' => $allPaidItem->id,
            'quantity' => 1,
            'start_date' => $start,
            'end_date' => $end,
            'rental_purpose' => 'Musyawarah besar',
            'jenis_acara' => 'sosial',
            'payment_method' => 'tunai',
        ]);
        $reqAllPaidApi->setUserResolver(fn () => $citizen);
        $resApi = $apiController->store($reqAllPaidApi);
        $this->assertSame(201, $resApi->getStatusCode());
        $this->assertEquals(800000, (float) $resApi->getData(true)['data']['total_amount']);

        // E. Legacy row safety: berbayar dengan harga_sewa NULL / 0 otomatis dianggap gratis
        $legacyBroken = FasilitasUmum::create([
            'region_id' => 1,
            'nama_fasilitas' => 'Legacy Tanpa Harga',
            'deskripsi' => 'Test fallback',
            'stok' => 1,
            'status' => 'Tersedia',
            'status_biaya' => 'berbayar',
            'harga_sewa' => null,
        ]);
        $this->assertSame('gratis', $legacyBroken->effectivePricingScheme());
        $this->assertFalse($legacyBroken->isPaidForEvent('komersial'));
    }

    public function test_fasilitas_booking_view_displays_accurate_copy_and_nominal_for_all_schemes(): void
    {
        $citizen = User::findOrFail(1);
        Auth::login($citizen);
        view()->share('errors', new ViewErrorBag());
        $setting = ProfilPembayaranWilayah::untuk(1);

        // 1. Gratis Sepenuhnya
        $gratis = FasilitasUmum::create([
            'region_id' => 1,
            'nama_fasilitas' => 'Pendopo Desa',
            'deskripsi' => 'Gratis',
            'kategori' => 'Gedung',
            'stok' => 1,
            'status' => 'Tersedia',
            'status_biaya' => 'gratis',
            'harga_sewa' => null,
        ]);
        $htmlGratis = view('users.fasilitas-umum-booking', [
            'item' => $gratis,
            'setting' => $setting,
            'quantity' => 1,
            'sop_fasilitas' => null,
            'sop_fasilitas_umum' => null,
            'region' => Region::find(1),
            'regionSettings' => [],
            'alamatTersimpan' => collect(),
            'adaGateway' => false,
        ])->render();
        $this->assertStringContainsString('Sosial / Masyarakat (Gratis)', $htmlGratis);
        $this->assertStringContainsString('Komersial / Pribadi (Gratis)', $htmlGratis);
        $this->assertStringNotContainsString('id="pembayaran-fasilitas"', $htmlGratis);

        // 2. Berbayar Pribadi (Multifungsi)
        $mixed = FasilitasUmum::create([
            'region_id' => 1,
            'nama_fasilitas' => 'Aula Desa',
            'deskripsi' => 'Multifungsi',
            'kategori' => 'Gedung',
            'stok' => 1,
            'status' => 'Tersedia',
            'status_biaya' => 'berbayar',
            'harga_sewa' => 500000,
        ]);
        $htmlMixed = view('users.fasilitas-umum-booking', [
            'item' => $mixed,
            'setting' => $setting,
            'quantity' => 1,
            'sop_fasilitas' => null,
            'sop_fasilitas_umum' => null,
            'region' => Region::find(1),
            'regionSettings' => [],
            'alamatTersimpan' => collect(),
            'adaGateway' => false,
        ])->render();
        $this->assertStringNotContainsString('Peminjaman fasilitas ini tidak dipungut biaya (Gratis).', $htmlMixed);
        $this->assertStringContainsString('Sosial / Masyarakat (Gratis)', $htmlMixed);
        $this->assertStringContainsString('Komersial / Pribadi (Rp 500.000 / hari)', $htmlMixed);
        $this->assertStringContainsString('Total yang Harus Ditransfer', $htmlMixed);
        $this->assertStringContainsString('Rp 500.000', $htmlMixed);

        // 3. Semua Acara Berbayar
        $allPaid = FasilitasUmum::create([
            'region_id' => 1,
            'nama_fasilitas' => 'Gedung Pertemuan Utama',
            'deskripsi' => 'Semua berbayar',
            'kategori' => 'Gedung',
            'stok' => 1,
            'status' => 'Tersedia',
            'status_biaya' => 'berbayar_semua',
            'harga_sewa' => 750000,
        ]);
        $htmlAllPaid = view('users.fasilitas-umum-booking', [
            'item' => $allPaid,
            'setting' => $setting,
            'quantity' => 1,
            'sop_fasilitas' => null,
            'sop_fasilitas_umum' => null,
            'region' => Region::find(1),
            'regionSettings' => [],
            'alamatTersimpan' => collect(),
            'adaGateway' => false,
        ])->render();
        $this->assertStringContainsString('Sosial / Masyarakat (Rp 750.000 / hari)', $htmlAllPaid);
        $this->assertStringContainsString('Komersial / Pribadi (Rp 750.000 / hari)', $htmlAllPaid);
        $this->assertStringContainsString('Total yang Harus Ditransfer', $htmlAllPaid);
        $this->assertStringContainsString('Rp 750.000', $htmlAllPaid);
        $this->assertStringContainsString('id="pembayaran-fasilitas" class="mb-6 "', $htmlAllPaid);
    }

    public function test_gas_booking_view_uses_clean_top_layout_without_duplicate_map(): void
    {
        $citizen = User::findOrFail(1);
        Auth::login($citizen);
        view()->share('errors', new ViewErrorBag());
        $setting = ProfilPembayaranWilayah::untuk(1);

        $gas = Gas::create([
            'region_id' => 1,
            'jenis_gas' => 'Gas LPG 3 Kg',
            'kategori' => 'Gas Bersubsidi',
            'stok' => 25,
            'harga_satuan' => 21000,
            'status' => 'tersedia',
            'lokasi' => 'Kantor Desa Pematang Duku Timur',
        ]);

        $html = view('users.gas-booking', [
            'item' => $gas,
            'setting' => $setting,
            'quantity' => 1,
            'sop_gas' => null,
            'alamatTersimpan' => collect(),
            'adaGateway' => false,
            'quotaInfo' => null,
        ])->render();

        $this->assertStringContainsString('Antar Jemput Pembelian Gas', $html);
        // Pastikan peta Lokasi Layanan tidak lagi muncul di atas pilihan Diantar/Ambil Sendiri
        $posChoices = strpos($html, 'segi-delivery-choices');
        $posJemputCard = strpos($html, 'id="gas-jemput-location-card"');
        $posLokasiLayanan = strpos($html, 'Lokasi Layanan');

        $this->assertNotFalse($posChoices);
        $this->assertNotFalse($posJemputCard);
        $this->assertNotFalse($posLokasiLayanan);
        $this->assertGreaterThan($posChoices, $posLokasiLayanan, 'Peta lokasi layanan tidak boleh muncul di atas tombol Diantar / Ambil Sendiri.');
        $this->assertGreaterThan($posJemputCard, $posLokasiLayanan, 'Peta lokasi layanan harus berada di dalam kartu Ambil Sendiri.');
        $this->assertSame(1, substr_count($html, 'Lokasi Layanan'), 'Peta lokasi layanan hanya boleh muncul satu kali (tidak duplikat).');
    }

    public function test_admin_staff_form_fields_renders_all_three_pricing_schemes(): void
    {
        $staff = $this->makeStaffUser();
        Auth::login($staff);
        view()->share('errors', new ViewErrorBag());

        $html = view('admin.unit.fasilitas_umum.partials.form_fields', [
            'fasilitas' => null,
            'savedLocations' => collect(),
            'categories' => collect(),
            'pengurusList' => collect(),
        ])->render();

        $this->assertStringContainsString('value="gratis"', $html);
        $this->assertStringContainsString('value="berbayar"', $html);
        $this->assertStringContainsString('value="berbayar_semua"', $html);
        $this->assertStringContainsString('Semua Acara Berbayar', $html);
        $this->assertStringContainsString('harga_sewa_client_error', $html);
    }
}
