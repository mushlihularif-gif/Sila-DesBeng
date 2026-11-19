@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    {{-- Latar gelombang halaman layanan --}}
    @include('partials.abstract-bg')

    <section class="relative z-10 min-h-screen pt-40 pb-16">
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="text-center mb-12">
                <h1 class="text-3xl md:text-4xl font-bold text-[#115789]">Layanan di {{ $kecamatan->name }}</h1>
                <p class="text-gray-600 mt-3">Pilih jenis layanan yang Anda butuhkan. Katalog menampilkan layanan yang tersedia.</p>
                <a href="{{ route('kemitraan.create') }}#direktori-wilayah" class="inline-flex mt-4 text-[#115789] font-semibold underline">Lihat desa yang sudah dan belum bergabung</a>
            </div>

            <!-- Unit Pelayanan Section -->
            @include('users.partials.unit-carousel')

        </div>
    </section>
</main>
@endsection
