@php($newsStates = ['draft'=>'Draf — belum dikirim','pending'=>'Menunggu pemeriksaan admin','review'=>'Sedang diperiksa admin','published'=>'Disetujui dan diterbitkan','scheduled'=>'Disetujui — menunggu jadwal terbit','merged'=>'Digabungkan ke berita lain','rejected'=>'Tidak disetujui','cancelled'=>'Dibatalkan','archived'=>'Diarsipkan'])
@forelse($newsItems as $news)
<article class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm" data-news-id="{{ $news->id }}">
    <div class="flex flex-wrap justify-between gap-2"><h2 class="font-bold text-lg">{{ (int)$news->admin_id === (int)auth()->id() ? ($news->draft_data['title'] ?? $news->title ?: 'Draf tanpa judul') : $news->title }}</h2><span class="text-sm font-semibold text-[#115789]">{{ $newsStates[$news->workflow_state] ?? 'Sedang diproses' }}</span></div>
    @if((int)$news->admin_id !== (int)auth()->id())<p class="text-sm mt-2">Anda dicantumkan sebagai penyumbang cerita atau foto dalam berita ini.</p>@endif
    @if($news->workflow_state === 'review' && $news->editor)<p class="text-sm mt-2">Ditangani oleh {{ $news->editor->name }}.</p>@endif
    @if($news->rejection_reason && (int)$news->admin_id === (int)auth()->id())<p class="text-sm mt-2 text-red-700">Alasan: {{ $news->rejection_reason }}</p>@endif
    <p class="text-sm text-gray-500 mt-2">Diperbarui {{ $news->updated_at?->format('d M Y H:i') }}</p>
    <div class="flex flex-wrap gap-4 mt-3">
    @if((int)$news->admin_id === (int)auth()->id())<a href="{{ route('content.show',$news) }}" class="text-[#115789] underline font-semibold">Lihat Kiriman</a>@endif
    @if($news->is_active)<a href="{{ route('announcements.show',$news) }}" class="text-[#115789] underline font-semibold">Baca Berita</a>@elseif($news->mergedInto?->is_active)<a href="{{ route('announcements.show',$news->mergedInto) }}" class="text-[#115789] underline font-semibold">Baca Berita Gabungan</a>@endif
    </div>
</article>
@empty
<div class="rounded-2xl bg-white border border-slate-200 p-6 text-center"><h2 class="font-bold">Belum Ada Aktivitas Berita Daerah</h2><p class="text-gray-600 mt-2">Kiriman Anda dan berita yang mencantumkan kontribusi Anda akan tampil di sini.</p></div>
@endforelse
