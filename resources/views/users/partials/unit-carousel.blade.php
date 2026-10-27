<section id="unit-carousel-container" class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-16 overflow-hidden relative z-10">
    @include('users.partials.unit-carousel-content', [
        'carouselRegionId' => isset($kecamatan) ? null : $region?->id,
        'carouselFromRegion' => !isset($kecamatan) && $region,
    ])
</section>
