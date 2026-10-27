<?php

namespace Tests\Feature;

use App\Models\{Region, User};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
use Tests\TestCase;

class ServiceCategoryPageTest extends TestCase
{
    private function prepareRegions(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('type');
        });
        Schema::create('services', function (Blueprint $table) {
            $table->id(); $table->string('name');
        });
        Schema::create('region_services', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('region_id'); $table->unsignedBigInteger('service_id');
            $table->boolean('is_active'); $table->boolean('is_exclusive')->default(false); $table->timestamps();
        });
        DB::table('regions')->insert([
            ['id' => 12, 'name' => 'Desa Pilihan', 'type' => 'desa'],
            ['id' => 13, 'name' => 'Desa Asal', 'type' => 'desa'],
        ]);
        DB::table('services')->insert([
            ['id' => 1, 'name' => 'Penjualan Gas'],
            ['id' => 2, 'name' => 'Penyewaan Alat'],
            ['id' => 3, 'name' => 'Pelaporan Warga'],
        ]);
        DB::table('region_services')->insert([
            ['region_id' => 12, 'service_id' => 1, 'is_active' => true],
            ['region_id' => 12, 'service_id' => 2, 'is_active' => true],
            ['region_id' => 12, 'service_id' => 3, 'is_active' => false],
        ]);
    }

    public function test_village_carousel_opens_categories_before_individual_services(): void
    {
        $region = new Region(['name' => 'Desa Pilihan', 'type' => 'desa']);
        $region->id = 12;
        $html = $this->view('users.partials.unit-carousel', [
            'region' => $region, 'activeServices' => ['Penjualan Gas', 'Penyewaan Alat'],
        ]);
        $html->assertSee(route('service-category.show', ['category' => 'belanja-kebutuhan', 'region_id' => 12, 'from' => 'region']))
            ->assertSee(route('service-category.show', ['category' => 'layanan-daerah', 'region_id' => 12, 'from' => 'region']))
            ->assertSee(route('announcements.index', ['region_id' => 12]))
            ->assertSee('Layanan berikutnya')
            ->assertDontSee(route('gas.sales'))
            ->assertDontSee(route('rental.equipment'));
    }

    public function test_category_preserves_village_on_destinations_and_return_link(): void
    {
        $this->prepareRegions();
        $this->get(route('service-category.show', ['category' => 'belanja-kebutuhan', 'region_id' => 12, 'from' => 'region']))
            ->assertOk()
            ->assertSee(route('gas.sales', ['region_id' => 12]))
            ->assertSee(route('pasar.index', ['region_id' => 12]))
            ->assertSee('Kembali ke Desa Pilihan')
            ->assertSee(route('bumdes.detail', ['slug' => 'desa-pilihan', 'id' => 12]) . '#unit-carousel-container');
        $this->get(route('service-category.show', ['category' => 'layanan-daerah', 'region_id' => 12]))
            ->assertOk()
            ->assertSee(route('rental.equipment', ['region_id' => 12]))
            ->assertDontSee(route('pelaporan.landing', ['region_id' => 12]));
        $this->get(route('service-category.show', ['category' => 'layanan-daerah', 'region_id' => 999]))->assertNotFound();
    }

    public function test_explicit_village_is_not_replaced_by_logged_in_users_home_region(): void
    {
        $this->prepareRegions();
        $user = new User(); $user->id = 1; $user->role = 'user'; $user->region_id = 13;
        $this->actingAs($user);
        $request = \Illuminate\Http\Request::create('/kategori-layanan/belanja-kebutuhan', 'GET', ['region_id' => 12]);
        $data = app(\App\Http\Controllers\User\ServiceCategoryController::class)->show($request, 'belanja-kebutuhan')->getData();
        $this->assertSame(12, $data['region']->id);
        $this->assertSame(route('gas.sales', ['region_id' => 12]), $data['items']->first()['url']);
    }

    public function test_public_layout_has_valid_organization_schema_without_blade_directives(): void
    {
        $response = $this->get(route('service-category.show', 'belanja-kebutuhan'));
        $response->assertOk();
        $this->assertSame(1, preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $response->getContent(), $matches));
        $schema = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('https://schema.org', $schema['@context'] ?? null);
        $this->assertSame('GovernmentOrganization', $schema['@type']);
        $this->assertSame('SegiDaerah', $schema['name']);
        $this->assertStringNotContainsString('<?php', $matches[1]);
    }

    public function test_guest_can_open_belanja_category_and_find_its_destinations(): void
    {
        $response = $this->get(route('service-category.show', 'belanja-kebutuhan'));

        $response->assertOk()
            ->assertSee('Belanja dan Kebutuhan')
            ->assertSee('Gas Daerah')
            ->assertSee('Pasar Daerah');
    }

    public function test_guest_can_open_layanan_category_and_find_its_destinations(): void
    {
        $response = $this->get(route('service-category.show', 'layanan-daerah'));

        $response->assertOk()
            ->assertSee('Layanan Daerah')
            ->assertSee('Penyewaan Alat')
            ->assertSee('Penyewaan Transportasi')
            ->assertSee('Fasilitas Umum')
            ->assertSee('Pelaporan Warga');
    }

    public function test_unknown_service_category_returns_not_found(): void
    {
        $this->get('/kategori-layanan/tidak-ada')->assertNotFound();
    }

    public function test_pelayanan_cards_link_to_their_actual_pages(): void
    {
        $response = $this->get(route('pelayanan'));

        $response->assertOk()
            ->assertSee('class="service-card-link', false)
            ->assertSee(route('bumdes.profil', ['redirect' => 'rental.equipment']), false)
            ->assertSee(route('bumdes.laporan'), false)
            ->assertSee(route('bumdes.profil', ['redirect' => 'gas.sales']), false)
            ->assertSee(route('bumdes.profil', ['redirect' => 'mobil.rental.equipment']), false)
            ->assertSee(route('bumdes.profil', ['redirect' => 'user.fasilitas-umum.equipment']), false)
            ->assertSee(route('bumdes.profil', ['redirect' => 'pelaporan.landing']), false)
            ->assertSee(route('announcements.index'), false)
            ->assertSee(route('pasar.index'), false);
    }
}
