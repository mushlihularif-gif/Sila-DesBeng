@extends('layouts.user')

@section('title', $announcement->title)

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16">
        {{-- Background Image --}}
        <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
            <img src="{{ asset('Admin/img/elements/background.webp') }}" class="w-full h-full object-cover" alt="">
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            
            {{-- Back Button --}}
            <div class="mb-8 animate-section">
                <a href="{{ route('announcements.index') }}"
                    class="inline-flex items-center gap-2 backdrop-blur-sm bg-white/70 hover:bg-white text-blue-600 hover:text-blue-700 px-6 py-2.5 rounded-full transition-all border border-white/80 shadow-md font-semibold text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>Kembali ke Daftar Kabar</span>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Main Content (Kiri) --}}
                <div class="lg:col-span-2 space-y-8 animate-section">
                    <div class="backdrop-blur-sm bg-white/90 rounded-3xl overflow-hidden shadow-xl border border-white/80 p-6 md:p-8">
                        {{-- Judul dan Meta Info (Gaya Detik) --}}
                        <div class="text-center mb-6">
                            <h1 class="text-3xl md:text-4xl font-bold text-blue-900 leading-tight mb-4">
                                {{ $announcement->title }}
                            </h1>
                            <div class="text-sm font-medium">
                                <p class="text-blue-600 mb-1">
                                    {{ $announcement->admin->name ?? 'Admin Sistem' }} - <span class="text-orange-500 font-bold">Pemerintah {{ $announcement->region->public_name ?? 'Pusat' }}</span>
                                </p>
                                <p class="text-gray-500">
                                    {{ $announcement->region->public_name ?? 'Pusat' }}, {{ \Carbon\Carbon::parse($announcement->created_at)->translatedFormat('l, d M Y H:i') }} WIB
                                </p>
                            </div>
                        </div>

                        {{-- Image/Banner --}}
                        <div class="w-full h-64 md:h-[450px] bg-gray-100 relative group overflow-hidden rounded-2xl mb-8 shadow-sm">
                            @if($announcement->images && $announcement->images->count() > 0)
                                <div class="flex transition-transform duration-700 ease-in-out h-full" id="slider-main">
                                    @foreach($announcement->images as $img)
                                        <div class="w-full h-full flex-shrink-0 relative">
                                            <img src="{{ Storage::url($img->image_path) }}" alt="{{ $announcement->title }}" class="w-full h-full object-cover">
                                        </div>
                                    @endforeach
                                </div>
                                
                                @if($announcement->images->count() > 1)
                                    <!-- Controls -->
                                    <button id="slider-prev" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/30 hover:bg-black/50 backdrop-blur-md rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                        <i class="bx bx-chevron-left text-2xl"></i>
                                    </button>
                                    <button id="slider-next" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/30 hover:bg-black/50 backdrop-blur-md rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                        <i class="bx bx-chevron-right text-2xl"></i>
                                    </button>
                                @endif
                            @elseif($announcement->cover_image)
                                <div class="w-full h-full flex-shrink-0 relative">
                                    <img src="{{ Storage::url($announcement->cover_image) }}" alt="{{ $announcement->title }}" class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 bg-slate-50">
                                    <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span class="text-sm font-medium">Tidak ada gambar</span>
                                </div>
                            @endif
                        </div>

                        {{-- Content Body --}}
                        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed text-justify">
                                {!! nl2br(e($announcement->description)) !!}
                            
                                  {{-- Baca Juga (Detik Style) --}}
                                  @if($relatedAnnouncements->count() > 0)
                                  <div class="mt-8 pt-6 border-t border-gray-200">
                                      <h4 class="font-bold text-gray-900 mb-3 text-lg flex items-center gap-2">
                                          <div class="w-1 h-5 bg-blue-600 rounded-full"></div>
                                          Baca juga:
                                      </h4>
                                      <ul class="space-y-3">
                                          @foreach($relatedAnnouncements->take(2) as $related)
                                          <li>
                                              <a href="{{ route('announcements.show', $related->id) }}" class="text-blue-700 font-bold hover:text-blue-900 hover:underline text-lg">
                                                  {{ $related->title }}
                                              </a>
                                          </li>
                                          @endforeach
                                      </ul>
                                  </div>
                                  @endif
