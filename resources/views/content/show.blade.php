@extends('layouts.user')
@section('title', 'Tulis dan Kelola Kabar Daerah')
@push('styles')
<style>
#content-workspace [hidden]{display:none!important}
#content-workspace button:disabled{opacity:.55;cursor:not-allowed}
#content-workspace dialog{margin:auto;max-height:90dvh;overflow-y:auto;width:calc(100% - 2rem)}
#content-workspace dialog::backdrop{background:rgb(15 23 42 / .5)}
#content-workspace summary{cursor:pointer}
</style>
@endpush
@section('page')
@php($isManager = app(\App\Services\ContentAccess::class)->manager(auth()->user()))
<main class="max-w-7xl mx-auto w-full px-4 pt-36 pb-16" id="content-workspace" data-manager="{{ $isManager ? '1' : '0' }}" data-base="{{ url('/kelola-kabar/'.$item->id) }}" data-ai="{{ route('content.ai') }}" data-csrf="{{ csrf_token() }}">
    <a href="{{ route('content.index') }}" class="text-blue-600">← Kembali ke daftar kiriman</a>
    <div class="flex flex-wrap gap-4 justify-between items-start mt-4 mb-5">
        <div>
            <p class="text-blue-600 font-semibold">{{ $item->post_category }}</p>
            <h1 class="font-bold text-3xl">{{ $isManager ? 'Periksa dan Kelola Kiriman' : 'Ceritakan Kegiatan di Daerah Anda' }}</h1>
            <p class="text-gray-600 mt-2">Pengirim: {{ $item->admin->name ?? 'Warga' }} · {{ $item->region->name ?? '' }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button id="claim" type="button" class="bg-blue-600 text-white rounded-xl px-5 py-3 font-semibold">{{ $isManager ? 'Periksa dan Edit' : 'Mulai Menulis' }}</button>
            <button id="release" type="button" hidden class="border border-slate-200 rounded-xl px-4 py-3">Simpan dan Selesai Mengedit</button>
        </div>
    </div>
    <ol aria-label="Langkah mengelola kiriman" class="grid sm:grid-cols-3 gap-3 text-sm mb-5">
        <li class="rounded-xl bg-slate-50 p-3"><strong>1. {{ $isManager ? 'Periksa kiriman' : 'Isi kegiatan' }}</strong><p class="text-slate-600 mt-1">{{ $isManager ? 'Baca cerita, waktu, lokasi, dan fotonya.' : 'Tulis tempat dan waktu kegiatan, lalu tambahkan foto.' }}</p></li>
        <li class="rounded-xl bg-slate-50 p-3"><strong>2. {{ $isManager ? 'Lengkapi ceritanya' : 'Tulis atau minta bantuan AI' }}</strong><p class="text-slate-600 mt-1">{{ $isManager ? 'Rapikan sendiri, gunakan AI, atau gabungkan kiriman kegiatan yang sama.' : 'Cukup ceritakan yang terjadi. AI dapat membantu menyusun tulisan.' }}</p></li>
        <li class="rounded-xl bg-slate-50 p-3"><strong>3. {{ $isManager ? 'Periksa lalu terbitkan' : 'Kirim ke admin' }}</strong><p class="text-slate-600 mt-1">{{ $isManager ? 'Lihat hasil akhir dan penerimanya sebelum diterbitkan.' : 'Admin wilayah meninjau kiriman. Hasilnya masuk ke notifikasi Anda.' }}</p></li>
    </ol>
    <div id="sync-status" role="status" aria-live="polite" class="bg-blue-50 border border-blue-100 text-blue-900 rounded-xl p-4 mb-3">Memeriksa siapa yang sedang mengedit…</div>
    <p id="presence" class="text-sm text-gray-500 mb-4"></p>
    <div id="error" role="alert" class="bg-red-50 text-red-800 rounded-xl p-4 mb-4" hidden></div>
    <div id="recovery" hidden class="rounded-xl border border-amber-300 bg-amber-50 p-4 mb-4">
        <p>Tulisan Anda masih ada di halaman ini, tetapi belum tersimpan. Unduh salinannya sebelum membuka tulisan terbaru.</p>
        <div class="flex gap-3 mt-3 flex-wrap"><button id="download-draft" class="border border-slate-200 rounded-xl px-3 py-2">Unduh Salinan Tulisan Saya</button><button id="load-latest" class="border border-slate-200 rounded-xl px-3 py-2">Ganti dengan Tulisan Terbaru</button></div>
    </div>
    <div id="updated" role="status" aria-live="polite" class="text-sm text-blue-700 mb-3"></div>
    <div class="grid lg:grid-cols-3 gap-6 items-start">
        <section class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm p-5 sm:p-7">
            @if($item->contribution_to)<p class="mb-5 rounded-xl bg-blue-50 text-blue-900 p-4">Anda menambahkan dokumentasi untuk kabar yang sudah ada. Pilih foto dan tulis keterangan singkat; admin akan meninjaunya.</p>@endif
            <form id="editor-form">
                <fieldset id="editor-fields" disabled class="space-y-5 min-w-0">
                    <legend class="font-bold text-lg mb-4">Tentang kegiatan</legend>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div><label for="location" class="block font-semibold mb-2">Lokasi kegiatan</label><input id="location" name="location" maxlength="255" class="w-full border border-slate-200 rounded-xl p-3" placeholder="Contoh: Lapangan RT 01, Desa Pematang Duku Timur"></div>
                        <div><label for="event_date" class="block font-semibold mb-2">Waktu kegiatan</label><input id="event_date" name="event_date" type="datetime-local" class="w-full border border-slate-200 rounded-xl p-3" aria-describedby="event-help"><p id="event-help" class="text-sm text-gray-500 mt-2">Isi waktu kejadian. Waktu terbit dicatat otomatis.</p></div>
                    </div>
                    <div><label for="type" class="block font-semibold mb-2">Jenis kegiatan</label><select id="type" name="type" class="w-full border border-slate-200 rounded-xl p-3"><option value="Event">Kegiatan / Acara</option><option value="Pengumuman">Pengumuman</option><option value="Gotong Royong">Gotong Royong</option></select></div>
                    @if($item->post_category === 'Pengumuman')
                    <div><label for="target_audience_id" class="block font-semibold mb-2">Siapa yang menerima pengumuman?</label><select id="target_audience_id" name="target_audience_id" class="w-full border border-slate-200 rounded-xl p-3">@foreach($targets as $target)<option value="{{ $target->id }}">{{ $target->name }} ({{ strtoupper($target->type) }})</option>@endforeach</select><p class="text-sm text-gray-500 mt-2">Jika RT memilih RW induknya, kiriman perlu disetujui pengelola RW/desa terlebih dahulu.</p></div>
                    @endif
                    <div>
                        <label for="photos" class="block font-semibold mb-2">Foto kegiatan</label><input id="photos" type="file" accept="image/jpeg,image/png,image/webp" multiple class="w-full" aria-describedby="photo-help photo-status">
                        <p id="photo-help" class="text-sm text-gray-500 mt-2">Pilih foto, lalu tekan Tambahkan Foto. Maksimal 8 foto sekaligus, 5 MB per foto.</p>
                        <p id="photo-status" role="status" class="text-sm text-blue-700 mt-2"></p>
                        <button id="upload" type="button" class="border border-slate-200 rounded-xl px-4 py-2 mt-2">Tambahkan Foto</button>
                        <div id="gallery" class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4"></div>
                    </div>
                    <div class="border-t border-slate-200 pt-5">
                        <label for="write-mode" class="font-bold text-lg block mb-2">Cara menulis</label>
                        <select id="write-mode" class="border border-slate-200 rounded-xl p-3 w-full" aria-describedby="write-help"><option value="manual">Saya tulis sendiri</option><option value="assisted">Bantu tuliskan dengan AI</option></select>
                        <p id="write-help" class="text-sm text-gray-500 mt-2">Tuliskan ceritanya dengan bahasa Anda. Admin dapat membantu merapikannya sebelum terbit.</p>
                    </div>
                    <div id="facts-panel" hidden class="bg-blue-50 rounded-xl p-4">
                        <label for="facts" class="block font-semibold mb-2">Ceritakan poin pentingnya</label><textarea id="facts" rows="5" maxlength="8000" class="w-full bg-white border border-slate-200 rounded-xl p-3" placeholder="Contoh: Lomba 17 Agustus. Diikuti warga RT 01. Ada balap karung dan tarik tambang. Setelah lomba, panitia membagikan hadiah."></textarea>
                        <p class="text-sm text-slate-600 mt-2">Isi sesuai kejadian. Anda akan melihat dan memilih hasil AI sebelum menggunakannya.</p>
                        <button id="compose" type="button" class="mt-3 bg-blue-600 text-white rounded-xl px-4 py-2">Buat Judul dan Cerita dengan AI</button>
                    </div>
                    <div><label for="title" class="block font-semibold mb-2">Judul</label><input id="title" name="title" maxlength="255" class="w-full border border-slate-200 rounded-xl p-3" placeholder="Contoh: Warga RT 01 Meriahkan Lomba 17 Agustus"></div>
                    <div><label for="description" class="block font-semibold mb-2">Isi cerita / pengumuman</label><textarea id="description" name="description" rows="8" maxlength="20000" class="w-full border border-slate-200 rounded-xl p-3" placeholder="Apa kegiatannya? Siapa yang ikut? Apa saja yang dilakukan dan bagaimana hasilnya?"></textarea><button id="correct" type="button" class="mt-2 px-4 py-2 bg-blue-50 text-blue-800 rounded-xl">Periksa Ejaan dengan AI</button></div>
                    @if($isManager)
                    <details class="border border-slate-200 rounded-xl p-4"><summary class="font-semibold">Atur waktu terbit dan masa berlaku (opsional)</summary><p class="text-sm text-gray-500 mt-2">Biarkan kosong jika ingin langsung terbit setelah Anda menyetujuinya.</p><div class="grid sm:grid-cols-2 gap-4 mt-4"><div><label for="scheduled_at" class="block font-semibold mb-2">Jadwalkan terbit</label><input id="scheduled_at" name="scheduled_at" type="datetime-local" class="w-full border border-slate-200 rounded-xl p-3"></div><div><label for="ends_at" class="block font-semibold mb-2">Berlaku sampai</label><input id="ends_at" name="ends_at" type="datetime-local" class="w-full border border-slate-200 rounded-xl p-3"></div></div></details>
                    @endif
                </fieldset>
                <p id="saved" class="text-sm text-gray-500 mt-5" role="status">Saat Anda mengedit, tulisan disimpan otomatis sebagai draf. Draf belum terbit.</p>
                <div class="flex flex-wrap gap-3 mt-4"><button id="save" type="submit" disabled class="px-5 py-3 border border-slate-200 rounded-xl">Simpan Draf</button><button id="submit" type="button" hidden class="px-5 py-3 bg-blue-600 text-white rounded-xl font-semibold">Kirim ke Admin</button><button id="publish" type="button" hidden class="px-5 py-3 bg-blue-600 text-white rounded-xl font-semibold">Periksa Hasil dan Terbitkan</button></div>
                <p id="send-help" class="text-sm text-gray-500 mt-3">{{ $isManager ? 'Anda akan melihat hasil akhir sebelum menyetujui penerbitan.' : 'Kiriman masuk ke admin RT, RW, dan desa sesuai wilayah akun Anda. Kiriman baru tampil untuk umum setelah disetujui.' }}</p>
            </form>
            <div id="ai-unavailable" hidden class="mt-5 p-4 border border-slate-200 rounded-xl"><p>AI belum dapat membantu saat ini. Anda tetap bisa melanjutkan setelah memeriksa tulisan sendiri.</p><button id="continue-manual" type="button" class="mt-3 border border-slate-200 rounded-xl px-4 py-2">Sudah Saya Periksa, Lanjutkan</button></div>
            <section id="ai-preview" hidden class="mt-6 border border-slate-200 rounded-xl p-4 bg-slate-50" aria-labelledby="ai-heading"><h2 id="ai-heading" class="text-lg font-bold mb-2">Pilih tulisan yang ingin dipakai</h2><p class="text-sm text-gray-600 mb-4">Periksa nama, tanggal, dan fakta. Memilih tulisan di sini belum menerbitkan kabar.</p><div class="grid md:grid-cols-2 gap-4"><div><h3 class="font-semibold">Tulisan Anda</h3><p id="original-title" class="font-semibold mt-2"></p><p id="original-body" class="whitespace-pre-wrap mt-2"></p></div><div><h3 class="font-semibold">Usulan AI</h3><p id="suggested-title" class="font-semibold mt-2"></p><p id="suggested-body" class="whitespace-pre-wrap mt-2"></p><ul id="ai-questions" class="mt-3 list-disc pl-5"></ul></div></div><div class="flex gap-3 flex-wrap mt-4"><button id="use-ai" type="button" class="bg-blue-600 text-white px-4 py-2 rounded-xl">Pakai Hasil AI</button><button id="keep-original" type="button" class="border border-slate-200 px-4 py-2 rounded-xl">Tetap Pakai Tulisan Saya</button></div></section>
        </section>
        <aside class="space-y-5">
            <section class="bg-white border border-slate-200 rounded-2xl p-5"><h2 class="font-bold text-lg">Status Kiriman</h2><p id="workflow-state" class="mt-3 font-semibold text-blue-700"></p><p id="next-step" class="mt-2 text-sm text-slate-600"></p><p id="published-at" class="text-sm text-gray-500 mt-2"></p><p id="reason" class="mt-2 text-red-700"></p><a id="public-link" hidden class="inline-block text-blue-700 underline mt-3">Lihat Berita yang Terbit</a></section>
            @if($isManager)
                @if($item->post_category === 'Berita')
                <section id="source-panel" class="bg-white border border-slate-200 rounded-2xl p-5"><h2 class="font-bold text-lg">Gabungkan Kiriman Warga</h2><p class="text-sm text-gray-600 mt-2">Kegiatan yang sama dikirim beberapa warga? Pilih foto dan cerita mereka untuk dijadikan satu berita.</p><button id="load-sources" type="button" class="border border-blue-200 text-blue-700 rounded-xl px-4 py-3 mt-3">Pilih Kiriman Warga</button><p class="text-sm text-gray-500 mt-2">Opsional. Nama penyumbang foto dan cerita dicatat otomatis.</p><div id="selected-sources" class="space-y-4 mt-4"></div></section>
                @endif
                <details class="bg-white border border-slate-200 rounded-2xl p-5"><summary class="font-bold">Tidak diterbitkan / batalkan kegiatan</summary><p class="text-sm text-gray-600 mt-3">Cerita kurang rapi bisa Anda perbaiki langsung. Gunakan keputusan ini jika kiriman memang tidak dapat diterbitkan atau kegiatannya dibatalkan.</p><label for="reason-choice" class="text-sm block mt-3">Pilih alasan</label><select id="reason-choice" class="w-full border border-slate-200 rounded-xl p-2 mt-1"><option value="">Pilih alasan</option><option>Informasi tidak dapat dikonfirmasi</option><option>Sudah dimuat dalam kabar lain</option><option>Tidak sesuai kegiatan wilayah</option><option>Mengandung data pribadi yang tidak boleh disebarkan</option></select><label for="reason-text" class="text-sm block mt-3">Penjelasan tambahan / alasan sendiri</label><textarea id="reason-text" rows="3" maxlength="1000" class="w-full border border-slate-200 rounded-xl p-2 mt-1" placeholder="Jelaskan alasannya dengan singkat dan sopan. Warga akan menerima penjelasan ini."></textarea><button id="reject" type="button" disabled class="border border-red-300 text-red-700 rounded-xl p-3 mt-3">Tidak Diterbitkan</button><button id="cancel" type="button" hidden class="border border-red-300 text-red-700 rounded-xl p-3 mt-3">Batalkan Kegiatan</button></details>
                <details class="bg-white border border-slate-200 rounded-2xl p-5"><summary class="font-bold">Riwayat Penanganan</summary><p class="text-sm text-gray-500 mt-3">Daftar admin yang membuka, mengedit, dan mengambil keputusan. Membuka halaman belum berarti selesai memeriksa.</p><ol id="activity" class="space-y-3 mt-4 text-sm"></ol></details>
            @endif
        </aside>
    </div>
    <dialog id="publish-review" aria-labelledby="review-heading" class="rounded-2xl p-6 max-w-xl shadow-xl"><h2 id="review-heading" class="text-xl font-bold">Periksa Sebelum Terbit</h2><p id="review-error" role="alert" hidden class="text-red-700 mt-3"></p><p id="review-target" class="mt-3 text-blue-700"></p><p id="review-meta" class="text-sm text-slate-600 mt-3"></p><div id="review-photos" class="grid grid-cols-3 gap-3 mt-4"></div><h3 id="review-title" class="font-bold mt-4"></h3><p id="review-body" class="whitespace-pre-wrap mt-3 max-h-72 overflow-auto"></p><p class="text-sm text-gray-600 mt-4">Pastikan fakta, waktu, dan penerimanya sudah sesuai.</p><div class="flex gap-3 mt-5 flex-wrap"><button id="confirm-publish" type="button" class="bg-blue-600 text-white px-4 py-2 rounded-xl">Ya, Terbitkan</button><button id="close-review" type="button" class="border border-slate-200 px-4 py-2 rounded-xl">Kembali Mengedit</button></div></dialog>
    @if($isManager && $item->post_category === 'Berita')
    <dialog id="source-picker" aria-labelledby="source-heading" class="rounded-2xl p-5 sm:p-7 max-w-4xl shadow-xl">
        <div class="flex justify-between gap-3 items-start"><div><h2 id="source-heading" class="text-xl font-bold">Pilih Foto dan Cerita Warga</h2><p class="text-sm text-gray-600 mt-2">Pilih kiriman tentang kegiatan yang sama. Foto dan cerita boleh diambil dari warga yang berbeda.</p></div><button id="close-sources" type="button" class="border border-slate-200 rounded-xl px-3 py-2">Tutup</button></div>
        <p id="source-error" role="alert" hidden class="mt-3 text-red-700"></p>
        <form id="source-search-form" class="flex gap-2 mt-5"><label class="flex-1 min-w-0"><span class="sr-only">Cari judul atau nama warga</span><input id="source-search" type="search" maxlength="100" class="w-full border border-slate-200 rounded-xl p-3" placeholder="Cari judul atau nama warga"></label><button id="source-search-button" type="submit" class="border border-slate-200 rounded-xl px-4 py-2">Cari</button></form>
        <p id="source-results" class="text-sm text-gray-500 mt-3" role="status"></p>
        <div id="source-options" class="grid sm:grid-cols-2 gap-4 mt-4"></div>
        <div class="flex justify-between items-center gap-3 mt-4"><button id="sources-prev" type="button" class="border border-slate-200 rounded-xl px-3 py-2">Sebelumnya</button><span id="source-page" class="text-sm"></span><button id="sources-next" type="button" class="border border-slate-200 rounded-xl px-3 py-2">Berikutnya</button></div>
        <div class="border-t border-slate-200 mt-5 pt-4"><p id="source-selection" role="status" class="font-semibold">Belum ada foto atau cerita yang dipilih.</p><p class="text-sm text-gray-500 mt-1">Pilihan tetap tersimpan saat Anda mencari atau pindah halaman.</p><button id="merge" type="button" disabled class="bg-blue-600 text-white px-4 py-3 rounded-xl mt-3">Periksa Pilihan</button></div>
        <section id="merge-review" hidden class="mt-5 p-4 bg-blue-50 rounded-xl" aria-labelledby="merge-heading"><h3 id="merge-heading" class="font-bold">Gabungkan ke berita ini?</h3><ul id="merge-summary" class="list-disc pl-5 mt-3 space-y-2"></ul><p class="text-sm text-slate-700 mt-3">Foto masuk ke galeri. Cerita tersedia untuk ditambahkan ke tulisan atau dirapikan dengan AI. Kiriman asal ditandai Digabungkan; perubahan belum otomatis diterbitkan.</p><p class="text-sm text-slate-700 mt-2">Pastikan semua pilihan membahas kegiatan yang sama.</p><div class="flex gap-3 mt-4 flex-wrap"><button id="confirm-merge" type="button" class="bg-blue-600 text-white rounded-xl px-4 py-3">Ya, Gabungkan ke Draf</button><button id="change-selection" type="button" class="border border-slate-200 rounded-xl px-4 py-3">Ubah Pilihan</button></div></section>
    </dialog>
    @endif
</main>
<script id="content-initial" type="application/json">{!! json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script type="module" src="{{ asset('js/content-workspace.js') }}?v={{ filemtime(public_path('js/content-workspace.js')) }}"></script>
@endsection
