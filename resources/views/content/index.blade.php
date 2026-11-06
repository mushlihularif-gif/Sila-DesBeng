@extends('layouts.user')
@section('title', 'Kelola Kabar dan Informasi Daerah')
@section('page')
@php($canCreate = $manager || auth()->user()->verification_status === 'verified')
@php($states = ['draft'=>'Draf — belum dikirim','pending'=>'Menunggu admin','review'=>'Sedang diperiksa','published'=>'Terbit','scheduled'=>'Dijadwalkan','merged'=>'Digabungkan','rejected'=>'Tidak diterbitkan','cancelled'=>'Dibatalkan','archived'=>'Arsip','expired'=>'Sudah dibersihkan'])
<main class="max-w-6xl mx-auto px-4 pt-36 pb-16 w-full" data-content-list>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div><a href="{{ route('announcements.index') }}" class="text-blue-600">← Kabar dan Informasi Daerah</a><h1 class="text-3xl font-bold mt-3">{{ $manager ? 'Ruang Kelola Kabar' : 'Kiriman Saya' }}</h1>
        <p class="text-gray-600 mt-2">{{ $manager ? 'Pilih kiriman untuk dibaca. Tekan Periksa dan Edit jika ingin merapikan, menggabungkan, atau menerbitkannya.' : 'Bagikan kegiatan di lingkungan Anda. Admin wilayah membantu merapikan dan meninjaunya sebelum terbit.' }}</p></div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('notification-settings.index') }}" class="px-4 py-3 rounded-xl border border-slate-200 bg-white">Pengaturan Notifikasi</a>
            @if($canCreate)<form action="{{ route('content.store') }}" method="post">@csrf<input type="hidden" name="category" value="Berita"><button class="px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold">{{ $manager ? 'Tulis Kabar Baru' : 'Bagikan Kegiatan' }}</button></form>@endif
            @if($manager)<form action="{{ route('content.store') }}" method="post">@csrf<input type="hidden" name="category" value="Pengumuman"><button class="px-5 py-3 rounded-xl bg-white border border-blue-600 text-blue-700 font-semibold">Buat Pengumuman</button></form>@endif
        </div>
    </div>
    @if(!$canCreate)<p class="p-4 rounded-xl bg-amber-50 mb-4">Verifikasi akun dan wilayah sebelum mengirim kabar. <a href="{{ route('user.verifikasi.index') }}" class="text-blue-700 underline font-semibold">Buka Verifikasi Akun</a></p>@endif
    <div class="rounded-xl bg-blue-50 text-blue-900 p-4 mb-5 text-sm">{{ $manager ? 'Membaca saja tidak mengunci kiriman. Jika admin lain sedang mengedit, namanya muncul dan Anda dapat memilih kiriman lain. Status diperbarui otomatis.' : 'Mulai dari Bagikan Kegiatan → isi foto dan cerita → Kirim ke Admin. Anda akan mendapat notifikasi hasilnya; menyimpan draf saja belum mengirim kiriman.' }}</div>
    <form method="get" class="flex gap-3 flex-wrap mb-5">
        <input name="search" value="{{ request('search') }}" placeholder="Cari judul kabar" aria-label="Cari judul" class="border border-slate-200 rounded-xl px-4 py-2 flex-1 min-w-0">
        <select name="state" aria-label="Status kiriman" class="border border-slate-200 rounded-xl px-3 py-2"><option value="">Semua status</option>@foreach($states as $value=>$label)<option value="{{ $value }}" @selected(request('state') === $value)>{{ $label }}</option>@endforeach</select>
        <button class="bg-blue-600 text-white rounded-xl px-4 py-2">Cari</button>
    </form>
    <p id="list-sync" class="text-sm text-gray-500 mb-3" role="status">Status diperbarui otomatis.</p>
    <div class="space-y-3" id="content-rows">
    @forelse($items as $item)
        <a href="{{ route('content.show', $item) }}" data-item-id="{{ $item->id }}" class="block rounded-2xl border border-slate-200 bg-white p-5 hover:border-blue-500 shadow-sm">
            <div class="flex justify-between gap-3"><h2 class="font-bold text-lg" data-title>{{ $item->draft_data['title'] ?? $item->title ?: 'Draf tanpa judul' }}</h2><span class="text-sm text-blue-700" data-state>{{ $states[$item->workflow_state] ?? $item->workflow_state }}</span></div>
            <p class="text-gray-500 text-sm mt-2">{{ $item->post_category }} · {{ $item->admin->name ?? 'Warga' }}</p><p data-editor class="text-sm text-amber-700 mt-1"></p>
            <span class="inline-block text-sm text-blue-700 font-semibold mt-3">{{ $manager ? 'Buka dan Periksa' : 'Lihat Kiriman' }} →</span>
        </a>
    @empty
        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-gray-600">Belum ada kiriman dalam daftar ini.</div>
    @endforelse
    </div>
    <div class="mt-6">{{ $items->links() }}</div>
</main>
<script type="module" src="{{ asset('js/content-workspace.js') }}?v={{ filemtime(public_path('js/content-workspace.js')) }}"></script>
@endsection