</div>
                        </div>
                    </div>
                </div>

                
                    {{-- {{ $announcement->post_category === 'Pengumuman' ? 'Pengumuman Terkait' : 'Berita Terkait' }} (Detik Style Grid) --}}
                    @if($relatedAnnouncements->count() > 0)
                    <div class="mt-8 mb-4">
                        <h3 class="text-xl font-bold text-blue-900 mb-6 flex items-center gap-2 border-b-2 border-blue-100 pb-2 inline-block">
                            {{ $announcement->post_category === 'Pengumuman' ? 'Pengumuman Terkait' : 'Berita Terkait' }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($relatedAnnouncements as $related)
                            <a href="{{ route('announcements.show', $related->id) }}" class="group flex flex-col gap-3">
                                <div class="w-full h-40 rounded-xl overflow-hidden bg-gray-100 relative shadow-sm">
                                    @if($related->cover_image)
                                        <img src="{{ Storage::url($related->cover_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400 bg-slate-50">
                                            <i class="bx bx-news text-4xl"></i>
                                        </div>
                                    @endif
                                </div>
                                <h4 class="font-bold text-gray-900 leading-snug group-hover:text-blue-600 transition-colors">
                                    {{ $related->title }}
                                </h4>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                {{-- Sidebar (Kanan) --}}
                <div class="space-y-6 animate-section" style="animation-delay: 0.2s;">
                    
                    {{-- Info Event Card --}}
                    @if($announcement->event_date || $announcement->location)
                    <div class="backdrop-blur-sm bg-blue-50/90 rounded-3xl p-6 shadow-md border border-blue-100">
                        <h3 class="text-lg font-bold text-blue-800 mb-5 flex items-center gap-2 border-b border-blue-200 pb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Detail Pelaksanaan
                        </h3>
                        <div class="space-y-5">
                            @if($announcement->event_date)
                            <div class="flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-0.5">Waktu</p>
                                    <p class="text-gray-900 font-bold">{{ $announcement->event_date->format('l, d F Y') }}</p>
                                    <p class="text-blue-600 font-semibold text-sm">{{ $announcement->event_date->format('H:i') }} WIB</p>
                                </div>
                            </div>
                            @endif
                            
                            @if($announcement->location)
                            <div class="flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-0.5">Lokasi</p>
                                    <p class="text-gray-900 font-bold text-sm leading-snug">{{ $announcement->location }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Laporan Reference Card --}}
                    @if($announcement->laporan)
                    <div class="backdrop-blur-sm bg-white/80 rounded-3xl p-6 shadow-md border border-white/80 relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 text-7xl opacity-5">💬</div>
                        <h3 class="text-md font-bold text-gray-800 mb-2">Tindak Lanjut Laporan Warga</h3>
                        <p class="text-xs text-gray-600 mb-4 leading-relaxed">Pengumuman ini diterbitkan sebagai tindak lanjut dari laporan warga berikut:</p>
                        
                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                            <p class="text-blue-600 font-bold mb-1.5 text-sm line-clamp-2">"{{ $announcement->laporan->nama }}"</p>
                            <p class="text-xs text-gray-500 mb-3">Oleh: <span class="font-semibold text-gray-700">{{ $announcement->laporan->user->name ?? 'Warga' }}</span></p>
                            <span class="inline-flex px-2.5 py-1 bg-green-100 text-green-700 text-xs rounded-md border border-green-200 font-semibold">Sedang Diproses</span>
                        </div>
                    </div>
                    @endif

                    {{-- Related Announcements --}}
                    @if($relatedAnnouncements->count() > 0)
                    <div class="backdrop-blur-sm bg-white/80 rounded-3xl p-6 shadow-md border border-white/80">
                        <h3 class="text-md font-bold text-gray-800 mb-5 border-b border-gray-200 pb-3">{{ $announcement->post_category === 'Pengumuman' ? 'Pengumuman Lainnya' : 'Rekomendasi untuk Anda' }}</h3>
                        <div class="space-y-4">
                            @foreach($relatedAnnouncements as $related)
                            <a href="{{ route('announcements.show', $related->id) }}" class="block group">
                                <div class="flex gap-4 items-start p-2 rounded-2xl hover:bg-blue-50 transition-colors">
                                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 shrink-0 border border-gray-200 shadow-sm">
                                        @if($related->cover_image)
                                            <img src="{{ Storage::url($related->cover_image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-2xl bg-gradient-to-br from-gray-50 to-gray-200 opacity-60">📰</div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-gray-900 text-sm font-bold group-hover:text-blue-600 transition-colors line-clamp-2 leading-snug">{{ $related->title }}</h4>
                                        <p class="text-xs text-gray-500 mt-1.5 font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            {{ $related->created_at->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('styles')
<style>
    * {
        font-family: 'Inter', sans-serif;
    }

    /* Smooth animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-section {
        animation: fadeIn 0.6s ease-out forwards;
        opacity: 0;
    }
</style>
@endpush

@push('scripts')
@if($announcement->images && $announcement->images->count() > 1)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const slider = document.getElementById('slider-main');
        const prev = document.getElementById('slider-prev');
        const next = document.getElementById('slider-next');
        const indicators = document.getElementById('slider-indicators').children;
        const total = {{ $announcement->images->count() }};
        let current = 0;

        function updateSlider() {
            slider.style.transform = `translateX(-${current * 100}%)`;
            Array.from(indicators).forEach((ind, i) => {
                if(i === current) {
                    ind.className = 'w-4 h-2 rounded-full transition-all bg-white';
                } else {
                    ind.className = 'w-2 h-2 rounded-full transition-all bg-white/50';
                }
            });
        }

        prev.addEventListener('click', () => {
            current = current > 0 ? current - 1 : total - 1;
            updateSlider();
        });

        next.addEventListener('click', () => {
            current = current < total - 1 ? current + 1 : 0;
            updateSlider();
        });

        Array.from(indicators).forEach((ind, i) => {
            ind.addEventListener('click', () => {
                current = i;
                updateSlider();
            });
        });

        // Auto slide
        setInterval(() => {
            current = current < total - 1 ? current + 1 : 0;
            updateSlider();
        }, 5000);
    });
</script>
@endif
@endpush
