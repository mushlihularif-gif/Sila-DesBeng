<?php

namespace Tests\Feature;

use Tests\TestCase;

class ServiceCategoryPageTest extends TestCase
{
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
