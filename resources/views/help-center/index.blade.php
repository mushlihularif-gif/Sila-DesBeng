@extends('layouts.user')
@section('title', 'Pusat Bantuan - SegiDaerah')
@section('page')
@include('partials.abstract-bg')
<section class="relative z-10 max-w-4xl mx-auto px-4 pt-32 pb-16">
    <x-home-link />
    <h1 class="text-3xl font-bold text-[#115789] mt-6 mb-2">Pusat Bantuan</h1>
    <p class="text-gray-600 mb-6">Panduan menggunakan akun dan layanan SegiDaerah.</p>
    @foreach($faqs as $section)
        <section class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5 mb-5">
            <h2 class="text-xl font-bold text-[#115789] mb-3">{{ $section['category'] }}</h2>
            @foreach($section['items'] as $faq)
                <details class="border-t border-gray-100 py-3">
                    <summary class="font-semibold cursor-pointer">{{ $faq['question'] }}</summary>
                    <p class="text-gray-600 mt-2 leading-relaxed">{{ $faq['answer'] }}</p>
                </details>
            @endforeach
        </section>
    @endforeach
    <section id="hubungi" class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5 scroll-mt-24">
        <h2 class="text-xl font-bold text-[#115789] mb-2">Hubungi Bantuan SegiDaerah</h2>
        <p class="text-gray-600 mb-4">Untuk jadwal, pembayaran, atau pengantaran pesanan, hubungi Chat Pengelola pada halaman layanan. Untuk kendala akun dan aplikasi, gunakan kontak berikut.</p>
        <div class="grid sm:grid-cols-2 gap-3">
            <a href="https://wa.me/6282249213061" target="_blank" rel="noopener noreferrer" class="segi-outline text-center">WhatsApp SegiDaerah</a>
            <a href="mailto:segidaerah@inovasia.site" class="segi-outline text-center">Email SegiDaerah</a>
        </div>
    </section>
</section>
@endsection
