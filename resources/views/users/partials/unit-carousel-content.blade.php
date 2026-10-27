<!-- Judul kategori utama -->
<div class="text-center mt-10 sm:mt-16 mb-8 sm:mb-12 relative">
    <a href="{{ route('pelayanan') }}" aria-label="Buka halaman Tentang Layanan" class="inline-flex items-center gap-2 text-xl font-extrabold tracking-tight no-underline transition hover:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#60a5fa] focus-visible:ring-offset-4 sm:text-2xl md:text-3xl">
        <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Unit</span>
        <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Layanan</span>
    </a>
</div>

    @php
        $carouselFromRegion = $carouselFromRegion ?? false;
        $hasRegionContext = !empty($carouselRegionId);
        $userRegionId = $carouselRegionId ?? null;

        $isServiceActive = function($unitName) use ($hasRegionContext, $activeServices) {
            if (!$hasRegionContext) return true;

            // Menu informasi dan Pasar Daerah tersedia untuk semua wilayah.
            if (in_array($unitName, ['Belanja dan Kebutuhan', 'Pasar Daerah', 'Kabar dan Informasi Daerah', 'Pengumuman dan Event'])) {
                return true;
            }

            $serviceNames = [
                'Unit Penyewaan Alat' => ['Penyewaan Alat'],
                'Unit Penjualan Gas' => ['Penjualan Gas'],
                'Unit Penyewaan Mobil' => ['Penyewaan Mobil', 'Penyewaan Transportasi'],
                'Unit Penyewaan Transportasi' => ['Penyewaan Mobil', 'Penyewaan Transportasi'],
                'Unit Peminjaman Fasilitas Umum' => ['Fasilitas Umum', 'Peminjaman Fasilitas Umum'],
                'Pelaporan Warga' => ['Pelaporan Warga'],
            ];

            return collect($serviceNames[$unitName] ?? [$unitName])
                ->intersect($activeServices ?? [])->isNotEmpty();
        };

        $hasBelanja = $isServiceActive('Belanja dan Kebutuhan');
        $hasLayanan = collect(['Unit Penyewaan Alat', 'Unit Penyewaan Transportasi', 'Unit Peminjaman Fasilitas Umum', 'Pelaporan Warga'])
            ->contains(fn ($unit) => $isServiceActive($unit));
        $hasKabar = $isServiceActive('Kabar dan Informasi Daerah');
        $activeCount = (int) $hasBelanja + (int) $hasLayanan + (int) $hasKabar;
        $categoryPageUrl = function ($slug) use ($hasRegionContext, $userRegionId, $carouselFromRegion) {
            return route('service-category.show', array_filter([
                'category' => $slug,
                'region_id' => $hasRegionContext ? $userRegionId : null,
                'from' => $carouselFromRegion ? 'region' : null,
            ]));
        };
    @endphp

    @if($activeCount > 0)
    <div class="relative w-full flex justify-center items-center unit-stage-wrapper">
        <div class="relative w-full max-w-6xl mx-auto h-full">
            @if($hasBelanja)
            <a href="{{ $categoryPageUrl('belanja-kebutuhan') }}" aria-label="Buka kategori Belanja dan Kebutuhan" class="unit-card state-1 cursor-pointer no-underline hover:scale-105 transition-transform focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-300 rounded-2xl"
                 data-index="0"
                 data-name="Belanja dan Kebutuhan"
                 data-heading="&quot;Cari kebutuhan rumah atau produk lokal?&quot;"
                 data-body="Pesan gas atau jelajahi produk usaha daerah melalui Pasar Daerah."
                 data-badge="Belanja dan Kebutuhan"
                 data-icon="bx-shopping-bag"
                 data-box-bg="bg-amber-50/90"
                 data-box-border="border-amber-200"
                 data-badge-bg="bg-amber-100 text-amber-800 border-amber-300"
                 data-text-color="text-amber-950">
                <img src="{{ asset('Admin/img/menu3dberanda/belanja-kebutuhan.png') }}" onerror="this.onerror=null; this.src='{{ asset('Admin/img/pasardaerah/PasarDaerah.png') }}';" alt="Belanja dan Kebutuhan" loading="lazy">
            </a>
            @endif

            @if($hasLayanan)
            <a href="{{ $categoryPageUrl('layanan-daerah') }}" aria-label="Buka kategori Layanan Daerah" class="unit-card state-2 cursor-pointer no-underline hover:scale-105 transition-transform focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-300 rounded-2xl"
                 data-index="1"
                 data-name="Layanan Daerah"
                 data-heading="&quot;Butuh bantuan layanan dari daerah?&quot;"
                 data-body="Sewa perlengkapan atau transportasi, gunakan fasilitas umum, dan sampaikan laporan warga dari satu tempat."
                 data-badge="Layanan Daerah"
                 data-icon="bx-grid-alt"
                 data-box-bg="bg-blue-50/90"
                 data-box-border="border-blue-200"
                 data-badge-bg="bg-blue-100 text-blue-800 border-blue-300"
                 data-text-color="text-blue-950">
                <img src="{{ asset('Admin/img/menu3dberanda/layanan-daerah.webp') }}" onerror="this.onerror=null; this.src='{{ asset('User/img/elemen/fasilitas.png') }}';" alt="Layanan Daerah" loading="lazy">
            </a>
            @endif

            @if($hasKabar)
            <a href="{{ route('announcements.index', $hasRegionContext ? ['region_id' => $userRegionId] : []) }}" aria-label="Buka Kabar dan Informasi Daerah" class="unit-card state-0 cursor-pointer no-underline hover:scale-105 transition-transform focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-300 rounded-2xl"
                 data-index="2"
                 data-name="Kabar dan Informasi Daerah"
                 data-heading="&quot;Ingin tahu kabar terbaru di daerah?&quot;"
                 data-body="Baca berita, pengumuman, dan informasi resmi untuk warga."
                 data-badge="Kabar dan Informasi"
                 data-icon="bx-news"
                 data-box-bg="bg-sky-50/90"
                 data-box-border="border-sky-200"
                 data-badge-bg="bg-sky-100 text-sky-800 border-sky-300"
                 data-text-color="text-sky-950">
                <img src="{{ asset('User/img/elemen/KabardanInformasiDaerah.png') }}" onerror="this.onerror=null; this.src='{{ asset('Admin/img/kabardaerah/KabardanInformasiDaerah.png') }}';" alt="Kabar dan Informasi Daerah" loading="lazy">
            </a>
            @endif
        </div>
    </div>

    <div class="unit-nav-wrapper mt-4 sm:mt-8 mb-4 sm:mb-8 flex flex-col items-center justify-center gap-3 px-4 relative z-[70]">
        <div class="unit-nav-controls flex w-full max-w-3xl items-center justify-center gap-2 sm:gap-5">
            <button type="button" id="unit-prev" class="unit-nav-button" aria-label="Layanan sebelumnya">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <div class="unit-title-box text-center min-w-0 max-w-full flex-1">
                <h3 id="unit-title" class="text-base sm:text-xl md:text-2xl font-bold text-gray-900 transition-all duration-300 leading-snug">
                    Belanja dan Kebutuhan
                </h3>
            </div>
            <button type="button" id="unit-next" class="unit-nav-button" aria-label="Layanan berikutnya">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
    </div>
    @else
    <div class="w-full flex flex-col items-center justify-center text-center p-12 bg-white/60 backdrop-blur-md rounded-3xl border border-white/50 shadow-lg mt-4 max-w-4xl mx-auto">
        <div class="w-20 h-20 mb-6 rounded-full bg-blue-50/80 flex items-center justify-center border border-blue-100 shadow-inner">
            <svg class="w-10 h-10 text-[#115789]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-800 mb-3">Unit Pelayanan Belum Tersedia</h3>
        <p class="text-gray-500 max-w-lg text-lg leading-relaxed">Mohon maaf, desa atau kelurahan Anda belum mengaktifkan layanan SegiDaerah.</p>
    </div>
    @endif

@once
    @push('styles')
        @include('users.partials.unit-carousel-styles')
    @endpush
    @push('scripts')
        @include('users.partials.unit-carousel-scripts')
    @endpush
@endonce
