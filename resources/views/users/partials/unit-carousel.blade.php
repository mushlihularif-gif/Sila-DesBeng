@php
    $isMenuServiceActive = function ($name) use ($activeServices, $region) {
        if (!$region || in_array($name, ['Pasar Daerah', 'Pengumuman dan Event'], true)) return true;

        $map = [
            'Unit Penyewaan Alat' => ['Penyewaan Alat'],
            'Unit Penjualan Gas' => ['Penjualan Gas'],
            'Unit Penyewaan Mobil' => ['Penyewaan Mobil', 'Penyewaan Transportasi'],
            'Unit Peminjaman Fasilitas Umum' => ['Peminjaman Fasilitas Umum', 'Fasilitas Umum'],
            'Pelaporan Warga' => ['Pelaporan Warga'],
        ];

        return collect($map[$name] ?? [$name])->intersect($activeServices ?? [])->isNotEmpty();
    };
    $menuUrl = fn ($routeName) => route($routeName) . ($region ? '?region_id=' . $region->id : '');

    $belanjaLinks = [];
    if ($isMenuServiceActive('Unit Penjualan Gas')) $belanjaLinks[] = ['label' => 'Gas Daerah', 'route' => 'gas.sales'];
    $belanjaLinks[] = ['label' => 'Pasar Daerah', 'route' => 'pasar.index'];

    $layananLinks = [];
    foreach ([
        ['Unit Penyewaan Alat', 'Penyewaan Alat', 'rental.equipment'],
        ['Unit Penyewaan Mobil', 'Penyewaan Transportasi', 'mobil.rental.equipment'],
        ['Unit Peminjaman Fasilitas Umum', 'Fasilitas Umum', 'user.fasilitas-umum.equipment'],
        ['Pelaporan Warga', 'Pelaporan Warga', 'pelaporan.landing'],
    ] as [$service, $label, $routeName]) {
        if ($isMenuServiceActive($service)) $layananLinks[] = ['label' => $label, 'route' => $routeName];
    }

    $categoryMenus = [
        [
            'title' => 'Belanja & Kebutuhan',
            'description' => 'Gas daerah dan produk lokal untuk kebutuhan sehari-hari.',
            'image' => 'Admin/img/menu3dberanda/belanja-kebutuhan.png',
            'fallback_image' => 'Admin/img/pasardaerah/PasarDaerah.png',
            'links' => $belanjaLinks,
        ],
        [
            'title' => 'Layanan Daerah',
            'description' => 'Akses layanan dan fasilitas yang tersedia di wilayah ini.',
            'image' => 'Admin/img/menu3dberanda/layanan-daerah.webp',
            'fallback_image' => 'User/img/elemen/fasilitas.png',
            'links' => $layananLinks,
        ],
        [
            'title' => 'Kabar dan Informasi Daerah',
            'description' => 'Berita, pengumuman, dan informasi resmi untuk warga.',
            'image' => 'User/img/elemen/KabardanInformasiDaerah.png',
            'fallback_image' => 'Admin/img/kabardaerah/KabardanInformasiDaerah.png',
            'links' => [['label' => 'Buka Kabar Daerah', 'route' => 'announcements.index']],
        ],
    ];
@endphp

<section id="unit-carousel-container" class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-16 relative z-10">
    <header class="text-center mb-8 sm:mb-10">
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900">
            Jelajahi <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Daerah</span>
        </h2>
        <p class="mt-2 text-sm sm:text-base text-gray-600">Pilih kebutuhan atau layanan yang ingin Anda akses.</p>
    </header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
        @foreach($categoryMenus as $menu)
            @if(count($menu['links']))
            <article class="group flex flex-col rounded-3xl border border-white/80 bg-white/75 p-5 sm:p-6 text-center shadow-md backdrop-blur-md transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div class="mx-auto flex h-36 w-full items-center justify-center sm:h-40">
                    <img src="{{ asset($menu['image']) }}" onerror="this.onerror=null; this.src='{{ asset($menu['fallback_image'] ?? 'User/img/elemen/F3.png') }}';" alt="{{ $menu['title'] }}" loading="lazy" class="h-full w-full object-contain drop-shadow-lg transition-transform duration-300 group-hover:scale-105">
                </div>
                <h3 class="mt-4 text-lg sm:text-xl font-extrabold text-gray-900">{{ $menu['title'] }}</h3>
                <p class="mt-2 min-h-10 text-sm leading-relaxed text-gray-600">{{ $menu['description'] }}</p>
                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    @foreach($menu['links'] as $link)
                    <a href="{{ $menuUrl($link['route']) }}" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-[#115789] px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[#0d4267] focus:outline-none focus:ring-2 focus:ring-[#60a5fa] focus:ring-offset-2">
                        {{ $link['label'] }}
                    </a>
                    @endforeach
                </div>
            </article>
            @endif
        @endforeach
    </div>
</section>
