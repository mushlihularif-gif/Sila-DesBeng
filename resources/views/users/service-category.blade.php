@extends('layouts.user')

@section('title', $category['title'] . ' - SiladesBeng')

@section('page')
<main class="relative flex-grow w-full">
    @include('partials.abstract-bg')

    <section class="relative z-10 min-h-screen px-4 pb-20 pt-28 sm:px-6 sm:pt-36">
        <div class="mx-auto max-w-6xl">
            <a href="{{ route('beranda') }}" class="inline-flex items-center gap-2 rounded-full border border-white/80 bg-white/80 px-4 py-2 text-sm font-semibold text-[#115789] shadow-sm backdrop-blur transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-[#60a5fa]">
                Kembali ke Beranda
            </a>

            <header class="mx-auto mb-10 mt-8 max-w-3xl text-center sm:mb-12">
                <div class="mx-auto mb-5 flex h-32 w-32 items-center justify-center sm:h-40 sm:w-40">
                    <img src="{{ asset($category['image']) }}" alt="" class="h-full w-full object-contain drop-shadow-xl">
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">{{ $category['title'] }}</span>
                </h1>
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-gray-600 sm:text-base">{{ $category['description'] }}</p>
                @if($region)
                    <span class="mt-4 inline-flex items-center gap-1.5 rounded-full border border-blue-100 bg-white/80 px-3 py-1.5 text-xs font-semibold text-[#115789] shadow-sm">
                        <i class="bx bx-map-pin" aria-hidden="true"></i>
                        {{ $region->name }}
                    </span>
                @endif
            </header>

            @if($items->isNotEmpty())
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($items as $item)
                        <a href="{{ $item['url'] }}" class="group flex min-h-64 flex-col items-center rounded-3xl border border-white/80 bg-white/80 p-6 text-center shadow-md backdrop-blur-md transition duration-300 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-[#60a5fa] focus:ring-offset-2 focus:ring-offset-transparent">
                            <div class="flex h-28 w-full items-center justify-center">
                                <img src="{{ asset($item['image']) }}" alt="" loading="lazy" class="h-full max-w-full object-contain drop-shadow-md transition-transform duration-300 group-hover:scale-105">
                            </div>
                            <h2 class="mt-4 text-lg font-extrabold text-gray-900 group-hover:text-[#115789] sm:text-xl">{{ $item['title'] }}</h2>
                            <p class="mt-2 flex-grow text-sm leading-relaxed text-gray-600">{{ $item['description'] }}</p>
                            <span class="mt-5 inline-flex items-center justify-center rounded-xl bg-[#2563eb] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition group-hover:bg-[#1d4ed8]">
                                Buka {{ $item['title'] }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="mx-auto max-w-xl rounded-3xl border border-white/80 bg-white/80 px-6 py-12 text-center shadow-sm backdrop-blur">
                    <i class="bx bx-info-circle text-4xl text-[#115789]" aria-hidden="true"></i>
                    <h2 class="mt-3 text-lg font-bold text-gray-900">Belum ada layanan aktif</h2>
                    <p class="mt-2 text-sm text-gray-600">Belum ada layanan dalam kategori ini yang diaktifkan untuk wilayah tersebut.</p>
                </div>
            @endif
        </div>
    </section>
</main>
@endsection
