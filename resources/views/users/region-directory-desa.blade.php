@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    {{-- Latar gelombang halaman layanan --}}
    @include('partials.abstract-bg')

    <section class="village-directory relative z-10 min-h-screen" data-village-directory>
        <div class="village-directory-content relative z-10">
            <div class="text-center mb-8">
                <h1 class="text-3xl md:text-4xl font-bold text-[#115789]">Layanan di {{ $kecamatan->name }}</h1>
                <p class="text-gray-600 mt-3">Pilih desa atau kelurahan yang sudah bergabung untuk melihat layanannya.</p>
            </div>

            @if($joinedVillages->isNotEmpty())
                <div class="village-search">
                    <label for="village-search" class="sr-only">Cari desa atau kelurahan yang sudah bergabung</label>
                    <input id="village-search" type="search" placeholder="Cari desa atau kelurahan" autocomplete="off" data-village-search aria-controls="village-list">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
                </div>
                <div id="village-list" class="village-list" data-village-list>
                @foreach($joinedVillages as $village)
                    <article class="village-row" data-village-row data-village-name="{{ $village->name }}" @if($loop->index >= 5) hidden @endif>
                        <h2>{{ $village->name }}</h2>
                        <a class="village-select" aria-label="Pilih {{ $village->name }}" href="{{ route('bumdes.detail', ['slug' => \Illuminate\Support\Str::slug($village->name), 'id' => $village->id]) }}">Pilih</a>
                    </article>
                @endforeach
                </div>
                <p class="village-empty" data-village-empty hidden>Tidak ada desa atau kelurahan yang cocok dengan pencarian Anda.</p>
                <p class="sr-only" role="status" aria-live="polite" data-village-status></p>
                <div class="village-toggle-wrap">
                    <button type="button" class="village-toggle" data-village-toggle aria-expanded="false" aria-controls="village-list" @if($joinedVillages->count() <= 5) hidden @endif>
                        <span data-village-toggle-label>Tampilkan lebih banyak</span>
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 7 5 5 5-5"/></svg>
                    </button>
                </div>
                <noscript><style>.village-row[hidden]{display:flex!important}.village-search,.village-toggle-wrap{display:none!important}</style></noscript>
            @else
                <p class="village-empty">Belum ada desa atau kelurahan yang bergabung.</p>
            @endif
        </div>
    </section>
</main>
@endsection

@push('styles')
<style>
    .village-directory{padding:10rem 1.5rem 4rem}
    .village-directory-content{max-width:1000px;margin:0 auto}
    .village-search{position:relative;max-width:660px;margin:0 auto 2.5rem;border:2px solid transparent;border-radius:16px;background:linear-gradient(white,white) padding-box,linear-gradient(110deg,#00aaff,#ffb800) border-box;box-shadow:0 2px 4px #00000012}
    .village-search input{width:100%;min-height:48px;padding:.7rem 3rem .7rem 1rem;border:0;border-radius:14px;background:transparent;text-align:center;color:#1f2937;font:inherit}
    .village-search svg{position:absolute;right:1rem;top:50%;transform:translateY(-50%);width:23px;height:23px;pointer-events:none;color:#115789}
    .village-search:focus-within{outline:3px solid #00aaff40;outline-offset:3px}
    .village-list{overflow:hidden;display:flex;flex-direction:column;gap:24px;padding:4px 3px 6px}
    .village-row{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:24px 32px;border:1px solid #ffffffb3;border-radius:18px;background:#ffffffd9;box-shadow:0 3px 4px #00000024}
    .village-row[hidden],.village-toggle[hidden],.village-empty[hidden]{display:none}
    .village-row h2{min-width:0;margin:0;font-size:20px;line-height:1.45;font-weight:700;color:#1f2937;overflow-wrap:anywhere}
    .village-select{flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;min-width:104px;min-height:44px;padding:8px 20px;border:1px solid #00aaff;border-radius:12px;color:#115789;background:#fff;font-weight:600;transition:background .2s,box-shadow .2s}
    .village-select:hover{background:#eff8ff;box-shadow:0 2px 8px #00aaff20}
    .village-select:focus-visible,.village-toggle:focus-visible{outline:3px solid #00aaff;outline-offset:3px}
    .village-toggle-wrap{text-align:center;margin-top:24px}
    .village-toggle{display:inline-flex;align-items:center;justify-content:center;gap:10px;min-height:44px;padding:8px 16px;background:transparent;border:0;color:#115789;font:inherit;cursor:pointer}
    .village-toggle svg{width:22px;height:22px;color:#00aaff;transition:transform .28s}
    .village-toggle[aria-expanded=true] svg{transform:rotate(180deg)}
    .village-empty{padding:24px;text-align:center;color:#4b5563;background:#ffffffd9;border-radius:18px}
    @media(max-width:640px){.village-directory{padding:8rem 16px 3rem}.village-search{margin-bottom:24px}.village-list{gap:16px}.village-row{padding:18px 16px;gap:12px}.village-row h2{font-size:16px}.village-select{min-width:72px;padding:8px 12px}.village-directory h1{font-size:26px}}
    @media(prefers-reduced-motion:reduce){.village-select,.village-toggle svg{transition:none}}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/village-directory.js') }}?v={{ filemtime(public_path('js/village-directory.js')) }}" defer></script>
@endpush
