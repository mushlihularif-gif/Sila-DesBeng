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
                <form class="village-search" role="search" data-village-search-form>
                    <label for="village-search" class="sr-only">Cari desa atau kelurahan yang sudah bergabung</label>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input id="village-search" type="search" placeholder="Cari desa atau kelurahan..." autocomplete="off" data-village-search aria-controls="village-list">
                    <button type="submit" class="village-search-submit">Cari</button>
                </form>
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
    .village-search{display:flex;align-items:center;gap:8px;max-width:672px;margin:0 auto 2.5rem;padding:6px;border:2px solid transparent;border-radius:9999px;background:linear-gradient(white,white) padding-box,linear-gradient(to right,#2563eb,#38bdf8,#fbbf24) border-box;box-shadow:0 2px 4px #00000012}
    .village-search input{flex:1;min-width:0;width:100%;padding:12px 8px;border:0;outline:none;background:transparent;color:#1f2937;font:inherit;font-weight:500}
    .village-search input::placeholder{color:#9ca3af}
    .village-search svg{flex-shrink:0;margin-left:14px;width:24px;height:24px;pointer-events:none;color:#115789}
    .village-search:focus-within{outline:3px solid #2563eb30;outline-offset:3px}
    .village-search-submit{flex-shrink:0;min-height:44px;padding:12px 32px;border:0;border-radius:9999px;background:#2563eb;color:#fff;font:inherit;font-size:14px;font-weight:700;box-shadow:0 2px 4px #00000018;cursor:pointer;transition:background .2s,box-shadow .2s}
    .village-search-submit:hover{background:#1d4ed8;box-shadow:0 3px 6px #00000020}
    .village-search-submit:focus-visible{outline:3px solid #115789;outline-offset:3px}
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
    @media(max-width:640px){.village-directory{padding:8rem 16px 3rem}.village-search{margin-bottom:24px;padding:3px;gap:4px}.village-search svg{margin-left:9px;width:18px;height:18px}.village-search input{padding:10px 4px;font-size:12px}.village-search-submit{padding:10px 16px;font-size:12px}.village-list{gap:16px}.village-row{padding:18px 16px;gap:12px}.village-row h2{font-size:16px}.village-select{min-width:72px;padding:8px 12px}.village-directory h1{font-size:26px}}
    @media(prefers-reduced-motion:reduce){.village-select,.village-toggle svg,.village-search-submit{transition:none}}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/village-directory.js') }}?v={{ filemtime(public_path('js/village-directory.js')) }}" defer></script>
@endpush
