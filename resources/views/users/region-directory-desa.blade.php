@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    {{-- Latar gelombang halaman layanan --}}
    @include('partials.abstract-bg')

    <section class="relative z-10 min-h-screen pt-40 pb-16">
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="text-center mb-12">
                <h1 class="text-3xl md:text-4xl font-bold text-[#115789]">Layanan di {{ $kecamatan->name }}</h1>
                <p class="text-gray-600 mt-3">Pilih desa penyedia untuk melihat layanan dan lokasi pengelolanya.</p>
                <a href="{{ route('kemitraan.create') }}#direktori-wilayah" class="inline-flex mt-4 text-[#115789] font-semibold underline">Lihat desa yang sudah dan belum bergabung</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                @forelse($joinedVillages as $village)
                    <article class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <h2 class="text-lg font-bold text-gray-800">{{ $village->name }}</h2>
                        <p class="text-sm text-gray-600 mt-2">{{ $village->services->isNotEmpty() ? $village->services->pluck('name')->join(' · ') : 'Sudah bergabung. Layanan sedang disiapkan.' }}</p>
                        <a class="inline-flex mt-4 font-semibold text-[#115789]" href="{{ route('bumdes.detail', ['slug' => \Illuminate\Support\Str::slug($village->name), 'id' => $village->id]) }}">Lihat layanan desa</a>
                    </article>
                @empty
                    <p class="text-gray-600">Belum ada desa penyedia yang bergabung.</p>
                @endforelse
            </div>

            <!-- Unit Pelayanan Section -->
            @include('users.partials.unit-carousel')

        </div>
    </section>
</main>
@endsection
