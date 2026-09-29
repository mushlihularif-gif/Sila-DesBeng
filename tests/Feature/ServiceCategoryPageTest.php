<?php

namespace Tests\Feature;

use Tests\TestCase;

class ServiceCategoryPageTest extends TestCase
{
    public function test_guest_can_open_belanja_category_and_find_its_destinations(): void
    {
        $response = $this->get(route('service-category.show', 'belanja-kebutuhan'));

        $response->assertOk()
            ->assertSee('Belanja &amp; Kebutuhan', false)
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
}
