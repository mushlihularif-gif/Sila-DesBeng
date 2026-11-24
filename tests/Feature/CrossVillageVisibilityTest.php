<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\RegionSettingController;
use App\Http\Controllers\Api\BerandaController as ApiBerandaController;
use App\Http\Controllers\Api\FasilitasBookingApiController;
use App\Http\Controllers\Api\MobilBookingApiController;
use App\Http\Controllers\User\BerandaController;
use App\Http\Controllers\User\FasilitasUmumBookingController;
use App\Http\Controllers\User\FasilitasUmumUserController;
use App\Http\Controllers\User\MobilBookingController;
use App\Http\Controllers\User\MobilRentalUserController;
use App\Http\Middleware\CheckRegionService;
use App\Models\Barang;
use App\Models\FasilitasUmum;
use App\Models\Gas;
use App\Models\Mobil;
use App\Models\Region;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\Support\EditorialDatabase;
use Tests\TestCase;

class CrossVillageVisibilityTest extends TestCase
{
    use EditorialDatabase { setUp as editorialSetUp; }

    protected function setUp(): void
    {
        $this->editorialSetUp();

        Schema::table('regions', function (Blueprint $t) {
            $t->text('profile_text')->nullable();
            $t->string('contact_phone')->nullable();
            $t->string('contact_email')->nullable();
            $t->json('settings')->nullable();
            $t->json('payment_info')->nullable();
            $t->boolean('is_gas_crisis')->default(false);
            $t->timestamps();
        });
        Schema::table('users', function (Blueprint $t) {
            $t->string('nik_hash')->nullable();
        });
        DB::table('users')->update(['verification_status' => 'verified']);

        // Place Desa A (1) and Desa B (5) under Kecamatan Bengkalis (11)
        DB::table('regions')->insert([
            ['id' => 10, 'name' => 'Kabupaten Bengkalis', 'type' => 'kabupaten', 'parent_id' => null],
            ['id' => 11, 'name' => 'Kecamatan Bengkalis', 'type' => 'kecamatan', 'parent_id' => 10],
        ]);
        DB::table('regions')->whereIn('id', [1, 5])->update(['parent_id' => 11]);

        foreach ([
            '2026_06_15_182007_create_services_table.php',
            '2026_06_15_182008_create_region_services_table.php',
            '2026_06_22_151200_add_is_exclusive_to_region_services_table.php',
        ] as $file) {
            (require database_path('migrations/' . $file))->up();
        }
        Schema::create('system_settings', fn (Blueprint $t) => $t->id());

        DB::table('services')->insert([
            ['id' => 1, 'name' => 'Penjualan Gas', 'slug' => 'penjualan-gas'],
            ['id' => 2, 'name' => 'Penyewaan Alat', 'slug' => 'penyewaan-alat'],
            ['id' => 3, 'name' => 'Penyewaan Mobil', 'slug' => 'penyewaan-mobil'],
            ['id' => 4, 'name' => 'Fasilitas Umum', 'slug' => 'fasilitas-umum'],
        ]);

        Schema::create('gas', function (Blueprint $t) {
            $t->id(); $t->integer('region_id'); $t->string('jenis_gas'); $t->string('kategori')->default('LPG');
            $t->integer('stok')->default(10); $t->decimal('harga_satuan', 12, 2)->default(22000);
            $t->string('status')->default('tersedia'); $t->string('foto')->nullable(); $t->timestamps();
        });
        Schema::create('gas_orders', function (Blueprint $t) {
            $t->id(); $t->integer('gas_id'); $t->integer('region_id')->nullable(); $t->integer('quantity')->default(1);
            $t->string('status')->default('completed'); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('barang', function (Blueprint $t) {
            $t->id(); $t->integer('region_id'); $t->string('nama_barang'); $t->string('kategori')->default('Tenda');
            $t->integer('stok')->default(5); $t->decimal('harga_sewa', 12, 2)->default(700000);
            $t->string('satuan')->default('paket'); $t->string('billing_basis')->default('per_activity');
            $t->string('status')->default('tersedia'); $t->string('foto')->nullable(); $t->timestamps();
        });
        Schema::create('rental_bookings', function (Blueprint $t) {
            $t->id(); $t->integer('barang_id'); $t->integer('region_id')->nullable(); $t->integer('quantity')->default(1);
            $t->string('status')->default('completed'); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('mobils', function (Blueprint $t) {
            $t->id(); $t->integer('region_id'); $t->string('nama_mobil'); $t->string('kategori')->default('Minibus');
            $t->integer('stok')->default(2); $t->decimal('harga_sewa', 12, 2)->default(300000);
            $t->string('satuan')->default('hari'); $t->string('status')->default('tersedia');
            $t->string('foto')->nullable(); $t->timestamps();
        });
        Schema::create('mobil_bookings', function (Blueprint $t) {
            $t->id(); $t->integer('mobil_id'); $t->integer('region_id')->nullable();
            $t->string('status')->default('completed'); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('fasilitas_umums', function (Blueprint $t) {
            $t->id(); $t->integer('region_id'); $t->string('nama_fasilitas'); $t->string('kategori')->default('Gedung');
            $t->integer('stok')->default(1); $t->decimal('harga_sewa', 12, 2)->default(0);
            $t->string('status_biaya')->default('gratis'); $t->string('status')->default('tersedia');
            $t->string('foto')->nullable(); $t->timestamps();
        });
        Schema::create('supirs', function (Blueprint $t) {
            $t->id(); $t->string('nama'); $t->string('kontak')->nullable(); $t->string('foto')->nullable(); $t->string('status')->default('Tersedia'); $t->timestamps();
        });
        Schema::create('fasilitas_pengurus', function (Blueprint $t) {
            $t->id(); $t->integer('fasilitas_id'); $t->integer('pengurus_id'); $t->timestamps();
        });

        // Desa A (region 1) products — will be set EXCLUSIVE (Khusus Warga Desa A)
        Gas::create(['id' => 1, 'region_id' => 1, 'jenis_gas' => 'Gas Eksklusif Desa A', 'stok' => 15, 'harga_satuan' => 21000, 'status' => 'tersedia']);
        Barang::create(['id' => 1, 'region_id' => 1, 'nama_barang' => 'Tenda Eksklusif Desa A', 'stok' => 4, 'harga_sewa' => 700000, 'status' => 'tersedia']);
        Mobil::create(['id' => 1, 'region_id' => 1, 'nama_mobil' => 'Mobil Eksklusif Desa A', 'stok' => 2, 'harga_sewa' => 300000, 'status' => 'tersedia']);
        FasilitasUmum::create(['id' => 1, 'region_id' => 1, 'nama_fasilitas' => 'Aula Eksklusif Desa A', 'stok' => 1, 'status' => 'tersedia']);

        // High historical orders on Desa A so popularity queries would previously leak Desa A!
        DB::table('rental_bookings')->insert(['barang_id' => 1, 'region_id' => 1, 'quantity' => 50, 'status' => 'completed', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('gas_orders')->insert(['gas_id' => 1, 'region_id' => 1, 'quantity' => 50, 'status' => 'completed', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('mobil_bookings')->insert(['mobil_id' => 1, 'region_id' => 1, 'status' => 'completed', 'created_at' => now(), 'updated_at' => now()]);

        // Desa B (region 5) public products (Terbuka Publik)
        Gas::create(['id' => 2, 'region_id' => 5, 'jenis_gas' => 'Gas Publik Desa B', 'stok' => 20, 'harga_satuan' => 22000, 'status' => 'tersedia']);
        Barang::create(['id' => 2, 'region_id' => 5, 'nama_barang' => 'Tenda Publik Desa B', 'stok' => 5, 'harga_sewa' => 650000, 'status' => 'tersedia']);

        // Configure Desa A = ALL EXCLUSIVE (is_exclusive = true), Desa B = PUBLIC (is_exclusive = false)
        foreach ([1, 2, 3, 4] as $sid) {
            DB::table('region_services')->insert([
                'region_id' => 1, 'service_id' => $sid, 'is_active' => true, 'is_exclusive' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        foreach ([1, 2] as $sid) {
            DB::table('region_services')->insert([
                'region_id' => 5, 'service_id' => $sid, 'is_active' => true, 'is_exclusive' => false,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function assertForbidden(callable $fn): void
    {
        try {
            $fn();
            $this->fail('Expected 403 Forbidden');
        } catch (HttpExceptionInterface $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_guest_browses_all_active_villages_while_logged_in_other_village_is_strictly_blocked(): void
    {
        $beranda = app(BerandaController::class);
        $popularMethod = new \ReflectionMethod($beranda, 'getPopularProducts');

        // 1. GUEST (unauthenticated) browsing Kecamatan Bengkalis (11)
        // Guest belum punya identitas desa, jadi etalase semua desa yang layanannya AKTIF (is_active = true) tetap tampil
        // walaupun is_exclusive = true (Khusus Warga Desa). Layanan nonaktif (is_active = false) tetap disembunyikan.
        Auth::logout();
        $guestPopular = $popularMethod->invoke($beranda, now()->year, 11);
        $guestNames = $guestPopular->pluck('name')->all();
        $this->assertNotEmpty($guestNames);

        $guestSearch = collect($beranda->liveSearch(Request::create('/beranda/live-search', 'GET', ['search' => 'Eksklusif']))->getData());
        $this->assertSame(4, $guestSearch->count(), 'Guest harus bisa melihat etalase semua unit aktif di pencarian.');

        $apiBeranda = app(ApiBerandaController::class);
        $guestApiServices = collect($apiBeranda->services()->getData(true)['data'])->pluck('name')->all();
        foreach (['Gas Eksklusif Desa A', 'Tenda Eksklusif Desa A', 'Mobil Eksklusif Desa A', 'Aula Eksklusif Desa A'] as $activeItem) {
            $this->assertContains($activeItem, $guestApiServices);
        }

        // Guest dapat melihat halaman detail & API show produk dari unit aktif (saat klik Booking akan diminta login)
        $this->assertSame(200, app(MobilBookingApiController::class)->show(1)->getStatusCode());
        $this->assertSame(200, app(FasilitasBookingApiController::class)->show(1)->getStatusCode());

        // Guest juga boleh lewat middleware katalog GET untuk desa yang layanannya aktif walau eksklusif
        $guestCatalogReq = Request::create('/unit-penyewaan-mobil', 'GET', ['region_id' => 1]);
        $guestCatalogRes = app(CheckRegionService::class)->handle($guestCatalogReq, fn () => response('OK'), 'penyewaan-mobil');
        $this->assertSame(200, $guestCatalogRes->getStatusCode());

        // Tetapi jika unit DINONAKTIFKAN (is_active = false), guest pun tidak boleh melihatnya
        DB::table('region_services')->where('region_id', 1)->where('service_id', 3)->update(['is_active' => false]);
        $this->assertSame(403, app(MobilBookingApiController::class)->show(1)->getStatusCode());
        $guestSearchDisabled = collect($beranda->liveSearch(Request::create('/beranda/live-search', 'GET', ['search' => 'Mobil Eksklusif Desa A']))->getData());
        $this->assertCount(0, $guestSearchDisabled);
        DB::table('region_services')->where('region_id', 1)->where('service_id', 3)->update(['is_active' => true]);

        // 2. DESA B RESIDENT (user 6, region_id = 5) — SUDAH LOGIN: blokir ketat seluruh layanan eksklusif Desa A!
        $desaBUser = $this->user(6);
        $this->actingAs($desaBUser);
        $desaBPopular = $popularMethod->invoke($beranda, now()->year, 11);
        $desaBNames = $desaBPopular->pluck('name')->all();
        foreach (['Gas Eksklusif Desa A', 'Tenda Eksklusif Desa A', 'Mobil Eksklusif Desa A', 'Aula Eksklusif Desa A'] as $secret) {
            $this->assertNotContains($secret, $desaBNames, "Desa B Beranda leaked: {$secret}");
        }
        $this->assertContains('Tenda Publik Desa B', $desaBNames);

        $desaBSearch = collect($beranda->liveSearch(Request::create('/beranda/live-search', 'GET', ['search' => 'Eksklusif']))->getData());
        $this->assertCount(0, $desaBSearch);

        // Direct show & store blocked for Desa B resident on Desa A's Mobil & Fasilitas
        $this->assertForbidden(fn () => app(MobilRentalUserController::class)->show(1));
        $this->assertForbidden(fn () => app(FasilitasUmumUserController::class)->show(1));
        $this->assertSame(403, app(MobilBookingApiController::class)->show(1)->getStatusCode());
        $this->assertSame(403, app(FasilitasBookingApiController::class)->show(1)->getStatusCode());

        $mobilStoreReq = Request::create('/mobil/booking', 'POST', [
            'mobil_id' => 1, 'jenis_sewa' => 'harian', 'quantity' => 1,
            'start_date' => now()->addDay()->toDateString(), 'end_date' => now()->addDays(2)->toDateString(),
            'delivery_method' => 'jemput', 'rental_purpose' => 'Uji', 'payment_method' => 'tunai',
        ]);
        $mobilStoreReq->setUserResolver(fn () => $desaBUser);
        $this->assertForbidden(fn () => app(MobilBookingController::class)->store($mobilStoreReq));

        $fasilitasStoreReq = Request::create('/fasilitas/booking', 'POST', [
            'fasilitas_id' => 1, 'quantity' => 1,
            'start_date' => now()->addDay()->toDateString(), 'end_date' => now()->addDays(2)->toDateString(),
            'rental_purpose' => 'Uji', 'jenis_acara' => 'sosial',
        ]);
        $fasilitasStoreReq->setUserResolver(fn () => $desaBUser);
        $this->assertForbidden(fn () => app(FasilitasUmumBookingController::class)->store($fasilitasStoreReq));

        $mobilApiReq = Request::create('/api/mobil/bookings', 'POST', [
            'mobil_id' => 1, 'start_date' => now()->addDay()->toDateString(), 'end_date' => now()->addDays(2)->toDateString(),
            'recipient_name' => 'Warga B', 'payment_method' => 'tunai',
        ]);
        $mobilApiReq->setUserResolver(fn () => $desaBUser);
        $this->assertForbidden(fn () => app(MobilBookingApiController::class)->store($mobilApiReq));

        $fasilitasApiReq = Request::create('/api/fasilitas/bookings', 'POST', [
            'fasilitas_id' => 1, 'start_date' => now()->addDay()->toDateString(), 'end_date' => now()->addDays(2)->toDateString(),
            'rental_purpose' => 'Uji', 'jenis_acara' => 'sosial',
        ]);
        $fasilitasApiReq->setUserResolver(fn () => $desaBUser);
        $this->assertForbidden(fn () => app(FasilitasBookingApiController::class)->store($fasilitasApiReq));

        // 3. DESA A RT RESIDENT (user 1, region_id = 3 = RT 01 under Desa A)
        $desaARtUser = $this->user(1);
        $this->actingAs($desaARtUser);
        $this->assertSame(1, Region::resolveServiceRegionId((int) $desaARtUser->region_id));

        $desaAPopular = $popularMethod->invoke($beranda, now()->year, 1);
        $desaANames = $desaAPopular->pluck('name')->all();
        $this->assertContains('Gas Eksklusif Desa A', $desaANames);
        $this->assertContains('Tenda Eksklusif Desa A', $desaANames);
        $this->assertContains('Mobil Eksklusif Desa A', $desaANames);

        $desaASearch = collect($beranda->liveSearch(Request::create('/beranda/live-search', 'GET', ['search' => 'Eksklusif']))->getData());
        $this->assertSame(4, $desaASearch->count());
    }

    public function test_default_exclusive_migration_and_inverted_admin_toggle_work_as_expected(): void
    {
        // Tambahkan layanan pengumuman agar migrasi teruji membedakan unit usaha vs pengumuman
        DB::table('services')->insert(['id' => 5, 'name' => 'Pengumuman', 'slug' => 'pengumuman', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('region_services')->insert([
            'region_id' => 5, 'service_id' => 5, 'is_active' => true, 'is_exclusive' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);

        // Jalankan migrasi default eksklusif: Desa B (sebelumnya is_exclusive = false) harus terkunci otomatis, kecuali pengumuman
        (require database_path('migrations/2026_11_24_000002_default_region_services_exclusive.php'))->up();

        $this->assertTrue((bool) DB::table('region_services')->where('region_id', 5)->where('service_id', 1)->value('is_exclusive'));
        $this->assertFalse((bool) DB::table('region_services')->where('region_id', 5)->where('service_id', 5)->value('is_exclusive'));

        // Admin Desa A menyimpan pengaturan dengan sakelar "Izinkan Desa Lain" OFF untuk semua (default), lalu ON hanya untuk Mobil (id=3)
        $adminDesaA = $this->user(4);
        $this->actingAs($adminDesaA);

        $reqAllLocked = Request::create('/admin/region-settings', 'POST', [
            'access_toggle_mode' => 'public',
            'services' => [1, 2, 3, 4],
            'public_services' => [],
            'gas_delivery_antar_active' => '1',
            'alat_delivery_antar_active' => '1',
            'mobil_rental_delivery_antar_active' => '1',
        ]);
        $reqAllLocked->setUserResolver(fn () => $adminDesaA);
        app(RegionSettingController::class)->update($reqAllLocked);

        foreach ([1, 2, 3, 4] as $sid) {
            $this->assertTrue((bool) DB::table('region_services')->where('region_id', 1)->where('service_id', $sid)->value('is_exclusive'));
        }

        // Nyalakan sakelar "Izinkan Desa Lain" khusus Mobil (id=3)
        $reqOpenMobil = Request::create('/admin/region-settings', 'POST', [
            'access_toggle_mode' => 'public',
            'services' => [1, 2, 3, 4],
            'public_services' => [3],
            'gas_delivery_antar_active' => '1',
            'alat_delivery_antar_active' => '1',
            'mobil_rental_delivery_antar_active' => '1',
        ]);
        $reqOpenMobil->setUserResolver(fn () => $adminDesaA);
        app(RegionSettingController::class)->update($reqOpenMobil);

        $this->assertFalse((bool) DB::table('region_services')->where('region_id', 1)->where('service_id', 3)->value('is_exclusive'));
        $this->assertTrue((bool) DB::table('region_services')->where('region_id', 1)->where('service_id', 1)->value('is_exclusive'));

        // Auto-aktivasi produk tambahan tidak boleh menimpa is_exclusive = false yang sudah dipilih admin
        \App\Models\RegionService::aktifkanUntukWilayah(1, 3);
        $this->assertFalse((bool) DB::table('region_services')->where('region_id', 1)->where('service_id', 3)->value('is_exclusive'));
    }

    public function test_disabled_service_stays_hidden_even_for_own_village_and_middleware_does_not_reenable_it(): void
    {
        DB::table('region_services')->where('region_id', 1)->where('service_id', 3)->update(['is_active' => false]);

        $desaARtUser = $this->user(1);
        $this->actingAs($desaARtUser);

        $allowedMobil = Region::wilayahLayananTerlihat($desaARtUser->region_id, 'penyewaan-mobil');
        $this->assertNotContains(1, $allowedMobil);

        // Middleware must redirect away and NEVER flip is_active back to true
        $req = Request::create('/unit-penyewaan-mobil', 'GET', ['region_id' => 1]);
        $req->setUserResolver(fn () => $desaARtUser);
        $response = app(CheckRegionService::class)->handle($req, fn () => response('OK'), 'penyewaan-mobil');
        $this->assertSame(302, $response->getStatusCode());
        $this->assertFalse((bool) DB::table('region_services')->where('region_id', 1)->where('service_id', 3)->value('is_active'));
    }

    public function test_opening_village_service_to_public_makes_it_visible_to_other_villages(): void
    {
        DB::table('region_services')->where('region_id', 1)->where('service_id', 3)->update(['is_exclusive' => false]);

        $desaBUser = $this->user(6);
        $this->actingAs($desaBUser);

        $allowedMobil = Region::wilayahLayananTerlihat($desaBUser->region_id, 'penyewaan-mobil');
        $this->assertContains(1, $allowedMobil);

        $beranda = app(BerandaController::class);
        $searchNames = collect($beranda->liveSearch(Request::create('/beranda/live-search', 'GET', ['search' => 'Mobil Eksklusif Desa A']))->getData())->pluck('name')->all();
        $this->assertContains('Mobil Eksklusif Desa A', $searchNames);
    }
}
